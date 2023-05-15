{{ Form::open(['url' => 'projects', 'method' => 'post','enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="form-group">
                {{ Form::label('project_name', __('Project Name'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('project_name', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                {{ Form::date('start_date', null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('total_days', __('Total Days Working'), ['class' => 'form-label']) }}
                {{ Form::text('total_days', null, ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-sm-12 col-md-12">
            {{ Form::label('project_image', __('Project Image'), ['class' => 'form-label']) }}
            <div class="form-file mb-3">
                <input type="file" accept=".png, .jpg, .jpeg" class="form-control" name="project_image">
            </div>

        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('public_accountant_id', __('Public Accountant'),['class'=>'form-label']) }}
                {!! Form::select('public_accountant_id', $public_accountant, null,array('class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('client', __('Client'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {!! Form::select('client', $clients, null,array('class' => 'form-control select2','id'=>'choices-multiple1','required'=>'required')) !!}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('user', __('User'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {!! Form::select('user[]', $users, null,array('class' => 'form-control','required'=>'required')) !!}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('template_task_id', __('Task Template'),['class'=>'form-label']) }}<span class="text-danger"></span>
                {!! Form::select('template_task_id', $tasktemplate, null,array('class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('budget', __('Budget'), ['class' => 'form-label']) }}
                {{ Form::number('budget', null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('label', __('Label'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                <select name="label" id="label" class="form-control main-element" required>
                    @foreach(\App\Models\Project::$label as $k => $v)
                        <option value="{{$k}}">{{__($v)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- <div class="col-6 col-md-6">
            <div class="form-group">
                {{ Form::label('estimated_hrs', __('Estimated Hours'),['class' => 'form-label']) }}
                {{ Form::number('estimated_hrs', null, ['class' => 'form-control','min'=>'0','maxlength' => '8', 'readonly' => 'true']) }}
            </div>
        </div> -->
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '4', 'cols' => '50']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('tag', __('Tag'), ['class' => 'form-label']) }}
                <select name="tag" id="tag" class="form-control main-element">
                    @foreach(\App\Models\Project::$tags as $k => $v)
                        <option value="{{$k}}">{{__($v)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('book_year', __('Book Year'),['class'=>'form-label']) }}
                {{ Form::text('book_year', null, array('class' => 'form-control','placeholder'=>__('Enter Book Year'))) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="form-group">
                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                <select name="status" id="status" class="form-control main-element">
                    @foreach(\App\Models\Project::$project_status as $k => $v)
                        <option value="{{$k}}">{{__($v)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="modal-body">
    <div class="col-sm-12 col-md-12">
            <div class="form-group">
                {{ Form::label('', __('Project Offerings'), ['class' => 'form-label']) }}
            </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('als_partners', __('Allocated Hours Partners'),['class'=>'form-label']) }}
                {{ Form::number('als_partners', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Partners'))) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('rate_partners', __('Rate Partners'),['class'=>'form-label']) }}
                {{ Form::number('rate_partners', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Partners'))) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('als_manager', __('Allocated Hours Manager'),['class'=>'form-label']) }}
                {{ Form::number('als_manager', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Manager'))) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('rate_manager', __('Rate Manager'),['class'=>'form-label']) }}
                {{ Form::number('rate_manager', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Manager'))) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('als_senior_associate', __('Allocated Senior Associate'),['class'=>'form-label']) }}
                {{ Form::number('als_senior_associate', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Senior Associate'))) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('rate_senior_associate', __('Rate Senior Associate'),['class'=>'form-label']) }}
                {{ Form::number('rate_senior_associate', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Senior Associate'))) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('als_associate', __('Allocated Hours Associate'),['class'=>'form-label']) }}
                {{ Form::number('als_associate', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Associate'))) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('rate_associate', __('Rate Associate'),['class'=>'form-label']) }}
                {{ Form::number('rate_associate', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Associate'))) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('als_intern', __('Allocated Hours Intern'),['class'=>'form-label']) }}
                {{ Form::number('als_intern', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Intern'))) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('rate_intern', __('Rate Intern'),['class'=>'form-label']) }}
                {{ Form::number('rate_intern', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Intern'))) }}
            </div>
        </div>
    </div>
    
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}
