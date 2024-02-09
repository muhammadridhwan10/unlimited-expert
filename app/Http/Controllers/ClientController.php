<?php

namespace App\Http\Controllers;

use App\Models\ClientDeal;
use App\Models\ClientPermission;
use App\Models\Contract;
use App\Models\CustomField;
use App\Models\Estimation;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\User;
use App\Models\Client as Clients;
use App\Models\Utility;
use App\Models\ClientBusinessSector;
use http\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(
            [
                'auth',
                'XSS',
            ]
        );
    }

    public function index()
    {
        if(\Auth::user()->can('manage client'))
        {
            $user    = \Auth::user();
            $clients = User::where('type', '=', 'client')->get();

            return view('clients.index', compact('clients'));
        }
        else
        {

            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create(Request $request)
    {

        if(\Auth::user()->can('create client'))
        {
            if($request->ajax)
            {
                return view('clients.createAjax');
            }
            else
            {
                $businesssector   = ClientBusinessSector::get()->pluck('name', 'id');
                $businesssector->prepend('Select Business Sector', '');
                $customFields = CustomField::where('module', '=', 'client')->get();

                return view('clients.create', compact('businesssector','customFields'));
            }
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create client'))
        {
            $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->where('created_by', '=', \Auth::user()->creatorId())->first();

            $user      = \Auth::user();
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                if($request->ajax)
                {
                    return response()->json(['error' => $messages->first()], 401);
                }
                else
                {
                    return redirect()->back()->with('error', $messages->first());
                }
            }
            $objCustomer    = \Auth::user();
            $creator        = User::find($objCustomer->creatorId());
            $total_client = User::where('created_by', '=', \Auth::user()->creatorId())->where('type','client')->count();
            $plan           = Plan::find($creator->plan);
            if($total_client < $plan->max_clients || $plan->max_clients == -1)
            {
                $role = Role::findByName('client');
                $client = User::create(
                    [
                        'name' => $request->name,
                        'email' => $request->email,
                        'job_title' => $request->job_title,
                        'type' => 'client',
                        'lang' => !empty($default_language) ? $default_language->value : 'en',
                        'created_by' => $user->creatorId(),
                        'email_verified_at' => date('Y-m-d H:i:s'),
                    ]
                );

                $clients = Clients::create(
                    [
                        'user_id'       => $client->id,
                        'name_invoice'  => $request->name_invoice,
                        'position'      => $request->position,
                        'telp'          => $request->telp,
                        'npwp'          => $request->npwp,
                        'address'       => $request->address,
                        'country'       => $request->country,
                        'state'         => $request->state,
                        'city'          => $request->city,
                        'client_business_sector_id' => $request->client_business_sector_id,
                        'created_by' => $user->creatorId(),
                    ]
                );

                //Send Email
                $setings = Utility::settings();

                // if($setings['new_client'] == 1)
                // {
                //     $role_r = Role::findByName('client');
                //     $client->assignRole($role_r);

                //     $clientArr = [
                //         'client_name' => $client->name,
                //         'client_email' => $client->email,
                //         'client_password' =>  $client->password,
                //     ];
                //     $resp = Utility::sendEmailTemplate('new_client', [$client->email], $clientArr);
                //     return redirect()->route('clients.index')->with('success', __('Client successfully added.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                // }
                return redirect()->route('clients.index')->with('success', __('Client successfully created.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Your client limit is over, Please upgrade plan.'));
            }
        }
        else
        {
            if($request->ajax)
            {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
    }

    public function show(User $client)
    {
        $usr = Auth::user();
        $clients = Clients::where('user_id', $client->id)->first();
        if(\Auth::user()->can('manage client'))
        {
            // For Estimations
            $estimations = $client->clientEstimations()->orderByDesc('id')->get();
            $curr_month  = $client->clientEstimations()->whereMonth('issue_date', '=', date('m'))->get();
            $curr_week   = $client->clientEstimations()->whereBetween(
                'issue_date', [
                                \Carbon\Carbon::now()->startOfWeek(),
                                \Carbon\Carbon::now()->endOfWeek(),
                            ]
            )->get();
            $last_30days = $client->clientEstimations()->whereDate('issue_date', '>', \Carbon\Carbon::now()->subDays(30))->get();
            // Estimation Summary
            $cnt_estimation                = [];
            $cnt_estimation['total']       = Estimation::getEstimationSummary($estimations);
            $cnt_estimation['this_month']  = Estimation::getEstimationSummary($curr_month);
            $cnt_estimation['this_week']   = Estimation::getEstimationSummary($curr_week);
            $cnt_estimation['last_30days'] = Estimation::getEstimationSummary($last_30days);

            $cnt_estimation['cnt_total']       = $estimations->count();
            $cnt_estimation['cnt_this_month']  = $curr_month->count();
            $cnt_estimation['cnt_this_week']   = $curr_week->count();
            $cnt_estimation['cnt_last_30days'] = $last_30days->count();

            // For Contracts
            $contracts   = $client->clientContracts()->orderByDesc('id')->get();
            $curr_month  = $client->clientContracts()->whereMonth('start_date', '=', date('m'))->get();
            $curr_week   = $client->clientContracts()->whereBetween(
                'start_date', [
                                \Carbon\Carbon::now()->startOfWeek(),
                                \Carbon\Carbon::now()->endOfWeek(),
                            ]
            )->get();
            $last_30days = $client->clientContracts()->whereDate('start_date', '>', \Carbon\Carbon::now()->subDays(30))->get();

            // Contracts Summary
            $cnt_contract                = [];
            $cnt_contract['total']       = Contract::getContractSummary($contracts);
            $cnt_contract['this_month']  = Contract::getContractSummary($curr_month);
            $cnt_contract['this_week']   = Contract::getContractSummary($curr_week);
            $cnt_contract['last_30days'] = Contract::getContractSummary($last_30days);

            $cnt_contract['cnt_total']       = $contracts->count();
            $cnt_contract['cnt_this_month']  = $curr_month->count();
            $cnt_contract['cnt_this_week']   = $curr_week->count();
            $cnt_contract['cnt_last_30days'] = $last_30days->count();

            return view('clients.show', compact('client','clients', 'estimations', 'cnt_estimation', 'contracts', 'cnt_contract'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function edit(User $client)
    {
        if(\Auth::user()->can('edit client'))
        {
            $user = \Auth::user();
            $client->customField = CustomField::getData($client, 'client');
            $customFields        = CustomField::where('module', '=', 'client')->get();
            $businesssector      = ClientBusinessSector::get()->pluck('name', 'id');
            $businesssector->prepend('Select Business Sector', '');
            $clients = Clients::where('user_id', $client->id)->first();
            return view('clients.edit', compact('client', 'businesssector','clients', 'customFields'));
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function update(User $client, Request $request)
    {
        if(\Auth::user()->can('edit client'))
        {
            $user = \Auth::user();
            $validation = [
                'name' => 'required',
                'email' => 'required'
            ];

            $clients = Clients::where('user_id', $client->id)->first();

            $post                   = [];
            $clientss               = [];
            $post['name']           = $request->name;

            // if(!empty($request->password))
            // {
            //     $validation['password'] = 'required';
            //     $post['password']       = Hash::make($request->password);
            // }

            $validator = \Validator::make($request->all(), $validation);
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $post['email'] = $request->email;

            $clientss['name_invoice']   = $request->name_invoice;
            $clientss['position']       = $request->position;
            $clientss['telp']           = $request->telp;
            $clientss['npwp']           = $request->npwp;
            $clientss['address']        = $request->address;
            $clientss['country']        = $request->country;
            $clientss['state']          = $request->state;
            $clientss['city']           = $request->city;
            $clientss['client_business_sector_id'] = $request->client_business_sector_id;

            $client->update($post);
            $clients->update($clientss);

            CustomField::saveData($client, $request->customField);

            return redirect()->back()->with('success', __('Client Updated Successfully!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function destroy(User $client)
    {
            $user = \Auth::user();
            $estimation = Estimation::where('client_id', '=', $client->id)->first();
            if(empty($estimation))
            {
                $clients = Clients::find($client->id);
                /*  ClientDeal::where('client_id', '=', $client->id)->delete();
                ClientPermission::where('client_id', '=', $client->id)->delete();*/
                $client->delete();
                $clients->delete();
                return redirect()->back()->with('success', __('Client Deleted Successfully!'));
            }
            else
            {
                return redirect()->back()->with('error', __('This client has assigned some estimation.'));
            }
    }

    public function clientPassword($id)
    {
        $eId        = \Crypt::decrypt($id);
        $user = User::find($eId);
        $client = User::where('created_by', '=', $user->creatorId())->where('type', '=', 'client')->first();


        return view('clients.reset', compact('user', 'client'));
    }

    public function clientPasswordReset(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'password' => 'required|confirmed|same:password_confirmation',
                           ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $user                 = User::where('id', $id)->first();
        $user->forceFill([
                             'password' => Hash::make($request->password),
                         ])->save();

        return redirect()->route('clients.index')->with(
            'success', 'Client Password successfully updated.'
        );


    }

    // public function filterClientView(Request $request)
    // {

    //     if(\Auth::user()->can('manage client'))
    //     {
    //         $usr           = Auth::user();
    //         if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
    //         {
    //             $user_projects = User::where('type', '=', 'client')->pluck('id','id')->toArray();
    //         }
    //         if($request->ajax() && $request->has('view') && $request->has('sort'))
    //         {
    //             $sort     = explode('-', $request->sort);
    //             $clients = User::whereIn('id', array_keys($user_projects))->orderBy($sort[0], $sort[1]);

    //             if(!empty($request->keyword))
    //             {
    //                 $clients->where('name', 'LIKE', $request->keyword . '%');
    //             }

    //             $clients   = $clients->get();
    //             $returnHTML = view('clients.' . $request->view, compact('clients', 'user_projects'))->render();

    //             return response()->json(
    //                 [
    //                     'success' => true,
    //                     'html' => $returnHTML,
    //                 ]
    //             );
    //         }
    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', __('Permission Denied.'));
    //     }
    // }



}
