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
use DateTime;
use DatePeriod;
use DateInterval;
use Illuminate\Support\Facades\Validator;


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

        // $employee = Employee::where('user_id', auth()->user()->id)->where('created_by', '=', \Auth::user()->creatorId())->first();

        // $branch_rest_time = Settings::where('name', 'branch_' . $employee->branch_id . '_rest_time')->value('value');

        $settings = [
            'shot_time'=> isset($settings['interval_time'])?$settings['interval_time']:0.5,
        ];

        return $this->success([
            'token' => auth()->user()->createToken('API Token')->plainTextToken,
            'user'=> auth()->user()->name,
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
    

}
