@extends('layouts.admin')
@section('page-title')
    {{__('Manage Timesheet')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Timesheet')}}</li>
@endsection

@push('script-page')

    <script type="text/javascript" src="{{ asset('js/jszip.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/pdfmake.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/vfs_fonts.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/dataTables.buttons.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/buttons.html5.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        function exportToExcel() {
            $('#export_excel').val(1);
            document.getElementById('report_monthly_tracker').submit();
        }
    </script>
@endpush

@section('action-btn')
    <div class="float-end">
        @can('create timesheet')
            <a href="{{ route('timesheet.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Create')}}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
        <a href="#" class="btn btn-sm btn-success" onclick="exportToExcel()" data-bs-toggle="tooltip" title="{{__('Export to Excel')}}" data-original-title="{{__('Export to Excel')}}">
            <span class="btn-inner--icon"><i class="ti ti-file"></i></span>
        </a>
    </div>
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


@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('timesheet.list'),'method'=>'get','id'=>'report_monthly_tracker')) }}
                        {{ Form::hidden('export_excel', 0, ['id' => 'export_excel']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="btn-box">
                                            {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                                           {{ Form::month('month', request()->input('month', ''), ['class' => 'month-btn form-control']) }}

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto" style = "width:400px;">
                                <div class="btn-box">
                                    {{ Form::label('project_id', __('Project'), ['class' => 'form-label']) }}
                                    {{ Form::select('project_id', $project, isset($_GET['project_id']) ? $_GET['project_id'] : 0, ['class' => 'form-control select2']) }}
                                </div>
                            </div>
                            @if(Auth::user()->type == "admin" || Auth::user()->type == "company" || Auth::user()->type == "partners")
                            <div class="col-auto" style = "width:300px;">
                                <div class="btn-box">
                                    {{ Form::label('user_id', __('Employee'), ['class' => 'form-label']) }}
                                    {{ Form::select('user_id', $employee, isset($_GET['user_id']) ? $_GET['user_id'] : 0, ['class' => 'form-control select2']) }}
                                </div>
                            </div>
                            @endif
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_monthly_tracker').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('timesheet.list')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style mt-2">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>

                                <!-- <th> {{__('Description')}}</th> -->
                                <th> {{__('Employee')}}</th>
                                <th> {{__('Project')}}</th>
                                <th> {{__('Date')}}</th>
                                <th> {{__('Time')}}</th>
                                <th>{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $logged_hours = 0;
                            @endphp
                            @foreach ($employeeTimesheet as $timesheet)
                            @php
                                $hours = date('H', strtotime($timesheet->time));
                                $minutes = date('i', strtotime($timesheet->time));
                                $total_hours = $hours + ($minutes / 60);
                                $logged_hours += $total_hours;
                            @endphp
                                <tr>
                                    <td>{{!empty($timesheet->user->name)?$timesheet->user->name:'-'}}</td>
                                    <td>{{!empty($timesheet->project->project_name)?$timesheet->project->project_name:'-'}}</td>
                                    <td>{{date("l, d-m-Y",strtotime($timesheet->date))}}</td>
                                    <td>{{date("H:i:s",strtotime($timesheet->time))}}</td>
                                    @if (Gate::check('edit timesheet') || Gate::check('delete timesheet'))
                                        <td class="Action">
                                                <span>
                                                    @can('edit timesheet')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a href="#!" data-size="lg" data-url="{{ route('timesheet.edit',\Crypt::encrypt($timesheet->id)) }}" data-ajax-popup="true" 
                                                            class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-bs-original-title="{{__('Edit Timesheet')}}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete timesheet')
                                                        <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['timesheet.destroy', $timesheet->id]]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para " data-bs-toggle="tooltip" title="{{__('Delete')}}"
                                                                       data-original-title="{{ __('Delete') }}"
                                                                       data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                       data-confirm-yes="document.getElementById('delete-form-{{ $timesheet->id }}').submit();">
                                                                        <i class="ti ti-trash text-white"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                    @endcan
                                                </span>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            
                            </tbody>
                            @php
                                $totalSeconds = $logged_hours * 3600;
                                $hours = floor($logged_hours);
                                $minutes = floor(($logged_hours - $hours) * 60);
                                $seconds = floor((($logged_hours - $hours) * 60 - $minutes) * 60);
                            @endphp
                            <tr>
                                <td colspan="5" style="border: 1px solid black; text-align: center; background-color:#008b8b; color:white; font-weight: bold;">
                                    <strong>Total Time: {{ sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds) }}</strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg ss_modale " role="document">
          <div class="modal-content image_sider_div">

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
