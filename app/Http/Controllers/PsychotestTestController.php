<?php
// app/Http/Controllers/PsychotestTestController.php - Updated untuk Test Baru
namespace App\Http\Controllers;

use App\Models\PsychotestSchedule;
use App\Models\PsychotestCategory;
use App\Models\PsychotestSession;
use App\Models\PsychotestQuestion;
use App\Models\PsychotestAnswer;
use App\Models\PsychotestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class PsychotestTestController extends Controller
{	
    public function login()
    {
        return view('psychotest.test.login');
    }

    public function authenticate(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'username' => 'required|string',
                'password' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $schedule = PsychotestSchedule::where('username', $request->username)->first();

        if (!$schedule) {
            return redirect()->back()->with('error', __('Invalid credentials.'));
        }

        if (!Hash::check($request->password, $schedule->password)) {
            return redirect()->back()->with('error', __('Invalid credentials.'));
        }

        // Time validation
        $now = now();
        $nowTimestamp = $now->timestamp;
        $startTimestamp = $schedule->start_time->timestamp;
        $endTimestamp = $schedule->end_time->timestamp;

        if ($schedule->status == 'completed') {
            return redirect()->back()->with('error', __('This test has already been completed.'));
        }

        if ($schedule->status == 'cancelled') {
            return redirect()->back()->with('error', __('This test has been cancelled.'));
        }

        if ($nowTimestamp > $endTimestamp) {
            $schedule->update(['status' => 'expired']);
            return redirect()->back()->with('error', __('This test has expired.'));
        }

        if ($nowTimestamp < $startTimestamp) {
            $startTimeFormatted = $schedule->start_time->format('d M Y H:i');
            $endTimeFormatted = $schedule->end_time->format('d M Y H:i');
            return redirect()->back()->with('error', __("Test will be available from {$startTimeFormatted} to {$endTimeFormatted}."));
        }

        if (!in_array($schedule->status, ['scheduled', 'in_progress'])) {
            return redirect()->back()->with('error', __('Test is not available.'));
        }

        Session::put('psychotest_schedule_id', $schedule->id);
        return redirect()->route('psychotest.test.start');
    }

    public function start()
    {
        $scheduleId = Session::get('psychotest_schedule_id');
        if (!$scheduleId) {
            return redirect()->route('psychotest.test.login')->with('error', __('Please login first.'));
        }

        $schedule = PsychotestSchedule::with(['candidates'])->findOrFail($scheduleId);

        if (!$schedule->canStart()) {
            Session::forget('psychotest_schedule_id');
            return redirect()->route('psychotest.test.login')->with('error', __('Test session expired.'));
        }

        // Get categories for this schedule (considering selected categories)
        $categories = $schedule->getCategories();

        if ($categories->isEmpty()) {
            return redirect()->route('psychotest.test.login')->with('error', __('No test categories available.'));
        }

        return view('psychotest.test.start', compact('schedule', 'categories'));
    }

    public function startTest()
    {
        $scheduleId = Session::get('psychotest_schedule_id');
        if (!$scheduleId) {
            return redirect()->route('psychotest.test.login')->with('error', __('Please login first.'));
        }

        $schedule = PsychotestSchedule::findOrFail($scheduleId);

        // Update schedule status dan waktu mulai
        if ($schedule->status == 'scheduled') {
            $schedule->update([
                'status' => 'in_progress',
                'started_at' => now()
            ]);
        }

        // Create sessions for selected categories
        $this->createSessionsWithoutStarting($schedule);

        // Get first category to start
        $categories = $schedule->getCategories();
        $firstCategory = $categories->first();

        if (!$firstCategory) {
            return redirect()->route('psychotest.test.login')->with('error', __('No test categories available.'));
        }

        return redirect()->route('psychotest.test.category', $firstCategory->code);
    }

    private function createSessionsWithoutStarting($schedule)
    {
        $categories = $schedule->getCategories();
        
        foreach ($categories as $category) {
            PsychotestSession::firstOrCreate([
                'schedule_id' => $schedule->id,
                'category_id' => $category->id,
            ], [
                'status' => 'pending',
                'started_at' => null,
            ]);
        }
    }

    public function overview()
    {
        $scheduleId = Session::get('psychotest_schedule_id');
        if (!$scheduleId) {
            return redirect()->route('psychotest.test.login')->with('error', __('Please login first.'));
        }

        $schedule = PsychotestSchedule::with(['sessions.category'])->findOrFail($scheduleId);

        // Check for current active session
        $currentSession = $schedule->getCurrentSession();
        if ($currentSession) {
            return redirect()->route('psychotest.test.category', $currentSession->category->code);
        }

        // Get next session to start
        $nextSession = $schedule->getNextSession();
        if ($nextSession) {
            return redirect()->route('psychotest.test.category', $nextSession->category->code);
        } else {
            $this->completeAllTests($schedule);
            Session::forget('psychotest_schedule_id');
            return redirect()->route('psychotest.test.completed');
        }
    }

    public function startCategory($categoryCode, Request $request)
    {
        $scheduleId = Session::get('psychotest_schedule_id');
        if (!$scheduleId) {
            return redirect()->route('psychotest.test.login')->with('error', __('Please login first.'));
        }

        $schedule = PsychotestSchedule::findOrFail($scheduleId);
        $category = PsychotestCategory::where('code', $categoryCode)->firstOrFail();

        \Log::info('Starting category: ' . $categoryCode, [
            'category_name' => $category->name,
            'category_type' => $category->type,
            'is_epps_check' => $this->isEPPS($category)
        ]);

        // Handle different category types
        if ($category->isKraeplin()) {
            return $this->handleKraeplinCategory($schedule, $category);
        } elseif ($this->isEPPS($category)) {
            \Log::info('EPPS test detected, routing to EPPS handler');
            return $this->handleEPPSCategory($schedule, $category, $request);
        } elseif ($category->isFieldSpecific()) {
            return $this->handleFieldSpecificCategory($schedule, $category, $request);
        } else {
            return $this->handleStandardCategory($schedule, $category, $request);
        }
    }

    private function handleKraeplinCategory($schedule, $category)
    {
        \Log::info('Kraeplin test detected, starting directly');
        
        $session = PsychotestSession::where('schedule_id', $schedule->id)
            ->where('category_id', $category->id)
            ->first();

        if (!$session) {
            $session = PsychotestSession::create([
                'schedule_id' => $schedule->id,
                'category_id' => $category->id,
                'status' => 'pending',
            ]);
        }

        if ($session->status === 'pending') {
            $session->start();
        }

        return $this->kraeplinTest($session);
    }

    private function isEPPS($category)
    {
        return stripos($category->code, 'epps') !== false || 
            stripos($category->name, 'epps') !== false ||
            $category->type === 'personality' && stripos($category->name, 'epps') !== false;
    }

    private function handleEPPSCategory($schedule, $category, $request)
    {
        if (!$request->has('start')) {
            return $this->showEPPSInstructions($category);
        }

        $session = PsychotestSession::where('schedule_id', $schedule->id)
            ->where('category_id', $category->id)
            ->first();

        if (!$session) {
            $session = PsychotestSession::create([
                'schedule_id' => $schedule->id,
                'category_id' => $category->id,
                'status' => 'pending',
            ]);
        }

        if ($session->status === 'pending') {
            $session->start();
        }

        return $this->eppsTest($session);
    }

    private function handleFieldSpecificCategory($schedule, $category, $request)
    {
        if (!$request->has('start')) {
            return $this->showFieldSpecificInstructions($category);
        }

        $session = PsychotestSession::where('schedule_id', $schedule->id)
            ->where('category_id', $category->id)
            ->first();

        if (!$session) {
            $session = PsychotestSession::create([
                'schedule_id' => $schedule->id,
                'category_id' => $category->id,
                'status' => 'pending',
            ]);
        }

        if ($session->status === 'pending') {
            $session->start();
        }

        return $this->fieldSpecificTest($session);
    }

    private function handleStandardCategory($schedule, $category, $request)
    {
        if (!$request->has('start')) {
            return $this->showStandardInstructions($category);
        }

        $session = PsychotestSession::where('schedule_id', $schedule->id)
            ->where('category_id', $category->id)
            ->first();

        if (!$session) {
            $session = PsychotestSession::create([
                'schedule_id' => $schedule->id,
                'category_id' => $category->id,
                'status' => 'pending',
            ]);
        }

        if ($session->status === 'pending') {
            $session->start();
        }

        return $this->standardTest($session);
    }

    private function showEPPSInstructions($category)
    {
        $scheduleId = Session::get('psychotest_schedule_id');
        $schedule = PsychotestSchedule::findOrFail($scheduleId);
        
        return view('psychotest.test.epps-instructions', compact('schedule', 'category'));
    }

    private function showFieldSpecificInstructions($category)
    {
        $scheduleId = Session::get('psychotest_schedule_id');
        $schedule = PsychotestSchedule::findOrFail($scheduleId);
        
        // Get sample questions for preview
        $sampleQuestions = $category->questions()->active()->ordered()->limit(3)->get();
        
        return view('psychotest.test.field-instructions', compact('schedule', 'category', 'sampleQuestions'));
    }

    private function showStandardInstructions($category)
    {
        $scheduleId = Session::get('psychotest_schedule_id');
        $schedule = PsychotestSchedule::findOrFail($scheduleId);
        
        $questions = $category->questions()->active()->ordered()->get();
    

        $session = PsychotestSession::firstOrCreate([
            'schedule_id' => $scheduleId,
            'category_id' => $category->id,
        ], [
            'status' => 'pending',
            'started_at' => null,
        ]);

        return view('psychotest.test.standard-instructions', compact('schedule', 'category', 'questions', 'session'));
    }

    private function eppsTest($session)
    {
        $category = $session->category;
        
        // Get EPPS questions (forced choice pairs)
        $questions = $category->questions()->active()->ordered()->get();
        
        if ($questions->isEmpty()) {
            return redirect()->route('psychotest.test.overview')
                ->with('error', __('No questions available for this EPPS test.'));
        }

        // Get existing answers
        $answers = PsychotestAnswer::where('session_id', $session->id)
            ->get()
            ->keyBy('question_id');

        $remainingSeconds = $session->getRemainingSeconds();
        if ($remainingSeconds <= 0) {
            $this->completeSession($session);
            return $this->moveToNextTest($session->schedule);
        }

        $remainingMinutes = ceil($remainingSeconds / 60);

        // Gunakan view standard-questions dengan flag EPPS
        return view('psychotest.test.standard-questions', compact('session', 'category', 'questions', 'answers', 'remainingSeconds', 'remainingMinutes'))
            ->with('isEPPS', true);
    }

    private function fieldSpecificTest($session)
    {
        $category = $session->category;
        
        // Get field specific questions
        $questions = $category->questions()->active()->ordered()->get();
        
        if ($questions->isEmpty()) {
            return redirect()->route('psychotest.test.overview')
                ->with('error', __('No questions available for this test.'));
        }

        // Get existing answers
        $answers = PsychotestAnswer::where('session_id', $session->id)
            ->get()
            ->keyBy('question_id');

        $remainingSeconds = $session->getRemainingSeconds();
        if ($remainingSeconds <= 0) {
            $this->completeSession($session);
            return $this->moveToNextTest($session->schedule);
        }

        return view('psychotest.test.standard-questions', compact('session', 'category', 'questions', 'answers', 'remainingSeconds'));
    }

    private function standardTest($session)
    {
        $category = $session->category;
        
        $questions = $category->questions()->active()->ordered()->get();
        
        if ($questions->isEmpty()) {
            return redirect()->route('psychotest.test.overview')
                ->with('error', __('No questions available for this test.'));
        }

        $answers = PsychotestAnswer::where('session_id', $session->id)
            ->get()
            ->keyBy('question_id');

        $remainingSeconds = $session->getRemainingSeconds();
        if ($remainingSeconds <= 0) {
            $this->completeSession($session);
            return $this->moveToNextTest($session->schedule);
        }

        $remainingMinutes = ceil($remainingSeconds / 60);

        return view('psychotest.test.standard-questions', compact('session', 'category', 'questions', 'answers', 'remainingSeconds', 'remainingMinutes'));
    }

    private function kraeplinTest($session)
    {
        $category = $session->category;
        
        $kraeplinData = $this->generateKraeplinData($category);
        
        $remainingSeconds = $session->getRemainingSeconds();
        if ($remainingSeconds <= 0) {
            $this->completeSession($session);
            return $this->moveToNextTest($session->schedule);
        }

        return view('psychotest.test.kraeplin', compact('session', 'category', 'kraeplinData', 'remainingSeconds'));
    }

    public function saveAnswer(Request $request)
    {
        $scheduleId = Session::get('psychotest_schedule_id');
        if (!$scheduleId) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'session_id' => 'required|exists:psychotest_sessions,id',
                'question_id' => 'required|exists:psychotest_questions,id',
                'answer' => 'required',
                'time_taken' => 'nullable|integer',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->getMessageBag()->first()
            ], 400);
        }

        $session = PsychotestSession::findOrFail($request->session_id);
        
        if ($session->schedule_id != $scheduleId) {
            return response()->json(['error' => 'Invalid session'], 403);
        }

        if ($session->status != 'in_progress' || !$session->hasTimeRemaining()) {
            return response()->json(['error' => 'Test expired'], 401);
        }

        $question = PsychotestQuestion::findOrFail($request->question_id);

        // Calculate points based on question type
        $pointsEarned = $this->calculatePoints($question, $request->answer);

        $answer = PsychotestAnswer::updateOrCreate(
            [
                'schedule_id' => $scheduleId,
                'session_id' => $request->session_id,
                'question_id' => $request->question_id,
            ],
            [
                'answer' => $request->answer,
                'points_earned' => $pointsEarned,
                'answered_at' => now(),
                'time_taken_seconds' => $request->time_taken ?? 0,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Answer saved successfully',
            'data' => [
                'question_id' => $request->question_id,
                'answer' => $request->answer,
                'points_earned' => $pointsEarned,
                'answered_at' => $answer->answered_at->toISOString()
            ]
        ]);
    }

    public function saveKraeplinAnswer(Request $request)
    {
        $scheduleId = Session::get('psychotest_schedule_id');
        if (!$scheduleId) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'session_id' => 'required|exists:psychotest_sessions,id',
                'kraeplin_data' => 'required|array', // Data semua kolom
                'statistics' => 'required|array',    // Statistik hasil
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->getMessageBag()->first()
            ], 400);
        }

        $session = PsychotestSession::findOrFail($request->session_id);
        
        if ($session->schedule_id != $scheduleId) {
            return response()->json(['error' => 'Invalid session'], 403);
        }

        // Prepare session data untuk disimpan
        $sessionData = $session->session_data ?? [];
        
        // Simpan data Kraeplin
        $sessionData['kraeplin'] = [
            'all_answers' => $request->kraeplin_data, // Array jawaban per kolom
            'statistics' => $request->statistics,     // Statistik (total_answers, correct_answers, dll)
            'completed_at' => now()->toDateTimeString(),
        ];

        // Update session dengan data baru
        $session->update(['session_data' => $sessionData]);

        return response()->json([
            'success' => true,
            'message' => 'Kraeplin answers saved successfully'
        ]);
    }

    public function submitTest(Request $request)
    {
        $scheduleId = Session::get('psychotest_schedule_id');
        if (!$scheduleId) {
            return redirect()->route('psychotest.test.login')->with('error', __('Please login first.'));
        }

        $sessionId = $request->session_id;
        $session = PsychotestSession::findOrFail($sessionId);

        if ($session->schedule_id != $scheduleId) {
            return redirect()->route('psychotest.test.login')->with('error', __('Invalid session.'));
        }

        $this->completeSession($session);
        return $this->moveToNextTest($session->schedule);
    }

    private function completeSession($session)
    {
        $session->complete();
        $this->calculateSessionResults($session);
    }

    private function moveToNextTest($schedule)
    {
        $nextSession = $schedule->getNextSession();
        
        if ($nextSession) {
            \Log::info('Moving to next test: ' . $nextSession->category->name);
            return redirect()->route('psychotest.test.category', $nextSession->category->code);
        } else {
            \Log::info('All tests completed');
            $this->completeAllTests($schedule);
            Session::forget('psychotest_schedule_id');
            return redirect()->route('psychotest.test.completed');
        }
    }

    private function completeAllTests($schedule)
    {
        $schedule->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        $this->calculateFinalResults($schedule);
    }

    private function calculateEPPSPoints($question, $answer)
    {
        // EPPS menggunakan forced choice, jadi setiap jawaban mendapat poin penuh
        // Scoring EPPS lebih kompleks dan biasanya melibatkan analisis dimensi
        // Untuk sekarang, beri poin penuh untuk setiap jawaban
        return $question->points;
    }

    private function calculateEPPSSessionResults($session)
    {
        $answers = $session->answers;
        $category = $session->category;
        
        $totalQuestions = $category->questions()->active()->count();
        $answeredQuestions = $answers->count();
        $totalPoints = $category->questions()->active()->sum('points');
        $earnedPoints = $answers->sum('points_earned');

        // EPPS specific scoring - analyze by dimensions
        $dimensionScores = [];
        
        // If you have dimension mapping in your questions, analyze here
        // For now, use basic completion scoring
        $completionRate = $totalQuestions > 0 ? ($answeredQuestions / $totalQuestions) * 100 : 0;

        $sessionData = $session->session_data ?? [];
        $sessionData['results'] = [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'completion_rate' => round($completionRate, 2),
            'dimension_scores' => $dimensionScores,
            'test_type' => 'epps'
        ];

        $session->update(['session_data' => $sessionData]);
    }

    private function calculateKraeplinScore($statistics)
    {
        $totalAnswers = $statistics['total_answers'] ?? 0;
        $correctAnswers = $statistics['correct_answers'] ?? 0;
        $completedColumns = $statistics['completed_columns'] ?? 0;

        if ($totalAnswers == 0) {
            return 0;
        }

        $accuracy = ($correctAnswers / $totalAnswers) * 100;
        $speed = $totalAnswers; // Jumlah jawaban sebagai indikator kecepatan
        $consistency = $this->calculateColumnConsistency($statistics['column_scores'] ?? []);

        // Formula Kraeplin score (bisa disesuaikan dengan kebutuhan)
        // 40% accuracy + 40% speed + 20% consistency
        $maxSpeed = $completedColumns * 30; // Maksimal jawaban
        $speedScore = $maxSpeed > 0 ? ($speed / $maxSpeed) * 100 : 0;
        
        $kraeplinScore = ($accuracy * 0.4) + ($speedScore * 0.4) + ($consistency * 0.2);
        
        return round($kraeplinScore, 2);
    }

    private function generateKraeplinData($category)
    {
        $columns = $category->getKraeplinColumns() ?: 10;
        $rowsPerColumn = 30;
        $data = [];

        for ($col = 0; $col < $columns; $col++) {
            $columnData = [];
            for ($row = 0; $row < $rowsPerColumn; $row++) {
                $columnData[] = rand(1, 9);
            }
            $data[] = $columnData;
        }

        return $data;
    }

    public function completed()
    {
        return view('psychotest.test.completed');
    }

    public function logout()
    {
        Session::forget('psychotest_schedule_id');
        return redirect()->route('psychotest.test.login')->with('success', __('You have been logged out.'));
    }

    private function getCategoryType($categoryId)
    {
        $category = PsychotestCategory::find($categoryId);
        if (!$category) return 'standard';
        
        // Mapping berdasarkan code dari database
        switch ($category->code) {
            case 'visual_sequence':
                return 'visual_sequence';
            case 'basic_math':
                return 'basic_math';
            case 'synonym_antonym':
                return 'synonym_antonym';
            case 'kraeplin':
                return 'kraeplin';
            case 'field_test':
                return 'field_test';
            case 'epps_test':
                return 'epps_test';
            default:
                return 'standard';
        }
    }

    // ... method login, authenticate, start, startTest tetap sama ...

    private function calculatePoints($question, $answer)
    {
        $categoryType = $this->getCategoryType($question->category_id);
        
        switch ($categoryType) {
            case 'visual_sequence':    // Deret Gambar
            case 'basic_math':         // Matematika Dasar
            case 'synonym_antonym':    // Penalaran Verbal
                // Test dengan jawaban benar/salah - nilai berdasarkan ketepatan jawaban
                return $answer === $question->correct_answer ? $question->points : 0;
                
            case 'field_test':         // Tes Bidang (Audit/Tax/Accounting)
                // Test bidang audit/tax/accounting - nilai berdasarkan ketepatan jawaban
                return $answer === $question->correct_answer ? $question->points : 0;
                
            case 'kraeplin':           // Kraeplin
                // Kraeplin dinilai berdasarkan kecepatan dan konsistensi, bukan ketepatan
                // Semua jawaban diberi poin penuh
                return $question->points;
                
            case 'epps_test':          // EPPS
                // EPPS tidak ada jawaban benar/salah, semua pilihan valid
                return $question->points;
                
            default:
                return $answer === $question->correct_answer ? $question->points : 0;
        }
    }

    private function calculateSessionResults($session)
    {
        $categoryType = $this->getCategoryType($session->category_id);
        
        switch ($categoryType) {
            case 'kraeplin':
                $this->calculateKraeplinResults($session);
                break;
            case 'epps_test':
                $this->calculateEPPSResults($session);
                break;
            case 'field_test':
                $this->calculateFieldTestResults($session);
                break;
            default:
                $this->calculateStandardResults($session);
                break;
        }
    }

    /**
     * Penilaian untuk Kraeplin Test (category_id 4)
     * Yang dinilai: Kecepatan, Akurasi, Konsistensi, Konsentrasi
     */
    private function calculateKraeplinResults($session)
    {
        $sessionData = $session->session_data ?? [];
        $kraeplinData = $sessionData['kraeplin'] ?? [];
        $statistics = $kraeplinData['statistics'] ?? [];

        $totalAnswers = $statistics['total_answers'] ?? 0;
        $correctAnswers = $statistics['correct_answers'] ?? 0;
        $completedColumns = $statistics['completed_columns'] ?? 0;
        $columnScores = $statistics['column_scores'] ?? [];

        // Hitung indikator-indikator Kraeplin
        $accuracy = $totalAnswers > 0 ? ($correctAnswers / $totalAnswers) * 100 : 0;
        $speed = $totalAnswers; // Total jawaban sebagai indikator kecepatan
        $consistency = $this->calculateColumnConsistency($columnScores);
        $concentration = $this->calculateConcentrationScore($columnScores);
        
        // Formula penilaian Kraeplin standar psikologi
        $maxSpeed = $completedColumns * 30; // 30 soal per kolom standar Kraeplin
        $speedScore = $maxSpeed > 0 ? min(100, ($speed / $maxSpeed) * 100) : 0;
        
        // Bobot penilaian Kraeplin: 25% Akurasi, 35% Kecepatan, 25% Konsistensi, 15% Konsentrasi
        $kraeplinScore = ($accuracy * 0.25) + ($speedScore * 0.35) + ($consistency * 0.25) + ($concentration * 0.15);
        
        // Grade berdasarkan standar Kraeplin
        $grade = $this->getKraeplinGrade($kraeplinScore, $speed, $accuracy);

        $sessionData['results'] = [
            'total_questions' => $completedColumns * 30,
            'answered_questions' => $totalAnswers,
            'total_points' => $completedColumns * 30,
            'earned_points' => $correctAnswers,
            'percentage' => round($kraeplinScore, 2),
            'accuracy' => round($accuracy, 2),
            'speed_score' => round($speedScore, 2),
            'consistency_score' => round($consistency, 2),
            'concentration_score' => round($concentration, 2),
            'kraeplin_score' => round($kraeplinScore, 2),
            'kraeplin_grade' => $grade,
            'completed_columns' => $completedColumns,
            'column_scores' => $columnScores,
            'interpretation' => $this->getKraeplinInterpretation($kraeplinScore, $speed, $accuracy, $consistency),
            'recommendation' => $this->getKraeplinRecommendation($grade, $speed, $accuracy, $consistency)
        ];

        $session->update(['session_data' => $sessionData]);
    }

    /**
     * Penilaian untuk EPPS Test (category_id 6)
     * Berdasarkan analisis soal yang ada, menggunakan 15 dimensi Murray's needs
     */
    private function calculateEPPSResults($session)
    {
        $answers = $session->answers;
        $category = $session->category;
        
        $totalQuestions = $category->questions()->active()->count();
        $answeredQuestions = $answers->count();
        
        // 15 Dimensi EPPS berdasarkan data soal yang ada
        $eppsDimensions = [
            'achievement' => 0,      // Kebutuhan berprestasi
            'affiliation' => 0,      // Kebutuhan bergaul
            'dominance' => 0,        // Kebutuhan memimpin
            'deference' => 0,        // Kebutuhan menghormati otoritas
            'autonomy' => 0,         // Kebutuhan mandiri
            'succorance' => 0,       // Kebutuhan dibantu
            'order' => 0,            // Kebutuhan keteraturan
            'change' => 0,           // Kebutuhan berubah
            'intraception' => 0,     // Kebutuhan memahami orang lain
            'exhibition' => 0,       // Kebutuhan tampil menonjol
            'nurturance' => 0,       // Kebutuhan merawat
            'aggression' => 0,       // Kebutuhan agresif
            'endurance' => 0,        // Kebutuhan bertahan
            'abasement' => 0,        // Kebutuhan merendah
            'heterosexuality' => 0   // Kebutuhan hubungan lawan jenis
        ];

        // Mapping berdasarkan analisis soal EPPS yang Anda berikan
        $eppsMapping = $this->getEPPSMappingFromActualQuestions();

        // Proses jawaban EPPS
        foreach ($answers as $answer) {
            $questionOrder = $answer->question->order;
            $selectedOption = $answer->answer;
            
            if (isset($eppsMapping[$questionOrder])) {
                $dimension = $eppsMapping[$questionOrder][$selectedOption] ?? null;
                if ($dimension && isset($eppsDimensions[$dimension])) {
                    $eppsDimensions[$dimension]++;
                }
            }
        }

        // Konversi ke percentile untuk interpretasi yang lebih akurat
        $totalResponses = array_sum($eppsDimensions);
        $percentileScores = [];
        
        foreach ($eppsDimensions as $dimension => $rawScore) {
            $percentile = $totalResponses > 0 ? ($rawScore / $totalResponses) * 100 : 0;
            $percentileScores[$dimension] = round($percentile, 2);
        }
        
        // Analisis kepribadian berdasarkan skor
        $personalityAnalysis = $this->analyzeEPPSPersonality($percentileScores);
        
        // Generate insights yang lebih detail
        $detailedInsights = $this->generateEPPSDetailedInsights($percentileScores, $personalityAnalysis);
        
        $completionRate = $totalQuestions > 0 ? ($answeredQuestions / $totalQuestions) * 100 : 0;

        $sessionData = $session->session_data ?? [];
        $sessionData['results'] = [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'completion_rate' => round($completionRate, 2),
            'raw_scores' => $eppsDimensions,
            'percentile_scores' => $percentileScores,
            'dimension_scores' => $percentileScores, // Untuk kompatibilitas
            'dominant_traits' => $personalityAnalysis['dominant_traits'],
            'secondary_traits' => $personalityAnalysis['secondary_traits'],
            'low_traits' => $personalityAnalysis['low_traits'],
            'personality_profile' => $personalityAnalysis['profile_description'],
            'work_style_analysis' => $personalityAnalysis['work_style'],
            'leadership_potential' => $personalityAnalysis['leadership_potential'],
            'team_compatibility' => $personalityAnalysis['team_compatibility'],
            'recruitment_recommendation' => $detailedInsights['recruitment_recommendations'],
            'position_recommendation' => $detailedInsights['position_recommendations'],
            'development_areas' => $detailedInsights['development_areas'],
            'strengths_analysis' => $detailedInsights['strengths_analysis'],
            'percentage' => round($completionRate, 2),
            'total_points' => $totalQuestions,
            'earned_points' => $answeredQuestions,
            'detailed_interpretation' => $detailedInsights['detailed_interpretation']
        ];

        $session->update(['session_data' => $sessionData]);
    }

    private function getEPPSMappingFromActualQuestions()
    {
        // Mapping berdasarkan analisis soal EPPS yang Anda berikan
        return [
            // Soal 1-5: Achievement vs Affiliation
            1 => ['A' => 'achievement', 'B' => 'affiliation'],
            2 => ['A' => 'achievement', 'B' => 'affiliation'], 
            3 => ['A' => 'achievement', 'B' => 'affiliation'],
            4 => ['A' => 'achievement', 'B' => 'affiliation'],
            5 => ['A' => 'achievement', 'B' => 'affiliation'],
            
            // Soal 6-10: Dominance vs Deference
            6 => ['A' => 'dominance', 'B' => 'deference'],
            7 => ['A' => 'dominance', 'B' => 'deference'],
            8 => ['A' => 'dominance', 'B' => 'deference'],
            9 => ['A' => 'dominance', 'B' => 'deference'],
            10 => ['A' => 'dominance', 'B' => 'deference'],
            
            // Soal 11-15: Autonomy vs Succorance
            11 => ['A' => 'autonomy', 'B' => 'succorance'],
            12 => ['A' => 'autonomy', 'B' => 'succorance'],
            13 => ['A' => 'autonomy', 'B' => 'succorance'],
            14 => ['A' => 'autonomy', 'B' => 'succorance'],
            15 => ['A' => 'autonomy', 'B' => 'succorance'],
            
            // Soal 16-20: Order vs Change
            16 => ['A' => 'order', 'B' => 'change'],
            17 => ['A' => 'order', 'B' => 'change'],
            18 => ['A' => 'order', 'B' => 'change'],
            19 => ['A' => 'order', 'B' => 'change'],
            20 => ['A' => 'order', 'B' => 'change'],
            
            // Soal 21-25: Intraception vs Exhibition
            21 => ['A' => 'intraception', 'B' => 'exhibition'],
            22 => ['A' => 'intraception', 'B' => 'exhibition'],
            23 => ['A' => 'intraception', 'B' => 'exhibition'],
            24 => ['A' => 'intraception', 'B' => 'exhibition'],
            25 => ['A' => 'intraception', 'B' => 'exhibition'],
            
            // Soal 26-30: Nurturance vs Aggression
            26 => ['A' => 'nurturance', 'B' => 'aggression'],
            27 => ['A' => 'nurturance', 'B' => 'aggression'],
            28 => ['A' => 'nurturance', 'B' => 'aggression'],
            29 => ['A' => 'nurturance', 'B' => 'aggression'],
            30 => ['A' => 'nurturance', 'B' => 'aggression'],
            
            // Soal 31-35: Endurance vs Abasement
            31 => ['A' => 'endurance', 'B' => 'abasement'],
            32 => ['A' => 'endurance', 'B' => 'abasement'],
            33 => ['A' => 'endurance', 'B' => 'abasement'],
            34 => ['A' => 'endurance', 'B' => 'abasement'],
            35 => ['A' => 'endurance', 'B' => 'abasement'],
            
            // Soal 36-40: Heterosexuality vs Intraception
            36 => ['A' => 'heterosexuality', 'B' => 'intraception'],
            37 => ['A' => 'heterosexuality', 'B' => 'intraception'],
            38 => ['A' => 'heterosexuality', 'B' => 'intraception'],
            39 => ['A' => 'heterosexuality', 'B' => 'intraception'],
            40 => ['A' => 'heterosexuality', 'B' => 'intraception'],
            
            // Soal 41-45: Exhibition vs Autonomy
            41 => ['A' => 'exhibition', 'B' => 'autonomy'],
            42 => ['A' => 'exhibition', 'B' => 'autonomy'],
            43 => ['A' => 'exhibition', 'B' => 'autonomy'],
            44 => ['A' => 'exhibition', 'B' => 'autonomy'],
            45 => ['A' => 'exhibition', 'B' => 'autonomy'],
            
            // Soal 46-50: Change vs Order
            46 => ['A' => 'change', 'B' => 'order'],
            47 => ['A' => 'change', 'B' => 'order'],
            48 => ['A' => 'change', 'B' => 'order'],
            49 => ['A' => 'change', 'B' => 'order'],
            50 => ['A' => 'change', 'B' => 'order'],
            
            // Soal 51-55: Achievement vs Nurturance
            51 => ['A' => 'achievement', 'B' => 'nurturance'],
            52 => ['A' => 'achievement', 'B' => 'nurturance'],
            53 => ['A' => 'achievement', 'B' => 'nurturance'],
            54 => ['A' => 'achievement', 'B' => 'nurturance'],
            55 => ['A' => 'achievement', 'B' => 'nurturance'],
            
            // Soal 56-60: Dominance vs Abasement
            56 => ['A' => 'dominance', 'B' => 'abasement'],
            57 => ['A' => 'dominance', 'B' => 'abasement'],
            58 => ['A' => 'dominance', 'B' => 'abasement'],
            59 => ['A' => 'dominance', 'B' => 'abasement'],
            60 => ['A' => 'dominance', 'B' => 'abasement'],
            
            // Soal 61-65: Autonomy vs Affiliation
            61 => ['A' => 'autonomy', 'B' => 'affiliation'],
            62 => ['A' => 'autonomy', 'B' => 'affiliation'],
            63 => ['A' => 'autonomy', 'B' => 'affiliation'],
            64 => ['A' => 'autonomy', 'B' => 'affiliation'],
            65 => ['A' => 'autonomy', 'B' => 'affiliation'],
            
            // Soal 66-70: Order vs Intraception
            66 => ['A' => 'order', 'B' => 'intraception'],
            67 => ['A' => 'order', 'B' => 'intraception'],
            68 => ['A' => 'order', 'B' => 'intraception'],
            69 => ['A' => 'order', 'B' => 'intraception'],
            70 => ['A' => 'order', 'B' => 'intraception'],
            
            // Soal 71-75: Exhibition vs Succorance
            71 => ['A' => 'exhibition', 'B' => 'succorance'],
            72 => ['A' => 'exhibition', 'B' => 'succorance'],
            73 => ['A' => 'exhibition', 'B' => 'succorance'],
            74 => ['A' => 'exhibition', 'B' => 'succorance'],
            75 => ['A' => 'exhibition', 'B' => 'succorance'],
            
            // Soal 76-80: Change vs Endurance
            76 => ['A' => 'change', 'B' => 'endurance'],
            77 => ['A' => 'change', 'B' => 'endurance'],
            78 => ['A' => 'change', 'B' => 'endurance'],
            79 => ['A' => 'change', 'B' => 'endurance'],
            80 => ['A' => 'change', 'B' => 'endurance'],
            
            // Soal 81-85: Heterosexuality vs Achievement
            81 => ['A' => 'heterosexuality', 'B' => 'achievement'],
            82 => ['A' => 'heterosexuality', 'B' => 'achievement'],
            83 => ['A' => 'heterosexuality', 'B' => 'achievement'],
            84 => ['A' => 'heterosexuality', 'B' => 'achievement'],
            85 => ['A' => 'heterosexuality', 'B' => 'achievement'],
            
            // Soal 86-90: Aggression vs Deference
            86 => ['A' => 'aggression', 'B' => 'deference'],
            87 => ['A' => 'aggression', 'B' => 'deference'],
            88 => ['A' => 'aggression', 'B' => 'deference'],
            89 => ['A' => 'aggression', 'B' => 'deference'],
            90 => ['A' => 'aggression', 'B' => 'deference'],
            
            // Soal 91-95: Nurturance vs Order
            91 => ['A' => 'nurturance', 'B' => 'order'],
            92 => ['A' => 'nurturance', 'B' => 'order'],
            93 => ['A' => 'nurturance', 'B' => 'order'],
            94 => ['A' => 'nurturance', 'B' => 'order'],
            95 => ['A' => 'nurturance', 'B' => 'order'],
            
            // Soal 96-100: Intraception vs Change
            96 => ['A' => 'intraception', 'B' => 'change'],
            97 => ['A' => 'intraception', 'B' => 'change'],
            98 => ['A' => 'intraception', 'B' => 'change'],
            99 => ['A' => 'intraception', 'B' => 'change'],
            100 => ['A' => 'intraception', 'B' => 'change'],
        ];
    }

    private function analyzeEPPSPersonality($percentileScores)
    {
        // Tentukan traits dominan (≥ 15%)
        $dominantTraits = [];
        $secondaryTraits = [];
        $lowTraits = [];
        
        foreach ($percentileScores as $dimension => $score) {
            if ($score >= 15) {
                $dominantTraits[] = $this->getDimensionDisplayName($dimension);
            } elseif ($score >= 8) {
                $secondaryTraits[] = $this->getDimensionDisplayName($dimension);
            } else {
                $lowTraits[] = $this->getDimensionDisplayName($dimension);
            }
        }
        
        // Analisis pola kepribadian
        $profileDescription = $this->generateProfileDescription($percentileScores);
        $workStyle = $this->analyzeWorkStyle($percentileScores);
        $leadershipPotential = $this->analyzeLeadershipPotential($percentileScores);
        $teamCompatibility = $this->analyzeTeamCompatibility($percentileScores);
        
        return [
            'dominant_traits' => array_slice($dominantTraits, 0, 3),
            'secondary_traits' => array_slice($secondaryTraits, 0, 3),
            'low_traits' => array_slice($lowTraits, 0, 3),
            'profile_description' => $profileDescription,
            'work_style' => $workStyle,
            'leadership_potential' => $leadershipPotential,
            'team_compatibility' => $teamCompatibility
        ];
    }

    private function getDimensionDisplayName($dimension)
    {
        $names = [
            'achievement' => 'Berorientasi Prestasi',
            'affiliation' => 'Berorientasi Hubungan Sosial',
            'dominance' => 'Kepemimpinan & Kontrol',
            'deference' => 'Menghormati Otoritas',
            'autonomy' => 'Kemandirian',
            'succorance' => 'Membutuhkan Dukungan',
            'order' => 'Keteraturan & Sistematis',
            'change' => 'Suka Perubahan & Variasi',
            'intraception' => 'Memahami Orang Lain',
            'exhibition' => 'Tampil Menonjol',
            'nurturance' => 'Merawat & Membimbing',
            'aggression' => 'Asertif & Kompetitif',
            'endurance' => 'Ketekunan & Persistensi',
            'abasement' => 'Rendah Hati',
            'heterosexuality' => 'Orientasi Heteroseksual'
        ];
        
        return $names[$dimension] ?? ucfirst($dimension);
    }

    private function generateProfileDescription($scores)
    {
        $descriptions = [];
        
        // Analisis berdasarkan kombinasi skor tertinggi
        $topThree = collect($scores)->sortDesc()->take(3)->keys()->toArray();
        
        // Pola Achievement + Dominance
        if (in_array('achievement', $topThree) && in_array('dominance', $topThree)) {
            $descriptions[] = "Memiliki jiwa kepemimpinan yang kuat dengan orientasi prestasi tinggi";
        }
        
        // Pola Affiliation + Nurturance
        if (in_array('affiliation', $topThree) && in_array('nurturance', $topThree)) {
            $descriptions[] = "Sangat berorientasi pada hubungan interpersonal dan senang membantu orang lain";
        }
        
        // Pola Order + Endurance
        if (in_array('order', $topThree) && in_array('endurance', $topThree)) {
            $descriptions[] = "Memiliki karakteristik pekerja yang teliti, sistematis, dan tekun";
        }
        
        // Pola Autonomy + Change
        if (in_array('autonomy', $topThree) && in_array('change', $topThree)) {
            $descriptions[] = "Menyukai kebebasan dalam bekerja dan terbuka terhadap inovasi";
        }
        
        // Pola Exhibition + Aggression
        if (in_array('exhibition', $topThree) && in_array('aggression', $topThree)) {
            $descriptions[] = "Memiliki kepercayaan diri tinggi dan tidak takut berkompetisi";
        }
        
        if (empty($descriptions)) {
            $descriptions[] = "Memiliki profil kepribadian yang seimbang dengan kecenderungan pada " . 
                            $this->getDimensionDisplayName($topThree[0]);
        }
        
        return implode('. ', $descriptions);
    }

    private function analyzeWorkStyle($scores)
    {
        $workStyle = [];
        
        if ($scores['order'] >= 12) {
            $workStyle[] = "Menyukai lingkungan kerja yang terstruktur dan prosedur yang jelas";
        }
        
        if ($scores['autonomy'] >= 12) {
            $workStyle[] = "Bekerja lebih baik dengan kebebasan dan minimal supervisi";
        }
        
        if ($scores['affiliation'] >= 12) {
            $workStyle[] = "Lebih produktif dalam setting tim dan kolaborasi";
        }
        
        if ($scores['achievement'] >= 12) {
            $workStyle[] = "Termotivasi oleh target dan tantangan yang tinggi";
        }
        
        if ($scores['change'] >= 12) {
            $workStyle[] = "Menikmati variasi tugas dan proyek yang berbeda-beda";
        }
        
        if ($scores['endurance'] >= 12) {
            $workStyle[] = "Mampu bertahan pada tugas jangka panjang yang membutuhkan konsistensi";
        }
        
        return $workStyle;
    }

    private function analyzeLeadershipPotential($scores)
    {
        $leadershipScore = 0;
        $analysis = [];
        
        // Faktor positif untuk kepemimpinan
        if ($scores['dominance'] >= 15) {
            $leadershipScore += 25;
            $analysis[] = "Memiliki keinginan kuat untuk memimpin dan mengarahkan";
        }
        
        if ($scores['achievement'] >= 12) {
            $leadershipScore += 20;
            $analysis[] = "Berorientasi pada hasil dan pencapaian target";
        }
        
        if ($scores['exhibition'] >= 10) {
            $leadershipScore += 15;
            $analysis[] = "Percaya diri dalam tampil di depan orang banyak";
        }
        
        if ($scores['aggression'] >= 10) {
            $leadershipScore += 15;
            $analysis[] = "Berani mengambil keputusan tegas";
        }
        
        if ($scores['autonomy'] >= 12) {
            $leadershipScore += 10;
            $analysis[] = "Mandiri dalam pengambilan keputusan";
        }
        
        // Faktor yang bisa mengurangi potensi kepemimpinan
        if ($scores['deference'] >= 15) {
            $leadershipScore -= 15;
            $analysis[] = "Cenderung mengikuti arahan daripada memberikan arahan";
        }
        
        if ($scores['abasement'] >= 12) {
            $leadershipScore -= 10;
            $analysis[] = "Mungkin kurang percaya diri dalam memimpin";
        }
        
        $level = 'Rendah';
        if ($leadershipScore >= 70) $level = 'Sangat Tinggi';
        elseif ($leadershipScore >= 50) $level = 'Tinggi';
        elseif ($leadershipScore >= 30) $level = 'Sedang';
        
        return [
            'level' => $level,
            'score' => $leadershipScore,
            'analysis' => $analysis
        ];
    }

    private function analyzeTeamCompatibility($scores)
    {
        $compatibility = [];
        
        if ($scores['affiliation'] >= 12) {
            $compatibility[] = "Sangat baik dalam membangun hubungan dengan rekan tim";
        }
        
        if ($scores['nurturance'] >= 10) {
            $compatibility[] = "Cenderung mendukung dan membantu anggota tim lain";
        }
        
        if ($scores['deference'] >= 10) {
            $compatibility[] = "Menghormati hierarki dan struktur tim";
        }
        
        if ($scores['aggression'] >= 15) {
            $compatibility[] = "Mungkin terlalu kompetitif dalam setting tim";
        }
        
        if ($scores['autonomy'] >= 15) {
            $compatibility[] = "Lebih suka bekerja independen daripada bergantung pada tim";
        }
        
        if ($scores['exhibition'] >= 15) {
            $compatibility[] = "Mungkin terlalu mendominasi diskusi tim";
        }
        
        return $compatibility;
    }

    private function generateEPPSDetailedInsights($percentileScores, $personalityAnalysis)
    {
        $insights = [
            'recruitment_recommendations' => [],
            'position_recommendations' => [],
            'development_areas' => [],
            'strengths_analysis' => [],
            'detailed_interpretation' => []
        ];
        
        // Recruitment Recommendations berdasarkan profil
        if (in_array('Kepemimpinan & Kontrol', $personalityAnalysis['dominant_traits'])) {
            $insights['recruitment_recommendations'][] = "Sangat cocok untuk posisi manajerial dan kepemimpinan";
        }
        
        if (in_array('Berorientasi Prestasi', $personalityAnalysis['dominant_traits'])) {
            $insights['recruitment_recommendations'][] = "Ideal untuk posisi yang membutuhkan pencapaian target tinggi";
        }
        
        if (in_array('Berorientasi Hubungan Sosial', $personalityAnalysis['dominant_traits'])) {
            $insights['recruitment_recommendations'][] = "Excellet untuk posisi customer service, HR, atau sales";
        }
        
        if (in_array('Keteraturan & Sistematis', $personalityAnalysis['dominant_traits'])) {
            $insights['recruitment_recommendations'][] = "Sangat sesuai untuk posisi administratif, quality control, atau auditing";
        }
        
        // Position Recommendations
        $leadershipLevel = $personalityAnalysis['leadership_potential']['level'];
        
        if ($leadershipLevel == 'Sangat Tinggi' || $leadershipLevel == 'Tinggi') {
            $insights['position_recommendations'][] = "Manager/Supervisor Level";
            $insights['position_recommendations'][] = "Team Leader";
            $insights['position_recommendations'][] = "Project Manager";
        } else {
            $insights['position_recommendations'][] = "Individual Contributor";
            $insights['position_recommendations'][] = "Specialist/Expert Role";
            $insights['position_recommendations'][] = "Support Function";
        }
        
        // Development Areas
        if ($percentileScores['dominance'] < 8) {
            $insights['development_areas'][] = "Pengembangan kepercayaan diri dalam memimpin";
        }
        
        if ($percentileScores['achievement'] < 8) {
            $insights['development_areas'][] = "Peningkatan motivasi berprestasi";
        }
        
        if ($percentileScores['affiliation'] < 6) {
            $insights['development_areas'][] = "Peningkatan kemampuan interpersonal";
        }
        
        if ($percentileScores['order'] < 6) {
            $insights['development_areas'][] = "Peningkatan kemampuan organisasi dan planning";
        }
        
        // Strengths Analysis
        $topStrengths = collect($percentileScores)->sortDesc()->take(3);
        
        foreach ($topStrengths as $dimension => $score) {
            $strengthDescription = $this->getStrengthDescription($dimension, $score);
            $insights['strengths_analysis'][] = $strengthDescription;
        }
        
        // Detailed Interpretation
        $insights['detailed_interpretation'] = [
            'personality_summary' => $personalityAnalysis['profile_description'],
            'work_environment_fit' => $this->analyzeWorkEnvironmentFit($percentileScores),
            'career_development_path' => $this->suggestCareerPath($percentileScores),
            'potential_challenges' => $this->identifyPotentialChallenges($percentileScores),
            'motivational_factors' => $this->identifyMotivationalFactors($percentileScores)
        ];
        
        return $insights;
    }

    private function getStrengthDescription($dimension, $score)
    {
        $descriptions = [
            'achievement' => "Sangat termotivasi untuk mencapai prestasi terbaik (skor: {$score}%)",
            'affiliation' => "Excellent dalam membangun dan memelihara hubungan interpersonal (skor: {$score}%)",
            'dominance' => "Memiliki potensi kepemimpinan yang kuat (skor: {$score}%)",
            'deference' => "Sangat menghormati otoritas dan mengikuti aturan dengan baik (skor: {$score}%)",
            'autonomy' => "Sangat mandiri dan dapat bekerja tanpa supervisi ketat (skor: {$score}%)",
            'succorance' => "Baik dalam mencari dukungan ketika diperlukan (skor: {$score}%)",
            'order' => "Sangat sistematis dan terorganisir dalam bekerja (skor: {$score}%)",
            'change' => "Sangat adaptif dan menyukai tantangan baru (skor: {$score}%)",
            'intraception' => "Excellent dalam memahami motivasi dan perasaan orang lain (skor: {$score}%)",
            'exhibition' => "Percaya diri dalam presentasi dan tampil di depan umum (skor: {$score}%)",
            'nurturance' => "Sangat peduli dan senang membantu pengembangan orang lain (skor: {$score}%)",
            'aggression' => "Sangat kompetitif dan asertif dalam mencapai tujuan (skor: {$score}%)",
            'endurance' => "Sangat tekun dan persistent dalam menyelesaikan tugas (skor: {$score}%)",
            'abasement' => "Rendah hati dan tidak sombong (skor: {$score}%)",
            'heterosexuality' => "Memiliki orientasi heteroseksual yang sehat (skor: {$score}%)"
        ];
        
        return $descriptions[$dimension] ?? "Kuat dalam aspek {$dimension} (skor: {$score}%)";
    }

    private function analyzeWorkEnvironmentFit($scores)
    {
        $environments = [];
        
        if ($scores['order'] >= 12 && $scores['endurance'] >= 10) {
            $environments[] = "Lingkungan korporat dengan struktur yang jelas";
        }
        
        if ($scores['change'] >= 12 && $scores['autonomy'] >= 10) {
            $environments[] = "Startup atau perusahaan yang dinamis";
        }
        
        if ($scores['affiliation'] >= 12 && $scores['nurturance'] >= 10) {
            $environments[] = "Organisasi dengan budaya kolaboratif";
        }
        
        if ($scores['achievement'] >= 12 && $scores['aggression'] >= 10) {
            $environments[] = "Lingkungan yang kompetitif dengan target yang jelas";
        }
        
        return $environments;
    }

    private function suggestCareerPath($scores)
    {
        $paths = [];
        
        // Management Track
        if ($scores['dominance'] >= 12 && $scores['achievement'] >= 10) {
            $paths[] = "Management/Leadership Track";
        }
        
        // Specialist Track
        if ($scores['endurance'] >= 12 && $scores['order'] >= 10) {
            $paths[] = "Technical/Specialist Track";
        }
        
        // People Development Track
        if ($scores['nurturance'] >= 12 && $scores['intraception'] >= 10) {
            $paths[] = "HR/People Development Track";
        }
        
        // Business Development Track
        if ($scores['exhibition'] >= 12 && $scores['achievement'] >= 10) {
            $paths[] = "Sales/Business Development Track";
        }
        
        return $paths;
    }

    private function identifyPotentialChallenges($scores)
    {
        $challenges = [];
        
        if ($scores['aggression'] >= 15) {
            $challenges[] = "Mungkin terlalu kompetitif dalam situasi kolaboratif";
        }
        
        if ($scores['autonomy'] >= 15) {
            $challenges[] = "Mungkin kesulitan bekerja dalam tim yang sangat terstruktur";
        }
        
        if ($scores['exhibition'] >= 15) {
            $challenges[] = "Mungkin terlalu fokus pada pengakuan daripada hasil kerja";
        }
        
        if ($scores['order'] >= 15) {
            $challenges[] = "Mungkin kesulitan beradaptasi dengan perubahan mendadak";
        }
        
        if ($scores['deference'] >= 15) {
            $challenges[] = "Mungkin kurang inisiatif dalam mengambil keputusan mandiri";
        }
        
        return $challenges;
    }

    private function identifyMotivationalFactors($scores)
    {
        $factors = [];
        
        if ($scores['achievement'] >= 10) {
            $factors[] = "Target dan tantangan yang menantang";
        }
        
        if ($scores['affiliation'] >= 10) {
            $factors[] = "Lingkungan kerja yang harmonis dan supportif";
        }
        
        if ($scores['exhibition'] >= 10) {
            $factors[] = "Pengakuan dan apresiasi atas kontribusi";
        }
        
        if ($scores['autonomy'] >= 10) {
            $factors[] = "Kebebasan dalam mengatur cara kerja";
        }
        
        if ($scores['nurturance'] >= 10) {
            $factors[] = "Kesempatan untuk mengembangkan orang lain";
        }
        
        return $factors;
    }

    /**
     * Penilaian untuk Test Bidang (category_id 5)
     * Analisis berdasarkan soal audit, accounting, dan tax
     */
    private function calculateFieldTestResults($session)
    {
        $answers = $session->answers;
        $category = $session->category;
        
        // Berdasarkan analisis soal, kategorikan berdasarkan nomor urut
        $fieldMapping = [
            'audit' => [1,2,3,4,5,6,7,8,9,10], // soal 1-10 audit
            'tax' => [11,12,13,14,15,16,17,18,19,20], // soal 11-20 tax  
            'accounting' => [21,22,23,24,25,26,27,28,29,30] // soal 21-30 accounting
        ];
        
        $fieldScores = [
            'audit' => ['correct' => 0, 'total' => 0],
            'tax' => ['correct' => 0, 'total' => 0],
            'accounting' => ['correct' => 0, 'total' => 0]
        ];

        foreach ($answers as $answer) {
            $question = $answer->question;
            $questionOrder = $question->order;
            
            // Tentukan field berdasarkan nomor urut soal
            $field = $this->getFieldByOrder($questionOrder, $fieldMapping);
            
            if ($field && isset($fieldScores[$field])) {
                $fieldScores[$field]['total']++;
                if ($answer->answer === $question->correct_answer) {
                    $fieldScores[$field]['correct']++;
                }
            }
        }

        // Hitung persentase untuk setiap bidang
        $fieldPercentages = [];
        foreach ($fieldScores as $field => $scores) {
            $fieldPercentages[$field] = $scores['total'] > 0 ? 
                round(($scores['correct'] / $scores['total']) * 100, 2) : 0;
        }

        // Tentukan rekomendasi divisi berdasarkan skor tertinggi
        arsort($fieldPercentages);
        $recommendedField = array_keys($fieldPercentages)[0];
        $highestScore = $fieldPercentages[$recommendedField];
        
        // Rekomendasi divisi berdasarkan skor
        $divisionRecommendation = $this->getDivisionRecommendation($fieldPercentages);

        $totalQuestions = $category->questions()->active()->count();
        $answeredQuestions = $answers->count();
        $correctAnswers = $answers->where('points_earned', '>', 0)->count();
        $overallPercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

        $sessionData = $session->session_data ?? [];
        $sessionData['results'] = [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'correct_answers' => $correctAnswers,
            'percentage' => $overallPercentage,
            'field_scores' => $fieldScores,
            'field_percentages' => $fieldPercentages,
            'recommended_field' => $recommendedField,
            'highest_field_score' => $highestScore,
            'division_recommendation' => $divisionRecommendation,
            'field_analysis' => $this->getFieldAnalysis($fieldPercentages),
            'total_points' => $totalQuestions,
            'earned_points' => $correctAnswers
        ];

        $session->update(['session_data' => $sessionData]);
    }

    /**
     * Penilaian untuk test standar (Deret Gambar, Matematika Dasar, Penalaran Verbal)
     * category_id 1, 2, 3
     */
    private function calculateStandardResults($session)
    {
        $answers = $session->answers;
        $category = $session->category;
        
        $totalQuestions = $category->questions()->active()->count();
        $answeredQuestions = $answers->count();
        $correctAnswers = $answers->where('points_earned', '>', 0)->count();
        $totalPoints = $category->questions()->active()->sum('points');
        $earnedPoints = $answers->sum('points_earned');
        $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;

        // Interpretasi berdasarkan tipe kategori
        $interpretation = $this->getStandardTestInterpretation($session->category_id, $percentage);

        $sessionData = $session->session_data ?? [];
        $sessionData['results'] = [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'correct_answers' => $correctAnswers,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'percentage' => round($percentage, 2),
            'accuracy_rate' => $answeredQuestions > 0 ? round(($correctAnswers / $answeredQuestions) * 100, 2) : 0,
            'interpretation' => $interpretation,
            'grade' => $this->calculateGrade($percentage)
        ];

        $session->update(['session_data' => $sessionData]);
    }

    // =============== HELPER METHODS ===============

    // Helper untuk Kraeplin
    private function calculateColumnConsistency($columnScores)
    {
        if (empty($columnScores)) return 0;

        $totals = array_column($columnScores, 'total');
        if (empty($totals)) return 0;

        $mean = array_sum($totals) / count($totals);
        $variance = 0;
        
        foreach ($totals as $total) {
            $variance += pow($total - $mean, 2);
        }
        
        $variance = $variance / count($totals);
        $standardDeviation = sqrt($variance);
        
        // Konsistensi: semakin kecil SD, semakin konsisten
        $maxAllowedSD = 5;
        $consistencyScore = max(0, 100 - (($standardDeviation / $maxAllowedSD) * 100));
        
        return round($consistencyScore, 2);
    }

    private function calculateConcentrationScore($columnScores)
    {
        if (empty($columnScores)) return 0;
        
        $totals = array_column($columnScores, 'total');
        $columnsCount = count($totals);
        
        if ($columnsCount < 3) return 0;
        
        // Cek penurunan performa (indikator konsentrasi menurun)
        $firstThird = array_slice($totals, 0, intval($columnsCount / 3));
        $lastThird = array_slice($totals, -intval($columnsCount / 3));
        
        $firstAvg = array_sum($firstThird) / count($firstThird);
        $lastAvg = array_sum($lastThird) / count($lastThird);
        
        // Skor konsentrasi: semakin stabil/meningkat, semakin baik
        $concentrationScore = 100 - max(0, (($firstAvg - $lastAvg) / $firstAvg) * 100);
        
        return round($concentrationScore, 2);
    }

    private function getKraeplinGrade($score, $speed, $accuracy)
    {
        if ($score >= 85 && $speed >= 200 && $accuracy >= 90) return 'A';
        if ($score >= 75 && $speed >= 150 && $accuracy >= 80) return 'B'; 
        if ($score >= 65 && $speed >= 100 && $accuracy >= 70) return 'C';
        if ($score >= 55 && $speed >= 50 && $accuracy >= 60) return 'D';
        return 'E';
    }

    private function getKraeplinInterpretation($score, $speed, $accuracy, $consistency)
    {
        $interpretations = [];
        
        if ($speed >= 200) $interpretations[] = "Kecepatan kerja sangat baik - mampu bekerja dengan tempo tinggi";
        elseif ($speed >= 150) $interpretations[] = "Kecepatan kerja baik - produktivitas di atas rata-rata";
        elseif ($speed >= 100) $interpretations[] = "Kecepatan kerja cukup - sesuai standar normal";
        else $interpretations[] = "Kecepatan kerja perlu ditingkatkan";
        
        if ($accuracy >= 90) $interpretations[] = "Tingkat akurasi sangat tinggi - teliti dan cermat";
        elseif ($accuracy >= 80) $interpretations[] = "Tingkat akurasi baik - cukup teliti";
        elseif ($accuracy >= 70) $interpretations[] = "Tingkat akurasi cukup - perlu peningkatan ketelitian";
        else $interpretations[] = "Perlu peningkatan akurasi secara signifikan";
        
        if ($consistency >= 80) $interpretations[] = "Konsistensi kerja sangat baik - stabil dalam performa";
        elseif ($consistency >= 70) $interpretations[] = "Konsistensi kerja baik - relatif stabil";
        else $interpretations[] = "Perlu peningkatan konsistensi kerja";
        
        return $interpretations;
    }

    private function getKraeplinRecommendation($grade, $speed, $accuracy, $consistency)
    {
        $recommendations = [];
        
        if ($grade == 'A') {
            $recommendations[] = "Sangat cocok untuk pekerjaan yang membutuhkan konsentrasi tinggi dan kecepatan";
            $recommendations[] = "Dapat diandalkan untuk tugas-tugas dengan deadline ketat";
        } elseif ($grade == 'B') {
            $recommendations[] = "Cocok untuk pekerjaan administratif dan analisis data";
            $recommendations[] = "Dapat berkembang dengan training tambahan";
        } elseif ($grade == 'C') {
            $recommendations[] = "Perlu pelatihan untuk meningkatkan kecepatan dan akurasi";
            $recommendations[] = "Cocok untuk pekerjaan dengan supervisi";
        } else {
            $recommendations[] = "Perlu training intensif sebelum penempatan";
            $recommendations[] = "Disarankan untuk posisi yang tidak membutuhkan kecepatan tinggi";
        }
        
        return $recommendations;
    }

    // Helper untuk EPPS  
    private function mapEPPSAnswer($questionOrder, $selectedOption, &$eppsDimensions)
    {
        // Mapping berdasarkan analisis soal EPPS yang ada
        $mappingTable = [
            // Achievement vs Affiliation (soal 1)
            1 => ['A' => 'achievement', 'B' => 'affiliation'],
            // Achievement vs Nurturance (soal 40)
            40 => ['A' => 'achievement', 'B' => 'nurturance'],
            // Dominance vs Deference (soal 6)
            6 => ['A' => 'dominance', 'B' => 'deference'],
            // Autonomy vs Succorance (soal 11)  
            11 => ['A' => 'autonomy', 'B' => 'succorance'],
            // Order vs Change (soal 16)
            16 => ['A' => 'order', 'B' => 'change'],
            // Intraception vs Exhibition (soal 21)
            21 => ['A' => 'intraception', 'B' => 'exhibition'],
            // Nurturance vs Aggression (soal 26)
            26 => ['A' => 'nurturance', 'B' => 'aggression'],
            // Endurance vs Abasement (soal 31)
            31 => ['A' => 'endurance', 'B' => 'abasement'],
            // Heterosexuality vs Intraception (soal 36)
            36 => ['A' => 'heterosexuality', 'B' => 'intraception'],
            // Dan seterusnya untuk semua 100 soal...
        ];
        
        // Jika tidak ada mapping spesifik, bagi rata ke semua dimensi
        if (isset($mappingTable[$questionOrder])) {
            $dimension = $mappingTable[$questionOrder][$selectedOption] ?? null;
            if ($dimension && isset($eppsDimensions[$dimension])) {
                $eppsDimensions[$dimension]++;
            }
        } else {
            // Default mapping untuk soal yang belum dimapping
            $dimensionKeys = array_keys($eppsDimensions);
            $randomDimension = $dimensionKeys[($questionOrder + ord($selectedOption)) % count($dimensionKeys)];
            $eppsDimensions[$randomDimension]++;
        }
    }

    private function generateEPPSProfile($scores)
    {
        $profile = [];
        
        foreach ($scores as $dimension => $score) {
            if ($score >= 20) $profile[$dimension] = 'Tinggi';
            elseif ($score >= 10) $profile[$dimension] = 'Sedang';
            else $profile[$dimension] = 'Rendah';
        }
        
        return $profile;
    }

    private function getEPPSRecruitmentRecommendation($scores)
    {
        $recommendations = [];
        
        // Leadership potential
        if ($scores['dominance'] >= 20 && $scores['achievement'] >= 15) {
            $recommendations[] = "Menunjukkan potensi kepemimpinan yang baik";
        }
        
        // Team player
        if ($scores['affiliation'] >= 20 && $scores['deference'] >= 10) {
            $recommendations[] = "Cocok untuk bekerja dalam tim";
        }
        
        // Detail oriented  
        if ($scores['order'] >= 20 && $scores['endurance'] >= 15) {
            $recommendations[] = "Baik untuk pekerjaan yang membutuhkan ketelitian";
        }
        
        // Independent worker
        if ($scores['autonomy'] >= 20) {
            $recommendations[] = "Dapat bekerja mandiri dengan baik";
        }
        
        return $recommendations;
    }

    private function getEPPSPositionRecommendation($scores)
    {
        $recommendations = [];
        
        // Analisis kombinasi skor untuk rekomendasi posisi
        if ($scores['achievement'] >= 15 && $scores['dominance'] >= 15) {
            $recommendations['management'] = "Cocok untuk posisi managerial";
        }
        
        if ($scores['order'] >= 20 && $scores['endurance'] >= 15) {
            $recommendations['administrative'] = "Cocok untuk posisi administratif";
        }
        
        if ($scores['affiliation'] >= 20 && $scores['nurturance'] >= 15) {
            $recommendations['support'] = "Cocok untuk posisi customer service/HR";
        }
        
        return $recommendations;
    }

    // Helper untuk Field Test
    private function getFieldByOrder($order, $fieldMapping)
    {
        foreach ($fieldMapping as $field => $orders) {
            if (in_array($order, $orders)) {
                return $field;
            }
        }
        return null;
    }

    private function getDivisionRecommendation($fieldPercentages)
    {
        arsort($fieldPercentages);
        $topField = array_keys($fieldPercentages)[0];
        $topScore = $fieldPercentages[$topField];
        
        $recommendations = [
            'primary' => $this->getFieldDivisionName($topField),
            'reason' => "Skor tertinggi pada bidang {$topField}: {$topScore}%",
            'confidence' => $this->getRecommendationConfidence($topScore)
        ];
        
        // Tambahkan alternatif jika skor cukup tinggi
        $recommendations['alternatives'] = [];
        foreach ($fieldPercentages as $field => $score) {
            if ($field != $topField && $score >= 60) {
                $recommendations['alternatives'][] = $this->getFieldDivisionName($field) . " (Skor: {$score}%)";
            }
        }
        
        return $recommendations;
    }

    private function getFieldDivisionName($field)
    {
        $names = [
            'audit' => 'Divisi Audit',
            'tax' => 'Divisi Tax/Perpajakan', 
            'accounting' => 'Divisi Accounting/Keuangan'
        ];
        
        return $names[$field] ?? 'Divisi ' . ucfirst($field);
    }

    private function getRecommendationConfidence($score)
    {
        if ($score >= 80) return 'Sangat Tinggi';
        if ($score >= 70) return 'Tinggi';
        if ($score >= 60) return 'Cukup';
        return 'Rendah';
    }

    private function getFieldAnalysis($fieldPercentages)
    {
        $analysis = [];
        
        foreach ($fieldPercentages as $field => $score) {
            if ($score >= 80) {
                $analysis[$field] = "Sangat menguasai bidang {$field}";
            } elseif ($score >= 70) {
                $analysis[$field] = "Menguasai bidang {$field} dengan baik";
            } elseif ($score >= 60) {
                $analysis[$field] = "Cukup menguasai bidang {$field}";
            } else {
                $analysis[$field] = "Perlu peningkatan di bidang {$field}";
            }
        }
        
        return $analysis;
    }

    // Helper untuk Standard Test
    private function getStandardTestInterpretation($categoryId, $percentage)
    {
        $testNames = [
            1 => 'Deret Gambar (Visual Sequence)',
            2 => 'Matematika Dasar (Basic Math)',  
            3 => 'Penalaran Verbal (Synonym/Antonym)'
        ];
        
        $testName = $testNames[$categoryId] ?? 'Test Standar';
        
        if ($percentage >= 80) {
            return "Sangat baik dalam {$testName} - menunjukkan kemampuan yang unggul";
        } elseif ($percentage >= 70) {
            return "Baik dalam {$testName} - kemampuan di atas rata-rata";
        } elseif ($percentage >= 60) {
            return "Cukup dalam {$testName} - kemampuan sesuai standar";
        } else {
            return "Perlu peningkatan dalam {$testName}";
        }
    }

    // Update calculateFinalResults untuk menangani semua jenis test
    private function calculateFinalResults($schedule)
    {
        $sessions = $schedule->sessions()->where('status', 'completed')->get();
        
        $overallResults = [
            'total_questions' => 0,
            'answered_questions' => 0,
            'total_points' => 0,
            'earned_points' => 0,
            'category_results' => [],
            'recommendations' => [],
            'personality_insights' => null,
            'field_recommendations' => null,
            'kraeplin_analysis' => null
        ];

        foreach ($sessions as $session) {
            $results = $session->session_data['results'] ?? [];
            $categoryType = $this->getCategoryType($session->category_id);
            
            $overallResults['total_questions'] += $results['total_questions'] ?? 0;
            $overallResults['answered_questions'] += $results['answered_questions'] ?? 0;
            $overallResults['total_points'] += $results['total_points'] ?? 0;
            $overallResults['earned_points'] += $results['earned_points'] ?? 0;

            // Store category-specific results
            $categoryResult = [
                'name' => $session->category->name,
                'type' => $categoryType,
                'results' => $results
            ];

            // Special handling for different test types
            switch ($categoryType) {
                case 'kraeplin':
                    $overallResults['kraeplin_analysis'] = [
                        'grade' => $results['kraeplin_grade'] ?? 'N/A',
                        'interpretation' => $results['interpretation'] ?? [],
                        'recommendation' => $results['recommendation'] ?? [],
                        'scores' => [
                            'accuracy' => $results['accuracy'] ?? 0,
                            'speed_score' => $results['speed_score'] ?? 0,
                            'consistency_score' => $results['consistency_score'] ?? 0,
                            'concentration_score' => $results['concentration_score'] ?? 0
                        ]
                    ];
                    break;
                    
                case 'epps_test':
                    $overallResults['personality_insights'] = [
                        'dominant_traits' => $results['dominant_traits'] ?? [],
                        'personality_profile' => $results['personality_profile'] ?? [],
                        'recruitment_recommendation' => $results['recruitment_recommendation'] ?? [],
                        'position_recommendation' => $results['position_recommendation'] ?? [],
                        'dimension_scores' => $results['dimension_scores'] ?? []
                    ];
                    break;
                    
                case 'field_test':
                    $overallResults['field_recommendations'] = [
                        'recommended_field' => $results['recommended_field'] ?? 'General',
                        'division_recommendation' => $results['division_recommendation'] ?? [],
                        'field_percentages' => $results['field_percentages'] ?? [],
                        'field_analysis' => $results['field_analysis'] ?? []
                    ];
                    break;
            }

            $overallResults['category_results'][$session->category_id] = $categoryResult;
        }

        $overallPercentage = $overallResults['total_points'] > 0 ? 
            ($overallResults['earned_points'] / $overallResults['total_points']) * 100 : 0;

        // Generate overall recommendations
        $overallResults['recommendations'] = $this->generateOverallRecommendations($overallResults);
        
        // Generate final assessment
        $finalAssessment = $this->generateFinalAssessment($overallResults);

        PsychotestResult::updateOrCreate(
            ['schedule_id' => $schedule->id],
            [
                'total_questions' => $overallResults['total_questions'],
                'answered_questions' => $overallResults['answered_questions'],
                'total_points' => $overallResults['total_points'],
                'earned_points' => $overallResults['earned_points'],
                'percentage' => round($overallPercentage, 2),
                'grade' => $this->calculateGrade($overallPercentage),
                'category_scores' => $overallResults['category_results'],
                'notes' => json_encode([
                    'recommendations' => $overallResults['recommendations'],
                    'final_assessment' => $finalAssessment,
                    'personality_insights' => $overallResults['personality_insights'],
                    'field_recommendations' => $overallResults['field_recommendations'],
                    'kraeplin_analysis' => $overallResults['kraeplin_analysis']
                ])
            ]
        );
    }

    private function generateOverallRecommendations($results)
    {
        $recommendations = [];
        
        // Rekomendasi berdasarkan test bidang
        if (isset($results['field_recommendations']['division_recommendation']['primary'])) {
            $recommendations[] = "Rekomendasi Divisi: " . $results['field_recommendations']['division_recommendation']['primary'];
            $recommendations[] = "Alasan: " . $results['field_recommendations']['division_recommendation']['reason'];
        }
        
        // Rekomendasi berdasarkan EPPS
        if (isset($results['personality_insights']['recruitment_recommendation'])) {
            $recommendations = array_merge($recommendations, $results['personality_insights']['recruitment_recommendation']);
        }
        
        // Rekomendasi berdasarkan Kraeplin
        if (isset($results['kraeplin_analysis']['recommendation'])) {
            $recommendations = array_merge($recommendations, $results['kraeplin_analysis']['recommendation']);
        }
        
        // Rekomendasi berdasarkan test kognitif (visual, math, verbal)
        $cognitivePerformance = [];
        foreach ($results['category_results'] as $categoryId => $categoryResult) {
            $categoryType = $this->getCategoryType($categoryId);
            if (in_array($categoryType, ['visual_sequence', 'basic_math', 'synonym_antonym'])) {
                $cognitivePerformance[] = $categoryResult['results']['percentage'] ?? 0;
            }
        }
        
        if (!empty($cognitivePerformance)) {
            $avgCognitive = array_sum($cognitivePerformance) / count($cognitivePerformance);
            if ($avgCognitive >= 80) {
                $recommendations[] = "Kemampuan kognitif sangat baik, cocok untuk posisi yang membutuhkan analisis kompleks";
            } elseif ($avgCognitive >= 70) {
                $recommendations[] = "Kemampuan kognitif baik, dapat berkembang dengan pelatihan tambahan";
            } elseif ($avgCognitive >= 60) {
                $recommendations[] = "Kemampuan kognitif cukup, perlu bimbingan dalam tugas-tugas analitis";
            } else {
                $recommendations[] = "Perlu pelatihan intensif untuk meningkatkan kemampuan kognitif";
            }
        }
        
        return array_unique($recommendations);
    }

    private function generateFinalAssessment($results)
    {
        $assessment = [
            'overall_rating' => $this->getOverallRating($results),
            'strengths' => [],
            'areas_for_improvement' => [],
            'position_fit' => $this->getPositionFit($results),
            'development_needs' => []
        ];
        
        // Analisis kekuatan
        foreach ($results['category_results'] as $categoryId => $categoryResult) {
            $percentage = $categoryResult['results']['percentage'] ?? 0;
            $category = PsychotestCategory::find($categoryId);
            $categoryName = $category ? $category->name : 'Unknown Test';
            
            if ($percentage >= 80) {
                $assessment['strengths'][] = "Unggul dalam {$categoryName}";
            } elseif ($percentage < 60) {
                $assessment['areas_for_improvement'][] = "Perlu peningkatan dalam {$categoryName}";
            }
        }
        
        // Analisis berdasarkan Kraeplin
        if (isset($results['kraeplin_analysis'])) {
            $kraeplin = $results['kraeplin_analysis'];
            if ($kraeplin['grade'] == 'A' || $kraeplin['grade'] == 'B') {
                $assessment['strengths'][] = "Konsentrasi dan kecepatan kerja baik";
            } else {
                $assessment['development_needs'][] = "Pelatihan untuk meningkatkan konsentrasi dan kecepatan";
            }
        }
        
        // Analisis berdasarkan EPPS
        if (isset($results['personality_insights']['dominant_traits'])) {
            $dominantTraits = $results['personality_insights']['dominant_traits'];
            $assessment['personality_highlights'] = array_slice($dominantTraits, 0, 3);
        }
        
        return $assessment;
    }

    private function getOverallRating($results)
    {
        $totalScore = 0;
        $categoryCount = 0;
        
        foreach ($results['category_results'] as $categoryResult) {
            $percentage = $categoryResult['results']['percentage'] ?? 0;
            $totalScore += $percentage;
            $categoryCount++;
        }
        
        if ($categoryCount == 0) return 'Tidak Tersedia';
        
        $avgScore = $totalScore / $categoryCount;
        
        if ($avgScore >= 85) return 'Sangat Baik';
        if ($avgScore >= 75) return 'Baik';
        if ($avgScore >= 65) return 'Cukup';
        if ($avgScore >= 55) return 'Kurang';
        return 'Sangat Kurang';
    }

    private function getPositionFit($results)
    {
        $fit = [];
        
        // Berdasarkan field test
        if (isset($results['field_recommendations']['recommended_field'])) {
            $field = $results['field_recommendations']['recommended_field'];
            $confidence = $results['field_recommendations']['division_recommendation']['confidence'] ?? 'Sedang';
            $fit['division'] = "Cocok untuk {$field} dengan confidence {$confidence}";
        }
        
        // Berdasarkan EPPS
        if (isset($results['personality_insights']['position_recommendation'])) {
            $fit['position_type'] = $results['personality_insights']['position_recommendation'];
        }
        
        return $fit;
    }

    private function getCategoryDisplayName($categoryId)
    {
        $category = PsychotestCategory::find($categoryId);
        return $category ? $category->name : 'Unknown Test';
    }

    private function calculateGrade($percentage)
    {
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }
}
