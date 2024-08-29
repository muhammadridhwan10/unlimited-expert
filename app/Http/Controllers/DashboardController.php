<?php

namespace App\Http\Controllers;

use App\Models\AccountList;
use App\Models\Announcement;
use App\Models\AttendanceEmployee;
use App\Models\BalanceSheet;
use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\Bug;
use App\Models\BugStatus;
use App\Models\DealTask;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\LandingPageSection;
use App\Models\Meeting;
use App\Models\Order;
use App\Models\Payees;
use App\Models\Payer;
use App\Models\Payment;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceUnit;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\ProjectTask;
use App\Models\Revenue;
use App\Models\Tax;
use App\Models\Ticket;
use App\Models\Timesheet;
use App\Models\TimeTracker;
use App\Models\LogDesktop;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\User;
use App\Models\UserOvertime;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Reimbursment;
use App\Models\ReimbursmentType;
use App\Models\Task;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function account_dashboard_index()
    {
        if(Auth::check())
        {
            if(Auth::user()->type == 'super admin')
            {
                return redirect()->route('client.dashboard.view');
            }
            elseif(Auth::user()->type == 'client')
            {
                return redirect()->route('client.dashboard.view');
            }
            elseif(Auth::user()->type == 'staff_client')
            {
                return redirect()->route('client.dashboard.view');
            }
            elseif(Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                    $data['latestIncome']  = Revenue::orderBy('id', 'desc')->limit(5)->get();
                    $data['latestExpense'] = Payment::orderBy('id', 'desc')->limit(5)->get();


                    // $incomeCategory = ProductServiceCategory::where('type', '=', 1)->get();
                    // $inColor        = array();
                    // $inCategory     = array();
                    // $inAmount       = array();
                    // for($i = 0; $i < count($incomeCategory); $i++)
                    // {
                    //     $inColor[]    = '#' . $incomeCategory[$i]->color;
                    //     $inCategory[] = $incomeCategory[$i]->name;
                    //     $inAmount[]   = $incomeCategory[$i]->incomeCategoryRevenueAmount();
                    // }


                    // $data['incomeCategoryColor'] = $inColor;
                    // $data['incomeCategory']      = $inCategory;
                    // $data['incomeCatAmount']     = $inAmount;


                    // $expenseCategory = ProductServiceCategory::where('type', '=', 2)->get();
                    // $exColor         = array();
                    // $exCategory      = array();
                    // $exAmount        = array();
                    // for($i = 0; $i < count($expenseCategory); $i++)
                    // {
                    //     $exColor[]    = '#' . $expenseCategory[$i]->color;
                    //     $exCategory[] = $expenseCategory[$i]->name;
                    //     $exAmount[]   = $expenseCategory[$i]->expenseCategoryAmount();
                    // }

                    // $data['expenseCategoryColor'] = $exColor;
                    // $data['expenseCategory']      = $exCategory;
                    // $data['expenseCatAmount']     = $exAmount;

                    $data['incExpBarChartData']  = \Auth::user()->getincExpBarChartData();
                    $data['incExpLineChartData'] = \Auth::user()->getIncExpLineChartDate();

                    $data['currentYear']  = date('Y');
                    $data['currentMonth'] = date('M');

                    $constant['taxes']         = Tax::all()->count();
                    $constant['category']      = ProductServiceCategory::all()->count();
                    $constant['units']         = ProductServiceUnit::all()->count();
                    $constant['bankAccount']   = BankAccount::all()->count();
                    $data['constant']          = $constant;
                    $data['bankAccountDetail'] = BankAccount::all();
                    $data['recentInvoice']     = Invoice::orderBy('id', 'desc')->limit(5)->get();
                    $data['weeklyInvoice']     = \Auth::user()->weeklyInvoice();
                    $data['monthlyInvoice']    = \Auth::user()->monthlyInvoice();
                    $data['recentBill']        = Bill::orderBy('id', 'desc')->limit(5)->get();
                    $data['weeklyBill']        = \Auth::user()->weeklyBill();
                    $data['monthlyBill']       = \Auth::user()->monthlyBill();
                    $data['goals']             = Goal::where('is_display', 1)->get();


//                }
//                else
//                {
//                    $data = [];
//                }

                return view('dashboard.account-dashboard', $data);
            }
            else
            {
               if(\Auth::user()->can('show account dashboard'))
               {
                    $data['latestIncome']  = Revenue::where('created_by', '=', \Auth::user()->creatorId())->orderBy('id', 'desc')->limit(5)->get();
                    $data['latestExpense'] = Payment::where('created_by', '=', \Auth::user()->creatorId())->orderBy('id', 'desc')->limit(5)->get();


                    $incomeCategory = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 1)->get();
                    $inColor        = array();
                    $inCategory     = array();
                    $inAmount       = array();
                    for($i = 0; $i < count($incomeCategory); $i++)
                    {
                        $inColor[]    = '#' . $incomeCategory[$i]->color;
                        $inCategory[] = $incomeCategory[$i]->name;
                        $inAmount[]   = $incomeCategory[$i]->incomeCategoryRevenueAmount();
                    }


                    $data['incomeCategoryColor'] = $inColor;
                    $data['incomeCategory']      = $inCategory;
                    $data['incomeCatAmount']     = $inAmount;


                    $expenseCategory = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 2)->get();
                    $exColor         = array();
                    $exCategory      = array();
                    $exAmount        = array();
                    for($i = 0; $i < count($expenseCategory); $i++)
                    {
                        $exColor[]    = '#' . $expenseCategory[$i]->color;
                        $exCategory[] = $expenseCategory[$i]->name;
                        $exAmount[]   = $expenseCategory[$i]->expenseCategoryAmount();
                    }

                    $data['expenseCategoryColor'] = $exColor;
                    $data['expenseCategory']      = $exCategory;
                    $data['expenseCatAmount']     = $exAmount;

                    $data['incExpBarChartData']  = \Auth::user()->getincExpBarChartData();
                    $data['incExpLineChartData'] = \Auth::user()->getIncExpLineChartDate();

                    $data['currentYear']  = date('Y');
                    $data['currentMonth'] = date('M');

                    $constant['taxes']         = Tax::where('created_by', \Auth::user()->creatorId())->count();
                    $constant['category']      = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())->count();
                    $constant['units']         = ProductServiceUnit::where('created_by', \Auth::user()->creatorId())->count();
                    $constant['bankAccount']   = BankAccount::where('created_by', \Auth::user()->creatorId())->count();
                    $data['constant']          = $constant;
                    $data['bankAccountDetail'] = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get();
                    $data['recentInvoice']     = Invoice::where('created_by', '=', \Auth::user()->creatorId())->orderBy('id', 'desc')->limit(5)->get();
                    $data['weeklyInvoice']     = \Auth::user()->weeklyInvoice();
                    $data['monthlyInvoice']    = \Auth::user()->monthlyInvoice();
                    $data['recentBill']        = Bill::where('created_by', '=', \Auth::user()->creatorId())->orderBy('id', 'desc')->limit(5)->get();
                    $data['weeklyBill']        = \Auth::user()->weeklyBill();
                    $data['monthlyBill']       = \Auth::user()->monthlyBill();
                    $data['goals']             = Goal::where('created_by', '=', \Auth::user()->creatorId())->where('is_display', 1)->get();


               }
               else
               {
                 return redirect()->back()->with('error', __('Permission denied.'));
               }

                return view('dashboard.account-dashboard', $data);
            }
        }
        else
        {
            if(!file_exists(storage_path() . "/installed"))
            {
                header('location:install');
                die;
            }
            else
            {
                $settings = Utility::settings();
                if($settings['display_landing_page'] == 'on')
                {
                    return view('layouts.landing', compact('settings'));
                }
                else
                {
                    return redirect('login');
                }

            }
        }
    }

    public function project_dashboard_index()
    {
        $user = Auth::user();
        if(\Auth::user()->can('show project dashboard'))
        {
            if($user->type == 'admin' || $user->type == 'company')
            {
                $home_data = [];

                $user_projects   = Project::all()->pluck('id')->toArray();
                $project_tasks   = ProjectTask::all();
                $project_expense = Expense::all();
                $seven_days      = Utility::getLastSevenDays();

                // Total Projects
                $complete_project           = Project::where('status', 'LIKE', 'complete')->count();
                $home_data['total_project'] = [
                    'total' => count($user_projects),
                    'percentage' => Utility::getPercentage($complete_project, count($user_projects)),
                ];

                // Total Tasks
                $complete_task           = ProjectTask::where('is_complete', '=', 1)->whereRaw("find_in_set('" . $user->id . "',assign_to)")->whereIn('project_id', $user_projects)->count();
                $home_data['total_task'] = [
                    'total' => $project_tasks->count(),
                    'percentage' => Utility::getPercentage($complete_task, $project_tasks->count()),
                ];

                // Total Expense
                $total_expense        = 0;
                $total_project_amount = 0;
                foreach($user->projects as $pr)
                {
                    $total_project_amount += $pr->budget;
                }
                foreach($project_expense as $expense)
                {
                    $total_expense += $expense->amount;
                }
                $home_data['total_expense'] = [
                    'total' => $project_expense->count(),
                    'percentage' => Utility::getPercentage($total_expense, $total_project_amount),
                ];

                // Total Users
                $home_data['total_user'] = Auth::user()->contacts->count();

                // Tasks Overview Chart & Timesheet Log Chart
                $task_overview    = [];
                $timesheet_logged = [];
                foreach($seven_days as $date => $day)
                {
                    // Task
                    $task_overview[$day] = ProjectTask::where('is_complete', '=', 1)->where('marked_at', 'LIKE', $date)->count();

                    // Timesheet
                    $time                   = Timesheet::where('date', 'LIKE', $date)->pluck('time')->toArray();
                    $timesheet_logged[$day] = str_replace(':', '.', Utility::calculateTimesheetHours($time));
                }

                $home_data['task_overview']    = $task_overview;
                $home_data['timesheet_logged'] = $timesheet_logged;

                // Project Status
                $total_project  = count($user_projects);
                $project_status = [];
                foreach(Project::$project_status as $k => $v)
                {
                    $project_status[$k]['total']      = Project::where('status', 'LIKE', $k)->count();
                    $project_status[$k]['percentage'] = Utility::getPercentage($project_status[$k]['total'], $total_project);
                }
                $home_data['project_status'] = $project_status;

                // Top Due Project
                $home_data['due_project'] = Project::orderBy('end_date', 'DESC')->limit(5)->get();

                $harisekarang =   date('Y-m-d');
                $home_data['project'] = $user->projects()->where('status', '!=', 'complete')->get();
                $home_data['project_user'] = ProjectUser::all();

                // Top Due Tasks
                $home_data['due_tasks'] = ProjectTask::where('is_complete', '=', 0)->whereIn('project_id', $user_projects)->orderBy('end_date', 'DESC')->limit(5)->get();

                $home_data['last_tasks'] = ProjectTask::orderBy('end_date', 'DESC')->limit(5)->get();

                return view('dashboard.project-dashboard', compact('home_data'));
            }
            else
            {
                $home_data = [];

                $user_projects   = $user->projects()->pluck('project_id')->toArray();
                $project_tasks   = ProjectTask::whereIn('project_id', $user_projects)->get();
                $project_expense = Expense::whereIn('project_id', $user_projects)->get();
                $seven_days      = Utility::getLastSevenDays();

                // Total Projects
                $complete_project           = $user->projects()->where('status', 'LIKE', 'complete')->count();
                $home_data['total_project'] = [
                    'total' => count($user_projects),
                    'percentage' => Utility::getPercentage($complete_project, count($user_projects)),
                ];

                // Total Tasks
                $complete_task           = ProjectTask::where('is_complete', '=', 1)->whereRaw("find_in_set('" . $user->id . "',assign_to)")->whereIn('project_id', $user_projects)->count();
                $home_data['total_task'] = [
                    'total' => $project_tasks->count(),
                    'percentage' => Utility::getPercentage($complete_task, $project_tasks->count()),
                ];

                // Total Expense
                $total_expense        = 0;
                $total_project_amount = 0;
                foreach($user->projects as $pr)
                {
                    $total_project_amount += $pr->budget;
                }
                foreach($project_expense as $expense)
                {
                    $total_expense += $expense->amount;
                }
                $home_data['total_expense'] = [
                    'total' => $project_expense->count(),
                    'percentage' => Utility::getPercentage($total_expense, $total_project_amount),
                ];

                // Total Users
                $home_data['total_user'] = Auth::user()->contacts->count();

                // Tasks Overview Chart & Timesheet Log Chart
                $task_overview    = [];
                $timesheet_logged = [];
                foreach($seven_days as $date => $day)
                {
                    // Task
                    $task_overview[$day] = ProjectTask::where('is_complete', '=', 1)->where('marked_at', 'LIKE', $date)->whereIn('project_id', $user_projects)->count();

                    // Timesheet
                    $time                   = Timesheet::whereIn('project_id', $user_projects)->where('date', 'LIKE', $date)->pluck('time')->toArray();
                    $timesheet_logged[$day] = str_replace(':', '.', Utility::calculateTimesheetHours($time));
                }

                $home_data['task_overview']    = $task_overview;
                $home_data['timesheet_logged'] = $timesheet_logged;

                // Project Status
                $total_project  = count($user_projects);
                $project_status = [];
                foreach(Project::$project_status as $k => $v)
                {
                    $project_status[$k]['total']      = $user->projects->where('status', 'LIKE', $k)->count();
                    $project_status[$k]['percentage'] = Utility::getPercentage($project_status[$k]['total'], $total_project);
                }
                $home_data['project_status'] = $project_status;

                // Top Due Project
                $home_data['due_project'] = $user->projects()->orderBy('end_date', 'DESC')->limit(5)->get();

                $harisekarang =   date('Y-m-d');
                $home_data['project'] = $user->projects()->where('status', '!=', 'complete')->get();
                $home_data['project_user'] = ProjectUser::where('user_id','=', $user->id)->get();

                // Top Due Tasks
                $home_data['due_tasks'] = ProjectTask::where('is_complete', '=', 0)->whereIn('project_id', $user_projects)->orderBy('end_date', 'DESC')->limit(5)->get();

                $home_data['last_tasks'] = ProjectTask::whereIn('project_id', $user_projects)->orderBy('end_date', 'DESC')->limit(5)->get();

                return view('dashboard.project-dashboard', compact('home_data'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function saveToken(Request $request)
    {
        $authuser = Auth::user();

        $authuser->device_token = $request->token;

        $authuser->save();
        return response()->json(['token saved successfully.']);
    }

    public function hrm_dashboard_index(Request $request)
    {
        if(Auth::check())
        {
            if(\Auth::user()->can('show hrm dashboard'))
            {
                $user = Auth::user();
                if($user->type != 'client' && $user->type != 'staff_client' && $user->type != 'company' && $user->type != 'admin')
                {
                    $emp = Employee::where('user_id', '=', $user->id)->first();
                    $get_name                = $user->name;
                    $img                     = \DefaultProfileImage::create($get_name);
                    $profile                 = \Storage::put($get_name . '.jpg', $img->encode());

                    $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->leftjoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')->where('announcement_employees.employee_id', '=', $emp->id)->orWhere(
                        function ($q){
                            $q->where('announcements.department_id', '["0"]')->where('announcements.employee_id', '["0"]');
                        }
                    )->get();

                    $employees = Employee::where('user_id', '=', $user->id)->get()->pluck('name', 'id');

                    if(!empty($request->month))
                    {
                        $currentdate = strtotime($request->month);
                        $month       = date('m', $currentdate);
                        $year        = date('Y', $currentdate);
                        $curMonth    = date('M-Y', strtotime($request->month));

                    }
                    else
                    {
                        $month    = date('m');
                        $year     = date('Y');
                        $curMonth = date('M-Y', strtotime($year . '-' . $month));
                    }

                    $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
                    for($i = 1; $i <= $num_of_days; $i++)
                    {
                        $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
                    }

                    $employeesAttendances = [];
                    $totalPresent        = $totalLeave = $totalEarlyLeave = 0;

                    foreach ($employees as $id => $employee) {
                        $attendances['name'] = $employee;
                    
                        foreach ($dates as $date) {
                            $dateFormat = $year . '-' . $month . '-' . $date;
                    
                            if ($dateFormat <= date('Y-m-d')) {
                                if ($this->isWeekend($dateFormat)) {
                                    $attendanceStatus[$date] = 'W'; // Jika hari adalah Sabtu atau Minggu, status kehadiran diatur sebagai 'W'
                                } else {
                                    $employeeAttendance = AttendanceEmployee::where('employee_id', $id)->where('date', $dateFormat)->first();
                    
                                    if (!empty($employeeAttendance) && $employeeAttendance->status == 'Present') {
                                        $attendanceStatus[$date] = 'P';
                                        $totalPresent += 1;
                                    } else {
                                        $attendanceStatus[$date] = '';
                                    }
                                }
                            } else {
                                $attendanceStatus[$date] = '';
                            }
                        }            
                        $attendances['status'] = $attendanceStatus;
                        $employeesAttendances[] = $attendances;
                    }

                    $data['totalPresent']    = $totalPresent;
                    $data['curMonth']        = $curMonth;


                    $meetings  = Meeting::orderBy('meetings.id', 'desc')->take(5)->leftjoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')->where('meeting_employees.employee_id', '=', $emp->id)->orWhere(
                        function ($q){
                            $q->where('meetings.department_id', '["0"]')->where('meetings.employee_id', '["0"]');
                        }
                    )->get();
                    $events    = Event::leftjoin('event_employees', 'events.id', '=', 'event_employees.event_id')->where('event_employees.employee_id', '=', $emp->id)->orWhere(
                        function ($q){
                            $q->where('events.department_id', '["0"]')->where('events.employee_id', '["0"]');
                        }
                    )->get();

                    $arrEvents = [];
                    foreach($events as $event)
                    {

                        $arr['id']              = $event['id'];
                        $arr['title']           = $event['title'];
                        $arr['start']           = $event['start_date'];
                        $arr['end']             = $event['end_date'];
                        $arr['backgroundColor'] = $event['color'];
                        $arr['borderColor']     = "#fff";
                        $arr['textColor']       = "white";
                        $arrEvents[]            = $arr;
                    }

                    $date               = date("Y-m-d");
                    $time               = date("H:i:s");
                    $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', '=', $date)->first();

                    if($emp->branch_id == 1)
                    {
                        $officeTime['startTime']    = Utility::getValByName('company_start_time');
                        $officeTime['endTime']      = Utility::getValByName('company_end_time');
                    }
                    elseif($emp->branch_id == 2)
                    {
                        $officeTime['startTime']    = "08:30";
                        $officeTime['endTime']      = "17:30";
                    }
                    elseif($emp->branch_id == 3)
                    {
                        $officeTime['startTime']    = "08:00";
                        $officeTime['endTime']      = "17:00";
                    }

                    return view('dashboard.dashboard', compact('employeesAttendances', 'dates', 'data', 'profile','arrEvents', 'announcements', 'employees', 'meetings', 'employeeAttendance', 'officeTime'));
                }
                elseif($user->type = 'admin')
                {
                    $events    = Event::all();
                    $arrEvents = [];

                    foreach($events as $event)
                    {
                        $arr['id']    = $event['id'];
                        $arr['title'] = $event['title'];
                        $arr['start'] = $event['start_date'];
                        $arr['end']   = $event['end_date'];

                        $arr['backgroundColor'] = $event['color'];
                        $arr['borderColor']     = "#fff";
                        $arr['textColor']       = "white";
                        $arr['url']             = route('event.edit', $event['id']);

                        $arrEvents[] = $arr;
                    }


                    $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->get();


                    $employees           = User::where('type', '!=', 'client')->get();
                    $countEmployee = count($employees);

                    $emp = Employee::where('user_id', '=', Auth::user()->id)->first();

                    $date               = date("Y-m-d");
                    $time               = date("H:i:s");
                    $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', '=', $date)->first();

                    if($emp->branch_id == 1)
                    {
                        $officeTime['startTime']    = Utility::getValByName('company_start_time');
                        $officeTime['endTime']      = Utility::getValByName('company_end_time');
                    }
                    elseif($emp->branch_id == 2)
                    {
                        $officeTime['startTime']    = "08:30";
                        $officeTime['endTime']      = "17:30";
                    }
                    elseif($emp->branch_id == 3)
                    {
                        $officeTime['startTime']    = "08:00";
                        $officeTime['endTime']      = "17:00";
                    }

                    $get_name                = $user->name;
                    $img                     = \DefaultProfileImage::create($get_name);
                    $profile                 = \Storage::put($get_name . '.jpg', $img->encode());

                    $user      = User::where('type', '!=', 'client')->where('type', '!=', 'intern')->get();
                    $countUser = count($user);

                    $intern     = User::where('type', '!=', 'client')->where('type', '=', 'intern')->get();
                    $countIntern = count($intern);

                    $countTrainer    = Trainer::all()->count();
                    $onGoingTraining = Training::where('status', '=', 1)->count();
                    $doneTraining    = Training::where('status', '=', 2)->count();

                    $currentDate = date('Y-m-d');

                    $employees   = User::where('type', '=', 'client')->get();
                    $countClient = count($employees);
                    $notClockIn  = AttendanceEmployee::where('date', '=', $currentDate)->get()->pluck('employee_id');

                    $notClockIns = Employee::whereNotIn('id', $notClockIn)->get();
                    $activeJob   = Job::where('status', 'active')->count();
                    $inActiveJOb = Job::where('status', 'in_active')->count();


                    $meetings = Meeting::limit(5)->get();
                    $officeTime['startTime'] = Utility::getValByName('company_start_time');
                    $officeTime['endTime']   = Utility::getValByName('company_end_time');

                    return view('dashboard.dashboard', compact('profile','countIntern','arrEvents', 'officeTime', 'onGoingTraining', 'activeJob', 'inActiveJOb', 'doneTraining', 'announcements', 'employees', 'meetings', 'countTrainer', 'countClient', 'countUser', 'notClockIns', 'countEmployee', 'employeeAttendance'));
                }
                elseif($user->type = 'company')
                {
                    $events    = Event::all();
                    $arrEvents = [];

                    foreach($events as $event)
                    {
                        $arr['id']    = $event['id'];
                        $arr['title'] = $event['title'];
                        $arr['start'] = $event['start_date'];
                        $arr['end']   = $event['end_date'];

                        $arr['backgroundColor'] = $event['color'];
                        $arr['borderColor']     = "#fff";
                        $arr['textColor']       = "white";
                        $arr['url']             = route('event.edit', $event['id']);

                        $arrEvents[] = $arr;
                    }


                    $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->get();


                    $employees           = User::where('type', '!=', 'client')->get();
                    $countEmployee = count($employees);

                    $emp = Employee::where('user_id', '=', Auth::user()->id)->first();

                    $date               = date("Y-m-d");
                    $time               = date("H:i:s");
                    $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', '=', $date)->first();

                    if($emp->branch_id == 1)
                    {
                        $officeTime['startTime']    = Utility::getValByName('company_start_time');
                        $officeTime['endTime']      = Utility::getValByName('company_end_time');
                    }
                    elseif($emp->branch_id == 2)
                    {
                        $officeTime['startTime']    = "08:30";
                        $officeTime['endTime']      = "17:30";
                    }
                    elseif($emp->branch_id == 3)
                    {
                        $officeTime['startTime']    = "08:00";
                        $officeTime['endTime']      = "17:00";
                    }

                    $get_name                = $user->name;
                    $img                     = \DefaultProfileImage::create($get_name);
                    $profile                 = \Storage::put($get_name . '.jpg', $img->encode());

                    $user      = User::where('type', '!=', 'client')->where('type', '!=', 'intern')->get();
                    $countUser = count($user);

                    $intern     = User::where('type', '!=', 'client')->where('type', '=', 'intern')->get();
                    $countIntern = count($intern);

                    $countTrainer    = Trainer::all()->count();
                    $onGoingTraining = Training::where('status', '=', 1)->count();
                    $doneTraining    = Training::where('status', '=', 2)->count();

                    $currentDate = date('Y-m-d');

                    $employees   = User::where('type', '=', 'client')->get();
                    $countClient = count($employees);
                    $notClockIn  = AttendanceEmployee::where('date', '=', $currentDate)->get()->pluck('employee_id');

                    $notClockIns = Employee::whereNotIn('id', $notClockIn)->get();
                    $activeJob   = Job::where('status', 'active')->count();
                    $inActiveJOb = Job::where('status', 'in_active')->count();


                    $meetings = Meeting::limit(5)->get();
                    $officeTime['startTime'] = Utility::getValByName('company_start_time');
                    $officeTime['endTime']   = Utility::getValByName('company_end_time');

                    return view('dashboard.dashboard', compact('profile','countIntern','arrEvents', 'officeTime', 'onGoingTraining', 'activeJob', 'inActiveJOb', 'doneTraining', 'announcements', 'employees', 'meetings', 'countTrainer', 'countClient', 'countUser', 'notClockIns', 'countEmployee', 'employeeAttendance'));
                }
                else
                {
                    $events    = Event::where('created_by', '=', \Auth::user()->creatorId())->get();
                    $arrEvents = [];

                    foreach($events as $event)
                    {
                        $arr['id']    = $event['id'];
                        $arr['title'] = $event['title'];
                        $arr['start'] = $event['start_date'];
                        $arr['end']   = $event['end_date'];

                        $arr['backgroundColor'] = $event['color'];
                        $arr['borderColor']     = "#fff";
                        $arr['textColor']       = "white";
                        $arr['url']             = route('event.edit', $event['id']);

                        $arrEvents[] = $arr;
                    }


                    $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->where('created_by', '=', \Auth::user()->creatorId())->get();


                    $employees           = User::where('type', '!=', 'client')->where('type', '!=', 'company')->where('created_by', '=', \Auth::user()->creatorId())->get();
                    $countEmployee = count($employees);

                    $emp = Employee::where('user_id', '=', Auth::user()->id)->first();


                    $date               = date("Y-m-d");
                    $time               = date("H:i:s");
                    $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', '=', $date)->first();

                    if($emp->branch_id == 1)
                    {
                        $officeTime['startTime']    = Utility::getValByName('company_start_time');
                        $officeTime['endTime']      = Utility::getValByName('company_end_time');
                    }
                    elseif($emp->branch_id == 2)
                    {
                        $officeTime['startTime']    = "08:30";
                        $officeTime['endTime']      = "17:30";
                    }
                    elseif($emp->branch_id == 3)
                    {
                        $officeTime['startTime']    = "08:00";
                        $officeTime['endTime']      = "17:00";
                    }

                    $get_name                = $user->name;
                    $img                     = \DefaultProfileImage::create($get_name);
                    $profile                 = \Storage::put($get_name . '.jpg', $img->encode());

                    $user      = User::where('type', '!=', 'client')->where('type', '!=', 'company')->where('created_by', '=', \Auth::user()->creatorId())->get();
                    $countUser = count($user);


                    $countTrainer    = Trainer::where('created_by', '=', \Auth::user()->creatorId())->count();
                    $onGoingTraining = Training::where('status', '=', 1)->where('created_by', '=', \Auth::user()->creatorId())->count();
                    $doneTraining    = Training::where('status', '=', 2)->where('created_by', '=', \Auth::user()->creatorId())->count();

                    $currentDate = date('Y-m-d');

                    $employees   = User::where('type', '=', 'client')->where('created_by', '=', \Auth::user()->creatorId())->get();
                    $countClient = count($employees);
                    $notClockIn  = AttendanceEmployee::where('date', '=', $currentDate)->get()->pluck('employee_id');

                    $notClockIns = Employee::where('created_by', '=', \Auth::user()->creatorId())->whereNotIn('id', $notClockIn)->get();
                    $activeJob   = Job::where('status', 'active')->where('created_by', '=', \Auth::user()->creatorId())->count();
                    $inActiveJOb = Job::where('status', 'in_active')->where('created_by', '=', \Auth::user()->creatorId())->count();


                    $meetings = Meeting::where('created_by', '=', \Auth::user()->creatorId())->limit(5)->get();

                    return view('dashboard.dashboard', compact('profile','arrEvents', 'onGoingTraining', 'activeJob', 'inActiveJOb', 'doneTraining', 'announcements', 'employees', 'meetings', 'countTrainer', 'countClient', 'countUser', 'notClockIns', 'countEmployee', 'employeeAttendance'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            if(!file_exists(storage_path() . "/installed"))
            {
                header('location:install');
                die;
            }
            else
            {
                $settings = Utility::settings();
                if($settings['display_landing_page'] == 'on')
                {


                    return view('layouts.landing');
                }
                else
                {
                    return redirect('login');
                }

            }
        }
    }

    // Load Dashboard user's using ajax
    public function filterView(Request $request)
    {
        $usr   = Auth::user();
        $users = User::where('id', '!=', $usr->id);

        if($request->ajax())
        {
            if(!empty($request->keyword))
            {
                $users->where('name', 'LIKE', $request->keyword . '%')->orWhereRaw('FIND_IN_SET("' . $request->keyword . '",skills)');
            }

            $users      = $users->get();
            $returnHTML = view('dashboard.view', compact('users'))->render();

            return response()->json(
                [
                    'success' => true,
                    'html' => $returnHTML,
                ]
            );
        }
    }

    public function clientView()
    {

        if(Auth::check())
        {

            if(Auth::user()->type == 'client')
            {
                $transdate = date('Y-m-d', time());
                $currentYear  = date('Y');

                $calenderTasks = [];
                $chartData     = [];
                $arrCount      = [];
                $arrErr        = [];
                $m             = date("m");
                $de            = date("d");
                $y             = date("Y");
                $format        = 'Y-m-d';
                $user          = \Auth::user();
                if(\Auth::user()->can('View Task'))
                {
                    $company_setting = Utility::settings();
                }
                $arrTemp = [];
                for($i = 0; $i <= 7 - 1; $i++)
                {
                    $date                 = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));
                    $arrTemp['date'][]    = __(date('D', strtotime($date)));
                    $arrTemp['invoice'][] = 10;
                    $arrTemp['payment'][] = 20;
                }

                $chartData = $arrTemp;

                foreach($user->clientDeals as $deal)
                {
                    foreach($deal->tasks as $task)
                    {
                        $calenderTasks[] = [
                            'title' => $task->name,
                            'start' => $task->date,
                            'url' => route(
                                'deals.tasks.show', [
                                                      $deal->id,
                                                      $task->id,
                                                  ]
                            ),
                            'className' => ($task->status) ? 'bg-success border-success' : 'bg-warning border-warning',
                        ];
                    }

                    $calenderTasks[] = [
                        'title' => $deal->name,
                        'start' => $deal->created_at->format('Y-m-d'),
                        'url' => route('deals.show', [$deal->id]),
                        'className' => 'deal bg-primary border-primary',
                    ];
                }
                $client_deal = $user->clientDeals->pluck('id');

                $arrCount['deal'] = $user->clientDeals->count();
                if(!empty($client_deal->first()))
                {
                    $arrCount['task'] = DealTask::whereIn('deal_id', [$client_deal])->count();
                }
                else
                {
                    $arrCount['task'] = 0;
                }


                $project['projects']             = Project::where('client_id', '=', Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->where('end_date', '>', date('Y-m-d'))->limit(5)->orderBy('end_date')->get();
                $project['projects_count']       = count($project['projects']);
                $user_projects                   = Project::where('client_id', \Auth::user()->id)->pluck('id', 'id')->toArray();
                $tasks                           = ProjectTask::whereIn('project_id', $user_projects)->where('created_by', \Auth::user()->creatorId())->get();
                $project['projects_tasks_count'] = count($tasks);
                $project['project_budget']       = Project::where('client_id', Auth::user()->id)->sum('budget');

                $project_last_stages      = Auth::user()->last_projectstage();
                $project_last_stage       = (!empty($project_last_stages) ? $project_last_stages->id : 0);
                $project['total_project'] = Auth::user()->user_project();
                $total_project_task       = Auth::user()->created_total_project_task();
                $allProject               = Project::where('client_id', \Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->get();
                $allProjectCount          = count($allProject);

                $bugs                               = Bug::whereIn('project_id', $user_projects)->where('created_by', \Auth::user()->creatorId())->get();
                $project['projects_bugs_count']     = count($bugs);
                $bug_last_stage                     = BugStatus::orderBy('order', 'DESC')->first();
                $completed_bugs                     = Bug::whereIn('project_id', $user_projects)->where('status', $bug_last_stage->id)->where('created_by', \Auth::user()->creatorId())->get();
                $allBugCount                        = count($bugs);
                $completedBugCount                  = count($completed_bugs);
                $project['project_bug_percentage']  = ($allBugCount != 0) ? intval(($completedBugCount / $allBugCount) * 100) : 0;
                $complete_task                      = Auth::user()->project_complete_task($project_last_stage);
                $completed_project                  = Project::where('client_id', \Auth::user()->id)->where('status', 'complete')->where('created_by', \Auth::user()->creatorId())->get();
                $completed_project_count            = count($completed_project);
                $project['project_percentage']      = ($allProjectCount != 0) ? intval(($completed_project_count / $allProjectCount) * 100) : 0;
                $project['project_task_percentage'] = ($total_project_task != 0) ? intval(($complete_task / $total_project_task) * 100) : 0;
                $invoice                            = [];
                $top_due_invoice                    = [];
                $invoice['total_invoice']           = 5;
                $complete_invoice                   = 0;
                $total_due_amount                   = 0;
                $top_due_invoice                    = array();
                $pay_amount                         = 0;

                if(Auth::user()->type == 'client')
                {
                    if(!empty($project['project_budget']))
                    {
                        $project['client_project_budget_due_per'] = intval(($pay_amount / $project['project_budget']) * 100);
                    }
                    else
                    {
                        $project['client_project_budget_due_per'] = 0;
                    }

                }

                $top_tasks       = Auth::user()->created_top_due_task();
                $users['staff']  = User::where('created_by', '=', Auth::user()->creatorId())->count();
                $users['user']   = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '!=', 'client')->count();
                $users['client'] = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '=', 'client')->count();
                $project_status  = array_values(Project::$project_status);
                $projectData     = \App\Models\Project::getProjectStatus();

                $taskData        = \App\Models\TaskStage::getChartData();

                return view('dashboard.clientView', compact('calenderTasks', 'arrErr', 'arrCount', 'chartData', 'project', 'invoice', 'top_tasks', 'top_due_invoice', 'users', 'project_status', 'projectData', 'taskData','transdate','currentYear'));
            }
            elseif(Auth::user()->type = "staff_client")
            {
                $transdate = date('Y-m-d', time());
                $currentYear  = date('Y');

                $calenderTasks = [];
                $chartData     = [];
                $arrCount      = [];
                $arrErr        = [];
                $m             = date("m");
                $de            = date("d");
                $y             = date("Y");
                $format        = 'Y-m-d';
                $user          = \Auth::user();
                if(\Auth::user()->can('View Task'))
                {
                    $company_setting = Utility::settings();
                }
                $arrTemp = [];
                for($i = 0; $i <= 7 - 1; $i++)
                {
                    $date                 = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));
                    $arrTemp['date'][]    = __(date('D', strtotime($date)));
                    $arrTemp['invoice'][] = 10;
                    $arrTemp['payment'][] = 20;
                }

                $chartData = $arrTemp;

                foreach($user->clientDeals as $deal)
                {
                    foreach($deal->tasks as $task)
                    {
                        $calenderTasks[] = [
                            'title' => $task->name,
                            'start' => $task->date,
                            'url' => route(
                                'deals.tasks.show', [
                                                      $deal->id,
                                                      $task->id,
                                                  ]
                            ),
                            'className' => ($task->status) ? 'bg-success border-success' : 'bg-warning border-warning',
                        ];
                    }

                    $calenderTasks[] = [
                        'title' => $deal->name,
                        'start' => $deal->created_at->format('Y-m-d'),
                        'url' => route('deals.show', [$deal->id]),
                        'className' => 'deal bg-primary border-primary',
                    ];
                }
                $client_deal = $user->clientDeals->pluck('id');

                $arrCount['deal'] = $user->clientDeals->count();
                if(!empty($client_deal->first()))
                {
                    $arrCount['task'] = DealTask::whereIn('deal_id', [$client_deal])->count();
                }
                else
                {
                    $arrCount['task'] = 0;
                }

                $users = User::where('id', '=', Auth::user()->id)->get();

                $project['projects']             = Project::where('client_id', '=', $users[0]['client_id'])->where('created_by', \Auth::user()->creatorId())->where('end_date', '>', date('Y-m-d'))->limit(5)->orderBy('end_date')->get();
                $project['projects_count']       = count($project['projects']);
                $user_projects                   = Project::where('client_id', $users[0]['client_id'])->pluck('id', 'id')->toArray();
                $tasks                           = ProjectTask::whereIn('project_id', $user_projects)->where('created_by', \Auth::user()->creatorId())->get();
                $project['projects_tasks_count'] = count($tasks);
                $project['project_budget']       = Project::where('client_id', $users[0]['client_id'])->sum('budget');

                $project_last_stages      = Auth::user()->last_projectstage();
                $project_last_stage       = (!empty($project_last_stages) ? $project_last_stages->id : 0);
                $project['total_project'] = Auth::user()->user_project();
                $total_project_task       = Auth::user()->created_total_project_task();
                $allProject               = Project::where('client_id', $users[0]['client_id'])->where('created_by', \Auth::user()->creatorId())->get();
                $allProjectCount          = count($allProject);

                $bugs                               = Bug::whereIn('project_id', $user_projects)->where('created_by', \Auth::user()->creatorId())->get();
                $project['projects_bugs_count']     = count($bugs);
                $bug_last_stage                     = BugStatus::orderBy('order', 'DESC')->first();
                $completed_bugs                     = Bug::whereIn('project_id', $user_projects)->where('status', $bug_last_stage->id)->where('created_by', \Auth::user()->creatorId())->get();
                $allBugCount                        = count($bugs);
                $completedBugCount                  = count($completed_bugs);
                $project['project_bug_percentage']  = ($allBugCount != 0) ? intval(($completedBugCount / $allBugCount) * 100) : 0;
                $complete_task                      = Auth::user()->project_complete_task($project_last_stage);
                $completed_project                  = Project::where('client_id', $users[0]['client_id'])->where('status', 'complete')->where('created_by', \Auth::user()->creatorId())->get();
                $completed_project_count            = count($completed_project);
                $project['project_percentage']      = ($allProjectCount != 0) ? intval(($completed_project_count / $allProjectCount) * 100) : 0;
                $project['project_task_percentage'] = ($total_project_task != 0) ? intval(($complete_task / $total_project_task) * 100) : 0;
                $invoice                            = [];
                $top_due_invoice                    = [];
                $invoice['total_invoice']           = 5;
                $complete_invoice                   = 0;
                $total_due_amount                   = 0;
                $top_due_invoice                    = array();
                $pay_amount                         = 0;

                if(Auth::user()->type == 'staff_client')
                {
                    if(!empty($project['project_budget']))
                    {
                        $project['client_project_budget_due_per'] = intval(($pay_amount / $project['project_budget']) * 100);
                    }
                    else
                    {
                        $project['client_project_budget_due_per'] = 0;
                    }

                }

                $top_tasks       = Auth::user()->created_top_due_task();
                $users['staff']  = User::where('created_by', '=', Auth::user()->creatorId())->count();
                $users['user']   = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '!=', 'client')->count();
                $users['client'] = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '=', 'client')->count();
                $project_status  = array_values(Project::$project_status);
                $projectData     = \App\Models\Project::getProjectStatus();

                $taskData        = \App\Models\TaskStage::getChartData();

                return view('dashboard.clientView', compact('calenderTasks', 'arrErr', 'arrCount', 'chartData', 'project', 'invoice', 'top_tasks', 'top_due_invoice', 'users', 'project_status', 'projectData', 'taskData','transdate','currentYear'));
            }
        }
    }

    public function getOrderChart($arrParam)
    {
        $arrDuration = [];
        if($arrParam['duration'])
        {
            if($arrParam['duration'] == 'week')
            {
                $previous_week = strtotime("-2 week +1 day");
                for($i = 0; $i < 14; $i++)
                {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
                    $previous_week                              = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }

        $arrTask          = [];
        $arrTask['label'] = [];
        $arrTask['data']  = [];
        foreach($arrDuration as $date => $label)
        {

            $data               = Order::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
            $arrTask['label'][] = $label;
            $arrTask['data'][]  = $data->total;
        }

        return $arrTask;
    }

    public function stopTracker(Request $request)
    {
        if(Auth::user()->isClient())
        {
            return Utility::error_res(__('Permission denied.'));
        }
        $validatorArray = [
            'name' => 'required|max:120',
            'project_id' => 'required|integer',
        ];
        $validator      = Validator::make(
            $request->all(), $validatorArray
        );
        if($validator->fails())
        {
            return Utility::error_res($validator->errors()->first());
        }
        $tracker = TimeTracker::where('created_by', '=', Auth::user()->id)->where('is_active', '=', 1)->first();
        if($tracker)
        {
            $tracker->end_time   = $request->has('end_time') ? $request->input('end_time') : date("Y-m-d H:i:s");
            $tracker->is_active  = 0;
            $tracker->total_time = Utility::diffance_to_time($tracker->start_time, $tracker->end_time);
            $tracker->save();

            return Utility::success_res(__('Add Time successfully.'));
        }

        return Utility::error_res('Tracker not found.');
    }

    public function home(Request $request)
    {
        if(Auth::check())
        {
            if(\Auth::user()->can('show hrm dashboard'))
            {
                    $user = Auth::user();
                    if($user->type != 'client' && $user->type != 'staff_client' && $user->type != 'company' && $user->type != 'admin' && $user->type != 'partners')
                    {
                        $emp = Employee::where('user_id', '=', $user->id)->first();
                        $get_name                = $user->name;
                        $img                     = \DefaultProfileImage::create($get_name);
                        $profile                 = \Storage::put($get_name . '.jpg', $img->encode());

                        $employees = Employee::where('user_id', '=', $user->id)->get()->pluck('name', 'id');

                        if(!empty($request->month))
                        {
                            $currentdate = strtotime($request->month);
                            $month       = date('m', $currentdate);
                            $year        = date('Y', $currentdate);
                            $curMonth    = date('M-Y', strtotime($request->month));

                        }
                        else
                        {
                            $month    = date('m');
                            $year     = date('Y');
                            $curMonth = date('M-Y', strtotime($year . '-' . $month));
                        }

                        $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
                        for($i = 1; $i <= $num_of_days; $i++)
                        {
                            $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
                        }

                        $employeesAttendances = [];
                        $totalPresent        = $totalLeave = $totalEarlyLeave = 0;

                        foreach ($employees as $id => $employee) {
                            $attendances['name'] = $employee;
                        
                            foreach ($dates as $date) {
                                $dateFormat = $year . '-' . $month . '-' . $date;
                        
                                if ($dateFormat <= date('Y-m-d')) {
                                                if ($this->isWeekend($dateFormat)) {
                                                    $employeeAttendance = AttendanceEmployee::where('employee_id', $id)
                                                                                            ->where('date', $dateFormat)
                                                                                            ->where('status', 'Present')
                                                                                            ->first();
                                                    
                                                    if (!empty($employeeAttendance)) {
                                                        $attendanceStatus[$date] = 'P'; // Jika ada kehadiran pada hari Sabtu atau Minggu, status kehadiran diatur sebagai 'P'
                                                        $attendanceLong[$date] = $employeeAttendance->longitude;
                                                        $attendanceLat[$date] = $employeeAttendance->latitude;
                                                        $totalPresent += 1;
                                                    } else {
                                                        $attendanceStatus[$date] = 'W'; // Jika tidak ada kehadiran pada hari Sabtu atau Minggu, status kehadiran diatur sebagai 'W'
                                                    }
                                                } 
                                                else {
                                                    $employeeAttendance = AttendanceEmployee::where('employee_id', $id)
                                                                                            ->where('date', $dateFormat)
                                                                                            ->first();
                                            
                                                    if (!empty($employeeAttendance) && $employeeAttendance->status == 'Present') {
                                                        $attendanceStatus[$date] = 'P';
                                                        $attendanceLong[$date] = $employeeAttendance->longitude;
                                                        $attendanceLat[$date] = $employeeAttendance->latitude;
                                                        $totalPresent += 1;
                                                    } else {
                                                        $attendanceStatus[$date] = '';
                                                        $attendanceLong[$date] = '';
                                                        $attendanceLat[$date] = '';
                                                    }
                                                }
                                } else {
                                    $attendanceStatus[$date] = '';
                                    $attendanceLong[$date] = '';
                                    $attendanceLat[$date] = '';
                                }  
                            }     
                            $attendances['status'] = $attendanceStatus;
                            $attendances['longitude'] = $attendanceLong;
                            $attendances['latitude'] = $attendanceLat;
                            $employeesAttendances[] = $attendances;
                        }

                        $data['totalPresent']    = $totalPresent;
                        $data['curMonth']        = $curMonth;

                        $date               = date("Y-m-d");
                        $time               = date("H:i:s");
                        $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', '=', $date)->first();

                        if($emp->branch_id == 1)
                        {
                            $officeTime['startTime']    = Utility::getValByName('company_start_time');
                            $officeTime['endTime']      = Utility::getValByName('company_end_time');
                        }
                        elseif($emp->branch_id == 2)
                        {
                            $officeTime['startTime']    = "08:30";
                            $officeTime['endTime']      = "17:30";
                        }
                        elseif($emp->branch_id == 3)
                        {
                            $officeTime['startTime']    = "08:00";
                            $officeTime['endTime']      = "17:00";
                        }

                        // attendance statistics
                        $absentData = [];
                        for ($day = 1; $day <= 31; $day++) {
                            $months = $request->month ?? date('Y-m');
                            $date = sprintf('%s-%02d', $months, $day);
                            
                            $absentCount = AttendanceEmployee::where('date', '=', $date)
                                ->where('status', '=', 'Present')->whereHas('employee', function ($query) use ($emp) {
                                    $query->where('employee_id', '=', $emp->id);
                                })
                                ->count();
                            
                            $lateCount = 0;
                            
                            $lateCount = AttendanceEmployee::where('date', '=', $date)
                                ->where('status', '=', 'Present')->whereHas('employee', function ($query) use ($emp, $officeTime) {
                                    $query->where('employee_id', '=', $emp->id)
                                        ->whereTime('clock_in', '>', $officeTime['startTime']);
                                })
                                ->count();
            
                            $absentData[] = $absentCount;
                            $lateData[] = $lateCount;
                        }

                        //overtime statistics
                        // Ambil data UserOvertime berdasarkan bulan dan tahun
                        $overtimeData = UserOvertime::where('user_id', $emp->id)
                            ->whereMonth('start_date', $month)
                            ->whereYear('start_date', $year)
                            ->get();

                        // Inisialisasi variabel untuk statistik
                        $overtimePerDay = [];
                        $totalOvertimeHours = 0;
                        $approvedOvertimeCount = 0;

                        foreach ($overtimeData as $overtime) {
                            // Hitung total jam lembur
                            $startTime = strtotime($overtime->start_time);
                            $endTime = strtotime($overtime->end_time);

                            if($overtime->end_time == '00:00:00' && $overtime->total_time !== null) {
                                $endTime = strtotime('24:00:00');
                            }

                            $hoursWorked = ($endTime - $startTime) / 3600; // Konversi detik ke jam
                            $totalOvertimeHours += $hoursWorked;

                            // Hitung lembur per hari
                            $date = date('Y-m-d', strtotime($overtime->start_date));
                            if(!isset($overtimePerDay[$date])) {
                                $overtimePerDay[$date] = 0;
                            }
                            $overtimePerDay[$date] += $hoursWorked;

                            // Hitung jumlah lembur yang disetujui
                            if($overtime->status == 'Approved') {
                                $approvedOvertimeCount++;
                            }
                        }

                        $data_absen = $absentData;
                        $data_late = $lateData;

                        // total leave user
                        // Calculate total leave days taken by the user
                        $totalLeaveDays = Leave::where('leave_type_id', '1')->where('employee_id', $emp->id)->whereYear('applied_on', $year)->where('status','Approved')->sum('total_leave_days');

                        // Calculate total allocated leave days from LeaveType model
                        $totalAllocatedLeaveDays = LeaveType::where('id', '1')->sum('days');

                        // Calculate remaining leave days
                        $totalRemainingLeaveDays = $totalAllocatedLeaveDays - $totalLeaveDays;

                        // total leave user
                        // Calculate total sick days by the user
                        $totalSickDays = Leave::where('absence_type', 'sick')->where('employee_id', $emp->id)->whereMonth('applied_on', $month)->whereYear('applied_on', $year)->sum('total_sick_days');

                        // total medical allowance
                        $reimbusment_counts = [];
                        $currentYear = now()->year;
                        $reimbursment_types = ReimbursmentType::where('created_by', \Auth::user()->creatorId())->get();

                        foreach ($reimbursment_types as $type) {
                            $counts = Reimbursment::select(\DB::raw('COALESCE(SUM(reimbursment.amount), 0) AS total_amount'))
                                ->where('reimbursment_type', $type->title)
                                ->whereYear('date', $currentYear)
                                ->where('employee_id', $emp->id)
                                ->where('status', '=', 'Paid')
                                ->first();

                            $reimbusment_count['total_amount'] = !empty($counts) ? $counts['total_amount'] : 0;
                            $reimbusment_count['amount'] = $type->amount;
                            $reimbusment_counts[] = $reimbusment_count;
                        }

                        // Pass the first reimbursement type data to the view (you can extend this to handle multiple types)
                        $totalReimbursement = $reimbusment_counts[0]['total_amount'] ?? 0;
                        $totalReimbursementAmount = $reimbusment_counts[0]['amount'] ?? 0;

                        // chart timesheet
                        $timesheetData = [];
                        foreach ($dates as $date) {
                            $dateFormat = $year . '-' . $month . '-' . $date;
                            $totalTime = Timesheet::where('created_by', $user->id)
                                                ->whereDate('date', $dateFormat)
                                                ->sum('time') / 10000;
                            $timesheetData[] = $totalTime;
                        }

                        // Hitung jumlah proyek berdasarkan status
                        $projectStatusCounts = ProjectUser::join('projects', 'project_users.project_id', '=', 'projects.id')
                        ->select('projects.status', \DB::raw('count(*) as total'))
                        ->where('project_users.user_id', $user->id) // Sesuaikan dengan user_id yang ingin Anda filter
                        ->groupBy('projects.status')
                        ->pluck('total', 'projects.status')
                        ->all();



                        $home_data = [];

                        $user_projects   = $user->projects()->pluck('project_id')->toArray();

                        // Analitik Jumlah Tugas per Proyek
                        $tasksPerProject = ProjectTask::whereIn('project_id', $user_projects)
                        ->select('project_id', \DB::raw('count(*) as total_tasks'))
                        ->groupBy('project_id')
                        ->pluck('total_tasks', 'project_id')
                        ->all();

                        // Analitik Tugas Menurut Status
                        $tasksStatusPerProject = ProjectTask::whereIn('project_id', $user_projects)
                            ->select('is_complete', \DB::raw('count(*) as total_tasks'))
                            ->groupBy('is_complete')
                            ->pluck('total_tasks', 'is_complete')
                            ->all();

                        // Pastikan nilai default jika tidak ada data
                        $completedTasks = $tasksStatusPerProject[1] ?? 0;
                        $incompleteTasks = $tasksStatusPerProject[0] ?? 0;

                        // Analitik Tugas per Prioritas
                        $tasksPriorityPerProject = ProjectTask::whereIn('project_id', $user_projects)
                            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
                            ->select('projects.project_name', 'priority', \DB::raw('count(*) as total_tasks'))
                            ->groupBy('projects.project_name', 'priority')
                            ->pluck('total_tasks', 'projects.project_name', 'priority')
                            ->all();


                        // Analitik Tugas yang Tertunda
                        $overdueTasksPerProject = ProjectTask::whereIn('project_tasks.project_id', $user_projects)
                        ->whereDate('project_tasks.end_date', '<', now())
                        ->where('project_tasks.is_complete', '=', 0)
                        ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
                        ->select('projects.project_name', \DB::raw('count(project_tasks.id) as total_overdue_tasks'))
                        ->groupBy('projects.project_name')
                        ->pluck('total_overdue_tasks', 'projects.project_name')
                        ->all();

                        $project_tasks   = ProjectTask::whereIn('project_id', $user_projects)->get();
                        $project_expense = Expense::whereIn('project_id', $user_projects)->get();
                        $seven_days      = Utility::getLastSevenDays();

                        // Total Projects
                        $complete_project           = $user->projects()->where('status', 'LIKE', 'complete')->count();
                        $home_data['total_project'] = [
                            'total' => count($user_projects),
                            'percentage' => Utility::getPercentage($complete_project, count($user_projects)),
                        ];

                        // Total Tasks
                        $complete_task           = ProjectTask::where('is_complete', '=', 1)->whereRaw("find_in_set('" . $user->id . "',assign_to)")->whereIn('project_id', $user_projects)->count();
                        $home_data['total_task'] = [
                            'total' => $project_tasks->count(),
                            'percentage' => Utility::getPercentage($complete_task, $project_tasks->count()),
                        ];

                        // Top Due Project
                        $home_data['due_project'] = $user->projects()->orderBy('end_date', 'DESC')->limit(5)->get();

                        $harisekarang =   date('Y-m-d');
                        $home_data['project'] = $user->projects()->where('status', '!=', 'complete')->get();
                        $home_data['project_user'] = ProjectUser::where('user_id','=', $user->id)->get();

                        if(\Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                        {
                            $users        = \Auth::user();
                            $employee     = Employee::where('user_id', '=', $users->id)->first();
                            $approval     = UserOvertime::where('approval', '=', $employee->id)->where('status','=', 'Pending')->get();
                        }
                        else
                        {
                            $users        = \Auth::user();
                            $employee     = Employee::where('user_id', '=', $users->id)->first();
                            $approval     = UserOvertime::where('approval', '=', $employee->id)->where('status','=', 'Pending')->get();
                        }

                        return view('dashboard.home', compact('employeesAttendances', 'dates', 'data', 'profile', 'employees', 'employeeAttendance', 'officeTime', 'home_data', 'approval', 'data_absen', 'data_late', 'overtimePerDay', 'totalOvertimeHours', 'totalLeaveDays', 'totalRemainingLeaveDays', 'totalAllocatedLeaveDays','totalSickDays','totalReimbursement','totalReimbursementAmount','timesheetData','curMonth','projectStatusCounts','overdueTasksPerProject','tasksStatusPerProject','tasksPerProject','tasksPriorityPerProject','completedTasks','incompleteTasks'));

                    }
                    elseif($user->type == 'partners')
                    {

                        $absentData = [];
                        $branch = Employee::where('user_id', $user->id)->first();

                        $employees = Employee::select('id', 'name');

                        if(!empty($request->employee_id) && $request->employee_id[0]!=0){
                            $employees->whereIn('id', $request->employee_id);
                        }
                        $employees = $employees->where('branch_id', $branch->branch_id);

                        $employees = $employees->get()->pluck('name', 'id');

                        if(!empty($request->month))
                        {
                            $currentdate = strtotime($request->month);
                            $month       = date('m', $currentdate);
                            $year        = date('Y', $currentdate);
                            $curMonth    = date('M-Y', strtotime($request->month));

                        }
                        else
                        {
                            $month    = date('m');
                            $year     = date('Y');
                            $curMonth = date('M-Y', strtotime($year . '-' . $month));
                        }

                        if($branch->branch_id == 1)
                        {
                            $officeTime['startTime']    = Utility::getValByName('company_start_time');
                            $officeTime['endTime']      = Utility::getValByName('company_end_time');
                        }
                        elseif($branch->branch_id == 2)
                        {
                            $officeTime['startTime']    = "08:30";
                            $officeTime['endTime']      = "17:30";
                        }
                        elseif($branch->branch_id == 3)
                        {
                            $officeTime['startTime']    = "08:00";
                            $officeTime['endTime']      = "17:00";
                        }

                        for ($day = 1; $day <= 31; $day++) {
                            $months = $request->month ?? date('Y-m');
                            $date = sprintf('%s-%02d', $months, $day);
                            
                            $absentCount = AttendanceEmployee::where('date', '=', $date)
                                ->where('status', '=', 'Present')->whereHas('employee', function ($query) use ($branch) {
                                    $query->where('branch_id', '=', $branch->branch_id);
                                })
                                ->count();
                            
                            $lateCount = 0;
                            
                            $lateCount = AttendanceEmployee::where('date', '=', $date)
                                ->where('status', '=', 'Present')->whereHas('employee', function ($query) use ($branch, $officeTime) {
                                    $query->where('branch_id', '=', $branch->branch_id)
                                        ->whereTime('clock_in', '>', $officeTime['startTime']);
                                })
                                ->count();
            
                            $absentData[] = $absentCount;
                            $lateData[] = $lateCount;
                        }


                        $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
                        for($i = 1; $i <= $num_of_days; $i++)
                        {
                            $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
                        }

                        $employeesAttendance = [];
                        foreach($employees as $id => $employee)
                        {
                            $attendances['name'] = $employee;

                            foreach($dates as $date)
                            {
                                $dateFormat = $year . '-' . $month . '-' . $date;

                                if($dateFormat <= date('Y-m-d'))
                                {
                                    $employeeAttendance = AttendanceEmployee::where('employee_id', $id)->where('date', $dateFormat)->first();

                                    if(!empty($employeeAttendance) && $employeeAttendance->status == 'Present')
                                    {
                                        $attendanceStatus[$date] = 'P';
                                        $attendanceLong[$date] = $employeeAttendance->longitude;
                                        $attendanceLat[$date] = $employeeAttendance->latitude;

                                    }
                                    elseif(!empty($employeeAttendance) && $employeeAttendance->status == 'Leave')
                                    {
                                        $attendanceStatus[$date] = 'A';
                                    }
                                    else
                                    {
                                        $attendanceStatus[$date] = '';
                                        $attendanceLong[$date] = '';
                                        $attendanceLat[$date] = '';
                                    }
                                }
                                else
                                {
                                    $attendanceStatus[$date] = '';
                                    $attendanceLong[$date] = '';
                                    $attendanceLat[$date] = '';
                                }

                            }
                            $attendances['status'] = $attendanceStatus;
                            $attendances['longitude'] = $attendanceLong;
                            $attendances['latitude'] = $attendanceLat;
                            $employeesAttendances[] = $attendances;
                        }


                        $data['latestIncome']  = Revenue::orderBy('id', 'desc')->limit(5)->get();
                        $data['latestExpense'] = Payment::orderBy('id', 'desc')->limit(5)->get();


                        // $incomeCategory = ProductServiceCategory::where('type', '=', 1)->get();
                        // $inColor        = array();
                        // $inCategory     = array();
                        // $inAmount       = array();
                        // for($i = 0; $i < count($incomeCategory); $i++)
                        // {
                        //     $inColor[]    = '#' . $incomeCategory[$i]->color;
                        //     $inCategory[] = $incomeCategory[$i]->name;
                        //     $inAmount[]   = $incomeCategory[$i]->incomeCategoryRevenueAmount();
                        // }


                        // $data['incomeCategoryColor'] = $inColor;
                        // $data['incomeCategory']      = $inCategory;
                        // $data['incomeCatAmount']     = $inAmount;


                        // $expenseCategory = ProductServiceCategory::where('type', '=', 2)->get();
                        // $exColor         = array();
                        // $exCategory      = array();
                        // $exAmount        = array();
                        // for($i = 0; $i < count($expenseCategory); $i++)
                        // {
                        //     $exColor[]    = '#' . $expenseCategory[$i]->color;
                        //     $exCategory[] = $expenseCategory[$i]->name;
                        //     $exAmount[]   = $expenseCategory[$i]->expenseCategoryAmount();
                        // }

                        // $data['expenseCategoryColor'] = $exColor;
                        // $data['expenseCategory']      = $exCategory;
                        // $data['expenseCatAmount']     = $exAmount;

                        $data['list_revenue'] = Revenue::where('user_id', \Auth::user()->id)->get();
                        $data['list_expense'] = Payment::where('user_id', \Auth::user()->id)->where('status', 3)->get();

                        $data['incPartExpBarChartData']  = \Auth::user()->getincPartExpBarChartData();
                        $data['absentData'] = $absentData;
                        $data['lateData'] = $lateData;
                        $data['currentYear']  = date('Y');
                        $data['currentMonth'] = date('M');

                        return view('dashboard.home',$data, compact('employeesAttendances','dates'));
                    }
                    else
                    {
                        $events    = Event::where('created_by', '=', \Auth::user()->creatorId())->get();
                        $arrEvents = [];
    
                        foreach($events as $event)
                        {
                            $arr['id']    = $event['id'];
                            $arr['title'] = $event['title'];
                            $arr['start'] = $event['start_date'];
                            $arr['end']   = $event['end_date'];
    
                            $arr['backgroundColor'] = $event['color'];
                            $arr['borderColor']     = "#fff";
                            $arr['textColor']       = "white";
                            $arr['url']             = route('event.edit', $event['id']);
    
                            $arrEvents[] = $arr;
                        }
    
    
                        $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->where('created_by', '=', \Auth::user()->creatorId())->get();
    
    
                        $employees           = User::where('type', '!=', 'client')->where('type', '!=', 'company')->where('created_by', '=', \Auth::user()->creatorId())->get();
                        $countEmployee = count($employees);
    
                        $emp = Employee::where('user_id', '=', Auth::user()->id)->first();
    
    
                        $date               = date("Y-m-d");
                        $time               = date("H:i:s");
                        $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', '=', $date)->first();
    
                        if($emp->branch_id == 1)
                        {
                            $officeTime['startTime']    = Utility::getValByName('company_start_time');
                            $officeTime['endTime']      = Utility::getValByName('company_end_time');
                        }
                        elseif($emp->branch_id == 2)
                        {
                            $officeTime['startTime']    = "08:30";
                            $officeTime['endTime']      = "17:30";
                        }
                        elseif($emp->branch_id == 3)
                        {
                            $officeTime['startTime']    = "08:00";
                            $officeTime['endTime']      = "17:00";
                        }
    
                        $get_name                = $user->name;
                        $img                     = \DefaultProfileImage::create($get_name);
                        $profile                 = \Storage::put($get_name . '.jpg', $img->encode());
    
                        $user      = User::where('type', '!=', 'client')->where('type', '!=', 'company')->where('created_by', '=', \Auth::user()->creatorId())->get();
                        $countUser = count($user);
    
    
                        $countTrainer    = Trainer::where('created_by', '=', \Auth::user()->creatorId())->count();
                        $onGoingTraining = Training::where('status', '=', 1)->where('created_by', '=', \Auth::user()->creatorId())->count();
                        $doneTraining    = Training::where('status', '=', 2)->where('created_by', '=', \Auth::user()->creatorId())->count();
    
                        $currentDate = date('Y-m-d');
    
                        $employees   = User::where('type', '=', 'client')->where('created_by', '=', \Auth::user()->creatorId())->get();
                        $countClient = count($employees);
                        $notClockIn  = AttendanceEmployee::where('date', '=', $currentDate)->get()->pluck('employee_id');
    
                        $notClockIns = Employee::where('created_by', '=', \Auth::user()->creatorId())->whereNotIn('id', $notClockIn)->get();
                        $activeJob   = Job::where('status', 'active')->where('created_by', '=', \Auth::user()->creatorId())->count();
                        $inActiveJOb = Job::where('status', 'in_active')->where('created_by', '=', \Auth::user()->creatorId())->count();
    
    
                        $meetings = Meeting::where('created_by', '=', \Auth::user()->creatorId())->limit(5)->get();
    
                        return view('dashboard.dashboard', compact('profile','arrEvents', 'onGoingTraining', 'activeJob', 'inActiveJOb', 'doneTraining', 'announcements', 'employees', 'meetings', 'countTrainer', 'countClient', 'countUser', 'notClockIns', 'countEmployee', 'employeeAttendance'));
                    }
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            if(!file_exists(storage_path() . "/installed"))
            {
                header('location:install');
                die;
            }
            else
            {
                $settings = Utility::settings();
                if($settings['display_landing_page'] == 'on')
                {


                    return view('layouts.landing');
                }
                else
                {
                    return redirect('login');
                }

            }
        }
    }

    public function dashboard(Request $request)
    {
        if(Auth::check())
        {
            if(Auth::user()->type == 'admin' || Auth::user()->type == 'company')
            {

                //---------------------------HRM-----------------------------------------------

                $absentData = [];
                $absentDataPusat = [];
                $absentDataBekasi = [];
                $absentDataMalang = [];
                $lateData = [];

                if ($request->has('month')) {
                    $selectedMonth = $request->input('month');
                } else {
                    $selectedMonth = date('Y-m'); // Default ke bulan sekarang dengan format 'YYYY-MM'
                }

                $employees           = User::where('type', '!=', 'client')->get();
                $countEmployee = count($employees);

                $emp = Employee::where('user_id', '=', Auth::user()->id)->first();

                $date               = date("Y-m-d");
                $time               = date("H:i:s");
                $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', '=', $date)->first();

                if($emp->branch_id == 1)
                {
                    $officeTime['startTime']    = Utility::getValByName('company_start_time');
                    $officeTime['endTime']      = Utility::getValByName('company_end_time');
                }
                elseif($emp->branch_id == 2)
                {
                    $officeTime['startTime']    = "08:30";
                    $officeTime['endTime']      = "17:30";
                }
                elseif($emp->branch_id == 3)
                {
                    $officeTime['startTime']    = "08:00";
                    $officeTime['endTime']      = "17:00";
                }

                $branchTime = [
                    1 => ['startTime' => Utility::getValByName('company_start_time'), 'endTime' => Utility::getValByName('company_end_time')],
                    2 => ['startTime' => '08:30', 'endTime' => '17:30'],
                    3 => ['startTime' => '08:00', 'endTime' => '17:00']
                ];

                for ($day = 1; $day <= 31; $day++) {
                    $date = sprintf('%s-%02d', $selectedMonth, $day);
                    
                    // $absentCount = AttendanceEmployee::where('date', '=', $date)
                    //     ->where('status', '=', 'Present')
                    //     ->count();
                    

                    $absentCountPusat = AttendanceEmployee::where('date', '=', $date)
                        ->where('status', '=', 'Present')->whereHas('employee', function ($query) {
                            $query->where('branch_id', '=', 1);
                        })
                        ->count();
                    
                    $absentCountBekasi = AttendanceEmployee::where('date', '=', $date)
                        ->where('status', '=', 'Present')->whereHas('employee', function ($query) {
                            $query->where('branch_id', '=', 2);
                        })
                        ->count();

                    $absentCountMalang = AttendanceEmployee::where('date', '=', $date)
                        ->where('status', '=', 'Present')->whereHas('employee', function ($query) {
                            $query->where('branch_id', '=', 3);
                        })
                        ->count();
                    
                    $lateCount = 0;
                    $lateCountPusat = 0;
                    $lateCountBekasi = 0;
                    $lateCountMalang = 0;

                    $lateCountPusat = AttendanceEmployee::where('date', '=', $date)
                        ->where('status', '=', 'Present')->whereHas('employee', function ($query) {
                            $query->where('branch_id', '=', 1)
                                ->whereTime('clock_in', '>', '09:00:00');
                        })
                        ->count();
                    
                    $lateCountBekasi = AttendanceEmployee::where('date', '=', $date)
                        ->where('status', '=', 'Present')->whereHas('employee', function ($query) {
                            $query->where('branch_id', '=', 2)
                                ->whereTime('clock_in', '>', '08:30:00');
                        })
                        ->count();
                    
                    $lateCountMalang = AttendanceEmployee::where('date', '=', $date)
                        ->where('status', '=', 'Present')->whereHas('employee', function ($query) {
                            $query->where('branch_id', '=', 3)
                                ->whereTime('clock_in', '>', '08:00:00');
                        })
                        ->count();

                    // foreach ($branchTime as $branchId => $officeTime) {
                    //     $lateCount += AttendanceEmployee::where('date', '=', $date)
                    //         ->whereHas('employee', function ($query) use ($branchId, $officeTime) {
                    //             $query->where('branch_id', '=', $branchId)
                    //                 ->whereTime('clock_in', '>', $officeTime['startTime']);
                    //         })
                    //         ->count();
                    // }
    
                    // $absentData[] = $absentCount;
                    $absentDataPusat[] = $absentCountPusat;
                    $absentDataBekasi[] = $absentCountBekasi;
                    $absentDataMalang[] = $absentCountMalang;
                    // $lateData[] = $lateCount;
                    $lateDataPusat[] = $lateCountPusat;
                    $lateDataBekasi[] = $lateCountBekasi;
                    $lateDataMalang[] = $lateCountMalang;
                }
                


                $totalEmployees = [
                    'Jakarta' => User::join('employees', 'users.id', '=', 'employees.user_id')
                                    ->where('employees.branch_id', 1)
                                    ->where('users.is_active', 1)
                                    ->where('users.type','!=', 'admin')
                                    ->where('users.type','!=', 'company')
                                    ->count(),
                    'Bekasi' => User::join('employees', 'users.id', '=', 'employees.user_id')
                                    ->where('employees.branch_id', 2)
                                    ->where('users.is_active', 1)
                                    ->where('users.type','!=', 'admin')
                                    ->where('users.type','!=', 'company')
                                    ->count(),
                    'Malang' => User::join('employees', 'users.id', '=', 'employees.user_id')
                                    ->where('employees.branch_id', 3)
                                    ->where('users.is_active', 1)
                                    ->where('users.type','!=', 'admin')
                                    ->where('users.type','!=', 'company')
                                    ->count(),
                ];
            
                $branches = [1 => 'Jakarta', 2 => 'Bekasi', 3 => 'Malang'];
                $employeeTypes = ['Partners', 'Senior Audit', 'Junior Audit', 'Senior Accounting', 'Junior Accounting', 'Staff IT', 'Staff', 'Intern'];
                $employeesByBranch = [];

                foreach ($branches as $branchId => $branchName) {
                    $employeesByBranch[$branchName] = [];
                    foreach ($employeeTypes as $type) {
                        $employeesByBranch[$branchName][$type] = 0;
                    }
                }

                foreach ($employeeTypes as $type) {
                    foreach ($branches as $branchId => $branchName) {
                        $employeesByBranch[$branchName][$type] = User::join('employees', 'users.id', '=', 'employees.user_id')
                            ->where('employees.branch_id', $branchId)
                            ->where('users.is_active', 1)
                            ->where('users.type', $type)
                            ->count();
                    }
                }


                $currentDate = date('Y-m-d');

                $employees   = User::where('type', '=', 'client')->get();
                $countClient = count($employees);
                $notEnableDesktop  = LogDesktop::whereDate('last_active_at', '=', $currentDate)->get()->pluck('user_id');

                $notEnableDesktops = User::whereNotIn('id', $notEnableDesktop)
                ->where('type','!=','client')
                ->where('type', '!=', 'admin')
                ->where('type', '!=', 'company')
                ->where('type', '!=', 'partners')
                ->where('is_active', 1)->get();

                $activeJob   = Job::where('status', 'active')->count();
                $inActiveJOb = Job::where('status', 'in_active')->count();

                $officeTime['startTime'] = Utility::getValByName('company_start_time');
                $officeTime['endTime']   = Utility::getValByName('company_end_time');

                //--------------------------------------------------------------------------//


                $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
                $account->prepend('select Account', '');
                $client = User::where('type','=','client')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $client->prepend('Select Client', '');
                $partner = User::where('type','=','partners')->orWhere('type','=','senior accounting')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $partner->prepend('Select Partner', '');
                $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 1)->get()->pluck('name', 'id');
                $category->prepend('Select Category', '');
                $companies = Invoice::$company;
                $companies = ['' => 'Select Company'] + $companies;

                $currencyList = ['Rp' => 'Rp', '$' => '$'];

                $data['monthList']  = $month = $this->yearMonth();
                $data['yearList']   = $this->yearList();
                $filter['category'] = __('All');
                $filter['client'] = __('All');


                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;

                // ------------------------------REVENUE INCOME-----------------------------------
                $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $incomes->where('revenues.created_by', '=', \Auth::user()->creatorId());
                $incomes->whereRAW('YEAR(date) =?', [$year]);


                if(!empty($request->customer))
                {
                    $incomes->where('customer_id', '=', $request->customer);
                    $cust               = Customer::find($request->customer);
                    $filter['customer'] = !empty($cust) ? $cust->name : '';
                }
                $incomes->groupBy('month', 'year');
                $incomes = $incomes->get();

                $tmpArray = [];
                foreach($incomes as $income)
                {
                    $tmpArray[$income->category_id][$income->month] = $income->amount;
                }
                $array = [];
                foreach($tmpArray as $cat_id => $record)
                {
                    $tmp             = [];
                    $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $tmp['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $tmp['data'][$i] = array_key_exists($i, $record) ? $record[$i] : 0;
                    }
                    $array[] = $tmp;
                }


                $incomesData = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $incomesData->where('revenues.created_by', '=', \Auth::user()->creatorId());
                $incomesData->whereRAW('YEAR(date) =?', [$year]);

                if(!empty($request->category))
                {
                    $incomesData->where('category_id', '=', $request->category);
                }
                if(!empty($request->customer))
                {
                    $incomesData->where('customer_id', '=', $request->customer);
                }
                $incomesData->groupBy('month', 'year');
                $incomesData = $incomesData->get();
                $incomeArr   = [];
                foreach($incomesData as $k => $incomeData)
                {
                    $incomeArr[$incomeData->month] = $incomeData->amount;
                }
                for($i = 1; $i <= 12; $i++)
                {
                    $incomeTotal[] = array_key_exists($i, $incomeArr) ? $incomeArr[$i] : 0;
                }

                //---------------------------INVOICE INCOME-----------------------------------------------

                $invoices = Invoice::selectRaw('MONTH(send_date) as month, YEAR(send_date) as year, category_id, invoice_id, id, currency')
                ->where('status', '=', 2);

                $invoices->whereRAW('YEAR(send_date) =?', [$year]);

                if(!empty($request->client))
                {
                    $invoices->where('client_id', '=', $request->client);
                }

                if(!empty($request->user_id))
                {
                    $invoices->where('user_id', '=', $request->user_id);
                }

                if(!empty($request->company))
                {
                    $invoices->where('company', '=', $request->company);
                }

                if(!empty($request->category))
                {
                    $invoices->where('category_id', '=', $request->category);
                }

                $invoices        = $invoices->get();
                $invoiceTmpArray = [];
                foreach($invoices as $invoice)
                {
                    $categoryIds = explode(',', $invoice->category_id);
                    foreach ($categoryIds as $categoryId) {
                        $invoiceTmpArray[$categoryId][$invoice->month][] = $invoice->getTotal();
                    }
                }


                $invoiceArray = [];
                foreach ($invoiceTmpArray as $cat_id => $record) {
                    $invoice             = [];
                    $invoice['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $invoice['data']     = [];
                
                    for ($i = 1; $i <= 12; $i++) {
                        $invoice['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
                
                    $invoiceArray[] = $invoice;
                }

                $invoiceTotalArrayRp = [];
                $invoiceTotalArrayUsd = [];
                $totalInvoiceRp = 0;
                $totalInvoiceDollar = 0;
                

                foreach($invoices as $invoice)
                {
                    if($invoice->currency == 'Rp') {
                        $invoiceTotalArrayRp[$invoice->month][] = $invoice->getTotal();
                        $totalInvoiceRp += $invoice->getTotal();
                    } elseif($invoice->currency == '$') {
                        $invoiceTotalArrayUsd[$invoice->month][] = $invoice->getTotal();
                        $totalInvoiceDollar += $invoice->getTotal();
                    }
                }


                $invoiceTotalRp = [];
                $invoiceTotalUsd = [];

                for($i = 1; $i <= 12; $i++)
                {
                    $invoiceTotalRp[] = array_key_exists($i, $invoiceTotalArrayRp) ? array_sum($invoiceTotalArrayRp[$i]) : 0;
                    $invoiceTotalUsd[] = array_key_exists($i, $invoiceTotalArrayUsd) ? array_sum($invoiceTotalArrayUsd[$i]) : 0;
                }


                $chartIncomeArrRp = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $incomeTotal, $invoiceTotalRp
                );

                $chartIncomeArrUsd = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $incomeTotal, $invoiceTotalUsd
                );

                $invoicePartnersRp = [];
                $invoicePartnersUsd = [];

                $partners = User::whereIn('type', ['partners', 'senior accounting'])->get();

                foreach ($partners as $partner) {

                    $invoices = Invoice::selectRaw('MONTH(send_date) as month, YEAR(send_date) as year, category_id, invoice_id, id, currency')
                    ->where('user_id', $partner->id)
                    ->where('status', '=', 2)
                    ->whereRaw('YEAR(send_date) = ?', [$year]);

                    if (!empty($request->company)) {
                        $invoices->where('company', '=', $request->company);
                    }

                    $invoices = $invoices->get();

                    $invoicePartners[$partner->name] = $invoices;

                    foreach($invoices as $invoice)
                    {
                        if($invoice->currency == 'Rp') {
                            $invoicePartnersRp[$invoice->month][$partner->name] = $invoice->getTotal();
                        } elseif($invoice->currency == '$') {
                            $invoicePartnersUsd[$invoice->month][$partner->name] = $invoice->getTotal();
                        }
                    }
                }

                $lineChartDataRp = [];
                $lineChartDataUsd = [];

                foreach ($invoicePartnersRp as $month => $partnerData) {
                    foreach ($partnerData as $partnerName => $total) {
                        $lineChartDataRp[$partnerName][$month] = $total;
                    }
                }

                foreach ($invoicePartnersUsd as $month => $partnerData) {
                    foreach ($partnerData as $partnerName => $total) {
                        $lineChartDataUsd[$partnerName][$month] = $total;
                    }
                }


                $invoicePaidCount = Invoice::where('status', '=', 3)->count();
                $invoiceDraftCount = Invoice::where('status', '=', 1)->count();
                $invoiceUnpaidCount = Invoice::where('status', '=', 2)->count();
                $totalInvoiceCount = Invoice::count();

                $data['chartIncomeArrRp'] = $chartIncomeArrRp;
                $data['chartIncomeArrUsd'] = $chartIncomeArrUsd;
                $data['lineChartDataRp'] = $lineChartDataRp;
                $data['lineChartDataUsd'] = $lineChartDataUsd;
                $data['totalInvoiceRp'] = $totalInvoiceRp;
                $data['totalInvoiceDollar'] = $totalInvoiceDollar;
                $data['incomeArr']      = $array;
                $data['invoiceArray']   = $invoiceArray;
                $data['account']        = $account;
                $data['client']         = $client;
                $data['partner']        = $partner;
                $data['companies']        = $companies;
                $data['officeTime']       = $officeTime;
                $data['employees']       = $employees;
                $data['countClient']       = $countClient;
                $data['notEnableDesktops']       = $notEnableDesktops;
                $data['employeeAttendance']       = $employeeAttendance;
                $data['category']       = $category;
                // $data['absentData'] = $absentData;
                // $data['lateData'] = $lateData;
                $data['absentDataPusat'] = $absentDataPusat;
                $data['absentDataBekasi'] = $absentDataBekasi;
                $data['absentDataMalang'] = $absentDataMalang;
                $data['lateDataPusat'] = $absentDataMalang;
                $data['lateDataBekasi'] = $lateDataBekasi;
                $data['lateDataMalang'] = $lateDataMalang;
                $data['selectedMonth'] = $selectedMonth;
                $data['invoiceSummary'] = [
                    'paid' => [
                        'count' => $invoicePaidCount,
                    ],
                    'draft' => [
                        'count' => $invoiceDraftCount,
                    ],
                    'unpaid' => [
                        'count' => $invoiceUnpaidCount,
                    ],
                    'total' => $totalInvoiceCount,
                ];
                $data['totalEmployees'] = $totalEmployees;
                $data['employeesByBranch'] = $employeesByBranch;
                $data['employeeTypes'] = $employeeTypes;
                

                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;


                return view('dashboard.admin-dashboard',  compact('filter','currencyList'), $data);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            if(!file_exists(storage_path() . "/installed"))
            {
                header('location:install');
                die;
            }
            else
            {
                $settings = Utility::settings();
                if($settings['display_landing_page'] == 'on')
                {
                    return view('layouts.landing', compact('settings'));
                }
                else
                {
                    return redirect('login');
                }

            }
        }
    }

    public function yearMonth()
    {

        $month[] = __('January');
        $month[] = __('February');
        $month[] = __('March');
        $month[] = __('April');
        $month[] = __('May');
        $month[] = __('June');
        $month[] = __('July');
        $month[] = __('August');
        $month[] = __('September');
        $month[] = __('October');
        $month[] = __('November');
        $month[] = __('December');

        return $month;
    }

    public function yearList()
    {
        $starting_year = date('Y', strtotime('-5 year'));
        $ending_year   = date('Y');

        foreach(range($ending_year, $starting_year) as $year)
        {
            $years[$year] = $year;
        }

        return $years;
    }

    function isWeekend($date)
    {
        $dayOfWeek = date('N', strtotime($date)); // Mengambil hari dalam format angka (1-7, dimulai dari Senin)
        return ($dayOfWeek == 6 || $dayOfWeek == 7); // Jika hari adalah Sabtu (6) atau Minggu (7), kembalikan true
    }

}
