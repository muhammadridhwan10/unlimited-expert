<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\TrainingType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class TrainingController extends Controller
{

    public function index()
    {
        $user = \Auth::user();
        if(\Auth::user()->can('manage training'))
        {
            if($user->type = 'admin'){
                $trainings = Training::all();
                $status    = Training::$Status;
    
                return view('training.index', compact('trainings', 'status'));
            }
            elseif($user->type = 'company')
            {
                $trainings = Training::all();
                $status    = Training::$Status;
    
                return view('training.index', compact('trainings', 'status'));
            }
            else{
                $trainings = Training::where('created_by', '=', \Auth::user()->creatorId())->get();
                $status    = Training::$Status;
    
                return view('training.index', compact('trainings', 'status'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if(\Auth::user()->can('create training'))
        {
            if(\Auth::user()->type = 'admin')
            {
                $branches      = Branch::all()->pluck('name', 'id');
                $trainingTypes = TrainingType::all()->pluck('name', 'id');
                $options       = Training::$options;
                $trainingTypes->prepend('Select Type', '');
    
                return view('training.create', compact('branches', 'trainingTypes', 'options'));
            }
            elseif(\Auth::user()->type = 'company')
            {
                $branches      = Branch::all()->pluck('name', 'id');
                $trainingTypes = TrainingType::all()->pluck('name', 'id');
                $options       = Training::$options;
                $trainingTypes->prepend('Select Type', '');
    
                return view('training.create', compact('branches', 'trainingTypes', 'options'));
            }
            else
            {
                $branches      = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $trainingTypes = TrainingType::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $options       = Training::$options;
                $trainingTypes->prepend('Select Type', '');
    
                return view('training.create', compact('branches', 'trainingTypes', 'options'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        if(\Auth::user()->can('create training'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'branch' => 'required',
                                'training_type' => 'required',
                                'training_title' => 'required',
                                'year' => 'required|integer',
                            ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $training                 = new Training();
            $training->branch         = $request->branch;
            $training->trainer_option = $request->trainer_option;
            $training->training_type  = $request->training_type;
            $training->training_title = $request->training_title;
            $training->year           = $request->year;
            $training->location       = $request->location;
            $training->employee       = auth()->user()->employee->id;
            $training->description    = $request->description;
            $training->created_by     = \Auth::user()->creatorId();
            $training->save();

            return redirect()->route('form-response.create')->with('success', __('Training successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($id)
    {
        $traId       = Crypt::decrypt($id);
        $training    = Training::find($traId);
        $performance = Training::$performance;
        $status      = Training::$Status;

        return view('training.show', compact('training', 'performance', 'status'));
    }


    public function edit(Training $training)
    {
        if(\Auth::user()->can('create training'))
        {
            if(\Auth::user()->type = 'admin')
            {
                $branches      = Branch::get()->pluck('name', 'id');
                $trainingTypes = TrainingType::get()->pluck('name', 'id');
                $options       = Training::$options;
                $trainingTypes->prepend('Select Type', '');
            }
            elseif(\Auth::user()->type = 'company')
            {
                $branches      = Branch::get()->pluck('name', 'id');
                $trainingTypes = TrainingType::get()->pluck('name', 'id');
                $options       = Training::$options;
                $trainingTypes->prepend('Select Type', '');
            }
            else
            {
                $branches      = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $trainingTypes = TrainingType::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $options       = Training::$options;
                $trainingTypes->prepend('Select Type', '');
            }

            return view('training.edit', compact('branches', 'trainingTypes', 'options', 'training'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, Training $training)
    {
        if(\Auth::user()->can('edit training'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'branch' => 'required',
                                'training_type' => 'required',
                                'training_title' => 'required',
                                'year' => 'required|integer',
                            ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $training->branch         = $request->branch;
            $training->trainer_option = $request->trainer_option;
            $training->training_type  = $request->training_type;
            $training->training_title = $request->training_title;
            $training->year           = $request->year;
            $training->location       = $request->location;
            $training->employee       = auth()->user()->employee->id;
            $training->description    = $request->description;
            $training->save();

            return redirect()->route('training.index')->with('success', __('Training successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Training $training)
    {
        if(\Auth::user()->can('delete training'))
        {
            if($training->created_by == \Auth::user()->creatorId())
            {
                $training->delete();

                return redirect()->route('training.index')->with('success', __('Training successfully deleted.'));
            }
            elseif(\Auth::user()->type = 'admin')
            {
                $training->delete();

                return redirect()->route('training.index')->with('success', __('Training successfully deleted.'));
            }
            elseif(\Auth::user()->type = 'company')
            {
                $training->delete();

                return redirect()->route('training.index')->with('success', __('Training successfully deleted.'));
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

    public function updateStatus(Request $request)
    {
        $training              = Training::find($request->id);
        $training->performance = $request->performance;
        $training->status      = $request->status;
        $training->remarks     = $request->remarks;
        $training->save();

        return redirect()->route('training.index')->with('success', __('Training status successfully updated.'));
    }

    public function employeeTraining(Request $request, $id)
    {
        $employee = Employee::where('user_id', $id)->first();
        $training  = Training::where('employee', $employee->id)->get();

        return view('training.trainingShow', compact('training'));

    }
}
