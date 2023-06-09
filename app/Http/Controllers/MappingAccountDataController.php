<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use App\Models\MappingAccountData;
use App\Models\Materialitas;
use App\Models\CustomField;
use Illuminate\Support\Facades\Crypt;
use App\Models\ProductServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MappingAccountDataController extends Controller
{
    public function index(Request $request)
    {

        if(\Auth::user()->can('manage mapping account data'))
        {

            $materialitas          = Materialitas::limit(10)->pluck('name', 'id');
            $materialitas->prepend('All', '');
            $mapping_accounts      = MappingAccountData::all();

            if(!empty($request->materialitas))
            {
                $mapping_account = $mapping_accounts->where('account_group', '=', $request->materialitas);         
            }
            elseif($request->category_template_id = 'All')
            {
                $mapping_account = MappingAccountData::all();
            }

            return view('mappingaccountdata.index', compact('mapping_account', 'materialitas'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create()
    {

        if(\Auth::user()->can('create mapping account data'))
        {
            $materialitas = Materialitas::limit(10)->pluck('name', 'id');
            $materialitas->prepend('Select Group', '');
        
            return view('mappingaccountdata.create', compact('materialitas'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        if(\Auth::user()->can('create mapping account data'))
        {

            $materialitas = $request->items;
            $account_group = $request->account_group;


            for($i = 0; $i < count($materialitas); $i++)
            {
                $mapping_account                            = new MappingAccountData();
                $mapping_account->code                      = $materialitas[$i]['code'];
                $mapping_account->name                      = $materialitas[$i]['name'];
                $mapping_account->account_group             = $account_group;
                $mapping_account->save();
            }


            return redirect()->route('mappingaccountdata.index')->with('success', __('Mapping Account Data successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($ids)
    {
        if(\Auth::user()->can('edit mapping account data'))
        {
            $id              = Crypt::decrypt($ids);
            $mapping_account = MappingAccountData::find($id);

            $materialitas       = Materialitas::limit(10)->pluck('name', 'id');
            $materialitas->prepend('Select Group', '');


            return view('mappingaccountdata.edit', compact('mapping_account', 'materialitas'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $id)
    {
        if(\Auth::user()->can('edit mapping account data'))
        {
            $mapping_account = MappingAccountData::find($id);
            $mapping_account->account_group            = $request->account_group;
            $mapping_account->name                     = $request->name;
            $mapping_account->code                     = $request->code;
            $mapping_account->update();

            return redirect()->route('mappingaccountdata.index')->with('success', __('Mapping Account Data successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Request $request, $id)
    {

        if(\Auth::user()->can('delete mapping account data'))
        {
                $mapping_account = MappingAccountData::find($id);
                $mapping_account->delete();

                return redirect()->route('mappingaccountdata.index')->with('success', __('Mapping Account Data successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
