{{Form::model($reimbursment, ['route' => ['reimbursment-client.update', $reimbursment->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data'])}}
    <div class="modal-body">
        @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'client' || \Auth::user()->type == 'staff_client')
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('employee_id', __('Employee'), ['class'=>'form-label'])}}<span class="text-danger">*</span>
                        {{Form::select('employee_id', $employees, null, ['class'=>'form-control select2', 'id'=>'employee_id', 'placeholder'=>__('Select Employee')])}}
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('client_id', __('Client'), ['class'=>'form-label'])}}<span class="text-danger">*</span>
                    {{Form::select('client_id', $client, null, ['class'=>'form-control select2', 'required'=>'required', 'id'=>'client', 'placeholder'=>__('Select Client')])}}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('approval', __('Approval By'), ['class'=>'form-label'])}}<span class="text-danger">*</span>
                    {{Form::select('approval', $approval, null, ['class'=>'form-control select2', 'required'=>'required', 'id'=>'approval', 'placeholder'=>__('Select Approval')])}}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('date', __('Date'), ['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::date('date', null, ['class'=>'form-control', 'required'=>'required'])}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('amount', __('Amount'), ['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('amount', null, ['class'=>'form-control'])}}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-sm-12 col-md-12">
                <label for="file_type" class="form-label">{{ __('Select File Type') }}</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="file_type" id="image_radio" value="image" checked>
                    <label class="form-check-label" for="image_radio">
                        {{ __('Image') }}
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="file_type" id="pdf_radio" value="pdf">
                    <label class="form-check-label" for="pdf_radio">
                        {{ __('PDF') }}
                    </label>
                </div>
            </div>
        </div>
        <div id="image_upload" class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('reimbursment_image', __('Image'), ['class'=>'form-label'])}}<span class="text-danger">*</span>
                    <div class="choose-file form-group">
                        <label for="reimbursment_image" class="form-label">
                            <input type="file" accept=".png, .jpg, .jpeg" class="form-control" name="reimbursment_image" id="reimbursment_image" data-filename="reimbursment_image_create">
                            <img id="image" class="mt-3" style="width:25%;"/>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div id="pdf_upload" class="row" style="display: none;">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('reimbursment_pdf', __('PDF'), ['class'=>'form-label'])}}<span class="text-danger">*</span>
                    <div class="choose-file form-group">
                        <label for="reimbursment_pdf" class="form-label">
                            <input type="file" accept=".pdf" class="form-control" name="reimbursment_pdf" id="reimbursment_pdf" data-filename="reimbursment_pdf_create">
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('description', __('Description'), ['class'=>'form-label'])}}
                    {{Form::textarea('description', null, ['class'=>'form-control', 'placeholder'=>__('Description')])}}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>
{{Form::close()}}
<script>
    document.getElementById('image_radio').addEventListener('change', function() {
        document.getElementById('image_upload').style.display = 'block';
        document.getElementById('pdf_upload').style.display = 'none';
    });

    document.getElementById('pdf_radio').addEventListener('change', function() {
        document.getElementById('pdf_upload').style.display = 'block';
        document.getElementById('image_upload').style.display = 'none';
    });

    document.getElementById('reimbursment_image').onchange = function () {
        var src = URL.createObjectURL(this.files[0]);
        document.getElementById('image').src = src;
    };
</script>
