<?php

namespace App\Http\Controllers;

use App\Models\ProjectOrders;
use App\Models\FormField;
use App\Models\FormFieldResponse;
use App\Models\FormResponse;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Pipeline;
use App\Models\User;
use App\Models\UserLead;
use App\Models\ClientBusinessSector;
use App\Models\ClientAccountingStandard;
use App\Models\ClientOwnershipStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormClientController extends Controller
{
    public function index()
    {
        $usr = \Auth::user();
        if($usr->type = 'admin')
        {
            $client = ProjectOrders::all();

            return view('form_client.index', compact('client'));
        }
        elseif($usr->type = 'comapany')
        {
            $client = ProjectOrders::all();

            return view('form_client.index', compact('client'));
        }
        else
        {
            $client = ProjectOrders::where('created_by', '=', $usr->creatorId())->get();

            return view('form_client.index', compact('client'));
        }
    }


    public function show($id)
    {
        $projectOrder = ProjectOrders::findOrFail($id);

        return view('form_client.show', compact('projectOrder'));
    }

    public function edit($id)
    {
        $projectOrder = ProjectOrders::findOrFail($id);
        $businesssector   = ClientBusinessSector::get()->pluck('name', 'id');
        $ownership   = ClientOwnershipStatus::get()->pluck('name', 'id');
        $accountingstandards   = ClientAccountingStandard::get()->pluck('name', 'id');
        
        $businesssector->prepend('Select Business Sector', '');
        $ownership->prepend('Select Ownership', '');
        $accountingstandards->prepend('Select Accounting Standars', '');

        return view('form_client.edit', compact('projectOrder', 'businesssector', 'ownership', 'accountingstandards'));
    }



    public function update(Request $request, $id)
    {
        // Validasi data
        $rules = [
            'name' => 'required|string|max:255',
            'client_business_sector_id' => 'required|integer',
            'email' => 'required|string|email|max:255',
            'name_pic' => 'required|string|max:255',
            'email_pic' => 'required|string|email|max:255',
            'telp_pic' => 'required|string|max:20',
            'total_company_income_per_year' => 'required|numeric',
            'total_company_assets_value' => 'required|numeric',
            'total_company_profit_or_loss' => 'required|numeric',
            'total_employee' => 'required|integer',
            'total_branch_offices' => 'required|integer',
            'npwp' => 'required|string|max:25',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'periode' => 'required|string',
            'where_did_you_find_out_about_us' => 'required|string',
            'ph_partners' => 'required|numeric',
            'rate_partners' => 'required|numeric',
            'ph_manager' => 'required|numeric',
            'rate_manager' => 'required|numeric',
            'ph_senior' => 'required|numeric',
            'rate_senior' => 'required|numeric',
            'ph_associate' => 'required|numeric',
            'rate_associate' => 'required|numeric',
            'ph_assistant' => 'required|numeric',
            'rate_assistant' => 'required|numeric',
            'estimated_hrs' => 'required|numeric',
            'budget' => 'required|numeric',
        ];
    
        if ($request->category_services === 'Audit') {
            $rules['client_ownership_id'] = 'required';
            $rules['accounting_standars_id'] = 'required';
        }
    
        if (in_array($request->category_services, ['KPPK', 'Agreed Upon Procedures (AUP)', 'Other'])) {
            unset($rules['periode']);
        }
    
        if ($request->category_services === 'KPPK') {
            unset($rules['total_company_assets_value']);
        }

        $validator = \Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
    
            return redirect()->back()->with('error', $messages->first());
        }

        // dd($request->all());

        // Menyimpan data ke database
        $projectOrder = ProjectOrders::findOrFail($id);
        $projectOrder->name = $request->name;
        $projectOrder->client_business_sector_id = $request->client_business_sector_id;
        $projectOrder->client_ownership_id = $request->client_ownership_id ?? 0;
        $projectOrder->accounting_standars_id = $request->accounting_standars_id ?? 0;
        $projectOrder->email = $request->email;
        $projectOrder->name_invoice = $request->name_invoice;
        $projectOrder->position = $request->position;
        $projectOrder->telp = $request->telp;
        $projectOrder->name_pic = $request->name_pic;
        $projectOrder->email_pic = $request->email_pic;
        $projectOrder->telp_pic = $request->telp_pic;
        $projectOrder->total_company_income_per_year = $request->total_company_income_per_year;
        $projectOrder->total_company_assets_value = $request->total_company_assets_value;
        $projectOrder->total_company_profit_or_loss = $request->total_company_profit_or_loss;
        $projectOrder->total_employee = $request->total_employee;
        $projectOrder->total_branch_offices = $request->total_branch_offices;
        $projectOrder->npwp = $request->npwp;
        $projectOrder->address = $request->address;
        $projectOrder->country = $request->country;
        $projectOrder->state = $request->state;
        $projectOrder->city = $request->city;
        $projectOrder->periode = $request->periode;
        $projectOrder->where_did_you_find_out_about_us = $request->where_did_you_find_out_about_us;
        $projectOrder->category_service = $request->category_services;
        $projectOrder->note = $request->note;
        $projectOrder->ph_partners = $request->ph_partners;
        $projectOrder->rate_partners = $request->rate_partners;
        $projectOrder->ph_manager = $request->ph_manager;
        $projectOrder->rate_manager = $request->rate_manager;
        $projectOrder->ph_senior = $request->ph_senior;
        $projectOrder->rate_senior = $request->rate_senior;
        $projectOrder->ph_associate = $request->ph_associate;
        $projectOrder->rate_associate = $request->rate_associate;
        $projectOrder->ph_assistant = $request->ph_assistant;
        $projectOrder->rate_assistant = $request->rate_assistant;
        $projectOrder->estimated_hrs = $request->estimated_hrs;
        $projectOrder->budget = $request->budget;
        $projectOrder->created_by = \Auth::user()->creatorId();

        $projectOrder->save();

        return redirect()->route('form_client.index')->with('success', __('Data updated successfully.'));
    }

    // For Front Side View
    public function formClientView()
    {
        $businesssector   = ClientBusinessSector::get()->pluck('name', 'id');
        $ownership   = ClientOwnershipStatus::get()->pluck('name', 'id');
        $accountingstandards   = ClientAccountingStandard::get()->pluck('name', 'id');
        
        $businesssector->prepend('Select Business Sector', '');
        $ownership->prepend('Select Ownership', '');
        $accountingstandards->prepend('Select Accounting Standars', '');

        return view('form_client.form_view', compact('businesssector', 'ownership', 'accountingstandards'));
    }

    // For Front Side View Store
    public function formClientViewStore(Request $request)
    {

        $rules = [
            'name' => 'required|string|max:255',
            'client_business_sector_id' => 'required|integer',
            'email' => 'required|string|email|max:255',
            'name_pic' => 'required|string|max:255',
            'email_pic' => 'required|string|email|max:255',
            'telp_pic' => 'required|string|max:20',
            'total_company_income_per_year' => 'required|numeric',
            'total_company_assets_value' => 'required|numeric',
            'total_company_profit_or_loss' => 'required|numeric',
            'total_employee' => 'required|integer',
            'total_branch_offices' => 'required|integer',
            'npwp' => 'required|string|max:25',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'periode' => 'required|string',
            'where_did_you_find_out_about_us' => 'required|string',
        ];
    
        if ($request->category_services === 'Audit') {
            $rules['client_ownership_id'] = 'required';
            $rules['accounting_standars_id'] = 'required';
        }
    
        if (in_array($request->category_services, ['KPPK', 'Agreed Upon Procedures (AUP)', 'Other'])) {
            unset($rules['periode']);
        }
    
        if ($request->category_services === 'KPPK') {
            unset($rules['total_company_assets_value']);
        }
    
        $validator = \Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
    
            return redirect()->back()->with('error', $messages->first());
        }

        $projectOrder = new ProjectOrders();
        $projectOrder->name = $request->name;
        $projectOrder->client_business_sector_id = $request->client_business_sector_id;
        $projectOrder->client_ownership_id = $request->client_ownership_id ?? 0;
        $projectOrder->accounting_standars_id = $request->accounting_standars_id ?? 0;
        $projectOrder->email = $request->email;
        $projectOrder->name_invoice = $request->name_invoice;
        $projectOrder->position = $request->position;
        $projectOrder->telp = $request->telp;
        $projectOrder->name_pic = $request->name_pic;
        $projectOrder->email_pic = $request->email_pic;
        $projectOrder->telp_pic = $request->telp_pic;
        $projectOrder->total_company_income_per_year = $request->total_company_income_per_year;
        $projectOrder->total_company_assets_value = $request->total_company_assets_value;
        $projectOrder->total_company_profit_or_loss = $request->total_company_profit_or_loss;
        $projectOrder->total_employee = $request->total_employee;
        $projectOrder->total_branch_offices = $request->total_branch_offices;
        $projectOrder->npwp = $request->npwp;
        $projectOrder->address = $request->address;
        $projectOrder->country = $request->country;
        $projectOrder->state = $request->state;
        $projectOrder->city = $request->city;
        $projectOrder->periode = $request->periode;
        $projectOrder->where_did_you_find_out_about_us = $request->where_did_you_find_out_about_us;
        $projectOrder->category_service = $request->category_services;
        $projectOrder->note = $request->note;
        $projectOrder->created_by = \Auth::user()->creatorId();

        $projectOrder->save();

        return redirect()->back()->with('success', __('Data submit successfully. We will contact you soon, please wait up to 7 days.'));
    }

    public function updateStatus($id, Request $request)
    {
        $projectOrder = ProjectOrders::findOrFail($id);

        // dd($request->all());

        $projectOrder->status_client = $request->status_client;

        if($projectOrder->status_client == 'Approval')
        {
            $projectOrder->status_client = 'Approved';
        }
        elseif($projectOrder->status_client == 'Reject')
        {
            $projectOrder->status_client = 'Rejected';
        }

        $projectOrder->save();

        return redirect()->route('form_client.index')->with('success', __('Client Data Status Successfully Changed'));

    }

    public function formTimeBudget($id)
    {
        $projectOrder = ProjectOrders::findOrFail($id);

        return view('form_client.time-budget', compact('projectOrder'));
    }

    public function addTimeBudget($id, Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                'ph_partners' => 'required|numeric',
                'rate_partners' => 'required|numeric',
                'ph_manager' => 'required|numeric',
                'rate_manager' => 'required|numeric',
                'ph_senior' => 'required|numeric',
                'rate_senior' => 'required|numeric',
                'ph_associate' => 'required|numeric',
                'rate_associate' => 'required|numeric',
                'ph_assistant' => 'required|numeric',
                'rate_assistant' => 'required|numeric',
                'estimated_hrs' => 'required|numeric',
                'ph_assbudgetistant' => 'required|numeric',
            ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        
        $projectOrder = ProjectOrders::findOrFail($id);

        $projectOrder->ph_partners = $request->ph_partners;
        $projectOrder->rate_partners = $request->rate_partners;
        $projectOrder->ph_manager = $request->ph_manager;
        $projectOrder->rate_manager = $request->rate_manager;
        $projectOrder->ph_senior = $request->ph_senior;
        $projectOrder->rate_senior = $request->rate_senior;
        $projectOrder->ph_associate = $request->ph_associate;
        $projectOrder->rate_associate = $request->rate_associate;
        $projectOrder->ph_assistant = $request->ph_assistant;
        $projectOrder->rate_assistant = $request->rate_assistant;
        $projectOrder->estimated_hrs = $request->estimated_hrs;
        $projectOrder->budget = $request->budget;

        $projectOrder->status_client = 'Pending';
        $projectOrder->save();

        return redirect()->route('form_client.index')->with('success', __('Time Budget Data Successfully Created'));

}
}
