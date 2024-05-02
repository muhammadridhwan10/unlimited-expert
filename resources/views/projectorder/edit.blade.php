{{Form::model($projectOrder, ['route' => ['project-orders.update', $projectOrder->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'id' => 'multi-step-form'])}}
<div class="modal-body">
    <div class="progress">
        <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <div id="step1" class="step">
    <h3>Step 1: Client Details</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('name', __('Client Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('name', null, array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
            <div class="col-md-6">  
                <div class="form-group">
                    {{ Form::label('client_business_sector_id', __('Client Business Sector'),['class'=>'form-label']) }}
                    {{ Form::select('client_business_sector_id', $businesssector,null, array('class' => 'form-control select')) }}
                </div>
            </div>
            <div class="col-md-6">  
                <div class="form-group">
                    {{ Form::label('category_services', __('Category Services'),['class'=>'form-label']) }}
                    <select name="category_services" id="category_services" class="form-control main-element" required>
                            <option value="0">{{__('Select Category')}}</option>
                        @foreach(\App\Models\ProjectOrders::$label as $k => $v)
                            <option value="{{$k}}">{{__($v)}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6" id="client_ownership_id" style="display: none;">  
                <div class="form-group">
                    {{ Form::label('client_ownership_id', __('Client Ownership'),['class'=>'form-label']) }}
                    {{ Form::select('client_ownership_id', $ownership,null, array('class' => 'form-control select')) }}
                </div>
            </div>
            <div class="col-md-6" id="accounting_standars_id" style="display: none;">  
                <div class="form-group">
                    {{ Form::label('accounting_standars_id', __('Accounting Standars'),['class'=>'form-label']) }}
                    {{ Form::select('accounting_standars_id', $accountingstandards,null, array('class' => 'form-control select')) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('email', __('Client Email'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('email',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('name_invoice', __('Attention'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('name_invoice',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('position', __('Position'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('position',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('telp', __('Telp'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::number('telp',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('name_pic', __('Name PIC'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('name_pic',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('email_pic', __('Email PIC'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('email_pic',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('telp_pic', __('Telp PIC'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::number('telp_pic',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('total_company_income_per_year', __('Total Company Income Per Year'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::number('total_company_income_per_year',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('total_company_assets_value', __('Total Company Assets Value'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::number('total_company_assets_value',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('total_employee', __('Total Employee'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::number('total_employee',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('total_branch_offices', __('Total Branch Offices'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::number('total_branch_offices',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('npwp', __('Tax Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('npwp',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('address', __('Address'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('address',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('country', __('Country'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('country',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('state', __('State'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('state',null,array('class'=>'form-control'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('city', __('City'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('city',null,array('class'=>'form-control'))}}
                </div>
            </div>
        </div>
    </div>
    <div id="step2" class="step">
     <h3>Step 2: Order Detail</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('project_name', __('Project Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('project_name', null, array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::date('start_date', null, ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::date('end_date', null, ['class' => 'form-control']) }}
                </div>
            </div>
             <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('public_accountant_id', __('Public Accountant'),['class'=>'form-label']) }}
                    {!! Form::select('public_accountant_id', $public_accountant, null,array('class' => 'form-control')) !!}
                </div>
            </div>
            <div class="col-sm-6 col-md-6">
                <div class="form-group">
                    {{ Form::label('leader_project', __('Leader Project'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {!! Form::select('leader_project', $leader, null,array('class' => 'form-control','required'=>'required')) !!}
                </div>
            </div>
            <div class="col-sm-6 col-md-6">
                <div class="form-group">
                    {{ Form::label('template_task_id', __('Task Template'),['class'=>'form-label']) }}<span class="text-danger"></span>
                    {!! Form::select('template_task_id', $tasktemplate, null,array('class' => 'form-control')) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('tags', __('Tag Project'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    <select name="tags" id="tags" class="form-control main-element">
                        @foreach(\App\Models\Project::$tags as $k => $v)
                            <option value="{{$k}}">{{__($v)}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('label', __('Label'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    <select name="label" id="label" class="form-control main-element" required>
                        @foreach(\App\Models\Project::$label as $k => $v)
                            <option value="{{$k}}">{{__($v)}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('status', __('Status'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    <select name="status" id="status" class="form-control main-element">
                        @foreach(\App\Models\Project::$project_status as $k => $v)
                            <option value="{{$k}}">{{__($v)}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
                    {{Form::textarea('description',null,array('class'=>'form-control'))}}
                </div>
            </div>
        </div>
    </div>
    <div id="step3" class="step">
       <h3>Step 3: Time Budget</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('ph_partners', __('Project Hour Partner'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::number('ph_partners', null, array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('rate_partners', __('Rate Partner'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::number('rate_partners', null, array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('ph_manager', __('Project Hour Manager'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::number('ph_manager', null, array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('rate_manager', __('Rate Manager'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::number('rate_manager', null, array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('ph_senior', __('Project Hour Senior Associate'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::number('ph_senior', null, array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('rate_senior', __('Rate Senior Associate'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::number('rate_senior', null, array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('ph_associate', __('Project Hour Associate'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::number('ph_associate', null, array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('rate_associate', __('Rate Associate'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::number('rate_associate', null, array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('ph_assistant', __('Project Hour Assistant'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::number('ph_assistant', null, array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('rate_assistant', __('Rate Assistant'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::number('rate_assistant', null, array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('estimated_hrs', __('Total Project Hours'),['class'=>'form-label']) }}
                    {{Form::text('estimated_hrs',null,array('class'=>'form-control', 'readonly'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('budget', __('Total Charge Out'), ['class' => 'form-label']) }}
                    {{ Form::number('budget', null, ['class' => 'form-control', 'readonly']) }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" onclick="prevStep()">Previous</button>
    <button type="button" id="btnNext" class="btn btn-primary" onclick="nextStep()">Next</button>
    <input type="submit" value="{{__('Update')}}" id="btnSubmit" class="btn  btn-primary" style="display: none;">
</div>
{{Form::close()}}

<script>
var currentStep = 1;
var totalSteps = 3;

function showStep(step) {
    $('.step').hide();
    $('#step' + step).show();

    // Update progress bar
    var progress = (step / totalSteps) * 100;
    $('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress);
}

function nextStep() {
    if (currentStep < totalSteps) {
        currentStep++;
        showStep(currentStep);
    }
    if (currentStep === totalSteps) {
        $('#btnNext').hide();
        $('#btnSubmit').show();
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
    }
    if (currentStep !== totalSteps) {
        $('#btnNext').show();
        $('#btnSubmit').hide();
    }
}

$(document).ready(function() {
    showStep(currentStep);
});


</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function(){
        $('#category_services').change(function(){
            var selectedCategory = $(this).val();
            if(selectedCategory === 'Audit'){
                $('#client_ownership_id').show();
                $('#accounting_standars_id').show();
            } else {
                $('#client_ownership_id').hide();
                $('#accounting_standars_id').hide();
            }
        });
    });
</script>
<script>
    $(document).ready(function(){

        function calculateTotalProjectHours() {
            var totalHours = 0;

            $('.step input[id^="ph_"]').each(function() {
                var value = parseFloat($(this).val()) || 0;
                totalHours += value;
            });

            $('#estimated_hrs').val(totalHours);
        }


        function calculateTotalBudget() {
            var totalBudget = 0;

            $('.step input[id^="rate_"]').each(function() {
                var value = parseFloat($(this).val()) || 0;
                totalBudget += value;
            });

            $('#budget').val(totalBudget);
        }

        $('.step input').on('input', function() {
            calculateTotalProjectHours();
            calculateTotalBudget();
        });
    });
</script>
