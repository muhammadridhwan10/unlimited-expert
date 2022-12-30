<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use App\Models\CustomField;
use Auth;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Session;

class ServiceTypeController extends Controller
{
    public function index(Request $request)
    {

        if(\Auth::user()->can('manage project task template'))
        {

            if(\Auth::user()->type = 'admin')
            {
    
                $query = ServiceType::all();
    
                if(!empty($request->category))
                {
                    $query->where('category_id', '=', $request->category);
                }

                $servicetype = $query;
    
                return view('servicetype.index', compact('servicetype'));
            }
            elseif(\Auth::user()->type = 'company')
            {  
                $query = ServiceType::all();
    
                if(!empty($request->category))
                {
                    $query->where('category_id', '=', $request->category);
                }
                $servicetype = $query;
    
                return view('servicetype.index', compact('servicetype'));
            }
            else
            {
                $query = ServiceType::where('created_by', '=', \Auth::user()->creatorId());
    
                if(!empty($request->category))
                {
                    $query->where('category_id', '=', $request->category);
                }
                $servicetype = $query->get();
    
                return view('servicetype.index', compact('servicetype'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create()
    {

        if(\Auth::user()->can('create project task template'))
        {
            if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $customFields   = CustomField::where('module', '=', 'servicetype')->get();
            }
            else
            {
                $customFields   = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'servicetype')->get();
            }
        
            return view('servicetype.create', compact('customFields'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        if(\Auth::user()->can('create project task template'))
        {
            $category = $request->items;
            for($i = 0; $i < count($category); $i++)
            {
                $servicetype                 = new ServiceType();
                $servicetype->name           = $category[$i]['name'];
                $servicetype->service_type_code  = $category[$i]['service_type_code'];
                $servicetype->created_by     = \Auth::user()->creatorId();
                $servicetype->save();
                CustomField::saveData($servicetype, $request->customField);
            }


            return redirect()->route('servicetype.index')->with('success', __('Service Type successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($ids)
    {
        if(\Auth::user()->can('edit project task template'))
        {
            $id           = Crypt::decrypt($ids);
            $servicetype  = ServiceType::find($id);

            if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
    
                $servicetype->customField = CustomField::getData($servicetype, 'servicetype');
                $customFields         = CustomField::where('module', '=', 'servicetype')->get();
            }
            else
            {
    
                $servicetype->customField = CustomField::getData($servicetype, 'servicetype');
                $customFields         = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'servicetype')->get();
            }


            return view('servicetype.edit', compact('servicetype','customFields'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, ServiceType $servicetype)
    {
        if(\Auth::user()->can('edit user'))
        {
            if($servicetype->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required',
                    ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('servicetype.index')->with('error', $messages->first());
                }
                $servicetype->name      = $request->name;
                $servicetype->service_type_code      = $request->service_type_code;
                $servicetype->save();

                CustomField::saveData($servicetype, $request->customField);

                return redirect()->route('servicetype.index')->with('success', __('Service Type successfully updated.'));
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

                    return redirect()->route('servicetype.index')->with('error', $messages->first());
                }
                $servicetype->name      = $request->name;
                $servicetype->service_type_code      = $request->service_type_code;
                $servicetype->save();
                CustomField::saveData($invoice, $request->customField);

                return redirect()->route('servicetype.index')->with('success', __('Service Type successfully updated.'));
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
            $servicetype = ServiceType::find($id);
            $servicetype->delete();
            return redirect()->route('servicetype.index')->with('success', __('Service Type successfully deleted .'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
}
