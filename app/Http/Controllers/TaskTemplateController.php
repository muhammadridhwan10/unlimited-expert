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
            $category = ProductServiceCategory::all()->pluck('name', 'id');
            $category_template = CategoryTemplate::get()->pluck('name', 'id');
            $category_template->prepend('Select Category Template', '');
            $category->prepend('All', '');

            $query = ProjectTaskTemplate::orderByDesc('id')->get();

            if(!empty($request->category))
            {
                $templates = $query->where('category_id', '=', $request->category)->orderByDesc('id');
            }
            elseif($request->category = 'All')
            {
                $templates = ProjectTaskTemplate::orderByDesc('id')->get();
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

            $customFields   = CustomField::where('module', '=', 'tasktemplate')->get();
            $category       = CategoryTemplate::get()->pluck('name', 'id');
            $category->prepend('Select Category', '');
            
            return view('tasktemplate.create', compact('category', 'customFields'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        if (!\Auth::user()->can('create project task template')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

 
        $request->validate([
            'category_id' => 'required', 
            'tasks' => 'required|array|min:1',
            'tasks.*.name' => 'required|string|max:255',
            'tasks.*.estimated_hrs' => 'required|numeric|min:0',
            'tasks.*.description' => 'nullable|string',
        ]);


        $categoryId = $request->input('category_id');

    
        foreach ($request->input('tasks') as $taskData) {
            $taskTemplate = new ProjectTaskTemplate();
            $taskTemplate->stage_id = 1;
            $taskTemplate->name = $taskData['name'];
            $taskTemplate->category_id = $categoryId;
            $taskTemplate->estimated_hrs = $taskData['estimated_hrs'];
            $taskTemplate->description = $taskData['description'] ?? null; 
            $taskTemplate->created_by = \Auth::user()->creatorId();
            $taskTemplate->save();
        }

        return redirect()->route('tasktemplate.index')->with('success', __('Task Template successfully created.'));
    }

    public function edit($ids)
    {
        if(\Auth::user()->can('edit project task template'))
        {
            $id           = Crypt::decrypt($ids);
            $tasktemplate = ProjectTaskTemplate::find($id);

            $category       = CategoryTemplate::get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            $tasktemplate->customField = CustomField::getData($tasktemplate, 'tasktemplate');
            $customFields         = CustomField::where('module', '=', 'tasktemplate')->get();
    

            return view('tasktemplate.edit', compact('tasktemplate','category', 'customFields',));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, ProjectTaskTemplate $tasktemplate)
    {

        if (!\Auth::user()->can('edit project task template')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'category_id' => 'required',
            'name' => 'required|string|max:255',
            'estimated_hrs' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $tasktemplate->category_id = $request->input('category_id');
        $tasktemplate->name = $request->input('name');
        $tasktemplate->estimated_hrs = $request->input('estimated_hrs');
        $tasktemplate->description = $request->input('description');
        $tasktemplate->save();

        return redirect()->route('tasktemplate.index')->with('success', __('Task Template successfully updated.'));
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
