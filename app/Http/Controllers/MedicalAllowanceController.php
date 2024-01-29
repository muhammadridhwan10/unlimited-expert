<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Reimbursment;
use App\Models\ReimbursmentType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use App\Mail\MedicalAllowanceNotification;
use App\Mail\MedicalAllowanceApprovalNotification;

class MedicalAllowanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(\Auth::user()->type == 'admin')
        {
            $reimbursment   = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->get();

            $employee = Employee::all();
            $employee = $employee->pluck('id');
            $employeeReimbursment = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->whereIn('employee_id', $employee);

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeReimbursment->whereBetween('date', [$start_date, $end_date]);
            } 

            if (!empty($request->employee_id)) {
                $employeeReimbursment->where('employee_id', $request->employee_id);
            }

            $employeeReimbursment = $employeeReimbursment->get();

            $employees = Employee::all()->pluck('name','id');


            $users        = \Auth::user();
            $employee     = Employee::where('user_id', '=', $users->id)->first();
            $approval     = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->where('approval', '=', $employee->id)->where('status','=', 'Pending')->get();
        }
        elseif(\Auth::user()->type == 'company')
        {
            $reimbursment   = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->get();

            $employee = Employee::all();
            $employee = $employee->pluck('id');
            $employeeReimbursment = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->whereIn('employee_id', $employee);

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeReimbursment->whereBetween('date', [$start_date, $end_date]);
            } 

            if (!empty($request->employee_id)) {
                $employeeReimbursment->where('employee_id', $request->employee_id);
            }

            $employeeReimbursment = $employeeReimbursment->get();

            $employees = Employee::all()->pluck('name','id');

            $users        = \Auth::user();
            $employee     = Employee::where('user_id', '=', $users->id)->first();
            $approval     = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->where('approval', '=', $employee->id)->where('status','=', 'Pending')->get();
        }
        elseif(\Auth::user()->type == 'senior accounting')
        {
            $reimbursment   = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->get();

            $employee = Employee::all();
            $employee = $employee->pluck('id');
            $employeeReimbursment = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->whereIn('employee_id', $employee);

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeReimbursment->whereBetween('date', [$start_date, $end_date]);
            } 

            if (!empty($request->employee_id)) {
                $employeeReimbursment->where('employee_id', $request->employee_id);
            }

            $employeeReimbursment = $employeeReimbursment->get();

            $employees = Employee::all()->pluck('name','id');


            $users        = \Auth::user();
            $employee     = Employee::where('user_id', '=', $users->id)->first();
            $approval     = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->where('approval', '=', $users->id)->where('status','=', 'Pending')->get();
        }
        elseif(\Auth::user()->type == 'senior audit' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'staff IT' || \Auth::user()->type == 'intern')
        {

            $employee                      = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('id');
            $employeeReimbursment = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->whereIn('employee_id', $employee);

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeReimbursment->whereBetween('date', [$start_date, $end_date]);
            } 

            if (!empty($request->employee_id)) {
                $employeeReimbursment->where('employee_id', $request->employee_id);
            }

            $employeeReimbursment = $employeeReimbursment->get();

            $employees = Employee::where('user_id', '=', \Auth::user()->id)->first()->pluck('name','id');


            $users        = \Auth::user();
            $employee     = Employee::where('user_id', '=', $users->id)->first();
            $reimbursment   = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->where('employee_id', '=', $employee->id)->get();
            $approval     = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->where('approval', '=', $users->id)->where('status','=', 'Pending')->get();
        }
        else
        {
            $reimbursment   = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->get();

            $employee                      = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('id');
            $employeeReimbursment = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->whereIn('employee_id', $employee);

            if (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $employeeReimbursment->whereBetween('date', [$start_date, $end_date]);
            } 

            if (!empty($request->employee_id)) {
                $employeeReimbursment->where('employee_id', $request->employee_id);
            }

            $employeeReimbursment = $employeeReimbursment->get();

            $employees = Employee::where('user_id', '=', \Auth::user()->id)->first()->pluck('name','id');


            $users        = \Auth::user();
            $employee     = Employee::where('user_id', '=', $users->id)->first();
            $approval     = Reimbursment::where('reimbursment_type', '=', 'Medical Allowance')->where('approval', '=', \Auth::user()->id)->where('status','=', 'Pending')->get();
        }

        return view('medical-allowance.index', compact('reimbursment','approval','employeeReimbursment','employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(\Auth::user()->type == 'staff IT' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior accounting' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'intern')
        {
            $employees                       = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('name', 'id');
            $approval                        = User::where('type', '=', 'senior accounting')->get()->pluck('name', 'id');                
            $client = User::where('type', '=', 'client')
            ->whereIn('name', ['Kantor Pusat', 'Kantor Cabang Bekasi', 'Kantor Cabang Malang'])
            ->get()
            ->pluck('name', 'id');
            $reimbursment_type               = ReimbursmentType::where('created_by', '=', \Auth::user()->creatorId())->get();                
        }
        elseif(Auth::user()->type == 'admin')
        {
            $employees                       = Employee::all()->pluck('name', 'id');
            $approval                        = User::where('type', '=', 'senior accounting')->get()->pluck('name', 'id'); 
            $client = User::where('type', '=', 'client')
            ->whereIn('name', ['Kantor Pusat', 'Kantor Cabang Bekasi', 'Kantor Cabang Malang'])
            ->get()
            ->pluck('name', 'id');
            $reimbursment_type               = ReimbursmentType::where('created_by', '=', \Auth::user()->creatorId())->get();                               
        }
        elseif(Auth::user()->type == 'company')
        {
            $employees                       = Employee::all()->pluck('name', 'id');
            $approval                        = User::where('type', '=', 'senior accounting')->get()->pluck('name', 'id'); 
            $client = User::where('type', '=', 'client')
            ->whereIn('name', ['Kantor Pusat', 'Kantor Cabang Bekasi', 'Kantor Cabang Malang'])
            ->get()
            ->pluck('name', 'id');
            $reimbursment_type               = ReimbursmentType::where('created_by', '=', \Auth::user()->creatorId())->get();                            
        }
        else
        {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $approval        = User::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $client = User::where('type', '=', 'client')
            ->whereIn('name', ['Kantor Pusat', 'Kantor Cabang Bekasi', 'Kantor Cabang Malang'])
            ->get()
            ->pluck('name', 'id');
            $reimbursment_type               = ReimbursmentType::where('created_by', '=', \Auth::user()->creatorId())->get();                    
        }

        return view('medical-allowance.create', compact('employees', 'approval','client','reimbursment_type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'client_id' => 'required',
                               'approval' => 'required',
                               'date' => 'required',
                               'amount' => 'required',
                               'reimbursment_image' => 'mimes:png,jpeg,jpg|max:10240',
                           ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $employee = Employee::where('user_id', '=', Auth::user()->id)->first();

        if(!empty($request->reimbursment_image))
        {
            $filenameWithExt = $request->file('reimbursment_image')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('reimbursment_image')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $dir             = storage_path('uploads/reimbursment/');

            if(!file_exists($dir))
            {
                mkdir($dir, 0777, true);
            }
            // $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore);
            $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore, 's3');
        }

        $date            = Carbon::now()->format('Y-m-d');

        $reimbursment    = new Reimbursment();

        if(\Auth::user()->type == "admin" || \Auth::user()->type == "company" )
        {
            $reimbursment->employee_id = $request->employee_id;
        }
        else
        {
            $reimbursment->employee_id = $employee->id;
        }

        $reimbursment->client_id            = $request->client_id;
        $reimbursment->approval             = $request->approval;
        $reimbursment->reimbursment_type    = $request->reimbursment_type;
        $reimbursment->date                 = $request->date;
        $reimbursment->amount               = $request->amount;
        $reimbursment->description          = $request->description;
        $reimbursment->status               = 'Pending';
        $reimbursment->created_by           = \Auth::user()->creatorId();
        $reimbursment->reimbursment_image  = !empty('uploads/reimbursment/' . $request->reimbursment_image) ? 'uploads/reimbursment/' . $fileNameToStore : '';
        $reimbursment->created_date         = $date;

        $reimbursment->save();

        //Email Notification
        $user = User::where('id', $reimbursment->approval)->first();
        $email = $user->email;
        Mail::to($email)->send(new MedicalAllowanceNotification($reimbursment));

        //Email Notification
        // $user = User::where('id', $leave->approval)->first();
        // $email = $user->email;
        // Mail::to($email)->send(new LeaveNotification($leave));

        return redirect()->route('medical-allowance.index')->with('success', __('Medical Allowance successfully created.'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
        {
            $ids               = Crypt::decrypt($id);
            $reimbursment      = Reimbursment::find($ids);
            $employees         = Employee::get()->pluck('name', 'id');
            $approval          = User::where('type', '=', 'senior accounting')->get()->pluck('name', 'id');                
            $client = User::where('type', '=', 'client')
            ->whereIn('name', ['Kantor Pusat', 'Kantor Cabang Bekasi', 'Kantor Cabang Malang'])
            ->get()
            ->pluck('name', 'id');
            $reimbursment_type               = ReimbursmentType::where('created_by', '=', \Auth::user()->creatorId())->get();                                           
        }
        else
        {
            $ids               = Crypt::decrypt($id);
            $reimbursment      = Reimbursment::find($ids);
            $employees    = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('name', 'id');
            $approval     = User::where('type', '=', 'senior accounting')->get()->pluck('name', 'id');                
            $client = User::where('type', '=', 'client')
            ->whereIn('name', ['Kantor Pusat', 'Kantor Cabang Bekasi', 'Kantor Cabang Malang'])
            ->get()
            ->pluck('name', 'id');
            $reimbursment_type               = ReimbursmentType::where('created_by', '=', \Auth::user()->creatorId())->get();                                             
        }

        return view('medical-allowance.edit', compact('reimbursment', 'employees', 'client', 'approval','reimbursment_type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $reimbursment = Reimbursment::find($id);

        $validator = \Validator::make(
            $request->all(), [
                               'client_id' => 'required',
                               'approval' => 'required',
                               'date' => 'required',
                               'amount' => 'required',
                               'reimbursment_image' => 'mimes:png,jpeg,jpg|max:10240',
                           ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $employee = Employee::where('user_id', '=', Auth::user()->id)->first();

        if(!empty($request->reimbursment_image))
        {
            $filenameWithExt = $request->file('reimbursment_image')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('reimbursment_image')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $dir             = storage_path('uploads/reimbursment/');

            if(!file_exists($dir))
            {
                mkdir($dir, 0777, true);
            }
            // $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore);
            $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore, 's3');
        }

        $date            = Carbon::now()->format('Y-m-d');

        if(\Auth::user()->type == "admin" || \Auth::user()->type == "company" )
        {
            $reimbursment->employee_id = $request->employee_id;
        }
        else
        {
            $reimbursment->employee_id = $employee->id;
        }

        $reimbursment->client_id            = $request->client_id;
        $reimbursment->approval             = $request->approval;
        $reimbursment->reimbursment_type    = $request->reimbursment_type;
        $reimbursment->date                 = $request->date;
        $reimbursment->amount               = $request->amount;
        $reimbursment->description          = $request->description;
        $reimbursment->status               = 'Pending';
        $reimbursment->created_by           = \Auth::user()->creatorId();
        $reimbursment->reimbursment_image  = !empty('uploads/reimbursment/' . $request->reimbursment_image) ? 'uploads/reimbursment/' . $fileNameToStore : '';
        $reimbursment->created_date         = $date;

        $reimbursment->save();

        return redirect()->back()->with('success', __('Medical Allowance successfully updated.'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getMedicalAllowanceImages(Request $request)
    {
        $reimbursment   = Reimbursment::find($request->id);
        $images         = Reimbursment::where('id',$request->id)->get();
        return view('medical-allowance.images',compact('images','reimbursment'));
    }

    public function changeaction(Request $request)
    {

        $reimbursment = Reimbursment::find($request->reimbursment_id);

        $reimbursment->status = $request->status;
        if($reimbursment->status == 'Paid')
        {
            $reimbursment->status           = 'Paid';
        }

        $reimbursment->save();

        //Email Notification
        $employee = Employee::where('id', $reimbursment->employee_id)->first();
        $email = $employee->email;
        Mail::to($email)->send(new MedicalAllowanceApprovalNotification($reimbursment));

        return redirect()->route('medical-allowance.index')->with('success', __('Medical Allowance successfully updated.'));
    }
    
    public function action($id)
    {

        $reimbursment   = Reimbursment::find($id);
        $employee       = Employee::where('id', $reimbursment->employee_id)->first();
        $user           = User::find($employee->user_id);
        $client         = User::find($reimbursment->client_id);

        return view('medical-allowance.action', compact('reimbursment', 'user', 'client'));
    }

    public function jsoncount(Request $request)
    {

        $reimbusment_counts=[];
        $currentYear = now()->year;
        $reimbursment_types = ReimbursmentType::where('created_by',\Auth::user()->creatorId())->get();
        foreach ($reimbursment_types as  $type) {
            $counts = Reimbursment::select(\DB::raw('COALESCE(SUM(reimbursment.amount),0) AS total_amount'))
            ->where('reimbursment_type',$type->title)
            ->whereYear('date', $currentYear) 
            ->groupBy('reimbursment.reimbursment_type')
            ->where('employee_id',$request->employee_id)
            ->where('status','=', 'Paid')
            ->first();

            $reimbusment_count['total_amount']=!empty($counts)?$counts['total_amount']:0;
            $reimbusment_count['title']=$type->title;
            $reimbusment_count['amount']=$type->amount;
            $reimbusment_count['id']=$type->id;
            $reimbusment_count['remaining_amount'] = $type->amount - $reimbusment_count['total_amount'];
            $reimbusment_counts[]=$reimbusment_count;
        }

        return $reimbusment_counts;

    }
}
