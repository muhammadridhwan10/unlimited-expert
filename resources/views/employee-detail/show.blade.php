@extends('layouts.admin')
@section('page-title')
    {{__('Employee Information')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('employee-reports.index')}}">{{__('Employee List')}}</a></li>
    <li class="breadcrumb-item">  {{ ucwords($employee_report->name).__("'s Detail") }}</li>
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
<form action="{{ route('employee-reports.show', $employee_report->id) }}" method="GET">
    <div class="float-end">
        <button type="submit" name="filter" value="this_month" class="btn btn-primary">{{ __('This Month') }}</button>
        <button type="submit" name="filter" value="last_7_days" class="btn btn-info">{{ __('Last 7 Days') }}</button>
        <button type="submit" name="filter" value="last_month" class="btn btn-success">{{ __('Last Month') }}</button>
    </div>
</form>
@endsection

@section('content')

    <div class="container mt-4">
        <ul class="nav nav-tabs" id="projectTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">Information Details</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="project-tab" data-bs-toggle="tab" data-bs-target="#project" type="button" role="tab" aria-controls="project" aria-selected="false">Project</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tracker-tab" data-bs-toggle="tab" data-bs-target="#tracker" type="button" role="tab" aria-controls="tracker" aria-selected="false">Tracker</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="timesheet-tab" data-bs-toggle="tab" data-bs-target="#timesheet" type="button" role="tab" aria-controls="timesheet" aria-selected="false">Timesheet</button>
            </li>
        </ul>
        <div class="tab-content" id="projectTabsContent">
            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                <div class="row mt-4">
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
                                                    {{ $employee_report->name }}
                                                </h6>
                                                <p class="card-text mb-0">{{ __('E-Mail') }}</p>
                                                <h6 class="report-text mb-0">{{ $employee_report->email }}</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="p-4">
                                                <p class="card-text mb-0">{{ __('DOB') }}</p>
                                                <h6 class="report-text mb-3">{{ $employee_report->employee->dob ? $employee_report->employee->dob : '-' }}</h6>
                                                <p class="card-text mb-0">{{ __('Gender') }}</p>
                                                <h6 class="report-text mb-0">{{ $employee_report->employee->gender ? $employee_report->employee->gender : '-' }}</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="p-4">
                                                <p class="card-text mb-0">{{ __('Phone') }}</p>
                                                <h6 class="report-text mb-3">{{ $employee_report->employee->phone ? $employee_report->employee->phone : '-' }}</h6>
                                                <p class="card-text mb-0">{{ __('Address') }}</p>
                                                <h6 class="report-text mb-3">{{ $employee_report->employee->address ? $employee_report->employee->address : '-' }}</h6>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade show" id="project" role="tabpanel" aria-labelledby="project-tab">
                <div class="row mt-4">
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
                                                    <th>{{ __('Project Fee') }}</th>
                                                    <th>{{ __('Estimated Hrs') }}</th>
                                                    <th>{{ __('Real Hrs') }}</th>
                                                    @if(Gate::check('project show') || Gate::check('project edit') || Gate::check('project delete'))
                                                        <th width="10%"> {{ __('Action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($employee_report->userProject($employee_report->id) as $project)
                                                <tr class="font-style">
                                                    <td>{{ !empty($project) ? $project->project_name : '-' }}</td>
                                                    <td>{{ !empty($project) ? $project->status : '-' }}</td>
                                                    <td>{{ !empty($project) ? $project->start_date : '-'}}</td>
                                                    <td>{{ !empty($project) ? $project->end_date : '-' }}</td>
                                                    <td>{{ !empty($project) ? \Auth::user()->priceFormat($project->budget) : '-' }}</td>
                                                    <td>{{ $project->totalEstimatedHrs($employee_report->id) ?? 0 }} {{__('H')}}</td>
                                                    <td>{{ $project->totalHoursUser($employee_report->id,$filter) }} {{__('H')}}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                    <div class="d-flex justify-content-center">
                                        {{ $employee_report->userProject($employee_report->id, $filter)->links() }}
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade show" id="tracker" role="tabpanel" aria-labelledby="tracker-tab">
                <div class="row mt-4">
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
                                                @forelse ($employee_report->userTracker($employee_report->id, $filter) as $tracker)
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
                                    {{ $employee_report->userTracker($employee_report->id, $filter)->links() }}
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
            </div>
            <div class="tab-pane fade show" id="timesheet" role="tabpanel" aria-labelledby="timesheet-tab">
                <div class="row mt-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="d-inline-block">{{ __('Total Timesheet Hours') }}</h5>
                                    <h3 class="mt-3">
                                        {{ $employee_report->getTotalTimesheetHours($employee_report->id, $filter) ?? '00:00:00' }}
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
                                                @forelse ($employee_report->userTimesheet($employee_report->id, $filter) as $timesheet)
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
                                    {{ $employee_report->userTimesheet($employee_report->id, $filter)->links() }}
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
