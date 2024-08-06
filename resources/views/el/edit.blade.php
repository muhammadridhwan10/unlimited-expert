{{ Form::model($el, ['route' => ['el.update', $el->id], 'method' => 'put', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="form-group new-el">
        {{ Form::label('el_number', 'EL Number') }}
        {{ Form::text('el_number', null, ['class' => 'form-control']) }}
    </div>

    <div class="form-group new-el">
        {{ Form::label('file', 'File') }}
        <input type="file" accept=".pdf" class="form-control" name="file" id="file" data-filename="file_create">
    </div>

    <div class="form-group">
        {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
        <select name="status" id="status" class="form-control main-element" required>
            <option value="0">{{__('Select Status EL')}}</option>
            @foreach(\App\Models\El::$status as $k => $v)
                <option value="{{$k}}">{{__($v)}}</option>
            @endforeach
        </select>
    </div>

    {{ Form::hidden('project_id', $el->project_id) }}
    {{ Form::hidden('client_id', $el->client_id) }}
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{ Form::close() }}
