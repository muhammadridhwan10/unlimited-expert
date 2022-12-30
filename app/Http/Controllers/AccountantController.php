<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\Employee;
use App\Models\Mail\UserCreate;
use App\Models\PublicAccountant;
use App\Models\Office;
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

class AccountantController extends Controller
{
    public function index()
    {
        $user = \Auth::user();
        if(\Auth::user()->can('manage user'))
        {
            if(\Auth::user()->type = 'admin')
            {
                $accountant = PublicAccountant::get();
            }
            elseif(\Auth::user()->type = 'company')
            {
                $accountant = PublicAccountant::get();
            }
            else
            {
                $accountant = PublicAccountant::where('created_by', '=', $user->creatorId())->get();
            }
            return view('accountant.index', compact('accountant'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function create()
    {

        if(\Auth::user()->can('create user'))
        {
            if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $customFields   = CustomField::where('module', '=', 'accountant')->get();
                $office = Office::get()->pluck('name', 'id');
                $office->prepend('Select Office', '');
            }
            else
            {
                $customFields   = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'office')->get();
                $office = Office::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $office->prepend('Select Office', '');
            }
        
            return view('accountant.create', compact('office', 'customFields'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create user'))
        {
            // $validator = \Validator::make(
            //     $request->all(), [
            //                     'category_id' => 'required',
            //                     'estimated_hrs' => 'required',
            //                     'start_date' => 'required',
            //                     'end_date' => 'required',
            //                 ]
            // );
            // if($validator->fails())
            // {
            //     $messages = $validator->getMessageBag();

            //     return redirect()->back()->with('error', $messages->first());
            // }


            $category = $request->items;

            for($i = 0; $i < count($category); $i++)
            {
                $accountant                                     = new PublicAccountant();
                $accountant->name                               = $category[$i]['name'];
                $accountant->client_public_accountant_code      = $category[$i]['client_public_accountant_code'];
                $accountant->office_id                          = $request->office_id;
                $accountant->created_by                         = \Auth::user()->creatorId();
                $accountant->save();
                CustomField::saveData($accountant, $request->customField);
            }


            return redirect()->route('accountant.index')->with('success', __('Accountant successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($ids)
    {
        if(\Auth::user()->can('edit user'))
        {
            $id               = Crypt::decrypt($ids);
            $accountant       = PublicAccountant::find($id);

            if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {  
                $accountant->customField = CustomField::getData($accountant, 'accountant');
                $office = Office::get()->pluck('name', 'id');
                $office->prepend('Select Office', '');
                $customFields         = CustomField::where('module', '=', 'accountant')->get();
            }
            else
            {
    
                $accountant->customField  = CustomField::getData($accountant, 'accountant');
                $office = Office::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $office->prepend('Select Office', '');
                $customFields         = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'accountant')->get();
            }


            return view('accountant.edit', compact('office','accountant','customFields'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, PublicAccountant $accountant)
    {
        if(\Auth::user()->can('edit user'))
        {
            if($accountant->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required',
                    ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('accountant.index')->with('error', $messages->first());
                }
                $accountant->name      = $request->name;
                $accountant->client_public_accountant_code      = $request->client_public_accountant_code;
                $accountant->office_id      = $request->office_id;
                $accountant->save();

                CustomField::saveData($accountant, $request->customField);

                return redirect()->route('accountant.index')->with('success', __('Accountant successfully updated.'));
            }
            elseif(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required',
                    ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('accountant.index')->with('error', $messages->first());
                }
                $accountant->name      = $request->name;
                $accountant->client_public_accountant_code      = $request->client_public_accountant_code;
                $accountant->office_id      = $request->office_id;
                $accountant->save();
                CustomField::saveData($invoice, $request->customField);

                return redirect()->route('accountant.index')->with('success', __('Accountant successfully updated.'));
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

    public function destroy($id)
    {

        if(\Auth::user()->can('delete user'))
        {
            $accountant = PublicAccountant::find($id);
            $accountant->delete();
            return redirect()->route('accountant.index')->with('success', __('Accountant successfully deleted .'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
}
