{{ Form::model($subtask, ['route' => ['subtask.update',[$project->id, $subtask->id]], 'id' => 'edit_subtask', 'method' => 'POST']) }}
    <div class="modal-body">
        <div class="px-3 py-2 row align-items-center">
            <div class="col-10 mb-3">
                {{ Form::label('name', __('Sub Task name'),['class' => 'form-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control']) }}
            </div>
            <div class="col-10 mb-3">
                {{ Form::label('description', __('Description Sub Task'),['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control']) }}
            </div>
            <div class="col-10 mb-3">
                {{ Form::label('link', __('Link Sub Task'),['class' => 'form-label']) }}
                {{ Form::text('link', null, ['class' => 'form-control', 'type' => 'url']) }}
            </div>
            <div class="col-10 mb-3">
                <select id="parent_id" name="parent_id" class="form-control">
                    <option value="">Parent</option>
                    <option value="0">Parent</option>
                    @foreach ($task->checklist as $childtask)
                    <option value="{{ $childtask->id }}">{{ $childtask->name }}</option>
                    @endforeach
                    <!-- <option value="{{ $subtask->id }}">{{ $subtask->name }}</option> -->
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
    </div>
    {{Form::close()}}


