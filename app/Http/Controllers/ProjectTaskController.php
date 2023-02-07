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
                $taskss      = ProjectTask::where('project_id', '=', $project_id)->get();
                if(!empty($request->category_template_id))
                {
                    $tasks = $taskss->where('category_template_id', '=', $request->category_template_id);         
                }elseif($request->category_template_id = 'All')
                {
                    $tasks = ProjectTask::where('project_id', '=', $project_id)->get();
                }

                return view('project_task.index', compact('tasks','category_template_id','project'));
            }
            elseif($usr->type == 'company')
            {
                $category_template_id       = CategoryTemplate::all()->pluck('name', 'id');
                $category_template_id->prepend('All', '');
                $project    = Project::find($project_id);
                $taskss      = ProjectTask::where('project_id', '=', $project_id)->get();
                if(!empty($request->category_template_id))
                {
                    $tasks = $taskss->where('category_template_id', '=', $request->category_template_id);         
                }elseif($request->category_template_id = 'All')
                {
                    $tasks = ProjectTask::where('project_id', '=', $project_id)->get();
                }

                return view('project_task.index', compact('tasks','category_template_id','project'));
            }
            else
            {
                $category_template_id       = CategoryTemplate::all()->pluck('name', 'id');
                $category_template_id->prepend('All', '');
                $project    = Project::find($project_id);
                $taskss      = ProjectTask::where('project_id', '=', $project_id)->get();
                if(!empty($request->category_template_id))
                {
                    $tasks = $taskss->where('category_template_id', '=', $request->category_template_id);         
                }elseif($request->category_template_id = 'All')
                {
                    $tasks = ProjectTask::where('project_id', '=', $project_id)->get();
                }
                
                return view('project_task.index', compact('tasks','category_template_id','project'));
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
            $post['start_date']=date("Y-m-d H:i:s", strtotime($request->start_date));
            $post['end_date']=date("Y-m-d H:i:s", strtotime($request->end_date));
            $post['category_template_id'] = $request->category_template_id;
            $task = ProjectTask::create($post);

            //Make entry in activity log
            ActivityLog::create(
                [
                    'user_id' => $usr->id,
                    'project_id' => $project_id,
                    'task_id' => $task->id,
                    'log_type' => 'Create Task',
                    'remark' => json_encode(['title' => $task->name]),
                ]
            );


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
                                'name' => 'required',
                                'estimated_hrs' => 'required',
                                'priority' => 'required',
                            ]
            );

            if($validator->fails())
            {
                return redirect()->back()->with('error', Utility::errorFormat($validator->getMessageBag()));
            }

            $post = $request->all();
            $task = ProjectTask::find($task_id);
            $task->update($post);

            if($task->stage_id == 4)
            {
                $task->is_complete = 1;
            }

            $task->save();

            return redirect()->back()->with('success', __('Task Updated successfully.'));
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

                //Email Notification
            $users = ProjectUser::where('project_id', $projectID)->whereNotIn('user_id',[\Auth::user()->id])->get();
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

            return redirect()->back()->with('success', __(' Sub Task Updated successfully.'));
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
}
