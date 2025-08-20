<?php

namespace App\Http\Controllers;

use App\Models\PsychotestQuestion;
use App\Models\PsychotestCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PsychotestQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $user = Auth::user();
        
        // Build base query based on user type
        $questionsQuery = PsychotestQuestion::with(['category']);
        $categoriesQuery = PsychotestCategory::active();
        
        if ($user->type != 'admin' && $user->type != 'company') {
            $questionsQuery->where('created_by', Auth::user()->creatorId());
            $categoriesQuery->where('created_by', Auth::user()->creatorId());
        }

        // Filter by category if requested
        if ($request->filled('category_id')) {
            $questionsQuery->where('category_id', $request->category_id);
        }

        // Filter by type if requested
        if ($request->filled('type')) {
            $questionsQuery->where('type', $request->type);
        }

        // Search by title or question
        if ($request->filled('search')) {
            $search = $request->search;
            $questionsQuery->where(function($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                      ->orWhere('question', 'like', '%' . $search . '%');
            });
        }

        $questions = $questionsQuery->orderBy('category_id')
                                   ->orderBy('order')
                                   ->paginate(20);
        
        $categories = $categoriesQuery->get();
        $types = PsychotestQuestion::$types;

        return view('psychotest.questions.index', compact('questions', 'categories', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $user = Auth::user();
        
        // Get categories based on user type
        $categoriesQuery = PsychotestCategory::active();
        
        if ($user->type != 'admin' && $user->type != 'company') {
            $categoriesQuery->where('created_by', Auth::user()->creatorId());
        }

        $categories = $categoriesQuery->orderBy('order')->get();
        $types = PsychotestQuestion::$types;
        
        // Pre-select category if provided
        $selectedCategory = $request->get('category');
        
        return view('psychotest.questions.create', compact('types', 'categories', 'selectedCategory'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // Validation rules
        $rules = [
            'category_id' => 'required|exists:psychotest_categories,id',
            'title' => 'required|string|max:255',
            'question' => 'required|string',
            'type' => 'required|in:multiple_choice,essay,rating_scale,true_false,kraeplin,image_choice',
            'points' => 'required|integer|min:1',
            'order' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'time_limit_seconds' => 'nullable|integer|min:1',
        ];

        // Additional validation based on question type
        if (in_array($request->type, ['multiple_choice', 'image_choice'])) {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*'] = 'required|string|max:255';
            $rules['correct_answer'] = 'required|string';
        } elseif ($request->type === 'true_false') {
            $rules['correct_answer'] = 'required|in:True,False';
        } elseif ($request->type === 'rating_scale') {
            $rules['rating_scale'] = 'required|integer|min:2|max:10';
            $rules['correct_answer'] = 'required|integer|min:1';
        } elseif ($request->type === 'kraeplin') {
            $rules['kraeplin_columns'] = 'required|integer|min:5|max:20';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('error', $validator->errors()->first());
        }

        try {
            // Prepare data
            $data = [
                'category_id' => $request->category_id,
                'title' => $request->title,
                'question' => $request->question,
                'type' => $request->type,
                'points' => $request->points,
                'order' => $request->order,
                'time_limit_seconds' => $request->time_limit_seconds,
                'created_by' => Auth::user()->creatorId(),
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                $imageName = $this->handleImageUpload($request->file('image'));
                $data['image'] = $imageName;
            }

            // Handle options and correct answers based on type
            $this->handleQuestionTypeData($request, $data);

            // Create question
            PsychotestQuestion::create($data);

            return redirect()->route('psychotest-question.index')
                           ->with('success', __('Question successfully created.'));

        } catch (\Exception $e) {
            Log::error('Error creating psychotest question: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', __('Error creating question: ') . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $question = PsychotestQuestion::with(['category', 'creator'])->findOrFail($id);
        
        // Check ownership for non-admin users
        $user = Auth::user();
        if ($user->type != 'admin' && $user->type != 'company') {
            if ($question->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }

        return view('psychotest.questions.show', compact('question'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

        $question = PsychotestQuestion::with('category')->findOrFail($id);
        
        // Check ownership for non-admin users
        $user = Auth::user();
        if ($user->type != 'admin' && $user->type != 'company') {
            if ($question->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }

        // Get categories
        $categoriesQuery = PsychotestCategory::active();
        
        if ($user->type != 'admin' && $user->type != 'company') {
            $categoriesQuery->where('created_by', Auth::user()->creatorId());
        }

        $categories = $categoriesQuery->orderBy('order')->get();
        $types = PsychotestQuestion::$types;
        
        return view('psychotest.questions.edit', compact('question', 'types', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $question = PsychotestQuestion::findOrFail($id);
        
        // Check ownership for non-admin users
        $user = Auth::user();
        if ($user->type != 'admin' && $user->type != 'company') {
            if ($question->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }

        // Validation rules
        $rules = [
            'category_id' => 'required|exists:psychotest_categories,id',
            'title' => 'required|string|max:255',
            'question' => 'required|string',
            'type' => 'required|in:multiple_choice,essay,rating_scale,true_false,kraeplin,image_choice',
            'points' => 'required|integer|min:1',
            'order' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'time_limit_seconds' => 'nullable|integer|min:1',
        ];

        // Additional validation based on question type
        if (in_array($request->type, ['multiple_choice', 'image_choice'])) {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*'] = 'required|string|max:255';
            $rules['correct_answer'] = 'required|string';
        } elseif ($request->type === 'true_false') {
            $rules['correct_answer'] = 'required|in:True,False';
        } elseif ($request->type === 'rating_scale') {
            $rules['rating_scale'] = 'required|integer|min:2|max:10';
            $rules['correct_answer'] = 'required|integer|min:1';
        } elseif ($request->type === 'kraeplin') {
            $rules['kraeplin_columns'] = 'required|integer|min:5|max:20';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('error', $validator->errors()->first());
        }

        try {
            // Prepare data
            $data = [
                'category_id' => $request->category_id,
                'title' => $request->title,
                'question' => $request->question,
                'type' => $request->type,
                'points' => $request->points,
                'order' => $request->order,
                'time_limit_seconds' => $request->time_limit_seconds,
            ];

            // Handle image upload/removal
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($question->image) {
                    $this->deleteImage($question->image);
                }
                
                $imageName = $this->handleImageUpload($request->file('image'));
                $data['image'] = $imageName;
            } elseif ($request->has('remove_image') && $request->remove_image) {
                // Remove image if requested
                if ($question->image) {
                    $this->deleteImage($question->image);
                }
                $data['image'] = null;
            }

            // Handle options and correct answers based on type
            $this->handleQuestionTypeData($request, $data);

            // Update question
            $question->update($data);

            return redirect()->route('psychotest-question.index')
                           ->with('success', __('Question successfully updated.'));

        } catch (\Exception $e) {
            Log::error('Error updating psychotest question: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', __('Error updating question: ') . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        $question = PsychotestQuestion::findOrFail($id);
        
        // Check ownership for non-admin users
        $user = Auth::user();
        if ($user->type != 'admin' && $user->type != 'company') {
            if ($question->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }

        // Check if question is used in any test
        if ($question->answers()->exists()) {
            return redirect()->back()->with('error', __('Cannot delete question that has been answered in tests.'));
        }

        try {
            // Delete associated image
            if ($question->image) {
                $this->deleteImage($question->image);
            }

            $question->delete();

            return redirect()->route('psychotest-question.index')
                           ->with('success', __('Question successfully deleted.'));

        } catch (\Exception $e) {
            Log::error('Error deleting psychotest question: ' . $e->getMessage());
            return redirect()->back()->with('error', __('Error deleting question: ') . $e->getMessage());
        }
    }

    /**
     * Toggle question status (active/inactive)
     */
    public function toggleStatus($id)
    {

        $question = PsychotestQuestion::findOrFail($id);
        
        // Check ownership for non-admin users
        $user = Auth::user();
        if ($user->type != 'admin' && $user->type != 'company') {
            if ($question->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }

        $question->update(['is_active' => !$question->is_active]);

        $status = $question->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', __("Question successfully {$status}."));
    }

    /**
     * Get questions by category (AJAX)
     */
    public function getByCategory($categoryId)
    {

        try {
            $user = Auth::user();
            $questionsQuery = PsychotestQuestion::where('category_id', $categoryId)->active()->ordered();
            
            if ($user->type != 'admin' && $user->type != 'company') {
                $questionsQuery->where('created_by', Auth::user()->creatorId());
            }

            $questions = $questionsQuery->get();

            return response()->json([
                'success' => true,
                'questions' => $questions
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching questions'], 500);
        }
    }

    /**
     * Bulk import questions from CSV
     */
    public function import(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:psychotest_categories,id',
            'import_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {
            $file = $request->file('import_file');
            $path = $file->getRealPath();
            $data = array_map('str_getcsv', file($path));
            
            if (empty($data)) {
                return redirect()->back()->with('error', __('CSV file is empty.'));
            }

            $header = array_shift($data); // Remove header row
            $imported = 0;
            $errors = [];
            
            foreach ($data as $rowIndex => $row) {
                try {
                    if (count($row) >= 4) { // Minimum required columns
                        $options = [];
                        $correctAnswer = null;
                        
                        // Parse options (column 5)
                        if (isset($row[4]) && !empty($row[4])) {
                            $optionsString = trim($row[4]);
                            $options = array_map('trim', explode('|', $optionsString));
                        }
                        
                        // Parse correct answer (column 6)
                        if (isset($row[5]) && !empty($row[5])) {
                            $correctAnswer = trim($row[5]);
                        }
                        
                        $questionData = [
                            'category_id' => $request->category_id,
                            'title' => trim($row[0]),
                            'question' => trim($row[1]),
                            'type' => trim($row[2]) ?: 'multiple_choice',
                            'points' => (int)(trim($row[3]) ?: 1),
                            'options' => !empty($options) ? $options : null,
                            'correct_answer' => $correctAnswer,
                            'order' => $imported,
                            'created_by' => Auth::user()->creatorId(),
                        ];

                        PsychotestQuestion::create($questionData);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                }
            }
            
            $message = __("Successfully imported {$imported} questions.");
            if (!empty($errors)) {
                $message .= " " . __("Errors: ") . implode(', ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " " . __("and {count} more.", ['count' => count($errors) - 3]);
                }
            }

            return redirect()->route('psychotest-question.index')
                           ->with('success', $message);
                           
        } catch (\Exception $e) {
            Log::error('Error importing questions: ' . $e->getMessage());
            return redirect()->back()->with('error', __('Import failed: ') . $e->getMessage());
        }
    }

    /**
     * Download sample CSV for import
     */
    public function downloadSample()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample_questions.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'Title',
                'Question', 
                'Type',
                'Points',
                'Options (separated by |)',
                'Correct Answer'
            ]);
            
            // Sample data
            fputcsv($file, [
                'Sample Math Question',
                'What is 2 + 2?',
                'multiple_choice',
                '1',
                '2|3|4|5',
                '4'
            ]);
            
            fputcsv($file, [
                'Sample True/False',
                'The sky is blue.',
                'true_false',
                '1',
                'True|False',
                'True'
            ]);

            fputcsv($file, [
                'Sample Essay',
                'Explain the importance of teamwork.',
                'essay',
                '5',
                '',
                ''
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Preview question (AJAX)
     */
    public function preview(Request $request)
    {
        try {
            $data = $request->all();
            
            // Handle image if uploaded
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = 'preview_' . time() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/uploads/psychotest/temp', $imageName);
                $data['image_url'] = asset('storage/uploads/psychotest/temp/' . $imageName);
            }
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean up temporary files
     */
    public function cleanupTemp()
    {
        try {
            $tempPath = storage_path('app/public/uploads/psychotest/temp');
            
            if (is_dir($tempPath)) {
                $files = glob($tempPath . '/preview_*');
                $now = time();
                $cleaned = 0;
                
                foreach ($files as $file) {
                    if (is_file($file)) {
                        // Delete files older than 1 hour
                        if ($now - filemtime($file) >= 3600) {
                            unlink($file);
                            $cleaned++;
                        }
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'cleaned' => $cleaned
                ]);
            }
            
            return response()->json(['success' => true, 'cleaned' => 0]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle image upload
     */
    private function handleImageUpload($image)
    {
        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        
        // Create directory if not exists
        $uploadPath = 'uploads/psychotest/images';
        $fullPath = storage_path('app/public/' . $uploadPath);
        
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        $image->storeAs('public/' . $uploadPath, $imageName);
        
        return $imageName;
    }

    /**
     * Delete image file
     */
    private function deleteImage($imageName)
    {
        $imagePath = 'public/uploads/psychotest/images/' . $imageName;
        
        if (Storage::exists($imagePath)) {
            Storage::delete($imagePath);
        }
    }

    /**
     * Handle question type specific data
     */
    private function handleQuestionTypeData($request, &$data)
    {
        switch ($request->type) {
            case 'multiple_choice':
            case 'image_choice':
                $data['options'] = array_filter($request->options ?: []);
                $data['correct_answer'] = $request->correct_answer;
                break;
                
            case 'true_false':
                $data['options'] = ['True', 'False'];
                $data['correct_answer'] = $request->correct_answer;
                break;
                
            case 'rating_scale':
                $scale = $request->rating_scale ?: 5;
                $data['options'] = range(1, $scale);
                $data['correct_answer'] = $request->correct_answer;
                break;
                
            case 'kraeplin':
                $columns = $request->kraeplin_columns ?: 10;
                $data['kraeplin_data'] = $this->generateKraeplinData($columns);
                $data['options'] = null;
                $data['correct_answer'] = null;
                break;
                
            case 'essay':
            default:
                $data['options'] = null;
                $data['correct_answer'] = null;
                break;
        }
    }

    /**
     * Generate kraeplin test data
     */
    private function generateKraeplinData($columns)
    {
        $data = [
            'columns' => $columns,
            'rows_per_column' => 50,
            'time_per_column' => 30, // seconds
        ];

        // Generate sample data for preview (first 3 columns, 10 rows each)
        $sampleData = [];
        for ($col = 0; $col < min($columns, 3); $col++) {
            $columnData = [];
            for ($row = 0; $row < 10; $row++) {
                $num1 = rand(1, 9);
                $num2 = rand(1, 9);
                $columnData[] = [
                    'num1' => $num1, 
                    'num2' => $num2, 
                    'sum' => $num1 + $num2
                ];
            }
            $sampleData[] = $columnData;
        }
        
        $data['sample_data'] = $sampleData;
        
        return $data;
    }
}