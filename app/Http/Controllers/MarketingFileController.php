<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketingFile;

class MarketingFileController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage document'))
        {
            $marketing_files = MarketingFile::orderByDesc('id')->paginate(10);

            return view('marketing-files.index', compact('marketing_files'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create document'))
        {
            return view('marketing-files.create');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create document'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $marketing_files              = new MarketingFile();

            if (!empty($request->file)) {
                $filenameWithExt = $request->file('file')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('file')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $dir             = storage_path('uploads/marketingFiles/');
    
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $path = $request->file('file')->storeAs('uploads/marketingFiles/', $fileNameToStore);
                // $path = $request->file('file')->storeAs('uploads/marketingFiles/', $fileNameToStore, 's3');
    
                $marketing_files->file = 'uploads/marketingFiles/' . $fileNameToStore;
            } else {
                $marketing_files->file = NULL;
            }

            $marketing_files->name = $request->name;
            $marketing_files->created_by = \Auth::user()->id;
            $marketing_files->save();

            return redirect()->route('marketing-files.index')->with('success', __('Marketing Files successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(MarketingFile $marketing_file)
    {
        if (\Auth::user()->can('edit document')) 
        {
            return view('marketing-files.edit', compact('marketing_file'));

        } else {

            return response()->json(['error' => __('Permission denied.')], 401);

        }
    }


    public function update(Request $request, MarketingFile $marketing_file)
    {
        if (\Auth::user()->can('edit document')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            if (!empty($request->file)) {
                $filenameWithExt = $request->file('file')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('file')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $dir             = storage_path('uploads/marketingFiles/');
    
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $path = $request->file('file')->storeAs('uploads/marketingFiles/', $fileNameToStore);
                // $path = $request->file('file')->storeAs('uploads/marketingFiles/', $fileNameToStore, 's3');
    
                $marketing_file->file = 'uploads/marketingFiles/' . $fileNameToStore;
            } else {
                $marketing_file->file = NULL;
            }

            $marketing_file->name = $request->name;
            $marketing_file->created_by = \Auth::user()->id;
            $marketing_file->save();

            return redirect()->route('marketing-files.index')->with('success', __('Marketing Files successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }



    // public function destroy(DocumentRequest $document_request)
    // {
    //     if(\Auth::user()->can('delete document type'))
    //     {
    //         if($document_request->created_by == \Auth::user()->creatorId())
    //         {
    //             $document_request->delete();

    //             return redirect()->route('document-request.index')->with('success', __('Document Request successfully deleted.'));
    //         }
    //         elseif(Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
    //         {
    //             $document_request->delete();

    //             return redirect()->route('document-request.index')->with('success', __('Document Request successfully deleted.'));
    //         }
    //         else
    //         {
    //             return redirect()->back()->with('error', __('Permission denied.'));
    //         }
    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    public function getFile(Request $request)
    {
        $file       = MarketingFile::find($request->id);
        $images     = MarketingFile::where('id',$request->id)->get();

        return view('marketing-files.images',compact('images','file'));
    }

    // public function action($id)
    // {

    //     $document     = DocumentRequest::find($id);
    //     $employee     = Employee::where('id', $document->employee_id)->first();

    //     return view('document-request.action', compact('document', 'employee'));
    // }

    // public function changeaction(Request $request)
    // {
    //     $document = DocumentRequest::find($request->document_id);

    //     if( $document->document_type == 'Contract Employee' )
    //     {
    //         if (!empty($request->file_feedback)) {
    //             $filenameWithExt = $request->file('file_feedback')->getClientOriginalName();
    //             $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
    //             $extension       = $request->file('file_feedback')->getClientOriginalExtension();
    //             $fileNameToStore = $filename . '_' . time() . '.' . $extension;
    //             $dir             = storage_path('uploads/documentRequest/');
    
    //             if (!file_exists($dir)) {
    //                 mkdir($dir, 0777, true);
    //             }
    //             // $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore);
    //             // $path = $request->file('file_feedback')->storeAs('uploads/documentRequest/', $fileNameToStore, 's3');
    
    //             $document->file_feedback = 'uploads/documentRequest/' . $fileNameToStore;
    //         } else {
    //             $document->file_feedback = NULL;
    //         }
    //     }
    //     else
    //     {
    //         if (!empty($request->file)) {
    //             $filenameWithExt = $request->file('file')->getClientOriginalName();
    //             $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
    //             $extension       = $request->file('file')->getClientOriginalExtension();
    //             $fileNameToStore = $filename . '_' . time() . '.' . $extension;
    //             $dir             = storage_path('uploads/documentRequest/');
    
    //             if (!file_exists($dir)) {
    //                 mkdir($dir, 0777, true);
    //             }
    //             // $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore);
    //             $path = $request->file('file')->storeAs('uploads/documentRequest/', $fileNameToStore, 's3');
    
    //             $document->file = 'uploads/documentRequest/' . $fileNameToStore;
    //         } else {
    //             $document->file = NULL;
    //         }
    //     }

    //     $document->status = 'Completed';
    //     $document->save();

    //     //Email Notification
    //     $employee = Employee::where('id', $document->employee_id)->first();
    //     $email = $employee->email;
    //     Mail::to($email)->send(new DocumentCompletedNotification($document));

    //     return redirect()->route('document-request.index')->with('success', __('Document Request successfully updated.'));
    // }
}
