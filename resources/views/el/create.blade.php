{{Form::open(array('url'=>'el','method'=>'post','enctype' => 'multipart/form-data'))}}
<div class="modal-body">
    <div class="form-group">
        {{ Form::label('is_existing', 'The EL Data Already Exist?') }}
        {{ Form::select('is_existing', ['' => 'Select...', 'true' => 'Already Exists', 'false' => 'Not Yet'], null, ['class' => 'form-control', 'id' => 'is_existing']) }}
    </div>

    <div class="form-group existing-el" style="display: none;">
        {{ Form::label('existing_el', 'Select EL') }}
        {{ Form::select('existing_el', $el, null, ['class' => 'form-control']) }}
    </div>

    <div class="form-group new-el" style="display: none;">
        {{ Form::label('el_number', 'EL Number') }}
        {{ Form::text('el_number', null, ['class' => 'form-control']) }}
    </div>

    <div class="form-group new-el" style="display: none;">
        {{ Form::label('file', 'File') }}
        <input type="file" accept=".pdf" class="form-control" name="file" id="file" data-filename="file_create">
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


