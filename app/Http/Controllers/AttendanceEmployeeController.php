<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\IpRestrict;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonPeriod;


class AttendanceEmployeeController extends Controller
{
    public function index(Request $request)
    {

        if(\Auth::user()->can('manage attendance'))
        {
            if(\Auth::user()->type = 'admin')
            {
                $branch = Branch::all()->pluck('name', 'id');
                $branch->prepend('Select Branch', '');
    
                $department = Department::all()->pluck('name', 'id');
                $department->prepend('Select Department', '');

                $employees = Employee::get()
                 ->pluck('name', 'id');
                $employees->prepend('Select Employee', '');
    
                if(\Auth::user()->type != 'client' && \Auth::user()->type != 'admin' && \Auth::user()->type != 'company')
                {
                    $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
    
                    $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);
                    if($request->type == 'monthly' && !empty($request->month))
                    {
                        $month = date('m', strtotime($request->month));
                        $year  = date('Y', strtotime($request->month));
    
                        $start_date = date($year . '-' . $month . '-01');
                        $end_date   = date($year . '-' . $month . '-t');
    
                        $attendanceEmployee->whereBetween(
                            'date', [
                                      $start_date,
                                      $end_date,
                                  ]
                        );
                    }
                    elseif($request->type == 'daily' && !empty($request->date))
                    {
                        $attendanceEmployee->where('date', $request->date);
                    }
                    else
                    {
                        $month      = date('m');
                        $year       = date('Y');
                        $start_date = date($year . '-' . $month . '-01');
                        $end_date   = date($year . '-' . $month . '-t');
    
                        $attendanceEmployee->whereBetween(
                            'date', [
                                      $start_date,
                                      $end_date,
                                  ]
                        );
                    }
                    $attendanceEmployee = $attendanceEmployee->orderByDesc('id')->paginate(10)->appends([
                        'type' => $request->type,
                        'month' => $request->month,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'employee_id' => $request->employee_id,
                    ]);  
    
                }
                else
                {
                    $employee = Employee::all();

                    if(!empty($request->branch_id))
                    {
                        $employee = $employee->where('branch_id', '=', $request->branch_id);
                    }

                    if(!empty($request->department))
                    {
                        $employee = $employee->where('department_id', $request->department);
                    }
    
                    $employee = $employee->pluck('id');
    
                    $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee);
    
                    if($request->type == 'monthly' && !empty($request->month))
                    {
                        $month = date('m', strtotime($request->month));
                        $year  = date('Y', strtotime($request->month));
    
                        $start_date = date($year . '-' . $month . '-01');
                        $end_date   = date($year . '-' . $month . '-t');
    
                        $attendanceEmployee->whereBetween(
                            'date', [
                                      $start_date,
                                      $end_date,
                                  ]
                        );
                    }
                    elseif($request->type == 'daily' && !empty($request->start_date) && !empty($request->end_date))
                    {
                        $startDate = $request->start_date;
                        $endDate = $request->end_date;
                        $attendanceEmployee->whereBetween(
                            'date', [
                                      $startDate,
                                      $endDate,
                                  ]
                        );
                    }
                    else {
                        // Default to current month's dates
                        $startDate = date('Y-m-01');
                        $endDate = date('Y-m-t');

                        $attendanceEmployee->whereBetween(
                            'date', [
                                      $startDate,
                                      $endDate,
                                  ]
                        );
                    }

                    if(!empty($request->employee_id))
                    {
                        $attendanceEmployee->where('employee_id', '=', $request->employee_id);
                    }
    
    
                    $attendanceEmployee = $attendanceEmployee->orderByDesc('id')->paginate(10)->appends([
                        'type' => $request->type,
                        'month' => $request->month,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'employee_id' => $request->employee_id,
                    ]);  
                    

                    if (!empty($request->export_excel)) {
                        $exportData = $this->prepareExportData($attendanceEmployee, $startDate, $endDate);

                        // Passing the $dates array to the AttendanceExport
                        $dates = CarbonPeriod::create($startDate, $endDate)->toArray();
                        $dateHeadings = array_map(function($date) {
                            return $date->format('Y-m-d');
                        }, $dates);

                        return Excel::download(new AttendanceExport($exportData->toArray(), $dateHeadings), 'attendance_report.xlsx');


                    }
    
                }
    
                return view('attendance.index', compact('attendanceEmployee', 'branch', 'department','employees'));
            }
            elseif(\Auth::user()->type = 'company')
            {
                $branch = Branch::all()->pluck('name', 'id');
                $branch->prepend('Select Branch', '');
    
                $department = Department::all()->pluck('name', 'id');
                $department->prepend('Select Department', '');
    
                if(\Auth::user()->type != 'client' && \Auth::user()->type != 'admin' && \Auth::user()->type != 'company')
                {
                    $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
    
                    $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);
                    if($request->type == 'monthly' && !empty($request->month))
                    {
                        $month = date('m', strtotime($request->month));
                        $year  = date('Y', strtotime($request->month));
    
                        $start_date = date($year . '-' . $month . '-01');
                        $end_date   = date($year . '-' . $month . '-t');
    
                        $attendanceEmployee->whereBetween(
                            'date', [
                                      $start_date,
                                      $end_date,
                                  ]
                        );
                    }
                    elseif($request->type == 'daily' && !empty($request->date))
                    {
                        $attendanceEmployee->where('date', $request->date);
                    }
                    else
                    {
                        $month      = date('m');
                        $year       = date('Y');
                        $start_date = date($year . '-' . $month . '-01');
                        $end_date   = date($year . '-' . $month . '-t');
    
                        $attendanceEmployee->whereBetween(
                            'date', [
                                      $start_date,
                                      $end_date,
                                  ]
                        );
                    }
                    $attendanceEmployee = $attendanceEmployee->orderByDesc('id')->paginate(10)->appends([
                        'type' => $request->type,
                        'month' => $request->month,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'employee_id' => $request->employee_id,
                    ]);  
    
                }
                else
                {
                    $employee = Employee::all();
    
                    if(!empty($request->branch))
                    {
                        $employee->where('branch_id', $request->branch);
                    }
    
                    if(!empty($request->department))
                    {
                        $employee->where('department_id', $request->department);
                    }
    
                    $employee = $employee->pluck('id');
    
                    $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee);
    
