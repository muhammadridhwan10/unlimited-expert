@extends('layouts.admin')

@section('page-title')
    {{__('Manage Project Orders')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Manage Project Orders')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="lg" data-url="{{ route('project-orders.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Order')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
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

    {{-- <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('medical-allowance.index'),'method'=>'get','id'=>'report_monthly_medical_allowance')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-auto">

                                <div class="row">
                                    <div class="col-auto">
                                        <div class="btn-box" style = "width:300px;">
                                            {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                            {{ Form::select('employee_id', $employees, isset($_GET['employee_id']) ? $_GET['employee_id'] : null, ['class' => 'form-control select2', 'placeholder' => __('Select employee')]) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="btn-box">
                                            {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                                            {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'month-btn form-control'))}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_monthly_medical_allowance').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('medical-allowance.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
    </div> --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
            <div class="card-body table-border-style">
                    <div class="table-responsive">
                    <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Order Number')}}</th>
                                <th>{{__('Client Name')}}</th>
                                <th>{{__('Project Name')}}</th>
                                <th>{{__('Start Date')}}</th>
                                <th>{{__('End Date')}}</th>
                                <th>{{__('Label')}}</th>
                                <th>{{__('Fee EL')}}</th>
                                <th>{{__('Status')}}</th>
                                <th width="200px">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td class="Id">
                                        <a href="{{ route('project-orders.show', \Crypt::encrypt($order->id)) }}" class="btn btn-outline-primary">{{ $order->order_number }}</a>
                                    </td>
                                    <td>{{!empty($order->name) ? $order->name:'-'}}</td>
                                    <td>{{!empty($order->project_name) ? $order->project_name:'-'}}</td>
                                    <td>{{!empty($order->start_date) ? $order->start_date:'-'}}</td>
                                    <td>{{!empty($order->end_date) ? $order->end_date:'-'}}</td>
                                    <td>{{!empty($order->label) ? $order->label:'-'}}</td>
                                    <td>{{ !empty($order->budget) ? \Auth::user()->priceFormat($order->budget) : '-' }}</td>
                                    <td>
                                        @if($order->is_approve === null)
                                            <span class="p-2 px-3 badge rounded-pill bg-warning">{{__('Pending')}}</span>
                                        @elseif($order->is_approve == 1)
                                            <span class="p-2 px-3 badge rounded-pill bg-success">{{__('Approved')}}</span>
                                        @elseif($order->is_approve == 0)
                                            <span class="p-2 px-3 badge rounded-pill bg-danger">{{__('Not Approved')}}</span>
                                        @endif
                                    </td>
                                    <td>
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="#" data-url="{{ URL::to('project-orders/'.$order->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Project Orders')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                            </div>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('project-orders.show', \Crypt::encrypt($order->id)) }}"
                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Show "
                                                    data-original-title="{{ __('Detail') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                    <a href="{{ route('project-orders.destroy', $order->id) }}" class="mx-3 btn btn-sm align-items-center bs-pass-para " data-bs-toggle="tooltip" title="{{__('Delete')}}"
                                                    data-original-title="{{ __('Delete') }}"
                                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                    data-confirm-yes="document.getElementById('delete-form-{{ $order->id }}').submit();">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                            </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('script-page')
@endpush
