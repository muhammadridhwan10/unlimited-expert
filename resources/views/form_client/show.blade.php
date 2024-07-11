@php
    $logo=asset(Storage::url('uploads/logo/'));
    $company_favicon=Utility::getValByName('company_favicon');
    $favicon=Utility::getValByName('company_favicon');
@endphp

<html lang="en">
<meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
<head>
    <title>{{(Utility::getValByName('title_text')) ? Utility::getValByName('title_text') : config('app.name', 'TGS AU-Partners Apps')}} - Form New Client</title>

    <!-- Meta -->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui"/>
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <link rel="icon" href="{{$logo.'/'.(isset($company_favicon) && !empty($company_favicon)?$company_favicon:'favicon.png')}}" type="image" sizes="16x16">

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}">

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">
    <!-- vendor css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">

    <link rel="stylesheet" href="{{ asset('css/custom.css') }}" id="main-style-link">

    <!-- Tambahkan CSS untuk daterangepicker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    
    <!-- Tambahkan script jQuery jika belum ada -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Tambahkan script untuk moment.js dan daterangepicker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <style>
        .step {
            display: none;
        }
        .step.active {
            display: block;
        }
        .step-buttons {
            display: flex;
            justify-content: space-between;
        }
        .progress-container {
            width: 100%;
            background-color: #f3f3f3;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .progress-bar {
            width: 0;
            height: 20px;
            background-color: #4caf50;
            text-align: center;
            color: white;
            border-radius: 5px;
        }
        .form-wizard {
            max-width: 900px;
            margin: auto;
        }
    </style>
    <style>
        .btn-success {
            margin-left: 450px;
        }
    </style>

</head>

