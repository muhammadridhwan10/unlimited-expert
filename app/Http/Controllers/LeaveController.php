<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\Mail\LeaveActionSend;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\LeaveNotification;
use App\Mail\LeaveApprovalNotification;
use App\Mail\LeaveRejectNotification;
use Illuminate\Support\Facades\Mail;
use App\Models\Notification;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        if(\Auth::user()->can('manage leave'))
        {
            $leaves = Leave::all();
            
            // Get filter parameters
            $filters = [
                'employee_filter' => $request->get('employee_filter'),
                'leave_type_filter' => $request->get('leave_type_filter'),
                'status_filter' => $request->get('status_filter'),
                'attendance_type_filter' => $request->get('attendance_type_filter'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'leave_start_from' => $request->get('leave_start_from'),
                'leave_start_to' => $request->get('leave_start_to'),
                'search' => $request->get('search'),
            ];

            if(\Auth::user()->type == 'staff IT' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior accounting' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'intern' || \Auth::user()->type == 'support' ||  \Auth::user()->type == 'staff') 
            {
                $user = \Auth::user();
                $employee = Employee::where('user_id', '=', $user->id)->first();
                
                // Build query for absence_leave with filters
                $absence_leave_query = Leave::where('employee_id', '=', $employee->id)
                                        ->where('absence_type', '=', 'leave');
                
                // Apply filters
                $absence_leave_query = $this->applyFilters($absence_leave_query, $filters);
                $absence_leave = $absence_leave_query->orderByDesc('id')->paginate(10);
                
                $approval = Leave::where('approval', '=', $user->id)
                            ->where('status','=', 'Pending')
                            ->orderByDesc('id')
                            ->paginate(10);

                // Get data for filter dropdowns (limited to current employee)
                $employees = collect([$employee]); // Only current employee
                $leave_types = \App\Models\LeaveType::all();
            }
            elseif(\Auth::user()->type == 'admin')
            {
                $employee = Employee::all();
                
                // Build query for absence_leave with filters
                $absence_leave_query = Leave::where('absence_type', '=', 'leave');
                $absence_leave_query = $this->applyFilters($absence_leave_query, $filters);
                $absence_leave = $absence_leave_query->orderByDesc('id')->paginate(10);
                
                $users = \Auth::user();
                $approval = Leave::where('approval', '=', $users->id)
                            ->where('status','=', 'Pending')
                            ->orderByDesc('id')
                            ->paginate(10);

                // Get data for filter dropdowns
                $employees = Employee::all();
                $leave_types = \App\Models\LeaveType::all();
            }
            elseif(\Auth::user()->type == 'company')
            {
                $employee = Employee::all();
                
                // Build query for absence_leave with filters
                $absence_leave_query = Leave::where('absence_type', '=', 'leave');
                $absence_leave_query = $this->applyFilters($absence_leave_query, $filters);
                $absence_leave = $absence_leave_query->orderByDesc('id')->paginate(10);
                
                $users = \Auth::user();  
                $approval = Leave::where('approval', '=', $users->id)
                            ->where('status','=', 'Pending')
                            ->orderByDesc('id')
                            ->paginate(10);

                // Get data for filter dropdowns
                $employees = Employee::all();
                $leave_types = \App\Models\LeaveType::all();
            }
            elseif(\Auth::user()->type == 'partners')
            {
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

                // Build query for absence_leave with filters
                $absence_leave_query = Leave::whereIn('employee_id', $employee_ids)
                                        ->where('absence_type', '=', 'leave');
                $absence_leave_query = $this->applyFilters($absence_leave_query, $filters);
                $absence_leave = $absence_leave_query->orderByDesc('id')->paginate(10);
                
                $users = \Auth::user();  
                $approval = Leave::where('approval', '=', $users->id)
                            ->where('status','=', 'Pending')
                            ->orderByDesc('id')
                            ->paginate(10);

                // Get data for filter dropdowns (limited to branch employees)
                $employees = $employee;
                $leave_types = \App\Models\LeaveType::all();
            }
            else
            {
                $employee = Employee::where('created_by', '=', \Auth::user()->creatorId())->get();
                
                // Build query for absence_leave with filters
                $absence_leave_query = Leave::where('absence_type', '=', 'leave')
                                        ->where('created_by', '=', \Auth::user()->creatorId());
                $absence_leave_query = $this->applyFilters($absence_leave_query, $filters);
                $absence_leave = $absence_leave_query->orderByDesc('id')->paginate(10);
                
                $approval = Leave::where('approval', '=', \Auth::user()->id)
                            ->where('status','=', 'Pending')
                            ->orderByDesc('id')
                            ->paginate(10);

                // Get data for filter dropdowns
                $employees = $employee;
                $leave_types = \App\Models\LeaveType::all();
            }

            // Append query parameters to pagination links
            $absence_leave->appends($request->query());

            return view('leave.index', compact('absence_leave', 'employee', 'approval', 'employees', 'leave_types'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create leave'))
        {
            if(\Auth::user()->type == 'staff IT' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior accounting' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'intern' || \Auth::user()->type == 'support' ||  \Auth::user()->type == 'staff')
            {
                $employees         = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('name', 'id');
                $leavetypes        = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
                $approval = User::where(function($query) {
                    $query->where('type', 'admin')
                          ->orWhere('type', 'company');
                })
                ->get()
                ->pluck('name', 'id');                
                $leavetypes_days   = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
            }
            elseif(Auth::user()->type == 'admin')
            {
                $employees       = Employee::all()->pluck('name', 'id');
                $leavetypes      = LeaveType::all();
                $approval = User::where(function($query) {
                    $query->where('type', 'admin')
                          ->orWhere('type', 'company');
                })
                ->get()
                ->pluck('name', 'id');  
                $leavetypes_days = LeaveType::all();
            }
            elseif(Auth::user()->type == 'company')
            {
                $employees       = Employee::all()->pluck('name', 'id');
                $leavetypes      = LeaveType::all();
                $approval = User::where(function($query) {
                    $query->where('type', 'admin')
                          ->orWhere('type', 'company');
                })
                ->get()
                ->pluck('name', 'id');  
                $leavetypes_days = LeaveType::all();
            }
            else
            {
                $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $leavetypes      = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
                $leavetypes_days = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
                $approval        = User::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            }

            return view('leave.create', compact('employees', 'leavetypes', 'leavetypes_days', 'approval'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create leave'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'approval' => $request->type == 'leave' ? 'required' : '',
                    'leave_type_id' => $request->type == 'leave' ? 'required' : '',
                    'start_date' => $request->type == 'leave' ? 'required' : '',
                    'end_date' => $request->type == 'leave' ? 'required' : '',
                    'leave_reason' => $request->type == 'leave' ? 'required' : '',
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $leave = new Leave();

            $leave->employee_id = $request->employee_id;

            $leave->applied_on = date('Y-m-d');
            $leave->approval = !empty($request->approval) ? $request->approval : 0;
            $leave->start_date = $request->start_date;
            $leave->end_date = $request->end_date;
            $leave->total_leave_days = 0;
            $leave->leave_reason = $request->leave_reason;
            $leave->absence_type = 'leave';
            $leave->status = 'Pending';
            $leave->created_by = \Auth::user()->creatorId();
            $leave->leave_type_id = $request->leave_type_id;
            $leave->save();

            if ($leave->approval) {
                Notification::createNotification(
                    $leave->approval,
                    'leave_submitted',
                    [
                        'leave_id' => $leave->id,
                        'employee_name' => $employee->name,
                        'leave_type' => $leave->leaveType->title ?? 'Leave',
                        'start_date' => $leave->start_date,
                        'end_date' => $leave->end_date,
                        'updated_by' => Auth::id()
                    ],
                    Notification::PRIORITY_NORMAL
                );
            }

            $user = User::where('id', $leave->approval)->first();
            $email = $user->employee->email;
            Mail::to($email)->send(new LeaveNotification($leave));

            return redirect()->route('leave.index')->with('success', __('Leave Request successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(Leave $leave)
    {
        return redirect()->route('leave.index');
    }

    public function edit($id)
    {
        if(\Auth::user()->can('edit leave'))
        {
            $leave = Leave::find($id);
            $employees = Employee::get()->pluck('name', 'id');
            $leavetypes      = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
            $approval = User::where(function($query) {
                $query->where('type', 'admin')
                        ->orWhere('type', 'company');
            })
            ->get()
            ->pluck('name', 'id');   

            return view('leave.edit', compact('leave', 'employees', 'leavetypes', 'approval'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $leave)
    {

        $leave = Leave::find($leave);
        if(\Auth::user()->can('edit leave'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'approval' => $request->type == 'leave' ? 'required' : '',
                    'employee_id' => $request->type == 'leave' ? 'required' : '',
                    'leave_type_id' => $request->type == 'leave' ? 'required' : '',
                    'start_date' => $request->type == 'leave' ? 'required' : '',
                    'end_date' => $request->type == 'leave' ? 'required' : '',
                    'leave_reason' => $request->type == 'leave' ? 'required' : '',
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $leave->employee_id = $request->employee_id;
            
            $leave->applied_on       = date('Y-m-d');
            $leave->approval         = !empty($request->approval) ? $request->approval : 0;
            $leave->start_date       = $request->start_date;
            $leave->end_date         = $request->end_date;
            $leave->total_leave_days = 0;
            $leave->leave_reason     = $request->leave_reason;
            $leave->absence_type     = 'leave';
            $leave->created_by       = \Auth::user()->creatorId();
            $leave->leave_type_id    = $request->leave_type_id;

            $leave->save();

            return redirect()->route('leave.index')->with('success', __('Absence Request successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function destroy(Leave $leave)
    // {
    //     if(\Auth::user()->can('delete leave'))
    //     {
    //         if(Auth::user()->type !=='admin' || Auth::user()->type !=='company')
    //         {
    //             $leave->delete();

    //             return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
    //         }
    //         elseif(\Auth::user()->type == 'admin')
    //         {
    //             $leave->delete();

    //             return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
    //         }
    //         elseif(\Auth::user()->type == 'company')
    //         {
    //             $leave->delete();

    //             return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
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

    public function action($id)
    {
        $leave     = Leave::find($id);
        $employee  = Employee::find($leave->employee_id);
        $leavetype = LeaveType::find($leave->leave_type_id);

        return view('leave.action', compact('employee', 'leavetype', 'leave'));
    }

    public function changeaction(Request $request)
    {

        $leave = Leave::find($request->leave_id);

        $leave->status = $request->status;
        if($leave->status == 'Approval')
        {
            $startDate = new \DateTime($leave->start_date);
            $endDate = new \DateTime($leave->end_date);
            $total_leave_days = 0;

            while ($startDate <= $endDate) {
                if ($startDate->format('N') <= 5) {
                    $total_leave_days++;
                }
                $startDate->add(new \DateInterval('P1D'));
            }
            $leave->total_leave_days = $total_leave_days;
            $leave->status           = 'Approved';
        }
        else
        {
            $leave->status           = 'Reject';
        }

        $leave->save();

        if($leave->status == 'Approved')
        {
            Notification::createNotification(
                $leave->employee->user_id,
                'leave_approved',
                [
                    'leave_id' => $leave->id,
                    'leave_type' => $leave->leaveType->title ?? 'Leave',
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'updated_by' => Auth::id()
                ],
                Notification::PRIORITY_HIGH
            );

            //Email Notification
            $employee = Employee::where('id', $leave->employee_id)->first();
            $email = $employee->email;
            Mail::to($email)->send(new LeaveApprovalNotification($leave));
        }
        else
        {

            Notification::createNotification(
                $leave->employee->user_id,
                'leave_rejected',
                [
                    'leave_id' => $leave->id,
                    'leave_type' => $leave->leaveType->title ?? 'Leave',
                    'updated_by' => Auth::id()
                ],
                Notification::PRIORITY_HIGH
            );

            //Email Notification
            $employee = Employee::where('id', $leave->employee_id)->first();
            $email = $employee->email;
            Mail::to($email)->send(new LeaveRejectNotification($leave));
        }

        return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.'));
    }

    public function jsoncount(Request $request)
    {


        $leave_counts = [];
        $leave_types = LeaveType::where('created_by', \Auth::user()->creatorId())->get();

        foreach ($leave_types as $type) {
            $counts = Leave::select(\DB::raw('
                COALESCE(SUM(
                    CASE 
                        WHEN sick_letter IS NULL THEN total_leave_days + total_sick_days
                        ELSE total_leave_days 
                    END
                ), 0) AS total_leave'))
                ->where('leave_type_id', $type->id)
                ->where('employee_id', $request->employee_id)
                ->whereYear('created_at', now()->year)
                ->groupBy('leaves.leave_type_id')
                ->first();

            $leave_count['total_leave'] = !empty($counts) ? $counts['total_leave'] : 0;
            $leave_count['title'] = $type->title;
            $leave_count['days'] = $type->days;
            $leave_count['id'] = $type->id;
            $leave_count['remaining_leave'] = $type->days - $leave_count['total_leave'];
            $leave_counts[] = $leave_count;
        }

        return $leave_counts;


    }

    private function applyFilters($query, $filters)
    {
        // Employee filter
        if (!empty($filters['employee_filter'])) {
            $query->where('employee_id', $filters['employee_filter']);
        }

        // Leave type filter
        if (!empty($filters['leave_type_filter'])) {
            $query->where('leave_type_id', $filters['leave_type_filter']);
        }

        // Status filter
        if (!empty($filters['status_filter'])) {
            $query->where('status', $filters['status_filter']);
        }

        // Attendance type filter
        if (!empty($filters['attendance_type_filter'])) {
            $query->where('absence_type', $filters['attendance_type_filter']);
        }

        // Applied date range filter
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('applied_on', [$filters['date_from'], $filters['date_to']]);
        } elseif (!empty($filters['date_from'])) {
            $query->where('applied_on', '>=', $filters['date_from']);
        } elseif (!empty($filters['date_to'])) {
            $query->where('applied_on', '<=', $filters['date_to']);
        }

        // Leave start date range filter
        if (!empty($filters['leave_start_from']) && !empty($filters['leave_start_to'])) {
            $query->whereBetween('start_date', [$filters['leave_start_from'], $filters['leave_start_to']]);
        } elseif (!empty($filters['leave_start_from'])) {
            $query->where('start_date', '>=', $filters['leave_start_from']);
        } elseif (!empty($filters['leave_start_to'])) {
            $query->where('start_date', '<=', $filters['leave_start_to']);
        }

        // Search filter (search in employee name or leave reason)
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('leave_reason', 'LIKE', $searchTerm)
                ->orWhereHas('employees', function($employeeQuery) use ($searchTerm) {
                    $employeeQuery->where('name', 'LIKE', $searchTerm);
                });
            });
        }

        return $query;
    }

}
