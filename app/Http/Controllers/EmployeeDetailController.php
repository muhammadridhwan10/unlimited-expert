<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\Employee;
use App\Models\Training;
use App\Models\Mail\UserCreate;
use App\Models\User;
use App\Models\ProjectUser;
use App\Models\ProjectTask;
use App\Models\UserCompany;
use App\Models\LoginDetail;
use App\Models\Branch;
use Auth;
use File;
use App\Models\Utility;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserToDo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Session;
use Spatie\Permission\Models\Role;

class EmployeeDetailController extends Controller
{
    public function index()
    {
        $user = \Auth::user();
        if(\Auth::user()->can('manage user'))
        {
            if(\Auth::user()->type = 'admin')
            {
                $users = User::where('type', '!=', 'client')->get();
            }
            elseif(\Auth::user()->type = 'company')
            {
                $users = User::where('type', '!=', 'client')->get();
            }
            else
            {
                $users = User::where('created_by', '=', $user->creatorId())->where('type', '!=', 'client')->get();
            }
            return view('employee-detail.index', compact('users'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

    }

    public function show(User $employee_detail)
    {
        if(\Auth::user()->can('manage user'))
        {
            
            $filter = request()->input('filter');

            return view('employee-detail.show', compact('employee_detail', 'filter'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
