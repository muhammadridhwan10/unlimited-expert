<?php

namespace App\Http\Controllers;

use App\Models\TimeTracker;
use App\Models\User;
use App\Models\AuditPlan;
use App\Models\ProjectOfferings;
use App\Models\Project;
use App\Models\Utility;
use App\Models\Bug;
use App\Models\BugStatus;
use App\Models\BugFile;
use App\Models\BugComment;
use App\Models\CategoryTemplate;
use App\Models\Milestone;
use App\Models\ProjectTaskTemplate;
use App\Models\PublicAccountant;
use App\Models\ProductServiceCategory;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProjectNotification;
use Carbon\Carbon;
use App\Models\ActivityLog;
use DateTime;
use DatePeriod;
use DateInterval;
use App\Models\ProjectTask;
use App\Models\ProjectUser;
use App\Models\TaskStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Mail\InviteMemberNotification;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($view = 'grid')
    {
        if(\Auth::user()->can('manage project'))
        {
            return view('projects.index', compact('view'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(\Auth::user()->can('create project'))
        {
            if(\Auth::user()->type == 'company')
            {
                $users   = User::where('type', '!=', 'client')->where('type', '!=', 'admin')->get()->pluck('name', 'id');
                $clients = User::where('type', '=', 'client')->get()->pluck('name', 'id');
                $tasktemplate = ProductServiceCategory::get()->pluck('name', 'id');
                $public_accountant = PublicAccountant::get()->pluck('name', 'id');
                $public_accountant->prepend('Select Public Accountant', '');
                $clients->prepend('Select Client', '');
                $users->prepend('Select User', '');
                $tasktemplate->prepend('Select Task Template', '');
            }
            elseif(\Auth::user()->type == 'admin')
            {
                $users   = User::where('type', '!=', 'client')->get()->pluck('name', 'id');
                $clients = User::where('type', '=', 'client')->get()->pluck('name', 'id');
                $tasktemplate = ProductServiceCategory::get()->pluck('name', 'id');
                $public_accountant = PublicAccountant::get()->pluck('name', 'id');
                $public_accountant->prepend('Select Public Accountant', '');
                $clients->prepend('Select Client', '');
                $users->prepend('Select User', '');
                $tasktemplate->prepend('Select Task Template', '');
                
            }
            elseif(\Auth::user()->can('create project'))
            {
                $users   = User::where('type', '!=', 'client')->get()->pluck('name', 'id');
                $clients = User::where('type', '=', 'client')->get()->pluck('name', 'id');
                $tasktemplate = ProductServiceCategory::get()->pluck('name', 'id');
                $public_accountant = PublicAccountant::get()->pluck('name', 'id');
                $public_accountant->prepend('Select Public Accountant', '');
                $clients->prepend('Select Client', '');
                $users->prepend('Select User', '');
                $tasktemplate->prepend('Select Task Template', '');
                
            }
            else
            {
                $users   = User::where('type', '!=', 'client')->where('type', '!=', 'admin')->get()->pluck('name', 'id');
                $clients = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'client')->get()->pluck('name', 'id');
                $tasktemplate = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('category_id', 'id');
                $public_accountant = PublicAccountant::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $public_accountant->prepend('Select Public Accountant', '');
                $clients->prepend('Select Client', '');
                $users->prepend('Select User', '');
                $tasktemplate->prepend('Select Task Template', '');
            }
            return view('projects.create', compact('clients','users','public_accountant','tasktemplate'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if(\Auth::user()->can('create project'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'project_name' => 'required',
                                'project_image' => 'mimes:png,jpeg,jpg|max:20480',
                            ]
            );
            if($validator->fails())
            {
                return redirect()->back()->with('error', Utility::errorFormat($validator->getMessageBag()));
            }
            $project = new Project();
            $project->project_name = $request->project_name;

            if($request->start_date == null)
            {
                $project->start_date = \Carbon\Carbon::now();
            }
            else
            {
                $project->start_date = date("Y-m-d H:i:s", strtotime($request->start_date));
            }   

            $tanggal_mulai = $project->start_date;
            $tanggal_akhir = $request->total_days;
        
            $d = new DateTime($tanggal_mulai);
            $t = $d->getTimestamp();
        
            for($i=0; $i< $tanggal_akhir; $i++){
        
                $addDay = 86400;
        
                $nextDay = date('w', ($t+$addDay));
        
                if($nextDay == 0 || $nextDay == 6) {
                    $i--;
                }
        
                $t = $t+$addDay;
            }
        
            $d->setTimestamp($t);
        
            $end_date = $d->format( 'Y-m-d H:i:s' );

            $project->end_date = $end_date;

            $count = $tanggal_akhir * 8;

            if($request->hasFile('project_image'))
            {
                $imageName = time() . '.' . $request->project_image->extension();
                $request->file('project_image')->storeAs('projects', $imageName);
                $project->project_image      = 'projects/'.$imageName;
            }
            $project->client_id = $request->client;
            $project->public_accountant_id = $request->public_accountant_id;
            $project->template_task_id = $request->template_task_id;
            $project->budget = !empty($request->budget) ? $request->budget : 0;
            $project->description = $request->description;
            $project->status = $request->status;
            $project->estimated_hrs = $count;
            $project->book_year = $request->book_year;
            $project->tags = $request->tag;
            $project->label = $request->label;
            $project->created_by = \Auth::user()->creatorId();
            $project->save();

            $project_offerings = new ProjectOfferings();
            $project_offerings->project_id = $project->id;
            $project_offerings->als_partners = $request->als_partners;
            $project_offerings->rate_partners = $request->rate_partners;
            $project_offerings->als_manager = $request->als_manager;
            $project_offerings->rate_manager = $request->rate_manager;
            $project_offerings->als_senior_associate = $request->als_senior_associate;
            $project_offerings->rate_senior_associate = $request->rate_senior_associate;
            $project_offerings->als_associate = $request->als_associate;
            $project_offerings->rate_associate = $request->rate_associate;
            $project_offerings->als_intern = $request->als_intern;
            $project_offerings->rate_intern = $request->rate_intern;
            $project_offerings->save();

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $project->id,
                    'task_id' => 0,
                    'log_type' => 'Create Project',
                    'remark' => json_encode(['title' => $project->project_name]),
                ]
            );

            ProjectUser::create(
                [
                    'project_id' => $project->id,
                    'user_id' => Auth::user()->id,
                ]
            );

            $authuser = Auth::user();

            if($request->user){
              foreach($request->user as $key => $value) {
                ProjectUser::create(
                    [
                        'project_id' => $project->id,
                        'user_id' => $value,
                    ]
                );

                $firebaseToken = User::where('id', $value)->whereNotNull('device_token')->pluck('device_token');
                $SERVER_API_KEY = 'AAAA9odnGYA:APA91bEW0H4cOYVOnneXeKl-cE1ECxNFiRmwzEAdspRw34q6RwjGNqO2o6l_4T3HtyIR0ahZ5g8tb_0AST6RnxOchE8S6DEEby_HpwJHDk1H9GYmKwrcFRkPYWDiNvjTnQoIcDjj5Ogx';

                $data = [
                    "registration_ids" => $firebaseToken,
                    "notification" => [
                        "title" => 'AUP-APPS',
                        "body" => $authuser->name . ' inviting you into the project ' . $project->project_name,  
                        "icon" => 'https://i.postimg.cc/8z1vzXPV/logo-tgs-fix.png',
                        "content_available" => true,
                        "priority" => "high",
                    ]
                ];
                $dataString = json_encode($data);
            
                $headers = [
                    'Authorization: key=' . $SERVER_API_KEY,
                    'Content-Type: application/json',
                ];
            
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

                
                $response = curl_exec($ch);
                $datas = User::where('id', $value)->pluck('email');
                Mail::to($datas)->send(new ProjectNotification($project));
              }
            }

            // $projectUser = ProjectUser::with('user')->orderBy('id', 'DESC'); 
            // $id = $zoommeeting->user_id;
            // $data = User::whereIn('id', $id)->pluck('email');
            
            $template = Project::with('details')->get();
            foreach ($template as $templates) 
            {
                $details = $templates->details;
            }

            if($project->template_task_id !== NULL)
            {
                $category = $request->items;
                $category_id = $request->category_id;


                for($i = 0; $i < count($details); $i++)
                {
                    // dd($details);
                    $tasks                 = new ProjectTask();
                    $tasks->project_id     = $project->id;
                    $tasks->assign_to      = 0;
                    $tasks->stage_id       =  $details[$i]['stage_id'];
                    $tasks->name           = $details[$i]['name'];
                    $tasks->category_template_id      =  $details[$i]['category_template_id'];
                    $tasks->start_date     = $project->start_date;
                    $tasks->end_date       = $project->end_date;
                    $tasks->estimated_hrs  = $details[$i]['estimated_hrs'];
                    $tasks->description    = $details[$i]['description'];
                    $tasks->created_by     = \Auth::user()->creatorId();
                    $tasks->save();

                    ActivityLog::create(
                        [
                            'user_id' => \Auth::user()->id,
                            'project_id' => $project->id,
                            'task_id' => $tasks->id,
                            'log_type' => 'Create Task',
                            'remark' => json_encode(['title' => $tasks->name]),
                        ]
                    );
                }

                $category = Project::category_progress($count, $project->id); 

                $Preengagement = $category['TotalPreengagement'];
                $Riskassessment = $category['TotalRiskassessment'];
                $Riskresponse = $category['TotalRiskresponse'];
                $Conclutioncompletion = $category['TotalConclutioncompletion'];

                $task = ProjectTask::where('project_id','=', $project->id)->get();

                for($i = 0; $i < count($task); $i++)
                {
                    if($task[$i]['category_template_id'] == 1)
                    {
                        $estimated_hrs = 0;
                    }
                    elseif($task[$i]['category_template_id'] == 2)
                    {
                        $estimated_hrs = $Preengagement;
                    }
                    elseif($task[$i]['category_template_id'] == 3)
                    {
                        $estimated_hrs = $Riskassessment;
                    }
                    elseif($task[$i]['category_template_id'] == 4)
                    {
                        $estimated_hrs = $Riskresponse;
                    }
                    elseif($task[$i]['category_template_id'] == 5)
                    {
                        $estimated_hrs = $Conclutioncompletion;
                    }

                    ProjectTask::where(['id' => $task[$i]['id']])->update([
                        'estimated_hrs' => $estimated_hrs,
                    ]);

    
                }
            }
            else
            {
                $project = Project::find($project->id);

                $project->update(
                    [
                        'is_template' => 0,
                    ]
                );
            }
            

            //Slack Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
            if(isset($setting['project_notification']) && $setting['project_notification'] ==1){
                $msg = $request->project_name.' '.__(" created by").' ' .\Auth::user()->name.'.';
                Utility::send_slack_msg($msg);
            }

            //Telegram Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
            if(isset($setting['telegram_project_notification']) && $setting['telegram_project_notification'] ==1){
                $msg = __("New").' '.$request->project_name.' '.__("project").' '.__(" created by").' ' .\Auth::user()->name.'.';
                Utility::send_telegram_msg($msg);
            }

            return redirect()->route('projects.index')->with('success', __('Project Add Successfully'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Poject  $poject
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {

        if(\Auth::user()->can('view project'))
        {

            $usr           = Auth::user();
            if(\Auth::user()->type == 'client')
            {
                $user_projects = Project::where('client_id',\Auth::user()->id)->pluck('id','id')->toArray();;
            }elseif(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
            {
                $user_projects = Project::all()->pluck('id','id')->toArray();
            }else
            {
                $user_projects = $usr->projects->pluck('id')->toArray();
            }
            if(in_array($project->id, $user_projects))
            {
                $project_data = [];
                // Task Count
                $tasks = ProjectTask::where('project_id',$project->id)->get();
                $project_task         = $tasks->count();
                $completedTask = ProjectTask::where('project_id',$project->id)->where('is_complete',1)->get();

                $project_done_task    = $completedTask->count();

                $project_data['task'] = [
                    'total' => number_format($project_task),
                    'done' => number_format($project_done_task),
                    'percentage' => Utility::getPercentage($project_done_task, $project_task),
                ];

                // end Task Count

                // Expense
                $expAmt = 0;
                foreach($project->expense as $expense)
                {
                    $expAmt += $expense->amount;
                }

                $project_data['expense'] = [
                    'allocated' => $project->budget,
                    'total' => $expAmt,
                    'percentage' => Utility::getPercentage($expAmt, $project->budget),
                ];
                // end expense


                // Users Assigned
                $total_users = $project->users->where('type','!==','admin')->where('type','!==','company')->count();


                $project_data['user_assigned'] = [
                    'total' => number_format($total_users) . '/' . number_format($total_users),
                    'percentage' => Utility::getPercentage($total_users, $total_users),
                ];
                // end users assigned

                // Day left
                $total_day                = Carbon::parse($project->start_date)->diffInDays(Carbon::parse($project->end_date));
                $remaining_day            = Carbon::parse($project->start_date)->diffInDays(now());
                $project_data['day_left'] = [
                    'day' => number_format($remaining_day) . '/' . number_format($total_day),
                    'percentage' => Utility::getPercentage($remaining_day, $total_day),
                ];
                // end Day left

                // Open Task
                if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
                {
                    $remaining_task = ProjectTask::where('project_id', '=', $project->id)->where('is_complete', '=', 0)->count();
                }
                else
                {
                    $remaining_task = ProjectTask::where('project_id', '=', $project->id)->where('is_complete', '=', 0)->where('created_by',\Auth::user()->creatorId())->count();
                }
                    $total_task     = $project->tasks->count();

                $project_data['open_task'] = [
                    'tasks' => number_format($remaining_task) . '/' . number_format($total_task),
                    'percentage' => Utility::getPercentage($remaining_task, $total_task),
                ];
                // end open task

                // Milestone
                $total_milestone           = $project->milestones()->count();
                $complete_milestone        = $project->milestones()->where('status', 'LIKE', 'complete')->count();
                $project_data['milestone'] = [
                    'total' => number_format($complete_milestone) . '/' . number_format($total_milestone),
                    'percentage' => Utility::getPercentage($complete_milestone, $total_milestone),
                ];
                // End Milestone

                // Time spent

                if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
                {
                    $times = $project->timesheets()->where('project_id', '=', $project->id)->pluck('time')->toArray();
                }
                else
                {
                    $times = $project->timesheets()->where('created_by', '=', $usr->id)->pluck('time')->toArray();
                }
                $totaltime                  = str_replace(':', '.', Utility::timeToHr($times));
                $project_data['time_spent'] = [
                    'total' => number_format($totaltime) . '/' . number_format($totaltime),
                    'percentage' => Utility::getPercentage(number_format($totaltime), $totaltime),
                ];
                // end time spent

                // Allocated Hours
                $hrs = Project::projectHrs($project->id);
                $project_data['task_allocated_hrs'] = [
                    'hrs' => number_format($hrs['allocated']) . '/' . number_format($hrs['allocated']),
                    'percentage' => Utility::getPercentage($hrs['allocated'], $hrs['allocated']),
                ];
                // end allocated hours

                // Chart
                $seven_days      = Utility::getLastSevenDays();
                $chart_task      = [];
                $chart_timesheet = [];
                $cnt             = 0;
                $cnt1            = 0;

                foreach(array_keys($seven_days) as $k => $date)
                {
                        $task_cnt     = $project->tasks()->where('is_complete', '=', 1)->whereRaw("find_in_set('" . $usr->id . "',assign_to)")->where('marked_at', 'LIKE', $date)->count();
                        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
                        {
                            $arrTimesheet = $project->timesheets()->where('project_id', '=', $project->id)->where('date', 'LIKE', $date)->pluck('time')->toArray();
                        }
                        else
                        {
                            $arrTimesheet = $project->timesheets()->where('created_by', '=', $usr->id)->where('date', 'LIKE', $date)->pluck('time')->toArray();
                        }
                        

                    // Task Chart Count
                    $cnt += $task_cnt;

                    // Timesheet Chart Count
                    $timesheet_cnt = str_replace(':', '.', Utility::timeToHr($arrTimesheet));
                    $cn[]          = $timesheet_cnt;
                    $cnt1          += $timesheet_cnt;

                    $chart_task[]      = $task_cnt;
                    $chart_timesheet[] = $timesheet_cnt;
                }

                $project_data['task_chart']      = [
                    'chart' => $chart_task,
                    'total' => $cnt,
                ];
                $project_data['timesheet_chart'] = [
                    'chart' => $chart_timesheet,
                    'total' => $cnt1,
                ];

                // end chart

                $auditplan = AuditPlan::where('project_id', $project->id)->get();
                $project_offerings = ProjectOfferings::where('project_id', $project->id)->get()->toArray();

                if(!empty($project_offerings))
                {
                    $co_partners = $project_offerings[0]['als_partners'] * $project_offerings[0]['rate_partners'];
                    $co_manager = $project_offerings[0]['als_manager'] * $project_offerings[0]['rate_manager'];
                    $co_senior_associate = $project_offerings[0]['als_senior_associate'] * $project_offerings[0]['rate_senior_associate'];
                    $co_associate = $project_offerings[0]['als_associate'] * $project_offerings[0]['rate_associate'];
                    $co_intern = $project_offerings[0]['als_intern'] * $project_offerings[0]['rate_intern'];
                }
                else
                {
                    $co_partners = 0;
                    $co_manager = 0;
                    $co_senior_associate = 0;
                    $co_associate = 0;
                    $co_intern = 0;
                }

                return view('projects.view',compact('project','project_offerings','co_partners','co_manager','co_senior_associate','co_associate','co_intern','project_data','auditplan'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Poject  $poject
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        $usr = \Auth::user();
        if(\Auth::user()->can('edit project'))
        {
            if($usr->type == 'admin')
            {
                $clients = User::where('type', '=', 'client')->get()->pluck('name', 'id');
                $project = Project::findOrfail($project->id);
                $tasktemplate = ProductServiceCategory::get()->pluck('name', 'id');
                $public_accountant = PublicAccountant::get()->pluck('name', 'id');
                $public_accountant->prepend('Select Public Accountant', '');
                $tasktemplate->prepend('Select Task Template', '');

                return view('projects.edit', compact('tasktemplate','public_accountant','project', 'clients'));

            }
            elseif($usr->type == 'company')
            {
                $clients = User::where('type', '=', 'client')->get()->pluck('name', 'id');
                $project = Project::findOrfail($project->id);
                $tasktemplate = ProductServiceCategory::get()->pluck('name', 'id');
                $public_accountant = PublicAccountant::get()->pluck('name', 'id');
                $public_accountant->prepend('Select Public Accountant', '');
                $tasktemplate->prepend('Select Task Template', '');

                return view('projects.edit', compact('tasktemplate','public_accountant', 'project', 'clients'));

            }
            elseif(\Auth::user()->can('edit project'))
            {
                $clients = User::where('type', '=', 'client')->get()->pluck('name', 'id');
                $project = Project::findOrfail($project->id);
                $tasktemplate = ProductServiceCategory::get()->pluck('name', 'id');
                $public_accountant = PublicAccountant::get()->pluck('name', 'id');
                $public_accountant->prepend('Select Public Accountant', '');
                $tasktemplate->prepend('Select Task Template', '');

                return view('projects.edit', compact('tasktemplate','public_accountant','project', 'clients'));
                
            }
            else{
                
                $clients = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'client')->get()->pluck('name', 'id');
                $project = Project::findOrfail($project->id);
                $tasktemplate = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('category_id', 'id');
                $public_accountant = PublicAccountant::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $public_accountant->prepend('Select Public Accountant', '');
                $tasktemplate->prepend('Select Task Template', '');
                if($project->created_by == \Auth::user()->creatorId())
                {
                    return view('projects.edit', compact('tasktemplate','public_accountant','project', 'clients'));
                }
                else
                {
                    return response()->json(['error' => __('Permission denied.')], 401);
                }
            }
            return view('projects.edit',compact('project','public_accountant'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Poject  $poject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        if(\Auth::user()->can('edit project'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'project_name' => 'required',
                                'project_image' => 'mimes:png,jpeg,jpg|max:20480',
                            ]
            );
            if($validator->fails())
            {
                return redirect()->back()->with('error', Utility::errorFormat($validator->getMessageBag()));
            }
            $project = Project::find($project->id);
            $project->project_name = $request->project_name;
            $project->start_date = date("Y-m-d H:i:s", strtotime($request->start_date));
            $project->end_date = date("Y-m-d H:i:s", strtotime($request->end_date));
            $tanggal_mulai = $project->start_date;
            $tanggal_akhir = $request->total_days;
        
            $d = new DateTime($tanggal_mulai);
            $t = $d->getTimestamp();
        
            for($i=0; $i< $tanggal_akhir; $i++){
        
                $addDay = 86400;
        
                $nextDay = date('w', ($t+$addDay));
        
                if($nextDay == 0 || $nextDay == 6) {
                    $i--;
                }
        
                $t = $t+$addDay;
            }
        
            $d->setTimestamp($t);
        
            $end_date = $d->format( 'Y-m-d H:i:s' );

            $project->end_date = $end_date;

            $count = $tanggal_akhir * 8;

            if($request->hasFile('project_image'))
            {
                Utility::checkFileExistsnDelete([$project->project_image]);
                $imageName = time() . '.' . $request->project_image->extension();
                $request->file('project_image')->storeAs('projects', $imageName);
                $project->project_image      = 'projects/'.$imageName;
            }
            $project->template_task_id = $request->template_task;
            // dd($project->template_task_id);
            $project->budget = $request->budget;
            $project->client_id = $request->client;
            $project->public_accountant_id = $request->public_accountant_id;
            $project->description = $request->description;
            $project->status = $request->status;
            $project->estimated_hrs = $count;
            $project->book_year = $request->book_year;
            $project->tags = $request->tag;
            $project->label = $request->label;
            $project->save();

            $project_offerings = ProjectOfferings::where('project_id','=', $project->id)->first();
            
            if($project_offerings !== NULL)
            {
                $project_offerings->update(
                    [
                        'als_partners' => $request->als_partners,
                        'rate_partners' => $request->rate_partners,
                        'als_manager' => $request->als_manager,
                        'rate_manager' => $request->rate_manager,
                        'als_senior_associate' => $request->als_senior_associate,
                        'rate_senior_associate' => $request->rate_senior_associate,
                        'als_associate' => $request->als_associate,
                        'rate_associate' => $request->rate_associate,
                        'als_intern' => $request->als_intern,
                        'rate_intern' => $request->rate_intern,
                    ]
                );
            }else
            {
                $project_offerings = new ProjectOfferings();
                $project_offerings->project_id = $project->id;
                $project_offerings->als_partners = $request->als_partners;
                $project_offerings->rate_partners = $request->rate_partners;
                $project_offerings->als_manager = $request->als_manager;
                $project_offerings->rate_manager = $request->rate_manager;
                $project_offerings->als_senior_associate = $request->als_senior_associate;
                $project_offerings->rate_senior_associate = $request->rate_senior_associate;
                $project_offerings->als_associate = $request->als_associate;
                $project_offerings->rate_associate = $request->rate_associate;
                $project_offerings->als_intern = $request->als_intern;
                $project_offerings->rate_intern = $request->rate_intern;
                $project_offerings->save();
            }
            

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $project->id,
                    'task_id' => 0,
                    'log_type' => 'Update Project',
                    'remark' => json_encode(['title' => $project->project_name]),
                ]
            );

            $template = Project::with('details')->get();
            foreach ($template as $templates) 
            {
                $details = $templates->details;
            }

            if($project->template_task_id !== NULL)
            {
                $category = $request->items;
                $category_id = $request->category_id;
    
                for($i = 0; $i < count($details); $i++)
                {
                    // dd($details);
                    $tasks                 = new ProjectTask();
                    $tasks->project_id     = $project->id;
                    $tasks->assign_to      = 0;
                    $tasks->stage_id       =  $details[$i]['stage_id'];
                    $tasks->name           = $details[$i]['name'];
                    $tasks->category_template_id      =  $details[$i]['category_template_id'];
                    $tasks->start_date     = $project->start_date;
                    $tasks->end_date       = $project->end_date;
                    $tasks->estimated_hrs  = $details[$i]['estimated_hrs'];
                    $tasks->description    = $details[$i]['description'];
                    $tasks->created_by     = \Auth::user()->creatorId();
                    $tasks->update();
    
                }
    
                $category = Project::category_progress($count, $project->id); 
    
                $Preengagement = $category['TotalPreengagement'];
                $Riskassessment = $category['TotalRiskassessment'];
                $Riskresponse = $category['TotalRiskresponse'];
                $Conclutioncompletion = $category['TotalConclutioncompletion'];
    
                $task = ProjectTask::where('project_id','=', $project->id)->get();
    
                for($i = 0; $i < count($task); $i++)
                {
                    if($task[$i]['category_template_id'] == 1)
                    {
                        $estimated_hrs = 0;
                    }
                    elseif($task[$i]['category_template_id'] == 2)
                    {
                        $estimated_hrs = $Preengagement;
                    }
                    elseif($task[$i]['category_template_id'] == 3)
                    {
                        $estimated_hrs = $Riskassessment;
                    }
                    elseif($task[$i]['category_template_id'] == 4)
                    {
                        $estimated_hrs = $Riskresponse;
                    }
                    elseif($task[$i]['category_template_id'] == 5)
                    {
                        $estimated_hrs = $Conclutioncompletion;
                    }
    
                    ProjectTask::where(['id' => $task[$i]['id']])->update([
                        'estimated_hrs' => $estimated_hrs,
                    ]);
    
    
                }

            }
            else
            {
                $project = Project::find($project->id);

                $project->update(
                    [
                        'is_template' => 0,
                    ]
                );
            }

            

            return redirect()->route('projects.index')->with('success', __('Project Updated Successfully'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Poject  $poject
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        if(\Auth::user()->can('delete project'))
        {
            if(!empty($project->image))
            {
                Utility::checkFileExistsnDelete([$project->project_image]);
            }
            $project->delete();
            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $project->id,
                    'task_id' => 0,
                    'log_type' => 'Delete Project',
                    'remark' => json_encode(['title' => $project->project_name]),
                ]
            );
            return redirect()->back()->with('success', __('Project Successfully Deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function inviteMemberView(Request $request, $project_id)
    {
        $usr          = Auth::user();
        $project      = Project::find($project_id);

        $user_project = $project->users->pluck('id')->toArray();

        if(\Auth::user()->type = 'admin')
        {
            $user_contact = User::where('type','!=','client')->whereNOTIn('id', $user_project)->pluck('id')->toArray();
        }
        elseif(\Auth::user()->type = 'company')
        {
            $user_contact = User::where('type','!=','client')->whereNOTIn('id', $user_project)->pluck('id')->toArray();
        }
        else
        {
            $user_contact = User::where('created_by', \Auth::user()->creatorId())->where('type','!=','client')->whereNOTIn('id', $user_project)->pluck('id')->toArray();
        }
        $arrUser      = array_unique($user_contact);
        $users        = User::whereIn('id', $arrUser)->get();

        return view('projects.invite', compact('project_id', 'users'));
    }

    public function inviteClientView(Request $request, $project_id)
    {
        $usr          = Auth::user();
        $project      = Project::find($project_id);

        $user_project = $project->users->pluck('id')->toArray();

        if(\Auth::user()->type = 'client')
        {
            $user_contact = User::where('type','=','staff_client')->whereNOTIn('id', $user_project)->pluck('id')->toArray();
        }
        else
        {
            $user_contact = User::where('created_by', \Auth::user()->creatorId())->where('type','!=','client')->whereNOTIn('id', $user_project)->pluck('id')->toArray();
        }
        $arrUser      = array_unique($user_contact);
        $users        = User::whereIn('id', $arrUser)->get();

        return view('projects.invite-client', compact('project_id', 'users'));
    }

    public function inviteProjectUserMember(Request $request)
    {
        $authuser = Auth::user();

        // Make entry in project_user tbl

        $post                   = [];
        $post['project_id']     = $request->project_id;
        $post['user_id']        = $request->user_id;
        $post['invited_by']     = $authuser->id;

        $inviteuser = ProjectUser::create($post);

            
        // ProjectUser::create(
        //     [
        //         'project_id' => $request->project_id,
        //         'user_id' => $request->user_id,
        //         'invited_by' => $authuser->id,
        //     ]
        // );

        $users = User::where('id', $request->user_id)->pluck('name');

        $member = User::where('id', $request->user_id)->pluck('email');
        Mail::to($member)->send(new InviteMemberNotification($inviteuser));

        $project = $inviteuser->project;

        $firebaseToken = User::whereIn('id', [$request->user_id])->whereNotNull('device_token')->pluck('device_token');
        $SERVER_API_KEY = 'AAAA9odnGYA:APA91bEW0H4cOYVOnneXeKl-cE1ECxNFiRmwzEAdspRw34q6RwjGNqO2o6l_4T3HtyIR0ahZ5g8tb_0AST6RnxOchE8S6DEEby_HpwJHDk1H9GYmKwrcFRkPYWDiNvjTnQoIcDjj5Ogx';

        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => 'AUP-APPS',
                "body" => $authuser->name . '  inviting you into the project ' . $project->project_name,  
                "icon" => 'https://i.postimg.cc/8z1vzXPV/logo-tgs-fix.png',
                "content_available" => true,
                "priority" => "high",
            ]
        ];
        $dataString = json_encode($data);
    
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        // Make entry in activity_log tbl
        ActivityLog::create(
            [
                'user_id' => $authuser->id,
                'project_id' => $request->project_id,
                'log_type' => 'Invite User',
                'remark' => json_encode(['title' => $users]),
            ]
        );

        return json_encode(
            [
                'code' => 200,
                'status' => 'Success',
                'success' => __('User invited successfully.'),
            ]
        );
    }

    public function inviteProjectClientMember(Request $request)
    {
        $authuser = Auth::user();

        // Make entry in project_user tbl
        ProjectUser::create(
            [
                'project_id' => $request->project_id,
                'user_id' => $request->user_id,
                'invited_by' => $authuser->id,
            ]
        );

        $project = $request->project_i->project;

        $users = User::where('id', $request->user_id)->pluck('name');

        $firebaseToken = User::whereIn('id', [$request->user_id])->whereNotNull('device_token')->pluck('device_token');
        $SERVER_API_KEY = 'AAAA9odnGYA:APA91bEW0H4cOYVOnneXeKl-cE1ECxNFiRmwzEAdspRw34q6RwjGNqO2o6l_4T3HtyIR0ahZ5g8tb_0AST6RnxOchE8S6DEEby_HpwJHDk1H9GYmKwrcFRkPYWDiNvjTnQoIcDjj5Ogx';

        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => 'AUP-APPS',
                "body" => $authuser->name . ' inviting you into the project ' . $project->project_name,  
                "icon" => 'https://i.postimg.cc/8z1vzXPV/logo-tgs-fix.png',
                "content_available" => true,
                "priority" => "high",
            ]
        ];
        $dataString = json_encode($data);
    
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        // Make entry in activity_log tbl
        ActivityLog::create(
            [
                'user_id' => $authuser->id,
                'project_id' => $request->project_id,
                'log_type' => 'Invite User',
                'remark' => json_encode(['title' => $users]),
            ]
        );

        return json_encode(
            [
                'code' => 200,
                'status' => 'Success',
                'success' => __('User invited successfully.'),
            ]
        );
    }

    public function destroyProjectUser($id, $user_id)
    {
        $authuser = Auth::user();
        $project = Project::find($id);
        $users = User::where('id', $user_id)->pluck('name');

            if($project->created_by == \Auth::user()->ownerId())
            {
                ProjectUser::where('project_id', '=', $project->id)->where('user_id', '=', $user_id)->delete();

                ActivityLog::create(
                    [
                        'user_id' => $authuser->id,
                        'project_id' => $project->id,
                        'log_type' => 'Delete Team',
                        'remark' => json_encode(['title' => $users]),
                    ]
                );

                return redirect()->back()->with('success', __('User successfully deleted!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }

    }

    // public function destroyProjectClient($id, $user_id)
    // {
    //     $project = Project::find($id);
        
    //         if($project->created_by == \Auth::user()->ownerId())
    //         {
    //             ProjectUser::where('project_id', '=', $project->id)->where('invited_by', '=', Auth::user()->id)->where('user_id', '=', $user_id)->delete();

    //             return redirect()->back()->with('success', __('User successfully deleted!'));
    //         }
    //         else
    //         {
    //             return redirect()->back()->with('error', __('Permission Denied.'));
    //         }

    // }

    public function loadUser(Request $request)
    {
        if($request->ajax())
        {
            $project    = Project::find($request->project_id);
            $returnHTML = view('projects.users', compact('project'))->render();

            return response()->json(
                [
                    'success' => true,
                    'html' => $returnHTML,
                ]
            );
        }
    }

    public function loadClient(Request $request)
    {
        if($request->ajax())
        {
            $project    = Project::find($request->project_id);
            $returnHTML = view('projects.clients', compact('project'))->render();

            return response()->json(
                [
                    'success' => true,
                    'html' => $returnHTML,
                ]
            );
        }
    }

    public function milestone($project_id)
    {
        if(\Auth::user()->can('create milestone'))
        {
            $project = Project::find($project_id);

            return view('projects.milestone', compact('project'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function milestoneStore(Request $request, $project_id)
    {
        if(\Auth::user()->can('create milestone'))
        {
            $project   = Project::find($project_id);
            $validator = Validator::make(
                $request->all(), [
                                   'title' => 'required',
                                   'status' => 'required',
                               ]
            );

            if($validator->fails())
            {
                return redirect()->back()->with('error', Utility::errorFormat($validator->getMessageBag()));
            }

            $milestone              = new Milestone();
            $milestone->project_id  = $project->id;
            $milestone->title       = $request->title;
            $milestone->status      = $request->status;
            $milestone->description = $request->description;
            $milestone->save();

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $project->id,
                    'log_type' => 'Create Milestone',
                    'remark' => json_encode(['title' => $milestone->title]),
                ]
            );

            return redirect()->back()->with('success', __('Milestone successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function milestoneEdit($id)
    {
        if(\Auth::user()->can('edit milestone'))
        {
            $milestone = Milestone::find($id);

            return view('projects.milestoneEdit', compact('milestone'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function milestoneUpdate($id, Request $request)
    {
        if(\Auth::user()->can('edit milestone'))
        {
            $validator = Validator::make(
                $request->all(), [
                                'title' => 'required',
                                'status' => 'required',
                            ]
            );

            if($validator->fails())
                {
                    return redirect()->back()->with('error', Utility::errorFormat($validator->getMessageBag()));
                }

            $milestone              = Milestone::find($id);
            $milestone->title       = $request->title;
            $milestone->status      = $request->status;
            $milestone->description = $request->description;
            $milestone->save();

            return redirect()->back()->with('success', __('Milestone updated successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function milestoneDestroy($id)
    {
        if(\Auth::user()->can('delete milestone'))
        {
            $milestone = Milestone::find($id);
            $milestone->delete();

            return redirect()->back()->with('success', __('Milestone successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function milestoneShow($id)
    {
        if(\Auth::user()->can('view milestone'))
        {
            $milestone = Milestone::find($id);

            return view('projects.milestoneShow', compact('milestone'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function filterProjectView(Request $request)
    {

        if(\Auth::user()->can('manage project'))
        {
            $usr           = Auth::user();
            if(\Auth::user()->type == 'client'){
                $user_projects = Project::where('client_id',\Auth::user()->id)->where('created_by',\Auth::user()->creatorId())->pluck('id','id')->toArray();
            }
            elseif(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
            {
                $user_projects = Project::all()->pluck('id','id')->toArray();
            }
            else{
                $user_projects = $usr->projects()->pluck('project_id', 'project_id')->toArray();
            }
            if($request->ajax() && $request->has('view') && $request->has('sort'))
            {
                $sort     = explode('-', $request->sort);
                $projects = Project::whereIn('id', array_keys($user_projects))->orderBy($sort[0], $sort[1]);

                if(!empty($request->keyword))
                {
                    $projects->where('project_name', 'LIKE', $request->keyword . '%')->orWhereRaw('FIND_IN_SET("' . $request->keyword . '",tags)');
                }
                if(!empty($request->status))
                {
                    $projects->whereIn('status', $request->status);
                }
                if(!empty($request->tags))
                {
                    $projects->whereIn('tags', $request->tags);
                }
                $projects   = $projects->get();
                $returnHTML = view('projects.' . $request->view, compact('projects', 'user_projects'))->render();

                return response()->json(
                    [
                        'success' => true,
                        'html' => $returnHTML,
                    ]
                );
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    // Project Gantt Chart
    public function gantt($projectID, $duration = 'Week')
    {
        if(\Auth::user()->can('view grant chart'))
        {
            $project = Project::find($projectID);
            $tasks   = [];

            if($project)
            {
                $tasksobj = $project->tasks;

                foreach($tasksobj as $task)
                {
                    $tmp                 = [];
                    $tmp['id']           = 'task_' . $task->id;
                    $tmp['name']         = $task->name;
                    $tmp['start']        = $task->start_date;
                    $tmp['end']          = $task->end_date;
                    $tmp['custom_class'] = (empty($task->priority_color) ? '#ecf0f1' : $task->priority_color);
                    $tmp['progress']     = str_replace('%', '', $task->taskProgress()['percentage']);
                    $tmp['extra']        = [
                        'priority' => ucfirst(__($task->priority)),
                        'comments' => count($task->comments),
                        'duration' => Utility::getDateFormated($task->start_date) . ' - ' . Utility::getDateFormated($task->end_date),
                    ];
                    $tasks[]             = $tmp;
                }
            }

            return view('projects.gantt', compact('project', 'tasks', 'duration'));
        }

        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function ganttPost($projectID, Request $request)
    {
        $project = Project::find($projectID);

        if($project)
        {
            if(\Auth::user()->can('view project task'))
            {
                $id               = trim($request->task_id, 'task_');
                $task             = ProjectTask::find($id);
                $task->start_date = $request->start;
                $task->end_date   = $request->end;
                $task->save();

                return response()->json(
                    [
                        'is_success' => true,
                        'message' => __("Time Updated"),
                    ], 200
                );
            }
            else
           {
                return response()->json(
                    [
                        'is_success' => false,
                        'message' => __("You can't change Date!"),
                    ], 400
                );
            }
        }
        else
        {
            return response()->json(
                [
                    'is_success' => false,
                    'message' => __("Something is wrong."),
                ], 400
            );
        }
    }

    public function bug($project_id)
    {


        $user = Auth::user();
        if($user->can('manage bug report'))
        {
            $project = Project::find($project_id);

            if(!empty($project) && $project->created_by == Auth::user()->creatorId())
            {

                if($user->type != 'company')
                {
                    if(\Auth::user()->type == 'client'){
                      $bugs = Bug::where('project_id',$project->id)->get();
                    }else{
                      $bugs = Bug::where('project_id',$project->id)->whereRaw("find_in_set('" . $user->id . "',assign_to)")->get();
                    }
                }

                // if($user->type == 'company')
                // {
                //     $bugs = Bug::where('project_id', '=', $project_id)->get();
                // }

                return view('projects.bug', compact('project', 'bugs'));
            }
            else
            {
                if($user->type == 'admin' || $user->type == 'company')
                {
                    $bugs = Bug::where('project_id', '=', $project_id)->get();
                }
                return view('projects.bug', compact('project', 'bugs'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bugCreate($project_id)
    {
        if(\Auth::user()->can('create bug report'))
        {

            $priority     = Bug::$priority;
            if(\Auth::user()->type = 'admin')
            {
                $status       = BugStatus::get()->pluck('title', 'id');
            }
            elseif(\Auth::user()->type = 'company')
            {
                $status       = BugStatus::get()->pluck('title', 'id');
            }
            else
            {
                $status       = BugStatus::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');
            }
            $project_user = ProjectUser::where('project_id', $project_id)->get();


            $users        = [];
            foreach($project_user as $key=>$user)
            {

                $user_data = User::where('id',$user->user_id)->first();
                $key = $user->user_id;
                $user_name = !empty($user_data)? $user_data->name:'';
                $users[$key] = $user_name;
            }

            return view('projects.bugCreate', compact('status', 'project_id', 'priority', 'users'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }

    }

    function bugNumber()
    {
        if(\Auth::user()->type = 'admin')
        {
            $latest = Bug::latest()->first();
        }
        if(\Auth::user()->type = 'company')
        {
            $latest = Bug::latest()->first();
        }
        else
        {
            $latest = Bug::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        }
        if(!$latest)
        {
            return 1;
        }

        return $latest->bug_id + 1;
    }

    public function bugStore(Request $request, $project_id)
    {
        if(\Auth::user()->can('create bug report'))
        {
            $validator = \Validator::make(
                $request->all(), [

                                   'title' => 'required',
                                   'priority' => 'required',
                                   'status' => 'required',
                                   'assign_to' => 'required',
                                   'start_date' => 'required',
                                   'due_date' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('task.bug', $project_id)->with('error', $messages->first());
            }

            $usr         = \Auth::user();
            $userProject = ProjectUser::where('project_id', '=', $project_id)->pluck('user_id')->toArray();
            $project     = Project::where('id', '=', $project_id)->first();

            $bug              = new Bug();
            $bug->bug_id      = $this->bugNumber();
            $bug->project_id  = $project_id;
            $bug->title       = $request->title;
            $bug->priority    = $request->priority;
            $bug->start_date  = $request->start_date;
            $bug->due_date    = $request->due_date;
            $bug->description = $request->description;
            $bug->status      = $request->status;
            $bug->assign_to   = $request->assign_to;
            $bug->created_by  = \Auth::user()->creatorId();
            $bug->save();

            ActivityLog::create(
                [
                    'user_id' => $usr->id,
                    'project_id' => $project_id,
                    'log_type' => 'Create Bug',
                    'remark' => json_encode(['title' => $bug->title]),
                ]
            );

            $projectArr = [
                'project_id' => $project_id,
                'name' => $project->name,
                'updated_by' => $usr->id,
            ];

            return redirect()->route('task.bug', $project_id)->with('success', __('Bug successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bugEdit($project_id, $bug_id)
    {
        if(\Auth::user()->can('edit bug report'))
        {
            $bug          = Bug::find($bug_id);
            $priority     = Bug::$priority;
            if(\Auth::user()->type = 'admin')
            {
                $status       = BugStatus::get()->pluck('title', 'id');
            }
            elseif(\Auth::user()->type = 'company')
            {
                $status       = BugStatus::get()->pluck('title', 'id');
            }
            else
            {
                $status       = BugStatus::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');
            }
            $project_user = ProjectUser::where('project_id', $project_id)->get();
            $users        = array();
            foreach($project_user as $user)
            {
              $user_data = User::where('id',$user->user_id)->first();
              $key = $user->user_id;
              $user_name = !empty($user_data) ? $user_data->name:'';
              $users[$key] = $user_name;
            }

            return view('projects.bugEdit', compact('status', 'project_id', 'priority', 'users', 'bug'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }


    }

    public function bugUpdate(Request $request, $project_id, $bug_id)
    {


        if(\Auth::user()->can('edit bug report'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'title' => 'required',
                                   'priority' => 'required',
                                   'status' => 'required',
                                   'assign_to' => 'required',
                                   'start_date' => 'required',
                                   'due_date' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('task.bug', $project_id)->with('error', $messages->first());
            }
            $bug              = Bug::find($bug_id);
            $bug->title       = $request->title;
            $bug->priority    = $request->priority;
            $bug->start_date  = $request->start_date;
            $bug->due_date    = $request->due_date;
            $bug->description = $request->description;
            $bug->status      = $request->status;
            $bug->assign_to   = $request->assign_to;
            $bug->save();

            return redirect()->route('task.bug', $project_id)->with('success', __('Bug successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bugDestroy($project_id, $bug_id)
    {


        if(\Auth::user()->can('delete bug report'))
        {
            $bug = Bug::find($bug_id);
            $bug->delete();

            return redirect()->route('task.bug', $project_id)->with('success', __('Bug successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bugKanban($project_id)
    {
        $user = Auth::user();
        if($user->can('move bug report'))
        {

            $project = Project::find($project_id);

            if(!empty($project) && $project->created_by == $user->creatorId())
            {
                if($user->type != 'company')
                {
                    $bugStatus = BugStatus::where('created_by', '=', Auth::user()->creatorId())->orderBy('order', 'ASC')->get();
                }

                if($user->type == 'company' || $user->type == 'client')
                {
                    $bugStatus = BugStatus::where('created_by', '=', Auth::user()->creatorId())->orderBy('order', 'ASC')->get();
                }

                return view('projects.bugKanban', compact('project', 'bugStatus'));
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

    public function bugKanbanOrder(Request $request)
    {
        if(\Auth::user()->can('move bug report'))
        {
            $post   = $request->all();
            $bug    = Bug::find($post['bug_id']);
            $status = BugStatus::find($post['status_id']);

            if(!empty($status))
            {
                $bug->status = $post['status_id'];
                $bug->save();
            }

            foreach($post['order'] as $key => $item)
            {
                $bug_order         = Bug::find($item);
                $bug_order->order  = $key;
                $bug_order->status = $post['status_id'];
                $bug_order->save();
            }
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }


    }

    public function bugShow($project_id, $bug_id)
    {
        $bug = Bug::find($bug_id);

        return view('projects.bugShow', compact('bug'));
    }

    public function bugCommentStore(Request $request, $project_id, $bug_id)
    {

        $post               = [];
        $post['bug_id']     = $bug_id;
        $post['comment']    = $request->comment;
        $post['created_by'] = \Auth::user()->authId();
        $post['user_type']  = \Auth::user()->type;
        $comment            = BugComment::create($post);
        $comment->deleteUrl = route('bug.comment.destroy', [$comment->id]);

        return $comment->toJson();
    }

    public function bugCommentDestroy($comment_id)
    {
        $comment = BugComment::find($comment_id);
        $comment->delete();

        return "true";
    }

    public function bugCommentStoreFile(Request $request, $bug_id)
    {
        $request->validate(
            ['file' => 'required|mimes:jpeg,jpg,png,gif,svg,pdf,txt,doc,docx,zip,rar|max:20480']
        );
        $fileName = $bug_id . time() . "_" . $request->file->getClientOriginalName();

        $request->file->storeAs('bugs', $fileName);
        $post['bug_id']     = $bug_id;
        $post['file']       = $fileName;
        $post['name']       = $request->file->getClientOriginalName();
        $post['extension']  = "." . $request->file->getClientOriginalExtension();
        $post['file_size']  = round(($request->file->getSize() / 1024) / 1024, 2) . ' MB';
        $post['created_by'] = \Auth::user()->authId();
        $post['user_type']  = \Auth::user()->type;

        $BugFile            = BugFile::create($post);
        $BugFile->deleteUrl = route('bug.comment.file.destroy', [$BugFile->id]);

        return $BugFile->toJson();
    }

    public function bugCommentDestroyFile(Request $request, $file_id)
    {
        $commentFile = BugFile::find($file_id);
        $path        = storage_path('bugs/' . $commentFile->file);
        if(file_exists($path))
        {
            \File::delete($path);
        }
        $commentFile->delete();

        return "true";
    }

    public function tracker($id)
    {
        $treckers=TimeTracker::with('user')->where('project_id',$id)->get();
        return view('time_trackers.index',compact('treckers'));
    }

    public function ClientInformation(Request $request, $project_id)
    {
        $usr          = \Auth::user();
        if(\Auth::user()->can('edit project'))
        {
            if($usr->type == 'admin')
            {
                $clients = User::where('type', '=', 'client')->where('name', '=', 'KAP AGUS UBAIDILLAH & REKAN')->get()->pluck('name');
                $project = Project::findOrfail($project_id);

                return view('projects.client', compact('project_id','project', 'clients'));

            }
            elseif($usr->type == 'company')
            {
                $clients = User::where('type', '=', 'client')->where('name', '=', 'KAP AGUS UBAIDILLAH & REKAN')->get()->pluck('name');
                $project = Project::findOrfail($project_id);

                return view('projects.client', compact('project_id','project', 'clients'));

            }
            else{
                
                $clients = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'client')->get()->pluck('name', 'id');
                $project = Project::findOrfail($project_id);
                if($project->created_by == \Auth::user()->creatorId())
                {
                    return view('projects.client', compact('project', 'clients'));
                }
                else
                {
                    return response()->json(['error' => __('Permission denied.')], 401);
                }
            }
            return view('projects.client',compact('project_id', 'project'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
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
        $stages = TaskStage::where('created_by', '=', $arrParam['created_by'])->orderBy('order');

        foreach ($arrDuration as $date => $label) {
            $objProject = projectTask::select('stage_id', \DB::raw('count(*) as total'))->whereDate('updated_at', '=', $date)->groupBy('stage_id');

            if (isset($arrParam['project_id'])) {
                $objProject->where('project_id', '=', $arrParam['project_id']);
            }


            $data = $objProject->pluck('total', 'stage_id')->all();

            foreach ($stages->pluck('name', 'id')->toArray() as $id => $stage) {
                $arrTask[$id][] = isset($data[$id]) ? $data[$id] : 0;
            }
            $arrTask['label'][] = __($label);
        }
        $arrTask['stages'] = $stages->pluck('name', 'id')->toArray();

        return $arrTask;
    }

    public function inviteclientmember($project_id)
    {
        if(\Auth::user()->can('create project task'))
        {
            $user  = \Auth::user();
            $project = Project::find($project_id);
            $roles = Role::where('name','=','staff_client')->get()->pluck('name', 'id');

            return view('projects.inviteclient', compact('project_id', 'user', 'project', 'roles'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function storeclientmember(Request $request, $project_id)
    {
        if(\Auth::user()->can('create project task'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:120',
                                   'email' => 'required|email|unique:users',
                                   'role' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }


            $objUser               = \Auth::user();
            $role_r                = Role::findById($request->role);
            $psw                   = 'clienttgsau23';
            $request['password']   = Hash::make('clienttgsau23');
            $request['type']       = $role_r->name;
            $request['created_by'] = \Auth::user()->id;

            $user = User::create($request->all());
            $user->assignRole($role_r);

            //Send Email

            $user->password = $psw;
            $user->type     = $role_r->name;

            ProjectUser::create(
                [
                    'project_id' => $project_id,
                    'user_id' => $user->id,
                ]
            );

            $userArr = [
                'email' => $user->email,
                'password' =>  $user->password,
            ];
            $resp = Utility::sendEmailTemplate('create_user', [$user->id => $user->email], $userArr);
            return redirect()->route('projects.index')->with('success', __('User successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));

        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

    }

    public function listUsers()
    {

        $user = \Auth::user();
        
        $users = User::where('type', '!=', 'client')->where('type', '!=', 'staff_client')->orderBy('name', 'ASC')->get();
        return view('projects.listUsers', compact('users'));

    }

    public function assignUsers($id)
    {

        $project           = Project::get()->pluck('project_name', 'id');
        $task              = ProjectTask::get()->pluck('name', 'id');
        $category          = CategoryTemplate::get()->pluck('name', 'id');
        $user              = User::findOrFail($id);

        return view('projects.assignUser', compact('user','category', 'project', 'task'));

    }

    public function gettask($id, $category_template_id, Request $request)
    {
        $task = ProjectTask::select('id', 'name')->where('project_id', $id)->where('category_template_id', $category_template_id)->get();
        return \Response::json($task);

        // $task = ProjectTask::where('project_id', $request->project_id)->get()->pluck('name', 'id')->toArray();
        // return response()->json($task);

    }

    public function assignUsersProject($id, Request $request)
    {
        $user = User::findOrFail($id);

        $project                  = new ProjectUser();
        $project->project_id      = $request->project_id;
        $project->user_id         = $user->id;

        $gettask    = $request->task_id;
        $task       = ProjectTask::whereIn('id', $gettask)->get();

        for($i = 0; $i < count($task); $i++)
        {

            ProjectTask::where(['id' => $task[$i]['id']])->update([
                'assign_to' => $user->id,
            ]);

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $project->project_id,
                    'task_id' => $task[$i]['id'],
                    'log_type' => 'Update Task',
                    'remark' => json_encode(['title' => $task[$i]['name']]),
                ]
            );


        }

        return redirect()->route('project.listUsers')->with(
            'success', 'User successfully Assigned.'
        );

    }

    public function auditPlanning($project_id)
    {
        $project = Project::findOrfail($project_id);
        $task = ProjectTask::where('project_id', $project_id)->get()->pluck('name', 'id');
        $user = User::where('type', '!=', 'client')->where('type', '!=', 'staff_client')->get()->pluck('name', 'id');

        return view('projects.auditplanning', compact('task','user','project'));
    }

    public function createAuditPlanning(Request $request, $project_id)
    {
        if(\Auth::user()->can('edit project'))
        {
            $project = Project::find($project_id);
            $auditplanning = new AuditPlan;
            $auditplanning->project_id = $project_id;
            $auditplanning->start_date = date("Y-m-d H:i:s", strtotime($request->start_date));
            $auditplanning->task_id = !empty($request->task_id) ? implode(',', $request->task_id) : '';
            $auditplanning->user_id = !empty($request->user_id) ? implode(',', $request->user_id) : '';
            
            // $gettask    = $request->task_id;
            // $task       = ProjectTask::whereIn('id', $gettask)->get();

            // for($i = 0; $i < count($task); $i++)
            // {
            //     $project = Project::find($project_id);
                
            //     ProjectTask::where(['id' => $task[$i]['id']])->update([
            //         'assign_to' => $auditplanning->user_id,
            //         'start_date' => $auditplanning->start_date,
            //     ]);

            //     ActivityLog::create(
            //         [
            //             'user_id' => \Auth::user()->id,
            //             'project_id' => $project_id,
            //             'task_id' => $task[$i]['id'],
            //             'log_type' => 'Update Task',
            //             'remark' => json_encode(['title' => $task[$i]['name']]),
            //         ]
            //     );


            // }
            
            $auditplanning->save();

            // ActivityLog::create(
            //     [
            //         'user_id' => \Auth::user()->id,
            //         'project_id' => $project->id,
            //         'task_id' => 0,
            //         'log_type' => 'Update Project',
            //         'remark' => json_encode(['title' => $project->project_name]),
            //     ]
            // );

            return redirect()->route('projects.index')->with('success', __('Audit Planning Create Successfully'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

}