<body class="theme-4">
    <div class="dash-content">
        <div class="min-vh-100 py-5 d-flex align-items-center">
            <div class="w-100">
                <div class="row justify-content-center">
                    <div class="col-sm-10 col-lg-8"> <!-- Adjusted to make form wider -->
                        <div class="row justify-content-center mb-3 text-center">
                            <a class="navbar-brand" href="#">
                                <img src="{{ asset(Storage::url('uploads/logo/logo-dark.png')) }}" class="navbar-brand-img big-logo">
                            </a>
                        </div>
                        <div class="card shadow zindex-100 mb-0 form-wizard"> <!-- Added form-wizard class -->
                            {{ Form::model($projectOrder, array('route' => array('form.client.status', $projectOrder->id), 'method' => 'POST')) }}
                                <div class="card-body px-md-5 py-5">
                                    <div>
                                        <a href="{{ route('form_client.index') }}" class="text-decoration-none me-3">
                                            <i class="fas fa-arrow-left" style="font-size: 1.5rem;"></i>
                                        </a>
                                    </div>
                                    <br>
                                    <div class="step active">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('name', __('Client Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{ Form::text('name', $projectOrder->name, array('class'=>'form-control','readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                             <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('email', __('Client Email'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('email',$projectOrder->email,array('class'=>'form-control', 'readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('address', __('Address'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('address',$projectOrder->address,array('class'=>'form-control', 'readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('country', __('Country'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('country',$projectOrder->country,array('class'=>'form-control', 'readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('state', __('State'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('state',$projectOrder->state,array('class'=>'form-control', 'readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('city', __('City'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('city',$projectOrder->city,array('class'=>'form-control','readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="step">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('name_pic', __('Name PIC'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('name_pic',$projectOrder->name_pic,array('class'=>'form-control', 'readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('email_pic', __('Email PIC'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('email_pic',$projectOrder->email_pic,array('class'=>'form-control', 'readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('telp_pic', __('Phone Number / WA number PIC'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::number('telp_pic',$projectOrder->telp_pic,array('class'=>'form-control', 'readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('name_invoice', __('Invoice Recipient Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('name_invoice',$projectOrder->name_invoice,array('class'=>'form-control', 'readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('position', __('Position On Invoice'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('position',$projectOrder->position,array('class'=>'form-control', 'readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('telp', __('Phone Number / WA number on Invoice'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::number('telp',$projectOrder->telp,array('class'=>'form-control', 'readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="step">
                                        <div class="row">
                                            <div class="col-md-6">  
                                                <div class="form-group">
                                                    {{ Form::label('client_business_sector_id', __('Client Business Sector'),['class'=>'form-label']) }}
                                                    {{ Form::text('client_business_sector_id', $projectOrder->sector->name, array('class' => 'form-control', 'readonly'=>'readonly')) }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">  
                                                <div class="form-group">
                                                    {{ Form::label('client_ownership_id', __('Client Ownership'), ['class'=>'form-label']) }}
                                                    {{ Form::text('client_ownership_id', isset($projectOrder->ownership) ? $projectOrder->ownership->name : '-', ['class' => 'form-control', 'readonly' => 'readonly']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">  
                                                <div class="form-group">
                                                    {{ Form::label('accounting_standars_id', __('Accounting Standars'), ['class'=>'form-label']) }}
                                                    {{ Form::text('accounting_standars_id', isset($projectOrder->accountingstandard) ? $projectOrder->accountingstandard->name : '-', ['class' => 'form-control', 'readonly' => 'readonly']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('total_company_assets_value', __('Total Company Assets Value'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::number('total_company_assets_value',$projectOrder->total_company_assets_value,array('class'=>'form-control', 'readonly' => 'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('total_company_income_per_year', __('Total Company Income Per Year'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('total_company_income_per_year',$projectOrder->total_company_income_per_year,array('class'=>'form-control','readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('total_company_profit_or_loss', __('Total Company Profit Or Loss For The Year'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('total_company_profit_or_loss',$projectOrder->total_company_income_per_year,array('class'=>'form-control','readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('periode', __('Periode Order'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('periode',$projectOrder->periode,array('class'=>'form-control','readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('total_employee', __('Total Employee'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('total_employee',$projectOrder->total_employee,array('class'=>'form-control','readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('total_branch_offices', __('Total Branch Offices'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('total_branch_offices',$projectOrder->total_branch_offices,array('class'=>'form-control','readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('npwp', __('Tax Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('npwp',$projectOrder->npwp,array('class'=>'form-control','readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('where_did_you_find_out_about_us', __('Where Did You Find Out About Us?'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{Form::text('where_did_you_find_out_about_us',$projectOrder->where_did_you_find_out_about_us,array('class'=>'form-control', 'readonly'=>'readonly'))}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="step">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('ph_partners', __('Project Hour Partner'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{ Form::number('ph_partners', $projectOrder->ph_partners, array('class'=>'form-control','readonly', 'id'=>'ph_partners'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('rate_partners', __('Rate Partner'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{ Form::text('rate_partners', \Auth::user()->priceFormat($projectOrder->rate_partners), array('class'=>'form-control','readonly', 'id'=>'rate_partners'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('ph_manager', __('Project Hour Manager'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{ Form::number('ph_manager', $projectOrder->ph_manager, array('class'=>'form-control','readonly', 'id'=>'ph_manager'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('rate_manager', __('Rate Manager'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{ Form::text('rate_manager', \Auth::user()->priceFormat($projectOrder->rate_manager), array('class'=>'form-control','readonly', 'id'=>'rate_manager'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('ph_senior', __('Project Hour Senior Associate'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{ Form::number('ph_senior', $projectOrder->ph_senior, array('class'=>'form-control','readonly', 'id'=>'ph_senior'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('rate_senior', __('Rate Senior Associate'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{ Form::text('rate_senior', \Auth::user()->priceFormat($projectOrder->rate_senior), array('class'=>'form-control','readonly', 'id'=>'rate_senior'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('ph_associate', __('Project Hour Associate'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{ Form::number('ph_associate', $projectOrder->ph_associate, array('class'=>'form-control','readonly', 'id'=>'ph_associate'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('rate_associate', __('Rate Associate'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{ Form::text('rate_associate', \Auth::user()->priceFormat($projectOrder->rate_associate), array('class'=>'form-control','readonly', 'id'=>'rate_associate'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('ph_assistant', __('Project Hour Assistant'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{ Form::number('ph_assistant', $projectOrder->ph_assistant, array('class'=>'form-control','readonly', 'id'=>'ph_assistant'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('rate_assistant', __('Rate Assistant'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                                    {{ Form::text('rate_assistant', \Auth::user()->priceFormat($projectOrder->rate_assistant), array('class'=>'form-control','readonly', 'id'=>'rate_assistant'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('estimated_hrs', __('Total Project Hours (Time Budget)'),['class'=>'form-label']) }}
                                                    {{Form::text('estimated_hrs',$projectOrder->estimated_hrs,array('class'=>'form-control', 'readonly', 'id'=>'estimated_hrs'))}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('budget', __('Total Charge Out'), ['class' => 'form-label']) }}
                                                    {{ Form::text('budget', \Auth::user()->priceFormat($projectOrder->budget), ['class' => 'form-control', 'readonly', 'id'=>'budget']) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="step-buttons">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>
                                        @if($projectOrder->budget != NULL)
                                        <input type="submit" value="{{__('Approval')}}" class="btn btn-success" style="display:none" name="status_client">
                                        <input type="submit" value="{{__('Reject')}}" class="btn btn-danger" style="display:none" name="status_client">
                                        @endif
                                    </div>
                                </div>
                            {{Form::close()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('partials.admin.footer')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3.1.0/daterangepicker.min.js"></script>

    <script>
        let currentStep = 0;
        const steps = document.querySelectorAll('.step');
        const nextButton = document.querySelector('.step-buttons .btn-primary');
        const prevButton = document.querySelector('.step-buttons .btn-secondary');
        const approvedButton = document.querySelector('.step-buttons .btn-success');
        const rejectedButton = document.querySelector('.step-buttons .btn-danger');
        const progressBar = document.getElementById('progressBar');

        function showStep(step) {
            steps.forEach((step, index) => {
                step.classList.remove('active');
                if (index === currentStep) {
                    step.classList.add('active');
                }
            });
            prevButton.style.display = currentStep === 0 ? 'none' : 'inline-block';
            nextButton.style.display = currentStep === steps.length - 1 ? 'none' : 'inline-block';
            approvedButton.style.display = currentStep === steps.length - 1 ? 'inline-block' : 'none';
            rejectedButton.style.display = currentStep === steps.length - 1 ? 'inline-block' : 'none';
            updateProgressBar();
        }

        function nextStep() {
            if (currentStep < steps.length - 1) {
                currentStep++;
                showStep(currentStep);
            }
        }

        function prevStep() {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        }

        function updateProgressBar() {
            const progress = (currentStep / (steps.length - 1)) * 100;
            progressBar.style.width = progress + '%';
            progressBar.innerText = Math.round(progress) + '%';
        }

        document.addEventListener('DOMContentLoaded', () => {
            showStep(currentStep);
        });
    </script>
</body>


</html>
