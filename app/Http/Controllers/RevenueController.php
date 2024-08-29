<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\InvoicePayment;
use App\Models\Mail\InvoicePaymentCreate;
use App\Models\User;
use App\Models\Revenue;
use App\Models\Invoice;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RevenueController extends Controller
{

    public function index(Request $request)
    {
        if(\Auth::user()->can('manage revenue'))
        {
            if(\Auth::user()->type == 'company')
            {
                $user = User::where('type', '=', 'partners')->pluck('name', 'id');
                $user->prepend('Select Partner', '');

                $query = Revenue::orderByDesc('id')->get();

                if(!empty($request->date))
                {
                    $date_range = explode('to', $request->date);
                    $query->whereBetween('date', $date_range);
                }

                if(!empty($request->user))
                {
                    $query->where('user_id', '=', $request->user);
                }

                $revenues = $query;

                return view('revenue.index', compact('revenues', 'user'));
            }
            elseif(\Auth::user()->type == 'partners')
            {
                $user = User::where('type', '=', 'partners')->pluck('name', 'id');
                $user->prepend('Select Partner', '');

                $query = Revenue::where('user_id', \Auth::user()->id);

                if(!empty($request->date))
                {
                    $date_range = explode('to', $request->date);
                    $query->whereBetween('date', $date_range);
                }

                if(!empty($request->user))
                {
                    $query->where('user_id', '=', $request->user);
                }

                $revenues = $query->orderByDesc('id')->get();

                return view('revenue.index', compact('revenues', 'user'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
    }


    public function create()
    {

        if(\Auth::user()->can('create revenue'))
        {

            $user = User::where('type', '=', 'partners')->get()->pluck('name', 'id');
            $user->prepend('--', 0);
            $invoices = Invoice::get()->pluck('invoice_id', 'id');

            return view('revenue.create', compact('user','invoices'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function store(Request $request)
    {
        if(\Auth::user()->can('create revenue'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'date' => 'required',
                                   'amount' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $revenue                 = new Revenue();
            $revenue->invoice_id     = $request->invoice_id;
            $revenue->date           = $request->date;
            $revenue->amount         = $request->amount;
            $revenue->description    = $request->description;
            $revenue->user_id        = $request->user_id;
            $revenue->created_by     = \Auth::user()->creatorId();
            $revenue->save();

            return redirect()->route('revenue.index')->with('success', __('Revenue successfully created.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit($id)
    {
        if(\Auth::user()->can('edit revenue'))
        {
            $revenue = Revenue::find($id);
            $user = User::where('type', '=', 'partners')->get()->pluck('name', 'id');
            $user->prepend('--', 0);
            $invoices = Invoice::get()->pluck('invoice_id', 'id');

            return view('revenue.edit', compact('user', 'invoices', 'revenue'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, $id)
    {
        if(\Auth::user()->can('edit revenue'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                    'date' => 'required',
                                    'amount' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post = $request->all();
            $revenue = Revenue::find($id);
            $revenue->update($post);

            return redirect()->route('revenue.index')->with('success', __('Revenue successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        if(\Auth::user()->can('delete revenue'))
        {
            $revenue = Revenue::find($id);
            $revenue->delete();
               
            return redirect()->route('revenue.index')->with('success', __('Revenue successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getinvoice(Request $request)
    {
        $invoice = Invoice::where('id', $request->id)->first();

        $amount = round($invoice->getDue());

        echo json_encode($amount);
    }
}
