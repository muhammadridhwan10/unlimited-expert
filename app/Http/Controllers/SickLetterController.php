<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\Mail\LeaveActionSend;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\LeaveNotification;
use App\Mail\LeaveApprovalNotification;
use App\Mail\LeaveRejectNotification;
use Illuminate\Support\Facades\Mail;

class SickLetterController extends Controller
{
    public function index(Request $request)
    {
        if(\Auth::user()->can('manage leave'))
        {
            $leaves = Leave::all();
            
            // Get filter parameters
            $filters = [
                'employee_filter' => $request->get('employee_filter'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'sick_date_from' => $request->get('sick_date_from'),
                'sick_date_to' => $request->get('sick_date_to'),
                'search' => $request->get('search'),
            ];

            if(\Auth::user()->type == 'staff IT' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior accounting' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'intern' || \Auth::user()->type == 'support' ||  \Auth::user()->type == 'staff') 
            {
                $user = \Auth::user();
                $employee = Employee::where('user_id', '=', $user->id)->first();
                
                // Build query for absence_sick with filters
                $absence_sick_query = Leave::where('employee_id', '=', $employee->id)
                                        ->where('absence_type', '=', 'sick');
                
                // Apply filters
                $absence_sick_query = $this->applySickFilters($absence_sick_query, $filters);
                $absence_sick = $absence_sick_query->orderByDesc('id')->paginate(10);
                
                $approval = Leave::where('approval', '=', $user->id)
                            ->where('status','=', 'Pending')
                            ->orderByDesc('id')
                            ->paginate(10);

                // Get data for filter dropdowns (limited to current employee)
                $employees = collect([$employee]); // Only current employee
            }
            elseif(\Auth::user()->type == 'admin')
            {
                $employee = Employee::all();
                
                // Build query for absence_sick with filters
                $absence_sick_query = Leave::where('absence_type', '=', 'sick');
                $absence_sick_query = $this->applySickFilters($absence_sick_query, $filters);
                $absence_sick = $absence_sick_query->orderByDesc('id')->paginate(10);
                
                $users = \Auth::user();
                $approval = Leave::where('approval', '=', $users->id)
                            ->where('status','=', 'Pending')
                            ->orderByDesc('id')
                            ->paginate(10);

                // Get data for filter dropdowns
                $employees = Employee::all();
            }
            elseif(\Auth::user()->type == 'company')
            {
                $employee = Employee::all();
                
                // Build query for absence_sick with filters
                $absence_sick_query = Leave::where('absence_type', '=', 'sick');
                $absence_sick_query = $this->applySickFilters($absence_sick_query, $filters);
                $absence_sick = $absence_sick_query->orderByDesc('id')->paginate(10);
                
                $users = \Auth::user();  
                $approval = Leave::where('approval', '=', $users->id)
                            ->where('status','=', 'Pending')
                            ->orderByDesc('id')
                            ->paginate(10);

                // Get data for filter dropdowns
                $employees = Employee::all();
            }
            elseif(\Auth::user()->type == 'partners')
            {
                // Get employees based on branch
                if(\Auth::user()->employee->branch_id == 2)
                {
                    $employee = Employee::where('branch_id', 2)->get();
                }
                elseif(\Auth::user()->employee->branch_id == 3)
                {
                    $employee = Employee::where('branch_id', 3)->get();
                }
                else
                {
                    $employee = Employee::all();
                }
                
                $employee_ids = $employee->pluck('id');

                // Build query for absence_sick with filters
                $absence_sick_query = Leave::whereIn('employee_id', $employee_ids)
                                        ->where('absence_type', '=', 'sick');
                $absence_sick_query = $this->applySickFilters($absence_sick_query, $filters);
                $absence_sick = $absence_sick_query->orderByDesc('id')->paginate(10);
                
                $users = \Auth::user();  
                $approval = Leave::where('approval', '=', $users->id)
                            ->where('status','=', 'Pending')
                            ->orderByDesc('id')
                            ->paginate(10);

                // Get data for filter dropdowns (limited to branch employees)
                $employees = $employee;
            }
            else
            {
                $employee = Employee::where('created_by', '=', \Auth::user()->creatorId())->get();
                
                // Build query for absence_sick with filters
                $absence_sick_query = Leave::where('absence_type', '=', 'sick')
                                        ->where('created_by', '=', \Auth::user()->creatorId());
                $absence_sick_query = $this->applySickFilters($absence_sick_query, $filters);
                $absence_sick = $absence_sick_query->orderByDesc('id')->paginate(10);
                
                $approval = Leave::where('approval', '=', \Auth::user()->id)
                            ->where('status','=', 'Pending')
                            ->orderByDesc('id')
                            ->paginate(10);

                // Get data for filter dropdowns
                $employees = $employee;
            }

            // Append query parameters to pagination links
            $absence_sick->appends($request->query());

            return view('sick-letter.index', compact('absence_sick', 'employee', 'approval', 'employees'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create leave'))
        {
            if(\Auth::user()->type == 'staff IT' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior accounting' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'intern' || \Auth::user()->type == 'support' ||  \Auth::user()->type == 'staff')
            {
                $employees         = Employee::where('user_id', '=', \Auth::user()->id)->get()->pluck('name', 'id');
            }
            elseif(Auth::user()->type == 'admin')
            {
                $employees       = Employee::all()->pluck('name', 'id');
            }
            elseif(Auth::user()->type == 'company')
            {
                $employees       = Employee::all()->pluck('name', 'id');
            }
            else
            {
                $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            }

            return view('sick-letter.create', compact('employees'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create leave'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'total_sick_days' => $request->type == 'sick' ? 'required' : '',
                    'date_sick_letter' => $request->type == 'sick' ? 'required' : '',
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $leave = new Leave();

            if(\Auth::user()->type == "employee")
            {
                $leave->employee_id = $employee->id;
            }
            else
            {
                $leave->employee_id = $request->employee_id;
            }

            if(!empty($request->sick_letter))
            {
                $filenameWithExt = $request->file('sick_letter')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('sick_letter')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $dir = storage_path('uploads/sick_letter/' . \Auth::user()->name . '/');

                if(!file_exists($dir))
                {
                    mkdir($dir, 0777, true);
                }

                Storage::disk('minio')->put(
                    'uploads/sick_letter/' . \Auth::user()->name . '/' . $fileNameToStore,
                    file_get_contents($request->file('sick_letter'))
                );

                // $path = $request->file('sick_letter')->storeAs('uploads/sick_letter/' . \Auth::user()->name . '/', $fileNameToStore, 's3');
            }

            $leave->sick_letter = !empty($fileNameToStore) ? 'uploads/sick_letter/' . \Auth::user()->name . '/' . $fileNameToStore : '';
            $leave->total_sick_days = $request->total_sick_days;
            $leave->absence_type = 'sick';
            $leave->status = 'Pending';
            $leave->date_sick_letter = $request->date_sick_letter;
            $leave->created_by = \Auth::user()->creatorId();

            if($leave->absence_type == 'sick')
            {
                if($leave->sick_letter == NULL)
                {
                    $leave->leave_type_id = 1;
                }
                else
                {
                    $leave->leave_type_id = $request->leave_type_id;
                }
            }
            else
            {
                $leave->leave_type_id = $request->leave_type_id;
            }

            $leave->save();

            return redirect()->route('sick-letter.index')->with('success', __('Absence Request successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $leave)
    {

        $leave = Leave::find($leave);
        if(\Auth::user()->can('edit leave'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'approval' => $request->type == 'leave' ? 'required' : '',
                    'employee_id' => $request->type == 'leave' ? 'required' : '',
                    'leave_type_id' => $request->type == 'leave' ? 'required' : '',
                    'start_date' => $request->type == 'leave' ? 'required' : '',
                    'end_date' => $request->type == 'leave' ? 'required' : '',
                    'leave_reason' => $request->type == 'leave' ? 'required' : '',
                    'total_sick_days' => $request->type == 'sick' ? 'required' : '',
                    'date_sick_letter' => $request->type == 'sick' ? 'required' : '',
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();

            if(\Auth::user()->type == "employee")
            {
                $leave->employee_id = $employee->id;
            }
            else
            {
                $leave->employee_id = $request->employee_id;
            }

            if(!empty($request->sick_letter))
            {
                $filenameWithExt = $request->file('sick_letter')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('sick_letter')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $dir             = storage_path('uploads/sick_letter/' . \Auth::user()->name . '/');

                if(!file_exists($dir))
                {
                    mkdir($dir, 0777, true);
                }

                Storage::disk('minio')->put(
                    'uploads/sick_letter/' . \Auth::user()->name . '/' . $fileNameToStore,
                    file_get_contents($request->file('sick_letter'))
                );


                // $path = $request->file('sick_letter')->storeAs('uploads/sick_letter/' . \Auth::user()->name . '/', $fileNameToStore, 's3');
            }
            
            $leave->applied_on       = date('Y-m-d');
            $leave->approval         = !empty($request->approval) ? $request->approval : 0;
            $leave->start_date       = $request->start_date;
            $leave->end_date         = $request->end_date;
            $leave->total_leave_days = 0;
            $leave->leave_reason     = $request->leave_reason;
            $leave->sick_letter      = !empty('uploads/sick_letter/' .\Auth::user()->name . '/' . $request->sick_letter) ? 'uploads/sick_letter/' .\Auth::user()->name . '/' . $fileNameToStore : '';
            $leave->total_sick_days  = $request->total_sick_days;
            $leave->absence_type     = $request->type;
            $leave->date_sick_letter = $request->date_sick_letter;
            $leave->created_by       = \Auth::user()->creatorId();

            if($leave->absence_type  == 'sick')
            {
                if($leave->sick_letter == NULL)
                {
                    $leave->leave_type_id    = 1;
                }
                else
                {
                    $leave->leave_type_id    = $request->leave_type_id;
                }
            }
            else
            {
                $leave->leave_type_id    = $request->leave_type_id;
            }

            $leave->save();

            return redirect()->route('sick-letter.index')->with('success', __('Absence Request successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Leave $leave)
    {
        if(\Auth::user()->can('delete leave'))
        {
            if(Auth::user()->type !=='admin' || Auth::user()->type !=='company')
            {
                $leave->delete();

                return redirect()->route('sick-letter.index')->with('success', __('Leave successfully deleted.'));
            }
            elseif(\Auth::user()->type == 'admin')
            {
                $leave->delete();

                return redirect()->route('sick-letter.index')->with('success', __('Leave successfully deleted.'));
            }
            elseif(\Auth::user()->type == 'company')
            {
                $leave->delete();

                return redirect()->route('sick-letter.index')->with('success', __('Leave successfully deleted.'));
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

    public function action($id)
    {
        $leave     = Leave::find($id);
        $employee  = Employee::find($leave->employee_id);
        $leavetype = LeaveType::find($leave->leave_type_id);

        return view('sick-letter.action', compact('employee', 'leavetype', 'leave'));
    }

    public function changeaction(Request $request)
    {

        $leave = Leave::find($request->leave_id);

        $leave->status = $request->status;
        if($leave->status == 'Approval')
        {
            $startDate = new \DateTime($leave->start_date);
            $endDate = new \DateTime($leave->end_date);
            $total_leave_days = 0;

            while ($startDate <= $endDate) {
                if ($startDate->format('N') <= 5) { // Memeriksa apakah hari adalah Senin hingga Jumat
                    $total_leave_days++;
                }
                $startDate->add(new \DateInterval('P1D')); // Menambahkan 1 hari ke tanggal start_date
            }
            $leave->total_leave_days = $total_leave_days;
            $leave->status           = 'Approved';
        }
        else
        {
            $leave->status           = 'Reject';
        }

        $leave->save();

        if($leave->status == 'Approved')
        {
            //Email Notification
            $employee = Employee::where('id', $leave->employee_id)->first();
            $email = $employee->email;
            Mail::to($email)->send(new LeaveApprovalNotification($leave));
        }
        else
        {
            //Email Notification
            $employee = Employee::where('id', $leave->employee_id)->first();
            $email = $employee->email;
            Mail::to($email)->send(new LeaveRejectNotification($leave));
        }

        //Send Email
//         $setings = Utility::settings();
//         if($setings['leave_status'] == 1)
//         {

//             $employee     = Employee::where('id', $leave->employee_id)->where('created_by', '=', \Auth::user()->creatorId())->first();
//             $leave->name  = !empty($employee->name) ? $employee->name : '';
//             $leave->email = !empty($employee->email) ? $employee->email : '';
// //            dd($leave);

//             $actionArr = [

//                 'leave_name'=> $employee->name,
//                 'leave_status' => $leave->status,
//                 'leave_reason' =>  $leave->leave_reason,
//                 'leave_start_date' => $leave->start_date,
//                 'leave_end_date' => $leave->end_date,
//                 'total_leave_days' => $leave->total_leave_days,

//             ];
// //            dd($actionArr);
//             $resp = Utility::sendEmailTemplate('leave_action_send', [$employee->id => $employee->email], $actionArr);


//             return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.') .(($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));

//         }

        return redirect()->route('sick-letter.index')->with('success', __('Leave status successfully updated.'));
    }

    public function jsoncount(Request $request)
    {

        // $leave_counts = LeaveType::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave, leave_types.title, leave_types.days,leave_types.id'))
        //                          ->leftjoin('leaves', function ($join) use ($request){
        //     $join->on('leaves.leave_type_id', '=', 'leave_types.id');
        //     $join->where('leaves.employee_id', '=', $request->employee_id);
        // }
        // )->groupBy('leaves.leave_type_id')->get();

        $leave_counts = [];
        $leave_types = LeaveType::where('created_by', \Auth::user()->creatorId())->get();

        foreach ($leave_types as $type) {
            $counts = Leave::select(\DB::raw('
                COALESCE(SUM(
                    CASE 
                        WHEN sick_letter IS NULL THEN total_leave_days + total_sick_days
                        ELSE total_leave_days 
                    END
                ), 0) AS total_leave'))
                ->where('leave_type_id', $type->id)
                ->where('employee_id', $request->employee_id)
                ->whereYear('created_at', now()->year)
                ->groupBy('leaves.leave_type_id')
                ->first();

            // Menyimpan hasil ke dalam array
            $leave_count['total_leave'] = !empty($counts) ? $counts['total_leave'] : 0;
            $leave_count['title'] = $type->title;
            $leave_count['days'] = $type->days;
            $leave_count['id'] = $type->id;
            $leave_count['remaining_leave'] = $type->days - $leave_count['total_leave'];
            $leave_counts[] = $leave_count;
        }

        return $leave_counts;


    }

    public function getSickLetter(Request $request)
    {
        $absence_sick   = Leave::find($request->id);
        $images         = Leave::where('id',$request->id)->get();
        return view('sick-letter.images',compact('images','absence_sick'));
    }

    /**
     * Apply filters to the sick letter query
     */
    private function applySickFilters($query, $filters)
    {
        // Employee filter
        if (!empty($filters['employee_filter'])) {
            $query->where('employee_id', $filters['employee_filter']);
        }

        // Applied date range filter (if you have applied_on field for sick letters)
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('applied_on', [$filters['date_from'], $filters['date_to']]);
        } elseif (!empty($filters['date_from'])) {
            $query->where('applied_on', '>=', $filters['date_from']);
        } elseif (!empty($filters['date_to'])) {
            $query->where('applied_on', '<=', $filters['date_to']);
        }

        // Sick letter date range filter
        if (!empty($filters['sick_date_from']) && !empty($filters['sick_date_to'])) {
            $query->whereBetween('date_sick_letter', [$filters['sick_date_from'], $filters['sick_date_to']]);
        } elseif (!empty($filters['sick_date_from'])) {
            $query->where('date_sick_letter', '>=', $filters['sick_date_from']);
        } elseif (!empty($filters['sick_date_to'])) {
            $query->where('date_sick_letter', '<=', $filters['sick_date_to']);
        }

        // Search filter (search in employee name)
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->whereHas('employees', function($employeeQuery) use ($searchTerm) {
                $employeeQuery->where('name', 'LIKE', $searchTerm);
            });
        }

        return $query;
    }

}
