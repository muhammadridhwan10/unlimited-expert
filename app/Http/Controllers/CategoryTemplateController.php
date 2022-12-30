<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use App\Models\CategoryTemplate;
use App\Models\CustomField;
use Illuminate\Support\Facades\Crypt;
use App\Models\ProductServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CategoryTemplateController extends Controller
{
    public function index(Request $request)
    {

        if(\Auth::user()->can('manage project task template'))
        {

            $user = Auth::user();
            if($user->type == 'admin')
            {
                $category_template = CategoryTemplate::all();
            }
            elseif($user->type == 'company')
            {
                $category_template = CategoryTemplate::all();
            }
            else
            {
                $category_template = CategoryTemplate::where('created_by', '=', \Auth::user()->creatorId())->orderBy('order','asc')->get();
            }

            return view('categorytemplate.index',compact('category_template'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

/**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function storingValue(Request $request)
    {
        if(\Auth::user()->can('create project task template'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'name' => 'required|max:255',
                            ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $arrStages       = CategoryTemplate::orderBy('order')->pluck('name', 'id')->all();
            $order           = CategoryTemplate::where('created_by',\Auth::user()->ownerId())->get()->count();
            $obj             = new CategoryTemplate();
            $obj->name       = $request->name;
            $obj->order      = $order+1;
            $obj->created_by = \Auth::user()->creatorId();
            $obj->save();
            return redirect()->route('categorytemplate.index')->with('success', __('Category Template Added Successfully'));
        }
    }
    public function create()
    {
        if(\Auth::user()->can('create project task template'))
        {
            return view('categorytemplate.create');
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {

        if(\Auth::user()->can('create project task template'))
        {
            $rules = [
                'stages' => 'required|present|array',
            ];

            $attributes = [];

            if($request->stages)
            {
                foreach($request->stages as $key => $val)
                {
                    $rules['stages.' . $key . '.name']      = 'required|max:255';
                    $attributes['stages.' . $key . '.name'] = __('Stage Name');
                }
            }

            $validator = Validator::make($request->all(), $rules, [], $attributes);
            if($validator->fails())
            {
                return redirect()->back()->with('errors', Utility::errorFormat($validator->getMessageBag()));
            }
            $arrStages = CategoryTemplate::orderBy('order')->pluck('name', 'id')->all();
            $order=0;

            foreach($request->stages as $key => $stage)
            {
                $obj = new CategoryTemplate();
                if(isset($stage['id']) && !empty($stage['id']))
                {
                    $obj = CategoryTemplate::find($stage['id']);
                    unset($arrStages[$obj->id]);
                }
                $obj->name       = $stage['name'];
                $obj->order      = $order++;
                $obj->created_by = \Auth::user()->creatorId();
                $obj->save();
            }

            if($arrStages)
            {
                foreach($arrStages as $id => $name)
                {
                    CategoryTemplate::find($id)->delete();
                }
            }
            return redirect()->route('categorytemplate.index')->with('success', __('Category Template Add Successfully'));
        }

        else
        {
            return redirect()->back()->with('errors', __('Permission Denied.'));
        }
    }

    /**
    * Display the specified resource.
    *
    * @param  \App\TaskStage  $taskStage
    * @return \Illuminate\Http\Response
    */
    public function show(CategoryTemplate $taskStage)
    {
        //
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  \App\CategoryTemplate  $category_template
    * @return \Illuminate\Http\Response
    */
    public function edit(CategoryTemplate $category_template,$id)
    {
        $category_template = CategoryTemplate::findOrfail($id);
        if($category_template->created_by == \Auth::user()->creatorId())
        {
            return view('categorytemplate.edit', compact('category_template'));
        }
        elseif(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
        {
        return view('categorytemplate.edit', compact('category_template'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\CategoryTemplate  $category_template
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, CategoryTemplate $category_template,$id)
    {
        $category_template = CategoryTemplate::findOrfail($id);
        if($category_template->created_by == \Auth::user()->creatorId())
        {
            $validator = \Validator::make(
                $request->all(), [
                                    'name' => 'required|max:244',
                                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('categorytemplate.index')->with('error', $messages->first());
            }

            $category_template->name = $request->name;
            $category_template->save();

            return redirect()->route('categorytemplate.index')->with('success', __('Category Template successfully updated.'));
        }
        elseif(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
        {
        $validator = \Validator::make(
            $request->all(), [
                                'name' => 'required|max:244',
                            ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->route('categorytemplate.index')->with('error', $messages->first());
        }

        $category_template->name = $request->name;
        $category_template->save();

        return redirect()->route('categorytemplate.index')->with('success', __('Category Template successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  \App\CategoryTemplate  $taskStage
    * @return \Illuminate\Http\Response
    */
    public function destroy(CategoryTemplate $category_template,$id)
    {

        if(\Auth::user()->can('delete project task template'))
        {
            $category_template = CategoryTemplate::find($id);
            $category_template->delete();
            return redirect()->back()->with('success', __('Task Stage Successfully Deleted.'));
        }

        else
        {
            return redirect()->back()->with('errors', __('Permission Denied.'));
        }
    }
    public function order(Request $request)
    {
        $post = $request->all();
        foreach($post['order'] as $key => $item)
        {
            $status        = CategoryTemplate::where('id', '=', $item)->first();
            $status->order = $key;
            $status->save();
        }
    }
}
