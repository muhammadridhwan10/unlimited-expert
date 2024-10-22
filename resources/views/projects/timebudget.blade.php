{{ Form::model($project, array('route' => array('save.time.budgets', $project->id), 'method' => 'POST')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <button type="button" class="btn btn-success mb-3" id="setRateBtn">Template Rate</button>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('als_partners', __('Project Hour Partner'),['class'=>'form-label']) }}
                {{ Form::number('als_partners', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Partner'))) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('rate_partners', __('Rate Partner'),['class'=>'form-label']) }}
                {{ Form::number('rate_partners', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Partner'))) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('als_manager', __('Project Hour Manager'),['class'=>'form-label']) }}
                {{ Form::number('als_manager', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Manager'))) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('rate_manager', __('Rate Manager'),['class'=>'form-label']) }}
                {{ Form::number('rate_manager', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Manager'))) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('als_senior_associate', __('Project Hour Senior Associate'),['class'=>'form-label']) }}
                {{ Form::number('als_senior_associate', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Senior Associate'))) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('rate_senior_associate', __('Rate Senior Associate'),['class'=>'form-label']) }}
                {{ Form::number('rate_senior_associate', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Senior Associate'))) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('als_associate', __('Project Hour Associate'),['class'=>'form-label']) }}
                {{ Form::number('als_associate', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Associate'))) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('rate_associate', __('Rate Associate'),['class'=>'form-label']) }}
                {{ Form::number('rate_associate', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Associate'))) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('als_intern', __('Project Hour Assistant'),['class'=>'form-label']) }}
                {{ Form::number('als_intern', null, array('class' => 'form-control','placeholder'=>__('Enter Allocated Hours Intern'))) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('rate_intern', __('Rate Assistant'),['class'=>'form-label']) }}
                {{ Form::number('rate_intern', null, array('class' => 'form-control','placeholder'=>__('Enter Rate Intern'))) }}
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
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Save')}}" class="btn  btn-primary">
</div>
{{Form::close()}}

<script>
    $(document).ready(function(){
        // Rates berdasarkan data yang diberikan
        var rates = {
            'partners': 1000000,
            'manager': 400000,
            'senior_associate': 200000,
            'associate': 150000,
            'intern': 75000
        };

        // Fungsi untuk menghitung total hours
        function calculateTotalProjectHours() {
            var totalHours = 0;
            totalHours += parseFloat($('#als_partners').val()) || 0;
            totalHours += parseFloat($('#als_manager').val()) || 0;
            totalHours += parseFloat($('#als_senior_associate').val()) || 0;
            totalHours += parseFloat($('#als_associate').val()) || 0;
            totalHours += parseFloat($('#als_intern').val()) || 0;

            $('#estimated_hrs').val(totalHours);
        }

        // Fungsi untuk menghitung total budget
        function calculateTotalBudget() {
            var totalBudget = 0;
            totalBudget += parseFloat($('#rate_partners').val()) || 0;
            totalBudget += parseFloat($('#rate_manager').val()) || 0;
            totalBudget += parseFloat($('#rate_senior_associate').val()) || 0;
            totalBudget += parseFloat($('#rate_associate').val()) || 0;
            totalBudget += parseFloat($('#rate_intern').val()) || 0;

            $('#budget').val(totalBudget);
        }

        // Event listener untuk input perubahan
        $('input[id^="als_"], input[id^="rate_"]').on('input', function() {
            calculateTotalProjectHours();
            calculateTotalBudget();
        });

        // Ketika button "Set Rate" diklik, isi otomatis rate
        $('#setRateBtn').on('click', function() {
            $('#rate_partners').val(rates.partners);
            $('#rate_manager').val(rates.manager);
            $('#rate_senior_associate').val(rates.senior_associate);
            $('#rate_associate').val(rates.associate);
            $('#rate_intern').val(rates.intern);

            // Hitung ulang total budget setelah nilai diisi
            calculateTotalBudget();
        });

        calculateTotalProjectHours();
        calculateTotalBudget();
    });
</script>


