<?php

namespace App\Http\Controllers;

use App\Models\TimeTracker;
use App\Models\Timesheet;
use App\Models\UserOvertime;
use App\Models\User;
use App\Models\AuditPlan;
use App\Models\ProjectOfferings;
use App\Models\Project;
use App\Models\El;
use App\Models\Utility;
use App\Models\Bug;
use App\Models\BugStatus;
use App\Models\LabelProject;
use App\Models\BugFile;
use App\Models\BugComment;
use App\Models\CategoryTemplate;
use App\Models\Milestone;
use App\Models\ProjectTaskTemplate;
use App\Models\AttendanceEmployee;
use App\Models\PublicAccountant;
use App\Models\AppraisalEmployee;
use App\Models\ProductServiceCategory;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProjectNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use App\Models\ActivityLog;
use DateTime;
use DatePeriod;
use DateInterval;
use App\Models\ProjectTask;
use App\Models\Notification;
use App\Models\ProjectUser;
use App\Models\TaskStage;
use App\Models\InvoiceProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Mail\InviteMemberNotification;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($view = 'list')
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
            $users   = User::where('type', '!=', 'client')->where('type', '!=', 'admin')->get()->pluck('name', 'id');
            $clients = User::where('type', '=', 'client')->get()->pluck('name', 'id');
            $tasktemplate = CategoryTemplate::get()->pluck('name', 'id');
            $public_accountant = PublicAccountant::get()->pluck('name', 'id');
            
            // Get service types from database instead of static array
            $service_types = LabelProject::getActiveServiceTypes();
            
            $public_accountant->prepend('Select Partner', '');
            $clients->prepend('Select Client', '');
            $users->prepend('Select User', '');
            $tasktemplate->prepend('Select Task Template', '');
            $service_types->prepend('Select Project Service', '');

            return view('projects.create', compact('clients','users','public_accountant','tasktemplate','service_types'));
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

            $project->end_date = date("Y-m-d H:i:s", strtotime($request->end_date));

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
            $project->total_days = $request->total_days;
            $project->book_year = $request->book_year;
            $project->tags = $request->tag;
            $project->label = $request->label;
            $project->created_by = \Auth::user()->creatorId();
            $project->save();


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

                $notificationData = [
                    'user_id' => $value,
                    'type' => 'create_project',
                    'data' => json_encode([
                        'updated_by' => Auth::user()->id,
                        'project_id' => $project->id, // Update with the correct field from your data
                        'name' => $project->project_name,
                    ]),
                    'is_read' => false,
                ];
    
                Notification::create($notificationData);

                
                // $response = curl_exec($ch);
                $datas = User::where('id', $value)->pluck('email');
                // Mail::to($datas)->send(new ProjectNotification($project));
              }
            }

            
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
                    $tasks                 = new ProjectTask();
                    $tasks->project_id     = $project->id;
                    $tasks->assign_to      = 0;
                    $tasks->stage_id       =  $details[$i]['stage_id'];
                    $tasks->name           = $details[$i]['name'];
                    $tasks->category_template_id      =  $details[$i]['category_template_id'];
                    $tasks->start_date     = '';
                    $tasks->end_date       = '';
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
    public function show($ids)
    {

        if(\Auth::user()->can('view project'))
        {

            try {
                $id = Crypt::decrypt($ids);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Project Not Found.'));
            }

            $project = Project::find($id);

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
                $completedTask = ProjectTask::where('project_id',$project->id)->where('stage_id',4)->get();

                $project_done_task    = $completedTask->count();

                $overdueTasks = $tasks->where('end_date', '<', now())->where('stage_id', '!=', 4);
                $activeTasks = $tasks->where('end_date', '>=', now())->where('stage_id','!=', 4);
                $completedTasks = $tasks->where('stage_id', 4);

                $project_data['task'] = [
                    'total' => number_format($project_task),
                    'done' => number_format($project_done_task),
                    'percentage' => Utility::getPercentage($project_done_task, $project_task),
                    'overdue_tasks' => $overdueTasks->count(),
                    'active_tasks' => $activeTasks->count(),
                    'completed_tasks' => $completedTasks->count(),
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
                    'percentage' => Utility::getPercentage($totaltime, $totaltime),
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
                $project_offerings = ProjectOfferings::where('project_id', $project->id)->first();
                $el = El::where('project_id', $project->id)->get();
                $projects = Project::where('created_by', \Auth::user()->creatorId())->get();

                return view('projects.view',compact('project','project_offerings','project_data','auditplan','el','projects'));
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
            $clients = User::where('type', '=', 'client')->get()->pluck('name', 'id');
            $project = Project::findOrfail($project->id);
            $tasktemplate = CategoryTemplate::get()->pluck('name', 'id');
            $public_accountant = PublicAccountant::get()->pluck('name', 'id');
            
            // Get service types from database
            $service_types = LabelProject::getActiveServiceTypes();
            
            $public_accountant->prepend('Select Partner', '');
            $tasktemplate->prepend('Select Task Template', '');
            $service_types->prepend('Select Project Service', '');

            return view('projects.edit', compact('tasktemplate','public_accountant','project', 'clients', 'service_types'));
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
        if (!\Auth::user()->can('edit project')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $validator = \Validator::make(
            $request->all(), [
                'project_name' => 'required',
            ]
        );
        if ($validator->fails()) {
            return redirect()->back()->with('error', Utility::errorFormat($validator->getMessageBag()));
        }

        $oldTemplateTaskId = $project->template_task_id;

        $project->project_name = $request->project_name;
        $project->start_date = date("Y-m-d H:i:s", strtotime($request->start_date));
        $project->end_date = date("Y-m-d H:i:s", strtotime($request->end_date));

        if ($request->hasFile('project_image')) {
            Utility::checkFileExistsnDelete([$project->project_image]);
            $imageName = time() . '.' . $request->project_image->extension();
            $request->file('project_image')->storeAs('projects', $imageName);
            $project->project_image = 'projects/' . $imageName;
        }

        $newTemplateTaskId = intval($request->template_task_id);

        ActivityLog::create([
            'user_id' => \Auth::user()->id,
            'project_id' => $project->id,
            'task_id' => 0,
            'log_type' => 'Update Project',
            'remark' => json_encode(['title' => $project->project_name]),
        ]);

        if ($newTemplateTaskId) {

            $template = CategoryTemplate::find($newTemplateTaskId);

            if ($template) {

                if ($oldTemplateTaskId !== $newTemplateTaskId) {

                    ProjectTask::where('project_id', $project->id)->delete();

                    $tasks = ProjectTaskTemplate::where('category_id', $template->id)->get();

                    foreach ($tasks as $taskDetail) {
                        $newTask = new ProjectTask();
                        $newTask->project_id = $project->id;
                        $newTask->assign_to = 0;
                        $newTask->stage_id = $taskDetail->stage_id;
                        $newTask->name = $taskDetail->name;
                        $newTask->category_template_id = $taskDetail->category_template_id;
                        $newTask->start_date = '';
                        $newTask->end_date = '';
                        $newTask->estimated_hrs = $taskDetail->estimated_hrs;
                        $newTask->description = $taskDetail->description;
                        $newTask->created_by = \Auth::user()->creatorId();
                        $newTask->save();
                    }
                }
            }
        }

        $project->template_task_id = $newTemplateTaskId;
        $project->total_days = $request->total_days;
        $project->budget = $request->total_calculation;
        $project->client_id = $request->client;
        $project->public_accountant_id = $request->public_accountant_id;
        $project->description = $request->description;
        $project->status = $request->status;
        $project->book_year = $request->book_year;
        $project->tags = $request->tag;
        $project->label = $request->label;

        $project->save();

        $projectOfferingData = $request->only([
        'als_partners', 'rate_partners',
        'als_manager', 'rate_manager',
        'als_leader', 'rate_leader',
        'als_senior_associate', 'rate_senior_associate',
        'als_associate', 'rate_associate',
        'als_intern', 'rate_intern'
        ]);

        $projectOffering = ProjectOfferings::firstOrNew(['project_id' => $project->id]);

        foreach ($projectOfferingData as $key => $value) {
            $projectOffering->{$key} = $value;
        }

        $projectOffering->save();

        return redirect()->route('projects.show', \Crypt::encrypt($project->id))->with('success', __('Project Updated Successfully'));
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
            ProjectUser::where('project_id', '=', $project->id)->delete();
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
        $usr = Auth::user();
        $project = Project::find($project_id);

        $user_project = $project->users->pluck('id')->toArray();

        if (\Auth::user()->type == 'admin') {
            $user_contact = User::where('type', '!=', 'client')
                                ->where('is_active', 1)
                                ->whereNotIn('id', $user_project)
                                ->pluck('id')
                                ->toArray();
        } elseif (\Auth::user()->type == 'company') {
            $user_contact = User::where('type', '!=', 'client')
                                ->where('is_active', 1)
                                ->whereNotIn('id', $user_project)
                                ->pluck('id')
                                ->toArray();
        } else {
            $user_contact = User::where('created_by', \Auth::user()->creatorId())
                                ->where('type', '!=', 'client')
                                ->where('is_active', 1)
                                ->whereNotIn('id', $user_project)
                                ->pluck('id')
                                ->toArray();
        }

        $arrUser = array_unique($user_contact);
        $users = User::whereIn('id', $arrUser)->get();

        return view('projects.invite', compact('project_id', 'users'));
    }

    public function timeBudgetView(Request $request, $project_id)
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

        return view('projects.timebudget', compact('project_id', 'users','project'));
    }

    public function addTimeBudget($id, Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                'als_partners' => 'required|numeric',
                'rate_partners' => 'required|numeric',
                'als_manager' => 'required|numeric',
                'rate_manager' => 'required|numeric',
                'als_leader' => 'required|numeric',
                'rate_leader' => 'required|numeric',
                'als_senior_associate' => 'required|numeric',
                'rate_senior_associate' => 'required|numeric',
                'als_associate' => 'required|numeric',
                'rate_associate' => 'required|numeric',
                'als_intern' => 'required|numeric',
                'rate_intern' => 'required|numeric',
            ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        
        $project_offerings = new ProjectOfferings();

        $project = Project::find($id);
        $project->budget = $request->total_calculation;
        $project->estimated_hrs = $request->estimated_hrs;

        $project_offerings->project_id = $id;
        $project_offerings->als_partners = $request->als_partners;
        $project_offerings->rate_partners = $request->rate_partners;
        $project_offerings->als_manager = $request->als_manager;
        $project_offerings->rate_manager = $request->rate_manager;
        $project_offerings->als_leader = $request->als_leader;
        $project_offerings->rate_leader = $request->rate_leader;
        $project_offerings->als_senior_associate = $request->als_senior_associate;
        $project_offerings->rate_senior_associate = $request->rate_senior_associate;
        $project_offerings->als_associate = $request->als_associate;
        $project_offerings->rate_associate = $request->rate_associate;
        $project_offerings->als_intern = $request->als_intern;
        $project_offerings->rate_intern = $request->rate_intern;

        $project_offerings->save();
        $project->save();

        return redirect()->route('projects.index')->with('success', __('Project Time Budget Data Successfully Created'));

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

        Notification::createNotification(
            $request->user_id,
            'project_invitation',
            [
                'project_id' => $request->project_id,
                'project_name' => $project->project_name,
                'invited_by' => $authuser->name,
                'updated_by' => $authuser->id
            ],
            Notification::PRIORITY_NORMAL
        );

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
            $project_users = ProjectUser::where('project_id', $project->id)
                ->with('employee')
                ->get()
                ->pluck('employee.id');

            $reviewedUsers = AppraisalEmployee::where('project_id', $project->id)
            ->whereIn('employee_id', $project_users)->pluck('employee_id')->toArray();
            
            $ratingUsers = AppraisalEmployee::where('project_id', $project->id)
                ->whereIn('employee_id', $project_users)
                ->pluck('rating', 'employee_id')
                ->toArray();

            $ratingUser = [];
            foreach ($ratingUsers as $employee_id => $rating) {
                $ratingUser[$employee_id] = json_decode($rating, true);
            }

            $overallRatings = [];
            foreach ($ratingUser as $employee_id => $ratings) {
                $starsum = !empty($ratings) ? array_sum($ratings) : 0;
                $overallrating = count($ratings) !== 0 ? $starsum / count($ratings) : 0;
                $overallRatings[$employee_id] = $overallrating;
            }
    
            $returnHTML = view('projects.users', compact('project','reviewedUsers','overallRatings'))->render();

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

                if (!empty($request->keyword)) {
                    $projects->where('project_name', 'LIKE', '%' . $request->keyword . '%');
                }                
                if(!empty($request->status))
                {
                    $projects->whereIn('status', $request->status);
                }
                if(!empty($request->tags))
                {
                    $projects->whereIn('tags', $request->tags);
                }
                if(!empty($request->label))
                {
                    $projects->whereIn('label', $request->label);
                }
                $projects = $projects->orderByDesc('id')->paginate(10);
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

    public function tracker($ids, Request $request)
    {

        $id = Crypt::decrypt($ids);

        $treckers = TimeTracker::with('user')->where('project_id',$id);

        if (!empty($request->month)) {
            $month = date('m', strtotime($request->month));
            $year  = date('Y', strtotime($request->month));

            $start_date = date($year . '-' . $month . '-01');
            $end_date   = date($year . '-' . $month . '-t');

            $treckers->whereBetween('start_time', [$start_date, $end_date]);
        } 

        $treckers = $treckers->orderByDesc('id')->paginate(10)->appends([
            'month' => $request->month,
        ]);  

        $project = Project::find($id);

        return view('time_trackers.time_tracker_table',compact('treckers', 'project'));
    }

    public function invoice($ids)
    {
        $id = Crypt::decrypt($ids);
        $invoices = InvoiceProduct::with('invoice')->where('product_id',$id)->get();
        return view('projects.invoice',compact('invoices'));
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

    public function listOvertime(Request $request)
    {
        $user = \Auth::user();
        
        $users = User::where('type', '!=', 'client')->where('type', '!=', 'staff_client')->orderBy('name', 'ASC')->get();
        return view('projects.overtime', compact('users'));
    }

    public function getProjectUsers($projectId) {
        $users = User::whereHas('comments', function ($query) use ($projectId) {
            $query->where('project_id', $projectId);
        })->select('id', 'name')->get();
    
        return response()->json($users);
    }

    public function getLastAI($projectId, Request $request)
    {
        $googleApiKey = env('GOOGLE_AI_API_KEY');

        $projectData = $request->only([
            'running_days', 'total_tasks', 'completed_tasks',
            'pending_tasks', 'overdue_tasks', 'progress_percentage',
            'time_spent', 'overtime_hours'
        ]);

        $prompt = "Kami memiliki data,
            Running Days: {$projectData['running_days']}, Total Tasks: {$projectData['total_tasks']},
            Completed Tasks: {$projectData['completed_tasks']}, In Progress Tasks: {$projectData['pending_tasks']},
            Overdue Tasks: {$projectData['overdue_tasks']}, Progress Percentage: {$projectData['progress_percentage']}%,
            Time Spent: " . json_encode($projectData['time_spent']) . ",
            Overtime Hours: " . json_encode($projectData['overtime_hours']) . ",
            tolong berikan rekomendasi untuk meningkatkan produktivitas dari data tersebut.";

        try {

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$googleApiKey", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Failed to fetch from Google AI API',
                    'message' => $response->body(),
                ], $response->status());
            }

            $data = $response->json();

            return response()->json([
                'cached' => false,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch from Google AI API',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function regenerateAI(Request $request, $projectId)
    {
        $request->validate([
            'prompt' => 'required|string',
        ]);

        $googleApiKey = env('GOOGLE_AI_API_KEY');
        $prompt = $request->input('prompt');

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$googleApiKey", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Failed to fetch from Google AI API',
                    'message' => $response->body(),
                ], $response->status());
            }

            $data = $response->json();

            return response()->json([
                'cached' => false,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch from Google AI API',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function reportData($id)
    {

        $project = Project::findOrFail($id);

        $tasks = ProjectTask::where('project_id', $project->id)->get();
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('stage_id', 4)->count();
        $pendingTasks = $tasks->where('stage_id', 2)->count();
        $overdueTasks = $tasks->where('end_date', '<', now())->where('stage_id', 4)->count();

        $progressPercentage = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

        $startDate = Carbon::parse($project->start_date);
        $today = Carbon::now();
        $runningDays = $startDate->diffInDays($today);

        $teamActivity = [];
        $users = $project->users()->where('type', '!=', 'admin')->distinct()->get();
        foreach ($users as $user) {
            $assignedTasks = $tasks->filter(function ($task) use ($user) {

                $assignedUserIds = array_map('intval', explode(',', $task->assign_to ?? ''));
                return in_array($user->id, $assignedUserIds);

            })->count();

            $teamActivity[] = [
                'name' => $user->name,
                'tasks' => $assignedTasks,
            ];
        }

        $taskDetails = [];
        foreach ($users as $user) {
            $userTasks = $tasks->filter(function ($task) use ($user) {
                $assignedUserIds = array_map('intval', explode(',', $task->assign_to ?? ''));
                return in_array($user->id, $assignedUserIds);
            })->map(function ($task) {
                return [
                    'name' => $task->name,
                    'status' => $task->is_complete ? 'Completed' : ($task->end_date < now() ? 'Overdue' : 'Pending'),
                ];
            })->values()->toArray();

            $taskDetails[] = [
                'name' => $user->name,
                'tasks' => $userTasks,
            ];
        }

        $timeSpent = [];
        foreach ($users as $user) {
            $timesheets = Timesheet::where('project_id', $project->id)
                ->where('created_by', $user->id)
                ->pluck('time')
                ->toArray();

            $totalSeconds = array_sum(array_map(function ($time) {
                list($hours, $minutes, $seconds) = explode(':', $time);
                return ($hours * 3600) + ($minutes * 60) + $seconds;
            }, $timesheets));

            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;

            $formattedTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

            $timeSpent[] = [
                'name' => $user->name,
                'time' => $formattedTime,
            ];
        }

        $overtimeHours = [];
        foreach ($users as $user) {
            $overtime = UserOvertime::where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->sum('total_time');
            $overtimeHours[] = [
                'name' => $user->name,
                'hours' => round($overtime / 3600, 2),
            ];
        }

        $projectData = [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $pendingTasks,
            'overdue_tasks' => $overdueTasks,
            'progress_percentage' => $progressPercentage,
            'time_spent' => $timeSpent,
            'overtime_hours' => $overtimeHours,
            'running_days' => $runningDays,
        ];

        return response()->json([
            'project_overview' => [
                'running_days' => $runningDays,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'overdue_tasks' => $overdueTasks,
            ],
            'progress_percentage' => $progressPercentage,
            'team_activity' => $teamActivity,
            'time_spent' => $timeSpent,
            'overtime_hours' => $overtimeHours,
            'project_data' => $projectData,
            'task_details' => $taskDetails,
        ]);
    }

    /**
     * Filter project activity by time and branch
     */
    public function filterProjectActivity(Request $request)
    {
        if(!\Auth::user()->can('manage project')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $timeFilter = $request->input('time_filter', 'today');
        $branchFilter = $request->input('branch_filter', 'PUSAT'); // Default ke PUSAT
        $page = $request->input('page', 1);
        $perPage = 100;
        $userType = \Auth::user()->type;
        $currentUserId = \Auth::user()->id;

        try {
            // Cache key dengan page
            $cacheKey = "activity_report_{$userType}_{$currentUserId}_{$timeFilter}_{$branchFilter}_{$page}";
            $cacheTime = 180;
            
            if (\Cache::has($cacheKey) && !$request->has('refresh')) {
                $result = \Cache::get($cacheKey);
            } else {
                $result = $this->getOptimizedActivityData($timeFilter, $branchFilter, $userType, $currentUserId, $page, $perPage);
                \Cache::put($cacheKey, $result, $cacheTime);
            }
            
            // Pastikan summary selalu ada dengan default values
            $defaultSummary = [
                'total_users' => 0,
                'total_projects' => 0,
                'active_today' => 0,
                'no_tracker_today' => 0,
                'absent_today' => 0,
                'no_data_today' => 0
            ];
            
            $summary = isset($result['summary']) && is_array($result['summary']) 
                    ? array_merge($defaultSummary, $result['summary'])
                    : $defaultSummary;
            
            $viewData = [
                'groupedActivities' => $result['data'] ?? [],
                'timeFilter' => $timeFilter,
                'branchFilter' => $branchFilter,
                'pagination' => $result['pagination'] ?? [
                    'current_page' => (int)$page,
                    'per_page' => $perPage,
                    'total' => 0,
                    'last_page' => 1,
                    'has_more_pages' => false
                ],
                'totalUsers' => $result['total_users'] ?? 0,
                'summary' => $summary
            ];
            
            if($request->ajax()) {
                $returnHTML = view('projects.activity_filter', $viewData)->render();
                return response()->json([
                    'success' => true,
                    'html' => $returnHTML,
                    'pagination' => $viewData['pagination'],
                    'summary' => $summary
                ]);
            }

            return view('projects.activity_index', $viewData);
            
        } catch (\Exception $e) {
            \Log::error('Activity Filter Error: ' . $e->getMessage());
            
            // Default data untuk error case
            $errorData = [
                'groupedActivities' => [],
                'timeFilter' => $timeFilter,
                'branchFilter' => $branchFilter,
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => $perPage,
                    'total' => 0,
                    'last_page' => 1,
                    'has_more_pages' => false
                ],
                'totalUsers' => 0,
                'summary' => [
                    'total_users' => 0,
                    'total_projects' => 0,
                    'active_today' => 0,
                    'no_tracker_today' => 0,
                    'absent_today' => 0,
                    'no_data_today' => 0
                ],
                'error' => 'Error loading activity data'
            ];
            
            if($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error loading activity data',
                    'html' => view('projects.activity_filter', $errorData)->render()
                ]);
            }
            
            return view('projects.activity_index', $errorData);
        }
    }

    /**
     * Get optimized activity data with pagination
     */
    private function getOptimizedActivityData($timeFilter, $branchFilter, $userType, $currentUserId, $page, $perPage)
    {
        $dateRange = $this->getDateRange($timeFilter);
        
        // Step 1: Get user-project combinations with improved filtering
        $userProjectQuery = $this->buildOptimizedUserProjectQuery($branchFilter, $userType, $currentUserId, $dateRange);
        
        // Total count BEFORE pagination
        $totalCount = $userProjectQuery->count();
        
        // Apply pagination
        $offset = ($page - 1) * $perPage;
        $userProjects = $userProjectQuery->offset($offset)->limit($perPage)->get();
        
        // Step 2: Get activity data only for current page
        $activities = $this->getActivityDataForUsers($userProjects, $dateRange);
        
        // Step 3: Group by branch (using employee branch)
        $groupedData = $this->groupActivitiesByBranchAndUser($activities, $dateRange);
        
        // Step 4: Generate summary
        $summary = $this->generateActivitySummary($activities);
        
        return [
            'data' => $groupedData,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'last_page' => ceil($totalCount / $perPage),
                'has_more_pages' => $page < ceil($totalCount / $perPage),
                'from' => $totalCount > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $totalCount)
            ],
            'total_users' => $totalCount,
            'summary' => $summary
        ];
    }


    /**
     * Get date range based on filter
     */
    private function getDateRange($timeFilter)
    {
        $today = \Carbon\Carbon::now();
        
        switch($timeFilter) {
            case 'today':
                $startDate = $today->copy();
                $endDate = $today->copy();
                break;
            case '7days':
                // 7 hari yang lalu dari hari ini (termasuk hari ini)
                $startDate = $today->copy()->subDays(6); // 6 hari yang lalu + hari ini = 7 hari
                $endDate = $today->copy();
                break;
            case '1month':
                // CORRECTED: 1 bulan penuh hari kerja
                // Dari 1 bulan yang lalu sampai hari ini
                $startDate = $today->copy()->subMonth();
                $endDate = $today->copy();
                break;
            default:
                $startDate = $today->copy();
                $endDate = $today->copy();
        }
        
        return [
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d')
        ];
    }

    /**
     * Build activity query based on user type and filters
     */
    private function buildActivityQuery($dateRange, $branchFilter, $userType, $currentUserId)
    {
        // Base query untuk mendapatkan semua user yang relevan
        $userQuery = User::select([
            'users.id', 
            'users.name', 
            'users.type',
            'projects.id as project_id',
            'projects.project_name',
            'projects.tags as branch'
        ])
        ->leftJoin('project_users', 'users.id', '=', 'project_users.user_id')
        ->leftJoin('projects', 'project_users.project_id', '=', 'projects.id')
        ->where('users.type', '!=', 'client')
        ->where('users.type', '!=', 'staff_client');

        // Filter berdasarkan user type
        if($userType !== 'admin' && $userType !== 'company') {
            // Untuk user biasa, hanya tampilkan project yang sama
            $userProjectIds = ProjectUser::where('user_id', $currentUserId)->pluck('project_id')->toArray();
            $userQuery->whereIn('projects.id', $userProjectIds);
        }

        // Filter berdasarkan branch
        if($branchFilter !== 'all') {
            $userQuery->where('projects.tags', $branchFilter);
        }

        // Ambil data user dan project
        $userProjects = $userQuery->get();

        // Untuk setiap user-project combination, ambil data timesheet, timetracker, dan attendance
        $activities = collect();

        foreach($userProjects as $userProject) {
            if(!$userProject->project_id) continue;

            // Cek timesheet
            $timesheets = Timesheet::where('created_by', $userProject->id)
                ->where('project_id', $userProject->project_id)
                ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
                ->get();

            // Cek timetracker
            $timetrackers = TimeTracker::where('created_by', $userProject->id)
                ->where('project_id', $userProject->project_id)
                ->whereBetween('start_time', [$dateRange['start'] . ' 00:00:00', $dateRange['end'] . ' 23:59:59'])
                ->get();

            // Cek attendance untuk periode yang sama
            $attendances = AttendanceEmployee::whereHas('employee', function($q) use ($userProject) {
                    $q->where('user_id', $userProject->id);
                })
                ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
                ->get();

            // Group by date untuk analisis
            $dateActivity = $this->analyzeUserActivity($userProject, $timesheets, $timetrackers, $attendances, $dateRange);
            
            $activities->push([
                'user' => $userProject,
                'project_id' => $userProject->project_id,
                'project_name' => $userProject->project_name,
                'branch' => $userProject->branch,
                'daily_activity' => $dateActivity
            ]);
        }

        return $activities;
    }

    /**
     * Analyze user activity per date
     */
    private function analyzeUserActivity($user, $timesheets, $timetrackers, $attendances, $dateRange)
    {
        $period = new \DatePeriod(
            new \DateTime($dateRange['start']),
            new \DateInterval('P1D'),
            new \DateTime($dateRange['end'] . ' +1 day')
        );

        $dailyActivity = [];

        foreach($period as $date) {
            $currentDate = $date->format('Y-m-d');
            
            // Cek timesheet untuk tanggal ini
            $dayTimesheet = $timesheets->where('date', $currentDate)->first();
            
            // Cek timetracker untuk tanggal ini
            $dayTimetracker = $timetrackers->filter(function($item) use ($currentDate) {
                return \Carbon\Carbon::parse($item->start_time)->format('Y-m-d') === $currentDate;
            })->first();

            // Cek attendance untuk tanggal ini
            $dayAttendance = $attendances->where('date', $currentDate)->first();

            // Analisis status
            $status = $this->determineActivityStatus($dayTimesheet, $dayTimetracker, $dayAttendance);
            
            $dailyActivity[$currentDate] = [
                'date' => $currentDate,
                'status' => $status,
                'timesheet' => $dayTimesheet,
                'timetracker' => $dayTimetracker,
                'attendance' => $dayAttendance,
                'work_hours' => $dayTimesheet ? $dayTimesheet->time : '00:00:00',
                'tracker_hours' => $dayTimetracker ? $this->calculateTrackerHours($dayTimetracker) : '00:00:00'
            ];
        }

        return $dailyActivity;
    }


    /**
     * Determine activity status based on data availability
     */
    private function determineActivityStatus($timesheet, $timetracker, $attendance)
    {
        // Jika ada timesheet atau timetracker, berarti aktif
        if($timesheet || $timetracker) {
            return 'active';
        }

        // Jika tidak ada timesheet/tracker tapi ada attendance, berarti tidak menyalakan tracker
        if($attendance && $attendance->status === 'present') {
            return 'no_tracker';
        }

        // Jika tidak ada attendance atau status absent
        if(!$attendance || $attendance->status === 'absent') {
            return 'absent';
        }

        return 'no_data';
    }

    /**
     * Calculate total hours from timetracker
     */
    private function calculateTrackerHours($timetracker)
    {
        if(!$timetracker->start_time || !$timetracker->end_time) {
            return '00:00:00';
        }

        $start = \Carbon\Carbon::parse($timetracker->start_time);
        $end = \Carbon\Carbon::parse($timetracker->end_time);
        
        $diff = $start->diffInSeconds($end);
        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        $seconds = $diff % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Group activities by branch and user
     */
    private function groupActivitiesByBranchAndUser($activities, $dateRange)
    {
        $grouped = [];
        
        // Get semua branch yang ada dalam data
        $availableBranches = $activities->pluck('branch_name')->unique()->sort();
        
        // Jika tidak ada data, ambil dari database
        if ($availableBranches->isEmpty()) {
            $availableBranches = \DB::table('branches')->pluck('name')->sort();
        }

        foreach($availableBranches as $branchName) {
            $branchActivities = $activities->where('branch_name', $branchName);
            
            if($branchActivities->count() > 0) {
                $users = [];
                $groupedByUser = $branchActivities->groupBy('user_id');
                
                foreach($groupedByUser as $userId => $userActivities) {
                    $firstActivity = $userActivities->first();
                    
                    // IMPORTANT: Generate combined activity for the user
                    $combinedDailyActivity = $this->combineUserDailyActivities($userActivities);
                    
                    $users[] = [
                        'user' => (object)[
                            'id' => $firstActivity['user_id'],
                            'name' => $firstActivity['user_name'],
                            'type' => $firstActivity['user_type'],
                            'avatar' => $firstActivity['user_avatar']
                        ],
                        'projects' => $userActivities->map(function($activity) {
                            return [
                                'project_name' => $activity['project_name'],
                                'daily_activity' => $activity['daily_activity']
                            ];
                        })->values()->toArray(),
                        'combined_activity' => $combinedDailyActivity // This is crucial!
                    ];
                }
                
                $grouped[$branchName] = [
                    'branch_name' => $branchName,
                    'users' => $users
                ];
            }
        }

        return $grouped;
    }


    /**
     * Export project activity report
     */
    public function exportProjectActivity(Request $request)
    {
        if(!\Auth::user()->can('manage project')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $timeFilter = $request->input('time_filter', 'today'); // Ganti get() dengan input()
        $branchFilter = $request->input('branch_filter', 'all'); // Ganti get() dengan input()
        $userType = \Auth::user()->type;
        $currentUserId = \Auth::user()->id;

        // Get data sama seperti di filter method
        $dateRange = $this->getDateRange($timeFilter);
        $activities = $this->buildActivityQuery($dateRange, $branchFilter, $userType, $currentUserId);
        $groupedActivities = $this->groupActivitiesByBranchAndUser($activities, $dateRange);

        // Prepare data for export
        $exportData = [];
        $headers = ['Branch', 'User', 'Project'];
        
        // Add date columns based on filter
        if($timeFilter == 'today') {
            $headers[] = 'Today (' . date('d/m/Y') . ')';
        } elseif($timeFilter == '7days') {
            for($i = 6; $i >= 0; $i--) {
                $headers[] = date('d/m/Y', strtotime('-'.$i.' days'));
            }
        } elseif($timeFilter == '1month') {
            for($i = 30; $i >= 0; $i--) {
                if($i % 3 == 0) {
                    $headers[] = date('d/m/Y', strtotime('-'.$i.' days'));
                }
            }
        }
        
        $headers[] = 'Active Days';
        $headers[] = 'No Tracker Days';
        $headers[] = 'Absent Days';

        $exportData[] = $headers;

        // Build data rows
        foreach($groupedActivities as $branchName => $branchData) {
            foreach($branchData['users'] as $userData) {
                $user = $userData['user'];
                $projects = $userData['projects'];
                
                foreach($projects as $project) {
                    $row = [];
                    $row[] = $branchName;
                    $row[] = $user->name;
                    $row[] = $project['project_name'] ?? 'No Project';
                    
                    // Add activity status for each date
                    if($timeFilter == 'today') {
                        $todayDate = date('Y-m-d');
                        $todayActivity = $project['daily_activity'][$todayDate] ?? null;
                        $row[] = $todayActivity ? $this->getActivityStatusText($todayActivity) : 'No Data';
                    } elseif($timeFilter == '7days') {
                        for($i = 6; $i >= 0; $i--) {
                            $date = date('Y-m-d', strtotime('-'.$i.' days'));
                            $dayActivity = $project['daily_activity'][$date] ?? null;
                            $row[] = $dayActivity ? $this->getActivityStatusText($dayActivity) : 'No Data';
                        }
                    } elseif($timeFilter == '1month') {
                        for($i = 30; $i >= 0; $i--) {
                            if($i % 3 == 0) {
                                $date = date('Y-m-d', strtotime('-'.$i.' days'));
                                $dayActivity = $project['daily_activity'][$date] ?? null;
                                $row[] = $dayActivity ? $this->getActivityStatusText($dayActivity) : 'No Data';
                            }
                        }
                    }
                    
                    // Add summary
                    $summary = $this->calculateActivitySummary($project['daily_activity']);
                    $row[] = $summary['active'];
                    $row[] = $summary['no_tracker'];
                    $row[] = $summary['absent'];
                    
                    $exportData[] = $row;
                }
            }
        }

        // Generate Excel file
        return $this->generateExcelExport($exportData, $timeFilter, $branchFilter);
    }

    /**
     * Get activity status as text for export
     */
    private function getActivityStatusText($activity)
    {
        switch($activity['status']) {
            case 'active':
                $hours = $activity['work_hours'] ?: $activity['tracker_hours'];
                return 'Active (' . $hours . ')';
            case 'no_tracker':
                return 'No Tracker';
            case 'absent':
                return 'Absent';
            default:
                return 'No Data';
        }
    }

    /**
     * Generate Excel export
     */
    private function generateExcelExport($data, $timeFilter, $branchFilter)
    {
        $filename = 'project_activity_report_' . $branchFilter . '_' . $timeFilter . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get activity status badge HTML
     */
    public function getActivityStatusBadge($activity) 
    {
        switch($activity['status']) {
            case 'active':
                $hours = $activity['work_hours'] ?: $activity['tracker_hours'];
                return '<span class="badge bg-success" title="Work Hours: '.$hours.'">Active<br><small>'.$hours.'</small></span>';
            case 'no_tracker':
                return '<span class="badge bg-warning" title="Present but no tracker">No Tracker</span>';
            case 'absent':
                return '<span class="badge bg-danger" title="Absent">Absent</span>';
            default:
                return '<span class="badge bg-secondary" title="No data">No Data</span>';
        }
    }

    /**
     * Get activity status icon HTML
     */
    public function getActivityStatusIcon($activity) 
    {
        switch($activity['status']) {
            case 'active':
                return '<i class="ti ti-circle-check text-success" title="Active - '.$activity['work_hours'].'"></i>';
            case 'no_tracker':
                return '<i class="ti ti-clock-off text-warning" title="Present but no tracker"></i>';
            case 'absent':
                return '<i class="ti ti-circle-x text-danger" title="Absent"></i>';
            default:
                return '<i class="ti ti-minus text-muted" title="No data"></i>';
        }
    }

    /**
     * Calculate activity summary
     */
    public function calculateActivitySummary($dailyActivity) 
    {
        $summary = ['active' => 0, 'no_tracker' => 0, 'absent' => 0, 'no_data' => 0];
        
        foreach($dailyActivity as $day) {
            if(isset($summary[$day['status']])) {
                $summary[$day['status']]++;
            }
        }
        
        return $summary;
    }

    /**
     * Get activity data for specific users and date range
     */
    private function getActivityDataForUsers($userProjects, $dateRange)
    {
        if($userProjects->isEmpty()) {
            return collect();
        }

        $userIds = $userProjects->pluck('user_id')->unique();
        $projectIds = $userProjects->pluck('project_id')->unique();

        // Batch query untuk timesheet - hanya dalam date range
        $timesheets = \DB::table('timesheets')
            ->whereIn('created_by', $userIds)
            ->whereIn('project_id', $projectIds)
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->get()
            ->groupBy(['created_by', 'project_id', 'date']);

        // Batch query untuk timetracker - hanya dalam date range  
        $timetrackers = \DB::table('time_trackers')
            ->whereIn('created_by', $userIds)
            ->whereIn('project_id', $projectIds)
            ->whereBetween('start_time', [$dateRange['start'] . ' 00:00:00', $dateRange['end'] . ' 23:59:59'])
            ->get()
            ->groupBy(['created_by', 'project_id']);

        // Batch query untuk attendance
        $attendances = \DB::table('attendance_employees')
            ->join('employees', 'attendance_employees.employee_id', '=', 'employees.id')
            ->whereIn('employees.user_id', $userIds)
            ->whereBetween('attendance_employees.date', [$dateRange['start'], $dateRange['end']])
            ->select('employees.user_id', 'attendance_employees.*')
            ->get()
            ->groupBy(['user_id', 'date']);

        // Process data untuk setiap user-project
        $activities = collect();
        foreach($userProjects as $userProject) {
            // Pastikan user benar-benar punya aktivitas di project ini dalam date range
            $hasActivity = $this->userHasActivityInRange($userProject, $timesheets, $timetrackers, $dateRange);
            
            if ($hasActivity) {
                $dailyActivity = $this->buildDailyActivityOptimized(
                    $userProject, 
                    $timesheets, 
                    $timetrackers, 
                    $attendances, 
                    $dateRange
                );
                
                $activities->push([
                    'user_id' => $userProject->user_id,
                    'user_name' => $userProject->user_name,
                    'user_type' => $userProject->user_type,
                    'user_avatar' => $userProject->user_avatar,
                    'project_id' => $userProject->project_id,
                    'project_name' => $userProject->project_name,
                    'branch_id' => $userProject->branch_id,
                    'branch_name' => $userProject->branch_name,
                    'daily_activity' => $dailyActivity
                ]);
            }
        }

        return $activities;
    }

    /**
     * Build daily activity data optimized
     */
    private function buildDailyActivityOptimized($userProject, $timesheets, $timetrackers, $attendances, $dateRange)
    {
        $period = new \DatePeriod(
            new \DateTime($dateRange['start']),
            new \DateInterval('P1D'),
            new \DateTime($dateRange['end'] . ' +1 day')
        );

        $dailyActivity = [];
        foreach($period as $date) {
            $currentDate = $date->format('Y-m-d');
            
            // SKIP WEEKENDS (Saturday = 6, Sunday = 0)
            $dayOfWeek = $date->format('w');
            if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                continue; // Skip weekends
            }
            
            // Check timesheet untuk project ini
            $dayTimesheet = $timesheets->get($userProject->user_id, collect())
                ->get($userProject->project_id, collect())
                ->get($currentDate, collect())
                ->first();
            
            // Check timetracker untuk project ini
            $dayTimetracker = $timetrackers->get($userProject->user_id, collect())
                ->get($userProject->project_id, collect())
                ->filter(function($item) use ($currentDate) {
                    return \Carbon\Carbon::parse($item->start_time)->format('Y-m-d') === $currentDate;
                })
                ->first();
            
            // Check attendance untuk user ini (global, bukan per project)
            $dayAttendance = $attendances->get($userProject->user_id, collect())
                ->get($currentDate, collect())
                ->first();
            
            // Per-project status (akan digabung nanti di combineUserDailyActivities)
            if($dayTimesheet || $dayTimetracker) {
                $status = 'active'; // Project ini aktif
            } else {
                // Project ini tidak aktif, tapi attendance tetap disimpan untuk referensi
                $status = 'no_activity'; // Status sementara per project
            }
            
            $dailyActivity[$currentDate] = [
                'date' => $currentDate,
                'status' => $status,
                'work_hours' => $dayTimesheet ? $dayTimesheet->time : '00:00:00',
                'has_tracker' => $dayTimetracker ? true : false,
                'attendance_status' => $dayAttendance ? $dayAttendance->status : null
            ];
        }

        return $dailyActivity;
    }

    private function determineActivityStatusImproved($timesheet, $timetracker, $attendance)
    {
        // 1. Jika ada timesheet atau timetracker = ACTIVE
        if($timesheet || $timetracker) {
            return 'active';
        }
        
        // 2. Jika tidak ada timesheet/tracker tapi ada attendance record
        if($attendance) {
            if($attendance->status === 'present') {
                return 'no_tracker'; // Hadir tapi tidak menyalakan tracker
            } else {
                return 'absent'; // Ada record attendance tapi status absent/etc
            }
        }
        
        // 3. Jika tidak ada timesheet/tracker DAN tidak ada attendance record
        return 'absent'; // Tidak ada data attendance = dianggap absent
    }

    /**
     * Determine activity status optimized
     */
    private function determineActivityStatusOptimized($timesheet, $timetracker, $attendance)
    {
        if($timesheet || $timetracker) {
            return 'active';
        }
        
        if($attendance && $attendance->status === 'present') {
            return 'no_tracker';
        }
        
        if($attendance && $attendance->status === 'absent') {
            return 'absent';
        }
        
        return 'no_data';
    }

    /**
     * Group activities optimized
     */
    private function groupActivitiesOptimized($activities)
    {
        $grouped = [];
        $branches = ['PUSAT', 'BEKASI', 'MALANG'];

        foreach($branches as $branch) {
            $branchActivities = $activities->where('branch', $branch);
            
            if($branchActivities->count() > 0) {
                $users = [];
                $groupedByUser = $branchActivities->groupBy('user_id');
                
                foreach($groupedByUser as $userId => $userActivities) {
                    $firstActivity = $userActivities->first();
                    $projects = $userActivities->map(function($activity) {
                        return [
                            'project_name' => $activity['project_name'],
                            'daily_activity' => $activity['daily_activity']
                        ];
                    })->toArray();
                    
                    $users[] = [
                        'user' => (object)[
                            'id' => $firstActivity['user_id'],
                            'name' => $firstActivity['user_name'],
                            'type' => $firstActivity['user_type'],
                            'avatar' => $firstActivity['user_avatar'] // Include avatar
                        ],
                        'projects' => $projects
                    ];
                }
                
                $grouped[$branch] = [
                    'branch_name' => $branch,
                    'users' => $users
                ];
            }
        }

        return $grouped;
    }

    /**
     * Generate activity summary
     */
    private function generateActivitySummary($activities)
    {
        $today = date('Y-m-d');
        
        // Skip if today is weekend
        $todayDayOfWeek = \Carbon\Carbon::parse($today)->format('w');
        $isWeekend = ($todayDayOfWeek == 0 || $todayDayOfWeek == 6);
        
        $summary = [
            'total_users' => 0,
            'total_projects' => 0,
            'active_today' => 0,
            'no_tracker_today' => 0,
            'absent_today' => 0,
            'no_data_today' => 0,
            'projects_with_activity' => 0,
            'is_weekend' => $isWeekend
        ];

        if ($activities->isEmpty()) {
            return $summary;
        }

        $summary['total_users'] = $activities->groupBy('user_id')->count();
        $summary['total_projects'] = $activities->count();
        $summary['projects_with_activity'] = $activities->filter(function($activity) {
            return !empty($activity['daily_activity']);
        })->count();

        // Count today's activity status - SKIP WEEKENDS and COUNT USERS NOT PROJECTS
        if (!$isWeekend) {
            // Group by user first, then check their combined activity
            $userActivities = $activities->groupBy('user_id');
            
            foreach($userActivities as $userId => $userProjects) {
                $combinedActivity = $this->combineUserDailyActivities($userProjects);
                
                if(isset($combinedActivity[$today])) {
                    $status = $combinedActivity[$today]['status'];
                    
                    switch($status) {
                        case 'active':
                            $summary['active_today']++;
                            break;
                        case 'no_tracker':
                            $summary['no_tracker_today']++;
                            break;
                        case 'absent':
                            $summary['absent_today']++;
                            break;
                        default:
                            $summary['no_data_today']++;
                            break;
                    }
                } else {
                    // No data for today = check if there's any attendance record
                    $hasAttendanceRecord = false;
                    foreach($userProjects as $project) {
                        if(isset($project['daily_activity'][$today]['attendance_status'])) {
                            $hasAttendanceRecord = true;
                            break;
                        }
                    }
                    
                    if($hasAttendanceRecord) {
                        $summary['no_tracker_today']++; // Present but no activity
                    } else {
                        $summary['absent_today']++; // No attendance record
                    }
                }
            }
        }

        return $summary;
    }

    /**
     * Clear activity cache
     */
    public function clearActivityCache()
    {
        $pattern = "activity_report_*";
        $keys = \Cache::getRedis()->keys($pattern);
        foreach($keys as $key) {
            \Cache::forget($key);
        }
        
        return response()->json(['success' => true, 'message' => 'Cache cleared']);
    }

    private function userHasActivityInRange($userProject, $timesheets, $timetrackers, $dateRange)
    {
        // Check timesheet activity
        $userTimesheets = $timesheets->get($userProject->user_id, collect())
                                    ->get($userProject->project_id, collect());
        
        if ($userTimesheets->isNotEmpty()) {
            return true;
        }

        // Check timetracker activity
        $userTimetrackers = $timetrackers->get($userProject->user_id, collect())
                                    ->get($userProject->project_id, collect());
        
        if ($userTimetrackers->isNotEmpty()) {
            foreach ($userTimetrackers as $tracker) {
                $trackerDate = \Carbon\Carbon::parse($tracker->start_time)->format('Y-m-d');
                if ($trackerDate >= $dateRange['start'] && $trackerDate <= $dateRange['end']) {
                    return true;
                }
            }
        }

        return false;
    }

    private function combineUserDailyActivities($userActivities)
    {
        $combinedActivity = [];
        
        // Get date range dari aktivitas pertama
        $firstActivity = $userActivities->first();
        if (!$firstActivity || !isset($firstActivity['daily_activity'])) {
            return $combinedActivity;
        }
        
        // Kumpulkan semua tanggal dari semua project user ini
        $allDates = collect();
        foreach($userActivities as $activity) {
            if(isset($activity['daily_activity'])) {
                $allDates = $allDates->merge(array_keys($activity['daily_activity']));
            }
        }
        
        $uniqueDates = $allDates->unique()->sort();
        
        foreach($uniqueDates as $date) {
            // Check if it's weekend
            $dayOfWeek = \Carbon\Carbon::parse($date)->format('w');
            if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                continue; // Skip weekends
            }
            
            // FIXED LOGIC: Check user's overall activity for this date
            $hasAnyActivity = false;
            $hasAnyTracker = false;
            $bestWorkHours = '00:00:00';
            $userAttendanceStatus = null;
            
            // Check aktivitas di SEMUA project untuk tanggal ini
            foreach($userActivities as $activity) {
                if(isset($activity['daily_activity'][$date])) {
                    $dayData = $activity['daily_activity'][$date];
                    
                    // Jika ada aktivitas di project manapun = user active
                    if($dayData['status'] === 'active') {
                        $hasAnyActivity = true;
                        if($dayData['has_tracker']) {
                            $hasAnyTracker = true;
                        }
                        // Ambil work hours terbaik dari semua project
                        if($dayData['work_hours'] && $dayData['work_hours'] !== '00:00:00') {
                            $bestWorkHours = $dayData['work_hours'];
                        }
                    }
                    
                    // Ambil attendance status (asumsi sama untuk user di hari yang sama)
                    if($dayData['attendance_status']) {
                        $userAttendanceStatus = $dayData['attendance_status'];
                    }
                }
            }
            
            // CORRECTED: Tentukan status berdasarkan user secara keseluruhan
            if($hasAnyActivity) {
                // User punya aktivitas di minimal 1 project = ACTIVE
                $status = 'active';
            } else {
                // User tidak punya aktivitas timesheet/tracker di project manapun
                // Cek attendance status
                if($userAttendanceStatus === 'present') {
                    $status = 'no_tracker'; // Hadir tapi tidak ada tracker
                } else {
                    $status = 'absent'; // Tidak ada attendance atau status bukan present
                }
            }
            
            $combinedActivity[$date] = [
                'date' => $date,
                'status' => $status,
                'work_hours' => $bestWorkHours,
                'has_tracker' => $hasAnyTracker,
                'attendance_status' => $userAttendanceStatus
            ];
        }
        
        return $combinedActivity;
    }

    public function getWeekdaysInRange($startDate, $endDate)
    {
        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            new \DateTime($endDate . ' +1 day')
        );
        
        $weekdays = [];
        foreach($period as $date) {
            $dayOfWeek = $date->format('w');
            if ($dayOfWeek != 0 && $dayOfWeek != 6) { // Exclude Sunday (0) and Saturday (6)
                $weekdays[] = [
                    'date' => $date->format('Y-m-d'),
                    'day' => $date->format('D'),
                    'formatted' => $date->format('d/m'),
                    'full' => $date->format('d M Y')
                ];
            }
        }
        
        return $weekdays;
    }

    public function debugWeekdays(Request $request)
    {
        $timeFilter = $request->input('time_filter', '1month');
        $dateRange = $this->getDateRange($timeFilter);
        $weekdays = $this->getWeekdaysInRange($dateRange['start'], $dateRange['end']);
        
        return response()->json([
            'time_filter' => $timeFilter,
            'date_range' => $dateRange,
            'total_weekdays' => count($weekdays),
            'weekdays' => $weekdays,
            'today' => date('Y-m-d'),
            'example_calculation' => [
                'today' => date('d M Y'),
                'one_month_ago' => date('d M Y', strtotime('-1 month')),
                'expected_range' => date('d M Y', strtotime('-1 month')) . ' to ' . date('d M Y')
            ]
        ]);
    }

    /**
     * Build optimized user-project query with branch permission check
     */
    private function buildOptimizedUserProjectQuery($branchFilter, $userType, $currentUserId, $dateRange)
    {
        $query = \DB::table('users')
            ->select([
                'users.id as user_id',
                'users.name as user_name',
                'users.type as user_type',
                'users.avatar as user_avatar',
                'projects.id as project_id',
                'projects.project_name',
                'branches.id as branch_id',
                'branches.name as branch_name'
            ])
            ->join('employees', 'users.id', '=', 'employees.user_id') // Join dengan employees
            ->join('branches', 'employees.branch_id', '=', 'branches.id') // Join dengan branches
            ->join('project_users', 'users.id', '=', 'project_users.user_id')
            ->join('projects', 'project_users.project_id', '=', 'projects.id')
            ->where('users.is_active', 1) // Hanya user yang aktif
            ->where('users.type', '!=', 'client')
            ->where('users.type', '!=', 'staff_client')
            ->where('users.type', '!=', 'admin') // Exclude admin
            ->where('users.type', '!=', 'company') // Exclude company
            ->whereNotNull('projects.id')
            ->whereNotNull('employees.branch_id'); // Pastikan ada branch

        // UPDATED: Filter berdasarkan user type untuk permission
        if($userType !== 'admin' && $userType !== 'company') {
            // Get current user's branch
            $currentUserBranch = \DB::table('users')
                ->join('employees', 'users.id', '=', 'employees.user_id')
                ->join('branches', 'employees.branch_id', '=', 'branches.id')
                ->where('users.id', $currentUserId)
                ->first();

            if($currentUserBranch) {
                // Limit to same branch only
                $query->where('branches.id', $currentUserBranch->branch_id);
            } else {
                // If user doesn't have branch, return empty result
                $query->where('branches.id', -1);
            }
            
            // Also limit to projects that current user is involved in
            $userProjectIds = \DB::table('project_users')
                ->where('user_id', $currentUserId)
                ->pluck('project_id');
            $query->whereIn('projects.id', $userProjectIds);
        }

        // Filter berdasarkan branch selection - hanya berlaku untuk admin/company
        if($branchFilter !== 'all' && ($userType === 'admin' || $userType === 'company')) {
            if(is_numeric($branchFilter)) {
                // Jika branch_id (numeric)
                $query->where('branches.id', $branchFilter);
            } else {
                // Jika branch name (string)
                $query->where('branches.name', $branchFilter);
            }
        }

        // FILTER PROJECT: Hanya tampilkan project yang user kerjakan dalam periode filter
        $query->where(function($q) use ($dateRange) {
            $q->whereExists(function($subQuery) use ($dateRange) {
                $subQuery->select(\DB::raw(1))
                        ->from('timesheets')
                        ->whereRaw('timesheets.created_by = users.id')
                        ->whereRaw('timesheets.project_id = projects.id')
                        ->whereBetween('timesheets.date', [$dateRange['start'], $dateRange['end']]);
            })
            ->orWhereExists(function($subQuery) use ($dateRange) {
                $subQuery->select(\DB::raw(1))
                        ->from('time_trackers')
                        ->whereRaw('time_trackers.created_by = users.id')
                        ->whereRaw('time_trackers.project_id = projects.id')
                        ->whereBetween('time_trackers.start_time', [$dateRange['start'] . ' 00:00:00', $dateRange['end'] . ' 23:59:59']);
            });
        });

        return $query->orderBy('branches.name')->orderBy('users.name');
    }

    /**
     * Updated getBranchStats with branch permission check
     */
    private function getBranchStats($dateRange, $userType, $currentUserId)
    {
        $stats = [];
        $today = date('Y-m-d');

        // Check if user has branch restriction
        if($userType !== 'admin' && $userType !== 'company') {
            // Get current user's branch only
            $currentUserBranch = \DB::table('users')
                ->join('employees', 'users.id', '=', 'employees.user_id')
                ->join('branches', 'employees.branch_id', '=', 'branches.id')
                ->where('users.id', $currentUserId)
                ->select('branches.id', 'branches.name')
                ->first();

            if($currentUserBranch) {
                $branches = collect([$currentUserBranch->id => $currentUserBranch->name]);
            } else {
                return []; // No branch access
            }
        } else {
            // Admin/Company can see all branches
            $branches = \DB::table('branches')->pluck('name', 'id');
        }

        foreach($branches as $branchId => $branchName) {
            $branchQuery = \DB::table('users')
                ->join('employees', 'users.id', '=', 'employees.user_id')
                ->join('branches', 'employees.branch_id', '=', 'branches.id')
                ->join('project_users', 'users.id', '=', 'project_users.user_id')
                ->join('projects', 'project_users.project_id', '=', 'projects.id')
                ->where('users.is_active', 1) // Hanya user aktif
                ->where('users.type', '!=', 'client')
                ->where('users.type', '!=', 'staff_client')
                ->where('users.type', '!=', 'admin') // Exclude admin
                ->where('users.type', '!=', 'company') // Exclude company
                ->where('branches.id', $branchId); // Filter by branch

            // Additional project filter for non-admin users
            if($userType !== 'admin' && $userType !== 'company') {
                $userProjectIds = \DB::table('project_users')
                    ->where('user_id', $currentUserId)
                    ->pluck('project_id');
                $branchQuery->whereIn('projects.id', $userProjectIds);
            }

            // Hanya user yang punya aktivitas dalam periode
            $branchQuery->where(function($q) use ($dateRange) {
                $q->whereExists(function($subQuery) use ($dateRange) {
                    $subQuery->select(\DB::raw(1))
                            ->from('timesheets')
                            ->whereRaw('timesheets.created_by = users.id')
                            ->whereBetween('timesheets.date', [$dateRange['start'], $dateRange['end']]);
                })
                ->orWhereExists(function($subQuery) use ($dateRange) {
                    $subQuery->select(\DB::raw(1))
                            ->from('time_trackers')
                            ->whereRaw('time_trackers.created_by = users.id')
                            ->whereBetween('time_trackers.start_time', [$dateRange['start'] . ' 00:00:00', $dateRange['end'] . ' 23:59:59']);
                });
            });

            $totalInBranch = $branchQuery->distinct('users.id')->count('users.id');
            
            // Active in this branch today
            $activeInBranch = clone $branchQuery;
            $activeInBranch->where(function($q) use ($today) {
                $q->whereExists(function($query) use ($today) {
                    $query->select(\DB::raw(1))
                        ->from('timesheets')
                        ->whereRaw('timesheets.created_by = users.id')
                        ->where('timesheets.date', $today);
                })
                ->orWhereExists(function($query) use ($today) {
                    $query->select(\DB::raw(1))
                        ->from('time_trackers')
                        ->whereRaw('time_trackers.created_by = users.id')
                        ->whereDate('time_trackers.start_time', $today);
                });
            });

            $activeCount = $activeInBranch->distinct('users.id')->count('users.id');

            $stats[$branchName] = [
                'total' => $totalInBranch,
                'active' => $activeCount,
                'rate' => $totalInBranch > 0 ? round(($activeCount / $totalInBranch) * 100, 1) : 0
            ];
        }

        return $stats;
    }

    /**
     * Updated getActivityStats with branch permission
     */
    public function getActivityStats(Request $request)
    {
        $timeFilter = $request->input('time_filter', 'today');
        $branchFilter = $request->input('branch_filter', 'all');
        $userType = \Auth::user()->type;
        $currentUserId = \Auth::user()->id;

        $cacheKey = "activity_stats_{$userType}_{$currentUserId}_{$timeFilter}_{$branchFilter}";
        
        if (\Cache::has($cacheKey)) {
            return response()->json(\Cache::get($cacheKey));
        }

        $dateRange = $this->getDateRange($timeFilter);
        $today = date('Y-m-d');

        // Quick stats query - with branch permission check
        $baseQuery = \DB::table('users')
            ->join('employees', 'users.id', '=', 'employees.user_id')
            ->join('branches', 'employees.branch_id', '=', 'branches.id')
            ->join('project_users', 'users.id', '=', 'project_users.user_id')
            ->join('projects', 'project_users.project_id', '=', 'projects.id')
            ->where('users.is_active', 1)
            ->where('users.type', '!=', 'client')
            ->where('users.type', '!=', 'staff_client')
            ->where('users.type', '!=', 'admin')
            ->where('users.type', '!=', 'company');

        // UPDATED: Apply branch restriction for non-admin users
        if($userType !== 'admin' && $userType !== 'company') {
            // Get current user's branch
            $currentUserBranch = \DB::table('users')
                ->join('employees', 'users.id', '=', 'employees.user_id')
                ->where('users.id', $currentUserId)
                ->value('employees.branch_id');

            if($currentUserBranch) {
                $baseQuery->where('branches.id', $currentUserBranch);
            } else {
                // No branch access
                return response()->json([
                    'total_users' => 0,
                    'active_today' => 0,
                    'inactive_today' => 0,
                    'activity_rate' => 0,
                    'branches' => [],
                    'period' => $timeFilter,
                    'date_range' => $dateRange,
                    'message' => 'User has no branch access'
                ]);
            }

            $userProjectIds = \DB::table('project_users')
                ->where('user_id', $currentUserId)
                ->pluck('project_id');
            $baseQuery->whereIn('projects.id', $userProjectIds);
        } else {
            // Admin/Company can filter by branch selection
            if($branchFilter !== 'all') {
                if(is_numeric($branchFilter)) {
                    $baseQuery->where('branches.id', $branchFilter);
                } else {
                    $baseQuery->where('branches.name', $branchFilter);
                }
            }
        }

        // Hanya user yang punya aktivitas dalam periode
        $baseQuery->where(function($q) use ($dateRange) {
            $q->whereExists(function($subQuery) use ($dateRange) {
                $subQuery->select(\DB::raw(1))
                        ->from('timesheets')
                        ->whereRaw('timesheets.created_by = users.id')
                        ->whereBetween('timesheets.date', [$dateRange['start'], $dateRange['end']]);
            })
            ->orWhereExists(function($subQuery) use ($dateRange) {
                $subQuery->select(\DB::raw(1))
                        ->from('time_trackers')
                        ->whereRaw('time_trackers.created_by = users.id')
                        ->whereBetween('time_trackers.start_time', [$dateRange['start'] . ' 00:00:00', $dateRange['end'] . ' 23:59:59']);
            });
        });

        $totalUsers = $baseQuery->distinct('users.id')->count('users.id');
        
        // Active users today (have timesheet or timetracker)
        $activeUsersQuery = clone $baseQuery;
        $activeUsersQuery->where(function($q) use ($today) {
            $q->whereExists(function($query) use ($today) {
                $query->select(\DB::raw(1))
                    ->from('timesheets')
                    ->whereRaw('timesheets.created_by = users.id')
                    ->where('timesheets.date', $today);
            })
            ->orWhereExists(function($query) use ($today) {
                $query->select(\DB::raw(1))
                    ->from('time_trackers')
                    ->whereRaw('time_trackers.created_by = users.id')
                    ->whereDate('time_trackers.start_time', $today);
            });
        });

        $activeCount = $activeUsersQuery->distinct('users.id')->count('users.id');

        $stats = [
            'total_users' => $totalUsers,
            'active_today' => $activeCount,
            'inactive_today' => $totalUsers - $activeCount,
            'activity_rate' => $totalUsers > 0 ? round(($activeCount / $totalUsers) * 100, 1) : 0,
            'branches' => $this->getBranchStats($dateRange, $userType, $currentUserId),
            'period' => $timeFilter,
            'date_range' => $dateRange,
            'user_branch_only' => ($userType !== 'admin' && $userType !== 'company')
        ];

        \Cache::put($cacheKey, $stats, 600); // 10 minutes cache
        return response()->json($stats);
    }

    /**
     * Helper function to get current user's branch info
     */
    private function getCurrentUserBranch($userId)
    {
        return \DB::table('users')
            ->join('employees', 'users.id', '=', 'employees.user_id')
            ->join('branches', 'employees.branch_id', '=', 'branches.id')
            ->where('users.id', $userId)
            ->select('branches.id', 'branches.name')
            ->first();
    }
        

}