<?php

namespace App\Http\Controllers;

use App\Exports\AccountStatementExport;
use App\Exports\LeaveReportExport;
use App\Exports\PayrollExport;
use App\Exports\ProductStockExport;
use App\Exports\Overtime;
use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\Branch;
use App\Models\ClientDeal;
use App\Models\Deal;
use App\Models\UserOvertime;
use App\Models\Department;
use App\Models\ProjectUser;
use App\Models\Employee;
use App\Models\Meeting;
use App\Models\Lead;
use App\Models\Leave;
use App\Models\PaySlip;
use App\Models\AttendanceEmployee;
use App\Models\BillProduct;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\JournalItem;
use App\Models\Payment;
use App\Models\Pipeline;
use App\Models\ProductServiceCategory;
use App\Models\Revenue;
use App\Models\Source;
use App\Models\StockReport;
use App\Models\User;
use App\Models\UserDeal;
use App\Models\Utility;
use App\Models\Tax;
use App\Models\Timesheet;
use App\Models\LeaveType;
use App\Models\Reimbursment;
use App\Models\BankTransfer;
use App\Models\AppraisalEmployee;
use App\Models\Vender;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProjectsExport;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function incomeSummary(Request $request)
    {
        if(\Auth::user()->can('income report'))
        {
            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend('select Account', '');
            $client = User::where('type','=','client')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $client->prepend('Select Client', '');
            $partner = User::where('type','=','partners')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $partner->prepend('Select Partner', '');
            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 1)->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            $currencyList = ['Rp' => 'Rp', '$' => '$'];

            $data['monthList']  = $month = $this->yearMonth();
            $data['yearList']   = $this->yearList();
            $filter['category'] = __('All');
            $filter['client'] = __('All');


            if(isset($request->year))
            {
                $year = $request->year;
            }
            else
            {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            // ------------------------------REVENUE INCOME-----------------------------------
            // $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            // $incomes->where('revenues.created_by', '=', \Auth::user()->creatorId());
            // $incomes->whereRAW('YEAR(date) =?', [$year]);

            // if(!empty($request->category))
            // {
            //     $incomes->where('category_id', '=', $request->category);
            //     $cat                = ProductServiceCategory::find($request->category);
            //     $filter['category'] = !empty($cat) ? $cat->name : '';
            // }

            // if(!empty($request->customer))
            // {
            //     $incomes->where('customer_id', '=', $request->customer);
            //     $cust               = Customer::find($request->customer);
            //     $filter['customer'] = !empty($cust) ? $cust->name : '';
            // }
            // $incomes->groupBy('month', 'year');
            // $incomes = $incomes->get();

            // $tmpArray = [];
            // foreach($incomes as $income)
            // {
            //     $tmpArray[$income->category_id][$income->month] = $income->amount;
            // }
            // $array = [];
            // foreach($tmpArray as $cat_id => $record)
            // {
            //     $tmp             = [];
            //     $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
            //     $tmp['data']     = [];
            //     for($i = 1; $i <= 12; $i++)
            //     {
            //         $tmp['data'][$i] = array_key_exists($i, $record) ? $record[$i] : 0;
            //     }
            //     $array[] = $tmp;
            // }

            //---------------------------INVOICE INCOME-----------------------------------------------

            $invoices = Invoice::selectRaw('MONTH(send_date) as month, YEAR(send_date) as year, category_id, invoice_id, id, currency')
            ->where('status', '=', 3);

            $invoices->whereRAW('YEAR(send_date) =?', [$year]);

            if(!empty($request->client))
            {
                $invoices->where('client_id', '=', $request->client);
            }

            if(!empty($request->user_id))
            {
                $invoices->where('user_id', '=', $request->user_id);
            }

            if(!empty($request->category))
            {
                $invoices->where('category_id', '=', $request->category);
            }

            $invoices        = $invoices->get();
            $invoiceTmpArray = [];
            foreach($invoices as $invoice)
            {
                $categoryIds = explode(',', $invoice->category_id);
                foreach ($categoryIds as $categoryId) {
                    $invoiceTmpArray[$categoryId][$invoice->month][] = $invoice->getTotal();
                }
            }


            $invoiceArray = [];
            foreach ($invoiceTmpArray as $cat_id => $record) {
                $invoice             = [];
                $invoice['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $invoice['data']     = [];
            
                for ($i = 1; $i <= 12; $i++) {
                    $invoice['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
            
                $invoiceArray[] = $invoice;
            }

            $invoiceTotalArrayRp = [];
            $invoiceTotalArrayUsd = [];
            $totalInvoiceRp = 0;
            $totalInvoiceDollar = 0;
            

            foreach($invoices as $invoice)
            {
                if($invoice->currency == 'Rp') {
                    $invoiceTotalArrayRp[$invoice->month][] = $invoice->getTotal();
                    $totalInvoiceRp += $invoice->getTotal();
                } elseif($invoice->currency == '$') {
                    $invoiceTotalArrayUsd[$invoice->month][] = $invoice->getTotal();
                    $totalInvoiceDollar += $invoice->getTotal();
                }
            }


            $invoiceTotalRp = [];
            $invoiceTotalUsd = [];

            for($i = 1; $i <= 12; $i++)
            {
                $invoiceTotalRp[] = array_key_exists($i, $invoiceTotalArrayRp) ? array_sum($invoiceTotalArrayRp[$i]) : 0;
                $invoiceTotalUsd[] = array_key_exists($i, $invoiceTotalArrayUsd) ? array_sum($invoiceTotalArrayUsd[$i]) : 0;
            }


            $chartIncomeArrRp = array_map(
                function (){
                    return array_sum(func_get_args());
                }, $invoiceTotalRp
            );

            $chartIncomeArrUsd = array_map(
                function (){
                    return array_sum(func_get_args());
                }, $invoiceTotalUsd
            );

            $data['chartIncomeArrRp'] = $chartIncomeArrRp;
            $data['chartIncomeArrUsd'] = $chartIncomeArrUsd;
            $data['totalInvoiceRp'] = $totalInvoiceRp;
            $data['totalInvoiceDollar'] = $totalInvoiceDollar;
            // $data['incomeArr']      = $array;
            $data['invoiceArray']   = $invoiceArray;
            $data['account']        = $account;
            $data['client']         = $client;
            $data['partner']        = $partner;
            $data['category']       = $category;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange']   = 'Dec-' . $year;


            return view('report.income_summary', compact('filter','currencyList'), $data);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }

    public function expenseSummary(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('expense report'))
        {
            $account = BankAccount::all()->pluck('holder_name', 'id');
            $account->prepend('Select Account', '');
            $vender = Vender::all()->pluck('name', 'id');
            $vender->prepend('Select Vendor', '');
            $category = ProductServiceCategory::where('type', '=', 2)->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');
            $partner = User::where('type','=','partners')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $partner->prepend('Select Partner', '');

            $data['monthList']  = $month = $this->yearMonth();
            $data['yearList']   = $this->yearList();
            $filter['category'] = __('All');
            $filter['vender']   = __('All');

            if(isset($request->year))
            {
                $year = $request->year;
            }
            else
            {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            //   -----------------------------------------PAYMENT EXPENSE ------------------------------------------------------------
            $expensesData = Payment::selectRaw('sum(payments.amount) as amount, MONTH(date) as month, YEAR(date) as year, currency')
            ->whereYear('date', '=', $year);

            if (!empty($request->category)) {
                $expensesData->where('category_id', '=', $request->category);
            }

            if (!empty($request->vender)) {
                $expensesData->where('vender_id', '=', $request->vender);
            }

            if (!empty($request->user_id)) {
                $expensesData->where('user_id', '=', $request->user_id);
            }

            $expensesData->groupBy('month', 'year', 'currency');
            $expensesData = $expensesData->get();

            $expenseArr = [];
            foreach ($expensesData as $expenseData) {
                $expenseArr[$expenseData->month][$expenseData->currency] = $expenseData->amount;
            }

            $expenseTotal = [];
            for ($i = 1; $i <= 12; $i++) {
                $expenseTotal[$i]['Rp'] = $expenseArr[$i]['Rp'] ?? 0;
                $expenseTotal[$i]['€'] = $expenseArr[$i]['€'] ?? 0;
                $expenseTotal[$i]['S$'] = $expenseArr[$i]['S$'] ?? 0;
            }

            $billTotalArrayRp = [];
            $billTotalArrayEuro = [];
            $billTotalArraySgd = [];
            $totalBillRp = 0;
            $totalBillEuro = 0;
            $totalBillSgd = 0;

            foreach ($expensesData as $bill) {
                if($bill->currency == 'Rp') {
                    $billTotalArrayRp[$bill->month][] = $bill->amount;
                    $totalBillRp += $bill->amount;
                } elseif($bill->currency == '€') {
                    $billTotalArrayEuro[$bill->month][] = $bill->amount;
                    $totalBillEuro += $bill->amount;
                } elseif($bill->currency == 'S$') {
                    $billTotalArraySgd[$bill->month][] = $bill->amount;
                    $totalBillSgd += $bill->amount;
                } 
            }


            $billTotalRp = [];
            $billTotalEuro = [];
            $billTotalSgd = [];

            for($i = 1; $i <= 12; $i++)
            {
                $billTotalRp[] = array_key_exists($i, $billTotalArrayRp) ? array_sum($billTotalArrayRp[$i]) : 0;
                $billTotalEuro[] = array_key_exists($i, $billTotalArrayEuro) ? array_sum($billTotalArrayEuro[$i]) : 0;
                $billTotalSgd[] = array_key_exists($i, $billTotalArraySgd) ? array_sum($billTotalArraySgd[$i]) : 0;
            }

            $chartExpenseArrRp = array_map(
                function (){
                    return array_sum(func_get_args());
                }, $billTotalRp
            );

            $chartExpenseArrEuro = array_map(
                function (){
                    return array_sum(func_get_args());
                }, $billTotalEuro
            );

            $chartExpenseArrSgd = array_map(
                function (){
                    return array_sum(func_get_args());
                }, $billTotalSgd
            );


            $data['chartExpenseArrRp'] = $chartExpenseArrRp;
            $data['chartExpenseArrEuro'] = $chartExpenseArrEuro;
            $data['chartExpenseArrSgd'] = $chartExpenseArrSgd;
            $data['totalBillRp'] = $totalBillRp;
            $data['totalBillEuro'] = $totalBillEuro;
            $data['totalBillSgd'] = $totalBillSgd;
            $data['account']         = $account;
            $data['vender']          = $vender;
            $data['category']        = $category;
            $data['partner']        = $partner;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange']   = 'Dec-' . $year;

            return view('report.expense_summary', compact('filter'), $data);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }

    public function incomeVsExpenseSummary(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('income vs expense report'))
        {
            if($user->type == 'admin' || $user->type == 'company'){
                $account = BankAccount::all()->pluck('holder_name', 'id');
                $account->prepend('Select Account', '');
                $vender = Vender::all()->pluck('name', 'id');
                $vender->prepend('Select Vendor', '');
                $customer = Customer::all()->pluck('name', 'id');
                $customer->prepend('Select Customer', '');
    
                $category = ProductServiceCategory::whereIn(
                    'type', [
                              1,
                              2,
                          ]
                )->get()->pluck('name', 'id');
                $category->prepend('Select Category', '');
    
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList']  = $this->yearList();
    
                $filter['category'] = __('All');
                $filter['customer'] = __('All');
                $filter['vender']   = __('All');
    
                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;
    
                // ------------------------------TOTAL PAYMENT EXPENSE-----------------------------------------------------------
                $expensesData = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $expensesData->get();
                $expensesData->whereRAW('YEAR(date) =?', [$year]);
    
                if(!empty($request->category))
                {
                    $expensesData->where('category_id', '=', $request->category);
                    $cat                = ProductServiceCategory::find($request->category);
                    $filter['category'] = !empty($cat) ? $cat->name : '';
    
                }
                if(!empty($request->vender))
                {
                    $expensesData->where('vender_id', '=', $request->vender);
    
                    $vend             = Vender::find($request->vender);
                    $filter['vender'] = !empty($vend) ? $vend->name : '';
                }
                $expensesData->groupBy('month', 'year');
                $expensesData = $expensesData->get();
    
                $expenseArr = [];
                foreach($expensesData as $k => $expenseData)
                {
                    $expenseArr[$expenseData->month] = $expenseData->amount;
                }
    
                // ------------------------------TOTAL BILL EXPENSE-----------------------------------------------------------
    
                $bills = Bill:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('status', '!=', 0);
                $bills->whereRAW('YEAR(send_date) =?', [$year]);
    
                if(!empty($request->vender))
                {
                    $bills->where('vender_id', '=', $request->vender);
    
                }
    
                if(!empty($request->category))
                {
                    $bills->where('category_id', '=', $request->category);
                }
    
                $bills        = $bills->get();
                $billTmpArray = [];
                foreach($bills as $bill)
                {
                    $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
                }
                $billArray = [];
                foreach($billTmpArray as $cat_id => $record)
                {
                    $bill             = [];
                    $bill['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $bill['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {
    
                        $bill['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
                    $billArray[] = $bill;
                }
    
                $billTotalArray = [];
                foreach($bills as $bill)
                {
                    $billTotalArray[$bill->month][] = $bill->getTotal();
                }
    
    
                // ------------------------------TOTAL REVENUE INCOME-----------------------------------------------------------
    
                $incomesData = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $incomesData->get();
                $incomesData->whereRAW('YEAR(date) =?', [$year]);
    
                if(!empty($request->category))
                {
                    $incomesData->where('category_id', '=', $request->category);
                }
                if(!empty($request->customer))
                {
                    $incomesData->where('customer_id', '=', $request->customer);
                    $cust               = Customer::find($request->customer);
                    $filter['customer'] = !empty($cust) ? $cust->name : '';
                }
                $incomesData->groupBy('month', 'year');
                $incomesData = $incomesData->get();
                $incomeArr   = [];
                foreach($incomesData as $k => $incomeData)
                {
                    $incomeArr[$incomeData->month] = $incomeData->amount;
                }
    
                // ------------------------------TOTAL INVOICE INCOME-----------------------------------------------------------
                $invoices = Invoice:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('status', '!=', 0);
                $invoices->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $invoices->where('customer_id', '=', $request->customer);
                }
                if(!empty($request->category))
                {
                    $invoices->where('category_id', '=', $request->category);
                }
                $invoices        = $invoices->get();
                $invoiceTmpArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
                }
    
                $invoiceArray = [];
                foreach($invoiceTmpArray as $cat_id => $record)
                {
    
                    $invoice             = [];
                    $invoice['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $invoice['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {
    
                        $invoice['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
                    $invoiceArray[] = $invoice;
                }
    
                $invoiceTotalArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
                }
                //        ----------------------------------------------------------------------------------------------------
    
                for($i = 1; $i <= 12; $i++)
                {
                    $paymentExpenseTotal[] = array_key_exists($i, $expenseArr) ? $expenseArr[$i] : 0;
                    $billExpenseTotal[]    = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
    
                    $RevenueIncomeTotal[] = array_key_exists($i, $incomeArr) ? $incomeArr[$i] : 0;
                    $invoiceIncomeTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
    
                }
    
                $totalIncome = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $RevenueIncomeTotal, $invoiceIncomeTotal
                );
    
                $totalExpense = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $paymentExpenseTotal, $billExpenseTotal
                );
    
                $profit = [];
                $keys   = array_keys($totalIncome + $totalExpense);
                foreach($keys as $v)
                {
                    $profit[$v] = (empty($totalIncome[$v]) ? 0 : $totalIncome[$v]) - (empty($totalExpense[$v]) ? 0 : $totalExpense[$v]);
                }
    
    
                $data['paymentExpenseTotal'] = $paymentExpenseTotal;
                $data['billExpenseTotal']    = $billExpenseTotal;
                $data['revenueIncomeTotal']  = $RevenueIncomeTotal;
                $data['invoiceIncomeTotal']  = $invoiceIncomeTotal;
                $data['profit']              = $profit;
                $data['account']             = $account;
                $data['vender']              = $vender;
                $data['customer']            = $customer;
                $data['category']            = $category;
    
                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;
    
                return view('report.income_vs_expense_summary', compact('filter'), $data);
            }else{
                $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
                $account->prepend('Select Account', '');
                $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $vender->prepend('Select Vendor', '');
                $customer = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $customer->prepend('Select Customer', '');
    
                $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->whereIn(
                    'type', [
                              1,
                              2,
                          ]
                )->get()->pluck('name', 'id');
                $category->prepend('Select Category', '');
    
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList']  = $this->yearList();
    
                $filter['category'] = __('All');
                $filter['customer'] = __('All');
                $filter['vender']   = __('All');
    
                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;
    
                // ------------------------------TOTAL PAYMENT EXPENSE-----------------------------------------------------------
                $expensesData = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $expensesData->where('payments.created_by', '=', \Auth::user()->creatorId());
                $expensesData->whereRAW('YEAR(date) =?', [$year]);
    
                if(!empty($request->category))
                {
                    $expensesData->where('category_id', '=', $request->category);
                    $cat                = ProductServiceCategory::find($request->category);
                    $filter['category'] = !empty($cat) ? $cat->name : '';
    
                }
                if(!empty($request->vender))
                {
                    $expensesData->where('vender_id', '=', $request->vender);
    
                    $vend             = Vender::find($request->vender);
                    $filter['vender'] = !empty($vend) ? $vend->name : '';
                }
                $expensesData->groupBy('month', 'year');
                $expensesData = $expensesData->get();
    
                $expenseArr = [];
                foreach($expensesData as $k => $expenseData)
                {
                    $expenseArr[$expenseData->month] = $expenseData->amount;
                }
    
                // ------------------------------TOTAL BILL EXPENSE-----------------------------------------------------------
    
                $bills = Bill:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
                $bills->whereRAW('YEAR(send_date) =?', [$year]);
    
                if(!empty($request->vender))
                {
                    $bills->where('vender_id', '=', $request->vender);
    
                }
    
                if(!empty($request->category))
                {
                    $bills->where('category_id', '=', $request->category);
                }
    
                $bills        = $bills->get();
                $billTmpArray = [];
                foreach($bills as $bill)
                {
                    $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
                }
                $billArray = [];
                foreach($billTmpArray as $cat_id => $record)
                {
                    $bill             = [];
                    $bill['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $bill['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {
    
                        $bill['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
                    $billArray[] = $bill;
                }
    
                $billTotalArray = [];
                foreach($bills as $bill)
                {
                    $billTotalArray[$bill->month][] = $bill->getTotal();
                }
    
    
                // ------------------------------TOTAL REVENUE INCOME-----------------------------------------------------------
    
                $incomesData = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $incomesData->where('revenues.created_by', '=', \Auth::user()->creatorId());
                $incomesData->whereRAW('YEAR(date) =?', [$year]);
    
                if(!empty($request->category))
                {
                    $incomesData->where('category_id', '=', $request->category);
                }
                if(!empty($request->customer))
                {
                    $incomesData->where('customer_id', '=', $request->customer);
                    $cust               = Customer::find($request->customer);
                    $filter['customer'] = !empty($cust) ? $cust->name : '';
                }
                $incomesData->groupBy('month', 'year');
                $incomesData = $incomesData->get();
                $incomeArr   = [];
                foreach($incomesData as $k => $incomeData)
                {
                    $incomeArr[$incomeData->month] = $incomeData->amount;
                }
    
                // ------------------------------TOTAL INVOICE INCOME-----------------------------------------------------------
                $invoices = Invoice:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
                $invoices->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $invoices->where('customer_id', '=', $request->customer);
                }
                if(!empty($request->category))
                {
                    $invoices->where('category_id', '=', $request->category);
                }
                $invoices        = $invoices->get();
                $invoiceTmpArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
                }
    
                $invoiceArray = [];
                foreach($invoiceTmpArray as $cat_id => $record)
                {
    
                    $invoice             = [];
                    $invoice['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $invoice['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {
    
                        $invoice['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
                    $invoiceArray[] = $invoice;
                }
    
                $invoiceTotalArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
                }
                //        ----------------------------------------------------------------------------------------------------
    
                for($i = 1; $i <= 12; $i++)
                {
                    $paymentExpenseTotal[] = array_key_exists($i, $expenseArr) ? $expenseArr[$i] : 0;
                    $billExpenseTotal[]    = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
    
                    $RevenueIncomeTotal[] = array_key_exists($i, $incomeArr) ? $incomeArr[$i] : 0;
                    $invoiceIncomeTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
    
                }
    
                $totalIncome = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $RevenueIncomeTotal, $invoiceIncomeTotal
                );
    
                $totalExpense = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $paymentExpenseTotal, $billExpenseTotal
                );
    
                $profit = [];
                $keys   = array_keys($totalIncome + $totalExpense);
                foreach($keys as $v)
                {
                    $profit[$v] = (empty($totalIncome[$v]) ? 0 : $totalIncome[$v]) - (empty($totalExpense[$v]) ? 0 : $totalExpense[$v]);
                }
    
    
                $data['paymentExpenseTotal'] = $paymentExpenseTotal;
                $data['billExpenseTotal']    = $billExpenseTotal;
                $data['revenueIncomeTotal']  = $RevenueIncomeTotal;
                $data['invoiceIncomeTotal']  = $invoiceIncomeTotal;
                $data['profit']              = $profit;
                $data['account']             = $account;
                $data['vender']              = $vender;
                $data['customer']            = $customer;
                $data['category']            = $category;
    
                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;
    
                return view('report.income_vs_expense_summary', compact('filter'), $data);        
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function taxSummary(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('tax report'))
        {
            if($user->type = 'admin'){
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList']  = $this->yearList();
                $data['taxList']   = $taxList = Tax::all();
    
                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
    
                $data['currentYear'] = $year;
    
                $invoiceProducts = InvoiceProduct::selectRaw('invoice_products.* ,MONTH(invoice_products.created_at) as month,YEAR(invoice_products.created_at) as year')->leftjoin('product_services', 'invoice_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(invoice_products.created_at) =?', [$year])->get();
    
                $incomeTaxesData = [];
                foreach($invoiceProducts as $invoiceProduct)
                {
                    $incomeTax   = [];
                    $incomeTaxes = Utility::tax($invoiceProduct->tax);
                    foreach($incomeTaxes as $taxe)
                    {
                        $taxDataPrice           = Utility::taxRate(!empty($taxe)?$taxe->rate: 0, $invoiceProduct->price, $invoiceProduct->quantity);
                        $incomeTax[!empty($taxe)?$taxe->name:''] = $taxDataPrice;
                    }
                    $incomeTaxesData[$invoiceProduct->month][] = $incomeTax;
                }
    
                $income = [];
                foreach($incomeTaxesData as $month => $incomeTaxx)
                {
                    $incomeTaxRecord = [];
                    foreach($incomeTaxx as $k => $record)
                    {
                        foreach($record as $incomeTaxName => $incomeTaxAmount)
                        {
                            if(array_key_exists($incomeTaxName, $incomeTaxRecord))
                            {
                                $incomeTaxRecord[$incomeTaxName] += $incomeTaxAmount;
                            }
                            else
                            {
                                $incomeTaxRecord[$incomeTaxName] = $incomeTaxAmount;
                            }
                        }
                        $income['data'][$month] = $incomeTaxRecord;
                    }
    
                }
    
                foreach($income as $incomeMonth => $incomeTaxData)
                {
                    $incomeData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $incomeData[$i] = array_key_exists($i, $incomeTaxData) ? $incomeTaxData[$i] : 0;
                    }
    
                }
    
                $incomes = [];
                if(isset($incomeData) && !empty($incomeData))
                {
                    foreach($taxList as $taxArr)
                    {
                        foreach($incomeData as $month => $tax)
                        {
                            if($tax != 0)
                            {
                                if(isset($tax[$taxArr->name]))
                                {
                                    $incomes[$taxArr->name][$month] = $tax[$taxArr->name];
                                }
                                else
                                {
                                    $incomes[$taxArr->name][$month] = 0;
                                }
                            }
                            else
                            {
                                $incomes[$taxArr->name][$month] = 0;
                            }
                        }
                    }
                }
    
    
                $billProducts = BillProduct::selectRaw('bill_products.* ,MONTH(bill_products.created_at) as month,YEAR(bill_products.created_at) as year')->leftjoin('product_services', 'bill_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(bill_products.created_at) =?', [$year])->get();
    
                $expenseTaxesData = [];
                foreach($billProducts as $billProduct)
                {
                    $billTax   = [];
                    $billTaxes = Utility::tax($billProduct->tax);
                    foreach($billTaxes as $taxe)
                    {
                        $taxDataPrice         = Utility::taxRate(!empty($taxe)?$taxe->rate: 0, $billProduct->price, $billProduct->quantity);
                        $billTax[!empty($taxe)?$taxe->name:''] = $taxDataPrice;
                    }
                    $expenseTaxesData[$billProduct->month][] = $billTax;
                }
    
                $bill = [];
                foreach($expenseTaxesData as $month => $billTaxx)
                {
                    $billTaxRecord = [];
                    foreach($billTaxx as $k => $record)
                    {
                        foreach($record as $billTaxName => $billTaxAmount)
                        {
                            if(array_key_exists($billTaxName, $billTaxRecord))
                            {
                                $billTaxRecord[$billTaxName] += $billTaxAmount;
                            }
                            else
                            {
                                $billTaxRecord[$billTaxName] = $billTaxAmount;
                            }
                        }
                        $bill['data'][$month] = $billTaxRecord;
                    }
    
                }
    
                foreach($bill as $billMonth => $billTaxData)
                {
                    $billData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $billData[$i] = array_key_exists($i, $billTaxData) ? $billTaxData[$i] : 0;
                    }
    
                }
                $expenses = [];
                if(isset($billData) && !empty($billData))
                {
    
                    foreach($taxList as $taxArr)
                    {
                        foreach($billData as $month => $tax)
                        {
                            if($tax != 0)
                            {
                                if(isset($tax[$taxArr->name]))
                                {
                                    $expenses[$taxArr->name][$month] = $tax[$taxArr->name];
                                }
                                else
                                {
                                    $expenses[$taxArr->name][$month] = 0;
                                }
                            }
                            else
                            {
                                $expenses[$taxArr->name][$month] = 0;
                            }
                        }
    
                    }
                }
    
                $data['expenses'] = $expenses;
                $data['incomes']  = $incomes;
    
                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;
    
                return view('report.tax_summary', compact('filter'), $data);
            }
            elseif($user->type = 'company'){
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList']  = $this->yearList();
                $data['taxList']   = $taxList = Tax::all();
    
                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
    
                $data['currentYear'] = $year;
    
                $invoiceProducts = InvoiceProduct::selectRaw('invoice_products.* ,MONTH(invoice_products.created_at) as month,YEAR(invoice_products.created_at) as year')->leftjoin('product_services', 'invoice_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(invoice_products.created_at) =?', [$year])->get();
    
                $incomeTaxesData = [];
                foreach($invoiceProducts as $invoiceProduct)
                {
                    $incomeTax   = [];
                    $incomeTaxes = Utility::tax($invoiceProduct->tax);
                    foreach($incomeTaxes as $taxe)
                    {
                        $taxDataPrice           = Utility::taxRate(!empty($taxe)?$taxe->rate: 0, $invoiceProduct->price, $invoiceProduct->quantity);
                        $incomeTax[!empty($taxe)?$taxe->name:''] = $taxDataPrice;
                    }
                    $incomeTaxesData[$invoiceProduct->month][] = $incomeTax;
                }
    
                $income = [];
                foreach($incomeTaxesData as $month => $incomeTaxx)
                {
                    $incomeTaxRecord = [];
                    foreach($incomeTaxx as $k => $record)
                    {
                        foreach($record as $incomeTaxName => $incomeTaxAmount)
                        {
                            if(array_key_exists($incomeTaxName, $incomeTaxRecord))
                            {
                                $incomeTaxRecord[$incomeTaxName] += $incomeTaxAmount;
                            }
                            else
                            {
                                $incomeTaxRecord[$incomeTaxName] = $incomeTaxAmount;
                            }
                        }
                        $income['data'][$month] = $incomeTaxRecord;
                    }
    
                }
    
                foreach($income as $incomeMonth => $incomeTaxData)
                {
                    $incomeData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $incomeData[$i] = array_key_exists($i, $incomeTaxData) ? $incomeTaxData[$i] : 0;
                    }
    
                }
    
                $incomes = [];
                if(isset($incomeData) && !empty($incomeData))
                {
                    foreach($taxList as $taxArr)
                    {
                        foreach($incomeData as $month => $tax)
                        {
                            if($tax != 0)
                            {
                                if(isset($tax[$taxArr->name]))
                                {
                                    $incomes[$taxArr->name][$month] = $tax[$taxArr->name];
                                }
                                else
                                {
                                    $incomes[$taxArr->name][$month] = 0;
                                }
                            }
                            else
                            {
                                $incomes[$taxArr->name][$month] = 0;
                            }
                        }
                    }
                }
    
    
                $billProducts = BillProduct::selectRaw('bill_products.* ,MONTH(bill_products.created_at) as month,YEAR(bill_products.created_at) as year')->leftjoin('product_services', 'bill_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(bill_products.created_at) =?', [$year])->get();
    
                $expenseTaxesData = [];
                foreach($billProducts as $billProduct)
                {
                    $billTax   = [];
                    $billTaxes = Utility::tax($billProduct->tax);
                    foreach($billTaxes as $taxe)
                    {
                        $taxDataPrice         = Utility::taxRate(!empty($taxe)?$taxe->rate: 0, $billProduct->price, $billProduct->quantity);
                        $billTax[!empty($taxe)?$taxe->name:''] = $taxDataPrice;
                    }
                    $expenseTaxesData[$billProduct->month][] = $billTax;
                }
    
                $bill = [];
                foreach($expenseTaxesData as $month => $billTaxx)
                {
                    $billTaxRecord = [];
                    foreach($billTaxx as $k => $record)
                    {
                        foreach($record as $billTaxName => $billTaxAmount)
                        {
                            if(array_key_exists($billTaxName, $billTaxRecord))
                            {
                                $billTaxRecord[$billTaxName] += $billTaxAmount;
                            }
                            else
                            {
                                $billTaxRecord[$billTaxName] = $billTaxAmount;
                            }
                        }
                        $bill['data'][$month] = $billTaxRecord;
                    }
    
                }
    
                foreach($bill as $billMonth => $billTaxData)
                {
                    $billData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $billData[$i] = array_key_exists($i, $billTaxData) ? $billTaxData[$i] : 0;
                    }
    
                }
                $expenses = [];
                if(isset($billData) && !empty($billData))
                {
    
                    foreach($taxList as $taxArr)
                    {
                        foreach($billData as $month => $tax)
                        {
                            if($tax != 0)
                            {
                                if(isset($tax[$taxArr->name]))
                                {
                                    $expenses[$taxArr->name][$month] = $tax[$taxArr->name];
                                }
                                else
                                {
                                    $expenses[$taxArr->name][$month] = 0;
                                }
                            }
                            else
                            {
                                $expenses[$taxArr->name][$month] = 0;
                            }
                        }
    
                    }
                }
    
                $data['expenses'] = $expenses;
                $data['incomes']  = $incomes;
    
                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;
    
                return view('report.tax_summary', compact('filter'), $data);
            }
            else{
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList']  = $this->yearList();
                $data['taxList']   = $taxList = Tax::where('created_by', \Auth::user()->creatorId())->get();
    
                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
    
                $data['currentYear'] = $year;
    
                $invoiceProducts = InvoiceProduct::selectRaw('invoice_products.* ,MONTH(invoice_products.created_at) as month,YEAR(invoice_products.created_at) as year')->leftjoin('product_services', 'invoice_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(invoice_products.created_at) =?', [$year])->where('product_services.created_by', '=', \Auth::user()->creatorId())->get();
    
                $incomeTaxesData = [];
                foreach($invoiceProducts as $invoiceProduct)
                {
                    $incomeTax   = [];
                    $incomeTaxes = Utility::tax($invoiceProduct->tax);
                    foreach($incomeTaxes as $taxe)
                    {
                        $taxDataPrice           = Utility::taxRate(!empty($taxe)?$taxe->rate: 0, $invoiceProduct->price, $invoiceProduct->quantity);
                        $incomeTax[!empty($taxe)?$taxe->name:''] = $taxDataPrice;
                    }
                    $incomeTaxesData[$invoiceProduct->month][] = $incomeTax;
                }
    
                $income = [];
                foreach($incomeTaxesData as $month => $incomeTaxx)
                {
                    $incomeTaxRecord = [];
                    foreach($incomeTaxx as $k => $record)
                    {
                        foreach($record as $incomeTaxName => $incomeTaxAmount)
                        {
                            if(array_key_exists($incomeTaxName, $incomeTaxRecord))
                            {
                                $incomeTaxRecord[$incomeTaxName] += $incomeTaxAmount;
                            }
                            else
                            {
                                $incomeTaxRecord[$incomeTaxName] = $incomeTaxAmount;
                            }
                        }
                        $income['data'][$month] = $incomeTaxRecord;
                    }
    
                }
    
                foreach($income as $incomeMonth => $incomeTaxData)
                {
                    $incomeData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $incomeData[$i] = array_key_exists($i, $incomeTaxData) ? $incomeTaxData[$i] : 0;
                    }
    
                }
    
                $incomes = [];
                if(isset($incomeData) && !empty($incomeData))
                {
                    foreach($taxList as $taxArr)
                    {
                        foreach($incomeData as $month => $tax)
                        {
                            if($tax != 0)
                            {
                                if(isset($tax[$taxArr->name]))
                                {
                                    $incomes[$taxArr->name][$month] = $tax[$taxArr->name];
                                }
                                else
                                {
                                    $incomes[$taxArr->name][$month] = 0;
                                }
                            }
                            else
                            {
                                $incomes[$taxArr->name][$month] = 0;
                            }
                        }
                    }
                }
    
    
                $billProducts = BillProduct::selectRaw('bill_products.* ,MONTH(bill_products.created_at) as month,YEAR(bill_products.created_at) as year')->leftjoin('product_services', 'bill_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(bill_products.created_at) =?', [$year])->where('product_services.created_by', '=', \Auth::user()->creatorId())->get();
    
                $expenseTaxesData = [];
                foreach($billProducts as $billProduct)
                {
                    $billTax   = [];
                    $billTaxes = Utility::tax($billProduct->tax);
                    foreach($billTaxes as $taxe)
                    {
                        $taxDataPrice         = Utility::taxRate(!empty($taxe)?$taxe->rate: 0, $billProduct->price, $billProduct->quantity);
                        $billTax[!empty($taxe)?$taxe->name:''] = $taxDataPrice;
                    }
                    $expenseTaxesData[$billProduct->month][] = $billTax;
                }
    
                $bill = [];
                foreach($expenseTaxesData as $month => $billTaxx)
                {
                    $billTaxRecord = [];
                    foreach($billTaxx as $k => $record)
                    {
                        foreach($record as $billTaxName => $billTaxAmount)
                        {
                            if(array_key_exists($billTaxName, $billTaxRecord))
                            {
                                $billTaxRecord[$billTaxName] += $billTaxAmount;
                            }
                            else
                            {
                                $billTaxRecord[$billTaxName] = $billTaxAmount;
                            }
                        }
                        $bill['data'][$month] = $billTaxRecord;
                    }
    
                }
    
                foreach($bill as $billMonth => $billTaxData)
                {
                    $billData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $billData[$i] = array_key_exists($i, $billTaxData) ? $billTaxData[$i] : 0;
                    }
    
                }
                $expenses = [];
                if(isset($billData) && !empty($billData))
                {
    
                    foreach($taxList as $taxArr)
                    {
                        foreach($billData as $month => $tax)
                        {
                            if($tax != 0)
                            {
                                if(isset($tax[$taxArr->name]))
                                {
                                    $expenses[$taxArr->name][$month] = $tax[$taxArr->name];
                                }
                                else
                                {
                                    $expenses[$taxArr->name][$month] = 0;
                                }
                            }
                            else
                            {
                                $expenses[$taxArr->name][$month] = 0;
                            }
                        }
    
                    }
                }
    
                $data['expenses'] = $expenses;
                $data['incomes']  = $incomes;
    
                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;
    
                return view('report.tax_summary', compact('filter'), $data);
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function profitLossSummary(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('loss & profit report'))
        {
            if($user->type = 'admin'){
                $data['month']     = [
                    'Jan-Mar',
                    'Apr-Jun',
                    'Jul-Sep',
                    'Oct-Dec',
                    'Total',
                ];
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList']  = $this->yearList();
    
                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;
    
                // -------------------------------REVENUE INCOME-------------------------------------------------
    
                $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
                $incomes->get();
                $incomes->whereRAW('YEAR(date) =?', [$year]);
                $incomes->groupBy('month', 'year', 'category_id');
                $incomes        = $incomes->get();
                $tmpIncomeArray = [];
                foreach($incomes as $income)
                {
                    $tmpIncomeArray[$income->category_id][$income->month] = $income->amount;
                }
    
                $incomeCatAmount_1  = $incomeCatAmount_2 = $incomeCatAmount_3 = $incomeCatAmount_4 = 0;
                $revenueIncomeArray = array();
                foreach($tmpIncomeArray as $cat_id => $record)
                {
    
                    $tmp             = [];
                    $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $sumData         = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $sumData[] = array_key_exists($i, $record) ? $record[$i] : 0;
                    }
    
                    $month_1 = array_slice($sumData, 0, 3);
                    $month_2 = array_slice($sumData, 3, 3);
                    $month_3 = array_slice($sumData, 6, 3);
                    $month_4 = array_slice($sumData, 9, 3);
    
    
                    $incomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $incomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $incomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $incomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $incomeData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );
    
                    $incomeCatAmount_1 += $sum_1;
                    $incomeCatAmount_2 += $sum_2;
                    $incomeCatAmount_3 += $sum_3;
                    $incomeCatAmount_4 += $sum_4;
    
                    $data['month'] = array_keys($incomeData);
                    $tmp['amount'] = array_values($incomeData);
    
                    $revenueIncomeArray[] = $tmp;
    
                }
    
                $data['incomeCatAmount'] = $incomeCatAmount = [
                    $incomeCatAmount_1,
                    $incomeCatAmount_2,
                    $incomeCatAmount_3,
                    $incomeCatAmount_4,
                    array_sum(
                        array(
                            $incomeCatAmount_1,
                            $incomeCatAmount_2,
                            $incomeCatAmount_3,
                            $incomeCatAmount_4,
                        )
                    ),
                ];
    
                $data['revenueIncomeArray'] = $revenueIncomeArray;
    
                //-----------------------INVOICE INCOME---------------------------------------------
    
                $invoices = Invoice:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('status', '!=', 0);
                $invoices->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $invoices->where('customer_id', '=', $request->customer);
                }
                $invoices        = $invoices->get();
                $invoiceTmpArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
                }
    
                $invoiceCatAmount_1 = $invoiceCatAmount_2 = $invoiceCatAmount_3 = $invoiceCatAmount_4 = 0;
                $invoiceIncomeArray = array();
                foreach($invoiceTmpArray as $cat_id => $record)
                {
    
                    $invoiceTmp             = [];
                    $invoiceTmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $invoiceSumData         = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $invoiceSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
    
                    }
    
                    $month_1                          = array_slice($invoiceSumData, 0, 3);
                    $month_2                          = array_slice($invoiceSumData, 3, 3);
                    $month_3                          = array_slice($invoiceSumData, 6, 3);
                    $month_4                          = array_slice($invoiceSumData, 9, 3);
                    $invoiceIncomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $invoiceIncomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $invoiceIncomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $invoiceIncomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $invoiceIncomeData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );
                    $invoiceCatAmount_1               += $sum_1;
                    $invoiceCatAmount_2               += $sum_2;
                    $invoiceCatAmount_3               += $sum_3;
                    $invoiceCatAmount_4               += $sum_4;
    
                    $invoiceTmp['amount'] = array_values($invoiceIncomeData);
    
                    $invoiceIncomeArray[] = $invoiceTmp;
    
                }
    
                $data['invoiceIncomeCatAmount'] = $invoiceIncomeCatAmount = [
                    $invoiceCatAmount_1,
                    $invoiceCatAmount_2,
                    $invoiceCatAmount_3,
                    $invoiceCatAmount_4,
                    array_sum(
                        array(
                            $invoiceCatAmount_1,
                            $invoiceCatAmount_2,
                            $invoiceCatAmount_3,
                            $invoiceCatAmount_4,
                        )
                    ),
                ];
    
    
                $data['invoiceIncomeArray'] = $invoiceIncomeArray;
    
                $data['totalIncome'] = $totalIncome = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $invoiceIncomeCatAmount, $incomeCatAmount
                );
    
                //---------------------------------PAYMENT EXPENSE-----------------------------------
    
                $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
                $expenses->get();
                $expenses->whereRAW('YEAR(date) =?', [$year]);
                $expenses->groupBy('month', 'year', 'category_id');
                $expenses = $expenses->get();
    
                $tmpExpenseArray = [];
                foreach($expenses as $expense)
                {
                    $tmpExpenseArray[$expense->category_id][$expense->month] = $expense->amount;
                }
    
                $expenseArray       = [];
                $expenseCatAmount_1 = $expenseCatAmount_2 = $expenseCatAmount_3 = $expenseCatAmount_4 = 0;
                foreach($tmpExpenseArray as $cat_id => $record)
                {
                    $tmp             = [];
                    $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $expenseSumData  = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $expenseSumData[] = array_key_exists($i, $record) ? $record[$i] : 0;
    
                    }
    
                    $month_1 = array_slice($expenseSumData, 0, 3);
                    $month_2 = array_slice($expenseSumData, 3, 3);
                    $month_3 = array_slice($expenseSumData, 6, 3);
                    $month_4 = array_slice($expenseSumData, 9, 3);
    
                    $expenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $expenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $expenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $expenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $expenseData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );
    
                    $expenseCatAmount_1 += $sum_1;
                    $expenseCatAmount_2 += $sum_2;
                    $expenseCatAmount_3 += $sum_3;
                    $expenseCatAmount_4 += $sum_4;
    
                    $data['month'] = array_keys($expenseData);
                    $tmp['amount'] = array_values($expenseData);
    
                    $expenseArray[] = $tmp;
    
                }
    
                $data['expenseCatAmount'] = $expenseCatAmount = [
                    $expenseCatAmount_1,
                    $expenseCatAmount_2,
                    $expenseCatAmount_3,
                    $expenseCatAmount_4,
                    array_sum(
                        array(
                            $expenseCatAmount_1,
                            $expenseCatAmount_2,
                            $expenseCatAmount_3,
                            $expenseCatAmount_4,
                        )
                    ),
                ];
                $data['expenseArray']     = $expenseArray;
    
                //    ----------------------------EXPENSE BILL-----------------------------------------------------------------------
    
                $bills = Bill:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('status', '!=', 0);
                $bills->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $bills->where('vender_id', '=', $request->vender);
                }
                $bills        = $bills->get();
                $billTmpArray = [];
                foreach($bills as $bill)
                {
                    $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
                }
    
                $billExpenseArray       = [];
                $billExpenseCatAmount_1 = $billExpenseCatAmount_2 = $billExpenseCatAmount_3 = $billExpenseCatAmount_4 = 0;
                foreach($billTmpArray as $cat_id => $record)
                {
                    $billTmp             = [];
                    $billTmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $billExpensSumData   = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $billExpensSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
    
                    $month_1 = array_slice($billExpensSumData, 0, 3);
                    $month_2 = array_slice($billExpensSumData, 3, 3);
                    $month_3 = array_slice($billExpensSumData, 6, 3);
                    $month_4 = array_slice($billExpensSumData, 9, 3);
    
                    $billExpenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $billExpenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $billExpenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $billExpenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $billExpenseData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );
    
                    $billExpenseCatAmount_1 += $sum_1;
                    $billExpenseCatAmount_2 += $sum_2;
                    $billExpenseCatAmount_3 += $sum_3;
                    $billExpenseCatAmount_4 += $sum_4;
    
                    $data['month']     = array_keys($billExpenseData);
                    $billTmp['amount'] = array_values($billExpenseData);
    
                    $billExpenseArray[] = $billTmp;
    
                }
    
                $data['billExpenseCatAmount'] = $billExpenseCatAmount = [
                    $billExpenseCatAmount_1,
                    $billExpenseCatAmount_2,
                    $billExpenseCatAmount_3,
                    $billExpenseCatAmount_4,
                    array_sum(
                        array(
                            $billExpenseCatAmount_1,
                            $billExpenseCatAmount_2,
                            $billExpenseCatAmount_3,
                            $billExpenseCatAmount_4,
                        )
                    ),
                ];
    
                $data['billExpenseArray'] = $billExpenseArray;
    
    
                $data['totalExpense'] = $totalExpense = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $billExpenseCatAmount, $expenseCatAmount
                );
    
    
                foreach($totalIncome as $k => $income)
                {
                    $netProfit[] = $income - $totalExpense[$k];
                }
                $data['netProfitArray'] = $netProfit;
    
                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;
    
                return view('report.profit_loss_summary', compact('filter'), $data);
            }
            elseif($user->type = 'company'){
                $data['month']     = [
                    'Jan-Mar',
                    'Apr-Jun',
                    'Jul-Sep',
                    'Oct-Dec',
                    'Total',
                ];
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList']  = $this->yearList();
    
                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;
    
                // -------------------------------REVENUE INCOME-------------------------------------------------
    
                $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
                $incomes->get();
                $incomes->whereRAW('YEAR(date) =?', [$year]);
                $incomes->groupBy('month', 'year', 'category_id');
                $incomes        = $incomes->get();
                $tmpIncomeArray = [];
                foreach($incomes as $income)
                {
                    $tmpIncomeArray[$income->category_id][$income->month] = $income->amount;
                }
    
                $incomeCatAmount_1  = $incomeCatAmount_2 = $incomeCatAmount_3 = $incomeCatAmount_4 = 0;
                $revenueIncomeArray = array();
                foreach($tmpIncomeArray as $cat_id => $record)
                {
    
                    $tmp             = [];
                    $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $sumData         = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $sumData[] = array_key_exists($i, $record) ? $record[$i] : 0;
                    }
    
                    $month_1 = array_slice($sumData, 0, 3);
                    $month_2 = array_slice($sumData, 3, 3);
                    $month_3 = array_slice($sumData, 6, 3);
                    $month_4 = array_slice($sumData, 9, 3);
    
    
                    $incomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $incomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $incomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $incomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $incomeData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );
    
                    $incomeCatAmount_1 += $sum_1;
                    $incomeCatAmount_2 += $sum_2;
                    $incomeCatAmount_3 += $sum_3;
                    $incomeCatAmount_4 += $sum_4;
    
                    $data['month'] = array_keys($incomeData);
                    $tmp['amount'] = array_values($incomeData);
    
                    $revenueIncomeArray[] = $tmp;
    
                }
    
                $data['incomeCatAmount'] = $incomeCatAmount = [
                    $incomeCatAmount_1,
                    $incomeCatAmount_2,
                    $incomeCatAmount_3,
                    $incomeCatAmount_4,
                    array_sum(
                        array(
                            $incomeCatAmount_1,
                            $incomeCatAmount_2,
                            $incomeCatAmount_3,
                            $incomeCatAmount_4,
                        )
                    ),
                ];
    
                $data['revenueIncomeArray'] = $revenueIncomeArray;
    
                //-----------------------INVOICE INCOME---------------------------------------------
    
                $invoices = Invoice:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('status', '!=', 0);
                $invoices->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $invoices->where('customer_id', '=', $request->customer);
                }
                $invoices        = $invoices->get();
                $invoiceTmpArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
                }
    
                $invoiceCatAmount_1 = $invoiceCatAmount_2 = $invoiceCatAmount_3 = $invoiceCatAmount_4 = 0;
                $invoiceIncomeArray = array();
                foreach($invoiceTmpArray as $cat_id => $record)
                {
    
                    $invoiceTmp             = [];
                    $invoiceTmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $invoiceSumData         = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $invoiceSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
    
                    }
    
                    $month_1                          = array_slice($invoiceSumData, 0, 3);
                    $month_2                          = array_slice($invoiceSumData, 3, 3);
                    $month_3                          = array_slice($invoiceSumData, 6, 3);
                    $month_4                          = array_slice($invoiceSumData, 9, 3);
                    $invoiceIncomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $invoiceIncomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $invoiceIncomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $invoiceIncomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $invoiceIncomeData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );
                    $invoiceCatAmount_1               += $sum_1;
                    $invoiceCatAmount_2               += $sum_2;
                    $invoiceCatAmount_3               += $sum_3;
                    $invoiceCatAmount_4               += $sum_4;
    
                    $invoiceTmp['amount'] = array_values($invoiceIncomeData);
    
                    $invoiceIncomeArray[] = $invoiceTmp;
    
                }
    
                $data['invoiceIncomeCatAmount'] = $invoiceIncomeCatAmount = [
                    $invoiceCatAmount_1,
                    $invoiceCatAmount_2,
                    $invoiceCatAmount_3,
                    $invoiceCatAmount_4,
                    array_sum(
                        array(
                            $invoiceCatAmount_1,
                            $invoiceCatAmount_2,
                            $invoiceCatAmount_3,
                            $invoiceCatAmount_4,
                        )
                    ),
                ];
    
    
                $data['invoiceIncomeArray'] = $invoiceIncomeArray;
    
                $data['totalIncome'] = $totalIncome = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $invoiceIncomeCatAmount, $incomeCatAmount
                );
    
                //---------------------------------PAYMENT EXPENSE-----------------------------------
    
                $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
                $expenses->get();
                $expenses->whereRAW('YEAR(date) =?', [$year]);
                $expenses->groupBy('month', 'year', 'category_id');
                $expenses = $expenses->get();
    
                $tmpExpenseArray = [];
                foreach($expenses as $expense)
                {
                    $tmpExpenseArray[$expense->category_id][$expense->month] = $expense->amount;
                }
    
                $expenseArray       = [];
                $expenseCatAmount_1 = $expenseCatAmount_2 = $expenseCatAmount_3 = $expenseCatAmount_4 = 0;
                foreach($tmpExpenseArray as $cat_id => $record)
                {
                    $tmp             = [];
                    $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $expenseSumData  = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $expenseSumData[] = array_key_exists($i, $record) ? $record[$i] : 0;
    
                    }
    
                    $month_1 = array_slice($expenseSumData, 0, 3);
                    $month_2 = array_slice($expenseSumData, 3, 3);
                    $month_3 = array_slice($expenseSumData, 6, 3);
                    $month_4 = array_slice($expenseSumData, 9, 3);
    
                    $expenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $expenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $expenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $expenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $expenseData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );
    
                    $expenseCatAmount_1 += $sum_1;
                    $expenseCatAmount_2 += $sum_2;
                    $expenseCatAmount_3 += $sum_3;
                    $expenseCatAmount_4 += $sum_4;
    
                    $data['month'] = array_keys($expenseData);
                    $tmp['amount'] = array_values($expenseData);
    
                    $expenseArray[] = $tmp;
    
                }
    
                $data['expenseCatAmount'] = $expenseCatAmount = [
                    $expenseCatAmount_1,
                    $expenseCatAmount_2,
                    $expenseCatAmount_3,
                    $expenseCatAmount_4,
                    array_sum(
                        array(
                            $expenseCatAmount_1,
                            $expenseCatAmount_2,
                            $expenseCatAmount_3,
                            $expenseCatAmount_4,
                        )
                    ),
                ];
                $data['expenseArray']     = $expenseArray;
    
                //    ----------------------------EXPENSE BILL-----------------------------------------------------------------------
    
                $bills = Bill:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('status', '!=', 0);
                $bills->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $bills->where('vender_id', '=', $request->vender);
                }
                $bills        = $bills->get();
                $billTmpArray = [];
                foreach($bills as $bill)
                {
                    $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
                }
    
                $billExpenseArray       = [];
                $billExpenseCatAmount_1 = $billExpenseCatAmount_2 = $billExpenseCatAmount_3 = $billExpenseCatAmount_4 = 0;
                foreach($billTmpArray as $cat_id => $record)
                {
                    $billTmp             = [];
                    $billTmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $billExpensSumData   = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $billExpensSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
    
                    $month_1 = array_slice($billExpensSumData, 0, 3);
                    $month_2 = array_slice($billExpensSumData, 3, 3);
                    $month_3 = array_slice($billExpensSumData, 6, 3);
                    $month_4 = array_slice($billExpensSumData, 9, 3);
    
                    $billExpenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $billExpenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $billExpenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $billExpenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $billExpenseData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );
    
                    $billExpenseCatAmount_1 += $sum_1;
                    $billExpenseCatAmount_2 += $sum_2;
                    $billExpenseCatAmount_3 += $sum_3;
                    $billExpenseCatAmount_4 += $sum_4;
    
                    $data['month']     = array_keys($billExpenseData);
                    $billTmp['amount'] = array_values($billExpenseData);
    
                    $billExpenseArray[] = $billTmp;
    
                }
    
                $data['billExpenseCatAmount'] = $billExpenseCatAmount = [
                    $billExpenseCatAmount_1,
                    $billExpenseCatAmount_2,
                    $billExpenseCatAmount_3,
                    $billExpenseCatAmount_4,
                    array_sum(
                        array(
                            $billExpenseCatAmount_1,
                            $billExpenseCatAmount_2,
                            $billExpenseCatAmount_3,
                            $billExpenseCatAmount_4,
                        )
                    ),
                ];
    
                $data['billExpenseArray'] = $billExpenseArray;
    
    
                $data['totalExpense'] = $totalExpense = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $billExpenseCatAmount, $expenseCatAmount
                );
    
    
                foreach($totalIncome as $k => $income)
                {
                    $netProfit[] = $income - $totalExpense[$k];
                }
                $data['netProfitArray'] = $netProfit;
    
                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;
    
                return view('report.profit_loss_summary', compact('filter'), $data);
            }
            else{
                $data['month']     = [
                    'Jan-Mar',
                    'Apr-Jun',
                    'Jul-Sep',
                    'Oct-Dec',
                    'Total',
                ];
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList']  = $this->yearList();
    
                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;
    
                // -------------------------------REVENUE INCOME-------------------------------------------------
    
                $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
                $incomes->where('created_by', '=', \Auth::user()->creatorId());
                $incomes->whereRAW('YEAR(date) =?', [$year]);
                $incomes->groupBy('month', 'year', 'category_id');
                $incomes        = $incomes->get();
                $tmpIncomeArray = [];
                foreach($incomes as $income)
                {
                    $tmpIncomeArray[$income->category_id][$income->month] = $income->amount;
                }
    
                $incomeCatAmount_1  = $incomeCatAmount_2 = $incomeCatAmount_3 = $incomeCatAmount_4 = 0;
                $revenueIncomeArray = array();
                foreach($tmpIncomeArray as $cat_id => $record)
                {
    
                    $tmp             = [];
                    $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $sumData         = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $sumData[] = array_key_exists($i, $record) ? $record[$i] : 0;
                    }
    
                    $month_1 = array_slice($sumData, 0, 3);
                    $month_2 = array_slice($sumData, 3, 3);
                    $month_3 = array_slice($sumData, 6, 3);
                    $month_4 = array_slice($sumData, 9, 3);
    
    
                    $incomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $incomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $incomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $incomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $incomeData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );
    
                    $incomeCatAmount_1 += $sum_1;
                    $incomeCatAmount_2 += $sum_2;
                    $incomeCatAmount_3 += $sum_3;
                    $incomeCatAmount_4 += $sum_4;
    
                    $data['month'] = array_keys($incomeData);
                    $tmp['amount'] = array_values($incomeData);
    
                    $revenueIncomeArray[] = $tmp;
    
                }
    
                $data['incomeCatAmount'] = $incomeCatAmount = [
                    $incomeCatAmount_1,
                    $incomeCatAmount_2,
                    $incomeCatAmount_3,
                    $incomeCatAmount_4,
                    array_sum(
                        array(
                            $incomeCatAmount_1,
                            $incomeCatAmount_2,
                            $incomeCatAmount_3,
                            $incomeCatAmount_4,
                        )
                    ),
                ];
    
                $data['revenueIncomeArray'] = $revenueIncomeArray;
    
                //-----------------------INVOICE INCOME---------------------------------------------
    
                $invoices = Invoice:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
                $invoices->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $invoices->where('customer_id', '=', $request->customer);
                }
                $invoices        = $invoices->get();
                $invoiceTmpArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
                }
    
                $invoiceCatAmount_1 = $invoiceCatAmount_2 = $invoiceCatAmount_3 = $invoiceCatAmount_4 = 0;
                $invoiceIncomeArray = array();
                foreach($invoiceTmpArray as $cat_id => $record)
                {
    
                    $invoiceTmp             = [];
                    $invoiceTmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $invoiceSumData         = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $invoiceSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
    
                    }
    
                    $month_1                          = array_slice($invoiceSumData, 0, 3);
                    $month_2                          = array_slice($invoiceSumData, 3, 3);
                    $month_3                          = array_slice($invoiceSumData, 6, 3);
                    $month_4                          = array_slice($invoiceSumData, 9, 3);
                    $invoiceIncomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $invoiceIncomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $invoiceIncomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $invoiceIncomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $invoiceIncomeData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );
                    $invoiceCatAmount_1               += $sum_1;
                    $invoiceCatAmount_2               += $sum_2;
                    $invoiceCatAmount_3               += $sum_3;
                    $invoiceCatAmount_4               += $sum_4;
    
                    $invoiceTmp['amount'] = array_values($invoiceIncomeData);
    
                    $invoiceIncomeArray[] = $invoiceTmp;
    
                }
    
                $data['invoiceIncomeCatAmount'] = $invoiceIncomeCatAmount = [
                    $invoiceCatAmount_1,
                    $invoiceCatAmount_2,
                    $invoiceCatAmount_3,
                    $invoiceCatAmount_4,
                    array_sum(
                        array(
                            $invoiceCatAmount_1,
                            $invoiceCatAmount_2,
                            $invoiceCatAmount_3,
                            $invoiceCatAmount_4,
                        )
                    ),
                ];
    
    
                $data['invoiceIncomeArray'] = $invoiceIncomeArray;
    
                $data['totalIncome'] = $totalIncome = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $invoiceIncomeCatAmount, $incomeCatAmount
                );
    
                //---------------------------------PAYMENT EXPENSE-----------------------------------
    
                $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
                $expenses->where('created_by', '=', \Auth::user()->creatorId());
                $expenses->whereRAW('YEAR(date) =?', [$year]);
                $expenses->groupBy('month', 'year', 'category_id');
                $expenses = $expenses->get();
    
                $tmpExpenseArray = [];
                foreach($expenses as $expense)
                {
                    $tmpExpenseArray[$expense->category_id][$expense->month] = $expense->amount;
                }
    
                $expenseArray       = [];
                $expenseCatAmount_1 = $expenseCatAmount_2 = $expenseCatAmount_3 = $expenseCatAmount_4 = 0;
                foreach($tmpExpenseArray as $cat_id => $record)
                {
                    $tmp             = [];
                    $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $expenseSumData  = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $expenseSumData[] = array_key_exists($i, $record) ? $record[$i] : 0;
    
                    }
    
                    $month_1 = array_slice($expenseSumData, 0, 3);
                    $month_2 = array_slice($expenseSumData, 3, 3);
                    $month_3 = array_slice($expenseSumData, 6, 3);
                    $month_4 = array_slice($expenseSumData, 9, 3);
    
                    $expenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $expenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $expenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $expenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $expenseData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );
    
                    $expenseCatAmount_1 += $sum_1;
                    $expenseCatAmount_2 += $sum_2;
                    $expenseCatAmount_3 += $sum_3;
                    $expenseCatAmount_4 += $sum_4;
    
                    $data['month'] = array_keys($expenseData);
                    $tmp['amount'] = array_values($expenseData);
    
                    $expenseArray[] = $tmp;
    
                }
    
                $data['expenseCatAmount'] = $expenseCatAmount = [
                    $expenseCatAmount_1,
                    $expenseCatAmount_2,
                    $expenseCatAmount_3,
                    $expenseCatAmount_4,
                    array_sum(
                        array(
                            $expenseCatAmount_1,
                            $expenseCatAmount_2,
                            $expenseCatAmount_3,
                            $expenseCatAmount_4,
                        )
                    ),
                ];
                $data['expenseArray']     = $expenseArray;
    
                //    ----------------------------EXPENSE BILL-----------------------------------------------------------------------
    
                $bills = Bill:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
                $bills->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $bills->where('vender_id', '=', $request->vender);
                }
                $bills        = $bills->get();
                $billTmpArray = [];
                foreach($bills as $bill)
                {
                    $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
                }
    
                $billExpenseArray       = [];
                $billExpenseCatAmount_1 = $billExpenseCatAmount_2 = $billExpenseCatAmount_3 = $billExpenseCatAmount_4 = 0;
                foreach($billTmpArray as $cat_id => $record)
                {
                    $billTmp             = [];
                    $billTmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                    $billExpensSumData   = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $billExpensSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
    
                    $month_1 = array_slice($billExpensSumData, 0, 3);
                    $month_2 = array_slice($billExpensSumData, 3, 3);
                    $month_3 = array_slice($billExpensSumData, 6, 3);
                    $month_4 = array_slice($billExpensSumData, 9, 3);
    
                    $billExpenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $billExpenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $billExpenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $billExpenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $billExpenseData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );
    
                    $billExpenseCatAmount_1 += $sum_1;
                    $billExpenseCatAmount_2 += $sum_2;
                    $billExpenseCatAmount_3 += $sum_3;
                    $billExpenseCatAmount_4 += $sum_4;
    
                    $data['month']     = array_keys($billExpenseData);
                    $billTmp['amount'] = array_values($billExpenseData);
    
                    $billExpenseArray[] = $billTmp;
    
                }
    
                $data['billExpenseCatAmount'] = $billExpenseCatAmount = [
                    $billExpenseCatAmount_1,
                    $billExpenseCatAmount_2,
                    $billExpenseCatAmount_3,
                    $billExpenseCatAmount_4,
                    array_sum(
                        array(
                            $billExpenseCatAmount_1,
                            $billExpenseCatAmount_2,
                            $billExpenseCatAmount_3,
                            $billExpenseCatAmount_4,
                        )
                    ),
                ];
    
                $data['billExpenseArray'] = $billExpenseArray;
    
    
                $data['totalExpense'] = $totalExpense = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $billExpenseCatAmount, $expenseCatAmount
                );
    
    
                foreach($totalIncome as $k => $income)
                {
                    $netProfit[] = $income - $totalExpense[$k];
                }
                $data['netProfitArray'] = $netProfit;
    
                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;
    
                return view('report.profit_loss_summary', compact('filter'), $data);     
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }

    public function yearMonth()
    {

        $month[] = __('January');
        $month[] = __('February');
        $month[] = __('March');
        $month[] = __('April');
        $month[] = __('May');
        $month[] = __('June');
        $month[] = __('July');
        $month[] = __('August');
        $month[] = __('September');
        $month[] = __('October');
        $month[] = __('November');
        $month[] = __('December');

        return $month;
    }

    public function yearList()
    {
        $starting_year = date('Y', strtotime('-5 year'));
        $ending_year   = date('Y');

        foreach(range($ending_year, $starting_year) as $year)
        {
            $years[$year] = $year;
        }

        return $years;
    }

    public function invoiceSummary(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('invoice report'))
        {
            if($user->type = 'admin'){
                $filter['customer'] = __('All');
                $filter['status']   = __('All');
    
    
                $customer = Customer::all()->pluck('name', 'customer_id');
                $customer->prepend('Select Customer', '');
                $status = Invoice::$statues;
    
                $invoices = Invoice::selectRaw('invoices.*,MONTH(send_date) as month,YEAR(send_date) as year');
    
                if($request->status != '')
                {
                    $invoices->where('status', $request->status);
    
                    $filter['status'] = Invoice::$statues[$request->status];
                }
                else
                {
                    $invoices->where('status', '!=', 0);
                }
    
                $invoices->get();
    
                if(!empty($request->start_month) && !empty($request->end_month))
                {
                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                }
                else
                {
                    $start = strtotime(date('Y-01'));
                    $end   = strtotime(date('Y-12'));
                }
    
                $invoices->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));
    
    
                $filter['startDateRange'] = date('M-Y', $start);
                $filter['endDateRange']   = date('M-Y', $end);
    
    
                if(!empty($request->customer))
                {
                    $invoices->where('customer_id', $request->customer);
                    $cust = Customer::find($request->customer);
    
                    $filter['customer'] = !empty($cust) ? $cust->name : '';
                }
    
    
                $invoices = $invoices->get();
    
    
                $totalInvoice      = 0;
                $totalDueInvoice   = 0;
                $invoiceTotalArray = [];
                foreach($invoices as $invoice)
                {
                    $totalInvoice    += $invoice->getTotal();
                    $totalDueInvoice += $invoice->getDue();
    
                    $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
                }
                $totalPaidInvoice = $totalInvoice - $totalDueInvoice;
    
                for($i = 1; $i <= 12; $i++)
                {
                    $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
                }
    
                $monthList = $month = $this->yearMonth();
    
                return view('report.invoice_report', compact('invoices', 'customer', 'status', 'totalInvoice', 'totalDueInvoice', 'totalPaidInvoice', 'invoiceTotal', 'monthList', 'filter'));
            }
            elseif($user->type = 'company'){
                $filter['customer'] = __('All');
                $filter['status']   = __('All');
    
    
                $customer = Customer::all()->pluck('name', 'customer_id');
                $customer->prepend('Select Customer', '');
                $status = Invoice::$statues;
    
                $invoices = Invoice::selectRaw('invoices.*,MONTH(send_date) as month,YEAR(send_date) as year');
    
                if($request->status != '')
                {
                    $invoices->where('status', $request->status);
    
                    $filter['status'] = Invoice::$statues[$request->status];
                }
                else
                {
                    $invoices->where('status', '!=', 0);
                }
    
                $invoices->get();
    
                if(!empty($request->start_month) && !empty($request->end_month))
                {
                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                }
                else
                {
                    $start = strtotime(date('Y-01'));
                    $end   = strtotime(date('Y-12'));
                }
    
                $invoices->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));
    
    
                $filter['startDateRange'] = date('M-Y', $start);
                $filter['endDateRange']   = date('M-Y', $end);
    
    
                if(!empty($request->customer))
                {
                    $invoices->where('customer_id', $request->customer);
                    $cust = Customer::find($request->customer);
    
                    $filter['customer'] = !empty($cust) ? $cust->name : '';
                }
    
    
                $invoices = $invoices->get();
    
    
                $totalInvoice      = 0;
                $totalDueInvoice   = 0;
                $invoiceTotalArray = [];
                foreach($invoices as $invoice)
                {
                    $totalInvoice    += $invoice->getTotal();
                    $totalDueInvoice += $invoice->getDue();
    
                    $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
                }
                $totalPaidInvoice = $totalInvoice - $totalDueInvoice;
    
                for($i = 1; $i <= 12; $i++)
                {
                    $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
                }
    
                $monthList = $month = $this->yearMonth();
    
                return view('report.invoice_report', compact('invoices', 'customer', 'status', 'totalInvoice', 'totalDueInvoice', 'totalPaidInvoice', 'invoiceTotal', 'monthList', 'filter'));
            }
            else{
                $filter['customer'] = __('All');
                $filter['status']   = __('All');
    
    
                $customer = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'customer_id');
                $customer->prepend('Select Customer', '');
                $status = Invoice::$statues;
    
                $invoices = Invoice::selectRaw('invoices.*,MONTH(send_date) as month,YEAR(send_date) as year');
    
                if($request->status != '')
                {
                    $invoices->where('status', $request->status);
    
                    $filter['status'] = Invoice::$statues[$request->status];
                }
                else
                {
                    $invoices->where('status', '!=', 0);
                }
    
                $invoices->where('created_by', '=', \Auth::user()->creatorId());
    
                if(!empty($request->start_month) && !empty($request->end_month))
                {
                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                }
                else
                {
                    $start = strtotime(date('Y-01'));
                    $end   = strtotime(date('Y-12'));
                }
    
                $invoices->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));
    
    
                $filter['startDateRange'] = date('M-Y', $start);
                $filter['endDateRange']   = date('M-Y', $end);
    
    
                if(!empty($request->customer))
                {
                    $invoices->where('customer_id', $request->customer);
                    $cust = Customer::find($request->customer);
    
                    $filter['customer'] = !empty($cust) ? $cust->name : '';
                }
    
    
                $invoices = $invoices->get();
    
    
                $totalInvoice      = 0;
                $totalDueInvoice   = 0;
                $invoiceTotalArray = [];
                foreach($invoices as $invoice)
                {
                    $totalInvoice    += $invoice->getTotal();
                    $totalDueInvoice += $invoice->getDue();
    
                    $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
                }
                $totalPaidInvoice = $totalInvoice - $totalDueInvoice;
    
                for($i = 1; $i <= 12; $i++)
                {
                    $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
                }
    
                $monthList = $month = $this->yearMonth();
    
                return view('report.invoice_report', compact('invoices', 'customer', 'status', 'totalInvoice', 'totalDueInvoice', 'totalPaidInvoice', 'invoiceTotal', 'monthList', 'filter'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function billSummary(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('bill report'))
        {
            if($user->type = 'admin'){
                $filter['vender'] = __('All');
                $filter['status'] = __('All');
    
    
                $vender = Vender::all()->pluck('name', 'vender_id');
                $vender->prepend('Select Vendor', '');
                $status = Bill::$statues;
    
                $bills = Bill::selectRaw('bills.*,MONTH(send_date) as month,YEAR(send_date) as year');
    
                if(!empty($request->start_month) && !empty($request->end_month))
                {
                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                }
                else
                {
                    $start = strtotime(date('Y-01'));
                    $end   = strtotime(date('Y-12'));
                }
    
                $bills->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));
    
                $filter['startDateRange'] = date('M-Y', $start);
                $filter['endDateRange']   = date('M-Y', $end);
    
    
                if(!empty($request->vender))
                {
                    $bills->where('vender_id', $request->vender);
                    $vend = Vender::find($request->vender);
    
                    $filter['vender'] = !empty($vend) ? $vend->name : '';
                }
    
                if($request->status != '')
                {
                    $bills->where('status', '=', $request->status);
    
                    $filter['status'] = Bill::$statues[$request->status];
                }
                else
                {
                    $bills->where('status', '!=', 0);
                }
    
                $bills->get();
                $bills = $bills->get();
    
    
                $totalBill      = 0;
                $totalDueBill   = 0;
                $billTotalArray = [];
                foreach($bills as $bill)
                {
                    $totalBill    += $bill->getTotal();
                    $totalDueBill += $bill->getDue();
    
                    $billTotalArray[$bill->month][] = $bill->getTotal();
                }
                $totalPaidBill = $totalBill - $totalDueBill;
    
                for($i = 1; $i <= 12; $i++)
                {
                    $billTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
                }
    
                $monthList = $month = $this->yearMonth();
    
                return view('report.bill_report', compact('bills', 'vender', 'status', 'totalBill', 'totalDueBill', 'totalPaidBill', 'billTotal', 'monthList', 'filter'));
            }
            elseif($user->type = 'company'){
                $filter['vender'] = __('All');
                $filter['status'] = __('All');
    
    
                $vender = Vender::all()->pluck('name', 'vender_id');
                $vender->prepend('Select Vendor', '');
                $status = Bill::$statues;
    
                $bills = Bill::selectRaw('bills.*,MONTH(send_date) as month,YEAR(send_date) as year');
    
                if(!empty($request->start_month) && !empty($request->end_month))
                {
                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                }
                else
                {
                    $start = strtotime(date('Y-01'));
                    $end   = strtotime(date('Y-12'));
                }
    
                $bills->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));
    
                $filter['startDateRange'] = date('M-Y', $start);
                $filter['endDateRange']   = date('M-Y', $end);
    
    
                if(!empty($request->vender))
                {
                    $bills->where('vender_id', $request->vender);
                    $vend = Vender::find($request->vender);
    
                    $filter['vender'] = !empty($vend) ? $vend->name : '';
                }
    
                if($request->status != '')
                {
                    $bills->where('status', '=', $request->status);
    
                    $filter['status'] = Bill::$statues[$request->status];
                }
                else
                {
                    $bills->where('status', '!=', 0);
                }
    
                $bills->get();
                $bills = $bills->get();
    
    
                $totalBill      = 0;
                $totalDueBill   = 0;
                $billTotalArray = [];
                foreach($bills as $bill)
                {
                    $totalBill    += $bill->getTotal();
                    $totalDueBill += $bill->getDue();
    
                    $billTotalArray[$bill->month][] = $bill->getTotal();
                }
                $totalPaidBill = $totalBill - $totalDueBill;
    
                for($i = 1; $i <= 12; $i++)
                {
                    $billTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
                }
    
                $monthList = $month = $this->yearMonth();
    
                return view('report.bill_report', compact('bills', 'vender', 'status', 'totalBill', 'totalDueBill', 'totalPaidBill', 'billTotal', 'monthList', 'filter'));
            }
            else{
                $filter['vender'] = __('All');
                $filter['status'] = __('All');
    
    
                $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'vender_id');
                $vender->prepend('Select Vendor', '');
                $status = Bill::$statues;
    
                $bills = Bill::selectRaw('bills.*,MONTH(send_date) as month,YEAR(send_date) as year');
    
                if(!empty($request->start_month) && !empty($request->end_month))
                {
                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                }
                else
                {
                    $start = strtotime(date('Y-01'));
                    $end   = strtotime(date('Y-12'));
                }
    
                $bills->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));
    
                $filter['startDateRange'] = date('M-Y', $start);
                $filter['endDateRange']   = date('M-Y', $end);
    
    
                if(!empty($request->vender))
                {
                    $bills->where('vender_id', $request->vender);
                    $vend = Vender::find($request->vender);
    
                    $filter['vender'] = !empty($vend) ? $vend->name : '';
                }
    
                if($request->status != '')
                {
                    $bills->where('status', '=', $request->status);
    
                    $filter['status'] = Bill::$statues[$request->status];
                }
                else
                {
                    $bills->where('status', '!=', 0);
                }
    
                $bills->where('created_by', '=', \Auth::user()->creatorId());
                $bills = $bills->get();
    
    
                $totalBill      = 0;
                $totalDueBill   = 0;
                $billTotalArray = [];
                foreach($bills as $bill)
                {
                    $totalBill    += $bill->getTotal();
                    $totalDueBill += $bill->getDue();
    
                    $billTotalArray[$bill->month][] = $bill->getTotal();
                }
                $totalPaidBill = $totalBill - $totalDueBill;
    
                for($i = 1; $i <= 12; $i++)
                {
                    $billTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
                }
    
                $monthList = $month = $this->yearMonth();
    
                return view('report.bill_report', compact('bills', 'vender', 'status', 'totalBill', 'totalDueBill', 'totalPaidBill', 'billTotal', 'monthList', 'filter'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function accountStatement(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('statement report'))
        {
            if($user->type = 'admin'){
                $filter['account']             = __('All');
                $filter['type']                = __('Revenue');
                $reportData['revenues']        = '';
                $reportData['payments']        = '';
                $reportData['revenueAccounts'] = '';
                $reportData['paymentAccounts'] = '';
    
                $account = BankAccount::all()->pluck('holder_name', 'id');
                $account->prepend('Select Account', '');
    
                $types = [
                    'revenue' => __('Revenue'),
                    'payment' => __('Payment'),
                ];
    
                if($request->type == 'revenue' || !isset($request->type))
                {
    
                    $revenueAccounts = Revenue::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'revenues.account_id', '=', 'bank_accounts.id')->groupBy('revenues.account_id')->selectRaw('sum(amount) as total');
    
                    $revenues = Revenue::orderBy('id', 'desc');
                }
    
                if($request->type == 'payment')
                {
                    $paymentAccounts = Payment::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'payments.account_id', '=', 'bank_accounts.id')->groupBy('payments.account_id')->selectRaw('sum(amount) as total');
    
                    $payments = Payment::orderBy('id', 'desc');
                }
    
    
                if(!empty($request->start_month) && !empty($request->end_month))
                {
                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                }
                else
                {
                    $start = strtotime(date('Y-m'));
                    $end   = strtotime(date('Y-m', strtotime("-5 month")));
                }
    
    
                $currentdate = $start;
                while($currentdate <= $end)
                {
                    $data['month'] = date('m', $currentdate);
                    $data['year']  = date('Y', $currentdate);
    
                    if($request->type == 'revenue' || !isset($request->type))
                    {
                        $revenues->Orwhere(
                            function ($query) use ($data){
                                $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                                $query->get();
                            }
                        );
    
                        $revenueAccounts->Orwhere(
                            function ($query) use ($data){
                                $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                                $query->get();
                            }
                        );
                    }
    
                    if($request->type == 'payment')
                    {
                        $paymentAccounts->Orwhere(
                            function ($query) use ($data){
                                $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                                $query->get();
                            }
                        );
                    }
    
    
                    $currentdate = strtotime('+1 month', $currentdate);
                }
    
                if(!empty($request->account))
                {
                    if($request->type == 'revenue' || !isset($request->type))
                    {
                        $revenues->where('account_id', $request->account);
                        $revenues->get();
                        $revenueAccounts->where('account_id', $request->account);
                        $revenueAccounts->get();
                    }
    
                    if($request->type == 'payment')
                    {
                        $payments->where('account_id', $request->account);
                        $payments->get();
    
                        $paymentAccounts->where('account_id', $request->account);
                        $paymentAccounts->get();
                    }
    
    
                    $bankAccount       = BankAccount::find($request->account);
                    $filter['account'] = !empty($bankAccount) ? $bankAccount->holder_name . ' - ' . $bankAccount->bank_name : '';
                    if($bankAccount->holder_name == 'Cash')
                    {
                        $filter['account'] = 'Cash';
                    }
    
                }
    
                if($request->type == 'revenue' || !isset($request->type))
                {
                    $reportData['revenues'] = $revenues->get();
    
                    $revenueAccounts->get();
                    $reportData['revenueAccounts'] = $revenueAccounts->get();
    
                }
    
                if($request->type == 'payment')
                {
                    $reportData['payments'] = $payments->get();
    
                    $paymentAccounts->get();
                    $reportData['paymentAccounts'] = $paymentAccounts->get();
                    $filter['type']                = __('Payment');
                }
    
    
                $filter['startDateRange'] = date('M-Y', $start);
                $filter['endDateRange']   = date('M-Y', $end);
    
    
                return view('report.statement_report', compact('reportData', 'account', 'types', 'filter'));
            }
            elseif($user->type = 'company'){
                $filter['account']             = __('All');
                $filter['type']                = __('Revenue');
                $reportData['revenues']        = '';
                $reportData['payments']        = '';
                $reportData['revenueAccounts'] = '';
                $reportData['paymentAccounts'] = '';
    
                $account = BankAccount::all()->pluck('holder_name', 'id');
                $account->prepend('Select Account', '');
    
                $types = [
                    'revenue' => __('Revenue'),
                    'payment' => __('Payment'),
                ];
    
                if($request->type == 'revenue' || !isset($request->type))
                {
    
                    $revenueAccounts = Revenue::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'revenues.account_id', '=', 'bank_accounts.id')->groupBy('revenues.account_id')->selectRaw('sum(amount) as total');
    
                    $revenues = Revenue::orderBy('id', 'desc');
                }
    
                if($request->type == 'payment')
                {
                    $paymentAccounts = Payment::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'payments.account_id', '=', 'bank_accounts.id')->groupBy('payments.account_id')->selectRaw('sum(amount) as total');
    
                    $payments = Payment::orderBy('id', 'desc');
                }
    
    
                if(!empty($request->start_month) && !empty($request->end_month))
                {
                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                }
                else
                {
                    $start = strtotime(date('Y-m'));
                    $end   = strtotime(date('Y-m', strtotime("-5 month")));
                }
    
    
                $currentdate = $start;
                while($currentdate <= $end)
                {
                    $data['month'] = date('m', $currentdate);
                    $data['year']  = date('Y', $currentdate);
    
                    if($request->type == 'revenue' || !isset($request->type))
                    {
                        $revenues->Orwhere(
                            function ($query) use ($data){
                                $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                                $query->get();
                            }
                        );
    
                        $revenueAccounts->Orwhere(
                            function ($query) use ($data){
                                $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                                $query->get();
                            }
                        );
                    }
    
                    if($request->type == 'payment')
                    {
                        $paymentAccounts->Orwhere(
                            function ($query) use ($data){
                                $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                                $query->get();
                            }
                        );
                    }
    
    
                    $currentdate = strtotime('+1 month', $currentdate);
                }
    
                if(!empty($request->account))
                {
                    if($request->type == 'revenue' || !isset($request->type))
                    {
                        $revenues->where('account_id', $request->account);
                        $revenues->get();
                        $revenueAccounts->where('account_id', $request->account);
                        $revenueAccounts->get();
                    }
    
                    if($request->type == 'payment')
                    {
                        $payments->where('account_id', $request->account);
                        $payments->get();
    
                        $paymentAccounts->where('account_id', $request->account);
                        $paymentAccounts->get();
                    }
    
    
                    $bankAccount       = BankAccount::find($request->account);
                    $filter['account'] = !empty($bankAccount) ? $bankAccount->holder_name . ' - ' . $bankAccount->bank_name : '';
                    if($bankAccount->holder_name == 'Cash')
                    {
                        $filter['account'] = 'Cash';
                    }
    
                }
    
                if($request->type == 'revenue' || !isset($request->type))
                {
                    $reportData['revenues'] = $revenues->get();
    
                    $revenueAccounts->get();
                    $reportData['revenueAccounts'] = $revenueAccounts->get();
    
                }
    
                if($request->type == 'payment')
                {
                    $reportData['payments'] = $payments->get();
    
                    $paymentAccounts->get();
                    $reportData['paymentAccounts'] = $paymentAccounts->get();
                    $filter['type']                = __('Payment');
                }
    
    
                $filter['startDateRange'] = date('M-Y', $start);
                $filter['endDateRange']   = date('M-Y', $end);
    
    
                return view('report.statement_report', compact('reportData', 'account', 'types', 'filter'));
            }
            else{
                $filter['account']             = __('All');
                $filter['type']                = __('Revenue');
                $reportData['revenues']        = '';
                $reportData['payments']        = '';
                $reportData['revenueAccounts'] = '';
                $reportData['paymentAccounts'] = '';
    
                $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
                $account->prepend('Select Account', '');
    
                $types = [
                    'revenue' => __('Revenue'),
                    'payment' => __('Payment'),
                ];
    
                if($request->type == 'revenue' || !isset($request->type))
                {
    
                    $revenueAccounts = Revenue::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'revenues.account_id', '=', 'bank_accounts.id')->groupBy('revenues.account_id')->selectRaw('sum(amount) as total')->where('revenues.created_by', '=', \Auth::user()->creatorId());
    
                    $revenues = Revenue::where('revenues.created_by', '=', \Auth::user()->creatorId())->orderBy('id', 'desc');
                }
    
                if($request->type == 'payment')
                {
                    $paymentAccounts = Payment::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'payments.account_id', '=', 'bank_accounts.id')->groupBy('payments.account_id')->selectRaw('sum(amount) as total')->where('payments.created_by', '=', \Auth::user()->creatorId());
    
                    $payments = Payment::where('payments.created_by', '=', \Auth::user()->creatorId())->orderBy('id', 'desc');
                }
    
    
                if(!empty($request->start_month) && !empty($request->end_month))
                {
                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                }
                else
                {
                    $start = strtotime(date('Y-m'));
                    $end   = strtotime(date('Y-m', strtotime("-5 month")));
                }
    
    
                $currentdate = $start;
                while($currentdate <= $end)
                {
                    $data['month'] = date('m', $currentdate);
                    $data['year']  = date('Y', $currentdate);
    
                    if($request->type == 'revenue' || !isset($request->type))
                    {
                        $revenues->Orwhere(
                            function ($query) use ($data){
                                $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                                $query->where('revenues.created_by', '=', \Auth::user()->creatorId());
                            }
                        );
    
                        $revenueAccounts->Orwhere(
                            function ($query) use ($data){
                                $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                                $query->where('revenues.created_by', '=', \Auth::user()->creatorId());
                            }
                        );
                    }
    
                    if($request->type == 'payment')
                    {
                        $paymentAccounts->Orwhere(
                            function ($query) use ($data){
                                $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                                $query->where('payments.created_by', '=', \Auth::user()->creatorId());
                            }
                        );
                    }
    
    
                    $currentdate = strtotime('+1 month', $currentdate);
                }
    
                if(!empty($request->account))
                {
                    if($request->type == 'revenue' || !isset($request->type))
                    {
                        $revenues->where('account_id', $request->account);
                        $revenues->where('revenues.created_by', '=', \Auth::user()->creatorId());
                        $revenueAccounts->where('account_id', $request->account);
                        $revenueAccounts->where('revenues.created_by', '=', \Auth::user()->creatorId());
                    }
    
                    if($request->type == 'payment')
                    {
                        $payments->where('account_id', $request->account);
                        $payments->where('payments.created_by', '=', \Auth::user()->creatorId());
    
                        $paymentAccounts->where('account_id', $request->account);
                        $paymentAccounts->where('payments.created_by', '=', \Auth::user()->creatorId());
                    }
    
    
                    $bankAccount       = BankAccount::find($request->account);
                    $filter['account'] = !empty($bankAccount) ? $bankAccount->holder_name . ' - ' . $bankAccount->bank_name : '';
                    if($bankAccount->holder_name == 'Cash')
                    {
                        $filter['account'] = 'Cash';
                    }
    
                }
    
                if($request->type == 'revenue' || !isset($request->type))
                {
                    $reportData['revenues'] = $revenues->get();
    
                    $revenueAccounts->where('revenues.created_by', '=', \Auth::user()->creatorId());
                    $reportData['revenueAccounts'] = $revenueAccounts->get();
    
                }
    
                if($request->type == 'payment')
                {
                    $reportData['payments'] = $payments->get();
    
                    $paymentAccounts->where('payments.created_by', '=', \Auth::user()->creatorId());
                    $reportData['paymentAccounts'] = $paymentAccounts->get();
                    $filter['type']                = __('Payment');
                }
    
    
                $filter['startDateRange'] = date('M-Y', $start);
                $filter['endDateRange']   = date('M-Y', $end);
    
    
                return view('report.statement_report', compact('reportData', 'account', 'types', 'filter'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function balanceSheet(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('bill report'))
        {

            if($user->type = 'admin')
            {
                if(!empty($request->start_date) && !empty($request->end_date))
                {
                    $start = $request->start_date;
                    $end   = $request->end_date;
                }
                else
                {
                    $start = date('Y-m-01');
                    $end   = date('Y-m-t');
                }
    
                $types         = ChartOfAccountType::all();
                $chartAccounts = [];
                foreach($types as $type)
                {
                    $subTypes     = ChartOfAccountSubType::where('type', $type->id)->get();
                    $subTypeArray = [];
                    foreach($subTypes as $subType)
                    {
                        $accounts     = ChartOfAccount::where('type', $type->id)->where('sub_type', $subType->id)->get();
                        $accountArray = [];
                        foreach($accounts as $account)
                        {
    
                            $journalItem = JournalItem::select(\DB::raw('sum(credit) as totalCredit'), \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) - sum(debit) as netAmount'))->where('account', $account->id);
                            $journalItem->where('created_at', '>=', $start);
                            $journalItem->where('created_at', '<=', $end);
                            $journalItem          = $journalItem->first();
                            $data['account_name'] = $account->name;
                            $data['totalCredit']  = $journalItem->totalCredit;
                            $data['totalDebit']   = $journalItem->totalDebit;
                            $data['netAmount']    = $journalItem->netAmount;
                            $accountArray[]       = $data;
                        }
                        $subTypeData['subType'] = $subType->name;
                        $subTypeData['account'] = $accountArray;
                        $subTypeArray[]         = $subTypeData;
                    }
    
                    $chartAccounts[$type->name]=$subTypeArray;
                }
    
                $filter['startDateRange'] = $start;
                $filter['endDateRange']   = $end;
    
    
                return view('report.balance_sheet', compact('filter', 'chartAccounts'));
            }
            elseif($user->type = 'company')
            {
                if(!empty($request->start_date) && !empty($request->end_date))
                {
                    $start = $request->start_date;
                    $end   = $request->end_date;
                }
                else
                {
                    $start = date('Y-m-01');
                    $end   = date('Y-m-t');
                }
    
                $types         = ChartOfAccountType::all();
                $chartAccounts = [];
                foreach($types as $type)
                {
                    $subTypes     = ChartOfAccountSubType::where('type', $type->id)->get();
                    $subTypeArray = [];
                    foreach($subTypes as $subType)
                    {
                        $accounts     = ChartOfAccount::where('type', $type->id)->where('sub_type', $subType->id)->get();
                        $accountArray = [];
                        foreach($accounts as $account)
                        {
    
                            $journalItem = JournalItem::select(\DB::raw('sum(credit) as totalCredit'), \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) - sum(debit) as netAmount'))->where('account', $account->id);
                            $journalItem->where('created_at', '>=', $start);
                            $journalItem->where('created_at', '<=', $end);
                            $journalItem          = $journalItem->first();
                            $data['account_name'] = $account->name;
                            $data['totalCredit']  = $journalItem->totalCredit;
                            $data['totalDebit']   = $journalItem->totalDebit;
                            $data['netAmount']    = $journalItem->netAmount;
                            $accountArray[]       = $data;
                        }
                        $subTypeData['subType'] = $subType->name;
                        $subTypeData['account'] = $accountArray;
                        $subTypeArray[]         = $subTypeData;
                    }
    
                    $chartAccounts[$type->name]=$subTypeArray;
                }
    
                $filter['startDateRange'] = $start;
                $filter['endDateRange']   = $end;
    
    
                return view('report.balance_sheet', compact('filter', 'chartAccounts'));
            }
            else
            {
                if(!empty($request->start_date) && !empty($request->end_date))
                {
                    $start = $request->start_date;
                    $end   = $request->end_date;
                }
                else
                {
                    $start = date('Y-m-01');
                    $end   = date('Y-m-t');
                }
    
                $types         = ChartOfAccountType::where('created_by',\Auth::user()->creatorId())->get();
                $chartAccounts = [];
                foreach($types as $type)
                {
                    $subTypes     = ChartOfAccountSubType::where('type', $type->id)->get();
                    $subTypeArray = [];
                    foreach($subTypes as $subType)
                    {
                        $accounts     = ChartOfAccount::where('type', $type->id)->where('sub_type', $subType->id)->get();
                        $accountArray = [];
                        foreach($accounts as $account)
                        {
    
                            $journalItem = JournalItem::select(\DB::raw('sum(credit) as totalCredit'), \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) - sum(debit) as netAmount'))->where('account', $account->id);
                            $journalItem->where('created_at', '>=', $start);
                            $journalItem->where('created_at', '<=', $end);
                            $journalItem          = $journalItem->first();
                            $data['account_name'] = $account->name;
                            $data['totalCredit']  = $journalItem->totalCredit;
                            $data['totalDebit']   = $journalItem->totalDebit;
                            $data['netAmount']    = $journalItem->netAmount;
                            $accountArray[]       = $data;
                        }
                        $subTypeData['subType'] = $subType->name;
                        $subTypeData['account'] = $accountArray;
                        $subTypeArray[]         = $subTypeData;
                    }
    
                    $chartAccounts[$type->name]=$subTypeArray;
                }
    
                $filter['startDateRange'] = $start;
                $filter['endDateRange']   = $end;
    
    
                return view('report.balance_sheet', compact('filter', 'chartAccounts'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function ledgerSummary(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('ledger report'))
        {
            if($user->type = 'admin')
            {
                $accounts = ChartOfAccount::all()->pluck('name', 'id');
                $accounts->prepend('Select Account', '');
    
    
                if(!empty($request->start_date) && !empty($request->end_date))
                {
                    $start = $request->start_date;
                    $end   = $request->end_date;
                }
                else
                {
                    $start = date('Y-m-01');
                    $end   = date('Y-m-t');
                }
    
                if(!empty($request->account))
                {
                    $account = ChartOfAccount::find($request->account);
                }
                else
                {
                    $account = ChartOfAccount::first();
                }
    
    
                $journalItems = JournalItem::select('journal_entries.journal_id', 'journal_entries.date as transaction_date', 'journal_items.*')->leftjoin('journal_entries', 'journal_entries.id', 'journal_items.journal')->where('account', !empty($account) ? $account->id : 0);
                $journalItems->where('date', '>=', $start);
                $journalItems->where('date', '<=', $end);
                $journalItems = $journalItems->get();
    
                $balance = 0;
                $debit   = 0;
                $credit  = 0;
                foreach($journalItems as $item)
                {
                    if($item->debit > 0)
                    {
                        $debit += $item->debit;
                    }
    
                    else
                    {
                        $credit += $item->credit;
                    }
    
                    $balance = $credit - $debit;
                }
    
                $filter['balance']        = $balance;
                $filter['credit']         = $credit;
                $filter['debit']          = $debit;
                $filter['startDateRange'] = $start;
                $filter['endDateRange']   = $end;
    
    
                return view('report.ledger_summary', compact('filter', 'journalItems', 'account', 'accounts'));
            }
            elseif($user->type = 'company')
            {
                $accounts = ChartOfAccount::all()->pluck('name', 'id');
                $accounts->prepend('Select Account', '');
    
    
                if(!empty($request->start_date) && !empty($request->end_date))
                {
                    $start = $request->start_date;
                    $end   = $request->end_date;
                }
                else
                {
                    $start = date('Y-m-01');
                    $end   = date('Y-m-t');
                }
    
                if(!empty($request->account))
                {
                    $account = ChartOfAccount::find($request->account);
                }
                else
                {
                    $account = ChartOfAccount::first();
                }
    
    
                $journalItems = JournalItem::select('journal_entries.journal_id', 'journal_entries.date as transaction_date', 'journal_items.*')->leftjoin('journal_entries', 'journal_entries.id', 'journal_items.journal')->where('account', !empty($account) ? $account->id : 0);
                $journalItems->where('date', '>=', $start);
                $journalItems->where('date', '<=', $end);
                $journalItems = $journalItems->get();
    
                $balance = 0;
                $debit   = 0;
                $credit  = 0;
                foreach($journalItems as $item)
                {
                    if($item->debit > 0)
                    {
                        $debit += $item->debit;
                    }
    
                    else
                    {
                        $credit += $item->credit;
                    }
    
                    $balance = $credit - $debit;
                }
    
                $filter['balance']        = $balance;
                $filter['credit']         = $credit;
                $filter['debit']          = $debit;
                $filter['startDateRange'] = $start;
                $filter['endDateRange']   = $end;
    
    
                return view('report.ledger_summary', compact('filter', 'journalItems', 'account', 'accounts'));
            }
            else
            {
                $accounts = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $accounts->prepend('Select Account', '');
    
    
                if(!empty($request->start_date) && !empty($request->end_date))
                {
                    $start = $request->start_date;
                    $end   = $request->end_date;
                }
                else
                {
                    $start = date('Y-m-01');
                    $end   = date('Y-m-t');
                }
    
                if(!empty($request->account))
                {
                    $account = ChartOfAccount::find($request->account);
                }
                else
                {
                    $account = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->first();
                }
    
    
                $journalItems = JournalItem::select('journal_entries.journal_id', 'journal_entries.date as transaction_date', 'journal_items.*')->leftjoin('journal_entries', 'journal_entries.id', 'journal_items.journal')->where('journal_entries.created_by', '=', \Auth::user()->creatorId())->where('account', !empty($account) ? $account->id : 0);
                $journalItems->where('date', '>=', $start);
                $journalItems->where('date', '<=', $end);
                $journalItems = $journalItems->get();
    
                $balance = 0;
                $debit   = 0;
                $credit  = 0;
                foreach($journalItems as $item)
                {
                    if($item->debit > 0)
                    {
                        $debit += $item->debit;
                    }
    
                    else
                    {
                        $credit += $item->credit;
                    }
    
                    $balance = $credit - $debit;
                }
    
                $filter['balance']        = $balance;
                $filter['credit']         = $credit;
                $filter['debit']          = $debit;
                $filter['startDateRange'] = $start;
                $filter['endDateRange']   = $end;
    
    
                return view('report.ledger_summary', compact('filter', 'journalItems', 'account', 'accounts'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function trialBalanceSummary(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('trial balance report'))
        {
            if($user->type = 'admin')
            {
                if(!empty($request->start_date) && !empty($request->end_date))
                {
                    $start = $request->start_date;
                    $end   = $request->end_date;
                }
                else
                {
                    $start = date('Y-m-01');
                    $end   = date('Y-m-t');
                }
    
                $journalItem = JournalItem::select('chart_of_accounts.name', \DB::raw('sum(credit) as totalCredit'), \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) - sum(debit) as netAmount'));
                $journalItem->leftjoin('journal_entries', 'journal_entries.id', 'journal_items.journal');
                $journalItem->leftjoin('chart_of_accounts', 'journal_items.account', 'chart_of_accounts.id');
                $journalItem->where('journal_items.created_at', '>=', $start);
                $journalItem->where('journal_items.created_at', '<=', $end);
                $journalItem->groupBy('account');
                $journalItem = $journalItem->get()->toArray();
    
                $filter['startDateRange'] = $start;
                $filter['endDateRange']   = $end;
    
                return view('report.trial_balance', compact('filter', 'journalItem'));
            }
            elseif($user->type = 'company')
            {
                if(!empty($request->start_date) && !empty($request->end_date))
                {
                    $start = $request->start_date;
                    $end   = $request->end_date;
                }
                else
                {
                    $start = date('Y-m-01');
                    $end   = date('Y-m-t');
                }
    
                $journalItem = JournalItem::select('chart_of_accounts.name', \DB::raw('sum(credit) as totalCredit'), \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) - sum(debit) as netAmount'));
                $journalItem->leftjoin('journal_entries', 'journal_entries.id', 'journal_items.journal');
                $journalItem->leftjoin('chart_of_accounts', 'journal_items.account', 'chart_of_accounts.id');
                $journalItem->where('journal_items.created_at', '>=', $start);
                $journalItem->where('journal_items.created_at', '<=', $end);
                $journalItem->groupBy('account');
                $journalItem = $journalItem->get()->toArray();
    
                $filter['startDateRange'] = $start;
                $filter['endDateRange']   = $end;
    
                return view('report.trial_balance', compact('filter', 'journalItem'));
            }
            else
            {
                if(!empty($request->start_date) && !empty($request->end_date))
                {
                    $start = $request->start_date;
                    $end   = $request->end_date;
                }
                else
                {
                    $start = date('Y-m-01');
                    $end   = date('Y-m-t');
                }
    
                $journalItem = JournalItem::select('chart_of_accounts.name', \DB::raw('sum(credit) as totalCredit'), \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) - sum(debit) as netAmount'));
                $journalItem->leftjoin('journal_entries', 'journal_entries.id', 'journal_items.journal');
                $journalItem->leftjoin('chart_of_accounts', 'journal_items.account', 'chart_of_accounts.id');
                $journalItem->where('chart_of_accounts.created_by',\Auth::user()->creatorId());
                $journalItem->where('journal_items.created_at', '>=', $start);
                $journalItem->where('journal_items.created_at', '<=', $end);
                $journalItem->groupBy('account');
                $journalItem = $journalItem->get()->toArray();
    
                $filter['startDateRange'] = $start;
                $filter['endDateRange']   = $end;
    
                return view('report.trial_balance', compact('filter', 'journalItem'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function leave(Request $request)
    {

        if(\Auth::user()->can('manage report'))
        {

            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('Select Department', '');

            $leave_type = LeaveType::where('created_by', \Auth::user()->creatorId())->get()->pluck('title', 'id');
            $leave_type->prepend('Select Leave Type', '');

            $filterYear['branch']        = __('All');
            $filterYear['department']    = __('All');
            $filterYear['type']          = __('Monthly');
            $filterYear['dateYearRange'] = date('M-Y');
            $employees                   = Employee::whereHas('user', function($query) {
                $query->where('is_active', 1);
            })->where('created_by', \Auth::user()->creatorId());
            if(!empty($request->branch))
            {
                $employees->where('branch_id', $request->branch);
                $filterYear['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }
            if(!empty($request->department))
            {
                $employees->where('department_id', $request->department);
                $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }


            $employees = $employees->get();

            $leaves        = [];
            $totalApproved = $totalReject = $totalPending = 0;
            foreach($employees as $employee)
            {

                $employeeLeave['id']          = $employee->id;
                $employeeLeave['employee_id'] = $employee->employee_id;
                $employeeLeave['employee']    = $employee->name;

                $approved = Leave::where('employee_id', $employee->id)->where('status', 'Approved')->where('absence_type', '=', 'leave');
                $reject   = Leave::where('employee_id', $employee->id)->where('status', 'Reject')->where('absence_type', '=', 'leave');
                $pending  = Leave::where('employee_id', $employee->id)->where('status', 'Pending')->where('absence_type', '=', 'leave');

                if($request->type == 'monthly' && !empty($request->month))
                {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));

                    $approved->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $reject->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $pending->whereMonth('applied_on', $month)->whereYear('applied_on', $year);

                    $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
                    $filterYear['type']          = __('Monthly');

                }
                elseif(!isset($request->type))
                {
                    $month     = date('m');
                    $year      = date('Y');
                    $monthYear = date('Y-m');

                    $approved->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $reject->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $pending->whereMonth('applied_on', $month)->whereYear('applied_on', $year);

                    $filterYear['dateYearRange'] = date('M-Y', strtotime($monthYear));
                    $filterYear['type']          = __('Monthly');
                }


                if($request->type == 'yearly' && !empty($request->year))
                {
                    $approved->whereYear('applied_on', $request->year);
                    $reject->whereYear('applied_on', $request->year);
                    $pending->whereYear('applied_on', $request->year);


                    $filterYear['dateYearRange'] = $request->year;
                    $filterYear['type']          = __('Yearly');
                }

                $approved = $approved->count();
                $reject   = $reject->count();
                $pending  = $pending->count();

                $totalApproved += $approved;
                $totalReject   += $reject;
                $totalPending  += $pending;

                if (!empty($request->leave_type_id)) {
                    $leaveType = LeaveType::find($request->leave_type_id);
            
                    $totalApprovedLeaveDays = Leave::where('employee_id', $employee->id)
                                                    ->where('leave_type_id', $leaveType->id)
                                                    ->where('status', 'Approved')
                                                    ->whereYear('created_at', now()->year)
                                                    ->sum('total_leave_days');
            
                    $remainingLeaves = $leaveType->days - $totalApprovedLeaveDays;
                } else {
                    $remainingLeaves = 0;
                }

                $employeeLeave['approved'] = $approved;
                $employeeLeave['reject']   = $reject;
                $employeeLeave['pending']  = $pending;
                $employeeLeave['remaining'] = $remainingLeaves;


                $leaves[] = $employeeLeave;
            }

            $starting_year = date('Y', strtotime('-5 year'));
            $ending_year   = date('Y', strtotime('+5 year'));

            $filterYear['starting_year'] = $starting_year;
            $filterYear['ending_year']   = $ending_year;

            $filter['totalApproved'] = $totalApproved;
            $filter['totalReject']   = $totalReject;
            $filter['totalPending']  = $totalPending;


            return view('report.leave', compact('department', 'branch', 'leaves', 'filterYear', 'filter', 'leave_type'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function employeeLeave(Request $request, $employee_id, $status, $type, $month, $year)
    {
        $user = Auth::user();
        if(\Auth::user()->can('manage report'))
        {
            if($user->type = 'admin'){
                $leaveTypes = LeaveType::all();
                $leaves     = [];
                foreach($leaveTypes as $leaveType)
                {
                    $leave        = new Leave();
                    $leave->title = $leaveType->title;
                    $totalLeave   = Leave::where('employee_id', $employee_id)->where('status', $status)->where('leave_type_id', $leaveType->id)->where('absence_type', '=', 'leave');
                    if($type == 'yearly')
                    {
                        $totalLeave->whereYear('applied_on', $year);
                    }
                    else
                    {
                        $m = date('m', strtotime($month));
                        $y = date('Y', strtotime($month));
    
                        $totalLeave->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                    }
                    $totalLeave = $totalLeave->count();
    
                    $leave->total = $totalLeave;
                    $leaves[]     = $leave;
                }
    
                $leaveData = Leave::where('employee_id', $employee_id)->where('status', $status)->where('absence_type', '=', 'leave');
                if($type == 'yearly')
                {
                    $leaveData->whereYear('applied_on', $year);
                }
                else
                {
                    $m = date('m', strtotime($month));
                    $y = date('Y', strtotime($month));
    
                    $leaveData->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                }
    
    
                $leaveData = $leaveData->get();
    
    
                return view('report.leaveShow', compact('leaves', 'leaveData'));
            }
            elseif($user->type = 'company'){
                $leaveTypes = LeaveType::all();
                $leaves     = [];
                foreach($leaveTypes as $leaveType)
                {
                    $leave        = new Leave();
                    $leave->title = $leaveType->title;
                    $totalLeave   = Leave::where('employee_id', $employee_id)->where('status', $status)->where('leave_type_id', $leaveType->id)->where('absence_type', '=', 'leave');
                    if($type == 'yearly')
                    {
                        $totalLeave->whereYear('applied_on', $year);
                    }
                    else
                    {
                        $m = date('m', strtotime($month));
                        $y = date('Y', strtotime($month));
    
                        $totalLeave->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                    }
                    $totalLeave = $totalLeave->count();
    
                    $leave->total = $totalLeave;
                    $leaves[]     = $leave;
                }
    
                $leaveData = Leave::where('employee_id', $employee_id)->where('status', $status)->where('absence_type', '=', 'leave');
                if($type == 'yearly')
                {
                    $leaveData->whereYear('applied_on', $year);
                }
                else
                {
                    $m = date('m', strtotime($month));
                    $y = date('Y', strtotime($month));
    
                    $leaveData->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                }
    
    
                $leaveData = $leaveData->get();
    
    
                return view('report.leaveShow', compact('leaves', 'leaveData'));
            }
            else{
                $leaveTypes = LeaveType::where('created_by', \Auth::user()->creatorId())->get();
                $leaves     = [];
                foreach($leaveTypes as $leaveType)
                {
                    $leave        = new Leave();
                    $leave->title = $leaveType->title;
                    $totalLeave   = Leave::where('employee_id', $employee_id)->where('status', $status)->where('leave_type_id', $leaveType->id)->where('absence_type', '=', 'leave');
                    if($type == 'yearly')
                    {
                        $totalLeave->whereYear('applied_on', $year);
                    }
                    else
                    {
                        $m = date('m', strtotime($month));
                        $y = date('Y', strtotime($month));
    
                        $totalLeave->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                    }
                    $totalLeave = $totalLeave->count();
    
                    $leave->total = $totalLeave;
                    $leaves[]     = $leave;
                }
    
                $leaveData = Leave::where('employee_id', $employee_id)->where('status', $status)->where('absence_type', '=', 'leave');
                if($type == 'yearly')
                {
                    $leaveData->whereYear('applied_on', $year);
                }
                else
                {
                    $m = date('m', strtotime($month));
                    $y = date('Y', strtotime($month));
    
                    $leaveData->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                }
    
    
                $leaveData = $leaveData->get();
    
    
                return view('report.leaveShow', compact('leaves', 'leaveData'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }
    public function monthlyAttendance(Request $request)
    {
        $user = Auth::user();
        if (\Auth::user()->can('manage report')) {
            $branch = Branch::get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = Department::get();

            $data['branch'] = __('All');
            $data['department'] = __('All');

            $employees = Employee::select('id', 'name')
                ->whereHas('user', function($query) {
                    $query->where('is_active', 1);
                });

            if (!empty($request->employee_id) && $request->employee_id[0] != 0) {
                $employees->whereIn('id', $request->employee_id);
            }

            if (!empty($request->branch)) {
                $employees->where('branch_id', $request->branch);
                $data['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }

            if (!empty($request->department)) {
                $employees->where('department_id', $request->department);
                $data['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }

            $employees = $employees->get()->pluck('name', 'id');

            // Check if start_date and end_date are provided
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;
            } else {
                // Default to current month's dates
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-t');
            }

            $dates = [];
            $currentDate = $startDate;
            while (strtotime($currentDate) <= strtotime($endDate)) {
                $dates[] = $currentDate;
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            }

            $employeesAttendance = [];
            $totalPresent = $totalLeave = $totalEarlyLeave = 0;
            $overtimeHours = $overtimeMins = $earlyleaveHours = $earlyleaveMins = $lateHours = $lateMins = 0;

            foreach ($employees as $id => $employee) {
                $attendances['name'] = $employee;
                $attendanceStatus = [];
                $attendanceLong = [];
                $attendanceLat = [];
                $attendanceRad = [];

                foreach ($dates as $date) {
                    if ($date <= date('Y-m-d')) {
                        $employeeAttendance = AttendanceEmployee::where('employee_id', $id)
                            ->where('date', $date)
                            ->first();

                        if (!empty($employeeAttendance) && $employeeAttendance->status == 'Present') {
                            $attendanceStatus[$date] = 'P';
                            $attendanceLong[$date] = $employeeAttendance->longitude;
                            $attendanceLat[$date] = $employeeAttendance->latitude;
                            $attendanceRad[$date] = $employeeAttendance->distance_from_office;
                            $totalPresent++;

                            if ($employeeAttendance->overtime > 0) {
                                $overtimeHours += date('H', strtotime($employeeAttendance->overtime));
                                $overtimeMins += date('i', strtotime($employeeAttendance->overtime));
                            }

                            if ($employeeAttendance->early_leaving > 0) {
                                $earlyleaveHours += date('H', strtotime($employeeAttendance->early_leaving));
                                $earlyleaveMins += date('i', strtotime($employeeAttendance->early_leaving));
                            }

                            if ($employeeAttendance->late > 0) {
                                $lateHours += date('H', strtotime($employeeAttendance->late));
                                $lateMins += date('i', strtotime($employeeAttendance->late));
                            }
                        } elseif (!empty($employeeAttendance) && $employeeAttendance->status == 'Leave') {
                            $attendanceStatus[$date] = 'A';
                            $totalLeave++;
                        } else {
                            $attendanceStatus[$date] = '';
                            $attendanceLong[$date] = '';
                            $attendanceRad[$date] = '';
                            $attendanceLat[$date] = '';
                        }
                    } else {
                        $attendanceStatus[$date] = '';
                        $attendanceLong[$date] = '';
                        $attendanceLat[$date] = '';
                        $attendanceRad[$date] = '';
                    }
                }

                $attendances['status'] = $attendanceStatus;
                $attendances['longitude'] = $attendanceLong;
                $attendances['latitude'] = $attendanceLat;
                $attendances['radius'] = $attendanceRad;
                $employeesAttendance[] = $attendances;
            }

            $totalOverTime = $overtimeHours + ($overtimeMins / 60);
            $totalEarlyleave = $earlyleaveHours + ($earlyleaveMins / 60);
            $totalLate = $lateHours + ($lateMins / 60);

            $data['totalOvertime'] = $totalOverTime;
            $data['totalEarlyLeave'] = $totalEarlyleave;
            $data['totalLate'] = $totalLate;
            $data['totalPresent'] = $totalPresent;
            $data['totalLeave'] = $totalLeave;

            return view('report.monthlyAttendance', compact('employeesAttendance', 'branch', 'department', 'dates', 'data'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function payroll(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('manage report'))
        {
            if($user->type = 'admin'){
                $branch = Branch::all()->pluck('name', 'id');
                $branch->prepend('Select Branch', '');
    
                $department = Department::all()->pluck('name', 'id');
                $department->prepend('Select Department', '');
    
                $filterYear['branch']     = __('All');
                $filterYear['department'] = __('All');
                $filterYear['type']       = __('Monthly');
    
                $payslips = PaySlip::select('pay_slips.*', 'employees.name')->leftjoin('employees', 'pay_slips.employee_id', '=', 'employees.id');
    
    
                if($request->type == 'monthly' && !empty($request->month))
                {
    
                    $payslips->where('salary_month', $request->month);
    
                    $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
                    $filterYear['type']          = __('Monthly');
                }
                elseif(!isset($request->type))
                {
                    $month = date('Y-m');
    
                    $payslips->where('salary_month', $month);
    
                    $filterYear['dateYearRange'] = date('M-Y', strtotime($month));
                    $filterYear['type']          = __('Monthly');
                }
    
    
                if($request->type == 'yearly' && !empty($request->year))
                {
                    $startMonth = $request->year . '-01';
                    $endMonth   = $request->year . '-12';
                    $payslips->where('salary_month', '>=', $startMonth)->where('salary_month', '<=', $endMonth);
    
                    $filterYear['dateYearRange'] = $request->year;
                    $filterYear['type']          = __('Yearly');
                }
    
    
                if(!empty($request->branch))
                {
                    $payslips->where('employees.branch_id', $request->branch);
    
                    $filterYear['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
                }
    
                if(!empty($request->department))
                {
                    $payslips->where('employees.department_id', $request->department);
    
                    $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
                }
    
                $payslips = $payslips->get();
    
                $totalBasicSalary = $totalNetSalary = $totalAllowance = $totalCommision = $totalLoan = $totalSaturationDeduction = $totalOtherPayment = $totalOverTime = 0;
    
                foreach($payslips as $payslip)
                {
                    $totalBasicSalary += $payslip->basic_salary;
                    $totalNetSalary   += $payslip->net_payble;
    
                    $allowances = json_decode($payslip->allowance);
                    foreach($allowances as $allowance)
                    {
                        $totalAllowance += $allowance->amount;
    
                    }
    
                    $commisions = json_decode($payslip->commission);
                    foreach($commisions as $commision)
                    {
                        $totalCommision += $commision->amount;
    
                    }
    
                    $loans = json_decode($payslip->loan);
                    foreach($loans as $loan)
                    {
                        $totalLoan += $loan->amount;
                    }
    
                    $saturationDeductions = json_decode($payslip->saturation_deduction);
                    foreach($saturationDeductions as $saturationDeduction)
                    {
                        $totalSaturationDeduction += $saturationDeduction->amount;
                    }
    
                    $otherPayments = json_decode($payslip->other_payment);
                    foreach($otherPayments as $otherPayment)
                    {
                        $totalOtherPayment += $otherPayment->amount;
                    }
    
                    $overtimes = json_decode($payslip->overtime);
                    foreach($overtimes as $overtime)
                    {
                        $days  = $overtime->number_of_days;
                        $hours = $overtime->hours;
                        $rate  = $overtime->rate;
    
                        $totalOverTime += ($rate * $hours) * $days;
                    }
    
    
                }
    
                $filterData['totalBasicSalary']         = $totalBasicSalary;
                $filterData['totalNetSalary']           = $totalNetSalary;
                $filterData['totalAllowance']           = $totalAllowance;
                $filterData['totalCommision']           = $totalCommision;
                $filterData['totalLoan']                = $totalLoan;
                $filterData['totalSaturationDeduction'] = $totalSaturationDeduction;
                $filterData['totalOtherPayment']        = $totalOtherPayment;
                $filterData['totalOverTime']            = $totalOverTime;
    
    
                $starting_year = date('Y', strtotime('-5 year'));
                $ending_year   = date('Y', strtotime('+5 year'));
    
                $filterYear['starting_year'] = $starting_year;
                $filterYear['ending_year']   = $ending_year;
    
                return view('report.payroll', compact('payslips', 'filterData', 'branch', 'department', 'filterYear'));
            }
            elseif($user->type = 'company'){
                $branch = Branch::all()->pluck('name', 'id');
                $branch->prepend('Select Branch', '');
    
                $department = Department::all()->pluck('name', 'id');
                $department->prepend('Select Department', '');
    
                $filterYear['branch']     = __('All');
                $filterYear['department'] = __('All');
                $filterYear['type']       = __('Monthly');
    
                $payslips = PaySlip::select('pay_slips.*', 'employees.name')->leftjoin('employees', 'pay_slips.employee_id', '=', 'employees.id');
    
    
                if($request->type == 'monthly' && !empty($request->month))
                {
    
                    $payslips->where('salary_month', $request->month);
    
                    $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
                    $filterYear['type']          = __('Monthly');
                }
                elseif(!isset($request->type))
                {
                    $month = date('Y-m');
    
                    $payslips->where('salary_month', $month);
    
                    $filterYear['dateYearRange'] = date('M-Y', strtotime($month));
                    $filterYear['type']          = __('Monthly');
                }
    
    
                if($request->type == 'yearly' && !empty($request->year))
                {
                    $startMonth = $request->year . '-01';
                    $endMonth   = $request->year . '-12';
                    $payslips->where('salary_month', '>=', $startMonth)->where('salary_month', '<=', $endMonth);
    
                    $filterYear['dateYearRange'] = $request->year;
                    $filterYear['type']          = __('Yearly');
                }
    
    
                if(!empty($request->branch))
                {
                    $payslips->where('employees.branch_id', $request->branch);
    
                    $filterYear['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
                }
    
                if(!empty($request->department))
                {
                    $payslips->where('employees.department_id', $request->department);
    
                    $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
                }
    
                $payslips = $payslips->get();
    
                $totalBasicSalary = $totalNetSalary = $totalAllowance = $totalCommision = $totalLoan = $totalSaturationDeduction = $totalOtherPayment = $totalOverTime = 0;
    
                foreach($payslips as $payslip)
                {
                    $totalBasicSalary += $payslip->basic_salary;
                    $totalNetSalary   += $payslip->net_payble;
    
                    $allowances = json_decode($payslip->allowance);
                    foreach($allowances as $allowance)
                    {
                        $totalAllowance += $allowance->amount;
    
                    }
    
                    $commisions = json_decode($payslip->commission);
                    foreach($commisions as $commision)
                    {
                        $totalCommision += $commision->amount;
    
                    }
    
                    $loans = json_decode($payslip->loan);
                    foreach($loans as $loan)
                    {
                        $totalLoan += $loan->amount;
                    }
    
                    $saturationDeductions = json_decode($payslip->saturation_deduction);
                    foreach($saturationDeductions as $saturationDeduction)
                    {
                        $totalSaturationDeduction += $saturationDeduction->amount;
                    }
    
                    $otherPayments = json_decode($payslip->other_payment);
                    foreach($otherPayments as $otherPayment)
                    {
                        $totalOtherPayment += $otherPayment->amount;
                    }
    
                    $overtimes = json_decode($payslip->overtime);
                    foreach($overtimes as $overtime)
                    {
                        $days  = $overtime->number_of_days;
                        $hours = $overtime->hours;
                        $rate  = $overtime->rate;
    
                        $totalOverTime += ($rate * $hours) * $days;
                    }
    
    
                }
    
                $filterData['totalBasicSalary']         = $totalBasicSalary;
                $filterData['totalNetSalary']           = $totalNetSalary;
                $filterData['totalAllowance']           = $totalAllowance;
                $filterData['totalCommision']           = $totalCommision;
                $filterData['totalLoan']                = $totalLoan;
                $filterData['totalSaturationDeduction'] = $totalSaturationDeduction;
                $filterData['totalOtherPayment']        = $totalOtherPayment;
                $filterData['totalOverTime']            = $totalOverTime;
    
    
                $starting_year = date('Y', strtotime('-5 year'));
                $ending_year   = date('Y', strtotime('+5 year'));
    
                $filterYear['starting_year'] = $starting_year;
                $filterYear['ending_year']   = $ending_year;
    
                return view('report.payroll', compact('payslips', 'filterData', 'branch', 'department', 'filterYear'));
            }
            else{
                $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branch->prepend('Select Branch', '');
    
                $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $department->prepend('Select Department', '');
    
                $filterYear['branch']     = __('All');
                $filterYear['department'] = __('All');
                $filterYear['type']       = __('Monthly');
    
                $payslips = PaySlip::select('pay_slips.*', 'employees.name')->leftjoin('employees', 'pay_slips.employee_id', '=', 'employees.id')->where('pay_slips.created_by', \Auth::user()->creatorId());
    
    
                if($request->type == 'monthly' && !empty($request->month))
                {
    
                    $payslips->where('salary_month', $request->month);
    
                    $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
                    $filterYear['type']          = __('Monthly');
                }
                elseif(!isset($request->type))
                {
                    $month = date('Y-m');
    
                    $payslips->where('salary_month', $month);
    
                    $filterYear['dateYearRange'] = date('M-Y', strtotime($month));
                    $filterYear['type']          = __('Monthly');
                }
    
    
                if($request->type == 'yearly' && !empty($request->year))
                {
                    $startMonth = $request->year . '-01';
                    $endMonth   = $request->year . '-12';
                    $payslips->where('salary_month', '>=', $startMonth)->where('salary_month', '<=', $endMonth);
    
                    $filterYear['dateYearRange'] = $request->year;
                    $filterYear['type']          = __('Yearly');
                }
    
    
                if(!empty($request->branch))
                {
                    $payslips->where('employees.branch_id', $request->branch);
    
                    $filterYear['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
                }
    
                if(!empty($request->department))
                {
                    $payslips->where('employees.department_id', $request->department);
    
                    $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
                }
    
                $payslips = $payslips->get();
    
                $totalBasicSalary = $totalNetSalary = $totalAllowance = $totalCommision = $totalLoan = $totalSaturationDeduction = $totalOtherPayment = $totalOverTime = 0;
    
                foreach($payslips as $payslip)
                {
                    $totalBasicSalary += $payslip->basic_salary;
                    $totalNetSalary   += $payslip->net_payble;
    
                    $allowances = json_decode($payslip->allowance);
                    foreach($allowances as $allowance)
                    {
                        $totalAllowance += $allowance->amount;
    
                    }
    
                    $commisions = json_decode($payslip->commission);
                    foreach($commisions as $commision)
                    {
                        $totalCommision += $commision->amount;
    
                    }
    
                    $loans = json_decode($payslip->loan);
                    foreach($loans as $loan)
                    {
                        $totalLoan += $loan->amount;
                    }
    
                    $saturationDeductions = json_decode($payslip->saturation_deduction);
                    foreach($saturationDeductions as $saturationDeduction)
                    {
                        $totalSaturationDeduction += $saturationDeduction->amount;
                    }
    
                    $otherPayments = json_decode($payslip->other_payment);
                    foreach($otherPayments as $otherPayment)
                    {
                        $totalOtherPayment += $otherPayment->amount;
                    }
    
                    $overtimes = json_decode($payslip->overtime);
                    foreach($overtimes as $overtime)
                    {
                        $days  = $overtime->number_of_days;
                        $hours = $overtime->hours;
                        $rate  = $overtime->rate;
    
                        $totalOverTime += ($rate * $hours) * $days;
                    }
    
    
                }
    
                $filterData['totalBasicSalary']         = $totalBasicSalary;
                $filterData['totalNetSalary']           = $totalNetSalary;
                $filterData['totalAllowance']           = $totalAllowance;
                $filterData['totalCommision']           = $totalCommision;
                $filterData['totalLoan']                = $totalLoan;
                $filterData['totalSaturationDeduction'] = $totalSaturationDeduction;
                $filterData['totalOtherPayment']        = $totalOtherPayment;
                $filterData['totalOverTime']            = $totalOverTime;
    
    
                $starting_year = date('Y', strtotime('-5 year'));
                $ending_year   = date('Y', strtotime('+5 year'));
    
                $filterYear['starting_year'] = $starting_year;
                $filterYear['ending_year']   = $ending_year;
    
                return view('report.payroll', compact('payslips', 'filterData', 'branch', 'department', 'filterYear'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
    public function exportCsv($filter_month, $branch, $department)
    {
        $data['branch']=__('All');
        $data['department']=__('All');
        $employees = Employee::select('id', 'name')->where('created_by', \Auth::user()->creatorId());
        if($branch != 0)
        {
            $employees->where('branch_id', $branch);
            $data['branch'] = !empty(Branch::find($branch)) ? Branch::find($branch)->name : '';
        }

        if($department != 0)
        {
            $employees->where('department_id', $department);
            $data['department'] = !empty(Department::find($department)) ? Department::find($department)->name : '';
        }

        $employees = $employees->get()->pluck('name', 'id');


        $currentdate = strtotime($filter_month);
        $month       = date('m', $currentdate);
        $year        = date('Y', $currentdate);
        $data['curMonth']    = date('M-Y', strtotime($filter_month));


        $fileName = $data['branch'] . ' ' . __('Branch') . ' ' . $data['curMonth'] . ' ' . __('Attendance Report of') . ' ' . $data['department'] . ' ' . __('Department') . ' ' . '.csv';


        $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
        for($i = 1; $i <= $num_of_days; $i++)
        {
            $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        foreach($employees as $id => $employee)
        {
            $attendances['name'] = $employee;

            foreach($dates as $date)
            {

                $dateFormat = $year . '-' . $month . '-' . $date;

                if($dateFormat <= date('Y-m-d'))
                {
                    $employeeAttendance = AttendanceEmployee::where('employee_id', $id)->where('date', $dateFormat)->first();

                    if(!empty($employeeAttendance) && $employeeAttendance->status == 'Present')
                    {
                        $attendanceStatus[$date] = 'P';
                    }
                    elseif(!empty($employeeAttendance) && $employeeAttendance->status == 'Leave')
                    {
                        $attendanceStatus[$date] = 'A';
                    }
                    else
                    {
                        $attendanceStatus[$date] = '-';
                    }

                }
                else
                {
                    $attendanceStatus[$date] = '-';
                }
                $attendances[$date] = $attendanceStatus[$date];
            }

            $employeesAttendance[] = $attendances;
        }

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        );

        $emp = array(
            'employee',
        );

        $columns = array_merge($emp, $dates);

        $callback = function () use ($employeesAttendance, $columns){
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach($employeesAttendance as $attendance)
            {
                fputcsv($file, str_replace('"', '', array_values($attendance)));
            }


            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function productStock(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('stock report'))
        {
            if($user->type = 'admin'){
                $stocks = StockReport::all();
                return view('report.product_stock_report',compact('stocks'));
            }
            elseif($user->type = 'company'){
                $stocks = StockReport::all();
                return view('report.product_stock_report',compact('stocks'));
            }
            else{
                $stocks = StockReport::where('created_by', '=', \Auth::user()->creatorId())->get();
                return view('report.product_stock_report',compact('stocks'));
            }
            
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }

    //for export in account statement report
    public function export()
    {
        $name = 'account_statement' . date('Y-m-d i:h:s');
        $data = Excel::download(new AccountStatementExport(), $name . '.xlsx');

        return $data;
    }
    // for export in product stock report
    public function stock_export()
    {
        $name = 'Product_Stock' . date('Y-m-d i:h:s');
        $data = Excel::download(new ProductStockExport(), $name . '.xlsx');

        return $data;
    }

    // for export in payroll report
    public function PayrollReportExport(Request $request)
    {
        $name = 'Payroll_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new PayrollExport(), $name . '.xlsx');

        return $data;
    }

    // for export in leave report
    public function LeaveReportExport()
    {
        $name = 'leave_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new LeaveReportExport(), $name . '.xlsx');

        return $data;
    }


    //branch wise department get in monthly-attendance report
    // public function getdepartment(Request $request)
    // {
    //     if($request->branch_id == 0)
    //     {
    //         $departments = Department::get()->pluck('name', 'id')->toArray();
    //     }
    //     else
    //     {
    //         $departments = Department::where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
    //     }

    //     return response()->json($departments);
    // }

    // public function getemployee(Request $request)
    // {
    //     if(!$request->department_id )
    //     {
    //         $employees = Employee::get()->pluck('name', 'id')->toArray();
    //     }
    //     else
    //     {
    //         $employees = Employee::where('department_id', $request->department_id)->get()->pluck('name', 'id')->toArray();
    //     }
    //     return response()->json($employees);
    // }

    public function leadreport(Request $request)
    {
        $user      = \Auth::user();
        $leads = Lead::orderBy('id');
        $leads->where('created_by', \Auth::user()->creatorId());

        $user_week_lead = Lead::orderBy('created_at')->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->get()->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        });
        $carbaoDay = Carbon::now()->startOfWeek();

        $weeks = [];
        for ($i = 0; $i < 7; $i++) {
            $weeks[$carbaoDay->startOfWeek()->addDay($i)->format('Y-m-d')] = 0;
        }
        foreach ($user_week_lead as $name => $leads) {
            $weeks[$name] = $leads->count();
        }

        $devicearray          = [];
        $devicearray['label'] = [];
        $devicearray['data']  = [];

        foreach ($weeks as $name => $leads) {
            $devicearray['label'][] = Carbon::parse($name)->format('l');
            $devicearray['data'][] = $leads;
        }
        $leads = Lead::where('created_by', '=', \Auth::user()->creatorId())->get();

        $lead_source = Source::where('created_by', \Auth::user()->id)->get();

        $leadsourceName = [];
        $leadsourceeData = [];
        foreach ($lead_source as $lead_source_data) {
            $lead_source = lead::where('created_by', \Auth::user()->id)->where('sources', $lead_source_data->id)->count();
            $leadsourceName[] = $lead_source_data->name;
            $leadsourceeData[] = $lead_source;
        }


        // monthly report

        $labels = [];
        $data   = [];

        if (!empty($request->start_month) && !empty($request->end_month)) {
            $start = strtotime($request->start_month);
            $end   = strtotime($request->end_month);
        } else {
            $start = strtotime(date('Y-01'));
            $end   = strtotime(date('Y-12'));
        }

        $leads = Lead::orderBy('id');
        $leads->where('date', '>=', date('Y-m-01', $start))->where('date', '<=', date('Y-m-t', $end));
        $leads->where('created_by', \Auth::user()->creatorId());
        $leads = $leads->get();

        $currentdate = $start;
        while ($currentdate <= $end) {
            $month = date('m', $currentdate);
            $year  = date('Y');

            if (!empty($request->start_month)) {
                $leadFilter = Lead::where('created_by', \Auth::user()->creatorId())->whereMonth('date', $request->start_month)->whereYear('date', $year)->get();

            } else {
                $leadFilter = Lead::where('created_by', \Auth::user()->creatorId())->whereMonth('date', $month)->whereYear('date', $year)->get();
                // dd($request->leadFilter);
            }

            $data[]      = count($leadFilter);
            $labels[]    = date('M Y', $currentdate);
            $currentdate = strtotime('+1 month', $currentdate);


            if (!empty($request->start_month)) {
                $cdate = '01-' . $request->start_month . '-' . $year;
                $mstart = strtotime($cdate);
                $labelss[]    = date('M Y', $mstart);

                return response()->json(['data' => $data, 'name' => $labelss]);
            }
        }

        if(empty($request->start_month) && !empty($request->all())){
            return response()->json(['data' => $data, 'name' => $labels]);
        }
        $filter['startDateRange'] = date('M-Y', $start);
        $filter['endDateRange']   = date('M-Y', $end);

        $monthList = $month = $this->yearMonth();

        //staff report
        $leads = Lead::where('created_by', '=', \Auth::user()->creatorId())->get();

        if ($request->type == "staff_repport") {
            $form_date = date('Y-m-d H:i:s', strtotime($request->From_Date));
            $to_date = date('Y-m-d H:i:s', strtotime($request->To_Date));

            if (!empty($request->From_Date) && !empty($request->To_Date)) {

                $lead_user = User::where('created_by', \Auth::user()->id)->get();
                $leaduserName = [];
                $leadusereData = [];
                foreach ($lead_user as $lead_user_data) {
                    $lead_user = Lead::where('created_by', \Auth::user()->id)->where('user_id', $lead_user_data->id)->whereBetween('created_at', [$form_date, $to_date])->count();
                    $leaduserName[] = $lead_user_data->name;
                    $leadusereData[] = $lead_user;
                }
                return response()->json(['data' => $leadusereData, 'name' => $leaduserName]);
            }
        } else {
            $lead_user = User::where('created_by', \Auth::user()->id)->get();
            $leaduserName = [];
            $leadusereData = [];
            foreach ($lead_user as $lead_user_data) {
                $lead_user = Lead::where('created_by', \Auth::user()->id)->where('user_id', $lead_user_data->id)->count();
                $leaduserName[] = $lead_user_data->name;
                $leadusereData[] = $lead_user;
            }
        }

        $leads = Lead::where('created_by', '=', \Auth::user()->creatorId())->get();

        $lead_pipeline = Pipeline::where('created_by', \Auth::user()->id)->get();

        $leadpipelineName = [];
        $leadpipelineeData = [];
        foreach ($lead_pipeline as $lead_pipeline_data) {
            $lead_pipeline = lead::where('created_by', \Auth::user()->id)->where('pipeline_id', $lead_pipeline_data->id)->count();
            $leadpipelineName[] = $lead_pipeline_data->name;
            $leadpipelineeData[] = $lead_pipeline;
        }


        return view('report.lead', compact('devicearray', 'leadsourceName', 'leadsourceeData', 'labels', 'data', 'filter', 'monthList','leads', 'leaduserName', 'leadusereData', 'user', 'leadpipelineName', 'leadpipelineeData'));
    }

    public function dealreport(Request $request)
    {
        $user      = \Auth::user();
        $deals = Deal::orderBy('id');
        $deals->where('created_by', \Auth::user()->creatorId());

        $user_week_deal = Deal::orderBy('created_at')->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->get()->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        });

        $carbaoDay = Carbon::now()->startOfWeek();
        $weeks = [];
        for ($i = 0; $i < 7; $i++) {
            $weeks[$carbaoDay->startOfWeek()->addDay($i)->format('Y-m-d')] = 0;
        }
        foreach ($user_week_deal as $name => $deals) {
            $weeks[$name] = $deals->count();
        }

        $devicearray          = [];
        $devicearray['label'] = [];
        $devicearray['data']  = [];
        foreach ($weeks as $name => $deals) {
            $devicearray['label'][] = Carbon::parse($name)->format('l');
            $devicearray['data'][] = $deals;
        }
        $deals = Deal::where('created_by', '=', \Auth::user()->creatorId())->get();

        $deals_source = Source::where('created_by', \Auth::user()->id)->get();

        $dealsourceName = [];
        $dealsourceeData = [];
        foreach ($deals_source as $deals_source_data) {
            $deals_source = Deal::where('created_by', \Auth::user()->id)->where('sources', $deals_source_data->id)->count();
            $dealsourceName[] = $deals_source_data->name;
            $dealsourceeData[] = $deals_source;
        }
        if ($request->type == "deal_staff_repport") {
            $from_date = date('Y-m-d H:i:s', strtotime($request->From_Date));
            $to_date = date('Y-m-d H:i:s', strtotime($request->To_Date));

            if (!empty($request->From_Date) && !empty($request->To_Date)) {
                $user_deal = User::where('created_by', \Auth::user()->creatorId())->get();
                $dealUserData = [];
                $dealUserName = [];
                foreach ($user_deal as $user_deal_data) {

                    $user_deals = UserDeal::where('user_id', $user_deal_data->id)->whereBetween('created_at', [$from_date, $to_date])->count();
                    $dealUserName[] = $user_deal_data->name;
                    $dealUserData[] = $user_deals;
                }
                return response()->json(['data' => $dealUserData, 'name' => $dealUserName]);
            }
        } else {
            $user_deal = User::where('created_by', \Auth::user()->creatorId())->get();
            $dealUserData = [];
            $dealUserName = [];
            foreach ($user_deal as $user_deal_data) {
                $user_deals = UserDeal::where('user_id', $user_deal_data->id)->count();

                $dealUserName[] = $user_deal_data->name;
                $dealUserData[] = $user_deals;
            }
        }

        $deals = Deal::where('created_by', '=', \Auth::user()->creatorId())->get();

        $deal_pipeline = Pipeline::where('created_by', \Auth::user()->id)->get();

        $dealpipelineName = [];
        $dealpipelineeData = [];
        foreach ($deal_pipeline as $deal_pipeline_data) {
            $deal_pipeline = Deal::where('created_by', \Auth::user()->id)->where('pipeline_id', $deal_pipeline_data->id)->count();
            $dealpipelineName[] = $deal_pipeline_data->name;
            $dealpipelineeData[] = $deal_pipeline;
        }

        if ($request->type == "client_repport") {

            $from_date1 = date('Y-m-d H:i:s', strtotime($request->from_date));
            $to_date1 = date('Y-m-d H:i:s', strtotime($request->to_date));
            if (!empty($request->from_date) && !empty($request->to_date)) {
                $client_deal = User::where('created_by', \Auth::user()->creatorId())->get();
                $dealClientData = [];
                $dealClientName = [];
                foreach ($client_deal as $client_deal_data) {

                    $deals_client = ClientDeal::where('client_id', $client_deal_data->id)->whereBetween('created_at', [$from_date1, $to_date1])->count();
                    $dealClientName[] = $client_deal_data->name;
                    $dealClientData[] = $deals_client;
                }
                return response()->json(['data' => $dealClientData, 'name' =>  $dealClientName]);
            }
        } else {
            $client_deal = User::where('created_by', \Auth::user()->creatorId())->get();
            $dealClientName = [];
            $dealClientData = [];
            foreach ($client_deal as $client_deal_data) {
                $deals_client = ClientDeal::where('client_id', $client_deal_data->id)->count();
                $dealClientName[] = $client_deal_data->name;
                $dealClientData[] = $deals_client;
            }
        }
        $labels = [];
        $data   = [];

        if (!empty($request->start_month) && !empty($request->end_month)) {
            $start = strtotime($request->start_month);
            $end   = strtotime($request->end_month);
        } else {
            $start = strtotime(date('Y-01'));
            $end   = strtotime(date('Y-12'));
        }

        $deals = Deal::orderBy('id');
        $deals->where('created_at', '>=', date('Y-m-01', $start))->where('created_at', '<=', date('Y-m-t', $end));
        $deals->where('created_by', \Auth::user()->creatorId());
        $deals = $deals->get();

        $currentdate = $start;
        while ($currentdate <= $end) {
            $month = date('m', $currentdate);

            $year  = date('Y');

            if (!empty($request->start_month)) {
                $dealFilter = Deal::where('created_by', \Auth::user()->creatorId())->whereMonth('created_at', $request->start_month)->whereYear('created_at', $year)->get();
            } else {
                $dealFilter = Deal::where('created_by', \Auth::user()->creatorId())->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
            }

            $data[]      = count($dealFilter);
            $labels[]    = date('M Y', $currentdate);
            $currentdate = strtotime('+1 month', $currentdate);

            if (!empty($request->start_month)) {
                $cdate = '01-' . $request->start_month . '-' . $year;
                $mstart = strtotime($cdate);
                $labelss[]    = date('M Y', $mstart);

                return response()->json(['data' => $data, 'name' => $labelss]);
            }
        }
        if(empty($request->start_month) && !empty($request->all())){
            return response()->json(['data' => $data, 'name' => $labels]);
        }
        $filter['startDateRange'] = date('M-Y', $start);
        $filter['endDateRange']   = date('M-Y', $end);

        $monthList = $month = $this->yearMonth();
        return view('report.deal', compact('devicearray', 'dealsourceName', 'dealsourceeData', 'dealUserData', 'dealUserName', 'dealpipelineName', 'dealpipelineeData', 'data', 'labels', 'dealClientName', 'dealClientData','monthList'));
    }


    public function overtime(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('manage report'))
        {
            $branch = Branch::get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $employees = Employee::select('id', 'name')
            ->whereHas('user', function($query) {
                $query->where('is_active', 1);
            });
            
            if(!empty($request->employee_id) && $request->employee_id[0]!=0){
                $employees->whereIn('id', $request->employee_id);
            }
            $employees=$employees;

            if(!empty($request->branch))
            {
                $employees->where('branch_id', $request->branch);
                $data['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }

            // if(!empty($request->department))
            // {
            //     $employees->where('department_id', $request->department);
            //     $data['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            // }

            $employees = $employees->get()->pluck('name', 'id');
            // dd($employees);

            if(!empty($request->month))
            {
                $currentdate = strtotime($request->month);
                $month       = date('m', $currentdate);
                $year        = date('Y', $currentdate);
                $curMonth    = date('M-Y', strtotime($request->month));

            }
            else
            {
                $month    = date('m');
                $year     = date('Y');
                $curMonth = date('M-Y', strtotime($year . '-' . $month));
            }


            $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
            for($i = 1; $i <= $num_of_days; $i++)
            {
                $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
            }

            $employeesAttendance = [];
            $totalPresent        = $totalLeave = $totalEarlyLeave = 0;
            $ovetimeHours        = $overtimeMins = $earlyleaveHours = $earlyleaveMins = $lateHours = $lateMins = 0;
            
            foreach($employees as $id => $employee)
            {
                $attendances['id'] = $id;
                $attendances['name'] = $employee;
                $totalOvertimeDays = 0;
                $totalOverTime = 0;
                $overtimeHours = 0;
                $overtimeMins = 0;
                $totalOvertimeHours = 0;
                $totalOvertimeMins = 0;


                foreach($dates as $date)
                {
                    $dateFormat = $year . '-' . $month . '-' . $date;

                    if($dateFormat <= date('Y-m-d'))
                    {
                        $employeeAttendance = AttendanceEmployee::where('employee_id', $id)->where('date', $dateFormat)->first();
                        $overtimes = UserOvertime::where('user_id', $id)->where('status', 'Approved')->whereYear('updated_at', $year)->whereMonth('updated_at', $month)->get();

                        if(!empty($employeeAttendance) && $employeeAttendance->status == 'Present')
                        {
                            $attendanceStatus[$date] = 'P';
                            $totalPresent            += 1;

                            // if($employeeAttendance->overtime > 0)
                            // {
                            //     $ovetimeHours += date('h', strtotime($employeeAttendance->overtime));
                            //     $overtimeMins += date('i', strtotime($employeeAttendance->overtime));
                            // }

                            foreach($overtimes as $overtime)
                            {
                                $totalOvertimeHours += (int)date('H', strtotime($overtime->total_time));
                                $totalOvertimeMins  += (int)date('i', strtotime($overtime->total_time));
                            }

                            if($employeeAttendance->early_leaving > 0)
                            {
                                $earlyleaveHours += date('h', strtotime($employeeAttendance->early_leaving));
                                $earlyleaveMins  += date('i', strtotime($employeeAttendance->early_leaving));
                            }

                            if($employeeAttendance->late > 0)
                            {
                                $lateHours += date('h', strtotime($employeeAttendance->late));
                                $lateMins  += date('i', strtotime($employeeAttendance->late));
                            }

                        }
                        elseif(!empty($employeeAttendance) && $employeeAttendance->status == 'Leave')
                        {
                            $attendanceStatus[$date] = 'A';
                            $totalLeave              += 1;
                        }
                        else
                        {
                            $attendanceStatus[$date] = '';
                        }

                        // Hitung jumlah hari lembur berdasarkan start_date

                        foreach ($overtimes as $overtime) {
                            // Format updated_at menjadi Y-m-d
                            $updatedAtDate = $overtime->updated_at->format('Y-m-d');

                            if ($updatedAtDate == $dateFormat) {
                                $totalOvertimeDays += 1;

                                // Hitung total_time
                                $totalOverTime += strtotime($overtime->total_time) - strtotime('00:00:00');
                                $overtimeHours += (int) date('H', strtotime($overtime->total_time));
                                $overtimeMins  += (int) date('i', strtotime($overtime->total_time));
                            }
                        }

                        
                        
                        
                    }
                    else
                    {
                        $attendanceStatus[$date] = '';
                    }
   
                }

                $totalOvertimeDuration = sprintf("%02d:%02d:%02d", $overtimeHours, $overtimeMins, 0);
                $attendances['status'] = $attendanceStatus;
                $attendances['overtime'] = $totalOvertimeDays;
                $attendances['total_overtime'] = $totalOvertimeDuration;
                $attendanceCounts = array_count_values($attendanceStatus);
                $totalPresents = isset($attendanceCounts['P']) ? $attendanceCounts['P'] : 0;
                $attendances['present'] = $totalPresents;
                $employeesAttendance[] = $attendances;

                
            }

            

            $totalOverTime   = $ovetimeHours + ($overtimeMins / 60);
            $totalEarlyleave = $earlyleaveHours + ($earlyleaveMins / 60);
            $totalLate       = $lateHours + ($lateMins / 60);

            $data['totalOvertime']   = $totalOverTime;
            $data['totalEarlyLeave'] = $totalEarlyleave;
            $data['totalLate']       = $totalLate;
            $data['totalPresent']    = $totalPresent;
            $data['totalLeave']      = $totalLeave;
            $data['curMonth']        = $curMonth;

            return view('report.overtime', compact('employeesAttendance', 'branch', 'dates', 'data'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function projects(Request $request)
    {
        if(\Auth::user()->can('manage report'))
        {

            $employee = User::all();
            $employee = $employee->pluck('id');
            $employeeProjects = ProjectUser::whereIn('user_id', $employee);
            $client =   User::where('type','=','client')->pluck('name','id');
            $filter_clients = $request->client_id;
            $employess =   User::where('type','!=','client')->pluck('name','id');

            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start_month = date('m', strtotime($request->start_month));
                $end_month = date('m', strtotime($request->end_month));
                $start_year = date('Y', strtotime($request->start_month));
                $end_year = date('Y', strtotime($request->end_month));
        
                $start_date = date($start_year . '-' . $start_month . '-01');
                $end_date = date($end_year . '-' . $end_month . '-t');
        
                $employeeProjects->whereHas('project', function ($query) use ($start_date, $end_date) {
                    $query->whereBetween('start_date', [$start_date, $end_date]);
                });
            } elseif (!empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year = date('Y', strtotime($request->month));
        
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date($year . '-' . $month . '-t');
        
                $employeeProjects->whereHas('project', function ($query) use ($start_date, $end_date) {
                    $query->whereBetween('start_date', [$start_date, $end_date]);
                });
            }

            if (!empty($request->user_ids)) {
                $selectedEmployees = $request->user_ids;
                $employeeProjects->whereIn('user_id', $selectedEmployees);
            }

            if (!empty($request->client_id)) {
                $employeeProjects
                ->whereHas('project', function ($query) use ($filter_clients) {
                    $query->whereIn('client_id', $filter_clients);
                });
            }

            if (!empty($request->label)) {
                $filteredLabels = $request->label;
        
                $employeeProjects->whereHas('project', function ($query) use ($filteredLabels) {
                    $query->whereIn('label', $filteredLabels);
                });
            }

            // $employeeProject = $employeeProjects->get();

            $employeeProject = $employeeProjects->orderByDesc('id')->paginate(10)->appends([
                    'user_ids' => $request->user_ids,
                    'start_month' => $request->start_month,
                    'end_month' => $request->end_month,
                    'month' => $request->month,
                    'client_id' => $request->client_id,
                    'label' => $request->label,
                ]);   

            if (!empty($request->export_excel)) {
                // Persiapkan data untuk diekspor
                $exportData = $this->prepareExportData($employeeProject);
        
                // Convert the collection to an array
                $exportDataArray = $exportData->toArray();

                // Ekspor ke Excel
                return Excel::download(new ProjectsExport($exportDataArray), 'projects_report.xlsx');
            }
            

            return view('report.projects', compact('employeeProject','client','employess'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function employeeProjects(Request $request, $employee_id, $type, $month, $year)
    {
        if(\Auth::user()->can('manage report'))
        {
            $projects                = [];
            $employee                = Employee::where('employee_id', $employee_id)->first();
            $userDetail              = \Auth::user();
            $user_projects           = $employee->projects()->pluck('project_id','project_id')->toArray();
            $project                 = ProjectUser::with('project');

            if($type == 'yearly')
            {
                $project
                    ->where('user_id', $employee->user_id)
                    ->whereHas('project', function ($query) use ($month, $year) {
                        $query->whereYear('start_date', $year);
                    });
            }
            // elseif ($type == 'weekly')
            // {
            //     $startOfWeek = date('Y-m-d', strtotime($week));
            //     $endOfWeek = date('Y-m-d', strtotime('+6 days', strtotime($startOfWeek)));

            //     $project
            //         ->where('user_id', $employee->user_id)
            //         ->whereHas('project', function ($query) use ($startOfWeek, $endOfWeek) {
            //             $query->whereBetween('start_date', [$startOfWeek, $endOfWeek]);
            //         });
            // }
            else
            {
                $m = date('m', strtotime($month));
                $y = date('Y', strtotime($month));

                $project
                    ->where('user_id', $employee->user_id)
                    ->whereHas('project', function ($query) use ($m, $y) {
                        $query->whereMonth('start_date', $m)->whereYear('start_date', $y);
                    });
            }


            $project = $project->get();


            return view('report.projectsShow', compact('projects', 'project'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }

    public function employeeOvertime(Request $request, $employee_id, $month)
    {
        if(\Auth::user()->can('manage report'))
        {
            $employee_overtime = UserOvertime::where('user_id', $employee_id)
                ->where('status', '=', 'Approved');

            $m = date('m', strtotime($month));
            $y = date('Y', strtotime($month));

            $employee_overtime->whereMonth('updated_at', $m)
                ->whereYear('updated_at', $y);

            $employee_overtime = $employee_overtime->get();

            return view('report.overtimeShow', compact('employee_overtime'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }

    public function reimbursment(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('manage report'))
        {
            $branch = Branch::get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $client = User::where('type','=','client')->get()->pluck('name', 'id');
            $client->prepend('Select Client', '');

            $employees = Employee::select('id', 'name');
            if(!empty($request->employee_id) && $request->employee_id[0]!=0){
                $employees->whereIn('id', $request->employee_id);
            }
            $employees=$employees;

            if(!empty($request->branch))
            {
                $employees->where('branch_id', $request->branch);
                $data['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }

            // if(!empty($request->department))
            // {
            //     $employees->where('department_id', $request->department);
            //     $data['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            // }

            $employees = $employees->get()->pluck('name', 'id');
            // dd($employees);

            // Filter berdasarkan reimbursment_type
            if (!empty($request->reimbursment_type)) {
                $reimbursmentType = $request->reimbursment_type;
                $employees = $employees->filter(function ($value, $key) use ($reimbursmentType) {
                    $reimbursments = Reimbursment::where('employee_id', $key)
                        ->where('reimbursment_type', $reimbursmentType)
                        ->exists();

                    return $reimbursments;
                });
            }

            if (!empty($request->client_id)) {
                $reimbursmentclient = $request->client_id;
                $employees = $employees->filter(function ($value, $key) use ($reimbursmentclient) {
                    $reimbursments = Reimbursment::where('employee_id', $key)
                        ->where('client_id', $reimbursmentclient)
                        ->exists();

                    return $reimbursments;
                });
            }

            if(!empty($request->month))
            {
                $currentdate = strtotime($request->month);
                $month       = date('m', $currentdate);
                $year        = date('Y', $currentdate);
                $curMonth    = date('M-Y', strtotime($request->month));

            }
            else
            {
                $month    = date('m');
                $year     = date('Y');
                $curMonth = date('M-Y', strtotime($year . '-' . $month));
            }


            $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
            for($i = 1; $i <= $num_of_days; $i++)
            {
                $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
            }

            $employeesReimbursment = [];
            $totalPresent = $totalLeave = $totalEarlyLeave = 0;
            $ovetimeHours = $overtimeMins = $earlyleaveHours = $earlyleaveMins = $lateHours = $lateMins = 0;

            foreach ($employees as $id => $employee) {
                $reimbursments = [];
                $reimbursments['id'] = $id;
                $reimbursments['name'] = $employee;
                $totalReimbursment = 0;
                $totalPendingAmount = 0;
                $totalReimbursmentCount = 0;

                foreach ($dates as $date) {
                    $dateFormat = $year . '-' . $month . '-' . $date;
                    $reimbursmentType = $request->reimbursment_type;

                    if ($dateFormat <= date('Y-m-d')) {
                        $reimbursment = Reimbursment::where('employee_id', '=', $id)
                            ->where('status', '=', 'Paid')
                            ->where('date', $dateFormat)
                            ->where('reimbursment_type', $reimbursmentType)
                            ->get();

                        // Hitung jumlah hari lembur berdasarkan start_date

                        foreach ($reimbursment as $reimbursmentss) {
                            if ($reimbursmentss->date == $dateFormat) {
                                $totalReimbursment += $reimbursmentss->amount;
                            }
                        }

                        $pendingReimbursments = Reimbursment::where('employee_id', '=', $id)
                            ->where('status', '=', 'Pending')
                            ->where('date', $dateFormat)
                            ->where('reimbursment_type', $reimbursmentType)
                            ->get();

                        foreach ($pendingReimbursments as $pendingreimbursment) {
                            if ($pendingreimbursment->date == $dateFormat) {
                                $totalPendingAmount += $pendingreimbursment->amount;
                            }
                        }

                        $total_reimbursment = Reimbursment::where('employee_id', '=', $id)
                            ->where('date', $dateFormat)
                            ->where('reimbursment_type', $reimbursmentType)
                            ->get();

                        // Hitung jumlah reimbursment berdasarkan start_date

                        foreach ($total_reimbursment as $total_reimbursments) {
                            if ($total_reimbursments->date == $dateFormat) {
                                $totalReimbursmentCount++;
                            }
                        }

                    }
                }

                $totalAmountReimbursment = $totalReimbursment;
                $totalAmountPendingReimbursment = $totalPendingAmount;
                $reimbursments['total_reimbursment'] = $totalAmountReimbursment + $totalAmountPendingReimbursment;
                $reimbursments['paid_amount'] = $totalAmountReimbursment;
                $reimbursments['unpaid_amount'] = $totalAmountPendingReimbursment;
                $reimbursments['total_reimbursment_count'] = $totalReimbursmentCount;
                $employeesReimbursment[] = $reimbursments;
            }


            return view('report.reimbursment', compact('employeesReimbursment', 'branch', 'dates', 'client'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function employeeReimbursment(Request $request, $employee_id, $month, $reimbursment_type)
    {
        if(\Auth::user()->can('manage report'))
        {
            $employee_reimbursment  = Reimbursment::where('employee_id', $employee_id)->where('reimbursment_type', $reimbursment_type);
            $m = date('m', strtotime($month));
            $y = date('Y', strtotime($month));

            $employee_reimbursment->whereMonth('date', $m)->whereYear('date', $y);
            $employee_reimbursment = $employee_reimbursment->get();


            return view('report.reimbursmentShow', compact('employee_reimbursment'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }

    public function performance(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('manage report'))
        {
            $branch = Branch::get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $employees = Employee::select('id', 'name');
            if(!empty($request->employee_id) && $request->employee_id[0]!=0){
                $employees->whereIn('id', $request->employee_id);
            }
            $employees=$employees;

            if(!empty($request->branch))
            {
                $employees->where('branch_id', $request->branch);
                $data['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }

            // if(!empty($request->department))
            // {
            //     $employees->where('department_id', $request->department);
            //     $data['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            // }

            $employees = $employees->get()->pluck('name', 'id');
            // dd($employees);

            if(!empty($request->month))
            {
                $currentdate = strtotime($request->month);
                $month       = date('m', $currentdate);
                $year        = date('Y', $currentdate);
                $curMonth    = date('M-Y', strtotime($request->month));

            }
            else
            {
                $month    = date('m');
                $year     = date('Y');
                $curMonth = date('M-Y', strtotime($year . '-' . $month));
            }


            $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
            for($i = 1; $i <= $num_of_days; $i++)
            {
                $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
            }

            $employeesRating = [];

            foreach ($employees as $id => $employee) {
                $employeeRating = [
                    'id' => $id,
                    'name' => $employee,
                    'total_rating' => 0,
                    'num_of_projects' => 0,
                ];

                $appraisals = AppraisalEmployee::where('employee_id', $id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->get();

                $totalRating = 0;
                $totalRaters = 0;
                $overallRating = 0;
                $numOfProjects = 0;

                foreach ($appraisals as $appraisal) {
                    // Assuming the rating field in the AppraisalEmployee model contains the JSON array of ratings.
                    $rating = json_decode($appraisal->rating, true);
                    $starSum = !empty($rating) ? array_sum($rating) : 0;
                    $totalRating += $starSum;
                    $totalRaters += count($rating);
                    $numOfProjects++;
                }

                if ($totalRaters > 0) {
                    $overallRating = $totalRating / $totalRaters;
                }

                $employeeRating['total_rating'] = $overallRating;
                $employeeRating['num_of_projects'] = $numOfProjects;

                $employeesRating[] = $employeeRating;
            }

            return view('report.performance', compact('employeesRating', 'branch', 'dates'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function prepareExportData($employeeProject)
    {
        $exportData = new Collection();

        foreach ($employeeProject as $project_user) {
            $data = [
                'Employee' => !empty($project_user->user->name) ? $project_user->user->name : '-',
                'Start Date' => !empty($project_user->project->start_date) ? $project_user->project->start_date : '-',
                'Project Name' => !empty($project_user->project->project_name) ? $project_user->project->project_name : '-',
                'Label Project' => !empty($project_user->project->label) ? $project_user->project->label : '-',
                'Client Name' => !empty($project_user->project->user->name) ? $project_user->project->user->name : '-',
                'Logged Hours' => $this->calculateLoggedHours($project_user->project_id, $project_user->user_id),
                'Status' => !empty($project_user->project->status) ? $project_user->project->status : '-',
            ];

            $exportData->push($data);
        }

        return $exportData;
    }

    public function calculateLoggedHours($projectId, $userId)
    {
        $logged_hours = 0;
        $timesheets = Timesheet::where('project_id', $projectId)->where('created_by', $userId)->get();

        foreach ($timesheets as $timesheet) {
            $hours = date('H', strtotime($timesheet->time));
            $minutes = date('i', strtotime($timesheet->time));
            $total_hours = $hours + ($minutes / 60);
            $logged_hours += $total_hours;
        }

        return number_format($logged_hours, 2, '.', '');
    }

    public function sick(Request $request)
    {
        $user = Auth::user();
        if(\Auth::user()->can('manage report'))
        {
            $branch = Branch::get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $employees = Employee::select('id', 'name')
            ->whereHas('user', function($query) {
                $query->where('is_active', 1);
            });
            if(!empty($request->employee_id) && $request->employee_id[0]!=0){
                $employees->whereIn('id', $request->employee_id);
            }
            $employees=$employees;

            if(!empty($request->branch))
            {
                $employees->where('branch_id', $request->branch);
                $data['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }

            $employees = $employees->get()->pluck('name', 'id');

            if(!empty($request->month))
            {
                $currentdate = strtotime($request->month);
                $month       = date('m', $currentdate);
                $year        = date('Y', $currentdate);
                $curMonth    = date('M-Y', strtotime($request->month));

            }
            else
            {
                $month    = date('m');
                $year     = date('Y');
                $curMonth = date('M-Y', strtotime($year . '-' . $month));
            }


            $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
            for($i = 1; $i <= $num_of_days; $i++)
            {
                $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
            }

            $employeesSick = [];            
            foreach($employees as $id => $employee)
            {
                $sick['id'] = $id;
                $sick['name'] = $employee;
                $totalSickDays = 0;
                $totalSickLetter = 0;

                foreach ($dates as $date) {
                    $dateFormat = $year . '-' . $month . '-' . $date;

                    if ($dateFormat <= date('Y-m-d')) {

                        $total_sick_days = Leave::where('employee_id', '=', $id)
                            ->where('applied_on', $dateFormat)
                            ->where('absence_type', '=', 'sick')
                            ->sum('total_sick_days');

                        $totalSickDays += $total_sick_days;

                        $total_sick_letter = Leave::where('employee_id', '=', $id)
                            ->where('applied_on', $dateFormat)
                            ->where('absence_type', '=', 'sick')
                            ->whereNotNull('sick_letter')
                            ->count();

                        $totalSickLetter += $total_sick_letter;

                    }
                }

                $sick['sick'] = $totalSickDays;
                $sick['total_sick_letter'] = $totalSickLetter;
                $employeesSick[] = $sick;

    
            }

            return view('report.sick', compact('employeesSick', 'branch', 'dates'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function employeeSick(Request $request, $employee_id, $month)
    {
        if(\Auth::user()->can('manage report'))
        {
            $employee_sick = Leave::where('employee_id', $employee_id)->where('absence_type', '=', 'sick');
            $m = date('m', strtotime($month));
            $y = date('Y', strtotime($month));

            $employee_sick->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
            $employee_sick = $employee_sick->get();


            return view('report.sickShow', compact('employee_sick'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }

    public function getSickLetter(Request $request)
    {
        $absence_sick   = Leave::where('employee_id', $request->id)->get();
        $images         = Leave::where('employee_id', $request->id)->get();
        return view('report.sickShow',compact('images','absence_sick'));
    }

    public function attendance_user(Request $request)
    {
        if(Auth::check())
        {
            if(\Auth::user()->can('show hrm dashboard'))
            {
                $user = Auth::user();
                if($user->type != 'client' && $user->type != 'staff_client' && $user->type != 'company' && $user->type != 'admin' && $user->type != 'partners')
                {
                    $emp = Employee::where('user_id', '=', $user->id)->first();
                    $employees = Employee::where('user_id', '=', $user->id)->get()->pluck('name', 'id');

                    if(!empty($request->month))
                    {
                        $currentdate = strtotime($request->month);
                        $month       = date('m', $currentdate);
                        $year        = date('Y', $currentdate);
                        $curMonth    = date('M-Y', strtotime($request->month));

                    }
                    else
                    {
                        $month    = date('m');
                        $year     = date('Y');
                        $curMonth = date('M-Y', strtotime($year . '-' . $month));
                    }

                    $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
                    for($i = 1; $i <= $num_of_days; $i++)
                    {
                        $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
                    }

                    $employeesAttendances = [];
                    $totalPresent        = $totalLeave = $totalEarlyLeave = 0;

                    foreach ($employees as $id => $employee) {
                        $attendances['name'] = $employee;
                    
                        foreach ($dates as $date) {
                            $dateFormat = $year . '-' . $month . '-' . $date;
                    
                            if ($dateFormat <= date('Y-m-d')) {
                                            if ($this->isWeekend($dateFormat)) {
                                                $employeeAttendance = AttendanceEmployee::where('employee_id', $id)
                                                                                        ->where('date', $dateFormat)
                                                                                        ->where('status', 'Present')
                                                                                        ->first();
                                                
                                                if (!empty($employeeAttendance)) {
                                                    $attendanceStatus[$date] = 'P'; // Jika ada kehadiran pada hari Sabtu atau Minggu, status kehadiran diatur sebagai 'P'
                                                    $attendanceLong[$date] = $employeeAttendance->longitude;
                                                    $attendanceLat[$date] = $employeeAttendance->latitude;
                                                    $totalPresent += 1;
                                                } else {
                                                    $attendanceStatus[$date] = 'W'; // Jika tidak ada kehadiran pada hari Sabtu atau Minggu, status kehadiran diatur sebagai 'W'
                                                }
                                            } 
                                            else {
                                                $employeeAttendance = AttendanceEmployee::where('employee_id', $id)
                                                                                        ->where('date', $dateFormat)
                                                                                        ->first();
                                        
                                                if (!empty($employeeAttendance) && $employeeAttendance->status == 'Present') {
                                                    $attendanceStatus[$date] = 'P';
                                                    $attendanceLong[$date] = $employeeAttendance->longitude;
                                                    $attendanceLat[$date] = $employeeAttendance->latitude;
                                                    $totalPresent += 1;
                                                } else {
                                                    $attendanceStatus[$date] = '';
                                                    $attendanceLong[$date] = '';
                                                    $attendanceLat[$date] = '';
                                                }
                                            }
                            } else {
                                $attendanceStatus[$date] = '';
                                $attendanceLong[$date] = '';
                                $attendanceLat[$date] = '';
                            }  
                        }     
                        $attendances['status'] = $attendanceStatus;
                        $attendances['longitude'] = $attendanceLong;
                        $attendances['latitude'] = $attendanceLat;
                        $employeesAttendances[] = $attendances;
                    }

                    if($emp->branch_id == 1)
                    {
                        $officeTime['startTime']    = Utility::getValByName('company_start_time');
                        $officeTime['endTime']      = Utility::getValByName('company_end_time');
                    }
                    elseif($emp->branch_id == 2)
                    {
                        $officeTime['startTime']    = "08:30";
                        $officeTime['endTime']      = "17:30";
                    }
                    elseif($emp->branch_id == 3)
                    {
                        $officeTime['startTime']    = "08:00";
                        $officeTime['endTime']      = "17:00";
                    }

                    $absentData = [];
                    for ($day = 1; $day <= 31; $day++) {
                        $months = $request->month ?? date('Y-m');
                        $date = sprintf('%s-%02d', $months, $day);
                        
                        $absentCount = AttendanceEmployee::where('date', '=', $date)
                            ->where('status', '=', 'Present')->whereHas('employee', function ($query) use ($emp) {
                                $query->where('employee_id', '=', $emp->id);
                            })
                            ->count();
                        
                        $lateCount = 0;
                        
                        $lateCount = AttendanceEmployee::where('date', '=', $date)
                            ->where('status', '=', 'Present')->whereHas('employee', function ($query) use ($emp, $officeTime) {
                                $query->where('employee_id', '=', $emp->id)
                                    ->whereTime('clock_in', '>', $officeTime['startTime']);
                            })
                            ->count();
        
                        $absentData[] = $absentCount;
                        $lateData[] = $lateCount;
                    }

                    $data_absen = $absentData;
                    $data_late = $lateData;

                    return view('report.attendance_staff', compact('employeesAttendances', 'dates','data_absen','data_late'));
                }
                elseif($user->type == 'partners')
                {
                    $emp = Employee::where('user_id', '=', $user->id)->first();

                    $employees = Employee::where('branch_id', Employee::where('user_id', $user->id)->value('branch_id'))
                     ->pluck('name', 'id');


                    if(!empty($request->month))
                    {
                        $currentdate = strtotime($request->month);
                        $month       = date('m', $currentdate);
                        $year        = date('Y', $currentdate);
                        $curMonth    = date('M-Y', strtotime($request->month));

                    }
                    else
                    {
                        $month    = date('m');
                        $year     = date('Y');
                        $curMonth = date('M-Y', strtotime($year . '-' . $month));
                    }

                    $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
                    for($i = 1; $i <= $num_of_days; $i++)
                    {
                        $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
                    }

                    $employeesAttendances = [];
                    $totalPresent        = $totalLeave = $totalEarlyLeave = 0;

                    foreach ($employees as $id => $employee) {
                        $attendances['name'] = $employee;
                    
                        foreach ($dates as $date) {
                            $dateFormat = $year . '-' . $month . '-' . $date;
                    
                            if ($dateFormat <= date('Y-m-d')) {
                                            if ($this->isWeekend($dateFormat)) {
                                                $employeeAttendance = AttendanceEmployee::where('employee_id', $id)
                                                                                        ->where('date', $dateFormat)
                                                                                        ->where('status', 'Present')
                                                                                        ->first();
                                                
                                                if (!empty($employeeAttendance)) {
                                                    $attendanceStatus[$date] = 'P'; // Jika ada kehadiran pada hari Sabtu atau Minggu, status kehadiran diatur sebagai 'P'
                                                    $attendanceLong[$date] = $employeeAttendance->longitude;
                                                    $attendanceLat[$date] = $employeeAttendance->latitude;
                                                    $totalPresent += 1;
                                                } else {
                                                    $attendanceStatus[$date] = 'W'; // Jika tidak ada kehadiran pada hari Sabtu atau Minggu, status kehadiran diatur sebagai 'W'
                                                }
                                            } 
                                            else {
                                                $employeeAttendance = AttendanceEmployee::where('employee_id', $id)
                                                                                        ->where('date', $dateFormat)
                                                                                        ->first();
                                        
                                                if (!empty($employeeAttendance) && $employeeAttendance->status == 'Present') {
                                                    $attendanceStatus[$date] = 'P';
                                                    $attendanceLong[$date] = $employeeAttendance->longitude;
                                                    $attendanceLat[$date] = $employeeAttendance->latitude;
                                                    $totalPresent += 1;
                                                } else {
                                                    $attendanceStatus[$date] = '';
                                                    $attendanceLong[$date] = '';
                                                    $attendanceLat[$date] = '';
                                                }
                                            }
                            } else {
                                $attendanceStatus[$date] = '';
                                $attendanceLong[$date] = '';
                                $attendanceLat[$date] = '';
                            }  
                        }     
                        $attendances['status'] = $attendanceStatus;
                        $attendances['longitude'] = $attendanceLong;
                        $attendances['latitude'] = $attendanceLat;
                        $employeesAttendances[] = $attendances;
                    }

                    if($emp->branch_id == 1)
                    {
                        $officeTime['startTime']    = Utility::getValByName('company_start_time');
                        $officeTime['endTime']      = Utility::getValByName('company_end_time');
                    }
                    elseif($emp->branch_id == 2)
                    {
                        $officeTime['startTime']    = "08:30";
                        $officeTime['endTime']      = "17:30";
                    }
                    elseif($emp->branch_id == 3)
                    {
                        $officeTime['startTime']    = "08:00";
                        $officeTime['endTime']      = "17:00";
                    }

                    $absentData = [];
                    for ($day = 1; $day <= 31; $day++) {
                        $months = $request->month ?? date('Y-m');
                        $date = sprintf('%s-%02d', $months, $day);

                        $absentCount = AttendanceEmployee::where('date', '=', $date)
                                ->where('status', '=', 'Present')->whereHas('employee', function ($query) use ($emp) {
                                    $query->where('branch_id', '=', $emp->branch_id);
                                })
                                ->count();
                            
                            $lateCount = 0;
                            
                            $lateCount = AttendanceEmployee::where('date', '=', $date)
                                ->where('status', '=', 'Present')->whereHas('employee', function ($query) use ($emp, $officeTime) {
                                    $query->where('branch_id', '=', $emp->branch_id)
                                        ->whereTime('clock_in', '>', $officeTime['startTime']);
                                })
                                ->count();
            
                        $absentData[] = $absentCount;
                        $lateData[] = $lateCount;
                    }

                    $data_absen = $absentData;
                    $data_late = $lateData;

                    return view('report.attendance_staff', compact('employeesAttendances', 'dates','data_absen','data_late'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
    }

    function isWeekend($date)
    {
        $dayOfWeek = date('N', strtotime($date)); // Mengambil hari dalam format angka (1-7, dimulai dari Senin)
        return ($dayOfWeek == 6 || $dayOfWeek == 7); // Jika hari adalah Sabtu (6) atau Minggu (7), kembalikan true
    }

    public function overtime_user(Request $request)
    {
        if(Auth::check())
        {
            if(\Auth::user()->can('show hrm dashboard'))
            {
                $user = Auth::user();
                if($user->type != 'client' && $user->type != 'staff_client' && $user->type != 'company' && $user->type != 'admin' && $user->type != 'partners')
                {
                    $emp = Employee::where('user_id', '=', $user->id)->first();
                    
                    // Filter berdasarkan bulan yang dipilih
                    if(!empty($request->month))
                    {
                        $currentdate = strtotime($request->month);
                        $month = date('m', $currentdate);
                        $year  = date('Y', $currentdate);
                    }
                    else
                    {
                        $month = date('m');
                        $year  = date('Y');
                    }
                    
                    // Ambil data UserOvertime berdasarkan bulan dan tahun
                    $overtimeData = UserOvertime::where('user_id', $emp->id)
                        ->whereMonth('start_date', $month)
                        ->whereYear('start_date', $year)
                        ->get();

                    // Inisialisasi variabel untuk statistik
                    $totalOvertimeHours = 0;
                    $overtimePerDay = [];
                    $approvedOvertimeCount = 0;
                    $totalOvertimeCount = $overtimeData->count();

                    foreach ($overtimeData as $overtime) {
                        // Hitung total jam lembur
                        $startTime = strtotime($overtime->start_time);
                        $endTime = strtotime($overtime->end_time);

                        if($overtime->end_time == '00:00:00' && $overtime->total_time !== null) {
                            $endTime = strtotime('24:00:00');
                        }

                        $hoursWorked = ($endTime - $startTime) / 3600; // Konversi detik ke jam
                        $totalOvertimeHours += $hoursWorked;

                        // Hitung lembur per hari
                        $date = date('Y-m-d', strtotime($overtime->start_date));
                        if(!isset($overtimePerDay[$date])) {
                            $overtimePerDay[$date] = 0;
                        }
                        $overtimePerDay[$date] += $hoursWorked;

                        // Hitung jumlah lembur yang disetujui
                        if($overtime->status == 'Approved') {
                            $approvedOvertimeCount++;
                        }
                    }

                    // Hitung persentase lembur yang disetujui
                    $approvalRate = $totalOvertimeCount > 0 ? ($approvedOvertimeCount / $totalOvertimeCount) * 100 : 0;

                    // Return data ke view
                    return view('report.overtime_staff', compact('totalOvertimeHours', 'overtimePerDay', 'approvalRate', 'month', 'year'));
                }
                elseif($user->type == 'partners')
                {
                    $emp = Employee::where('user_id', '=', $user->id)->first();
                    
                    // Filter berdasarkan bulan yang dipilih
                    if(!empty($request->month))
                    {
                        $currentdate = strtotime($request->month);
                        $month = date('m', $currentdate);
                        $year  = date('Y', $currentdate);
                    }
                    else
                    {
                        $month = date('m');
                        $year  = date('Y');
                    }

                    if(\Auth::user()->employee->branch_id == 2)
                    {
                        $employee = Employee::where('branch_id', 2)->get();
                    }
                    elseif(\Auth::user()->employee->branch_id == 3)
                    {
                        $employee = Employee::where('branch_id', 3)->get();
                    }
                    else
                    {
                        $employee = Employee::all();
                    }
                    $employee = $employee->pluck('id');
                    
                    // Ambil data UserOvertime berdasarkan bulan dan tahun
                    $overtimeData = UserOvertime::whereIn('user_id', $employee)
                        ->whereMonth('start_date', $month)
                        ->whereYear('start_date', $year)
                        ->get();

                    // Inisialisasi variabel untuk statistik
                    $totalOvertimeHours = 0;
                    $overtimePerDay = [];
                    $approvedOvertimeCount = 0;
                    $totalOvertimeCount = $overtimeData->count();

                    foreach ($overtimeData as $overtime) {
                        // Hitung total jam lembur
                        $startTime = strtotime($overtime->start_time);
                        $endTime = strtotime($overtime->end_time);

                        if($overtime->end_time == '00:00:00' && $overtime->total_time !== null) {
                            $endTime = strtotime('24:00:00');
                        }

                        $hoursWorked = ($endTime - $startTime) / 3600; // Konversi detik ke jam
                        $totalOvertimeHours += $hoursWorked;

                        // Hitung lembur per hari
                        $date = date('Y-m-d', strtotime($overtime->start_date));
                        if(!isset($overtimePerDay[$date])) {
                            $overtimePerDay[$date] = 0;
                        }
                        $overtimePerDay[$date] += $hoursWorked;

                        // Hitung jumlah lembur yang disetujui
                        if($overtime->status == 'Approved') {
                            $approvedOvertimeCount++;
                        }
                    }

                    // Hitung persentase lembur yang disetujui
                    $approvalRate = $totalOvertimeCount > 0 ? ($approvedOvertimeCount / $totalOvertimeCount) * 100 : 0;

                    // Return data ke view
                    return view('report.overtime_staff', compact('totalOvertimeHours', 'overtimePerDay', 'approvalRate', 'month', 'year'));
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

    public function absence_user(Request $request)
    {
        if (Auth::check()) {
            if (\Auth::user()->can('show hrm dashboard')) 
            {
                $user = Auth::user();
                if($user->type != 'client' && $user->type != 'staff_client' && $user->type != 'company' && $user->type != 'admin' && $user->type != 'partners')
                {

                    // Ambil karyawan berdasarkan user_id
                    $employee = Employee::where('user_id', $user->id)->first();

                    // Ambil data cuti dan izin sakit berdasarkan bulan yang dipilih
                    if (!empty($request->month)) {
                        $currentDate = strtotime($request->month);
                        $month = date('m', $currentDate);
                        $year = date('Y', $currentDate);
                    } else {
                        $month = date('m');
                        $year = date('Y');
                    }

                    // Ambil jumlah hari dalam bulan yang dipilih
                    $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
                    $dates = [];
                    for ($i = 1; $i <= $num_of_days; $i++) {
                        $date = str_pad($i, 2, '0', STR_PAD_LEFT);
                        $dateFormat = $year . '-' . $month . '-' . $date;
                        $dayOfWeek = date('N', strtotime($dateFormat));

                        // Menyimpan hanya tanggal yang jatuh pada hari Senin hingga Jumat (1-5)
                        if ($dayOfWeek <= 5) {
                            $dates[] = $date;
                        }
                    }

                    // Ambil data izin sakit dan cuti lainnya
                    $totalLeaves = [];
                    $totalSickLeaves = [];

                    foreach ($dates as $date) {
                        $dateFormat = $year . '-' . $month . '-' . $date;

                        // Hitung total cuti pada tanggal tertentu
                        $leaveCount = Leave::where('employee_id', $employee->id)
                            ->whereDate('start_date', '<=', $dateFormat)
                            ->whereDate('end_date', '>=', $dateFormat)
                            ->where('absence_type', 'leave')
                            ->where('status', 'Approved')
                            ->get()
                            ->reduce(function ($carry, $leave) use ($dateFormat) {
                                $startDate = new \DateTime($leave->start_date);
                                $endDate = new \DateTime($leave->end_date);
                                $total_leave_days = 0;

                                while ($startDate <= $endDate) {
                                    if ($startDate->format('N') <= 5) { // Memeriksa apakah hari adalah Senin hingga Jumat
                                        $total_leave_days++;
                                    }
                                    $startDate->add(new \DateInterval('P1D')); // Menambahkan 1 hari ke tanggal start_date
                                }

                                return $carry + $total_leave_days;
                            }, 0);

                        $totalLeavePerMonth = Leave::where('employee_id', $employee->id)
                            ->whereDate('applied_on', '=', $dateFormat)
                            ->where('absence_type', 'leave')
                            ->where('status', 'Approved')
                            ->get()
                            ->reduce(function ($carry, $leave) use ($dateFormat) {
                                $startDate = new \DateTime($leave->start_date);
                                $endDate = new \DateTime($leave->end_date);
                                $total_leave_days = 0;

                                while ($startDate <= $endDate) {
                                    if ($startDate->format('N') <= 5) { // Memeriksa apakah hari adalah Senin hingga Jumat
                                        $total_leave_days++;
                                    }
                                    $startDate->add(new \DateInterval('P1D')); // Menambahkan 1 hari ke tanggal start_date
                                }

                                return $carry + $total_leave_days;
                            }, 0);

                        // Hitung total izin sakit pada tanggal tertentu
                        $sickLeaveCount = Leave::where('employee_id', $employee->id)
                            ->whereDate('applied_on', '=', $dateFormat)
                            ->where('absence_type', 'sick')
                            ->count();

                        $totalLeaves[] = $leaveCount;
                        $totalLeavePerMonths[] = $totalLeavePerMonth;
                        $totalSickLeaves[] = $sickLeaveCount;
                    }

                    $totalLeavePerMonth = array_sum($totalLeavePerMonths);
                    $totalSickPerMonth = array_sum($totalSickLeaves);
                }
                elseif($user->type == 'partners')
                {
                    // Ambil karyawan berdasarkan user_id
                    if (\Auth::user()->employee->branch_id == 2) {
                        $employees = Employee::where('branch_id', 2)->get();
                    } elseif (\Auth::user()->employee->branch_id == 3) {
                        $employees = Employee::where('branch_id', 3)->get();
                    } else {
                        $employees = Employee::all();
                    }

                    // Dapatkan array dari id karyawan
                    $employeeIds = $employees->pluck('id')->toArray();

                    // Ambil data cuti dan izin sakit berdasarkan bulan yang dipilih
                    if (!empty($request->month)) {
                        $currentDate = strtotime($request->month);
                        $month = date('m', $currentDate);
                        $year = date('Y', $currentDate);
                    } else {
                        $month = date('m');
                        $year = date('Y');
                    }

                    // Ambil jumlah hari dalam bulan yang dipilih
                    $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
                    $dates = [];
                    for ($i = 1; $i <= $num_of_days; $i++) {
                        $date = str_pad($i, 2, '0', STR_PAD_LEFT);
                        $dateFormat = $year . '-' . $month . '-' . $date;
                        $dayOfWeek = date('N', strtotime($dateFormat));

                        // Menyimpan hanya tanggal yang jatuh pada hari Senin hingga Jumat (1-5)
                        if ($dayOfWeek <= 5) {
                            $dates[] = $date;
                        }
                    }

                    // Ambil data izin sakit dan cuti lainnya
                    $totalLeaves = [];
                    $totalSickLeaves = [];
                    $totalLeavePerMonths = [];

                    foreach ($dates as $date) {
                        $dateFormat = $year . '-' . $month . '-' . $date;

                        // Hitung total cuti pada tanggal tertentu
                        $leaveCount = Leave::whereIn('employee_id', $employeeIds)
                            ->whereDate('start_date', '<=', $dateFormat)
                            ->whereDate('end_date', '>=', $dateFormat)
                            ->where('absence_type', 'leave')
                            ->where('status', 'Approved')
                            ->get()
                            ->reduce(function ($carry, $leave) use ($dateFormat) {
                                $startDate = new \DateTime($leave->start_date);
                                $endDate = new \DateTime($leave->end_date);
                                $total_leave_days = 0;

                                while ($startDate <= $endDate) {
                                    if ($startDate->format('N') <= 5) { // Memeriksa apakah hari adalah Senin hingga Jumat
                                        $total_leave_days++;
                                    }
                                    $startDate->add(new \DateInterval('P1D')); // Menambahkan 1 hari ke tanggal start_date
                                }

                                return $carry + $total_leave_days;
                            }, 0);

                        // Hitung total cuti per bulan pada tanggal tertentu
                        $totalLeavePerMonth = Leave::whereIn('employee_id', $employeeIds)
                            ->whereDate('applied_on', '=', $dateFormat)
                            ->where('absence_type', 'leave')
                            ->where('status', 'Approved')
                            ->get()
                            ->reduce(function ($carry, $leave) use ($dateFormat) {
                                $startDate = new \DateTime($leave->start_date);
                                $endDate = new \DateTime($leave->end_date);
                                $total_leave_days = 0;

                                while ($startDate <= $endDate) {
                                    if ($startDate->format('N') <= 5) { // Memeriksa apakah hari adalah Senin hingga Jumat
                                        $total_leave_days++;
                                    }
                                    $startDate->add(new \DateInterval('P1D')); // Menambahkan 1 hari ke tanggal start_date
                                }

                                return $carry + $total_leave_days;
                            }, 0);

                        // Hitung total izin sakit pada tanggal tertentu
                        $sickLeaveCount = Leave::whereIn('employee_id', $employeeIds)
                            ->whereDate('applied_on', '=', $dateFormat)
                            ->where('absence_type', 'sick')
                            ->count();

                        $totalLeaves[] = $leaveCount;
                        $totalLeavePerMonths[] = $totalLeavePerMonth;
                        $totalSickLeaves[] = $sickLeaveCount;
                    }

                    $totalLeavePerMonth = array_sum($totalLeavePerMonths);
                    $totalSickPerMonth = array_sum($totalSickLeaves);

                }

                return view('report.absence_staff', compact('totalLeaves', 'totalSickLeaves','month','year','dates','totalLeavePerMonth','totalSickPerMonth'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
    }

    public function timesheet(Request $request)
    {
        $user = Auth::user();
        if (\Auth::user()->can('manage report')) {
            $branch = Branch::get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');
        
            $employees = Employee::select('user_id', 'name')
                ->whereHas('user', function ($query) {
                    $query->where('is_active', 1);
                });
        
            if (!empty($request->employee_id) && $request->employee_id[0] != 0) {
                $employees->whereIn('id', $request->employee_id);
            }
        
            if (!empty($request->branch)) {
                $employees->where('branch_id', $request->branch);
                $data['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }
        
            $employees = $employees->get()->pluck('name', 'user_id');
        
            $startDate = !empty($request->start_date) ? $request->start_date : null;
            $endDate = !empty($request->end_date) ? $request->end_date : null;
        
            $employeesAttendance = [];
            
            foreach ($employees as $userId => $employeeName) {
                $timesheetQuery = Timesheet::where('created_by', $userId);
                $meetingQuery = Meeting::where('created_by', $userId);
                
                if ($startDate && $endDate) {
                    $timesheetQuery->whereBetween('date', [$startDate, $endDate]);
                    $meetingQuery->whereBetween('date', [$startDate, $endDate]);
                } else {
                    $month = date('m');
                    $year = date('Y');
                    $curMonth = date('M-Y', strtotime($year . '-' . $month));
                    
                    $timesheetQuery->whereMonth('date', $month)->whereYear('date', $year);
                    $meetingQuery->whereMonth('date', $month)->whereYear('date', $year);
                }
        
                $timesheetData = $timesheetQuery->get();
                $meetingData = $meetingQuery->get();
        
                $totalWorkingHours = 0;
                $totalMeetingHours = 0;
        
                foreach ($timesheetData as $timesheet) {
                    list($hours, $minutes, $seconds) = explode(':', $timesheet->time);
                    $totalWorkingHours += ($hours * 3600) + ($minutes * 60) + $seconds;
                }
        
                foreach ($meetingData as $meeting) {
                    list($hours, $minutes, $seconds) = explode(':', $meeting->time);
                    $totalMeetingHours += ($hours * 3600) + ($minutes * 60) + $seconds;
                }
        
                $formattedWorkingHours = sprintf('%02d:%02d:%02d', floor($totalWorkingHours / 3600), floor(($totalWorkingHours % 3600) / 60), ($totalWorkingHours % 60));
                $formattedMeetingHours = sprintf('%02d:%02d:%02d', floor($totalMeetingHours / 3600), floor(($totalMeetingHours % 3600) / 60), ($totalMeetingHours % 60));
        
                $employeesAttendance[] = [
                    'name' => $employeeName,
                    'total_working_hours' => $formattedWorkingHours,
                    'total_meeting_hours' => $formattedMeetingHours,
                    'id' => $userId,
                ];
            }

        
            return view('report.timesheet', compact('employeesAttendance', 'branch', ));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
    }

    public function employeeTimesheet(Request $request, $employee_id, $startdate, $enddate)
    {
        if(\Auth::user()->can('manage report'))
        {
            $employee_timesheet   = Timesheet::where('created_by', $employee_id);

            if($startdate && $enddate)
            {
                $employee_timesheet->whereBetween('date', [$startdate, $enddate]);
            }

            $employee_timesheet = $employee_timesheet->get();


            return view('report.timesheetShow', compact('employee_timesheet'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }

    public function employeeMeeting(Request $request, $employee_id, $startdate, $enddate)
    {
        if(\Auth::user()->can('manage report'))
        {
            $employee_meeting   = Meeting::where('created_by', $employee_id);
            
            if($startdate && $enddate)
            {
                $employee_meeting->whereBetween('date', [$startdate, $enddate]);
            }

            $employee_meeting = $employee_meeting->get();


            return view('report.meetingShow', compact('employee_meeting'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }






}
