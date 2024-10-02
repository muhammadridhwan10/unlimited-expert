<?php

namespace App\Http\Controllers;
use App\Models\Milestone;
use App\Models\Projectstages;
use App\Models\TaskStage;
use App\Models\TaskChecklist;
use App\Models\TaskFile;
use App\Models\TaskComment;
use App\Models\User;
use Auth;
use App\Models\Utility;
use App\Models\ProjectTask;
use App\Models\ProjectStage;
use App\Models\ProjectMilestone;
use App\Models\Timesheet;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\UserDefualtView;
use App\Exports\task_reportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ProjectReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
        public function index(Request $request)
        {
            $user = \Auth::user();

            if($user->type == 'client')
            {
                $projects = Project::where('client_id', '=', $user->id);
                $users=[];
                $status=[];

            }
            elseif(\Auth::user()->type == 'company')
            {

                if(isset($request->all_users)&& !empty($request->all_users)){
                    $projects = Project::select('projects.*')
                        ->leftjoin('project_users', 'project_users.project_id', 'projects.id')
                        ->where('project_users.user_id', '=', $request->all_users)->orderby('id','desc');


                }else{
                    $projects = Project::orderby('id','desc');
                }

                if(isset($request->status)&& !empty($request->status)){
                    $projects->where('status', '=', $request->status);
                }
                if(isset($request->label)&& !empty($request->label)){
                    $projects->where('label', '=', $request->label);
                }
                if(isset($request->tags)&& !empty($request->tags)){
                    $projects->where('tags', '=', $request->tags);
                }
                if (isset($request->start_date) && !empty($request->start_date)) {
                    $projects->whereHas('timesheets', function ($query) use ($request) {
                        $query->where('date', '>=', $request->start_date);
                    });
                }
                
                if (isset($request->end_date) && !empty($request->end_date)) {
                    $projects->whereHas('timesheets', function ($query) use ($request) {
                        $query->where('date', '<=', $request->end_date);
                    });
                }

                $users = User::where('type', '!=', 'client')->get();
                $status = Project::$project_status;
                $label = Project::$label;
                $tags = Project::$tags;

            }
            elseif(\Auth::user()->type == 'admin')
            {

                if(isset($request->all_users)&& !empty($request->all_users)){
                    $projects = Project::select('projects.*')
                        ->leftjoin('project_users', 'project_users.project_id', 'projects.id')
                        ->where('project_users.user_id', '=', $request->all_users)->orderby('id','desc');


                }else{
                    $projects = Project::orderby('id','desc')->get();
                }

                if(isset($request->status)&& !empty($request->status)){
                    $projects->where('status', '=', $request->status);
                }
                if(isset($request->label)&& !empty($request->label)){
                    $projects->where('label', '=', $request->label);
                }
                if(isset($request->tags)&& !empty($request->tags)){
                    $projects->where('tags', '=', $request->tags);
                }

                if (isset($request->start_date) && !empty($request->start_date)) {
                    $projects->whereHas('timesheets', function ($query) use ($request) {
                        $query->where('date', '>=', $request->start_date);
                    });
                }
                
                if (isset($request->end_date) && !empty($request->end_date)) {
                    $projects->whereHas('timesheets', function ($query) use ($request) {
                        $query->where('date', '<=', $request->end_date);
                    });
                }

                $users = User::where('created_by', '=', $user->creatorId())->where('type', '!=', 'client')->get();
                $status = Project::$project_status;
                $label = Project::$label;
                $tags = Project::$tags;

            }
            else
            {
                $usr           = Auth::user();
                $users         = User::where('id', '=', $user->id)->get();
                $status        = Project::$project_status;
                $label         = Project::$label;
                $tags = Project::$tags;
                $projects = Project::select('projects.*')->leftjoin('project_users', 'project_users.project_id', 'projects.id')->where('project_users.user_id', '=', $user->id)->orderby('id','desc');

            }

            $totalAllProjectHours = $projects->get();

            $totalSeconds = 0;

            foreach ($totalAllProjectHours as $project) {
                $totalTime = $project->totalHours($request->start_date, $request->end_date, $request->all_users);
                list($hours, $minutes, $seconds) = explode(':', $totalTime);
                $totalSeconds += $hours * 3600 + $minutes * 60 + $seconds;
            }

            $totalHours = floor($totalSeconds / 3600);
            $totalMinutes = floor(($totalSeconds % 3600) / 60);
            $totalSeconds = $totalSeconds % 60;
            $totalFormattedTime = sprintf('%02d:%02d:%02d', $totalHours, $totalMinutes, $totalSeconds);

            $projects = $projects->paginate(10)->appends([
                'all_users' => $request->all_users,
                'status' => $request->status,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'tags' => $request->tags,
                'label' => $request->label,
            ]);    

            return view('project_report.index', compact('projects','users','status','label','tags','request','totalFormattedTime'));
        }


        public function show(Request $request,$id)
        {

            $id       = \Crypt::decrypt($id);

            $user = \Auth::user();

            if(\Auth::user()->type == 'admin')
            {
                $users = User::where('type', '!=', 'client')->where('type', '!=', 'staff_client')->get();
            }
            elseif(\Auth::user()->type == 'company')
            {
                $users = User::where('type', '!=', 'client')->where('type', '!=', 'staff_client')->get();
            }
            else
            {
                $users = User::where('created_by', '=', $user->creatorId())->where('type', '!=', 'client')->get();
            }

            if($user->type == 'client')
            {
                $project = Project::where('client_id', '=', $user->id)->where('id',$id)->first();
            }
            elseif(\Auth::user()->type !== 'client' && \Auth::user()->type !== 'staff_client')
            {

                $project = Project::find($id);

            }
            else
            {
                $project = Project::select('projects.*')->leftjoin('project_users', 'project_users.project_id', 'projects.id')->where('project_users.user_id', '=', $user->id)->first();
            }

            $count = $project->estimated_hrs;
            $category = Project::category_progress($count, $project->id);

            $Preengagement = $category['Preengagement'];
            $Riskassessment = $category['Riskassessment'];
            $Riskresponse = $category['Riskresponse'];
            $Conclutioncompletion = $category['Conclutioncompletion'];
            $totalhoursestimate = $Preengagement + $Riskassessment + $Riskresponse + $Conclutioncompletion;

            if ($user) {
                $chartData = $this->getProjectChart(
                    [
                        'project_id' => $id,
                        'duration' => 'week',
                        ]
                    );
                    $daysleft = round((((strtotime($user->end_date) - strtotime(date('Y-m-d'))) / 24) / 60) / 60);

                    $project_status_task = TaskStage::join("project_tasks", "project_tasks.stage_id", "=", "task_stages.id")
                        ->where('project_tasks.project_id', '=', $id)->groupBy('task_stages.name')
                        ->selectRaw('count(project_tasks.stage_id) as count, task_stages.name as task_stages_name')->pluck('count', 'task_stages_name');
    //                dd($project_status_task);

                    $totaltask = ProjectTask::where('project_id',$id)->count();



                    $arrProcessPer_status_task = [];
                    $arrProcess_Label_status_tasks = [];
                    foreach ($project_status_task as $lables => $percentage_stage) {
                         $arrProcess_Label_status_tasks[] = $lables;
                        if ($totaltask == 0) {
                            $arrProcessPer_status_task[] = 0.00;
                        } else {
                            $arrProcessPer_status_task[] = round(($percentage_stage * 100) / $totaltask, 2);
                        }
                    }


                    $project_priority_task = ProjectTask::where('project_id',$id)->groupBy('priority')->selectRaw('count(id) as count, priority')->pluck('count', 'priority');

                    $arrProcessPer_priority = [];
                    $arrProcess_Label_priority = [];
                    foreach ($project_priority_task as $lable => $process) {
                         $arrProcess_Label_priority[] = $lable;
                        if ($totaltask == 0) {
                            $arrProcessPer_priority[] = 0.00;
                        } else {
                            $arrProcessPer_priority[] = round(($process * 100) / $totaltask, 2);
                        }
                    }
                    $arrProcessClass = [
                        'text-success',
                        'text-primary',
                        'text-danger',
                    ];

                      $chartData = app('App\Http\Controllers\ProjectController')->getProjectChart([
                        'created_by' =>$id,
                        'duration' => 'week',
                    ]);

                    $stages = TaskStage::all();
                    // $stages = ProjectStage::where('created_by', '=', $user->id)->orderBy('order')->get();
                    // dd($stages);
                    $milestones = Milestone::where('project_id' ,$id)->get();
                    $logged_hour_chart = 0;
                    $total_hour = 0;
                    $logged_hour = 0;


                    $tasks = ProjectTask::where('project_id',$id)->get();
                    $data = [];
                    $rataratalink = 0;
                    $rataratacomment = 0;
                    $jumlahhari = 0;
                    $countsubtask = 0;
                    $counttasklink = 0;
                    $counttaskcomment = 0;
                    $totalchecked = 0;
                    foreach ($tasks as $task)
                    {
                        $projects = $task->project;

                        $awal_project  = $projects->start_date;
                        $akhir_project = $projects->end_date;
                        
                        $awal_project = strtotime($awal_project);
                        
                        $akhir_project = strtotime($akhir_project);
                        
                        $jumlahhari = array();
                        $sabtuminggu = array();
                        
                        for ($i = $awal_project; $i <= $akhir_project; $i += (60 * 60 * 24)) {
                            if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                                $jumlahhari[] = $i;
                            } else {
                                $sabtuminggu[] = $i;
                            }
                        
                        }
                        $jumlahhari = count($jumlahhari);

                        $jumlahtask = $tasks->count();

                        $totaltask        = intval($jumlahtask * 4);
                        $rataratalink     = intval($jumlahtask / $jumlahtask);
                        $rataratacomment  = intval($totaltask / $jumlahhari);

                        $targetlink    = $jumlahtask;
                        $targetcomment = intval($rataratacomment * $jumlahhari);

                        $countsubtask = TaskChecklist::where('project_id', $task->project_id)->where('parent_id','=', 0)->count();
                        $counttasklink = TaskChecklist::where('project_id', $task->project_id)->where('link','!=', NULL)->pluck('link')->count(). '/' .  $targetlink;
                        $counttaskcomment = TaskComment::where('project_id', $task->project_id)->count();
                        $countchecked = TaskChecklist::where('project_id', $task->project_id)->where('status', '=', 1)->count();
                        $timesheets_task = Timesheet::where('task_id',$task->id)->where('project_id',$id)->get();
                        $totalchecked = $countchecked . '/' .  $countsubtask;

                        foreach($timesheets_task as $timesheet)
                        {

                            $hours =  date('H', strtotime($timesheet->time));
                            $minutes =  date('i', strtotime($timesheet->time));
                            $total_hour = $hours + ($minutes/60) ;
                            $logged_hour += $total_hour ;
                            $logged_hour_chart = number_format($logged_hour, 2, '.', '');

                        }
                    }


                //Estimated Hours
                $esti_logged_hour_chart = ProjectTask::where('project_id',$id)->sum('estimated_hrs');



                $tasks = ProjectTask::where('project_id','=',$id)->get();


                return view('project_report.show', compact('user','users', 'rataratalink','rataratacomment', 'jumlahhari', 'countsubtask', 'counttasklink', 'counttaskcomment', 'totalchecked', 'arrProcessPer_status_task','arrProcess_Label_priority','esti_logged_hour_chart','logged_hour_chart','arrProcessPer_priority','arrProcess_Label_status_tasks','project','milestones', 'daysleft','chartData','arrProcessClass','stages','tasks','Preengagement', 'Riskassessment', 'Riskresponse', 'Conclutioncompletion', 'totalhoursestimate'));

         }
        }



        public function getProjectChart($arrParam)
            {
                $arrDuration = [];
                if ($arrParam['duration'] && $arrParam['duration'] == 'week') {
                    $previous_week = Utility::getFirstSeventhWeekDay(-1);
                    foreach ($previous_week['datePeriod'] as $dateObject) {
                        $arrDuration[$dateObject->format('Y-m-d')] = $dateObject->format('D');
                    }
                }

            $arrTask = [
                'label' => [],
                'color' => [],
            ];


            foreach ($arrDuration as $date => $label) {
                $objProject = ProjectTask::select('stage_id', \DB::raw('count(*) as total'))->whereDate('updated_at', '=', $date)->groupBy('stage_id');

                if (isset($arrParam['project_id'])) {
                    $objProject->where('project_id', '=', $arrParam['project_id']);
                }
                if (isset($arrParam['created_by'])) {
                    $objProject->whereIn(
                        'project_id', function ($query) use ($arrParam) {
                            $query->select('id')->from('projects')->where('created_by', '=', $arrParam['created_by']);
                        }
                    );
                }
                $data = $objProject->pluck('total', 'stage_id')->all();
                $arrTask['label'][] = __($label);

            return $arrTask;
            }
        }

        public function export($id)
        {
            $name = 'task_report_' . date('Y-m-d i:h:s');
            $data = Excel::download(new task_reportExport($id), $name . '.xlsx');
            return $data;
        }

}