                    if($request->type == 'monthly' && !empty($request->month))
                    {
                        $month = date('m', strtotime($request->month));
                        $year  = date('Y', strtotime($request->month));
    
                        $start_date = date($year . '-' . $month . '-01');
                        $end_date   = date($year . '-' . $month . '-t');
    
                        $attendanceEmployee->whereBetween(
                            'date', [
                                      $start_date,
                                      $end_date,
                                  ]
                        );
                    }
                    elseif($request->type == 'daily' && !empty($request->date))
                    {
                        $attendanceEmployee->where('date', $request->date);
                    }
                    else
                    {
                        $month      = date('m');
                        $year       = date('Y');
                        $start_date = date($year . '-' . $month . '-01');
                        $end_date   = date($year . '-' . $month . '-t');
    
                        $attendanceEmployee->whereBetween(
                            'date', [
                                      $start_date,
                                      $end_date,
                                  ]
                        );
                    }
    
    
                    $attendanceEmployee = $attendanceEmployee->orderByDesc('id')->paginate(10)->appends([
                        'type' => $request->type,
                        'month' => $request->month,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'employee_id' => $request->employee_id,
                    ]);  
    
                }
    
                return view('attendance.index', compact('attendanceEmployee', 'branch', 'department'));
            }
            else{
                $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branch->prepend('Select Branch', '');
    
                $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $department->prepend('Select Department', '');
    
                if(\Auth::user()->type != 'client' && \Auth::user()->type != 'company')
                {
                    $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
    
                    $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);
                    if($request->type == 'monthly' && !empty($request->month))
                    {
                        $month = date('m', strtotime($request->month));
                        $year  = date('Y', strtotime($request->month));
    
                        $start_date = date($year . '-' . $month . '-01');
                        $end_date   = date($year . '-' . $month . '-t');
    
                        $attendanceEmployee->whereBetween(
                            'date', [
                                      $start_date,
                                      $end_date,
                                  ]
                        );
                    }
                    elseif($request->type == 'daily' && !empty($request->date))
                    {
                        $attendanceEmployee->where('date', $request->date);
                    }
                    else
                    {
                        $month      = date('m');
                        $year       = date('Y');
                        $start_date = date($year . '-' . $month . '-01');
                        $end_date   = date($year . '-' . $month . '-t');
    
                        $attendanceEmployee->whereBetween(
                            'date', [
                                      $start_date,
                                      $end_date,
                                  ]
                        );
                    }
                    $attendanceEmployee = $attendanceEmployee->orderByDesc('id')->paginate(10)->appends([
                        'type' => $request->type,
                        'month' => $request->month,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'employee_id' => $request->employee_id,
                    ]);  
    
                }
                else
                {
                    $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId());
    
                    if(!empty($request->branch))
                    {
                        $employee->where('branch_id', $request->branch);
                    }
    
                    if(!empty($request->department))
                    {
                        $employee->where('department_id', $request->department);
                    }
    
                    $employee = $employee->get()->pluck('id');
    
                    $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee);
    
                    if($request->type == 'monthly' && !empty($request->month))
                    {
                        $month = date('m', strtotime($request->month));
                        $year  = date('Y', strtotime($request->month));
    
                        $start_date = date($year . '-' . $month . '-01');
                        $end_date   = date($year . '-' . $month . '-t');
    
                        $attendanceEmployee->whereBetween(
                            'date', [
                                      $start_date,
                                      $end_date,
                                  ]
                        );
                    }
                    elseif($request->type == 'daily' && !empty($request->date))
                    {
                        $attendanceEmployee->where('date', $request->date);
                    }
                    else
                    {
                        $month      = date('m');
                        $year       = date('Y');
                        $start_date = date($year . '-' . $month . '-01');
                        $end_date   = date($year . '-' . $month . '-t');
    
                        $attendanceEmployee->whereBetween(
                            'date', [
                                      $start_date,
                                      $end_date,
                                  ]
                        );
                    }
    
    
                    $attendanceEmployee = $attendanceEmployee->orderByDesc('id')->paginate(10)->appends([
                        'type' => $request->type,
                        'month' => $request->month,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'employee_id' => $request->employee_id,
                    ]);  
    
                }
    
                return view('attendance.index', compact('attendanceEmployee', 'branch', 'department'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create attendance'))
        {
            $employees = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '=', "employee")->get()->pluck('name', 'id');

            return view('attendance.create', compact('employees'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create attendance'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'date' => 'required',
                                   'clock_in' => 'required',
                                   'clock_out' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $startTime  = Utility::getValByName('company_start_time');
            $endTime    = Utility::getValByName('company_end_time');
            $attendance = AttendanceEmployee::where('employee_id', '=', $request->employee_id)->where('date', '=', $request->date)->where('clock_out', '=', '00:00:00')->get()->toArray();
            if($attendance)
            {
                return redirect()->route('attendanceemployee.index')->with('error', __('Employee Attendance Already Created.'));
            }
            else
            {
                $date = date("Y-m-d");

                $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime);

                $hours = floor($totalLateSeconds / 3600);
                $mins  = floor($totalLateSeconds / 60 % 60);
                $secs  = floor($totalLateSeconds % 60);
                $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                //early Leaving
                $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
                $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs                     = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


                if(strtotime($request->clock_out) > strtotime($date . $endTime))
                {
                    //Overtime
                    $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
                    $hours                = floor($totalOvertimeSeconds / 3600);
                    $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                    $secs                 = floor($totalOvertimeSeconds % 60);
                    $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                }
                else
                {
                    $overtime = '00:00:00';
                }

                $employeeAttendance                = new AttendanceEmployee();
                $employeeAttendance->employee_id   = $request->employee_id;
                $employeeAttendance->date          = $request->date;
                $employeeAttendance->status        = 'Present';
                $employeeAttendance->clock_in      = $request->clock_in . ':00';
                $employeeAttendance->clock_out     = $request->clock_out . ':00';
                $employeeAttendance->late          = $late;
                $employeeAttendance->early_leaving = $earlyLeaving;
                $employeeAttendance->overtime      = $overtime;
                $employeeAttendance->total_rest    = '00:00:00';
                $employeeAttendance->created_by    = \Auth::user()->creatorId();
                $employeeAttendance->save();

                return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully created.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if(\Auth::user()->can('edit attendance'))
        {
            if(\Auth::user()->type = 'admin')
            {
                $attendanceEmployee = AttendanceEmployee::where('id', $id)->first();
                $employees          = Employee::get()->pluck('name', 'id');
            }
            elseif(\Auth::user()->type = 'company')
            {
                $attendanceEmployee = AttendanceEmployee::where('id', $id)->first();
                $employees          = Employee::get()->pluck('name', 'id');
            }
            else
            {
                $attendanceEmployee = AttendanceEmployee::where('id', $id)->first();
                $employees          = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            }
            return view('attendance.edit', compact('attendanceEmployee', 'employees'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        //        dd($request->all());
        // if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        //     $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        //     $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        // }
        // $client  = @$_SERVER['HTTP_CLIENT_IP'];
        // $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        // $remote  = $_SERVER['REMOTE_ADDR'];
    
        // if(filter_var($client, FILTER_VALIDATE_IP))
        // {
        //     $ip = $client;
        // }
        // elseif(filter_var($forward, FILTER_VALIDATE_IP))
        // {
        //     $ip = $forward;
        // }
        // else
        // {
        //     $ip = $remote;
        // }

        // $clientIP = geoip()->getLocation($ip);
        // if($clientIP->city != "Matraman Dalam")
        //     {
        //         $city = "Anda berada diluar kantor";
        //     }
        //     else
        //     {
        //         $city = "Matraman Dalam";
        //     }
        $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();
        //        dd($todayAttendance);
        if(!empty($todayAttendance) && $todayAttendance->clock_out == '00:00:00')
        {

            $employee = Employee::where('id', $employeeId)->first();
            
            if($employee->branch_id == 1)
            {
                $startTime = Utility::getValByName('company_start_time');
                $endTime   = Utility::getValByName('company_end_time');
            }
            elseif($employee->branch_id == 2)
            {
                $startTime = "08:30";
                $endTime   = "17:30";
            }
            elseif($employee->branch_id == 3)
            {
                $startTime = "08:00";
                $endTime   = "17:00";
            }

            if(Auth::user()->type == 'Employee' || Auth::user()->type == 'intern' || Auth::user()->type == 'company' || Auth::user()->type == 'admin' || Auth::user()->type =='junior audit' || Auth::user()->type =='senior audit' || Auth::user()->type =='junior accounting' || Auth::user()->type =='senior accounting' || Auth::user()->type =='staff IT' || Auth::user()->type =='partners' || Auth::user()->type =='manager audit' || Auth::user()->type =='manager accounting')
            {

                $date = date("Y-m-d");
                $time = date("H:i:s");
                //                dd($time);
                //early Leaving
                $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
                $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs                     = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                if($employee->branch_id == 1)
                {
                    $endTime   = "19:00";
                    if(time() > strtotime($date . $endTime))
                    {
                        //Overtime
                        $totalOvertimeSeconds = time() - strtotime($date . $endTime);
                        $hours                = floor($totalOvertimeSeconds / 3600);
                        $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                        $secs                 = floor($totalOvertimeSeconds % 60);
                        $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    }
                    else
                    {
                        $overtime = '00:00:00';
                    }
                }
                else
                {
                        if(time() > strtotime($date . $endTime))
                    {
                        //Overtime
                        $totalOvertimeSeconds = time() - strtotime($date . $endTime);
                        $hours                = floor($totalOvertimeSeconds / 3600);
                        $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                        $secs                 = floor($totalOvertimeSeconds % 60);
                        $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    }
                    else
                    {
                        $overtime = '00:00:00';
                    }
                }

                

                //                $attendanceEmployee                = AttendanceEmployee::find($id);
                $attendanceEmployee['clock_out']     = $time;
                $attendanceEmployee['early_leaving'] = $earlyLeaving;
                $attendanceEmployee['overtime']      = $overtime;
                // $attendanceEmployee['ip']            = $clientIP->ip;
                // $attendanceEmployee['location']      = $city;

                if(!empty($request->date)) {
                    $attendanceEmployee['date']       =  $request->date;
                }
                //                dd($attendanceEmployee);
                AttendanceEmployee::where('id',$id)->update($attendanceEmployee);
                //                $attendanceEmployee->save();

                return redirect()->back()->with('success', __('Employee Successfully Clock Out.'));

                // return redirect()->route('hrm.dashboard')->with('success', __('Employee successfully clock Out.'));
            }
            else
            {
                $date = date("Y-m-d");
                //late
                $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime);

                $hours = floor($totalLateSeconds / 3600);
                $mins  = floor($totalLateSeconds / 60 % 60);
                $secs  = floor($totalLateSeconds % 60);
                $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                //early Leaving
                $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
                $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs                     = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


                if(strtotime($request->clock_out) > strtotime($date . $endTime))
                {
                    //Overtime
                    $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
                    $hours                = floor($totalOvertimeSeconds / 3600);
                    $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                    $secs                 = floor($totalOvertimeSeconds % 60);
                    $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                }
                else
                {
                    $overtime = '00:00:00';
                }

                $attendanceEmployee                = AttendanceEmployee::find($id);
                $attendanceEmployee->employee_id   = $request->employee_id;
                $attendanceEmployee->date          = $request->date;
                $attendanceEmployee->clock_in      = $request->clock_in;
                $attendanceEmployee->clock_out     = $request->clock_out;
                // $attendanceEmployee->ip            = $clientIP->ip;
                // $attendanceEmployee->location      = $city;
                $attendanceEmployee->late          = $late;
                $attendanceEmployee->early_leaving = $earlyLeaving;
                $attendanceEmployee->overtime      = $overtime;
                $attendanceEmployee->total_rest    = '00:00:00';

                $attendanceEmployee->save();

                return redirect()->back()->with('success', __('Employee attendance successfully updated.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
        }
    }

    public function destroy($id)
    {
        if(\Auth::user()->can('delete attendance'))
        {
            $attendance = AttendanceEmployee::where('id', $id)->first();

            $attendance->delete();

            return redirect()->route('attendanceemployee.index')->with('success', __('Attendance successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function attendance(Request $request)
    {
        $settings = Utility::settings();
        $employeeId = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();
        
        if(empty($todayAttendance))
        {
            $employee = Employee::where('id', $employeeId)->first();
            
            $officeLatitudePusat = -6.2245196;
            $officeLongitudePusat = 106.8402893;

            $officeLatitudeBekasi = -6.2778273;
            $officeLongitudeBekasi = 106.9745264;

            $officeLatitudeMalang = -7.95058;
            $officeLongitudeMalang = 112.63041;
            $allowedRadius = 1.0;

            if ($employee->branch_id == 1) {
                $startTime = Utility::getValByName('company_start_time');
                $endTime   = Utility::getValByName('company_end_time');
            } 
            elseif($employee->branch_id == 2) {
                $startTime = "08:30";
                $endTime   = "17:30";
            }
            elseif($employee->branch_id == 3) {
                $startTime = "08:00";
                $endTime   = "17:00";
            }

            $attendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', $employeeId)->where('clock_out', '=', '00:00:00')->first();
            if ($attendance != null) {
                $attendance->clock_out = $endTime;
                $attendance->save();
            }

            $date = date("Y-m-d");
            $time = date("H:i:s");

            $totalLateSeconds = time() - strtotime($date . $startTime);
            $hours = floor($totalLateSeconds / 3600);
            $mins = floor($totalLateSeconds / 60 % 60);
            $secs = floor($totalLateSeconds % 60);
            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            $checkDb = AttendanceEmployee::where('employee_id', '=', \Auth::user()->id)->get()->toArray();

            // Fetch employee's latitude and longitude from the request
            $employeeLatitude = $request->latitude;
            $employeeLongitude = $request->longitude;

            if ($employee->branch_id == 1) {
                $distance = $this->calculateDistance($officeLatitudePusat, $officeLongitudePusat, $employeeLatitude, $employeeLongitude);
            } 
            elseif($employee->branch_id == 2) {
                $distance = $this->calculateDistance($officeLatitudeBekasi, $officeLongitudeBekasi, $employeeLatitude, $employeeLongitude);
            }
            elseif($employee->branch_id == 3) {
                $distance = $this->calculateDistance($officeLatitudeMalang, $officeLongitudeMalang, $employeeLatitude, $employeeLongitude);
            }

            $employeeAttendance = new AttendanceEmployee();
            $employeeAttendance->employee_id = $employeeId;
            $employeeAttendance->date = $date;
            $employeeAttendance->status = 'Present';
            $employeeAttendance->clock_in = $time;
            $employeeAttendance->clock_out = '00:00:00';
            $employeeAttendance->latitude = $employeeLatitude;
            $employeeAttendance->longitude = $employeeLongitude;
            $employeeAttendance->work_location = $request->work_location;
            $employeeAttendance->late = $late;
            $employeeAttendance->early_leaving = '00:00:00';
            $employeeAttendance->overtime = '00:00:00';
            $employeeAttendance->total_rest = '01:00:00';
            $employeeAttendance->distance_from_office = $distance;
            $employeeAttendance->created_by = \Auth::user()->id;
            $employeeAttendance->save();

            return redirect()->back()->with('success', __('Employee Successfully Clocked In.'));

            // if ($distance <= $allowedRadius) 
            // {

            //     $employeeAttendance = new AttendanceEmployee();
            //     $employeeAttendance->employee_id = $employeeId;
            //     $employeeAttendance->date = $date;
            //     $employeeAttendance->status = 'Present';
            //     $employeeAttendance->clock_in = $time;
            //     $employeeAttendance->clock_out = '00:00:00';
            //     $employeeAttendance->latitude = $employeeLatitude;
            //     $employeeAttendance->longitude = $employeeLongitude;
            //     $employeeAttendance->late = $late;
            //     $employeeAttendance->early_leaving = '00:00:00';
            //     $employeeAttendance->overtime = '00:00:00';
            //     $employeeAttendance->total_rest = '01:00:00';
            //     $employeeAttendance->distance_from_office = $distance;
            //     $employeeAttendance->created_by = \Auth::user()->id;
            //     $employeeAttendance->save();
    
            //     return redirect()->back()->with('success', __('Employee Successfully Clocked In.'));
            // } else {
            //     return redirect()->back()->with('error', __('You are too far from the office to clock in.'));
            // }
        }
        else {
            return redirect()->back()->with('error', __('Employee are not allowed multiple time clock in & clock for every day.'));
        }
    }

    public function bulkAttendance(Request $request)
    {
        if(\Auth::user()->can('create attendance'))
        {

            if(\Auth::user()->type = 'admin'){
                $branch = Branch::all()->pluck('name', 'id');
                $branch->prepend('Select Branch', '');
    
                $department = Department::all()->pluck('name', 'id');
                $department->prepend('Select Department', '');
    
                $employees = [];
                if(!empty($request->branch) && !empty($request->department))
                {
                    $employees = Employee::where('branch_id', $request->branch)->where('department_id', $request->department)->get();
    
    
                }else{
                    $employees = Employee::all();
                }
    
    
                return view('attendance.bulk', compact('employees', 'branch', 'department'));
            }
            elseif(\Auth::user()->type = 'company')
            {
                $branch = Branch::all()->pluck('name', 'id');
                $branch->prepend('Select Branch', '');
    
                $department = Department::all()->pluck('name', 'id');
                $department->prepend('Select Department', '');
    
                $employees = [];
                if(!empty($request->branch) && !empty($request->department))
                {
                    $employees = Employee::where('branch_id', $request->branch)->where('department_id', $request->department)->get();
    
    
                }else{
                    $employees = Employee::all();
                }
    
    
                return view('attendance.bulk', compact('employees', 'branch', 'department'));
            }
            else{
                $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branch->prepend('Select Branch', '');
    
                $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $department->prepend('Select Department', '');
    
                $employees = [];
                if(!empty($request->branch) && !empty($request->department))
                {
                    $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('branch_id', $request->branch)->where('department_id', $request->department)->get();
    
    
                }else{
                    $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('branch_id', 1)->where('department_id',1)->get();
                }
    
    
                return view('attendance.bulk', compact('employees', 'branch', 'department'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bulkAttendanceData(Request $request)
    {

        if(\Auth::user()->can('create attendance'))
        {
            if(!empty($request->branch) && !empty($request->department))
            {
                $date      = $request->date;

                $employees = $request->employee_id;
                $employee = Employee::where('id', $employees)->first();
            
                if($employee->branch_id == 1)
                {
                    $startTime = Utility::getValByName('company_start_time');
                    $endTime   = Utility::getValByName('company_end_time');
                }
                elseif($employee->branch_id == 2)
                {
                    $startTime = "08:30";
                    $endTime   = "17:30";
                }
                elseif($employee->branch_id == 3)
                {
                    $startTime = "08:00";
                    $endTime   = "17:00";
                }

                $atte      = [];
                foreach($employees as $employee)
                {
                    $present = 'present-' . $employee;
                    $in      = 'in-' . $employee;
                    $out     = 'out-' . $employee;
                    $atte[]  = $present;
                    if($request->$present == 'on')
                    {

                        $in  = date("H:i:s", strtotime($request->$in));
                        $out = date("H:i:s", strtotime($request->$out));

                        $totalLateSeconds = strtotime($in) - strtotime($startTime);

                        $hours = floor($totalLateSeconds / 3600);
                        $mins  = floor($totalLateSeconds / 60 % 60);
                        $secs  = floor($totalLateSeconds % 60);
                        $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                        //early Leaving
                        $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($out);
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


                        if(strtotime($out) > strtotime($endTime))
                        {
                            //Overtime
                            $totalOvertimeSeconds = strtotime($out) - strtotime($endTime);
                            $hours                = floor($totalOvertimeSeconds / 3600);
                            $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                            $secs                 = floor($totalOvertimeSeconds % 60);
                            $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        }
                        else
                        {
                            $overtime = '00:00:00';
                        }


                        $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                        if(!empty($attendance))
                        {
                            $employeeAttendance = $attendance;
                        }
                        else
                        {
                            $employeeAttendance              = new AttendanceEmployee();
                            $employeeAttendance->employee_id = $employee;
                            $employeeAttendance->created_by  = \Auth::user()->creatorId();
                        }


                        $employeeAttendance->date          = $request->date;
                        $employeeAttendance->status        = 'Present';
                        $employeeAttendance->clock_in      = $in;
                        $employeeAttendance->clock_out     = $out;
                        $employeeAttendance->late          = $late;
                        $employeeAttendance->early_leaving = ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00';
                        $employeeAttendance->overtime      = $overtime;
                        $employeeAttendance->total_rest    = '00:00:00';
                        $employeeAttendance->save();

                    }
                    else
                    {
                        $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                        if(!empty($attendance))
                        {
                            $employeeAttendance = $attendance;
                        }
                        else
                        {
                            $employeeAttendance              = new AttendanceEmployee();
                            $employeeAttendance->employee_id = $employee;
                            $employeeAttendance->created_by  = \Auth::user()->creatorId();
                        }

                        $employeeAttendance->status        = 'Leave';
                        $employeeAttendance->date          = $request->date;
                        $employeeAttendance->clock_in      = '00:00:00';
                        $employeeAttendance->clock_out     = '00:00:00';
                        $employeeAttendance->late          = '00:00:00';
                        $employeeAttendance->early_leaving = '00:00:00';
                        $employeeAttendance->overtime      = '00:00:00';
                        $employeeAttendance->total_rest    = '00:00:00';
                        $employeeAttendance->save();
                    }
                }

                return redirect()->back()->with('success', __('Employee attendance successfully created.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Branch & department field required.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function prepareExportData($attendanceEmployee, $start_date, $end_date)
    {
        $exportData = new Collection();
        
        // Generate date range array
        $period = CarbonPeriod::create($start_date, $end_date);
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        $groupedByEmployee = $attendanceEmployee->groupBy('employee_id');

        foreach ($groupedByEmployee as $employee_id => $attendances) {
            $employeeName = $attendances->first()->employee->name ?? '-';
            $data = ['Employee' => $employeeName];

            // Initialize all dates with '-'
            foreach ($dates as $date) {
                $data[$date] = '-';
            }

            // Populate the clock_in - clock_out time for each date
            foreach ($attendances as $attendance) {
                $clockIn = !empty($attendance->clock_in) ? $attendance->clock_in : '-';
                $clockOut = !empty($attendance->clock_out) ? $attendance->clock_out : '-';
                $data[$attendance->date] = $clockIn . ' - ' . $clockOut;
            }

            $exportData->push($data);
        }

        return $exportData;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2) 
    {
        $earthRadius = 6371; // Earth radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance, 2); // Rounded to 2 decimal places
    }



}
