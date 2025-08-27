<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Overtime;
use App\Models\UserOvertime;
use App\Models\ProjectUser;
use App\Models\Project;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\OvertimeNotification;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class OvertimeController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->get('show_entries', 10);
        
        // Get filter parameters
        $filters = [
            'employee_filter' => $request->get('employee_filter'),
            'project_filter' => $request->get('project_filter'),
            'status_filter' => $request->get('status_filter'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'month' => $request->get('month'),
            'search' => $request->get('search'),
        ];

        if(\Auth::user()->type == 'admin')
        {
            $overtimes = UserOvertime::all();
            $employee = Employee::all();
            $employee_ids = $employee->pluck('id');
            
            // Build query with filters
            $employeeOvertimes_query = UserOvertime::whereIn('user_id', $employee_ids);
            $employeeOvertimes_query = $this->applyOvertimeFilters($employeeOvertimes_query, $filters);
            $employeeOvertimes = $employeeOvertimes_query->orderByDesc('id')->paginate($perPage);

            $users = \Auth::user();
            $employee_detail = Employee::where('user_id', '=', $users->id)->first();
            $approval = UserOvertime::where('approval', '=', $employee_detail->id)
                                    ->where('status','=', 'Pending')
                                    ->orderByDesc('id')
                                    ->paginate(10);

            // Get data for filter dropdowns
            $employees = Employee::all();
            $projects = \App\Models\Project::all();
        }
        elseif(\Auth::user()->type == 'company')
        {
            $overtimes = UserOvertime::all();
            $employee = Employee::all();
            $employee_ids = $employee->pluck('id');
            
            // Build query with filters
            $employeeOvertimes_query = UserOvertime::whereIn('user_id', $employee_ids);
            $employeeOvertimes_query = $this->applyOvertimeFilters($employeeOvertimes_query, $filters);
            $employeeOvertimes = $employeeOvertimes_query->orderByDesc('id')->paginate($perPage);

            $users = \Auth::user();
            $employee_detail = Employee::where('user_id', '=', $users->id)->first();
            $approval = UserOvertime::where('approval', '=', $employee_detail->id)
                                    ->where('status','=', 'Pending')
                                    ->orderByDesc('id')
                                    ->paginate(10);

            // Get data for filter dropdowns
            $employees = Employee::all();
            $projects = \App\Models\Project::all();
        }
        elseif(\Auth::user()->type == 'senior accounting')
        {
            $overtimes = UserOvertime::all();
            $employee = Employee::all();
            $employee_ids = $employee->pluck('id');
            
            // Build query with filters
            $employeeOvertimes_query = UserOvertime::whereIn('user_id', $employee_ids);
            $employeeOvertimes_query = $this->applyOvertimeFilters($employeeOvertimes_query, $filters);
            $employeeOvertimes = $employeeOvertimes_query->orderByDesc('id')->paginate($perPage);

            $users = \Auth::user();
            $employee_detail = Employee::where('user_id', '=', $users->id)->first();
            $approval = UserOvertime::where('approval', '=', $employee_detail->id)
                                    ->where('status','=', 'Pending')
                                    ->orderByDesc('id')
                                    ->paginate(10);

            // Get data for filter dropdowns
            $employees = Employee::all();
            $projects = \App\Models\Project::all();
        }
        elseif(\Auth::user()->type == 'partners')
        {
            $overtimes = UserOvertime::all();
            
            // Get employees based on branch
            if(\Auth::user()->employee->branch_id == 2)
            {
                $employee = Employee::where('branch_id', 2)->get();
            }
            elseif(\Auth::user()->employee->branch_id == 3)
            {
                $employee = Employee::where('branch_id', 3)->get();
            }
            else
            {
                $employee = Employee::all();
            }
            $employee_ids = $employee->pluck('id');
            
            // Build query with filters
            $employeeOvertimes_query = UserOvertime::whereIn('user_id', $employee_ids);
            $employeeOvertimes_query = $this->applyOvertimeFilters($employeeOvertimes_query, $filters);
            $employeeOvertimes = $employeeOvertimes_query->orderByDesc('id')->paginate($perPage);

            $users = \Auth::user();
            $employee_detail = Employee::where('user_id', '=', $users->id)->first();
            $approval = UserOvertime::where('approval', '=', $employee_detail->id)
                                    ->where('status','=', 'Pending')
                                    ->orderByDesc('id')
                                    ->paginate(10);

            // Get data for filter dropdowns (limited to branch employees)
            $employees = $employee;
            $projects = \App\Models\Project::all();
        }
        elseif(\Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'staff')
        {
            $users = \Auth::user();
            $employee = Employee::where('user_id', '=', $users->id);
            $employee_ids = $employee->pluck('id');
            
            // Build query with filters
            $employeeOvertimes_query = UserOvertime::whereIn('user_id', $employee_ids);
            $employeeOvertimes_query = $this->applyOvertimeFilters($employeeOvertimes_query, $filters);
            $employeeOvertimes = $employeeOvertimes_query->orderByDesc('id')->paginate($perPage);

            $employee_detail = Employee::where('user_id', '=', $users->id)->first();
            $overtimes = UserOvertime::where('user_id', '=', $users->id)->get();
            $approval = UserOvertime::where('approval', '=', $employee_detail->id)
                                    ->where('status','=', 'Pending')
                                    ->orderByDesc('id')
                                    ->paginate(10);

            // Get data for filter dropdowns (limited to current employee)
            $employees = collect([$employee_detail]); // Only current employee
            $projects = \App\Models\Project::all();
        }
        else
        {
            $employees_all = Employee::all();
            $employee = $employees_all->where('user_id', '=', \Auth::user()->id)->pluck('id');
            
            // Build query with filters
            $employeeOvertimes_query = UserOvertime::whereIn('user_id', $employee);
            $employeeOvertimes_query = $this->applyOvertimeFilters($employeeOvertimes_query, $filters);
            $employeeOvertimes = $employeeOvertimes_query->orderByDesc('id')->paginate($perPage);

            $employee_detail = Employee::where('user_id', '=', \Auth::user()->id)->first();
            $overtimes = UserOvertime::where('user_id', '=', $employee_detail->id)->get();
            $approval = UserOvertime::where('approval', '=', \Auth::user()->id)
                                    ->orderByDesc('id')
                                    ->paginate(10);

            // Get data for filter dropdowns
            $employees = collect([$employee_detail]); // Only current employee
            $projects = \App\Models\Project::all();
        }

        // Append query parameters to pagination links
        $employeeOvertimes->appends($request->query());

        return view('overtime.index', compact('overtimes','approval','employeeOvertimes', 'employees', 'projects'));
    }


    public function create()
    {
            $user = \Auth::user();
            if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $employees         = Employee::get()->pluck('name', 'id');
                $approval          = Employee::get()->pluck('name', 'id');
                $project           = Project::get()->pluck('project_name', 'id');
            }
            else
            {
                $employees    = Employee::where('user_id', '=', $user->id)->get()->pluck('name', 'id');
                $approval     = Employee::get()->pluck('name', 'id');
                $project      = $user->projects()->pluck('project_name', 'project_id');
            }

            return view('overtime.create', compact('employees', 'project', 'approval'));
    }

    // public function overtimeCreate($id)
    // {
    //     $employee = Employee::find($id);

    //     return view('overtime.create', compact('employee'));
    // }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create overtime'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'start_time' => 'required',
                                   'end_time' => 'required',
                                   'start_date' => 'required',
                                   'note' => 'required',
                                   'approval' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $overtime                 = new UserOvertime();
            $user                     = \Auth::user();
            $employees                = Employee::where('user_id', '=', $user->id)->first();
            $date                     = Carbon::now()->format('Y-m-d');
            if(\Auth::user()->type == "admin" || \Auth::user()->type == "company" )
            {
                $overtime->user_id = $request->user_id;
            }
            else
            {
                $overtime->user_id = $employees->id;
            }
            $overtime->project_id       = $request->project_id;
            $overtime->start_time       = $request->start_time;
            $overtime->end_time         = $request->end_time;
            $overtime->start_date       = $request->start_date;
            $overtime->approval         = $request->approval;
            $overtime->status           = 'Pending';
            $overtime->created_date     = $date;
            $overtime->total_time       = 0;
            $overtime->note             = $request->note;
            $overtime->save();

            $notificationData = [
                'user_id' => $request->approval,
                'type' => 'create_overtime',
                'data' => json_encode([
                    'updated_by' => $user->id,
                    'project_id' => $overtime->project_id,
                    'name' => $user->name,
                ]),
                'is_read' => false,
            ];

            Notification::create($notificationData);

            //Email Notification Client
            $user = Employee::where('id', $overtime->approval)->first();
            $email = $user->email;
            Mail::to($email)->send(new OvertimeNotification($overtime));

            return redirect()->back()->with('success', __('Overtime  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function changeaction(Request $request)
    {
        $overtime = UserOvertime::find($request->overtime_id);

        $overtime->start_time = $request->start_time;
        $overtime->end_time = $request->end_time;

        if ($request->status == 'Approval') {
            $time_difference = $this->calculateTimeDifference($request->start_time, $request->end_time);
            $overtime->total_time = $time_difference;
            $overtime->status = 'Approved';
        } else {
            $overtime->status = $request->status;
        }

        $overtime->save();

        return redirect()->route('overtime.index')->with('success', __('Overtime successfully updated.'));
    }

    public function show(Overtime $overtime)
    {
        return redirect()->route('commision.index');
    }
    
    public function action($id)
    {

        $overtime     = UserOvertime::find($id);
        $employee     = Employee::where('id', $overtime->user_id)->first();
        $user         = User::find($employee->user_id);
        $project      = Project::find($overtime->project_id);

        return view('overtime.action', compact('overtime', 'user','project'));
    }

    public function edit(UserOvertime $overtime)
    {
        if(\Auth::user()->can('edit overtime'))
        {
            if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $employees         = Employee::get()->pluck('name', 'id');
                $approval          = Employee::get()->pluck('name', 'id');
                $project           = Project::get()->pluck('project_name', 'id');
            }
            else
            {
                $employees    = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('name', 'id');
                $approval     = Employee::get()->pluck('name', 'id');
                $project      = Project::get()->pluck('project_name', 'id');
            }

            return view('overtime.edit', compact('overtime', 'employees', 'project', 'approval'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $overtime)
    {

        $overtime = UserOvertime::find($overtime);
        if(\Auth::user()->can('edit overtime'))
        {
            $user                     = \Auth::user();
            $employees                = Employee::where('user_id', '=', $user->id)->first();
            $date                     = Carbon::now()->format('Y-m-d');
            if(\Auth::user()->type == "admin" || \Auth::user()->type == "company" )
            {
                $overtime->user_id = $request->user_id;
            }
            else
            {
                $overtime->user_id = $employees->id;
            }
            $overtime->project_id       = $request->project_id;
            $overtime->start_time       = $request->start_time;
            $overtime->end_time         = $request->end_time;
            $overtime->start_date       = $request->start_date;
            $overtime->approval         = $request->approval;
            $overtime->status           = 'Pending';
            $overtime->created_date     = $date;
            $overtime->total_time       = 0;
            $overtime->note             = $request->note;
            $overtime->save();

            return redirect()->back()->with('success', __('Overtime  successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function edit($overtime)
    // {
    //     $overtime = Overtime::find($overtime);
    //     if(\Auth::user()->can('edit overtime'))
    //     {
    //         if($overtime->created_by == \Auth::user()->creatorId())
    //         {
    //             return view('overtime.edit', compact('overtime'));
    //         }
    //         elseif(\Auth::user()->type = 'admin')
    //         {
    //             return view('overtime.edit', compact('overtime'));
    //         }
    //         elseif(\Auth::user()->type = 'company')
    //         {
    //             return view('overtime.edit', compact('overtime'));
    //         }
    //         else
    //         {
    //             return response()->json(['error' => __('Permission denied.')], 401);
    //         }
    //     }
    //     else
    //     {
    //         return response()->json(['error' => __('Permission denied.')], 401);
    //     }
    // }

    // public function update(Request $request, $overtime)
    // {
    //     $overtime = Overtime::find($overtime);
    //     if(\Auth::user()->can('edit overtime'))
    //     {
    //         if($overtime->created_by == \Auth::user()->creatorId())
    //         {
    //             $validator = \Validator::make(
    //                 $request->all(), [
    //                                    'title' => 'required',
    //                                    'number_of_days' => 'required',
    //                                    'hours' => 'required',
    //                                    'rate' => 'required',
    //                                ]
    //             );
    //             if($validator->fails())
    //             {
    //                 $messages = $validator->getMessageBag();

    //                 return redirect()->back()->with('error', $messages->first());
    //             }

    //             $overtime->title          = $request->title;
    //             $overtime->number_of_days = $request->number_of_days;
    //             $overtime->hours          = $request->hours;
    //             $overtime->rate           = $request->rate;
    //             $overtime->save();

    //             return redirect()->back()->with('success', __('Overtime successfully updated.'));
    //         }
    //         elseif(\Auth::user()->type = 'admin')
    //         {
    //             $validator = \Validator::make(
    //                 $request->all(), [
    //                                    'title' => 'required',
    //                                    'number_of_days' => 'required',
    //                                    'hours' => 'required',
    //                                    'rate' => 'required',
    //                                ]
    //             );
    //             if($validator->fails())
    //             {
    //                 $messages = $validator->getMessageBag();

    //                 return redirect()->back()->with('error', $messages->first());
    //             }

    //             $overtime->title          = $request->title;
    //             $overtime->number_of_days = $request->number_of_days;
    //             $overtime->hours          = $request->hours;
    //             $overtime->rate           = $request->rate;
    //             $overtime->save();

    //             return redirect()->back()->with('success', __('Overtime successfully updated.'));
    //         }
    //         elseif(\Auth::user()->type = 'company')
    //         {
    //             $validator = \Validator::make(
    //                 $request->all(), [
    //                                    'title' => 'required',
    //                                    'number_of_days' => 'required',
    //                                    'hours' => 'required',
    //                                    'rate' => 'required',
    //                                ]
    //             );
    //             if($validator->fails())
    //             {
    //                 $messages = $validator->getMessageBag();

    //                 return redirect()->back()->with('error', $messages->first());
    //             }

    //             $overtime->title          = $request->title;
    //             $overtime->number_of_days = $request->number_of_days;
    //             $overtime->hours          = $request->hours;
    //             $overtime->rate           = $request->rate;
    //             $overtime->save();

    //             return redirect()->back()->with('success', __('Overtime successfully updated.'));
    //         }
    //         else
    //         {
    //             return redirect()->back()->with('error', __('Permission denied.'));
    //         }
    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    public function destroy(Overtime $overtime)
    {
        if(\Auth::user()->can('delete overtime'))
        {
            if($overtime->created_by == \Auth::user()->creatorId())
            {
                $overtime->delete();

                return redirect()->back()->with('success', __('Overtime successfully deleted.'));
            }
            elseif(\Auth::user()->type = 'admin')
            {
                $overtime->delete();

                return redirect()->back()->with('success', __('Overtime successfully deleted.'));
            }
            elseif(\Auth::user()->type = 'company')
            {
                $overtime->delete();

                return redirect()->back()->with('success', __('Overtime successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function calculateTimeDifference($start_time, $end_time) {
        if ($end_time === '00:00:00') {
            $end_time = '24:00:00';
        }
    
        $start = \Carbon\Carbon::parse($start_time);
        $end = \Carbon\Carbon::parse($end_time);
        
        $difference = $start->diffInSeconds($end);
    
        return gmdate('H:i:s', $difference);
    }

    public function approveMultiple(Request $request)
    {

        $selectedIds = $request->input('selectedIds');

        UserOvertime::whereIn('id', $selectedIds)->update(['status' => 'Approved']);

        foreach ($selectedIds as $reimbursmentId) {
            $overtime = UserOvertime::find($reimbursmentId);
            $start_time = $overtime->start_time;
            $end_time = $overtime->end_time;
            $time_difference = $this->calculateTimeDifference($start_time, $end_time);
            $overtime->total_time = $time_difference;
            $overtime->save();
        }

        return redirect()->route('overtime.index')->with('success', __('Overtime successfully updated.'));
    }

    /**
     * Apply filters to the overtime query
     */
    private function applyOvertimeFilters($query, $filters)
    {
        // Employee filter
        if (!empty($filters['employee_filter'])) {
            $query->where('user_id', $filters['employee_filter']);
        }

        // Project filter
        if (!empty($filters['project_filter'])) {
            $query->where('project_id', $filters['project_filter']);
        }

        // Status filter
        if (!empty($filters['status_filter'])) {
            $query->where('status', $filters['status_filter']);
        }

        // Date range filter
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('start_date', [$filters['date_from'], $filters['date_to']]);
        } elseif (!empty($filters['date_from'])) {
            $query->where('start_date', '>=', $filters['date_from']);
        } elseif (!empty($filters['date_to'])) {
            $query->where('start_date', '<=', $filters['date_to']);
        }

        // Month filter (existing functionality)
        if (!empty($filters['month'])) {
            $month = date('m', strtotime($filters['month']));
            $year = date('Y', strtotime($filters['month']));
            $start_date = date($year . '-' . $month . '-01');
            $end_date = date($year . '-' . $month . '-t');
            $query->whereBetween('start_date', [$start_date, $end_date]);
        }

        // Search filter (search in employee name, project name, or note)
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('note', 'LIKE', $searchTerm)
                ->orWhereHas('employee', function($employeeQuery) use ($searchTerm) {
                    $employeeQuery->where('name', 'LIKE', $searchTerm);
                })
                ->orWhereHas('project', function($projectQuery) use ($searchTerm) {
                    $projectQuery->where('project_name', 'LIKE', $searchTerm);
                });
            });
        }

        return $query;
    }
}
