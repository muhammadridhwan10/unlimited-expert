<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\ClientAccountingStandard;
use App\Models\ClientOwnershipStatus;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Session;

class ClientOwnershipStatusController extends Controller
{
    public function index()
    {
        $user = \Auth::user();
        if(\Auth::user()->can('manage user'))
        {
            if(\Auth::user()->type = 'admin')
            {
                $ownershipstatus = ClientOwnershipStatus::get();
            }
            elseif(\Auth::user()->type = 'company')
            {
                $ownershipstatus = ClientOwnershipStatus::get();
            }
            else
            {
                $ownershipstatus = ClientOwnershipStatus::where('created_by', '=', $user->creatorId())->get();
            }
            return view('ownershipstatus.index', compact('ownershipstatus'));
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
                $customFields   = CustomField::where('module', '=', 'ownershipstatus')->get();
            }
            else
            {
                $customFields   = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'ownershipstatus')->get();
            }
        
            return view('ownershipstatus.create', compact('customFields'));
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
                $ownershipstatus                                     = new ClientOwnershipStatus();
                $ownershipstatus->name                               = $category[$i]['name'];
                $ownershipstatus->created_by                         = \Auth::user()->creatorId();
                $ownershipstatus->save();
                CustomField::saveData($ownershipstatus, $request->customField);
            }


            return redirect()->route('ownershipstatus.index')->with('success', __('Ownership Status successfully created.'));
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
            $ownershipstatus          = ClientOwnershipStatus::find($id);

            if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {  
                $ownershipstatus->customField = CustomField::getData($ownershipstatus, 'ownershipstatus');
                $customFields         = CustomField::where('module', '=', 'ownershipstatus')->get();
            }
            else
            {
    
                $ownershipstatus->customField  = CustomField::getData($ownershipstatus, 'ownershipstatus');
                $customFields         = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'ownershipstatus')->get();
            }


            return view('ownershipstatus.edit', compact('ownershipstatus','customFields'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, ClientOwnershipStatus $ownershipstatus)
    {
        if(\Auth::user()->can('edit user'))
        {
            if($ownershipstatus->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required',
                    ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('ownershipstatus.index')->with('error', $messages->first());
                }
                $ownershipstatus->name      = $request->name;
                $ownershipstatus->save();

                CustomField::saveData($ownershipstatus, $request->customField);

                return redirect()->route('ownershipstatus.index')->with('success', __('Ownership Status successfully updated.'));
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

                    return redirect()->route('ownershipstatus.index')->with('error', $messages->first());
                }
                $ownershipstatus->name      = $request->name;
                $ownershipstatus->save();
                CustomField::saveData($invoice, $request->customField);

                return redirect()->route('ownershipstatus.index')->with('success', __('Ownership Status successfully updated.'));
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
            $ownershipstatus = ClientOwnershipStatus::find($id);
            $ownershipstatus->delete();
            return redirect()->route('ownershipstatus.index')->with('success', __('Ownership Status successfully deleted .'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
}
