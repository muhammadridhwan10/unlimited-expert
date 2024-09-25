<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Pmpj;
use App\Models\Utility;
use App\Models\TaskFile;
use App\Models\Timesheet;
use App\Models\Bug;
use App\Models\User;
use App\Models\ProjectUser;
use App\Models\CategoryTemplate;
use App\Models\BugStatus;
use App\Models\TaskStage;
use App\Models\ActivityLog;
use App\Models\ProjectTask;
use App\Models\TaskComment;
use App\Models\TaskLink;
use App\Models\TaskChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DataImport;
use App\Models\FinancialStatement;
use App\Models\MappingAccount;
use App\Models\MappingAccountData;
use App\Models\Materialitas;
use App\Models\ValueMaterialitas;
use App\Models\SummaryMateriality;
use App\Models\AuditMemorandum;
use App\Models\SummaryJournalData;
use App\Models\SummaryIdentifiedMisstatements;
use App\Models\NotesAnalysis;
use Illuminate\Support\Facades\Crypt;
use App\Mail\CommentNotification;
use App\Models\Respons;
use HaoZiTeam\ChatGPT\V1 as ChatGPTV1;
use App\Http\Library\ChatGPT\V1 as ChatGPT;

class ProjectTaskController extends Controller
{
    public function index(Request $request, $project_id)
    {
        $usr = \Auth::user();
        if(\Auth::user()->can('manage project task'))
        {
            if($usr->type == 'admin')
            {
                $category_template_id       = CategoryTemplate::all()->pluck('name', 'id');
                $category_template_id->prepend('All', '');
                $project    = Project::find($project_id);
                $taskstage   = TaskStage::all();
                $taskss      = ProjectTask::where('project_id', '=', $project_id)->get();
                if(!empty($request->category_template_id))
                {
                    $tasks = $taskss->where('category_template_id', '=', $request->category_template_id);         
                }elseif($request->category_template_id = 'All')
                {
                    $tasks = ProjectTask::where('project_id', '=', $project_id)->get();
                }

                $data_task = ProjectTask::where('project_id', $project_id)->where('name', 'Prosedur Analitis')->first();

                if(!empty($data_task))
                {
                    $existingSubTasks = TaskChecklist::whereIn('name', ['Perbandingan Data Antar Periode', 'Rasio Keuangan', 'Audit Memorandum'])
                        ->where('task_id', $data_task->id)
                        ->where('project_id', $project_id)
                        ->pluck('name')
                        ->toArray();

                    $createSubTask = [];

                    if (!in_array('Perbandingan Data Antar Periode', $existingSubTasks)) {
                        $createSubTask[] = [
                            'name' => 'Perbandingan Data Antar Periode',
                            'task_id' => $data_task->id,
                            'project_id' => $project_id,
                            'created_by' => 1,
                            'user_type' => 'User',
                            'status' => 0,
                        ];
                    }

                    if (!in_array('Rasio Keuangan', $existingSubTasks)) {
                        $createSubTask[] = [
                            'name' => 'Rasio Keuangan',
                            'task_id' => $data_task->id,
                            'project_id' => $project_id,
                            'created_by' => 1,
                            'user_type' => 'User',
                            'status' => 0,
                        ];
                    }

                    if (!in_array('Audit Memorandum', $existingSubTasks)) {
                        $createSubTask[] = [
                            'name' => 'Audit Memorandum',
                            'task_id' => $data_task->id,
                            'project_id' => $project_id,
                            'created_by' => 1,
                            'user_type' => 'User',
                            'status' => 0,
                        ];
                    }

                    if (!empty($createSubTask)) {
                        $checklist = TaskChecklist::insert($createSubTask);
                    }
                }



                return view('project_task.index', compact('tasks','taskstage','category_template_id','project'));
            }
            elseif($usr->type == 'company')
            {
                $category_template_id       = CategoryTemplate::all()->pluck('name', 'id');
                $category_template_id->prepend('All', '');
                $project    = Project::find($project_id);
                $taskstage   = TaskStage::all();
                $taskss      = ProjectTask::where('project_id', '=', $project_id)->get();
                if(!empty($request->category_template_id))
                {
                    $tasks = $taskss->where('category_template_id', '=', $request->category_template_id);         
                }elseif($request->category_template_id = 'All')
                {
                    $tasks = ProjectTask::where('project_id', '=', $project_id)->get();
                }

                $data_task = ProjectTask::where('project_id', '=', $project_id)->where('name','=','Prosedur Analitis')->get();

                if(!empty($data_task))
                {
                    foreach($data_task as $data)
                    {

                        $createSubTask = [
                            [
                                'name' => 'Perbandingan Data Antar Periode',
                                'task_id' => $data->id,
                                'project_id' => $data->project_id,
                                'created_by' => 1,
                                'user_type' => 'User',
                                'status' => 0,
                            ],
                            [
                                'name' => 'Rasio Keuangan',
                                'task_id' => $data->id,
                                'project_id' => $data->project_id,
                                'created_by' => 1,
                                'user_type' => 'User',
                                'status' => 0,
                            ],
                            [
                                'name' => 'Audit Memorandum',
                                'task_id' => $data->id,
                                'project_id' => $data->project_id,
                                'created_by' => 1,
                                'user_type' => 'User',
                                'status' => 0,
                            ],
                        ];

                        foreach ($createSubTask as $subTask) {
                            $checklist = TaskChecklist::where('name', $subTask['name'])->where('task_id', $subTask['task_id'])->first();

                            if(!$checklist) {
                                $checklist = new TaskChecklist();
                                $checklist->name = $subTask['name'];
                                $checklist->task_id = $subTask['task_id'];
                                $checklist->project_id = $subTask['project_id'];
                                $checklist->created_by = $subTask['created_by'];
                                $checklist->user_type = $subTask['user_type'];
                                $checklist->status = $subTask['status'];
                                $checklist->save();
                            }
                        }
                    }
                }



                return view('project_task.index', compact('tasks','taskstage','category_template_id','project'));
            }
            else
            {
                $category_template_id       = CategoryTemplate::all()->pluck('name', 'id');
                $category_template_id->prepend('All', '');
                $project    = Project::find($project_id);
                $taskstage   = TaskStage::all();
                $taskss      = ProjectTask::where('project_id', '=', $project_id)->get();
                if(!empty($request->category_template_id))
                {
                    $tasks = $taskss->where('category_template_id', '=', $request->category_template_id);         
                }elseif($request->category_template_id = 'All')
                {
                    $tasks = ProjectTask::where('project_id', '=', $project_id)->get();
                }

                $data_task = ProjectTask::where('project_id', '=', $project_id)->where('name','=','Prosedur Analitis')->get();
                
                if(!empty($data_task))
                {
                    foreach($data_task as $data)
                    {

                        $createSubTask = [
                            [
                                'name' => 'Perbandingan Data Antar Periode',
                                'task_id' => $data->id,
                                'project_id' => $data->project_id,
                                'created_by' => 1,
                                'user_type' => 'User',
                                'status' => 0,
                            ],
                            [
                                'name' => 'Rasio Keuangan',
                                'task_id' => $data->id,
                                'project_id' => $data->project_id,
                                'created_by' => 1,
                                'user_type' => 'User',
                                'status' => 0,
                            ],
                            [
                                'name' => 'Audit Memorandum',
                                'task_id' => $data->id,
                                'project_id' => $data->project_id,
                                'created_by' => 1,
                                'user_type' => 'User',
                                'status' => 0,
                            ],
                        ];

                        foreach ($createSubTask as $subTask) {
                            $checklist = TaskChecklist::where('name', $subTask['name'])->where('task_id', $subTask['task_id'])->first();

                            if(!$checklist) {
                                $checklist = new TaskChecklist();
                                $checklist->name = $subTask['name'];
                                $checklist->task_id = $subTask['task_id'];
                                $checklist->project_id = $subTask['project_id'];
                                $checklist->created_by = $subTask['created_by'];
                                $checklist->user_type = $subTask['user_type'];
                                $checklist->status = $subTask['status'];
                                $checklist->save();
                            }
                        }
                    }
                }
                
                return view('project_task.index', compact('tasks','taskstage','category_template_id','project'));
            }

            
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create($project_id)
    {
        if(\Auth::user()->can('create project task'))
        {
            $category_template_id   = CategoryTemplate::all()->pluck('name', 'id');
            $project = Project::find($project_id);
            $hrs     = Project::projectHrs($project_id);

            return view('project_task.create', compact('project_id', 'category_template_id', 'project', 'hrs'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function invite($project_id)
    {
        if(\Auth::user()->can('create project task'))
        {
            $category_template_id   = CategoryTemplate::all()->pluck('name', 'id');
            $project = Project::find($project_id);
            $user = ProjectUser::where('project_id', $project_id)->get();

            return view('project_task.invite', compact('project_id', 'category_template_id', 'project', 'user'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function store(Request $request, $project_id)
    {

        if(\Auth::user()->can('create project task'))
        {
            $validator = Validator::make(
                $request->all(), [
                                'name' => 'required',
                                'priority' => 'required',
                            ]
            );

            if($validator->fails())
            {
                return redirect()->back()->with('error', Utility::errorFormat($validator->getMessageBag()));
            }

            $usr        = Auth::user();
            $project    = Project::find($project_id);
            $last_stage = $project->first()->id;
            $post               = $request->all();
            $post['project_id'] = $project->id;
            $post['stage_id']   = 1;
            $post['assign_to'] = $request->assign_to;
            $post['created_by'] = \Auth::user()->creatorId();
            $post['start_date'] = $project->start_date;
            $post['end_date'] = $project->end_date;
            $post['category_template_id'] = $request->category_template_id;
            $task = ProjectTask::create($post);

            ActivityLog::create(
                [
                    'user_id' => $usr->id,
                    'project_id' => $project_id,
                    'task_id' => $task->id,
                    'log_type' => 'Create Task',
                    'remark' => json_encode(['title' => $task->name]),
                ]
            );

            $start_project = $project->start_date;
            $end_project   = $project->end_date;
            
            $start_projects = strtotime($start_project . "+1 days");
            $end_projects = strtotime($end_project);
            
            $jml_hari = array();
            $sabtuminggu = array();
            
            for ($i = $start_projects; $i <= $end_projects; $i += (60 * 60 * 24)) {
                if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                    $jml_hari[] = $i;
                } else {
                    $sabtuminggu[] = $i;
                }
            
            }
            $jumlah_hari = count($jml_hari);

            if($project->is_template !== 0)
            {
                $category = ProjectTask::category_progress($jumlah_hari, $project->id); 

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


            //Slack Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
            $project_name = Project::find($project_id);
            $project = Project::where('id',$project_name->id)->first();
            if(isset($setting['task_notification']) && $setting['task_notification'] ==1){
                $msg = $task->name .__("of").' '.$project->project_name .__(" created by").' '.\Auth::user()->name.'.';
                Utility::send_slack_msg($msg);
            }

            //Telegram Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
            $project_name = Project::find($project_id);
            $project = Project::where('id',$project_name->id)->first();
            if(isset($setting['telegram_task_notification']) && $setting['telegram_task_notification'] ==1){
                $msg = $task->name .__("of").' '.$project->project_name .__(" created by").' '.\Auth::user()->name.'.';
                Utility::send_telegram_msg($msg);
            }
            return redirect()->back()->with('success', __('Task added successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    // For Taskboard View
    public function taskBoard($view)
    {
        $user = Auth::user();
        if($view == 'list'){
            return view('project_task.taskboard', compact('view'));
          }else{
            if($user->type = 'admin' || $user->type = 'company'){
                $tasks = ProjectTask::all();
                return view('project_task.grid', compact('tasks','view'));
            }else{
                $tasks = ProjectTask::where('created_by',\Auth::user()->creatorId())->get();
                return view('project_task.grid', compact('tasks','view'));
            }
              
        }
          return redirect()->back()->with('error', __('Permission Denied.'));

    }

    // For Taskboard View
    public function allBugList($view)
    {
          $bugStatus = BugStatus::where('created_by',\Auth::user()->creatorId())->get();
          if(Auth::user()->type == 'company'){
            $bugs = Bug::where('created_by',\Auth::user()->creatorId())->get();
          }
          elseif(Auth::user()->type != 'company'){
            if(\Auth::user()->type == 'client'){
              $user_projects = Project::where('client_id',\Auth::user()->id)->pluck('id','id')->toArray();
              $bugs = Bug::whereIn('project_id', $user_projects)->where('created_by',\Auth::user()->creatorId())->get();
            }
            else{
              $bugs = Bug::where('created_by',\Auth::user()->creatorId())->whereRaw("find_in_set('" . \Auth::user()->id . "',assign_to)")->get();
            }
          }
          if($view == 'list'){
            return view('projects.allBugListView', compact('bugs','bugStatus','view'));
          }else{
            return view('projects.allBugGridView', compact('bugs','bugStatus','view'));
          }
          return redirect()->back()->with('error', __('Permission Denied.'));
    }


    // For Load Task using ajax
    public function taskboardView(Request $request)
    {

        $usr           = Auth::user();
        if(\Auth::user()->type == 'client')
        {
            $user_projects = Project::where('client_id',\Auth::user()->id)->pluck('id','id')->toArray();
        }elseif(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
        {
            $user_projects = Project::all()->pluck('id','id')->toArray();
        }
        elseif(\Auth::user()->type != 'client')
        {
            $user_projects = $usr->projects()->pluck('project_id','project_id')->toArray();
        }
        if($request->ajax() && $request->has('view') && $request->has('sort'))
        {
            $sort  = explode('-', $request->sort);
            $task = ProjectTask::whereIn('project_id', $user_projects)->get();
            $tasks = ProjectTask::whereIn('project_id', $user_projects)->orderBy($sort[0], $sort[1]);
            
            if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
            {
                $tasks->where('created_by',\Auth::user()->creatorId());
            }
            else
            {
                $tasks->whereRaw("find_in_set('" . $usr->id . "',assign_to)");
            }

            if(!empty($request->keyword))
            {
                $tasks->where('name', 'LIKE', $request->keyword . '%');
            }

            if(!empty($request->status))
            {
                $todaydate = date('Y-m-d');

                // For Optimization
                $status = $request->status;
                foreach($status as $k => $v)
                {
                    if($v == 'due_today' || $v == 'over_due' || $v == 'starred' || $v == 'see_my_tasks')
                    {
                        unset($status[$k]);
                    }
                }
                // end

                if(count($status) > 0)
                {
                    $tasks->whereIn('priority', $status);
                }

                if(in_array('see_my_tasks', $request->status))
                {
                    $tasks->whereRaw("find_in_set('" . $usr->id . "',assign_to)");
                }

                if(in_array('due_today', $request->status))
                {
                    $tasks->where('end_date', $todaydate);
                }

                if(in_array('over_due', $request->status))
                {
                    $tasks->where('end_date', '<', $todaydate);
                }

                if(in_array('starred', $request->status))
                {
                    $tasks->where('is_favourite', '=', 1);
                }
            }

            $tasks = $tasks->orderByDesc('id')->paginate(10);

            $returnHTML = view('project_task.' . $request->view, compact('tasks'))->render();

            return response()->json(
                [
                    'success' => true,
                    'html' => $returnHTML,
                ]
            );
        }
    }

    public function show($project_id, $task_id)
    {

        if(\Auth::user()->can('view project task'))
        {
            $allow_progress = Project::find($project_id)->task_progress;
            $task           = ProjectTask::find($task_id);
            $subtask        = TaskChecklist::where('task_id', $task_id)->where('parent_id', 0)->get();

            return view('project_task.view', compact('task','subtask', 'allow_progress'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function showcomment($project_id, $task_id)
    {

        if(\Auth::user()->can('view project task'))
        {
            $allow_progress = Project::find($project_id)->task_progress;
            $task           = ProjectTask::find($task_id);
            $subtask        = TaskChecklist::where('task_id', $task_id)->where('parent_id', 0)->get();

            return view('project_task.comment', compact('task','subtask', 'allow_progress'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function edit($project_id, $task_id)
    {
        if(\Auth::user()->can('edit project task'))
        {
            $project = Project::find($project_id);
            $task    = ProjectTask::find($task_id);
            $hrs     = Project::projectHrs($project_id);
            $taskstage   = TaskStage::get()->pluck('name', 'id');

            return view('project_task.edit', compact('project', 'taskstage', 'task', 'hrs'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function update(Request $request, $project_id, $task_id)
    {

        if(\Auth::user()->can('edit project task'))
        {
            $validator = Validator::make(
                $request->all(), [
                                'name' => 'required'
                            ]
            );

            if($validator->fails())
            {
                return redirect()->back()->with('error', Utility::errorFormat($validator->getMessageBag()));
            }

            $authuser = Auth::user();

            $post = $request->all();
            $task = ProjectTask::find($task_id);
            $task->update($post);

            if($task->stage_id == 4)
            {
                $task->is_complete = 1;
            }

            $task->save();

            $project = Project::where('id', $project_id)->pluck('project_name')->first();
            $firebaseToken = User::whereIn('id', [$task->assign_to])->whereNotNull('device_token')->pluck('device_token');
            $SERVER_API_KEY = 'AAAA9odnGYA:APA91bEW0H4cOYVOnneXeKl-cE1ECxNFiRmwzEAdspRw34q6RwjGNqO2o6l_4T3HtyIR0ahZ5g8tb_0AST6RnxOchE8S6DEEby_HpwJHDk1H9GYmKwrcFRkPYWDiNvjTnQoIcDjj5Ogx';

            $data = [
                "registration_ids" => $firebaseToken,
                "notification" => [
                    "title" => 'AUP-APPS',
                    "body" => $authuser->name . ' inviting you into the task ' . $task->name . ' in project ' . $project,  
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

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $project_id,
                    'task_id' => $task_id,
                    'log_type' => 'Update Task',
                    'remark' => json_encode(['title' => $task->name]),
                ]
            );

            return redirect()->back()->with('success', __('Task Updated successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function updatedropdown(Request $request)
    {

        if(\Auth::user()->can('edit project task'))
        {

            $task_id = $request->get('id');
            $priority = $request->get('priority');
        
            $data = ProjectTask::find($task_id);
            $data->priority = $priority;
            $data->save();

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $data->project_id,
                    'task_id' => $data->id,
                    'log_type' => 'Update Priority',
                    'remark' => json_encode(['title' => $data->name]),
                ]
            );

            return response()->json(['success' => __('Task Updated successfully.')], 200);
            
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function updatedropdownstage(Request $request)
    {

        if(\Auth::user()->can('edit project task'))
        {

            $task_id = $request->get('id');
            $stage_id = $request->get('stage_id');
        
            $data = ProjectTask::find($task_id);
            $data->stage_id = $stage_id;

            if($data->stage_id == 4)
            {
                $data->is_complete = 1;
            }
            elseif($data->stage_id !== 4)
            {
                $data->is_complete = 0;
            }

            $data->save();

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $data->project_id,
                    'task_id' => $data->id,
                    'log_type' => 'Update Status',
                    'remark' => json_encode(['title' => $data->name]),
                ]
            );
        
            return response()->json(['success' => __('Task Updated successfully.')], 200);
            
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function destroy($project_id, $task_id)
    {
        if(\Auth::user()->can('delete project task'))
        {
            ProjectTask::deleteTask([$task_id]);

            return redirect()->back()->with('success', __('Task successfully deleted!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function getStageTasks(Request $request, $stage_id)
    {

        if(\Auth::user()->can('view project task'))
        {
            $count = ProjectTask::where('stage_id', $stage_id)->count();
            echo json_encode($count);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function changeCom($projectID, $taskId)
    {

        if(\Auth::user()->can('view project task'))
        {
            $project = Project::find($projectID);
            $task    = ProjectTask::find($taskId);

            if($task->is_complete == 0)
            {
                if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
                {
                    $last_stage        = TaskStage::orderBy('order', 'DESC')->first();
                }
                else
                {
                    $last_stage        = TaskStage::orderBy('order', 'DESC')->where('created_by',\Auth::user()->creatorId())->first();
                }
                $task->is_complete = 1;
                $task->marked_at   = date('Y-m-d');
                $task->stage_id    = $last_stage->id;
            }
            else
            {
                if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
                {
                    $first_stage       = TaskStage::orderBy('order', 'ASC')->first();
                }
                else
                {
                    $first_stage       = TaskStage::orderBy('order', 'ASC')->where('created_by',\Auth::user()->creatorId())->first();
                }
                $task->is_complete = 0;
                $task->marked_at   = NULL;
                $task->stage_id    = $first_stage->id;
            }

            $task->save();

            return [
                'com' => $task->is_complete,
                'task' => $task->id,
                'stage' => $task->stage_id,
            ];
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function changeFav($projectID, $taskId)
    {
        if(\Auth::user()->can('view project task'))
        {
            $task = ProjectTask::find($taskId);
            if($task->is_favourite == 0)
            {
                $task->is_favourite = 1;
            }
            else
            {
                $task->is_favourite = 0;
            }

            $task->save();

            return [
                'fav' => $task->is_favourite,
            ];
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function changeProg(Request $request, $projectID, $taskId)
    {
        if(\Auth::user()->can('view project task'))
        {
            $task           = ProjectTask::find($taskId);
            $task->progress = $request->progress;
            $task->save();

            return ['task_id' => $taskId];
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function checklistStore(Request $request, $projectID, $taskID)
    {

        if(\Auth::user()->can('view project task'))
        {
            $request->validate(
                ['name' => 'required']
            );

            $post                       = [];
            $post['name']               = $request->name;
            $post['link']               = $request->link;
            $post['description']        = $request->description;
            $post['task_id']            = $taskID;
            $post['user_type']          = 'User';
            $post['created_by']         = \Auth::user()->id;
            $post['status']             = 0;
            $post['parent_id']          = $request->parent_id;
            $post['project_id']         = $projectID;

            $checkList            = TaskChecklist::create($post);
            $user                 = $checkList->user;
            $checkList->updateUrl = route(
                'checklist.update', [
                                    $projectID,
                                    $checkList->id,
                                ]
            );
            $checkList->deleteUrl = route(
                'checklist.destroy', [
                                    $projectID,
                                    $checkList->id,
                                ]
            );

            ActivityLog::create(
                [
                    'user_id' => $user->id,
                    'project_id' => $projectID,
                    'task_id' => $taskID,
                    'log_type' => 'Create Sub Task',
                    'remark' => json_encode(['title' => $request->name]),
                ]
            );

            return $checkList->toJson();
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function checklistUpdate($projectID, $checklistID)
    {

        if(\Auth::user()->can('view project task'))
        {
            $checkList = TaskChecklist::find($checklistID);
            if($checkList->status == 0)
            {
                $checkList->status = 1;
            }
            else
            {
                $checkList->status = 0;
            }
            $checkList->save();

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $projectID,
                    'task_id' => $checkList->task_id,
                    'log_type' => 'Update Sub Task',
                    'remark' => json_encode(['title' => $checkList->name]),
                ]
            );

            return $checkList->toJson();
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function checklistDestroy($projectID, $checklistID)
    {
        if(\Auth::user()->can('view project task'))
        {
            $checkList = TaskChecklist::find($checklistID);
            $checkList->delete();

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $projectID,
                    'task_id' => $checkList->task_id,
                    'log_type' => 'Delete Sub Task',
                    'remark' => json_encode(['title' => $checkList->name]),
                ]
            );

            return "true";
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function linkStore(Request $request, $projectID, $taskID)
    {

        if(\Auth::user()->can('view project task'))
        {
            $post               = [];
            $post['task_id']    = $taskID;
            $post['user_id']    = \Auth::user()->id;
            $post['link']       = $request->link;
            $post['created_by'] = \Auth::user()->creatorId();
            $post['user_type']  = \Auth::user()->type;
            $post['project_id']  = $projectID;

            $link = TaskLink::create($post);
            $user    = $link->user;
            $user_detail    = $link->userdetail;

            $link->deleteUrl = route(
                'link.destroy', [
                                     $projectID,
                                     $taskID,
                                     $link->id,
                                 ]
            );

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $projectID,
                    'task_id' => $taskID,
                    'log_type' => 'Create Link',
                    'remark' => json_encode(['title' => $link->link]),
                ]
            );

            // //Slack Notification
            // $setting  = Utility::settings(\Auth::user()->creatorId());
            // $comments = ProjectTask::find($taskID);
            // if(isset($setting['taskcomment_notification']) && $setting['taskcomment_notification'] ==1){
            //     $msg = __("New Comment added in").' '.$comments->name.'.';
            //     Utility::send_slack_msg($msg);
            // }

            // //Telegram Notification
            // $setting  = Utility::settings(\Auth::user()->creatorId());
            // $comments = ProjectTask::find($taskID);
            // if(isset($setting['telegram_taskcomment_notification']) && $setting['telegram_taskcomment_notification'] ==1){
            //     $msg = __("New Comment added in").' '.$comments->name.'.';
            //     Utility::send_telegram_msg($msg);
            // }

            $link->current_time= $link->created_at->diffForHumans();
            $link->default_img= asset(\Storage::url("uploads/avatar/avatar.png"));
            return $link->toJson();
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function linkDestroy($projectID, $linkID)
    {

        if(\Auth::user()->can('view project task'))
        {
            $link = TaskLink::find($linkID);
            $link->delete();

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $projectID,
                    'task_id' => $link->task_id,
                    'log_type' => 'Delete Link',
                    'remark' => json_encode(['title' => $link->link]),
                ]
            );


            return "true";
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function commentStoreFile(Request $request, $projectID, $taskID)
    {

        if(\Auth::user()->can('view project task'))
        {
            $request->validate(
                ['file' => 'required|mimes:pdf,xls,csv,xlsx|max:20480']
            );
            $fileName = $taskID . time() . "_" . $request->file->getClientOriginalName();
            $request->file->storeAs('tasks', $fileName);
            $post['task_id']     = $taskID;
            $post['file']        = $fileName;
            $post['name']        = $request->file->getClientOriginalName();
            $post['extension']   = $request->file->getClientOriginalExtension();
            $post['file_size']   = round(($request->file->getSize() / 1024) / 1024, 2) . ' MB';
            $post['created_by']  = \Auth::user()->id;
            $post['user_type']   = 'User';
            $post['project_id']  = $projectID;
            $TaskFile            = TaskFile::create($post);
            $user                = $TaskFile->user;
            $TaskFile->deleteUrl = '';
            $TaskFile->deleteUrl = route(
                'comment.destroy.file', [
                                        $projectID,
                                        $taskID,
                                        $TaskFile->id,
                                    ]
            );

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $projectID,
                    'task_id' => $taskID,
                    'log_type' => 'Create Attachment',
                    'remark' => json_encode(['title' => $request->file->getClientOriginalName()]),
                ]
            );

            return $TaskFile->toJson();
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function commentDestroyFile(Request $request, $projectID, $taskID, $fileID)
    {
        if(\Auth::user()->can('view project task'))
        {
            $commentFile = TaskFile::find($fileID);
            $path        = storage_path('tasks/' . $commentFile->file);
            if(file_exists($path))
            {
                \File::delete($path);
            }
            $commentFile->delete();

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $projectID,
                    'task_id' => $taskID,
                    'log_type' => 'Delete Attachment',
                    'remark' => json_encode(['title' => $commentFile->name]),
                ]
            );

            return "true";
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function commentDestroy(Request $request, $projectID, $taskID, $commentID)
    {

        if(\Auth::user()->can('view project task'))
        {
            $comment = TaskComment::find($commentID);
            $comment->delete();

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $projectID,
                    'task_id' => $taskID,
                    'log_type' => 'Delete Comment',
                    'remark' => json_encode(['title' => $comment->comment]),
                ]
            );


            return "true";
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function commentStore(Request $request, $projectID, $taskID)
    {

        if(\Auth::user()->can('view project task'))
        {
            $authuser = Auth::user();
            $post               = [];
            $post['task_id']    = $taskID;
            $post['user_id']    = \Auth::user()->id;
            $post['project_id'] = $projectID;
            $post['comment']    = $request->comment;
            $post['created_by'] = \Auth::user()->creatorId();
            $post['user_type']  = \Auth::user()->type;

            $comment = TaskComment::create($post);
            $user    = $comment->user;
            $user_detail    = $comment->userdetail;

            $comment->deleteUrl = route(
                'comment.destroy', [
                                     $projectID,
                                     $taskID,
                                     $comment->id,
                                 ]
            );

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $projectID,
                    'task_id' => $taskID,
                    'log_type' => 'Create Comment',
                    'remark' => json_encode(['title' => $comment->comment]),
                ]
            );

            //Slack Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
            $comments = ProjectTask::find($taskID);
            if(isset($setting['taskcomment_notification']) && $setting['taskcomment_notification'] ==1){
                $msg = __("New Comment added in").' '.$comments->name.'.';
                Utility::send_slack_msg($msg);
            }

                //Desktop Notification
            
            $users = ProjectUser::where('project_id', $projectID)->whereNotIn('user_id',[\Auth::user()->id])->get();
            
            $response = array();
            foreach($users as $data)
            {
                $response[] = array("id"=> $data->user->id);
                foreach ($response as $recipient) 
                {
                    $project = Project::where('id', $projectID)->pluck('project_name')->first();
                    $task = ProjectTask::where('project_id', $projectID)->where('id', $taskID)->pluck('name')->first();
                    $firebaseToken = User::whereIn('id', [$recipient])->whereNotNull('device_token')->pluck('device_token');
                    $SERVER_API_KEY = 'AAAA9odnGYA:APA91bEW0H4cOYVOnneXeKl-cE1ECxNFiRmwzEAdspRw34q6RwjGNqO2o6l_4T3HtyIR0ahZ5g8tb_0AST6RnxOchE8S6DEEby_HpwJHDk1H9GYmKwrcFRkPYWDiNvjTnQoIcDjj5Ogx';

                    $data = [
                        "registration_ids" => $firebaseToken,
                        "notification" => [
                            "title" => $project . ' in task ' . $task,
                            "body" =>  $authuser->name . ' : ' . $comment->comment,  
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
                }
            }

            $response = array();
            foreach($users as $data)
            {
                $response[] = array("email"=> $data->user->email);
                foreach ($response as $recipient) {
                    Mail::to($recipient)->send(new CommentNotification($comment));
                }
            }

                //Email Notification Client
            $client = Project::where('id', $projectID)->where('client_id',!\Auth::user()->id)->get();
            foreach($client as $data)
            {
                $email = $data->user->email;
                Mail::to($email)->send(new CommentNotification($comment));
            }

            //Telegram Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
            $comments = ProjectTask::find($taskID);
            if(isset($setting['telegram_taskcomment_notification']) && $setting['telegram_taskcomment_notification'] ==1){
                $msg = __("New Comment added in").' '.$comments->name.'.';
                Utility::send_telegram_msg($msg);
            }
            $comment->current_time= $comment->created_at->diffForHumans();
            $comment->default_img= asset(\Storage::url("uploads/avatar/avatar.png"));
            return $comment->toJson();
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    
    public function updateTaskPriorityColor(Request $request)
    {
        if(\Auth::user()->can('view project task'))
        {
            $task_id = $request->input('task_id');
            $color   = $request->input('color');

            $task = ProjectTask::find($task_id);

            if($task && $color)
            {
                $task->priority_color = $color;
                $task->save();
            }
            echo json_encode(true);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function taskOrderUpdate(Request $request, $project_id)
    {

        if(\Auth::user()->can('view project task'))
        {

            $user    = \Auth::user();
            $project = Project::find($project_id);
            // Save data as per order

            if(isset($request->sort))
            {
                foreach($request->sort as $index => $taskID)
                {
                    if(!empty($taskID))
                    {
                        echo $index . "-" . $taskID;
                        $task        = ProjectTask::find($taskID);

                        $task->order = $index;
                        $task->save();

                    }
                }
            }

            // Update Task Stage
            if($request->new_stage != $request->old_stage)
            {

                $new_stage  = TaskStage::find($request->new_stage);
                $old_stage  = TaskStage::find($request->old_stage);
                if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
                {
                    $last_stage = TaskStage::orderBy('order', 'DESC')->first();
                }
                else
                {
                    $last_stage = TaskStage::where('created_by',\Auth::user()->creatorId())->orderBy('order', 'DESC')->first();
                }
                $last_stage = $last_stage->id;

                $task = ProjectTask::find($request->id);

                $task->stage_id = $request->new_stage;

                if($request->new_stage == $last_stage)
                {
                    $task->is_complete = 1;
                    $task->marked_at   = date('Y-m-d');
                }
                else
                {
                    $task->is_complete = 0;
                    $task->marked_at   = NULL;
                }
                $task->save();

                //Slack Notification
                $old_stage  = TaskStage::find($request->old_stage);
                $new_stage  = TaskStage::find($request->new_stage);
                $setting  = Utility::settings(\Auth::user()->creatorId());
                $task = ProjectTask::find($request->id);
                if(isset($setting['taskmove_notification']) && $setting['taskmove_notification'] ==1){
                    $msg = $task->name.' '. __("status changed from").' '. $old_stage->name .' '.__("to").' ' .$new_stage->name;
                    Utility::send_slack_msg($msg);
                }

                //Telegram Notification
                $old_stage  = TaskStage::find($request->old_stage);
                $new_stage  = TaskStage::find($request->new_stage);
                $setting  = Utility::settings(\Auth::user()->creatorId());
                $task = ProjectTask::find($request->id);
                if(isset($setting['telegram_taskmove_notification']) && $setting['telegram_taskmove_notification'] ==1){
                    $msg = $task->name.' '. __("status changed from").' '. $old_stage->name .' '.__("to").' ' .$new_stage->name;
                    Utility::send_telegram_msg($msg);
                }

                // Make Entry in activity log
                ActivityLog::create(
                    [
                        'user_id' => $user->id,
                        'project_id' => $project_id,
                        'task_id' => $request->id,
                        'log_type' => 'Move Task',
                        'remark' => json_encode(
                            [
                                'title' => $task->name,
                                'old_stage' => $old_stage->name,
                                'new_stage' => $new_stage->name,
                            ]
                        ),
                    ]

                );




                return $task->toJson();
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function taskGet($task_id)
    {
        if(\Auth::user()->can('view project task'))
        {
            $task        = ProjectTask::find($task_id);
            $html = '';
            $html .= '<div class="card-body"><div class="row align-items-center mb-2">';
            $html .= '<div class="col-6">';
            $html .= '<span class="badge badge-pill badge-xs badge-' . ProjectTask::$priority_color[$task->priority] . '">' . ProjectTask::$priority[$task->priority] . '</span>';
            $html .= '</div>';
            $html .= '<div class="col-6 text-end">';
            if(str_replace('%', '', $task->taskProgress()['percentage']) > 0)
            {
                $html .= '<span class="text-sm">' . $task->taskProgress()['percentage'] . '</span>';
            }
            if(\Auth::user()->can('view project task') || \Auth::user()->can('edit project task') || \Auth::user()->can('delete project task'))
            {
                $html .= '<div class="dropdown action-item">
                                                            <a href="#" class="action-item" data-toggle="dropdown"><i class="ti ti-ellipsis-h"></i></a>
                                                            <div class="dropdown-menu dropdown-menu-right">';
                if(\Auth::user()->can('view project task'))
                {
                    $html .= '<a href="#" data-url="' . route(
                            'projects.tasks.show', [
                                                    $task->project_id,
                                                    $task->id,
                                                ]
                        ) . '" data-ajax-popup="true" class="dropdown-item">' . __('View') . '</a>';
                }
                if(\Auth::user()->can('edit project task'))
                {
                    $html .= '<a href="#" data-url="' . route(
                            "projects.tasks.edit", [
                                                    $task->project_id,
                                                    $task->id,
                                                ]
                        ) . '" data-ajax-popup="true" data-size="lg" data-title="' . __("Edit ") . $task->name . '" class="dropdown-item">' . __('Edit') . '</a>';
                }
                if(\Auth::user()->can('delete project task'))
                {
                    $html .= '<a href="#" class="dropdown-item del_task" data-url="' . route(
                            'projects.tasks.destroy', [
                                                        $task->project_id,
                                                        $task->id,
                                                    ]
                        ) . '">' . __('Delete') . '</a>';
                }
                $html .= '                                 </div>
                                                        </div>
                                                    </div>';
                $html .= '</div>';
            }
            $html .= '<a class="h6" href="#" data-url="' . route(
                    "projects.tasks.show", [
                                            $task->project_id,
                                            $task->id,
                                        ]
                ) . '" data-ajax-popup="true">' . $task->name . '</a>';
            $html .= '<div class="row align-items-center">';
            $html .= '<div class="col-12">';
            $html .= '<div class="actions d-inline-block">';
            if(count($task->taskFiles) > 0)
            {
                $html .= '<div class="action-item mr-2"><i class="ti ti-paperclip mr-2"></i>' . count($task->taskFiles) . '</div>';
            }
            if(count($task->comments) > 0)
            {
                $html .= '<div class="action-item mr-2"><i class="ti ti-brand-hipchart mr-2"></i>' . count($task->comments) . '</div>';
            }
            if($task->checklist->count() > 0)
            {
                $html .= '<div class="action-item mr-2"><i class="ti ti-tasks mr-2"></i>' . $task->countTaskChecklist() . '</div>';
            }
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="col-5">';
            if(!empty($task->end_date) && $task->end_date != '0000-00-00')
            {
                $clr  = (strtotime($task->end_date) < time()) ? 'text-danger' : '';
                $html .= '<small class="' . $clr . '">' . date("d M Y", strtotime($task->end_date)) . '</small>';
            }
            $html .= '</div>';
            $html .= '<div class="col-7 text-end">';

            if($users = $task->users())
            {
                $html .= '<div class="avatar-group">';
                foreach($users as $key => $user)
                {
                    if($key < 3)
                    {
                        $html .= ' <a href="#" class="avatar rounded-circle avatar-sm">';
                        $html .= '<img class="hweb" src="' . $user->getImgImageAttribute() . '" title="' . $user->name . '">';
                        $html .= '</a>';
                    }
                }

                if(count($users) > 3)
                {
                    $html .= '<a href="#" class="avatar rounded-circle avatar-sm"><img avatar="';
                    $html .= count($users) - 3;
                    $html .= '"></a>';
                }
                $html .= '</div>';
            }
            $html .= '</div></div></div>';

            print_r($html);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function getDefaultTaskInfo(Request $request, $task_id)
    {
        if(\Auth::user()->can('view project task'))
        {
            $response = [];
            $task     = ProjectTask::find($task_id);
            if($task)
            {
                $response['task_name']     = $task->name;
                $response['task_due_date'] = $task->due_date;
            }

            return json_encode($response);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    // Calendar View
    public function calendarView($task_by, $project_id = NULL)
    {
        $usr = Auth::user();
        $weekday = date("l");
        if ($weekday != "Saturday" && $weekday != "Sunday") {
            $transdate = date('Y-m-d', time());
        }

        if($usr->type != 'super admin')
        {
            if(\Auth::user()->type == 'client')
            {
                $user_projects = Project::where('client_id',\Auth::user()->id)->pluck('id','id')->toArray();
            }
            elseif(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
            {
                $user_projects = Project::all()->pluck('id','id')->toArray();
            }
            else
            {
                $user_projects = $usr->projects()->pluck('project_id','project_id')->toArray();
            }

            $user_projects = (!empty($project_id) && $project_id > 0) ? [$project_id] : $user_projects;
            // if(\Auth::user()->type == 'company'){
            //   $tasks = ProjectTask::whereIn('project_id', $user_projects);
            // }
            // if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
            // {
            //     $tasks = ProjectTask::whereIn('project_id', $user_projects);
            // }
            // elseif(\Auth::user()->type != 'company')
            // {
            //     if(\Auth::user()->type == 'client')
            //     {
            //         $tasks = ProjectTask::whereIn('project_id', $user_projects);
            //     }
            //     else{
            //         $tasks = ProjectTask::whereIn('project_id', $user_projects)->whereRaw("find_in_set('" . \Auth::user()->id . "',assign_to)");
            //     }
            // }

            if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
            {
                $project = ProjectUser::whereIn('project_id', $user_projects);
            }
            elseif(\Auth::user()->type != 'company')
            {
                if(\Auth::user()->type == 'client')
                {
                    $project = ProjectUser::whereIn('project_id', $user_projects);
                }
                else{
                    $project = ProjectUser::whereIn('project_id', $user_projects)->where('user_id', $usr->id);
                }
            }

            if(\Auth::user()->type  == 'client')
            {
                if($task_by == 'all')
                {
                    $tasks->where('created_by',\Auth::user()->creatorId());
                }
            }
            else
            {
                if($task_by == 'my')
                {
                    $tasks->whereRaw("find_in_set('" . $usr->id . "',assign_to)");
                }
            }
            $project    = $project->get();
            $arrTasks = [];

            foreach($project as $projects)
            {
                $arTasks = [];
                if((!empty($projects->project->start_date) && $projects->project->start_date != '0000-00-00') || !empty($projects->project->end_date) && $projects->project->end_date != '0000-00-00')
                {
                    $arTasks['id']    = $projects->project_id;
                    $arTasks['title'] = $projects->project->project_name;
                    // $arTasks['project'] = $task->project->project_name;

                    if(!empty($projects->project->start_date) && $projects->project->start_date != '0000-00-00')
                    {
                        $arTasks['start'] = $projects->project->start_date;
                    }
                    elseif(!empty($projects->project->end_date) && $projects->project->end_date != '0000-00-00')
                    {
                        $arTasks['start'] = $projects->project->end_date;
                    }

                    if(!empty($projects->project->end_date) && $projects->project->end_date != '0000-00-00')
                    {
                        $arTasks['end'] = $projects->project->end_date;
                    }
                    elseif(!empty($projects->project->end_date) && $projects->project->end_date != '0000-00-00')
                    {
                        $arTasks['end'] = $projects->project->end_date;
                    }

                    $arTasks['allDay']      = !0;
                    // $arTasks['className']   = 'event-' . ProjectTask::$priority_color[$task->priority];
                    // $arTasks['description'] = $task->description;
                    // $arTasks['url']         = route('task.calendar.show', $projects->project_id);
                    // $arTasks['resize_url']  = route('task.calendar.drag', $projects->project_id);

                    $arrTasks[] = $arTasks;

                    // dd($arrTasks);


                }
            }

            // foreach($tasks as $task)
            // {
            //     $arTasks = [];
            //     if((!empty($task->start_date) && $task->start_date != '0000-00-00') || !empty($task->end_date) && $task->end_date != '0000-00-00')
            //     {
            //         $arTasks['id']    = $task->id;
            //         $arTasks['title'] = $task->name;
            //         $arTasks['project'] = $task->project->project_name;

            //         if(!empty($task->start_date) && $task->start_date != '0000-00-00')
            //         {
            //             $arTasks['start'] = $task->start_date;
            //         }
            //         elseif(!empty($task->end_date) && $task->end_date != '0000-00-00')
            //         {
            //             $arTasks['start'] = $task->end_date;
            //         }

            //         if(!empty($task->end_date) && $task->end_date != '0000-00-00')
            //         {
            //             $arTasks['end'] = $task->end_date;
            //         }
            //         elseif(!empty($task->start_date) && $task->start_date != '0000-00-00')
            //         {
            //             $arTasks['end'] = $task->start_date;
            //         }

            //         $arTasks['allDay']      = !0;
            //         $arTasks['className']   = 'event-' . ProjectTask::$priority_color[$task->priority];
            //         $arTasks['description'] = $task->description;
            //         $arTasks['url']         = route('task.calendar.show', $task->id);
            //         $arTasks['resize_url']  = route('task.calendar.drag', $task->id);

            //         $arrTasks[] = $arTasks;


            //     }
            // }

            return view('tasks.calendar', compact('arrTasks', 'project_id', 'task_by','transdate'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    // Calendar Show
    public function calendarShow($id)
    {
        $projects = ProjectUser::find($id);

        return view('tasks.calendar_show', compact('projects'));
    }

    // Calendar Drag
    public function calendarDrag(Request $request, $id)
    {
        $task             = ProjectTask::find($id);
        $task->start_date = $request->start;
        $task->end_date   = $request->end;
        $task->save();
    }

    public function subtaskEdit($projectID, $checklistID)
    {
        if(\Auth::user()->can('edit project task'))
        {
            $project    = Project::find($projectID);
            $subtask    = TaskChecklist::find($checklistID);
            $task       = ProjectTask::find($subtask->task_id);

            return view('project_task.edit_subtask', compact('project', 'task', 'subtask'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function subtaskUpdate(Request $request,$projectID, $checklistID)
    {

        if(\Auth::user()->can('view project task'))
        {
            $post = $request->all();
            $subtask = TaskChecklist::find($checklistID);
            $subtask->update($post);

            // ActivityLog::create(
            //     [
            //         'user_id' => \Auth::user()->id,
            //         'project_id' => $projectID,
            //         'task_id' => $checkList->task_id,
            //         'log_type' => 'Update Sub Task',
            //         'remark' => json_encode(['title' => $checkList->name]),
            //     ]
            // );

            return redirect()->back()->with('success', __(' Sub Task Updated ssuccessfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    // public function play(Request $request, $project_id, $task_id)
    // {
    //     $settings = Utility::settings();
    //     $task = ProjectTask::find($task_id);
    //     $project = Project::find($task->project_id);

    //     $userId      = Auth::user()->id;
    //     $todayPlay   = Timesheet::where('created_by', '=', $userId)->where('date', date('Y-m-d'))->first();
    //     if(empty($todayPlay))
    //     {

    //         $startTime = Utility::getValByName('company_start_time');
    //         $endTime   = Utility::getValByName('company_end_time');

    //         $timesheet = Timesheet::orderBy('id', 'desc')->where('created_by', '=', $userId)->where('time', '=', '00:00:00')->first();

    //         if($timesheet != null)
    //         {
    //             $timesheet             = Timesheet::find($timesheet->id);
    //             $timesheet->end_time = $endTime;
    //             $timesheet->save();
    //         }

    //         $date = date("Y-m-d");
    //         $time = date("H:i:s");

    //         $checkDb = Timesheet::where('created_by', '=', \Auth::user()->id)->get()->toArray();

    //         if(empty($checkDb))
    //         {
    //             $timePlay                = new Timesheet();
    //             $timePlay->created_by    = $userId;
    //             $timePlay->date          = $date;
    //             $timePlay->project_id    = $task->project_id;
    //             $timePlay->task_id       = $task_id;
    //             $timePlay->start_time    = $time;
    //             $timePlay->end_time      = '00:00:00';

    //             $timePlay->save();

    //             return redirect()->route('taskBoard.view', 'list')->with('success', __('Employee Successfully Play Time.'));
    //         }

    //         foreach($checkDb as $check)
    //         {


    //             $timePlay                = new Timesheet();
    //             $timePlay->created_by    = $userId;
    //             $timePlay->date          = $date;
    //             $timePlay->project_id    = $task->project_id;
    //             $timePlay->task_id       = $task_id;
    //             $timePlay->start_time    = $time;
    //             $timePlay->end_time      = '00:00:00';

    //             $timePlay->save();

    //             return redirect()->route('taskBoard.view', 'list')->with('success', __('Employee Successfully Play Time.'));

    //         }
    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
    //     }
    // }

    // public function stop(Request $request, $id)
    // {

    //     $userId      = Auth::user()->id;
    //     $stoptime    = Timesheet::where('created_by', '=', $userId)->where('date', date('Y-m-d'))->first();

    //     if(!empty($stoptime) && $stoptime->end_time == '00:00:00')
    //     {

    //         $timesheets = Timesheet::where('id', $id)->where('created_by', $userId)->first();

    //         $startTime = $timesheets->start_time;
    //         $endTime   = Utility::getValByName('company_end_time');

    //         if(Auth::user()->type !== 'client' && Auth::user()->type !== 'staff_client')
    //         {

    //             $date = date("Y-m-d");
    //             $time = date("H:i:s");
                
    //             $totalLateSeconds = strtotime($time) - strtotime($startTime);

    //             $hours = floor($totalLateSeconds / 3600);
    //             $mins  = floor($totalLateSeconds / 60 % 60);
    //             $secs  = floor($totalLateSeconds % 60);
    //             $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //             $timesheet['end_time']      = $time;
    //             $timesheet['time']          = $late;

    //             if(!empty($request->date)) {
    //                 $timesheet['date']       =  $request->date;
    //             }
    //             //                dd($attendanceEmployee);
    //             Timesheet::where('id',$id)->update($timesheet);
    //             //                $attendanceEmployee->save();

    //             return redirect()->route('taskBoard.view', 'list')->with('success', __('Employee successfully Stop Timer.'));
    //         }
    //         else
    //         {
    //             $date = date("Y-m-d");
    //             $time = date("H:i:s");
                
    //             $totalLateSeconds = strtotime($request->start_time) - strtotime($startTime);

    //             $hours = floor($totalLateSeconds / 3600);
    //             $mins  = floor($totalLateSeconds / 60 % 60);
    //             $secs  = floor($totalLateSeconds % 60);
    //             $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //             $timesheetEmployee                 = Timesheet::find($id);
    //             $timesheetEmployee->created_by     = $userId;
    //             $timesheetEmployee->date           = $date;
    //             $timesheetEmployee->end_time       = $time;
    //             $timesheetEmployee->time           = $late;

    //             $timesheetEmployee->save();

    //             return redirect()->back()->with('success', __('Employee successfully Stop Timer.'));
    //         }
    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
    //     }
    // }

    public function wp()
    {
        return view('project_task.wp');
    }

    public function financialStatement($project_id, $task_id)
    {
        if(\Auth::user()->can('manage project task'))
        {  
            $id                            = Crypt::decrypt($task_id);
            $project                       = Project::find($project_id);
            $task                          = ProjectTask::find($id);
            $financial_statement           = FinancialStatement::where('project_id', $project_id)->get();
            $mapping_accounts              = MappingAccount::where('project_id', $project_id)->get();

            $result = $mapping_accounts->map(function ($mapping_account) use ($financial_statement) {
                $account_code = $mapping_account->account_code;
                $name = $mapping_account->name;
                $account_group = $mapping_account->materialitas->name;
            
                $financial_data = $financial_statement->where('lk', $account_code);

                $prior_period2 = $financial_data->sum('prior_period2') ?? null;
                $prior_period = $financial_data->sum('prior_period') ?? null;
                $inhouse = $financial_data->sum('inhouse') ?? null;
                $audited = $financial_data->sum('audited') ?? null;

                // dd($audited);

                if ($account_group === 'LIABILITAS') {
                    $prior_period2 *= -1;
                    $prior_period *= -1;
                    $inhouse *= -1;
                    $audited *= -1;
                }
                elseif($account_group === 'EKUITAS')
                {
                    $prior_period2 *= -1;
                    $prior_period *= -1;
                    $inhouse *= -1;
                    $audited *= -1;
                }
                elseif($account_group === 'PENDAPATAN')
                {
                    $prior_period2 *= -1;
                    $prior_period *= -1;
                    $inhouse *= -1;
                    $audited *= -1;
                }
                elseif($account_group === 'PENDAPATAN / BEBAN KEUANGAN')
                {
                    $prior_period2 *= -1;
                    $prior_period *= -1;
                    $inhouse *= -1;
                    $audited *= -1;
                }
                elseif($account_group === 'PENDAPATAN / BEBAN LAIN-LAIN')
                {
                    $prior_period2 *= -1;
                    $prior_period *= -1;
                    $inhouse *= -1;
                    $audited *= -1;
                }
                elseif($account_group === "PENGHASILAN KOMPREHENSIF LAIN")
                {
                    $prior_period2 *= -1;
                    $prior_period *= -1;
                    $inhouse *= -1;
                    $audited *= -1;
                }
            
                return [
                    'account_code' => $account_code,
                    'name' => $name,
                    'account_group' => $account_group,
                    'prior_period2' => $prior_period2,
                    'prior_period' => $prior_period,
                    'inhouse' => $inhouse,
                    'audited' => $audited,
                ];
            });

            $summary_mapping = [];

            // dd($result);
            if (!$result->isEmpty()) 
            {

                // Mengelompokkan hasil berdasarkan account_group
                $grouped_result = $result->groupBy('account_group');

                // Menghitung total masing-masing dari account_group
                $summary = $grouped_result->map(function ($group) {
                    $accountGroup = $group->first()['account_group'];
                    $priorPeriod2Total = $group->sum('prior_period2');
                    $priorPeriodTotal = $group->sum('prior_period');
                    $inhouseTotal = $group->sum('inhouse');
                    $auditedTotal = $group->sum('audited');
                    
                    
                    
                    return [
                        'account_group' => $accountGroup,
                        'prior_period2_total' => $priorPeriod2Total,
                        'prior_period_total' => $priorPeriodTotal,
                        'inhouse_total' => $inhouseTotal,
                        'audited_total' => $auditedTotal,
                    ];
                });

                // dd($summary);

                $laba_kotor = [
                    'account_group' => 'LABA BRUTO',
                    'prior_period2_total' => isset($summary['PENDAPATAN']['prior_period2_total']) && isset($summary['BEBAN POKOK PENDAPATAN']['prior_period2_total']) ? $summary['PENDAPATAN']['prior_period2_total'] - $summary['BEBAN POKOK PENDAPATAN']['prior_period2_total'] : 0,
                    'prior_period_total' => isset($summary['PENDAPATAN']['prior_period_total']) && isset($summary['BEBAN POKOK PENDAPATAN']['prior_period_total']) ? $summary['PENDAPATAN']['prior_period_total'] - $summary['BEBAN POKOK PENDAPATAN']['prior_period_total'] : 0,
                    'inhouse_total' => isset($summary['PENDAPATAN']['inhouse_total']) && isset($summary['BEBAN POKOK PENDAPATAN']['inhouse_total']) ? $summary['PENDAPATAN']['inhouse_total'] - $summary['BEBAN POKOK PENDAPATAN']['inhouse_total'] : 0,
                    'audited_total' => isset($summary['PENDAPATAN']['audited_total']) && isset($summary['BEBAN POKOK PENDAPATAN']['audited_total']) ? $summary['PENDAPATAN']['audited_total'] - $summary['BEBAN POKOK PENDAPATAN']['audited_total'] : 0,
                ];


                // Menambahkan perhitungan "TOTAL LABA OPERASI"
                $laba_operasi = [
                    'account_group' => 'LABA OPERASIONAL',
                    'prior_period2_total' => isset($laba_kotor['prior_period2_total']) && isset($summary['BEBAN OPERASIONAL']['prior_period2_total']) ? $laba_kotor['prior_period2_total'] - $summary['BEBAN OPERASIONAL']['prior_period2_total'] : 0,
                    'prior_period_total' => isset($laba_kotor['prior_period_total']) && isset($summary['BEBAN OPERASIONAL']['prior_period_total']) ? $laba_kotor['prior_period_total'] - $summary['BEBAN OPERASIONAL']['prior_period_total'] : 0,
                    'inhouse_total' => isset($laba_kotor['inhouse_total']) && isset($summary['BEBAN OPERASIONAL']['inhouse_total']) ? $laba_kotor['inhouse_total'] - $summary['BEBAN OPERASIONAL']['inhouse_total'] : 0,
                    'audited_total' => isset($laba_kotor['audited_total']) && isset($summary['BEBAN OPERASIONAL']['audited_total']) ? $laba_kotor['audited_total'] - $summary['BEBAN OPERASIONAL']['audited_total'] : 0,
                ];                

                // Menambahkan perhitungan "TOTAL LABA SEBELUM PAJAK"
                $laba_sebelum_pajak = [
                    'account_group' => 'LABA SEBELUM PAJAK',
                    'prior_period2_total' => isset($summary['PENDAPATAN / BEBAN KEUANGAN']['prior_period2_total']) ? $laba_operasi['prior_period2_total'] + $summary['PENDAPATAN / BEBAN KEUANGAN']['prior_period2_total'] + $summary['PENDAPATAN / BEBAN LAIN-LAIN']['prior_period2_total'] : 0,
                    'prior_period_total' => isset($summary['PENDAPATAN / BEBAN KEUANGAN']['prior_period_total']) ? $laba_operasi['prior_period_total'] + $summary['PENDAPATAN / BEBAN KEUANGAN']['prior_period_total'] + $summary['PENDAPATAN / BEBAN LAIN-LAIN']['prior_period_total'] : 0,
                    'inhouse_total' => isset($summary['PENDAPATAN / BEBAN KEUANGAN']['inhouse_total']) ? $laba_operasi['inhouse_total'] + $summary['PENDAPATAN / BEBAN KEUANGAN']['inhouse_total'] + $summary['PENDAPATAN / BEBAN LAIN-LAIN']['inhouse_total'] : 0,
                    'audited_total' => isset($summary['PENDAPATAN / BEBAN KEUANGAN']['audited_total']) ? $laba_operasi['audited_total'] + $summary['PENDAPATAN / BEBAN KEUANGAN']['audited_total'] + $summary['PENDAPATAN / BEBAN LAIN-LAIN']['audited_total'] : 0,
                ];               

                // Menambahkan perhitungan "TOTAL LABA SETELAH PAJAK"
                $laba_setelah_pajak = [
                    'account_group' => 'LABA SETELAH PAJAK',
                    'prior_period2_total' => isset($summary['BEBAN PAJAK PENGHASILAN']['prior_period2_total']) ? $summary['BEBAN PAJAK PENGHASILAN']['prior_period2_total'] + $laba_sebelum_pajak['prior_period2_total'] : 0,
                    'prior_period_total' => isset($summary['BEBAN PAJAK PENGHASILAN']['prior_period_total']) ? $summary['BEBAN PAJAK PENGHASILAN']['prior_period_total'] + $laba_sebelum_pajak['prior_period_total'] : 0,
                    'inhouse_total' => isset($summary['BEBAN PAJAK PENGHASILAN']['inhouse_total']) ? $summary['BEBAN PAJAK PENGHASILAN']['inhouse_total'] + $laba_sebelum_pajak['inhouse_total'] : 0,
                    'audited_total' => isset($summary['BEBAN PAJAK PENGHASILAN']['audited_total']) ? $summary['BEBAN PAJAK PENGHASILAN']['audited_total'] + $laba_sebelum_pajak['audited_total'] : 0,
                ];

                // Menambahkan perhitungan "LABA RUGI KOMPREHENSIF SETELAH PAJAK
                $laba_rugi_komprehensif_setelah_pajak = [
                    'account_group' => 'LABA RUGI KOMPREHENSIF SETELAH PAJAK',
                    'prior_period2_total' => isset($summary['PENGHASILAN KOMPREHENSIF LAIN']['prior_period2_total']) ? $laba_setelah_pajak['prior_period2_total'] + $summary['PENGHASILAN KOMPREHENSIF LAIN']['prior_period2_total'] : 0,
                    'prior_period_total' => isset($summary['PENGHASILAN KOMPREHENSIF LAIN']['prior_period_total']) ? $laba_setelah_pajak['prior_period_total'] + $summary['PENGHASILAN KOMPREHENSIF LAIN']['prior_period_total'] : 0,
                    'inhouse_total' => isset($summary['PENGHASILAN KOMPREHENSIF LAIN']['inhouse_total']) ? $laba_setelah_pajak['inhouse_total'] + $summary['PENGHASILAN KOMPREHENSIF LAIN']['inhouse_total'] : 0,
                    'audited_total' => isset($summary['PENGHASILAN KOMPREHENSIF LAIN']['audited_total']) ? $laba_setelah_pajak['audited_total'] + $summary['PENGHASILAN KOMPREHENSIF LAIN']['audited_total'] : 0,
                ];                

                // Menggabungkan hasil total masing-masing account_group dengan perhitungan "TOTAL LABA KOTOR"
                $summaryWithLabaKotor = $summary->put('LABA BRUTO', $laba_kotor); 
                $summaryWithBebanOperasi = $summary->put('LABA OPERASIONAL', $laba_operasi); 
                $summaryWithLabaSebelumPajak = $summary->put('LABA SEBELUM PAJAK', $laba_sebelum_pajak); 
                $summaryWithLabaSetelahPajak = $summary->put('LABA SETELAH PAJAK', $laba_setelah_pajak); 
                $summaryWithLabaRugiKomprehensifSetelahPajak = $summary->put('LABA RUGI KOMPREHENSIF SETELAH PAJAK', $laba_rugi_komprehensif_setelah_pajak); 

                $summary_mapping = [
                    'ASET' => isset($summary['ASET']) ? $summary['ASET'] : [
                        'account_group' => 'ASET',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'LIABILITAS' => isset($summary['LIABILITAS']) ? $summary['LIABILITAS'] : [
                        'account_group' => 'LIABILITAS',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'EKUITAS' => isset($summary['EKUITAS']) ? $summary['EKUITAS'] : [
                        'account_group' => 'EKUITAS',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'PENDAPATAN' => isset($summary['PENDAPATAN']) ? $summary['PENDAPATAN'] : [
                        'account_group' => 'PENDAPATAN',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'BEBAN POKOK PENDAPATAN' => isset($summary['BEBAN POKOK PENDAPATAN']) ? $summary['BEBAN POKOK PENDAPATAN'] : [
                        'account_group' => 'BEBAN POKOK PENDAPATAN',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'LABA BRUTO' => isset($summary['LABA BRUTO']) ? $summary['LABA BRUTO'] : [
                        'account_group' => 'LABA BRUTO',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'BEBAN OPERASIONAL' => isset($summary['BEBAN OPERASIONAL']) ? $summary['BEBAN OPERASIONAL'] : [
                        'account_group' => 'BEBAN OPERASIONAL',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'LABA OPERASIONAL' => isset($summary['LABA OPERASIONAL']) ? $summary['LABA OPERASIONAL'] : [
                        'account_group' => 'LABA OPERASIONAL',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'PENDAPATAN / BEBAN KEUANGAN' => isset($summary['PENDAPATAN / BEBAN KEUANGAN']) ? $summary['PENDAPATAN / BEBAN KEUANGAN'] : [
                        'account_group' => 'PENDAPATAN / BEBAN KEUANGAN',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'PENDAPATAN / BEBAN LAIN-LAIN' => isset($summary['PENDAPATAN / BEBAN LAIN-LAIN']) ? $summary['PENDAPATAN / BEBAN LAIN-LAIN'] : [
                        'account_group' => 'PENDAPATAN / BEBAN LAIN-LAIN',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'LABA SEBELUM PAJAK' => isset($summary['LABA SEBELUM PAJAK']) ? $summary['LABA SEBELUM PAJAK'] : [
                        'account_group' => 'LABA SEBELUM PAJAK',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'BEBAN PAJAK PENGHASILAN' => isset($summary['BEBAN PAJAK PENGHASILAN']) ? $summary['BEBAN PAJAK PENGHASILAN'] : [
                        'account_group' => 'BEBAN PAJAK PENGHASILAN',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'LABA SETELAH PAJAK' => isset($summary['LABA SETELAH PAJAK']) ? $summary['LABA SETELAH PAJAK'] : [
                        'account_group' => 'LABA SETELAH PAJAK',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'PENGHASILAN KOMPREHENSIF LAIN' => isset($summary['PENGHASILAN KOMPREHENSIF LAIN']) ? $summary['PENGHASILAN KOMPREHENSIF LAIN'] : [
                        'account_group' => 'PENGHASILAN KOMPREHENSIF LAIN',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                    'LABA RUGI KOMPREHENSIF SETELAH PAJAK' => isset($summary['LABA RUGI KOMPREHENSIF SETELAH PAJAK']) ? $summary['LABA RUGI KOMPREHENSIF SETELAH PAJAK'] : [
                        'account_group' => 'LABA RUGI KOMPREHENSIF SETELAH PAJAK',
                        'prior_period2_total' => 0,
                        'prior_period_total' => 0,
                        'inhouse_total' => 0,
                        'audited_total' => 0,
                    ],
                ];
                

                // dd($summary_mapping);
                
            }

            return view('project_task.financialStatement', compact('task','summary_mapping','project','financial_statement','mapping_accounts', 'result'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function getMaterialitas(Request $request, $project_id, $task_id)
    {
        if(\Auth::user()->can('manage project task'))
        {  
            $id                                 = Crypt::decrypt($task_id);
            $project                            = Project::find($project_id);
            $task                               = ProjectTask::find($id);
            $financial_statement                = FinancialStatement::where('project_id', $project_id)->get();
            $materialitas                       = Materialitas::get();
            $get_data_materialitas              = ValueMaterialitas::where('project_id', $project_id)->get();
            $valuemateriality                   = SummaryMateriality::where('project_id', $project_id)->orderBy('id', 'DESC')->first();

            $respons                            = Respons::where('project_id', $project_id)->where('task_id', $id)->orderBy('id', 'DESC')->first();
            //data
            $data_m1 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.1')->get();
            $data_m2 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.2')->get();
            $data_m3 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.3')->get();
            $data_m4 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.4')->get();
            $data_m5 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.5')->get();
            $data_m6 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.6')->get();
            $data_m7 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.7')->get();
            $data_m8 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.8')->get();
            $data_m9 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.9')->get();
            $data_m10 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.10')->get();

            //data 2020
            $data_m1_2020 = $data_m1->pluck('prior_period2')->toArray();
            $total_m1_2020 = array_sum($data_m1_2020);
            $data_m2_2020 = $data_m2->pluck('prior_period2')->toArray();
            $total_m2_2020 = array_sum($data_m2_2020);
            $data_m3_2020 = $data_m3->pluck('prior_period2')->toArray();
            $total_m3_2020 = array_sum($data_m3_2020);
            $data_m4_2020 = $data_m4->pluck('prior_period2')->toArray();
            $total_m4_2020 = array_sum($data_m4_2020);
            $data_m5_2020 = $data_m5->pluck('prior_period2')->toArray();
            $total_m5_2020 = array_sum($data_m5_2020);
            $data_m6_2020 = $data_m6->pluck('prior_period2')->toArray();
            $total_m6_2020 = array_sum($data_m6_2020);
            $data_m7_2020 = $data_m7->pluck('prior_period2')->toArray();
            $total_m7_2020 = array_sum($data_m7_2020);
            $data_m8_2020 = $data_m8->pluck('prior_period2')->toArray();
            $total_m8_2020 = array_sum($data_m8_2020);
            $data_m9_2020 = $data_m9->pluck('prior_period2')->toArray();
            $total_m9_2020 = array_sum($data_m9_2020);
            $data_m10_2020 = $data_m10->pluck('prior_period2')->toArray();
            $total_m10_2020 = array_sum($data_m10_2020);

            //data 2021
            $data_m1_2021 = $data_m1->pluck('prior_period')->toArray();
            $total_m1_2021 = array_sum($data_m1_2021);
            $data_m2_2021 = $data_m2->pluck('prior_period')->toArray();
            $total_m2_2021 = array_sum($data_m2_2021);
            $data_m3_2021 = $data_m3->pluck('prior_period')->toArray();
            $total_m3_2021 = array_sum($data_m3_2021);
            $data_m4_2021 = $data_m4->pluck('prior_period')->toArray();
            $total_m4_2021 = array_sum($data_m4_2021);
            $data_m5_2021 = $data_m5->pluck('prior_period')->toArray();
            $total_m5_2021 = array_sum($data_m5_2021);
            $data_m6_2021 = $data_m6->pluck('prior_period')->toArray();
            $total_m6_2021 = array_sum($data_m6_2021);
            $data_m7_2021 = $data_m7->pluck('prior_period')->toArray();
            $total_m7_2021 = array_sum($data_m7_2021);
            $data_m8_2021 = $data_m8->pluck('prior_period')->toArray();
            $total_m8_2021 = array_sum($data_m8_2021);
            $data_m9_2021 = $data_m9->pluck('prior_period')->toArray();
            $total_m9_2021 = array_sum($data_m9_2021);
            $data_m10_2021 = $data_m10->pluck('prior_period')->toArray();
            $total_m10_2021 = array_sum($data_m10_2021);

            //data inhouse 2022
            $data_m1_in_2022 = $data_m1->pluck('inhouse')->toArray();
            $total_m1_in_2022 = array_sum($data_m1_in_2022);
            $data_m2_in_2022 = $data_m2->pluck('inhouse')->toArray();
            $total_m2_in_2022 = array_sum($data_m2_in_2022);
            $data_m3_in_2022 = $data_m3->pluck('inhouse')->toArray();
            $total_m3_in_2022 = array_sum($data_m3_in_2022);
            $data_m4_in_2022 = $data_m4->pluck('inhouse')->toArray();
            $total_m4_in_2022 = array_sum($data_m4_in_2022);
            $data_m5_in_2022 = $data_m5->pluck('inhouse')->toArray();
            $total_m5_in_2022 = array_sum($data_m5_in_2022);
            $data_m6_in_2022 = $data_m6->pluck('inhouse')->toArray();
            $total_m6_in_2022 = array_sum($data_m6_in_2022);
            $data_m7_in_2022 = $data_m7->pluck('inhouse')->toArray();
            $total_m7_in_2022 = array_sum($data_m7_in_2022);
            $data_m8_in_2022 = $data_m8->pluck('inhouse')->toArray();
            $total_m8_in_2022 = array_sum($data_m8_in_2022);
            $data_m9_in_2022 = $data_m9->pluck('inhouse')->toArray();
            $total_m9_in_2022 = array_sum($data_m9_in_2022);
            $data_m10_in_2022 = $data_m10->pluck('inhouse')->toArray();
            $total_m10_in_2022 = array_sum($data_m10_in_2022);

            //data audited 2022
            $data_m1_au_2022 = $data_m1->pluck('audited')->toArray();
            $total_m1_au_2022 = array_sum($data_m1_au_2022);
            $data_m2_au_2022 = $data_m2->pluck('audited')->toArray();
            $total_m2_au_2022 = array_sum($data_m2_au_2022);
            $data_m3_au_2022 = $data_m3->pluck('audited')->toArray();
            $total_m3_au_2022 = array_sum($data_m3_au_2022);
            $data_m4_au_2022 = $data_m4->pluck('audited')->toArray();
            $total_m4_au_2022 = array_sum($data_m4_au_2022);
            $data_m5_au_2022 = $data_m5->pluck('audited')->toArray();
            $total_m5_au_2022 = array_sum($data_m5_au_2022);
            $data_m6_au_2022 = $data_m6->pluck('audited')->toArray();
            $total_m6_au_2022 = array_sum($data_m6_au_2022);
            $data_m7_au_2022 = $data_m7->pluck('audited')->toArray();
            $total_m7_au_2022 = array_sum($data_m7_au_2022);
            $data_m8_au_2022 = $data_m8->pluck('audited')->toArray();
            $total_m8_au_2022 = array_sum($data_m8_au_2022);
            $data_m9_au_2022 = $data_m9->pluck('audited')->toArray();
            $total_m9_au_2022 = array_sum($data_m9_au_2022);
            $data_m10_au_2022 = $data_m10->pluck('audited')->toArray();
            $total_m10_au_2022 = array_sum($data_m10_au_2022);

            //data array unaudited 2020
            $data_array_2020 = 
            [
                '1' => $total_m1_2020,
                '2' => $total_m2_2020 * -1,
                '3' => $total_m3_2020 * -1,
                '4' => $total_m4_2020 * -1,
                '5' => $total_m5_2020,
                '6' => $total_m6_2020,
                '7' => $total_m7_2020 * -1,
                '8' => $total_m8_2020 * -1,
                '9' => $total_m9_2020,
                '10' => $total_m10_2020 * -1,
            ];

            $data_array_2020['11'] = ($total_m4_2020 * -1) - $total_m5_2020;
            $data_array_2020['12'] = $data_array_2020['11'] - $total_m6_2020;
            $data_array_2020['13'] = $data_array_2020['12'] + ($total_m8_2020 * -1) + ($total_m7_2020 * -1);
            $data_array_2020['14'] = $data_array_2020['13'] - $total_m9_2020;
            $data_array_2020['15'] = $data_array_2020['14'] + ($total_m10_2020 * -1);

            //data array audited 2021
            $data_array_2021 = 
            [
                '1' => $total_m1_2021,
                '2' => $total_m2_2021 * -1,
                '3' => $total_m3_2021 * -1,
                '4' => $total_m4_2021 * -1,
                '5' => $total_m5_2021,
                '6' => $total_m6_2021,
                '7' => $total_m7_2021 * -1,
                '8' => $total_m8_2021 * -1,
                '9' => $total_m9_2021,
                '10' => $total_m10_2021 * -1,
            ];

            $data_array_2021['11'] = ($total_m4_2021 * -1) - $total_m5_2021;
            $data_array_2021['12'] = $data_array_2021['11'] - $total_m6_2021;
            $data_array_2021['13'] = $data_array_2021['12'] + ($total_m8_2021 * -1) + ($total_m7_2021 * -1);
            $data_array_2021['14'] = $data_array_2021['13'] - $total_m9_2021;
            $data_array_2021['15'] = $data_array_2021['14'] + ($total_m10_2021 * -1);

            //data array inhouse 2022
            $data_array_in_2022 = 
            [
                '1' => $total_m1_in_2022,
                '2' => $total_m2_in_2022 * -1,
                '3' => $total_m3_in_2022 * -1,
                '4' => $total_m4_in_2022 * -1,
                '5' => $total_m5_in_2022,
                '6' => $total_m6_in_2022,
                '7' => $total_m7_in_2022 * -1,
                '8' => $total_m8_in_2022 * -1,
                '9' => $total_m9_in_2022,
                '10' => $total_m10_in_2022 * -1,
            ];

            $data_array_in_2022['11'] = ($total_m4_in_2022 * -1) - $total_m5_in_2022;
            $data_array_in_2022['12'] = $data_array_in_2022['11'] - $total_m6_in_2022;
            $data_array_in_2022['13'] = $data_array_in_2022['12'] + ($total_m8_in_2022 * -1) + ($total_m7_in_2022 * -1);
            $data_array_in_2022['14'] = $data_array_in_2022['13'] - $total_m9_in_2022;
            $data_array_in_2022['15'] = $data_array_in_2022['14'] + ($total_m10_in_2022 * -1);

            //data array audited 2022
            $data_array_au_2022 = 
            [
                '1' => $total_m1_au_2022,
                '2' => $total_m2_au_2022 * -1,
                '3' => $total_m3_au_2022 * -1,
                '4' => $total_m4_au_2022 * -1,
                '5' => $total_m5_au_2022,
                '6' => $total_m6_au_2022,
                '7' => $total_m7_au_2022 * -1,
                '8' => $total_m8_au_2022 * -1,
                '9' => $total_m9_au_2022,
                '10' => $total_m10_au_2022 * -1,
            ];

            $data_array_au_2022['11'] = ($total_m4_au_2022 * -1) - $total_m5_au_2022;
            $data_array_au_2022['12'] = $data_array_au_2022['11'] - $total_m6_au_2022;
            $data_array_au_2022['13'] = $data_array_au_2022['12'] + ($total_m8_au_2022 * -1) + ($total_m7_au_2022 * -1);
            $data_array_au_2022['14'] = $data_array_au_2022['13'] - $total_m9_au_2022;
            $data_array_au_2022['15'] = $data_array_au_2022['14'] + ($total_m10_au_2022 * -1);

            $savematerialitas = Materialitas::get();

            foreach ($savematerialitas as $key => $materialitasss) 
            {
                ValueMaterialitas::updateOrCreate(
                    ['project_id' => $project_id, 'materialitas_id' => $materialitasss->id],
                    [
                        'prior_period2' => isset($data_array_2020[$key+1]) ? $data_array_2020[$key+1] : null,
                        'prior_period' => isset($data_array_2021[$key+1]) ? json_encode($data_array_2021[$key+1]) : null,
                        'inhouse' => isset($data_array_in_2022[$key+1]) ? json_encode($data_array_in_2022[$key+1]) : null,
                        'audited' => isset($data_array_au_2022[$key+1]) ? json_encode($data_array_au_2022[$key+1]) : null,
                    ]
                );
            }
             
            return view('project_task.materialitas', compact(
                'task','project','materialitas','financial_statement',
                'data_array_2020','data_array_2021','data_array_in_2022',
                'data_array_au_2022','get_data_materialitas','valuemateriality','respons'
            ));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function import(Request $request, $project_id)
    {
        $file = $request->file('file');
        $projectId = $project_id;
        Excel::import(new DataImport($projectId), $file);

        return redirect()->back()->with('success', __('Financial Statement added successfully.'));
    }

    public function materialitas(Request $request)
    {

        $data['materialitas']       = $materialitas = ValueMaterialitas::find($request->materialitas_id);
        $data['inhouse2022']        = (!empty($materialitas->inhouse)) ? $materialitas->inhouse : '-';
        $data['audited2022']        = (!empty($materialitas->audited)) ? $materialitas->audited : '-';

        return json_encode($data);
    }

    public function summaryMaterialitas(Request $request, $project_id)
    {
        
        $materialitas_id = $request->input('materialitas_id');
        $rate = $request->input('rate');
        $pmrate = $request->input('pmrate');
        $terate = $request->input('terate');
        $initialom = str_replace(',', '', $request->input('initialmaterialityom'));
        $finalom = str_replace(',', '', $request->input('finalmaterialityom'));
        $initialpm = str_replace(',', '', $request->input('initialmaterialitypm'));
        $finalpm = str_replace(',', '', $request->input('finalmaterialitypm'));
        $initialte = str_replace(',', '', $request->input('initialmaterialityte'));
        $finalte = str_replace(',', '', $request->input('finalmaterialityte'));
        $description = $request->input('description');

        $valuesummary = ValueMaterialitas::where('project_id', $project_id)->where('materialitas_id', $materialitas_id)->first();
        $project_id = $project_id;
        $value_materialitas_id = $valuesummary->id;

        // Simpan data ke dalam database menggunakan model
        $data = new SummaryMateriality();
        $data->project_id = $project_id;
        $data->value_materialitas_id = $value_materialitas_id;
        $data->materialitas_id = $materialitas_id;
        $data->rate = $rate;
        $data->pmrate = $pmrate;
        $data->terate = $terate;
        $data->initialmaterialityom = $initialom;
        $data->finalmaterialityom = $finalom;
        $data->initialmaterialitypm = $initialpm;
        $data->finalmaterialitypm = $finalpm;
        $data->initialmaterialityte = $initialte;
        $data->finalmaterialityte = $finalte;
        $data->description = $description;

        $data->save();

        ActivityLog::create(
            [
                'user_id' => \Auth::user()->id,
                'project_id' => $project_id,
                'task_id' => 0,
                'log_type' => 'Create Summary Materiality',
                'remark' => json_encode(['title' => 'Create Summary Materiality']),
            ]
        );

        return redirect()->back()->with('success', __('Summary Materiality added successfully.'));

    }

    public function createFinancialStatement($project_id, $task_id)
    {
        if(\Auth::user()->can('create project task'))
        {
            $materialitas   = Materialitas::all()->pluck('code', 'code');
            $project = Project::find($project_id);

            return view('project_task.createFinancialStatement', compact('project_id','task_id', 'materialitas','project'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function storeFinancialStatement(Request $request, $project_id, $task_id)
    {

        if(\Auth::user()->can('create project task'))
        {
            $validator = Validator::make(
                $request->all(), [
                                'm' => 'required',
                                'lk' => 'required',
                                'cn' => 'required',
                                'rp' => 'required',
                                'add1' => 'required',
                                'add2' => 'required',
                                'add3' => 'required',
                                'coa' => 'required',
                                'prior_period2' => 'required',
                                'prior_period' => 'required',
                                'inhouse' => 'required',
                                'jan' => 'required',
                                'feb' => 'required',
                                'mar' => 'required',
                                'apr' => 'required',
                                'may' => 'required',
                                'jun' => 'required',
                                'jul' => 'required',
                                'aug' => 'required',
                                'sep' => 'required',
                                'oct' => 'required',
                                'nov' => 'required',
                                'dec' => 'required',
                                'triwulan1' => 'required',
                                'triwulan2' => 'required',
                                'triwulan3' => 'required',
                                'triwulan4' => 'required',
                            ]
            );

            if($validator->fails())
            {
                return redirect()->back()->with('error', Utility::errorFormat($validator->getMessageBag()));
            }

            $usr        = Auth::user();
            $project    = Project::find($project_id);
            $task       = ProjectTask::find($task_id);

            $post               = $request->all();
            $post['project_id'] = $project->id;
            $post['m'] = $request->m;
            $post['lk'] = $request->lk;
            $post['cn'] = $request->cn;
            $post['rp'] = $request->rp;
            $post['add1'] = $request->add1;
            $post['add2'] = $request->add2;
            $post['add3'] = $request->add3;
            $post['coa'] = $request->coa;
            $post['prior_period2'] = $request->prior_period2;
            $post['prior_period'] = $request->prior_period;
            $post['inhouse'] = $request->inhouse;
            if ($post['audited'] == 0) {
                $post['audited'] = $request->inhouse;
            } else {
                $post['audited'] = $request->audited;
            }            
            $post['jan'] = $request->jan;
            $post['feb'] = $request->feb;
            $post['mar'] = $request->mar;
            $post['apr'] = $request->apr;
            $post['may'] = $request->may;
            $post['jun'] = $request->jun;
            $post['jul'] = $request->jul;
            $post['aug'] = $request->aug;
            $post['sep'] = $request->sep;
            $post['oct'] = $request->oct;
            $post['nov'] = $request->nov;
            $post['dec'] = $request->dec;
            $post['triwulan1'] = $request->triwulan1;
            $post['triwulan2'] = $request->triwulan2;
            $post['triwulan3'] = $request->triwulan3;
            $post['triwulan4'] = $request->triwulan4;

            $financial_statement = FinancialStatement::create($post);

            ActivityLog::create(
                [
                    'user_id' => $usr->id,
                    'project_id' => $project_id,
                    'task_id' => $task_id,
                    'log_type' => 'Create Financial Statment',
                    'remark' => json_encode(['title' => 'Add Data In ' . $task->name]),
                ]
            );
            
            return redirect()->back()->with('success', __('Financial Statement added successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function getJournalEntries(Request $request, $project_id, $task_id)
    {
        if(\Auth::user()->can('manage project task'))
        {  
            $id                                 = Crypt::decrypt($task_id);
            $project                            = Project::find($project_id);
            $task                               = ProjectTask::find($id);
            $journaldata                        = SummaryJournalData::where('project_id', $project_id)->get();
            return view('project_task.journalentries', compact(
                'task','project','journaldata',
            ));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function createJournalData($project_id, $task_id)
    {
        if(\Auth::user()->can('create project task'))
        {
            $project                       = Project::find($project_id);
            $task                          = ProjectTask::find($task_id);
            $financial_statement = FinancialStatement::where('project_id', $project_id)->get()->pluck('account', 'coa');

            return view('project_task.createJournalData', compact('project','task', 'financial_statement'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function journaldata(Request $request)
    {

        $data['journaldata']        = $journaldata = FinancialStatement::find($request->id);

        return json_encode($data);
    }

    public function saveJournalData(Request $request, $project_id, $task_id)
    {

        if(\Auth::user()->can('create project task'))
        {

            $id             = Crypt::decrypt($task_id);
            $project        = Project::find($project_id);
            $task           = ProjectTask::find($id);

            $journaldata = $request->items;
            $summary_journaldata = new SummaryJournalData();

            $lastAdjCodes = SummaryJournalData::select('adj_code')
                ->where('project_id', $project_id)
                ->groupBy('adj_code')
                ->orderBy('adj_code', 'desc')
                ->get();

            $adjCodeCounter = 1;
            $prefix = $request->adj_code;

            if ($lastAdjCodes->isNotEmpty()) {
                $lastAdjCode = $lastAdjCodes[0]->adj_code;

                if (strpos($lastAdjCode, $prefix) === 0) {
                    $lastCode = intval(substr($lastAdjCode, strlen($prefix)));
                    $adjCodeCounter = $lastCode + 1;
                }
            }

            $items = [];
            $dr = 0;
            $cr = 0;
            $previous_dr = 0;
            $previous_cr = 0;

            for ($i = 0; $i < count($journaldata); $i++) {
                $summary_journaldata->project_id = $project_id;
                $summary_journaldata->notes = $request->notes;
                $summary_journaldata->adj_code = $prefix . $adjCodeCounter;
                $summary_journaldata->dr = $journaldata[$i]['dr'];
                $summary_journaldata->cr = $journaldata[$i]['cr'];

                $coaValues = $journaldata[$i]['item'];

                $item = new SummaryJournalData();
                $item->project_id = $summary_journaldata->project_id;
                $item->notes = $summary_journaldata->notes;
                $item->adj_code = $summary_journaldata->adj_code;
                $item->dr = str_replace(',', '', $summary_journaldata->dr);
                $item->cr = str_replace(',', '', $summary_journaldata->cr);
                $item->coa = $coaValues;
                $items[] = $item;

                $data_keuangan = FinancialStatement::where('project_id', $project_id)
                ->where('coa', $coaValues)
                ->first();

                $m = $data_keuangan->m;

                // Cek apakah akun termasuk dalam M.4, M.5, M.6, M.7, M.8, M.9 (CE)
                if (strpos($m, 'M.4') !== false || strpos($m, 'M.5') !== false ||
                    strpos($m, 'M.6') !== false || strpos($m, 'M.7') !== false ||
                    strpos($m, 'M.8') !== false || strpos($m, 'M.9') !== false) {

                    $financial_data = FinancialStatement::where('project_id', $project_id)
                        ->where('lk', 'LK.34')
                        ->first();

                    if ($financial_data) {

                        $coaCodes = ['M.4', 'M.5', 'M.6', 'M.7', 'M.8', 'M.9'];

                        $summary_journal_data = FinancialStatement::where('project_id', $project_id)
                        ->whereIn('m', $coaCodes)
                        ->get();

                        // Menghitung total 'dr' dan 'cr' dari SummaryJournalData yang memiliki COA sesuai dengan $coaCodes
                        $previous_dr = $summary_journal_data->sum('dr');
                        $previous_cr = $summary_journal_data->sum('cr');

                        // Tambahkan nilai DR dan CR baru jika nilainya bukan 0
                        if ($item->dr != 0) {
                            $previous_dr += $item->dr;
                        }

                        if ($item->cr != 0) {
                            $previous_cr += $item->cr;
                        }

                        // Hitung nilai audited berdasarkan DR dan CR yang baru
                        $financial_data->audited = $financial_data->inhouse + $previous_dr - $previous_cr;
                        $financial_data->save();
                    }

                }

                // Cek apakah akun termasuk dalam M.4, M.5, M.6, M.7, M.8, M.9 (OCI)
                if (strpos($m, 'M.10') !== false) 
                {

                    $financial_data = FinancialStatement::where('project_id', $project_id)
                        ->where('coa', 'OCI')
                        ->first();

                    if ($financial_data) {

                        $coaCodes = ['M.10'];

                        $summary_journal_data = FinancialStatement::where('project_id', $project_id)
                        ->whereIn('m', $coaCodes)
                        ->get();

                        // Menghitung total 'dr' dan 'cr' dari SummaryJournalData yang memiliki COA sesuai dengan $coaCodes
                        $previous_dr = $summary_journal_data->sum('dr');
                        $previous_cr = $summary_journal_data->sum('cr');

                        // Tambahkan nilai DR dan CR baru jika nilainya bukan 0
                        if ($item->dr != 0) {
                            $previous_dr += $item->dr;
                        }

                        if ($item->cr != 0) {
                            $previous_cr += $item->cr;
                        }

                        // Hitung nilai audited berdasarkan DR dan CR yang baru
                        $financial_data->audited = $financial_data->inhouse + $previous_dr - $previous_cr;
                        $financial_data->save();
                    }
                }   

                $financial_data = FinancialStatement::where('project_id', $project_id)
                    ->where('coa', $coaValues)
                    ->first();

                if ($financial_data) {
                    // Data ditemukan, lakukan penambahan nilai dr dan cr
                    $financial_data->dr += $item->dr;
                    $financial_data->cr += $item->cr;
                    $financial_data->audited = $financial_data->inhouse + $financial_data->dr - $financial_data->cr;
                    $financial_data->save();
                } else {
                    // Data tidak ditemukan, buat entri baru
                    $financial_data = new FinancialStatement();
                    $financial_data->project_id = $project_id;
                    $financial_data->coa = $coaValues;
                    $financial_data->dr = $item->dr;
                    $financial_data->cr = $item->cr;
                    $financial_data->audited = $financial_data->inhouse + $item->dr - $item->cr;
                    $financial_data->save();
                }
            }

            $adjCodeCounter++;

            $result = implode(',', $items);

            // Menyimpan item-item ke dalam database
            foreach ($items as $item) {
                $item->save();
            }


            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $project_id,
                    'task_id' => $task_id,
                    'log_type' => 'Create Journal Data',
                    'remark' => json_encode(['title' => 'Create Journal Data']),
                ]
            );

            return redirect()->route('projects.tasks.journal.entries', [$project_id, $task_id])->with('success', __('Journal Data successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function editJournalData($project_id, $task_id, $ids)
    {
        if(\Auth::user()->can('edit project task'))
        {
            $id             = Crypt::decrypt($task_id);
            $task           = ProjectTask::find($id);
            $project        = Project::find($project_id);
            $summary_journaldata = SummaryJournalData::find($ids);
            $financial_statement = FinancialStatement::where('project_id', $project_id)->get()->pluck('account', 'coa');

            return view('project_task.editJournalData', compact('financial_statement','task', 'summary_journaldata', 'project'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function updateJournalData(Request $request, $project_id, $task_id, $id)
    {
        if (\Auth::user()->can('edit project task')) {
            $journaldata = $request->items;

            // Mendapatkan nilai audited dari akun LK.34
            $lk_account = FinancialStatement::where('project_id', $project_id)
                ->where('lk', 'LK.34')
                ->first();

            $lk_inhouse = $lk_account->inhouse;
            $lk_audited = $lk_account->audited;

            // Mendapatkan nilai audited dari akun LK.35
            $lk_account_35 = FinancialStatement::where('project_id', $project_id)
                ->where('coa', 'OCI')
                ->first();

            $lk_inhouse_35 = $lk_account_35->inhouse;
            $lk_audited_35 = $lk_account_35->audited;

            for ($i = 0; $i < count($journaldata); $i++) {
                $summary_journaldata = SummaryJournalData::find($id);
                $summary_journaldata->project_id = $project_id;
                $summary_journaldata->notes = $request->notes;
                $dr = $journaldata[$i]['dr'];
                $cr = $journaldata[$i]['cr'];
                $summary_journaldata->dr = str_replace(',', '', $dr);
                $summary_journaldata->cr = str_replace(',', '', $cr);

                $coaValues = $journaldata[$i]['coa'];

                $item = SummaryJournalData::find($id);
                $item->project_id = $summary_journaldata->project_id;
                $item->notes = $summary_journaldata->notes;
                $item->adj_code = $summary_journaldata->adj_code;
                $item->dr = str_replace(',', '', $summary_journaldata->dr);
                $item->cr = str_replace(',', '', $summary_journaldata->cr);
                $item->coa = $coaValues;
                $items[] = $item;

                if ($dr != 0 || $cr != 0) {
                    // Cek apakah jenis data yang diupdate adalah dr atau cr
                    if ($dr != 0 && $cr == 0) {
                        // Update data DR dan perhitungan audited
                        $financial_data = FinancialStatement::where('project_id', $project_id)
                            ->where('coa', $coaValues)
                            ->first();

                        if ($financial_data) {
                            // Membandingkan nilai input dengan nilai sebelumnya
                            $drDifference = $summary_journaldata->dr - $financial_data->dr;

                            if ($drDifference != 0) {
                                // Perbarui nilai DR pada financial_data
                                $financial_data->dr += $drDifference;

                                // Perbarui nilai audited
                                $financial_data->audited = $financial_data->inhouse + $financial_data->dr - $financial_data->cr;

                                $m = $financial_data->m;

                                // Jika akun berkode m = M.4, M.5, M.6, M.7, M.8, M.9, perbarui nilai audited pada akun lk = LK.34
                                if (in_array($m, ['M.4', 'M.5', 'M.6', 'M.7', 'M.8', 'M.9'])) {

                                    // Mendapatkan nilai cr dari akun yang memenuhi kriteria m = M.4, M.5, M.6, M.7, M.8, M.9
                                    $cr_account = FinancialStatement::where('project_id', $project_id)
                                    ->whereIn('m', ['M.4', 'M.5', 'M.6', 'M.7', 'M.8', 'M.9'])
                                    ->sum('cr');

                                    $dr_account = FinancialStatement::where('project_id', $project_id)
                                    ->whereIn('m', ['M.4', 'M.5', 'M.6', 'M.7', 'M.8', 'M.9'])
                                    ->sum('dr');

                                    $cr_value = $cr_account ?? 0;
                                    $dr_value = $dr_account ?? 0;

                                    $previous_dr_data = SummaryJournalData::find($id); 
                                    $previous_dr = $dr_value + $financial_data->dr - $previous_dr_data->dr;

                                    $lk_audited = $lk_inhouse + $previous_dr - $cr_value;
                                    // dd($lk_inhouse, $dr_value, $financial_data->dr, $previous_dr, $cr_value);
                                    $lk_account->audited = $lk_audited;
                                    $lk_account->save();
                                }

                                if (in_array($m, ['M.10'])) {

                                    // Mendapatkan nilai cr dari akun yang memenuhi kriteria m = M.4, M.5, M.6, M.7, M.8, M.9
                                    $cr_account_35 = FinancialStatement::where('project_id', $project_id)
                                    ->whereIn('m', ['M.10'])
                                    ->sum('cr');

                                    $dr_account_35 = FinancialStatement::where('project_id', $project_id)
                                    ->whereIn('m', ['M.4', 'M.5', 'M.6', 'M.7', 'M.8', 'M.9'])
                                    ->sum('dr');

                                    $cr_value_35 = $cr_account_35 ?? 0;
                                    $dr_value_35 = $dr_account_35 ?? 0;

                                    $previous_dr_data_35 = SummaryJournalData::find($id); 
                                    $previous_dr_35 = $dr_value_35 + $financial_data->dr - $previous_dr_data_35->dr;

                                    $lk_audited_35 = $lk_inhouse_35 + $previous_dr_35 - $cr_value_35;
                                    $lk_account_35->audited = $lk_audited_35;
                                    $lk_account_35->save();
                                } 

                                // Simpan perubahan di financial_data
                                $financial_data->save();
                            }
                        }
                    } elseif ($dr == 0 && $cr != 0) {
                        // Update data CR dan perhitungan audited
                        $financial_data = FinancialStatement::where('project_id', $project_id)
                            ->where('coa', $coaValues)
                            ->first();

                        if ($financial_data) {
                            // Membandingkan nilai input dengan nilai sebelumnya
                            $crDifference = $summary_journaldata->cr - $financial_data->cr;

                            if ($crDifference != 0) {
                                // Perbarui nilai CR pada financial_data
                                $financial_data->cr += $crDifference;

                                // Perbarui nilai audited
                                $financial_data->audited = $financial_data->inhouse + $financial_data->dr - $financial_data->cr;

                                $m = $financial_data->m;

                                // Jika akun berkode m = M.4, M.5, M.6, M.7, M.8, M.9, perbarui nilai audited pada akun lk = LK.34
                                if (in_array($m, ['M.4', 'M.5', 'M.6', 'M.7', 'M.8', 'M.9'])) {

                                    // Mendapatkan nilai cr dari akun yang memenuhi kriteria m = M.4, M.5, M.6, M.7, M.8, M.9
                                    $dr_account = FinancialStatement::where('project_id', $project_id)
                                    ->whereIn('m', ['M.4', 'M.5', 'M.6', 'M.7', 'M.8', 'M.9'])
                                    ->sum('dr');

                                    // Mendapatkan nilai cr dari akun yang memenuhi kriteria m = M.4, M.5, M.6, M.7, M.8, M.9
                                    $cr_account = FinancialStatement::where('project_id', $project_id)
                                    ->whereIn('m', ['M.4', 'M.5', 'M.6', 'M.7', 'M.8', 'M.9'])
                                    ->sum('cr');

                                    $cr_value = $cr_account ?? 0;
                                    $dr_value = $dr_account ?? 0;

                                    $previous_cr_data = SummaryJournalData::find($id); 
                                    $previous_cr = $cr_value + $financial_data->cr - $previous_cr_data->cr;

                                    $lk_audited = $lk_inhouse + $dr_value - $previous_cr;
                                    $lk_account->audited = $lk_audited;
                                    $lk_account->save();
                                }

                                if (in_array($m, ['M.10'])) {

                                    // Mendapatkan nilai cr dari akun yang memenuhi kriteria m = M.4, M.5, M.6, M.7, M.8, M.9
                                    $dr_account_35 = FinancialStatement::where('project_id', $project_id)
                                    ->whereIn('m', ['M.10'])
                                    ->sum('dr');

                                    // Mendapatkan nilai cr dari akun yang memenuhi kriteria m = M.4, M.5, M.6, M.7, M.8, M.9
                                    $cr_account_35 = FinancialStatement::where('project_id', $project_id)
                                    ->whereIn('m', ['M.4', 'M.5', 'M.6', 'M.7', 'M.8', 'M.9'])
                                    ->sum('cr');

                                    $cr_value_35 = $cr_account_35 ?? 0;
                                    $dr_value_35 = $dr_account_35 ?? 0;
                                    
                                    $previous_cr_data_35 = SummaryJournalData::find($id); 
                                    $previous_cr_35 = $cr_value + $financial_data->cr - $previous_cr_data_35->cr;

                                    $lk_audited_35 = $lk_inhouse_35 + $dr_value_35 - $previous_cr_35;
                                    $lk_account_35->audited = $lk_audited_35;
                                    $lk_account_35->save();
                                } 

                                // Simpan perubahan di financial_data
                                $financial_data->save();
                            }
                        }
                    }
                }

                // Update nilai dr dan cr di summary_journaldata
                $summary_journaldata->save();
            }

            return redirect()->route('projects.tasks.journal.entries', [$project_id, $task_id])->with('success', __('Journal Data successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroyJournalData(Request $request, $project_id, $task_id, $id)
    {
        if (\Auth::user()->can('delete project task')) {
            $summary_journaldata = SummaryJournalData::find($id);
            $drToDelete = $summary_journaldata->dr;
            $crToDelete = $summary_journaldata->cr;
            
            $summary_journaldata->delete();
            
            $financial_data = FinancialStatement::where('project_id', $project_id)
                ->where('coa', $summary_journaldata->coa)
                ->first();

            $lk_account = FinancialStatement::where('project_id', $project_id)
                ->where('lk', 'LK.34')
                ->first();

            $lk_inhouse = $lk_account->inhouse;
            $lk_audited = $lk_account->audited;

            // Mendapatkan nilai audited dari akun LK.35
            $lk_account_35 = FinancialStatement::where('project_id', $project_id)
                ->where('coa', 'OCI')
                ->first();

            $lk_inhouse_35 = $lk_account_35->inhouse;
            $lk_audited_35 = $lk_account_35->audited;

            $m = $financial_data->m;

            if ($financial_data) {
                $financial_data->dr -= $drToDelete;
                $financial_data->cr -= $crToDelete;
                $financial_data->audited = $financial_data->inhouse + $financial_data->dr - $financial_data->cr;
                $financial_data->save();
            }

            // Cek apakah akun termasuk dalam kriteria yang ditentukan
            if (in_array($m, ['M.4', 'M.5', 'M.6', 'M.7', 'M.8', 'M.9'])) {

                $dr_account = FinancialStatement::where('project_id', $project_id)
                ->whereIn('m', ['M.4', 'M.5', 'M.6', 'M.7', 'M.8', 'M.9'])
                ->sum('dr');

                $dr_value = $dr_account ?? 0;

                $cr_account = FinancialStatement::where('project_id', $project_id)
                ->whereIn('m', ['M.4', 'M.5', 'M.6', 'M.7', 'M.8', 'M.9'])
                ->sum('cr');

                $cr_value = $cr_account ?? 0;

                if ($lk_account) {
                    $lk_audited = $lk_inhouse + $dr_value - $cr_value;
                    // dd($lk_audited);
                    $lk_account->audited = $lk_audited;
                    $lk_account->save();
                }
            }

            if (in_array($m, ['M.10'])) {

                $dr_account_35 = FinancialStatement::where('project_id', $project_id)
                ->whereIn('m', ['M.10',])
                ->sum('dr');

                $dr_value_35 = $dr_account_35 ?? 0;

                $cr_account_35 = FinancialStatement::where('project_id', $project_id)
                ->whereIn('m', ['M.10'])
                ->sum('cr');

                $cr_value_35 = $cr_account_35 ?? 0;

                if ($lk_account_35) {
                    $lk_audited_35 = $lk_inhouse_35 + $dr_value_35 - $cr_value_35;
                    // dd($lk_audited);
                    $lk_account_35->audited = $lk_audited_35;
                    $lk_account_35->save();
                }
            }
            
            return redirect()->route('projects.tasks.journal.entries', [$project_id, $task_id])->with('success', __('Journal Data successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getKeuanganRingkas(Request $request, $project_id, $task_id)
    {
        if(\Auth::user()->can('manage project task'))
        {  
            $id                                 = Crypt::decrypt($task_id);
            $project                            = Project::find($project_id);
            $task                               = ProjectTask::find($id);
            $financial_statement                = FinancialStatement::where('project_id', $project_id)->get();
            $materialitas                       = Materialitas::get();
            $get_data_materialitas              = ValueMaterialitas::where('project_id', $project_id)->get();
            $mapping_accounts                   = MappingAccount::where('project_id', $project_id)->get();
            $summart_materialitas = SummaryMateriality::where('project_id', $project_id)->orderBy('id', 'DESC')->first();
            $data_initialmaterialityom = isset($summart_materialitas) && is_object($summart_materialitas) ? $summart_materialitas->initialmaterialityom : null;
            $notesanalysis                      = NotesAnalysis::where('project_id', $project_id)->where('task_id','=', $task_id)->orderBy('id', 'DESC')->first();
            $respons                            = Respons::where('project_id', $project_id)->where('task_id', $id)->orderBy('id', 'DESC')->first();

            $data_keuangan = $mapping_accounts->map(function ($mapping_account) use ($financial_statement, $data_initialmaterialityom) {
                $account_code = $mapping_account->account_code;
                $name = $mapping_account->name;
                $account_group = $mapping_account->materialitas->name;
            
                $financial_data = $financial_statement->where('lk', $account_code);

                $prior_period2 = $financial_data->sum('prior_period2') ?? null;
                $prior_period = $financial_data->sum('prior_period') ?? null;
                $inhouse = $financial_data->sum('inhouse') ?? null;
                $audited = $financial_data->sum('audited') ?? null;
                $kenaikan_penurunan_prior_period_1 = $inhouse - $prior_period;
                $filter_kenaikan_penurunan_prior_period_1 = null;

                if ($kenaikan_penurunan_prior_period_1 > $data_initialmaterialityom || $kenaikan_penurunan_prior_period_1 * -1 > $data_initialmaterialityom) {
                    $filter_kenaikan_penurunan_prior_period_1 = $kenaikan_penurunan_prior_period_1;
                } 

                $kenaikan_penurunan_prior_period_persen_1 = ($prior_period != 0) ? (($inhouse - $prior_period) / $prior_period) * 100 : 0;
                $kenaikan_penurunan_prior_period_2 = $audited - $prior_period;
                $kenaikan_penurunan_prior_period_persen_2 = ($prior_period != 0) ? (($audited - $prior_period) / $prior_period) * 100 : 0;


                if ($account_group === 'LIABILITAS') {
                    $prior_period2 *= -1;
                    $prior_period *= -1;
                    $inhouse *= -1;
                    $audited *= -1;
                    $kenaikan_penurunan_prior_period_1 *= -1;
                    $kenaikan_penurunan_prior_period_2 *= -1;
                    $kenaikan_penurunan_prior_period_persen_1 *= -1;
                    $kenaikan_penurunan_prior_period_persen_2 *= -1; 
                }
                elseif($account_group === 'EKUITAS')
                {
                    $prior_period2 *= -1;
                    $prior_period *= -1;
                    $inhouse *= -1;
                    $audited *= -1;
                    $kenaikan_penurunan_prior_period_1 *= -1;
                    $kenaikan_penurunan_prior_period_2 *= -1;
                    $kenaikan_penurunan_prior_period_persen_1 *= -1;
                    $kenaikan_penurunan_prior_period_persen_2 *= -1; 
                }
                elseif($account_group === 'PENDAPATAN')
                {
                    $prior_period2 *= -1;
                    $prior_period *= -1;
                    $inhouse *= -1;
                    $audited *= -1;
                    $kenaikan_penurunan_prior_period_1 *= -1;
                    $kenaikan_penurunan_prior_period_2 *= -1;
                    $kenaikan_penurunan_prior_period_persen_1 *= -1;
                    $kenaikan_penurunan_prior_period_persen_2 *= -1; 
                }
                elseif($account_group === 'PENDAPATAN / BEBAN KEUANGAN')
                {
                    $prior_period2 *= -1;
                    $prior_period *= -1;
                    $inhouse *= -1;
                    $audited *= -1;
                    $kenaikan_penurunan_prior_period_1 *= -1;
                    $kenaikan_penurunan_prior_period_2 *= -1;
                    $kenaikan_penurunan_prior_period_persen_1 *= -1;
                    $kenaikan_penurunan_prior_period_persen_2 *= -1; 
                }
                elseif($account_group === 'PENDAPATAN / BEBAN LAIN-LAIN')
                {
                    $prior_period2 *= -1;
                    $prior_period *= -1;
                    $inhouse *= -1;
                    $audited *= -1;
                    $kenaikan_penurunan_prior_period_1 *= -1;
                    $kenaikan_penurunan_prior_period_2 *= -1;
                    $kenaikan_penurunan_prior_period_persen_1 *= -1;
                    $kenaikan_penurunan_prior_period_persen_2 *= -1; 
                }
                elseif($account_group === "PENGHASILAN KOMPREHENSIF LAIN")
                {
                    $prior_period2 *= -1;
                    $prior_period *= -1;
                    $inhouse *= -1;
                    $audited *= -1;
                    $kenaikan_penurunan_prior_period_1 *= -1;
                    $kenaikan_penurunan_prior_period_2 *= -1;
                    $kenaikan_penurunan_prior_period_persen_1 *= -1;
                    $kenaikan_penurunan_prior_period_persen_2 *= -1; 
                }
            
                return [
                    'account_code' => $account_code,
                    'name' => $name,
                    'account_group' => $account_group,
                    'prior_period2' => $prior_period2,
                    'prior_period' => $prior_period,
                    'inhouse' => $inhouse,
                    'audited' => $audited,
                    'kenaikan_penurunan_prior_period_1' => $kenaikan_penurunan_prior_period_1,
                    'filter_kenaikan_penurunan_prior_period_1' => $filter_kenaikan_penurunan_prior_period_1,
                    'kenaikan_penurunan_prior_period_persen_1' => $kenaikan_penurunan_prior_period_persen_1,
                    'kenaikan_penurunan_prior_period_2' => $kenaikan_penurunan_prior_period_2,
                    'kenaikan_penurunan_prior_period_persen_2' => $kenaikan_penurunan_prior_period_persen_2,
                ];
            });

            $tableData = [];

            if (count(array($data_keuangan)) > 0) {
                foreach ($data_keuangan as $data_keuangans) {
                    // Lakukan pemrosesan data sesuai kebutuhan Anda
                    // ...

                    // Cek apakah nilai tidak kosong atau null sebelum menyimpan ke $tableData
                    if (!empty($data_keuangans['filter_kenaikan_penurunan_prior_period_1'])) {
                        $tableData[] = [
                            'account_code' => $data_keuangans['account_code'],
                            'name' => $data_keuangans['name'],
                            'filter_kenaikan_penurunan_prior_period_1' => $data_keuangans['filter_kenaikan_penurunan_prior_period_1'],
                        ];
                    }
                }
            }

            
            

            // $data_lk_2020 = [];
            // $data_lk_2021 = [];
            // $data_lk_in_2022 = [];
            // $data_januari = [];
            // $data_februari = [];
            // $data_maret = [];
            // $data_april = [];
            // $data_mei = [];
            // $data_juni = [];
            // $data_juli = [];
            // $data_agustus = [];
            // $data_september = [];
            // $data_oktober = [];
            // $data_november = [];
            // $data_desember = [];

            // $index = [];
            // $summarys = [];
            // $cn = [];

            // for ($i = 1; $i <= 35; $i++) {
            //     $m = 'LK.' . $i;
            //     $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();
                
            //     $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
            //     $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
                
            //     $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
            //     $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
                
            //     $data_lk_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
            //     $total_lk_in_2022[$m] = array_sum($data_lk_in_2022[$m]);
                
            //     $data_lk_au_2022[$m] = $data_lk->pluck('audited')->toArray();
            //     $total_lk_au_2022[$m] = array_sum($data_lk_au_2022[$m]);

            //     $data_januari[$m] = $data_lk->pluck('jan')->toArray();
            //     $total_januari[$m] = array_sum($data_januari[$m]);
                
            //     $data_februari[$m] = $data_lk->pluck('feb')->toArray();
            //     $total_februari[$m] = array_sum($data_februari[$m]);

            //     $data_maret[$m] = $data_lk->pluck('mar')->toArray();
            //     $total_maret[$m] = array_sum($data_maret[$m]);

            //     $data_april[$m] = $data_lk->pluck('apr')->toArray();
            //     $total_april[$m] = array_sum($data_april[$m]);

            //     $data_mei[$m] = $data_lk->pluck('may')->toArray();
            //     $total_mei[$m] = array_sum($data_mei[$m]);

            //     $data_juni[$m] = $data_lk->pluck('jun')->toArray();
            //     $total_juni[$m] = array_sum($data_juni[$m]);

            //     $data_juli[$m] = $data_lk->pluck('jul')->toArray();
            //     $total_juli[$m] = array_sum($data_juli[$m]);

            //     $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
            //     $total_agustus[$m] = array_sum($data_agustus[$m]);

            //     $data_september[$m] = $data_lk->pluck('sep')->toArray();
            //     $total_september[$m] = array_sum($data_september[$m]);

            //     $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
            //     $total_oktober[$m] = array_sum($data_oktober[$m]);

            //     $data_november[$m] = $data_lk->pluck('nov')->toArray();
            //     $total_november[$m] = array_sum($data_november[$m]);

            //     $data_desember[$m] = $data_lk->pluck('dec')->toArray();
            //     $total_desember[$m] = array_sum($data_desember[$m]);
            // }

            // //Mencari nilai total aset
            // $total_aset_2020 = 0;
            // $total_aset_2021 = 0;
            // $total_aset_in_2022 = 0;
            // $total_aset_au_2022 = 0;
            // $total_aset_januari = 0;
            // $total_aset_februari = 0;
            // $total_aset_maret = 0;
            // $total_aset_april = 0;
            // $total_aset_mei = 0;
            // $total_aset_juni = 0;
            // $total_aset_juli = 0;
            // $total_aset_agustus = 0;
            // $total_aset_september = 0;
            // $total_aset_oktober = 0;
            // $total_aset_november = 0;
            // $total_aset_desember = 0;
            
            // for ($i = 1; $i <= 15; $i++) {
            //     $m = 'LK.' . $i;
            //     $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

            //     $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
            //     $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
                
            //     $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
            //     $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
                
            //     $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
            //     $total_in_2022[$m] = array_sum($data_in_2022[$m]);
                
            //     $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
            //     $total_au_2022[$m] = array_sum($data_au_2022[$m]);

            //     $data_januari[$m] = $data_lk->pluck('jan')->toArray();
            //     $total_januari[$m] = array_sum($data_januari[$m]);
                
            //     $data_februari[$m] = $data_lk->pluck('feb')->toArray();
            //     $total_februari[$m] = array_sum($data_februari[$m]);

            //     $data_maret[$m] = $data_lk->pluck('mar')->toArray();
            //     $total_maret[$m] = array_sum($data_maret[$m]);

            //     $data_april[$m] = $data_lk->pluck('apr')->toArray();
            //     $total_april[$m] = array_sum($data_april[$m]);

            //     $data_mei[$m] = $data_lk->pluck('may')->toArray();
            //     $total_mei[$m] = array_sum($data_mei[$m]);

            //     $data_juni[$m] = $data_lk->pluck('jun')->toArray();
            //     $total_juni[$m] = array_sum($data_juni[$m]);

            //     $data_juli[$m] = $data_lk->pluck('jul')->toArray();
            //     $total_juli[$m] = array_sum($data_juli[$m]);

            //     $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
            //     $total_agustus[$m] = array_sum($data_agustus[$m]);

            //     $data_september[$m] = $data_lk->pluck('sep')->toArray();
            //     $total_september[$m] = array_sum($data_september[$m]);

            //     $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
            //     $total_oktober[$m] = array_sum($data_oktober[$m]);

            //     $data_november[$m] = $data_lk->pluck('nov')->toArray();
            //     $total_november[$m] = array_sum($data_november[$m]);

            //     $data_desember[$m] = $data_lk->pluck('dec')->toArray();
            //     $total_desember[$m] = array_sum($data_desember[$m]);

            //     $total_aset_2020 += $total_lk_2020[$m];
            //     $total_aset_2021 += $total_lk_2021[$m];
            //     $total_aset_in_2022 += $total_in_2022[$m];
            //     $total_aset_au_2022 += $total_au_2022[$m];
            //     $total_aset_januari += $total_januari[$m];
            //     $total_aset_februari += $total_februari[$m];
            //     $total_aset_maret += $total_maret[$m];
            //     $total_aset_april += $total_april[$m];
            //     $total_aset_mei += $total_mei[$m];
            //     $total_aset_juni += $total_juni[$m];
            //     $total_aset_juli += $total_juli[$m];
            //     $total_aset_agustus += $total_agustus[$m];
            //     $total_aset_september += $total_september[$m];
            //     $total_aset_oktober += $total_oktober[$m];
            //     $total_aset_november += $total_november[$m];
            //     $total_aset_desember += $total_desember[$m];
            // }


            // //Mencari nilai total liabitias
            // $total_liabilitas_2020 = 0;
            // $total_liabilitas_2021 = 0;
            // $total_liabilitas_in_2022 = 0;
            // $total_liabilitas_au_2022 = 0;
            // $total_liabilitas_januari = 0;
            // $total_liabilitas_februari = 0;
            // $total_liabilitas_maret = 0;
            // $total_liabilitas_april = 0;
            // $total_liabilitas_mei = 0;
            // $total_liabilitas_juni = 0;
            // $total_liabilitas_juli = 0;
            // $total_liabilitas_agustus = 0;
            // $total_liabilitas_september = 0;
            // $total_liabilitas_oktober = 0;
            // $total_liabilitas_november = 0;
            // $total_liabilitas_desember = 0;
            
            // for ($i = 16; $i <= 25; $i++) {
            //     $m = 'LK.' . $i;
            //     $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

            //     $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
            //     $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
                
            //     $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
            //     $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
                
            //     $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
            //     $total_in_2022[$m] = array_sum($data_in_2022[$m]);
                
            //     $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
            //     $total_au_2022[$m] = array_sum($data_au_2022[$m]);
                
            //     $data_januari[$m] = $data_lk->pluck('jan')->toArray();
            //     $total_januari[$m] = array_sum($data_januari[$m]);
                
            //     $data_februari[$m] = $data_lk->pluck('feb')->toArray();
            //     $total_februari[$m] = array_sum($data_februari[$m]);

            //     $data_maret[$m] = $data_lk->pluck('mar')->toArray();
            //     $total_maret[$m] = array_sum($data_maret[$m]);

            //     $data_april[$m] = $data_lk->pluck('apr')->toArray();
            //     $total_april[$m] = array_sum($data_april[$m]);

            //     $data_mei[$m] = $data_lk->pluck('may')->toArray();
            //     $total_mei[$m] = array_sum($data_mei[$m]);

            //     $data_juni[$m] = $data_lk->pluck('jun')->toArray();
            //     $total_juni[$m] = array_sum($data_juni[$m]);

            //     $data_juli[$m] = $data_lk->pluck('jul')->toArray();
            //     $total_juli[$m] = array_sum($data_juli[$m]);

            //     $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
            //     $total_agustus[$m] = array_sum($data_agustus[$m]);

            //     $data_september[$m] = $data_lk->pluck('sep')->toArray();
            //     $total_september[$m] = array_sum($data_september[$m]);

            //     $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
            //     $total_oktober[$m] = array_sum($data_oktober[$m]);

            //     $data_november[$m] = $data_lk->pluck('nov')->toArray();
            //     $total_november[$m] = array_sum($data_november[$m]);

            //     $data_desember[$m] = $data_lk->pluck('dec')->toArray();
            //     $total_desember[$m] = array_sum($data_desember[$m]);

            //     $total_liabilitas_2020 += $total_lk_2020[$m];
            //     $total_liabilitas_2021 += $total_lk_2021[$m];
            //     $total_liabilitas_in_2022 += $total_in_2022[$m];
            //     $total_liabilitas_au_2022 += $total_au_2022[$m];
            //     $total_liabilitas_januari += $total_januari[$m];
            //     $total_liabilitas_februari += $total_februari[$m];
            //     $total_liabilitas_maret += $total_maret[$m];
            //     $total_liabilitas_april += $total_april[$m];
            //     $total_liabilitas_mei += $total_mei[$m];
            //     $total_liabilitas_juni += $total_juni[$m];
            //     $total_liabilitas_juli += $total_juli[$m];
            //     $total_liabilitas_agustus += $total_agustus[$m];
            //     $total_liabilitas_september += $total_september[$m];
            //     $total_liabilitas_oktober += $total_oktober[$m];
            //     $total_liabilitas_november += $total_november[$m];
            //     $total_liabilitas_desember += $total_desember[$m];
            // }

            // //Mencari nilai total ekuitas
            // $total_ekuitas_2020 = 0;
            // $total_ekuitas_2021 = 0;
            // $total_ekuitas_in_2022 = 0;
            // $total_ekuitas_au_2022 = 0;
            // $total_ekuitas_januari = 0;
            // $total_ekuitas_februari = 0;
            // $total_ekuitas_maret = 0;
            // $total_ekuitas_april = 0;
            // $total_ekuitas_mei = 0;
            // $total_ekuitas_juni = 0;
            // $total_ekuitas_juli = 0;
            // $total_ekuitas_agustus = 0;
            // $total_ekuitas_september = 0;
            // $total_ekuitas_oktober = 0;
            // $total_ekuitas_november = 0;
            // $total_ekuitas_desember = 0;
            
            // for ($i = 26; $i <= 29; $i++) {
            //     $m = 'LK.' . $i;
            //     $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

            //     $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
            //     $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
            //     $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
            //     $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
            //     $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
            //     $total_in_2022[$m] = array_sum($data_in_2022[$m]);
            //     $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
            //     $total_au_2022[$m] = array_sum($data_au_2022[$m]);
                
            //     $data_januari[$m] = $data_lk->pluck('jan')->toArray();
            //     $total_januari[$m] = array_sum($data_januari[$m]);
                
            //     $data_februari[$m] = $data_lk->pluck('feb')->toArray();
            //     $total_februari[$m] = array_sum($data_februari[$m]);

            //     $data_maret[$m] = $data_lk->pluck('mar')->toArray();
            //     $total_maret[$m] = array_sum($data_maret[$m]);

            //     $data_april[$m] = $data_lk->pluck('apr')->toArray();
            //     $total_april[$m] = array_sum($data_april[$m]);

            //     $data_mei[$m] = $data_lk->pluck('may')->toArray();
            //     $total_mei[$m] = array_sum($data_mei[$m]);

            //     $data_juni[$m] = $data_lk->pluck('jun')->toArray();
            //     $total_juni[$m] = array_sum($data_juni[$m]);

            //     $data_juli[$m] = $data_lk->pluck('jul')->toArray();
            //     $total_juli[$m] = array_sum($data_juli[$m]);

            //     $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
            //     $total_agustus[$m] = array_sum($data_agustus[$m]);

            //     $data_september[$m] = $data_lk->pluck('sep')->toArray();
            //     $total_september[$m] = array_sum($data_september[$m]);

            //     $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
            //     $total_oktober[$m] = array_sum($data_oktober[$m]);

            //     $data_november[$m] = $data_lk->pluck('nov')->toArray();
            //     $total_november[$m] = array_sum($data_november[$m]);

            //     $data_desember[$m] = $data_lk->pluck('dec')->toArray();
            //     $total_desember[$m] = array_sum($data_desember[$m]);

            //     $total_ekuitas_2020 += $total_lk_2020[$m];
            //     $total_ekuitas_2021 += $total_lk_2021[$m];
            //     $total_ekuitas_in_2022 += $total_in_2022[$m];
            //     $total_ekuitas_au_2022 += $total_au_2022[$m];
            //     $total_ekuitas_januari += $total_januari[$m];
            //     $total_ekuitas_februari += $total_februari[$m];
            //     $total_ekuitas_maret += $total_maret[$m];
            //     $total_ekuitas_april += $total_april[$m];
            //     $total_ekuitas_mei += $total_mei[$m];
            //     $total_ekuitas_juni += $total_juni[$m];
            //     $total_ekuitas_juli += $total_juli[$m];
            //     $total_ekuitas_agustus += $total_agustus[$m];
            //     $total_ekuitas_september += $total_september[$m];
            //     $total_ekuitas_oktober += $total_oktober[$m];
            //     $total_ekuitas_november += $total_november[$m];
            //     $total_ekuitas_desember += $total_desember[$m];
            // }

            // //Mencari nilai total laba kotor
            // $total_laba_kotor_2020 = 0;
            // $total_laba_kotor_2021 = 0;
            // $total_laba_kotor_in_2022 = 0;
            // $total_laba_kotor_au_2022 = 0;
            // $total_laba_kotor_januari = 0;
            // $total_laba_kotor_februari = 0;
            // $total_laba_kotor_maret = 0;
            // $total_laba_kotor_april = 0;
            // $total_laba_kotor_mei = 0;
            // $total_laba_kotor_juni = 0;
            // $total_laba_kotor_juli = 0;
            // $total_laba_kotor_agustus = 0;
            // $total_laba_kotor_september = 0;
            // $total_laba_kotor_oktober = 0;
            // $total_laba_kotor_november = 0;
            // $total_laba_kotor_desember = 0;
            
            // for ($i = 30; $i <= 31; $i++) {
            //     $m = 'LK.' . $i;
            //     $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

            //     $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
            //     $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
            //     $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
            //     $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
            //     $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
            //     $total_in_2022[$m] = array_sum($data_in_2022[$m]);
            //     $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
            //     $total_au_2022[$m] = array_sum($data_au_2022[$m]);

            //     $data_januari[$m] = $data_lk->pluck('jan')->toArray();
            //     $total_januari[$m] = array_sum($data_januari[$m]);
                
            //     $data_februari[$m] = $data_lk->pluck('feb')->toArray();
            //     $total_februari[$m] = array_sum($data_februari[$m]);

            //     $data_maret[$m] = $data_lk->pluck('mar')->toArray();
            //     $total_maret[$m] = array_sum($data_maret[$m]);

            //     $data_april[$m] = $data_lk->pluck('apr')->toArray();
            //     $total_april[$m] = array_sum($data_april[$m]);

            //     $data_mei[$m] = $data_lk->pluck('may')->toArray();
            //     $total_mei[$m] = array_sum($data_mei[$m]);

            //     $data_juni[$m] = $data_lk->pluck('jun')->toArray();
            //     $total_juni[$m] = array_sum($data_juni[$m]);

            //     $data_juli[$m] = $data_lk->pluck('jul')->toArray();
            //     $total_juli[$m] = array_sum($data_juli[$m]);

            //     $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
            //     $total_agustus[$m] = array_sum($data_agustus[$m]);

            //     $data_september[$m] = $data_lk->pluck('sep')->toArray();
            //     $total_september[$m] = array_sum($data_september[$m]);

            //     $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
            //     $total_oktober[$m] = array_sum($data_oktober[$m]);

            //     $data_november[$m] = $data_lk->pluck('nov')->toArray();
            //     $total_november[$m] = array_sum($data_november[$m]);

            //     $data_desember[$m] = $data_lk->pluck('dec')->toArray();
            //     $total_desember[$m] = array_sum($data_desember[$m]);

            //     $total_laba_kotor_2020 += $total_lk_2020[$m];
            //     $total_laba_kotor_2021 += $total_lk_2021[$m];
            //     $total_laba_kotor_in_2022 += $total_in_2022[$m];
            //     $total_laba_kotor_au_2022 += $total_au_2022[$m];
            //     $total_laba_kotor_januari += $total_januari[$m];
            //     $total_laba_kotor_februari += $total_februari[$m];
            //     $total_laba_kotor_maret += $total_maret[$m];
            //     $total_laba_kotor_april += $total_april[$m];
            //     $total_laba_kotor_mei += $total_mei[$m];
            //     $total_laba_kotor_juni += $total_juni[$m];
            //     $total_laba_kotor_juli += $total_juli[$m];
            //     $total_laba_kotor_agustus += $total_agustus[$m];
            //     $total_laba_kotor_september += $total_september[$m];
            //     $total_laba_kotor_oktober += $total_oktober[$m];
            //     $total_laba_kotor_november += $total_november[$m];
            //     $total_laba_kotor_desember += $total_desember[$m];
            // }

            // //Mencari nilai total laba bersih sebelum pajak
            // $total_laba_bersih_sebelum_pajak_2020 = 0;
            // $total_laba_bersih_sebelum_pajak_2021 = 0;
            // $total_laba_bersih_sebelum_pajak_in_2022 = 0;
            // $total_laba_bersih_sebelum_pajak_au_2022 = 0;
            // $total_laba_bersih_sebelum_pajak_januari = 0;
            // $total_laba_bersih_sebelum_pajak_februari = 0;
            // $total_laba_bersih_sebelum_pajak_maret = 0;
            // $total_laba_bersih_sebelum_pajak_april = 0;
            // $total_laba_bersih_sebelum_pajak_mei = 0;
            // $total_laba_bersih_sebelum_pajak_juni = 0;
            // $total_laba_bersih_sebelum_pajak_juli = 0;
            // $total_laba_bersih_sebelum_pajak_agustus = 0;
            // $total_laba_bersih_sebelum_pajak_september = 0;
            // $total_laba_bersih_sebelum_pajak_oktober = 0;
            // $total_laba_bersih_sebelum_pajak_november = 0;
            // $total_laba_bersih_sebelum_pajak_desember = 0;
            
            // for ($i = 32; $i <= 34; $i++) {
            //     $m = 'LK.' . $i;
            //     $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

            //     $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
            //     $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
            //     $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
            //     $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
            //     $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
            //     $total_in_2022[$m] = array_sum($data_in_2022[$m]);
            //     $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
            //     $total_au_2022[$m] = array_sum($data_au_2022[$m]);

            //     $data_januari[$m] = $data_lk->pluck('jan')->toArray();
            //     $total_januari[$m] = array_sum($data_januari[$m]);
                
            //     $data_februari[$m] = $data_lk->pluck('feb')->toArray();
            //     $total_februari[$m] = array_sum($data_februari[$m]);

            //     $data_maret[$m] = $data_lk->pluck('mar')->toArray();
            //     $total_maret[$m] = array_sum($data_maret[$m]);

            //     $data_april[$m] = $data_lk->pluck('apr')->toArray();
            //     $total_april[$m] = array_sum($data_april[$m]);

            //     $data_mei[$m] = $data_lk->pluck('may')->toArray();
            //     $total_mei[$m] = array_sum($data_mei[$m]);

            //     $data_juni[$m] = $data_lk->pluck('jun')->toArray();
            //     $total_juni[$m] = array_sum($data_juni[$m]);

            //     $data_juli[$m] = $data_lk->pluck('jul')->toArray();
            //     $total_juli[$m] = array_sum($data_juli[$m]);

            //     $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
            //     $total_agustus[$m] = array_sum($data_agustus[$m]);

            //     $data_september[$m] = $data_lk->pluck('sep')->toArray();
            //     $total_september[$m] = array_sum($data_september[$m]);

            //     $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
            //     $total_oktober[$m] = array_sum($data_oktober[$m]);

            //     $data_november[$m] = $data_lk->pluck('nov')->toArray();
            //     $total_november[$m] = array_sum($data_november[$m]);

            //     $data_desember[$m] = $data_lk->pluck('dec')->toArray();
            //     $total_desember[$m] = array_sum($data_desember[$m]);

            //     $total_laba_bersih_sebelum_pajak_2020 += $total_lk_2020[$m];
            //     $total_laba_bersih_sebelum_pajak_2021 += $total_lk_2021[$m];
            //     $total_laba_bersih_sebelum_pajak_in_2022 += $total_in_2022[$m];
            //     $total_laba_bersih_sebelum_pajak_au_2022 += $total_au_2022[$m];
            //     $total_laba_bersih_sebelum_pajak_januari += $total_januari[$m];
            //     $total_laba_bersih_sebelum_pajak_februari += $total_februari[$m];
            //     $total_laba_bersih_sebelum_pajak_maret += $total_maret[$m];
            //     $total_laba_bersih_sebelum_pajak_april += $total_april[$m];
            //     $total_laba_bersih_sebelum_pajak_mei += $total_mei[$m];
            //     $total_laba_bersih_sebelum_pajak_juni += $total_juni[$m];
            //     $total_laba_bersih_sebelum_pajak_juli += $total_juli[$m];
            //     $total_laba_bersih_sebelum_pajak_agustus += $total_agustus[$m];
            //     $total_laba_bersih_sebelum_pajak_september += $total_september[$m];
            //     $total_laba_bersih_sebelum_pajak_oktober += $total_oktober[$m];
            //     $total_laba_bersih_sebelum_pajak_november += $total_november[$m];
            //     $total_laba_bersih_sebelum_pajak_desember += $total_desember[$m];
            // }

            // //Mencari nilai total laba bersih setelah pajak
            // $total_laba_bersih_setelah_pajak_2020 = 0;
            // $total_laba_bersih_setelah_pajak_2021 = 0;
            // $total_laba_bersih_setelah_pajak_in_2022 = 0;
            // $total_laba_bersih_setelah_pajak_au_2022 = 0;
            // $total_laba_bersih_setelah_pajak_januari = 0;
            // $total_laba_bersih_setelah_pajak_februari = 0;
            // $total_laba_bersih_setelah_pajak_maret = 0;
            // $total_laba_bersih_setelah_pajak_april = 0;
            // $total_laba_bersih_setelah_pajak_mei = 0;
            // $total_laba_bersih_setelah_pajak_juni = 0;
            // $total_laba_bersih_setelah_pajak_juli = 0;
            // $total_laba_bersih_setelah_pajak_agustus = 0;
            // $total_laba_bersih_setelah_pajak_september = 0;
            // $total_laba_bersih_setelah_pajak_oktober = 0;
            // $total_laba_bersih_setelah_pajak_november = 0;
            // $total_laba_bersih_setelah_pajak_desember = 0;
            
            // $i = 35;
            // $m = 'LK.' . $i;
            // $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

            // $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
            // $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
            // $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
            // $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
            // $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
            // $total_in_2022[$m] = array_sum($data_in_2022[$m]);
            // $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
            // $total_au_2022[$m] = array_sum($data_au_2022[$m]);
            // $data_januari[$m] = $data_lk->pluck('jan')->toArray();
            // $total_januari[$m] = array_sum($data_januari[$m]);
            
            // $data_februari[$m] = $data_lk->pluck('feb')->toArray();
            // $total_februari[$m] = array_sum($data_februari[$m]);

            // $data_maret[$m] = $data_lk->pluck('mar')->toArray();
            // $total_maret[$m] = array_sum($data_maret[$m]);

            // $data_april[$m] = $data_lk->pluck('apr')->toArray();
            // $total_april[$m] = array_sum($data_april[$m]);

            // $data_mei[$m] = $data_lk->pluck('may')->toArray();
            // $total_mei[$m] = array_sum($data_mei[$m]);

            // $data_juni[$m] = $data_lk->pluck('jun')->toArray();
            // $total_juni[$m] = array_sum($data_juni[$m]);

            // $data_juli[$m] = $data_lk->pluck('jul')->toArray();
            // $total_juli[$m] = array_sum($data_juli[$m]);

            // $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
            // $total_agustus[$m] = array_sum($data_agustus[$m]);

            // $data_september[$m] = $data_lk->pluck('sep')->toArray();
            // $total_september[$m] = array_sum($data_september[$m]);

            // $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
            // $total_oktober[$m] = array_sum($data_oktober[$m]);

            // $data_november[$m] = $data_lk->pluck('nov')->toArray();
            // $total_november[$m] = array_sum($data_november[$m]);

            // $data_desember[$m] = $data_lk->pluck('dec')->toArray();
            // $total_desember[$m] = array_sum($data_desember[$m]);

            // $total_laba_bersih_setelah_pajak_2020 += $total_lk_2020[$m];
            // $total_laba_bersih_setelah_pajak_2021 += $total_lk_2021[$m];
            // $total_laba_bersih_setelah_pajak_in_2022 += $total_in_2022[$m];
            // $total_laba_bersih_setelah_pajak_au_2022 += $total_au_2022[$m];
            // $total_laba_bersih_setelah_pajak_januari += $total_januari[$m];
            // $total_laba_bersih_setelah_pajak_februari += $total_februari[$m];
            // $total_laba_bersih_setelah_pajak_maret += $total_maret[$m];
            // $total_laba_bersih_setelah_pajak_april += $total_april[$m];
            // $total_laba_bersih_setelah_pajak_mei += $total_mei[$m];
            // $total_laba_bersih_setelah_pajak_juni += $total_juni[$m];
            // $total_laba_bersih_setelah_pajak_juli += $total_juli[$m];
            // $total_laba_bersih_setelah_pajak_agustus += $total_agustus[$m];
            // $total_laba_bersih_setelah_pajak_september += $total_september[$m];
            // $total_laba_bersih_setelah_pajak_oktober += $total_oktober[$m];
            // $total_laba_bersih_setelah_pajak_november += $total_november[$m];
            // $total_laba_bersih_setelah_pajak_desember += $total_desember[$m];


            // //data array 2020
            // $data_array_2020 = 
            // [
            //     '00' => $total_aset_2020,
            //     '000' => $total_liabilitas_2020,
            //     '0000' => $total_ekuitas_2020,
            //     '00000' => $total_laba_kotor_2020,
            //     '000000' => $total_laba_kotor_2020 + $total_laba_bersih_sebelum_pajak_2020,
            //     '0000000' => $total_laba_kotor_2020 + $total_laba_bersih_sebelum_pajak_2020 + $total_laba_bersih_setelah_pajak_2020,
            // ];

            // //data array 2021
            // $data_array_2021 = 
            // [
            //     '00' => $total_aset_2021,
            //     '000' => $total_liabilitas_2021,
            //     '0000' => $total_ekuitas_2021,
            //     '00000' => $total_laba_kotor_2021,
            //     '000000' => $total_laba_kotor_2021 + $total_laba_bersih_sebelum_pajak_2021,
            //     '0000000' => $total_laba_kotor_2021 + $total_laba_bersih_sebelum_pajak_2021 + $total_laba_bersih_setelah_pajak_2021,
            // ];


            // //data array inhouse 2022
            // $data_array_in_2022 = 
            // [
            //     '00' => $total_aset_in_2022,
            //     '000' => $total_liabilitas_in_2022,
            //     '0000' => $total_ekuitas_in_2022,
            //     '00000' => $total_laba_kotor_in_2022,
            //     '000000' => $total_laba_kotor_in_2022 + $total_laba_bersih_sebelum_pajak_in_2022,
            //     '0000000' => $total_laba_kotor_in_2022 + $total_laba_bersih_sebelum_pajak_in_2022 + $total_laba_bersih_setelah_pajak_in_2022,
            // ];

            // //data array audited 2022
            // $data_array_au_2022 = 
            // [
            //     '00' => $total_aset_au_2022,
            //     '000' => $total_liabilitas_au_2022,
            //     '0000' => $total_ekuitas_au_2022,
            //     '00000' => $total_laba_kotor_au_2022,
            //     '000000' => $total_laba_kotor_au_2022 + $total_laba_bersih_sebelum_pajak_au_2022,
            //     '0000000' => $total_laba_kotor_au_2022 + $total_laba_bersih_sebelum_pajak_au_2022 + $total_laba_bersih_setelah_pajak_au_2022,
            // ];

            // $data_summary_januari = 
            // [
            //     '00' => $total_aset_januari,
            //     '000' => $total_liabilitas_januari,
            //     '0000' => $total_ekuitas_januari,
            //     '00000' => $total_laba_kotor_januari,
            //     '000000' => $total_laba_kotor_januari + $total_laba_bersih_sebelum_pajak_januari,
            //     '0000000' => $total_laba_kotor_januari + $total_laba_bersih_sebelum_pajak_januari + $total_laba_bersih_setelah_pajak_januari,
            // ];

            // $data_summary_februari = 
            // [
            //     '00' => $total_aset_februari,
            //     '000' => $total_liabilitas_februari,
            //     '0000' => $total_ekuitas_februari,
            //     '00000' => $total_laba_kotor_februari,
            //     '000000' => $total_laba_kotor_februari + $total_laba_bersih_sebelum_pajak_februari,
            //     '0000000' => $total_laba_kotor_februari + $total_laba_bersih_sebelum_pajak_februari + $total_laba_bersih_setelah_pajak_februari,
            // ];

            // $data_summary_maret = 
            // [
            //     '00' => $total_aset_maret,
            //     '000' => $total_liabilitas_maret,
            //     '0000' => $total_ekuitas_maret,
            //     '00000' => $total_laba_kotor_maret,
            //     '000000' => $total_laba_kotor_maret + $total_laba_bersih_sebelum_pajak_maret,
            //     '0000000' => $total_laba_kotor_maret + $total_laba_bersih_sebelum_pajak_maret + $total_laba_bersih_setelah_pajak_maret,
            // ];

            // $data_summary_april = 
            // [
            //     '00' => $total_aset_april,
            //     '000' => $total_liabilitas_april,
            //     '0000' => $total_ekuitas_april,
            //     '00000' => $total_laba_kotor_april,
            //     '000000' => $total_laba_kotor_april + $total_laba_bersih_sebelum_pajak_april,
            //     '0000000' => $total_laba_kotor_april + $total_laba_bersih_sebelum_pajak_april + $total_laba_bersih_setelah_pajak_april,
            // ];

            // $data_summary_mei = 
            // [
            //     '00' => $total_aset_mei,
            //     '000' => $total_liabilitas_mei,
            //     '0000' => $total_ekuitas_mei,
            //     '00000' => $total_laba_kotor_mei,
            //     '000000' => $total_laba_kotor_mei + $total_laba_bersih_sebelum_pajak_mei,
            //     '0000000' => $total_laba_kotor_mei + $total_laba_bersih_sebelum_pajak_mei + $total_laba_bersih_setelah_pajak_mei,
            // ];
            
            // $data_summary_juni = 
            // [
            //     '00' => $total_aset_juni,
            //     '000' => $total_liabilitas_juni,
            //     '0000' => $total_ekuitas_juni,
            //     '00000' => $total_laba_kotor_juni,
            //     '000000' => $total_laba_kotor_juni + $total_laba_bersih_sebelum_pajak_juni,
            //     '0000000' => $total_laba_kotor_juni + $total_laba_bersih_sebelum_pajak_juni + $total_laba_bersih_setelah_pajak_juni,
            // ];

            // $data_summary_juli = 
            // [
            //     '00' => $total_aset_juli,
            //     '000' => $total_liabilitas_juli,
            //     '0000' => $total_ekuitas_juli,
            //     '00000' => $total_laba_kotor_juli,
            //     '000000' => $total_laba_kotor_juli + $total_laba_bersih_sebelum_pajak_juli,
            //     '0000000' => $total_laba_kotor_juli + $total_laba_bersih_sebelum_pajak_juli + $total_laba_bersih_setelah_pajak_juli,
            // ];

            // $data_summary_agustus = 
            // [
            //     '00' => $total_aset_agustus,
            //     '000' => $total_liabilitas_agustus,
            //     '0000' => $total_ekuitas_agustus,
            //     '00000' => $total_laba_kotor_agustus,
            //     '000000' => $total_laba_kotor_agustus + $total_laba_bersih_sebelum_pajak_agustus,
            //     '0000000' => $total_laba_kotor_agustus + $total_laba_bersih_sebelum_pajak_agustus + $total_laba_bersih_setelah_pajak_agustus,
            // ];

            // $data_summary_september = 
            // [
            //     '00' => $total_aset_september,
            //     '000' => $total_liabilitas_september,
            //     '0000' => $total_ekuitas_september,
            //     '00000' => $total_laba_kotor_september,
            //     '000000' => $total_laba_kotor_september + $total_laba_bersih_sebelum_pajak_september,
            //     '0000000' => $total_laba_kotor_september + $total_laba_bersih_sebelum_pajak_september + $total_laba_bersih_setelah_pajak_september,
            // ];

            // $data_summary_oktober = 
            // [
            //     '00' => $total_aset_oktober,
            //     '000' => $total_liabilitas_oktober,
            //     '0000' => $total_ekuitas_oktober,
            //     '00000' => $total_laba_kotor_oktober,
            //     '000000' => $total_laba_kotor_oktober + $total_laba_bersih_sebelum_pajak_oktober,
            //     '0000000' => $total_laba_kotor_oktober + $total_laba_bersih_sebelum_pajak_oktober + $total_laba_bersih_setelah_pajak_oktober,
            // ];

            // $data_summary_november = 
            // [
            //     '00' => $total_aset_november,
            //     '000' => $total_liabilitas_november,
            //     '0000' => $total_ekuitas_november,
            //     '00000' => $total_laba_kotor_november,
            //     '000000' => $total_laba_kotor_november + $total_laba_bersih_sebelum_pajak_november,
            //     '0000000' => $total_laba_kotor_november + $total_laba_bersih_sebelum_pajak_november + $total_laba_bersih_setelah_pajak_november,
            // ];

            // $data_summary_desember = 
            // [
            //     '00' => $total_aset_desember,
            //     '000' => $total_liabilitas_desember,
            //     '0000' => $total_ekuitas_desember,
            //     '00000' => $total_laba_kotor_desember,
            //     '000000' => $total_laba_kotor_desember + $total_laba_bersih_sebelum_pajak_desember,
            //     '0000000' => $total_laba_kotor_desember + $total_laba_bersih_sebelum_pajak_desember + $total_laba_bersih_setelah_pajak_desember,
            // ];

            // $summary = ProjectTask::$summary;

            // $result = $this->mergeSummaryData($summary,$data_array_2020,$data_array_2021, $data_array_in_2022, $data_array_au_2022, $data_summary_januari, $data_summary_februari, $data_summary_maret,
            // $data_summary_april, $data_summary_mei, $data_summary_juni, $data_summary_juli, $data_summary_agustus,
            // $data_summary_september, $data_summary_oktober, $data_summary_november, $data_summary_desember);

            // $data_index = ProjectTask::$financial_statement;
            // $data_materialitiy = SummaryMateriality::where('project_id', $project_id)->first();
            // $initialmaterialityom = $data_materialitiy->initialmaterialityom;

            // foreach($data_index as $a => $b)
            // {
            //     $keuanganringkas['kode']    = $a;
            //     $keuanganringkas['akun']    = $b;
            //     $keuanganringkas['data_2020']    =  isset($total_lk_2020[$a]) ? $total_lk_2020[$a] : 0;
            //     $keuanganringkas['data_2021']    =  isset($total_lk_2021[$a]) ? $total_lk_2021[$a] : 0;
            //     $keuanganringkas['data_in_2022']    =  isset($total_lk_in_2022[$a]) ? $total_lk_in_2022[$a] : 0;
            //     $keuanganringkas['data_au_2022']    =  isset($total_lk_au_2022[$a]) ? $total_lk_au_2022[$a] : 0;
            //     $keuanganringkas['januari'] = isset($total_januari[$a]) ? $total_januari[$a] : 0;
            //     $keuanganringkas['februari'] = isset($total_februari[$a]) ? $total_februari[$a] : 0;
            //     $keuanganringkas['maret'] = isset($total_maret[$a]) ? $total_maret[$a] : 0;
            //     $keuanganringkas['april'] = isset($total_april[$a]) ? $total_april[$a] : 0;
            //     $keuanganringkas['mei'] = isset($total_mei[$a]) ? $total_mei[$a] : 0;
            //     $keuanganringkas['juni'] = isset($total_juni[$a]) ? $total_juni[$a] : 0;
            //     $keuanganringkas['juli'] = isset($total_juli[$a]) ? $total_juli[$a] : 0;
            //     $keuanganringkas['agustus'] = isset($total_agustus[$a]) ? $total_agustus[$a] : 0;
            //     $keuanganringkas['september'] = isset($total_september[$a]) ? $total_september[$a] : 0;
            //     $keuanganringkas['oktober'] = isset($total_oktober[$a]) ? $total_oktober[$a] : 0;
            //     $keuanganringkas['november'] = isset($total_november[$a]) ? $total_november[$a] : 0;
            //     $keuanganringkas['desember'] = isset($total_desember[$a]) ? $total_desember[$a] : 0;
            //     //2021

            //     $keuanganringkas['kenaikan_2021'] = $keuanganringkas['data_2021'] - $keuanganringkas['data_2020'];
                
            //     if ($keuanganringkas['data_2020'] != 0) {
            //         $keuanganringkas['persen_kenaikan'] = ($keuanganringkas['kenaikan_2021'] / $keuanganringkas['data_2020']) * 100;
            //     } else {
            //         $keuanganringkas['persen_kenaikan'] = 0;
            //     }

            //     if ($keuanganringkas['kenaikan_2021'] > $initialmaterialityom) {
            //         $keuanganringkas['M/TM'] = 'M';
            //     } elseif ($keuanganringkas['kenaikan_2021'] < $initialmaterialityom) {
            //         $keuanganringkas['M/TM'] = 'TM';
            //     } else {
            //         $keuanganringkas['M/TM'] = '-';
            //     }

            //     //2022
                
            //     $keuanganringkas['kenaikan_2022'] = $keuanganringkas['data_au_2022'] - $keuanganringkas['data_2021'];
                
            //     if ($keuanganringkas['data_2021'] != 0) {
            //         $keuanganringkas['persen_kenaikan_2022'] = ($keuanganringkas['kenaikan_2022'] / $keuanganringkas['data_2021']) * 100;
            //     } else {
            //         $keuanganringkas['persen_kenaikan_2022'] = 0;
            //     }

            //     if ($keuanganringkas['kenaikan_2022'] > $initialmaterialityom) {
            //         $keuanganringkas['M/TM_2022'] = 'M';
            //     } elseif ($keuanganringkas['kenaikan_2022'] < $initialmaterialityom) {
            //         $keuanganringkas['M/TM_2022'] = 'TM';
            //     } else {
            //         $keuanganringkas['M/TM_2022'] = '-';
            //     }
                

            //     $index[] = $keuanganringkas;
            // }
            // $ca = FinancialStatement::where('project_id', $project_id)->where('cn', '=', 'CA');
            // $nca = FinancialStatement::where('project_id', $project_id)->where('cn', '=', 'NCA');
            // $cl = FinancialStatement::where('project_id', $project_id)->where('cn', '=', 'CL');
            // $ncl = FinancialStatement::where('project_id', $project_id)->where('cn', '=', 'NCL');

            // $total_ca_2020 = $ca->sum('prior_period2');
            // $total_nca_2020 = $nca->sum('prior_period2');
            // $total_cl_2020 = $cl->sum('prior_period2');
            // $total_ncl_2020 = $ncl->sum('prior_period2');

            // $total_ca_2021 = $ca->sum('prior_period');
            // $total_nca_2021 = $nca->sum('prior_period');
            // $total_cl_2021 = $cl->sum('prior_period');
            // $total_ncl_2021 = $ncl->sum('prior_period');

            // $total_ca_in_2022 = $ca->sum('inhouse');
            // $total_nca_in_2022 = $nca->sum('inhouse');
            // $total_cl_in_2022 = $cl->sum('inhouse');
            // $total_ncl_in_2022 = $ncl->sum('inhouse');

            // $total_ca_au_2022 = $ca->sum('audited');
            // $total_nca_au_2022 = $nca->sum('audited');
            // $total_cl_au_2022 = $cl->sum('audited');
            // $total_ncl_au_2022 = $ncl->sum('audited');

            // $total_ca_januari = $ca->sum('jan');
            // $total_ca_februari = $ca->sum('feb');
            // $total_ca_maret = $ca->sum('mar');
            // $total_ca_april = $ca->sum('apr');
            // $total_ca_mei = $ca->sum('may');
            // $total_ca_juni = $ca->sum('jun');
            // $total_ca_juli = $ca->sum('jul');
            // $total_ca_agustus = $ca->sum('aug');
            // $total_ca_september = $ca->sum('sep');
            // $total_ca_oktober = $ca->sum('oct');
            // $total_ca_november = $ca->sum('nov');
            // $total_ca_desember = $ca->sum('dec');

            // $total_nca_januari = $nca->sum('jan');
            // $total_nca_februari = $nca->sum('feb');
            // $total_nca_maret = $nca->sum('mar');
            // $total_nca_april = $nca->sum('apr');
            // $total_nca_mei = $nca->sum('may');
            // $total_nca_juni = $nca->sum('jun');
            // $total_nca_juli = $nca->sum('jul');
            // $total_nca_agustus = $nca->sum('aug');
            // $total_nca_september = $nca->sum('sep');
            // $total_nca_oktober = $nca->sum('oct');
            // $total_nca_november = $nca->sum('nov');
            // $total_nca_desember = $nca->sum('dec');

            // $total_cl_januari = $cl->sum('jan');
            // $total_cl_februari = $cl->sum('feb');
            // $total_cl_maret = $cl->sum('mar');
            // $total_cl_april = $cl->sum('apr');
            // $total_cl_mei = $cl->sum('may');
            // $total_cl_juni = $cl->sum('jun');
            // $total_cl_juli = $cl->sum('jul');
            // $total_cl_agustus = $cl->sum('aug');
            // $total_cl_september = $cl->sum('sep');
            // $total_cl_oktober = $cl->sum('oct');
            // $total_cl_november = $cl->sum('nov');
            // $total_cl_desember = $cl->sum('dec');

            // $total_ncl_januari = $nca->sum('jan');
            // $total_ncl_februari = $nca->sum('feb');
            // $total_ncl_maret = $nca->sum('mar');
            // $total_ncl_april = $nca->sum('apr');
            // $total_ncl_mei = $nca->sum('may');
            // $total_ncl_juni = $nca->sum('jun');
            // $total_ncl_juli = $nca->sum('jul');
            // $total_ncl_agustus = $nca->sum('aug');
            // $total_ncl_september = $nca->sum('sep');
            // $total_ncl_oktober = $nca->sum('oct');
            // $total_ncl_november = $nca->sum('nov');
            // $total_ncl_desember = $nca->sum('dec');

            // $summary_2020 = 
            // [
            //     'CA' => $total_ca_2020,
            //     'NCA' => $total_nca_2020,
            //     'CL' => $total_cl_2020,
            //     'NCL' => $total_ncl_2020,
            // ];
            

            // $summary_2021 = 
            // [
            //     'CA' => $total_ca_2021,
            //     'NCA' => $total_nca_2021,
            //     'CL' => $total_cl_2021,
            //     'NCL' => $total_ncl_2021,
            // ];

            // $summary_in_2022 = 
            // [
            //     'CA' => $total_ca_in_2022,
            //     'NCA' => $total_nca_in_2022,
            //     'CL' => $total_cl_in_2022,
            //     'NCL' => $total_ncl_in_2022,
            // ];

            // $summary_au_2022 = 
            // [
            //     'CA' => $total_ca_au_2022,
            //     'NCA' => $total_nca_au_2022,
            //     'CL' => $total_cl_au_2022,
            //     'NCL' => $total_ncl_au_2022,
            // ];

            // $summary_jan = 
            // [
            //     'CA' => $total_ca_januari,
            //     'NCA' => $total_nca_januari,
            //     'CL' => $total_cl_januari,
            //     'NCL' => $total_ncl_januari,
            // ];

            // $summary_feb = 
            // [
            //     'CA' => $total_ca_februari,
            //     'NCA' => $total_nca_februari,
            //     'CL' => $total_cl_februari,
            //     'NCL' => $total_ncl_februari,
            // ];

            // $summary_mar = 
            // [
            //     'CA' => $total_ca_maret,
            //     'NCA' => $total_nca_maret,
            //     'CL' => $total_cl_maret,
            //     'NCL' => $total_ncl_maret,
            // ];

            // $summary_apr = 
            // [
            //     'CA' => $total_ca_april,
            //     'NCA' => $total_nca_april,
            //     'CL' => $total_cl_april,
            //     'NCL' => $total_ncl_april,
            // ];

            // $summary_may = 
            // [
            //     'CA' => $total_ca_mei,
            //     'NCA' => $total_nca_mei,
            //     'CL' => $total_cl_mei,
            //     'NCL' => $total_ncl_mei,
            // ];

            // $summary_jun = 
            // [
            //     'CA' => $total_ca_juni,
            //     'NCA' => $total_nca_juni,
            //     'CL' => $total_cl_juni,
            //     'NCL' => $total_ncl_juni,
            // ];

            // $summary_jul = 
            // [
            //     'CA' => $total_ca_juli,
            //     'NCA' => $total_nca_juli,
            //     'CL' => $total_cl_juli,
            //     'NCL' => $total_ncl_juli,
            // ];

            // $summary_aug = 
            // [
            //     'CA' => $total_ca_agustus,
            //     'NCA' => $total_nca_agustus,
            //     'CL' => $total_cl_agustus,
            //     'NCL' => $total_ncl_agustus,
            // ];

            // $summary_sep = 
            // [
            //     'CA' => $total_ca_september,
            //     'NCA' => $total_nca_september,
            //     'CL' => $total_cl_september,
            //     'NCL' => $total_ncl_september,
            // ];

            // $summary_oct = 
            // [
            //     'CA' => $total_ca_oktober,
            //     'NCA' => $total_nca_oktober,
            //     'CL' => $total_cl_oktober,
            //     'NCL' => $total_ncl_oktober,
            // ];

            // $summary_nov = 
            // [
            //     'CA' => $total_ca_november,
            //     'NCA' => $total_nca_november,
            //     'CL' => $total_cl_november,
            //     'NCL' => $total_ncl_november,
            // ];

            // $summary_dec = 
            // [
            //     'CA' => $total_ca_desember,
            //     'NCA' => $total_nca_desember,
            //     'CL' => $total_cl_desember,
            //     'NCL' => $total_ncl_desember,
            // ];

            // $data_cn = ProjectTask::$cn;

            // foreach($data_cn as $a => $b)
            // {
            //     $summarycn['kode']    = $a;
            //     $summarycn['akun']    = $b;
            //     $summarycn['data_2020']    =  isset($summary_2020[$a]) ? $summary_2020[$a] : 0;
            //     $summarycn['data_2021']    =  isset($summary_2021[$a]) ? $summary_2021[$a] : 0;
            //     $summarycn['data_in_2022']    =  isset($summary_in_2022[$a]) ? $summary_in_2022[$a] : 0;
            //     $summarycn['data_au_2022']    =  isset($summary_au_2022[$a]) ? $summary_au_2022[$a] : 0;
            //     $summarycn['januari']    =  isset($summary_jan[$a]) ? $summary_jan[$a] : 0;
            //     $summarycn['februari']    =  isset($summary_feb[$a]) ? $summary_feb[$a] : 0;
            //     $summarycn['maret']    =  isset($summary_mar[$a]) ? $summary_mar[$a] : 0;
            //     $summarycn['april']    =  isset($summary_apr[$a]) ? $summary_apr[$a] : 0;
            //     $summarycn['mei']    =  isset($summary_may[$a]) ? $summary_may[$a] : 0;
            //     $summarycn['juni']    =  isset($summary_jun[$a]) ? $summary_jun[$a] : 0;
            //     $summarycn['juli']    =  isset($summary_jul[$a]) ? $summary_jul[$a] : 0;
            //     $summarycn['agustus']    =  isset($summary_aug[$a]) ? $summary_aug[$a] : 0;
            //     $summarycn['september']    =  isset($summary_sep[$a]) ? $summary_sep[$a] : 0;
            //     $summarycn['oktober']    =  isset($summary_oct[$a]) ? $summary_oct[$a] : 0;
            //     $summarycn['november']    =  isset($summary_nov[$a]) ? $summary_nov[$a] : 0;
            //     $summarycn['desember']    =  isset($summary_dec[$a]) ? $summary_dec[$a] : 0;

            //     $cn[] = $summarycn;

            // }


            // $savematerialitas = Materialitas::get();

            // foreach ($savematerialitas as $key => $materialitasss) 
            // {
            //     ValueMaterialitas::updateOrCreate(
            //         ['project_id' => $project_id, 'materialitas_id' => $materialitasss->id],
            //         [
            //             'data2020' => isset($data_array_2020[$key+1]) ? $data_array_2020[$key+1] : null,
            //             'data2021' => isset($data_array_2021[$key+1]) ? json_encode($data_array_2021[$key+1]) : null,
            //             'inhouse2022' => isset($data_array_in_2022[$key+1]) ? json_encode($data_array_in_2022[$key+1]) : null,
            //             'audited2022' => isset($data_array_au_2022[$key+1]) ? json_encode($data_array_au_2022[$key+1]) : null,
            //         ]
            //     );
            // }

            $request->session()->put('tableData', $tableData);
             
            return view('project_task.keuanganringkas', compact(
                'task','project','materialitas','financial_statement','data_keuangan','notesanalysis','tableData','respons',
            ));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function notesAnalysis(Request $request, $project_id, $task_id)
    {

        // Simpan data ke dalam database menggunakan model
        $data = new NotesAnalysis();
        $data->project_id = $project_id;
        $data->task_id = $task_id;
        $data->notes = $request->notes;

        $data->save();

        ActivityLog::create(
            [
                'user_id' => \Auth::user()->id,
                'project_id' => $project_id,
                'task_id' => $task_id,
                'log_type' => 'Create Notes Analysis Perbandingan Data Antar Periode',
                'remark' => json_encode(['title' => 'Create Notes Analysis Perbandingan Data Antar Periode']),
            ]
        );

        return redirect()->back()->with('success', __('Notes Analysis Perbandingan Data Antar Periode added successfully.'));

    }

    public function showproseduranalisis($project_id, $task_id)
    {

        if(\Auth::user()->can('view project task'))
        {
            $allow_progress = Project::find($project_id)->task_progress;
            $task           = ProjectTask::find($task_id);
            $subtask        = TaskChecklist::where('task_id', $task_id)->where('parent_id', 0)->get();

            return view('project_task.proseduranalitis', compact('task','subtask', 'allow_progress'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function mergeSummaryData($summary, $data_array_2020,$data_array_2021, $data_array_in_2022, $data_array_au_2022, $data_summary_januari, $data_summary_februari, $data_summary_maret,
    $data_summary_april, $data_summary_mei, $data_summary_juni, $data_summary_juli, $data_summary_agustus,
    $data_summary_september, $data_summary_oktober, $data_summary_november, $data_summary_desember) 
    {
        $result = [];
        foreach($summary as $key => $value) {
            $increase_2021 = 0;
            $increase_2022 = 0;
            if (isset($data_array_2020[$key]) && isset($data_array_2021[$key]) && $data_array_2020[$key] != 0) {
                $increase_2021 = ($data_array_2021[$key] - $data_array_2020[$key]) / $data_array_2020[$key] * 100;
            }
            if (isset($data_array_2021[$key]) && isset($data_array_au_2022[$key]) && $data_array_2021[$key] != 0) {
                $increase_2022 = ($data_array_au_2022[$key] - $data_array_2021[$key]) / $data_array_2021[$key] * 100;
            }

            $result[] = [
                'kode' => $key,
                'akun' => $value,
                '2020' => isset($data_array_2020[$key]) ? $data_array_2020[$key] : 0,
                '2021' => isset($data_array_2021[$key]) ? $data_array_2021[$key] : 0,
                'inhouse2022' => isset($data_array_in_2022[$key]) ? $data_array_in_2022[$key] : 0,
                'audited2022' => isset($data_array_au_2022[$key]) ? $data_array_au_2022[$key] : 0,
                'januari' => isset($data_summary_januari[$key]) ? $data_summary_januari[$key] : 0,
                'februari' => isset($data_summary_februari[$key]) ? $data_summary_februari[$key] : 0,
                'maret' => isset($data_summary_maret[$key]) ? $data_summary_maret[$key] : 0,
                'april' => isset($data_summary_april[$key]) ? $data_summary_april[$key] : 0,
                'mei' => isset($data_summary_mei[$key]) ? $data_summary_mei[$key] : 0,
                'juni' => isset($data_summary_juni[$key]) ? $data_summary_juni[$key] : 0,
                'juli' => isset($data_summary_juli[$key]) ? $data_summary_juli[$key] : 0,
                'agustus' => isset($data_summary_agustus[$key]) ? $data_summary_agustus[$key] : 0,
                'september' => isset($data_summary_september[$key]) ? $data_summary_september[$key] : 0,
                'oktober' => isset($data_summary_oktober[$key]) ? $data_summary_oktober[$key] : 0,
                'november' => isset($data_summary_november[$key]) ? $data_summary_november[$key] : 0,
                'desember' => isset($data_summary_desember[$key]) ? $data_summary_desember[$key] : 0,
                'increase_2021' => $increase_2021,
                'increase_2022' => $increase_2022
            ];
        }
        
        return $result;
    }

    public function getAuditMemorandum($project_id, $task_id)
    {
        if(\Auth::user()->can('view project task'))
        {
            $id                                 = Crypt::decrypt($task_id);
            $project                            = Project::find($project_id);
            $task                               = ProjectTask::find($id);
            $auditmemorandum                    = AuditMemorandum::where('project_id', $project_id)->first();

            return view('project_task.auditmemorandum', compact('auditmemorandum','project','task'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function storeAuditMemorandum(Request $request, $project_id)
    {
        if(\Auth::user()->can('create project task'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'content' => 'required',
                            ]
            );
    
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
    
                return redirect()->back()->with('error', $messages->first());
            }

            AuditMemorandum::updateOrCreate(
                ['project_id' => $project_id],
                [
                    'content' => $request->content,
                ]
            );

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $project_id,
                    'task_id' => 0,
                    'log_type' => 'Create Audit Memorandum',
                    'remark' => json_encode(['title' => 'Audit Memorandum']),
                ]
            );
            
            return redirect()->back()->with('success', __('Audit Memorandum added successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function getRasioKeuangan(Request $request, $project_id, $task_id)
    {
        if(\Auth::user()->can('view project task'))
        {
            $id                                 = Crypt::decrypt($task_id);
            $project                            = Project::find($project_id);
            $task                               = ProjectTask::find($id);

            $rasio_likuiditas = [];
            $rasio_profitabilitas = [];
            $rasio_utang = [];
            
            $financial_statement = FinancialStatement::where('project_id', $project_id)->whereIn('cn', ['CA', 'NCA', 'CL', 'NCL'])->get(['cn', 'prior_period2', 'prior_period', 'inhouse', 'audited',
            'jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec']);

            //data persediaan
            $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', 'LK.9')->get();
            $data_persediaan_2020 = $data_lk->pluck('prior_period2')->toArray();
            $total_persediaan_2020 = array_sum($data_persediaan_2020);
            $data_persediaan_2021 = $data_lk->pluck('prior_period')->toArray();
            $total_persediaan_2021 = array_sum($data_persediaan_2021);
            $data_persediaan_in_2022 = $data_lk->pluck('inhouse')->toArray();
            $total_persediaan_in_2022 = array_sum($data_persediaan_in_2022);
            $data_persediaan_au_2022 = $data_lk->pluck('audited')->toArray();
            $total_persediaan_au_2022 = array_sum($data_persediaan_au_2022);

            //data kas dan setara kas
            $data_lk_kas = FinancialStatement::where('project_id', $project_id)->where('lk', '=', 'LK.1')->get();
            $data_kas_setara_kas_2020 = $data_lk_kas->pluck('prior_period2')->toArray();
            $total_kas_setara_kas_2020 = array_sum($data_kas_setara_kas_2020);
            $data_kas_setara_kas_2021 = $data_lk_kas->pluck('prior_period')->toArray();
            $total_kas_setara_kas_2021 = array_sum($data_kas_setara_kas_2021);
            $data_kas_setara_kas_in_2022 = $data_lk_kas->pluck('inhouse')->toArray();
            $total_kas_setara_kas_in_2022 = array_sum($data_kas_setara_kas_in_2022);
            $data_kas_setara_kas_au_2022 = $data_lk_kas->pluck('audited')->toArray();
            $total_kas_setara_kas_au_2022 = array_sum($data_kas_setara_kas_au_2022);

            //data pendapatan
            $data_m_pendapatan = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.4')->get();
            $data_pendapatan_2020 = $data_m_pendapatan->pluck('prior_period2')->toArray();
            $total_pendapatan_2020 = array_sum($data_pendapatan_2020);
            $data_pendapatan_2021 = $data_m_pendapatan->pluck('prior_period')->toArray();
            $total_pendapatan_2021 = array_sum($data_pendapatan_2021);
            $data_pendapatan_in_2022 = $data_m_pendapatan->pluck('inhouse')->toArray();
            $total_pendapatan_in_2022 = array_sum($data_pendapatan_in_2022);
            $data_pendapatan_au_2022 = $data_m_pendapatan->pluck('audited')->toArray();
            $total_pendapatan_au_2022 = array_sum($data_pendapatan_au_2022);

            //data piutang usaha pihak berelasi
            $data_lk_berelasi = FinancialStatement::where('project_id', $project_id)->where('lk', '=', 'LK.3')->get();
            $data_berelasi_2020 = $data_lk_berelasi->pluck('prior_period2')->toArray();
            $total_berelasi_2020 = array_sum($data_berelasi_2020);
            $data_berelasi_2021 = $data_lk_berelasi->pluck('prior_period')->toArray();
            $total_berelasi_2021 = array_sum($data_berelasi_2021);
            $data_berelasi_in_2022 = $data_lk_berelasi->pluck('inhouse')->toArray();
            $total_berelasi_in_2022 = array_sum($data_berelasi_in_2022);
            $data_berelasi_au_2022 = $data_lk_berelasi->pluck('audited')->toArray();
            $total_berelasi_au_2022 = array_sum($data_berelasi_au_2022);

            //data piutang usaha pihak ketiga
            $data_lk_ketiga = FinancialStatement::where('project_id', $project_id)->where('lk', '=', 'LK.4')->get();
            $data_ketiga_2020 = $data_lk_ketiga->pluck('prior_period2')->toArray();
            $total_ketiga_2020 = array_sum($data_ketiga_2020);
            $data_ketiga_2021 = $data_lk_ketiga->pluck('prior_period')->toArray();
            $total_ketiga_2021 = array_sum($data_ketiga_2021);
            $data_ketiga_in_2022 = $data_lk_ketiga->pluck('inhouse')->toArray();
            $total_ketiga_in_2022 = array_sum($data_ketiga_in_2022);
            $data_ketiga_au_2022 = $data_lk_ketiga->pluck('audited')->toArray();
            $total_ketiga_au_2022 = array_sum($data_ketiga_au_2022);
            
            $nca = $financial_statement->where('cn', 'NCA');
            $ca = $financial_statement->where('cn', 'CA');
            $cl = $financial_statement->where('cn', 'CL');
            $ncl = $financial_statement->where('cn', 'NCL');

            $total_ca_2020 = $ca->sum('prior_period2');
            $total_cl_2020 = $cl->sum('prior_period2');

            $total_ca_2021 = $ca->sum('prior_period');
            $total_cl_2021 = $cl->sum('prior_period');

            $total_ca_in_2022 = $ca->sum('inhouse');
            $total_cl_in_2022 = $cl->sum('inhouse');

            $total_ca_au_2022 = $ca->sum('audited');
            $total_cl_au_2022 = $cl->sum('audited');


            //Mencari nilai total liabilitas
            $total_liabilitas                 = ValueMaterialitas::where('project_id', $project_id)->where('materialitas_id', 2)->first();
            $total_liabilitas_2020            = $total_liabilitas->prior_period2;
            $total_liabilitas_2021            = $total_liabilitas->prior_period;
            $total_liabilitas_in_2022         = $total_liabilitas->inhouse;
            $total_liabilitas_au_2022         = $total_liabilitas->audited;

            //Mencari nilai total aset
            $total_aset                 = ValueMaterialitas::where('project_id', $project_id)->where('materialitas_id', 1)->first();
            $total_aset_2020            = $total_aset->prior_period2;
            $total_aset_2021            = $total_aset->prior_period;
            $total_aset_in_2022         = $total_aset->inhouse;
            $total_aset_au_2022         = $total_aset->audited;

            //Mencari nilai total ekuitas
            $total_ekuitas                 = ValueMaterialitas::where('project_id', $project_id)->where('materialitas_id', 3)->first();
            $total_ekuitas_2020            = $total_ekuitas->prior_period2;
            $total_ekuitas_2021            = $total_ekuitas->prior_period;
            $total_ekuitas_in_2022         = $total_ekuitas->inhouse;
            $total_ekuitas_au_2022         = $total_ekuitas->audited;

            //Mencari nilai total laba kotor
            $laba_bruto                 = ValueMaterialitas::where('project_id', $project_id)->where('materialitas_id', 11)->first();
            $total_laba_kotor_2020      = $laba_bruto->prior_period2;
            $total_laba_kotor_2021      = $laba_bruto->prior_period;
            $total_laba_kotor_in_2022   = $laba_bruto->inhouse;
            $total_laba_kotor_au_2022   = $laba_bruto->audited;

            //Mencari nilai total laba bersih sebelum pajak
            $total_laba_bersih_sebelum_pajak           = ValueMaterialitas::where('project_id', $project_id)->where('materialitas_id', 13)->first();
            $total_laba_bersih_sebelum_pajak_2020      = $total_laba_bersih_sebelum_pajak->prior_period2;
            $total_laba_bersih_sebelum_pajak_2021      = $total_laba_bersih_sebelum_pajak->prior_period;
            $total_laba_bersih_sebelum_pajak_in_2022   = $total_laba_bersih_sebelum_pajak->inhouse;
            $total_laba_bersih_sebelum_pajak_au_2022   = $total_laba_bersih_sebelum_pajak->audited;

            //Mencari nilai total laba bersih setelah pajak
            $total_laba_bersih_setelah_pajak           = ValueMaterialitas::where('project_id', $project_id)->where('materialitas_id', 14)->first();
            $total_laba_bersih_setelah_pajak_2020      = $total_laba_bersih_setelah_pajak->prior_period2;
            $total_laba_bersih_setelah_pajak_2021      = $total_laba_bersih_setelah_pajak->prior_period;
            $total_laba_bersih_setelah_pajak_in_2022   = $total_laba_bersih_setelah_pajak->inhouse;
            $total_laba_bersih_setelah_pajak_au_2022   = $total_laba_bersih_setelah_pajak->audited;

            //perhitungan current ratio
            $current_ratio_2020 = ($total_cl_2020 != 0) ? $total_ca_2020 / ($total_cl_2020 * -1) : 0;
            $current_ratio_2021 = ($total_cl_2021 != 0) ? $total_ca_2021 / ($total_cl_2021 * -1) : 0;
            $current_ratio_in_2022 = ($total_cl_in_2022 != 0) ? $total_ca_in_2022 / ($total_cl_in_2022 * -1) : 0;
            $current_ratio_au_2022 = ($total_cl_au_2022 != 0) ? $total_ca_au_2022 / ($total_cl_au_2022 * -1) : 0;

            //perhitungan quick ratio
            $quick_ratio_2020 = ($total_cl_2020 != 0) ? ($total_ca_2020 - $total_persediaan_2020) / ($total_cl_2020 * -1) : 0;
            $quick_ratio_2021 = ($total_cl_2021 != 0) ? ($total_ca_2021 - $total_persediaan_2021) / ($total_cl_2021 * -1) : 0;
            $quick_ratio_in_2022 = ($total_cl_in_2022 != 0) ? ($total_ca_in_2022 - $total_persediaan_in_2022) / ($total_cl_in_2022 * -1) : 0;
            $quick_ratio_au_2022 = ($total_cl_au_2022 != 0) ? ($total_ca_au_2022 - $total_persediaan_au_2022) / ($total_cl_au_2022 * -1) : 0;

            //perhitungan cash ratio
            $cash_ratio_2020 = ($total_cl_2020 != 0) ? $total_kas_setara_kas_2020 / ($total_cl_2020 * -1) : 0;
            $cash_ratio_2021 = ($total_cl_2021 != 0) ? $total_kas_setara_kas_2021 / ($total_cl_2021 * -1) : 0;
            $cash_ratio_in_2022 = ($total_cl_in_2022 != 0) ? $total_kas_setara_kas_in_2022 / ($total_cl_in_2022 * -1) : 0;
            $cash_ratio_au_2022 = ($total_cl_au_2022 != 0) ? $total_kas_setara_kas_au_2022 / ($total_cl_au_2022 * -1) : 0;

            //perhitungan debt to asset ratio
            $detara_2020 = ($total_aset_2020 != 0) ? ($total_liabilitas_2020 * -1) / $total_aset_2020 : 0;
            $detara_2021 = ($total_aset_2021 != 0) ? ($total_liabilitas_2021 * -1) / $total_aset_2021 : 0;
            $detara_in_2022 = ($total_aset_in_2022 != 0) ? ($total_liabilitas_in_2022 * -1) / $total_aset_in_2022 : 0;
            $detara_au_2022 = ($total_aset_au_2022 != 0) ? ($total_liabilitas_au_2022 * -1) / $total_aset_au_2022 : 0;

            //perhitungan debt to equity ratio
            $detera_2020 = ($total_ekuitas_2020 != 0) ? $total_liabilitas_2020 / $total_ekuitas_2020 : 0;
            $detera_2021 = ($total_ekuitas_2021 != 0) ? $total_liabilitas_2021 / $total_ekuitas_2021 : 0;
            $detera_in_2022 = ($total_ekuitas_in_2022 != 0) ? $total_liabilitas_in_2022 / $total_ekuitas_in_2022 : 0;
            $detera_au_2022 = ($total_ekuitas_au_2022 != 0) ? $total_liabilitas_au_2022 / $total_ekuitas_au_2022 : 0;

            // //perhitungan total asset turnover ratio
            // $tatura_2020 = ($total_aset_2020 != 0) ? ($total_pendapatan_2020 * -1) / $total_aset_2020 : 0;
            // $tatura_2021 = ($total_aset_2021 != 0) ? ($total_pendapatan_2021  * -1) / $total_aset_2021 : 0;
            // $tatura_in_2022 = ($total_aset_in_2022 != 0) ? ($total_pendapatan_in_2022  * -1) / $total_aset_in_2022 : 0;
            // $tatura_au_2022 = ($total_aset_au_2022 != 0) ? ($total_pendapatan_au_2022  * -1) / $total_aset_au_2022 : 0;

            // //perhitungan receivable turnover ratio
            // $piutang_usaha_2020 = $total_berelasi_2020 + $total_ketiga_2020;
            // $retura_2020 =   ($piutang_usaha_2020 != 0) ? ($total_pendapatan_2020 * -1) / $piutang_usaha_2020 : 0;
            // $piutang_usaha_2021 = ($total_berelasi_2020 + $total_ketiga_2020 + $total_berelasi_2021 + $total_ketiga_2021) / 2;
            // $retura_2021 =   ($piutang_usaha_2021 != 0) ? ($total_pendapatan_2021 * -1) / $piutang_usaha_2021 : 0;
            // $piutang_usaha_in_2022 = ($total_berelasi_2021 + $total_ketiga_2021 + $total_berelasi_in_2022 + $total_ketiga_in_2022) / 2;
            // $retura_in_2022 =   ($piutang_usaha_in_2022 != 0) ? ($total_pendapatan_in_2022 * -1) / $piutang_usaha_in_2022 : 0;
            // $piutang_usaha_au_2022 = ($total_berelasi_in_2022 + $total_ketiga_in_2022 + $total_berelasi_au_2022 + $total_ketiga_au_2022) / 2;
            // $retura_au_2022 =   ($piutang_usaha_au_2022 != 0) ? ($total_pendapatan_au_2022 * -1) / $piutang_usaha_au_2022 : 0;

            // //perhitungan receivable turnover ratio (hari)
            // $retura_hari_2020 =   ($retura_2020 != 0) ? 365 / $retura_2020 : 0;
            // $retura_hari_2021 =   ($retura_2021 != 0) ? 365 / $retura_2021 : 0;
            // $retura_hari_in_2022 =   ($retura_in_2022 != 0) ? 365 / $retura_in_2022 : 0;
            // $retura_hari_au_2022 =   ($retura_au_2022 != 0) ? 365 / $retura_au_2022 : 0;

            // //perhitungan inventory turnover ratio
            // $intura_2020 = ($total_persediaan_2020 != 0) ? ($total_pendapatan_2020 * -1) / $total_persediaan_2020 : 0;

            // $persediaan_2021 = ($total_persediaan_2020 + $total_persediaan_2021) / 2;
            // $intura_2021 = ($persediaan_2021 != 0) ? ($total_pendapatan_2021 * -1) / $persediaan_2021 : 0;

            // $persediaan_in_2022 = ($total_persediaan_2021 + $total_persediaan_in_2022) / 2;
            // $intura_in_2022 = ($persediaan_in_2022 != 0) ? ($total_pendapatan_in_2022 * -1) / $persediaan_in_2022 : 0;

            // $persediaan_au_2022 = ($total_persediaan_in_2022 + $total_persediaan_au_2022) / 2;
            // $intura_au_2022 = ($persediaan_au_2022 != 0) ? ($total_pendapatan_au_2022 * -1) / $persediaan_au_2022 : 0;

            //perhitungan gross profit margin
            $gpm_2020 = ($total_pendapatan_2020 != 0) ? $total_laba_kotor_2020 / $total_pendapatan_2020 : 0;
            $gpm_2021 = ($total_pendapatan_2021 != 0) ? $total_laba_kotor_2021 / $total_pendapatan_2021 : 0;
            $gpm_in_2022 = ($total_pendapatan_in_2022 != 0) ? $total_laba_kotor_in_2022 / $total_pendapatan_in_2022 : 0;
            $gpm_au_2022 = ($total_pendapatan_au_2022 != 0) ? $total_laba_kotor_au_2022 / $total_pendapatan_au_2022 : 0;

            //perhitungan operating profit margin
            $total_laba_bersih_a = $total_laba_kotor_2020 + $total_laba_bersih_sebelum_pajak_2020;
            $total_laba_bersih_b = $total_laba_kotor_2021 + $total_laba_bersih_sebelum_pajak_2021;
            $total_laba_bersih_c = $total_laba_kotor_in_2022 + $total_laba_bersih_sebelum_pajak_in_2022;
            $total_laba_bersih_d = $total_laba_kotor_au_2022 + $total_laba_bersih_sebelum_pajak_au_2022;
    
            $opm_2020 = ($total_pendapatan_2020 != 0) ? $total_laba_bersih_a / $total_pendapatan_2020 : 0;
            $opm_2021 = ($total_pendapatan_2021 != 0) ? $total_laba_bersih_b / $total_pendapatan_2021 : 0;
            $opm_in_2022 = ($total_pendapatan_in_2022 != 0) ? $total_laba_bersih_c / $total_pendapatan_in_2022 : 0;
            $opm_au_2022 = ($total_pendapatan_au_2022 != 0) ? $total_laba_bersih_d / $total_pendapatan_au_2022 : 0;

            //perhitungan net profit margin
            $total_laba_bersih_a1 = $total_laba_kotor_2020 + $total_laba_bersih_sebelum_pajak_2020 + $total_laba_bersih_setelah_pajak_2020;
            $total_laba_bersih_b1 = $total_laba_kotor_2021 + $total_laba_bersih_sebelum_pajak_2021 + $total_laba_bersih_setelah_pajak_2021;
            $total_laba_bersih_c1 = $total_laba_kotor_in_2022 + $total_laba_bersih_sebelum_pajak_in_2022 + $total_laba_bersih_setelah_pajak_in_2022;
            $total_laba_bersih_d1 = $total_laba_kotor_au_2022 + $total_laba_bersih_sebelum_pajak_au_2022 + $total_laba_bersih_setelah_pajak_au_2022;

            $npm_2020 = ($total_pendapatan_2020 != 0) ? $total_laba_bersih_a1 / $total_pendapatan_2020 : 0;
            $npm_2021 = ($total_pendapatan_2021 != 0) ? $total_laba_bersih_b1 / $total_pendapatan_2021 : 0;
            $npm_in_2022 = ($total_pendapatan_in_2022 != 0) ? $total_laba_bersih_c1 / $total_pendapatan_in_2022 : 0;
            $npm_au_2022 = ($total_pendapatan_au_2022 != 0) ? $total_laba_bersih_d1 / $total_pendapatan_au_2022 : 0;

            //perhitungan return on asset
            $roa_2020 = ($total_aset_2020 != 0) ? ($total_laba_bersih_a1 * -1) / $total_aset_2020 : 0;
            $roa_2021 = ($total_aset_2021 != 0) ? ($total_laba_bersih_b1 * -1) / $total_aset_2021 : 0;
            $roa_in_2022 = ($total_aset_in_2022 != 0) ? ($total_laba_bersih_c1 * -1) / $total_aset_in_2022 : 0;
            $roa_au_2022 = ($total_aset_au_2022 != 0) ? ($total_laba_bersih_d1 * -1) / $total_aset_au_2022 : 0;

            //perhitungan return on equity
            $roe_2020 = ($total_ekuitas_2020 != 0) ? $total_laba_bersih_a1 / $total_ekuitas_2020 : 0;
            $roe_2021 = ($total_ekuitas_2021 != 0) ? $total_laba_bersih_b1 / $total_ekuitas_2021 : 0;
            $roe_in_2022 = ($total_ekuitas_in_2022 != 0) ? $total_laba_bersih_c1 / $total_ekuitas_in_2022 : 0;
            $roe_au_2022 = ($total_ekuitas_au_2022 != 0) ? $total_laba_bersih_d1 / $total_ekuitas_au_2022 : 0;

            //perhitungan cash turnover ratio (perlu ditanyakan)
            $rata_rata_kas_setara_kas = ($total_kas_setara_kas_in_2022 + $total_kas_setara_kas_2021) / 2 ;

            $catura_2020 = ($total_pendapatan_2020 != 0) ? $rata_rata_kas_setara_kas / $total_pendapatan_2020 : 0;
            $catura_2021 = ($total_pendapatan_2021 != 0) ? $rata_rata_kas_setara_kas / $total_pendapatan_2021 : 0;
            $catura_in_2022 = ($total_pendapatan_in_2022 != 0) ? $rata_rata_kas_setara_kas / $total_pendapatan_in_2022 : 0;
            $catura_au_2022 = ($total_pendapatan_au_2022 != 0) ? $rata_rata_kas_setara_kas / $total_pendapatan_au_2022 : 0;



            $summary_2020_rasio_likuiditas = 
            [
                'CURA' => number_format($current_ratio_2020,2),
                'QURA' => number_format($quick_ratio_2020,2),
                'CARA' => number_format($cash_ratio_2020,2),
                'CATURA' => number_format($catura_2020,2),
            ];

            $summary_2020_rasio_profitabilitas = 
            [
                // 'DETARA' => number_format($detara_2020,2),
                // 'DETERA' => number_format($detera_2020,2),
                // 'TATURA' => number_format($tatura_2020,2),
                // 'RETURA' => number_format($retura_2020,2),
                // 'RETURAH' => number_format($retura_hari_2020),
                // 'INTURA' => number_format($intura_2020,2),
                'GPM' => number_format($gpm_2020,2),
                'OPM' => number_format($opm_2020,2),
                'NPM' => number_format($npm_2020,2),
                'ROA' => number_format($roa_2020,2),
                'ROE' => number_format($roe_2020,2),
            ];

            $summary_2020_rasio_utang = 
            [
                'DETARA' => number_format($detara_2020,2),
                'DETERA' => number_format($detera_2020,2),
            ];
            
            $summary_2021_rasio_likuiditas = 
            [
                'CURA' => number_format($current_ratio_2021,2),
                'QURA' => number_format($quick_ratio_2021,2),
                'CARA' => number_format($cash_ratio_2021,2),
                'CATURA' => number_format($catura_2021,2),
            ];

            $summary_2021_rasio_profitabilitas = 
            [
                // 'DETARA' => number_format($detara_2021,2),
                // 'DETERA' => number_format($detera_2021,2),
                // 'TATURA' => number_format($tatura_2021,2),
                // 'RETURA' => number_format($retura_2021,2),
                // 'RETURAH' => number_format($retura_hari_2021),
                // 'INTURA' => number_format($intura_2021,2),
                'GPM' => number_format($gpm_2021,2),
                'OPM' => number_format($opm_2021,2),
                'NPM' => number_format($npm_2021,2),
                'ROA' => number_format($roa_2021,2),
                'ROE' => number_format($roe_2021,2),
            ];

            $summary_2021_rasio_utang = 
            [
                'DETARA' => number_format($detara_2021,2),
                'DETERA' => number_format($detera_2021,2),
            ];

            $summary_in_2022_rasio_likuiditas = 
            [
                'CURA' => number_format($current_ratio_in_2022,2),
                'QURA' => number_format($quick_ratio_in_2022,2),
                'CARA' => number_format($cash_ratio_in_2022,2),
                'CATURA' => number_format($catura_in_2022,2),
            ];

            $summary_in_2022_rasio_profitabilitas = 
            [
                // 'DETARA' => number_format($detara_in_2022,2),
                // 'DETERA' => number_format($detera_in_2022,2),
                // 'TATURA' => number_format($tatura_in_2022,2),
                // 'RETURA' => number_format($retura_in_2022),
                // 'RETURAH' => number_format($retura_hari_in_2022),
                // 'INTURA' => number_format($intura_in_2022,2),
                'GPM' => number_format($gpm_in_2022,2),
                'OPM' => number_format($opm_in_2022,2),
                'NPM' => number_format($npm_in_2022,2),
                'ROA' => number_format($roa_in_2022,2),
                'ROE' => number_format($roe_in_2022,2),
            ];

            $summary_in_2022_rasio_utang = 
            [
                'DETARA' => number_format($detara_in_2022,2),
                'DETERA' => number_format($detera_in_2022,2),
            ];

            $summary_au_2022_rasio_likuiditas = 
            [
                'CURA' => number_format($current_ratio_au_2022,2),
                'QURA' => number_format($quick_ratio_au_2022,2),
                'CARA' => number_format($cash_ratio_au_2022,2),
                'CATURA' => number_format($catura_au_2022,2),
            ];

            $summary_au_2022_rasio_profitabilitas = 
            [
                // 'DETARA' => number_format($detara_au_2022,2),
                // 'DETERA' => number_format($detera_au_2022,2),
                // 'TATURA' => number_format($tatura_au_2022,2),
                // 'RETURA' => number_format($retura_au_2022),
                // 'RETURAH' => number_format($retura_hari_au_2022),
                // 'INTURA' => number_format($intura_au_2022,2),
                'GPM' => number_format($gpm_au_2022,2),
                'OPM' => number_format($opm_au_2022,2),
                'NPM' => number_format($npm_au_2022,2),
                'ROA' => number_format($roa_au_2022,2),
                'ROE' => number_format($roe_au_2022,2),
            ];

            $summary_au_2022_rasio_utang = 
            [
                'DETARA' => number_format($detara_au_2022,2),
                'DETERA' => number_format($detera_au_2022,2),
            ];

            $data_summary_rasio_likuiditas = ProjectTask::$rasio_likuiditas;
            $data_summary_rasio_profitabilitas = ProjectTask::$rasio_profitabilitas;
            $data_summary_rasio_utang = ProjectTask::$rasio_utang;

            foreach($data_summary_rasio_likuiditas as $a => $b)
            {
                $summaryrasiolikuiditas['kode']           = $a;
                $summaryrasiolikuiditas['akun']           = $b;
                $summaryrasiolikuiditas['data_2020']      =  isset($summary_2020_rasio_likuiditas[$a]) ? $summary_2020_rasio_likuiditas[$a] : 0;
                $summaryrasiolikuiditas['data_2021']      =  isset($summary_2021_rasio_likuiditas[$a]) ? $summary_2021_rasio_likuiditas[$a] : 0;
                $summaryrasiolikuiditas['data_in_2022']   =  isset($summary_in_2022_rasio_likuiditas[$a]) ? $summary_in_2022_rasio_likuiditas[$a] : 0;
                $summaryrasiolikuiditas['data_au_2022']   =  isset($summary_au_2022_rasio_likuiditas[$a]) ? $summary_au_2022_rasio_likuiditas[$a] : 0;

                $rasio_likuiditas[] = $summaryrasiolikuiditas;

            }

            foreach($data_summary_rasio_profitabilitas as $a => $b)
            {
                $summaryrasioprofitabilitas['kode']           = $a;
                $summaryrasioprofitabilitas['akun']           = $b;
                $summaryrasioprofitabilitas['data_2020']      =  isset($summary_2020_rasio_profitabilitas[$a]) ? $summary_2020_rasio_profitabilitas[$a] : 0;
                $summaryrasioprofitabilitas['data_2021']      =  isset($summary_2021_rasio_profitabilitas[$a]) ? $summary_2021_rasio_profitabilitas[$a] : 0;
                $summaryrasioprofitabilitas['data_in_2022']   =  isset($summary_in_2022_rasio_profitabilitas[$a]) ? $summary_in_2022_rasio_profitabilitas[$a] : 0;
                $summaryrasioprofitabilitas['data_au_2022']   =  isset($summary_au_2022_rasio_profitabilitas[$a]) ? $summary_au_2022_rasio_profitabilitas[$a] : 0;

                $rasio_profitabilitas[] = $summaryrasioprofitabilitas;

            }

            foreach($data_summary_rasio_utang as $a => $b)
            {
                $summaryrasioutang['kode']           = $a;
                $summaryrasioutang['akun']           = $b;
                $summaryrasioutang['data_2020']      =  isset($summary_2020_rasio_utang[$a]) ? $summary_2020_rasio_utang[$a] : 0;
                $summaryrasioutang['data_2021']      =  isset($summary_2021_rasio_utang[$a]) ? $summary_2021_rasio_utang[$a] : 0;
                $summaryrasioutang['data_in_2022']   =  isset($summary_in_2022_rasio_utang[$a]) ? $summary_in_2022_rasio_utang[$a] : 0;
                $summaryrasioutang['data_au_2022']   =  isset($summary_au_2022_rasio_utang[$a]) ? $summary_au_2022_rasio_utang[$a] : 0;

                $rasio_utang[] = $summaryrasioutang;

            }

            return view('project_task.rasiokeuangan', compact('project','task','rasio_likuiditas','rasio_profitabilitas','rasio_utang'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function createMappingAccount($project_id, $task_id)
    {
        if(\Auth::user()->can('create project task'))
        {
            $project                       = Project::find($project_id);
            $task                          = ProjectTask::find($task_id);
            $mapping_account               = MappingAccountData::get()->pluck('name', 'id');
            $mapping_account->prepend('--', '');

            return view('project_task.createMappingAccount', compact('project','task', 'mapping_account'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function saveMappingAccount(Request $request, $project_id, $task_id)
    {

        if(\Auth::user()->can('create project task'))
        {

            $id                            = Crypt::decrypt($task_id);
            $project                       = Project::find($project_id);
            $task                          = ProjectTask::find($id);

            $category = $request->items;

            for($i = 0; $i < count($category); $i++)
            {
                $mapping_account                    = new MappingAccount();
                $mapping_account->project_id        = $project_id;
                $mapping_account->task_id           = $task->id;
                $mapping_account->account_code      = $category[$i]['account_code'];
                $mapping_account->name              = $category[$i]['name'];
                $mapping_account->account_group     = $category[$i]['account_group'];
                $mapping_account->save();
            }

            // for($i = 0; $i < count($journaldata); $i++)
            // {

            //     FinancialStatement::where(['id' => $journaldata[$i]['item']])->update([
            //         'dr' => $journaldata[$i]['dr'],
            //         'notes' => $request->notes,
            //         'cr' => $journaldata[$i]['cr'],
            //         'audited' => $journaldata[$i]['audited'],
            //     ]);

            // }

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $project_id,
                    'task_id' => $task_id,
                    'log_type' => 'Create Mapping Account',
                    'remark' => json_encode(['title' => 'Create Mapping Account']),
                ]
            );

            return redirect()->route('projects.tasks.financial.statement', [$project_id, $task_id])->with('success', __('Mapping Account successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function mappingaccountdata(Request $request)
    {
        $data['mappingaccountdata']       = $mappingaccountdata = MappingAccountData::find($request->mappingaccountdata_id);
        $data['name']        = (!empty($mappingaccountdata->name)) ? $mappingaccountdata->name : '-';
        $data['code']        = (!empty($mappingaccountdata->code)) ? $mappingaccountdata->code : '-';
        $data['account_group']        = (!empty($mappingaccountdata->materialitas->code)) ? $mappingaccountdata->materialitas->code : '-';

        return json_encode($data);

    }

    public function getResponse(Request $request, $project_id, $task_id)
    {
        
        $tableData = $request->session()->get('tableData');

        //kesimpulan secara keseluruhan dari data ... ada;

        $prompt = $request->input('message');

        $chatGPT = new ChatGPT();
        $chatGPT->addAccount('eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6Ik1UaEVOVUpHTkVNMVFURTRNMEZCTWpkQ05UZzVNRFUxUlRVd1FVSkRNRU13UmtGRVFrRXpSZyJ9.eyJodHRwczovL2FwaS5vcGVuYWkuY29tL3Byb2ZpbGUiOnsiZW1haWwiOiJtdWhhbW1hZC5yaWRod2FuLmtvbnN1bHRhbmt1QGdtYWlsLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlfSwiaHR0cHM6Ly9hcGkub3BlbmFpLmNvbS9hdXRoIjp7InVzZXJfaWQiOiJ1c2VyLW84THNsSnE3WTRIczJLZ3BSTk9pNnBHeiJ9LCJpc3MiOiJodHRwczovL2F1dGgwLm9wZW5haS5jb20vIiwic3ViIjoiZ29vZ2xlLW9hdXRoMnwxMTA2NzA3MTc1Mzc0NjYxNTQ3ODYiLCJhdWQiOlsiaHR0cHM6Ly9hcGkub3BlbmFpLmNvbS92MSIsImh0dHBzOi8vb3BlbmFpLm9wZW5haS5hdXRoMGFwcC5jb20vdXNlcmluZm8iXSwiaWF0IjoxNjg4NTQyMjAxLCJleHAiOjE2ODk3NTE4MDEsImF6cCI6IlRkSkljYmUxNldvVEh0Tjk1bnl5d2g1RTR5T282SXRHIiwic2NvcGUiOiJvcGVuaWQgcHJvZmlsZSBlbWFpbCBtb2RlbC5yZWFkIG1vZGVsLnJlcXVlc3Qgb3JnYW5pemF0aW9uLnJlYWQgb3JnYW5pemF0aW9uLndyaXRlIn0.OG2lywbF7MuKasw9OxPJP6VCfdgtEjM6IT3rLolvws2jIHIjIajOpArDyethkUYRbQWaSB7U3PW0F6VPX0XllMnWxNy1btqeMF7Q9uhqURKFyfLPObnmuZ72PzLyeOLzcrsedLiNcbtp3tU8XkwKkA8nUPZNlM8Ct9w70Ui4wrPFnnoEDj8rGDGX085jDyNLHQFXNqAbCHn5Trr4VxL5QQHGZdGp7bl9EcjLg6GZ-56Unx7L5WU5mGlGU8SLwJgwwzHZjjp4lWd5f14CNeHkzrZ5AN_IAtsMLsC1XqgXv3eHtXA6iqj_SDnVkCIzTtNkpxH6AHSEusfBv3p3GYJ3_A');
        $ask = $chatGPT->ask($prompt . json_encode($tableData) . ' adalah');
        foreach ($ask as $hasil) {
            
        }

        $responseData = [
            'hasil' => $hasil,
        ];

        $response = $hasil['answer'];
        // dd($response);

        $respons = new Respons();
        $respons->project_id = $project_id; 
        $respons->task_id = $task_id;
        $respons->response = $response;
        $respons->save();

        return redirect()->back()->with('success', __('Answer By AI Successfully.'));
    }

    public function getResponseMaterialitas(Request $request, $project_id, $task_id)
    {

        $get_data_materialitas              = ValueMaterialitas::where('project_id', $project_id)->get();
        // dd($summary_materialitas);

        $tableDataMaterialitas = [];

        if (count(array($get_data_materialitas)) > 0) {
            foreach ($get_data_materialitas as $summary_materialitass) {

                $name = $summary_materialitass->materiality->name;

                $tableDataMaterialitas[] = [
                    'materialitas' => $name,
                    'prior_period2' => $summary_materialitass['prior_period2'],
                    'prior_period' => $summary_materialitass['prior_period'],
                    'inhouse' => $summary_materialitass['inhouse'],
                    'audited' => $summary_materialitass['audited'],
                ];
            }
        }

        //kesimpulan secara keseluruhan dari data ... ada;

        $prompt = $request->input('message');

        $chatGPT = new ChatGPT();
        $chatGPT->addAccount('eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6Ik1UaEVOVUpHTkVNMVFURTRNMEZCTWpkQ05UZzVNRFUxUlRVd1FVSkRNRU13UmtGRVFrRXpSZyJ9.eyJodHRwczovL2FwaS5vcGVuYWkuY29tL3Byb2ZpbGUiOnsiZW1haWwiOiJtdWhhbW1hZC5yaWRod2FuLmtvbnN1bHRhbmt1QGdtYWlsLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlfSwiaHR0cHM6Ly9hcGkub3BlbmFpLmNvbS9hdXRoIjp7InVzZXJfaWQiOiJ1c2VyLW84THNsSnE3WTRIczJLZ3BSTk9pNnBHeiJ9LCJpc3MiOiJodHRwczovL2F1dGgwLm9wZW5haS5jb20vIiwic3ViIjoiZ29vZ2xlLW9hdXRoMnwxMTA2NzA3MTc1Mzc0NjYxNTQ3ODYiLCJhdWQiOlsiaHR0cHM6Ly9hcGkub3BlbmFpLmNvbS92MSIsImh0dHBzOi8vb3BlbmFpLm9wZW5haS5hdXRoMGFwcC5jb20vdXNlcmluZm8iXSwiaWF0IjoxNjg4NTQyMjAxLCJleHAiOjE2ODk3NTE4MDEsImF6cCI6IlRkSkljYmUxNldvVEh0Tjk1bnl5d2g1RTR5T282SXRHIiwic2NvcGUiOiJvcGVuaWQgcHJvZmlsZSBlbWFpbCBtb2RlbC5yZWFkIG1vZGVsLnJlcXVlc3Qgb3JnYW5pemF0aW9uLnJlYWQgb3JnYW5pemF0aW9uLndyaXRlIn0.OG2lywbF7MuKasw9OxPJP6VCfdgtEjM6IT3rLolvws2jIHIjIajOpArDyethkUYRbQWaSB7U3PW0F6VPX0XllMnWxNy1btqeMF7Q9uhqURKFyfLPObnmuZ72PzLyeOLzcrsedLiNcbtp3tU8XkwKkA8nUPZNlM8Ct9w70Ui4wrPFnnoEDj8rGDGX085jDyNLHQFXNqAbCHn5Trr4VxL5QQHGZdGp7bl9EcjLg6GZ-56Unx7L5WU5mGlGU8SLwJgwwzHZjjp4lWd5f14CNeHkzrZ5AN_IAtsMLsC1XqgXv3eHtXA6iqj_SDnVkCIzTtNkpxH6AHSEusfBv3p3GYJ3_A');
        $ask = $chatGPT->ask($prompt . json_encode($tableDataMaterialitas) . ' adalah');
        foreach ($ask as $hasil) {
            
        }

        $responseDataMaterialitas = [
            'hasil' => $hasil,
        ];

        $response = $hasil['answer'];

        $respons = new Respons();
        $respons->project_id = $project_id; 
        $respons->task_id = $task_id;
        $respons->response = $response;
        $respons->save();

        // dd($response);   

        return redirect()->back()->with('success', __('Answer By AI Successfully.'));
    }

    public function getidentifiedmisstatements(Request $request, $project_id, $task_id)
    {
        if(\Auth::user()->can('manage project task'))
        {  
            $id                                 = Crypt::decrypt($task_id);
            $project                            = Project::find($project_id);
            $task                               = ProjectTask::find($id);
            $identifiedmisstatements            = SummaryIdentifiedMisstatements::where('project_id', $project_id)->where('task_id', $id)->get();
            $notesanalysis                      = NotesAnalysis::where('project_id', $project_id)->where('task_id','=', $task_id)->orderBy('id', 'DESC')->first();
            return view('project_task.identifiedmisstatements', compact(
                'task','project','identifiedmisstatements','notesanalysis',
            ));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function createSummaryIdentifiedMisstatements($project_id, $task_id)
    {
        if(\Auth::user()->can('create project task'))
        {
            $project                       = Project::find($project_id);
            $task                          = ProjectTask::find($task_id);

            return view('project_task.createSummaryIdentifiedMisstatements', compact('project','task'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function saveSummaryIdentifiedMisstatements(Request $request, $project_id, $task_id)
    {

        if(\Auth::user()->can('create project task'))
        {

            $id                            = Crypt::decrypt($task_id);
            $project                       = Project::find($project_id);
            $task                          = ProjectTask::find($id);

            $category = $request->items;

            for($i = 0; $i < count($category); $i++)
            {
                $identifiedmisstatements                        = new SummaryIdentifiedMisstatements();
                $identifiedmisstatements->project_id            = $project_id;
                $identifiedmisstatements->task_id               = $id;
                $identifiedmisstatements->description           = $category[$i]['description'];
                $identifiedmisstatements->period                = $category[$i]['period'];
                $identifiedmisstatements->type_misstatement     = $category[$i]['type_misstatement'];
                $identifiedmisstatements->corrected             = $category[$i]['corrected'];
                $identifiedmisstatements->assets                = $category[$i]['assets'];
                $identifiedmisstatements->liability             = $category[$i]['liability'];
                $identifiedmisstatements->equity                = $category[$i]['equity'];
                $identifiedmisstatements->income                = $category[$i]['income'];
                $identifiedmisstatements->re                    = $category[$i]['re'];
                $identifiedmisstatements->cause_of_misstatement = $category[$i]['cause_of_misstatement'];
                $identifiedmisstatements->managements_reason    = $category[$i]['managements_reason'];
                $identifiedmisstatements->save();
            }

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $project_id,
                    'task_id' => $task_id,
                    'log_type' => 'Create Summary Identified Misstatements',
                    'remark' => json_encode(['title' => 'Create Summary Identified Misstatements']),
                ]
            );

            return redirect()->route('projects.tasks.identifiedmisstatements', [$project_id, $task_id])->with('success', __('Summary Identified Misstatements Successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function editSummaryIdentifiedMisstatements($project_id, $task_id, $ids)
    {
        if(\Auth::user()->can('edit project task'))
        {
            $id             = Crypt::decrypt($task_id);
            $task           = ProjectTask::find($id);
            $project        = Project::find($project_id);
            $identifiedmisstatements = SummaryIdentifiedMisstatements::find($ids);

            return view('project_task.editSummaryIdentifiedMisstatements', compact('task', 'identifiedmisstatements', 'project'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function updateSummaryIdentifiedMisstatements(Request $request, $project_id, $task_id, $ids)
    {

        if(\Auth::user()->can('edit project task'))
        {

            $category = $request->items;

            for($i = 0; $i < count($category); $i++)
            {
                $identifiedmisstatements                        = SummaryIdentifiedMisstatements::find($ids);
                $identifiedmisstatements->description           = $category[$i]['description'];
                $identifiedmisstatements->period                = $category[$i]['period'];
                $identifiedmisstatements->type_misstatement     = $category[$i]['type_misstatement'];
                $identifiedmisstatements->corrected             = $category[$i]['corrected'];
                $assets                                         = $category[$i]['assets'];
                $liability                                      = $category[$i]['liability'];
                $equity                                         = $category[$i]['equity'];
                $income                                         = $category[$i]['income'];
                $re                                             = $category[$i]['re'];
                $identifiedmisstatements->assets = str_replace(',', '', $assets);
                $identifiedmisstatements->liability = str_replace(',', '', $liability);
                $identifiedmisstatements->equity = str_replace(',', '', $equity);
                $identifiedmisstatements->income = str_replace(',', '', $income);
                $identifiedmisstatements->re = str_replace(',', '', $re);
                $identifiedmisstatements->cause_of_misstatement = $category[$i]['cause_of_misstatement'];
                $identifiedmisstatements->managements_reason    = $category[$i]['managements_reason'];
                $identifiedmisstatements->update();
            }

            // $identifiedmisstatement                        = SummaryIdentifiedMisstatements::find($ids);
            // $identifiedmisstatement->description           = $request->description;
            // $identifiedmisstatement->period                = $request->period;
            // $identifiedmisstatement->type_misstatement     = $request->type_misstatement;
            // $identifiedmisstatement->corrected             = $request->corrected;
            // $identifiedmisstatement->assets                = $request->assets;
            // $identifiedmisstatement->liability             = $request->liability;
            // $identifiedmisstatement->equity                = $request->equity;
            // $identifiedmisstatement->income                = $request->income;
            // $identifiedmisstatement->re                    = $request->re;
            // $identifiedmisstatement->cause_of_misstatement = $request->cause_of_misstatement;
            // $identifiedmisstatement->managements_reason    = $request->managements_reason;
            // $identifiedmisstatement->save();
                  
            return redirect()->route('projects.tasks.identifiedmisstatements', [$project_id, $task_id])->with('success', __('Summary Identified Misstatements updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroySummaryIdentifiedMisstatements(Request $request, $project_id, $task_id, $id)
    {

        if(\Auth::user()->can('delete project task'))
        {
            $identifiedmisstatements = SummaryIdentifiedMisstatements::find($id);
            $identifiedmisstatements->delete();
                      
            return redirect()->route('projects.tasks.identifiedmisstatements', [$project_id, $task_id])->with('success', __('Summary Identified Misstatements successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function updateperiod(Request $request)
    {

        if(\Auth::user()->can('edit project task'))
        {

            $identifiedmisstatements_id = $request->get('id');
            $period = $request->get('period');
        
            $data = SummaryIdentifiedMisstatements::find($identifiedmisstatements_id);
            $data->period = $period;
            $data->save();

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $data->project_id,
                    'task_id' => $data->id,
                    'log_type' => 'Update Period',
                    'remark' => json_encode(['title' => $data->name]),
                ]
            );

            return response()->json(['success' => __('Summary Identified Misstatements successfully updated.')], 200);
            
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function updatedtype_misstatement(Request $request)
    {

        if(\Auth::user()->can('edit project task'))
        {

            $identifiedmisstatements_id = $request->get('id');
            $type_misstatement = $request->get('type_misstatement');
        
            $data = SummaryIdentifiedMisstatements::find($identifiedmisstatements_id);
            $data->type_misstatement = $type_misstatement;
            $data->save();

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $data->project_id,
                    'task_id' => $data->id,
                    'log_type' => 'Update Type Misstatements',
                    'remark' => json_encode(['title' => $data->name]),
                ]
            );

            return response()->json(['success' => __('Summary Identified Misstatements successfully updated.')], 200);
            
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function updatedcorrected(Request $request)
    {

        if(\Auth::user()->can('edit project task'))
        {

            $identifiedmisstatements_id = $request->get('id');
            $corrected = $request->get('corrected');
        
            $data = SummaryIdentifiedMisstatements::find($identifiedmisstatements_id);
            $data->corrected = $corrected;
            $data->save();

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'project_id' => $data->project_id,
                    'task_id' => $data->id,
                    'log_type' => 'Update Corrected',
                    'remark' => json_encode(['title' => $data->name]),
                ]
            );

            return response()->json(['success' => __('Summary Identified Misstatements successfully updated.')], 200);
            
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function getpmpj(Request $request, $project_id, $task_id)
    {
        if(\Auth::user()->can('manage project task'))
        {  
            $id                                 = Crypt::decrypt($task_id);
            $project                            = Project::find($project_id);
            $task                               = ProjectTask::find($id);
            $task                               = ProjectTask::find($id);
            $value_pmpj                         = Pmpj::where('project_id', $project_id)->orderBy('id', 'DESC')->first();
            return view('project_task.pmpj', compact(
                'task','project','value_pmpj',
            ));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function storePmpj(Request $request, $project_id, $task_id)
    {

        if(\Auth::user()->can('create project task'))
        {

            $pmpj                       = new Pmpj();
            $pmpj->project_id       = $project_id;
            $pmpj->task_id          = $task_id;
            $pmpj->ruang_lingkup_jasa         = $request->ruang_lingkup_jasa;
            $pmpj->profil_pengguna_jasa         = $request->profil_pengguna_jasa;
            $pmpj->risiko_ppj         = $request->risiko_ppj;
            $pmpj->profil_bisnis_pengguna_jasa         = $request->profil_bisnis_pengguna_jasa;
            $pmpj->risiko_pbpj         = $request->risiko_pbpj;
            $pmpj->domisili_pengguna_jasa         = $request->domisili_pengguna_jasa;
            $pmpj->risiko_domisili         = $request->risiko_domisili;
            $pmpj->politically_exposed_person         = $request->politically_exposed_person;
            $pmpj->risiko_exposeperson         = $request->risiko_exposeperson;
            $pmpj->transaksi_negara_risiko_tinggi         = $request->transaksi_negara_risiko_tinggi;
            $pmpj->risiko_exposeperson         = $request->risiko_exposeperson;
            $pmpj->risiko_fatf         = $request->risiko_fatf;
            $pmpj->prosedur_pmpj         = $request->prosedur_pmpj;
            $pmpj->link_surat_pernyataan         = $request->link_surat_pernyataan;
            $pmpj->kesimpulan         = $request->input('kesimpulan');
            $pmpj->pmpj_sederhana = $request->input('pmpj_sederhana');
            $pmpj->jenis_pengguna_jasa         = $request->jenis_pengguna_jasa;
            $pmpj->pengguna_jasa_bertindak_untuk         = $request->pengguna_jasa_bertindak_untuk;
            $pmpj->namapenggunajasa         = $request->namapenggunajasa;
            $pmpj->nib         = $request->nib;
            $pmpj->alamatpengguna         = $request->alamatpengguna;
            $pmpj->no_telp         = $request->no_telp;
            $pmpj->namapihak         = $request->namapihak;
            $pmpj->jabatanpihak         = $request->jabatanpihak;
            $pmpj->noidentitaspihak         = $request->noidentitaspihak;
            $pmpj->namabo         = $request->namabo;
            $pmpj->nibbo         = $request->nibbo;
            $pmpj->alamatbo         = $request->alamatbo;
            $pmpj->no_telpbo         = $request->no_telpbo;
            $pmpj->namapihakbo         = $request->namapihakbo;
            $pmpj->jabatanpihakbo         = $request->jabatanpihakbo;
            $pmpj->noidentitaspihakbo         = $request->noidentitaspihakbo;
            $pmpj->link_arsip         = $request->link_arsip;
            $pmpj->verifikasi         = $request->verifikasi;
            $pmpj->ptransaksi         = $request->ptransaksi;
            $pmpj->dokumentasi         = $request->dokumentasi;
            $pmpj->save();
    
            return redirect()->back()->with('success', 'Data has been saved successfully!.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function fetchConclusion($project_id, $task_id)
    {
        $pmpj = Pmpj::where('project_id', $project_id)->orderBy('id', 'DESC')->first();

        if ($pmpj && $pmpj->kesimpulan) {
            return response()->json(['kesimpulan' => $pmpj->kesimpulan]);
        }
    }
}