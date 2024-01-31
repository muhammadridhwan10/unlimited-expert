{{ Form::model($timesheet, ['route' => ['timesheet.update',$timesheet->id], 'id' => 'edit_timesheet', 'method' => 'PUT']) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    {{ Form::label('project_id', __('Project'),['class' => 'form-label']) }}<span class="text-danger">*</span>
                    <select name="project_id" id="project_id" class="form-control select2 main-element">
                        @foreach($projects as $project)
                            <option value="{{$project->id}}">{{__($project->project_name)}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    {{ Form::label('description', __('Description'),['class' => 'form-label']) }}
                    {{ Form::date('date', null, array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    {{ Form::label('time_hour', __('Time Hour'),['class' => 'form-label']) }}<span class="text-danger">*</span>
                    <select class="form-control" name="time_hour" id="time_hour" required="">
                        <option value="">{{ __('Hours') }}</option>

                        <?php for ($i = 0; $i < 23; $i++) { $i = $i < 10 ? '0' . $i : $i; ?>

                        <option value="{{ $i }}">{{ $i }}</option>

                        <?php } ?>

                    </select>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    {{ Form::label('time_hour', __('Time Hour'),['class' => 'form-label']) }}<span class="text-danger">*</span>
                    <select class="form-control form-control-light" name="time_minute" id="time_minute" required>
                        <option value="">{{ __('Minutes')}}</option>

                        <?php for ($i = 0; $i < 61; $i += 10) { $i = $i < 10 ? '0' . $i : $i; ?>

                        <option value="{{ $i }}">{{ $i }}</option>

                        <?php } ?>

                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" onclick="location.href = '{{route("timesheet.list")}}';" class="btn btn-light">
        <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
    </div>
{{Form::close()}}


