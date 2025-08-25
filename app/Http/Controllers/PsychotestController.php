<?php

namespace App\Http\Controllers;

use App\Models\PsychotestSchedule;
use App\Models\PsychotestSession;
use App\Models\PsychotestCategory;
use App\Models\PsychotestQuestion;
use App\Models\PsychotestAnswer;
use App\Models\PsychotestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PsychotestController extends Controller
{
    /**
     * Show test interface
     */
    public function test($sessionId)
    {
        try {
            $session = PsychotestSession::with(['schedule.candidates', 'category'])->findOrFail($sessionId);
            
            // Security check - ensure session belongs to authenticated user
            if (!$this->validateSessionAccess($session)) {
                return redirect()->route('psychotest.login')->with('error', 'Invalid session access.');
            }

            $schedule = $session->schedule;
            $category = $session->category;

            // Check if session can still be accessed
            if (!$schedule->canStart()) {
                return redirect()->route('psychotest.expired')->with('error', 'Test session has expired.');
            }

            // Start session if it's the first time
            if ($session->status === 'pending') {
                $session->start();
            }

            // Check if session has time remaining
            if (!$session->hasTimeRemaining()) {
                return $this->handleTimeExpired($session);
            }

            // Get questions for this category
            $questions = $this->getQuestionsForCategory($category);
            
            if ($questions->isEmpty()) {
                return redirect()->back()->with('error', 'No questions found for this category.');
            }

            // Get existing answers
            $answers = $this->getExistingAnswers($session);

            // Determine test type for special handling
            $isEPPS = $category->isEPPS();
            $isKraeplin = $category->isKraeplin();
            $isFieldTest = $category->isFieldSpecific();

            // Calculate remaining seconds
            $remainingSeconds = $session->getRemainingSeconds();

            return view('psychotest.test', compact(
                'session', 
                'schedule', 
                'category', 
                'questions', 
                'answers', 
                'remainingSeconds',
                'isEPPS',
                'isKraeplin', 
                'isFieldTest'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading psychotest:', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('psychotest.login')->with('error', 'Failed to load test. Please try again.');
        }
    }

    /**
     * Save answer with enhanced security tracking
     */
    public function saveAnswer(Request $request)
    {
        try {
            // Check if this is a security violation report
            if ($request->has('is_violation') && $request->is_violation) {
                return $this->handleSecurityViolation($request);
            }

            // Regular answer saving
            $validated = $request->validate([
                'session_id' => 'required|integer|exists:psychotest_sessions,id',
                'question_id' => 'required|integer|exists:psychotest_questions,id',
                'answer' => 'required|string'
            ]);

            $session = PsychotestSession::findOrFail($validated['session_id']);
            
            // Security validation
            if (!$this->validateSessionAccess($session)) {
                return response()->json(['success' => false, 'message' => 'Invalid session access'], 403);
            }

            // Check if session is still active
            if (!$session->hasTimeRemaining()) {
                return response()->json(['success' => false, 'message' => 'Session has expired'], 410);
            }

            // Save or update answer
            $answer = PsychotestAnswer::updateOrCreate([
                'session_id' => $validated['session_id'],
                'question_id' => $validated['question_id']
            ], [
                'schedule_id' => $session->schedule_id,
                'answer' => $validated['answer'],
                'answered_at' => now(),
                'time_taken_seconds' => $request->input('time_taken_seconds', 0),
                'points_earned' => $this->calculatePoints($validated['question_id'], $validated['answer'])
            ]);

            return response()->json([
                'success' => true,
                'answer_id' => $answer->id,
                'points_earned' => $answer->points_earned
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving answer:', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save answer'
            ], 500);
        }
    }

    /**
     * Handle security violation reporting
     */
    private function handleSecurityViolation(Request $request)
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|integer|exists:psychotest_sessions,id',
                'violation_type' => 'required|string|max:50',
                'violation_message' => 'required|string|max:255',
                'violation_count' => 'required|integer',
                'total_violations' => 'required|integer',
                'timestamp' => 'required|string',
                'user_agent' => 'nullable|string',
                'current_question' => 'nullable|integer'
            ]);

            $session = PsychotestSession::findOrFail($validated['session_id']);

            // Create security violations table entry
            DB::table('psychotest_security_violations')->insert([
                'session_id' => $validated['session_id'],
                'violation_type' => $validated['violation_type'],
                'violation_message' => $validated['violation_message'],
                'violation_count' => $validated['violation_count'],
                'total_violations' => $validated['total_violations'],
                'violation_timestamp' => Carbon::parse($validated['timestamp']),
                'user_agent' => $validated['user_agent'] ?? null,
                'current_question' => $validated['current_question'] ?? null,
                'ip_address' => $request->ip(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update session security status
            $this->updateSessionSecurityStatus($validated['session_id'], $validated['total_violations']);

            // Log critical violations
            if ($validated['total_violations'] >= 5) {
                Log::warning('High security violations detected', [
                    'session_id' => $validated['session_id'],
                    'total_violations' => $validated['total_violations'],
                    'latest_violation' => $validated['violation_type'],
                    'ip_address' => $request->ip(),
                    'candidate' => $session->schedule->candidates->name ?? 'Unknown'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Security event recorded'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to record security violation', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record security event'
            ], 500);
        }
    }

    /**
     * Submit test with security data
     */
    public function submit(Request $request)
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|integer|exists:psychotest_sessions,id',
                'security_violations' => 'nullable|json'
            ]);

            $session = PsychotestSession::with(['schedule.candidates', 'category'])->findOrFail($validated['session_id']);
            
            // Security validation
            if (!$this->validateSessionAccess($session)) {
                return redirect()->route('psychotest.login')->with('error', 'Invalid session access.');
            }

            // Parse security violations if provided
            $securityViolations = [];
            if ($request->has('security_violations')) {
                $securityViolations = json_decode($request->security_violations, true) ?? [];
            }

            // Calculate total violations
            $totalViolations = 0;
            if (is_array($securityViolations)) {
                $totalViolations = array_sum($securityViolations);
            }

            // Determine final security status
            $securityStatus = $this->determineSecurityStatus($totalViolations);

            // Complete the session
            $session->complete();

            // Calculate session results
            $this->calculateSessionResults($session);

            // Update session with final security data
            $sessionData = $session->session_data ?? [];
            $sessionData['security_summary'] = [
                'total_violations' => $totalViolations,
                'violation_breakdown' => $securityViolations,
                'security_status' => $securityStatus,
                'final_assessment' => $this->getSecurityAssessment($totalViolations),
                'submitted_at' => now()->toISOString()
            ];

            $session->update([
                'session_data' => $sessionData,
                'security_status' => $securityStatus
            ]);

            // Check if this was the last session for the schedule
            $schedule = $session->schedule;
            if ($schedule->allSessionsCompleted()) {
                $this->finalizeScheduleResults($schedule);
            }

            // Log completion with security info
            Log::info('Psychotest session completed', [
                'session_id' => $validated['session_id'],
                'candidate' => $session->schedule->candidates->name,
                'category' => $session->category->name,
                'security_status' => $securityStatus,
                'total_violations' => $totalViolations,
                'answers_count' => $session->answers()->count()
            ]);

            // Redirect with appropriate message based on security status
            return $this->redirectAfterSubmission($securityStatus, $schedule);

        } catch (\Exception $e) {
            Log::error('Failed to submit psychotest', [
                'error' => $e->getMessage(),
                'session_id' => $request->session_id
            ]);

            return redirect()->back()->with('error', 'Failed to submit test. Please try again.');
        }
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Validate session access
     */
    private function validateSessionAccess($session)
    {
        // Add your session validation logic here
        // For example, check if user is logged in with correct credentials
        return true; // Simplified for now
    }

    /**
     * Handle time expired session
     */
    private function handleTimeExpired($session)
    {
        $session->complete();
        $this->calculateSessionResults($session);

        return redirect()->route('psychotest.expired')->with('info', 'Test time has expired. Your answers have been saved automatically.');
    }

    /**
     * Get questions for category with proper ordering
     */
    private function getQuestionsForCategory($category)
    {
        return PsychotestQuestion::where('category_id', $category->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    /**
     * Get existing answers for session
     */
    private function getExistingAnswers($session)
    {
        return $session->answers()
            ->get()
            ->keyBy('question_id')
            ->map(function($answer) {
                return (object)['answer' => $answer->answer];
            });
    }

    /**
     * Calculate points for an answer
     */
    private function calculatePoints($questionId, $answer)
    {
        $question = PsychotestQuestion::find($questionId);
        if (!$question) return 0;

        // For EPPS and Kraeplin, all answers are valid
        if (in_array($question->type, ['epps_forced_choice', 'kraeplin'])) {
            return 1;
        }

        // For other question types, check correct answer
        if ($question->correct_answer && $answer === $question->correct_answer) {
            return $question->points ?? 1;
        }

        return 0;
    }

    /**
     * Update session security status
     */
    private function updateSessionSecurityStatus($sessionId, $totalViolations)
    {
        $securityStatus = $this->determineSecurityStatus($totalViolations);

        DB::table('psychotest_sessions')
            ->where('id', $sessionId)
            ->update([
                'security_status' => $securityStatus,
                'total_violations' => $totalViolations,
                'last_violation_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * Determine security status from violation count
     */
    private function determineSecurityStatus($totalViolations)
    {
        if ($totalViolations >= 8) {
            return 'critical';
        } elseif ($totalViolations >= 5) {
            return 'high_risk';
        } elseif ($totalViolations >= 3) {
            return 'moderate_risk';
        } else {
            return 'normal';
        }
    }

    /**
     * Get security assessment text
     */
    private function getSecurityAssessment($totalViolations)
    {
        if ($totalViolations >= 8) {
            return 'Critical security violations detected. Manual review required.';
        } elseif ($totalViolations >= 5) {
            return 'High risk security violations detected. Additional verification recommended.';
        } elseif ($totalViolations >= 3) {
            return 'Moderate security violations detected. Monitor for patterns.';
        } elseif ($totalViolations > 0) {
            return 'Minor security events detected. Within acceptable range.';
        } else {
            return 'No security violations detected. Clean test session.';
        }
    }

    /**
     * Calculate session results based on category type
     */
    private function calculateSessionResults($session)
    {
        $category = $session->category;
        $answers = $session->answers;

        if ($category->isKraeplin()) {
            $this->calculateKraeplinResults($session);
        } elseif ($category->isEPPS()) {
            $this->calculateEPPSResults($session);
        } elseif ($category->isFieldSpecific()) {
            $this->calculateFieldTestResults($session);
        } else {
            $this->calculateStandardResults($session);
        }
    }

    /**
     * Calculate Kraeplin test results
     */
    private function calculateKraeplinResults($session)
    {
        $answers = $session->answers;
        $totalAnswers = $answers->count();
        $correctAnswers = $answers->where('points_earned', '>', 0)->count();
        
        if ($totalAnswers === 0) {
            $this->createDefaultResults($session, 'kraeplin');
            return;
        }

        $accuracy = ($correctAnswers / $totalAnswers) * 100;
        $completedColumns = intval($totalAnswers / 30) + ($totalAnswers % 30 > 0 ? 1 : 0);
        $speedScore = min(100, ($totalAnswers / ($completedColumns * 30)) * 100);
        $consistencyScore = 75; // Default estimation
        $concentrationScore = $accuracy > 70 ? 80 : 60;
        
        $kraeplinScore = ($accuracy * 0.25) + ($speedScore * 0.35) + 
                        ($consistencyScore * 0.25) + ($concentrationScore * 0.15);
        
        $grade = $this->getKraeplinGrade($kraeplinScore);
        
        $sessionData = $session->session_data ?? [];
        $sessionData['results'] = [
            'total_questions' => $totalAnswers,
            'answered_questions' => $totalAnswers,
            'total_points' => $totalAnswers,
            'earned_points' => $correctAnswers,
            'percentage' => round($kraeplinScore, 2),
            'accuracy' => round($accuracy, 2),
            'speed_score' => round($speedScore, 2),
            'consistency_score' => $consistencyScore,
            'concentration_score' => $concentrationScore,
            'kraeplin_score' => round($kraeplinScore, 2),
            'kraeplin_grade' => $grade,
            'completed_columns' => $completedColumns,
            'interpretation' => $this->getKraeplinInterpretation($kraeplinScore, $accuracy),
            'recommendation' => $this->getKraeplinRecommendation($grade)
        ];

        $session->update(['session_data' => $sessionData]);
    }

    /**
     * Calculate EPPS test results
     */
    private function calculateEPPSResults($session)
    {
        $answers = $session->answers;
        $totalAnswers = $answers->count();
        
        if ($totalAnswers === 0) {
            $this->createDefaultResults($session, 'epps');
            return;
        }

        // EPPS analysis would be more complex in real implementation
        $completionRate = min(100, ($totalAnswers / 225) * 100); // Assuming 225 pairs in EPPS
        
        $sessionData = $session->session_data ?? [];
        $sessionData['results'] = [
            'total_questions' => 225, // Standard EPPS pair count
            'answered_questions' => $totalAnswers,
            'total_points' => $totalAnswers,
            'earned_points' => $totalAnswers, // All EPPS answers are valid
            'percentage' => $completionRate,
            'completion_rate' => $completionRate,
            'dominant_traits' => $this->calculateEPPSTraits($answers),
            'interpretation' => $this->getEPPSInterpretation($completionRate),
            'recommendation' => $this->getEPPSRecommendation($completionRate)
        ];

        $session->update(['session_data' => $sessionData]);
    }

    /**
     * Calculate Field Test results
     */
    private function calculateFieldTestResults($session)
    {
        $answers = $session->answers;
        $totalAnswers = $answers->count();
        $correctAnswers = $answers->where('points_earned', '>', 0)->count();
        
        if ($totalAnswers === 0) {
            $this->createDefaultResults($session, 'field');
            return;
        }

        $accuracy = ($correctAnswers / $totalAnswers) * 100;
        $fieldScores = $this->calculateFieldScores($answers);
        $recommendedField = $this->getRecommendedField($fieldScores);
        
        $sessionData = $session->session_data ?? [];
        $sessionData['results'] = [
            'total_questions' => $session->category->total_questions,
            'answered_questions' => $totalAnswers,
            'total_points' => $totalAnswers,
            'earned_points' => $correctAnswers,
            'percentage' => round($accuracy, 2),
            'accuracy_rate' => round($accuracy, 2),
            'field_scores' => $fieldScores,
            'recommended_field' => $recommendedField,
            'interpretation' => $this->getFieldTestInterpretation($accuracy, $recommendedField),
            'recommendation' => $this->getFieldTestRecommendation($recommendedField)
        ];

        $session->update(['session_data' => $sessionData]);
    }

    /**
     * Calculate standard test results
     */
    private function calculateStandardResults($session)
    {
        $answers = $session->answers;
        $totalAnswers = $answers->count();
        $correctAnswers = $answers->where('points_earned', '>', 0)->count();
        
        if ($totalAnswers === 0) {
            $this->createDefaultResults($session, 'standard');
            return;
        }

        $accuracy = ($correctAnswers / $totalAnswers) * 100;
        $grade = $this->getStandardGrade($accuracy);
        
        $sessionData = $session->session_data ?? [];
        $sessionData['results'] = [
            'total_questions' => $session->category->total_questions,
            'answered_questions' => $totalAnswers,
            'total_points' => $totalAnswers,
            'earned_points' => $correctAnswers,
            'percentage' => round($accuracy, 2),
            'accuracy_rate' => round($accuracy, 2),
            'grade' => $grade,
            'interpretation' => $this->getStandardInterpretation($accuracy),
            'recommendation' => $this->getStandardRecommendation($grade)
        ];

        $session->update(['session_data' => $sessionData]);
    }

    /**
     * Create default results for incomplete sessions
     */
    private function createDefaultResults($session, $type)
    {
        $sessionData = $session->session_data ?? [];
        $sessionData['results'] = [
            'total_questions' => $session->category->total_questions,
            'answered_questions' => 0,
            'total_points' => 0,
            'earned_points' => 0,
            'percentage' => 0,
            'status' => 'incomplete',
            'interpretation' => 'Test was not completed',
            'recommendation' => 'Retake test for valid results'
        ];

        $session->update(['session_data' => $sessionData]);
    }

    /**
     * Finalize schedule results when all sessions are completed
     */
    private function finalizeScheduleResults($schedule)
    {
        $sessions = $schedule->sessions()->with('category')->get();
        $totalScore = 0;
        $totalWeight = 0;
        $categoryScores = [];

        foreach ($sessions as $session) {
            $results = $session->session_data['results'] ?? [];
            $percentage = $results['percentage'] ?? 0;
            $weight = 1; // Could be category-specific weight
            
            $totalScore += $percentage * $weight;
            $totalWeight += $weight;
            
            $categoryScores[$session->category_id] = [
                'category_name' => $session->category->name,
                'results' => $results,
                'security_status' => $session->security_status ?? 'normal'
            ];
        }

        $overallPercentage = $totalWeight > 0 ? $totalScore / $totalWeight : 0;
        $overallGrade = $this->getOverallGrade($overallPercentage);

        // Create or update result
        PsychotestResult::updateOrCreate([
            'schedule_id' => $schedule->id
        ], [
            'total_questions' => $sessions->sum(function($s) { return $s->session_data['results']['total_questions'] ?? 0; }),
            'answered_questions' => $sessions->sum(function($s) { return $s->session_data['results']['answered_questions'] ?? 0; }),
            'total_points' => $sessions->sum(function($s) { return $s->session_data['results']['total_points'] ?? 0; }),
            'earned_points' => $sessions->sum(function($s) { return $s->session_data['results']['earned_points'] ?? 0; }),
            'percentage' => round($overallPercentage, 2),
            'grade' => $overallGrade,
            'category_scores' => json_encode($categoryScores),
            'notes' => json_encode([
                'security_summary' => $this->generateSecuritySummary($sessions),
                'final_assessment' => $this->generateFinalAssessment($overallPercentage, $categoryScores)
            ]),
            'total_time_spent_seconds' => $sessions->sum('time_spent_seconds'),
            'completion_status' => 'completed'
        ]);

        // Update schedule status
        $schedule->update(['status' => 'completed', 'completed_at' => now()]);
    }

    /**
     * Generate security summary for final results
     */
    private function generateSecuritySummary($sessions)
    {
        $totalViolations = 0;
        $riskySessions = 0;
        $violationTypes = [];

        foreach ($sessions as $session) {
            $sessionViolations = $session->total_violations ?? 0;
            $totalViolations += $sessionViolations;
            
            if ($sessionViolations >= 3) {
                $riskySessions++;
            }
        }

        return [
            'total_violations' => $totalViolations,
            'risky_sessions' => $riskySessions,
            'overall_security_status' => $totalViolations >= 10 ? 'high_risk' : ($totalViolations >= 5 ? 'moderate_risk' : 'normal'),
            'security_recommendation' => $totalViolations >= 8 ? 'Manual review required' : 'Acceptable security profile'
        ];
    }

    /**
     * Generate final assessment
     */
    private function generateFinalAssessment($overallPercentage, $categoryScores)
    {
        $assessment = [];
        
        if ($overallPercentage >= 85) {
            $assessment[] = 'Excellent overall performance';
        } elseif ($overallPercentage >= 75) {
            $assessment[] = 'Good overall performance';
        } elseif ($overallPercentage >= 65) {
            $assessment[] = 'Satisfactory performance';
        } else {
            $assessment[] = 'Below average performance';
        }

        // Add category-specific insights
        foreach ($categoryScores as $categoryScore) {
            $results = $categoryScore['results'];
            if (isset($results['interpretation'])) {
                $assessment[] = $categoryScore['category_name'] . ': ' . $results['interpretation'];
            }
        }

        return implode('. ', $assessment);
    }

    /**
     * Redirect after submission based on security status
     */
    private function redirectAfterSubmission($securityStatus, $schedule)
    {
        $nextSession = $schedule->getNextSession();
        
        if ($nextSession) {
            // Continue to next session
            $message = 'Test section completed successfully! ';
            if ($securityStatus !== 'normal') {
                $message .= 'Some security events were detected during your test. ';
            }
            $message .= 'Continue to the next section.';
            
            return redirect()->route('psychotest.test', $nextSession->id)
                ->with('info', $message);
        } else {
            // All sessions completed
            if ($securityStatus === 'critical') {
                return redirect()->route('psychotest.completed')
                    ->with('warning', 'Test completed. Your session has been flagged for review due to security violations.');
            } elseif ($securityStatus === 'high_risk') {
                return redirect()->route('psychotest.completed')
                    ->with('info', 'Test completed. Some security violations were detected during your test.');
            } else {
                return redirect()->route('psychotest.completed')
                    ->with('success', 'Test completed successfully!');
            }
        }
    }

    // ==========================================
    // GRADE AND INTERPRETATION HELPERS
    // ==========================================

    private function getKraeplinGrade($score)
    {
        if ($score >= 85) return 'A';
        if ($score >= 75) return 'B';
        if ($score >= 65) return 'C';
        if ($score >= 55) return 'D';
        if ($score >= 45) return 'E';
        return 'F';
    }

    private function getKraeplinInterpretation($score, $accuracy)
    {
        $interpretations = [];
        
        if ($score >= 80) {
            $interpretations[] = "Excellent concentration and calculation abilities";
        } elseif ($score >= 70) {
            $interpretations[] = "Good concentration and above average performance";
        } else {
            $interpretations[] = "Concentration and speed need improvement";
        }
        
        return $interpretations;
    }

    private function getKraeplinRecommendation($grade)
    {
        switch ($grade) {
            case 'A':
                return ['Highly suitable for detail-oriented and analytical roles'];
            case 'B':
                return ['Suitable for administrative and analytical positions'];
            case 'C':
                return ['Adequate for general office work with supervision'];
            default:
                return ['Requires training before placement in analytical roles'];
        }
    }

    private function calculateEPPSTraits($answers)
    {
        // Simplified EPPS trait calculation
        return ['Achievement', 'Dominance', 'Order']; // This would be more complex in reality
    }

    private function getEPPSInterpretation($completionRate)
    {
        return $completionRate >= 80 ? 
            'Comprehensive personality profile completed' : 
            'Partial personality assessment - may need completion';
    }

    private function getEPPSRecommendation($completionRate)
    {
        return $completionRate >= 80 ? 
            ['Suitable for leadership and team roles'] : 
            ['Complete assessment for better insights'];
    }

    private function calculateFieldScores($answers)
    {
        // Simplified field calculation
        return ['audit' => 75, 'tax' => 80, 'accounting' => 70];
    }

    private function getRecommendedField($fieldScores)
    {
        return array_keys($fieldScores, max($fieldScores))[0];
    }

    private function getFieldTestInterpretation($accuracy, $field)
    {
        return "Recommended for {$field} specialization with {$accuracy}% accuracy";
    }

    private function getFieldTestRecommendation($field)
    {
        return ["Focus career development in {$field} area"];
    }

    private function getStandardGrade($accuracy)
    {
        if ($accuracy >= 85) return 'A';
        if ($accuracy >= 75) return 'B';
        if ($accuracy >= 65) return 'C';
        if ($accuracy >= 55) return 'D';
        if ($accuracy >= 45) return 'E';
        return 'F';
    }

    private function getStandardInterpretation($accuracy)
    {
        if ($accuracy >= 80) return 'Excellent performance';
        if ($accuracy >= 70) return 'Good performance';
        if ($accuracy >= 60) return 'Satisfactory performance';
        return 'Needs improvement';
    }

    private function getStandardRecommendation($grade)
    {
        switch ($grade) {
            case 'A':
            case 'B':
                return ['Recommended for advanced positions'];
            case 'C':
                return ['Suitable for standard positions'];
            default:
                return ['Additional training recommended'];
        }
    }

    private function getOverallGrade($percentage)
    {
        if ($percentage >= 85) return 'A';
        if ($percentage >= 75) return 'B';
        if ($percentage >= 65) return 'C';
        if ($percentage >= 55) return 'D';
        if ($percentage >= 45) return 'E';
        return 'F';
    }
}