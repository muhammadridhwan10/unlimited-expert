<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\ClientAccountingStandard;
use App\Models\Office;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Session;

class ClientAccountingStandardController extends Controller
{
    public function index()
    {
        $user = \Auth::user();
        if(\Auth::user()->can('manage user'))
        {
            if(\Auth::user()->type = 'admin')
            {
                $accountingstandard = ClientAccountingStandard::get();
            }
            elseif(\Auth::user()->type = 'company')
            {
                $accountingstandard = ClientAccountingStandard::get();
            }
            else
            {
                $accountingstandard = ClientAccountingStandard::where('created_by', '=', $user->creatorId())->get();
            }
            return view('accountingstandard.index', compact('accountingstandard'));
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
                $customFields   = CustomField::where('module', '=', 'accountingstandard')->get();
            }
            else
            {
                $customFields   = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'accountingstandard')->get();
            }
        
            return view('accountingstandard.create', compact('customFields'));
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
                $accountingstandard                                     = new ClientAccountingStandard();
                $accountingstandard->name                               = $category[$i]['name'];
                $accountingstandard->created_by                         = \Auth::user()->creatorId();
                $accountingstandard->save();
                CustomField::saveData($accountingstandard, $request->customField);
            }


            return redirect()->route('accountingstandard.index')->with('success', __('Accounting Standard successfully created.'));
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
            $id                       = Crypt::decrypt($ids);
            $accountingstandard       = ClientAccountingStandard::find($id);

            if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {  
                $accountingstandard->customField = CustomField::getData($accountingstandard, 'accountingstandard');
                $customFields         = CustomField::where('module', '=', 'accountingstandard')->get();
            }
            else
            {
    
                $accountingstandard->customField  = CustomField::getData($accountingstandard, 'accountingstandard');
                $customFields         = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'accountingstandard')->get();
            }


            return view('accountingstandard.edit', compact('accountingstandard','customFields'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, ClientAccountingStandard $accountingstandard)
    {
        if(\Auth::user()->can('edit user'))
        {
            if($accountingstandard->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required',
                    ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('accountingstandard.index')->with('error', $messages->first());
                }
                $accountingstandard->name      = $request->name;
                $accountingstandard->save();

                CustomField::saveData($accountingstandard, $request->customField);

                return redirect()->route('accountingstandard.index')->with('success', __('Accounting Standard successfully updated.'));
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

                    return redirect()->route('accountingstandard.index')->with('error', $messages->first());
                }
                $accountingstandard->name      = $request->name;
                $accountingstandard->save();
                CustomField::saveData($invoice, $request->customField);

                return redirect()->route('accountingstandard.index')->with('success', __('Accounting Standard successfully updated.'));
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
            $accountingstandard = ClientAccountingStandard::find($id);
            $accountingstandard->delete();
            return redirect()->route('accountingstandard.index')->with('success', __('Accounting Standard successfully deleted .'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
}
