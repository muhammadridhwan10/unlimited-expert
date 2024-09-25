<!DOCTYPE html>
@php
    $logo=asset(Storage::url('uploads/logo/'));
    $company_logo=Utility::getValByName('company_logo_dark');
    $company_logos=Utility::getValByName('company_logo_light');
    $company_favicon=Utility::getValByName('company_favicon');
    $setting = \App\Models\Utility::colorset();
    $color = (!empty($setting['color'])) ? $setting['color'] : 'theme-4';
    $SITE_RTL= isset($setting['SITE_RTL'])?$setting['SITE_RTL']:'off';
    $mode_setting = \App\Models\Utility::mode_layout();





@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{isset($setting['SITE_RTL']) && $setting['SITE_RTL'] == 'on' ? 'rtl' : '' }}">
<head>
    <title>{{(Utility::getValByName('title_text')) ? Utility::getValByName('title_text') : config('app.name', 'Unlimited Expert')}} - @yield('page-title')</title>

    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="description" content="Dashboard Template Description"/>
    <meta name="robots" content="noindex, nofollow">

    <!-- Favicon icon -->
    <link rel="icon" href="{{$logo.'/'.(isset($company_favicon) && !empty($company_favicon)?$company_favicon:'favicon.png')}}" sizes="100x100" type="image/x-icon"/>

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">

    @if ($setting['SITE_RTL'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css')}}" id="main-style-link">
        <link rel="stylesheet" href="{{ asset('assets/css/custom-auth-rtl.css') }}" id="main-style-link">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/custom-auth.css') }}" id="main-style-link">
    @endif
    @if( $setting['SITE_RTL'] != 'on' && $setting['cust_darklayout'] != 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif

    @if($setting['cust_darklayout']=='on')
         <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}" id="main-style-link">
        <link rel="stylesheet" href="{{ asset('assets/css/custom-auth-dark.css') }}" id="main-style-link">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    <style>
        .intro {
            position: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background-image: url('{{ asset('assets/images/auth/hiking.jpg') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            transition: 1s;
        }

        .intro img {
            top: 15vh;
            position: absolute;
            animation-name: flip;
            animation-duration: 2s;
            animation-timing-function: ease-in-out;
            animation-iteration-count: infinite;
        }


        .logo-intro {
            font-size: 2.5vw;
            color: #ffffff;
            animation: logoIntroAnimation 2s ease-in-out infinite;
        }

        .logo-parts {
            position: relative;
            display: inline-block;
            bottom: -50vh;
            opacity: 0;
            font-size: 50px;
            color: white;
            transition: ease-in-out 0.5s;
        }
		
		@media only screen and (max-width: 768px) {
			.logo-parts {
				font-size: 30px;
			}
		}

        .logo-parts.active {
            bottom: 60;
            opacity: 1;
        }

        .logo-parts.fade {
            bottom: 25vh;
            opacity: 0;
        }

        @keyframes logoIntroAnimation {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes flip {
            0% {
                transform: perspective(400px) rotateY(0);
            }

            100% {
                transform: perspective(400px) rotateY(360deg);
            }
        }
    </style>


</head>

<body class="{{ $color }}">
{{-- <div class="intro">
        <img src="{{ asset('assets/images/auth/favicon-expert.png') }}" style="width: 120px; height: 120px;">
        <h1 class="logo-intro">
            <span class="logo-parts">Welcome</span>
             <span class="logo-parts">to</span>
              <span class="logo-parts">Unlimited</span>
               <span class="logo-parts">Expert</span>
        </h1>
        <p style="font-size: 20px; color: white;">Simplify and Streamline Your Work.</p>
</div>  --}}
{{-- <div class="auth-wrapper auth-v3">
    <div class="bg-auth-side bg-primary">
        <img
                            src="{{ asset('assets/images/auth/background-working.png') }}"
                            alt=""
                            class="img-fluid"  style="width: 800px; height: 800px;"
                        />
    </div>
    <div class="auth-content">
        <nav class="navbar navbar-expand-md navbar-light default">
            <div class="container-fluid pe-2">
                <a class="navbar-brand" href="#">
                    @if($mode_setting['cust_darklayout'] && $mode_setting['cust_darklayout'] == 'on' )
                        <img src="{{ $logo . '/' . (isset($company_logos) && !empty($company_logos) ? $company_logos : 'logo-dark.png') }}"
                             alt="{{ config('app.name', 'TGS AU-Partners Apps-SaaS') }}" class="logo w-50">
                    @else
                        <img src="{{ $logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png') }}"
                             alt="{{ config('app.name', 'TGS AU-Partners Apps-SaaS') }}" class="logo w-50">
                    @endif
                </a>
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarTogglerDemo01"
                    aria-controls="navbarTogglerDemo01"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarTogglerDemo01" style="flex-grow: 0;">
                    <ul class="navbar-nav align-items-center ms-auto mb-2 mb-lg-0">
                        <!-- <li class="nav-item">
                            <a class="nav-link active" href="#">Support</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Terms</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Privacy</a>
                        </li> -->
                        @yield('auth-topbar')
                    </ul>

                </div>
            </div>
        </nav>
        <div class="card">
            <div class="row align-items-center text-start">
                <div class="col-xl-6">
                    <div class="card-body">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
        <div class="auth-footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-6">
                        <p class="">
                            {{(Utility::getValByName('footer_text')) ? Utility::getValByName('footer_text') :  __('Copyright TGS AU-Partners Apps') }} {{ date('Y') }}
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div> --}}
<div class="custom-login" style="background-image: url('{{ asset('assets/images/auth/working.jpg') }}'); background-size: cover; background-position: center;">
        {{-- <div class="login-bg-img">
            <img src="{{ asset('assets/images/auth/'.$color.'.svg') }}" class="login-bg-1">
            <img src="{{ asset('assets/images/auth/common.svg') }}" class="login-bg-2">
        </div> --}}
        {{-- <div class="bg-login bg-primary"></div> --}}
        <div class="custom-login-inner">
            <header class="dash-header">
                <nav class="navbar navbar-expand-md default">
                    <div class="container">
                        <div class="navbar-brand">
                            <a class="navbar-brand" href="{{ url('/') }}">
                            @if($mode_setting['cust_darklayout'] && $mode_setting['cust_darklayout'] == 'on' )
                                <img src="{{ $logo . '/' . (isset($company_logos) && !empty($company_logos) ? $company_logos : 'logo-dark.png') }}"
                                    alt="{{ config('app.name', 'Unlimited Expert') }}" class="logo w-50">
                            @else
                                <img src="{{ $logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png') }}"
                                    alt="{{ config('app.name', 'Unlimited Expert') }}" class="logo w-80" style="height: 50px;">
                            @endif
                            </a>
                        </div>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarlogin">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarlogin">
                            <ul class="navbar-nav align-items-center ms-auto mb-2 mb-lg-0">
                                <li class="nav-item">
                                    <a class="nav-link" target="_blank" href="https://aup-docs.au-partners.com/">{{ __('Documentation')}}</a>
                                </li>
                                {{-- <li class="nav-item">
                                    <a class="nav-link" href="#">{{ __('Terms')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">{{ __('Privacy')}}</a>
                                </li> --}}
                                @yield('language-bar')
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>
            <main class="custom-wrapper">
                <div class="custom-row">
                    <div class="card">
                        @yield('content')
                    </div>
                </div>
            </main>
            <footer>
                <div class="auth-footer">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <p style="color:white;" class="mb-0"> &copy;
                                    {{ date('Y') }} {{ Utility::getValByName('footer_text') ? Utility::getValByName('footer_text') : config('app.name', 'ERPGo') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
</div>
<!-- [ auth-signup ] end -->

<!-- Required Js -->
<script src="{{ asset('assets/js/vendor-all.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
<script>
    feather.replace();
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let intro = document.querySelector('.intro');
        let logoSpan = document.querySelectorAll('.logo-parts');

        setTimeout(() => {
            logoSpan.forEach((span, index) => {
                setTimeout(() => {
                    span.classList.add('active');
                }, (index + 1) * 100);
            });

            setTimeout(() => {
                logoSpan.forEach((span, index) => {
                    setTimeout(() => {
                        span.classList.remove('active');
                        span.classList.add('fade'); 
                    }, (index + 1) * 50);
                });
            }, 2000);

            setTimeout(() => {
                intro.style.top  = '-100vh';
            }, 2300);
        });
    });
</script>


<script>
    feather.replace();
    var pctoggle = document.querySelector("#pct-toggler");
    if (pctoggle) {
        pctoggle.addEventListener("click", function () {
            if (
                !document.querySelector(".pct-customizer").classList.contains("active")
            ) {
                document.querySelector(".pct-customizer").classList.add("active");
            } else {
                document.querySelector(".pct-customizer").classList.remove("active");
            }
        });
    }

    var themescolors = document.querySelectorAll(".themes-color > a");
    for (var h = 0; h < themescolors.length; h++) {
        var c = themescolors[h];

        c.addEventListener("click", function (event) {
            var targetElement = event.target;
            if (targetElement.tagName == "SPAN") {
                targetElement = targetElement.parentNode;
            }
            var temp = targetElement.getAttribute("data-value");
            removeClassByPrefix(document.querySelector("body"), "theme-");
            document.querySelector("body").classList.add(temp);
        });
    }



    var custthemebg = document.querySelector("#cust-theme-bg");
    custthemebg.addEventListener("click", function () {
        if (custthemebg.checked) {
            document.querySelector(".dash-sidebar").classList.add("transprent-bg");
            document
                .querySelector(".dash-header:not(.dash-mob-header)")
                .classList.add("transprent-bg");
        } else {
            document.querySelector(".dash-sidebar").classList.remove("transprent-bg");
            document
                .querySelector(".dash-header:not(.dash-mob-header)")
                .classList.remove("transprent-bg");
        }
    });

    var custdarklayout = document.querySelector("#cust-darklayout");
    custdarklayout.addEventListener("click", function () {
        if (custdarklayout.checked) {
            document
                .querySelector(".m-header > .b-brand > .logo-lg")
                .setAttribute("src", "{{ asset('assets/images/logo.svg') }}");
            document
                .querySelector("#main-style-link")
                .setAttribute("href", "{{ asset('assets/css/style-dark.css') }}");
        } else {
            document
                .querySelector(".m-header > .b-brand > .logo-lg")
                .setAttribute("src", "{{ asset('assets/images/logo-dark.png') }}");
            document
                .querySelector("#main-style-link")
                .setAttribute("href", "{{ asset('assets/css/style.css') }}");
        }
    });

    function removeClassByPrefix(node, prefix) {
        for (let i = 0; i < node.classList.length; i++) {
            let value = node.classList[i];
            if (value.startsWith(prefix)) {
                node.classList.remove(value);
            }
        }
    }
</script>
@stack('custom-scripts')
</body>
</html>
