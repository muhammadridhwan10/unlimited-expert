@extends('layouts.admin')
@section('page-title')
    {{__('Manage User')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('users.index')}}">{{__('User')}}</a></li>
    <li class="breadcrumb-item">  {{ ucwords($user->name).__("'s Detail") }}</li>
@endsection
@push('css-page')
    <link rel="stylesheet" href="{{url('css/swiper.min.css')}}">

    <link rel="stylesheet" href="{{url('css/swiper.min.css')}}">


    <style>
        .product-thumbs .swiper-slide img {
        border:2px solid transparent;
        object-fit: cover;
        cursor: pointer;
        }
        .product-thumbs .swiper-slide-active img {
        border-color: #bc4f38;
        }

        .product-slider .swiper-button-next:after,
        .product-slider .swiper-button-prev:after {
            font-size: 20px;
            color: #000;
            font-weight: bold;
        }
        .modal-dialog.modal-md {
            background-color: #fff !important;
        }

        .no-image{
            min-height: 300px;
            align-items: center;
            display: flex;
            justify-content: center;
        }
    </style>
@endpush

@section('action-btn')
<form action="{{ route('user.show', $user->id) }}" method="GET">
    <div class="float-end">
        <button type="submit" name="filter" value="this_month" class="btn btn-primary">{{ __('This Month') }}</button>
        <button type="submit" name="filter" value="last_7_days" class="btn btn-info">{{ __('Last 7 Days') }}</button>
        <button type="submit" name="filter" value="last_month" class="btn btn-success">{{ __('Last Month') }}</button>
    </div>
</form>
@endsection

