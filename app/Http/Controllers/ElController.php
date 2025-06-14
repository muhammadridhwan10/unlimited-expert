<?php

namespace App\Http\Controllers;

use App\Models\El;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ElController extends Controller
{
    public function create(Request $request, $project_id, $client_id)
    {
        $el = El::where('client_id', $client_id)->get()->pluck('el_number', 'id');
        return view('el.create', compact('project_id', 'el','client_id'));
    }

    public function store(Request $request)
    {

        $validator = \Validator::make(
            $request->all(), [
                               'project_id' => 'required',
                                'client_id' => 'required',
                                'el_number' => 'required',
                                'file' => 'required',
                           ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $client = User::where('id', $request->client_id)->first();

        if(!empty($request->file))
        {
            $filenameWithExt = $request->file('file')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('file')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $dir             = storage_path('uploads/el/' . $client->name . '/');

            if(!file_exists($dir))
            {
                mkdir($dir, 0777, true);
            }

            Storage::disk('minio')->put(
                'uploads/el/' . $client->name . '/' . $fileNameToStore,
                file_get_contents($request->file('file'))
            );
            
            // $path = $request->file('file')->storeAs('uploads/el/' . $client->name . '/', $fileNameToStore, 's3');
        }

        $el                       = new El();
        $el->project_id           = $request->project_id;
        $el->client_id            = $request->client_id;
        $el->el_number            = $request->el_number;
        $el->status               = $request->status;
        $el->file                 = !empty('uploads/el/' . $client->name . '/'  . $request->file) ? 'uploads/el/'  . $client->name . '/'  .  $fileNameToStore : '';
        $el->created_by           = \Auth::user()->creatorId();

        $el->save();

        return redirect()->route('projects.show',\Crypt::encrypt($request->project_id))->with('success', 'EL created successfully');
    }

    public function edit($id)
    {
        $el = El::findOrFail($id);
        return view('el.edit', compact('el'));
    }

    public function update(Request $request, $id)
    {
        $el = El::findOrFail($id);

        $validator = \Validator::make(
            $request->all(), [
                'project_id' => 'required',
                'client_id' => 'required',
                'el_number' => 'required',
                'file' => 'nullable|file|mimes:pdf',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $client = User::where('id', $request->client_id)->first();

        if ($request->hasFile('file')) {
            $filenameWithExt = $request->file('file')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('file')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $dir = storage_path('uploads/el/' . $client->name . '/');

            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            Storage::disk('minio')->put(
                'uploads/el/' . $client->name . '/' . $fileNameToStore,
                file_get_contents($request->file('file'))
            );

            // $path = $request->file('file')->storeAs('uploads/el/' . $client->name . '/', $fileNameToStore, 'public');
            $el->file = 'uploads/el/' . $client->name . '/' . $fileNameToStore;
        }

        $el->project_id = $request->project_id;
        $el->client_id = $request->client_id;
        $el->el_number = $request->el_number;
        $el->status    = $request->status;
        $el->created_by = auth()->user()->id;

        $el->save();

        return redirect()->route('projects.show', \Crypt::encrypt($el->project_id))->with('success', 'EL updated successfully');
    }


    public function destroy($id)
    {
        $el = El::findOrFail($id);
        $el->delete();
        return redirect()->route('el.index')->with('success', 'EL deleted successfully');
    }

    public function getFileRequest(Request $request)
    {
        $file       = El::find($request->id);
        $images     = El::where('id',$request->id)->get();
        return view('el.images',compact('images','file'));
    }
}
