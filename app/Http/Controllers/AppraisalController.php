<?php

namespace App\Http\Controllers;

use App\Models\AppraisalEmployee;
use App\Models\Project;
use App\Models\User;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\PerformanceType;
use Illuminate\Http\Request;

class AppraisalController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('manage appraisal'))
        {
            $user = \Auth::user();
            if($user->type == 'admin' || $user->type == 'company')
            {
                $appraisals = AppraisalEmployee::all();
            }
            else
            {
                $employee   = Employee::where('user_id', $user->id)->first();
                $appraisals = AppraisalEmployee::where('created_by', '=', \Auth::user()->creatorId())->get();
            }

            return view('appraisal.index', compact('appraisals'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create($uid, $pid)
    {
        if(\Auth::user()->can('create appraisal'))
        {
//            $technicals      = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'technical')->get();
//            $organizationals = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'organizational')->get();
//            $behaviourals = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'behavioural')->get();
            
            $user = \Auth::user();
            if($user->type == 'admin')
            {
                $project                       = Project::find($pid);
                $user                          = User::find($uid);
                $performance    = PerformanceType::get();
            }
            else
            {
                $project                       = Project::find($pid);
                $user                          = User::find($uid);
                $performance     = PerformanceType::where('created_by', '=', \Auth::user()->creatorId())->get();
            }
            return view('appraisal.create', compact('user','project','performance'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request, $uid, $pid)
    {

        if(\Auth::user()->can('create appraisal'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'date' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::where('user_id', $uid)->first();

            $appraisal                    = new AppraisalEmployee();
            $appraisal->employee_id       = $employee->id;
            $appraisal->project_id        = $pid;
            $appraisal->date              = $request->date;
            $appraisal->rating            = json_encode($request->rating, true);
            $appraisal->remark            = $request->remark;
            $appraisal->created_by        = \Auth::user()->id;
            $appraisal->save();

            return redirect()->route('projects.show', [$pid])->with('success', __('Appraisal successfully created.'));
        }
    }

    public function show(AppraisalEmployee $appraisal)
    {
        $ratings = json_decode($appraisal->rating, true);
        if(\Auth::user()->type = 'admin')
        {
            $performance     = PerformanceType::all();
        }
        elseif(\Auth::user()->type = 'company')
        {
            $performance     = PerformanceType::all();
        }
        else
        {
            $performance     = PerformanceType::where('created_by', '=', \Auth::user()->creatorId())->get();
        }
//        $technicals      = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'technical')->get();
//        $organizationals = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'organizational')->get();
//        $behaviourals = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'behavioural')->get();

        return view('appraisal.show', compact('appraisal', 'performance', 'ratings'));
    }



    public function edit(AppraisalEmployee $appraisal)
    {
        if(\Auth::user()->can('edit appraisal'))
        {
            if(\Auth::user()->type = 'admin')
            {
                // $technicals      = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'technical')->get();
                // $organizationals = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'organizational')->get();
                // $behaviourals = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'behavioural')->get();
                $performance     = PerformanceType::all();
                $brances = Branch::get()->pluck('name', 'id');
                $brances->prepend('Select Branch', '');
                $ratings = json_decode($appraisal->rating,true);
            }
            elseif(\Auth::user()->type = 'company')
            {
                // $technicals      = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'technical')->get();
                // $organizationals = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'organizational')->get();
                // $behaviourals = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'behavioural')->get();
                $performance     = PerformanceType::all();
                $brances = Branch::get()->pluck('name', 'id');
                $brances->prepend('Select Branch', '');
                $ratings = json_decode($appraisal->rating,true);
            }
            else
            {
                // $technicals      = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'technical')->get();
                // $organizationals = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'organizational')->get();
                // $behaviourals = Competencies::where('created_by', \Auth::user()->creatorId())->where('type', 'behavioural')->get();
                $performance     = PerformanceType::where('created_by', '=', \Auth::user()->creatorId())->get();
                $brances = Branch::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $brances->prepend('Select Branch', '');
                $ratings = json_decode($appraisal->rating,true);
            }

            return view('appraisal.edit', compact( 'brances', 'appraisal', 'performance','ratings'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, AppraisalEmployee $appraisal)
    {
        if(\Auth::user()->can('edit appraisal'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'date' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $appraisal->date = $request->date;
            $appraisal->rating         = json_encode($request->rating, true);
            $appraisal->remark         = $request->remark;
            $appraisal->save();

            return redirect()->route('appraisal.index')->with('success', __('Appraisal successfully updated.'));
        }
    }
    public function destroy(AppraisalEmployee $appraisal)
    {
        if(\Auth::user()->can('delete appraisal'))
        {
            if($appraisal->created_by == \Auth::user()->creatorId())
            {
                $appraisal->delete();

                return redirect()->route('appraisal.index')->with('success', __('Appraisal successfully deleted.'));
            }
            elseif(\Auth::user()->type = 'admin')
            {
                $appraisal->delete();

                return redirect()->route('appraisal.index')->with('success', __('Appraisal successfully deleted.'));
            }
            elseif(\Auth::user()->type = 'company')
            {
                $appraisal->delete();

                return redirect()->route('appraisal.index')->with('success', __('Appraisal successfully deleted.'));
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
