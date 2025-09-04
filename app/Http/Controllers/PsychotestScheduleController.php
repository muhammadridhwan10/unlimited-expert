<?php
// app/Http/Controllers/PsychotestScheduleController.php - Updated with Multiple Candidate Support
namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\PsychotestSchedule;
use App\Models\PsychotestCategory;
use App\Models\PsychotestQuestion;
use App\Models\PsychotestAnswer;
use App\Models\PsychotestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PsychotestScheduled;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PsychotestScheduleController extends Controller
{
    public function index()
    {
        $user = \Auth::user();
        if ($user->type == 'admin') {
            $schedules = PsychotestSchedule::with(['candidates', 'candidates.jobs'])->get();
        } elseif ($user->type == 'company') {
            $schedules = PsychotestSchedule::with(['candidates', 'candidates.jobs'])->get();
        } else {
            $schedules = PsychotestSchedule::where('created_by', \Auth::user()->creatorId())
                ->with(['candidates', 'candidates.jobs'])->get();
        }

        return view('psychotest.index', compact('schedules'));
    }

    public function create($candidateId = null)
    {
        $user = \Auth::user();
        if ($user->type == 'admin') {
            $candidates = JobApplication::where('stage', 2)->with('jobs')->get();
        } elseif ($user->type == 'company') {
            $candidates = JobApplication::where('stage', 2)->with('jobs')->get();
        } else {
            $candidates = JobApplication::where('created_by', \Auth::user()->creatorId())->where('stage', 2)
                ->with('jobs')->get();
        }

        // Get all categories for selection
        $categories = PsychotestCategory::active()->ordered()->get();

        return view('psychotest.create', compact('candidates', 'candidateId', 'categories'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'candidates' => 'required|array|min:1',
                'candidates.*' => 'exists:job_applications,id',
                'start_time' => 'required|date|after:now',
                'end_time' => 'required|date|after:start_time',
                'duration_minutes' => 'required|integer|min:15|max:300',
                'selected_categories' => 'nullable|array',
                'selected_categories.*' => 'exists:psychotest_categories,id',
                'auto_select_by_job' => 'boolean',
                'selection_mode' => 'required|in:single,multiple,all',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $candidateIds = $request->candidates;
        
        // Check if any candidate already has active schedule
        $existingSchedules = PsychotestSchedule::whereIn('candidate', $candidateIds)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->with('candidates')
            ->get();

        if ($existingSchedules->count() > 0) {
            $candidateNames = $existingSchedules->pluck('candidates.name')->implode(', ');
            return redirect()->back()->with('error', __('Some candidates already have active psychotest schedules: ') . $candidateNames);
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        DB::beginTransaction();
        
        try {
            foreach ($candidateIds as $candidateId) {
                $candidate = JobApplication::find($candidateId);
                
                if (!$candidate) {
                    $errorCount++;
                    continue;
                }

                // Determine selected categories for each candidate
                $selectedCategories = $this->determineCategories($request, $candidate);

                // Generate unique username and password
                $username = strtolower(str_replace(' ', '', $candidate->name)) . '@' . rand(1000, 9999) . '@gmail.com';
                $password = strtoupper(Str::random(6)) . rand(10, 99);

                $schedule = PsychotestSchedule::create([
                    'candidate' => $candidateId,
                    'username' => $username,
                    'password' => $password,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'duration_minutes' => $request->duration_minutes,
                    'selected_categories' => $selectedCategories,
                    'instructions' => null,
                    'created_by' => \Auth::user()->id,
                ]);

                // Send email notification
                try {
                    Mail::to($candidate->email)->send(new \App\Mail\PsychotestScheduled($schedule, $password));
                    $schedule->update(['email_sent' => true]);
                    $successCount++;
                } catch (\Exception $e) {
                    \Log::error('Failed to send psychotest email to ' . $candidate->email . ': ' . $e->getMessage());
                    $errors[] = 'Failed to send email to ' . $candidate->name;
                    $successCount++; // Still count as success since schedule was created
                }
            }

            DB::commit();

            $message = __('Successfully created :count psychotest schedules.', ['count' => $successCount]);
            if (!empty($errors)) {
                $message .= ' ' . __('However, some emails failed to send: ') . implode(', ', $errors);
            }

            return redirect()->route('psychotest-schedule.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Failed to create psychotest schedules: ' . $e->getMessage());
            return redirect()->back()->with('error', __('Failed to create psychotest schedules. Please try again.'));
        }
    }

    private function determineCategories($request, $candidate)
    {
        $selectedCategories = null;
        
        if ($request->has('auto_select_by_job') && $request->auto_select_by_job) {
            // Auto select based on job title
            $jobTitle = $candidate->jobs->title ?? '';
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
                ->pluck('id')
                ->toArray();
            
            $selectedCategories = $categories;
        } elseif ($request->selected_categories && !empty($request->selected_categories)) {
            // Use manually selected categories
            $selectedCategories = $request->selected_categories;
        }

        return $selectedCategories;
    }

    // Rest of the existing methods remain the same...
    public function show($id)
    {
        $schedule = PsychotestSchedule::with(['candidates', 'candidates.jobs', 'answers.question', 'result'])
            ->findOrFail($id);
        
        // Get categories for this schedule
        $categories = $schedule->getCategories();
        
        return view('psychotest.show', compact('schedule', 'categories'));
    }

    public function edit($id)
    {
        $schedule = PsychotestSchedule::findOrFail($id);
        
        if ($schedule->status != 'scheduled') {
            return redirect()->back()->with('error', __('Cannot edit schedule that is not in scheduled status.'));
        }

        $user = \Auth::user();
        if ($user->type == 'admin') {
            $candidates = JobApplication::with('jobs')->get();
        } elseif ($user->type == 'company') {
            $candidates = JobApplication::with('jobs')->get();
        } else {
            $candidates = JobApplication::where('created_by', \Auth::user()->creatorId())
                ->with('jobs')->get();
        }

        // Get all categories for selection
        $categories = PsychotestCategory::active()->ordered()->get();

        return view('psychotest.edit', compact('schedule', 'candidates', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $schedule = PsychotestSchedule::findOrFail($id);

        if ($schedule->status != 'scheduled') {
            return redirect()->back()->with('error', __('Cannot update schedule that is not in scheduled status.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'duration_minutes' => 'required|integer|min:15|max:300',
                'selected_categories' => 'nullable|array',
                'selected_categories.*' => 'exists:psychotest_categories,id',
                'auto_select_by_job' => 'boolean',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        // Determine selected categories
        $selectedCategories = $this->determineCategories($request, $schedule->candidates);

        $schedule->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration_minutes' => $request->duration_minutes,
            'selected_categories' => $selectedCategories,
        ]);

        return redirect()->route('psychotest-schedule.index')
            ->with('success', __('Psychotest schedule successfully updated.'));
    }

    public function destroy($id)
    {
        $schedule = PsychotestSchedule::findOrFail($id);

        if ($schedule->status == 'completed') {
            return redirect()->back()->with('error', __('Cannot delete completed psychotest.'));
        }

        $schedule->delete();

        return redirect()->route('psychotest-schedule.index')
            ->with('success', __('Psychotest schedule successfully deleted.'));
    }

    public function cancel($id)
    {
        $schedule = PsychotestSchedule::findOrFail($id);

        if (in_array($schedule->status, ['completed', 'cancelled'])) {
            return redirect()->back()->with('error', __('Cannot cancel this psychotest.'));
        }

        $schedule->update(['status' => 'cancelled']);

        return redirect()->route('psychotest-schedule.index')
            ->with('success', __('Psychotest schedule successfully cancelled.'));
    }

    public function resendEmail($id)
    {
        $schedule = PsychotestSchedule::with('candidates')->findOrFail($id);

        if ($schedule->status != 'scheduled') {
            return redirect()->back()->with('error', __('Can only resend email for scheduled tests.'));
        }

        // Generate new password
        $newPassword = Str::random(8);
        $schedule->update(['password' => $newPassword]);

        try {
            Mail::to($schedule->candidates->email)->send(new \App\Mail\PsychotestScheduled($schedule, $newPassword));
            $schedule->update(['email_sent' => true]);

            return redirect()->back()->with('success', __('Email successfully resent with new credentials.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Failed to send email. Please try again.'));
        }
    }

    // Get categories for specific candidate/job (AJAX)
    public function getCategoriesForCandidate($candidateId)
    {
        try {
            if (!$candidateId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Candidate ID is required'
                ], 400);
            }

            $candidate = \App\Models\JobApplication::with('jobs')->find($candidateId);
            
            if (!$candidate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Candidate not found'
                ], 404);
            }

            $jobTitle = $candidate->jobs->title ?? '';
            
            // Get all categories
            $allCategories = \App\Models\PsychotestCategory::where('is_active', true)
                ->orderBy('order')
                ->get();
            
            $applicableCategoryIds = $this->getApplicableCategoryIds($jobTitle);

            return response()->json([
                'success' => true,
                'candidate' => [
                    'name' => $candidate->name,
                    'job_title' => $jobTitle
                ],
                'all_categories' => $allCategories->map(function($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'description' => $cat->description,
                        'duration_minutes' => $cat->duration_minutes,
                        'total_questions' => $cat->total_questions,
                        'is_job_specific' => $cat->is_job_specific ?? false,
                        'type' => $cat->type
                    ];
                }),
                'applicable_categories' => $applicableCategoryIds,
                'has_field_specific' => $this->hasFieldSpecificCategories($jobTitle)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting categories for candidate: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    // New method to get categories for multiple candidates
    public function getCategoriesForMultipleCandidates(Request $request)
    {
        try {
            $candidateIds = $request->candidate_ids;
            
            if (!$candidateIds || !is_array($candidateIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Candidate IDs are required'
                ], 400);
            }

            $candidates = JobApplication::with('jobs')->whereIn('id', $candidateIds)->get();
            
            if ($candidates->count() !== count($candidateIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some candidates not found'
                ], 404);
            }

            // Get all categories
            $allCategories = PsychotestCategory::where('is_active', true)
                ->orderBy('order')
                ->get();
            
            // Analyze job positions
            $jobTitles = $candidates->pluck('jobs.title')->filter()->unique();
            $commonCategories = [];
            $jobSpecificCategories = [];
            
            // Find categories that apply to all job positions
            foreach ($allCategories as $category) {
                if (!$category->is_job_specific) {
                    $commonCategories[] = $category->id;
                } else {
                    // Check if this job-specific category applies to any of the jobs
                    foreach ($jobTitles as $jobTitle) {
                        if ($this->categoryAppliestoJob($category, $jobTitle)) {
                            $jobSpecificCategories[] = $category->id;
                            break;
                        }
                    }
                }
            }

            $applicableCategories = array_unique(array_merge($commonCategories, $jobSpecificCategories));

            return response()->json([
                'success' => true,
                'candidates' => $candidates->map(function($candidate) {
                    return [
                        'id' => $candidate->id,
                        'name' => $candidate->name,
                        'job_title' => $candidate->jobs->title ?? ''
                    ];
                }),
                'job_positions' => $jobTitles->values(),
                'all_categories' => $allCategories->map(function($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'description' => $cat->description,
                        'duration_minutes' => $cat->duration_minutes,
                        'total_questions' => $cat->total_questions,
                        'is_job_specific' => $cat->is_job_specific ?? false,
                        'type' => $cat->type
                    ];
                }),
                'applicable_categories' => $applicableCategories,
                'common_categories' => $commonCategories,
                'job_specific_categories' => $jobSpecificCategories
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting categories for multiple candidates: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getApplicableCategoryIds($jobTitle)
    {
        $applicableCategoryIds = [];
        
        // Add general categories
        $generalCategories = PsychotestCategory::where('is_active', true)
            ->where('is_job_specific', false)
            ->pluck('id')
            ->toArray();
        
        $applicableCategoryIds = array_merge($applicableCategoryIds, $generalCategories);
        
        // Add job-specific categories if applicable
        if ($jobTitle) {
            $jobSpecificCategories = PsychotestCategory::where('is_active', true)
                ->where('is_job_specific', true)
                ->get()
                ->filter(function($category) use ($jobTitle) {
                    return $this->categoryAppliestoJob($category, $jobTitle);
                })
                ->pluck('id')
                ->toArray();
            
            $applicableCategoryIds = array_merge($applicableCategoryIds, $jobSpecificCategories);
        }
        
        return array_unique($applicableCategoryIds);
    }

    private function categoryAppliestoJob($category, $jobTitle)
    {
        if (!$category->target_job_keywords || !$jobTitle) {
            return false;
        }
        
        $jobTitleLower = strtolower($jobTitle);
        $keywords = ['auditor', 'audit', 'tax', 'taxation', 'accounting', 'akuntan', 'perpajakan'];
        
        foreach ($category->target_job_keywords as $targetKeyword) {
            foreach ($keywords as $jobKeyword) {
                if (strpos($jobTitleLower, strtolower($jobKeyword)) !== false && 
                    strtolower($targetKeyword) === strtolower($jobKeyword)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function hasFieldSpecificCategories($jobTitle)
    {
        $generalCategories = PsychotestCategory::where('is_active', true)
            ->where('is_job_specific', false)
            ->pluck('id')
            ->toArray();
        
        $allApplicableCategories = $this->getApplicableCategoryIds($jobTitle);
        
        return count(array_diff($allApplicableCategories, $generalCategories)) > 0;
    }
}