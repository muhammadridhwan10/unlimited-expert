<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Event;
use App\Models\EventEmployee;
use App\Models\Projects;
use App\Models\Tasks;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage event'))
        {
            if(Auth::user()->type == 'admin')
            {
                $employees = Employee::all();
                $events    = Event::all();
                $transdate = date('Y-m-d', time());
    
                $today_date = date('m');
                $current_month_event = Event::select('id','start_date','end_date', 'title', 'created_at','color')->whereRaw('MONTH(start_date)=' . $today_date,'MONTH(end_date)=' . $today_date)->get();
    
                $arrEvents = [];
                foreach($events as $event)
                {
                    $arr['id']        = $event['id'];
                    $arr['title']     = $event['title'];
                    $arr['start']     = $event['start_date'];
                    $arr['end']       = $event['end_date'];
                    $arr['className'] = 'event-primary';
                    $arr['url']       = route('event.edit', $event['id']);
                    $arrEvents[]      = $arr;
                }
                $arrEvents = str_replace('"[', '[', str_replace(']"', ']', json_encode($arrEvents)));
    
                return view('event.index', compact('arrEvents', 'employees', 'transdate','events','current_month_event'));
            }
            elseif(\Auth::user()->type == 'company')
            {
                $employees = Employee::all();
                $events    = Event::all();
                $transdate = date('Y-m-d', time());
    
                $today_date = date('m');
                $current_month_event = Event::select('id','start_date','end_date', 'title', 'created_at','color')->whereRaw('MONTH(start_date)=' . $today_date,'MONTH(end_date)=' . $today_date)->get();
    
                $arrEvents = [];
                foreach($events as $event)
                {
                    $arr['id']        = $event['id'];
                    $arr['title']     = $event['title'];
                    $arr['start']     = $event['start_date'];
                    $arr['end']       = $event['end_date'];
                    $arr['className'] = 'event-primary';
                    $arr['url']       = route('event.edit', $event['id']);
                    $arrEvents[]      = $arr;
                }
                $arrEvents = str_replace('"[', '[', str_replace(']"', ']', json_encode($arrEvents)));
    
                return view('event.index', compact('arrEvents', 'employees', 'transdate','events','current_month_event'));
            }
            else
            {
                $user      = \Auth::user();
                $employees = Employee::where('user_id', '=', $user->id)->first();
                $events    = Event::where('employee_id', '=', $employees->id)->get();
                $transdate = date('Y-m-d', time());
    
                $today_date = date('m');
                $current_month_event = Event::select('id','start_date','end_date', 'title', 'created_at','color')->whereRaw('MONTH(start_date)=' . $today_date,'MONTH(end_date)=' . $today_date)->get();
    
                $arrEvents = [];
                foreach($events as $event)
                {
                    $arr['id']        = $event['id'];
                    $arr['title']     = $event['title'];
                    $arr['start']     = $event['start_date'];
                    $arr['end']       = $event['end_date'];
                    $arr['className'] = 'event-primary';
                    $arr['url']       = route('event.edit', $event['id']);
                    $arrEvents[]      = $arr;
                }
                $arrEvents = str_replace('"[', '[', str_replace(']"', ']', json_encode($arrEvents)));
    
                return view('event.index', compact('arrEvents', 'employees', 'transdate','events','current_month_event'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create event'))
        {
            if(\Auth::user()->type == 'admin')
            {
                $employees   = Employee::all()->pluck('name', 'id');
                $branch      = Branch::all();
                $departments = Department::all();
            }
            elseif(\Auth::user()->type == 'company')
            {
                $employees   = Employee::all()->pluck('name', 'id');
                $branch      = Branch::all();
                $departments = Department::all();
            }
            else
            {
                $employees   = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branch      = Branch::where('created_by', '=', \Auth::user()->creatorId())->get();
                $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get();
            }

            return view('event.create', compact('employees', 'branch', 'departments'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        if(\Auth::user()->can('create event'))
        {

            $validator = \Validator::make($request->all(), [
                'title' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'color' => 'required',
            ]);
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if(\Auth::user()->type == 'staff IT' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior accounting' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'intern')
            {
                $user = \Auth::user();
                $employee = Employee::where('user_id', $user->id)->first();

                $event                = new Event();
                $event->branch_id     = $employee->branch_id;
                $event->department_id = 0;
                $event->employee_id   = $employee->id;
                $event->title         = $request->title;
                $event->start_date    = $request->start_date;
                $event->end_date      = $request->end_date;
                $event->color         = $request->color;
                $event->description   = $request->description;
                $event->created_by    = \Auth::user()->creatorId();
                $event->save();
            }
            else
            {
                $event                = new Event();
                $event->branch_id     = $request->branch_id;
                $event->department_id = json_encode($request->department_id);
                $event->employee_id   = json_encode($request->employee_id);
                $event->title         = $request->title;
                $event->start_date    = $request->start_date;
                $event->end_date      = $request->end_date;
                $event->color         = $request->color;
                $event->description   = $request->description;
                $event->created_by    = \Auth::user()->creatorId();
                $event->save();
            }

            if(in_array('0', [$request->employee_id]))
            {
                $departmentEmployee = Employee::whereIn('department_id', [$request->department_id])->get()->pluck('id');
                $departmentEmployee = $departmentEmployee;
            }
            else
            {
                $departmentEmployee = [$request->employee_id];
            }
            foreach($departmentEmployee as $employee)
            {
                $eventEmployee              = new EventEmployee();
                $eventEmployee->event_id    = $event->id;
                $eventEmployee->employee_id = $event->employee_id;
                $eventEmployee->created_by  = Auth::user()->creatorId();
                $eventEmployee->save();
            }

            //Slack Notification
            $setting = Utility::settings(\Auth::user()->creatorId());
            $branch  = Branch::find($request->branch_id);
            if(isset($setting['event_notification']) && $setting['event_notification'] == 1)
            {
                $msg = $request->title . ' ' . __("event created for branch") . ' ' . $branch->name . ' ' . __("from") . ' ' . $request->start_date . ' ' . __("to") . ' ' . $request->end_date . '.';
                Utility::send_slack_msg($msg);
            }

            //Telegram Notification
            $setting = Utility::settings(\Auth::user()->creatorId());
            $branch  = Branch::find($request->branch_id);
            if(isset($setting['telegram_event_notification']) && $setting['telegram_event_notification'] == 1)
            {
                $msg = $request->title . ' ' . __("event created for branch") . ' ' . $branch->name . ' ' . __("from") . ' ' . $request->start_date . ' ' . __("to") . ' ' . $request->end_date . '.';
                Utility::send_telegram_msg($msg);
            }


            return redirect()->route('event.index')->with('success', __('Event  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Event $event)
    {
        return redirect()->route('event.index');
    }

    public function edit($event)
    {

        if(\Auth::user()->can('edit event'))
        {
            $event = Event::find($event);
            $user      = \Auth::user();
            $employees = Employee::where('user_id', '=', $user->id)->first();
            if($event->employee_id == $employees->id)
            {
                $employees = Employee::where('employee_id', '=', $employees->id)->get()->pluck('name', 'id');

                return view('event.edit', compact('event', 'employees'));
            }
            elseif(Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $employees = Employee::get()->pluck('name', 'id');

                return view('event.edit', compact('event', 'employees'));
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

    public function update(Request $request, Event $event)
    {
        if(\Auth::user()->can('edit event'))
        {
            if($event->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make($request->all(), [
                    'title' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'color' => 'required',
                ]);
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $event->title       = $request->title;
                $event->start_date  = $request->start_date;
                $event->end_date    = $request->end_date;
                $event->color       = $request->color;
                $event->description = $request->description;
                $event->save();

                return redirect()->route('event.index')->with('success', __('Event successfully updated.'));
            }
            elseif(Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $validator = \Validator::make($request->all(), [
                    'title' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'color' => 'required',
                ]);
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $event->title       = $request->title;
                $event->start_date  = $request->start_date;
                $event->end_date    = $request->end_date;
                $event->color       = $request->color;
                $event->description = $request->description;
                $event->save();

                return redirect()->route('event.index')->with('success', __('Event successfully updated.'));
            }
            elseif(\Auth::user()->type == 'staff IT' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior accounting' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'intern')
            {
                $validator = \Validator::make($request->all(), [
                    'title' => 'required',
                    'start_date' => 'required',
                    'color' => 'required',
                ]);
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $event->title       = $request->title;
                $event->start_date  = $request->start_date;
                $event->end_date    = $request->end_date;
                $event->color       = $request->color;
                $event->description = $request->description;
                $event->save();

                return redirect()->route('event.index')->with('success', __('Event successfully updated.'));
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

    public function destroy(Event $event)
    {
        if(\Auth::user()->can('delete event'))
        {
            if($event->created_by == \Auth::user()->creatorId())
            {
                $event->delete();

                return redirect()->route('event.index')->with('success', __('Event successfully deleted.'));
            }
            elseif(Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $event->delete();

                return redirect()->route('event.index')->with('success', __('Event successfully deleted.'));
            }
            elseif(\Auth::user()->type == 'staff IT' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior accounting' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'intern')
            {
                $event->delete();

                return redirect()->route('event.index')->with('success', __('Event successfully deleted.'));
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

        if(in_array('0', [$request->department_id]))
        {
            $employees = Employee::all()->pluck('name', 'id')->toArray();
        }
        else
        {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->whereIn('department_id', [$request->department_id])->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($employees);
    }
}
