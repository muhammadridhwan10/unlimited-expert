{{ Form::model($project, array('route' => array('save.time.budgets', $project->id), 'method' => 'POST')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <button type="button" class="btn btn-success mb-3" id="setRateBtn">Template Rate</button>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('als_partners', __('Project Hour Partner'),['class'=>'form-label']) }}
                {{ Form::number('als_partners', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Partner'))) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('rate_partners', __('Rate Partner'),['class'=>'form-label']) }}
                {{ Form::number('rate_partners', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Partner'))) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('total_partner', __('Total Partner'),['class'=>'form-label']) }}
                {{ Form::text('total_partner', null, array('class' => 'form-control', 'readonly', 'id'=>'total_partner')) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('als_manager', __('Project Hour Manager'),['class'=>'form-label']) }}
                {{ Form::number('als_manager', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Manager'))) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('rate_manager', __('Rate Manager'),['class'=>'form-label']) }}
                {{ Form::number('rate_manager', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Manager'))) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('total_manager', __('Total Manager'),['class'=>'form-label']) }}
                {{ Form::text('total_manager', null, array('class' => 'form-control', 'readonly', 'id'=>'total_manager')) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('als_leader', __('Project Hour Leader'),['class'=>'form-label']) }}
                {{ Form::number('als_leader', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Leader'))) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('rate_leader', __('Rate Leader'),['class'=>'form-label']) }}
                {{ Form::number('rate_leader', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Leader'))) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('total_leader', __('Total Leader'),['class'=>'form-label']) }}
                {{ Form::text('total_leader', null, array('class' => 'form-control', 'readonly', 'id'=>'total_leader')) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('als_senior_associate', __('Project Hour Senior Associate'),['class'=>'form-label']) }}
                {{ Form::number('als_senior_associate', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Senior Associate'))) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('rate_senior_associate', __('Rate Senior Associate'),['class'=>'form-label']) }}
                {{ Form::number('rate_senior_associate', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Senior Associate'))) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('total_senior_associate', __('Total Senior Associate'),['class'=>'form-label']) }}
                {{ Form::text('total_senior_associate', null, array('class' => 'form-control', 'readonly', 'id'=>'total_senior_associate')) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('als_associate', __('Project Hour Associate'),['class'=>'form-label']) }}
                {{ Form::number('als_associate', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Associate'))) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('rate_associate', __('Rate Associate'),['class'=>'form-label']) }}
                {{ Form::number('rate_associate', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Associate'))) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('total_associate', __('Total Associate'),['class'=>'form-label']) }}
                {{ Form::text('total_associate', null, array('class' => 'form-control', 'readonly', 'id'=>'total_associate')) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('als_intern', __('Project Hour Assistant'),['class'=>'form-label']) }}
                {{ Form::number('als_intern', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Intern'))) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('rate_intern', __('Rate Assistant'),['class'=>'form-label']) }}
                {{ Form::number('rate_intern', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Intern'))) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('total_intern', __('Total Assistant'),['class'=>'form-label']) }}
                {{ Form::text('total_intern', null, array('class' => 'form-control', 'readonly', 'id'=>'total_intern')) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('estimated_hrs', __('Total Project Hours (Time Budget)'),['class'=>'form-label']) }}
                {{Form::text('estimated_hrs',null,array('class'=>'form-control', 'readonly', 'id'=>'estimated_hrs'))}}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('budget', __('Total Charge Out'), ['class' => 'form-label']) }}
                {{ Form::number('budget', null, ['class' => 'form-control', 'readonly', 'id'=>'budget']) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('total_calculation', __('Total Charge Calculation'), ['class' => 'form-label']) }}
                {{ Form::number('total_calculation', null, ['class' => 'form-control', 'readonly', 'id'=>'total_calculation']) }}
            </div>
        </div>
    </div>
    
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Save')}}" class="btn  btn-primary">
</div>
{{Form::close()}}

<script>
    $(document).ready(function(){
        var rates = {
            'partners': 1000000,
            'manager': 400000,
            'leader': 450000,
            'senior_associate': 200000,
            'associate': 150000,
            'intern': 75000
        };

        function calculateTotalProjectHours() {
            var totalHours = 0;
            totalHours += parseFloat($('#als_partners').val()) || 0;
            totalHours += parseFloat($('#als_manager').val()) || 0;
            totalHours += parseFloat($('#als_leader').val()) || 0;
            totalHours += parseFloat($('#als_senior_associate').val()) || 0;
            totalHours += parseFloat($('#als_associate').val()) || 0;
            totalHours += parseFloat($('#als_intern').val()) || 0;

            $('#estimated_hrs').val(totalHours);
        }

        function calculateTotalBudget() {
            var totalBudget = 0;
            totalBudget += parseFloat($('#rate_partners').val()) || 0;
            totalBudget += parseFloat($('#rate_manager').val()) || 0;
            totalBudget += parseFloat($('#rate_leader').val()) || 0;
            totalBudget += parseFloat($('#rate_senior_associate').val()) || 0;
            totalBudget += parseFloat($('#rate_associate').val()) || 0;
            totalBudget += parseFloat($('#rate_intern').val()) || 0;

            $('#budget').val(totalBudget);
        }


        function calculateIndividualTotal() {
            var partnerTotal = (parseFloat($('#als_partners').val()) || 0) * (parseFloat($('#rate_partners').val()) || 0);
            $('#total_partner').val(partnerTotal);

            var managerTotal = (parseFloat($('#als_manager').val()) || 0) * (parseFloat($('#rate_manager').val()) || 0);
            $('#total_manager').val(managerTotal);

            var leaderTotal = (parseFloat($('#als_leader').val()) || 0) * (parseFloat($('#rate_leader').val()) || 0);
            $('#total_leader').val(leaderTotal);

            var seniorAssociateTotal = (parseFloat($('#als_senior_associate').val()) || 0) * (parseFloat($('#rate_senior_associate').val()) || 0);
            $('#total_senior_associate').val(seniorAssociateTotal);

            var associateTotal = (parseFloat($('#als_associate').val()) || 0) * (parseFloat($('#rate_associate').val()) || 0);
            $('#total_associate').val(associateTotal);

            var internTotal = (parseFloat($('#als_intern').val()) || 0) * (parseFloat($('#rate_intern').val()) || 0);
            $('#total_intern').val(internTotal);

            var totalCalculation = partnerTotal + managerTotal + leaderTotal + seniorAssociateTotal + associateTotal + internTotal;
            $('#total_calculation').val(totalCalculation);
        }

        $('input[id^="als_"], input[id^="rate_"]').on('input', function() {
            calculateTotalProjectHours();
            calculateTotalBudget();
            calculateIndividualTotal();
        });

        $('#setRateBtn').on('click', function() {
            $('#rate_partners').val(rates.partners);
            $('#rate_manager').val(rates.manager);
            $('#rate_leader').val(rates.leader);
            $('#rate_senior_associate').val(rates.senior_associate);
            $('#rate_associate').val(rates.associate);
            $('#rate_intern').val(rates.intern);

            calculateIndividualTotal();
        });

        calculateTotalProjectHours();
        calculateTotalBudget();
        calculateIndividualTotal();
    });

</script>


