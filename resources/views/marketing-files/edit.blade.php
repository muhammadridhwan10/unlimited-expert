{{ Form::model($marketing_file, ['route' => ['marketing-files.update', $marketing_file->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
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


    <div class="col-12 text-end">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>
</div>
{{ Form::close() }}
