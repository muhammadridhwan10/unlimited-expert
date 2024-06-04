<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\AssignProject;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Utility;
use App\Models\Tag;
use App\Models\ProjectTask;
use App\Models\TimeTracker;
use App\Models\TrackPhoto;
use App\Models\Timesheet;
use App\Models\LogDesktop;
use App\Models\AttendanceEmployee;
use App\Models\Employee;
use DateTime;
use DatePeriod;
use DateInterval;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class ApiController extends Controller
{
    //
    use ApiResponser;

    public function login(Request $request)
    {

        $attr = $request->validate([
            'email' => 'required|string|email|',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($attr)) {
            return $this->error('Credentials not match', 401);
        }

        $settings              = Utility::settings(\Auth::user()->creatorId());

        $employee = Employee::where('user_id', auth()->user()->id)->first();
		
		if($employee->branch_id == 1)
		{
			$startTime = $settings['company_start_time'];
			$endTime   = $settings['company_end_time'];
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
		

        // $branch_rest_time = Settings::where('name', 'branch_' . $employee->branch_id . '_rest_time')->value('value');

        $settings = [
            'shot_time'=> isset($settings['interval_time'])?$settings['interval_time']:0.5,
        ];

        return $this->success([
            'token' => auth()->user()->createToken('API Token')->plainTextToken,
            'user'=> auth()->user()->name,
			'employee_id'=> auth()->user()->employee->id,
			'branch_id' => $employee->branch_id,
            // 'rest_time'=> $branch_rest_time,
            'avatar'=> auth()->user()->avatar,
            'settings' =>$settings,
        ],'Login successfully.');
    }
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->success([],'Tokens Revoked');
    }

    public function getProjects(Request $request){
        $user = auth()->user();
        $status = ["in_progress"];
        $keyword = $request->input('keyword');
    
        $projectQuery = Project::select(['project_name', 'id', 'client_id', 'status'])
            ->where('status', $status)
            ->where('project_name', 'like', '%' . $keyword . '%');
    
        if ($user->type !== 'company') {
            $assign_pro_ids = ProjectUser::where('user_id', $user->id)->pluck('project_id');
    
            $projectQuery->whereIn('id', $assign_pro_ids);
        } else {
            $projectQuery->where('created_by', $user->id);
        }
    
        $projects = $projectQuery->get();
    
        return $this->success([
            'projects' => $projects,
        ], 'Get Project List successfully.');
    }

    public function getTask(Request $request)
    {
        $date = date("Y-m-d");
        $time = date("H:i:s");
        $startTime = '10:00:12';
        $timesheet = Timesheet::where('id', 1)->where('created_by', 120)->first();
        
        $totalLateSeconds = strtotime($time) - strtotime($timesheet->start_time);

        $hours = floor($totalLateSeconds / 3600);
        $mins  = floor($totalLateSeconds / 60 % 60);
        $secs  = floor($totalLateSeconds % 60);
        $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        // dd($late);

        $timesheet['clock_out']     = $time;
        $timesheet['time']          = $late;

        if(!empty($request->date)) {
            $timesheet['date']       =  $request->date;
        }
    }

    public function addTracker(Request $request){

        $user = auth()->user();

        $LogDesktop = new LogDesktop();
        $LogDesktop->user_id = auth()->user()->id;
        $LogDesktop->last_active_at = now();
        $LogDesktop->save();
        
        if($request->has('action') && $request->action == 'start'){

            $validatorArray = [
                'project_id' => 'required|integer',
            ];
            $validator      = \Validator::make(
                $request->all(), $validatorArray
            );
            if($validator->fails())
            {
                return $this->error($validator->errors()->first(), 401);
            }
            $project= Project::find($request->project_id);

            if(empty($project)){
                return $this->error('Invalid Project', 401);
            }

            $project_id = $request->has('project_id') ? $request->project_id : null;
            TimeTracker::where('created_by', '=', $user->id)->where('is_active', '=', 1)->update(['end_time' => date("Y-m-d H:i:s")]);

            $track['name']        = $request->has('workin_on') ? $request->input('workin_on') : '';
            $track['project_id']  = $project_id;
            $track['is_billable'] =  $request->has('is_billable')? $request->is_billable:0;
            $track['tag_id']      = $request->has('workin_on') ? $request->input('workin_on') : '';
            $track['start_time']  = $request->has('time') ?  date("Y-m-d H:i:s",strtotime($request->input('time'))) : date("Y-m-d H:i:s");
            $track['task_id']     = 0;
            $track['created_by']  = $user->id;
            $track                = TimeTracker::create($track);
            $track->action        ='start';

            return $this->success( $track,'Track successfully create.');
        }else{
            $validatorArray = [
                'project_id' => 'required|integer',
                'traker_id' =>'required|integer',
            ];
            $validator      = Validator::make(
                $request->all(), $validatorArray
            );
            if($validator->fails())
            {
                return Utility::error_res($validator->errors()->first());
            }
            $tracker = TimeTracker::where('id',$request->traker_id)->first();
            // dd($tracker);
            if($tracker)
            {
                $date = date("Y-m-d");
                $tracker->end_time   = $request->has('time') ?  date("Y-m-d H:i:s",strtotime($request->input('time'))) : date("Y-m-d H:i:s");
                $tracker->is_active  = 0;
                $tracker->total_time = Utility::diffance_to_time($tracker->start_time, $tracker->end_time);
                $tracker->save();

                $timesheet = new Timesheet;
                $timesheet->project_id = $tracker->project_id;
                $timesheet->task_id = 0;
                $timesheet->date = $date;
                $seconds = $tracker->total_time;

                $H = floor($seconds / 3600);
                $i = ($seconds / 60) % 60;
                $s = $seconds % 60;

                $time = sprintf("%02d:%02d:%02d", $H, $i, $s);
                $timesheet->time = $time;
                $timesheet->platform = 'Desktop';
                $timesheet->created_by = $tracker->created_by;

                $timesheet->save();

                return $this->success( $tracker,'Stop time successfully.');
            }
        }

    }
    public function uploadImage(Request $request){
        $user = auth()->user();
        $image_base64 = base64_decode($request->img);
        $file =$request->imgName;
        if($request->has('tracker_id') && !empty($request->tracker_id)){
            $app_path = storage_path('uploads/traker_images/').$request->tracker_id.'/';
            if (!file_exists($app_path)) {
                mkdir($app_path, 0777, true);
            }

        }else{
            $app_path = storage_path('uploads/traker_images/');
            if (is_dir($app_path)) {
                mkdir($app_path, 0777, true);
            }
        }
        $file_name =  $app_path.$file;
        file_put_contents( $file_name, $image_base64);
        $new = new TrackPhoto();
        $new->track_id = $request->tracker_id;
        $new->user_id  = $user->id;
        $new->img_path  = 'uploads/traker_images/'.$request->tracker_id.'/'.$file;
        $new->time  = $request->time;
        $new->status  = 1;
        $new->save();
        return $this->success( [],'Uploaded successfully.');
    }
	
	public function clockIn(Request $request)
    {
			$settings = Utility::settings(\Auth::user()->creatorId());
        	$employeeId = $request->employee_id;
			$todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();

			if(empty($todayAttendance))
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

				$attendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', $employeeId)->where('clock_out', '=', '00:00:00')->first();

				if($attendance != null)
				{
					$attendance->clock_out = $endTime;
					$attendance->save();
				}

				$date = date("Y-m-d");
				$time = date("H:i:s");

				//late
				$totalLateSeconds = time() - strtotime($date . $startTime);
				$hours            = floor($totalLateSeconds / 3600);
				$mins             = floor($totalLateSeconds / 60 % 60);
				$secs             = floor($totalLateSeconds % 60);
				$late             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

				$checkDb = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', '=', $date)->first();

				if(empty($checkDb))
				{
					$employeeAttendance = new AttendanceEmployee();
					$employeeAttendance->employee_id   = $employeeId;
					$employeeAttendance->date          = $date;
					$employeeAttendance->status        = 'Present';
					$employeeAttendance->clock_in      = $time;
					$employeeAttendance->clock_out     = '00:00:00';
					$employeeAttendance->late          = $late;
					$employeeAttendance->early_leaving = '00:00:00';
					$employeeAttendance->overtime      = '00:00:00';
					$employeeAttendance->total_rest    = '00:00:00';
					$employeeAttendance->created_by    = \Auth::user()->id;
					$employeeAttendance->latitude = $request->latitude;
        			$employeeAttendance->longitude = $request->longitude;

					$employeeAttendance->save();

					return response()->json(['data' => $employeeAttendance], 200);
				}
				else
				{
					return response()->json(['error' => 'Employee are not allowed multiple time clock in & clock out for every day'], 400);
				}
			}
			else
			{
				return response()->json(['error' => 'Employee are not allowed multiple time clock in & clock out for every day'], 400);
			}
    }
	
	public function clockOut(Request $request, $id)
    {
			$settings = Utility::settings(\Auth::user()->creatorId());
        	$employeeId = $request->employee_id;
			$todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();

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

				$date = date("Y-m-d");
				$time = date("H:i:s");

				//late
				$totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
                $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs                     = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

				$attendanceEmployee['clock_out']     = $time;
                $attendanceEmployee['early_leaving'] = $earlyLeaving;
				
				AttendanceEmployee::where('id',$id)->update($attendanceEmployee);
				
				return response()->json(['message' => 'Employee Successfully Clock Out'], 200);


			}
			else
			{
				return response()->json(['error' => 'Employee are not allowed multiple time clock in & clock out for every day'], 400);
			}
    }
	
	public function attendanceHistory(Request $request)
	{
		$attr = $request->validate([
			'month' => 'required|integer|min:1|max:12', // Validasi bulan (1-12)
			'year' => 'required|integer|min:2000|max:9999', // Validasi tahun (minimal 2000)
			'employee_id' => 'nullable|integer', // ID karyawan opsional
		]);

		// Menyesuaikan query berdasarkan ID karyawan jika disediakan
		$query = AttendanceEmployee::query();
		if (isset($attr['employee_id'])) {
			$query->where('employee_id', $attr['employee_id']);
		}

		// Filter berdasarkan bulan dan tahun
		$query->whereYear('date', $attr['year'])
			  ->whereMonth('date', $attr['month']);

		$attendanceHistory = $query->orderBy('date', 'ASC')->get();

		// Hitung total_time untuk setiap entri
		foreach ($attendanceHistory as $attendance) {
			$clockIn = Carbon::parse($attendance->clock_in);
			$clockOut = Carbon::parse($attendance->clock_out);
			$totalTime = $clockOut->diff($clockIn)->format('%H:%I:%S');
			$attendance->total_time = $totalTime;
		}

		return response()->json(['attendance_history' => $attendanceHistory], 200);
	}
	
	public function getProfile($id)
	{
		$employee = Employee::with('user')->find($id);

		if (!$employee) {
			return response()->json(['error' => 'Employee not found'], 404);
		}

		return response()->json($employee);
	}
	

    

}
