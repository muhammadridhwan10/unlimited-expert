<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        {{ !empty($companySettings['header_text']) ? $companySettings['header_text']->value : config('app.name', 'TGS AU-Partners Apps SaaS') }}
        - {{ __('Career') }}</title>

    <link rel="icon"
          href="{{ asset(Storage::url('uploads/logo/')) . '/' . (isset($companySettings['company_favicon']) && !empty($companySettings['company_favicon']) ? $companySettings['company_favicon']->value : 'favicon.png') }}"
          type="image" sizes="16x16">

    {{--    <link rel="stylesheet" href="{{ asset('libs/@fortawesome/fontawesome-free/css/all.min.css') }}">--}}
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">

    <link rel="stylesheet" href="{{ asset('css/site.css') }}" id="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
<header class="header header-transparent" id="header-main">

    <nav class="navbar navbar-main navbar-expand-lg navbar-transparent navbar-light bg-white" id="navbar-main">
        <div class="container px-lg-0">
            <a class="navbar-brand mr-lg-5" href="#">
                <img class="hweb" alt="Image placeholder"
                     src="{{ asset(Storage::url('uploads/logo/')) . '/' . (isset($companySettings['company_logo']) && !empty($companySettings['company_logo']) ? $companySettings['company_logo']->value : 'logo-dark.png') }}"
                     id="navbar-logo" style="height: 50px;">
            </a>
            <button class="navbar-toggler pr-0" type="button" data-toggle="collapse"
                    data-target="#navbar-main-collapse" aria-controls="navbar-main-collapse" aria-expanded="false"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            {{-- <div class="collapse navbar-collapse" id="navbar-main-collapse">

                <ul class="navbar-nav align-items-lg-center ml-lg-auto">
                    <li class="nav-item">
                        <div class="dropdown global-icon" data-toggle="tooltip"
                             data-original-titla="{{ __('Choose Language') }}">
                            <a class="nav-link px-0" href="#" role="button" data-toggle="dropdown"
                               aria-haspopup="true" aria-expanded="false" data-offset="0,10">
                                <i class="ti ti-globe-europe"></i>
                                <span class="d-none d-lg-inline-block">{{ \Str::upper($currantLang) }}</span>
                            </a>

                            <div class="dropdown-menu  dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                @foreach ($languages as $language)
                                    <a class="dropdown-item @if ($language == $currantLang) text-danger @endif"
                                       href="{{ route('career', [$id, $language]) }}">{{ \Str::upper($language) }}</a>
                                @endforeach
                            </div>
                        </div>
                    </li>
                </ul>
            </div> --}}
        </div>
    </nav>
</header>

