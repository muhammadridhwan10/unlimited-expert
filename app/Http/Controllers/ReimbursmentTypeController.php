<?php

namespace App\Http\Controllers;
use App\Models\ReimbursmentType;

use Illuminate\Http\Request;

class ReimbursmentTypeController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage leave type'))
        {
            if(\Auth::user()->type == 'company')
            {
                $reimbursmenttypes = ReimbursmentType::all();

                return view('reimbursmenttype.index', compact('reimbursmenttypes'));
            }
            if(\Auth::user()->type == 'admin')
            {
                $reimbursmenttypes = ReimbursmentType::all();

                return view('reimbursmenttype.index', compact('reimbursmenttypes'));
            }
            else
            {
                $reimbursmenttypes = ReimbursmentType::where('created_by', '=', \Auth::user()->creatorId())->get();

                return view('reimbursmenttype.index', compact('reimbursmenttypes'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {

        if(\Auth::user()->can('create leave type'))
        {
            return view('reimbursmenttype.create');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        if(\Auth::user()->can('create leave type'))
        {

            $validator = \Validator::make(
                $request->all(), [
                'title' => 'required',
                'amount' => 'required',
            ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $reimbursmenttype             = new ReimbursmentType();
            $reimbursmenttype->title      = $request->title;
            $reimbursmenttype->amount     = $request->amount;
            $reimbursmenttype->created_by = \Auth::user()->creatorId();
            $reimbursmenttype->save();

            return redirect()->route('reimbursmenttype.index')->with('success', __('ReimbursmentType  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(ReimbursmentType $reimbursmenttype)
    {
        return redirect()->route('reimbursmenttype.index');
    }

    public function edit(ReimbursmentType $reimbursmenttype)
    {
        if(\Auth::user()->can('edit leave type'))
        {
            if($reimbursmenttype->created_by == \Auth::user()->creatorId())
            {

                return view('reimbursmenttype.edit', compact('reimbursmenttype'));
            }
            elseif(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                return view('reimbursmenttype.edit', compact('reimbursmenttype'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, ReimbursmentType $reimbursmenttype)
    {
        if(\Auth::user()->can('edit leave type'))
        {
            if($reimbursmenttype->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                    'title' => 'required',
                    'amount' => 'required',
                ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $reimbursmenttype->title = $request->title;
                $reimbursmenttype->amount = $request->amount;
                $reimbursmenttype->save();

                return redirect()->route('reimbursmenttype.index')->with('success', __('ReimbursmentType successfully updated.'));
            }
            elseif(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $validator = \Validator::make(
                    $request->all(), [
                    'title' => 'required',
                    'amount' => 'required',
                ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $reimbursmenttype->title = $request->title;
                $reimbursmenttype->amount  = $request->amount;
                $reimbursmenttype->save();

                return redirect()->route('reimbursmenttype.index')->with('success', __('ReimbursmentType successfully updated.'));
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

    public function destroy(ReimbursmentType $reimbursmenttype)
    {
        if(\Auth::user()->can('delete leave type'))
        {
            if($reimbursmenttype->created_by == \Auth::user()->creatorId())
            {
                $reimbursmenttype->delete();

                return redirect()->route('reimbursmenttype.index')->with('success', __('ReimbursmentType successfully deleted.'));
            }
            elseif(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            {
                $reimbursmenttype->delete();

                return redirect()->route('reimbursmenttype.index')->with('success', __('ReimbursmentType successfully deleted.'));
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