@section('content')

    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-4">
                </div>
                <div class="col-md-8 mt-4">
                    <ul class="nav nav-pills nav-fill cust-nav information-tab" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="customer-details-tab" data-bs-toggle="pill"
                                data-bs-target="#customer-details" type="button">{{ __('Details') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link " id="user-project-tab"
                                data-bs-toggle="pill" data-bs-target="#user-project"
                                type="button">{{ __('Projects') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="user-tracker-tab"
                                data-bs-toggle="pill" data-bs-target="#user-tracker"
                                type="button">{{ __('Tracker') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="user-timesheet-tab"
                                data-bs-toggle="pill" data-bs-target="#user-timesheet"
                                type="button">{{ __('Timesheet') }}</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 ">
            <div class="row">
                <div class="col-lg-12">
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade active show" id="customer-details" role="tabpanel"
                            aria-labelledby="pills-user-tab-1">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card pb-0">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ __('User Info') }}</h5>

                                            <div class="row">
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Employee Name') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $user->name }}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('E-Mail') }}</p>
                                                        <h6 class="report-text mb-0">{{ $user->email }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('DOB') }}</p>
                                                        <h6 class="report-text mb-3">{{ $user->employee->dob ? $user->employee->dob : '-' }}</h6>
                                                        <p class="card-text mb-0">{{ __('Gender') }}</p>
                                                        <h6 class="report-text mb-0">{{ $user->employee->gender ? $user->employee->gender : '-' }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Phone') }}</p>
                                                        <h6 class="report-text mb-3">{{ $user->employee->phone ? $user->employee->phone : '-' }}</h6>
                                                        <p class="card-text mb-0">{{ __('Address') }}</p>
                                                        <h6 class="report-text mb-3">{{ $user->employee->address ? $user->employee->address : '-' }}</h6>
                                                    </div>
                                                </div>
    
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="user-project" role="tabpanel"
                            aria-labelledby="pills-user-tab-4">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body table-border-style table-border-style">
                                            <h5 class="d-inline-block mb-5">{{ __('Project') }}</h5>
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('Name') }}</th>
                                                            <th>{{ __('Status') }}</th>
                                                            <th>{{ __('Start Date') }}</th>
                                                            <th>{{ __('End Date') }}</th>
                                                            <th>{{ __('Estimated Hrs') }}</th>
                                                            <th>{{ __('Fee') }}</th>
                                                            <th>{{ __('Total Hours') }}</th>
                                                            @if(Gate::check('project show') || Gate::check('project edit') || Gate::check('project delete'))
                                                                <th width="10%"> {{ __('Action') }}</th>
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($user->userProject($user->id) as $project)
                                                        <tr class="font-style">
                                                            <td>{{ !empty($project) ? $project->project_name : '-' }}</td>
                                                            <td>{{ !empty($project) ? $project->status : '-' }}</td>
                                                            <td>{{ !empty($project) ? $project->start_date : '-'}}</td>
                                                            <td>{{ !empty($project) ? $project->end_date : '-' }}</td>
                                                            <td>{{ !empty($project) ? $project->estimated_hrs . ' H' : '-' }}</td>
                                                            <td>{{ !empty($project) ? \Auth::user()->priceFormat($project->budget) : '-' }}</td>
                                                            <td>{{ $project->totalHoursUser($user->id) }} {{__('H')}}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                            <div class="d-flex justify-content-center">
                                                {{ $user->userProject($user->id)->links() }}
                                            </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="user-tracker" role="tabpanel"
                            aria-labelledby="pills-user-tab-4">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body table-border-style table-border-style">
                                            <h5 class="d-inline-block mb-5">{{ __('Project Tracker') }}</h5>
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('Start Date') }}</th>
                                                            <th>{{ __('Project') }}</th>
                                                            <th>{{ __('Start Time') }}</th>
                                                            <th>{{ __('End Time') }}</th>
                                                            <th>{{ __('Total Time') }}</th>
                                                            <th>{{ __('Action') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($user->userTracker($user->id, $filter) as $tracker)
                                                        <tr class="font-style">
                                                            <td>{{date("l, d-m-Y",strtotime($tracker->start_time))}}</td>
                                                            <td>{{!empty($tracker->project_name)?$tracker->project_name:'-'}}</td>
                                                            <td>{{date("H:i:s",strtotime($tracker->start_time))}}</td>
                                                            <td>{{date("H:i:s",strtotime($tracker->end_time))}}</td>
                                                            <td>
                                                                @php
                                                                    $startTime = \Carbon\Carbon::parse($tracker->start_time);
                                                                    $endTime = \Carbon\Carbon::parse($tracker->end_time);
                                                                    $diff = $startTime->diff($endTime);
                                                                    echo $diff->format('%H:%I:%S');
                                                                @endphp
                                                            </td>
                                                            <td>
                                                                <img alt="Image placeholder" src="{{ asset('assets/images/gallery.png')}}" class="avatar view-images rounded-circle avatar-sm" data-bs-toggle="tooltip" title="{{__('View Screenshot images')}}" data-original-title="{{__('View Screenshot images')}}" style="height: 25px;width:24px;margin-right:10px;cursor: pointer;" data-id="{{$tracker->id}}" id="track-images-{{$tracker->id}}">
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            {{ $user->userTracker($user->id, $filter)->links() }}
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg ss_modale " role="document">
                                        <div class="modal-content image_sider_div">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="user-timesheet" role="tabpanel"
                            aria-labelledby="pills-user-tab-4">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <h5 class="d-inline-block">{{ __('Total Timesheet Hours') }}</h5>
                                            <h3 class="mt-3">
                                                {{ $user->getTotalTimesheetHours($user->id, $filter) ?? '00:00:00' }}
                                            </h3>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-body table-border-style table-border-style">
                                            <h5 class="d-inline-block mb-5">{{ __('Project Timesheet') }}</h5>
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th> {{__('Project')}}</th>
                                                            <th> {{__('Date')}}</th>
                                                            <th> {{__('Time')}}</th>
                                                            <th> {{__('Status')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($user->userTimesheet($user->id, $filter) as $timesheet)
                                                        <tr class="font-style">
                                                            <td>{{!empty($timesheet->project->project_name)?$timesheet->project->project_name:'-'}}</td>
                                                            <td>{{date("l, d-m-Y",strtotime($timesheet->date))}}</td>
                                                            <td>{{date("H:i:s",strtotime($timesheet->time))}}</td>
                                                            <td>{{!empty($timesheet->project->status)?$timesheet->project->status:'-'}}</td>
                                                        </tr>
                                                        @empty
                                                        <tr>
                                                            <td colspan="4">{{ __('No Timesheet Found') }}</td>
                                                        </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            {{ $user->userTimesheet($user->id, $filter)->links() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script-page')
    <script src="{{url('js/swiper.min.js')}}"></script>


    <script type="text/javascript">

        function init_slider(){
                if($(".product-left").length){
                        var productSlider = new Swiper('.product-slider', {
                            spaceBetween: 0,
                            centeredSlides: false,
                            loop:false,
                            direction: 'horizontal',
                            loopedSlides: 5,
                            navigation: {
                                nextEl: ".swiper-button-next",
                                prevEl: ".swiper-button-prev",
                            },
                            resizeObserver:true,
                        });
                    var productThumbs = new Swiper('.product-thumbs', {
                        spaceBetween: 0,
                        centeredSlides: true,
                        loop: false,
                        slideToClickedSlide: true,
                        direction: 'horizontal',
                        slidesPerView: 7,
                        loopedSlides: 5,
                    });
                    productSlider.controller.control = productThumbs;
                    productThumbs.controller.control = productSlider;
                }
            }


        $(document).on('click', '.view-images', function () {

                var p_url = "{{route('tracker.image.view')}}";
                var data = {
                    'id': $(this).attr('data-id')
                };
                postAjax(p_url, data, function (res) {
                    $('.image_sider_div').html(res);
                    $('#exampleModalCenter').modal('show');
                    setTimeout(function(){
                        var total = $('.product-left').find('.product-slider').length
                        if(total > 0){
                            init_slider();
                        }

                    },200);

                });
                });


                // ============================ Remove Track Image ===============================//
                $(document).on("click", '.track-image-remove', function () {
                var rid = $(this).attr('data-pid');
                $('.confirm_yes').addClass('image_remove');
                $('.confirm_yes').attr('image_id', rid);
                $('#cModal').modal('show');
                var total = $('.product-left').find('.swiper-slide').length
                });

                function removeImage(id){
                    var p_url = "{{route('tracker.image.remove')}}";
                    var data = {id: id};
                    deleteAjax(p_url, data, function (res) {

                        if(res.flag){
                            $('#slide-thum-'+id).remove();
                            $('#slide-'+id).remove();
                            setTimeout(function(){
                                var total = $('.product-left').find('.swiper-slide').length
                                if(total > 0){
                                    init_slider();
                                }else{
                                    $('.product-left').html('<div class="no-image"><h5 class="text-muted">Images Not Available .</h5></div>');
                                }
                            },200);
                        }

                        $('#cModal').modal('hide');
                        show_toastr('error',res.msg,'error');
                    });
                }

                // $(document).on("click", '.remove-track', function () {
                // var rid = $(this).attr('data-id');
                // $('.confirm_yes').addClass('t_remove');
                // $('.confirm_yes').attr('uid', rid);
                // $('#cModal').modal('show');
            // });


    </script>
@endpush
