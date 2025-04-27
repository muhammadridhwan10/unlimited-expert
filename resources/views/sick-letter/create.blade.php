{{Form::open(array('url'=>'leave','method'=>'post','enctype' => 'multipart/form-data'))}}
    <div class="modal-body">
    <div class="row" id="sickFields">
        @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('employee_id',__('Employee') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                        {{Form::select('employee_id',$employees,null,array('class'=>'form-control select2','id'=>'employee_id','placeholder'=>__('Select Employee')))}}
                    </div>
                </div>
            </div>
        @endif
        <div class="form-group col-sm-12 col-md-12">
                {{Form::label('reimbursment_image',__('Image'),['class'=>'form-label'])}}
                <div class="choose-file form-group">
                    <label for="sick_letter" class="form-label">
                        <input type="file" accept=".png, .jpg, .jpeg" class="form-control" name="sick_letter" id="sick_letter" data-filename="sick_letter_create">
                        <img id="image" class="mt-3" style="width:25%;"/>

                    </label>
                </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('date_sick_letter', __('Date Sick Letter'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{Form::date('date_sick_letter',null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('total_sick_days', __('Total Sick Days'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::number('total_sick_days', null, ['class' => 'form-control', 'placeholder' => __('Total Sick Days')]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}