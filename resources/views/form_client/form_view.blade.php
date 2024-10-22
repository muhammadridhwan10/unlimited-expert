@php
    $logo=asset(Storage::url('uploads/logo/'));
    $company_favicon=Utility::getValByName('company_favicon');
    $favicon=Utility::getValByName('company_favicon');
@endphp
@push('custom-scripts')
    @if(env('RECAPTCHA_MODULE') == 'yes')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush

<html lang="en">
<meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
<head>
    <title>{{(Utility::getValByName('title_text')) ? Utility::getValByName('title_text') : config('app.name', 'Unlimited Expert')}} - Form New Client</title>

    <!-- Meta -->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui"/>
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <link rel="icon" href="{{$logo.'/'.(isset($company_favicon) && !empty($company_favicon)?$company_favicon:'favicon.png')}}" type="image" sizes="16x16">

    <!-- Favicon icon -->
    {{-- <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/x-icon"/> --}}

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
        .step-title {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .step-desc {
            font-size: 1em;
            margin-top: 20px;
            color: #6c757d;
            text-align: center;
        }
        .error-message {
            color: red;
            font-size: 0.875em;
            margin-top: 5px;
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
                                <img src="{{ asset(Storage::url('logo/tgs.png')) }}" class="navbar-brand-img big-logo" style="width: 80px; height: 80px;">
                            </a>
                            <p style="font-size:20px">{{__('Request A Price Quote')}}</p>
                        </div>
                        <div class="card shadow zindex-100 mb-0 form-wizard">
                            <!-- Added form-wizard class -->
                            {{ Form::open(array('route' => array('form.client.store'), 'method' => 'post')) }}
                            <div class="card-body px-md-5 py-5">
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
                                <div class="progress-container">
                                    <div class="progress-bar" id="progressBar"></div>
                                </div>
                                <div class="step active">
                                    <div class="step-title text-center">Request Services</div>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                {{ Form::label('category_service', __('Request For The Services You Need'), ['class' => 'form-label']) }}
                                                <select name="category_service" id="category_service" class="form-control main-element">
                                                    <option value="0">{{ __('Select Services') }}</option>
                                                    @foreach (\App\Models\ProjectOrders::$label as $k => $v)
                                                        <option value="{{ $k }}">{{ __($v) }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="error-message" id="category_service_error"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="step-desc">Page 1/3</div>
                                </div>
                                <div class="step">
                                    <div class="step-title text-center">Company Information</div>
                                    <br>
                                    <div class="row">
                                        {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('name', __('Company Name'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::text('name', null, array('class' => 'form-control', 'placeholder' => 'Enter Company Name', 'required')) }}
                                                <div class="error-message" id="name_error"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('email', __('Company Email'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::text('email', null, array('class' => 'form-control', 'placeholder' => 'Enter Company Email', 'required')) }}
                                                <div class="error-message" id="email_error"></div>
                                            </div>
                                        </div> --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::text('country', null, array('class' => 'form-control', 'placeholder' => 'Enter Country', 'required')) }}
                                                <div class="error-message" id="country_error"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('city', __('City'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::text('city', null, array('class' => 'form-control', 'placeholder' => 'Enter City', 'required')) }}
                                                <div class="error-message" id="city_error"></div>
                                            </div>
                                        </div>
                                        <div class="step-title text-center">Contact Person</div>
                                        <br>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('name_pic', __('Name'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::text('name_pic', null, array('class' => 'form-control', 'placeholder' => 'Enter Name', 'required')) }}
                                                <div class="error-message" id="name_pic_error"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('email_pic', __('Email'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::text('email_pic', null, array('class' => 'form-control', 'placeholder' => 'Enter Email', 'required')) }}
                                                <div class="error-message" id="email_pic_error"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('telp_pic', __('Phone'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::text('telp_pic', null, array('class' => 'form-control', 'placeholder' => 'Enter Phone', 'required')) }}
                                                <div class="error-message" id="telp_pic_error"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="step-desc">Page 2/3</div>
                                </div>
                                <div class="step">
                                    <div class="step-title text-center">Other Information</div>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('client_business_sector_id', __('Company Business Sector'), ['class' => 'form-label']) }}
                                                {{ Form::select('client_business_sector_id', $businesssector, null, array('class' => 'form-control select', 'required')) }}
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="client_ownership_id" style="display: none;">
                                            <div class="form-group">
                                                {{ Form::label('client_ownership_id', __('Company Ownership'), ['class' => 'form-label']) }}
                                                {{ Form::select('client_ownership_id', $ownership, null, array('class' => 'form-control select')) }}
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="accounting_standars_id" style="display: none;">
                                            <div class="form-group">
                                                {{ Form::label('accounting_standars_id', __('Accounting Standards'), ['class' => 'form-label']) }}
                                                {{ Form::select('accounting_standars_id', $accountingstandards, null, array('class' => 'form-control select')) }}
                                            </div>
                                        </div>
                                        <div class="col-md-6" id= "total_company_assets_value">
                                            <div class="form-group">
                                                {{ Form::label('total_company_assets_value', __('Total Company Assets'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::number('total_company_assets_value', null, array('class' => 'form-control', 'placeholder' => 'Enter Total Company Assets', 'required')) }}
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="periode" style="display: none;">
                                            <div class="form-group">
                                                {{ Form::label('periode', __('Periode Order'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                <input type="text" name="periode" class="form-control" placeholder="2023 / 2023 - 2024" required/>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('total_company_income_per_year', __('Total Company Revenue'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::number('total_company_income_per_year', null, array('class' => 'form-control', 'placeholder' => 'Enter Total Company Revenue', 'required')) }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('total_company_profit_or_loss', __('Total Company Profit Or Loss For The Year'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::number('total_company_profit_or_loss', null, array('class' => 'form-control', 'placeholder' => 'Enter Total Company Profit Or Loss For The Year', 'required')) }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('total_employee', __('Total Employee'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::number('total_employee', null, array('class' => 'form-control', 'placeholder' => 'Enter Total Employee', 'required')) }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('total_branch_offices', __('Total Branch Offices'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::number('total_branch_offices', null, array('class' => 'form-control', 'placeholder' => 'Enter Total Branch Offices', 'required')) }}
                                            </div>
                                        </div>
                                        {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('npwp', __('Tax Number'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::text('npwp', null, array('class' => 'form-control', 'placeholder' => 'Enter Tax Number', 'required')) }}
                                            </div>
                                        </div> --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('where_did_you_find_out_about_us', __('Where Did You Find Out About Us?'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                                {{ Form::select('where_did_you_find_out_about_us', [
                                                    'Internet' => 'Internet',
                                                    'Social Media' => 'Social Media',
                                                    'Website' => 'Website',
                                                    'Recommendation From Bank Mandiri' => 'Recommendation From Bank Mandiri',
                                                    'Other' => 'Other'
                                                ], null, array('class' => 'form-control select', 'placeholder' => 'Select an option')) }}
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                {{ Form::label('note', __('Notes'), ['class' => 'form-label']) }}
                                                {{ Form::textarea('note', null, array('class' => 'form-control', 'placeholder' => 'Enter Note')) }}
                                            </div>
                                        </div>
                                        @if(env('RECAPTCHA_MODULE') == 'yes')
                                            <div class="form-group mb-3">
                                                {!! NoCaptcha::display() !!}
                                                @error('g-recaptcha-response')
                                                <span class="small text-danger" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        @endif
                                    </div>
                                     <div class="step-desc">Page 3/3</div>
                                </div>
                                <div class="step-buttons">
                                    <button type="button" class="btn btn-primary" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
                                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextPrev(1)">Next</button>
                                    <button type="submit" class="btn btn-success" id="submitBtn">Submit</button>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3.1.0/daterangepicker.min.js"></script>

    <script>
        $(document).ready(function(){
            $('#category_service').change(function(){
                var selectedCategory = $(this).val();
                
                if(selectedCategory === 'Audit'){
                    $('#client_ownership_id').show();
                    $('#accounting_standars_id').show();
                } else {
                    $('#client_ownership_id').hide();
                    $('#accounting_standars_id').hide();
                }

                if(selectedCategory === 'KPPK' || selectedCategory === 'Agreed Upon Procedures (AUP)' || selectedCategory === 'Other') {
                    $('#periode').hide();
                } else {
                    $('#periode').show();
                }

                if(selectedCategory === 'KPPK') {
                    $('#total_company_assets_value').hide();
                } else {
                    $('#total_company_assets_value').show();
                }
            });
        });

    </script>
    <script>
        let currentStep = 0;
        showStep(currentStep);

        function showStep(n) {
            const steps = document.querySelectorAll('.step');
            steps.forEach((step, index) => {
                step.classList.remove('active');
                if (index === n) {
                    step.classList.add('active');
                }
            });

            document.getElementById('prevBtn').style.display = n === 0 ? 'none' : 'inline';
            document.getElementById('nextBtn').style.display = n === (steps.length - 1) ? 'none' : 'inline';
            document.getElementById('submitBtn').style.display = n === (steps.length - 1) ? 'inline' : 'none';

            updateProgressBar(n);
        }

        function nextPrev(n) {
            const steps = document.querySelectorAll('.step');
            if (n === 1 && !validateForm()) return false;
            currentStep += n;
            if (currentStep >= steps.length) {
                document.querySelector('form').submit();
                return false;
            }
            showStep(currentStep);
        }

        function validateForm() {
            let valid = true;
            const currentInputs = document.querySelectorAll('.step.active input[required], .step.active select[required]');
            currentInputs.forEach(input => {
                if (input.value === "") {
                    input.nextElementSibling.textContent = `${input.previousElementSibling.textContent} is required`;
                    valid = false;
                } else {
                    input.nextElementSibling.textContent = "";
                }
            });
            return valid;
        }

        function updateProgressBar(n) {
            const steps = document.querySelectorAll('.step');
            const progress = (n + 1) / steps.length * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var successMessage = "{{ session('success') }}";
            if (successMessage) {
                setTimeout(function () {
                    window.location.href = "http://au-partners.com";
                }, 3000);
            }
        });
    </script>

</body>
</html>
