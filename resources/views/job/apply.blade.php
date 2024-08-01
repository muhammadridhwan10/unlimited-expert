<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{!empty($companySettings['title_text']) ? $companySettings['title_text']->value : config('app.name', 'Unlimited Expert')}} - {{$job->title}}</title>

    <link rel="icon" href="{{asset(Storage::url('uploads/logo/')).'/'.(isset($companySettings['company_favicon']) && !empty($companySettings['company_favicon'])?$companySettings['company_favicon']->value:'favicon.png')}}" type="image" sizes="16x16">
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">

    <link rel="stylesheet" href="{{ asset('css/site.css') }}" id="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
<header class="header header-transparent" id="header-main">

    <nav class="navbar navbar-main navbar-expand-lg navbar-transparent navbar-light bg-white" id="navbar-main">
        <div class="container px-lg-0">
            <a class="navbar-brand mr-lg-5" href="https://au-partners.com/">
                <img class="hweb" alt="Image placeholder" src="{{asset(Storage::url('uploads/logo/')).'/'.(isset($companySettings['company_logo']) && !empty($companySettings['company_logo'])?$companySettings['company_logo']->value:'logo-dark.png')}}" id="navbar-logo" style="height: 50px;">
            </a>
            <button class="navbar-toggler pr-0" type="button" data-toggle="collapse" data-target="#navbar-main-collapse" aria-controls="navbar-main-collapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            {{-- <div class="collapse navbar-collapse" id="navbar-main-collapse">

                <ul class="navbar-nav align-items-lg-center ml-lg-auto">
                    <li class="nav-item">
                        <div class="dropdown global-icon" data-toggle="tooltip" data-original-titla="{{__('Choose Language')}}">
                            <a class="nav-link px-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-offset="0,10">
                                <i class="ti ti-globe-europe"></i>
                                <span class="d-none d-lg-inline-block">{{\Str::upper($currantLang)}}</span>
                            </a>

                            <div class="dropdown-menu  dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                @foreach($languages as $language)
                                    <a class="dropdown-item @if($language == $currantLang) text-danger @endif" href="{{route('job.apply',[$job->code,$language])}}">{{\Str::upper($language)}}</a>
                                @endforeach
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div> --}}
    </nav>
</header>

