<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Auth;
use App\Models\CustomField;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Session;

class OfficeController extends Controller
{
    public function index()
    {
        $user = \Auth::user();
        if(\Auth::user()->can('manage user'))
        {
            if(\Auth::user()->type = 'admin')
            {
                $office = Office::get();
            }
            elseif(\Auth::user()->type = 'company')
            {
                $office = Office::get();
            }
            else
            {
                $office = Office::where('created_by', '=', $user->creatorId())->get();
            }
            return view('office.index', compact('office'));
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
                $customFields   = CustomField::where('module', '=', 'office')->get();
                $servicetype = ServiceType::get()->pluck('name', 'id');
                $servicetype->prepend('Select Service Type', '');
            }
            else
            {
                $customFields   = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'office')->get();
                $servicetype = ServiceType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $servicetype->prepend('Select Service Type', '');
            }
        
            return view('office.create', compact('servicetype', 'customFields'));
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
                $office                     = new Office();
                $office->name               = $category[$i]['name'];
                $office->service_type_id    = $request->service_type_id;
                $office->created_by         = \Auth::user()->creatorId();
                $office->save();
                CustomField::saveData($office, $request->customField);
            }


            return redirect()->route('office.index')->with('success', __('Office successfully created.'));
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
            $id           = Crypt::decrypt($ids);
            $office       = Office::find($id);

            if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {  
                $office->customField = CustomField::getData($office, 'office');
                $servicetype = ServiceType::get()->pluck('name', 'id');
                $servicetype->prepend('Select Service Type', '');
                $customFields         = CustomField::where('module', '=', 'office')->get();
            }
            else
            {
    
                $office->customField  = CustomField::getData($office, 'office');
                $servicetype = ServiceType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $servicetype->prepend('Select Service Type', '');
                $customFields         = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'office')->get();
            }


            return view('office.edit', compact('office','servicetype','customFields'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Office $office)
    {
        if(\Auth::user()->can('edit user'))
        {
            if($office->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required',
                    ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('office.index')->with('error', $messages->first());
                }
                $office->name      = $request->name;
                $office->service_type_id      = $request->service_type_id;
                $office->save();

                CustomField::saveData($office, $request->customField);

                return redirect()->route('office.index')->with('success', __('Office successfully updated.'));
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

                    return redirect()->route('office.index')->with('error', $messages->first());
                }
                $office->name      = $request->name;
                $office->service_type_id      = $request->service_type_id;
                $tasktemplate->save();
                CustomField::saveData($invoice, $request->customField);

                return redirect()->route('office.index')->with('success', __('Office successfully updated.'));
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
            $office = Office::find($id);
            $office->delete();
            return redirect()->route('office.index')->with('success', __('Office successfully deleted .'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
}
