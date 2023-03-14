{{ Form::model($project, ['route' => ['projects.auditplanning.create', $project->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                {{ Form::date('start_date', null, ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="form-group">
                {{ Form::label('task_id', __('Task'),['class'=>'form-label']) }}
                {{ Form::select('task_id[]', $task,null, array('class' => 'form-control select2','id'=>'choices-multiple1','multiple'=>'')) }}
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="form-group">
                {{ Form::label('user_id', __('User'),['class'=>'form-label']) }}
                {{ Form::select('user_id[]', $user,null, array('class' => 'form-control select2','id'=>'choices-multiple2','multiple'=>'')) }}
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}

