<?php

namespace App\Http\Controllers;

use App\Models\TimeTracker;
use App\Models\TrackPhoto;
use App\Models\Utility;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\Projects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TimeTrackerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if($user->type == 'admin' || $user->type == 'company')
        {
            $treckers = TimeTracker::all();
            $branch      = Branch::get();
            $department = Department::get();

            $data['branch']     = __('All');
            $data['department'] = __('All');

            $employee = User::all();
            $employee = $employee->pluck('id');
            $employeeTimeTracker = TimeTracker::whereIn('created_by', $employee);

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeTimeTracker->whereBetween('start_time', [$start_date, $end_date]);
            } 

            $employeeTimeTracker = $employeeTimeTracker->get();

        }
        elseif($user->type == 'partners')
        {
            $branch      = Branch::get();
            $department = Department::get();

            $data['branch']     = __('All');
            $data['department'] = __('All');

            $employee = Employee::where('user_id', \Auth::user()->id)->first();
            $employeebranch = Employee::where('branch_id', $employee->branch_id);
            $employees = $employeebranch->pluck('user_id');
            $employeeTimeTracker = TimeTracker::whereIn('created_by', $employees);

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeTimeTracker->whereBetween('start_time', [$start_date, $end_date]);
            } 

            $employeeTimeTracker = $employeeTimeTracker->get();

        }
        else
        {
            $branch      = Branch::get();
            $department = Department::get();

            $data['branch']     = __('All');
            $data['department'] = __('All');
            $employeeTimeTracker = TimeTracker::where('created_by',\Auth::user()->id);

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeTimeTracker->whereBetween('start_time', [$start_date, $end_date]);
            } 

            $employeeTimeTracker = $employeeTimeTracker->get();
        }
        return view('time_trackers.index',compact('employeeTimeTracker','branch', 'department'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TimeTracker  $timeTracker
     * @return \Illuminate\Http\Response
     */
    public function show(TimeTracker $timeTracker)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TimeTracker  $timeTracker
     * @return \Illuminate\Http\Response
     */
    public function edit(TimeTracker $timeTracker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TimeTracker  $timeTracker
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TimeTracker $timeTracker)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TimeTracker  $timeTracker
     * @return \Illuminate\Http\Response
     */
    public function destroy($timetracker_id)
    {
//        return redirect()->back()->with('error',__('This operation is not perform due to demo mode.'));
        // if(Auth::user()->can('delete timesheet'))
        // {
            $timetrecker = TimeTracker::find($timetracker_id);
            $timetrecker->delete();

                return redirect()->back()->with('success', __('TimeTracker successfully deleted.'));
        // }
        // else
        // {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }



    }

    public function getTrackerImages(Request $request){

        $tracker = TimeTracker::find($request->id);

        $images = TrackPhoto::where('track_id',$request->id)->get();
        // dd($images->toArray());
        // dd($tracker);
        return view('time_trackers.images',compact('images','tracker'));
    }

    public function removeTrackerImages(Request $request){



        $images = TrackPhoto::find($request->id);
        if($images){
            $url= $images->img_path;
            if($images->delete()){
                \Storage::delete($url);
                return Utility::success_res(__('Tracker Photo remove successfully.'));
            }else{
                return Utility::error_res(__('opps something wren wrong.'));
            }
        }else{
            return Utility::error_res(__('opps something wren wrong.'));
        }

    }

    public function removeTracker(Request $request)
    {


        $track = TimeTracker::find($request->input('id'));
        if($track)
        {
            $track->delete();

            return Utility::success_res(__('Track remove successfully.'));
        }
        else
        {
            return Utility::error_res(__('Track not found.'));
        }
    }

    public function search_json(Request $request)
    {
        $month = $request->input('month');
        $user = Auth::user();
        if($user->type == 'admin' || $user->type == 'company')
        {
            $timeTrackers = TimeTracker::with('user');
        }
        elseif($user->type == 'partners')
        {
            $employee = Employee::where('user_id', \Auth::user()->id)->first();
            $employeebranch = Employee::where('branch_id', $employee->branch_id);
            $employees = $employeebranch->pluck('user_id');
            $timeTrackers = TimeTracker::with('user')->whereIn('created_by', $employees);
        }
        else
        {
            $timeTrackers = TimeTracker::with('user')->where('created_by',\Auth::user()->id);
        }

        if (!empty($request->month)) {
            $month = date('m', strtotime($request->month));
            $year  = date('Y', strtotime($request->month));

            $start_date = date($year . '-' . $month . '-01');
            $end_date   = date($year . '-' . $month . '-t');

            $timeTrackers->whereBetween('start_time', [$start_date, $end_date]);
        } 
        
        $timeTrackers = $timeTrackers->get();

        return response()->json($timeTrackers);
    }
}
