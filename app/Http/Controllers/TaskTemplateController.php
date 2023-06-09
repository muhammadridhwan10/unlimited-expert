<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use App\Models\ProjectTaskTemplate;
use App\Models\CategoryTemplate;
use App\Models\CustomField;
use Illuminate\Support\Facades\Crypt;
use App\Models\ProductServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TaskTemplateController extends Controller
{
    public function index(Request $request)
    {

        if(\Auth::user()->can('manage project task template'))
        {

            $user = Auth::user();
            if($user->type == 'admin')
            {
                $category = ProductServiceCategory::all()->pluck('name', 'id');
                $category_template = CategoryTemplate::get()->pluck('name', 'id');
                $category_template->prepend('Select Category Template', '');
                $category->prepend('Select Category', '');
    
                // $status = Invoice::$statues;
    
                $query = ProjectTaskTemplate::all();
    
                if(!empty($request->category))
                {
                    $query->where('category_id', '=', $request->category);
                }
                // if(!empty($request->issue_date))
                // {
                //     $date_range = explode(' - ', $request->issue_date);
                //     $query->where('issue_date', $date_range);
                // }
    
                // if(!empty($request->status))
                // {
                //     $query->where('status', '=', $request->status);
                // }
                $templates = $query;
            }
            elseif($user->type == 'company')
            {
                $category = ProductServiceCategory::all()->pluck('name', 'id');
                $category_template = CategoryTemplate::get()->pluck('name', 'id');
                $category_template->prepend('Select Category Template', '');
                $category->prepend('Select Category', '');
    
                // $status = Invoice::$statues;
    
                $query = ProjectTaskTemplate::all();
    
                if(!empty($request->category))
                {
                    $query->where('category_id', '=', $request->category);
                }
                // if(!empty($request->issue_date))
                // {
                //     $date_range = explode(' - ', $request->issue_date);
                //     $query->where('issue_date', $date_range);
                // }
    
                // if(!empty($request->status))
                // {
                //     $query->where('status', '=', $request->status);
                // }
                $templates = $query;
            }
            else
            {
                $category = ProductServiceCategory::all()->pluck('name', 'id');
                $category_template = CategoryTemplate::get()->pluck('name', 'id');
                $category_template->prepend('Select Category Template', '');
                $category->prepend('Select Category', '');
    
                // $status = Invoice::$statues;
    
                $query = ProjectTaskTemplate::where('created_by', '=', \Auth::user()->creatorId());
    
                if(!empty($request->category))
                {
                    $query->where('category_id', '=', $request->category);
                }
                // if(!empty($request->issue_date))
                // {
                //     $date_range = explode(' - ', $request->issue_date);
                //     $query->where('issue_date', $date_range);
                // }
    
                // if(!empty($request->status))
                // {
                //     $query->where('status', '=', $request->status);
                // }
                $templates = $query->get();
            }

            return view('tasktemplate.index', compact('templates', 'category', 'category_template'));
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
                $customFields   = CustomField::where('module', '=', 'tasktemplate')->get();
                $categorys = ProductServiceCategory::where('type', 0)->get()->pluck('name', 'id');
                $category_template = CategoryTemplate::get()->pluck('name', 'id');
                $category_template->prepend('Select Category Template', '');
                $categorys->prepend('Select Category', '');
            }
            else
            {
                $customFields   = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
                $categorys = ProductServiceCategory::where('type', 0)->get()->pluck('name', 'id');
                $category_template = CategoryTemplate::get()->pluck('name', 'id');
                $category_template->prepend('Select Category Template', '');
                $categorys->prepend('Select Category', '');
            }
        
            return view('tasktemplate.create', compact('categorys', 'customFields', 'category_template'));
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
            $category_id = $request->category_id;
            $category_template_id = $request->category_template_id;


            for($i = 0; $i < count($category); $i++)
            {
                $task_template                 = new ProjectTaskTemplate();
                $task_template->stage_id       = 1;
                $task_template->name           = $category[$i]['name'];
                $task_template->category_id    = $category_id;
                $task_template->category_template_id      = $category_template_id;
                // $task_template->start_date     = $category[$i]['start_date'];
                // $task_template->end_date       = $category[$i]['end_date'];
                $task_template->estimated_hrs  = $category[$i]['estimated_hrs'];
                $task_template->description    = $category[$i]['description'];
                $task_template->created_by     = \Auth::user()->creatorId();
                $task_template->save();
                CustomField::saveData($task_template, $request->customField);
            }


            return redirect()->route('tasktemplate.index')->with('success', __('Task Template successfully created.'));
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
            $tasktemplate = ProjectTaskTemplate::find($id);

            if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'partners')
            {
                $category       = ProductServiceCategory::where('type', 0)->get()->pluck('name', 'id');
                $category_template = CategoryTemplate::get()->pluck('name', 'id');
                $category_template->prepend('Select Category Template', '');
                $category->prepend('Select Category', '');
    
                $tasktemplate->customField = CustomField::getData($tasktemplate, 'tasktemplate');
                $customFields         = CustomField::where('module', '=', 'tasktemplate')->get();
            }
            else
            {
                $category       = ProductServiceCategory::where('type', 0)->get()->pluck('name', 'id');
                $category_template = CategoryTemplate::get()->pluck('name', 'id');
                $category_template->prepend('Select Category Template', '');
                $category->prepend('Select Category', '');
    
                $tasktemplate->customField = CustomField::getData($tasktemplate, 'tasktemplate');
                $customFields         = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'tasktemplate')->get();
            }


            return view('tasktemplate.edit', compact('tasktemplate','category', 'customFields', 'category_template'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, ProjectTaskTemplate $tasktemplate)
    {
        if(\Auth::user()->can('edit project task template'))
        {
            if($tasktemplate->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                        'category_id' => 'required',
                        'estimated_hrs' => 'required',
                    ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('tasktemplate.index')->with('error', $messages->first());
                }
                $tasktemplate->category_id      = $request->category_id;
                $tasktemplate->category_template_id      = $request->category_template_id;
                $tasktemplate->name             = $request->name;
                // $tasktemplate->start_date       = $request->start_date;
                // $tasktemplate->end_date         = $request->end_date;
                $tasktemplate->estimated_hrs    = $request->estimated_hrs;
                $tasktemplate->description      = $request->description;
                $tasktemplate->save();

                CustomField::saveData($tasktemplate, $request->customField);

                return redirect()->route('tasktemplate.index')->with('success', __('Task Template successfully updated.'));
            }
            elseif(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $validator = \Validator::make(
                    $request->all(), [
                        'category_id' => 'required',
                        'estimated_hrs' => 'required',
                    ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('tasktemplate.index')->with('error', $messages->first());
                }
                $tasktemplate->category_id      = $request->category_id;
                $tasktemplate->category_template_id      = $request->category_template_id;
                $tasktemplate->name             = $request->name;
                // $tasktemplate->start_date       = $request->start_date;
                // $tasktemplate->end_date         = $request->end_date;
                $tasktemplate->estimated_hrs    = $request->estimated_hrs;
                $tasktemplate->description      = $request->description;
                $tasktemplate->save();
                CustomField::saveData($invoice, $request->customField);

                return redirect()->route('tasktemplate.index')->with('success', __('Task Template successfully updated.'));
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

    public function destroy(ProjectTaskTemplate $tasktemplate,Request $request)
    {

        if(\Auth::user()->can('delete project task template'))
        {
            if($tasktemplate->created_by == \Auth::user()->creatorId())
            {
                $tasktemplate->delete();

                return redirect()->route('tasktemplate.index')->with('success', __('Task Template successfully deleted.'));
            }
            elseif(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $tasktemplate->delete();

                return redirect()->route('tasktemplate.index')->with('success', __('Task Template successfully deleted.'));
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
}
