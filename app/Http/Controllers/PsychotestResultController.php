<?php
// app/Http/Controllers/PsychotestResultController.php - Complete Rewrite
namespace App\Http\Controllers;

use App\Models\PsychotestSchedule;
use App\Models\PsychotestResult;
use App\Models\PsychotestSession;
use App\Models\PsychotestCategory;
use App\Models\PsychotestAnswer;
use App\Models\JobApplication;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PsychotestResultController extends Controller
{
    /**
     * Display a listing of psychotest results
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Build base query based on user type
        $schedulesQuery = PsychotestSchedule::with([
            'candidates.jobs', 
            'result', 
            'sessions.category'
        ]);
        
        if ($user->type != 'admin' && $user->type != 'company') {
            $schedulesQuery->where('created_by', Auth::user()->creatorId());
        }

        // Filter by status - only show completed and in progress tests
        $schedulesQuery->whereIn('status', ['completed', 'in_progress']);

        // Apply filters
        if ($request->filled('date_from')) {
            $schedulesQuery->whereDate('start_time', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $schedulesQuery->whereDate('start_time', '<=', $request->date_to);
        }
        
        if ($request->filled('candidate_search')) {
            $search = $request->candidate_search;
            $schedulesQuery->whereHas('candidates', function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        if ($request->filled('job_filter')) {
            $schedulesQuery->whereHas('candidates.jobs', function($query) use ($request) {
                $query->where('id', $request->job_filter);
            });
        }
        
        if ($request->filled('status_filter')) {
            $schedulesQuery->where('status', $request->status_filter);
        }

        $schedules = $schedulesQuery->orderBy('created_at', 'desc')->paginate(20);

        // Get available jobs for filter
        $jobs = Job::where('created_by', Auth::user()->creatorId())->get();

        // Get statistics
        $stats = $this->getResultStatistics();

        return view('psychotest.results.index', compact('schedules', 'jobs', 'stats'));
    }

    /**
     * Show detailed results for a specific schedule
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $schedule = PsychotestSchedule::with([
            'candidates.jobs',
            'result',
            'sessions.category',
            'sessions.answers.question',
            'answers.question'
        ])->findOrFail($id);

        // Check permission
        if ($user->type != 'admin' && $user->type != 'company') {
            if ($schedule->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }

        // Get detailed session results with proper interpretation
        $sessionResults = $this->getDetailedSessionResults($schedule);
        
        // Get overall performance metrics
        $performanceMetrics = $this->calculatePerformanceMetrics($schedule);
        
        return view('psychotest.results.show', compact('schedule', 'sessionResults', 'performanceMetrics'));
    }

    /**
     * Show results by category for a specific schedule
     */
    public function showByCategory($scheduleId, $categoryId)
    {
        $user = Auth::user();
        
        $schedule = PsychotestSchedule::with('candidates.jobs')->findOrFail($scheduleId);
        
        // Check permission
        if ($user->type != 'admin' && $user->type != 'company') {
            if ($schedule->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }

        $category = PsychotestCategory::findOrFail($categoryId);
        $session = PsychotestSession::where('schedule_id', $scheduleId)
            ->where('category_id', $categoryId)
            ->with(['answers.question'])
            ->first();

        if (!$session) {
            return redirect()->back()->with('error', __('Session not found for this category.'));
        }

        // Get detailed answers for this category with proper analysis
        $answers = $this->getCategoryAnswers($session);
        
        // Get category-specific analysis
        $categoryAnalysis = $this->analyzeCategoryPerformance($session, $category);

        return view('psychotest.results.category', compact('schedule', 'category', 'session', 'answers', 'categoryAnalysis'));
    }

    /**
     * Export results to PDF or Excel
     */
    public function export($id, $format = 'pdf')
    {
        $schedule = PsychotestSchedule::with([
            'candidates.jobs',
            'result',
            'sessions.category',
            'sessions.answers.question'
        ])->findOrFail($id);

        $sessionResults = $this->getDetailedSessionResults($schedule);
        $performanceMetrics = $this->calculatePerformanceMetrics($schedule);

        if ($format === 'pdf') {
            return $this->exportToPDF($schedule, $sessionResults, $performanceMetrics);
        } else {
            return $this->exportToExcel($schedule, $sessionResults, $performanceMetrics);
        }
    }

    /**
     * Compare results between multiple candidates
     */
    public function compare(Request $request)
    {
        $scheduleIds = $request->input('schedule_ids', []);
        
        if (count($scheduleIds) < 2) {
            return redirect()->back()->with('error', __('Please select at least 2 candidates to compare.'));
        }

        $schedules = PsychotestSchedule::with([
            'candidates.jobs',
            'result',
            'sessions.category'
        ])->whereIn('id', $scheduleIds)->get();

        $comparison = $this->generateComparison($schedules);

        return view('psychotest.results.compare', compact('schedules', 'comparison'));
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get category type from database
     */
    private function getCategoryType($categoryId)
    {
        $category = PsychotestCategory::find($categoryId);
        if (!$category) return 'standard';
        
        return $category->code;
    }

    /**
     * Get category name from database  
     */
    private function getCategoryName($categoryId)
    {
        $category = PsychotestCategory::find($categoryId);
        return $category ? $category->name : 'Unknown Test';
    }

    /**
     * Get detailed results for each session with proper interpretation
     */
    private function getDetailedSessionResults($schedule)
    {
        $sessions = $schedule->sessions()->with(['category', 'answers.question'])->get();
        $results = [];

        foreach ($sessions as $session) {
            $sessionData = $session->session_data['results'] ?? [];
            $categoryType = $this->getCategoryType($session->category_id);
            
            $result = [
                'session' => $session,
                'category' => $session->category,
                'category_type' => $categoryType,
                'status' => $session->status,
                'total_questions' => $sessionData['total_questions'] ?? 0,
                'answered_questions' => $sessionData['answered_questions'] ?? 0,
                'total_points' => $sessionData['total_points'] ?? 0,
                'earned_points' => $sessionData['earned_points'] ?? 0,
                'percentage' => $sessionData['percentage'] ?? 0,
                'time_spent' => $session->time_spent_seconds,
                'time_spent_formatted' => $this->formatTime($session->time_spent_seconds),
                'answers_count' => $session->answers()->count(),
            ];

            // Add category-specific interpretations
            switch ($categoryType) {
                case 'kraeplin':
                    $result['kraeplin_analysis'] = [
                        'grade' => $sessionData['kraeplin_grade'] ?? 'N/A',
                        'accuracy' => $sessionData['accuracy'] ?? 0,
                        'speed_score' => $sessionData['speed_score'] ?? 0,
                        'consistency_score' => $sessionData['consistency_score'] ?? 0,
                        'concentration_score' => $sessionData['concentration_score'] ?? 0,
                        'interpretation' => $sessionData['interpretation'] ?? [],
                        'recommendation' => $sessionData['recommendation'] ?? [],
                        'completed_columns' => $sessionData['completed_columns'] ?? 0,
                        'column_scores' => $sessionData['column_scores'] ?? []
                    ];
                    break;
                    
                case 'epps_test':
                    $result['epps_analysis'] = [
                        'completion_rate' => $sessionData['completion_rate'] ?? 0,
                        'dominant_traits' => $sessionData['dominant_traits'] ?? [],
                        'personality_profile' => $sessionData['personality_profile'] ?? [],
                        'dimension_scores' => $sessionData['dimension_scores'] ?? [],
                        'recruitment_recommendation' => $sessionData['recruitment_recommendation'] ?? [],
                        'position_recommendation' => $sessionData['position_recommendation'] ?? []
                    ];
                    break;
                    
                case 'field_test':
                    $result['field_analysis'] = [
                        'field_percentages' => $sessionData['field_percentages'] ?? [],
                        'recommended_field' => $sessionData['recommended_field'] ?? 'General',
                        'division_recommendation' => $sessionData['division_recommendation'] ?? [],
                        'field_analysis' => $sessionData['field_analysis'] ?? [],
                        'field_scores' => $sessionData['field_scores'] ?? []
                    ];
                    break;
                    
                case 'visual_sequence':
                case 'basic_math':
                case 'synonym_antonym':
                    $result['standard_analysis'] = [
                        'accuracy_rate' => $sessionData['accuracy_rate'] ?? 0,
                        'interpretation' => $sessionData['interpretation'] ?? 'No interpretation available',
                        'grade' => $sessionData['grade'] ?? 'N/A'
                    ];
                    break;
            }

            $results[] = $result;
        }

        // Sort by category order
        usort($results, function($a, $b) {
            return $a['category']->order <=> $b['category']->order;
        });

        return $results;
    }

    /**
     * Calculate overall performance metrics with proper interpretation
     */
    private function calculatePerformanceMetrics($schedule)
    {
        $result = $schedule->result;
        if (!$result) {
            return null;
        }

        $notes = json_decode($result->notes, true) ?? [];
        
        // Get session results and recalculate overall score
        $sessionResults = $this->getDetailedSessionResults($schedule);
        $newOverallScore = $this->calculateOverallWeightedScore($sessionResults);
        $newGrade = $this->calculateGradeFromScore($newOverallScore);
        
        // Update result in database to ensure consistency
        $result->update([
            'percentage' => round($newOverallScore, 2),
            'grade' => $newGrade
        ]);
        
        $strengths = [];
        $weaknesses = [];

        foreach ($sessionResults as $sessionResult) {
            $categoryName = $sessionResult['category']->name;
            $percentage = $sessionResult['percentage'];
            $categoryType = $sessionResult['category_type'];
            
            // Adjust thresholds based on category difficulty
            $strengthThreshold = $categoryType === 'kraeplin' || $categoryType === 'field_test' ? 70 : 80;
            $weaknessThreshold = $categoryType === 'kraeplin' || $categoryType === 'field_test' ? 50 : 60;
            
            if ($percentage >= $strengthThreshold) {
                $strengths[] = [
                    'category' => $categoryName,
                    'score' => $percentage,
                    'type' => $categoryType
                ];
            } elseif ($percentage < $weaknessThreshold) {
                $weaknesses[] = [
                    'category' => $categoryName,
                    'score' => $percentage,
                    'type' => $categoryType
                ];
            }
        }

        // Generate detailed decision and recommendation
        $decisionData = $this->generateDetailedDecisionRecommendation($newOverallScore, $strengths, $weaknesses, $sessionResults, $schedule);
        
        return [
            'overall_score' => round($newOverallScore, 2),
            'grade' => $newGrade,
            'total_time' => $this->getTotalTestTime($schedule),
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'insights' => $this->generateImprovedInsights($sessionResults, $notes),
            'recommendation' => $decisionData['detailed_recommendation'],
            'decision_status' => $decisionData['decision']['status'],
            'decision_confidence' => $decisionData['decision']['confidence'],
            'risk_level' => isset($decisionData['risk_assessment']['level']) 
                ? $decisionData['risk_assessment']['level'] 
                : 'SEDANG',
            'risk_details' => $decisionData['risk_assessment'] ?? null,
            'final_assessment' => $notes['final_assessment'] ?? null
        ];
    }


    /**
     * Get answers for a specific category with proper analysis
     */
    private function getCategoryAnswers($session)
    {
        $categoryType = $this->getCategoryType($session->category_id);
        
        return $session->answers()
            ->with('question')
            ->orderBy('question_id')
            ->get()
            ->map(function($answer) use ($categoryType) {
                $analysisData = [
                    'question' => $answer->question,
                    'user_answer' => $answer->answer,
                    'correct_answer' => $answer->question->correct_answer,
                    'points_earned' => $answer->points_earned,
                    'time_taken' => $answer->time_taken_seconds,
                    'answered_at' => $answer->answered_at,
                    'category_type' => $categoryType
                ];
                
                // Add type-specific analysis
                switch ($categoryType) {
                    case 'kraeplin':
                    case 'epps_test':
                        // For Kraeplin and EPPS, there's no "correct" answer concept
                        $analysisData['is_correct'] = true;
                        $analysisData['analysis'] = 'Semua jawaban valid untuk test ini';
                        break;
                        
                    case 'field_test':
                        $analysisData['is_correct'] = $answer->answer === $answer->question->correct_answer;
                        $analysisData['field_topic'] = $this->getFieldTopicFromOrder($answer->question->order);
                        break;
                        
                    default:
                        $analysisData['is_correct'] = $answer->answer === $answer->question->correct_answer;
                        break;
                }
                
                return $analysisData;
            });
    }

    /**
     * Get insights for EPPS test
     */
    private function getEPPSInsights($sessionData)
    {
        $insights = [];
        
        $completionRate = $sessionData['completion_rate'] ?? 0;
        $insights[] = "Tingkat penyelesaian: {$completionRate}%";
        
        // Analisis dari detailed interpretation
        if (isset($sessionData['detailed_interpretation'])) {
            $detailedInterpretation = $sessionData['detailed_interpretation'];
            
            // Personality Summary
            if (isset($detailedInterpretation['personality_summary'])) {
                $insights[] = $detailedInterpretation['personality_summary'];
            }
            
            // Motivational Factors
            if (isset($detailedInterpretation['motivational_factors']) && !empty($detailedInterpretation['motivational_factors'])) {
                $insights[] = "Faktor motivasi utama: " . implode(', ', array_slice($detailedInterpretation['motivational_factors'], 0, 3));
            }
            
            // Work Environment Fit
            if (isset($detailedInterpretation['work_environment_fit']) && !empty($detailedInterpretation['work_environment_fit'])) {
                $insights[] = "Lingkungan kerja yang cocok: " . $detailedInterpretation['work_environment_fit'][0];
            }
            
            // Career Path
            if (isset($detailedInterpretation['career_development_path']) && !empty($detailedInterpretation['career_development_path'])) {
                $insights[] = "Jalur karir yang disarankan: " . implode(' atau ', $detailedInterpretation['career_development_path']);
            }
        }
        
        // Analisis dari dominant traits jika detailed interpretation tidak ada
        if (count($insights) <= 1) {
            if (isset($sessionData['dominant_traits'])) {
                $traits = array_slice($sessionData['dominant_traits'], 0, 3);
                $insights[] = "Karakteristik dominan: " . implode(', ', $traits);
            }
            
            if (isset($sessionData['leadership_potential']['level'])) {
                $level = $sessionData['leadership_potential']['level'];
                $insights[] = "Potensi kepemimpinan: {$level}";
            }
            
            if (isset($sessionData['work_style_analysis']) && !empty($sessionData['work_style_analysis'])) {
                $workStyle = array_slice($sessionData['work_style_analysis'], 0, 2);
                $insights[] = "Gaya kerja: " . implode('. ', $workStyle);
            }
        }
        
        return $insights;
    }

    // Update method getKraeplinInsights
    private function getKraeplinInsights($sessionData)
    {
        $insights = [];
        
        $grade = $sessionData['kraeplin_grade'] ?? 'N/A';
        $accuracy = $sessionData['accuracy'] ?? 0;
        $speedScore = $sessionData['speed_score'] ?? 0;
        $consistencyScore = $sessionData['consistency_score'] ?? 0;
        $concentrationScore = $sessionData['concentration_score'] ?? 0;
        
        $insights[] = "Grade Kraeplin: {$grade}";
        $insights[] = "Akurasi: {$accuracy}% | Kecepatan: {$speedScore}%";
        $insights[] = "Konsistensi: {$consistencyScore}% | Konsentrasi: {$concentrationScore}%";
        
        // Interpretasi berdasarkan grade
        switch ($grade) {
            case 'A':
                $insights[] = "Performa sangat baik - cocok untuk pekerjaan yang membutuhkan konsentrasi tinggi";
                break;
            case 'B':
                $insights[] = "Performa baik - dapat diandalkan untuk tugas administratif dan analisis";
                break;
            case 'C':
                $insights[] = "Performa cukup - cocok untuk pekerjaan dengan supervisi";
                break;
            case 'D':
            case 'E':
                $insights[] = "Performa perlu ditingkatkan - disarankan training tambahan";
                break;
            default:
                $insights[] = "Belum ada data yang cukup untuk evaluasi";
                break;
        }
        
        // Analisis dari interpretation jika ada
        if (isset($sessionData['interpretation']) && !empty($sessionData['interpretation'])) {
            $interpretations = array_slice($sessionData['interpretation'], 0, 2);
            $insights = array_merge($insights, $interpretations);
        }
        
        return $insights;
    }

    // Update method analyzeCategoryPerformance untuk handle case kosong
    private function analyzeCategoryPerformance($session, $category)
    {
        $sessionData = $session->session_data['results'] ?? [];
        $answers = $session->answers;
        $categoryType = $this->getCategoryType($session->category_id);

        $analysis = [
            'completion_rate' => 0,
            'accuracy_rate' => 0,
            'average_time_per_question' => 0,
            'category_insights' => [],
            'performance_level' => 'Tidak Tersedia'
        ];

        // Handle jika tidak ada data
        if (empty($sessionData) && $answers->count() == 0) {
            $analysis['category_insights'] = ["Tidak ada data hasil test untuk kategori ini"];
            return $analysis;
        }

        // Prioritaskan data dari sessionData
        if (!empty($sessionData)) {
            $totalQuestions = $sessionData['total_questions'] ?? $category->total_questions;
            $answeredQuestions = $sessionData['answered_questions'] ?? 0;
            
            if ($totalQuestions > 0) {
                $analysis['completion_rate'] = round(($answeredQuestions / $totalQuestions) * 100, 1);
            }
            
            // Handle kategori khusus
            switch ($categoryType) {
                case 'kraeplin':
                    $analysis['accuracy_rate'] = $sessionData['accuracy'] ?? 0;
                    $analysis['category_insights'] = $this->getKraeplinInsights($sessionData);
                    break;
                    
                case 'epps_test':
                    $analysis['accuracy_rate'] = 100; // EPPS tidak ada jawaban salah
                    $analysis['category_insights'] = $this->getEPPSInsights($sessionData);
                    break;
                    
                default:
                    $analysis['accuracy_rate'] = $sessionData['accuracy_rate'] ?? 0;
                    $analysis['category_insights'] = $this->getStandardTestInsights($sessionData, $categoryType);
                    break;
            }
            
            $percentage = $sessionData['percentage'] ?? 0;
        } else {
            // Fallback ke data answers jika sessionData kosong
            $totalQuestions = $category->total_questions;
            $answeredQuestions = $answers->count();
            $totalTime = $answers->sum('time_taken_seconds') ?? 0;

            if ($totalQuestions > 0) {
                $analysis['completion_rate'] = round(($answeredQuestions / $totalQuestions) * 100, 1);
            }
            
            if ($answeredQuestions > 0) {
                $analysis['average_time_per_question'] = round($totalTime / $answeredQuestions, 1);
                
                if ($categoryType !== 'kraeplin' && $categoryType !== 'epps_test') {
                    $correctAnswers = $answers->where('points_earned', '>', 0)->count();
                    $analysis['accuracy_rate'] = round(($correctAnswers / $answeredQuestions) * 100, 1);
                } else {
                    $analysis['accuracy_rate'] = $categoryType === 'epps_test' ? 100 : 0;
                }
            }
            
            $percentage = $analysis['completion_rate'];
            $analysis['category_insights'] = ["Data diambil dari jawaban individual (data session tidak tersedia)"];
        }

        // Tentukan performance level
        if ($percentage >= 85) $analysis['performance_level'] = 'Sangat Baik';
        elseif ($percentage >= 75) $analysis['performance_level'] = 'Baik'; 
        elseif ($percentage >= 65) $analysis['performance_level'] = 'Cukup';
        elseif ($percentage >= 55) $analysis['performance_level'] = 'Kurang';
        elseif ($percentage > 0) $analysis['performance_level'] = 'Sangat Kurang';

        return $analysis;
    }

    /**
     * Get insights for field test
     */
    private function getFieldTestInsights($sessionData)
    {
        $insights = [];
        
        $recommendedField = $sessionData['recommended_field'] ?? 'General';
        $fieldPercentages = $sessionData['field_percentages'] ?? [];
        
        $insights[] = "Bidang yang direkomendasikan: {$recommendedField}";
        
        foreach ($fieldPercentages as $field => $percentage) {
            $insights[] = ucfirst($field) . ": {$percentage}%";
        }
        
        if (isset($sessionData['division_recommendation']['primary'])) {
            $insights[] = "Rekomendasi: " . $sessionData['division_recommendation']['primary'];
        }
        
        return $insights;
    }

    /**
     * Get insights for standard tests
     */
    private function getStandardTestInsights($sessionData, $categoryType)
    {
        $insights = [];
        
        $percentage = $sessionData['percentage'] ?? 0;
        $accuracyRate = $sessionData['accuracy_rate'] ?? 0;
        
        $testName = $this->getTestDisplayName($categoryType);
        
        $insights[] = "Skor {$testName}: {$percentage}%";
        $insights[] = "Tingkat akurasi: {$accuracyRate}%";
        
        if (isset($sessionData['interpretation'])) {
            $insights[] = $sessionData['interpretation'];
        }
        
        return $insights;
    }

    /**
     * Get field topic from question order (based on field test structure)
     */
    private function getFieldTopicFromOrder($order)
    {
        if ($order <= 10) return 'audit';
        if ($order <= 20) return 'tax';
        if ($order <= 30) return 'accounting';
        return 'general';
    }

    /**
     * Get test display name from category type
     */
    private function getTestDisplayName($categoryType)
    {
        $names = [
            'visual_sequence' => 'Deret Gambar',
            'basic_math' => 'Matematika Dasar',
            'synonym_antonym' => 'Penalaran Verbal',
            'kraeplin' => 'Kraeplin',
            'field_test' => 'Test Bidang', 
            'epps_test' => 'EPPS'
        ];
        
        return $names[$categoryType] ?? 'Test Standar';
    }

    /**
     * Generate recommendation based on results
     */
    private function generateRecommendation($overallScore, $strengths, $weaknesses, $notes = [])
    {
        $recommendation = [];
        
        // Basic recommendation based on overall score
        if ($overallScore >= 85) {
            $recommendation[] = 'Kandidat sangat berkualitas dengan performa unggul di berbagai aspek.';
        } elseif ($overallScore >= 75) {
            $recommendation[] = 'Kandidat berkualitas baik dengan potensi pengembangan.';
        } elseif ($overallScore >= 65) {
            $recommendation[] = 'Kandidat cukup baik, memerlukan pelatihan pada beberapa aspek.';
        } else {
            $recommendation[] = 'Kandidat memerlukan pengembangan intensif sebelum penempatan.';
        }
        
        // Add field-specific recommendations
        if (isset($notes['field_recommendations'])) {
            $field = $notes['field_recommendations'];
            if (isset($field['division_recommendation']['primary'])) {
                $recommendation[] = "Cocok untuk: " . $field['division_recommendation']['primary'];
            }
        }
        
        // Add personality-based recommendations
        if (isset($notes['personality_insights']['recruitment_recommendation'])) {
            $recommendation = array_merge($recommendation, 
                array_slice($notes['personality_insights']['recruitment_recommendation'], 0, 2));
        }
        
        // Add strengths and weaknesses summary
        if (!empty($strengths)) {
            $strengthAreas = array_column($strengths, 'category');
            $recommendation[] = "Kekuatan utama: " . implode(', ', array_slice($strengthAreas, 0, 3));
        }
        
        if (!empty($weaknesses)) {
            $weakAreas = array_column($weaknesses, 'category');
            $recommendation[] = "Area yang perlu dikembangkan: " . implode(', ', array_slice($weakAreas, 0, 2));
        }
        
        return implode(' ', $recommendation);
    }

    /**
     * Get result statistics for dashboard
     */
    private function getResultStatistics()
    {
        $user = Auth::user();
        $query = PsychotestSchedule::query();
        
        if ($user->type != 'admin' && $user->type != 'company') {
            $query->where('created_by', Auth::user()->creatorId());
        }

        $totalTests = $query->count();
        $completedTests = $query->where('status', 'completed')->count();
        $inProgressTests = $query->where('status', 'in_progress')->count();
        $averageScore = PsychotestResult::whereHas('schedule', function($q) use ($user) {
            if ($user->type != 'admin' && $user->type != 'company') {
                $q->where('created_by', Auth::user()->creatorId());
            }
        })->avg('percentage');

        return [
            'total_tests' => $totalTests,
            'completed_tests' => $completedTests,
            'in_progress_tests' => $inProgressTests,
            'completion_rate' => $totalTests > 0 ? round(($completedTests / $totalTests) * 100, 1) : 0,
            'average_score' => round($averageScore, 1),
        ];
    }

    /**
     * Get total test time
     */
    private function getTotalTestTime($schedule)
    {
        $totalSeconds = $schedule->sessions()->sum('time_spent_seconds');
        return $this->formatTime($totalSeconds);
    }

    /**
     * Format time in seconds to readable format
     */
    private function formatTime($seconds)
    {
        if ($seconds < 60) {
            return $seconds . ' detik';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . ' menit ' . $remainingSeconds . ' detik';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . ' jam ' . $minutes . ' menit';
        }
    }

    /**
     * Export to PDF
     */
    private function exportToPDF($schedule, $sessionResults, $performanceMetrics)
    {
        // Implementation for PDF export
        // You can use libraries like DomPDF or TCPDF
        
        return response()->json([
            'message' => 'PDF export feature will be implemented with DomPDF library',
            'data' => [
                'candidate' => $schedule->candidates->name,
                'overall_score' => $performanceMetrics['overall_score'] ?? 0,
                'grade' => $performanceMetrics['grade'] ?? 'N/A'
            ]
        ]);
    }

    /**
     * Export to Excel
     */
    private function exportToExcel($schedule, $sessionResults, $performanceMetrics)
    {
        // Implementation for Excel export
        // You can use libraries like PhpSpreadsheet
        
        return response()->json([
            'message' => 'Excel export feature will be implemented with PhpSpreadsheet library',
            'data' => [
                'candidate' => $schedule->candidates->name,
                'sessions' => count($sessionResults),
                'overall_score' => $performanceMetrics['overall_score'] ?? 0
            ]
        ]);
    }

    /**
     * Generate comparison between multiple candidates
     */
    private function generateComparison($schedules)
    {
        $comparison = [];
        
        foreach ($schedules as $schedule) {
            $sessions = $this->getDetailedSessionResults($schedule);
            
            $comparison[] = [
                'candidate' => $schedule->candidates,
                'overall_score' => $schedule->result->percentage ?? 0,
                'grade' => $schedule->result->grade ?? 'N/A',
                'sessions' => $sessions,
                'total_time' => $this->getTotalTestTime($schedule),
                'completed_categories' => $schedule->sessions()->where('status', 'completed')->count(),
                'strengths' => $this->getComparisonStrengths($sessions),
                'recommended_division' => $this->getRecommendedDivision($schedule)
            ];
        }

        return $comparison;
    }

    /**
     * Get strengths for comparison
     */
    private function getComparisonStrengths($sessions)
    {
        $strengths = [];
        
        foreach ($sessions as $session) {
            if ($session['percentage'] >= 80) {
                $strengths[] = [
                    'category' => $session['category']->name,
                    'score' => $session['percentage']
                ];
            }
        }
        
        // Sort by score descending
        usort($strengths, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_slice($strengths, 0, 3); // Top 3 strengths
    }

    /**
     * Get recommended division from test results
     */
    private function getRecommendedDivision($schedule)
    {
        $result = $schedule->result;
        if (!$result) return 'Belum Tersedia';
        
        $notes = json_decode($result->notes, true) ?? [];
        
        if (isset($notes['field_recommendations']['division_recommendation']['primary'])) {
            return $notes['field_recommendations']['division_recommendation']['primary'];
        }
        
        return 'General';
    }

    private function getKraeplinAnswers($session)
    {
        $sessionData = $session->session_data ?? [];
        $kraeplinData = $sessionData['kraeplin'] ?? [];
        
        // Debug: Log data yang tersimpan
        \Log::info('Kraeplin session data:', $sessionData);
        
        if (empty($kraeplinData)) {
            // Jika tidak ada data kraeplin, coba ambil dari answers table
            $answers = $session->answers()
                ->with('question')
                ->where('answer', '!=', null)
                ->get();
                
            if ($answers->isEmpty()) {
                return collect([]);
            }
            
            // Convert answers biasa ke format kraeplin
            return $answers->map(function($answer, $index) {
                return [
                    'question' => (object)[
                        'id' => $answer->question_id,
                        'title' => "Kraeplin Calculation " . ($index + 1),
                        'question' => "Perhitungan Kraeplin",
                        'type' => 'kraeplin',
                        'points' => 1,
                        'correct_answer' => $answer->question->correct_answer ?? null
                    ],
                    'user_answer' => $answer->answer,
                    'correct_answer' => $answer->question->correct_answer ?? null,
                    'points_earned' => $answer->points_earned,
                    'time_taken' => $answer->time_taken_seconds,
                    'answered_at' => $answer->answered_at,
                    'category_type' => 'kraeplin',
                    'is_correct' => $answer->points_earned > 0,
                    'analysis' => $answer->points_earned > 0 ? 'Perhitungan benar' : 'Perhitungan salah',
                    'column_number' => intval($index / 30) + 1,
                    'calculation_number' => ($index % 30) + 1
                ];
            });
        }
        
        // Jika ada data kraeplin, proses seperti biasa
        $answers = collect([]);
        $allAnswers = $kraeplinData['all_answers'] ?? [];
        
        foreach ($allAnswers as $columnIndex => $columnData) {
            if (empty($columnData) || !is_array($columnData)) continue;
            
            // Proses setiap perhitungan dalam kolom
            for ($i = 0; $i < count($columnData) - 2; $i += 3) {
                if (!isset($columnData[$i]) || !isset($columnData[$i + 1]) || !isset($columnData[$i + 2])) {
                    continue;
                }
                
                $num1 = intval($columnData[$i]);
                $num2 = intval($columnData[$i + 1]);
                $userAnswer = intval($columnData[$i + 2]);
                $correctAnswer = $num1 + $num2;
                
                $answers->push([
                    'question' => (object)[
                        'id' => 'kraeplin_' . $columnIndex . '_' . $i,
                        'title' => "Kolom " . ($columnIndex + 1) . " - Perhitungan " . (intval($i/3) + 1),
                        'question' => "{$num1} + {$num2} = ?",
                        'type' => 'kraeplin',
                        'points' => 1,
                        'options' => null,
                        'correct_answer' => $correctAnswer
                    ],
                    'user_answer' => $userAnswer,
                    'correct_answer' => $correctAnswer,
                    'points_earned' => $userAnswer === $correctAnswer ? 1 : 0,
                    'time_taken' => null,
                    'answered_at' => $session->started_at,
                    'category_type' => 'kraeplin',
                    'is_correct' => $userAnswer === $correctAnswer,
                    'analysis' => $userAnswer === $correctAnswer ? 'Perhitungan benar' : 'Perhitungan salah',
                    'column_number' => $columnIndex + 1,
                    'calculation_number' => intval($i/3) + 1
                ]);
            }
        }
        
        return $answers;
    }

    // 2. Update method calculateKraeplinResults dengan fallback

    private function calculateKraeplinResults($session)
    {
        $sessionData = $session->session_data ?? [];
        $kraeplinData = $sessionData['kraeplin'] ?? [];
        
        // Jika tidak ada data kraeplin, coba ambil dari answers
        if (empty($kraeplinData)) {
            $this->calculateKraeplinFromAnswers($session);
            return;
        }
        
        $statistics = $kraeplinData['statistics'] ?? [];
        $allAnswers = $kraeplinData['all_answers'] ?? [];

        // Jika statistics kosong, hitung ulang dari all_answers
        if (empty($statistics) && !empty($allAnswers)) {
            $statistics = $this->recalculateKraeplinStatistics($allAnswers);
        }

        // Analisis data Kraeplin
        $analysis = $this->analyzeKraeplinData($allAnswers, $statistics);
        
        // Hitung skor berdasarkan standar Kraeplin
        $kraeplinScore = $this->calculateKraeplinStandardScore($analysis);
        
        // Tentukan grade berdasarkan standar psikologi
        $grade = $this->getKraeplinGrade($kraeplinScore, $analysis);

        $sessionData['results'] = [
            'total_questions' => $analysis['total_calculations'],
            'answered_questions' => $analysis['total_answers'], 
            'total_points' => $analysis['total_calculations'],
            'earned_points' => $analysis['correct_answers'],
            'percentage' => round($kraeplinScore, 2),
            'accuracy' => round($analysis['accuracy_rate'], 2),
            'speed_score' => round($analysis['speed_score'], 2),
            'consistency_score' => round($analysis['consistency_score'], 2),
            'concentration_score' => round($analysis['concentration_score'], 2),
            'kraeplin_score' => round($kraeplinScore, 2),
            'kraeplin_grade' => $grade,
            'completed_columns' => $analysis['completed_columns'],
            'column_scores' => $analysis['column_details'],
            'interpretation' => $this->getKraeplinInterpretation($kraeplinScore, $analysis),
            'recommendation' => $this->getKraeplinRecommendation($grade, $analysis),
            'detailed_analysis' => $analysis
        ];

        $session->update(['session_data' => $sessionData]);
    }

    // 3. Method fallback untuk calculate dari answers table

    private function calculateKraeplinFromAnswers($session)
    {
        $answers = $session->answers()->get();
        
        if ($answers->isEmpty()) {
            $this->createDefaultKraeplinResult($session);
            return;
        }
        
        $totalAnswers = $answers->count();
        $correctAnswers = $answers->where('points_earned', '>', 0)->count();
        $accuracy = $totalAnswers > 0 ? ($correctAnswers / $totalAnswers) * 100 : 0;
        
        // Simulasi kolom berdasarkan jumlah jawaban (30 soal per kolom)
        $completedColumns = intval($totalAnswers / 30) + ($totalAnswers % 30 > 0 ? 1 : 0);
        
        // Hitung metrik dasar
        $speedScore = min(100, ($totalAnswers / ($completedColumns * 30)) * 100);
        $consistencyScore = 75; // Default untuk consistency
        $concentrationScore = $accuracy > 70 ? 80 : 60; // Estimasi berdasarkan akurasi
        
        $kraeplinScore = ($accuracy * 0.25) + ($speedScore * 0.35) + 
                        ($consistencyScore * 0.25) + ($concentrationScore * 0.15);
        
        $grade = $this->getKraeplinGradeFromScore($kraeplinScore);
        
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
            'column_scores' => [],
            'interpretation' => $this->getKraeplinInterpretationFromScore($kraeplinScore, $accuracy, $speedScore),
            'recommendation' => $this->getKraeplinRecommendationFromGrade($grade)
        ];

        $session->update(['session_data' => $sessionData]);
    }

    // 4. Helper methods

    private function recalculateKraeplinStatistics($allAnswers)
    {
        $totalAnswers = 0;
        $correctAnswers = 0;
        $completedColumns = count($allAnswers);
        $columnScores = [];
        
        foreach ($allAnswers as $columnIndex => $columnData) {
            if (empty($columnData) || !is_array($columnData)) continue;
            
            $columnCorrect = 0;
            $columnTotal = 0;
            
            for ($i = 0; $i < count($columnData) - 2; $i += 3) {
                if (!isset($columnData[$i]) || !isset($columnData[$i + 1]) || !isset($columnData[$i + 2])) {
                    continue;
                }
                
                $num1 = intval($columnData[$i]);
                $num2 = intval($columnData[$i + 1]);
                $userAnswer = intval($columnData[$i + 2]);
                $correctAnswer = $num1 + $num2;
                
                $totalAnswers++;
                $columnTotal++;
                
                if ($userAnswer === $correctAnswer) {
                    $correctAnswers++;
                    $columnCorrect++;
                }
            }
            
            $columnScores[] = [
                'column' => $columnIndex + 1,
                'total' => $columnTotal,
                'correct' => $columnCorrect,
                'accuracy' => $columnTotal > 0 ? ($columnCorrect / $columnTotal) * 100 : 0
            ];
        }
        
        return [
            'total_answers' => $totalAnswers,
            'correct_answers' => $correctAnswers,
            'completed_columns' => $completedColumns,
            'column_scores' => $columnScores
        ];
    }

    private function getKraeplinGradeFromScore($score)
    {
        if ($score >= 85) return 'A';
        if ($score >= 75) return 'B';
        if ($score >= 65) return 'C';
        if ($score >= 55) return 'D';
        if ($score >= 45) return 'E';
        return 'F';
    }

    private function getKraeplinInterpretationFromScore($score, $accuracy, $speed)
    {
        $interpretations = [];
        
        if ($score >= 80) {
            $interpretations[] = "Performa Kraeplin sangat baik - menunjukkan konsentrasi dan kecepatan tinggi";
        } elseif ($score >= 70) {
            $interpretations[] = "Performa Kraeplin baik - kemampuan di atas rata-rata";
        } elseif ($score >= 60) {
            $interpretations[] = "Performa Kraeplin cukup - sesuai standar normal";
        } else {
            $interpretations[] = "Performa Kraeplin perlu ditingkatkan";
        }
        
        if ($accuracy >= 80) {
            $interpretations[] = "Tingkat akurasi sangat baik - teliti dalam perhitungan";
        } elseif ($accuracy >= 70) {
            $interpretations[] = "Tingkat akurasi baik";
        } else {
            $interpretations[] = "Perlu peningkatan ketelitian dalam perhitungan";
        }
        
        if ($speed >= 80) {
            $interpretations[] = "Kecepatan kerja sangat baik";
        } elseif ($speed >= 70) {
            $interpretations[] = "Kecepatan kerja baik";
        } else {
            $interpretations[] = "Perlu peningkatan kecepatan kerja";
        }
        
        return $interpretations;
    }

    private function getKraeplinRecommendationFromGrade($grade)
    {
        $recommendations = [];
        
        switch ($grade) {
            case 'A':
                $recommendations[] = "Sangat cocok untuk pekerjaan yang membutuhkan konsentrasi tinggi";
                $recommendations[] = "Dapat diandalkan untuk tugas dengan deadline ketat";
                break;
            case 'B':
                $recommendations[] = "Cocok untuk pekerjaan administratif dan analisis";
                $recommendations[] = "Dapat berkembang dengan training tambahan";
                break;
            case 'C':
                $recommendations[] = "Perlu pelatihan untuk meningkatkan konsentrasi";
                $recommendations[] = "Cocok untuk pekerjaan dengan supervisi";
                break;
            default:
                $recommendations[] = "Perlu training intensif sebelum penempatan";
                $recommendations[] = "Fokus pada peningkatan konsentrasi dan kecepatan";
                break;
        }
        
        return $recommendations;
    }

    private function calculateWeightedCategoryScore($categoryType, $score, $accuracy, $completionRate)
    {
        $weights = [
            'visual_sequence' => ['difficulty' => 1.0, 'time_weight' => 0.3],
            'basic_math' => ['difficulty' => 1.2, 'time_weight' => 0.4],
            'synonym_antonym' => ['difficulty' => 1.1, 'time_weight' => 0.3],
            'kraeplin' => ['difficulty' => 1.5, 'time_weight' => 0.5],
            'field_test' => ['difficulty' => 1.8, 'time_weight' => 0.6],
            'epps_test' => ['difficulty' => 1.0, 'time_weight' => 0.0], // No time pressure
        ];
        
        $categoryWeight = $weights[$categoryType] ?? ['difficulty' => 1.0, 'time_weight' => 0.3];
        
        // Base score calculation
        $baseScore = $score;
        
        // Apply completion penalty if not fully completed
        if ($completionRate < 100) {
            $baseScore *= ($completionRate / 100);
        }
        
        // Apply difficulty multiplier
        $weightedScore = $baseScore * $categoryWeight['difficulty'];
        
        // Normalize to 0-100 scale
        return min(100, $weightedScore);
    }

    /**
     * Calculate field test score with proper 5-point system
     */
    private function calculateFieldTestScore($answers)
    {
        $scores = [
            'audit' => ['correct' => 0, 'total' => 0],
            'accounting' => ['correct' => 0, 'total' => 0],
            'tax' => ['correct' => 0, 'total' => 0]
        ];
        
        $totalPoints = 0;
        $earnedPoints = 0;
        
        foreach ($answers as $answer) {
            $questionOrder = $answer->question->order ?? 0;
            $field = $this->getFieldFromOrder($questionOrder);
            
            // Each field question worth 5 points
            $questionPoints = 5;
            $totalPoints += $questionPoints;
            
            if ($answer->answer === $answer->question->correct_answer) {
                $scores[$field]['correct']++;
                $earnedPoints += $questionPoints;
            }
            
            $scores[$field]['total']++;
        }
        
        return [
            'field_scores' => $scores,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'percentage' => $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0,
            'field_percentages' => [
                'audit' => $scores['audit']['total'] > 0 ? ($scores['audit']['correct'] / $scores['audit']['total']) * 100 : 0,
                'accounting' => $scores['accounting']['total'] > 0 ? ($scores['accounting']['correct'] / $scores['accounting']['total']) * 100 : 0,
                'tax' => $scores['tax']['total'] > 0 ? ($scores['tax']['correct'] / $scores['tax']['total']) * 100 : 0,
            ]
        ];
    }

    /**
     * Get field from question order
     */
    private function getFieldFromOrder($order)
    {
        if ($order <= 10) return 'audit';
        if ($order <= 20) return 'accounting';
        return 'tax';
    }

    /**
     * Calculate EPPS personality score (no right/wrong answers)
     */
    private function calculateEPPSScore($answers)
    {
        $dimensions = [
            'achievement', 'deference', 'order', 'exhibition', 'autonomy',
            'affiliation', 'intraception', 'succorance', 'dominance',
            'abasement', 'nurturance', 'change', 'endurance', 'heterosexuality', 'aggression'
        ];
        
        $dimensionScores = [];
        $totalAnswers = $answers->count();
        
        foreach ($dimensions as $dimension) {
            $dimensionScores[$dimension] = 0;
        }
        
        // Process forced-choice answers
        foreach ($answers as $answer) {
            $question = $answer->question;
            if ($question->personality_dimension && isset($dimensionScores[$question->personality_dimension])) {
                // EPPS uses forced choice - each selection adds to dimension score
                if ($answer->answer === $question->id) { // User chose this statement
                    $dimensionScores[$question->personality_dimension]++;
                }
            }
        }
        
        // Convert to percentiles (simplified version)
        $maxPossiblePerDimension = max(1, $totalAnswers / count($dimensions));
        foreach ($dimensionScores as $dimension => $score) {
            $dimensionScores[$dimension] = min(100, ($score / $maxPossiblePerDimension) * 100);
        }
        
        // Find dominant traits (top 5)
        arsort($dimensionScores);
        $dominantTraits = array_slice(array_keys($dimensionScores), 0, 5);
        
        return [
            'dimension_scores' => $dimensionScores,
            'dominant_traits' => $dominantTraits,
            'completion_rate' => 100, // EPPS completion is always 100% if finished
            'total_answered' => $totalAnswers,
            'personality_profile' => $this->generatePersonalityProfile($dominantTraits, $dimensionScores)
        ];
    }

    /**
     * Generate personality profile from EPPS results
     */
    private function generatePersonalityProfile($dominantTraits, $dimensionScores)
    {
        $profiles = [
            'achievement' => 'Berorientasi pada pencapaian dan kesuksesan',
            'deference' => 'Cenderung menghormati otoritas dan mengikuti aturan',
            'order' => 'Menyukai keteraturan dan sistematis',
            'exhibition' => 'Senang menjadi pusat perhatian',
            'autonomy' => 'Menghargai kemandirian dan kebebasan',
            'affiliation' => 'Berorientasi pada hubungan sosial',
            'intraception' => 'Memiliki kemampuan analisis psikologis',
            'succorance' => 'Mencari dukungan dan bantuan orang lain',
            'dominance' => 'Memiliki potensi kepemimpinan',
            'abasement' => 'Cenderung merendahkan diri',
            'nurturance' => 'Senang membantu dan merawat orang lain',
            'change' => 'Menyukai variasi dan perubahan',
            'endurance' => 'Memiliki daya tahan dan ketekunan tinggi',
            'heterosexuality' => 'Tertarik pada lawan jenis',
            'aggression' => 'Memiliki sifat kompetitif dan asertif'
        ];
        
        $profile = [];
        foreach ($dominantTraits as $trait) {
            if (isset($profiles[$trait]) && $dimensionScores[$trait] > 50) {
                $profile[] = $profiles[$trait];
            }
        }
        
        return $profile;
    }

    /**
     * Calculate improved Kraeplin score
     */
    private function calculateImprovedKraeplinScore($sessionData)
    {
        $statistics = $sessionData['statistics'] ?? [];
        $allAnswers = $sessionData['all_answers'] ?? [];
        
        if (empty($statistics) && !empty($allAnswers)) {
            $statistics = $this->recalculateKraeplinStatistics($allAnswers);
        }
        
        $totalAnswers = $statistics['total_answers'] ?? 0;
        $correctAnswers = $statistics['correct_answers'] ?? 0;
        $completedColumns = $statistics['completed_columns'] ?? 0;
        
        // Accuracy component (40%)
        $accuracy = $totalAnswers > 0 ? ($correctAnswers / $totalAnswers) * 100 : 0;
        $accuracyScore = $accuracy * 0.4;
        
        // Speed component (35%) - based on questions per minute
        $timeSpent = 600; // 10 minutes default for Kraeplin
        $questionsPerMinute = $timeSpent > 0 ? ($totalAnswers / ($timeSpent / 60)) : 0;
        $optimalQuestionsPerMinute = 30; // Expected rate
        $speedScore = min(100, ($questionsPerMinute / $optimalQuestionsPerMinute) * 100) * 0.35;
        
        // Consistency component (25%) - variation between columns
        $consistencyScore = $this->calculateKraeplinConsistency($allAnswers) * 0.25;
        
        $totalScore = $accuracyScore + $speedScore + $consistencyScore;
        
        return [
            'kraeplin_score' => round($totalScore, 2),
            'accuracy' => round($accuracy, 2),
            'speed_score' => round(($speedScore / 0.35), 2),
            'consistency_score' => round(($consistencyScore / 0.25), 2),
            'grade' => $this->getKraeplinGrade($totalScore),
            'completed_columns' => $completedColumns
        ];
    }

    /**
     * Calculate Kraeplin consistency score
     */
    private function calculateKraeplinConsistency($allAnswers)
    {
        if (empty($allAnswers) || count($allAnswers) < 2) {
            return 75; // Default consistency if insufficient data
        }
        
        $columnAccuracies = [];
        
        foreach ($allAnswers as $columnIndex => $columnData) {
            if (empty($columnData) || !is_array($columnData)) continue;
            
            $columnCorrect = 0;
            $columnTotal = 0;
            
            for ($i = 0; $i < count($columnData) - 2; $i += 3) {
                if (!isset($columnData[$i]) || !isset($columnData[$i + 1]) || !isset($columnData[$i + 2])) {
                    continue;
                }
                
                $num1 = intval($columnData[$i]);
                $num2 = intval($columnData[$i + 1]);
                $userAnswer = intval($columnData[$i + 2]);
                $correctAnswer = $num1 + $num2;
                
                $columnTotal++;
                if ($userAnswer === $correctAnswer) {
                    $columnCorrect++;
                }
            }
            
            if ($columnTotal > 0) {
                $columnAccuracies[] = ($columnCorrect / $columnTotal) * 100;
            }
        }
        
        if (empty($columnAccuracies)) {
            return 75;
        }
        
        // Calculate standard deviation to measure consistency
        $mean = array_sum($columnAccuracies) / count($columnAccuracies);
        $variance = 0;
        
        foreach ($columnAccuracies as $accuracy) {
            $variance += pow($accuracy - $mean, 2);
        }
        
        $stdDev = sqrt($variance / count($columnAccuracies));
        
        // Convert to consistency score (lower std dev = higher consistency)
        $consistencyScore = max(0, 100 - ($stdDev * 2));
        
        return $consistencyScore;
    }

    /**
     * Calculate overall weighted score
     */
    private function calculateOverallWeightedScore($sessionResults)
    {
        $totalWeightedScore = 0;
        $totalWeight = 0;
        
        // Category weights based on importance and difficulty
        $categoryWeights = [
            'visual_sequence' => 1.0,
            'basic_math' => 1.2,
            'synonym_antonym' => 1.1,
            'kraeplin' => 1.5,
            'field_test' => 2.0,  // Highest weight for job-specific skills
            'epps_test' => 0.8,   // Lower weight for personality (different scoring)
        ];
        
        foreach ($sessionResults as $result) {
            $categoryType = $result['category_type'];
            $categoryWeight = $categoryWeights[$categoryType] ?? 1.0;
            
            $score = $result['percentage'];
            
            // Special handling for EPPS (use completion rate instead of accuracy)
            if ($categoryType === 'epps_test') {
                $score = 85; // Fixed score for completed EPPS test
            }
            
            $weightedScore = $score * $categoryWeight;
            $totalWeightedScore += $weightedScore;
            $totalWeight += $categoryWeight;
        }
        
        return $totalWeight > 0 ? $totalWeightedScore / $totalWeight : 0;
    }

    private function calculateGradeFromScore($score)
    {
        if ($score >= 85) return 'A';
        if ($score >= 75) return 'B';
        if ($score >= 65) return 'C';
        if ($score >= 55) return 'D';
        if ($score >= 45) return 'E';
        return 'F';
    }

    private function generateImprovedInsights($sessionResults, $notes)
    {
        $insights = [];
        
        foreach ($sessionResults as $result) {
            $categoryType = $result['category_type'];
            $percentage = $result['percentage'];
            
            switch ($categoryType) {
                case 'field_test':
                    if (isset($result['field_analysis']['field_percentages'])) {
                        $fieldPercentages = $result['field_analysis']['field_percentages'];
                        $bestField = array_keys($fieldPercentages, max($fieldPercentages))[0];
                        $insights[] = "Bidang terkuat: " . ucfirst($bestField) . " (" . $fieldPercentages[$bestField] . "%)";
                    }
                    break;
                    
                case 'kraeplin':
                    if (isset($result['kraeplin_analysis']['accuracy'])) {
                        $accuracy = $result['kraeplin_analysis']['accuracy'];
                        $speed = $result['kraeplin_analysis']['speed_score'];
                        $insights[] = "Kraeplin: Akurasi {$accuracy}%, Kecepatan {$speed}%";
                    }
                    break;
                    
                case 'epps_test':
                    if (isset($result['epps_analysis']['dominant_traits'])) {
                        $traits = array_slice($result['epps_analysis']['dominant_traits'], 0, 3);
                        $insights[] = "Trait dominan: " . implode(', ', $traits);
                    }
                    break;
            }
        }
        
        return $insights;
    }

    private function generateImprovedRecommendation($overallScore, $strengths, $weaknesses, $sessionResults)
    {
        $recommendation = [];
        
        // Overall assessment
        if ($overallScore >= 80) {
            $recommendation[] = 'Kandidat sangat berkualitas dengan kemampuan di atas rata-rata.';
        } elseif ($overallScore >= 70) {
            $recommendation[] = 'Kandidat berkualitas baik dengan potensi yang dapat dikembangkan.';
        } elseif ($overallScore >= 60) {
            $recommendation[] = 'Kandidat cukup memenuhi syarat dengan beberapa area yang perlu diperkuat.';
        } else {
            $recommendation[] = 'Kandidat memerlukan pengembangan intensif sebelum dapat ditempatkan.';
        }
        
        // Field-specific recommendations
        foreach ($sessionResults as $result) {
            if ($result['category_type'] === 'field_test' && isset($result['field_analysis']['recommended_field'])) {
                $recommendedField = $result['field_analysis']['recommended_field'];
                $recommendation[] = "Paling cocok untuk divisi: " . ucfirst($recommendedField);
                break;
            }
        }
        
        // Strength-based recommendations
        if (!empty($strengths)) {
            $topStrengths = array_slice($strengths, 0, 2);
            $strengthAreas = array_column($topStrengths, 'category');
            $recommendation[] = "Keunggulan utama pada: " . implode(' dan ', $strengthAreas);
        }
        
        return implode(' ', $recommendation);
    }

    /**
     * Generate detailed decision and recommendation
     */
    private function generateDetailedDecisionRecommendation($overallScore, $strengths, $weaknesses, $sessionResults, $schedule)
    {
        $decision = $this->makeHiringDecision($overallScore, $strengths, $weaknesses, $sessionResults);
        $detailedRecommendation = $this->generateDetailedRecommendation($overallScore, $strengths, $weaknesses, $sessionResults, $decision, $schedule);
        
        return [
            'decision' => $decision,
            'detailed_recommendation' => $detailedRecommendation,
            'priority_level' => $this->getPriorityLevel($decision['status']),
            'next_steps' => $this->getNextSteps($decision['status'], $weaknesses),
            'risk_assessment' => $this->getRiskAssessment($overallScore, $weaknesses),
            'development_plan' => $this->getDevelopmentPlan($weaknesses)
        ];
    }

    /**
     * Make hiring decision based on comprehensive analysis
     */
    private function makeHiringDecision($overallScore, $strengths, $weaknesses, $sessionResults)
    {
        $decision = [
            'status' => '',
            'confidence' => 0,
            'reasoning' => [],
            'conditions' => []
        ];
        
        // Check critical failures
        $criticalFailures = $this->checkCriticalFailures($sessionResults);
        
        if (!empty($criticalFailures)) {
            $decision['status'] = 'TIDAK LOLOS';
            $decision['confidence'] = 95;
            $decision['reasoning'] = array_merge(['Gagal dalam aspek kritis:'], $criticalFailures);
            return $decision;
        }
        
        // Evaluate based on overall score and specific criteria
        if ($overallScore >= 85) {
            $decision['status'] = 'SANGAT DIREKOMENDASIKAN';
            $decision['confidence'] = 95;
            $decision['reasoning'] = [
                'Skor keseluruhan sangat tinggi (' . $overallScore . '%)',
                'Menunjukkan kompetensi unggul di berbagai aspek'
            ];
        } elseif ($overallScore >= 75) {
            $decision['status'] = 'DIREKOMENDASIKAN';
            $decision['confidence'] = 85;
            $decision['reasoning'] = [
                'Skor keseluruhan baik (' . $overallScore . '%)',
                'Memiliki potensi yang solid untuk dikembangkan'
            ];
            
            if (count($weaknesses) > 0) {
                $decision['conditions'][] = 'Memerlukan pelatihan pada area yang lemah';
            }
        } elseif ($overallScore >= 65) {
            $decision['status'] = 'PERLU PERTIMBANGAN';
            $decision['confidence'] = 70;
            $decision['reasoning'] = [
                'Skor keseluruhan cukup (' . $overallScore . '%)',
                'Ada potensi tapi memerlukan evaluasi lebih lanjut'
            ];
            $decision['conditions'] = [
                'Interview mendalam untuk menilai motivasi',
                'Rencana pengembangan intensif perlu disiapkan'
            ];
        } elseif ($overallScore >= 55) {
            $decision['status'] = 'KURANG DIREKOMENDASIKAN';
            $decision['confidence'] = 80;
            $decision['reasoning'] = [
                'Skor keseluruhan di bawah standar (' . $overallScore . '%)',
                'Banyak area yang memerlukan perbaikan signifikan'
            ];
            $decision['conditions'] = [
                'Hanya dipertimbangkan jika kandidat langka',
                'Memerlukan program training ekstensif'
            ];
        } else {
            $decision['status'] = 'TIDAK DIREKOMENDASIKAN';
            $decision['confidence'] = 90;
            $decision['reasoning'] = [
                'Skor keseluruhan sangat rendah (' . $overallScore . '%)',
                'Tidak memenuhi standar minimum kompetensi'
            ];
        }
        
        // Additional evaluation based on field test performance
        $fieldTestResult = $this->getFieldTestResult($sessionResults);
        if ($fieldTestResult) {
            $decision = $this->adjustDecisionBasedOnFieldTest($decision, $fieldTestResult);
        }
        
        return $decision;
    }

    /**
     * Check for critical failures that would disqualify candidate
     */
    private function checkCriticalFailures($sessionResults)
    {
        $failures = [];
        
        foreach ($sessionResults as $result) {
            $categoryType = $result['category_type'];
            $percentage = $result['percentage'];
            
            // Critical thresholds
            switch ($categoryType) {
                case 'field_test':
                    if ($percentage < 40) {
                        $failures[] = 'Skor tes bidang terlalu rendah (< 40%)';
                    }
                    break;
                    
                case 'basic_math':
                    if ($percentage < 35) {
                        $failures[] = 'Kemampuan matematika dasar sangat kurang (< 35%)';
                    }
                    break;
                    
                case 'kraeplin':
                    if (isset($result['kraeplin_analysis']['accuracy']) && $result['kraeplin_analysis']['accuracy'] < 30) {
                        $failures[] = 'Akurasi Kraeplin sangat rendah (< 30%)';
                    }
                    break;
            }
        }
        
        return $failures;
    }

    /**
     * Get category status based on score and type
     */
    private function getCategoryStatus($percentage, $categoryType)
    {
        // Different thresholds for different category types
        $thresholds = [
            'kraeplin' => ['excellent' => 80, 'good' => 65, 'fair' => 50, 'poor' => 35],
            'field_test' => ['excellent' => 85, 'good' => 70, 'fair' => 55, 'poor' => 40],
            'epps_test' => ['excellent' => 90, 'good' => 75, 'fair' => 60, 'poor' => 45],
            'default' => ['excellent' => 85, 'good' => 70, 'fair' => 55, 'poor' => 40]
        ];
        
        $threshold = $thresholds[$categoryType] ?? $thresholds['default'];
        
        if ($percentage >= $threshold['excellent']) return 'Sangat Baik';
        if ($percentage >= $threshold['good']) return 'Baik';
        if ($percentage >= $threshold['fair']) return 'Cukup';
        if ($percentage >= $threshold['poor']) return 'Kurang';
        return 'Sangat Kurang';
    }

    /**
     * Calculate risk level
     */
    private function calculateRiskLevel($overallScore, $weaknesses)
    {
        if ($overallScore >= 80 && count($weaknesses) <= 1) return 'RENDAH';
        if ($overallScore >= 65 && count($weaknesses) <= 3) return 'SEDANG';
        return 'TINGGI';
    }

    /**
     * Get field test result
     */
    private function getFieldTestResult($sessionResults)
    {
        foreach ($sessionResults as $result) {
            if ($result['category_type'] === 'field_test') {
                return $result;
            }
        }
        return null;
    }

    /**
     * Adjust decision based on field test performance
     */
    private function adjustDecisionBasedOnFieldTest($decision, $fieldTestResult)
    {
        $fieldScore = $fieldTestResult['percentage'];
        
        // If field test score is significantly higher/lower, adjust decision
        if ($fieldScore >= 85 && in_array($decision['status'], ['PERLU PERTIMBANGAN', 'KURANG DIREKOMENDASIKAN'])) {
            $decision['status'] = 'DIREKOMENDASIKAN';
            $decision['reasoning'][] = 'Skor tes bidang yang sangat baik mengkompensasi kelemahan lain';
            $decision['conditions'][] = 'Fokus pengembangan pada soft skills dan kemampuan umum';
        } elseif ($fieldScore < 50 && in_array($decision['status'], ['SANGAT DIREKOMENDASIKAN', 'DIREKOMENDASIKAN'])) {
            $decision['status'] = 'PERLU PERTIMBANGAN';
            $decision['reasoning'][] = 'Skor tes bidang yang rendah memerlukan perhatian khusus';
            $decision['conditions'][] = 'Training intensif untuk kompetensi teknis diperlukan';
        }
        
        return $decision;
    }

    /**
     * Get priority level based on decision status
     */
    private function getPriorityLevel($decisionStatus)
    {
        switch ($decisionStatus) {
            case 'SANGAT DIREKOMENDASIKAN':
                return 'URGENT';
            case 'DIREKOMENDASIKAN':
                return 'HIGH';
            case 'PERLU PERTIMBANGAN':
                return 'MEDIUM';
            case 'KURANG DIREKOMENDASIKAN':
                return 'LOW';
            case 'TIDAK DIREKOMENDASIKAN':
            case 'TIDAK LOLOS':
                return 'REJECTED';
            default:
                return 'MEDIUM';
        }
    }

    /**
     * Get next steps based on decision status
     */
    private function getNextSteps($decisionStatus, $weaknesses = [])
    {
        $steps = [];
        
        switch ($decisionStatus) {
            case 'SANGAT DIREKOMENDASIKAN':
                $steps = [
                    'Segera jadwalkan interview final dengan decision maker',
                    'Siapkan job offer dengan paket kompensasi kompetitif',
                    'Lakukan background check dan verifikasi dokumen',
                    'Pertimbangkan untuk fast-track process',
                    'Siapkan onboarding plan yang accelerated'
                ];
                break;
                
            case 'DIREKOMENDASIKAN':
                $steps = [
                    'Lakukan interview panel dengan tim langsung',
                    'Asesmen cultural fit dengan tim kerja',
                    'Negosiasi paket kompensasi',
                    'Reference check dari employer sebelumnya',
                    'Siapkan mentoring program untuk onboarding'
                ];
                break;
                
            case 'PERLU PERTIMBANGAN':
                $steps = [
                    'Interview mendalam dengan multiple stakeholder',
                    'Berikan case study atau practical test',
                    'Evaluasi motivasi dan commitment terhadap learning',
                    'Diskusikan expectation dan development plan',
                    'Pertimbangkan probation period yang extended',
                    'Asesmen tambahan untuk area weakness',
                    'Konsultasi dengan department head'
                ];
                break;
                
            case 'KURANG DIREKOMENDASIKAN':
                $steps = [
                    'Evaluasi ulang job requirement vs candidate profile',
                    'Pertimbangkan role alternative yang lebih sesuai',
                    'Diskusikan dengan hiring manager tentang flexibility',
                    'Jika tetap dipertimbangkan, siapkan intensive training plan',
                    'Set clear milestone dan KPI untuk probation period',
                    'Consider hanya jika candidate pool sangat terbatas'
                ];
                break;
                
            case 'TIDAK DIREKOMENDASIKAN':
            case 'TIDAK LOLOS':
                $steps = [
                    'Berikan feedback yang konstruktif kepada kandidat',
                    'Dokumentasikan hasil untuk database candidate',
                    'Update recruitment status menjadi rejected',
                    'Lanjutkan search untuk kandidat lain',
                    'Review kembali job posting jika banyak kandidat tidak lolos',
                    'Inform candidate dengan professional manner'
                ];
                break;
                
            default:
                $steps = [
                    'Review hasil assessment lebih detail',
                    'Konsultasi dengan tim recruitment',
                    'Tentukan langkah selanjutnya'
                ];
        }
        
        // Add specific steps based on weaknesses
        if (!empty($weaknesses) && in_array($decisionStatus, ['DIREKOMENDASIKAN', 'PERLU PERTIMBANGAN'])) {
            $weaknessTypes = array_column($weaknesses, 'type');
            
            if (in_array('field_test', $weaknessTypes)) {
                $steps[] = 'Siapkan technical training program untuk knowledge gap';
            }
            
            if (in_array('kraeplin', $weaknessTypes)) {
                $steps[] = 'Pertimbangkan role yang tidak memerlukan konsentrasi tinggi';
            }
            
            if (in_array('basic_math', $weaknessTypes)) {
                $steps[] = 'Evaluasi mathematical requirement untuk role tersebut';
            }
        }
        
        return $steps;
    }

    /**
     * Get comprehensive risk assessment
     */
    private function getRiskAssessment($overallScore, $weaknesses)
    {
        $riskFactors = [];
        $riskLevel = 'RENDAH';
        $riskScore = 0;
        
        // Score-based risk
        if ($overallScore < 50) {
            $riskFactors[] = 'Skor keseluruhan sangat rendah';
            $riskScore += 40;
        } elseif ($overallScore < 65) {
            $riskFactors[] = 'Skor keseluruhan di bawah standar optimal';
            $riskScore += 25;
        } elseif ($overallScore < 75) {
            $riskFactors[] = 'Skor keseluruhan cukup namun ada ruang improvement';
            $riskScore += 15;
        }
        
        // Weakness-based risk
        $criticalWeaknesses = 0;
        foreach ($weaknesses as $weakness) {
            if ($weakness['score'] < 40) {
                $criticalWeaknesses++;
                $riskFactors[] = "Sangat lemah dalam {$weakness['category']} ({$weakness['score']}%)";
                $riskScore += 20;
            } elseif ($weakness['score'] < 55) {
                $riskFactors[] = "Perlu improvement dalam {$weakness['category']} ({$weakness['score']}%)";
                $riskScore += 10;
            }
        }
        
        // Category-specific risks
        foreach ($weaknesses as $weakness) {
            switch ($weakness['type']) {
                case 'field_test':
                    $riskFactors[] = 'Risk tinggi pada kompetensi teknis job-specific';
                    $riskScore += 25;
                    break;
                case 'kraeplin':
                    $riskFactors[] = 'Potensi kesulitan dalam pekerjaan yang memerlukan konsentrasi tinggi';
                    $riskScore += 15;
                    break;
                case 'basic_math':
                    $riskFactors[] = 'Kemungkinan kesulitan dalam analisis numerik';
                    $riskScore += 10;
                    break;
            }
        }
        
        // Determine risk level
        if ($riskScore >= 60) {
            $riskLevel = 'TINGGI';
        } elseif ($riskScore >= 30) {
            $riskLevel = 'SEDANG';
        } else {
            $riskLevel = 'RENDAH';
        }
        
        return [
            'level' => $riskLevel,
            'score' => $riskScore,
            'factors' => $riskFactors,
            'mitigation' => $this->getRiskMitigation($riskLevel, $riskFactors)
        ];
    }

    /**
     * Get risk mitigation strategies
     */
    private function getRiskMitigation($riskLevel, $riskFactors)
    {
        $mitigation = [];
        
        switch ($riskLevel) {
            case 'TINGGI':
                $mitigation = [
                    'Extended probation period (6-12 bulan)',
                    'Intensive mentoring dan coaching program',
                    'Monthly performance review dan feedback',
                    'Specific training program untuk skill gap',
                    'Clear milestone dan improvement target',
                    'Consider alternative role yang lebih sesuai'
                ];
                break;
                
            case 'SEDANG':
                $mitigation = [
                    'Standard probation period dengan close monitoring',
                    'Regular mentoring session',
                    'Targeted training untuk area weakness',
                    'Quarterly performance review',
                    'Pair dengan senior team member untuk guidance'
                ];
                break;
                
            case 'RENDAH':
                $mitigation = [
                    'Standard onboarding process',
                    'Regular check-in selama 3 bulan pertama',
                    'Optional training sesuai kebutuhan development',
                    'Standard performance review cycle'
                ];
                break;
        }
        
        // Add specific mitigation based on risk factors
        foreach ($riskFactors as $factor) {
            if (str_contains($factor, 'field_test') || str_contains($factor, 'kompetensi teknis')) {
                $mitigation[] = 'Sediakan technical reference materials dan tools';
                $mitigation[] = 'Arrange shadowing dengan expert dalam bidang tersebut';
            }
            
            if (str_contains($factor, 'kraeplin') || str_contains($factor, 'konsentrasi')) {
                $mitigation[] = 'Berikan lingkungan kerja yang mendukung fokus';
                $mitigation[] = 'Avoid multitasking yang berlebihan di awal';
            }
            
            if (str_contains($factor, 'matematika') || str_contains($factor, 'numerik')) {
                $mitigation[] = 'Sediakan calculator dan spreadsheet template';
                $mitigation[] = 'Double-check system untuk perhitungan penting';
            }
        }
        
        return array_unique($mitigation);
    }

    /**
     * Generate comprehensive development plan
     */
    private function getDevelopmentPlan($weaknesses)
    {
        if (empty($weaknesses)) {
            return [
                'priority' => 'MAINTENANCE',
                'duration' => '3 bulan',
                'focus_areas' => ['Continuous improvement', 'Skill enhancement'],
                'training_modules' => ['Advanced skills sesuai role'],
                'timeline' => [
                    'Month 1-3' => 'Focus pada excellence dan leadership development'
                ]
            ];
        }
        
        $developmentPlan = [
            'priority' => 'HIGH',
            'duration' => '6-12 bulan',
            'focus_areas' => [],
            'training_modules' => [],
            'timeline' => [],
            'success_metrics' => [],
            'resources_needed' => []
        ];
        
        // Analyze weaknesses and create specific development plan
        $criticalWeaknesses = array_filter($weaknesses, function($w) { return $w['score'] < 40; });
        $moderateWeaknesses = array_filter($weaknesses, function($w) { return $w['score'] >= 40 && $w['score'] < 60; });
        
        if (!empty($criticalWeaknesses)) {
            $developmentPlan['priority'] = 'CRITICAL';
            $developmentPlan['duration'] = '12-18 bulan';
        } elseif (!empty($moderateWeaknesses)) {
            $developmentPlan['priority'] = 'HIGH';
            $developmentPlan['duration'] = '6-9 bulan';
        } else {
            $developmentPlan['priority'] = 'MEDIUM';
            $developmentPlan['duration'] = '3-6 bulan';
        }
        
        // Create focus areas and training modules based on weakness types
        foreach ($weaknesses as $weakness) {
            switch ($weakness['type']) {
                case 'field_test':
                    $developmentPlan['focus_areas'][] = 'Technical Competency Enhancement';
                    $developmentPlan['training_modules'][] = 'Job-specific technical training';
                    $developmentPlan['training_modules'][] = 'Industry best practices workshop';
                    $developmentPlan['training_modules'][] = 'Certification program terkait bidang';
                    $developmentPlan['success_metrics'][] = 'Improvement dalam technical assessment score > 70%';
                    $developmentPlan['resources_needed'][] = 'Technical training budget';
                    $developmentPlan['resources_needed'][] = 'Subject matter expert mentor';
                    break;
                    
                case 'kraeplin':
                    $developmentPlan['focus_areas'][] = 'Concentration & Accuracy Improvement';
                    $developmentPlan['training_modules'][] = 'Concentration enhancement training';
                    $developmentPlan['training_modules'][] = 'Speed vs accuracy balance workshop';
                    $developmentPlan['training_modules'][] = 'Stress management program';
                    $developmentPlan['success_metrics'][] = 'Improvement dalam Kraeplin score > Grade C';
                    $developmentPlan['resources_needed'][] = 'Concentration training tools';
                    break;
                    
                case 'basic_math':
                    $developmentPlan['focus_areas'][] = 'Quantitative Skills Development';
                    $developmentPlan['training_modules'][] = 'Basic mathematics refresher';
                    $developmentPlan['training_modules'][] = 'Business mathematics application';
                    $developmentPlan['training_modules'][] = 'Excel and calculation tools training';
                    $developmentPlan['success_metrics'][] = 'Mathematical assessment score > 70%';
                    $developmentPlan['resources_needed'][] = 'Online learning platform';
                    break;
                    
                case 'visual_sequence':
                    $developmentPlan['focus_areas'][] = 'Pattern Recognition & Logical Thinking';
                    $developmentPlan['training_modules'][] = 'Logical reasoning enhancement';
                    $developmentPlan['training_modules'][] = 'Pattern recognition training';
                    $developmentPlan['success_metrics'][] = 'Visual sequence score > 75%';
                    break;
                    
                case 'synonym_antonym':
                    $developmentPlan['focus_areas'][] = 'Verbal & Communication Skills';
                    $developmentPlan['training_modules'][] = 'Vocabulary building program';
                    $developmentPlan['training_modules'][] = 'Reading comprehension improvement';
                    $developmentPlan['training_modules'][] = 'Business communication workshop';
                    $developmentPlan['success_metrics'][] = 'Verbal reasoning score > 75%';
                    $developmentPlan['resources_needed'][] = 'Language learning resources';
                    break;
            }
        }
        
        // Create timeline
        $developmentPlan['timeline'] = [
            'Month 1-2' => 'Assessment baseline dan kickoff training program',
            'Month 3-4' => 'Intensive training pada area kritis',
            'Month 5-6' => 'Praktik aplikasi dan on-job training',
            'Month 7-9' => 'Advanced training dan skill refinement (jika diperlukan)',
            'Month 10-12' => 'Evaluation dan continuous improvement'
        ];
        
        // Remove duplicates
        $developmentPlan['focus_areas'] = array_unique($developmentPlan['focus_areas']);
        $developmentPlan['training_modules'] = array_unique($developmentPlan['training_modules']);
        $developmentPlan['success_metrics'] = array_unique($developmentPlan['success_metrics']);
        $developmentPlan['resources_needed'] = array_unique($developmentPlan['resources_needed']);
        
        return $developmentPlan;
    }

    /**
     * Generate job-oriented detailed recommendation
     */
    private function generateDetailedRecommendation($overallScore, $strengths, $weaknesses, $sessionResults, $decision, $schedule)
    {
        $recommendation = [];
        $candidateName = $schedule->candidates->name;
        $jobTitle = $schedule->candidates->jobs->title ?? 'N/A';
        
        // Header dengan decision
        $recommendation[] = "KEPUTUSAN: {$decision['status']}";
        $recommendation[] = "Tingkat keyakinan: {$decision['confidence']}%";
        $recommendation[] = "";
        
        // Work Performance Prediction
        $recommendation[] = "PREDIKSI KINERJA KERJA:";
        $workPrediction = $this->predictWorkPerformance($sessionResults);
        foreach ($workPrediction as $prediction) {
            $recommendation[] = "• {$prediction}";
        }
        $recommendation[] = "";
        
        // Division & Role Recommendation
        $divisionRecommendation = $this->analyzeDivisionFit($sessionResults);
        $recommendation[] = "REKOMENDASI PENEMPATAN:";
        $recommendation[] = "• Divisi Terbaik: {$divisionRecommendation['primary_division']}";
        $recommendation[] = "  - Alasan: {$divisionRecommendation['reasoning']}";
        $recommendation[] = "  - Confidence Level: {$divisionRecommendation['confidence']}%";
        if (isset($divisionRecommendation['alternative'])) {
            $recommendation[] = "• Divisi Alternatif: {$divisionRecommendation['alternative']}";
        }
        $recommendation[] = "";
        
        // EPPS Personality Analysis for Work
        $personalityAnalysis = $this->analyzeEPPSForWork($sessionResults);
        if ($personalityAnalysis) {
            $recommendation[] = "ANALISIS KEPRIBADIAN (EPPS) & IMPACT KERJA:";
            $recommendation[] = "• Tipe Kepribadian: {$personalityAnalysis['personality_type']}";
            $recommendation[] = "• Gaya Kerja: {$personalityAnalysis['work_style']}";
            $recommendation[] = "• Motivasi Utama: {$personalityAnalysis['primary_motivation']}";
            $recommendation[] = "• Cara Berkomunikasi: {$personalityAnalysis['communication_style']}";
            $recommendation[] = "• Leadership Style: {$personalityAnalysis['leadership_style']}";
            $recommendation[] = "• Team Dynamics: {$personalityAnalysis['team_fit']}";
            $recommendation[] = "";
            
            $recommendation[] = "KEKUATAN KEPRIBADIAN DI TEMPAT KERJA:";
            foreach ($personalityAnalysis['workplace_strengths'] as $strength) {
                $recommendation[] = "• {$strength}";
            }
            $recommendation[] = "";
            
            if (!empty($personalityAnalysis['potential_challenges'])) {
                $recommendation[] = "POTENSI TANTANGAN KEPRIBADIAN:";
                foreach ($personalityAnalysis['potential_challenges'] as $challenge) {
                    $recommendation[] = "• {$challenge}";
                }
                $recommendation[] = "";
            }
        }
        
        // Work Behavior Prediction based on all tests
        $workBehavior = $this->predictWorkBehavior($sessionResults);
        $recommendation[] = "PREDIKSI PERILAKU KERJA:";
        $recommendation[] = "• Saat Menangani Detail: {$workBehavior['attention_to_detail']}";
        $recommendation[] = "• Dalam Situasi Tekanan: {$workBehavior['under_pressure']}";
        $recommendation[] = "• Dalam Tim: {$workBehavior['teamwork']}";
        $recommendation[] = "• Menghadapi Deadline: {$workBehavior['deadline_management']}";
        $recommendation[] = "• Problem Solving: {$workBehavior['problem_solving']}";
        $recommendation[] = "";
        
        // Management & Development Recommendations
        $managementTips = $this->getManagementRecommendations($sessionResults, $personalityAnalysis);
        $recommendation[] = "CARA MENGELOLA KANDIDAT INI:";
        foreach ($managementTips as $tip) {
            $recommendation[] = "• {$tip}";
        }
        $recommendation[] = "";
        
        // Development Plan for Job Performance
        $jobDevelopment = $this->getJobDevelopmentPlan($weaknesses, $sessionResults);
        $recommendation[] = "RENCANA PENGEMBANGAN UNTUK PERFORMA KERJA:";
        $recommendation[] = "• Fokus Utama: {$jobDevelopment['primary_focus']}";
        $recommendation[] = "• Timeline: {$jobDevelopment['timeline']}";
        $recommendation[] = "• Training yang Dibutuhkan:";
        foreach ($jobDevelopment['required_training'] as $training) {
            $recommendation[] = "  - {$training}";
        }
        $recommendation[] = "";
        
        // Final Decision Reasoning
        $recommendation[] = "KESIMPULAN & NEXT STEPS:";
        foreach ($decision['reasoning'] as $reason) {
            $recommendation[] = "• {$reason}";
        }
        
        if (!empty($decision['conditions'])) {
            $recommendation[] = "";
            $recommendation[] = "SYARAT KHUSUS:";
            foreach ($decision['conditions'] as $condition) {
                $recommendation[] = "• {$condition}";
            }
        }
        
        return implode("\n", $recommendation);
    }

    /**
     * Predict work performance based on test results
     */
    private function predictWorkPerformance($sessionResults)
    {
        $predictions = [];
        
        foreach ($sessionResults as $result) {
            $categoryType = $result['category_type'];
            $percentage = $result['percentage'];
            
            switch ($categoryType) {
                case 'kraeplin':
                    if (isset($result['kraeplin_analysis'])) {
                        $accuracy = $result['kraeplin_analysis']['accuracy'];
                        $speed = $result['kraeplin_analysis']['speed_score'];
                        
                        if ($accuracy >= 90) {
                            $predictions[] = "Akan sangat teliti dalam pekerjaan detail dan perhitungan";
                        } elseif ($accuracy >= 70) {
                            $predictions[] = "Cukup teliti namun perlu double-check untuk pekerjaan kritis";
                        } else {
                            $predictions[] = "Memerlukan sistem kontrol kualitas untuk pekerjaan detail";
                        }
                        
                        if ($speed >= 70) {
                            $predictions[] = "Mampu bekerja dengan pace yang baik di lingkungan fast-paced";
                        } else {
                            $predictions[] = "Lebih cocok untuk pekerjaan yang tidak mengejar deadline ketat";
                        }
                    }
                    break;
                    
                case 'field_test':
                    if (isset($result['field_analysis']['field_percentages'])) {
                        $fieldScores = $result['field_analysis']['field_percentages'];
                        $bestField = array_keys($fieldScores, max($fieldScores))[0];
                        $bestScore = max($fieldScores);
                        
                        if ($bestScore >= 70) {
                            $predictions[] = "Akan unggul dalam pekerjaan {$bestField}-related";
                        } else {
                            $predictions[] = "Memerlukan intensive training untuk kompetensi teknis";
                        }
                    }
                    break;
                    
                case 'basic_math':
                    if ($percentage >= 80) {
                        $predictions[] = "Mampu menangani analisis finansial dan perhitungan kompleks";
                    } elseif ($percentage >= 60) {
                        $predictions[] = "Dapat menangani perhitungan dasar dengan bantuan tools";
                    } else {
                        $predictions[] = "Memerlukan support untuk tugas-tugas yang melibatkan angka";
                    }
                    break;
                    
                case 'synonym_antonym':
                    if ($percentage >= 80) {
                        $predictions[] = "Komunikasi tertulis dan verbal akan sangat baik";
                    } else {
                        $predictions[] = "Perlu improvement dalam komunikasi dan dokumentasi";
                    }
                    break;
            }
        }
        
        return $predictions;
    }

    /**
     * Analyze division fit based on field test results
     */
    private function analyzeDivisionFit($sessionResults)
    {
        $divisionFit = [
            'primary_division' => 'General',
            'reasoning' => 'Belum dapat menentukan divisi spesifik',
            'confidence' => 50,
            'alternative' => null
        ];
        
        foreach ($sessionResults as $result) {
            if ($result['category_type'] === 'field_test' && isset($result['field_analysis']['field_percentages'])) {
                $fieldScores = $result['field_analysis']['field_percentages'];
                arsort($fieldScores);
                
                $topField = array_keys($fieldScores)[0];
                $topScore = $fieldScores[$topField];
                $secondField = array_keys($fieldScores)[1] ?? null;
                $secondScore = $fieldScores[$secondField] ?? 0;
                
                // Determine division
                switch ($topField) {
                    case 'audit':
                        $divisionFit['primary_division'] = 'Audit Division';
                        if ($topScore >= 70) {
                            $divisionFit['reasoning'] = "Skor audit sangat baik ({$topScore}%), menunjukkan pemahaman prosedur audit dan analytical thinking yang kuat";
                            $divisionFit['confidence'] = 90;
                        } elseif ($topScore >= 60) {
                            $divisionFit['reasoning'] = "Skor audit cukup baik ({$topScore}%), dengan training dapat develop menjadi auditor yang kompeten";
                            $divisionFit['confidence'] = 75;
                        } else {
                            $divisionFit['reasoning'] = "Skor audit rendah ({$topScore}%), namun masih yang terbaik dibanding area lain";
                            $divisionFit['confidence'] = 60;
                        }
                        break;
                        
                    case 'tax':
                        $divisionFit['primary_division'] = 'Tax Division';
                        if ($topScore >= 70) {
                            $divisionFit['reasoning'] = "Skor tax excellent ({$topScore}%), menunjukkan pemahaman regulasi dan perhitungan pajak yang solid";
                            $divisionFit['confidence'] = 90;
                        } else {
                            $divisionFit['reasoning'] = "Skor tax terbaik ({$topScore}%), cocok untuk tax consultant dengan additional training";
                            $divisionFit['confidence'] = 75;
                        }
                        break;
                        
                    case 'accounting':
                        $divisionFit['primary_division'] = 'Accounting Division';
                        if ($topScore >= 70) {
                            $divisionFit['reasoning'] = "Skor accounting tinggi ({$topScore}%), menunjukkan pemahaman prinsip akuntansi dan financial reporting";
                            $divisionFit['confidence'] = 90;
                        } else {
                            $divisionFit['reasoning'] = "Skor accounting terbaik ({$topScore}%), suitable untuk staff accounting dengan mentoring";
                            $divisionFit['confidence'] = 75;
                        }
                        break;
                }
                
                // Alternative division
                if ($secondField && $secondScore >= 50) {
                    $divisionFit['alternative'] = ucfirst($secondField) . " Division (score: {$secondScore}%)";
                }
                
                break;
            }
        }
        
        return $divisionFit;
    }

    /**
     * Analyze EPPS results for workplace behavior
     */
    private function analyzeEPPSForWork($sessionResults)
    {
        foreach ($sessionResults as $result) {
            if ($result['category_type'] === 'epps_test' && isset($result['epps_analysis']['dominant_traits'])) {
                $dominantTraits = $result['epps_analysis']['dominant_traits'];
                $dimensionScores = $result['epps_analysis']['dimension_scores'] ?? [];
                
                return $this->interpretEPPSForWorkplace($dominantTraits, $dimensionScores);
            }
        }
        
        return null;
    }

    /**
     * Interpret EPPS results for workplace context
     */
    private function interpretEPPSForWorkplace($dominantTraits, $dimensionScores)
    {
        $analysis = [
            'personality_type' => '',
            'work_style' => '',
            'primary_motivation' => '',
            'communication_style' => '',
            'leadership_style' => '',
            'team_fit' => '',
            'workplace_strengths' => [],
            'potential_challenges' => []
        ];
        
        // Analyze based on dominant traits
        $topTraits = array_slice($dominantTraits, 0, 3);
        
        // Personality Type
        if (in_array('dominance', $topTraits) && in_array('achievement', $topTraits)) {
            $analysis['personality_type'] = 'Leader-Achiever';
            $analysis['leadership_style'] = 'Directive leadership, suka mengambil inisiatif dan memimpin tim';
        } elseif (in_array('affiliation', $topTraits) && in_array('deference', $topTraits)) {
            $analysis['personality_type'] = 'Team Player-Supporter';
            $analysis['leadership_style'] = 'Collaborative leadership, fokus pada harmony dan team building';
        } elseif (in_array('autonomy', $topTraits) && in_array('endurance', $topTraits)) {
            $analysis['personality_type'] = 'Independent-Persistent';
            $analysis['leadership_style'] = 'Expert leadership, memimpin melalui keahlian dan konsistensi';
        } elseif (in_array('order', $topTraits) && in_array('endurance', $topTraits)) {
            $analysis['personality_type'] = 'Systematic-Reliable';
            $analysis['leadership_style'] = 'Process-oriented leadership, fokus pada sistem dan procedures';
        } else {
            $analysis['personality_type'] = 'Balanced Professional';
            $analysis['leadership_style'] = 'Situational leadership, adaptable sesuai kondisi';
        }
        
        // Work Style
        foreach ($topTraits as $trait) {
            switch ($trait) {
                case 'achievement':
                    $analysis['work_style'] = 'Goal-oriented, driven by results dan personal excellence';
                    $analysis['primary_motivation'] = 'Pencapaian target dan recognition atas prestasi';
                    break;
                case 'order':
                    $analysis['work_style'] = 'Systematic dan detail-oriented, suka struktur dan planning';
                    $analysis['primary_motivation'] = 'Lingkungan kerja yang terorganisir dan predictable';
                    break;
                case 'autonomy':
                    $analysis['work_style'] = 'Independent worker, prefer minimal supervision';
                    $analysis['primary_motivation'] = 'Kebebasan dalam mengatur cara kerja dan decision making';
                    break;
                case 'affiliation':
                    $analysis['work_style'] = 'Collaborative, thrive dalam team environment';
                    $analysis['primary_motivation'] = 'Hubungan kerja yang harmonis dan team collaboration';
                    break;
                case 'dominance':
                    $analysis['work_style'] = 'Leadership-oriented, suka mengambil charge dalam projects';
                    $analysis['primary_motivation'] = 'Positions of responsibility dan influence';
                    break;
            }
            
            if ($analysis['work_style']) break;
        }
        
        // Communication Style
        if (in_array('exhibition', $topTraits)) {
            $analysis['communication_style'] = 'Assertive dan expressive, comfortable dalam presentations';
        } elseif (in_array('deference', $topTraits)) {
            $analysis['communication_style'] = 'Respectful dan diplomatic, good listener';
        } elseif (in_array('dominance', $topTraits)) {
            $analysis['communication_style'] = 'Direct dan confident, clear dalam instructions';
        } else {
            $analysis['communication_style'] = 'Balanced communicator, adaptable style';
        }
        
        // Team Fit
        if (in_array('affiliation', $topTraits)) {
            $analysis['team_fit'] = 'Excellent team player, natural collaborator';
        } elseif (in_array('autonomy', $topTraits)) {
            $analysis['team_fit'] = 'Independent contributor, works well with minimal team interaction';
        } elseif (in_array('dominance', $topTraits)) {
            $analysis['team_fit'] = 'Natural team leader, good for project management roles';
        } else {
            $analysis['team_fit'] = 'Flexible team member, dapat adapt dengan various team dynamics';
        }
        
        // Workplace Strengths
        foreach ($topTraits as $trait) {
            switch ($trait) {
                case 'achievement':
                    $analysis['workplace_strengths'][] = 'Highly motivated untuk exceed expectations';
                    $analysis['workplace_strengths'][] = 'Self-driven dalam continuous improvement';
                    break;
                case 'order':
                    $analysis['workplace_strengths'][] = 'Excellent dalam organizing dan planning tasks';
                    $analysis['workplace_strengths'][] = 'Detail-oriented dengan quality control mindset';
                    break;
                case 'endurance':
                    $analysis['workplace_strengths'][] = 'Persistent dalam long-term projects';
                    $analysis['workplace_strengths'][] = 'Reliable untuk tasks yang memerlukan consistency';
                    break;
                case 'dominance':
                    $analysis['workplace_strengths'][] = 'Natural leadership abilities';
                    $analysis['workplace_strengths'][] = 'Good dalam decision-making under pressure';
                    break;
                case 'affiliation':
                    $analysis['workplace_strengths'][] = 'Excellent interpersonal skills';
                    $analysis['workplace_strengths'][] = 'Good team builder dan conflict resolver';
                    break;
            }
        }
        
        // Potential Challenges
        foreach ($topTraits as $trait) {
            switch ($trait) {
                case 'dominance':
                    if (($dimensionScores['dominance'] ?? 0) > 80) {
                        $analysis['potential_challenges'][] = 'Mungkin too assertive, perlu balance dalam team collaboration';
                    }
                    break;
                case 'autonomy':
                    if (($dimensionScores['autonomy'] ?? 0) > 80) {
                        $analysis['potential_challenges'][] = 'Prefer independence, might resist micromanagement';
                    }
                    break;
                case 'exhibition':
                    if (($dimensionScores['exhibition'] ?? 0) > 80) {
                        $analysis['potential_challenges'][] = 'Might seek too much attention, perlu channel positively';
                    }
                    break;
                case 'change':
                    if (($dimensionScores['change'] ?? 0) > 80) {
                        $analysis['potential_challenges'][] = 'High need for variety, might get bored dengan routine tasks';
                    }
                    break;
            }
        }
        
        return $analysis;
    }

    /**
     * Predict work behavior based on all test results
     */
    private function predictWorkBehavior($sessionResults)
    {
        $behavior = [
            'under_pressure' => 'Respons normal terhadap tekanan',
            'attention_to_detail' => 'Tingkat detail cukup baik',
            'teamwork' => 'Dapat bekerja dalam tim',
            'deadline_management' => 'Mampu mengelola deadline dengan baik',
            'problem_solving' => 'Pendekatan problem solving sistematis'
        ];
        
        foreach ($sessionResults as $result) {
            $categoryType = $result['category_type'];
            
            switch ($categoryType) {
                case 'kraeplin':
                    if (isset($result['kraeplin_analysis'])) {
                        $accuracy = $result['kraeplin_analysis']['accuracy'];
                        $speed = $result['kraeplin_analysis']['speed_score'];
                        $consistency = $result['kraeplin_analysis']['consistency_score'];
                        
                        if ($accuracy >= 90 && $consistency >= 80) {
                            $behavior['under_pressure'] = 'Sangat calm dan accurate bahkan under pressure';
                            $behavior['attention_to_detail'] = 'Extremely detail-oriented, jarang membuat kesalahan';
                        } elseif ($speed < 50) {
                            $behavior['deadline_management'] = 'Prefer quality over speed, butuh realistic timeline';
                        }
                        
                        if ($consistency < 60) {
                            $behavior['under_pressure'] = 'Performance bisa inconsistent saat tekanan tinggi';
                        }
                    }
                    break;
                    
                case 'epps_test':
                    if (isset($result['epps_analysis']['dominant_traits'])) {
                        $traits = $result['epps_analysis']['dominant_traits'];
                        
                        if (in_array('affiliation', $traits)) {
                            $behavior['teamwork'] = 'Excellent team player, thrives dalam collaborative environment';
                        }
                        if (in_array('dominance', $traits)) {
                            $behavior['teamwork'] = 'Natural leader dalam tim, good untuk coordinate projects';
                        }
                        if (in_array('autonomy', $traits)) {
                            $behavior['teamwork'] = 'Independent worker, prefer individual contributions';
                        }
                        if (in_array('endurance', $traits)) {
                            $behavior['deadline_management'] = 'Excellent perseverance, reliable untuk long-term projects';
                        }
                        if (in_array('change', $traits)) {
                            $behavior['problem_solving'] = 'Creative problem solver, open untuk innovative solutions';
                        }
                        if (in_array('order', $traits)) {
                            $behavior['problem_solving'] = 'Systematic problem solver, methodical approach';
                        }
                    }
                    break;
                    
                case 'visual_sequence':
                    if ($result['percentage'] >= 80) {
                        $behavior['problem_solving'] = 'Strong logical thinking, good untuk analytical tasks';
                    }
                    break;
                    
                case 'synonym_antonym':
                    if ($result['percentage'] >= 80) {
                        $behavior['teamwork'] = 'Good communication skills, effective dalam team discussions';
                    }
                    break;
            }
        }
        
        return $behavior;
    }

    /**
     * Get management recommendations based on test results and personality
     */
    private function getManagementRecommendations($sessionResults, $personalityAnalysis)
    {
        $recommendations = [];
        
        if ($personalityAnalysis) {
            // Based on personality type
            switch ($personalityAnalysis['personality_type']) {
                case 'Leader-Achiever':
                    $recommendations[] = 'Berikan challenging projects dan clear targets untuk achievement';
                    $recommendations[] = 'Allow untuk take ownership of projects';
                    $recommendations[] = 'Provide regular feedback dan recognition';
                    break;
                    
                case 'Team Player-Supporter':
                    $recommendations[] = 'Integrate dengan tim dengan good team dynamics';
                    $recommendations[] = 'Assign untuk collaborative projects';
                    $recommendations[] = 'Provide supportive dan encouraging management style';
                    break;
                    
                case 'Independent-Persistent':
                    $recommendations[] = 'Give autonomy dalam cara kerja dengan clear deliverables';
                    $recommendations[] = 'Minimize micromanagement';
                    $recommendations[] = 'Provide long-term projects yang memerlukan persistence';
                    break;
                    
                case 'Systematic-Reliable':
                    $recommendations[] = 'Provide clear processes dan procedures';
                    $recommendations[] = 'Give structured tasks dengan detailed guidelines';
                    $recommendations[] = 'Appreciate consistency dan reliability mereka';
                    break;
            }
            
            // Based on dominant traits
            $dominantTraits = $personalityAnalysis['dominant_traits'] ?? [];
            if (in_array('exhibition', $dominantTraits)) {
                $recommendations[] = 'Give opportunities untuk presentations atau client interactions';
            }
            if (in_array('change', $dominantTraits)) {
                $recommendations[] = 'Provide variety dalam assignments untuk avoid boredom';
            }
        }
        
        // Based on test performance
        foreach ($sessionResults as $result) {
            if ($result['category_type'] === 'kraeplin') {
                if (isset($result['kraeplin_analysis']['speed_score']) && $result['kraeplin_analysis']['speed_score'] < 60) {
                    $recommendations[] = 'Allow sufficient time untuk tasks, avoid unnecessary time pressure';
                }
            }
        }
        
        if (empty($recommendations)) {
            $recommendations[] = 'Apply standard management approach dengan regular check-ins';
        }
        
        return $recommendations;
    }

    /**
     * Get job-specific development plan
     */
    private function getJobDevelopmentPlan($weaknesses, $sessionResults)
    {
        $plan = [
            'primary_focus' => 'General skill enhancement',
            'timeline' => '3-6 bulan',
            'required_training' => ['Basic orientation program']
        ];
        
        // Focus on most critical weakness
        if (!empty($weaknesses)) {
            $criticalWeakness = $weaknesses[0]; // Most critical
            
            switch ($criticalWeakness['type']) {
                case 'field_test':
                    $plan['primary_focus'] = 'Technical competency dalam audit/tax/accounting';
                    $plan['timeline'] = '6-9 bulan';
                    $plan['required_training'] = [
                        'Intensive technical training untuk area terlemah',
                        'Mentoring dengan senior staff',
                        'Certification program sesuai divisi placement',
                        'Hands-on practice dengan real cases',
                        'Regular assessment dan feedback sessions'
                    ];
                    break;
                    
                case 'kraeplin':
                    $plan['primary_focus'] = 'Concentration dan accuracy improvement';
                    $plan['timeline'] = '3-6 bulan';
                    $plan['required_training'] = [
                        'Concentration enhancement exercises',
                        'Time management training',
                        'Stress management workshop',
                        'Quality control procedures training'
                    ];
                    break;
                    
                case 'basic_math':
                    $plan['primary_focus'] = 'Numerical analysis dan calculation skills';
                    $plan['timeline'] = '4-6 bulan';
                    $plan['required_training'] = [
                        'Business mathematics refresher',
                        'Excel advanced training',
                        'Financial calculation workshops',
                        'Practice dengan real financial data'
                    ];
                    break;
            }
        }
        
        return $plan;
    }
}