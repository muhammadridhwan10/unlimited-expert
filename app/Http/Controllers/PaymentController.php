<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BillPayment;
use App\Models\Payment;
use App\Models\ProductServiceCategory;
use App\Models\Transaction;
use App\Models\Utility;
use App\Models\User;
use App\Models\ChartOfAccount;
use App\Models\Vender;
use Illuminate\Http\Request;
use App\Mail\BillPartnerNotification;
use App\Mail\BillAdminNotification;
use App\Mail\BillApprovalNotification;
use App\Mail\BillApprovedNotification;
use App\Mail\BillPaidNotification;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{

    public function index(Request $request)
    {
        if(\Auth::user()->can('manage payment'))
        {
            $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $vender->prepend('Select Vendor', '');

            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend('Select Account', '');

            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 2)->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            $query = Payment::query();

//            if(!empty($request->date))
//            {
//                $date_range = explode('to', $request->date);
//                $query->whereBetween('date', $date_range);
//            }
            if(count(explode('to', $request->date)) > 1)
            {
                $date_range = explode(' to ', $request->date);
                $query->whereBetween('date', $date_range);
            }
            elseif(!empty($request->date))
            {
                $date_range = [$request->date , $request->date];
                $query->whereBetween('date', $date_range);
            }

            if(!empty($request->vender))
            {
                $query->where('id', '=', $request->vender);
            }
            if(!empty($request->account))
            {
                $query->where('account_id', '=', $request->account);
            }

            if(!empty($request->category))
            {
                $query->where('category_id', '=', $request->category);
            }


            $payments = $query->get();


            return view('payment.index', compact('payments', 'account', 'category', 'vender'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if(\Auth::user()->can('create payment'))
        {
            $venders = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $venders->prepend('--', 0);
            $partners = User::where('type', 'partners')
            ->orWhere('type', 'senior accounting')
            ->get()
            ->pluck('name', 'id');
            $partners->prepend('Select Partner', '');
            $approval   = User::where('type','=', 'company')->orWhere('type','=', 'senior accounting')->orWhere('type','=', 'partners')->get()->pluck('name', 'id');
            $categories = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 2)->get()->pluck('name', 'id');
            $accounts   = ChartOfAccount::where('type', 5)->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $accounts->prepend('Select Account', '');

            return view('payment.create', compact('venders', 'categories', 'accounts','partners','approval'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function store(Request $request)
    {

    //    dd($request->all());

        if(\Auth::user()->can('create payment'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'date' => 'required',
                                   'amount' => 'required',
                                   'category_id' => 'required',
                                   'tax' => 'required',
                                   'add_receipt' => 'mimes:png,jpeg,jpg|max:10240',
                                   'add_bill' => 'mimes:png,jpeg,jpg|max:10240',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if(\Auth::user()->type == 'partners')
            {
                $payment                 = new Payment();
                $payment->date           = $request->date;
                $payment->amount         = $request->amount;
                $payment->amount_before_tax  = $request->amount_before_tax;
                $payment->account_id     = $request->account_id;
                $payment->vender_id      = $request->vender_id;
                $payment->category_id    = $request->category_id;
                $payment->user_id        = \Auth::user()->id;
                $payment->tax            = $request->tax;
                $payment->kurs           = $request->kurs;
                $payment->currency       = $request->currency;
                $payment->operator       = $request->operator;
                $payment->approval       = $request->approval ?? 0;
                $payment->payment_method = 0;
                $payment->status         = 0;
                $payment->reference      = $request->reference;
                if(!empty($request->add_receipt))
                {
                    $filenameWithExt = $request->file('add_receipt')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('add_receipt')->getClientOriginalExtension();
                    $fileNameToStoreReceipt = $filename . '_' . time() . '.' . $extension;
                    $dir             = storage_path('uploads/receipt/' . \Auth::user()->name . '/');

                    if(!file_exists($dir))
                    {
                        mkdir($dir, 0777, true);
                    }
                    // $path = $request->file('add_receipt')->storeAs('uploads/receipt/', $fileNameToStoreReceipt);
                    $path = $request->file('add_receipt')->storeAs('uploads/receipt/' . \Auth::user()->name . '/', $fileNameToStoreReceipt, 's3');
                }
                else {
                    $fileNameToStoreReceipt = '';
                }

                if(!empty($request->add_bill))
                {
                    $filenameWithExt = $request->file('add_bill')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('add_bill')->getClientOriginalExtension();
                    $fileNameToStoreBill = $filename . '_' . time() . '.' . $extension;
                    $dir             = storage_path('uploads/bill/' . \Auth::user()->name . '/');

                    if(!file_exists($dir))
                    {
                        mkdir($dir, 0777, true);
                    }
                    // $path = $request->file('add_bill')->storeAs('uploads/bill/', $fileNameToStoreBill);
                    $path = $request->file('add_bill')->storeAs('uploads/bill/' . \Auth::user()->name . '/', $fileNameToStoreBill, 's3');
                }
                else {
                    $fileNameToStoreBill = '';
                }
                $payment->description    = $request->description;
                $payment->add_receipt  = !empty('uploads/receipt/' . \Auth::user()->name . '/' . $request->add_receipt) ? 'uploads/receipt/' . \Auth::user()->name . '/' . $fileNameToStoreReceipt : '';
                $payment->add_bill  = !empty('uploads/bill/' .\Auth::user()->name . '/' . $request->add_bill) ? 'uploads/bill/' . \Auth::user()->name . '/' . $fileNameToStoreBill : '';
                $payment->created_by     = \Auth::user()->creatorId();
                
                //notification
                $user = User::where('email', '=', 'company')->orWhere('type', 'senior accounting')->get();
                
                foreach ($users as $user) {
                    Mail::to($user->email)->send(new BillPartnerNotification($payment));
                }
                
            }
            else
            {
                $payment                 = new Payment();
                $payment->date           = $request->date;
                $payment->amount         = $request->amount;
                $payment->amount_before_tax  = $request->amount_before_tax;
                $payment->account_id     = $request->account_id;
                $payment->vender_id      = $request->vender_id;
                $payment->category_id    = $request->category_id;
                $payment->user_id        = $request->user_id;
                $payment->tax            = $request->tax;
                $payment->kurs           = $request->kurs;
                $payment->currency       = $request->currency;
                $payment->operator       = $request->operator;
                $payment->approval       = $request->approval ?? 0;
                $payment->payment_method = 0;
                $payment->status         = 0;
                $payment->reference      = $request->reference;
                if(!empty($request->add_receipt))
                {
                    $filenameWithExt = $request->file('add_receipt')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('add_receipt')->getClientOriginalExtension();
                    $fileNameToStoreReceipt = $filename . '_' . time() . '.' . $extension;
                    $dir             = storage_path('uploads/receipt/');

                    if(!file_exists($dir))
                    {
                        mkdir($dir, 0777, true);
                    }
                    // $path = $request->file('add_receipt')->storeAs('uploads/receipt/', $fileNameToStoreReceipt);
                    $path = $request->file('add_receipt')->storeAs('uploads/receipt/', $fileNameToStoreReceipt, 's3');
                }
                else {
                    $fileNameToStoreReceipt = '';
                }

                if(!empty($request->add_bill))
                {
                    $filenameWithExt = $request->file('add_bill')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('add_bill')->getClientOriginalExtension();
                    $fileNameToStoreBill = $filename . '_' . time() . '.' . $extension;
                    $dir             = storage_path('uploads/bill/');

                    if(!file_exists($dir))
                    {
                        mkdir($dir, 0777, true);
                    }
                    $path = $request->file('add_bill')->storeAs('uploads/bill/', $fileNameToStoreBill);
                    // $path = $request->file('add_bill')->storeAs('uploads/bill/', $fileNameToStoreBill, 's3');
                }
                else {
                    $fileNameToStoreBill = '';
                }
                $payment->description    = $request->description;
                $payment->add_receipt  = !empty('uploads/receipt/' . $request->add_receipt) ? 'uploads/receipt/' . $fileNameToStoreReceipt : '';
                $payment->add_bill  = !empty('uploads/bill/' . $request->add_bill) ? 'uploads/bill/' . $fileNameToStoreBill : '';
                $payment->created_by     = \Auth::user()->creatorId();

                if($request->approval != 0)
                {
                    $user = User::where('id', '=', $request->approval)->first();
                    Mail::to($user->email)->send(new BillApprovalNotification($payment));
                }
                else
                {
                    $user = User::where('id', '=', $request->user_id)->first();
                    Mail::to($user->email)->send(new BillAdminNotification($payment));
                }

            }

            $payment->save();


            return redirect()->route('payment.index')->with('success', __('Payment successfully created'). ((isset($result) && $result!=1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));

        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(Payment $payment)
    {

        if(\Auth::user()->can('edit payment'))
        {
            $venders = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $venders->prepend('--', 0);
            $partners = User::where('type', 'partners')
            ->orWhere('type', 'senior accounting')
            ->get()
            ->pluck('name', 'id');
            $partners->prepend('Select Partner', '');
            $approval   = User::where('type','=', 'company')->orWhere('type','=', 'senior accounting')->orWhere('type','=', 'partners')->get()->pluck('name', 'id');
            $categories = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->get()->where('type', '=', 2)->pluck('name', 'id');

            $accounts   = ChartOfAccount::where('sub_type', 12)->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $accounts->prepend('Select Account', '');

            return view('payment.edit', compact('venders', 'categories', 'accounts', 'payment','partners','approval'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, Payment $payment)
    {
        if(\Auth::user()->can('edit payment'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'date' => 'required',
                                   'amount' => 'required',
                                   'account_id' => 'required',
                                   'vender_id' => 'required',
                                   'category_id' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $payment->date           = $request->date;
            $payment->amount         = $request->amount;
            $payment->amount_before_tax         = $request->amount_before_tax;
            $payment->account_id     = $request->account_id;
            $payment->vender_id      = $request->vender_id;
            $payment->category_id    = $request->category_id;

            if(\Auth::user()->type == 'partners')
            {
                $payment->user_id  = \Auth::user()->id;
            }
            else
            {
                $payment->user_id        = $request->user_id;
            }

            $payment->tax            = $request->tax;
            $payment->kurs           = $request->kurs;
            $payment->currency       = $request->currency;
            $payment->operator       = $request->operator;
            $payment->approval       = $request->approval;
            $payment->payment_method = 0;
            $payment->status         = 0;

            if(!empty($request->add_receipt))
            {
                $filenameWithExt = $request->file('add_receipt')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('add_receipt')->getClientOriginalExtension();
                $fileNameToStoreReceipt = $filename . '_' . time() . '.' . $extension;
                $dir             = storage_path('uploads/receipt/');

                if(!file_exists($dir))
                {
                    mkdir($dir, 0777, true);
                }
                // $path = $request->file('add_receipt')->storeAs('uploads/receipt/', $fileNameToStoreReceipt);
                $path = $request->file('add_receipt')->storeAs('uploads/receipt/', $fileNameToStoreReceipt, 's3');
            }
            else {
                $fileNameToStoreReceipt = '';
            }

            if(!empty($request->add_bill))
            {
                $filenameWithExt = $request->file('add_bill')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('add_bill')->getClientOriginalExtension();
                $fileNameToStoreBill = $filename . '_' . time() . '.' . $extension;
                $dir             = storage_path('uploads/bill/');

                if(!file_exists($dir))
                {
                    mkdir($dir, 0777, true);
                }
                // $path = $request->file('add_bill')->storeAs('uploads/bill/', $fileNameToStoreBill);
                $path = $request->file('add_bill')->storeAs('uploads/bill/', $fileNameToStoreBill, 's3');
            }
            else {
                $fileNameToStoreBill = '';
            }

            $payment->add_receipt  = !empty('uploads/receipt/' . $request->add_receipt) ? 'uploads/receipt/' . $fileNameToStoreReceipt : '';
            $payment->add_bill  = !empty('uploads/bill/' . $request->add_bill) ? 'uploads/bill/' . $fileNameToStoreBill : '';
            $payment->created_by     = \Auth::user()->creatorId();
            $payment->description    = $request->description;
            $payment->save();

            return redirect()->route('payment.index')->with('success', __('Payment Updated Successfully'). ((isset($result) && $result!=1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));

        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Payment $payment)
    {
        if(\Auth::user()->can('delete payment'))
        {
            if($payment->created_by == \Auth::user()->creatorId())
            {

                if(!empty($payment->add_receipt))
                {
                    //storage limit
                    $file_path = '/uploads/payment/'.$payment->add_receipt;
                    $result = Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);

                }

                $payment->delete();
                // $type = 'Payment';
                // $user = 'Vender';
                // Transaction::destroyTransaction($payment->id, $type, $user);

                // if($payment->vender_id != 0)
                // {
                //     Utility::userBalance('vendor', $payment->vender_id, $payment->amount, 'credit');
                // }
                // Utility::bankAccountBalance($payment->account_id, $payment->amount, 'credit');

                return redirect()->route('payment.index')->with('success', __('Payment successfully deleted.'));
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

    public function getPaymentReceiptImages(Request $request)
    {
        $payment   = Payment::find($request->id);
        $images    = Payment::where('id',$request->id)->get();
        return view('payment.receipt-images',compact('images','payment'));
    }

    public function getPaymentBillImages(Request $request)
    {
        $payment   = Payment::find($request->id);
        $images    = Payment::where('id',$request->id)->get();
        return view('payment.bill-images',compact('images','payment'));
    }

    public function action($id)
    {

        $payment      = Payment::find($id);
        $user         = User::find($payment->user_id);
        $vendor       = Vender::find($payment->vender_id);
        $account      = ChartOfAccount::find($payment->account_id);

        return view('payment.action', compact('payment', 'user','vendor','account'));
    }

    public function changeaction(Request $request)
    {

        $payment = Payment::find($request->payment_id);

        $status = $request->status;

        if($status == 'Approved')
        {
            $payment->status = 1;
            $user = User::where('email', '=', 'company')->orWhere('type', 'senior accounting')->get(); 
            foreach ($users as $user) {
                Mail::to($user->email)->send(new BillApprovedNotification($payment));
            }
        }
        elseif($status == 'Reject')
        {
            $payment->status = 2;
        }
        elseif($status == 'Paid')
        {
            $payment->status = 3;
            $user = User::where('id', '=', $request->user_id)->first();
                    Mail::to($user->email)->send(new BillPaidNotification($payment));
        }

        $payment->save();

        return redirect()->route('payment.index')->with('success', __('Payment successfully updated.'));
    }
}
