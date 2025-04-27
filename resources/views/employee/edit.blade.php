@extends('layouts.admin')
@section('page-title')
    {{__('Edit Employee')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}"></i> Dashboard</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{route('employee.index')}}"> {{__('Employee')}}</a>
    </li>
    <li class="breadcrumb-item active">
        <a>{{$employee->name}}</a>
    </li>
@endsection

@section('content')
    <div class="row">
        {{ Form::model($employee, array('route' => array('employee.update', $employee->id), 'method' => 'PUT' , 'enctype' => 'multipart/form-data')) }}
        @csrf
        <div class="col-md-12">
            <!-- Navbar for Steps -->
            <ul class="nav nav-pills nav-justified mb-4" id="step-nav">
                <li class="nav-item">
                    <a class="nav-link active" id="step1-tab" data-toggle="pill" href="#step1" role="tab" aria-controls="step1">
                        <i class="fas fa-user"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="step2-tab" data-toggle="pill" href="#step2" role="tab" aria-controls="step2">
                        <i class="fas fa-envelope"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="step3-tab" data-toggle="pill" href="#step3" role="tab" aria-controls="step3">
                        <i class="fas fa-building"></i>
                    </a>
                </li>
            </ul>

            <!-- Step Content -->
            <div class="tab-content" id="step-content">
                <!-- Step 1 -->
                <div class="tab-pane fade show active" id="step1" role="tabpanel" aria-labelledby="step1-tab">
                    <div class="card card-fluid">
                        <div class="card-header">
                            <h6 class="mb-0">{{ __('Personal Information Detail') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                {!! Form::label('name', __('Fullname'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::text('name', $employee->name ?? '', ['class' => 'form-control required-field', 'id' => 'fullname', 'placeholder' => __('Fullname')]) !!}
                                <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('pob', __('Place of Birth'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('pob',$employee->detail->pob ?? '', ['class' => 'form-control required-field', 'required' => 'required' , 'placeholder' => __('Place Of Birth')]) !!}
                                    <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('dob', __('Date of Birth'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::date('dob',$employee->dob ?? '', ['class' => 'form-control required-field', 'required' => 'required']) !!}
                                    <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    <div class="d-flex radio-check">
                                        <div class="form-check form-check-inline">
                                            {!! Form::radio(
                                                'gender',
                                                'Male',
                                                ($employee->gender ?? '') == 'Male',
                                                ['id' => 'g_male', 'class' => 'form-check-input']
                                            ) !!}
                                            {!! Form::label('g_male', __('Male'), ['class' => 'form-check-label']) !!}
                                        </div>
                                        <div class="form-check form-check-inline">
                                            {!! Form::radio(
                                                'gender',
                                                'Female',
                                                ($employee->gender ?? '') == 'Female',
                                                ['id' => 'g_female', 'class' => 'form-check-input']
                                            ) !!}
                                            {!! Form::label('g_female', __('Female'), ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('marital_status', __('Marital Status'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    <div class="d-flex radio-check">
                                        @foreach (['Married', 'Single', 'Widow', 'Widower'] as $status)
                                            <div class="form-check form-check-inline">
                                                {!! Form::radio(
                                                    'marital_status',
                                                    $status,
                                                    ($employee->detail->marital_status ?? '') == $status,
                                                    ['id' => 'ms_' . strtolower($status), 'class' => 'form-check-input']
                                                ) !!}
                                                {!! Form::label('ms_' . strtolower($status), __($status), ['class' => 'form-check-label']) !!}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('religion', __('Religion'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::select('religion', ['Islam' => 'Islam', 'Kristen' => 'Kristen', 'Katolik' => 'Katolik', 'Hindu' => 'Hindu', 'Buddha' => 'Buddha', 'Konghucu' => 'Konghucu'], $employee->personalInformation->religion ?? '', ['class' => 'form-control', 'placeholder' => __('Select Religion')]) !!}
                                    <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('blood_type', __('Blood Type'),['class'=>'form-label']) !!}
                                    {!! Form::select('blood_type', ['A' => 'A', 'AB' => 'AB', 'B' => 'B', 'O' => 'O'], $employee->detail->blood_type ?? '', ['class' => 'form-control', 'placeholder' => __('Select Blood Type')]) !!}
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('citizenship', __('Citizenship'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::select('citizenship', [
                                        'WNI' => __('WNI'),
                                        'WNA' => __('WNA')
                                    ], $employee->detail->citizenship ?? '', ['class' => 'form-control required-field', 'required' => 'required', 'placeholder' => __('Select Citizenship')]) !!}
                                    <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('last_education', __('Last Education Level'),['class'=>'form-label']) !!}
                                    {!! Form::select('last_education', [
                                        'S3' => __('S3'),
                                        'S2' => __('S2'),
                                        'S1' => __('S1'),
                                        'D4' => __('D4'),
                                        'D3' => __('D3'),
                                        'D2' => __('D2'),
                                        'STM' => __('STM'),
                                        'SMK' => __('SMK'),
                                        'SMA' => __('SMA'),
                                        'SMP' => __('SMP'),
                                        'SD' => __('SD')
                                    ], $employee->detail->last_education ?? '', ['class' => 'form-control', 'placeholder' => __('Select Last Education level')]) !!}
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('name_of_educational_institution', __('Institution Name'),['class'=>'form-label']) !!}
                                    {!! Form::text('name_of_educational_institution',$employee->detail->name_of_educational_institution ?? '', ['class' => 'form-control', 'placeholder' => __('Institution Name')]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('major', __('Major'),['class'=>'form-label']) !!}
                                    {!! Form::text('major',$employee->detail->major ?? '', ['class' => 'form-control', 'placeholder' => __('Major')]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="tab-pane fade" id="step2" role="tabpanel" aria-labelledby="step2-tab">
                    <div class="card card-fluid">
                        <div class="card-header">
                            <h6 class="mb-0">{{ __('Contact Information') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('identity_type', __('Identity Type'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::select('identity_type', [
                                        'ktp' => __('KTP'),
                                        'passport' => __('PASSPORT'),
                                        'kitas' => __('Kartu Izin Tinggal Terbatas (KITAS)'),
                                        'kitap' => __('Kartu Izin Tinggal Tetap (KITAP)'),
                                        'sim' => __('Surat Izin Mengemudi (SIM)'),
                                        'lainnya' => __('lainnya')
                                    ], $employee->detail->identity_type ?? '', ['class' => 'form-control required-field', 'required' => 'required', 'placeholder' => __('Select Identity Type')]) !!}
                                    <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('identity number', __('Identity Number'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::number('identity number',$employee->detail->identity_number ?? '', ['class' => 'form-control required-field','required' => 'required', 'placeholder' => __('Identity Number')]) !!}
                                    <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('email', __('Email'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::email('email', $employee->email ?? '', ['class' => 'form-control required-field','required' => 'required', 'placeholder' => __('Email')]) !!}
                                        <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('telp', __('Phone'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::number('telp', $employee->phone ?? '', ['class' => 'form-control required-field','required' => 'required', 'placeholder' => __('Phone')]) !!}
                                        <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    {!! Form::label('address', __('Address'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::textarea('address', $employee->address ?? '', ['class' => 'form-control required-field','required' => 'required', 'placeholder' => __('Address')]) !!}
                                    <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {{Form::label('country',__('Country'),['class'=>'form-label'])}}<span class="text-danger">*</span>
                                        <select id="country" class="form-control">
                                            <option value="">Select Country</option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country->code }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                </div>
                                <div class="form-group col-md-6 state">
                                    {{Form::label('state',__('State'),['class'=>'form-label'])}}<span class="text-danger">*</span>
                                    <select id="state" class="form-control">
                                        <option value="">Select State</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6 city">
                                    {{Form::label('city',__('City'),['class'=>'form-label'])}}<span class="text-danger">*</span>
                                    <select id="city" class="form-control">
                                        <option value="">Select City</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('emergency_contact', __('Emergency Contact Name'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('emergency_contact', $employee->detail->emergency_contact ?? '', ['class' => 'form-control required-field','required' => 'required', 'placeholder' => __('Emergency Contact Name')]) !!}
                                        <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('emergency_phone', __('Emergency Phone'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::number('emergency_phone', $employee->detail->emergency_phone ?? '', ['class' => 'form-control required-field','required' => 'required', 'placeholder' => __('Emergency Phone')]) !!}
                                        <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="tab-pane fade" id="step3" role="tabpanel" aria-labelledby="step3-tab">
                    <div class="card card-fluid">
                        <div class="card-header">
                            <h6 class="mb-0">{{ __('Company Detail') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('employee_number', __('Employee ID'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('employee_number', $employee->detail->employee_number ?? '', ['class' => 'form-control required-field','required' => 'required', 'placeholder' => __('Employee ID')]) !!}
                                    <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('employee_status', __('Employee Status'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::select('employee_status', [
                                        'pkwtt' => __('PKWTT'),
                                        'probation' => __('Probation'),
                                        'pkwt' => __('PKWT'),
                                    ], $employee->detail->employee_status ?? '', ['class' => 'form-control required-field', 'required' => 'required', 'placeholder' => __('Select Employee Status')]) !!}
                                    <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('company_doj', __('Company Date Of Joining'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::date('company_doj', $employee->company_doj ?? '', ['class' => 'form-control required-field','required' => 'required']) !!}
                                    <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('branch_id', __('Branch'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {{ Form::select('branch_id', $branches, $employee->branch_id ?? '', ['class' => 'form-control select2 required-field','placeholder' => __('Select Branch')]) }}
                                    <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('department_id', __('Department'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {{ Form::select('department_id', $departmentData, $employee->department_id ?? '', ['class' => 'form-control select2 required-field', 'id'=>'department_id', 'placeholder' => __('Select Department')]) }}
                                    <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('designation_id', __('Designation'),['class'=>'form-label']) }}<span class="text-danger pl-1">*</span>
                                    {{ Form::select('designation_id', $designationData, $employee->designation_id ?? '', ['class' => 'form-control select2 required-field', 'id'=>'designation_id', 'placeholder' => __('Select Designation')]) }}
                                    <small class="text-danger validation-error" style="display: none;">{{ __('This field is required.') }}</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 ">
                                    <div class="card card-fluid">
                                        <div class="card-header"><h6 class="mb-0">{{__('Document Employee')}}</h6></div>
                                        <div class="card-body employee-detail-create-body">
                                            @foreach($documents as $key=>$document)
                                                <div class="row">
                                                    <div class="form-group col-12">
                                                        <div class="float-left col-4">
                                                            <label for="document" class="float-left pt-1 form-label">{{ $document->name }} @if($document->is_required == 1) <span class="text-danger">*</span> @endif</label>
                                                        </div>
                                                        <div class="float-right col-8">
                                                            <input type="hidden" name="emp_doc_id[{{ $document->id}}]" id="" value="{{$document->id}}">
                                                            <div class="choose-file form-group">
                                                                <label for="document[{{ $document->id }}]">
                                                                    <div>{{__('Choose File')}}</div>
                                                                    <input class="form-control  @error('document') is-invalid @enderror border-0" @if($document->is_required == 1) required @endif name="document[{{ $document->id}}]" type="file" id="document[{{ $document->id }}]" data-filename="{{ $document->id.'_filename'}}">
                                                                </label>
                                                                <p class="{{ $document->id.'_filename'}}"></p>
                                                            </div>

                                                        </div>

                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-3">
                <button type="button" class="btn btn-secondary" id="prev-btn" style="display: none;">{{ __('Previous') }}</button>
                <button type="button" class="btn btn-primary" id="next-btn">{{ __('Next') }}</button>
                <button type="submit" class="btn btn-primary" id="save-btn" style="display: none;">{{ __('Save') }}</button>
            </div>
        </div>
        {{ Form::close() }}
    </div>
@endsection

@push('script-page')
<script>
    $(document).ready(function () {
        var d_id = $('#department_id').val();
        getDesignation(d_id);
    });

    $(document).on('change', 'select[name=department_id]', function () {
        var department_id = $(this).val();
        getDesignation(department_id);
    });

    function getDesignation(did) {

        $.ajax({
            url: '{{route('employee.json')}}',
            type: 'POST',
            data: {
                "department_id": did, "_token": "{{ csrf_token() }}",
            },
            success: function (data) {
                $('#designation_id').append('<option value="">{{__('Select any Designation')}}</option>');
                $.each(data, function (key, value) {
                    $('#designation_id').append('<option value="' + key + '">' + value + '</option>');
                });
            }
        });
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let currentStep = 1;
        const totalSteps = 3;

        const updateButtons = () => {
            document.getElementById('prev-btn').style.display = currentStep > 1 ? 'inline-block' : 'none';
            document.getElementById('next-btn').style.display = currentStep < totalSteps ? 'inline-block' : 'none';
            document.getElementById('save-btn').style.display = currentStep === totalSteps ? 'inline-block' : 'none';
        };

        const validateCurrentStep = () => {
            let isValid = true;
            const currentFields = document.querySelectorAll(`#step${currentStep} .required-field`);
            
            currentFields.forEach(field => {
                const errorContainer = field.nextElementSibling;
                if (!field.value.trim()) {
                    errorContainer.style.display = 'inline';
                    isValid = false;
                } else {
                    errorContainer.style.display = 'none';
                }
            });

            return isValid;
        };

        document.getElementById('next-btn').addEventListener('click', () => {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    document.getElementById(`step${currentStep}-tab`).classList.remove('active');
                    document.getElementById(`step${currentStep}`).classList.remove('show', 'active');
                    currentStep++;
                    document.getElementById(`step${currentStep}-tab`).classList.add('active');
                    document.getElementById(`step${currentStep}`).classList.add('show', 'active');
                    updateButtons();
                }
            }
        });

        document.getElementById('prev-btn').addEventListener('click', () => {
            if (currentStep > 1) {
                document.getElementById(`step${currentStep}-tab`).classList.remove('active');
                document.getElementById(`step${currentStep}`).classList.remove('show', 'active');
                currentStep--;
                document.getElementById(`step${currentStep}-tab`).classList.add('active');
                document.getElementById(`step${currentStep}`).classList.add('show', 'active');
                updateButtons();
            }
        });

        updateButtons();
    });
</script>
<script>
    $('#country').change(function() {
        var countryCode = $(this).val();
        $('#selected_country').val(countryCode);

        if (countryCode) {
            $.ajax({
                url: '{{ route("get.states.by.country") }}',
                type: 'GET',
                data: { country_code: countryCode },
                dataType: 'json',
                success: function(data) {
                    $('#state').empty().append('<option value="">Select State</option>');
                    $.each(data, function(index, state) {
                        $('#state').append('<option value="' + state.district + '">' + state.name + '</option>');
                    });
                }
            });
        } else {
            $('#state').empty().append('<option value="">Select State</option>');
            $('#city').empty().append('<option value="">Select City</option>');
        }
    });

    $('#state').change(function() {
        var stateDistrict = $(this).val();
        $('#selected_state').val(stateDistrict);

        if (stateDistrict) {
            $.ajax({
                url: '{{ route("get.cities.by.state") }}',
                type: 'GET',
                data: { state_district: stateDistrict },
                dataType: 'json',
                success: function(data) {
                    $('#city').empty().append('<option value="">Select City</option>');
                    $.each(data, function(index, city) {
                        $('#city').append('<option value="' + city.id + '">' + city.name + '</option>');
                    });
                }
            });
        } else {
            $('#city').empty().append('<option value="">Select City</option>');
        }
    });

    $('#city').change(function() {
        var cityId = $(this).val();
        $('#selected_city').val(cityId);
    });
</script>
@endpush
