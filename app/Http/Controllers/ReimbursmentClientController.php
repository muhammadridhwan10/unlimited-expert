<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Reimbursment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use App\Mail\ReimbursmentClientNotification;
use App\Mail\ReimbursmentClientApprovalNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ReimbursmentClientController extends Controller
{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->get('show_entries', 10);
        
        // Get filter parameters
        $filters = [
            'employee_filter' => $request->get('employee_filter'),
            'client_filter' => $request->get('client_filter'),
            'reimbursement_type_filter' => $request->get('reimbursement_type_filter'),
            'status_filter' => $request->get('status_filter'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'month' => $request->get('month'),
            'employee_id' => $request->get('employee_id'), // Keep existing functionality
            'client_id' => $request->get('client_id'), // Keep existing functionality
            'search' => $request->get('search'),
        ];

        if(\Auth::user()->type == 'admin')
        {
            $reimbursment = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')->get();
            $employee = Employee::all();
            $employee_ids = $employee->pluck('id');
            
            // Build query with filters
            $employeeReimbursment_query = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')
                                                    ->whereIn('employee_id', $employee_ids);
            $employeeReimbursment_query = $this->applyClientFilters($employeeReimbursment_query, $filters);
            $employeeReimbursment = $employeeReimbursment_query->orderByDesc('id')->paginate($perPage);

            $employees = Employee::all()->pluck('name','id');
            $client = User::where('type','=','client')->pluck('name','id');
            $users = \Auth::user();
            $approval = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')
                                    ->where('approval', '=', $users->id)
                                    ->where('status','=', 'Pending')
                                    ->orderByDesc('id')
                                    ->get();

            // Get data for filter dropdowns
            $employees_list = Employee::all();
            $clients = User::whereIn('type', ['client', 'staff_client'])->get();
            $reimbursement_types = Reimbursment::$reimbursment_type;
        }
        elseif(\Auth::user()->type == 'company')
        {
            $reimbursment = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')->get();
            $employee = Employee::all();
            $employee_ids = $employee->pluck('id');
            
            // Build query with filters
            $employeeReimbursment_query = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')
                                                    ->whereIn('employee_id', $employee_ids);
            $employeeReimbursment_query = $this->applyClientFilters($employeeReimbursment_query, $filters);
            $employeeReimbursment = $employeeReimbursment_query->orderByDesc('id')->paginate($perPage);

            $employees = Employee::all()->pluck('name','id');
            $client = User::where('type','=','client')->pluck('name','id');
            $users = \Auth::user();
            $approval = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')
                                    ->where('approval', '=', $users->id)
                                    ->where('status','=', 'Pending')
                                    ->orderByDesc('id')
                                    ->get();

            // Get data for filter dropdowns
            $employees_list = Employee::all();
            $clients = User::whereIn('type', ['client', 'staff_client'])->get();
            $reimbursement_types = Reimbursment::$reimbursment_type;
        }
        elseif(\Auth::user()->type == 'senior accounting')
        {
            $reimbursment = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')->get();
            $employee = Employee::all();
            $employee_ids = $employee->pluck('id');
            
            // Build query with filters
            $employeeReimbursment_query = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')
                                                    ->whereIn('employee_id', $employee_ids);
            $employeeReimbursment_query = $this->applyClientFilters($employeeReimbursment_query, $filters);
            $employeeReimbursment = $employeeReimbursment_query->orderByDesc('id')->paginate($perPage);

            $employees = Employee::all()->pluck('name','id');
            $client = User::where('type','=','client')->pluck('name','id');
            $users = \Auth::user();
            $approval = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')
                                    ->where('approval', '=', $users->id)
                                    ->where('status','=', 'Pending')
                                    ->orderByDesc('id')
                                    ->get();

            // Get data for filter dropdowns
            $employees_list = Employee::all();
            $clients = User::whereIn('type', ['client', 'staff_client'])->get();
            $reimbursement_types = Reimbursment::$reimbursment_type;
        }
        elseif(\Auth::user()->type == 'senior audit' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'staff IT' || \Auth::user()->type == 'staff' || \Auth::user()->type == 'intern')
        {
            $employee = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('id');
            
            // Build query with filters
            $employeeReimbursment_query = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')
                                                    ->whereIn('employee_id', $employee);
            $employeeReimbursment_query = $this->applyClientFilters($employeeReimbursment_query, $filters);
            $employeeReimbursment = $employeeReimbursment_query->orderByDesc('id')->paginate($perPage);

            $employees = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('name','id');
            $client = User::where('type','=','client')->pluck('name','id');
            $users = \Auth::user();
            $employee_detail = Employee::where('user_id', '=', $users->id)->first();
            $reimbursment = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')
                                    ->where('employee_id', '=', $employee_detail->id)
                                    ->get();
            $approval = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')
                                    ->where('approval', '=', $users->id)
                                    ->where('status','=', 'Pending')
                                    ->orderByDesc('id')
                                    ->get();

            // Get data for filter dropdowns (limited to current employee)
            $employees_list = collect([$employee_detail]); // Only current employee
            $clients = User::whereIn('type', ['client', 'staff_client'])->get();
            $reimbursement_types = Reimbursment::$reimbursment_type;
        }
        else
        {
            $reimbursment = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')->get();
            $employee = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('id');
            
            // Build query with filters
            $employeeReimbursment_query = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')
                                                    ->whereIn('employee_id', $employee);
            $employeeReimbursment_query = $this->applyClientFilters($employeeReimbursment_query, $filters);
            $employeeReimbursment = $employeeReimbursment_query->orderByDesc('id')->paginate($perPage);

            $employees = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('name','id');
            $client = User::where('type','=','client')->pluck('name','id');
            $users = \Auth::user();
            $employee_detail = Employee::where('user_id', '=', \Auth::user()->id)->first();
            $approval = Reimbursment::where('reimbursment_type', '=', 'Reimbursment Client')
                                    ->where('approval', '=', \Auth::user()->id)
                                    ->where('status','=', 'Pending')
                                    ->orderByDesc('id')
                                    ->get();

            // Get data for filter dropdowns
            $employees_list = collect([$employee_detail]); // Only current employee
            $clients = User::whereIn('type', ['client', 'staff_client'])->get();
            $reimbursement_types = Reimbursment::$reimbursment_type;
        }

        // Append query parameters to pagination links
        $employeeReimbursment->appends($request->query());

        return view('reimbursment-client.index', compact(
            'reimbursment',
            'approval',
            'employeeReimbursment',
            'employees',
            'client',
            'employees_list',
            'clients',
            'reimbursement_types'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(\Auth::user()->type == 'staff IT' || \Auth::user()->type == 'staff' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior accounting' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'intern')
        {
            $employees                       = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('name', 'id');
            $approval = User::where(function($query) {
                $query->where('type', 'senior accounting');
            })
            ->get()
            ->pluck('name', 'id');
            $client                          = User::where('type', '=', 'client')->get()->pluck('name', 'id');                
        }
        elseif(Auth::user()->type == 'admin')
        {
            $employees                       = Employee::all()->pluck('name', 'id');
            $approval = User::where(function($query) {
                $query->where('type', 'senior accounting');
            })
            ->get()
            ->pluck('name', 'id');
            $client                          = User::where('type', '=', 'client')->get()->pluck('name', 'id');                     
        }
        elseif(Auth::user()->type == 'company')
        {
            $employees                       = Employee::all()->pluck('name', 'id');
            $approval = User::where(function($query) {
                $query->where('type', 'senior accounting');
            })
            ->get()
            ->pluck('name', 'id');
            $client                          = User::where('type', '=', 'client')->get()->pluck('name', 'id');                     
        }
        else
        {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $approval        = User::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $client                          = User::where('type', '=', 'client')->get()->pluck('name', 'id');      
        }

        return view('reimbursment-client.create', compact('employees', 'approval','client'));
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

            Storage::disk('minio')->put(
                'uploads/reimbursment/' . $fileNameToStore,
                file_get_contents($request->file('reimbursment_image'))
            );

            // $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore);
            // $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore, 's3');
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
        $reimbursment->reimbursment_type    = "Reimbursment Client";
        $reimbursment->date                 = $request->date;
        $reimbursment->amount               = $request->amount;
        $reimbursment->description          = $request->description;
        $reimbursment->status               = 'Pending';
        $reimbursment->created_by           = \Auth::user()->creatorId();
        $reimbursment->reimbursment_image  = !empty('uploads/reimbursment/' . $request->reimbursment_image) ? 'uploads/reimbursment/' . $fileNameToStore : '';
        $reimbursment->created_date         = $date;

        $reimbursment->save();

        // Email Notification
        $user = User::where('id', $reimbursment->approval)->first();
        $email = $user->email;
        Mail::to($email)->send(new ReimbursmentClientNotification($reimbursment));

        return redirect()->route('reimbursment-client.index')->with('success', __('Reimbursment Client successfully created.'));
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
            $approval = User::where(function($query) {
                $query->where('type', 'admin')
                      ->orWhere('type', 'company')
                      ->orWhere('type', 'senior accounting');
            })
            ->get()
            ->pluck('name', 'id');              
            $client            = User::where('type', '=', 'client')->get()->pluck('name', 'id');                     
        }
        else
        {
            $ids               = Crypt::decrypt($id);
            $reimbursment      = Reimbursment::find($ids);
            $employees    = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('name', 'id');
            $approval = User::where(function($query) {
                $query->where('type', 'admin')
                      ->orWhere('type', 'company')
                      ->orWhere('type', 'senior accounting');
            })
            ->get()
            ->pluck('name', 'id');             
            $client       = User::where('type', '=', 'client')->get()->pluck('name', 'id');                     
        }

        return view('reimbursment-client.edit', compact('reimbursment', 'employees', 'client', 'approval'));
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

            Storage::disk('minio')->put(
                'uploads/reimbursment/' . $fileNameToStore,
                file_get_contents($request->file('reimbursment_image'))
            );

            // $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore);
            // $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore, 's3');
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
        $reimbursment->reimbursment_type    = "Reimbursment Client";
        $reimbursment->date                 = $request->date;
        $reimbursment->amount               = $request->amount;
        $reimbursment->description          = $request->description;
        $reimbursment->status               = 'Pending';
        $reimbursment->created_by           = \Auth::user()->creatorId();
        $reimbursment->reimbursment_image  = !empty('uploads/reimbursment/' . $request->reimbursment_image) ? 'uploads/reimbursment/' . $fileNameToStore : '';
        $reimbursment->created_date         = $date;

        $reimbursment->save();

        return redirect()->back()->with('success', __('Reimbursment Client successfully updated.'));
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
        return view('reimbursment-client.images',compact('images','reimbursment'));
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

        if($reimbursment->status == 'Paid')
        {
            //Email Notification
            $employee = Employee::where('id', $reimbursment->employee_id)->first();
            $email = $employee->email;
            Mail::to($email)->send(new ReimbursmentClientApprovalNotification($reimbursment));
        }
        

        return redirect()->route('reimbursment-client.index')->with('success', __('Reimbursment Client successfully updated.'));
    }
    
    public function action($id)
    {

        $reimbursment   = Reimbursment::find($id);
        $employee       = Employee::where('id', $reimbursment->employee_id)->first();
        $user           = User::find($employee->user_id);
        $client         = User::find($reimbursment->client_id);

        return view('reimbursment-client.action', compact('reimbursment', 'user', 'client'));
    }

    public function approveMultiple(Request $request)
    {

        $selectedIds = $request->input('selectedIds');

        Reimbursment::whereIn('id', $selectedIds)->update(['status' => 'Paid']);

        foreach ($selectedIds as $reimbursmentId) {
            $reimbursment = Reimbursment::find($reimbursmentId);
            $employee = Employee::find($reimbursment->employee_id);
    
            if ($employee) {
                $email = $employee->email;
                Mail::to($email)->send(new ReimbursmentClientApprovalNotification($reimbursment));
            }
        }

        return redirect()->route('reimbursment-client.index')->with('success', __('Reimbursment Client successfully updated.'));
    }

    private function applyClientFilters($query, $filters)
    {
        // Employee filter (existing functionality)
        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        // Client filter (existing functionality)
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        // New employee filter
        if (!empty($filters['employee_filter'])) {
            $query->where('employee_id', $filters['employee_filter']);
        }

        // New client filter
        if (!empty($filters['client_filter'])) {
            $query->where('client_id', $filters['client_filter']);
        }

        // Reimbursement type filter
        if (!empty($filters['reimbursement_type_filter'])) {
            $query->where('reimbursment_type', $filters['reimbursement_type_filter']);
        }

        // Status filter
        if (!empty($filters['status_filter'])) {
            $query->where('status', $filters['status_filter']);
        }

        // Date range filter
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('date', [$filters['date_from'], $filters['date_to']]);
        } elseif (!empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        } elseif (!empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        // Month filter (existing functionality)
        if (!empty($filters['month'])) {
            $month = date('m', strtotime($filters['month']));
            $year = date('Y', strtotime($filters['month']));
            $start_date = date($year . '-' . $month . '-01');
            $end_date = date($year . '-' . $month . '-t');
            $query->whereBetween('date', [$start_date, $end_date]);
        }

        // Search filter (search in employee name, client name, or description)
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('description', 'LIKE', $searchTerm)
                ->orWhereHas('employee', function($employeeQuery) use ($searchTerm) {
                    $employeeQuery->where('name', 'LIKE', $searchTerm);
                })
                ->orWhereHas('client', function($clientQuery) use ($searchTerm) {
                    $clientQuery->where('name', 'LIKE', $searchTerm);
                });
            });
        }

        return $query;
    }
}
