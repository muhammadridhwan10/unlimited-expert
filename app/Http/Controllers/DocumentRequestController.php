<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\Employee;
use App\Models\User;
use App\Models\ProductServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentRequestController extends Controller
{
    public function index()
    {

        if(\Auth::user()->can('manage document'))
        {
            if(Auth::user()->type == 'admin')
            {
                $documents = DocumentRequest::all();

                return view('document-request.index', compact('documents'));
            }
            elseif(\Auth::user()->type == 'company')
            {
                $documents = DocumentRequest::all();

                return view('document-request.index', compact('documents'));
            }
            else
            {
                $employee = Employee::where('user_id', \Auth::user()->id)->first();
                $documents = DocumentRequest::where('employee_id', '=', $employee->id)->get();

                return view('document-request.index', compact('documents'));
            }
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
            $employees      = Employee::get()->pluck('name', 'id');
            $approval       = User::where('type', '=', 'company')->pluck('name', 'id');
            $category       = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())->where('type', 1)->get()->pluck('name', 'id');
            $category->prepend('Select Service Type', '');

            return view('document-request.create', compact('employees','approval','category'));
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
                                   'document_type' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $document              = new DocumentRequest();
            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();

            if(\Auth::user()->type == "admin" || \Auth::user()->type == "company" )
            {
                $document->employee_id = $request->employee_id;
            }
            else
            {
                $document->employee_id = $employee->id;
            }

            $document->approval        = $request->approval;
            $document->document_type = $request->document_type;
            if ($request->document_type == 'Invoice') {
                $document->client_name = $request->client_name_invoice;
                $document->email_attention = $request->email_attention_invoice;
                $document->name_attention = $request->name_attention_invoice;
                $document->position_attention = $request->position_attention_invoice;
                $document->address = $request->address_invoice;
                $document->no_pic = $request->no_pic_invoice;
            } elseif ($request->document_type == 'Proposal' || $request->document_type == 'EL') {
                $document->client_name = $request->client_name_proposal;
                $document->email_attention = $request->email_attention_proposal;
                $document->name_attention = $request->name_attention_proposal;
                $document->position_attention = $request->position_attention_proposal;
                $document->address = $request->address_proposal;
                $document->service_type = $request->service_type_proposal;
                $document->period = $request->period_proposal;
                $document->termin1 = $request->termin1_proposal;
                $document->termin2 = $request->termin2_proposal;
                $document->termin3 = $request->termin3_proposal;
                $document->fee = $request->fee_proposal;
                $document->pph23 = $request->pph23_proposal;
            } elseif ($request->document_type == 'Barcode LAI') {
                $document->client_name = $request->client_name_barcode;
            }
            $document->note = $request->note;
            $document->status = 'Pending';
            $document->created_by  = \Auth::user()->creatorId();
            $document->save();

            return redirect()->route('document-request.index')->with('success', __('Document Request successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(DocumentRequest $document_request)
    {
        return redirect()->route('document.index');
    }

    public function edit(DocumentRequest $document_request)
    {
        if (\Auth::user()->can('edit document')) 
        {
            $employees = Employee::get()->pluck('name', 'id');
            $approval = User::where('type', '=', 'company')->pluck('name', 'id');
            $category = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())->where('type', 1)->get()->pluck('name', 'id');
            $category->prepend('Select Service Type', '');

            return view('document-request.edit', compact('document_request', 'employees', 'approval', 'category'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, DocumentRequest $document_request)
    {
        if (\Auth::user()->can('edit document')) {
            $validator = \Validator::make(
                $request->all(), [
                    'document_type' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::where('user_id', '=', \Auth::user()->id)->first();

            if (\Auth::user()->type == "admin" || \Auth::user()->type == "company") {
                $document_request->employee_id = $request->employee_id;
            } else {
                $document_request->employee_id = $employee->id;
            }

            $document_request->approval = $request->approval;
            $document_request->document_type = $request->document_type;

            if ($request->document_type == 'Invoice') {
                $document_request->client_name = $request->client_name_invoice;
                $document_request->email_attention = $request->email_attention_invoice;
                $document_request->name_attention = $request->name_attention_invoice;
                $document_request->position_attention = $request->position_attention_invoice;
                $document_request->address = $request->address_invoice;
                $document_request->no_pic = $request->no_pic_invoice;
            } elseif ($request->document_type == 'Proposal' || $request->document_type == 'EL') {
                $document_request->client_name = $request->client_name_proposal;
                $document_request->email_attention = $request->email_attention_proposal;
                $document_request->name_attention = $request->name_attention_proposal;
                $document_request->position_attention = $request->position_attention_proposal;
                $document_request->address = $request->address_proposal;
                $document_request->service_type = $request->service_type_proposal;
                $document_request->period = $request->period_proposal;
                $document_request->termin1 = $request->termin1_proposal;
                $document_request->termin2 = $request->termin2_proposal;
                $document_request->termin3 = $request->termin3_proposal;
                $document_request->fee = $request->fee_proposal;
                $document_request->pph23 = $request->pph23_proposal;
            } elseif ($request->document_type == 'Barcode LAI') {
                $document_request->client_name = $request->client_name_barcode;
            }

            $document_request->note = $request->note;
            $document_request->status = 'Pending';
            $document_request->created_by = \Auth::user()->creatorId();
            $document_request->save();

            return redirect()->route('document-request.index')->with('success', __('Document Request successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }



    public function destroy(DocumentRequest $document_request)
    {
        if(\Auth::user()->can('delete document type'))
        {
            if($document_request->created_by == \Auth::user()->creatorId())
            {
                $document_request->delete();

                return redirect()->route('document-request.index')->with('success', __('Document Request successfully deleted.'));
            }
            elseif(Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $document_request->delete();

                return redirect()->route('document-request.index')->with('success', __('Document Request successfully deleted.'));
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

    public function getFileRequest(Request $request)
    {
        $file       = DocumentRequest::find($request->id);
        $images     = DocumentRequest::where('id',$request->id)->get();
        return view('document-request.images',compact('images','file'));
    }

    public function action($id)
    {

        $document     = DocumentRequest::find($id);
        $employee     = Employee::where('id', $document->employee_id)->first();

        return view('document-request.action', compact('document', 'employee'));
    }

    public function changeaction(Request $request)
    {
        $document = DocumentRequest::find($request->document_id);

        if( $document->document_type == 'Contract Employee' )
        {
            if (!empty($request->file_feedback)) {
                $filenameWithExt = $request->file('file_feedback')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('file_feedback')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $dir             = storage_path('uploads/documentRequest/');
    
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                // $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore);
                $path = $request->file('file_feedback')->storeAs('uploads/documentRequest/', $fileNameToStore, 's3');
    
                $document->file_feedback = 'uploads/documentRequest/' . $fileNameToStore;
            } else {
                $document->file_feedback = NULL;
            }
        }
        else
        {
            if (!empty($request->file)) {
                $filenameWithExt = $request->file('file')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('file')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $dir             = storage_path('uploads/documentRequest/');
    
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                // $path = $request->file('reimbursment_image')->storeAs('uploads/reimbursment/', $fileNameToStore);
                $path = $request->file('file')->storeAs('uploads/documentRequest/', $fileNameToStore, 's3');
    
                $document->file = 'uploads/documentRequest/' . $fileNameToStore;
            } else {
                $document->file = NULL;
            }
        }

        $document->status = 'Completed';
        $document->save();

        return redirect()->route('document-request.index')->with('success', __('Document Request successfully updated.'));
    }

}
