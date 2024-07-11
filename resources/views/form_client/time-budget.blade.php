{{ Form::model($projectOrder, array('route' => array('save.time.budget', $projectOrder->id), 'method' => 'POST')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('ph_partners', __('Project Hour Partner'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::number('ph_partners', null, array('class'=>'form-control','required'=>'required', 'id'=>'ph_partners'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('rate_partners', __('Rate Partner'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::number('rate_partners', null, array('class'=>'form-control','required'=>'required', 'id'=>'rate_partners'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('ph_manager', __('Project Hour Manager'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::number('ph_manager', null, array('class'=>'form-control','required'=>'required', 'id'=>'ph_manager'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('rate_manager', __('Rate Manager'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::number('rate_manager', null, array('class'=>'form-control','required'=>'required', 'id'=>'rate_manager'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('ph_senior', __('Project Hour Senior Associate'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::number('ph_senior', null, array('class'=>'form-control','required'=>'required', 'id'=>'ph_senior'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('rate_senior', __('Rate Senior Associate'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::number('rate_senior', null, array('class'=>'form-control','required'=>'required', 'id'=>'rate_senior'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('ph_associate', __('Project Hour Associate'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::number('ph_associate', null, array('class'=>'form-control','required'=>'required', 'id'=>'ph_associate'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('rate_associate', __('Rate Associate'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::number('rate_associate', null, array('class'=>'form-control','required'=>'required', 'id'=>'rate_associate'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('ph_assistant', __('Project Hour Assistant'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::number('ph_assistant', null, array('class'=>'form-control','required'=>'required', 'id'=>'ph_assistant'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('rate_assistant', __('Rate Assistant'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::number('rate_assistant', null, array('class'=>'form-control','required'=>'required', 'id'=>'rate_assistant'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('estimated_hrs', __('Total Project Hours (Time Budget)'),['class'=>'form-label']) }}
                {{Form::text('estimated_hrs',null,array('class'=>'form-control', 'readonly', 'id'=>'estimated_hrs'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('budget', __('Total Charge Out'), ['class' => 'form-label']) }}
                {{ Form::number('budget', null, ['class' => 'form-control', 'readonly', 'id'=>'budget']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="submit" value="{{__('Save')}}" id="btnSubmit" class="btn btn-primary">
</div>
{{Form::close()}}

<script>
    $(document).ready(function(){

        function calculateTotalProjectHours() {
            var totalHours = 0;
            totalHours += parseFloat($('#ph_partners').val()) || 0;
            totalHours += parseFloat($('#ph_manager').val()) || 0;
            totalHours += parseFloat($('#ph_senior').val()) || 0;
            totalHours += parseFloat($('#ph_associate').val()) || 0;
            totalHours += parseFloat($('#ph_assistant').val()) || 0;

            $('#estimated_hrs').val(totalHours);
        }

        function calculateTotalBudget() {
            var totalBudget = 0;
            totalBudget += parseFloat($('#rate_partners').val()) || 0;
            totalBudget += parseFloat($('#rate_manager').val()) || 0;
            totalBudget += parseFloat($('#rate_senior').val()) || 0;
            totalBudget += parseFloat($('#rate_associate').val()) || 0;
            totalBudget += parseFloat($('#rate_assistant').val()) || 0;

            $('#budget').val(totalBudget);
        }

        $('input[id^="ph_"], input[id^="rate_"]').on('input', function() {
            calculateTotalProjectHours();
            calculateTotalBudget();
        });

        calculateTotalProjectHours();
        calculateTotalBudget();
    });
</script>

