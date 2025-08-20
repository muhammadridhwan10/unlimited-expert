<?php
// app/Http/Controllers/PsychotestScheduleController.php - Updated
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
            $candidates = JobApplication::with('jobs')->get();
        } elseif ($user->type == 'company') {
            $candidates = JobApplication::with('jobs')->get();
        } else {
            $candidates = JobApplication::where('created_by', \Auth::user()->creatorId())
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
                'candidate' => 'required|exists:job_applications,id',
                'start_time' => 'required|date|after:now',
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

        // Check if candidate already has active schedule
        $existingSchedule = PsychotestSchedule::where('candidate', $request->candidate)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->first();

        if ($existingSchedule) {
            return redirect()->back()->with('error', __('Candidate already has an active psychotest schedule.'));
        }

        $candidate = JobApplication::find($request->candidate);

        // Determine selected categories
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

        // Generate unique username and password
        $username = strtolower(str_replace(' ', '', $candidate->name)) . '@' . rand(1000, 9999) . '@gmail.com';
        $password = strtoupper(Str::random(6)) . rand(10, 99);

        $schedule = PsychotestSchedule::create([
            'candidate' => $request->candidate,
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
        } catch (\Exception $e) {
            \Log::error('Failed to send psychotest email: ' . $e->getMessage());
        }

        return redirect()->route('psychotest-schedule.index')
            ->with('success', __('Psychotest schedule successfully created and email sent.'));
    }

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
        $selectedCategories = null;
        if ($request->has('auto_select_by_job') && $request->auto_select_by_job) {
            // Auto select based on job title
            $jobTitle = $schedule->candidates->jobs->title ?? '';
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
        
        // PERBAIKAN: Pisahkan query untuk job-specific categories
        $applicableCategoryIds = [];
        
        // Tambahkan semua general categories (not job specific)
        $generalCategories = \App\Models\PsychotestCategory::where('is_active', true)
            ->where('is_job_specific', false)
            ->pluck('id')
            ->toArray();
        
        $applicableCategoryIds = array_merge($applicableCategoryIds, $generalCategories);
        
        // Jika ada job title, cari job-specific categories
        if ($jobTitle) {
            $jobTitleLower = strtolower($jobTitle);
            $keywords = ['auditor', 'audit', 'tax', 'taxation', 'accounting', 'akuntan', 'perpajakan'];
            
            $hasJobSpecificKeyword = false;
            foreach ($keywords as $keyword) {
                if (strpos($jobTitleLower, $keyword) !== false) {
                    $hasJobSpecificKeyword = true;
                    break;
                }
            }
            
            // Jika job title mengandung keyword yang relevan
            if ($hasJobSpecificKeyword) {
                $jobSpecificCategories = \App\Models\PsychotestCategory::where('is_active', true)
                    ->where('is_job_specific', true)
                    ->get()
                    ->filter(function($category) use ($keywords, $jobTitleLower) {
                        if (!$category->target_job_keywords) {
                            return false;
                        }
                        
                        // Check if any keyword matches
                        foreach ($category->target_job_keywords as $targetKeyword) {
                            foreach ($keywords as $jobKeyword) {
                                if (strpos($jobTitleLower, strtolower($jobKeyword)) !== false && 
                                    strtolower($targetKeyword) === strtolower($jobKeyword)) {
                                    return true;
                                }
                            }
                        }
                        return false;
                    })
                    ->pluck('id')
                    ->toArray();
                
                $applicableCategoryIds = array_merge($applicableCategoryIds, $jobSpecificCategories);
            }
        }
        
        // Remove duplicates
        $applicableCategoryIds = array_unique($applicableCategoryIds);

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
            'has_field_specific' => count(array_diff($applicableCategoryIds, $generalCategories)) > 0
        ]);

    } catch (\Exception $e) {
        \Log::error('Error getting categories for candidate: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Internal server error: ' . $e->getMessage()
        ], 500);
    }
}

}