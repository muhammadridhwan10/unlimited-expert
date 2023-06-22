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
use Illuminate\Support\Facades\Mail;

class LeaveController extends Controller
{
    public function index()
    {

        if(\Auth::user()->can('manage leave'))
        {
            $leaves = Leave::all();
            if(\Auth::user()->type == 'staff IT' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior accounting' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'intern')
            {
                $user     = \Auth::user();
                $employee = Employee::where('user_id', '=', $user->id)->first();
                $leaves   = Leave::where('employee_id', '=', $employee->id)->get();
                $approval      = Leave::where('approval', '=', $user->id)->where('status','=', 'Pending')->get();
            }
            elseif(\Auth::user()->type == 'admin')
            {
                $employee      = Employee::all();
                $leaves        = Leave::all();
                $users         = \Auth::user();
                $approval      = Leave::where('approval', '=', $users->id)->where('status','=', 'Pending')->get();
                
            }
            elseif(\Auth::user()->type == 'company')
            {
                $employee      = Employee::all();
                $leaves        = Leave::all();
                $users         = \Auth::user();  
                $approval      = Leave::where('approval', '=', $users->id)->where('status','=', 'Pending')->get();
            }
            else
            {
                $leaves = Leave::where('created_by', '=', \Auth::user()->creatorId())->get();

            }

            return view('leave.index', compact('leaves', 'employee', 'approval'));

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
            if(\Auth::user()->type == 'staff IT' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior accounting' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'intern')
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
                $approval        = User::where('type', '=', 'company')->get()->pluck('name', 'id');
                $leavetypes_days = LeaveType::all();
            }
            elseif(Auth::user()->type == 'company')
            {
                $employees       = Employee::all()->pluck('name', 'id');
                $leavetypes      = LeaveType::all();
                $approval        = User::where('type', '=', 'admin')->get()->pluck('name', 'id');
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
                                   'leave_type_id' => 'required',
                                   'start_date' => 'required',
                                   'end_date' => 'required',
                                   'leave_reason' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $leave    = new Leave();
            if(\Auth::user()->type == "employee")
            {
                $leave->employee_id = $employee->id;
            }
            else
            {
                $leave->employee_id = $request->employee_id;
            }
            $leave->leave_type_id    = $request->leave_type_id;
            $leave->applied_on       = date('Y-m-d');
            $leave->approval         = $request->approval;
            $leave->start_date       = $request->start_date;
            $leave->end_date         = $request->end_date;
            $leave->total_leave_days = 0;
            $leave->leave_reason     = $request->leave_reason;
            $leave->status           = 'Pending';
            $leave->created_by       = \Auth::user()->creatorId();

            $leave->save();

            //Email Notification
            $user = User::where('id', $leave->approval)->first();
            $email = $user->email;
            Mail::to($email)->send(new LeaveNotification($leave));

            return redirect()->route('leave.index')->with('success', __('Leave  successfully created.'));
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

    public function edit(Leave $leave)
    {
        if(\Auth::user()->can('edit leave'))
        {
            if(Auth::user()->type !=='admin' || Auth::user()->type !=='company')
            {
                $employees = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('name', 'id');
                $leavetypes      = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
                $approval = User::where(function($query) {
                    $query->where('type', 'admin')
                          ->orWhere('type', 'company');
                })
                ->get()
                ->pluck('name', 'id');   

                return view('leave.edit', compact('leave', 'employees', 'leavetypes', 'approval'));
            }
            elseif(\Auth::user()->type = 'admin')
            {
                $employees  = Employee::get()->pluck('name', 'id');
                $leavetypes = LeaveType::get()->pluck('title', 'id');
                $approval        = User::where('type', '=', 'company')->get()->pluck('name', 'id');

                return view('leave.edit', compact('leave', 'employees', 'leavetypes', 'approval'));
            }
            elseif(\Auth::user()->type = 'company')
            {
                $employees  = Employee::get()->pluck('name', 'id');
                $leavetypes = LeaveType::get()->pluck('title', 'id');
                $approval        = User::where('type', '=', 'admin')->get()->pluck('name', 'id');

                return view('leave.edit', compact('leave', 'employees', 'leavetypes', 'approval'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
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
            if(Auth::user()->type !=='admin' || Auth::user()->type !=='company')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'leave_type_id' => 'required',
                                       'start_date' => 'required',
                                       'end_date' => 'required',
                                       'leave_reason' => 'required',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $leave->employee_id      = $request->employee_id;
                $leave->leave_type_id    = $request->leave_type_id;
                $leave->approval         = $request->approval;
                $leave->start_date       = $request->start_date;
                $leave->end_date         = $request->end_date;
                $leave->total_leave_days = 0;
                $leave->leave_reason     = $request->leave_reason;

                $leave->save();

                return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
            }
            elseif(\Auth::user()->type == 'admin')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'leave_type_id' => 'required',
                                       'start_date' => 'required',
                                       'end_date' => 'required',
                                       'leave_reason' => 'required',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $leave->employee_id      = $request->employee_id;
                $leave->leave_type_id    = $request->leave_type_id;
                $leave->approval         = $request->approval;
                $leave->start_date       = $request->start_date;
                $leave->end_date         = $request->end_date;
                $leave->total_leave_days = 0;
                $leave->leave_reason     = $request->leave_reason;

                $leave->save();

                return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
            }
            elseif(\Auth::user()->type == 'company')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'leave_type_id' => 'required',
                                       'start_date' => 'required',
                                       'end_date' => 'required',
                                       'leave_reason' => 'required',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $leave->employee_id      = $request->employee_id;
                $leave->leave_type_id    = $request->leave_type_id;
                $leave->approval         = $request->approval;
                $leave->start_date       = $request->start_date;
                $leave->end_date         = $request->end_date;
                $leave->total_leave_days = 0;
                $leave->leave_reason     = $request->leave_reason;

                $leave->save();

                return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
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

    public function destroy(Leave $leave)
    {
        if(\Auth::user()->can('delete leave'))
        {
            if(Auth::user()->type !=='admin' || Auth::user()->type !=='company')
            {
                $leave->delete();

                return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
            }
            elseif(\Auth::user()->type == 'admin')
            {
                $leave->delete();

                return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
            }
            elseif(\Auth::user()->type == 'company')
            {
                $leave->delete();

                return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
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
                if ($startDate->format('N') <= 5) { // Memeriksa apakah hari adalah Senin hingga Jumat
                    $total_leave_days++;
                }
                $startDate->add(new \DateInterval('P1D')); // Menambahkan 1 hari ke tanggal start_date
            }
            $leave->total_leave_days = $total_leave_days;
            $leave->status           = 'Approved';
        }

        $leave->save();

        //Email Notification
        $employee = Employee::where('id', $leave->employee_id)->first();
        $email = $employee->email;
        Mail::to($email)->send(new LeaveApprovalNotification($leave));

        //Send Email
//         $setings = Utility::settings();
//         if($setings['leave_status'] == 1)
//         {

//             $employee     = Employee::where('id', $leave->employee_id)->where('created_by', '=', \Auth::user()->creatorId())->first();
//             $leave->name  = !empty($employee->name) ? $employee->name : '';
//             $leave->email = !empty($employee->email) ? $employee->email : '';
// //            dd($leave);

//             $actionArr = [

//                 'leave_name'=> $employee->name,
//                 'leave_status' => $leave->status,
//                 'leave_reason' =>  $leave->leave_reason,
//                 'leave_start_date' => $leave->start_date,
//                 'leave_end_date' => $leave->end_date,
//                 'total_leave_days' => $leave->total_leave_days,

//             ];
// //            dd($actionArr);
//             $resp = Utility::sendEmailTemplate('leave_action_send', [$employee->id => $employee->email], $actionArr);


//             return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.') .(($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));

//         }

        return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.'));
    }

    public function jsoncount(Request $request)
    {

        // $leave_counts = LeaveType::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave, leave_types.title, leave_types.days,leave_types.id'))
        //                          ->leftjoin('leaves', function ($join) use ($request){
        //     $join->on('leaves.leave_type_id', '=', 'leave_types.id');
        //     $join->where('leaves.employee_id', '=', $request->employee_id);
        // }
        // )->groupBy('leaves.leave_type_id')->get();

        $leave_counts=[];
        $leave_types = LeaveType::where('created_by',\Auth::user()->creatorId())->get();
        foreach ($leave_types as  $type) {
            $counts=Leave::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave'))->where('leave_type_id',$type->id)->groupBy('leaves.leave_type_id')->where('employee_id',$request->employee_id)->first();

            $leave_count['total_leave']=!empty($counts)?$counts['total_leave']:0;
            $leave_count['title']=$type->title;
            $leave_count['days']=$type->days;
            $leave_count['id']=$type->id;
            $leave_count['remaining_leave'] = $type->days - $leave_count['total_leave'];
            $leave_counts[]=$leave_count;
        }

        return $leave_counts;

    }

}