<div class="main-content">
    <!-- Header (v16) -->

    <section class="slice d-flex align-items-center" style="background-image: url('{{ asset('assets/images/career/career-page.png') }}'); background-size: cover; background-position: center; height: 500px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-left">
                        <h3 class="mt-12 text-white">{{ __('Discover Your Path to Achieving Dreams.') }}</h3>
                        <p class="lead lh-180 text-white">
                            {{ __('Embark on a journey to uncover the path that leads to your aspirations,') }}<br>
                            {{ __('and muster the strength to transform them into reality') }}<br>
                            {{ __('with an unshakable resolve.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>




    
    <section class="slice" style="background-image: url('{{ asset('assets/images/career/about.png') }}'); background-size: cover; background-position: center; height: 700px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h3 class="mt-6">{{ __('ABOUT TGS AU PARTNERS') }}</h3>
                    <p class="lead lh-180">
                    {{ __('We are dedicated to creating an environment where innovation thrives and employees are empowered to make a difference.') }}
                    <br>
                    {{ __('Here are some reasons to join us:') }}
                    </p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card card-fluid">
                        <img src="{{ asset('assets/images/career/project.jpeg') }}" class="card-img-top" alt="Innovative Projects" style="max-width: auto; height: 250px;">
                        <div class="card-body">
                            <h5 class="card-title">{{ __('Innovative Projects') }}</h5>
                            <p class="card-text">{{ __('Be part of cutting-edge projects that challenge the status quo and push boundaries.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-fluid">
                        <img src="{{ asset('assets/images/career/collaborative-team.png') }}" class="card-img-top" alt="Collaborative Team" style="max-width: auto; height: 250px;">
                        <div class="card-body">
                            <h5 class="card-title">{{ __('Collaborative Team') }}</h5>
                            <p class="card-text">{{ __('Work with a diverse and talented team that fosters collaboration and creativity.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-fluid">
                        <img src="{{ asset('assets/images/career/karir.jpeg') }}" class="card-img-top" alt="Career Growth" style="max-width: auto; height: 250px;">
                        <div class="card-body">
                            <h5 class="card-title">{{ __('Career Growth') }}</h5>
                            <p class="card-text">{{ __('Opportunities for personal and professional growth to help you advance in your career.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="slice" style="background-image: url('{{ asset('assets/images/career/ourlife.png') }}'); background-size: cover; background-position: center; height: 800px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h3 class="mt-3">{{ __('OUR LIFE IN THE COMPANY') }}</h3>
                    <p class="lead lh-180">{{ __('Discover the vibrant culture and daily experiences that make TGS AU Partners a great place to work:') }}</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card card-fluid">
                        <img src="{{ asset('assets/images/career/worklifebalance.png') }}" class="card-img-top animate__animated animate__fadeInLeft" alt="Work-Life Balance">
                        <div class="card-body">
                            <h5 class="card-title">{{ __('Work-Life Balance') }}</h5>
                            <p class="card-text">{{ __('Understanding the interplay between work demands and personal life is our foremost priority. Hence, we offer a range of flexible options to create a well-rounded balance between your professional commitments and personal needs.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-fluid">
                        <img src="{{ asset('assets/images/career/charity.png') }}" class="card-img-top animate__animated animate__fadeInRight" alt="Employee Events" style="max-width: auto; height: auto;">
                        <div class="card-body">
                            <h5 class="card-title">{{ __('Employee Events') }}</h5>
                            <p class="card-text">{{ __('Engage in a wide array of exhilarating events and collaborative team activities meticulously designed to foster unity and camaraderie among employees, creating an environment where colleagues can forge strong bonds while enjoying memorable experiences together') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>




    <!-- Table (v1) -->
    <section class="slice bg-secondary">
        <div class="container">
            <div class="mb-4 text-center">
                <h3 class=" mt-4">{{ __('Be Part of Our Team') }}</h3>
                <div class="fluid-paragraph mt-3">
                    <p class="lead lh-180 ">
                        {{ __('Join our dynamic and innovative company, and contribute your talents and expertise to achieve success together.') }}
                    </p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-11">
                    <div class="table-responsive-lg">
                        <table class="table table-hover table-scale--hover table-cards align-items-center">
                            <tbody>
                            @foreach ($jobs as $job)
                                <tr>
                                    <th scope="row">
                                        <div class="media align-items-center">
                                            <div>
                                                <span class="avatar bg-primary text-white mr-4" style="color:red;"
                                                      title="{{ __('Job Position') }}">{{ $job->position }}</span>
                                            </div>
                                            <div class="media-body media-body-custom">
                                                <a href="{{ route('job.requirement', [$job->code, !empty($job) ? (!empty($job->createdBy->lang) ? $job->createdBy->lang : 'en') : 'en']) }}"
                                                   class="h6 mb-0">{{ $job->title }}</a>
                                            </div>
                                        </div>
                                    </th>
                                    <td>

                                        @foreach (explode(',', $job->skill) as $skill)
                                            <span class="badge bg-primary p-2 px-3 rounded text-white">{{ $skill }}</span>
                                        @endforeach
                                    </td>

                                    <td><i class="ti ti-map-pin mr-3"></i><span
                                            class="h6">{{!empty($job->branches)?$job->branches->name:'-'}}</span>
                                    </td>
                                </tr>
                                <tr class="table-divider"></tr>
                            @endforeach
                            </tbody>
                        </table>
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
                        {{ !empty($companySettings['footer_text']) ? $companySettings['footer_text']->value : 'TGS AU-Partners Apps SaaS' }}
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
<script src="{{ asset('js/autosize/dist/autosize.min.js') }}"></script>
<script src="{{ asset('js/site.js') }}"></script>
<script src="{{ asset('js/demo.js') }} "></script>
</body>

</html>
