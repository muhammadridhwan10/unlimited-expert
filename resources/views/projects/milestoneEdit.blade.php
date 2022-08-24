
{{ Form::model($milestone, array('route' => array('project.milestone.update', $milestone->id), 'method' => 'POST')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('title', __('Title'),['class' => 'form-label']) }}
            {{ Form::text('title', null, array('class' => 'form-control','required'=>'required')) }}
            @error('title')
            <span class="invalid-title" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </span>
            @enderror
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('status', __('Status'),['class' => 'form-label']) }}
            {!! Form::select('status',\App\Models\Project::$project_status, null,array('class' => 'form-control selectric select','required'=>'required')) !!}
            @error('client')
            <span class="invalid-client" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </span>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="form-group  col-md-12">
            {{ Form::label('description', __('Description'),['class' => 'form-label']) }}
            {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'2']) !!}
            @error('description')
            <span class="invalid-description" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </span>
            @enderror
        </div>

    </div>
</div>
<div class="modal-footer">
    <input class="btn  btn-primary" type="submit" value="Update">
</div>
{{ Form::close() }}

