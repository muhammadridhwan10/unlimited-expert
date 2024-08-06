{{Form::open(array('url'=>'el','method'=>'post','enctype' => 'multipart/form-data'))}}
<div class="modal-body">
    <div class="form-group">
        {{ Form::label('is_existing', 'The EL Data Already Exist?') }}<span class="text-danger">*</span>
        {{ Form::select('is_existing', ['' => 'Select...', 'true' => 'Already Exists', 'false' => 'Not Yet'], null, ['class' => 'form-control', 'id' => 'is_existing']) }}
    </div>

    <div class="form-group existing-el" style="display: none;">
        {{ Form::label('existing_el', 'Select EL') }}<span class="text-danger">*</span>
        {{ Form::select('existing_el', $el, null, ['class' => 'form-control']) }}
    </div>

    <div class="form-group new-el" style="display: none;">
        {{ Form::label('el_number', 'EL Number') }}<span class="text-danger">*</span>
        {{ Form::text('el_number', null, ['class' => 'form-control']) }}
    </div>

    <div class="form-group new-el" style="display: none;">
        {{ Form::label('file', 'File') }}<span class="text-danger">*</span>
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

    <div class="form-group">
        {{ Form::hidden('project_id', $project_id) }}
        {{ Form::hidden('client_id', $client_id) }}
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}

<script>
    document.getElementById('is_existing').addEventListener('change', function() {
        let value = this.value;
        if (value == 'true') {
            document.querySelectorAll('.existing-el').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.new-el').forEach(el => el.style.display = 'none');
        } else {
            document.querySelectorAll('.existing-el').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.new-el').forEach(el => el.style.display = 'block');
        }
    });
</script>


