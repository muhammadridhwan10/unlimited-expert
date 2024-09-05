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
use App\Models\LeaveType;
use App\Models\Leave;
use App\Models\Reimbursment;
use App\Models\UserOvertime;
use App\Models\ReimbursmentType;
use App\Models\Announcement;
use App\Models\AnnouncementEmployee;
use App\Models\ProductServiceCategory;
use App\Models\DocumentRequest;
use App\Mail\LeaveNotification;
use App\Mail\MedicalAllowanceNotification;
use App\Mail\ReimbursmentClientNotification;
use App\Mail\ReimbursmentPersonalNotification;
use App\Mail\OvertimeNotification;
use App\Mail\DocumentRequestNotification;
use Illuminate\Support\Facades\Mail;
use DateTime;
use DatePeriod;
use DateInterval;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;


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
		
		$user = Auth::user();
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
		
		$refreshToken = Str::random(60);

		$user->refresh_token = $refreshToken;
		$user->refresh_token_expires_at = now()->addDays(30);
		$user->save();

        return $this->success([
            'token' => auth()->user()->createToken('API Token')->plainTextToken,
			'refresh_token' => $refreshToken,
            'user'=> auth()->user()->name,
			'user_type'=> auth()->user()->type,
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
    public function uploadImage(Request $request)
    {
        // Validasi untuk memastikan file yang diupload adalah gambar
        $request->validate([
            'img' => 'required|base64image', // Validasi custom base64 image
            'imgName' => 'required|string',
        ]);
    
        // Ekstensi gambar yang diizinkan
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
        // Ambil nama file
        $file = $request->imgName;
    
        // Cek apakah ekstensi file adalah salah satu dari yang diizinkan
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return response()->json(['error' => 'Only images with the following extensions are allowed: jpg, jpeg, png, gif'], 400);
        }
    
        // Dekode gambar base64
        $image_base64 = base64_decode($request->img);
    
        // Tentukan path penyimpanan berdasarkan tracker_id
        if($request->has('tracker_id') && !empty($request->tracker_id)){
            $app_path = storage_path('uploads/traker_images/').$request->tracker_id.'/';
            if (!file_exists($app_path)) {
                mkdir($app_path, 0777, true);
            }
        } else {
            $app_path = storage_path('uploads/traker_images/');
            if (!file_exists($app_path)) {
                mkdir($app_path, 0777, true);
            }
        }
    
        // Simpan file
        $file_name = $app_path . $file;
        file_put_contents($file_name, $image_base64);
    
        // Simpan informasi ke database
        $new = new TrackPhoto();
        $new->track_id = $request->tracker_id;
        $new->user_id  = auth()->user()->id;
        $new->img_path  = 'uploads/traker_images/'.$request->tracker_id.'/'.$file;
        $new->time  = $request->time;
        $new->status  = 1;
        $new->save();
    
        return response()->json(['success' => 'Uploaded successfully.']);
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
	
	public function clockOut(Request $request)
	{
		$settings = Utility::settings(\Auth::user()->creatorId());
		$employeeId = $request->employee_id;
		$todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();

		if (!empty($todayAttendance) && $todayAttendance->clock_out == '00:00:00') {
			$employee = Employee::where('id', $employeeId)->first();

			if ($employee->branch_id == 1) {
				$startTime = Utility::getValByName('company_start_time');
				$endTime = Utility::getValByName('company_end_time');
			} elseif ($employee->branch_id == 2) {
				$startTime = "08:30";
				$endTime = "17:30";
			} elseif ($employee->branch_id == 3) {
				$startTime = "08:00";
				$endTime = "17:00";
			}

			$date = date("Y-m-d");
			$time = date("H:i:s");

			// Calculate early leaving time
			$totalEarlyLeavingSeconds = strtotime($date . ' ' . $endTime) - strtotime($time);
			$hours = floor($totalEarlyLeavingSeconds / 3600);
			$mins = floor($totalEarlyLeavingSeconds / 60 % 60);
			$secs = floor($totalEarlyLeavingSeconds % 60);
			$earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

			// Update attendance details
			$todayAttendance->clock_out = $time;
			$todayAttendance->early_leaving = $earlyLeaving;
			$todayAttendance->save();

			return response()->json(['message' => 'Employee Successfully Clock Out'], 200);
		} else {
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
	
	public function getTodayAttendance($employeeId)
    {
        $today = Carbon::today();

        $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                        ->whereDate('date', $today)
                        ->first();

        if ($attendance) {
            return response()->json([
                'clock_in' => $attendance->clock_in,
                'clock_out' => $attendance->clock_out,
            ], 200);
        } else {
            return response()->json([
                'message' => 'No attendance data found for today.'
            ], 404);
        }
    }
	
	public function getProfile($id)
	{
		$employee = Employee::with('user')->find($id);

		if (!$employee) {
			return response()->json(['error' => 'Employee not found'], 404);
		}

		return response()->json($employee);
	}
	
	public function refreshToken(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);
		
		$employeeId = $request->employee_id;

		$employee = Employee::find($employeeId);

        $user = User::where('id', $employee->user_id)->first();

        if (!$user || $user->refresh_token !== $request->refresh_token || $user->refresh_token_expires_at < now()) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        $newToken = $user->createToken('API Token')->plainTextToken;

        $newRefreshToken = Str::random(60);
        $user->update([
            'refresh_token' => $newRefreshToken,
            'refresh_token_expires_at' => Carbon::now()->addDays(30),
        ]);

        return response()->json([
            'token' => $newToken,
            'refresh_token' => $newRefreshToken,
        ], 200);
    }

    public function getApprovals(Request $request)
    {
        $user = \Auth::user();
        $approvals = [];

        $approvals = User::where(function ($query) {
            $query->where('type', 'company');
        })->get()->pluck('name', 'id');

        return response()->json($approvals, 200);
    }
	
	public function getApprovalsFinance(Request $request)
    {
        $user = \Auth::user();
		$employee = Employee::where('user_id', $user->id)->first();
        $approvals = [];

		if($employee->branch_id == 2 || $employee->branch_id == 3)
		{
			$approvals = User::where(function ($query) {
				$query->where('type', 'company');
			})->get()->pluck('name', 'id');
		}
		else
		{
			$approvals = User::where(function ($query) {
				$query->where('type', 'senior accounting');
			})->get()->pluck('name', 'id');
		}

        return response()->json($approvals, 200);
    }
	
	public function getApprovalsOvertime(Request $request)
    {
        $user = \Auth::user();
        $approvals = [];
		
		if($user->type == 'staff IT' || $user->type == 'junior accounting' || $user->type == 'junior audit' || $user->type == 'staff')
		{

			$approvals = User::where(function($query) {
                    $query->where('type', 'company')
                    ->orWhere('type', 'senior audit')
						->orWhere('type', 'senior accounting')
						->orWhere('type', 'manager audit');
                })
                ->get()
                ->pluck('name', 'id');  
			
		}
		elseif($user->type == 'senior accounting' || $user->type == 'senior audit' || $user->type == 'manager audit' || $user->type == 'partners')
		{
			
			$approvals = User::where(function($query) {
                    $query->where('type', 'company');
                })
                ->get()
                ->pluck('name', 'id');  
			
		}
		else
		{
			$approvals = User::where(function($query) {
                    $query->where('type', 'company')
                    ->orWhere('type', 'senior audit')
						->orWhere('type', 'junior audit')
						->orWhere('type', 'senior accounting')
						->orWhere('type', 'junior accounting')
						->orWhere('type', 'manager audit');
                })
                ->get()
                ->pluck('name', 'id');  
		}

        return response()->json($approvals, 200);
    }
	
	public function getProject(Request $request)
    {
        $user = \Auth::user();
        $projects = [];

        $projects = Project::all();

        return response()->json($projects, 200);
    }

    public function getLeaveTypes(Request $request)
    {
        $user = \Auth::user();
		$employee = Employee::where('user_id', $user->id)->first();
		$leaveTypes = LeaveType::all();
		$leaveCounts = [];

		foreach ($leaveTypes as $type) {
			$usedLeave = Leave::where('leave_type_id', $type->id)
				->where('employee_id', $employee->id)
				->whereYear('created_at', now()->year)
				->sum('total_leave_days');

			$leaveCounts[] = [
				'id' => $type->id,
				'title' => $type->title,
				'used' => $usedLeave,
				'total' => $type->days,
			];
		}

		return response()->json($leaveCounts, 200);
    }
	
	public function getBranch(Request $request)
    {
        $user = \Auth::user();
        $branches = [];
		
		$branches = User::where('type', '=', 'client')
            ->whereIn('name', ['Kantor Pusat', 'Kantor Cabang Bekasi', 'Kantor Cabang Malang'])
            ->get()
            ->pluck('name', 'id');

        return response()->json($branches, 200);
    }
	
	public function getClient(Request $request)
    {
        $user = \Auth::user();
        $clients = [];

        $clients = User::where('type', '=', 'client')->get();

        return response()->json($clients, 200);
    }
	
	public function getAnnouncement(Request $request)
    {
        $current_employee = $request->employee_id;

        $announcement_ids = AnnouncementEmployee::where('employee_id', $current_employee)->pluck('announcement_id');

        $announcements = Announcement::whereIn('id', $announcement_ids)
            ->where('status', 'sending')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($announcements, 200);
    }



	
	public function createLeave(Request $request)
    {
        // Validasi input dari request
        $validator = Validator::make($request->all(), [
            'approval' => 'required',  
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'leave_reason' => 'required|string',
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $leave = new Leave();
        $leave->approval = 2;
        $leave->leave_type_id = $request->leave_type_id;
        $leave->start_date = $request->start_date;
        $leave->end_date = $request->end_date;
        $leave->leave_reason = $request->leave_reason;
        $leave->employee_id = $request->employee_id;
        $leave->applied_on       = date('Y-m-d');
        $leave->total_leave_days = 0;
        $leave->status           = 'Pending';
        $leave->absence_type     = 'leave';
        $leave->created_by       = \Auth::user()->creatorId();

        try {
            $leave->save();

            $user = User::where('id', $leave->approval)->first();
            $email = $user->email;
            Mail::to($email)->send(new LeaveNotification($leave));

            return response()->json(['message' => 'Leave request created successfully', 'data' => $leave], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create leave request: ' . $e->getMessage()], 500);
        }
    }
	
	public function createMedical(Request $request)
    {
        // Validasi input termasuk validasi untuk file reimbursment_image
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'approval' => 'required|string',
            'reimbursment_type' => 'required|string',
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'reimbursment_image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120', // Validasi reimbursment_image
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $input = $request->all();

        // Proses unggah reimbursment_image jika ada
        if ($request->hasFile('reimbursment_image')) {
            $filenameWithExt = $request->file('reimbursment_image')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('reimbursment_image')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $dir = storage_path('uploads/reimbursment/');

            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            // Simpan file ke dalam direktori penyimpanan
            $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore, 's3');
            
            // Simpan path file reimbursment_image ke dalam input
            $input['reimbursment_image'] = 'uploads/reimbursment/' . $fileNameToStore;
        } else {
            // Jika tidak ada file yang diunggah
            $input['reimbursment_image'] = '';
        }

        // Data tambahan untuk medical allowance
        $input['status'] = 'Pending';
        $input['created_by'] = \Auth::user()->creatorId();
        $input['created_date'] = Carbon::now()->format('Y-m-d');

        // Buat data medical allowance
        $medicalAllowance = Reimbursment::create($input);

        // Kirim email notifikasi
        $user = User::where('id', $medicalAllowance->approval)->first();
        $email = $user->email;
        Mail::to($email)->send(new MedicalAllowanceNotification($medicalAllowance));

        return response()->json(['message' => 'Medical Allowance Request successfully created', 'data' => $medicalAllowance], 200);
    }

	
	public function createReimbursment(Request $request)
    {
        // Validasi input termasuk validasi untuk file reimbursment_image
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'approval' => 'required|string',
            'reimbursment_type' => 'required|string',
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'reimbursment_image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120', // Validasi reimbursment_image
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $input = $request->all();

        // Proses unggah reimbursment_image jika ada
        if ($request->hasFile('reimbursment_image')) {
            $filenameWithExt = $request->file('reimbursment_image')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('reimbursment_image')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $dir = storage_path('uploads/reimbursment/');

            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            // Simpan file ke dalam direktori penyimpanan
            $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore, 's3');
            
            // Simpan path file reimbursment_image ke dalam input
            $input['reimbursment_image'] = 'uploads/reimbursment/' . $fileNameToStore;
        } else {
            // Jika tidak ada file yang diunggah
            $input['reimbursment_image'] = '';
        }

        // Data tambahan untuk reimbursment
        $input['status'] = 'Pending';
        $input['created_by'] = \Auth::user()->creatorId();
        $input['created_date'] = Carbon::now()->format('Y-m-d');

        // Buat data reimbursment
        $reimbursment = Reimbursment::create($input);

        // Kirim email notifikasi
        if ($reimbursment->reimbursment_type == "Reimbursment Client") {
            $user = User::where('id', $reimbursment->approval)->first();
            $email = $user->email;
            Mail::to($email)->send(new ReimbursmentClientNotification($reimbursment));
        } elseif ($reimbursment->reimbursment_type == "Reimbursment Personal") {
            $user = User::where('id', $reimbursment->approval)->first();
            $email = $user->email;
            Mail::to($email)->send(new ReimbursmentPersonalNotification($reimbursment));
        }

        return response()->json(['message' => 'Reimbursment Request successfully created', 'data' => $reimbursment], 200);
    }

	
	public function createOvertime(Request $request)
    {
        $validator = Validator::make($request->all(), [
               'start_time' => 'required',
			   'end_time' => 'required',
			   'start_date' => 'required',
			   'approval' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
		
		$employees                = Employee::where('user_id', '=', $request->approval)->first();

        $overtime                   = new UserOvertime();
		$overtime->user_id          = $request->user_id;
        $overtime->project_id       = $request->project_id;
		$overtime->start_time       = $request->start_time;
		$overtime->end_time         = $request->end_time;
		$overtime->start_date       = $request->start_date;
		$overtime->approval         = $employees->id;
		$overtime->status           = 'Pending';
		$overtime->created_date     = Carbon::now()->format('Y-m-d');
		$overtime->total_time       = 0;
		$overtime->note             = $request->note;
		$overtime->save();

        try {
            $overtime->save();

            $user = Employee::where('id', $overtime->approval)->first();
            $email = $user->email;
            Mail::to($email)->send(new OvertimeNotification($overtime));
            
            return response()->json(['message' => 'Overtime created successfully', 'data' => $overtime], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create overtime request: ' . $e->getMessage()], 500);
        }
    }
	
	public function getReimbursmentTypes(Request $request)
	{
		$employee_id = $request->input('employee_id');
		$currentYear = now()->year;

		$reimbursment_counts = [];
		$reimbursment_types = ReimbursmentType::where('created_by', \Auth::user()->creatorId())->get();

		foreach ($reimbursment_types as $type) {
			$counts = Reimbursment::select(\DB::raw('COALESCE(SUM(reimbursment.amount),0) AS total_amount'))
				->where('reimbursment_type', $type->title)
				->whereYear('date', $currentYear)
				->where('employee_id', $employee_id)
				->where('status', '=', 'Paid')
				->groupBy('reimbursment.reimbursment_type')
				->first();

			$reimbursment_count = [];
			$reimbursment_count['total_amount'] = !empty($counts) ? $counts['total_amount'] : 0;
			$reimbursment_count['title'] = $type->title;
			$reimbursment_count['amount'] = $type->amount;
			$reimbursment_count['id'] = $type->id;
			$reimbursment_count['remaining_amount'] = $type->amount - $reimbursment_count['total_amount'];

			$reimbursment_counts[] = $reimbursment_count;
		}

		return response()->json($reimbursment_counts);
	}
	
	public function createAbsence(Request $request)
    {
        // Validasi input termasuk validasi untuk file sick_letter
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'total_sick_days' => 'required|string',
            'date_sick_letter' => 'required|string',
            'sick_letter' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // Validasi sick_letter
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $input = $request->all();

        // Proses unggah sick_letter jika ada
        if ($request->hasFile('sick_letter')) {
            $filenameWithExt = $request->file('sick_letter')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('sick_letter')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $dir = storage_path('uploads/sick_letter/' . \Auth::user()->name . '/');

            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            // Simpan file ke dalam direktori penyimpanan
            $path = $request->file('sick_letter')->storeAs('uploads/sick_letter/' . \Auth::user()->name . '/', $fileNameToStore, 's3');
            
            // Simpan path file sick_letter ke dalam input
            $input['sick_letter'] = 'uploads/sick_letter/' . \Auth::user()->name . '/' . $fileNameToStore;
        } else {
            // Jika tidak ada file yang diunggah
            $input['sick_letter'] = '';
        }

        // Data tambahan untuk absence
        $input['absence_type'] = 'sick';
        $input['status'] = 'Approved';
        $input['created_by'] = \Auth::user()->creatorId();
        $input['applied_on'] = Carbon::now()->format('Y-m-d');

        // Buat data absence
        $absence = Leave::create($input);

        return response()->json(['message' => 'Absence Request successfully created', 'data' => $absence], 200);
    }

    
    public function createDocumentRequest(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'document_type' => 'required',
                           ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $employee_id           = $request->input('employee_id');

        $employee_id = $request->input('employee_id');
        $document = new DocumentRequest();
        $document->employee_id = $employee_id;
        $document->approval = $request->input('approval');
        $document->document_type = $request->input('document_type');

        if ($document->document_type == 'Invoice') {
            $document->client_name = $request->input('client_name');
            $document->email_attention = $request->input('email_attention');
            $document->name_attention = $request->input('name_attention');
            $document->position_attention = $request->input('position_attention');
            $document->address = $request->input('address');
            $document->no_pic = $request->input('no_pic');
            $document->sender_or_receiver = $request->input('sender_or_receiver');
        } elseif ($document->document_type == 'Proposal' || $document->document_type == 'EL') {
            $document->client_name = $request->input('client_name');
            $document->email_attention = $request->input('email_attention');
            $document->name_attention = $request->input('name_attention');
            $document->position_attention = $request->input('position_attention');
            $document->address = $request->input('address');
            $document->service_type = $request->input('service_type');
            $document->period = $request->input('period');
            $document->termin1 = $request->input('termin1');
            $document->termin2 = $request->input('termin2');
            $document->termin3 = $request->input('termin3');
            $document->fee = $request->input('fee');
            $document->pph23 = $request->input('pph23');
        } elseif ($document->document_type == 'Barcode LAI') {
            $document->client_name = $request->input('client_name');
        }
        
        $document->note = $request->note;
        $document->status = 'Pending';
        $document->created_by  = \Auth::user()->creatorId();
        $document->save();

        $firebaseToken = User::where('id', $request->approval)->whereNotNull('device_token')->pluck('device_token');
        $SERVER_API_KEY = 'AAAA9odnGYA:APA91bEW0H4cOYVOnneXeKl-cE1ECxNFiRmwzEAdspRw34q6RwjGNqO2o6l_4T3HtyIR0ahZ5g8tb_0AST6RnxOchE8S6DEEby_HpwJHDk1H9GYmKwrcFRkPYWDiNvjTnQoIcDjj5Ogx';

        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => 'AUP-APPS',
                "body" => \Auth::user()->name . '  Requests Document ' . $request->document_type,  
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

        $users = User::where('email', '=', 'info@au-partners.com')->orWhere('email', '=', 'luqman@au-partners.com')->orWhere('email', '=', 'melya.lubis@au-partners.com')->get();
            
        foreach ($users as $user) {
            Mail::to($user->email)->send(new DocumentRequestNotification($document));
        }

        return response()->json(['message' => 'Document Request successfully created', 'data' => $document], 200);
    }

    public function getService(Request $request)
    {
        $user = \Auth::user();
        $services = [];

        $services = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())->where('type', 1)->get();

        return response()->json($services, 200);
    }

}
