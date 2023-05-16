<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
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
use App\Models\Materialitas;
use App\Models\ValueMaterialitas;
use App\Models\SummaryMateriality;
use App\Models\AuditMemorandum;
use App\Models\SummaryJournalData;
use Illuminate\Support\Facades\Crypt;
use App\Mail\CommentNotification;

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
                    $existingSubTasks = TaskChecklist::whereIn('name', ['Data Keuangan Ringkas', 'Rasio Keuangan', 'Audit Memorandum'])
                        ->where('task_id', $data_task->id)
                        ->where('project_id', $project_id)
                        ->pluck('name')
                        ->toArray();

                    $createSubTask = [];

                    if (!in_array('Data Keuangan Ringkas', $existingSubTasks)) {
                        $createSubTask[] = [
                            'name' => 'Data Keuangan Ringkas',
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
                                'name' => 'Data Keuangan Ringkas',
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

                $data_task = ProjectTask::where('project_id', '=', $project_id)->where('name','=','Prosedur Analitis')->first();
                
                if(!empty($data_task))
                {
                    $createSubTask = [
                        [
                            'name' => 'Data Keuangan Ringkas',
                            'task_id' => $data_task->id,
                            'project_id' => $project_id,
                            'created_by' => 1,
                            'user_type' => 'User',
                            'status' => 0,
                        ],
                        [
                            'name' => 'Rasio Keuangan',
                            'task_id' => $data_task->id,
                            'project_id' => $project_id,
                            'created_by' => 1,
                            'user_type' => 'User',
                            'status' => 0,
                        ],
                        [
                            'name' => 'Audit Memorandum',
                            'task_id' => $data_task->id,
                            'project_id' => $project_id,
                            'created_by' => 1,
                            'user_type' => 'User',
                            'status' => 0,
                        ],
                    ];
        
                    $checklist = TaskChecklist::create($createSubTask);
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
            if($user->type = 'admin'){
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
            if(\Auth::user()->type != 'super admin')
            {
                if(\Auth::user()->type == 'client')
                {
                    $tasks->where('created_by',\Auth::user()->creatorId());
                }
                elseif(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
                {
                    $tasks->get();
                }
                else
                {
                    $tasks->whereRaw("find_in_set('" . $usr->id . "',assign_to)");
                }
            }
            else
            {
                $tasks->where('created_by',\Auth::user()->creatorId());
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

            $tasks = $tasks->get();

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
            return view('project_task.financialStatement', compact('task','project','financial_statement'));
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
            $valuemateriality                   = SummaryMateriality::where('project_id', $project_id)->first();

            //data
            $data_m1 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.1')->get();
            $data_m2 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.2')->get();
            $data_m3 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.3')->get();
            $data_m4 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.4')->get();
            $data_m5 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.5')->get();
            $data_m6 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.6')->get();
            $data_m7 = FinancialStatement::where('project_id', $project_id)->where('m', '=', 'M.7')->get();

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

            //data array unaudited 2020
            $data_array_2020 = 
            [
                '1' => $total_m1_2020,
                '2' => $total_m2_2020,
                '3' => $total_m3_2020,
                '4' => $total_m4_2020,
                '5' => $total_m5_2020,
                '6' => $total_m6_2020,
                '7' => $total_m7_2020,
            ];

            $data_array_2020['8'] = $total_m4_2020 + $total_m5_2020;
            $data_array_2020['9'] = $total_m6_2020 + $data_array_2020['8'];
            $data_array_2020['10'] = $total_m7_2020 + $data_array_2020['9'];

            //data array audited 2021
            $data_array_2021 = 
            [
                '1' => $total_m1_2021,
                '2' => $total_m2_2021,
                '3' => $total_m3_2021,
                '4' => $total_m4_2021,
                '5' => $total_m5_2021,
                '6' => $total_m6_2021,
                '7' => $total_m7_2021,
            ];

            $data_array_2021['8'] = $total_m4_2021 + $total_m5_2021;
            $data_array_2021['9'] = $total_m6_2021 + $data_array_2021['8'];
            $data_array_2021['10'] = $total_m7_2021 + $data_array_2021['9'];

            //data array inhouse 2022
            $data_array_in_2022 = 
            [
                '1' => $total_m1_in_2022,
                '2' => $total_m2_in_2022,
                '3' => $total_m3_in_2022,
                '4' => $total_m4_in_2022,
                '5' => $total_m5_in_2022,
                '6' => $total_m6_in_2022,
                '7' => $total_m7_in_2022,
            ];

            $data_array_in_2022['8'] = $total_m4_in_2022 + $total_m5_in_2022;
            $data_array_in_2022['9'] = $total_m6_in_2022 + $data_array_in_2022['8'];
            $data_array_in_2022['10'] = $total_m7_in_2022 + $data_array_in_2022['9'];

            //data array audited 2022
            $data_array_au_2022 = 
            [
                '1' => $total_m1_au_2022,
                '2' => $total_m2_au_2022,
                '3' => $total_m3_au_2022,
                '4' => $total_m4_au_2022,
                '5' => $total_m5_au_2022,
                '6' => $total_m6_au_2022,
                '7' => $total_m7_au_2022,
            ];

            $data_array_au_2022['8'] = $total_m4_au_2022 + $total_m5_au_2022;
            $data_array_au_2022['9'] = $total_m6_au_2022 + $data_array_au_2022['8'];
            $data_array_au_2022['10'] = $total_m7_au_2022 + $data_array_au_2022['9'];

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
                'data_array_au_2022','get_data_materialitas','valuemateriality'
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

    public function summaryMaterialitas(Request $request)
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

        $valuesummary = ValueMaterialitas::find($materialitas_id);
        $project_id = $valuesummary->project_id;
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

            return view('project_task.createFinancialStatement', compact('project_id','task_id', 'materialitas'));
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
            $financial_statement                = FinancialStatement::where('project_id', $project_id)->whereNotNull('audited')->get();
            return view('project_task.journalentries', compact(
                'task','project','financial_statement',
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
            $financial_statement = FinancialStatement::where('project_id', $project_id)->get()->pluck('coa', 'id');
            $financial_statement->prepend('--', '');

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

            $id                            = Crypt::decrypt($task_id);
            $project                       = Project::find($project_id);
            $task                          = ProjectTask::find($id);

            $journaldata = $request->items;

            $summary_journaldata               = new SummaryJournalData();
            $summary_journaldata->project_id   = $project_id;
            $summary_journaldata->notes        = $request->notes;

            $items = array();
            foreach ($journaldata as $row) {
            $items[] = $row['item'];
            }

            $result = implode(',', $items);

            $summary_journaldata->coa     = $result;
            $summary_journaldata->save();

            for($i = 0; $i < count($journaldata); $i++)
            {

                FinancialStatement::where(['id' => $journaldata[$i]['item']])->update([
                    'dr' => $journaldata[$i]['dr'],
                    'notes' => $request->notes,
                    'cr' => $journaldata[$i]['cr'],
                    'audited' => $journaldata[$i]['audited'],
                ]);

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


            $data_lk_2020 = [];
            $data_lk_2021 = [];
            $data_lk_in_2022 = [];
            $data_januari = [];
            $data_februari = [];
            $data_maret = [];
            $data_april = [];
            $data_mei = [];
            $data_juni = [];
            $data_juli = [];
            $data_agustus = [];
            $data_september = [];
            $data_oktober = [];
            $data_november = [];
            $data_desember = [];

            $index = [];
            $summarys = [];
            $cn = [];

            for ($i = 1; $i <= 35; $i++) {
                $m = 'LK.' . $i;
                $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();
                
                $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
                $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
                
                $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
                $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
                
                $data_lk_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
                $total_lk_in_2022[$m] = array_sum($data_lk_in_2022[$m]);
                
                $data_lk_au_2022[$m] = $data_lk->pluck('audited')->toArray();
                $total_lk_au_2022[$m] = array_sum($data_lk_au_2022[$m]);

                $data_januari[$m] = $data_lk->pluck('jan')->toArray();
                $total_januari[$m] = array_sum($data_januari[$m]);
                
                $data_februari[$m] = $data_lk->pluck('feb')->toArray();
                $total_februari[$m] = array_sum($data_februari[$m]);

                $data_maret[$m] = $data_lk->pluck('mar')->toArray();
                $total_maret[$m] = array_sum($data_maret[$m]);

                $data_april[$m] = $data_lk->pluck('apr')->toArray();
                $total_april[$m] = array_sum($data_april[$m]);

                $data_mei[$m] = $data_lk->pluck('may')->toArray();
                $total_mei[$m] = array_sum($data_mei[$m]);

                $data_juni[$m] = $data_lk->pluck('jun')->toArray();
                $total_juni[$m] = array_sum($data_juni[$m]);

                $data_juli[$m] = $data_lk->pluck('jul')->toArray();
                $total_juli[$m] = array_sum($data_juli[$m]);

                $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
                $total_agustus[$m] = array_sum($data_agustus[$m]);

                $data_september[$m] = $data_lk->pluck('sep')->toArray();
                $total_september[$m] = array_sum($data_september[$m]);

                $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
                $total_oktober[$m] = array_sum($data_oktober[$m]);

                $data_november[$m] = $data_lk->pluck('nov')->toArray();
                $total_november[$m] = array_sum($data_november[$m]);

                $data_desember[$m] = $data_lk->pluck('dec')->toArray();
                $total_desember[$m] = array_sum($data_desember[$m]);
            }

            //Mencari nilai total aset
            $total_aset_2020 = 0;
            $total_aset_2021 = 0;
            $total_aset_in_2022 = 0;
            $total_aset_au_2022 = 0;
            $total_aset_januari = 0;
            $total_aset_februari = 0;
            $total_aset_maret = 0;
            $total_aset_april = 0;
            $total_aset_mei = 0;
            $total_aset_juni = 0;
            $total_aset_juli = 0;
            $total_aset_agustus = 0;
            $total_aset_september = 0;
            $total_aset_oktober = 0;
            $total_aset_november = 0;
            $total_aset_desember = 0;
            
            for ($i = 1; $i <= 15; $i++) {
                $m = 'LK.' . $i;
                $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

                $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
                $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
                
                $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
                $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
                
                $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
                $total_in_2022[$m] = array_sum($data_in_2022[$m]);
                
                $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
                $total_au_2022[$m] = array_sum($data_au_2022[$m]);

                $data_januari[$m] = $data_lk->pluck('jan')->toArray();
                $total_januari[$m] = array_sum($data_januari[$m]);
                
                $data_februari[$m] = $data_lk->pluck('feb')->toArray();
                $total_februari[$m] = array_sum($data_februari[$m]);

                $data_maret[$m] = $data_lk->pluck('mar')->toArray();
                $total_maret[$m] = array_sum($data_maret[$m]);

                $data_april[$m] = $data_lk->pluck('apr')->toArray();
                $total_april[$m] = array_sum($data_april[$m]);

                $data_mei[$m] = $data_lk->pluck('may')->toArray();
                $total_mei[$m] = array_sum($data_mei[$m]);

                $data_juni[$m] = $data_lk->pluck('jun')->toArray();
                $total_juni[$m] = array_sum($data_juni[$m]);

                $data_juli[$m] = $data_lk->pluck('jul')->toArray();
                $total_juli[$m] = array_sum($data_juli[$m]);

                $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
                $total_agustus[$m] = array_sum($data_agustus[$m]);

                $data_september[$m] = $data_lk->pluck('sep')->toArray();
                $total_september[$m] = array_sum($data_september[$m]);

                $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
                $total_oktober[$m] = array_sum($data_oktober[$m]);

                $data_november[$m] = $data_lk->pluck('nov')->toArray();
                $total_november[$m] = array_sum($data_november[$m]);

                $data_desember[$m] = $data_lk->pluck('dec')->toArray();
                $total_desember[$m] = array_sum($data_desember[$m]);

                $total_aset_2020 += $total_lk_2020[$m];
                $total_aset_2021 += $total_lk_2021[$m];
                $total_aset_in_2022 += $total_in_2022[$m];
                $total_aset_au_2022 += $total_au_2022[$m];
                $total_aset_januari += $total_januari[$m];
                $total_aset_februari += $total_februari[$m];
                $total_aset_maret += $total_maret[$m];
                $total_aset_april += $total_april[$m];
                $total_aset_mei += $total_mei[$m];
                $total_aset_juni += $total_juni[$m];
                $total_aset_juli += $total_juli[$m];
                $total_aset_agustus += $total_agustus[$m];
                $total_aset_september += $total_september[$m];
                $total_aset_oktober += $total_oktober[$m];
                $total_aset_november += $total_november[$m];
                $total_aset_desember += $total_desember[$m];
            }


            //Mencari nilai total liabitias
            $total_liabilitas_2020 = 0;
            $total_liabilitas_2021 = 0;
            $total_liabilitas_in_2022 = 0;
            $total_liabilitas_au_2022 = 0;
            $total_liabilitas_januari = 0;
            $total_liabilitas_februari = 0;
            $total_liabilitas_maret = 0;
            $total_liabilitas_april = 0;
            $total_liabilitas_mei = 0;
            $total_liabilitas_juni = 0;
            $total_liabilitas_juli = 0;
            $total_liabilitas_agustus = 0;
            $total_liabilitas_september = 0;
            $total_liabilitas_oktober = 0;
            $total_liabilitas_november = 0;
            $total_liabilitas_desember = 0;
            
            for ($i = 16; $i <= 25; $i++) {
                $m = 'LK.' . $i;
                $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

                $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
                $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
                
                $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
                $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
                
                $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
                $total_in_2022[$m] = array_sum($data_in_2022[$m]);
                
                $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
                $total_au_2022[$m] = array_sum($data_au_2022[$m]);
                
                $data_januari[$m] = $data_lk->pluck('jan')->toArray();
                $total_januari[$m] = array_sum($data_januari[$m]);
                
                $data_februari[$m] = $data_lk->pluck('feb')->toArray();
                $total_februari[$m] = array_sum($data_februari[$m]);

                $data_maret[$m] = $data_lk->pluck('mar')->toArray();
                $total_maret[$m] = array_sum($data_maret[$m]);

                $data_april[$m] = $data_lk->pluck('apr')->toArray();
                $total_april[$m] = array_sum($data_april[$m]);

                $data_mei[$m] = $data_lk->pluck('may')->toArray();
                $total_mei[$m] = array_sum($data_mei[$m]);

                $data_juni[$m] = $data_lk->pluck('jun')->toArray();
                $total_juni[$m] = array_sum($data_juni[$m]);

                $data_juli[$m] = $data_lk->pluck('jul')->toArray();
                $total_juli[$m] = array_sum($data_juli[$m]);

                $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
                $total_agustus[$m] = array_sum($data_agustus[$m]);

                $data_september[$m] = $data_lk->pluck('sep')->toArray();
                $total_september[$m] = array_sum($data_september[$m]);

                $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
                $total_oktober[$m] = array_sum($data_oktober[$m]);

                $data_november[$m] = $data_lk->pluck('nov')->toArray();
                $total_november[$m] = array_sum($data_november[$m]);

                $data_desember[$m] = $data_lk->pluck('dec')->toArray();
                $total_desember[$m] = array_sum($data_desember[$m]);

                $total_liabilitas_2020 += $total_lk_2020[$m];
                $total_liabilitas_2021 += $total_lk_2021[$m];
                $total_liabilitas_in_2022 += $total_in_2022[$m];
                $total_liabilitas_au_2022 += $total_au_2022[$m];
                $total_liabilitas_januari += $total_januari[$m];
                $total_liabilitas_februari += $total_februari[$m];
                $total_liabilitas_maret += $total_maret[$m];
                $total_liabilitas_april += $total_april[$m];
                $total_liabilitas_mei += $total_mei[$m];
                $total_liabilitas_juni += $total_juni[$m];
                $total_liabilitas_juli += $total_juli[$m];
                $total_liabilitas_agustus += $total_agustus[$m];
                $total_liabilitas_september += $total_september[$m];
                $total_liabilitas_oktober += $total_oktober[$m];
                $total_liabilitas_november += $total_november[$m];
                $total_liabilitas_desember += $total_desember[$m];
            }

            //Mencari nilai total ekuitas
            $total_ekuitas_2020 = 0;
            $total_ekuitas_2021 = 0;
            $total_ekuitas_in_2022 = 0;
            $total_ekuitas_au_2022 = 0;
            $total_ekuitas_januari = 0;
            $total_ekuitas_februari = 0;
            $total_ekuitas_maret = 0;
            $total_ekuitas_april = 0;
            $total_ekuitas_mei = 0;
            $total_ekuitas_juni = 0;
            $total_ekuitas_juli = 0;
            $total_ekuitas_agustus = 0;
            $total_ekuitas_september = 0;
            $total_ekuitas_oktober = 0;
            $total_ekuitas_november = 0;
            $total_ekuitas_desember = 0;
            
            for ($i = 26; $i <= 29; $i++) {
                $m = 'LK.' . $i;
                $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

                $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
                $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
                $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
                $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
                $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
                $total_in_2022[$m] = array_sum($data_in_2022[$m]);
                $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
                $total_au_2022[$m] = array_sum($data_au_2022[$m]);
                
                $data_januari[$m] = $data_lk->pluck('jan')->toArray();
                $total_januari[$m] = array_sum($data_januari[$m]);
                
                $data_februari[$m] = $data_lk->pluck('feb')->toArray();
                $total_februari[$m] = array_sum($data_februari[$m]);

                $data_maret[$m] = $data_lk->pluck('mar')->toArray();
                $total_maret[$m] = array_sum($data_maret[$m]);

                $data_april[$m] = $data_lk->pluck('apr')->toArray();
                $total_april[$m] = array_sum($data_april[$m]);

                $data_mei[$m] = $data_lk->pluck('may')->toArray();
                $total_mei[$m] = array_sum($data_mei[$m]);

                $data_juni[$m] = $data_lk->pluck('jun')->toArray();
                $total_juni[$m] = array_sum($data_juni[$m]);

                $data_juli[$m] = $data_lk->pluck('jul')->toArray();
                $total_juli[$m] = array_sum($data_juli[$m]);

                $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
                $total_agustus[$m] = array_sum($data_agustus[$m]);

                $data_september[$m] = $data_lk->pluck('sep')->toArray();
                $total_september[$m] = array_sum($data_september[$m]);

                $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
                $total_oktober[$m] = array_sum($data_oktober[$m]);

                $data_november[$m] = $data_lk->pluck('nov')->toArray();
                $total_november[$m] = array_sum($data_november[$m]);

                $data_desember[$m] = $data_lk->pluck('dec')->toArray();
                $total_desember[$m] = array_sum($data_desember[$m]);

                $total_ekuitas_2020 += $total_lk_2020[$m];
                $total_ekuitas_2021 += $total_lk_2021[$m];
                $total_ekuitas_in_2022 += $total_in_2022[$m];
                $total_ekuitas_au_2022 += $total_au_2022[$m];
                $total_ekuitas_januari += $total_januari[$m];
                $total_ekuitas_februari += $total_februari[$m];
                $total_ekuitas_maret += $total_maret[$m];
                $total_ekuitas_april += $total_april[$m];
                $total_ekuitas_mei += $total_mei[$m];
                $total_ekuitas_juni += $total_juni[$m];
                $total_ekuitas_juli += $total_juli[$m];
                $total_ekuitas_agustus += $total_agustus[$m];
                $total_ekuitas_september += $total_september[$m];
                $total_ekuitas_oktober += $total_oktober[$m];
                $total_ekuitas_november += $total_november[$m];
                $total_ekuitas_desember += $total_desember[$m];
            }

            //Mencari nilai total laba kotor
            $total_laba_kotor_2020 = 0;
            $total_laba_kotor_2021 = 0;
            $total_laba_kotor_in_2022 = 0;
            $total_laba_kotor_au_2022 = 0;
            $total_laba_kotor_januari = 0;
            $total_laba_kotor_februari = 0;
            $total_laba_kotor_maret = 0;
            $total_laba_kotor_april = 0;
            $total_laba_kotor_mei = 0;
            $total_laba_kotor_juni = 0;
            $total_laba_kotor_juli = 0;
            $total_laba_kotor_agustus = 0;
            $total_laba_kotor_september = 0;
            $total_laba_kotor_oktober = 0;
            $total_laba_kotor_november = 0;
            $total_laba_kotor_desember = 0;
            
            for ($i = 30; $i <= 31; $i++) {
                $m = 'LK.' . $i;
                $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

                $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
                $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
                $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
                $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
                $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
                $total_in_2022[$m] = array_sum($data_in_2022[$m]);
                $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
                $total_au_2022[$m] = array_sum($data_au_2022[$m]);

                $data_januari[$m] = $data_lk->pluck('jan')->toArray();
                $total_januari[$m] = array_sum($data_januari[$m]);
                
                $data_februari[$m] = $data_lk->pluck('feb')->toArray();
                $total_februari[$m] = array_sum($data_februari[$m]);

                $data_maret[$m] = $data_lk->pluck('mar')->toArray();
                $total_maret[$m] = array_sum($data_maret[$m]);

                $data_april[$m] = $data_lk->pluck('apr')->toArray();
                $total_april[$m] = array_sum($data_april[$m]);

                $data_mei[$m] = $data_lk->pluck('may')->toArray();
                $total_mei[$m] = array_sum($data_mei[$m]);

                $data_juni[$m] = $data_lk->pluck('jun')->toArray();
                $total_juni[$m] = array_sum($data_juni[$m]);

                $data_juli[$m] = $data_lk->pluck('jul')->toArray();
                $total_juli[$m] = array_sum($data_juli[$m]);

                $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
                $total_agustus[$m] = array_sum($data_agustus[$m]);

                $data_september[$m] = $data_lk->pluck('sep')->toArray();
                $total_september[$m] = array_sum($data_september[$m]);

                $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
                $total_oktober[$m] = array_sum($data_oktober[$m]);

                $data_november[$m] = $data_lk->pluck('nov')->toArray();
                $total_november[$m] = array_sum($data_november[$m]);

                $data_desember[$m] = $data_lk->pluck('dec')->toArray();
                $total_desember[$m] = array_sum($data_desember[$m]);

                $total_laba_kotor_2020 += $total_lk_2020[$m];
                $total_laba_kotor_2021 += $total_lk_2021[$m];
                $total_laba_kotor_in_2022 += $total_in_2022[$m];
                $total_laba_kotor_au_2022 += $total_au_2022[$m];
                $total_laba_kotor_januari += $total_januari[$m];
                $total_laba_kotor_februari += $total_februari[$m];
                $total_laba_kotor_maret += $total_maret[$m];
                $total_laba_kotor_april += $total_april[$m];
                $total_laba_kotor_mei += $total_mei[$m];
                $total_laba_kotor_juni += $total_juni[$m];
                $total_laba_kotor_juli += $total_juli[$m];
                $total_laba_kotor_agustus += $total_agustus[$m];
                $total_laba_kotor_september += $total_september[$m];
                $total_laba_kotor_oktober += $total_oktober[$m];
                $total_laba_kotor_november += $total_november[$m];
                $total_laba_kotor_desember += $total_desember[$m];
            }

            //Mencari nilai total laba bersih sebelum pajak
            $total_laba_bersih_sebelum_pajak_2020 = 0;
            $total_laba_bersih_sebelum_pajak_2021 = 0;
            $total_laba_bersih_sebelum_pajak_in_2022 = 0;
            $total_laba_bersih_sebelum_pajak_au_2022 = 0;
            $total_laba_bersih_sebelum_pajak_januari = 0;
            $total_laba_bersih_sebelum_pajak_februari = 0;
            $total_laba_bersih_sebelum_pajak_maret = 0;
            $total_laba_bersih_sebelum_pajak_april = 0;
            $total_laba_bersih_sebelum_pajak_mei = 0;
            $total_laba_bersih_sebelum_pajak_juni = 0;
            $total_laba_bersih_sebelum_pajak_juli = 0;
            $total_laba_bersih_sebelum_pajak_agustus = 0;
            $total_laba_bersih_sebelum_pajak_september = 0;
            $total_laba_bersih_sebelum_pajak_oktober = 0;
            $total_laba_bersih_sebelum_pajak_november = 0;
            $total_laba_bersih_sebelum_pajak_desember = 0;
            
            for ($i = 32; $i <= 34; $i++) {
                $m = 'LK.' . $i;
                $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

                $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
                $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
                $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
                $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
                $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
                $total_in_2022[$m] = array_sum($data_in_2022[$m]);
                $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
                $total_au_2022[$m] = array_sum($data_au_2022[$m]);

                $data_januari[$m] = $data_lk->pluck('jan')->toArray();
                $total_januari[$m] = array_sum($data_januari[$m]);
                
                $data_februari[$m] = $data_lk->pluck('feb')->toArray();
                $total_februari[$m] = array_sum($data_februari[$m]);

                $data_maret[$m] = $data_lk->pluck('mar')->toArray();
                $total_maret[$m] = array_sum($data_maret[$m]);

                $data_april[$m] = $data_lk->pluck('apr')->toArray();
                $total_april[$m] = array_sum($data_april[$m]);

                $data_mei[$m] = $data_lk->pluck('may')->toArray();
                $total_mei[$m] = array_sum($data_mei[$m]);

                $data_juni[$m] = $data_lk->pluck('jun')->toArray();
                $total_juni[$m] = array_sum($data_juni[$m]);

                $data_juli[$m] = $data_lk->pluck('jul')->toArray();
                $total_juli[$m] = array_sum($data_juli[$m]);

                $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
                $total_agustus[$m] = array_sum($data_agustus[$m]);

                $data_september[$m] = $data_lk->pluck('sep')->toArray();
                $total_september[$m] = array_sum($data_september[$m]);

                $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
                $total_oktober[$m] = array_sum($data_oktober[$m]);

                $data_november[$m] = $data_lk->pluck('nov')->toArray();
                $total_november[$m] = array_sum($data_november[$m]);

                $data_desember[$m] = $data_lk->pluck('dec')->toArray();
                $total_desember[$m] = array_sum($data_desember[$m]);

                $total_laba_bersih_sebelum_pajak_2020 += $total_lk_2020[$m];
                $total_laba_bersih_sebelum_pajak_2021 += $total_lk_2021[$m];
                $total_laba_bersih_sebelum_pajak_in_2022 += $total_in_2022[$m];
                $total_laba_bersih_sebelum_pajak_au_2022 += $total_au_2022[$m];
                $total_laba_bersih_sebelum_pajak_januari += $total_januari[$m];
                $total_laba_bersih_sebelum_pajak_februari += $total_februari[$m];
                $total_laba_bersih_sebelum_pajak_maret += $total_maret[$m];
                $total_laba_bersih_sebelum_pajak_april += $total_april[$m];
                $total_laba_bersih_sebelum_pajak_mei += $total_mei[$m];
                $total_laba_bersih_sebelum_pajak_juni += $total_juni[$m];
                $total_laba_bersih_sebelum_pajak_juli += $total_juli[$m];
                $total_laba_bersih_sebelum_pajak_agustus += $total_agustus[$m];
                $total_laba_bersih_sebelum_pajak_september += $total_september[$m];
                $total_laba_bersih_sebelum_pajak_oktober += $total_oktober[$m];
                $total_laba_bersih_sebelum_pajak_november += $total_november[$m];
                $total_laba_bersih_sebelum_pajak_desember += $total_desember[$m];
            }

            //Mencari nilai total laba bersih setelah pajak
            $total_laba_bersih_setelah_pajak_2020 = 0;
            $total_laba_bersih_setelah_pajak_2021 = 0;
            $total_laba_bersih_setelah_pajak_in_2022 = 0;
            $total_laba_bersih_setelah_pajak_au_2022 = 0;
            $total_laba_bersih_setelah_pajak_januari = 0;
            $total_laba_bersih_setelah_pajak_februari = 0;
            $total_laba_bersih_setelah_pajak_maret = 0;
            $total_laba_bersih_setelah_pajak_april = 0;
            $total_laba_bersih_setelah_pajak_mei = 0;
            $total_laba_bersih_setelah_pajak_juni = 0;
            $total_laba_bersih_setelah_pajak_juli = 0;
            $total_laba_bersih_setelah_pajak_agustus = 0;
            $total_laba_bersih_setelah_pajak_september = 0;
            $total_laba_bersih_setelah_pajak_oktober = 0;
            $total_laba_bersih_setelah_pajak_november = 0;
            $total_laba_bersih_setelah_pajak_desember = 0;
            
            $i = 35;
            $m = 'LK.' . $i;
            $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

            $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
            $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
            $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
            $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
            $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
            $total_in_2022[$m] = array_sum($data_in_2022[$m]);
            $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
            $total_au_2022[$m] = array_sum($data_au_2022[$m]);
            $data_januari[$m] = $data_lk->pluck('jan')->toArray();
            $total_januari[$m] = array_sum($data_januari[$m]);
            
            $data_februari[$m] = $data_lk->pluck('feb')->toArray();
            $total_februari[$m] = array_sum($data_februari[$m]);

            $data_maret[$m] = $data_lk->pluck('mar')->toArray();
            $total_maret[$m] = array_sum($data_maret[$m]);

            $data_april[$m] = $data_lk->pluck('apr')->toArray();
            $total_april[$m] = array_sum($data_april[$m]);

            $data_mei[$m] = $data_lk->pluck('may')->toArray();
            $total_mei[$m] = array_sum($data_mei[$m]);

            $data_juni[$m] = $data_lk->pluck('jun')->toArray();
            $total_juni[$m] = array_sum($data_juni[$m]);

            $data_juli[$m] = $data_lk->pluck('jul')->toArray();
            $total_juli[$m] = array_sum($data_juli[$m]);

            $data_agustus[$m] = $data_lk->pluck('aug')->toArray();
            $total_agustus[$m] = array_sum($data_agustus[$m]);

            $data_september[$m] = $data_lk->pluck('sep')->toArray();
            $total_september[$m] = array_sum($data_september[$m]);

            $data_oktober[$m] = $data_lk->pluck('oct')->toArray();
            $total_oktober[$m] = array_sum($data_oktober[$m]);

            $data_november[$m] = $data_lk->pluck('nov')->toArray();
            $total_november[$m] = array_sum($data_november[$m]);

            $data_desember[$m] = $data_lk->pluck('dec')->toArray();
            $total_desember[$m] = array_sum($data_desember[$m]);

            $total_laba_bersih_setelah_pajak_2020 += $total_lk_2020[$m];
            $total_laba_bersih_setelah_pajak_2021 += $total_lk_2021[$m];
            $total_laba_bersih_setelah_pajak_in_2022 += $total_in_2022[$m];
            $total_laba_bersih_setelah_pajak_au_2022 += $total_au_2022[$m];
            $total_laba_bersih_setelah_pajak_januari += $total_januari[$m];
            $total_laba_bersih_setelah_pajak_februari += $total_februari[$m];
            $total_laba_bersih_setelah_pajak_maret += $total_maret[$m];
            $total_laba_bersih_setelah_pajak_april += $total_april[$m];
            $total_laba_bersih_setelah_pajak_mei += $total_mei[$m];
            $total_laba_bersih_setelah_pajak_juni += $total_juni[$m];
            $total_laba_bersih_setelah_pajak_juli += $total_juli[$m];
            $total_laba_bersih_setelah_pajak_agustus += $total_agustus[$m];
            $total_laba_bersih_setelah_pajak_september += $total_september[$m];
            $total_laba_bersih_setelah_pajak_oktober += $total_oktober[$m];
            $total_laba_bersih_setelah_pajak_november += $total_november[$m];
            $total_laba_bersih_setelah_pajak_desember += $total_desember[$m];


            //data array 2020
            $data_array_2020 = 
            [
                '00' => $total_aset_2020,
                '000' => $total_liabilitas_2020,
                '0000' => $total_ekuitas_2020,
                '00000' => $total_laba_kotor_2020,
                '000000' => $total_laba_bersih_sebelum_pajak_2020,
                '0000000' => $total_laba_bersih_setelah_pajak_2020,
            ];

            //data array 2021
            $data_array_2021 = 
            [
                '00' => $total_aset_2021,
                '000' => $total_liabilitas_2021,
                '0000' => $total_ekuitas_2021,
                '00000' => $total_laba_kotor_2021,
                '000000' => $total_laba_bersih_sebelum_pajak_2021,
                '0000000' => $total_laba_bersih_setelah_pajak_2021,
            ];

            //data array inhouse 2022
            $data_array_in_2022 = 
            [
                '00' => $total_aset_in_2022,
                '000' => $total_liabilitas_in_2022,
                '0000' => $total_ekuitas_in_2022,
                '00000' => $total_laba_kotor_in_2022,
                '000000' => $total_laba_bersih_sebelum_pajak_in_2022,
                '0000000' => $total_laba_bersih_setelah_pajak_in_2022,
            ];

            //data array audited 2022
            $data_array_au_2022 = 
            [
                '00' => $total_aset_au_2022,
                '000' => $total_liabilitas_au_2022,
                '0000' => $total_ekuitas_au_2022,
                '00000' => $total_laba_kotor_au_2022,
                '000000' => $total_laba_bersih_sebelum_pajak_au_2022,
                '0000000' => $total_laba_bersih_setelah_pajak_au_2022,
            ];

            $data_summary_januari = 
            [
                '00' => $total_aset_januari,
                '000' => $total_liabilitas_januari,
                '0000' => $total_ekuitas_januari,
                '00000' => $total_laba_kotor_januari,
                '000000' => $total_laba_bersih_sebelum_pajak_januari,
                '0000000' => $total_laba_bersih_setelah_pajak_januari,
            ];

            $data_summary_februari = 
            [
                '00' => $total_aset_februari,
                '000' => $total_liabilitas_februari,
                '0000' => $total_ekuitas_februari,
                '00000' => $total_laba_kotor_februari,
                '000000' => $total_laba_bersih_sebelum_pajak_februari,
                '0000000' => $total_laba_bersih_setelah_pajak_februari,
            ];

            $data_summary_maret = 
            [
                '00' => $total_aset_maret,
                '000' => $total_liabilitas_maret,
                '0000' => $total_ekuitas_maret,
                '00000' => $total_laba_kotor_maret,
                '000000' => $total_laba_bersih_sebelum_pajak_maret,
                '0000000' => $total_laba_bersih_setelah_pajak_maret,
            ];

            $data_summary_april = 
            [
                '00' => $total_aset_april,
                '000' => $total_liabilitas_april,
                '0000' => $total_ekuitas_april,
                '00000' => $total_laba_kotor_april,
                '000000' => $total_laba_bersih_sebelum_pajak_april,
                '0000000' => $total_laba_bersih_setelah_pajak_april,
            ];

            $data_summary_mei = 
            [
                '00' => $total_aset_mei,
                '000' => $total_liabilitas_mei,
                '0000' => $total_ekuitas_mei,
                '00000' => $total_laba_kotor_mei,
                '000000' => $total_laba_bersih_sebelum_pajak_mei,
                '0000000' => $total_laba_bersih_setelah_pajak_mei,
            ];
            
            $data_summary_juni = 
            [
                '00' => $total_aset_juni,
                '000' => $total_liabilitas_juni,
                '0000' => $total_ekuitas_juni,
                '00000' => $total_laba_kotor_juni,
                '000000' => $total_laba_bersih_sebelum_pajak_juni,
                '0000000' => $total_laba_bersih_setelah_pajak_juni,
            ];

            $data_summary_juli = 
            [
                '00' => $total_aset_juli,
                '000' => $total_liabilitas_juli,
                '0000' => $total_ekuitas_juli,
                '00000' => $total_laba_kotor_juli,
                '000000' => $total_laba_bersih_sebelum_pajak_juli,
                '0000000' => $total_laba_bersih_setelah_pajak_juli,
            ];

            $data_summary_agustus = 
            [
                '00' => $total_aset_agustus,
                '000' => $total_liabilitas_agustus,
                '0000' => $total_ekuitas_agustus,
                '00000' => $total_laba_kotor_agustus,
                '000000' => $total_laba_bersih_sebelum_pajak_agustus,
                '0000000' => $total_laba_bersih_setelah_pajak_agustus,
            ];

            $data_summary_september = 
            [
                '00' => $total_aset_september,
                '000' => $total_liabilitas_september,
                '0000' => $total_ekuitas_september,
                '00000' => $total_laba_kotor_september,
                '000000' => $total_laba_bersih_sebelum_pajak_september,
                '0000000' => $total_laba_bersih_setelah_pajak_september,
            ];

            $data_summary_oktober = 
            [
                '00' => $total_aset_oktober,
                '000' => $total_liabilitas_oktober,
                '0000' => $total_ekuitas_oktober,
                '00000' => $total_laba_kotor_oktober,
                '000000' => $total_laba_bersih_sebelum_pajak_oktober,
                '0000000' => $total_laba_bersih_setelah_pajak_oktober,
            ];

            $data_summary_november = 
            [
                '00' => $total_aset_november,
                '000' => $total_liabilitas_november,
                '0000' => $total_ekuitas_november,
                '00000' => $total_laba_kotor_november,
                '000000' => $total_laba_bersih_sebelum_pajak_november,
                '0000000' => $total_laba_bersih_setelah_pajak_november,
            ];

            $data_summary_desember = 
            [
                '00' => $total_aset_desember,
                '000' => $total_liabilitas_desember,
                '0000' => $total_ekuitas_desember,
                '00000' => $total_laba_kotor_desember,
                '000000' => $total_laba_bersih_sebelum_pajak_desember,
                '0000000' => $total_laba_bersih_setelah_pajak_desember,
            ];

            $summary = ProjectTask::$summary;

            $result = $this->mergeSummaryData($summary,$data_array_2020,$data_array_2021, $data_array_in_2022, $data_array_au_2022, $data_summary_januari, $data_summary_februari, $data_summary_maret,
            $data_summary_april, $data_summary_mei, $data_summary_juni, $data_summary_juli, $data_summary_agustus,
            $data_summary_september, $data_summary_oktober, $data_summary_november, $data_summary_desember);

            $data_index = ProjectTask::$financial_statement;
            $data_materialitiy = SummaryMateriality::where('project_id', $project_id)->first();
            $initialmaterialityom = $data_materialitiy->initialmaterialityom;

            foreach($data_index as $a => $b)
            {
                $keuanganringkas['kode']    = $a;
                $keuanganringkas['akun']    = $b;
                $keuanganringkas['data_2020']    =  isset($total_lk_2020[$a]) ? $total_lk_2020[$a] : 0;
                $keuanganringkas['data_2021']    =  isset($total_lk_2021[$a]) ? $total_lk_2021[$a] : 0;
                $keuanganringkas['data_in_2022']    =  isset($total_lk_in_2022[$a]) ? $total_lk_in_2022[$a] : 0;
                $keuanganringkas['data_au_2022']    =  isset($total_lk_au_2022[$a]) ? $total_lk_au_2022[$a] : 0;
                $keuanganringkas['januari'] = isset($total_januari[$a]) ? $total_januari[$a] : 0;
                $keuanganringkas['februari'] = isset($total_februari[$a]) ? $total_februari[$a] : 0;
                $keuanganringkas['maret'] = isset($total_maret[$a]) ? $total_maret[$a] : 0;
                $keuanganringkas['april'] = isset($total_april[$a]) ? $total_april[$a] : 0;
                $keuanganringkas['mei'] = isset($total_mei[$a]) ? $total_mei[$a] : 0;
                $keuanganringkas['juni'] = isset($total_juni[$a]) ? $total_juni[$a] : 0;
                $keuanganringkas['juli'] = isset($total_juli[$a]) ? $total_juli[$a] : 0;
                $keuanganringkas['agustus'] = isset($total_agustus[$a]) ? $total_agustus[$a] : 0;
                $keuanganringkas['september'] = isset($total_september[$a]) ? $total_september[$a] : 0;
                $keuanganringkas['oktober'] = isset($total_oktober[$a]) ? $total_oktober[$a] : 0;
                $keuanganringkas['november'] = isset($total_november[$a]) ? $total_november[$a] : 0;
                $keuanganringkas['desember'] = isset($total_desember[$a]) ? $total_desember[$a] : 0;
                //2021

                $keuanganringkas['kenaikan_2021'] = $keuanganringkas['data_2021'] - $keuanganringkas['data_2020'];
                
                if ($keuanganringkas['data_2020'] != 0) {
                    $keuanganringkas['persen_kenaikan'] = ($keuanganringkas['kenaikan_2021'] / $keuanganringkas['data_2020']) * 100;
                } else {
                    $keuanganringkas['persen_kenaikan'] = 0;
                }

                if ($keuanganringkas['kenaikan_2021'] > $initialmaterialityom) {
                    $keuanganringkas['M/TM'] = 'M';
                } elseif ($keuanganringkas['kenaikan_2021'] < $initialmaterialityom) {
                    $keuanganringkas['M/TM'] = 'TM';
                } else {
                    $keuanganringkas['M/TM'] = '-';
                }

                //2022
                
                $keuanganringkas['kenaikan_2022'] = $keuanganringkas['data_au_2022'] - $keuanganringkas['data_2021'];
                
                if ($keuanganringkas['data_2021'] != 0) {
                    $keuanganringkas['persen_kenaikan_2022'] = ($keuanganringkas['kenaikan_2022'] / $keuanganringkas['data_2021']) * 100;
                } else {
                    $keuanganringkas['persen_kenaikan_2022'] = 0;
                }

                if ($keuanganringkas['kenaikan_2022'] > $initialmaterialityom) {
                    $keuanganringkas['M/TM_2022'] = 'M';
                } elseif ($keuanganringkas['kenaikan_2022'] < $initialmaterialityom) {
                    $keuanganringkas['M/TM_2022'] = 'TM';
                } else {
                    $keuanganringkas['M/TM_2022'] = '-';
                }
                

                $index[] = $keuanganringkas;
            }
            $ca = FinancialStatement::where('project_id', $project_id)->whereIn('cn', ['CA', 'NCA', 'CL', 'NCL'])->get(['cn', 'prior_period2', 'prior_period', 'inhouse', 'audited',
            'jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec']);
            
            $nca = $ca->where('cn', 'NCA');
            $ca = $ca->where('cn', 'CA');
            $cl = $ca->where('cn', 'CL');
            $ncl = $ca->where('cn', 'NCL');

            $total_ca_2020 = $ca->sum('prior_period2');
            $total_nca_2020 = $nca->sum('prior_period2');
            $total_cl_2020 = $cl->sum('prior_period2');
            $total_ncl_2020 = $ncl->sum('prior_period2');

            $total_ca_2021 = $ca->sum('prior_period');
            $total_nca_2021 = $nca->sum('prior_period');
            $total_cl_2021 = $cl->sum('prior_period');
            $total_ncl_2021 = $ncl->sum('prior_period');

            $total_ca_in_2022 = $ca->sum('inhouse');
            $total_nca_in_2022 = $nca->sum('inhouse');
            $total_cl_in_2022 = $cl->sum('inhouse');
            $total_ncl_in_2022 = $ncl->sum('inhouse');

            $total_ca_au_2021 = $ca->sum('audited');
            $total_nca_au_2021 = $nca->sum('audited');
            $total_cl_au_2021 = $cl->sum('audited');
            $total_ncl_au_2021 = $ncl->sum('audited');

            $total_ca_januari = $ca->sum('jan');
            $total_ca_februari = $ca->sum('feb');
            $total_ca_maret = $ca->sum('mar');
            $total_ca_april = $ca->sum('apr');
            $total_ca_mei = $ca->sum('may');
            $total_ca_juni = $ca->sum('jun');
            $total_ca_juli = $ca->sum('jul');
            $total_ca_agustus = $ca->sum('aug');
            $total_ca_september = $ca->sum('sep');
            $total_ca_oktober = $ca->sum('oct');
            $total_ca_november = $ca->sum('nov');
            $total_ca_desember = $ca->sum('dec');

            $total_nca_januari = $nca->sum('jan');
            $total_nca_februari = $nca->sum('feb');
            $total_nca_maret = $nca->sum('mar');
            $total_nca_april = $nca->sum('apr');
            $total_nca_mei = $nca->sum('may');
            $total_nca_juni = $nca->sum('jun');
            $total_nca_juli = $nca->sum('jul');
            $total_nca_agustus = $nca->sum('aug');
            $total_nca_september = $nca->sum('sep');
            $total_nca_oktober = $nca->sum('oct');
            $total_nca_november = $nca->sum('nov');
            $total_nca_desember = $nca->sum('dec');

            $total_cl_januari = $cl->sum('jan');
            $total_cl_februari = $cl->sum('feb');
            $total_cl_maret = $cl->sum('mar');
            $total_cl_april = $cl->sum('apr');
            $total_cl_mei = $cl->sum('may');
            $total_cl_juni = $cl->sum('jun');
            $total_cl_juli = $cl->sum('jul');
            $total_cl_agustus = $cl->sum('aug');
            $total_cl_september = $cl->sum('sep');
            $total_cl_oktober = $cl->sum('oct');
            $total_cl_november = $cl->sum('nov');
            $total_cl_desember = $cl->sum('dec');

            $total_ncl_januari = $nca->sum('jan');
            $total_ncl_februari = $nca->sum('feb');
            $total_ncl_maret = $nca->sum('mar');
            $total_ncl_april = $nca->sum('apr');
            $total_ncl_mei = $nca->sum('may');
            $total_ncl_juni = $nca->sum('jun');
            $total_ncl_juli = $nca->sum('jul');
            $total_ncl_agustus = $nca->sum('aug');
            $total_ncl_september = $nca->sum('sep');
            $total_ncl_oktober = $nca->sum('oct');
            $total_ncl_november = $nca->sum('nov');
            $total_ncl_desember = $nca->sum('dec');

            $summary_2020 = 
            [
                'CA' => $total_ca_2020,
                'NCA' => $total_nca_2020,
                'CL' => $total_cl_2020,
                'NCL' => $total_ncl_2020,
            ];
            

            $summary_2021 = 
            [
                'CA' => $total_ca_2021,
                'NCA' => $total_nca_2021,
                'CL' => $total_cl_2021,
                'NCL' => $total_ncl_2021,
            ];

            $summary_in_2022 = 
            [
                'CA' => $total_ca_in_2022,
                'NCA' => $total_nca_in_2022,
                'CL' => $total_cl_in_2022,
                'NCL' => $total_ncl_in_2022,
            ];

            $summary_au_2022 = 
            [
                'CA' => $total_ca_au_2021,
                'NCA' => $total_nca_au_2021,
                'CL' => $total_cl_au_2021,
                'NCL' => $total_ncl_au_2021,
            ];

            $summary_jan = 
            [
                'CA' => $total_ca_januari,
                'NCA' => $total_nca_januari,
                'CL' => $total_cl_januari,
                'NCL' => $total_ncl_januari,
            ];

            $summary_feb = 
            [
                'CA' => $total_ca_februari,
                'NCA' => $total_nca_februari,
                'CL' => $total_cl_februari,
                'NCL' => $total_ncl_februari,
            ];

            $summary_mar = 
            [
                'CA' => $total_ca_maret,
                'NCA' => $total_nca_maret,
                'CL' => $total_cl_maret,
                'NCL' => $total_ncl_maret,
            ];

            $summary_apr = 
            [
                'CA' => $total_ca_april,
                'NCA' => $total_nca_april,
                'CL' => $total_cl_april,
                'NCL' => $total_ncl_april,
            ];

            $summary_may = 
            [
                'CA' => $total_ca_mei,
                'NCA' => $total_nca_mei,
                'CL' => $total_cl_mei,
                'NCL' => $total_ncl_mei,
            ];

            $summary_jun = 
            [
                'CA' => $total_ca_juni,
                'NCA' => $total_nca_juni,
                'CL' => $total_cl_juni,
                'NCL' => $total_ncl_juni,
            ];

            $summary_jul = 
            [
                'CA' => $total_ca_juli,
                'NCA' => $total_nca_juli,
                'CL' => $total_cl_juli,
                'NCL' => $total_ncl_juli,
            ];

            $summary_aug = 
            [
                'CA' => $total_ca_agustus,
                'NCA' => $total_nca_agustus,
                'CL' => $total_cl_agustus,
                'NCL' => $total_ncl_agustus,
            ];

            $summary_sep = 
            [
                'CA' => $total_ca_september,
                'NCA' => $total_nca_september,
                'CL' => $total_cl_september,
                'NCL' => $total_ncl_september,
            ];

            $summary_oct = 
            [
                'CA' => $total_ca_oktober,
                'NCA' => $total_nca_oktober,
                'CL' => $total_cl_oktober,
                'NCL' => $total_ncl_oktober,
            ];

            $summary_nov = 
            [
                'CA' => $total_ca_november,
                'NCA' => $total_nca_november,
                'CL' => $total_cl_november,
                'NCL' => $total_ncl_november,
            ];

            $summary_dec = 
            [
                'CA' => $total_ca_desember,
                'NCA' => $total_nca_desember,
                'CL' => $total_cl_desember,
                'NCL' => $total_ncl_desember,
            ];

            $data_cn = ProjectTask::$cn;

            foreach($data_cn as $a => $b)
            {
                $summarycn['kode']    = $a;
                $summarycn['akun']    = $b;
                $summarycn['data_2020']    =  isset($summary_2020[$a]) ? $summary_2020[$a] : 0;
                $summarycn['data_2021']    =  isset($summary_2021[$a]) ? $summary_2021[$a] : 0;
                $summarycn['data_in_2022']    =  isset($summary_in_2022[$a]) ? $summary_in_2022[$a] : 0;
                $summarycn['data_au_2022']    =  isset($summary_au_2022[$a]) ? $summary_au_2022[$a] : 0;
                $summarycn['januari']    =  isset($summary_jan[$a]) ? $summary_jan[$a] : 0;
                $summarycn['februari']    =  isset($summary_feb[$a]) ? $summary_feb[$a] : 0;
                $summarycn['maret']    =  isset($summary_mar[$a]) ? $summary_mar[$a] : 0;
                $summarycn['april']    =  isset($summary_apr[$a]) ? $summary_apr[$a] : 0;
                $summarycn['mei']    =  isset($summary_may[$a]) ? $summary_may[$a] : 0;
                $summarycn['juni']    =  isset($summary_jun[$a]) ? $summary_jun[$a] : 0;
                $summarycn['juli']    =  isset($summary_jul[$a]) ? $summary_jul[$a] : 0;
                $summarycn['agustus']    =  isset($summary_aug[$a]) ? $summary_aug[$a] : 0;
                $summarycn['september']    =  isset($summary_sep[$a]) ? $summary_sep[$a] : 0;
                $summarycn['oktober']    =  isset($summary_oct[$a]) ? $summary_oct[$a] : 0;
                $summarycn['november']    =  isset($summary_nov[$a]) ? $summary_nov[$a] : 0;
                $summarycn['desember']    =  isset($summary_dec[$a]) ? $summary_dec[$a] : 0;

                $cn[] = $summarycn;

            }


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
             
            return view('project_task.keuanganringkas', compact(
                'task','project','materialitas','financial_statement','index','result','cn'
            ));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
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

            $summary_rasio_keuangan = [];
            
            $financial_statement = FinancialStatement::where('project_id', $project_id)->whereIn('cn', ['CA', 'NCA', 'CL', 'NCL'])->get(['cn', 'prior_period2', 'prior_period', 'inhouse', 'audited',
            'jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec']);

            //data persediaan
            $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', 'LK.7')->get();
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
            $data_lk_pendapatan = FinancialStatement::where('project_id', $project_id)->where('lk', '=', 'LK.30')->get();
            $data_pendapatan_2020 = $data_lk_pendapatan->pluck('prior_period2')->toArray();
            $total_pendapatan_2020 = array_sum($data_pendapatan_2020);
            $data_pendapatan_2021 = $data_lk_pendapatan->pluck('prior_period')->toArray();
            $total_pendapatan_2021 = array_sum($data_pendapatan_2021);
            $data_pendapatan_in_2022 = $data_lk_pendapatan->pluck('inhouse')->toArray();
            $total_pendapatan_in_2022 = array_sum($data_pendapatan_in_2022);
            $data_pendapatan_au_2022 = $data_lk_pendapatan->pluck('audited')->toArray();
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

            $total_aset_2020 = 0;
            $total_aset_2021 = 0;
            $total_aset_in_2022 = 0;
            $total_aset_au_2022 = 0;
            
            for ($i = 1; $i <= 15; $i++) {
                $m = 'LK.' . $i;
                $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

                $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
                $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
                
                $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
                $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
                
                $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
                $total_in_2022[$m] = array_sum($data_in_2022[$m]);
                
                $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
                $total_au_2022[$m] = array_sum($data_au_2022[$m]);

                $total_aset_2020 += $total_lk_2020[$m];
                $total_aset_2021 += $total_lk_2021[$m];
                $total_aset_in_2022 += $total_in_2022[$m];
                $total_aset_au_2022 += $total_au_2022[$m];
            }


            //Mencari nilai total liabitias
            $total_liabilitas_2020 = 0;
            $total_liabilitas_2021 = 0;
            $total_liabilitas_in_2022 = 0;
            $total_liabilitas_au_2022 = 0;
            
            for ($i = 16; $i <= 25; $i++) {
                $m = 'LK.' . $i;
                $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

                $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
                $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
                
                $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
                $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
                
                $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
                $total_in_2022[$m] = array_sum($data_in_2022[$m]);
                
                $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
                $total_au_2022[$m] = array_sum($data_au_2022[$m]);
                

                $total_liabilitas_2020 += $total_lk_2020[$m];
                $total_liabilitas_2021 += $total_lk_2021[$m];
                $total_liabilitas_in_2022 += $total_in_2022[$m];
                $total_liabilitas_au_2022 += $total_au_2022[$m];
            }

            //Mencari nilai total ekuitas
            $total_ekuitas_2020 = 0;
            $total_ekuitas_2021 = 0;
            $total_ekuitas_in_2022 = 0;
            $total_ekuitas_au_2022 = 0;
            
            for ($i = 26; $i <= 29; $i++) {
                $m = 'LK.' . $i;
                $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

                $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
                $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
                $data_lk_2021[$m] = $data_lk->pluck('prior_period2')->toArray();
                $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
                $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
                $total_in_2022[$m] = array_sum($data_in_2022[$m]);
                $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
                $total_au_2022[$m] = array_sum($data_au_2022[$m]);
                

                $total_ekuitas_2020 += $total_lk_2020[$m];
                $total_ekuitas_2021 += $total_lk_2021[$m];
                $total_ekuitas_in_2022 += $total_in_2022[$m];
                $total_ekuitas_au_2022 += $total_au_2022[$m];
            }

            //Mencari nilai total laba kotor
            $total_laba_kotor_2020 = 0;
            $total_laba_kotor_2021 = 0;
            $total_laba_kotor_in_2022 = 0;
            $total_laba_kotor_au_2022 = 0;
            
            for ($i = 30; $i <= 31; $i++) {
                $m = 'LK.' . $i;
                $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

                $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
                $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
                $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
                $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
                $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
                $total_in_2022[$m] = array_sum($data_in_2022[$m]);
                $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
                $total_au_2022[$m] = array_sum($data_au_2022[$m]);

                $total_laba_kotor_2020 += $total_lk_2020[$m];
                $total_laba_kotor_2021 += $total_lk_2021[$m];
                $total_laba_kotor_in_2022 += $total_in_2022[$m];
                $total_laba_kotor_au_2022 += $total_au_2022[$m];
            }

            //Mencari nilai total laba bersih setelah pajak
            $total_laba_bersih_setelah_pajak_2020 = 0;
            $total_laba_bersih_setelah_pajak_2021 = 0;
            $total_laba_bersih_setelah_pajak_in_2022 = 0;
            $total_laba_bersih_setelah_pajak_au_2022 = 0;
            
            $i = 35;
            $m = 'LK.' . $i;
            $data_lk = FinancialStatement::where('project_id', $project_id)->where('lk', '=', $m)->get();

            $data_lk_2020[$m] = $data_lk->pluck('prior_period2')->toArray();
            $total_lk_2020[$m] = array_sum($data_lk_2020[$m]);
            $data_lk_2021[$m] = $data_lk->pluck('prior_period')->toArray();
            $total_lk_2021[$m] = array_sum($data_lk_2021[$m]);
            $data_in_2022[$m] = $data_lk->pluck('inhouse')->toArray();
            $total_in_2022[$m] = array_sum($data_in_2022[$m]);
            $data_au_2022[$m] = $data_lk->pluck('audited')->toArray();
            $total_au_2022[$m] = array_sum($data_au_2022[$m]);

            $total_laba_bersih_setelah_pajak_2020 += $total_lk_2020[$m];
            $total_laba_bersih_setelah_pajak_2021 += $total_lk_2021[$m];
            $total_laba_bersih_setelah_pajak_in_2022 += $total_in_2022[$m];
            $total_laba_bersih_setelah_pajak_au_2022 += $total_au_2022[$m];
            


            //perhitungan current ratio
            $current_ratio_2020 = ($total_cl_2020 != 0) ? $total_ca_2020 / $total_cl_2020 : 0;
            $current_ratio_2021 = ($total_cl_2021 != 0) ? $total_ca_2021 / $total_cl_2021 : 0;
            $current_ratio_in_2022 = ($total_cl_in_2022 != 0) ? $total_ca_in_2022 / $total_cl_in_2022 : 0;
            $current_ratio_au_2022 = ($total_cl_au_2022 != 0) ? $total_ca_au_2022 / $total_cl_au_2022 : 0;

            //perhitungan quick ratio
            $quick_ratio_2020 = ($total_cl_2020 != 0) ? ($total_ca_2020 - $total_persediaan_2020) / $total_cl_2020 : 0;
            $quick_ratio_2021 = ($total_cl_2021 != 0) ? ($total_ca_2021 - $total_persediaan_2021) / $total_cl_2021 : 0;
            $quick_ratio_in_2022 = ($total_cl_in_2022 != 0) ? ($total_ca_in_2022 - $total_persediaan_in_2022) / $total_cl_in_2022 : 0;
            $quick_ratio_au_2022 = ($total_cl_au_2022 != 0) ? ($total_ca_au_2022 - $total_persediaan_au_2022) / $total_cl_au_2022 : 0;

            //perhitungan cash ratio
            $cash_ratio_2020 = ($total_cl_2020 != 0) ? $total_kas_setara_kas_2020 / $total_cl_2020 : 0;
            $cash_ratio_2021 = ($total_cl_2021 != 0) ? $total_kas_setara_kas_2021 / $total_cl_2021 : 0;
            $cash_ratio_in_2022 = ($total_cl_in_2022 != 0) ? $total_kas_setara_kas_in_2022 / $total_cl_in_2022 : 0;
            $cash_ratio_au_2022 = ($total_cl_au_2022 != 0) ? $total_kas_setara_kas_au_2022 / $total_cl_au_2022 : 0;

            //perhitungan debt to asset ratio
            $detara_2020 = ($total_aset_2020 != 0) ? $total_liabilitas_2020 / $total_aset_2020 : 0;
            $detara_2021 = ($total_aset_2021 != 0) ? $total_liabilitas_2021 / $total_aset_2021 : 0;
            $detara_in_2022 = ($total_aset_in_2022 != 0) ? $total_liabilitas_in_2022 / $total_aset_in_2022 : 0;
            $detara_au_2022 = ($total_aset_au_2022 != 0) ? $total_liabilitas_au_2022 / $total_aset_au_2022 : 0;

            //perhitungan debt to equity ratio
            $detera_2020 = ($total_ekuitas_2020 != 0) ? $total_liabilitas_2020 / $total_ekuitas_2020 : 0;
            $detera_2021 = ($total_ekuitas_2021 != 0) ? $total_liabilitas_2021 / $total_ekuitas_2021 : 0;
            $detera_in_2022 = ($total_ekuitas_in_2022 != 0) ? $total_liabilitas_in_2022 / $total_ekuitas_in_2022 : 0;
            $detera_au_2022 = ($total_ekuitas_au_2022 != 0) ? $total_liabilitas_au_2022 / $total_ekuitas_au_2022 : 0;

            //perhitungan total asset turnover ratio
            $tatura_2020 = ($total_aset_2020 != 0) ? $total_pendapatan_2020 / $total_aset_2020 : 0;
            $tatura_2021 = ($total_aset_2021 != 0) ? $total_pendapatan_2021 / $total_aset_2021 : 0;
            $tatura_in_2022 = ($total_aset_in_2022 != 0) ? $total_pendapatan_in_2022 / $total_aset_in_2022 : 0;
            $tatura_au_2022 = ($total_aset_au_2022 != 0) ? $total_pendapatan_au_2022 / $total_aset_au_2022 : 0;

            //perhitungan receivable turnover ratio
            $piutang_usaha_2020 = $total_berelasi_2020 + $total_ketiga_2020;
            $retura_2020 =   ($piutang_usaha_2020 != 0) ? $total_pendapatan_2020 / $piutang_usaha_2020 : 0;
            $piutang_usaha_2021 = ($total_berelasi_2020 + $total_ketiga_2020 + $total_berelasi_2021 + $total_ketiga_2021) / 2;
            $retura_2021 =   ($piutang_usaha_2021 != 0) ? $total_pendapatan_2021 / $piutang_usaha_2021 : 0;
            $piutang_usaha_in_2022 = ($total_berelasi_2021 + $total_ketiga_2021 + $total_berelasi_in_2022 + $total_ketiga_in_2022) / 2;
            $retura_in_2022 =   ($piutang_usaha_in_2022 != 0) ? $total_pendapatan_in_2022 / $piutang_usaha_in_2022 : 0;
            $piutang_usaha_au_2022 = ($total_berelasi_in_2022 + $total_ketiga_in_2022 + $total_berelasi_au_2022 + $total_ketiga_au_2022) / 2;
            $retura_au_2022 =   ($piutang_usaha_au_2022 != 0) ? $total_pendapatan_au_2022 / $piutang_usaha_au_2022 : 0;

            //perhitungan receivable turnover ratio (hari)
            $retura_hari_2020 =   ($retura_2020 != 0) ? 365 / $retura_2020 : 0;
            $retura_hari_2021 =   ($retura_2021 != 0) ? 365 / $retura_2021 : 0;
            $retura_hari_in_2022 =   ($retura_in_2022 != 0) ? 365 / $retura_in_2022 : 0;
            $retura_hari_au_2022 =   ($retura_au_2022 != 0) ? 365 / $retura_au_2022 : 0;

            //perhitungan inventory turnover ratio
            $intura_2020 = ($total_persediaan_2020 != 0) ? $total_pendapatan_2020 / $total_persediaan_2020 : 0;
            $persediaan_2021 = ($total_persediaan_2020 + $total_persediaan_2021) / 2;
            $intura_2021 = ($persediaan_2021 != 0) ? $total_pendapatan_2021 / $persediaan_2021 : 0;
            $persediaan_in_2022 = ($total_persediaan_2021 + $total_persediaan_in_2022) / 2;
            $intura_in_2022 = ($persediaan_in_2022 != 0) ? $total_pendapatan_in_2022 / $persediaan_in_2022 : 0;
            $persediaan_au_2022 = ($total_persediaan_in_2022 + $total_persediaan_au_2022) / 2;
            $intura_au_2022 = ($persediaan_au_2022 != 0) ? $total_pendapatan_au_2022 / $persediaan_au_2022 : 0;

            //perhitungan gross profit margin
            $gpm_2020 = ($total_pendapatan_2020 != 0) ? $total_laba_kotor_2020 / $total_pendapatan_2020 : 0;
            $gpm_2021 = ($total_pendapatan_2021 != 0) ? $total_laba_kotor_2021 / $total_pendapatan_2021 : 0;
            $gpm_in_2022 = ($total_pendapatan_in_2022 != 0) ? $total_laba_kotor_in_2022 / $total_pendapatan_in_2022 : 0;
            $gpm_au_2022 = ($total_pendapatan_au_2022 != 0) ? $total_laba_kotor_au_2022 / $total_pendapatan_au_2022 : 0;

            //perhitungan operating profit margin
            $opm_2020 = ($total_pendapatan_2020 != 0) ? $total_laba_bersih_setelah_pajak_2020 / $total_pendapatan_2020 : 0;
            $opm_2021 = ($total_pendapatan_2021 != 0) ? $total_laba_bersih_setelah_pajak_2021 / $total_pendapatan_2021 : 0;
            $opm_in_2022 = ($total_pendapatan_in_2022 != 0) ? $total_laba_bersih_setelah_pajak_in_2022 / $total_pendapatan_in_2022 : 0;
            $opm_au_2022 = ($total_pendapatan_au_2022 != 0) ? $total_laba_bersih_setelah_pajak_au_2022 / $total_pendapatan_au_2022 : 0;

            //perhitungan net profit margin
            $npm_2020 = ($total_pendapatan_2020 != 0) ? $total_laba_bersih_setelah_pajak_2020 / $total_pendapatan_2020 : 0;
            $npm_2021 = ($total_pendapatan_2021 != 0) ? $total_laba_bersih_setelah_pajak_2021 / $total_pendapatan_2021 : 0;
            $npm_in_2022 = ($total_pendapatan_in_2022 != 0) ? $total_laba_bersih_setelah_pajak_in_2022 / $total_pendapatan_in_2022 : 0;
            $npm_au_2022 = ($total_pendapatan_au_2022 != 0) ? $total_laba_bersih_setelah_pajak_au_2022 / $total_pendapatan_au_2022 : 0;

            //perhitungan return on asset
            $roa_2020 = ($total_aset_2020 != 0) ? $total_laba_bersih_setelah_pajak_2020 / $total_aset_2020 : 0;
            $roa_2021 = ($total_aset_2021 != 0) ? $total_laba_bersih_setelah_pajak_2021 / $total_aset_2021 : 0;
            $roa_in_2022 = ($total_aset_in_2022 != 0) ? $total_laba_bersih_setelah_pajak_in_2022 / $total_aset_in_2022 : 0;
            $roa_au_2022 = ($total_aset_au_2022 != 0) ? $total_laba_bersih_setelah_pajak_au_2022 / $total_aset_au_2022 : 0;

            //perhitungan return on equity
            $roe_2020 = ($total_ekuitas_2020 != 0) ? $total_laba_bersih_setelah_pajak_2020 / $total_ekuitas_2020 : 0;
            $roe_2021 = ($total_ekuitas_2021 != 0) ? $total_laba_bersih_setelah_pajak_2021 / $total_ekuitas_2021 : 0;
            $roe_in_2022 = ($total_ekuitas_in_2022 != 0) ? $total_laba_bersih_setelah_pajak_in_2022 / $total_ekuitas_in_2022 : 0;
            $roe_au_2022 = ($total_ekuitas_au_2022 != 0) ? $total_laba_bersih_setelah_pajak_au_2022 / $total_ekuitas_au_2022 : 0;



            $summary_2020 = 
            [
                'CURA' => $current_ratio_2020,
                'QURA' => $quick_ratio_2020,
                'CARA' => $cash_ratio_2020,
                'DETARA' => $detara_2020,
                'DETERA' => $detera_2020,
                'TATURA' => $tatura_2020,
                'RETURA' => $retura_2020,
                'RETURAH' => $retura_hari_2020,
                'INTURA' => $intura_2020,
                'GPM' => $gpm_2020,
                'OPM' => $opm_2020,
                'NPM' => $npm_2020,
                'ROA' => $roa_2020,
                'ROE' => $roe_2020,
            ];
            
            $summary_2021 = 
            [
                'CURA' => $current_ratio_2021,
                'QURA' => $quick_ratio_2021,
                'CARA' => $cash_ratio_2021,
                'DETARA' => $detara_2021,
                'DETERA' => $detera_2021,
                'TATURA' => $tatura_2021,
                'RETURA' => $retura_2021,
                'RETURAH' => $retura_hari_2021,
                'INTURA' => $intura_2021,
                'GPM' => $gpm_2021,
                'OPM' => $opm_2021,
                'NPM' => $npm_2021,
                'ROA' => $roa_2021,
                'ROE' => $roe_2021,
            ];

            $summary_in_2022 = 
            [
                'CURA' => $current_ratio_in_2022,
                'QURA' => $quick_ratio_in_2022,
                'CARA' => $cash_ratio_in_2022,
                'DETARA' => $detara_in_2022,
                'DETERA' => $detera_in_2022,
                'TATURA' => $tatura_in_2022,
                'RETURA' => $retura_in_2022,
                'RETURAH' => $retura_hari_in_2022,
                'INTURA' => $intura_in_2022,
                'GPM' => $gpm_in_2022,
                'OPM' => $opm_in_2022,
                'NPM' => $npm_in_2022,
                'ROA' => $roa_in_2022,
                'ROE' => $roe_in_2022,
            ];

            $summary_au_2022 = 
            [
                'CURA' => $current_ratio_au_2022,
                'QURA' => $quick_ratio_au_2022,
                'CARA' => $cash_ratio_au_2022,
                'DETARA' => $detara_au_2022,
                'DETERA' => $detera_au_2022,
                'TATURA' => $tatura_au_2022,
                'RETURA' => $retura_au_2022,
                'RETURAH' => $retura_hari_au_2022,
                'INTURA' => $intura_au_2022,
                'GPM' => $gpm_au_2022,
                'OPM' => $opm_au_2022,
                'NPM' => $npm_au_2022,
                'ROA' => $roa_au_2022,
                'ROE' => $roe_au_2022,
            ];

            $data_summary = ProjectTask::$rasio_keuangan;

            foreach($data_summary as $a => $b)
            {
                $summaryrasiokeuangan['kode']    = $a;
                $summaryrasiokeuangan['akun']    = $b;
                $summaryrasiokeuangan['data_2020']    =  isset($summary_2020[$a]) ? $summary_2020[$a] : 0;
                $summaryrasiokeuangan['data_2021']    =  isset($summary_2021[$a]) ? $summary_2021[$a] : 0;
                $summaryrasiokeuangan['data_in_2022']    =  isset($summary_in_2022[$a]) ? $summary_in_2022[$a] : 0;
                $summaryrasiokeuangan['data_au_2022']    =  isset($summary_au_2022[$a]) ? $summary_au_2022[$a] : 0;

                $summary_rasio_keuangan[] = $summaryrasiokeuangan;

            }

            return view('project_task.rasiokeuangan', compact('project','task','summary_rasio_keuangan'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}