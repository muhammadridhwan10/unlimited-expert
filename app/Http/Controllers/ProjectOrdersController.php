<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectOrders;
use App\Models\User;
use App\Models\ClientBusinessSector;
use App\Models\ClientAccountingStandard;
use App\Models\ClientOwnershipStatus;
use App\Models\PublicAccountant;
use App\Models\ProductServiceCategory;
use App\Models\ProjectTask;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\ProjectOfferings;
use App\Models\Client;
use App\Mail\ApprovalSend;
use App\Mail\ProjectNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class ProjectOrdersController extends Controller
{
    public function index(Request $request)
    {
        if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting')
        {
            $orders = ProjectOrders::where('status_client', '=', 'Approved')->get();
            return view('projectorder.index', compact('orders'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting')
        {
            $businesssector   = ClientBusinessSector::get()->pluck('name', 'id');
            $ownership   = ClientOwnershipStatus::get()->pluck('name', 'id');
            $accountingstandards   = ClientAccountingStandard::get()->pluck('name', 'id');
            $leader   = User::where('type', '!=', 'client')->where('type', '!=', 'admin')->get()->pluck('name', 'id');
            $tasktemplate = ProductServiceCategory::get()->pluck('name', 'id');
            $public_accountant = PublicAccountant::get()->pluck('name', 'id');
            $public_accountant->prepend('Select Public Accountant', '');
            $leader->prepend('Select Leader Project', '');
            $tasktemplate->prepend('Select Task Template', '');
            $businesssector->prepend('Select Business Sector', '');
            $ownership->prepend('Select Ownership', '');
            $accountingstandards->prepend('Select Accounting Standars', '');
        
            return view('projectorder.create', compact('businesssector','ownership','accountingstandards','leader','tasktemplate','public_accountant'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    
    }

    public function store(Request $request)
    {
        if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting')
        {
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
            $projectOrder->total_employee = $request->total_employee;
            $projectOrder->total_branch_offices = $request->total_branch_offices;
            $projectOrder->npwp = $request->npwp;
            $projectOrder->address = $request->address;
            $projectOrder->country = $request->country;
            $projectOrder->state = $request->state;
            $projectOrder->city = $request->city;
            $projectOrder->project_name = $request->project_name;
            $projectOrder->start_date = $request->start_date;
            $projectOrder->end_date = $request->end_date;
            $projectOrder->tags = $request->tags;
            $projectOrder->label = $request->label;
            $projectOrder->status = $request->status;
            $projectOrder->template_task_id = $request->template_task_id;
            $projectOrder->public_accountant_id = $request->public_accountant_id;
            $projectOrder->leader_project = $request->leader_project;
            $projectOrder->description = $request->description;
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

            
            $lastOrder = ProjectOrders::orderBy('id', 'desc')->first();
            if ($lastOrder) {
                $lastIncrement = intval(substr($lastOrder->order_number, -2));
                $nextIncrement = sprintf('%02d', $lastIncrement + 1);
            } else {
                $nextIncrement = '01';
            }

            if($projectOrder->public_accountant_id == 1)
            {
                $bulan = date('n');
                $bulanRomawi = $this->bulanToRoman($bulan);
                $projectOrder->order_number = 'AUR/SLS/MJ/' . date('Y') . '/' . $bulanRomawi . '/' . $nextIncrement;
            }
            elseif($projectOrder->public_accountant_id == 3)
            {
                $bulan = date('n');
                $bulanRomawi = $this->bulanToRoman($bulan);
                $projectOrder->order_number = 'AUR/SLS/RA/' . date('Y') . '/' . $bulanRomawi . '/' . $nextIncrement;
            }
            elseif($projectOrder->public_accountant_id == 4)
            {
                $bulan = date('n');
                $bulanRomawi = $this->bulanToRoman($bulan);
                $projectOrder->order_number = 'AUR/SLS/DA/' . date('Y') . '/' . $bulanRomawi . '/' . $nextIncrement;
            }

            $projectOrder->save();

            return redirect()->route('project-orders.index')->with('success', 'Project Order created successfully.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(ProjectOrders $projectOrder)
    {
        if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting')
        {
            $businesssector   = ClientBusinessSector::get()->pluck('name', 'id');
            $ownership   = ClientOwnershipStatus::get()->pluck('name', 'id');
            $accountingstandards   = ClientAccountingStandard::get()->pluck('name', 'id');
            $leader   = User::where('type', '!=', 'client')->where('type', '!=', 'admin')->get()->pluck('name', 'id');
            $tasktemplate = ProductServiceCategory::get()->pluck('name', 'id');
            $public_accountant = PublicAccountant::get()->pluck('name', 'id');
            $public_accountant->prepend('Select Public Accountant', '');
            $leader->prepend('Select Leader Project', '');
            $tasktemplate->prepend('Select Task Template', '');
            $businesssector->prepend('Select Business Sector', '');
            $ownership->prepend('Select Ownership', '');
            $accountingstandards->prepend('Select Accounting Standars', '');

            return view('projectorder.edit', compact('projectOrder','businesssector','ownership','accountingstandards','leader','tasktemplate','public_accountant'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting')
        {
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
            $projectOrder->total_employee = $request->total_employee;
            $projectOrder->total_branch_offices = $request->total_branch_offices;
            $projectOrder->npwp = $request->npwp;
            $projectOrder->address = $request->address;
            $projectOrder->country = $request->country;
            $projectOrder->state = $request->state;
            $projectOrder->city = $request->city;
            $projectOrder->project_name = $request->project_name;
            $projectOrder->start_date = $request->start_date;
            $projectOrder->end_date = $request->end_date;
            $projectOrder->tags = $request->tags;
            $projectOrder->label = $request->label;
            $projectOrder->status = $request->status;
            $projectOrder->template_task_id = $request->template_task_id;
            $projectOrder->public_accountant_id = $request->public_accountant_id;
            $projectOrder->leader_project = $request->leader_project;
            $projectOrder->description = $request->description;
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
            $projectOrder->save();

            return redirect()->route('project-orders.index')->with('success', 'Project Order updated successfully.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($ids)
    {
        if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting')
        {

            $id      = Crypt::decrypt($ids);
            $projectOrder = ProjectOrders::find($id);

            return view('projectorder.view', compact('projectOrder'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting')
        {
            $projectOrder = ProjectOrders::findOrFail($id);
            
            $projectOrder->delete();

            return redirect()->route('project-orders.index')->with('success', 'Project Order deleted successfully.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function approval($id)
    {
        if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting')
        {
            $projectOrder = ProjectOrders::find($id);
            $partners = User::where('type', 'partners')
            ->get()
            ->pluck('name', 'id');
            $partners->prepend('Select Partner', '');
            return view('projectorder.approval', compact('projectOrder','partners'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function approved($id)
    {
        if(\Auth::user()->type == 'partners')
        {
            $projectOrder = ProjectOrders::find($id);
            return view('projectorder.approved', compact('projectOrder'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function sent($id)
    {
        if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting')
        {
            $selectedApproval = request('approval');
            $ccEmails = [];

            $ccEmailInput = request('cc_email');
            $ccEmails = preg_split('/[,\s]+/', $ccEmailInput);
            $ccEmails = array_unique(array_filter($ccEmails));

            $projectOrder     = ProjectOrders::where('id', $id)->first();
            $approval         = User::where('id', $selectedApproval)->first();

            Mail::to($approval->email)->send(new ApprovalSend($projectOrder, $ccEmails));

            return redirect()->back()->with('success', __('Approval successfully sent.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function sentApproved(Request $request, $id)
    {
        if(\Auth::user()->type == 'partners')
        {
            $projectOrder = ProjectOrders::findOrFail($id);

            $projectOrder->is_fulfilling_prospective_clients = $request->input('is_fulfilling_prospective_clients');
            $projectOrder->is_fulfill = $request->input('is_fulfill');

            if ($request->input('is_fulfilling_prospective_clients') == '1' && $request->input('is_fulfill') == '1') {
                $projectOrder->is_approve = '1';
            } else {
                $projectOrder->is_approve = '0';
            }

            $projectOrder->save();

            if($projectOrder->is_approve = '1')
            {
                $projectData = $projectOrder->toArray();

                $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
                $role_r           = Role::findById(4);

                //proses create user client ketika project orders di approve
                $userClient             = new User();
                $userClient->name       = $projectData['name'];
                $userClient->email      = $projectData['email'];
                $userClient->password   = Hash::make('tgsau123');
                $userClient->type       = $role_r->name;
                $userClient->lang       = !empty($default_language) ? $default_language->value : 'en';
                $userClient->created_by = \Auth::user()->creatorId();
                $userClient->save();
                $userClient->assignRole($role_r);

                //proses create client data ketika project orders di approve
                $client = new Client();
                $client->user_id = $userClient->id;
                $client->name_pic = $projectData['name_pic'];
                $client->email_pic = $projectData['email_pic'];
                $client->telp_pic = $projectData['telp_pic'];
                $client->name_invoice = $projectData['name_invoice'];
                $client->position = $projectData['position'];
                $client->telp = $projectData['telp'];
                $client->npwp = $projectData['npwp'];
                $client->address = $projectData['address'];
                $client->country = $projectData['country'];
                $client->state = $projectData['state'];
                $client->city = $projectData['city'];
                $client->total_company_income_per_year = $projectData['total_company_income_per_year'];
                $client->total_company_assets_value = $projectData['total_company_assets_value'];
                $client->total_employee = $projectData['total_employee'];
                $client->total_branch_offices = $projectData['total_branch_offices'];
                $client->client_business_sector_id = $projectData['client_business_sector_id'];
                $client->client_ownership_id = $projectData['client_ownership_id'];
                $client->accounting_standars_id = $projectData['accounting_standars_id'];
                $client->save();
                
                //proses create project data ketika project orders di approve
                $project = new Project();
                $project->project_name = $projectData['project_name'];
                $project->start_date = $projectData['start_date'];
                $project->end_date = $projectData['end_date'];
                $project->budget = $projectData['budget'];
                $project->client_id = $userClient->id;
                $project->public_accountant_id = $projectData['public_accountant_id'];
                $project->template_task_id = $projectData['template_task_id'];
                $project->description = $projectData['description'];
                $project->status = $projectData['status'];
                $project->estimated_hrs = $projectData['estimated_hrs'];
                $project->tags = $projectData['tags'];
                $project->label = $projectData['label'];
                $project->created_by = \Auth::user()->creatorId();
                $project->save();

                $value = $projectData['leader_project'];
                
                ProjectUser::create(
                    [
                        'project_id' => $project->id,
                        'user_id' => $value,
                    ]
                );

                $datas = User::where('id', $value)->pluck('email');
                Mail::to($datas)->send(new ProjectNotification($project));

                $template = Project::with('details')->get();
                foreach ($template as $templates) 
                {
                    $details = $templates->details;
                }

                if($projectData['template_task_id'] !== NULL)
                {
                    $category = $request->items;
                    $category_id = $request->category_id;


                    for($i = 0; $i < count($details); $i++)
                    {
                        // dd($details);
                        $tasks                 = new ProjectTask();
                        $tasks->project_id     = $project->id;
                        $tasks->assign_to      = 0;
                        $tasks->stage_id       =  $details[$i]['stage_id'];
                        $tasks->name           = $details[$i]['name'];
                        $tasks->category_template_id      =  $details[$i]['category_template_id'];
                        $tasks->start_date     = $project->start_date;
                        $tasks->end_date       = $project->end_date;
                        $tasks->estimated_hrs  = $details[$i]['estimated_hrs'];
                        $tasks->description    = $details[$i]['description'];
                        $tasks->created_by     = \Auth::user()->creatorId();
                        $tasks->save();

                    }
                }
                else
                {
                    $project = Project::find($project->id);

                    $project->update(
                        [
                            'is_template' => 0,
                        ]
                    );
                }

                $project_offerings = new ProjectOfferings();
                $project_offerings->project_id = $project->id;
                $project_offerings->als_partners = $projectData['ph_partners'];
                $project_offerings->rate_partners = $projectData['rate_partners'];
                $project_offerings->als_manager = $projectData['ph_manager'];
                $project_offerings->rate_manager = $projectData['rate_manager'];
                $project_offerings->als_senior_associate = $projectData['ph_senior'];
                $project_offerings->rate_senior_associate = $projectData['rate_senior'];
                $project_offerings->als_associate = $projectData['ph_associate'];
                $project_offerings->rate_associate = $projectData['rate_associate'];
                $project_offerings->als_intern = $projectData['ph_assistant'];
                $project_offerings->rate_intern = $projectData['rate_assistant'];
                $project_offerings->save();
            }

            return redirect()->back()->with('success', __('Project Order status updated successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function bulanToRoman($bulan) {
        switch ($bulan) {
            case 1: return 'I';
            case 2: return 'II';
            case 3: return 'III';
            case 4: return 'IV';
            case 5: return 'V';
            case 6: return 'VI';
            case 7: return 'VII';
            case 8: return 'VIII';
            case 9: return 'IX';
            case 10: return 'X';
            case 11: return 'XI';
            case 12: return 'XII';
            default: return '';
        }
    }
    

}