<div class="main-content">
    <!-- Spotlight -->
    <section class="slice" data-offset-top="#header-main" style="background-image: url('{{ asset('assets/images/career/career-page.png') }}'); background-size: cover; background-position: center; height: 500px;">
        <div class="container pt-5">
            <div class="row row-grid justify-content-center">
                <div class="col-lg-10">
                    <h2 class="h1 mb-4" style="color:white">{{$job->title}}</h2>
                    <p class="lead lh-180 text-muted">
                        @foreach(explode(',',$job->skill) as $skill)
                            <span class="badge bg-secondary p-2 px-3 rounded text-dark"> {{$skill}}</span>
                        @endforeach
                    </p>
                    @if(!empty($job->branches)?$job->branches->name:'')
                        <p class="lead" style="color:white"><i class="ti ti-map-pin"></i> {{!empty($job->branches)?$job->branches->name:''}} </p>
                    @endif

                </div>
            </div>
        </div>
    </section>

    <section class="slice bg-secondary">
        <div class="container">
            <div class="mb-5 text-center">
                <h3 class="mt-2">{{__('Apply for this job')}}</h3>
            </div>


            <div class="row justify-content-center">
                <div class="col-lg-8 justify-content-center">
                    <div class="card">
                        <div class="card-body">
                            {{Form::open(array('route'=>array('job.apply.data',$job->code),'method'=>'post', 'enctype' => "multipart/form-data"))}}
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
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {{Form::label('name',__('Name'),['class'=>'form-label'])}}<span class="text-danger">*</span>
                                    {{Form::text('name',null,array('class'=>'form-control name','required'=>'required'))}}
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('email',__('Email'),['class'=>'form-label'])}}<span class="text-danger">*</span>
                                    {{Form::text('email',null,array('class'=>'form-control','required'=>'required'))}}
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('phone',__('Phone'),['class'=>'form-label'])}}<span class="text-danger">*</span>
                                    {{Form::number('phone',null,array('class'=>'form-control','required'=>'required'))}}
                                </div>
                                @if(!empty($job->applicant) && in_array('dob',explode(',',$job->applicant)))
                                    <div class="form-group col-md-6 ">
                                        {!! Form::label('dob', __('Date of Birth'),['class'=>'form-label']) !!}<span class="text-danger">*</span>
                                        {!! Form::date('dob', old('dob'), ['class' => 'form-control datepicker','required'=>'required']) !!}
                                    </div>
                                @endif
                                @if(!empty($job->applicant) && in_array('gender',explode(',',$job->applicant)))
                                    <div class="form-group col-md-6 ">
                                        {!! Form::label('gender', __('Gender'),['class'=>'form-label']) !!}<span class="text-danger">*</span>
                                        <div class="d-flex radio-check">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="g_male" value="Male" name="gender" class="custom-control-input" >
                                                <label class="custom-control-label" for="g_male">{{__('Male')}}</label>
                                            </div>
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="g_female" value="Female" name="gender" class="custom-control-input">
                                                <label class="custom-control-label" for="g_female">{{__('Female')}}</label>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($job->applicant) && in_array('country',explode(',',$job->applicant)))
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
                                    <div class="form-group col-md-6 city">
                                        {{Form::label('city',__('City'),['class'=>'form-label'])}}<span class="text-danger">*</span>
                                        <select id="city" class="form-control">
                                            <option value="">Select City</option>
                                        </select>
                                    </div>
                                @endif
                                @if(!empty($job->applicant) && in_array('latest_education',explode(',',$job->applicant)))
                                    <div class="form-group col-md-6 ">
                                        {!! Form::label('latest_education', __('Latest Education'),['class'=>'form-label']) !!}<span class="text-danger">*</span>
                                        {!! Form::text('latest_education',null,array('class'=>'form-control')) !!}
                                    </div>
                                @endif
                                @if(!empty($job->applicant) && in_array('year_graduated',explode(',',$job->applicant)))
                                    <div class="form-group col-md-6 ">
                                        {!! Form::label('year_graduated', __('Year Graduated'),['class'=>'form-label']) !!}<span class="text-danger">*</span>
                                        {!! Form::text('year_graduated',null,array('class'=>'form-control')) !!}
                                    </div>
                                @endif
                                @if(!empty($job->applicant) && in_array('university',explode(',',$job->applicant)))
                                    <div class="form-group col-md-6 ">
                                        {!! Form::label('university', __('Univercity'),['class'=>'form-label']) !!}<span class="text-danger">*</span>
                                        <select id="university" name="university" class="form-control select2">
                                            <option value="">Select Univercity</option>
                                            @foreach($univercity as $university)
                                                <option value="{{ $university->name }}">{{ $university->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                @if(!empty($job->applicant) && in_array('major',explode(',',$job->applicant)))
                                    <div class="form-group col-md-6 ">
                                        {!! Form::label('major', __('College Major'),['class'=>'form-label']) !!}<span class="text-danger">*</span>
                                        {!! Form::text('major',null,array('class'=>'form-control')) !!}
                                    </div>
                                @endif
                                 @if(!empty($job->applicant) && in_array('ipk',explode(',',$job->applicant)))
                                    <div class="form-group col-md-6 ">
                                        {!! Form::label('ipk', __('Final Ipk or Temporary Ipk'),['class'=>'form-label']) !!}<span class="text-danger">*</span><span> example (3.41)</span>
                                        {!! Form::text('ipk',null,array('class'=>'form-control')) !!}
                                    </div>
                                @endif
                                @if(!empty($job->applicant) && in_array('latest_work_experience',explode(',',$job->applicant)))
                                    <div class="form-group col-md-6 ">
                                        {!! Form::label('latest_work_experience', __('Latest Work Experience'),['class'=>'form-label']) !!}<span class="text-danger">*</span>
                                        {!! Form::text('latest_work_experience',null,array('class'=>'form-control')) !!}
                                    </div>
                                @endif
                                @if(!empty($job->applicant) && in_array('length_of_last_job',explode(',',$job->applicant)))
                                    <div class="form-group col-md-6 ">
                                        {!! Form::label('length_of_last_job', __('Length of Last Job'),['class'=>'form-label']) !!}<span class="text-danger">*</span>
                                        {!! Form::text('length_of_last_job',null,array('class'=>'form-control')) !!}
                                    </div>
                                @endif
                                @if(!empty($job->visibility) && in_array('profile',explode(',',$job->visibility)))
                                    <div class="form-group col-md-6 ">
                                        {{Form::label('profile',__('Photo Profile'),['class'=>'col-form-label'])}}<span class="text-danger">*</span>
                                        {{--                                                <label for="profile" class="form-label">--}}
                                        <input type="file" class="form-control" accept=".png, .jpg, .jpeg" name="profile" id="profile" data-filename="profile_create" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                                        {{--                                                </label>--}}
                                        <img id="blah" src="" class="mt-3" width="25%"/>
                                        <p class="profile_create"></p>
                                    </div>

                                @endif
                                @if(!empty($job->visibility) && in_array('resume',explode(',',$job->visibility)))
                                    <div class="form-group col-md-6 ">
                                        {{Form::label('resume',__('CV / Resume'),['class'=>'col-form-label'])}}<span class="text-danger">*</span>

                                        {{--                                                <label for="resume" class="form-label">--}}
                                        <input type="file" accept=".png, .jpg, .jpeg, .pdf" class="form-control" name="resume" id="resume" data-filename="resume_create" onchange="document.getElementById('blah1').src = window.URL.createObjectURL(this.files[0])" required>
                                        {{--                                                </label>--}}
                                        <img id="blah1" class="mt-3" src="" width="25%"/>
                                        <p class="resume_create"></p>

                                    </div>
                                @endif
                                @if(!empty($job->visibility) && in_array('kk',explode(',',$job->visibility)))
                                    <div class="form-group col-md-6 ">
                                        {{Form::label('kk',__('KK'),['class'=>'col-form-label'])}}<span class="text-danger">*</span>

                                        {{--                                                <label for="resume" class="form-label">--}}
                                        <input type="file" accept=".png, .jpg, .jpeg, .pdf" class="form-control" name="kk" id="kk" data-filename="kk_create" onchange="document.getElementById('blah2').src = window.URL.createObjectURL(this.files[0])" required>
                                        {{--                                                </label>--}}
                                        <img id="blah2" class="mt-3" src="" width="25%"/>
                                        <p class="kk_create"></p>

                                    </div>
                                @endif
                                @if(!empty($job->visibility) && in_array('ktp',explode(',',$job->visibility)))
                                    <div class="form-group col-md-6 ">
                                        {{Form::label('ktp',__('KTP'),['class'=>'col-form-label'])}}<span class="text-danger">*</span>

                                        {{--                                                <label for="resume" class="form-label">--}}
                                        <input type="file" accept=".png, .jpg, .jpeg, .pdf" class="form-control" name="ktp" id="ktp" data-filename="ktp_create" onchange="document.getElementById('blah3').src = window.URL.createObjectURL(this.files[0])" required>
                                        {{--                                                </label>--}}
                                        <img id="blah3" class="mt-3" src="" width="25%"/>
                                        <p class="ktp_create"></p>

                                    </div>
                                @endif
                                @if(!empty($job->visibility) && in_array('transkrip_nilai',explode(',',$job->visibility)))
                                    <div class="form-group col-md-6 ">
                                        {{Form::label('transkrip_nilai',__('Final Grade Transcript'),['class'=>'col-form-label'])}}<span class="text-danger">*</span>

                                        {{--                                                <label for="resume" class="form-label">--}}
                                        <input type="file" accept=".png, .jpg, .jpeg, .pdf" class="form-control" name="transkrip_nilai" id="transkrip_nilai" data-filename="transkrip_nilai_create" onchange="document.getElementById('blah4').src = window.URL.createObjectURL(this.files[0])" required>
                                        {{--                                                </label>--}}
                                        <img id="blah4" class="mt-3" src="" width="25%"/>
                                        <p class="transkrip_nilai_create"></p>

                                    </div>
                                @endif
                                @if(!empty($job->visibility) && in_array('ijazah',explode(',',$job->visibility)))
                                    <div class="form-group col-md-6 ">
                                        {{Form::label('ijazah',__('Ijazah'),['class'=>'col-form-label'])}}

                                        {{--                                                <label for="resume" class="form-label">--}}
                                        <input type="file" accept=".png, .jpg, .jpeg, .pdf" class="form-control" name="ijazah" id="ijazah" data-filename="ijazah_create" onchange="document.getElementById('blah5').src = window.URL.createObjectURL(this.files[0])">
                                        {{--                                                </label>--}}
                                        <img id="blah5" class="mt-3" src="" width="25%"/>
                                        <p class="ijazah_create"></p>

                                    </div>
                                @endif
                                @if(!empty($job->visibility) && in_array('certificate',explode(',',$job->visibility)))
                                    <div class="form-group col-md-6 ">
                                        {{Form::label('certificate',__('Certificate'),['class'=>'col-form-label'])}}<span class="text-danger">*</span>

                                        {{--                                                <label for="resume" class="form-label">--}}
                                        <input type="file" accept=".png, .jpg, .jpeg, .pdf" class="form-control" name="certificate" id="certificate" data-filename="certificate_create" onchange="document.getElementById('blah6').src = window.URL.createObjectURL(this.files[0])" required>
                                        {{--                                                </label>--}}
                                        <img id="blah6" class="mt-3" src="" width="25%"/>
                                        <p class="certificate_create"></p>

                                    </div>
                                @endif
                                @if(!empty($job->visibility) && in_array('letter',explode(',',$job->visibility)))
                                    <div class="form-group col-md-12 ">
                                        {{Form::label('cover_letter',__('Cover Letter'),['class'=>'form-label'])}}<span class="text-danger">*</span>
                                        {{Form::textarea('cover_letter',null,array('class'=>'form-control','rows'=>'3'))}}
                                    </div>
                                @endif
                                @foreach($questions as $question)
                                    <div class="form-group col-md-12  question question_{{$question->id}}">
                                        {{Form::label($question->question,$question->question,['class'=>'form-label'])}}<span class="text-danger">*</span>
                                        <input type="text" class="form-control" name="question[{{$question->question}}]" {{($question->is_required=='yes')?'required':''}}>
                                    </div>
                                @endforeach
                                {{Form::hidden('selected_country', '', array('id' => 'selected_country'))}}
                                {{Form::hidden('selected_state', '', array('id' => 'selected_state'))}}
                                {{Form::hidden('selected_city', '', array('id' => 'selected_city'))}}
                                <div class="col-12">
                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary">{{__('Submit your application')}}</button>
                                    </div>
                                </div>
                            </div>
                            {{Form::close()}}
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </section>
</div>

<footer id="footer-main">
    <div class="footer-dark">
        <div class="container">
            <div class="row align-items-center justify-content-md-between py-4 mt-4 delimiter-top">
                <div class="col-md-6">
                    <div class="copyright text-sm font-weight-bold text-center text-md-left">
                        {{ !empty($companySettings['footer_text']) ? $companySettings['footer_text']->value : 'Unlimited Expert' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <ul class="nav justify-content-center justify-content-md-end mt-3 mt-md-0">
                        <li class="nav-item">
                            <a class="nav-link" href="#" target="_blank">
                                <i class="ti ti-brand-dribbble"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" target="_blank">
                                <i class="ti ti-brand-instagram"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" target="_blank">
                                <i class="ti ti-brand-github"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" target="_blank">
                                <i class="ti ti-brand-facebook"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
<script src="{{ asset('js/site.core.js') }}"></script>
{{--<script src="{{ asset('js/autosize/dist/autosize.min.js') }}"></script>--}}
<script src="{{ asset('js/moment.min.js') }}"></script>
{{--<script src="{{ asset('libs/bootstrap-daterangepicker/daterangepicker.js') }}"></script>--}}
<script src="{{ asset('js/site.js') }}"></script>
<script src="{{ asset('js/demo.js') }} "></script>


<script>
    function show_toastr(title, message, type) {
        var o, i;
        var icon = '';
        var cls = '';

        if (type == 'success') {
            icon = 'ti ti-check-circle';
            cls = 'success';
        } else {
            icon = 'ti ti-times-circle';
            cls = 'danger';
        }

        $.notify({icon: icon, title: " " + title, message: message, url: ""}, {
            element: "body",
            type: cls,
            allow_dismiss: !0,
            placement: {from: 'top', align: 'right'},
            offset: {x: 15, y: 15},
            spacing: 10,
            z_index: 1080,
            delay: 2500,
            timer: 2000,
            url_target: "_blank",
            mouse_over: !1,
            animate: {enter: o, exit: i},
            template: '<div class="alert alert-{0} alert-icon alert-group alert-notify" data-notify="container" role="alert"><div class="alert-group-prepend alert-content"><span class="alert-group-icon"><i data-notify="icon"></i></span></div><div class="alert-content"><strong data-notify="title">{1}</strong><div data-notify="message">{2}</div></div><button type="button" class="close" data-notify="dismiss" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'
        });
    }
    if ($(".datepicker").length) {
        $('.datepicker').daterangepicker({
            singleDatePicker: true,
            format: 'yyyy-mm-dd',
        });
    }

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
@if($message = Session::get('success'))
    <script>
        show_toastr('Success', '{!! $message !!}', 'success');
    </script>
@endif
@if($message = Session::get('error'))
    <script>
        show_toastr('Error', '{!! $message !!}', 'error');
    </script>
@endif
</body>

</html>
