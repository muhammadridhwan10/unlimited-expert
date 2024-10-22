{{ Form::open(['url' => 'marketing-files', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('name',__('File Name') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Input File Name')))}}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-sm-12 col-md-12">
            {{Form::label('file',__('File'),['class'=>'form-label'])}}<span class="text-danger">*</span>
            <div class="choose-file form-group">
                <label for="file" class="form-label">
                    <input type="file" accept=".png, .jpg, .jpeg, .docx, .doc, .xlsx, .xls, .pdf" class="form-control" name="file" id="file" data-filename="filecreate">
                </label>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}