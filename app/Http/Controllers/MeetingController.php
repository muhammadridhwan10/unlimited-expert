<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Meeting;
use App\Models\MeetingEmployee;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\MeetingNotification;

class MeetingController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage meeting'))
        {
            $employees = Employee::get();

            if(Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $meetings = Meeting::all();
            }
            else
            {
                $meetings = Meeting::where('created_by', '=', \Auth::user()->id)->get();
            }

            return view('meeting.index', compact('meetings', 'employees'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create meeting'))
        {
            $branch      = Branch::all();
            $departments = Department::all();
            $employees                   = Employee::whereHas('user', function($query) {
                $query->where('is_active', 1);
            })->pluck('name', 'id');
            
            return view('meeting.create', compact('employees', 'departments', 'branch'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        $validator = \Validator::make(
            $request->all(), [
                               'branch_id' => 'required',
                               'employee_id' => 'required',
                               'title' => 'required',
                               'date' => 'required',
                               'time' => 'required',
                           ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if(\Auth::user()->can('create meeting'))
        {
            $meeting                = new Meeting();
            $meeting->branch_id     = $request->branch_id;
            $meeting->employee_id   = json_encode($request->employee_id);
            $meeting->title         = $request->title;
            $meeting->date          = $request->date;
            $meeting->time          = $request->time;
            $meeting->created_by    = \Auth::user()->id;

            $meeting->save();
    
            $employeeIds = $request->employee_id;
            foreach($employeeIds as $employeeId)
            {
                $meetingEmployee              = new MeetingEmployee();
                $meetingEmployee->meeting_id  = $meeting->id; 
                $meetingEmployee->employee_id = $employeeId;
                $meetingEmployee->created_by  = \Auth::user()->id;
                $meetingEmployee->save();
            }

            return redirect()->route('meeting.index')->with('success', __('Meeting Time successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Meeting $meeting)
    {
        return redirect()->route('meeting.index');
    }

    public function edit($meeting)
    {
        if(\Auth::user()->can('edit meeting'))
        {
            $meeting    = Meeting::find($meeting);
            $employees  = Employee::whereHas('user', function($query) {
                $query->where('is_active', 1);
            })->pluck('name', 'id');
                    

            return view('meeting.edit', compact('meeting', 'employees'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Meeting $meeting)
    {
        if(\Auth::user()->can('edit meeting'))
        {
            $validator = \Validator::make(
                $request->all(), [

                                   'title' => 'required',
                                   'date' => 'required',
                                   'time' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $meeting->title = $request->title;
            $meeting->date  = $request->date;
            $meeting->time  = $request->time;
            $meeting->note  = $request->note;
            $meeting->save();

            return redirect()->route('meeting.index')->with('success', __('Meeting Time successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Meeting $meeting)
    {
        if(\Auth::user()->can('delete meeting'))
        {

            if(Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $meeting->delete();

                return redirect()->route('meeting.index')->with('success', __('Meeting successfully deleted.'));
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

    public function getdepartment(Request $request)
    {

        if($request->branch_id == 0)
        {
            $departments = Department::all()->pluck('name', 'id')->toArray();
        }
        else
        {
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($departments);
    }

    public function getemployee(Request $request)
    {
        if(in_array('0', $request->department_id))
        {
            $employees = Employee::all()->pluck('name', 'id')->toArray();
        }
        else
        {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->whereIn('department_id', $request->department_id)->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($employees);
    }
}
