{{ Form::model($project, ['route' => ['projects.update', $project->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="form-group">
                {{ Form::label('project_name', __('Project Name'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('project_name', null, ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                {{ Form::date('start_date', null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                {{ Form::date('end_date', null, ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('public_accountant_id', __('Public Accountant'),['class'=>'form-label']) }}
                {!! Form::select('public_accountant_id', $public_accountant, null,array('class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('client', __('Client'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {!! Form::select('client', $clients, $project->client_id,array('class' => 'form-control select2','id'=>'choices-multiple1','required'=>'required')) !!}
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('template_task_id', __('Task Template'),['class'=>'form-label']) }}<span class="text-danger"></span>
                {!! Form::select('template_task_id', $tasktemplate, $project->template_task_id,array('class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('tag', __('Office'), ['class' => 'form-label']) }}
                <select name="tag" id="tag" class="form-control main-element">
                    @foreach(\App\Models\Project::$tags as $k => $v)
                        <option value="{{$k}}">{{__($v)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('label', __('Type Of Service'), ['class' => 'form-label']) }}
                <select name="label" id="label" class="form-control main-element select2" >
                    @foreach(\App\Models\Project::$label as $k => $v)
                        <option value="{{$k}}" {{ ($project->status == $k) ? 'selected' : ''}}>{{__($v)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                <select name="status" id="status" class="form-control main-element select2" >
                    @foreach(\App\Models\Project::$project_status as $k => $v)
                        <option value="{{$k}}" {{ ($project->status == $k) ? 'selected' : ''}}>{{__($v)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '4', 'cols' => '50']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-12">
            {{ Form::label('project_image', __('Project Image'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
            <div class="form-file mb-3">
                <input type="file" accept=".png, .jpg, .jpeg" class="form-control" name="project_image" >
            </div>
            <img {{$project->img_image}} class="avatar avatar-xl" alt="">
        </div>

    </div>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('als_partners', __('Project Hour Partner'),['class'=>'form-label']) }}
                {{ Form::number('als_partners',$project->project_offerings->als_partners ?? '-', array('class' => 'form-control allocated-hours','placeholder'=>__('Enter Allocated Hours Partner'), 'id'=>'als_partners')) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('rate_partners', __('Rate Partner'),['class'=>'form-label']) }}
                {{ Form::number('rate_partners', $project->project_offerings->rate_partners ?? '-', array('class' => 'form-control rate','placeholder'=>__('Enter Rate Partner'), 'id'=>'rate_partners')) }}
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
                {{ Form::number('als_manager', $project->project_offerings->als_manager ?? '-', array('class' => 'form-control allocated-hours','placeholder'=>__('Enter Allocated Hours Manager'), 'id'=>'als_manager')) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('rate_manager', __('Rate Manager'),['class'=>'form-label']) }}
                {{ Form::number('rate_manager', $project->project_offerings->rate_manager ?? '-', array('class' => 'form-control rate','placeholder'=>__('Enter Rate Manager'),'id'=>'rate_manager')) }}
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
                {{ Form::number('als_leader', $project->project_offerings->als_leader ?? '-', array('class' => 'form-control allocated-hours','placeholder'=>__('Enter Allocated Hours Leader'), 'id'=>'als_leader')) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('rate_leader', __('Rate Leader'),['class'=>'form-label']) }}
                {{ Form::number('rate_leader', $project->project_offerings->rate_leader ?? '-', array('class' => 'form-control rate','placeholder'=>__('Enter Rate Leader'),'id'=>'rate_leader')) }}
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
                {{ Form::number('als_senior_associate', $project->project_offerings->als_senior_associate ?? '-', array('class' => 'form-control allocated-hours','placeholder'=>__('Enter Allocated Hours Senior Associate'), 'id'=>'als_senior_associate')) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('rate_senior_associate', __('Rate Senior Associate'),['class'=>'form-label']) }}
                {{ Form::number('rate_senior_associate', $project->project_offerings->rate_senior_associate ?? '-', array('class' => 'form-control rate','placeholder'=>__('Enter Rate Senior Associate'),'id'=>'rate_senior_associate')) }}
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
                {{ Form::number('als_associate', $project->project_offerings->als_associate ?? '-', array('class' => 'form-control allocated-hours','placeholder'=>__('Enter Allocated Hours Associate'),'id'=>'als_associate')) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('rate_associate', __('Rate Associate'),['class'=>'form-label']) }}
                {{ Form::number('rate_associate', $project->project_offerings->rate_associate ?? '-', array('class' => 'form-control rate','placeholder'=>__('Enter Rate Associate'),'id'=>'rate_associate')) }}
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
                {{ Form::number('als_intern', $project->project_offerings->als_intern ?? '-', array('class' => 'form-control allocated-hours','placeholder'=>__('Enter Allocated Hours Intern'),'id'=>'als_intern')) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('rate_intern', __('Rate Assistant'),['class'=>'form-label']) }}
                {{ Form::number('rate_intern', $project->project_offerings->rate_intern ?? '-', array('class' => 'form-control rate','placeholder'=>__('Enter Rate Intern'),'id'=>'rate_intern')) }}
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
                {{ Form::label('charge_out', __('Total Charge Out'), ['class' => 'form-label']) }}
                {{ Form::number('charge_out', null, ['class' => 'form-control', 'readonly', 'id'=>'charge_out']) }}
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
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{Form::close()}}
<script>
    // Function to calculate totals
    function calculateTotals() {
        let totalHours = 0;
        let totalChargeOut = 0;
        let totalCalculation = 0;

        // Get all hour and rate elements
        let allocatedHours = document.querySelectorAll('.allocated-hours');
        let rates = document.querySelectorAll('.rate');
        
        // Define the role-specific total fields
        const roleFields = [
            { hoursId: 'als_partners', rateId: 'rate_partners', totalId: 'total_partner' },
            { hoursId: 'als_manager', rateId: 'rate_manager', totalId: 'total_manager' },
            { hoursId: 'als_leader', rateId: 'rate_leader', totalId: 'total_leader' },
            { hoursId: 'als_senior_associate', rateId: 'rate_senior_associate', totalId: 'total_senior_associate' },
            { hoursId: 'als_associate', rateId: 'rate_associate', totalId: 'total_associate' },
            { hoursId: 'als_intern', rateId: 'rate_intern', totalId: 'total_intern' }
        ];

        // Loop through roles and calculate individual totals
        roleFields.forEach(role => {
            let hours = parseFloat(document.getElementById(role.hoursId).value) || 0;
            let rate = parseFloat(document.getElementById(role.rateId).value) || 0;
            let total = hours * rate;

            // Update individual total fields
            document.getElementById(role.totalId).value = total;

            // Accumulate totals for overall calculations
            totalHours += hours;
            totalChargeOut += rate;
            totalCalculation += total;
        });

        // Set calculated values to the respective fields
        document.getElementById('estimated_hrs').value = totalHours;
        document.getElementById('charge_out').value = totalChargeOut;
        document.getElementById('total_calculation').value = totalCalculation;
    }

    // Add event listeners to trigger calculation on input change
    document.querySelectorAll('.allocated-hours, .rate').forEach(input => {
        input.addEventListener('input', calculateTotals);
    });

    // Initial calculation
    calculateTotals();
</script>
