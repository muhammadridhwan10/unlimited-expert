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

        // Parse notes for additional insights
        $notes = json_decode($result->notes, true) ?? [];
        
        $categoryScores = $result->category_scores ?? [];
        $strengths = [];
        $weaknesses = [];
        $insights = [];

        foreach ($categoryScores as $categoryId => $categoryData) {
            $categoryName = $this->getCategoryName($categoryId);
            $results = $categoryData['results'] ?? [];
            $percentage = $results['percentage'] ?? 0;
            
            if ($percentage >= 80) {
                $strengths[] = [
                    'category' => $categoryName,
                    'score' => $percentage,
                    'type' => $this->getCategoryType($categoryId)
                ];
            } elseif ($percentage < 60) {
                $weaknesses[] = [
                    'category' => $categoryName,
                    'score' => $percentage,
                    'type' => $this->getCategoryType($categoryId)
                ];
            }
        }

        // Generate specific insights
        if (isset($notes['kraeplin_analysis'])) {
            $kraeplin = $notes['kraeplin_analysis'];
            $insights[] = "Kraeplin Grade: " . ($kraeplin['grade'] ?? 'N/A');
            if (isset($kraeplin['interpretation'])) {
                $insights = array_merge($insights, array_slice($kraeplin['interpretation'], 0, 2));
            }
        }

        if (isset($notes['field_recommendations'])) {
            $field = $notes['field_recommendations'];
            if (isset($field['division_recommendation']['primary'])) {
                $insights[] = "Rekomendasi: " . $field['division_recommendation']['primary'];
            }
        }

        if (isset($notes['personality_insights'])) {
            $personality = $notes['personality_insights'];
            if (isset($personality['dominant_traits'])) {
                $traits = array_slice($personality['dominant_traits'], 0, 3);
                $insights[] = "Trait Dominan: " . implode(', ', $traits);
            }
        }

        return [
            'overall_score' => $result->percentage,
            'grade' => $result->grade,
            'total_time' => $this->getTotalTestTime($schedule),
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'insights' => $insights,
            'recommendation' => $this->generateRecommendation($result->percentage, $strengths, $weaknesses, $notes),
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
        return 'E';
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
}