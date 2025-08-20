<?php
// app/Http/Controllers/PsychotestCategoryController.php - Updated
namespace App\Http\Controllers;

use App\Models\PsychotestCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PsychotestCategoryController extends Controller
{
    public function index()
    {
        $user = \Auth::user();
        if ($user->type == 'admin') {
            $categories = PsychotestCategory::with('questions')->orderBy('order')->get();
        } elseif ($user->type == 'company') {
            $categories = PsychotestCategory::with('questions')->orderBy('order')->get();
        } else {
            $categories = PsychotestCategory::where('created_by', \Auth::user()->creatorId())
                ->with('questions')->orderBy('order')->get();
        }

        return view('psychotest.categories.index', compact('categories'));
    }

    public function create()
    {
        $types = PsychotestCategory::$types;
        return view('psychotest.categories.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:100|unique:psychotest_categories',
                'description' => 'nullable|string',
                'type' => 'required|in:standard,kraeplin,visual,verbal,numeric,field_specific,personality',
                'duration_minutes' => 'required|integer|min:1|max:120',
                'total_questions' => 'required|integer|min:1|max:200',
                'order' => 'required|integer|min:0',
                'is_job_specific' => 'boolean',
                'target_job_keywords' => 'nullable|array',
                'kraeplin_columns' => 'required_if:type,kraeplin|integer|min:5|max:20',
                'time_per_column' => 'required_if:type,kraeplin|integer|min:10|max:60',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        // Prepare settings based on type
        $settings = [];
        if ($request->type === 'kraeplin') {
            $settings = [
                'kraeplin_columns' => $request->kraeplin_columns,
                'time_per_column' => $request->time_per_column,
                'show_instructions' => $request->has('show_instructions'),
            ];
        } elseif ($request->type === 'field_specific') {
            $settings = [
                'difficulty_levels' => ['easy', 'medium', 'hard'],
                'topics' => $request->field_topics ?? ['general'],
                'passing_score' => $request->passing_score ?? 70,
            ];
        } elseif ($request->type === 'personality') {
            $settings = [
                'personality_dimensions' => $request->personality_dimensions ?? [],
                'scoring_method' => $request->scoring_method ?? 'likert_scale',
                'show_progress' => $request->has('show_progress'),
            ];
        }

        PsychotestCategory::create([
            'name' => $request->name,
            'code' => Str::slug($request->code, '_'),
            'description' => $request->description,
            'type' => $request->type,
            'duration_minutes' => $request->duration_minutes,
            'total_questions' => $request->total_questions,
            'order' => $request->order,
            'is_job_specific' => $request->has('is_job_specific'),
            'target_job_keywords' => $request->target_job_keywords,
            'settings' => $settings,
            'created_by' => \Auth::user()->creatorId(),
        ]);

        return redirect()->route('psychotest-category.index')
            ->with('success', __('Test category successfully created.'));
    }

    public function show($id)
    {
        $category = PsychotestCategory::with('questions')->findOrFail($id);
        return view('psychotest.categories.show', compact('category'));
    }

    public function edit($id)
    {
        $category = PsychotestCategory::findOrFail($id);
        $types = PsychotestCategory::$types;
        return view('psychotest.categories.edit', compact('category', 'types'));
    }

    public function update(Request $request, $id)
    {
        $category = PsychotestCategory::findOrFail($id);

        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:100|unique:psychotest_categories,code,' . $id,
                'description' => 'nullable|string',
                'type' => 'required|in:standard,kraeplin,visual,verbal,numeric,field_specific,personality',
                'duration_minutes' => 'required|integer|min:1|max:120',
                'total_questions' => 'required|integer|min:1|max:200',
                'order' => 'required|integer|min:0',
                'is_job_specific' => 'boolean',
                'target_job_keywords' => 'nullable|array',
                'kraeplin_columns' => 'required_if:type,kraeplin|integer|min:5|max:20',
                'time_per_column' => 'required_if:type,kraeplin|integer|min:10|max:60',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        // Prepare settings based on type
        $settings = $category->settings ?? [];
        if ($request->type === 'kraeplin') {
            $settings = [
                'kraeplin_columns' => $request->kraeplin_columns,
                'time_per_column' => $request->time_per_column,
                'show_instructions' => $request->has('show_instructions'),
            ];
        } elseif ($request->type === 'field_specific') {
            $settings = [
                'difficulty_levels' => ['easy', 'medium', 'hard'],
                'topics' => $request->field_topics ?? ['general'],
                'passing_score' => $request->passing_score ?? 70,
            ];
        } elseif ($request->type === 'personality') {
            $settings = [
                'personality_dimensions' => $request->personality_dimensions ?? [],
                'scoring_method' => $request->scoring_method ?? 'likert_scale',
                'show_progress' => $request->has('show_progress'),
            ];
        }

        $category->update([
            'name' => $request->name,
            'code' => Str::slug($request->code, '_'),
            'description' => $request->description,
            'type' => $request->type,
            'duration_minutes' => $request->duration_minutes,
            'total_questions' => $request->total_questions,
            'order' => $request->order,
            'is_job_specific' => $request->has('is_job_specific'),
            'target_job_keywords' => $request->target_job_keywords,
            'settings' => $settings,
        ]);

        return redirect()->route('psychotest-category.index')
            ->with('success', __('Test category successfully updated.'));
    }

    public function destroy($id)
    {
        $category = PsychotestCategory::findOrFail($id);

        // Check if category is used in any active test
        if ($category->sessions()->exists()) {
            return redirect()->back()->with('error', __('Cannot delete category that is used in tests.'));
        }

        $category->delete();

        return redirect()->route('psychotest-category.index')
            ->with('success', __('Test category successfully deleted.'));
    }

    public function toggleStatus($id)
    {
        $category = PsychotestCategory::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', __("Test category successfully {$status}."));
    }

    // Seed default categories including new ones
    public function seedDefaults()
    {
        $defaultCategories = [
            [
                'name' => 'Deret Gambar',
                'code' => 'visual_sequence',
                'description' => 'Tes deret gambar untuk mengukur kemampuan visual dan logika',
                'type' => 'visual',
                'duration_minutes' => 15,
                'total_questions' => 20,
                'order' => 1,
                'is_job_specific' => false,
                'target_job_keywords' => null,
            ],
            [
                'name' => 'Matematika Dasar',
                'code' => 'basic_math',
                'description' => 'Tes matematika dasar untuk mengukur kemampuan numerik',
                'type' => 'numeric',
                'duration_minutes' => 15,
                'total_questions' => 20,
                'order' => 2,
                'is_job_specific' => false,
                'target_job_keywords' => null,
            ],
            [
                'name' => 'Penalaran Verbal',
                'code' => 'synonym_antonym',
                'description' => 'Tes antonim dan sinonim untuk mengukur kemampuan verbal',
                'type' => 'verbal',
                'duration_minutes' => 10,
                'total_questions' => 30,
                'order' => 3,
                'is_job_specific' => false,
                'target_job_keywords' => null,
            ],
            [
                'name' => 'Kraeplin',
                'code' => 'kraeplin',
                'description' => 'Tes kraeplin untuk mengukur konsentrasi dan kecepatan kerja',
                'type' => 'kraeplin',
                'duration_minutes' => 10,
                'total_questions' => 10,
                'order' => 4,
                'is_job_specific' => false,
                'target_job_keywords' => null,
                'settings' => [
                    'kraeplin_columns' => 10,
                    'time_per_column' => 30,
                    'show_instructions' => true,
                ],
            ],
            [
                'name' => 'Tes Bidang (Auditor/Tax/Accounting)',
                'code' => 'field_test',
                'description' => 'Tes khusus untuk menguji kemampuan teknis bidang auditor, perpajakan, dan akuntansi',
                'type' => 'field_specific',
                'duration_minutes' => 15,
                'total_questions' => 20,
                'order' => 5,
                'is_job_specific' => true,
                'target_job_keywords' => ['auditor', 'audit', 'tax', 'taxation', 'accounting', 'akuntan', 'perpajakan'],
                'settings' => [
                    'difficulty_levels' => ['easy', 'medium', 'hard'],
                    'topics' => ['audit_procedures', 'tax_calculation', 'financial_accounting', 'internal_control'],
                    'passing_score' => 70,
                ],
            ],
            [
                'name' => 'EPPS (Edward Personal Preference Schedule)',
                'code' => 'epps_test',
                'description' => 'Tes kepribadian untuk mengukur kebutuhan dan preferensi personal dalam bekerja',
                'type' => 'personality',
                'duration_minutes' => 60,
                'total_questions' => 100,
                'order' => 6,
                'is_job_specific' => false,
                'target_job_keywords' => null,
                'settings' => [
                    'personality_dimensions' => [
                        'achievement', 'deference', 'order', 'exhibition', 'autonomy',
                        'affiliation', 'intraception', 'succorance', 'dominance',
                        'abasement', 'nurturance', 'change', 'endurance', 'heterosexuality', 'aggression'
                    ],
                    'scoring_method' => 'forced_choice',
                    'show_progress' => true,
                ],
            ],
        ];

        foreach ($defaultCategories as $categoryData) {
            PsychotestCategory::firstOrCreate(
                ['code' => $categoryData['code']],
                array_merge($categoryData, ['created_by' => \Auth::user()->creatorId()])
            );
        }

        return redirect()->route('psychotest-category.index')
            ->with('success', __('Default test categories created successfully.'));
    }

    // Get categories for specific job
    public function getCategoriesForJob(Request $request)
    {
        $jobTitle = $request->get('job_title', '');
        
        $categories = PsychotestCategory::active()
            ->ordered()
            ->where(function($query) use ($jobTitle) {
                $query->where('is_job_specific', false);
                
                if ($jobTitle) {
                    $query->orWhere(function($q) use ($jobTitle) {
                        $q->where('is_job_specific', true);
                        
                        $jobTitleLower = strtolower($jobTitle);
                        $keywords = ['auditor', 'audit', 'tax', 'taxation', 'accounting', 'akuntan', 'perpajakan'];
                        
                        foreach ($keywords as $keyword) {
                            if (strpos($jobTitleLower, $keyword) !== false) {
                                $q->whereJsonContains('target_job_keywords', $keyword);
                                break;
                            }
                        }
                    });
                }
            })
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories->map(function($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'description' => $cat->description,
                    'duration_minutes' => $cat->duration_minutes,
                    'total_questions' => $cat->total_questions,
                    'is_job_specific' => $cat->is_job_specific,
                    'type' => $cat->type
                ];
            })
        ]);
    }
}