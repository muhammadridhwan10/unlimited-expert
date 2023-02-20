{{ Form::open(['route' => ['projects.tasks.store',$project_id],'id' => 'create_task']) }}
<div class="modal-body">
    <div class="form-group">
        <label class="form-label">{{__('Task')}}</label>
        <small class="form-text text-muted mb-2 mt-0">{{__('Below users are assigned in your project.')}}</small>
    </div>
    <div class="list-group list-group-flush mb-4">
        <div class="row">
            @foreach ($user as $users)
                <?php
                    $userss = App\Models\User::where('id', $users->user_id)->pluck('name','id')->first();
                    dd($userss);
                ?>
                <div class="form-group col-md-6">
                    {{ Form::label('users', __('User'),['class'=>'form-label'])}}
                    {{ Form::select('users',null,['class' => 'form-control date', 'placeholder' => __('Select User'), 'required' => 'required', 'multiple' => 'true']) }}
                </div>
            @endforeach
            
            <!-- <div class="form-group col-md-6">
                {{ Form::label('datetime', __('Start Date / Time'),['class'=>'form-label'])}}
                {{ Form::date('start_date',null,['class' => 'form-control date', 'placeholder' => __('Select Date/Time'), 'required' => 'required']) }}
            </div> -->
        </div>
        {{ Form::hidden('assign_to', null) }}
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}

