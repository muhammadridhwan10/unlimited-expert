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
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::date('start_date', null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::date('end_date', null, ['class' => 'form-control']) }}
            </div>
        </div>
        {{-- <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('total_days', __('Total Days Working'), ['class' => 'form-label']) }}
                {{ Form::text('total_days', null, ['class' => 'form-control']) }}
            </div>
        </div> --}}
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
                {{ Form::label('public_accountant_id', __('Partner'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {!! Form::select('public_accountant_id', $public_accountant, null,array('class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('client', __('Client'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {!! Form::select('client', $clients, null,array('class' => 'form-control select2','id'=>'choices-multiple1','required'=>'required')) !!}
            </div>
        </div>
        {{-- <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('budget', __('Budget'), ['class' => 'form-label']) }}
                {{ Form::number('budget', null, ['class' => 'form-control']) }}
            </div>
        </div> --}}
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('user', __('Team Leader'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {!! Form::select('user[]', $users, null,array('class' => 'form-control select2','id'=>'choices-multiple3','required'=>'required')) !!}
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
                {{ Form::label('label', __('Type Of Service'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                <select name="label" id="label" class="form-control main-element" required>
                    @foreach(\App\Models\Project::$label as $k => $v)
                        <option value="{{$k}}">{{__($v)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('tag', __('Office'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                <select name="tag" id="tag" class="form-control main-element">
                    @foreach(\App\Models\Project::$tags as $k => $v)
                        <option value="{{$k}}">{{__($v)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('book_year', __('Book Year'),['class'=>'form-label']) }}
                {{ Form::text('book_year', null, array('class' => 'form-control','placeholder'=>__('Enter Book Year'))) }}
            </div>
        </div> --}}
        <div class="col-sm-6 col-md-6">
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
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '4', 'cols' => '50']) }}
            </div>
        </div>
    </div>
    {{-- <div class="row">
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
    </div> --}}
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}
