<?php

namespace App\Http\Controllers;

use App\Models\LabelProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ProjectServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $serviceTypes = LabelProject::orderBy('sort_order')->orderBy('name')->get();
        return view('project-service.index', compact('serviceTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return view('project-service.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:label_projects,code',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
        ]);

        if($validator->fails()) {
            return redirect()->back()->with('error', \App\Models\Utility::errorFormat($validator->getMessageBag()));
        }

        LabelProject::create([
            'name' => $request->name,
            'code' => $request->code ?: Str::slug($request->name, '_'),
            'description' => $request->description,
            'sort_order' => $request->sort_order ?: 0,
            'is_active' => $request->has('is_active') ? true : false,
            'created_by' => \Auth::user()->creatorId(),
        ]);

        return redirect()->route('project-service.index')->with('success', __('Label Projects created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($ids)
    {

        $id                    = Crypt::decrypt($ids);
        $serviceType           = LabelProject::find($id);
        

        return view('project-service.edit', compact('serviceType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LabelProject $serviceType)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:label_projects,code,' . $serviceType->id,
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
        ]);

        if($validator->fails()) {
            return redirect()->back()->with('error', \App\Models\Utility::errorFormat($validator->getMessageBag()));
        }

        $serviceType->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?: 0,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('project-service.index')->with('success', __('Project Service updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LabelProject $serviceType)
    {
        $serviceType->delete();
        return redirect()->route('project-service.index')->with('success', __('Project Service deleted successfully.'));
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(LabelProject $serviceType)
    {
        $serviceType->update([
            'is_active' => !$serviceType->is_active
        ]);

        $status = $serviceType->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', __('Service Type ' . $status . ' successfully.'));
    }
}