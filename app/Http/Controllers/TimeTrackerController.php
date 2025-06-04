<?php

namespace App\Http\Controllers;

use App\Models\TimeTracker;
use App\Models\TrackPhoto;
use App\Models\Utility;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\Project;
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

            $client =   User::where('type','=','client')->pluck('name','id');
            $filter_clients = $request->client_id;

            $employess =   User::where('type','!=','client')->where('is_active', 1)->pluck('name','id');

            $status = Project::$project_status;
            $label = Project::$label;

            $projectStatus = $request->status;
            $projectLabel = $request->label;

            
            $employeeTimeTracker = TimeTracker::whereIn('created_by', $employee);

            // Filter start_date (hanya tanggal)
            if (isset($request->start_date) && !empty($request->start_date)) {
                $startDate = $request->start_date; // Input sudah dalam format Y-m-d
                $employeeTimeTracker->whereDate('start_time', '>=', $startDate);
            }

            // Filter end_date (hanya tanggal)
            if (isset($request->end_date) && !empty($request->end_date)) {
                $endDate = $request->end_date; // Input sudah dalam format Y-m-d
                $employeeTimeTracker->whereDate('start_time', '<=', $endDate);
            }

            if (!empty($request->user_ids)) {
                $selectedEmployees = $request->user_ids;
                $employeeTimeTracker->where('created_by', $selectedEmployees);
            }

            if (!empty($request->client_id)) {
                $employeeTimeTracker->whereHas('project', function ($query) use ($filter_clients) {
                    $query->whereIn('client_id', (array) $filter_clients);
                });
            }
            
            if (!empty($request->status)) {
                $employeeTimeTracker->whereHas('project', function ($query) use ($projectStatus) {
                    $query->where('status', $projectStatus);
                });
            }

            if (!empty($request->label)) {
                $employeeTimeTracker->whereHas('project', function ($query) use ($projectLabel) {
                    $query->where('label', $projectLabel);
                });
            }

            $employeeTimeTracker = $employeeTimeTracker->orderByDesc('id')->paginate(10)->appends([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'user_ids' => $request->user_ids,
                'client_id' => $request->client_id,
                'status' => $request->status,
                'label' => $request->label,
            ]);     

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

            $client =   User::where('type','=','client')->pluck('name','id');
            $filter_clients = $request->client_id;

            $employess = User::whereIn('id', $employees)
            ->where('type', '!=', 'client')
            ->where('is_active', 1)
            ->pluck('name', 'id');


            $status = Project::$project_status;
            $label = Project::$label;

            $projectStatus = $request->status;
            $projectLabel = $request->label;

            // Filter start_date (hanya tanggal)
            if (isset($request->start_date) && !empty($request->start_date)) {
                $startDate = $request->start_date; // Input sudah dalam format Y-m-d
                $employeeTimeTracker->whereDate('start_time', '>=', $startDate);
            }

            // Filter end_date (hanya tanggal)
            if (isset($request->end_date) && !empty($request->end_date)) {
                $endDate = $request->end_date; // Input sudah dalam format Y-m-d
                $employeeTimeTracker->whereDate('start_time', '<=', $endDate);
            }

            if (!empty($request->user_ids)) {
                $selectedEmployees = $request->user_ids;
                $employeeTimeTracker->where('created_by', $selectedEmployees);
            }

            if (!empty($request->client_id)) {
                $employeeTimeTracker->whereHas('project', function ($query) use ($filter_clients) {
                    $query->whereIn('client_id', (array) $filter_clients);
                });
            }
            
            if (!empty($request->status)) {
                $employeeTimeTracker->whereHas('project', function ($query) use ($projectStatus) {
                    $query->where('status', $projectStatus);
                });
            }

            if (!empty($request->label)) {
                $employeeTimeTracker->whereHas('project', function ($query) use ($projectLabel) {
                    $query->where('label', $projectLabel);
                });
            }

            $employeeTimeTracker = $employeeTimeTracker->orderByDesc('id')->paginate(10)->appends([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'user_ids' => $request->user_ids,
                'client_id' => $request->client_id,
                'status' => $request->status,
                'label' => $request->label,
            ]);    

        }
        else
        {
            $branch      = Branch::get();
            $department = Department::get();

            $data['branch']     = __('All');
            $data['department'] = __('All');

            $employee = Employee::where('user_id', \Auth::user()->id)->first();

            $employeebranch = Employee::where('branch_id', $employee->branch_id);
            $employees = $employeebranch->pluck('user_id');

            $employeeTimeTracker = TimeTracker::whereIn('created_by', $employees);

            $client =   User::where('type','=','client')->pluck('name','id');
            $filter_clients = $request->client_id;


            if($employee->branch_id == 1)
            {
                $employess = User::whereIn('id', $employees)
                ->where('type', '!=', 'client')
                ->whereHas('employee', function ($query) {
                    $query->where('branch_id', 1);
                })
                ->get()->pluck('name', 'id');
            }
            elseif($employee->branch_id  == 2)
            {
                $employess = User::whereIn('id', $employees)
                ->where('type', '!=', 'client')
                ->whereHas('employee', function ($query) {
                    $query->where('branch_id', 2);
                })
                ->get()->pluck('name', 'id');
            }
            elseif($employee->branch_id  == 3)
            {
                $employess = User::whereIn('id', $employees)
                ->where('type', '!=', 'client')
                ->whereHas('employee', function ($query) {
                    $query->where('branch_id', 3);
                })
                ->get()->pluck('name', 'id');
            }


            $status = Project::$project_status;
            $label = Project::$label;

            $projectStatus = $request->status;
            $projectLabel = $request->label;

            // Filter start_date (hanya tanggal)
            if (isset($request->start_date) && !empty($request->start_date)) {
                $startDate = $request->start_date; // Input sudah dalam format Y-m-d
                $employeeTimeTracker->whereDate('start_time', '>=', $startDate);
            }

            // Filter end_date (hanya tanggal)
            if (isset($request->end_date) && !empty($request->end_date)) {
                $endDate = $request->end_date; // Input sudah dalam format Y-m-d
                $employeeTimeTracker->whereDate('start_time', '<=', $endDate);
            }

            if (!empty($request->user_ids)) {
                $selectedEmployees = $request->user_ids;
                $employeeTimeTracker->where('created_by', $selectedEmployees);
            }

            if (!empty($request->client_id)) {
                $employeeTimeTracker->whereHas('project', function ($query) use ($filter_clients) {
                    $query->whereIn('client_id', (array) $filter_clients);
                });
            }
            
            if (!empty($request->status)) {
                $employeeTimeTracker->whereHas('project', function ($query) use ($projectStatus) {
                    $query->where('status', $projectStatus);
                });
            }

            if (!empty($request->label)) {
                $employeeTimeTracker->whereHas('project', function ($query) use ($projectLabel) {
                    $query->where('label', $projectLabel);
                });
            }

            $employeeTimeTracker = $employeeTimeTracker->orderByDesc('id')->paginate(10)->appends([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'user_ids' => $request->user_ids,
                'client_id' => $request->client_id,
                'status' => $request->status,
                'label' => $request->label,
            ]); 
        }
        return view('time_trackers.index',compact('employeeTimeTracker','branch','employess','client', 'department','status','label'));

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

    public function trackerJson(Request $request, $project_id)
    {
        $month = $request->input('month');
        $user = Auth::user();

        $timeTrackers = TimeTracker::with('user')->where('project_id', $project_id);

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
