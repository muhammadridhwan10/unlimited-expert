<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Overtime;
use App\Models\UserOvertime;
use App\Models\ProjectUser;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\OvertimeNotification;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class OvertimeController extends Controller
{

    public function index(Request $request)
    {

        if(\Auth::user()->type == 'admin')
        {
            $overtimes   = UserOvertime::all();

            $employee = Employee::all();
            $employee = $employee->pluck('id');
            $employeeOvertimes = UserOvertime::whereIn('user_id', $employee);

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeOvertimes->whereBetween('start_date', [$start_date, $end_date]);
            } 

            $employeeOvertimes = $employeeOvertimes->get();


            $users        = \Auth::user();
            $employee     = Employee::where('user_id', '=', $users->id)->first();
            $approval     = UserOvertime::where('approval', '=', $employee->id)->where('status','=', 'Pending')->get();
        }
        elseif(\Auth::user()->type == 'company')
        {
            $overtimes   = UserOvertime::all();

            $employee = Employee::all();
            $employee = $employee->pluck('id');
            $employeeOvertimes = UserOvertime::whereIn('user_id', $employee);

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeOvertimes->whereBetween('start_date', [$start_date, $end_date]);
            } 

            $employeeOvertimes = $employeeOvertimes->get();

            $users        = \Auth::user();
            $employee     = Employee::where('user_id', '=', $users->id)->first();
            $approval     = UserOvertime::where('approval', '=', $employee->id)->where('status','=', 'Pending')->get();
        }
        elseif(\Auth::user()->type == 'senior accounting')
        {
            $overtimes   = UserOvertime::all();

            $employee = Employee::all();
            $employee = $employee->pluck('id');
            $employeeOvertimes = UserOvertime::whereIn('user_id', $employee);

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeOvertimes->whereBetween('start_date', [$start_date, $end_date]);
            } 

            $employeeOvertimes = $employeeOvertimes->get();

            $users        = \Auth::user();
            $employee     = Employee::where('user_id', '=', $users->id)->first();
            $approval     = UserOvertime::where('approval', '=', $employee->id)->where('status','=', 'Pending')->get();
        }
        elseif(\Auth::user()->type == 'senior audit' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
        {

            $employee = Employee::all();
            $employee = $employee->pluck('id');
            $employeeOvertimes = UserOvertime::whereIn('user_id', $employee);

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeOvertimes->whereBetween('start_date', [$start_date, $end_date]);
            } 

            $employeeOvertimes = $employeeOvertimes->get();

            $users        = \Auth::user();
            $employee     = Employee::where('user_id', '=', $users->id)->first();
            $overtimes    = UserOvertime::where('user_id', '=', $users->id)->get();
            $approval     = UserOvertime::where('approval', '=', $employee->id)->where('status','=', 'Pending')->get();
        }
        else
        {
            $employees    = Employee::all();

            $employee = $employees->where('user_id', '=', \Auth::user()->id)->pluck('id');
            $employeeOvertimes = UserOvertime::whereIn('user_id', $employee);

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeOvertimes->whereBetween('start_date', [$start_date, $end_date]);
            } 

            $employeeOvertimes = $employeeOvertimes->get();

            $employee     = Employee::where('user_id', '=', \Auth::user()->id)->first();
            $overtimes    = UserOvertime::where('user_id', '=', $employee->id)->get();
            $approval     = UserOvertime::where('approval', '=', \Auth::user()->id)->get();
        }

        return view('overtime.index', compact('overtimes','approval','employeeOvertimes'));
    }

    public function create()
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

        $overtime->status = $request->status;
        if($overtime->status == 'Approval')
        {
            $start_time = $overtime->start_time;
            $end_time = $overtime->end_time;
            $time_difference = $this->calculateTimeDifference($start_time, $end_time);
            $overtime->total_time = $time_difference;
            $overtime->status           = 'Approved';
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
        $start = \Carbon\Carbon::parse($start_time);
        $end = \Carbon\Carbon::parse($end_time);
        $difference = $start->diffInSeconds($end);
    
        return gmdate('H:i:s', $difference);
    }
}
