{{Form::open(array('url'=>'absence-request','method'=>'post','enctype' => 'multipart/form-data'))}}
    <div class="modal-body">

    @if(\Auth::user()->type !='admin' || \Auth::user()->type !='company')
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('employee_id',__('Employee') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                    {{Form::select('employee_id',$employees,null,array('class'=>'form-control select2','id'=>'employee_id','placeholder'=>__('Select Employee')))}}
                </div>
            </div>
        </div>
    @endif
    <div class="row" id="typeFields">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('type',__('Select Type') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                {{Form::select('type', ['sick' => 'Sick', 'leave' => 'Leave'], null, ['class' => 'form-control', 'id' => 'type', 'placeholder' => 'Select Type'])}}
            </div>
        </div>
    </div>
    <div class="row" id="leaveFields" style="display: none;">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('approval',__('Approval By') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                    {{Form::select('approval',$approval,null,array('class'=>'form-control select2','id'=>'approval','placeholder'=>__('Select Approval')))}}
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('leave_type_id',__('Leave Type') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                <select name="leave_type_id" id="leave_type_id" class="form-control select">
                    @foreach($leavetypes as $leave)
                        <option value="{{ $leave->id }}">{{ $leave->title }} (<p class="float-right pr-5">{{ $leave->days }}</p>)</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{Form::date('start_date',null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{Form::date('end_date',null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('leave_reason',__('Leave Reason') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                {{Form::textarea('leave_reason',null,array('class'=>'form-control', 'placeholder'=>__('Leave Reason')))}}
            </div>
        </div>
    </div>


    <div class="row" id="sickFields" style="display: none;">
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
<script>
    $(document).ready(function(){
        $('#type').on('change', function() {
            var selectedApproval = $(this).val();
            if(selectedApproval === 'leave') {
                $('#sickFields').hide();
                $('#leaveFields').show();
            } else if(selectedApproval === 'sick') {
                $('#leaveFields').hide();
                $('#sickFields').show();
            } else {
                $('#leaveFields').hide();
                $('#sickFields').hide();
            }
        });
    });
</script>
