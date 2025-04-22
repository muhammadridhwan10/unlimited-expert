{{ Form::open(['route' => ['projects.tasks.store',$project_id],'id' => 'create_task']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('name', __('Task name'),['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('name', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('milestone_id', __('Task Group / Milestone'),['class' => 'form-label']) }}
                <a style="font-size:10px" href="#" data-size="md" data-url="{{ route('project.milestone', $project->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="" data-bs-original-title="{{__('Create Task Group / Milestone')}}">{{__('Create Task Group / Milestone')}}</a>
                <select class="form-control select" name="milestone_id" id="milestone_id">
                    <option value="0" class="text-muted">{{__('Select Task Group / Milestone')}}</option>
                    @foreach($project->milestones as $m_val)
                        <option value="{{ $m_val->id }}">{{ $m_val->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'),['class' => 'form-label']) }}
                <small class="form-text text-muted mb-2 mt-0">{{__('This textarea will autosize while you type')}}</small>
                {{ Form::textarea('description', null, ['class' => 'form-control','rows'=>'1','data-toggle' => 'autosize']) }}
            </div>
        </div>
        {{-- <div class="col-6">
            <div class="form-group">
                {{ Form::label('estimated_hrs', __('Estimated Hours'),['class' => 'form-label']) }}<span class="text-danger">*</span>
                <small class="form-text text-muted mb-2 mt-0">{{__('allocated total ').$hrs['allocated'].__(' hrs in other tasks')}}</small>
                {{ Form::number('estimated_hrs', null, ['class' => 'form-control','required' => 'required','min'=>'0','maxlength' => '8']) }}
            </div>
        </div> --}}
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('priority', __('Priority'),['class' => 'form-label']) }}<span class="text-danger">*</span>
                <small class="form-text text-muted mb-2 mt-0">{{__('Set Priority of your task')}}</small>
                <select class="form-control select" name="priority" id="priority" required>
                    <option value="low">{{'Select Priority'}}</option>
                    @foreach(\App\Models\ProjectTask::$priority as $key => $val)
                        <option value="{{ $key }}">{{ __($val) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'),['class' => 'form-label']) }}
                {{ Form::date('start_date', null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'),['class' => 'form-label']) }}
                {{ Form::date('end_date', null, ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">{{__('Subtask')}}</label>
        <small class="form-text text-muted mb-2 mt-0">{{__('This is only optional')}}</small>
        <div class="d-flex justify-content-end mb-3">
            <button type="button" id="add-subtask" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> {{ __('Add Subtask') }}
            </button>
        </div>
        <div id="subtasks-container">
            <div class="row subtask-field align-items-center mb-2">
                <div class="col-4">
                    <div class="form-group">
                        {{ Form::text('subtasks[][name]', null, ['class' => 'form-control', 'placeholder' => __('Subtask Name')]) }}
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        {{ Form::text('subtasks[][description]', null, ['class' => 'form-control', 'placeholder' => __('Description')]) }}
                    </div>
                </div>
                <div class="col-1 d-flex justify-content-end">
                    <button type="button" class="btn btn-danger btn-sm remove-subtask" style="margin-top:-20px">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">{{__('Task members')}}</label>
        <small class="form-text text-muted mb-2 mt-0">{{__('Below users are assigned in your project.')}}</small>
    </div>
    <div class="list-group list-group-flush mb-4">
        <div class="row">
            @foreach($project->users as $user)
                <div class="col-6">
                    <div class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <a href="#" class="avatar avatar-sm rounded-circle">
                                    <img class="wid-40 rounded-circle ml-3" data-original-title="{{(!empty($user)?$user->name:'')}}" @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif/>
                                </a>
                            </div>
                            <div class="col">
                                <p class="d-block h6 text-sm mb-0">{{ $user->name }}</p>
                                <p class="card-text text-sm text-muted mb-0">{{ $user->email }}</p>
                            </div>
                            <div class="col-auto text-end add_usr" data-id="{{ $user->id }}">
                                <button type="button" class="btn btn-xs btn-animated btn-blue rounded-pill btn-animated-y mr-3">
                                    <span class="btn-inner--visible">
                                      <i class="ti ti-plus" id="usr_icon_{{$user->id}}"></i>
                                    </span>
                                    <span class="btn-inner--hidden text-white" id="usr_txt_{{$user->id}}">{{__('Add')}}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {{ Form::hidden('assign_to', null) }}
    </div>
    @if(isset($settings['google_calendar_enable']) && $settings['google_calendar_enable'] == 'on')
        <div class="form-group col-md-6">
            {{Form::label('synchronize_type',__('Synchronize in Google Calendar ?'),array('class'=>'form-label')) }}
            <div class="form-switch">
                <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow" value="google_calender">
                <label class="form-check-label" for="switch-shadow"></label>
            </div>
        </div>
    @endif
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{Form::close()}}
<script>
    $(document).ready(function(){
        // Function to add a new subtask input
        $('#add-subtask').click(function(){
            let newField = `
            <div class="row subtask-field align-items-center mb-2">
                <div class="col-4">
                    <div class="form-group">
                        <input type="text" name="subtasks[][name]" class="form-control" placeholder="{{ __('Subtask Name') }}">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <input type="text" name="subtasks[][description]" class="form-control" placeholder="{{ __('Description') }}">
                    </div>
                </div>
                <div class="col-1 d-flex justify-content-end">
                    <button type="button" class="btn btn-danger btn-sm remove-subtask" style="margin-top:-20px">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>`;
            $('#subtasks-container').append(newField);
        });

        // Function to remove a subtask input
        $(document).on('click', '.remove-subtask', function(){
            $(this).closest('.subtask-field').remove();
        });
    });
</script>

