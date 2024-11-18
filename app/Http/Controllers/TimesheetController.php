<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Utility;
use App\Models\Timesheet;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ProjectTask;
use App\Models\ProjectUser;
use App\Models\TimeTracker;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use App\Exports\TimesheetExport;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class TimesheetController extends Controller
{
    public function timesheetView(Request $request, $project_id)
    {
        $authuser = Auth::user();
        
        if($authuser->can('manage timesheet'))
        {
            $project = Project::find($project_id);
            
            $assigned_user_ids = ProjectUser::where('project_id', $project_id)->pluck('user_id');

            $employee = User::whereIn('id', $assigned_user_ids)->get()->pluck('name', 'id');
            $employee->prepend('Select Employee', '0');
            
            $employeeTimesheet = Timesheet::where('project_id', $project_id);

            if (!empty($request->user_id)) {
                $employeeTimesheet->where('created_by', $request->user_id);
            }

            if (!empty($request->date)) {
                $employeeTimesheet->whereDate('date', $request->date);
            }

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = $year . '-' . $month . '-01';
                $end_date   = $year . '-' . $month . '-t';

                $employeeTimesheet->whereBetween('date', [$start_date, $end_date]);
            }

            $employeeTimesheet = $employeeTimesheet->orderByDesc('id')->paginate(10);

            if (!empty($request->export_excel)) {
                $exportData = $this->prepareExportData($employeeTimesheet);
                $exportDataArray = $exportData->toArray();

                return Excel::download(new TimesheetExport($exportDataArray), 'timesheet_report.xlsx');
            }

            return view('projects.timesheets.index', compact('employeeTimesheet', 'project', 'employee'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function appendTimesheetTaskHTML(Request $request)
    {

        $project_id     = $request->has('project_id') ? $request->project_id : null;

        $task_id        = $request->has('task_id') ? $request->task_id : null;
        $selected_dates = $request->has('selected_dates') ? $request->selected_dates : null;

        $returnHTML = '';

        $project = Project::find($project_id);

        if($project)
        {
            $task = ProjectTask::find($task_id);

            if($task && $selected_dates)
            {
                $twoDates = explode(' - ', $selected_dates);

                $first_day   = $twoDates[0];
                $seventh_day = $twoDates[1];

                $period = CarbonPeriod::create($first_day, $seventh_day);

                $returnHTML .= '<tr><td class="task-name">' . $task->name . '</td>';

                foreach($period as $key => $dateobj)
                {
                    $returnHTML .= '<td>
 <input class="form-control border-dark wid-120 task-time day-time1 task-time" data-ajax-timesheet-popup="true" data-type="create" data-task-id="' . $task->id . '" data-date="' . $dateobj->format('Y-m-d') . '" data-url="' . route('timesheet.create', $project_id) . '" value="00:00">';


                }

                $returnHTML .= '<td>
<input class="form-control border-dark wid-120 task-time total-task-time"  type="text" value="00:00" disabled>';
            }
        }

        return response()->json(
            [
                'success' => true,
                'html' => $returnHTML,
            ]
        );
    }

    public function filterTimesheetTableView(Request $request)
    {
        $sectionTaskArray = [];
//        $authuser         = Auth::user();

        $project = Project::find($request->project_id);
        if(Auth::user() != null){
            $authuser         = Auth::user();
        }else{
            $authuser         = User::where('id',$project->created_by)->first();
        }

        $week             = $request->week;
        $project_id       = $request->project_id;
        $timesheet_type   = 'task';

        if($request->has('week') && $request->has('project_id'))
        {
          if($authuser->type == 'client'){

            $project_ids = Project::where('client_id',\Auth::user()->id)->pluck('id','id')->toArray();
          }else{

            $project_ids = $authuser->projects()->pluck('project_id','project_id')->toArray();
          }
            $timesheets  = Timesheet::select('timesheets.*')->join('projects', 'projects.id', '=', 'timesheets.project_id');

            if($timesheet_type == 'task')
            {
                $projects_timesheet = $timesheets->join('project_tasks', 'project_tasks.id', '=', 'timesheets.task_id');
            }
            if($project_id == '0')
            {
                $projects_timesheet = $timesheets->whereIn('projects.id', $project_ids);
            }
            else if(in_array($project_id, $project_ids))
            {
                $projects_timesheet = $timesheets->where('timesheets.project_id', $project_id);

            }

            $days               = Utility::getFirstSeventhWeekDay($week);
            $first_day          = $days['first_day'];
            $seventh_day        = $days['seventh_day'];
            $onewWeekDate       = $first_day->format('M d') . ' - ' . $seventh_day->format('M d, Y');
            $selectedDate       = $first_day->format('Y-m-d') . ' - ' . $seventh_day->format('Y-m-d');
            $projects_timesheet = $projects_timesheet->whereDate('date', '>=', $first_day->format('Y-m-d'))->whereDate('date', '<=', $seventh_day->format('Y-m-d'));

            if($project_id == '0')
            {
                $timesheets = $projects_timesheet->get()->groupBy(
                    [
                        'project_id',
                        'task_id',
                    ]
                )->toArray();
            }
            else if(in_array($project_id, $project_ids))
            {
                $timesheets = $projects_timesheet->get()->groupBy('task_id')->toArray();

            }

            $returnHTML = Project::getProjectAssignedTimesheetHTML($projects_timesheet, $timesheets, $days, $project_id);

            $totalrecords = count($timesheets);
            if($project_id != '0')
            {
                $task_ids = array_keys($timesheets);

                $project  = Project::find($project_id);

                $sections = ProjectTask::getAllSectionedTaskList($request, $project, [], $task_ids);

                foreach($sections as $key => $section)
                {
                    $taskArray                              = [];
                    $sectionTaskArray[$key]['section_id']   = $section['section_id'];
                    $sectionTaskArray[$key]['section_name'] = $section['section_name'];

                    foreach($section['sections'] as $taskkey => $task)
                    {
                        $taskArray[$taskkey]['task_id']   = $task['id'];
                        $taskArray[$taskkey]['task_name'] = $task['taskinfo']['task_name'];
                    }
                    $sectionTaskArray[$key]['tasks'] = $taskArray;
                }
            }

            return response()->json(
                [
                    'success' => true,
                    'totalrecords' => $totalrecords,
                    'selectedDate' => $selectedDate,
                    'sectiontasks' => $sectionTaskArray,
                    'onewWeekDate' => $onewWeekDate,
                    'html' => $returnHTML,
                ]
            );
        }
    }

    public function timesheetCreate(Request $request)
    {
        if(\Auth::user()->can('create timesheet'))
        {
            $parseArray = [];

            $authuser      = Auth::user();
            $project_id    = $request->has('project_id') ? $request->project_id : null;
            $task_id       = $request->has('task_id') ? $request->task_id : null;
            $selected_date = $request->has('date') ? $request->date : null;
            $user_id       = $request->has('date') ? $request->user_id : null;

            $created_by = $user_id != null ? $user_id : $authuser->id;

            $projects = $authuser->projects();

            if($project_id)
            {
                $project = $projects->where('projects.id', '=', $project_id)->pluck('projects.project_name', 'projects.id')->all();

                if(!empty($project) && count($project) > 0)
                {

                    $project_id   = key($project);
                    $project_name = $project[$project_id];

                    $task = ProjectTask::where(
                        [
                            'project_id' => $project_id,
                            'id' => $task_id,
                        ]
                    )->pluck('name', 'id')->all();

                    $task_id   = key($task);
                    $task_name = $task[$task_id];

                    $tasktime = Timesheet::where('task_id', $task_id)->where('created_by', $created_by)->pluck('time')->toArray();

                    $totaltasktime = Utility::calculateTimesheetHours($tasktime);

                    $totalhourstimes = explode(':', $totaltasktime);

                    $totaltaskhour   = $totalhourstimes[0];
                    $totaltaskminute = $totalhourstimes[1];

                    $parseArray = [
                        'project_id' => $project_id,
                        'project_name' => $project_name,
                        'task_id' => $task_id,
                        'task_name' => $task_name,
                        'date' => $selected_date,
                        'totaltaskhour' => $totaltaskhour,
                        'totaltaskminute' => $totaltaskminute,
                    ];

                    return view('projects.timesheets.create', compact('parseArray'));
                }
            }
            else
            {
                $projects = $projects->get();

                return view('projects.timesheets.create', compact('projects', 'project_id', 'selected_date'));
            }
        }

        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function timesheetStore(Request $request)
    {
        if(\Auth::user()->can('create timesheet'))
        {
            $authuser = Auth::user();
            $project  = Project::find($request->project_id);

            if($project)
            {

                $request->validate(
                    [
                        'date' => 'required',
                        'time_hour' => 'required',
                        'time_minute' => 'required',
                    ]
                );

                $hour   = $request->time_hour;
                $minute = $request->time_minute;

                $time = ($hour != '' ? ($hour < 10 ? '0' + $hour : $hour) : '00') . ':' . ($minute != '' ? ($minute < 10 ? '0' + $minute : $minute) : '00');

                $timesheet              = new Timesheet();
                $timesheet->project_id  = $request->project_id;
                $timesheet->task_id     = $request->task_id;
                $timesheet->date        = $request->date;
                $timesheet->time        = $time;
                $timesheet->description = $request->description;
                $timesheet->created_by  = $authuser->id;
                $timesheet->save();

                return redirect()->back()->with('success', __('Timesheet Created Successfully!'));
            }
        }

        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function timesheetEdit(Request $request, $project_id, $timesheet_id)
    {
        if(\Auth::user()->can('edit timesheet'))
        {
            $authuser = Auth::user();

            $task_id    = $request->has('task_id') ? $request->task_id : null;
            $user_id    = $request->has('date') ? $request->user_id : null;
            $created_by = $user_id != null ? $user_id : $authuser->id;

            $project_view = '';

            if($request->has('project_view'))
            {
                $project_view = $request->project_view;
            }

            $projects = $authuser->projects();

            $timesheet = Timesheet::find($timesheet_id);

            if($timesheet)
            {

                $project = $projects->where('projects.id', '=', $project_id)->pluck('projects.project_name', 'projects.id')->all();

                if(!empty($project) && count($project) > 0)
                {

                    $project_id   = key($project);
                    $project_name = $project[$project_id];

                    $task = ProjectTask::where(
                        [
                            'project_id' => $project_id,
                            'id' => $task_id,
                        ]
                    )->pluck('name', 'id')->all();

                    $task_id   = key($task);
                    $task_name = $task[$task_id];

                    $tasktime = Timesheet::where('task_id', $task_id)->where('created_by', $created_by)->pluck('time')->toArray();

                    $totaltasktime = Utility::calculateTimesheetHours($tasktime);

                    $totalhourstimes = explode(':', $totaltasktime);

                    $totaltaskhour   = $totalhourstimes[0];
                    $totaltaskminute = $totalhourstimes[1];

                    $time = explode(':', $timesheet->time);

                    $parseArray = [
                        'project_id' => $project_id,
                        'project_name' => $project_name,
                        'task_id' => $task_id,
                        'task_name' => $task_name,
                        'time_hour' => $time[0] < 10 ? $time[0] : $time[0],
                        'time_minute' => $time[1] < 10 ? $time[1] : $time[1],
                        'totaltaskhour' => $totaltaskhour,
                        'totaltaskminute' => $totaltaskminute,
                    ];

                    return view('projects.timesheets.edit', compact('timesheet', 'parseArray'));
                }
            }
        }

        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function timesheetUpdate(Request $request, $timesheet_id)
    {
        if(\Auth::user()->can('edit timesheet'))
        {
            $project = Project::find($request->project_id);

            if($project)
            {

                $request->validate(
                    [
                        'date' => 'required',
                        'time_hour' => 'required',
                        'time_minute' => 'required',
                    ]
                );

                $hour   = $request->time_hour;
                $minute = $request->time_minute;

                $time = ($hour != '' ? ($hour < 10 ? '0' + $hour : $hour) : '00') . ':' . ($minute != '' ? ($minute < 10 ? '0' + $minute : $minute) : '00');

                $timesheet              = Timesheet::find($timesheet_id);
                $timesheet->project_id  = $request->project_id;
                $timesheet->task_id     = $request->task_id;
                $timesheet->date        = $request->date;
                $timesheet->time        = $time;
                $timesheet->description = $request->description;
                $timesheet->save();

                return redirect()->back()->with('success', __('Timesheet Updated Successfully!'));
            }
        }

        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function timesheetDestroy($timesheet_id)
    {
        if(\Auth::user()->can('delete timesheet'))
        {
            $timesheet = Timesheet::find($timesheet_id);

            if($timesheet)
            {
                $timesheet->delete();
            }

            return redirect()->back()->with('success', __('Timesheet deleted Successfully!'));
        }

        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function timesheetList()
    {
            return view('projects.timesheet_list');
    }

    public function timesheetListGet(Request $request)
    {
        $authuser = Auth::user();
        $week     = $request->week;

        if($request->has('week') && $request->has('project_id'))
        {
            $project_id = $request->project_id;

            $project_ids        = $authuser->projects()->pluck('project_id')->toArray();
            $timesheets         = Timesheet::select('timesheets.*')->join('projects', 'projects.id', '=', 'timesheets.project_id');
            $projects_timesheet = $timesheets->join('project_tasks', 'project_tasks.id', '=', 'timesheets.task_id');

            if($project_id == '0')
            {
                $projects_timesheet = $timesheets->whereIn('projects.id', $project_ids);
            }
            else if(in_array($project_id, $project_ids))
            {
                $projects_timesheet = $timesheets->where('timesheets.project_id', $project_id);
            }

            $days        = Utility::getFirstSeventhWeekDay($week);
            $first_day   = $days['first_day'];
            $seventh_day = $days['seventh_day'];

            $onewWeekDate = $first_day->format('M d') . ' - ' . $seventh_day->format('M d, Y');
            $selectedDate = $first_day->format('Y-m-d') . ' - ' . $seventh_day->format('Y-m-d');

            $projects_timesheet = $projects_timesheet->whereDate('date', '>=', $first_day->format('Y-m-d'))->whereDate('date', '<=', $seventh_day->format('Y-m-d'));

            if($project_id == '0')
            {
                $timesheets = $projects_timesheet->get()->groupBy(
                    [
                        'project_id',
                        'task_id',
                    ]
                )->toArray();
            }
            else if(in_array($project_id, $project_ids))
            {
                $timesheets = $projects_timesheet->get()->groupBy('task_id')->toArray();
            }

            $returnHTML = Project::getProjectAssignedTimesheetHTML($projects_timesheet, $timesheets, $days, $project_id);

            $totalrecords = count($timesheets);

            if($project_id != '0')
            {
                $task_ids = array_keys($timesheets);
                $project  = Project::find($project_id);
                $sections = ProjectTask::getAllSectionedTaskList($request, $project, [], $task_ids);

                foreach($sections as $key => $section)
                {
                    $taskArray = [];

                    $sectionTaskArray[$key]['section_id']   = $section['section_id'];
                    $sectionTaskArray[$key]['section_name'] = $section['section_name'];

                    foreach($section['sections'] as $taskkey => $task)
                    {
                        $taskArray[$taskkey]['task_id']   = $task['id'];
                        $taskArray[$taskkey]['task_name'] = $task['taskinfo']['task_name'];
                    }
                    $sectionTaskArray[$key]['tasks'] = $taskArray;
                }
            }

            return response()->json(
                [
                    'success' => true,
                    'totalrecords' => $totalrecords,
                    'selectedDate' => $selectedDate,
                    'sectiontasks' => $sectionTaskArray,
                    'onewWeekDate' => $onewWeekDate,
                    'html' => $returnHTML,
                ]
            );
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if($user->type == 'admin' || $user->type == 'company' || $user->type == 'partners')
        {
            $currentYear = now()->year;
            $employeeTimesheet = Timesheet::query();

            $employee = User::where('type', '!=', 'client' )->get()->pluck('name', 'id');
            $employee->prepend('Select Employee', '0');

            $client =   User::where('type','=','client')->pluck('name','id');
            $client->prepend('Select Client', '');

            $filter_project = $request->project_id;
            $filter_status = $request->status;
            $filter_client = $request->client_id;

            $project      = Project::get()->pluck('project_name', 'id');
            $project->prepend('All Project', '0');

            $startDate = !empty($request->start_date) ? $request->start_date : null;
            $endDate = !empty($request->end_date) ? $request->end_date : null;

            if ($startDate && $endDate) {
                $employeeTimesheet->whereBetween('date', [$startDate, $endDate]);
            }

            if (!empty($request->user_id)) {
                $selectedEmployees = $request->user_id;
                $employeeTimesheet->where('created_by', $selectedEmployees);
            }

            if (!empty($request->status)) {
                $employeeTimesheet
                ->whereHas('project', function ($query) use ($filter_status) {
                    $query->where('status', $filter_status);
                });
            }

            if (!empty($request->project_id)) {
                $employeeTimesheet
                ->whereHas('project', function ($query) use ($filter_project) {
                    $query->where('id', $filter_project);
                });
            }

            if (!empty($request->client_id)) {
                $employeeTimesheet
                ->whereHas('project', function ($query) use ($filter_client) {
                    $query->where('client_id', $filter_client);
                });
            }

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeTimesheet->whereBetween('date', [$start_date, $end_date]);
            } 

            $employeeTimesheet->whereYear('date', $currentYear);

            $totalTimesheet = $employeeTimesheet->get();
            $totalSeconds = 0;

            foreach ($totalTimesheet as $timesheet) {
                list($hours, $minutes, $seconds) = explode(':', $timesheet->time);

                $totalSeconds += $hours * 3600 + $minutes * 60 + $seconds;
            }

            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;

            $logged_hours = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

            $employeeTimesheet = $employeeTimesheet->orderByDesc('id')->paginate(10)->appends([
                'project_id' => $request->project_id,
                'client_id' => $request->client_id,
                'user_id' => $request->user_id,
                'status' => $request->status,
                'date' => $request->date,
                'month' => $request->month,
            ]);  

            if (!empty($request->export_excel)) {

                $exportData = $this->prepareExportData($totalTimesheet);
        
                $exportDataArray = $exportData->toArray();

                return Excel::download(new TimesheetExport($exportDataArray), 'timesheet_report.xlsx');
            }
        }
        elseif($user->type == 'senior accounting' || $user->type == 'senior audit' || $user->type == 'manager audit')
        {
            $currentYear = now()->year;
            $employeeTimesheet = Timesheet::query();

            // Ambil data user yang login
            $loggedInUser = \Auth::user();

            // Ambil cabang dari user yang login
            $branchId = $loggedInUser->employee->branch_id;

            // Filter berdasarkan cabang yang sama
            $employeeTimesheet->whereHas('user', function ($query) use ($branchId) {
                $query->whereHas('employee', function ($subQuery) use ($branchId) {
                    $subQuery->where('branch_id', $branchId);
                });
            });

            if($branchId == 1)
            {
                $employee = User::where('type', '!=', 'client')
                ->whereHas('employee', function ($query) {
                    $query->where('branch_id', 1);
                })
                ->get()->pluck('name', 'id');
            }
            elseif($branchId == 2)
            {
                $employee = User::where('type', '!=', 'client')
                ->whereHas('employee', function ($query) {
                    $query->where('branch_id', 2);
                })
                ->get()->pluck('name', 'id');
            }
            elseif($branchId == 3)
            {
                $employee = User::where('type', '!=', 'client')
                ->whereHas('employee', function ($query) {
                    $query->where('branch_id', 3);
                })
                ->get()->pluck('name', 'id');
            }

            $employee->prepend('Select Employee', '0');

            $client =   User::where('type','=','client')->pluck('name','id');
            $client->prepend('Select Client', '');

            $filter_project = $request->project_id;
            $filter_status = $request->status;
            $filter_client = $request->client_id;

            $project      = Project::get()->pluck('project_name', 'id');
            $project->prepend('All Project', '0');

            $startDate = !empty($request->start_date) ? $request->start_date : null;
            $endDate = !empty($request->end_date) ? $request->end_date : null;

            if ($startDate && $endDate) {
                $employeeTimesheet->whereBetween('date', [$startDate, $endDate]);
            }

            if (!empty($request->user_id)) {
                $selectedEmployees = $request->user_id;
                $employeeTimesheet->where('created_by', $selectedEmployees);
            }

            if (!empty($request->status)) {
                $employeeTimesheet
                ->whereHas('project', function ($query) use ($filter_status) {
                    $query->where('status', $filter_status);
                });
            }

            if (!empty($request->date)) {
                $selectedDate = $request->date;
                $employeeTimesheet->whereDate('date', $selectedDate);
            }

            if (!empty($request->project_id)) {
                $employeeTimesheet
                ->whereHas('project', function ($query) use ($filter_project) {
                    $query->where('id', $filter_project);
                });
            }

            if (!empty($request->client_id)) {
                $employeeTimesheet
                ->whereHas('project', function ($query) use ($filter_client) {
                    $query->where('client_id', $filter_client);
                });
            }

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeTimesheet->whereBetween('date', [$start_date, $end_date]);
            } 

            $employeeTimesheet->whereYear('date', $currentYear);

            $totalTimesheet = $employeeTimesheet->get();
            $totalSeconds = 0;

            foreach ($totalTimesheet as $timesheet) {
                list($hours, $minutes, $seconds) = explode(':', $timesheet->time);

                $totalSeconds += $hours * 3600 + $minutes * 60 + $seconds;
            }

            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;

            $logged_hours = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

            $employeeTimesheet = $employeeTimesheet->orderByDesc('id')->paginate(10)->appends([
                'project_id' => $request->project_id,
                'client_id' => $request->client_id,
                'user_id' => $request->user_id,
                'status' => $request->status,
                'date' => $request->date,
                'month' => $request->month,
            ]);  

            if (!empty($request->export_excel)) {

                $exportData = $this->prepareExportData($totalTimesheet);
        
                $exportDataArray = $exportData->toArray();

                return Excel::download(new TimesheetExport($exportDataArray), 'timesheet_report.xlsx');
            }
        }
        else
        {
            $currentYear = now()->year;
            $employeeTimesheet = Timesheet::where('created_by',\Auth::user()->id);

            $employee = Employee::where('user_id', \Auth::user()->id)->get()->pluck('name', 'id');

            $employee->prepend('Select Employee', '0');

            $client =   User::where('type','=','client')->pluck('name','id');
            $client->prepend('Select Client', '');

            $filter_project = $request->project_id;
            $filter_status = $request->status;
            $filter_client = $request->client_id;

            $project      = Project::get()->pluck('project_name', 'id');
            $project->prepend('All Project', '0');

            $startDate = !empty($request->start_date) ? $request->start_date : null;
            $endDate = !empty($request->end_date) ? $request->end_date : null;

            if ($startDate && $endDate) {
                $employeeTimesheet->whereBetween('date', [$startDate, $endDate]);
            }

            if (!empty($request->user_id)) {
                $selectedEmployees = $request->user_id;
                $employeeTimesheet->where('created_by', $selectedEmployees);
            }

            if (!empty($request->status)) {
                $employeeTimesheet
                ->whereHas('project', function ($query) use ($filter_status) {
                    $query->where('status', $filter_status);
                });
            }

            if (!empty($request->date)) {
                $selectedDate = $request->date;
                $employeeTimesheet->whereDate('date', $selectedDate);
            }

            if (!empty($request->project_id)) {
                $employeeTimesheet
                ->whereHas('project', function ($query) use ($filter_project) {
                    $query->where('id', $filter_project);
                });
            }

            if (!empty($request->client_id)) {
                $employeeTimesheet
                ->whereHas('project', function ($query) use ($filter_client) {
                    $query->where('client_id', $filter_client);
                });
            }

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeTimesheet->whereBetween('date', [$start_date, $end_date]);
            } 

            $employeeTimesheet->whereYear('date', $currentYear);

            $totalTimesheet = $employeeTimesheet->get();
            $totalSeconds = 0;

            foreach ($totalTimesheet as $timesheet) {
                list($hours, $minutes, $seconds) = explode(':', $timesheet->time);

                $totalSeconds += $hours * 3600 + $minutes * 60 + $seconds;
            }

            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;

            $logged_hours = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

            $employeeTimesheet = $employeeTimesheet->orderByDesc('id')->paginate(10)->appends([
                'project_id' => $request->project_id,
                'client_id' => $request->client_id,
                'user_id' => $request->user_id,
                'status' => $request->status,
                'date' => $request->date,
                'month' => $request->month,
            ]);  

            if (!empty($request->export_excel)) {

                $exportData = $this->prepareExportData($totalTimesheet);
        
                $exportDataArray = $exportData->toArray();

                return Excel::download(new TimesheetExport($exportDataArray), 'timesheet_report.xlsx');
            }
        }
        return view('projects.timesheet_list',compact('employeeTimesheet','project','employee','logged_hours','client'));

    }

    public function create()
    {

        if(\Auth::user()->can('create timesheet'))
        {
            if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {

                $assign_pro_ids = ProjectUser::where('user_id',\Auth::user()->id)->pluck('project_id');

                $projects      = Project::with(['tasks' => function($query)
                {
                    $user = auth()->user();
                    $query->whereRaw("find_in_set('" . $user->id . "',assign_to)")->get();
        
                }])->whereIn('id', $assign_pro_ids)->get();
                
            }
            else
            {
                $assign_pro_ids = ProjectUser::where('user_id',\Auth::user()->id)->pluck('project_id');

                $projects      = Project::with(['tasks' => function($query)
                {
                    $user = auth()->user();
                    $query->whereRaw("find_in_set('" . $user->id . "',assign_to)")->get();
        
                }])->whereIn('id', $assign_pro_ids)->get();
            }
        
            return view('projects.timesheets.create', compact('projects'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        if(\Auth::user()->can('create timesheet'))
        {

            $timesheet = $request->items;
            $date = $request->date;

            for($i = 0; $i < count($timesheet); $i++)
            {
                $timesheets                = new Timesheet();
                $timesheets->date          = $date;
                $timesheets->project_id    = $timesheet[$i]['project_id'];
                $hour                      = $timesheet[$i]['time_hour'];
                $minute                    = $timesheet[$i]['time_minute'];
                $formattedHour             = ($hour != '' ? ($hour < 10 ? '0' . $hour : $hour) : '00');
                $formattedMinute           = ($minute != '' ? ($minute < 10 ? '0' . $minute : $minute) : '00');
                $timesheets->time          = $formattedHour . ':' . $formattedMinute;
                $timesheets->task_id       = 0;
                $timesheets->created_by    = \Auth::user()->id;
                $timesheets->platform      = 'Web';
                $timesheets->save();
            }


            return redirect()->route('timesheet.index')->with('success', __('Timesheet successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($timesheet_id)
    {
        if(\Auth::user()->can('edit timesheet'))
        {
            $id           = Crypt::decrypt($timesheet_id);
            $timesheet    = Timesheet::find($id);
            $assign_pro_ids = ProjectUser::where('user_id',\Auth::user()->id)->pluck('project_id');

            $projects      = Project::with(['tasks' => function($query)
            {
                $user = auth()->user();
                $query->whereRaw("find_in_set('" . $user->id . "',assign_to)")->get();
    
            }])->whereIn('id', $assign_pro_ids)->get();

            return view('projects.timesheets.edit', compact('timesheet','projects'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function update(Request $request, $timesheet_id)
    {

        if(\Auth::user()->can('edit timesheet'))
        {

            $authuser = Auth::user();
            $post = $request->all();

            $timesheet = Timesheet::find($timesheet_id);
            $hour = $request->input('time_hour');
            $minute = $request->input('time_minute');

            $formattedHour = ($hour != '' ? ($hour < 10 ? '0' . $hour : $hour) : '00');
            $formattedMinute = ($minute != '' ? ($minute < 10 ? '0' . $minute : $minute) : '00');
            $formattedTime = $formattedHour . ':' . $formattedMinute;

            $timesheet->time = $formattedTime;
            $timesheet->platform      = 'Web';

            $timesheet->update($post);

            return redirect()->back()->with('success', __('Timesheet Updated successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function destroy($timesheet_id)
    {
        if(\Auth::user()->can('delete timesheet'))
        {
            $timesheet    = Timesheet::find($timesheet_id);
            $timesheet->delete();

            return redirect()->back()->with('success', __('Time successfully deleted!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function prepareExportData($employeeTimesheet)
    {
        $exportData = new Collection();
        $dailyHours = [];
        $dailyRecords = [];
        $totalTimeInSeconds = 0;
        $totalPositiveInSeconds = 0;
        $totalNegativeInSeconds = 0;

        foreach ($employeeTimesheet as $timesheet_user) {
            $date = $timesheet_user->date;
            $projectName = $timesheet_user->project->project_name ?? '-';

            // Gunakan kombinasi date dan project_name sebagai kunci unik
            $dateProjectKey = $date . '_' . $projectName;

            // Ambil data TimeTracker yang sesuai dengan date
            $timeTrackerForDate = $timesheet_user->timeTrackers->first();

            // Jika sudah ada entry untuk proyek pada tanggal yang sama, kita tambahkan waktu
            if (isset($dailyRecords[$dateProjectKey])) {
                // Tambahkan waktu ke data yang sudah ada
                if (!empty($timesheet_user->time)) {
                    list($hours, $minutes, $seconds) = explode(':', $timesheet_user->time);
                    $timeInSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
                    $totalTimeInSeconds += $timeInSeconds;

                    $dailyHours[$dateProjectKey] += $timeInSeconds;
                }
            } else {
                // Entry baru untuk proyek pada tanggal ini
                $data = [
                    'Employee' => !empty($timesheet_user->user->name) ? $timesheet_user->user->name : '-',
                    'Date' => $date ?? '-',
                    'Project Name' => $projectName,
                    'Start Time' => $timeTrackerForDate ? $timeTrackerForDate->start_time : '-',
                    'End Time' => $timeTrackerForDate ? $timeTrackerForDate->end_time : '-',
                    'Time' => !empty($timesheet_user->time) ? $this->formatTime($timesheet_user->time) : '-',
                    'Hours Shortfall' => '-',
                    'Platform' => !empty($timesheet_user->platform) ? $timesheet_user->platform : '-',
                    'Status Project' => !empty($timesheet_user->project->status) ? $timesheet_user->project->status : '-',
                ];

                $dailyRecords[$dateProjectKey] = $data;

                if (!empty($timesheet_user->time)) {
                    list($hours, $minutes, $seconds) = explode(':', $timesheet_user->time);
                    $timeInSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
                    $totalTimeInSeconds += $timeInSeconds;

                    $dailyHours[$dateProjectKey] = $timeInSeconds;
                }
            }
        }

        // Target jam kerja per hari (9 jam dalam detik)
        $targetSecondsPerDay = 9 * 3600;

        // Memproses data untuk setiap proyek pada tanggal tertentu
        foreach ($dailyRecords as $key => $record) {
            $totalSeconds = $dailyHours[$key];
            $difference = $totalSeconds - $targetSecondsPerDay;

            if ($difference >= 0) {
                $totalPositiveInSeconds += $difference;
            } else {
                $totalNegativeInSeconds += abs($difference);
            }

            $sign = $difference >= 0 ? '+' : '-';
            $difference = abs($difference);
            $diffHours = floor($difference / 3600);
            $diffMinutes = floor(($difference % 3600) / 60);
            $diffSeconds = $difference % 60;
            $hoursShortfall = $sign . sprintf('%02d:%02d:%02d', $diffHours, $diffMinutes, $diffSeconds);

            // Update waktu gabungan dan selisih jam kerja
            $totalHours = floor($totalSeconds / 3600);
            $totalMinutes = floor(($totalSeconds % 3600) / 60);
            $totalSecondsFormatted = sprintf('%02d:%02d:%02d', $totalHours, $totalMinutes, $totalSeconds % 60);
            
            $record['Time'] = $totalSecondsFormatted;
            $record['Hours Shortfall'] = $hoursShortfall;

            $exportData->push($record);
        }

        $totalHours = floor($totalTimeInSeconds / 3600);
        $totalMinutes = floor(($totalTimeInSeconds % 3600) / 60);
        $totalSeconds = $totalTimeInSeconds % 60;
        $totalTimeFormatted = sprintf('%02d:%02d:%02d', $totalHours, $totalMinutes, $totalSeconds);

        $netShortfallInSeconds = $totalPositiveInSeconds - $totalNegativeInSeconds;
        $netSign = $netShortfallInSeconds >= 0 ? '+' : '-';
        $netShortfallInSeconds = abs($netShortfallInSeconds);

        $netHours = floor($netShortfallInSeconds / 3600);
        $netMinutes = floor(($netShortfallInSeconds % 3600) / 60);
        $netSeconds = $netShortfallInSeconds % 60;
        $netShortfallFormatted = $netSign . sprintf('%02d:%02d:%02d', $netHours, $netMinutes, $netSeconds);

        $exportData->push([
            'Employee' => 'Total',
            'Date' => '-',
            'Project Name' => '-',
            'Start Time' => '-',
            'End Time' => '-',
            'Time' => $totalTimeFormatted,
            'Hours Shortfall' => $netShortfallFormatted,
            'Platform' => '-',
            'Status Project' => '-',
        ]);

        return $exportData;
    }

    protected function formatTime($time)
    {
        return gmdate("H:i:s", strtotime($time) - strtotime('00:00:00'));
    }

    // public function calculateLoggedHours($projectId, $userId)
    // {
    //     $logged_hours = 0;
    //     $timesheets = Timesheet::where('project_id', $projectId)->where('created_by', $userId)->get();

    //     foreach ($timesheets as $timesheet) {
    //         $hours = date('H', strtotime($timesheet->time));
    //         $minutes = date('i', strtotime($timesheet->time));
    //         $total_hours = $hours + ($minutes / 60);
    //         $logged_hours += $total_hours;
    //     }

    //     return number_format($logged_hours, 2, '.', '');
    // }


}
