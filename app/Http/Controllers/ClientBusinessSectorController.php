<?php

namespace App\Http\Controllers;

use App\Models\ClientBusinessSector;
use App\Models\CustomField;
use Auth;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Session;

class ClientBusinessSectorController extends Controller
{
    public function index()
    {
        $user = \Auth::user();
        if(\Auth::user()->can('manage user'))
        {
            if(\Auth::user()->type = 'admin')
            {
                $businesssector = ClientBusinessSector::get();
            }
            elseif(\Auth::user()->type = 'company')
            {
                $businesssector = ClientBusinessSector::get();
            }
            else
            {
                $businesssector = ClientBusinessSector::where('created_by', '=', $user->creatorId())->get();
            }
            return view('businesssector.index', compact('businesssector'));
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
                $customFields   = CustomField::where('module', '=', 'businesssector')->get();
            }
            else
            {
                $customFields   = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'businesssector')->get();
            }
        
            return view('businesssector.create', compact('customFields'));
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
                $businesssector                                     = new ClientBusinessSector();
                $businesssector->name                               = $category[$i]['name'];
                $businesssector->client_business_sector_code        = $category[$i]['client_business_sector_code'];
                $businesssector->created_by                         = \Auth::user()->creatorId();
                $businesssector->save();
                CustomField::saveData($businesssector, $request->customField);
            }


            return redirect()->route('businesssector.index')->with('success', __('Business Sector successfully created.'));
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
            $businesssector           = ClientBusinessSector::find($id);

            if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {  
                $businesssector->customField = CustomField::getData($businesssector, 'businesssector');
                $customFields                = CustomField::where('module', '=', 'businesssector')->get();
            }
            else
            {
    
                $businesssector->customField  = CustomField::getData($businesssector, 'businesssector');
                $customFields                 = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'businesssector')->get();
            }


            return view('businesssector.edit', compact('businesssector','customFields'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, ClientBusinessSector $businesssector)
    {
        if(\Auth::user()->can('edit user'))
        {
            if($businesssector->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required',
                    ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('businesssector.index')->with('error', $messages->first());
                }
                $businesssector->name                             = $request->name;
                $businesssector->client_business_sector_code      = $request->client_business_sector_code;
                $businesssector->save();

                CustomField::saveData($businesssector, $request->customField);

                return redirect()->route('businesssector.index')->with('success', __('Business Sector successfully updated.'));
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

                    return redirect()->route('businesssector.index')->with('error', $messages->first());
                }
                $businesssector->name                             = $request->name;
                $businesssector->client_business_sector_code      = $request->client_business_sector_code;
                $businesssector->save();
                CustomField::saveData($businesssector, $request->customField);

                return redirect()->route('businesssector.index')->with('success', __('Business Sector successfully updated.'));
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
            $businesssector = ClientBusinessSector::find($id);
            $businesssector->delete();
            return redirect()->route('businesssector.index')->with('success', __('Business Sector successfully deleted .'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
}
