@extends('layouts.admin')
@section('page-title')
    {{__('Settings')}}
@endsection
@php
    $logo=asset(Storage::url('uploads/logo/'));
    $logo_light = \App\Models\Utility::getValByName('company_logo_light');
    $logo_dark = \App\Models\Utility::getValByName('company_logo_dark');
    $company_favicon = \App\Models\Utility::getValByName('company_favicon');
    $lang=App\Models\Utility::getValByName('default_language');
    $setting = \App\Models\Utility::colorset();
    $color = (!empty($setting['color'])) ? $setting['color'] : 'theme-3';

      $SITE_RTL= $setting['SITE_RTL'];
     if (!empty($setting['SITE_RTL']))
     {
         $SITE_RTL == 'off';
     }
      $EmailTemplates     = App\Models\EmailTemplate::all();



@endphp
@push('scripts')
    <script>
        $(document).ready(function() {
            $('#gdpr_cookie').trigger('change');
        });
        $(document).on('change', '#gdpr_cookie', function(e) {
            $('.gdpr_cookie_text').hide();
            if ($("#gdpr_cookie").prop('checked') == true) {
                $('.gdpr_cookie_text').show();
            }
        });
    </script>
@endpush

@push('script-page')
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>

    <script>
        $(document).on("change", "select[name='invoice_template'], input[name='invoice_color']", function () {
            var template = $("select[name='invoice_template']").val();
            var color = $("input[name='invoice_color']:checked").val();
            $('#invoice_frame').attr('src', '{{url('/invoices/preview')}}/' + template + '/' + color);
        });

        $(document).on("change", "select[name='proposal_template'], input[name='proposal_color']", function () {
            var template = $("select[name='proposal_template']").val();
            var color = $("input[name='proposal_color']:checked").val();
            $('#proposal_frame').attr('src', '{{url('/proposal/preview')}}/' + template + '/' + color);
        });

        $(document).on("change", "select[name='bill_template'], input[name='bill_color']", function () {
            var template = $("select[name='bill_template']").val();
            var color = $("input[name='bill_color']:checked").val();
            $('#bill_frame').attr('src', '{{url('/bill/preview')}}/' + template + '/' + color);
        });
    </script>

    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300,
        })
        // $(".list-group-item").click(function(){
        //     $('.list-group-item').filter(function(){
        //         return this.href == id;
        //     }).parent().removeClass('text-primary');
        // });

        function check_theme(color_val) {
            $('#theme_color').prop('checked', false);
            $('input[value="' + color_val + '"]').prop('checked', true);
        }
    </script>

    <script>
        document.getElementById('company_logo_dark').onchange = function () {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image').src = src
        }
        document.getElementById('company_logo_light').onchange = function () {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image1').src = src
        }
        document.getElementById('company_favicon').onchange = function () {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image2').src = src
        }
    </script>

    <script type="text/javascript">

        $(document).on("click", ".email-template-checkbox", function () {
            var chbox = $(this);
            $.ajax({
                url: chbox.attr('data-url'),
                data: {_token: $('meta[name="csrf-token"]').attr('content'), status: chbox.val()},
                type: 'PUT',
                success: function (response) {
                    if (response.is_success) {
                        show_toastr('success', response.success, 'success');
                        if (chbox.val() == 1) {
                            $('#' + chbox.attr('id')).val(0);
                        } else {
                            $('#' + chbox.attr('id')).val(1);
                        }
                    } else {
                        show_toastr('Error', response.error, 'error');
                    }
                },
                error: function (response) {
                    response = response.responseJSON;
                    if (response.is_success) {
                        show_toastr('Error', response.error, 'error');
                    } else {
                        show_toastr('Error', response, 'error');
                    }
                }
            })
        });

    </script
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Settings')}}</li>
@endsection
@section('content')
    <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            <a href="#useradd-1" class="list-group-item list-group-item-action border-0">{{ __('Business Setting') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-2" class="list-group-item list-group-item-action border-0">{{ __('System Setting') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-3" class="list-group-item list-group-item-action border-0">{{ __('Company Setting') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-4" class="list-group-item list-group-item-action border-0">{{ __('Payment Setting') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-5" class="list-group-item list-group-item-action border-0">{{ __('Email Setting') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-6" class="list-group-item list-group-item-action border-0">{{ __('Pusher Setting') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-7" class="list-group-item list-group-item-action border-0">{{ __('Zoom Meeting Setting') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-8" class="list-group-item list-group-item-action border-0">{{ __('Slack Setting') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-9" class="list-group-item list-group-item-action border-0">{{ __('Telegram Setting') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-10" class="list-group-item list-group-item-action border-0">{{ __('Twillio Setting') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-11" class="list-group-item list-group-item-action border-0">{{ __('ReCaptcha Setting') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-12" class="list-group-item list-group-item-action border-0">{{ __('Email Notification ') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>


                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    <!--Business Setting-->
                    <div id="useradd-1" class="card">

                        {{Form::model($settings,array('route'=>'business.setting','method'=>'POST','enctype' => "multipart/form-data"))}}
                        <div class="card-header">
                            <h5>{{ __('Business Setting') }}</h5>
                            <small class="text-muted">{{ __('Edit details about your Company') }}</small>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-md-6">
                                    <div class="card logo_card">
                                        <div class="card-header">
                                            <h5>{{ __('Logo dark') }}</h5>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class=" setting-card">
{{--                                                @dd($logo_dark,$logo_light)--}}
                                                <div class="logo-content mt-4">
                                                    <img  id="image" src="{{$logo.'/'.(isset($logo_dark) && !empty($logo_dark)?$logo_dark:'logo-dark.png')}}"
                                                         class="big-logo">
                                                </div>
                                                <div class="choose-files mt-5">
                                                    <label for="company_logo_dark">
                                                        <div class=" bg-primary dark_logo_update"> <i
                                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" name="company_logo_dark" id="company_logo_dark" class="form-control file" data-filename="dark_logo_update">
                                                    </label>
                                                </div>
                                                @error('company_logo_dark')
                                                <div class="row">
                                                <span class="invalid-logo" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                                </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-md-6">
                                    <div class="card logo_card">
                                        <div class="card-header">
                                            <h5>{{ __('Logo Light') }}</h5>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class=" setting-card">
                                                <div class="logo-content mt-4">
                                                    <img id="image1" src="{{$logo.'/'.(isset($logo_light) && !empty($logo_light)?$logo_light:'logo-light.png')}}"
                                                         class="big-logo img_setting">
                                                </div>
                                                <div class="choose-files mt-5">
                                                    <label for="company_logo_light">
                                                        <div class=" bg-primary light_logo_update"> <i
                                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="form-control file" name="company_logo_light" id="company_logo_light"
                                                               data-filename="light_logo_update">
                                                    </label>
                                                </div>
                                                @error('company_logo_light')
                                                <div class="row">
                                                                        <span class="invalid-logo" role="alert">
                                                                            <strong class="text-danger">{{ $message }}</strong>
                                                                        </span>
                                                </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-md-6">
                                    <div class="card logo_card">
                                        <div class="card-header">
                                            <h5>{{ __('Favicon') }}</h5>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class=" setting-card">
                                                <div class="logo-content mt-4">
                                                    <img id="image2" src="{{$logo.'/'.(isset($company_favicon) && !empty($company_favicon)?$company_favicon:'favicon.png')}}" width="50px"
                                                         class="img_setting">
                                                </div>
                                                <div class="choose-files mt-5">
                                                    <label for="company_favicon">
                                                        <div class="bg-primary company_favicon_update"> <i
                                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="form-control file"  id="company_favicon" name="company_favicon"
                                                               data-filename="company_favicon_update">
                                                    </label>
                                                </div>
                                                @error('logo')
                                                <div class="row">
                                                    <span class="invalid-logo" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{Form::label('title_text',__('Title Text'),array('class'=>'form-label')) }}
                                            {{Form::text('title_text',null,array('class'=>'form-control','placeholder'=>__('Title Text')))}}
                                            @error('title_text')
                                            <span class="invalid-title_text" role="alert">
                                                     <strong class="text-danger">{{ $message }}</strong>
                                                 </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{Form::label('footer_text',__('Footer Text'),['class'=>'form-label']) }}
                                            {{Form::text('footer_text',Utility::getValByName('footer_text'),array('class'=>'form-control','placeholder'=>__('Enter Footer Text')))}}
                                            @error('footer_text')
                                            <span class="invalid-footer_text" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col">
                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-group">
                                                    {{Form::label('default_language',__('Default Language'),['class'=>'col-form-label text-dark']) }}
                                                    <div class="changeLanguage">

                                                        <select name="default_language" id="default_language" class="form-control select">
                                                            @foreach(\App\Models\Utility::languages() as $language)
                                                                <option @if($lang == $language) selected @endif value="{{$language }}">{{Str::upper($language)}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    @error('default_language')
                                                    <span class="invalid-default_language" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                                    @enderror
                                                </div>

                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label class="text-dark mb-1 mt-3" for="SITE_RTL">{{ __('RTL') }}</label>
                                                    <div class="">
                                                        <input type="checkbox" name="SITE_RTL" id="SITE_RTL" data-toggle="switchbutton" {{$settings['SITE_RTL'] == 'on' ? 'checked="checked"' : '' }} data-onstyle="primary">
                                                        <label class="form-check-labe" for="SITE_RTL"></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label class="text-dark mb-1 mt-3" for="display_landing_page">{{ __('Enable Landing Page') }}</label>
                                                    <div class="form-check form-switch d-inline-block">
                                                        <input type="checkbox" name="display_landing_page" class="form-check-input gdpr_fulltime gdpr_type" id="display_landing_page" data-toggle="switchbutton" {{ (Utility::getValByName('display_landing_page') == 'on') ? 'checked' : '' }} data-onstyle="primary">
                                                        <label class="form-check-label" for="display_landing_page"></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-3 my-auto">
                                                <div class="form-group">
                                                    <label class="text-dark mb-1 mt-3" for="gdpr_cookie">{{ __('GDPR Cookie') }}</label>
                                                    <div class="">
                                                        <input type="checkbox" class="gdpr_fulltime gdpr_type" name="gdpr_cookie" id="gdpr_cookie" data-toggle="switchbutton" {{ isset($settings['gdpr_cookie']) && $settings['gdpr_cookie'] == 'on' ? 'checked="checked"' : '' }} data-onstyle="primary">
                                                        <label class="form-check-label" for="gdpr_cookie"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row col-12">
                                    <div class="form-group">
                                            <div class="">
                                                {{Form::label('cookie_text',__('GDPR Cookie Text'),array('class'=>'fulltime form-label') )}}
                                                {!! Form::textarea('cookie_text',isset($settings['cookie_text']) && $settings['cookie_text'] ? $settings['cookie_text'] : '' , ['class'=>'form-control fulltime','style'=>'display: hidden;resize: none;','rows'=>'4']) !!}
                                            </div>
                                        </div>

                                </div>

                                <h4 class="small-title">{{__('Theme Customizer')}}</h4>
                                <div class="setting-card setting-logo-box p-3">
                                    <div class="row">
                                        <div class="col-lg-4 col-xl-4 col-md-4">
                                            <h6 class="mt-2 ">
                                                <i data-feather="credit-card" class="me-2"></i>{{ __('Primary color settings') }}
                                            </h6>

                                            <hr class="my-2 " />
                                            <div class="theme-color themes-color">
                                                <a href="#!" class="{{($settings['color'] == 'theme-1') ? 'active_color' : ''}}" data-value="theme-1" onclick="check_theme('theme-1')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-1" style="display: none;">
                                                <a href="#!" class="{{($settings['color'] == 'theme-2') ? 'active_color' : ''}} " data-value="theme-2" onclick="check_theme('theme-2')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-2" style="display: none;">
                                                <a href="#!" class="{{($settings['color'] == 'theme-3') ? 'active_color' : ''}}" data-value="theme-3" onclick="check_theme('theme-3')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-3" style="display: none;">
                                                <a href="#!" class="{{($settings['color'] == 'theme-4') ? 'active_color' : ''}}" data-value="theme-4" onclick="check_theme('theme-4')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-4" style="display: none;">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xl-4 col-md-4">
                                            <h6 class="mt-2 ">
                                                <i data-feather="layout" class="me-2"></i>{{__('Sidebar settings')}}
                                            </h6>
                                            <hr class="my-2 " />
                                            <div class="form-check form-switch">
                                                <input type="checkbox" class="form-check-input" id="cust-theme-bg" name="cust_theme_bg" {{ !empty($settings['cust_theme_bg']) && $settings['cust_theme_bg'] == 'on' ? 'checked' : '' }}/>
                                                <label class="form-check-label f-w-600 pl-1" for="cust-theme-bg"
                                                >{{__('Transparent layout')}}</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xl-4 col-md-4">
                                            <h6 class="mt-2 ">
                                                <i data-feather="sun" class="me-2"></i>{{__('Layout settings')}}
                                            </h6>
                                            <hr class="my-2 " />
                                            <div class="form-check form-switch mt-2">
                                                <input type="checkbox" class="form-check-input" id="cust-darklayout" name="cust_darklayout"{{ !empty($settings['cust_darklayout']) && $settings['cust_darklayout'] == 'on' ? 'checked' : '' }} />
                                                <label class="form-check-label f-w-600 pl-1" for="cust-darklayout">{{ __('Dark Layout') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <div class="form-group">
                                        <input class="btn btn-print-invoice btn-primary m-r-10" type="submit" value="{{__('Save Changes')}}">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                    <!--System Setting-->
                    <div id="useradd-2" class="card">
                        <div class="card-header">
                            <h5>{{ __('System Setting') }}</h5>
                            <small class="text-muted">{{ __('Edit details about your Company') }}</small>
                        </div>

                        {{Form::model($settings,array('route'=>'system.settings','method'=>'post'))}}
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {{Form::label('site_currency',__('Currency *'),array('class' => 'form-label')) }}
                                    {{Form::text('site_currency',null,array('class'=>'form-control font-style'))}}
                                    <small> {{ __('Note: Add currency code as per three-letter ISO code.') }}<br>
                                        <a href="https://stripe.com/docs/currencies"
                                           target="_blank">{{ __('you can find out here..') }}</a></small> <br>
                                    @error('site_currency')
                                    <span class="invalid-site_currency" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('site_currency_symbol',__('Currency Symbol *'),array('class' => 'form-label')) }}
                                    {{Form::text('site_currency_symbol',null,array('class'=>'form-control'))}}
                                    @error('site_currency_symbol')
                                    <span class="invalid-site_currency_symbol" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="form-label" for="example3cols3Input">{{__('Currency Symbol Position')}}</label>
                                    <div class="row">
                                        <div class="form-check col-md-6">
                                            <input class="form-check-input" type="radio" name="site_currency_symbol_position" value="pre" @if(@$settings['site_currency_symbol_position'] == 'pre') checked @endif
                                            id="flexCheckDefault">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                {{__('Pre')}}
                                            </label>
                                        </div>
                                        <div class="form-check col-md-6">
                                            <input class="form-check-input" type="radio" name="site_currency_symbol_position" value="post" @if(@$settings['site_currency_symbol_position'] == 'post') checked @endif
                                            id="flexCheckChecked" checked>
                                            <label class="form-check-label" for="flexCheckChecked">
                                                {{__('Post')}}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="site_date_format" class="form-label">{{__('Date Format')}}</label>
                                    <select type="text" name="site_date_format" class="form-control selectric" id="site_date_format">
                                        <option value="M j, Y" @if(@$settings['site_date_format'] == 'M j, Y') selected="selected" @endif>Jan 1,2015</option>
                                        <option value="d-m-Y" @if(@$settings['site_date_format'] == 'd-m-Y') selected="selected" @endif>d-m-y</option>
                                        <option value="m-d-Y" @if(@$settings['site_date_format'] == 'm-d-Y') selected="selected" @endif>m-d-y</option>
                                        <option value="Y-m-d" @if(@$settings['site_date_format'] == 'Y-m-d') selected="selected" @endif>y-m-d</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="site_time_format" class="form-label">{{__('Time Format')}}</label>
                                    <select type="text" name="site_time_format" class="form-control selectric" id="site_time_format">
                                        <option value="g:i A" @if(@$settings['site_time_format'] == 'g:i A') selected="selected" @endif>10:30 PM</option>
                                        <option value="g:i a" @if(@$settings['site_time_format'] == 'g:i a') selected="selected" @endif>10:30 pm</option>
                                        <option value="H:i" @if(@$settings['site_time_format'] == 'H:i') selected="selected" @endif>22:30</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('invoice_prefix',__('Invoice Prefix'),array('class'=>'form-label')) }}

                                    {{Form::text('invoice_prefix',null,array('class'=>'form-control'))}}
                                    @error('invoice_prefix')
                                    <span class="invalid-invoice_prefix" role="alert">
                                    <strong class="text-danger">{{ $message }}</strong>
                                </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    {{Form::label('invoice_starting_number',__('Invoice Starting Number'),array('class'=>'form-label')) }}
                                    {{Form::text('invoice_starting_number',null,array('class'=>'form-control'))}}
                                    @error('invoice_starting_number')
                                    <span class="invalid-invoice_starting_number" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('proposal_prefix',__('Proposal Prefix'),array('class'=>'form-label')) }}
                                    {{Form::text('proposal_prefix',null,array('class'=>'form-control'))}}
                                    @error('proposal_prefix')
                                    <span class="invalid-proposal_prefix" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('proposal_starting_number',__('Proposal Starting Number'),array('class'=>'form-label')) }}
                                    {{Form::text('proposal_starting_number',null,array('class'=>'form-control'))}}
                                    @error('proposal_starting_number')
                                    <span class="invalid-proposal_starting_number" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    {{Form::label('bill_prefix',__('Bill Prefix'),array('class'=>'form-label')) }}
                                    {{Form::text('bill_prefix',null,array('class'=>'form-control'))}}
                                    @error('bill_prefix')
                                    <span class="invalid-bill_prefix" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('bill_starting_number',__('Bill Starting Number'),array('class'=>'form-label')) }}
                                    {{Form::text('bill_starting_number',null,array('class'=>'form-control'))}}
                                    @error('bill_starting_number')
                                    <span class="invalid-bill_starting_number" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('customer_prefix',__('Customer Prefix'),array('class'=>'form-label')) }}
                                    {{Form::text('customer_prefix',null,array('class'=>'form-control'))}}
                                    @error('customer_prefix')
                                    <span class="invalid-customer_prefix" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('vender_prefix',__('Vender Prefix'),array('class'=>'form-label')) }}
                                    {{Form::text('vender_prefix',null,array('class'=>'form-control'))}}
                                    @error('vender_prefix')
                                    <span class="invalid-vender_prefix" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('footer_title',__('Invoice/Bill Footer Title'),array('class'=>'form-label')) }}
                                    {{Form::text('footer_title',null,array('class'=>'form-control'))}}
                                    @error('footer_title')
                                    <span class="invalid-footer_title" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    {{Form::label('decimal_number',__('Decimal Number Format'),array('class'=>'form-label')) }}
                                    {{Form::number('decimal_number', null, ['class'=>'form-control'])}}
                                    @error('decimal_number')
                                    <span class="invalid-decimal_number" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    {{Form::label('journal_prefix',__('Journal Prefix'),array('class'=>'form-label')) }}
                                    {{Form::text('journal_prefix',null,array('class'=>'form-control'))}}
                                    @error('journal_prefix')
                                    <span class="invalid-journal_prefix" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>


                                <div class="form-group col-md-6">
                                    {{Form::label('shipping_display',__('Shipping Display in Proposal / Invoice / Bill ?'),array('class'=>'form-label')) }}
                                    <div class=" form-switch form-switch-left">
                                        <input type="checkbox" class="form-check-input" name="shipping_display" id="email_tempalte_13" {{($settings['shipping_display']=='on')?'checked':''}} >
                                        <label class="form-check-label" for="email_tempalte_13"></label>
                                    </div>

                                    @error('shipping_display')
                                    <span class="invalid-shipping_display" role="alert">
                                    <strong class="text-danger">{{ $message }}</strong>
                                </span>
                                    @enderror
                                </div>





                                <div class="form-group col-md-6">
                                    {{Form::label('footer_notes',__('Invoice/Bill Footer Notes'),array('class'=>'form-label')) }}
                                    {{Form::textarea('footer_notes', null, ['class'=>'form-control','rows'=>'3'])}}
                                    @error('footer_notes')
                                    <span class="invalid-footer_notes" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>

                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="form-group">
                                <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit" value="{{__('Save Changes')}}">
                            </div>
                        </div>
                        {{Form::close()}}

                    </div>

                    <!--Company Setting-->
                    <div id="useradd-3" class="card">
                        <div class="card-header">
                            <h5>{{ __('Company Setting') }}</h5>
                            <small class="text-muted">{{ __('Edit details about your Company') }}</small>
                        </div>
                        {{Form::model($settings,array('route'=>'company.settings','method'=>'post'))}}
                        <div class="card-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {{Form::label('company_name *',__('Company Name *'),array('class' => 'form-label')) }}
                                    {{Form::text('company_name',null,array('class'=>'form-control font-style'))}}
                                    @error('company_name')
                                    <span class="invalid-company_name" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('company_address',__('Address'),array('class' => 'form-label')) }}
                                    {{Form::text('company_address',null,array('class'=>'form-control font-style'))}}
                                    @error('company_address')
                                    <span class="invalid-company_address" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('company_city',__('City'),array('class' => 'form-label')) }}
                                    {{Form::text('company_city',null,array('class'=>'form-control font-style'))}}
                                    @error('company_city')
                                    <span class="invalid-company_city" role="alert">
                                                                    <strong class="text-danger">{{ $message }}</strong>
                                                                </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('company_state',__('State'),array('class' => 'form-label')) }}
                                    {{Form::text('company_state',null,array('class'=>'form-control font-style'))}}
                                    @error('company_state')
                                    <span class="invalid-company_state" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('company_zipcode',__('Zip/Post Code'),array('class' => 'form-label')) }}
                                    {{Form::text('company_zipcode',null,array('class'=>'form-control'))}}
                                    @error('company_zipcode')
                                    <span class="invalid-company_zipcode" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group  col-md-6">
                                    {{Form::label('company_country',__('Country'),array('class' => 'form-label')) }}
                                    {{Form::text('company_country',null,array('class'=>'form-control font-style'))}}
                                    @error('company_country')
                                    <span class="invalid-company_country" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('company_telephone',__('Telephone'),array('class' => 'form-label')) }}
                                    {{Form::text('company_telephone',null,array('class'=>'form-control'))}}
                                    @error('company_telephone')
                                    <span class="invalid-company_telephone" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('company_email',__('System Email *'),array('class' => 'form-label')) }}
                                    {{Form::text('company_email',null,array('class'=>'form-control'))}}
                                    @error('company_email')
                                    <span class="invalid-company_email" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('company_email_from_name',__('Email (From Name) *'),array('class' => 'form-label')) }}
                                    {{Form::text('company_email_from_name',null,array('class'=>'form-control font-style'))}}
                                    @error('company_email_from_name')
                                    <span class="invalid-company_email_from_name" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{Form::label('registration_number',__('Company Registration Number *'),array('class' => 'form-label')) }}
                                    {{Form::text('registration_number',null,array('class'=>'form-control'))}}
                                    @error('registration_number')
                                    <span class="invalid-registration_number" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check form-check-inline form-group mb-3">
                                                    <input type="radio" id="customRadio8" name="tax_type" value="VAT" class="form-check-input" {{($settings['tax_type'] == 'VAT')?'checked':''}} >
                                                    <label class="form-check-label" for="customRadio8">{{__('VAT Number')}}</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-check-inline form-group mb-3">
                                                    <input type="radio" id="customRadio7" name="tax_type" value="GST" class="form-check-input" {{($settings['tax_type'] == 'GST')?'checked':''}}>
                                                    <label class="form-check-label" for="customRadio7">{{__('GST Number')}}</label>
                                                </div>
                                            </div>
                                        </div>
                                        {{Form::text('vat_number',null,array('class'=>'form-control','placeholder'=>__('Enter VAT / GST Number')))}}

                                    </div>
                                </div>

                                <div class="form-group col-md-6 mt-2">
                                    {{Form::label('timezone',__('Timezone'),array('class' => 'form-label'))}}
                                    <select type="text" name="timezone" class="form-control custom-select" id="timezone">
                                        <option value="">{{__('Select Timezone')}}</option>
                                        @foreach($timezones as $k=>$timezone)
                                            <option value="{{$k}}" {{(env('TIMEZONE')==$k)?'selected':''}}>{{$timezone}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    {{Form::label('company_start_time',__('Company Start Time *'),array('class' => 'form-label')) }}
                                    {{Form::time('company_start_time',null,array('class'=>'form-control'))}}
                                    @error('company_start_time')
                                    <span class="invalid-company_start_time" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    {{Form::label('company_end_time',__('Company End Time *'),array('class' => 'form-label')) }}
                                    {{Form::time('company_end_time',null,array('class'=>'form-control'))}}
                                    @error('company_end_time')
                                    <span class="invalid-company_end_time" role="alert">
                                                                    <strong class="text-danger">{{ $message }}</strong>
                                                                </span>
                                    @enderror
                                </div>


                            </div>

                        </div>
                        <div class="card-footer text-end">
                            <div class="form-group">
                                <input class="btn btn-print-invoice btn-primary m-r-10" type="submit" value="{{__('Save Changes')}}">
                            </div>
                        </div>
                        {{Form::close()}}

                    </div>

                    <!--Payment Setting-->
                    <div id="useradd-4" class="card">
                        <div class="card-header">
                            <h5>{{ __('Payment Setting') }}</h5>
                            <small class="text-muted">{{ __('This detail will use for collect payment on invoice from clients. On invoice client will find out pay now button based on your below configuration.') }}</small>
                        </div>
                        <div class="card-body">
                            {{Form::model($settings,['route'=>'company.payment.settings', 'method'=>'POST'])}}

                            @csrf

                            <div class="faq justify-content-center">
                                <div class="col-sm-12 col-md-10 col-xxl-12">
                                    <div class="accordion accordion-flush" id="accordionExample">

                                        <!-- Strip -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-2">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> {{ __('Stripe') }}
                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse1" class="accordion-collapse collapse"aria-labelledby="heading-2-2"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">

                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_stripe_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_stripe_enabled" id="is_stripe_enabled" {{ isset($company_payment_setting['is_stripe_enabled']) && $company_payment_setting['is_stripe_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                <label class="custom-control-label form-label" for="is_stripe_enabled">{{__('Enable')}}</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="stripe_key" class="col-form-label">{{__('Stripe Key')}}</label>
                                                                <input class="form-control" placeholder="{{__('Stripe Key')}}" name="stripe_key" type="text" value="{{(!isset($company_payment_setting['stripe_key']) || is_null($company_payment_setting['stripe_key'])) ? '' : $company_payment_setting['stripe_key']}}" id="stripe_key">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="stripe_secret" class="col-form-label">{{__('Stripe Secret')}}</label>
                                                                <input class="form-control " placeholder="{{ __('Stripe Secret') }}" name="stripe_secret" type="text" value="{{(!isset($company_payment_setting['stripe_secret']) || is_null($company_payment_setting['stripe_secret'])) ? '' : $company_payment_setting['stripe_secret']}}" id="stripe_secret">
                                                            </div
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Paypal -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-3">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="true" aria-controls="collapse2">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> {{ __('Paypal') }}
                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse2" class="accordion-collapse collapse"aria-labelledby="heading-2-3"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>



                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_paypal_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_paypal_enabled" id="is_paypal_enabled"  {{ isset($company_payment_setting['is_paypal_enabled']) && $company_payment_setting['is_paypal_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                <label class="custom-control-label form-label" for="is_paypal_enabled">{{__('Enable ')}}</label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12">
                                                            <label class="paypal-label col-form-label" for="paypal_mode">{{__('Paypal Mode')}}</label> <br>
                                                            <div class="d-flex">
                                                                <div class="mr-2" style="margin-right: 15px;">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labe text-dark {{isset($company_payment_setting['paypal_mode']) && $company_payment_setting['paypal_mode'] == 'sandbox' ? 'active' : ''}}">
                                                                                <input type="radio" name="paypal_mode" value="sandbox" {{ isset($company_payment_setting['paypal_mode']) && $company_payment_setting['paypal_mode'] == '' || isset($company_payment_setting['paypal_mode']) && $company_payment_setting['paypal_mode'] == 'sandbox' ? 'checked="checked"' : '' }}"
                                                                                       class="form-check-input" >

                                                                                {{__('Sandbox')}}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mr-2">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labe text-dark">
                                                                                <input type="radio" name="paypal_mode" value="live" class="form-check-input" {{ isset($company_payment_setting['paypal_mode']) && $company_payment_setting['paypal_mode'] == 'live' ? 'checked="checked"' : '' }}>

                                                                                {{__('Live')}}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paypal_client_id" class="col-form-label">{{ __('Client ID') }}</label>
                                                                <input type="text" name="paypal_client_id" id="paypal_client_id" class="form-control" value="{{(!isset($company_payment_setting['paypal_client_id']) || is_null($company_payment_setting['paypal_client_id'])) ? '' : $company_payment_setting['paypal_client_id']}}" placeholder="{{ __('Client ID') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paypal_secret_key" class="col-form-label">{{ __('Secret Key') }}</label>
                                                                <input type="text" name="paypal_secret_key" id="paypal_secret_key" class="form-control" value="{{(!isset($company_payment_setting['paypal_secret_key']) || is_null($company_payment_setting['paypal_secret_key'])) ? '' : $company_payment_setting['paypal_secret_key']}}" placeholder="{{ __('Secret Key') }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Paystack -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-4">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="true" aria-controls="collapse3">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> {{ __('Paystack') }}
                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse3" class="accordion-collapse collapse"aria-labelledby="heading-2-4"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_paystack_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_paystack_enabled" id="is_paystack_enabled" {{(isset($company_payment_setting['is_paystack_enabled']) && $company_payment_setting['is_paystack_enabled'] == 'on') ? 'checked' : ''}}>
                                                                <label class="custom-control-label form-label" for="is_paystack_enabled">{{__('Enable ')}}</label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paypal_client_id" class="col-form-label">{{ __('Public Key')}}</label>
                                                                <input type="text" name="paystack_public_key" id="paystack_public_key" class="form-control" value="{{(!isset($company_payment_setting['paystack_public_key']) || is_null($company_payment_setting['paystack_public_key'])) ? '' : $company_payment_setting['paystack_public_key']}}" placeholder="{{ __('Public Key')}}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paystack_secret_key" class="col-form-label">{{ __('Secret Key') }}</label>
                                                                <input type="text" name="paystack_secret_key" id="paystack_secret_key" class="form-control" value="{{(!isset($company_payment_setting['paystack_secret_key']) || is_null($company_payment_setting['paystack_secret_key'])) ? '' : $company_payment_setting['paystack_secret_key']}}" placeholder="{{ __('Secret Key') }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- FLUTTERWAVE -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-5">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="true" aria-controls="collapse4">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> {{ __('Flutterwave') }}
                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse4" class="accordion-collapse collapse"aria-labelledby="heading-2-5"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_flutterwave_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_flutterwave_enabled" id="is_flutterwave_enabled" {{(isset($company_payment_setting['is_flutterwave_enabled']) && $company_payment_setting['is_flutterwave_enabled'] == 'on') ? 'checked' : ''}}>
                                                                <label class="custom-control-label form-label" for="is_flutterwave_enabled">{{__('Enable ')}}</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paypal_client_id" class="col-form-label">{{ __('Public Key')}}</label>
                                                                <input type="text" name="flutterwave_public_key" id="flutterwave_public_key" class="form-control" value="{{(!isset($company_payment_setting['flutterwave_public_key']) || is_null($company_payment_setting['flutterwave_public_key'])) ? '' : $company_payment_setting['flutterwave_public_key']}}" placeholder="Public Key">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paystack_secret_key" class="col-form-label">{{ __('Secret Key') }}</label>
                                                                <input type="text" name="flutterwave_secret_key" id="flutterwave_secret_key" class="form-control" value="{{(!isset($company_payment_setting['flutterwave_secret_key']) || is_null($company_payment_setting['flutterwave_secret_key'])) ? '' : $company_payment_setting['flutterwave_secret_key']}}" placeholder="Secret Key">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Razorpay -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-6">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="true" aria-controls="collapse5">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> {{ __('Razorpay') }}
                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse5" class="accordion-collapse collapse"aria-labelledby="heading-2-6"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_razorpay_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_razorpay_enabled" id="is_razorpay_enabled" {{ isset($company_payment_setting['is_razorpay_enabled']) && $company_payment_setting['is_razorpay_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                <label class="custom-control-label form-label" for="is_razorpay_enabled">{{__('Enable ')}}</label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paypal_client_id" class="col-form-label">{{__('Public Key')}}</label>

                                                                <input type="text" name="razorpay_public_key" id="razorpay_public_key" class="form-control" value="{{(!isset($company_payment_setting['razorpay_public_key']) || is_null($company_payment_setting['razorpay_public_key'])) ? '' : $company_payment_setting['razorpay_public_key']}}" placeholder="Public Key">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paystack_secret_key" class="col-form-label">{{__('Secret Key')}}</label>
                                                                <input type="text" name="razorpay_secret_key" id="razorpay_secret_key" class="form-control" value="{{(!isset($company_payment_setting['razorpay_secret_key']) || is_null($company_payment_setting['razorpay_secret_key'])) ? '' : $company_payment_setting['razorpay_secret_key']}}" placeholder="Secret Key">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>



                                        <!-- Mercado Pago-->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-8">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="true" aria-controls="collapse7">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> {{ __('Mercado Pago') }}
                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse7" class="accordion-collapse collapse"aria-labelledby="heading-2-8"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_mercado_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_mercado_enabled" id="is_mercado_enabled" {{(isset($company_payment_setting['is_mercado_enabled']) && $company_payment_setting['is_mercado_enabled'] == 'on') ? 'checked' : ''}}>
                                                                <label class="custom-control-label form-label" for="is_mercado_enabled">{{__('Enable')}}</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 ">
                                                            <label class="coingate-label col-form-label" for="mercado_mode">{{__('Mercado Mode')}}</label> <br>
                                                            <div class="d-flex">
                                                                <div class="mr-2" style="margin-right: 15px;">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labe text-dark">
                                                                                <input type="radio" name="mercado_mode" value="sandbox" class="form-check-input" {{ isset($company_payment_setting['mercado_mode']) && $company_payment_setting['mercado_mode'] == '' || isset($company_payment_setting['mercado_mode']) && $company_payment_setting['mercado_mode'] == 'sandbox' ? 'checked="checked"' : '' }}>
                                                                                {{__('Sandbox')}}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mr-2">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labe text-dark">
                                                                                <input type="radio" name="mercado_mode" value="live" class="form-check-input" {{ isset($company_payment_setting['mercado_mode']) && $company_payment_setting['mercado_mode'] == 'live' ? 'checked="checked"' : '' }}>
                                                                                {{__('Live')}}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="mercado_access_token" class="col-form-label">{{ __('Access Token') }}</label>
                                                                <input type="text" name="mercado_access_token" id="mercado_access_token" class="form-control" value="{{isset($company_payment_setting['mercado_access_token']) ? $company_payment_setting['mercado_access_token']:''}}" placeholder="{{ __('Access Token') }}"/>
                                                                @if ($errors->has('mercado_secret_key'))
                                                                    <span class="invalid-feedback d-block">
                                                                            {{ $errors->first('mercado_access_token') }}
                                                                        </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Paytm -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-7">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="true" aria-controls="collapse6">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> {{ __('Paytm') }}
                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse6" class="accordion-collapse collapse"aria-labelledby="heading-2-7"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>

                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_paytm_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_paytm_enabled" id="is_paytm_enabled" {{ isset($company_payment_setting['is_paytm_enabled']) && $company_payment_setting['is_paytm_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                <label class="custom-control-label form-label" for="is_paytm_enabled">{{__('Enable')}}</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <label class="paypal-label col-form-label" for="paypal_mode">{{__('Paytm Environment')}}</label> <br>
                                                            <div class="d-flex">
                                                                <div class="mr-2" style="margin-right: 15px;">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labe text-dark">

                                                                                <input type="radio" name="paytm_mode" value="local" class="form-check-input" {{ !isset($company_payment_setting['paytm_mode']) || $company_payment_setting['paytm_mode'] == '' || $company_payment_setting['paytm_mode'] == 'local' ? 'checked="checked"' : '' }}>

                                                                                {{__('Local')}}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mr-2">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labe text-dark">
                                                                                <input type="radio" name="paytm_mode" value="production" class="form-check-input" {{ isset($company_payment_setting['paytm_mode']) && $company_payment_setting['paytm_mode'] == 'production' ? 'checked="checked"' : '' }}>

                                                                                {{__('Production')}}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="paytm_public_key" class="col-form-label">{{__('Industry Type')}}</label>
                                                                <input type="text" name="paytm_merchant_id" id="paytm_merchant_id" class="form-control" value="{{(!isset($company_payment_setting['paytm_merchant_id']) || is_null($company_payment_setting['paytm_merchant_id'])) ? '' : $company_payment_setting['paytm_merchant_id']}}" placeholder="Merchant ID">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="paytm_secret_key" class="col-form-label">{{__('Merchant Key')}}</label>
                                                                <input type="text" name="paytm_merchant_key" id="paytm_merchant_key" class="form-control" value="{{(!isset($company_payment_setting['paytm_merchant_key']) || is_null($company_payment_setting['paytm_merchant_key'])) ? '' : $company_payment_setting['paytm_merchant_key']}}" placeholder="Merchant Key">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="paytm_industry_type" class="col-form-label">{{__('Industry Type')}}</label>
                                                                <input type="text" name="paytm_industry_type" id="paytm_industry_type" class="form-control" value="{{(!isset($company_payment_setting['paytm_industry_type']) || is_null($company_payment_setting['paytm_industry_type'])) ? '' : $company_payment_setting['paytm_industry_type']}}" placeholder="Industry Type">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mollie -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-9">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="true" aria-controls="collapse8">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> {{ __('Mollie') }}
                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse8" class="accordion-collapse collapse"aria-labelledby="heading-2-9"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_mollie_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_mollie_enabled" id="is_mollie_enabled" {{(isset($company_payment_setting['is_mollie_enabled']) && $company_payment_setting['is_mollie_enabled'] == 'on') ? 'checked' : ''}}>
                                                                <label class="custom-control-label form-label" for="is_mollie_enabled">{{__('Enable')}}</label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="mollie_api_key" class="col-form-label">{{ __('Mollie Api Key') }}</label>
                                                                <input type="text" name="mollie_api_key" id="mollie_api_key" class="form-control" value="{{(!isset($company_payment_setting['mollie_api_key']) || is_null($company_payment_setting['mollie_api_key'])) ? '' : $company_payment_setting['mollie_api_key']}}" placeholder="Mollie Api Key">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="mollie_profile_id" class="col-form-label">{{ __('Mollie Profile Id') }}</label>
                                                                <input type="text" name="mollie_profile_id" id="mollie_profile_id" class="form-control" value="{{(!isset($company_payment_setting['mollie_profile_id']) || is_null($company_payment_setting['mollie_profile_id'])) ? '' : $company_payment_setting['mollie_profile_id']}}" placeholder="Mollie Profile Id">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="mollie_partner_id" class="col-form-label">{{ __('Mollie Partner Id') }}</label>
                                                                <input type="text" name="mollie_partner_id" id="mollie_partner_id" class="form-control" value="{{(!isset($company_payment_setting['mollie_partner_id']) || is_null($company_payment_setting['mollie_partner_id'])) ? '' : $company_payment_setting['mollie_partner_id']}}" placeholder="Mollie Partner Id">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Skrill -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-10">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse9" aria-expanded="true" aria-controls="collapse9">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> {{ __('Skrill') }}
                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse9" class="accordion-collapse collapse"aria-labelledby="heading-2-10"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_skrill_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_skrill_enabled" id="is_skrill_enabled" {{(isset($company_payment_setting['is_skrill_enabled']) && $company_payment_setting['is_skrill_enabled'] == 'on') ? 'checked' : ''}}>
                                                                <label class="custom-control-label form-label" for="is_skrill_enabled">{{__('Enable')}}</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="mollie_api_key" class="col-form-label">{{__('Skrill Email')}}</label>
                                                                <input type="text" name="skrill_email" id="skrill_email" class="form-control" value="{{(!isset($company_payment_setting['skrill_email']) || is_null($company_payment_setting['skrill_email'])) ? '' : $company_payment_setting['skrill_email']}}" placeholder="Enter Skrill Email">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- CoinGate -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-11">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse10" aria-expanded="true" aria-controls="collapse10">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> {{ __('CoinGate') }}
                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse10" class="accordion-collapse collapse"aria-labelledby="heading-2-11"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_coingate_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_coingate_enabled" id="is_coingate_enabled" {{(isset($company_payment_setting['is_coingate_enabled']) && $company_payment_setting['is_coingate_enabled'] == 'on') ? 'checked' : ''}}>
                                                                <label class="custom-control-label form-label" for="is_coingate_enabled">{{__('Enable')}}</label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12">
                                                            <label class="col-form-label" for="coingate_mode">{{__('CoinGate Mode')}}</label> <br>
                                                            <div class="d-flex">
                                                                <div class="mr-2" style="margin-right: 15px;">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labe text-dark">

                                                                                <input type="radio" name="coingate_mode" value="sandbox" class="form-check-input" {{ !isset($company_payment_setting['coingate_mode']) || $company_payment_setting['coingate_mode'] == '' || $company_payment_setting['coingate_mode'] == 'sandbox' ? 'checked="checked"' : '' }}>

                                                                                {{__('Sandbox')}}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mr-2">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labe text-dark">
                                                                                <input type="radio" name="coingate_mode" value="live" class="form-check-input" {{ isset($company_payment_setting['coingate_mode']) && $company_payment_setting['coingate_mode'] == 'live' ? 'checked="checked"' : '' }}>
                                                                                {{__('Live')}}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="coingate_auth_token" class="col-form-label">{{__('CoinGate Auth Token')}}</label>
                                                                <input type="text" name="coingate_auth_token" id="coingate_auth_token" class="form-control" value="{{(!isset($company_payment_setting['coingate_auth_token']) || is_null($company_payment_setting['coingate_auth_token'])) ? '' : $company_payment_setting['coingate_auth_token']}}" placeholder="CoinGate Auth Token">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- PaymentWall -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-12">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse11" aria-expanded="true" aria-controls="collapse11">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> {{ __('PaymentWall') }}
                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse11" class="accordion-collapse collapse"aria-labelledby="heading-2-12"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_paymentwall_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_paymentwall_enabled" id="is_paymentwall_enabled" {{(isset($company_payment_setting['is_paymentwall_enabled']) && $company_payment_setting['is_paymentwall_enabled'] == 'on') ? 'checked' : ''}}>
                                                                <label class="custom-control-label form-label" for="is_paymentwall_enabled">{{__('Enable ')}}</label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paymentwall_public_key" class="col-form-label">{{ __('Public Key')}}</label>
                                                                <input type="text" name="paymentwall_public_key" id="paymentwall_public_key" class="form-control" value="{{(!isset($company_payment_setting['paymentwall_public_key']) || is_null($company_payment_setting['paymentwall_public_key'])) ? '' : $company_payment_setting['paymentwall_public_key']}}" placeholder="{{ __('Public Key')}}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paymentwall_private_key" class="col-form-label">{{ __('Private Key') }}</label>
                                                                <input type="text" name="paymentwall_private_key" id="paymentwall_private_key" class="form-control" value="{{(!isset($company_payment_setting['paymentwall_private_key']) || is_null($company_payment_setting['paymentwall_private_key'])) ? '' : $company_payment_setting['paymentwall_private_key']}}" placeholder="{{ __('Private Key') }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <div class="form-group">
                                    <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit" value="{{__('Save Changes')}}">
                                </div>
                            </div>
                            </form>
                        </div>

                    </div>

                    <!--Email Setting-->
                    <div id="useradd-5" class="card">
                        <div class="card-header">
                            <h5>{{ __('Email Setting') }}</h5>
                        </div>
                        <div class="card-body">
                            {{Form::open(array('route'=>'email.settings','method'=>'post'))}}
                            @csrf

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_driver', __('Mail Driver'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_driver', env('MAIL_DRIVER'), ['class' => 'form-control', 'placeholder' => __('Enter Mail Driver')]) }}
                                        @error('mail_driver')
                                        <span class="invalid-mail_driver" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_host', __('Mail Host'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_host', env('MAIL_HOST'), ['class' => 'form-control ', 'placeholder' => __('Enter Mail Host')]) }}
                                        @error('mail_host')
                                        <span class="invalid-mail_driver" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_port', __('Mail Port'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_port', env('MAIL_PORT'), ['class' => 'form-control', 'placeholder' => __('Enter Mail Port')]) }}
                                        @error('mail_port')
                                        <span class="invalid-mail_port" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_username', __('Mail Username'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_username', env('MAIL_USERNAME'), ['class' => 'form-control', 'placeholder' => __('Enter Mail Username')]) }}
                                        @error('mail_username')
                                        <span class="invalid-mail_username" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_password', __('Mail Password'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_password', env('MAIL_PASSWORD'), ['class' => 'form-control', 'placeholder' => __('Enter Mail Password')]) }}
                                        @error('mail_password')
                                        <span class="invalid-mail_password" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_encryption', __('Mail Encryption'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_encryption', env('MAIL_ENCRYPTION'), ['class' => 'form-control', 'placeholder' => __('Enter Mail Encryption')]) }}
                                        @error('mail_encryption')
                                        <span class="invalid-mail_encryption" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>



                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_from_address', __('Mail From Address'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_from_address', env('MAIL_FROM_ADDRESS'), ['class' => 'form-control', 'placeholder' => __('Enter Mail From Address')]) }}
                                        @error('mail_from_address')
                                        <span class="invalid-mail_from_address" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_from_name', __('Mail From Name'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_from_name', env('MAIL_FROM_NAME'), ['class' => 'form-control', 'placeholder' => __('Enter Mail From Name')]) }}
                                        @error('mail_from_name')
                                        <span class="invalid-mail_from_name" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="card-footer d-flex justify-content-end">
                                    <div class="form-group me-2">
                                        <a href="#" data-url="{{ route('test.mail') }}" data-ajax-popup="true"
                                           data-title="{{ __('Send Test Mail') }}" class="btn btn-primary ">
                                            {{ __('Send Test Mail') }}
                                        </a>
                                    </div>


                                    <div class="form-group">
                                        <input class="btn btn-primary" type="submit" value="{{__('Save Changes')}}">
                                    </div>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>

                    <!--Pusher Setting-->
                    <div id="useradd-6" class="card">
                        <div class="card-header">
                            <h5>{{ __('Pusher Setting') }}</h5>
                        </div>
                        <div class="card-body">
                            {{Form::model($settings,array('route'=>'pusher.setting','method'=>'post'))}}
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{Form::label('pusher_app_id',__('Pusher App Id'),array('class'=>'form-label')) }}
                                        {{Form::text('pusher_app_id',env('PUSHER_APP_ID'),array('class'=>'form-control font-style'))}}
                                        @error('pusher_app_id')
                                        <span class="invalid-pusher_app_id" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{Form::label('pusher_app_key',__('Pusher App Key'),array('class'=>'form-label')) }}
                                        {{Form::text('pusher_app_key',env('PUSHER_APP_KEY'),array('class'=>'form-control font-style'))}}
                                        @error('pusher_app_key')
                                        <span class="invalid-pusher_app_key" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                        @enderror
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{Form::label('pusher_app_secret',__('Pusher App Secret'),array('class'=>'form-label')) }}
                                        {{Form::text('pusher_app_secret',env('PUSHER_APP_SECRET'),array('class'=>'form-control font-style'))}}
                                        @error('pusher_app_secret')
                                        <span class="invalid-pusher_app_secret" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{Form::label('pusher_app_cluster',__('Pusher App Cluster'),array('class'=>'form-label')) }}
                                        {{Form::text('pusher_app_cluster',env('PUSHER_APP_CLUSTER'),array('class'=>'form-control font-style'))}}
                                        @error('pusher_app_cluster')
                                        <span class="invalid-pusher_app_cluster" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                        @enderror
                                    </div>
                                </div>

                            </div>
                            <div class="card-footer text-end">
                                <div class="form-group">
                                    <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit" value="{{__('Save Changes')}}">
                                </div>
                            </div>

                            {{ Form::close() }}
                        </div>
                    </div>

                    <!--Zoom - Metting Setting-->
                    <div id="useradd-7" class="card">
                        <div class="card-header">
                            <h5>{{ __('Zoom-Meeting Setting') }}</h5>
                            <small class="text-muted">{{ __('Edit details about your Company zoom-meeting setting') }}</small>
                        </div>

                        <div class="card-body">
                            {{Form::model($settings,array('route'=>'zoom.settings','method'=>'post'))}}
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="form-control-label">{{__('Zoom API Key')}}</label> <br>
                                    <small>{{__("Zoom API Key.")}}</small>
                                    {{ Form::text('zoom_apikey',isset($settings['zoom_apikey'])?$settings['zoom_apikey']:'', ['class' => 'form-control', 'placeholder' => __('Enter Zoom API Key')]) }}
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-control-label">{{__('Zoom API Secret')}}</label> <br>
                                    <small>{{__("Zoom API Secret.")}}</small>
                                    {{ Form::text('zoom_apisecret',isset($settings['zoom_apisecret'])?$settings['zoom_apisecret']:'', ['class' => 'form-control', 'placeholder' => __('Enter Zoom API Secret')]) }}
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <div class="form-group">
                                    <input class="btn btn-print-invoice btn-primary m-r-10" type="submit" value="{{__('Save Changes')}}">
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>

                    </div>

                    <!--Slack Setting-->
                    <div id="useradd-8" class="card">
                        <div class="card-header">
                            <h5>{{ __('Slack Setting') }}</h5>
                            <small class="text-muted">{{ __('Edit details about your Company slack setting') }}</small>
                        </div>

                        <div class="card-body">
                            {{ Form::open(['route' => 'slack.settings','id'=>'slack-setting','method'=>'post' ,'class'=>'d-contents']) }}

                            <div class="form-group col-md-12">
                                <label class="form-label">{{__('Slack Webhook URL')}}</label> <br>
                                {{ Form::text('slack_webhook', isset($settings['slack_webhook']) ?$settings['slack_webhook'] :'', ['class' => 'form-control w-100', 'placeholder' => __('Enter Slack Webhook URL'), 'required' => 'required']) }}
                            </div>


                            <div class="col-md-12 mt-5 mb-2">
                                <h5 class="small-title">{{__('Module Setting')}}</h5>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Lead create')}}</span>
                                                {{Form::checkbox('lead_notification', '1',isset($settings['lead_notification']) && $settings['lead_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'lead_notification'))}}
                                                <label class="form-check-label" for="lead_notification"></label>

                                            </div>

                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Deal create')}}</span>
                                                {{Form::checkbox('deal_notification', '1',isset($settings['deal_notification']) && $settings['deal_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'deal_notification'))}}
                                                <label class="form-check-label" for="deal_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Lead To Deal convert')}}</span>
                                                {{Form::checkbox('leadtodeal_notification', '1',isset($settings['leadtodeal_notification']) && $settings['leadtodeal_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'leadtodeal_notification'))}}
                                                <label class="form-check-label" for="leadtodeal_notification"></label>

                                            </div>

                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Contract create')}}</span>
                                                {{Form::checkbox('contract_notification', '1',isset($settings['contract_notification']) && $settings['contract_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'contract_notification'))}}
                                                <label class="form-check-label" for="contract_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Project create')}}</span>
                                                {{Form::checkbox('project_notification', '1',isset($settings['project_notification']) && $settings['project_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'project_notification'))}}
                                                <label class="form-check-label" for="project_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Task create')}}</span>
                                                {{Form::checkbox('task_notification', '1',isset($settings['task_notification']) && $settings['task_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'task_notification'))}}
                                                <label class="form-check-label" for="task_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Task Move create')}}</span>
                                                {{Form::checkbox('taskmove_notification', '1',isset($settings['taskmove_notification']) && $settings['taskmove_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'taskmove_notification'))}}
                                                <label class="form-check-label" for="taskmove_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Task Comment')}}</span>
                                                {{Form::checkbox('taskcomment_notification', '1',isset($settings['taskcomment_notification']) && $settings['taskcomment_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'taskcomment_notification'))}}
                                                <label class="form-check-label" for="taskcomment_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                            </div>

                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Monthly Payslip Generate')}}</span>
                                                {{Form::checkbox('payslip_notification', '1',isset($settings['payslip_notification']) && $settings['payslip_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'payslip_notification'))}}
                                                <label class="form-check-label" for="payslip_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Award create')}}</span>
                                                {{Form::checkbox('award_notification', '1',isset($settings['award_notification']) && $settings['award_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'award_notification'))}}
                                                <label class="form-check-label" for="award_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Announcement create')}}</span>
                                                {{Form::checkbox('announcement_notification', '1',isset($settings['announcement_notification']) && $settings['announcement_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'announcement_notification'))}}
                                                <label class="form-check-label" for="announcement_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Holiday create')}}</span>
                                                {{Form::checkbox('holiday_notification', '1',isset($settings['holiday_notification']) && $settings['holiday_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'holiday_notification'))}}
                                                <label class="form-check-label" for="holiday_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Support create')}}</span>
                                                {{Form::checkbox('support_notification', '1',isset($settings['support_notification']) && $settings['support_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'support_notification'))}}
                                                <label class="form-check-label" for="support_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Event create')}}</span>
                                                {{Form::checkbox('event_notification', '1',isset($settings['event_notification']) && $settings['event_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'event_notification'))}}
                                                <label class="form-check-label" for="event_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Meeting create')}}</span>
                                                {{Form::checkbox('meeting_notification', '1',isset($settings['meeting_notification']) && $settings['meeting_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'meeting_notification'))}}
                                                <label class="form-check-label" for="meeting_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Company Policy create')}}</span>
                                                {{Form::checkbox('policy_notification', '1',isset($settings['policy_notification']) && $settings['policy_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'policy_notification'))}}
                                                <label class="form-check-label" for="policy_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                            </div>

                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Invoice create')}}</span>
                                                {{Form::checkbox('invoice_notification', '1',isset($settings['invoice_notification']) && $settings['invoice_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'invoice_notification'))}}
                                                <label class="form-check-label" for="invoice_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Revenue create')}}</span>
                                                {{Form::checkbox('revenue_notification', '1',isset($settings['revenue_notification']) && $settings['revenue_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'revenue_notification'))}}
                                                <label class="form-check-label" for="revenue_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Bill create')}}</span>
                                                {{Form::checkbox('bill_notification', '1',isset($settings['bill_notification']) && $settings['bill_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'bill_notification'))}}
                                                <label class="form-check-label" for="bill_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Payment create')}}</span>
                                                {{Form::checkbox('payment_notification', '1',isset($settings['payment_notification']) && $settings['payment_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'payment_notification'))}}
                                                <label class="form-check-label" for="payment_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Budget create')}}</span>
                                                {{Form::checkbox('budget_notification', '1',isset($settings['budget_notification']) && $settings['budget_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'budget_notification'))}}
                                                <label class="form-check-label" for="budget_notification"></label>
                                            </div>
                                        </li>

                                    </ul>
                                </div>
                            </div>


                            <div class="card-footer text-end">
                                <div class="form-group">
                                    <input class="btn btn-print-invoice btn-primary m-r-10" type="submit" value="{{__('Save Changes')}}">
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>

                    </div>

                    <!--Telegram Setting-->
                    <div id="useradd-9" class="card">
                        <div class="card-header">
                            <h5>{{ __('Telegram Setting') }}</h5>
                            <small class="text-muted">{{ __('Edit details about your Company telegram setting') }}</small>
                        </div>

                        <div class="card-body">
                            {{ Form::open(['route' => 'telegram.settings','id'=>'telegram-setting','method'=>'post' ,'class'=>'d-contents']) }}
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="form-label">{{__('Telegram AccessToken')}}</label> <br>
                                    {{ Form::text('telegram_accestoken',isset($settings['telegram_accestoken'])?$settings['telegram_accestoken']:'', ['class' => 'form-control', 'placeholder' => __('Enter Telegram AccessToken')]) }}
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-label ">{{__('Telegram ChatID')}}</label> <br>
                                    {{ Form::text('telegram_chatid',isset($settings['telegram_chatid'])?$settings['telegram_chatid']:'', ['class' => 'form-control', 'placeholder' => __('Enter Telegram ChatID')]) }}
                                </div>
                            </div>


                            <div class="col-md-12 mt-5 mb-2">
                                <h5 class="small-title">{{__('Module Setting')}}</h5>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Lead create')}}</span>
                                                {{Form::checkbox('telegram_lead_notification', '1',isset($settings['telegram_lead_notification']) && $settings['telegram_lead_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_lead_notification'))}}
                                                <label class="form-check-label" for="telegram_lead_notification"></label>

                                            </div>

                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Deal create')}}</span>
                                                {{Form::checkbox('telegram_deal_notification', '1',isset($settings['telegram_deal_notification']) && $settings['telegram_deal_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_deal_notification'))}}
                                                <label class="form-check-label" for="telegram_deal_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Lead To Deal convert')}}</span>
                                                {{Form::checkbox('telegram_leadtodeal_notification', '1',isset($settings['telegram_leadtodeal_notification']) && $settings['telegram_leadtodeal_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_leadtodeal_notification'))}}
                                                <label class="form-check-label" for="telegram_leadtodeal_notification"></label>

                                            </div>

                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Contract create')}}</span>
                                                {{Form::checkbox('telegram_contract_notification', '1',isset($settings['telegram_contract_notification']) && $settings['telegram_contract_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_contract_notification'))}}
                                                <label class="form-check-label" for="telegram_contract_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Project create')}}</span>
                                                {{Form::checkbox('telegram_project_notification', '1',isset($settings['telegram_project_notification']) && $settings['telegram_project_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_project_notification'))}}
                                                <label class="form-check-label" for="telegram_project_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Task create')}}</span>
                                                {{Form::checkbox('telegram_task_notification', '1',isset($settings['telegram_task_notification']) && $settings['telegram_task_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_task_notification'))}}
                                                <label class="form-check-label" for="telegram_task_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Task Move create')}}</span>
                                                {{Form::checkbox('telegram_taskmove_notification', '1',isset($settings['telegram_taskmove_notification']) && $settings['telegram_taskmove_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_taskmove_notification'))}}
                                                <label class="form-check-label" for="telegram_taskmove_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Task Comment')}}</span>
                                                {{Form::checkbox('telegram_taskcomment_notification', '1',isset($settings['telegram_taskcomment_notification']) && $settings['telegram_taskcomment_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_taskcomment_notification'))}}
                                                <label class="form-check-label" for="telegram_taskcomment_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                            </div>

                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Monthly Payslip Generate')}}</span>
                                                {{Form::checkbox('telegram_payslip_notification', '1',isset($settings['telegram_payslip_notification']) && $settings['telegram_payslip_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_payslip_notification'))}}
                                                <label class="form-check-label" for="telegram_payslip_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Award create')}}</span>
                                                {{Form::checkbox('telegram_award_notification', '1',isset($settings['telegram_award_notification']) && $settings['telegram_award_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_award_notification'))}}
                                                <label class="form-check-label" for="telegram_award_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Announcement create')}}</span>
                                                {{Form::checkbox('telegram_announcement_notification', '1',isset($settings['telegram_announcement_notification']) && $settings['telegram_announcement_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_announcement_notification'))}}
                                                <label class="form-check-label" for="telegram_announcement_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Holiday create')}}</span>
                                                {{Form::checkbox('telegram_holiday_notification', '1',isset($settings['telegram_holiday_notification']) && $settings['telegram_holiday_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_holiday_notification'))}}
                                                <label class="form-check-label" for="telegram_holiday_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Support create')}}</span>
                                                {{Form::checkbox('telegram_support_notification', '1',isset($settings['telegram_support_notification']) && $settings['telegram_support_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_support_notification'))}}
                                                <label class="form-check-label" for="telegram_support_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Event create')}}</span>
                                                {{Form::checkbox('telegram_event_notification', '1',isset($settings['telegram_event_notification']) && $settings['telegram_event_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_event_notification'))}}
                                                <label class="form-check-label" for="telegram_event_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Meeting create')}}</span>
                                                {{Form::checkbox('telegram_meeting_notification', '1',isset($settings['telegram_meeting_notification']) && $settings['telegram_meeting_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_meeting_notification'))}}
                                                <label class="form-check-label" for="telegram_meeting_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Company Policy create')}}</span>
                                                {{Form::checkbox('telegram_policy_notification', '1',isset($settings['telegram_policy_notification']) && $settings['telegram_policy_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_policy_notification'))}}
                                                <label class="form-check-label" for="telegram_policy_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                            </div>

                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Invoice create')}}</span>
                                                {{Form::checkbox('telegram_invoice_notification', '1',isset($settings['telegram_invoice_notification']) && $settings['telegram_invoice_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_invoice_notification'))}}
                                                <label class="form-check-label" for="telegram_invoice_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Revenue create')}}</span>
                                                {{Form::checkbox('telegram_revenue_notification', '1',isset($settings['telegram_revenue_notification']) && $settings['telegram_revenue_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_revenue_notification'))}}
                                                <label class="form-check-label" for="telegram_revenue_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Bill create')}}</span>
                                                {{Form::checkbox('telegram_bill_notification', '1',isset($settings['telegram_bill_notification']) && $settings['telegram_bill_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_bill_notification'))}}
                                                <label class="form-check-label" for="telegram_bill_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Payment create')}}</span>
                                                {{Form::checkbox('telegram_payment_notification', '1',isset($settings['telegram_payment_notification']) && $settings['telegram_payment_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_payment_notification'))}}
                                                <label class="form-check-label" for="telegram_payment_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Budget create')}}</span>
                                                {{Form::checkbox('telegram_budget_notification', '1',isset($settings['telegram_budget_notification']) && $settings['telegram_budget_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'telegram_budget_notification'))}}
                                                <label class="form-check-label" for="telegram_budget_notification"></label>
                                            </div>
                                        </li>

                                    </ul>
                                </div>
                            </div>


                            <div class="card-footer text-end">
                                <div class="form-group">
                                    <input class="btn btn-print-invoice btn-primary m-r-10" type="submit" value="{{__('Save Changes')}}">
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>

                    </div>

                    <!--Twilio Setting-->
                    <div id="useradd-10" class="card">
                        <div class="card-header">
                            <h5>{{ __('Twilio Setting') }}</h5>
                            <small class="text-muted">{{ __('Edit details about your Company twilio setting') }}</small>
                        </div>

                        <div class="card-body">
                            {{Form::model($settings,array('route'=>'twilio.setting','method'=>'post'))}}
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{Form::label('twilio_sid',__('Twilio SID '),array('class'=>'form-label')) }}
                                        {{ Form::text('twilio_sid', isset($settings['twilio_sid']) ?$settings['twilio_sid'] :'', ['class' => 'form-control w-100', 'placeholder' => __('Enter Twilio SID'), 'required' => 'required']) }}
                                        @error('twilio_sid')
                                        <span class="invalid-twilio_sid" role="alert">
                                        <strong class="text-danger">{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{Form::label('twilio_token',__('Twilio Token'),array('class'=>'form-label')) }}
                                        {{ Form::text('twilio_token', isset($settings['twilio_token']) ?$settings['twilio_token'] :'', ['class' => 'form-control w-100', 'placeholder' => __('Enter Twilio Token'), 'required' => 'required']) }}
                                        @error('twilio_token')
                                        <span class="invalid-twilio_token" role="alert">
                                        <strong class="text-danger">{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{Form::label('twilio_from',__('Twilio From'),array('class'=>'form-label')) }}
                                        {{ Form::text('twilio_from', isset($settings['twilio_from']) ?$settings['twilio_from'] :'', ['class' => 'form-control w-100', 'placeholder' => __('Enter Twilio From'), 'required' => 'required']) }}
                                        @error('twilio_from')
                                        <span class="invalid-twilio_from" role="alert">
                                        <strong class="text-danger">{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>


                                <div class="col-md-12 mt-4 mb-2">
                                    <h5 class="small-title">{{__('Module Setting')}}</h5>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Customer create')}}</span>
                                                {{Form::checkbox('customer_notification', '1',isset($settings['customer_notification']) && $settings['customer_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'customer_notification'))}}
                                                <label class="form-check-label" for="customer_notification"></label>
                                            </div>

                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Vendor create')}}</span>
                                                {{Form::checkbox('vender_notification', '1',isset($settings['vender_notification']) && $settings['vender_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'vender_notification'))}}
                                                <label class="form-check-label" for="vender_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Invoice Create')}}</span>
                                                {{Form::checkbox('invoice_notification', '1',isset($settings['invoice_notification']) && $settings['invoice_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'invoice_notification'))}}
                                                <label class="form-check-label" for="invoice_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Revenue create')}}</span>
                                                {{Form::checkbox('revenue_notification', '1',isset($settings['revenue_notification']) && $settings['revenue_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'revenue_notification'))}}
                                                <label class="form-check-label" for="revenue_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Bill Create')}}</span>
                                                {{Form::checkbox('bill_notification', '1',isset($settings['bill_notification']) && $settings['bill_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'bill_notification'))}}
                                                <label class="form-check-label" for="bill_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Proposal create')}}</span>
                                                {{Form::checkbox('proposal_notification', '1',isset($settings['proposal_notification']) && $settings['proposal_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'proposal_notification'))}}
                                                <label class="form-check-label" for="proposal_notification"></label>
                                            </div>
                                        </li>

                                    </ul>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Payment Create')}}</span>
                                                {{Form::checkbox('payment_notification', '1',isset($settings['payment_notification']) && $settings['payment_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'payment_notification'))}}
                                                <label class="form-check-label" for="payment_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{__('Invoice Reminder')}}</span>
                                                {{Form::checkbox('reminder_notification', '1',isset($settings['reminder_notification']) && $settings['reminder_notification'] == '1' ?'checked':'',array('class'=>'form-check-input','id'=>'reminder_notification'))}}
                                                <label class="form-check-label" for="reminder_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>



                            </div>
                            <div class="card-footer text-end">
                                <div class="form-group">
                                    <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit" value="{{__('Save Changes')}}">
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>

                    </div>

                    <!--ReCaptcha Setting-->
                    <div id="useradd-11" class="card">
                        <div class="card-header">
                            <h5>{{ __('ReCaptcha Setting') }}</h5>
                        </div>

                        <div class="card-body">
                            <form method="POST" action="{{ route('recaptcha.settings.store') }}" accept-charset="UTF-8">
                                @csrf
                                <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                    <div class=" form-switch">
                                        <input type="checkbox" class="form-check-input" name="recaptcha_module" id="recaptcha_module" value="yes" {{ env('RECAPTCHA_MODULE') == 'yes' ? 'checked="checked"' : '' }}>
                                        <label class="form-check-label" for="recaptcha_module">
                                            {{ __('Google Recaptcha') }}
                                            <a href="https://phppot.com/php/how-to-get-google-recaptcha-site-and-secret-key/" target="_blank" class="text-blue">
                                                <small>({{__('How to Get Google reCaptcha Site and Secret key')}})</small>
                                            </a>
                                        </label>
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="google_recaptcha_key" class="form-label">{{ __('Google Recaptcha Key') }}</label>
                                            <input class="form-control" placeholder="{{ __('Enter Google Recaptcha Key') }}" name="google_recaptcha_key" type="text" value="{{env('NOCAPTCHA_SITEKEY')}}" id="google_recaptcha_key">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="google_recaptcha_secret" class="form-label">{{ __('Google Recaptcha Secret') }}</label>
                                            <input class="form-control" placeholder="{{ __('Enter Google Recaptcha Secret') }}" name="google_recaptcha_secret" type="text" value="{{env('NOCAPTCHA_SECRET')}}" id="google_recaptcha_secret">
                                        </div>
                                    </div>



                                </div>
                                <div class="card-footer text-end">
                                    <div class="form-group">
                                        <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit" value="{{__('Save Changes')}}">
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>

                    </div>

                    <!--Email Notification Setting-->
                    <div id="useradd-12" class="card">

                        <div class="col-md-12">

                            <div class="card-header">
                                <h5>{{ __('Email Notification') }}</h5>
                                <small class="text-muted">{{ __('Edit details about your Company email notification setting') }}</small>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <!-- <div class=""> -->
                                    @foreach ($EmailTemplates as $EmailTemplate)
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            <div class="list-group">
                                                <div class="list-group-item form-switch form-switch-right">
                                                    <label class="form-label" style="margin-left:5%;">{{ $EmailTemplate->name }}</label>

                                                    <input class="form-check-input email-template-checkbox" id="email_tempalte_{{$EmailTemplate->template->id}}" type="checkbox" @if($EmailTemplate->template->is_active == 1) checked="checked" @endif type="checkbox" value="{{$EmailTemplate->template->is_active == 1 ? 1 : 0}}"
                                                           data-url="{{route('status.email.language',[$EmailTemplate->template->id])}}" />
                                                    <label class="form-check-label" for="email_tempalte_{{$EmailTemplate->template->id}}"></label>


                                                <!-- <label class="form-check-label form-switch form-switch-right">
                                                            <input type="checkbox" class="form-check-input " id="email_tempalte_{{$EmailTemplate->template->id}}"
                                                            @if($EmailTemplate->template->is_active == 1) checked="checked" @endif type="checkbox" value="{{$EmailTemplate->template->is_active}}"
                                                            data-url="{{route('status.email.language',[$EmailTemplate->template->id])}}"/>
                                                            <span class="slider1 round"></span>
                                                        </label> -->
                                                </div>
                                            </div>
                                        </div>
                                @endforeach
                                <!-- </div> -->
                                </div>
                            <!-- <div class="card-footer p-0">
                                    <div class="col-sm-12 mt-3 px-2">
                                        <div class="text-end">
                                            <input class="btn btn-print-invoice  btn-primary " type="submit" value="{{__('Save Changes')}}">
                                        </div>
                                    </div>

                                </div> -->
                            </div>
                        </div>
                        <!-- </form>  -->
                    </div>







                </div>

            </div>

        </div>
@endsection
