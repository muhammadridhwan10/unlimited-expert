<?php

namespace App\Http\Controllers;

use App\Exports\InvoiceExport;
use App\Models\BankAccount;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\InvoiceBankTransfer;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use App\Models\Plan;
use App\Models\Products;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\StockReport;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Client;
use App\Models\Revenue;
use App\Models\Project;
use App\Models\Utility;
use App\Models\ChartOfAccount;
use App\Models\Settings;
use Auth;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Mail\CustomerInvoiceSend;
use App\Mail\CustomerInvoiceThanksSend;
use App\Mail\ReminderInvoiceSend;

class InvoiceController extends Controller
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        if(\Auth::user()->can('manage invoice'))
        {
            if(\Auth::user()->type == 'partners')
            {
                $client = User::where('type','=','client')->get()->pluck('name', 'id');
                $client->prepend('Select Client', '');

                $partner = User::where('type', 'partners')
                 ->orWhere('type', 'senior accounting')
                 ->get()
                 ->pluck('name', 'id');
                $partner->prepend('Select Partner', '');
    
                $status = Invoice::$statues;
                $category_invoice = Invoice::$categoryInvoice;
                $companies = Invoice::$company;
    
                $query = Invoice::where('user_id', \Auth::user()->id);
    
                if(!empty($request->client))
                {
                    $query->where('client_id', '=', $request->client);
                }
                if(!empty($request->company))
                {
                    $query->where('company', '=', $request->company);
                }
                if(!empty($request->user_id))
                {
                    $query->where('user_id', '=', $request->user_id);
                }
                if (!empty($request->company)) {
                    $query->where('company', '=', $request->company);
                }
                if(count(explode('to', $request->issue_date)) > 1)
                {
                    $date_range = explode(' to ', $request->issue_date);
                    $query->whereBetween('issue_date', $date_range);
                }
                elseif(!empty($request->issue_date))
                {
                    $date_range = [$request->issue_date , $request->issue_date];
                    $query->whereBetween('issue_date', $date_range);
                }
                if(!empty($request->status))
                {
                    $query->where('status', '=', $request->status);
                }
                if(!empty($request->category_invoice))
                {
                    $query->where('category_invoice', '=', $request->category_invoice);
                }

                $invoices = $query->get();

                $monthlyData = [];
                $yearlyData = [];

                foreach ($invoices as $invoice) {
                    $issueDate = Carbon::parse($invoice->issue_date);
                    $monthKey = $issueDate->format('F Y');
                    $yearKey = $issueDate->format('Y');

                    $invoice->total_amount = $invoice->getDue();

                    if (!isset($monthlyData[$monthKey])) {
                        $monthlyData[$monthKey] = 0;
                    }
                    $monthlyData[$monthKey] += $invoice->total_amount;

                    if (!isset($yearlyData[$yearKey])) {
                        $yearlyData[$yearKey] = 0;
                    }
                    $yearlyData[$yearKey] += $invoice->total_amount;
                }
            }
            else
            {
                $client = User::where('type','=','client')->get()->pluck('name', 'id');
                $client->prepend('Select Client', '');

                $partner = User::where('type', 'partners')
                 ->orWhere('type', 'senior accounting')
                 ->get()
                 ->pluck('name', 'id');
                $partner->prepend('Select Partner', '');
    
                $status = Invoice::$statues;
                $category_invoice = Invoice::$categoryInvoice;
                $companies = Invoice::$company;
                $query = Invoice::query();

                if(!empty($request->client))
                {
                    $query->where('client_id', '=', $request->client);
                }
                if(!empty($request->company))
                {
                    $query->where('company', '=', $request->company);
                }
                if(!empty($request->user_id))
                {
                    $query->where('user_id', '=', $request->user_id);
                }
                if(count(explode('to', $request->issue_date)) > 1)
                {
                    $date_range = explode(' to ', $request->issue_date);
                    $query->whereBetween('issue_date', $date_range);
                }
                elseif(!empty($request->issue_date))
                {
                    $date_range = [$request->issue_date , $request->issue_date];
                    $query->whereBetween('issue_date', $date_range);
                }
                if(!empty($request->status))
                {
                    $query->where('status', '=', $request->status);
                }
                if(!empty($request->category_invoice))
                {
                    $query->where('category_invoice', '=', $request->category_invoice);
                }
                $invoices = $query->get();

                $monthlyData = [];
                $yearlyData = [];

                foreach ($invoices as $invoice) {
                    $issueDate = Carbon::parse($invoice->issue_date);
                    $monthKey = $issueDate->format('F Y');
                    $yearKey = $issueDate->format('Y');

                    $invoice->total_amount = $invoice->getDue();

                    if (!isset($monthlyData[$monthKey])) {
                        $monthlyData[$monthKey] = 0;
                    }
                    $monthlyData[$monthKey] += $invoice->total_amount;

                    if (!isset($yearlyData[$yearKey])) {
                        $yearlyData[$yearKey] = 0;
                    }
                    $yearlyData[$yearKey] += $invoice->total_amount;
                }         

            }
            return view('invoice.index', compact('invoices', 'client','companies','partner', 'status','monthlyData', 'yearlyData','category_invoice'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create($customerId)
    {
        if(\Auth::user()->can('create invoice'))
        {
            $customFields   = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
            $customers      = User::where('type','=','client')->get()->pluck('name', 'id');
            $customers->prepend('Select Client', '');
            $category = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())->where('type', 1)->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');
            $projects = Project::get()->pluck('project_name', 'id');
            $projects->prepend('--', '');
            $partners = User::where('type', 'partners')
            ->orWhere('type', 'senior accounting')
            ->get()
            ->pluck('name', 'id');
            $partners->prepend('Select Partner', '');
            $account = ChartOfAccount::where('sub_type', 16)->get()->pluck('name', 'id');
            $account->prepend('Select Account', '');
            $siteCurrencySymbol = Settings::where('name', 'site_currency_symbol')->value('value');
            $siteCurrencySymbol2 = Settings::where('name', 'site_currency_symbol_2')->value('value');

            return view('invoice.create', compact('customers','partners','account', 'projects', 'category', 'customFields', 'customerId','siteCurrencySymbol', 'siteCurrencySymbol2'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function customer(Request $request)
    {
        $client = Client::where('user_id', '=', $request->id)->first();
        return view('invoice.customer_detail', compact('client'));
    }

    public function product(Request $request)
    {

        $data['product']     = $product = ProductService::find($request->product_id);
        $data['unit']        = (!empty($product->unit())) ? $product->unit()->name : '';
        $data['taxRate']     = $taxRate = !empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0;
        $data['taxes']       = !empty($product->tax_id) ? $product->tax($product->tax_id) : 0;
        $salePrice           = $product->sale_price;
        $quantity            = 1;
        $taxPrice            = ($taxRate / 100) * ($salePrice * $quantity);
        $data['totalAmount'] = ($salePrice * $quantity);

        return json_encode($data);
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create invoice'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'client_id' => 'required',
                                   'user_id' => 'required',
                                   'company' => 'required',
                                   'account_id' => 'required',
                                   'issue_date' => 'required',
                                   'due_date' => 'required',
                                   'category_id' => 'required|array',
                                   'items' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $categoryIds = implode(',', $request->input('category_id'));


            $status = Invoice::$statues;
            $invoice                 = new Invoice();
            $invoice->invoice_id     = $request->invoice_id;
            $invoice->client_id      = $request->client_id;
            $invoice->status         = 1;
            $invoice->issue_date     = $request->issue_date;
            $invoice->due_date       = $request->due_date;
            $invoice->category_invoice = $request->category_invoice;
            $invoice->category_id    = $categoryIds;
            $invoice->ref_number     = $request->ref_number;
            $invoice->invoice_template = $request->invoice_template;
            $invoice->currency       = $request->currency;
            $invoice->user_id        = $request->user_id;
            $invoice->company        = $request->company;
            $invoice->account_id     = $request->account_id;
//            $invoice->discount_apply = isset($request->discount_apply) ? 1 : 0;
            $invoice->created_by     = \Auth::user()->creatorId();
            $invoice->save();
            CustomField::saveData($invoice, $request->customField);
            $products = $request->items;

            for($i = 0; $i < count($products); $i++)
            {

                $invoiceProduct              = new InvoiceProduct();
                $invoiceProduct->invoice_id  = $invoice->id;
                $invoiceProduct->product_id  = $products[$i]['item'];
                $invoiceProduct->tax         = $products[$i]['tax'];
                $invoiceProduct->price       = $products[$i]['price'];
                $invoiceProduct->description = $products[$i]['description'];
                $invoiceProduct->save();

                //For Notification
                // $setting  = Utility::settings(\Auth::user()->creatorId());
                // $customer = Clients::find($request->client_id);
                // $invoiceNotificationArr = [
                //     'invoice_number' => \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
                //     'user_name' => \Auth::user()->name,
                //     'invoice_issue_date' => $invoice->issue_date,
                //     'invoice_due_date' => $invoice->due_date,
                //     'customer_name' => $customer->name,
                // ];
                //Slack Notification
                // if(isset($setting['invoice_notification']) && $setting['invoice_notification'] ==1)
                // {
                //     Utility::send_slack_msg('new_invoice', $invoiceNotificationArr);
                // }
                //Telegram Notification
                // if(isset($setting['telegram_invoice_notification']) && $setting['telegram_invoice_notification'] ==1)
                // {
                //     Utility::send_telegram_msg('new_invoice', $invoiceNotificationArr);
                // }
                //Twilio Notification
                // if(isset($setting['twilio_invoice_notification']) && $setting['twilio_invoice_notification'] ==1)
                // {
                //     Utility::send_twilio_msg($customer->contact,'new_invoice', $invoiceNotificationArr);
                // }

            }

            //Product Stock Report
            // $type='invoice';
            // $type_id = $invoice->id;
            // StockReport::where('type','=','invoice')->where('type_id' ,'=', $invoice->id)->delete();
            // $description=$invoiceProduct->quantity.'  '.__(' quantity sold in invoice').' '. \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
            // Utility::addProductStock( $invoiceProduct->product_id,$invoiceProduct->quantity,$type,$description,$type_id);

            //webhook
            // $module ='New Invoice';
            // $webhook =  Utility::webhookSetting($module);
            // if($webhook)
            // {
            //     $parameter = json_encode($invoice);
            //     $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
            //     if($status == true)
            //     {
            //         return redirect()->route('invoice.index', $invoice->id)->with('success', __('Invoice successfully created.'));
            //     }
            //     else
            //     {
            //         return redirect()->back()->with('error', __('Webhook call failed.'));
            //     }
            // }


            return redirect()->route('invoice.index', $invoice->id)->with('success', __('Invoice successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($ids)
    {
        if(\Auth::user()->can('edit invoice'))
        {
            $id      = Crypt::decrypt($ids);
            $invoice = Invoice::find($id);
            $invoice_number = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
            $customers      = User::where('type','=','client')->get()->pluck('name', 'id');
            $category       = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())->where('type', 1)->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');
            $projects = Project::get()->pluck('project_name', 'id');
            $invoice->customField = CustomField::getData($invoice, 'invoice');
            $customFields         = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
            $partners = User::where('type', 'partners')
                 ->orWhere('type', 'senior accounting')
                 ->get()
                 ->pluck('name', 'id');
            $partners->prepend('Select Partner', '');
            $account = ChartOfAccount::where('sub_type', 16)->get()->pluck('name', 'id');
            $account->prepend('Select Account', '');
            $siteCurrencySymbol = Settings::where('name', 'site_currency_symbol')->value('value');
            $siteCurrencySymbol2 = Settings::where('name', 'site_currency_symbol_2')->value('value');

            return view('invoice.edit', compact('customers','partners','account', 'projects', 'invoice', 'invoice_number', 'category', 'customFields','siteCurrencySymbol','siteCurrencySymbol2'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Invoice $invoice)
    {
        if (\Auth::user()->can('edit invoice')) {
            // Validasi input (komentar jika tidak digunakan)
            // $validator = \Validator::make($request->all(), [
            //     'client_id' => 'required',
            //     'issue_date' => 'required',
            //     'user_id' => 'required',
            //     'company' => 'required',
            //     'account_id' => 'required',
            //     'due_date' => 'required',
            //     'category_id' => 'required|array',
            //     'price' => 'required|array',
            //     'tax' => 'required|array',
            //     'item' => 'required|array',
            // ]);

            // if ($validator->fails()) {
            //     $messages = $validator->getMessageBag();
            //     return redirect()->route('invoice.index')->with('error', $messages->first());
            // }

            $invoice->invoice_id = $request->invoice_id;
            $invoice->client_id = $request->client_id;
            $invoice->issue_date = $request->issue_date;
            $invoice->due_date = $request->due_date;
            $invoice->ref_number = $request->ref_number;
            $invoice->invoice_template = $request->invoice_template;
            $invoice->category_invoice = $request->category_invoice;
            $invoice->currency = $request->currency;
            $invoice->category_id = implode(',', $request->input('category_id'));
            $invoice->user_id = $request->user_id;
            $invoice->company = $request->company;
            $invoice->account_id = $request->account_id;
            $invoice->save();

            $productIds = $request->input('id', []);
            $products = $request->input('item', []);
            $prices = $request->input('price', []);
            $taxes = $request->input('tax', []);
            $descriptions = $request->input('description', []);

            $existingItems = $invoice->items->pluck('id')->toArray();

            foreach ($productIds as $key => $id) {
                $invoiceItem = InvoiceProduct::find($id);
                if ($invoiceItem) {
                    $invoiceItem->product_id = $products[$key];
                    $invoiceItem->price = $prices[$key];
                    $invoiceItem->tax = $taxes[$key];
                    $invoiceItem->description = $descriptions[$key];
                    $invoiceItem->save();
                } else {
                    InvoiceProduct::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $products[$key],
                        'price' => $prices[$key],
                        'tax' => $taxes[$key],
                        'description' => $descriptions[$key]
                    ]);
                }
            }

            $deletedItems = array_diff($existingItems, $productIds);
            InvoiceProduct::destroy($deletedItems);

            return redirect()->route('invoice.index')->with('success', __('Invoice updated successfully.'));
        } else {
            return redirect()->route('invoice.index')->with('error', __('Permission denied.'));
        }
    }



    function invoiceNumber()
    {
        $latest = Invoice::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->invoice_id + 1;
    }

    public function show($ids)
    {

        if(\Auth::user()->can('show invoice'))
        {
            try {
                $id       = Crypt::decrypt($ids);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Invoice Not Found.'));
            }
            $id      = Crypt::decrypt($ids);
            $invoice = Invoice::find($id);

            if(!empty($invoice->created_by) == \Auth::user()->creatorId())
            {
                $invoicePayment = InvoicePayment::where('invoice_id', $invoice->id)->first();


                $client               = $invoice->client;
                $iteams               = $invoice->items;
                $user                 = \Auth::user();

                // start for storage limit note
                $invoice_user = User::find($invoice->created_by);
                // $user_plan = Plan::find($invoice_user->plan);
                // end for storage limit note

                $invoice->customField = CustomField::getData($invoice, 'invoice');
                $customFields         = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();

                return view('invoice.view', compact('invoice', 'client', 'iteams', 'invoicePayment', 'customFields', 'user','invoice_user'));
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

    public function destroy(Invoice $invoice,Request $request)
    {
        if(\Auth::user()->can('delete invoice'))
        {
            if($invoice->created_by == \Auth::user()->creatorId())
            {
                foreach($invoice->payments as $invoices)
                {
                    Utility::bankAccountBalance($invoices->account_id, $invoices->amount, 'debit');

                    $invoicepayment = InvoicePayment::find($invoices->id);
                    $invoices->delete();
                    $invoicepayment->delete();

                }

                if($invoice->client_id != 0 && $invoice->status!=1)
                {
                    Utility::updateUserBalance('client', $invoice->client_id, $invoice->getDue(), 'debit');
                }

                CreditNote::where('invoice', '=', $invoice->id)->delete();

                InvoiceProduct::where('invoice_id', '=', $invoice->id)->delete();
                $invoice->delete();
                return redirect()->route('invoice.index')->with('success', __('Invoice successfully deleted.'));
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

    public function productDestroy(Request $request)
    {

        if(\Auth::user()->can('delete invoice product'))
        {
            $invoiceProduct=InvoiceProduct::find($request->id);
            $invoice=Invoice::find($invoiceProduct->invoice_id);

            Utility::updateUserBalance('client', $invoice->client_id, $request->amount, 'debit');

            InvoiceProduct::where('id', '=', $request->id)->delete();

            return redirect()->back()->with('success', __('Invoice product successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function customerInvoice(Request $request)
    {
        if(\Auth::user()->can('manage customer invoice'))
        {

            $status = Invoice::$statues;
            $query = Invoice::where('client_id', '=', \Auth::user()->id)->where('status', '!=', '0')->where('created_by', \Auth::user()->creatorId());

            if(!empty($request->issue_date))
            {
                $date_range = explode(' - ', $request->issue_date);
                $query->whereBetween('issue_date', $date_range);
            }

            if(!empty($request->status))
            {
                $query->where('status', '=', $request->status);
            }
            $invoices = $query->get();

            return view('invoice.index', compact('invoices', 'status'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function customerInvoiceShow($id)
    {

        $invoice = Invoice::where('id', $id)->first();
        $user    = User::where('id', $invoice->created_by)->first();
        if($invoice->created_by == $user->creatorId())
        {
            $customer = $invoice->client;
            $client   = Client::where('user_id', $customer->id)->first();
            $iteams   = $invoice->items;

            if($user->type == 'super admin')
            {
                return view('invoice.view', compact('invoice', 'customer','client', 'iteams', 'user'));
            }
            elseif($user->type == 'company')
            {
                return view('invoice.customer_invoice', compact('invoice', 'customer','client', 'iteams', 'user'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function sent($id)
    {
        if(\Auth::user()->can('send invoice'))
        {
            $selectedLanguage = request('language');
            $ccEmails = [];

            $ccEmailInput = request('cc_email');
            $ccEmails = preg_split('/[,\s]+/', $ccEmailInput);
            $ccEmails = array_unique(array_filter($ccEmails));

            $invoice            = Invoice::where('id', $id)->first();
            $invoice->send_date = date('Y-m-d');
            $invoice->status    = 2;
            $invoice->save();

            $customer         = User::where('id', $invoice->client_id)->first();
            $invoice->name    = !empty($customer) ? $customer->name : '';
            $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

            $invoiceId    = Crypt::encrypt($invoice->id);
            $invoice->url = route('invoice.pdf', $invoiceId);

            $totalAmount = 0;

            $invoiceProducts = InvoiceProduct::with('productService')->where('invoice_id', $invoice->id)->get();


            foreach ($invoiceProducts as $invoiceProduct) {

                $rate = $invoiceProduct->tax;
                $price = $invoiceProduct->price;
                
                $totalAmount += $price - ($price * $rate / 100);
                
            }


            // $productNames = $invoiceProducts->pluck('productService.name')->first();
            // $productPeriods = $invoiceProducts->pluck('productService.periode')->first();

            // Utility::userBalance('customer', $customer->id, $invoice->getTotal(), 'credit');

            // $customerArr = [

            //     'customer_name'=> $customer->name,
            //     'customer_email' => $customer->email,
            //     'invoice_name' => $customer->name,
            //     'invoice_date' => $invoice->issue_date,
            //     'invoice_products_name' => $productNames,
            //     'invoice_products_periods' => $productPeriods,
            //     'invoice_due_date' => $invoice->due_date,
            //     'invoice_company_name' => $customer->billing_name,
            //     'total_amount' => $totalAmount,
            //     'invoice_number' => $invoice->invoice_id,
            //     'invoice_url' => $invoice->url,

            // ];

            Mail::to($customer->email)->send(new CustomerInvoiceSend($invoice, $selectedLanguage, $ccEmails));

            return redirect()->back()->with('success', __('Invoice successfully sent.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function resent($id)
    {
        if(\Auth::user()->can('send invoice'))
        {
            $selectedLanguage = request('language');
            $ccEmails = [];

            $ccEmailInput = request('cc_email');
            $ccEmails = preg_split('/[,\s]+/', $ccEmailInput);
            $ccEmails = array_unique(array_filter($ccEmails));

            $invoice = Invoice::where('id', $id)->first();

            $customer         = User::where('id', $invoice->client_id)->first();
            $invoice->name    = !empty($customer) ? $customer->name : '';
            $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

            $invoiceId    = Crypt::encrypt($invoice->id);
            $invoice->url = route('invoice.pdf', $invoiceId);
            // $customerArr = [

            //     'customer_name'=> $customer->name,
            //     'customer_email' => $customer->email,
            //     'invoice_name' => $customer->name,
            //     'invoice_company_name' => $customer->billing_name,
            //     'invoice_number' => $invoice->invoice_id,
            //     'invoice_url' => $invoice->url,

            // ];

            Mail::to($customer->email)->send(new CustomerInvoiceSend($invoice, $selectedLanguage, $ccEmails));


            return redirect()->back()->with('success', __('Invoice successfully sent.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function payment($invoice_id)
    {
        if(\Auth::user()->can('create payment invoice'))
        {
            $invoice = Invoice::where('id', $invoice_id)->first();

            $customers  = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $categories = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $accounts   = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('invoice.payment', compact('customers', 'categories', 'accounts', 'invoice'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function createPayment(Request $request, $invoice_id)
    {

        if(\Auth::user()->can('create payment invoice'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'date' => 'required',
                                   'amount' => 'required',
                                   'account_id' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoicePayment                 = new InvoicePayment();
            $invoicePayment->invoice_id     = $invoice_id;
            $invoicePayment->date           = $request->date;
            $invoicePayment->amount         = $request->amount;
            $invoicePayment->account_id     = $request->account_id;
            $invoicePayment->payment_method = 0;
            $invoicePayment->reference      = $request->reference;
            $invoicePayment->description    = $request->description;
            if(!empty($request->add_receipt))
            {
                //storage limit
                $image_size = $request->file('add_receipt')->getSize();
                $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
                if($result==1)
                {
                    $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                    $request->add_receipt->storeAs('uploads/payment', $fileName);
                    $invoicePayment->add_receipt = $fileName;
                }

            }

            $invoicePayment->save();

            $invoice = Invoice::where('id', $invoice_id)->first();
            $due     = $invoice->getDue();
            $total   = $invoice->getTotal();
            if($invoice->status == 1)
            {
                $invoice->send_date = date('Y-m-d');
                $invoice->save();
            }

            if($due <= 0)
            {
                $invoice->status = 3;
                $invoice->save();
            }
            else
            {
                $invoice->status = 2;
                $invoice->save();
            }
            $invoicePayment->user_id    = $invoice->client_id;
            $invoicePayment->user_type  = 'Customer';
            $invoicePayment->type       = 'Partial';
            $invoicePayment->created_by = \Auth::user()->id;
            $invoicePayment->payment_id = $invoicePayment->id;
            $invoicePayment->category   = 'Invoice';
            $invoicePayment->account    = $request->account_id;

            Transaction::addTransaction($invoicePayment);
            $customer = Client::where('id', $invoice->client_id)->first();


            $payment            = new InvoicePayment();
            $payment->name      = $customer['name'];
            $payment->date      = \Auth::user()->dateFormat($request->date);
            $payment->amount    = \Auth::user()->priceFormat($request->amount);
            $payment->invoice   = 'invoice ' . \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
            $payment->dueAmount = \Auth::user()->priceFormat($invoice->getDue());

            Utility::updateUserBalance('client', $invoice->client_id, $request->amount, 'debit');

            Utility::bankAccountBalance($request->account_id, $request->amount, 'credit');

            // Send Email
            $setings = Utility::settings();
            if($setings['new_invoice_payment'] == 1)
            {
                $customer = Client::where('id', $invoice->client_id)->first();
                $invoicePaymentArr = [
                    'invoice_payment_name'   => $customer->name,
                    'invoice_payment_amount'   => $payment->amount,
                    'invoice_payment_date'  =>$payment->date,
                    'payment_dueAmount'  => $payment->dueAmount,

                ];

                $resp = Utility::sendEmailTemplate('new_invoice_payment', [$customer->id => $customer->email], $invoicePaymentArr);
            }

            //webhook
            $module ='New Invoice Payment';
            $webhook =  Utility::webhookSetting($module);
            if($webhook)
            {
                $parameter = json_encode($invoice);
                $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
                if($status == true)
                {
                    return redirect()->back()->with('success', __('Payment successfully added.') .((isset($result) && $result!=1) ? '<br> <span class="text-danger">' . $result . '</span>' : '').(($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : '') );

                }
                else
                {
                    return redirect()->back()->with('error', __('Webhook call failed.'));
                }
            }
            return redirect()->back()->with('success', __('Payment successfully added.') .((isset($result) && $result!=1) ? '<br> <span class="text-danger">' . $result . '</span>' : '').(($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : '') );

        }

    }

    public function paymentDestroy(Request $request, $invoice_id, $payment_id)
    {
//        dd($invoice_id,$payment_id);

        if(\Auth::user()->can('delete payment invoice'))
        {
            $payment = InvoicePayment::find($payment_id);

            InvoicePayment::where('id', '=', $payment_id)->delete();

            InvoiceBankTransfer::where('id', '=', $payment_id)->delete();

            $invoice = Invoice::where('id', $invoice_id)->first();
            $due     = $invoice->getDue();
            $total   = $invoice->getTotal();

            if($due > 0 && $total != $due)
            {
                $invoice->status = 3;

            }
            else
            {
                $invoice->status = 2;
            }

            if(!empty($payment->add_receipt))
            {
                //storage limit
                $file_path = '/uploads/payment/'.$payment->add_receipt;
                $result = Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);

            }

            $invoice->save();
            $type = 'Partial';
            $user = 'Customer';
            Transaction::destroyTransaction($payment_id, $type, $user);

            Utility::updateUserBalance('client', $invoice->client_id, $payment->amount, 'credit');

            Utility::bankAccountBalance($payment->account_id, $payment->amount, 'debit');

            return redirect()->back()->with('success', __('Payment successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function paymentReminder($invoice_id)
    {
        $invoice            = Invoice::find($invoice_id);
        $customer           = User::where('id', $invoice->client_id)->first();
        $invoice->dueAmount = \Auth:: user()->priceFormat($invoice->getDue());
        $invoice->name      = $customer['name'];
        $invoice->date      = \Auth::user()->dateFormat($invoice->send_date);
        $invoice->invoice   = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

        $selectedLanguage = request('language');
        $ccEmails = [];

        $ccEmailInput = request('cc_email');
        $ccEmails = preg_split('/[,\s]+/', $ccEmailInput);
        $ccEmails = array_unique(array_filter($ccEmails));

        Mail::to($customer->email)->send(new ReminderInvoiceSend($invoice, $ccEmails));


        // Send Email
        // $setings = Utility::settings();
        // if($setings['new_payment_reminder'] == 1)
        // {
        //     $invoice            = Invoice::find($invoice_id);
        //     $customer           = User::where('id', $invoice->client_id)->first();
        //     $invoice->dueAmount = \Auth:: user()->priceFormat($invoice->getDue());
        //     $invoice->name      = $customer['name'];
        //     $invoice->date      = \Auth::user()->dateFormat($invoice->send_date);
        //     $invoice->invoice   = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

        //     $reminderArr = [

        //         'payment_reminder_name'=> $invoice->name,
        //         'invoice_payment_number' =>$invoice->invoice,
        //         'invoice_payment_dueAmount'=>$invoice->dueAmount,
        //         'payment_reminder_date' => $invoice->date,

        //     ];

        //     Mail::to($customer->email)->send(new ReminderInvoiceSend($invoice, $ccEmails));

        // }
        //Twilio Notification
        $setting  = Utility::settings(\Auth::user()->creatorId());
        $customer = Client::find($invoice->client_id);
        if(isset($setting['twilio_reminder_notification']) && $setting['twilio_reminder_notification'] ==1)
        {
            $msg = __("New Payment Reminder of ").' '. \Auth::user()->invoiceNumberFormat($invoice->invoice_id).' '. __("created by").' ' .\Auth::user()->name.'.';
            Utility::send_twilio_msg($customer->contact,$msg);
        }

        return redirect()->back()->with('success', __('Payment reminder successfully send.'));
    }

    public function customerInvoiceSend($invoice_id)
    {
        return view('customer.invoice_send', compact('invoice_id'));
    }

    public function customerInvoiceSendMail(Request $request, $invoice_id)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'email' => 'required|email',
                           ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $email   = $request->email;
        $invoice = Invoice::where('id', $invoice_id)->first();

        $customer         = Customer::where('id', $invoice->client_id)->first();
        $invoice->name    = !empty($customer) ? $customer->name : '';
        $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

        $invoiceId    = Crypt::encrypt($invoice->id);
        $invoice->url = route('invoice.pdf', $invoiceId);

        try
        {
            Mail::to($email)->send(new CustomerInvoiceSend($invoice));
        }
        catch(\Exception $e)
        {
            $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
        }

        return redirect()->back()->with('success', __('Invoice successfully sent.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));

    }

    public function shippingDisplay(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if($request->is_display == 'true')
        {
            $invoice->shipping_display = 1;
        }
        else
        {
            $invoice->shipping_display = 0;
        }
        $invoice->save();

        return redirect()->back()->with('success', __('Shipping address status successfully changed.'));
    }

    public function duplicate($invoice_id)
    {
        if(\Auth::user()->can('duplicate invoice'))
        {
            $invoice                            = Invoice::where('id', $invoice_id)->first();
            $duplicateInvoice                   = new Invoice();
            $duplicateInvoice->invoice_id       = $invoice['invoice_id'];
            $duplicateInvoice->client_id      = $invoice['client_id'];
            $duplicateInvoice->user_id          = $invoice['user_id'];
            $duplicateInvoice->account_id       = $invoice['account_id'];
            $duplicateInvoice->company          = $invoice['company'];
            $duplicateInvoice->issue_date       = date('Y-m-d');
            $duplicateInvoice->due_date         = $invoice['due_date'];
            $duplicateInvoice->send_date        = null;
            $duplicateInvoice->category_id      = $invoice['category_id'];
            $duplicateInvoice->ref_number       = $invoice['ref_number'];
            $duplicateInvoice->status           = 0;
            $duplicateInvoice->shipping_display = $invoice['shipping_display'];
            $duplicateInvoice->created_by       = $invoice['created_by'];
            $duplicateInvoice->save();

            if($duplicateInvoice)
            {
                $invoiceProduct = InvoiceProduct::where('invoice_id', $invoice_id)->get();
                foreach($invoiceProduct as $product)
                {
                    $duplicateProduct             = new InvoiceProduct();
                    $duplicateProduct->invoice_id = $duplicateInvoice->id;
                    $duplicateProduct->product_id = $product->product_id;
                    $duplicateProduct->quantity   = $product->quantity;
                    $duplicateProduct->tax        = $product->tax;
                    $duplicateProduct->discount   = $product->discount;
                    $duplicateProduct->price      = $product->price;
                    $duplicateProduct->save();
                }
            }

            return redirect()->back()->with('success', __('Invoice duplicate successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function previewInvoice($template, $color)
    {
        date_default_timezone_set('Asia/Jakarta');

        $objUser  = \Auth::user();
        $settings = Utility::settings();
        $invoice  = new Invoice();
        $invoices  = new Invoice();

        $kode_invoice = now()->format('Y-m-d') . "/";

        // $customer                   = new \stdClass();
        // $customer->email            = '<Email>';
        // $customer->shipping_name    = '<Customer Name>';
        // $customer->position         = '<Customer Position>';
        // $customer->shipping_country = '<Country>';
        // $customer->shipping_state   = '<State>';
        // $customer->shipping_city    = '<City>';
        // $customer->shipping_phone   = '<Customer Phone Number>';
        // $customer->shipping_zip     = '<Zip>';
        // $customer->shipping_address = '<Address>';
        // $customer->billing_name     = '<Customer Name>';
        // $customer->billing_country  = '<Country>';
        // $customer->billing_state    = '<State>';
        // $customer->billing_city     = '<City>';
        // $customer->billing_phone    = '<Customer Phone Number>';
        // $customer->billing_zip      = '<Zip>';
        // $customer->billing_address  = '<Address>';

        $client                     = new \stdClass();
        $client->name               = '<Client Name>';

        $clients                     = new \stdClass();
        $clients->name_invoice      = '<Attention>';
        $clients->position          = '<Attention Position>';
        $clients->shipping_zip      = '<Zip>';
        $clients->address           = '<Address>';
        $clients->country           = '<Country>';
        $clients->state             = '<State>';
        $clients->city              = '<City>';

        $totalTaxPrice = 0;
        $taxesData     = [];

        $items = [];
        for($i = 1; $i <= 1; $i++)
        {
            $item           = new \stdClass();
            $item->name     = 'Item ' . $i;
            $item->quantity = 1;
            $item->tax      = 5;
            $item->discount = 50;
            $item->price    = 1000000;
            $item->description  = "AUR/EL/MJ/XII/2022/080";

            $taxes = [
                'Tax 1',
                'Tax 2',
            ];

            $itemTaxes = [];
            
            foreach($taxes as $k => $tax)
            {
                $taxPrice         = 10;
                $totalTaxPrice    += $taxPrice;
                $itemTax['name']  = 'Tax ' . $k;
                $itemTax['rate']  = '10 %';
                $itemTax['price'] = '$10';
                $itemTaxes[]      = $itemTax;
                if(array_key_exists('Tax ' . $k, $taxesData))
                {
                    $taxesData['Tax ' . $k] = $taxesData['Tax 1'] + $taxPrice;
                }
                else
                {
                    $taxesData['Tax ' . $k] = $taxPrice;
                }
            }
            $item->itemTax = $itemTaxes;
            $items[]       = $item;
        }

        $invoice->invoice_id = "#AUR/FIN/2022-12-16/001";
        $invoice->issue_date = date('Y-m-d H:i:s');
        $invoice->due_date   = date('Y-m-d H:i:s');
        $invoice->itemData   = $items;

        $invoice->totalTaxPrice = 60;
        $invoice->totalQuantity = 1;
        $invoice->totalRate     = 1000000;
        $invoice->totalDiscount = 10;
        $invoice->taxesData     = $taxesData;
        $invoice->created_by     = $objUser->creatorId();

        $invoice->customField   = [];
        $customFields           = [];

        $preview    = 1;
        $color      = '#000000';
        $font_color = Utility::getFontColor($color);


        $logo         = asset(Storage::url('uploads/logo/'));
        $invoice_logo = Utility::getValByName('invoice_logo');
        $company_logo = \App\Models\Utility::GetLogo();
        $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        
        // if(isset($invoice_logo) && !empty($invoice_logo))
        // {
        //     $img          = asset(\Storage::url('uploads/invoice_logo').'/'. $invoice_logo);
        // }

        // else{
        //     $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));

        // }

        $logo_kap = asset(\Storage::url('logo').'/logo-kap.png');
        $logo_ara = asset(\Storage::url('logo').'/ara.png');
        $logo_xga = asset(\Storage::url('logo').'/XGA.png');
        $ttd      = asset(\Storage::url('ttd').'/ttd.png');
        $ttd_mj      = asset(\Storage::url('ttd').'/ttd-mj.png');
        $ttd_ara      = asset(\Storage::url('ttd').'/ttd-ara.png');
        $ttd_xga      = asset(\Storage::url('ttd').'/ttd-xga.png');




        return view('invoice.templates.' . $template, compact('invoice', 'invoices', 'kode_invoice', 'preview', 'color', 'img','logo_ara','logo_xga', 'logo_kap', 'ttd','ttd_ara','ttd_xga','ttd_mj', 'settings','client','clients', 'font_color', 'customFields'));
    }

    public function invoice($invoice_id)
    {
        $settings = Utility::settings();

        date_default_timezone_set('Asia/Jakarta');
        $kode_invoice = now()->format('Y-m-d') . "/";

        $invoiceId = Crypt::decrypt($invoice_id);
        $invoice   = Invoice::where('id', $invoiceId)->first();

        $data  = DB::table('settings');
        $data  = $data->where('created_by', '=', $invoice->created_by);
        $data1 = $data->get();

        foreach($data1 as $row)
        {
            $settings[$row->name] = $row->value;
        }

        $client      = $invoice->client;
        $clients     = Client::where('user_id', $client->id)->first();
        $itemss        = $invoice->items;
        foreach($itemss as $products)
        {
            $invoices = $products->products;
        }
        $items         = [];
        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate     = 0;
        $totalDiscount = 0;
        $totalTax      = 0;
        $taxesData     = [];
        foreach($itemss as $product)
        {
            $item              = new \stdClass();
            $item->name        = !empty($product->product()) ? $product->product()->name : '';
            $item->quantity    = $product->quantity;
            $item->tax         = $product->tax;
            $item->discount    = $product->discount;
            $item->price       = $product->price;
            $item->description = $product->description;

            $totalRate     += $item->price;
            $totalTax      += $item->tax;

            $items[] = $item;
        }

        $invoice->itemData      = $items;
        $invoice->totalTaxPrice = $totalTaxPrice;
        $invoice->totalQuantity = $totalQuantity;
        $invoice->totalRate     = $totalRate;
        $invoice->totalDiscount = $totalDiscount;
        $invoice->taxesData     = $taxesData;
        $invoice->ref_number    = 21564626;
        $invoice->customField   = CustomField::getData($invoice, 'invoice');
        $customFields           = [];
        if(!empty(\Auth::user()))
        {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
        }


       //Set your logo
        $logo         = asset(Storage::url('uploads/logo/'));
        $invoice_logo = Utility::getValByName('invoice_logo');
        $company_logo = \App\Models\Utility::GetLogo();
        $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        // if(isset($invoice_logo) && !empty($invoice_logo))
        // {
        //     $img          = asset(\Storage::url('uploads/invoice_logo').'/'. $invoice_logo);
        // }

        // else{
        //     $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));

        // }

        $logo         = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        // $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));

        $logo_kap = asset(\Storage::url('logo').'/logo-kap.png');
        $logo_ara = asset(\Storage::url('logo').'/ara.png');
        $logo_xga = asset(\Storage::url('logo').'/XGA.png');
        $ttd      = asset(\Storage::url('ttd').'/ttd.png');
        $ttd_mj      = asset(\Storage::url('ttd').'/ttd-mj.png');
        $ttd_ara      = asset(\Storage::url('ttd').'/ttd-ara.png');
        $ttd_xga      = asset(\Storage::url('ttd').'/ttd-xga.png');


        if($invoice)
        {
            $color      = '#000000';
            $font_color = Utility::getFontColor($color);

            if($invoice->invoice_template == "template1")
            {
                $settings['invoice_template'] = 'template1'; 
            }
            elseif($invoice->invoice_template == "template4")
            {
                $settings['invoice_template'] = 'template4'; 
            }
            elseif($invoice->invoice_template == "template5")
            {
                $settings['invoice_template'] = 'template5'; 
            }
            elseif($invoice->invoice_template == "template6")
            {
                $settings['invoice_template'] = 'template6'; 
            }
            elseif($invoice->invoice_template == "template7")
            {
                $settings['invoice_template'] = 'template7'; 
            }
            elseif($invoice->invoice_template == "template8")
            {
                $settings['invoice_template'] = 'template8'; 
            }
            elseif($invoice->invoice_template == "template9")
            {
                $settings['invoice_template'] = 'template9'; 
            }
            elseif($invoice->invoice_template == "template10")
            {
                $settings['invoice_template'] = 'template10'; 
            }
            elseif($invoice->invoice_template == "template11")
            {
                $settings['invoice_template'] = 'template11'; 
            }
            elseif($invoice->invoice_template == "template12")
            {
                $settings['invoice_template'] = 'template12'; 
            }
            elseif($invoice->invoice_template == "template13")
            {
                $settings['invoice_template'] = 'template13'; 
            }
            elseif($invoice->invoice_template == "template14")
            {
                $settings['invoice_template'] = 'template14'; 
            }

            return view('invoice.templates.' . $settings['invoice_template'], compact('invoice', 'invoices', 'kode_invoice', 'color', 'settings', 'client','clients', 'img','logo_kap','logo_xga','logo_ara', 'ttd', 'ttd_ara','ttd_xga','ttd_mj', 'font_color', 'customFields'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function saveTemplateSettings(Request $request)
    {

        $validator = \Validator::make(
            $request->all(), [
                               'invoice_logo' => 'mimes:png|max:20480',
                           ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $post = $request->all();
        unset($post['_token']);

        if(isset($post['invoice_template']) && (!isset($post['invoice_color']) || empty($post['invoice_color'])))
        {
            $post['invoice_color'] = "ffffff";
        }

        if($request->hasFile('invoice_logo'))
        {
            //storage limit
            $invoice_logo = $request->file('invoice_logo')->getSize();
            $imageName = \Auth::user()->id . '_invoice_logo' . '.png';
            $request->file('invoice_logo')->storeAs('invoice_logo', $imageName);
            $post['invoice_logo']      = $imageName;
        }

        if($request->hasFile('invoice_logo'))
        {
            $filenameWithExt = $request->file('invoice_logo')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('invoice_logo')->getClientOriginalExtension();
            $fileNameToStore = \Auth::user()->id . '_' . $filename . '_' . time() . '.' . $extension;

            $dir        = 'uploads/invoice_logo/';

            $image_path = $dir . \Auth::user()->id . '_' . $fileNameToStore;

            if(File::exists($image_path))
            {
                File::delete($image_path);
            }

            $path = Utility::upload_file($request,'invoice_logo',$fileNameToStore,$dir,[]);

            if($path['flag']==0)
                {
                    return redirect()->back()->with('error', __($path['msg']));
                }
                $post['invoice_logo'] = $fileNameToStore;
            }



        // if($request->invoice_logo)
        // {
        //     $dir = 'invoice_logo/';
        //     $invoice_logo = \Auth::user()->id . '_invoice_logo.png';
        //     $validation =[
        //         'max:'.'20480',
        //     ];
        //     $path = Utility::upload_file($request,'invoice_logo',$invoice_logo,$dir,$validation);

        //     if($path['flag']==0)
        //     {
        //         return redirect()->back()->with('error', __($path['msg']));
        //     }
        //     $post['invoice_logo'] = $invoice_logo;
        // }

        foreach($post as $key => $data)
        {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                                                                                                                                             $data,
                                                                                                                                             $key,
                                                                                                                                             \Auth::user()->creatorId(),
                                                                                                                                         ]
            );
        }

        return redirect()->back()->with('success', __('Invoice Setting updated successfully'));
    }

    public function items(Request $request)
    {
        $items = InvoiceProduct::where('invoice_id', $request->invoice_id)->where('product_id', $request->product_id)->first();

        return json_encode($items);
    }

    public function invoiceLink($invoiceId)
    {
        try {
            $id       = Crypt::decrypt($invoiceId);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Invoice Not Found.'));
        }


        $id             = Crypt::decrypt($invoiceId);
        $invoice        = Invoice::find($id);
        if(!empty($invoice))
        {

            $user_id        = $invoice->created_by;
            $user           = User::find($user_id);
            $invoicePayment = InvoicePayment::where('invoice_id', $invoice->id)->get();
            $customer = $invoice->client;
            $client   = Client::where('user_id', $customer->id)->first();
            $iteams   = $invoice->items;
            $invoice->customField = CustomField::getData($invoice, 'invoice');
            $customFields         = CustomField::where('module', '=', 'invoice')->get();
            $company_payment_setting = Utility::getCompanyPaymentSetting($user_id);

            // start for storage limit note
            $invoice_user = User::find($invoice->created_by);
            // $user_plan = Plan::find($invoice_user->plan);
            // end for storage limit note


            return view('invoice.customer_invoice', compact('invoice', 'customer','client', 'iteams', 'invoicePayment', 'customFields', 'user','company_payment_setting','invoice_user'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }

    public function export()
    {
        $name = 'invoice_' . date('Y-m-d i:h:s');
        $data = Excel::download(new InvoiceExport(), $name . '.xlsx'); ob_end_clean();

        return $data;
    }

    public function languages($id)
    {
        $invoice = Invoice::find($id);
        return view('invoice.languages', compact('invoice'));
    }

    public function ccEmail($id)
    {
        $invoice = Invoice::find($id);
        return view('invoice.ccemail', compact('invoice'));
    }


    public function recentlanguages($id)
    {
        $invoice = Invoice::find($id);
        return view('invoice.recentlanguages', compact('invoice'));
    }

    private function getMonthlyData($invoices)
    {
        $monthlyData = [];

        foreach ($invoices as $invoice) {
            $issueMonth = Carbon::parse($invoice->issue_date)->format('F Y');

            if (!isset($monthlyData[$issueMonth])) {
                $monthlyData[$issueMonth] = 0;
            }

            $monthlyData[$issueMonth] += $invoice->getDue();
        }

        return $monthlyData;
    }

    public function changeStatus($invoiceId, $status)
    {
        $invoice = Invoice::find($invoiceId);

        if (Auth::user()->can('edit invoice')) {

            if (in_array($status, [0, 1, 2, 3, 4])) {

                $client = $invoice->client;
               
                $invoice->status = $status;
                $invoice->save();

                if ($status == 3) {
                    Mail::to($client->email)->send(new CustomerInvoiceThanksSend($invoice));
                }

                return redirect()->back()->with('success', __('Invoice status has been updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Invalid status.'));
            }
        } else {
            return redirect()->back()->with('error', __('You do not have permission to change the invoice status.'));
        }
    }

    public function convertToRevenue(Request $request)
    {

        $selectedIds = $request->input('selectedIds');

        foreach ($selectedIds as $invoiceId) {

            $invoice = Invoice::find($invoiceId);

            $date = date('Y-m-d', strtotime($invoice->updated_at));

            $revenue                 = new Revenue();
            $revenue->invoice_id     = $invoice->id;
            $revenue->date           = $date;
            $revenue->amount         = round($invoice->getDue());
            $revenue->description    = 'Revenue From Invoice ' . $invoice->invoice_id;
            $revenue->user_id        = $invoice->user_id;
            $revenue->created_by     = \Auth::user()->creatorId();
            $revenue->save();
        }

        return redirect()->route('invoice.index')->with('success', __('Invoice successfully convert to Balance Partners.'));
    }


}

