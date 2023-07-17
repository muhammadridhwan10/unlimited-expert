{{Form::open(array('url'=>'medical-allowance','method'=>'post','enctype' => 'multipart/form-data'))}}
    <div class="modal-body">

    @if(\Auth::user()->type !='admin' || \Auth::user()->type !='company')
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('employee_id',__('Employee') ,['class'=>'form-label'])}}<span class="text-danger">*</span>+
                    {{Form::select('employee_id',$employees,null,array('class'=>'form-control select2','id'=>'employee_id','placeholder'=>__('Select Employee')))}}
                </div>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('client_id',__('Branch') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                {{Form::select('client_id',$client,null,array('class'=>'form-control select2','required'=>'required','id'=>'client','placeholder'=>__('Select Branch')))}}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('approval',__('Approval By') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                {{Form::select('approval',$approval,null,array('class'=>'form-control select2','required'=>'required','id'=>'approval','placeholder'=>__('Select Approval')))}}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('reimbursment_type',__('Reimbursment Type') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                <select name="reimbursment_type" id="reimbursment_type" class="form-control select">
                    @foreach($reimbursment_type as $reimbursmenttypes)
                        <option value="{{ $reimbursmenttypes->title }}">{{ $reimbursmenttypes->title }} (<p class="float-right pr-5">{{ $reimbursmenttypes->amount }}</p>)</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('date', __('Date'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::date('date', null, array('class'=>'form-control','required'=>'required'))}}
            </div>
        </div>


        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{Form::text('amount',null,array('class'=>'form-control'))}}
                <span id="amount-error" class="text-danger"></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-sm-12 col-md-12">
                {{Form::label('reimbursment_image',__('Image'),['class'=>'form-label'])}}<span class="text-danger">*</span>
                <div class="choose-file form-group">
                    <label for="reimbursment_image" class="form-label">
                        <input type="file" accept=".png, .jpg, .jpeg" class="form-control" name="reimbursment_image" id="reimbursment_image" data-filename="reimbursment_image_create">
                        <img id="image" class="mt-3" style="width:25%;"/>

                    </label>
                </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('description',__('Description') ,['class'=>'form-label'])}}
                {{Form::textarea('description',null,array('class'=>'form-control','placeholder'=>__('Description')))}}
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
    document.getElementById('reimbursment_image').onchange = function () {
        var src = URL.createObjectURL(this.files[0])
        document.getElementById('image').src = src
    }
</script>
