@extends('layouts.admin')
@section('page-title')
    {{__('Project Orders Detail')}}
@endsection
@push('css-page')
    <style>
        #card-element {
            border: 1px solid #a3afbb !important;
            border-radius: 10px !important;
            padding: 10px !important;
        }
    </style>
@endpush
@push('script-page')
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <script>
        $(document).on('click', '#shipping', function () {
            var url = $(this).data('url');
            var is_display = $("#shipping").is(":checked");
            $.ajax({
                url: url,
                type: 'get',
                data: {
                    'is_display': is_display,
                },
                success: function (data) {
                    // console.log(data);
                }
            });
        })


    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('project-orders.index')}}">{{__('Project Orders')}}</a></li>
    <li class="breadcrumb-item">{{ $projectOrder->order_number }}</li>
@endsection


@section('content')

    <div class="row">
        <div class="card ">
            <div class="card-body">
                <div class="row timeline-wrapper">
                    <div class="col-md-6 col-lg-4 col-xl-4">
                        <div class="timeline-icons"><span class="timeline-dots"></span>
                            <i class="ti ti-plus text-primary"></i>
                        </div>
                        <h6 class="text-primary my-3">{{__('Create Project Orders')}}</h6>
                        <p class="text-muted text-sm mb-3"><i class="ti ti-clock mr-2"></i>{{__('Created on ')}}{{\Auth::user()->dateFormat($projectOrder->created_at)}}</p>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-4">
                        <div class="timeline-icons"><span class="timeline-dots"></span>
                            <i class="ti ti-clock text-warning"></i>
                        </div>
                        <h6 class="text-warning my-3">{{__('Review')}}</h6>
                        <p class="text-muted text-sm mb-3">
                            @if(\Auth::user()->type !== 'partners')
                                @if($projectOrder->is_approve == 0 || $projectOrder->is_approve == NULL)
                                <a href="#" data-size="lg" data-url="{{ route('order.approval',$projectOrder->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Select Approval')}}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-send mr-2"></i>{{__('Send Approval')}}
                                </a>
                                @endif
                            @else
                                @if($projectOrder->is_approve === NULL)
                                    <a href="#" data-size="lg" data-url="{{ route('order.approved',$projectOrder->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Approval')}}" class="btn btn-sm btn-primary">
                                        {{__('Approval')}}
                                    </a>
                                @elseif($projectOrder->is_approve === 0)
                                     {{__('Not Approved')}}
                                @endif
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-4">
                        <div class="timeline-icons"><span class="timeline-dots"></span>
                            <i class="ti ti-report text-info"></i>
                        </div>
                        <h6 class="text-info my-3">{{__('Approval Project Orders')}}</h6>
                        @if($projectOrder->is_approve === NULL)
                           <p class="badge rounded-pill bg-info text-white text-sm mb-3">{{__('Status')}} : {{__('Awaiting approval')}} </p>
                        @elseif($projectOrder->is_approve === 0)
                            <p class="badge rounded-pill bg-danger text-white text-sm mb-3">{{__('Status')}} : {{__('Not Approved')}} </p>
                        @else
                            <p class="badge rounded-pill bg-success text-white text-sm mb-3">{{__('Status')}} : {{__('Approved')}} </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- @if(\Auth::user()->type=='company' ||  \Auth::user()->type=='admin')
            <div class="row justify-content-between align-items-center mb-3">
                <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                    <div class="all-button-box mx-2">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="changeStatusDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{__('Change Status')}}
                            </button>
                            <div class="dropdown-menu" aria-labelledby="changeStatusDropdown">
                                @foreach(\App\Models\Invoice::$statues as $key => $status)
                                    @if($key != $invoice->status)
                                        <a class="dropdown-item" href="{{ route('invoice.change-status', ['invoice' => $invoice->id, 'status' => $key]) }}">
                                            {{ $status }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="all-button-box mr-2">
                        <a href="{{ route('invoice.pdf', Crypt::encrypt($invoice->id))}}" target="_blank" class="btn btn-sm btn-primary">{{__('Download')}}</a>
                    </div>
                </div>
            </div>
    @endif --}}

    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-4">
                </div>
                <div class="col-md-8 mt-4">
                    <ul class="nav nav-pills nav-fill cust-nav information-tab" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="client-details-tab" data-bs-toggle="pill"
                                data-bs-target="#client-details" type="button">{{ __('Client Details') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="project-tab"
                                data-bs-toggle="pill" data-bs-target="#project"
                                type="button">{{ __('Project') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link " id="time-budget-tab"
                                data-bs-toggle="pill" data-bs-target="#time-budget"
                                type="button">{{ __('Time Budget') }}</button>
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
                        <div class="tab-pane fade active show" id="client-details" role="tabpanel" aria-labelledby="pills-user-tab-1">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card pb-0">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ __('Company Info') }}</h5>

                                            <div class="row">
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Company Name ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $projectOrder->name }}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('Company E-Mail') }}</p>
                                                        <h6 class="report-text mb-0">{{ $projectOrder->email }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Total Company Income Per Year ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ \Auth::user()->priceFormat($projectOrder->total_company_income_per_year) }}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('Total Company Assets Value') }}</p>
                                                        <h6 class="report-text mb-0">{{ \Auth::user()->priceFormat($projectOrder->total_company_assets_value) }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Total Employee ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $projectOrder->total_employee }}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('Total Branch Office') }}</p>
                                                        <h6 class="report-text mb-0">{{ $projectOrder->total_branch_offices }}</h6>
                                                    </div>
                                                </div>
                                                 <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Business Sector ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $projectOrder && $projectOrder->client_business_sector_id ? $projectOrder->sector->name : '-' }}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('Company NPWP') }}</p>
                                                        <h6 class="report-text mb-3">{{ $projectOrder ? $projectOrder->npwp : '-' }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Company Telephone') }}</p>
                                                        <h6 class="report-text mb-3">{{ $projectOrder ? $projectOrder->telp : '-' }}</h6>
                                                        <p class="card-text mb-0">{{ __('Company Address') }}</p>
                                                        <h6 class="report-text mb-3">{{ $projectOrder ? $projectOrder->address : '-' }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Company Country') }}</p>
                                                        <h6 class="report-text mb-3">{{ $projectOrder ? $projectOrder->country : '-' }}</h6>
                                                        <p class="card-text mb-0">{{ __('Company State') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $projectOrder ? $projectOrder->state : '-' }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Company City') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $projectOrder && $projectOrder->city ? $projectOrder->city : '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                             <div class="row">
                                <div class="col-md-6">
                                    <div class="card pb-0">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ __('PIC Company Info') }}</h5>

                                            <div class="row">
                                                <div class="col-md-6 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('PIC Name ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $projectOrder->name_pic }}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('PIC E-Mail') }}</p>
                                                        <h6 class="report-text mb-0">{{ $projectOrder->email_pic }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('PIC Telp ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $projectOrder->telp_pic }}
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card pb-0">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ __('Invoice Info') }}</h5>

                                            <div class="row">
                                                <div class="col-md-6 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Attention Name ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $projectOrder->name_invoice }}
                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Position') }}</p>
                                                        <h6 class="report-text mb-0">{{ $projectOrder->position }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="project" role="tabpanel" aria-labelledby="pills-user-tab-1">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card pb-0">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ __('Project Info') }}</h5>

                                            <div class="row">
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Project Name ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $projectOrder->project_name }}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('Project Fee') }}</p>
                                                        <h6 class="report-text mb-0">{{ \Auth::user()->priceFormat($projectOrder->budget) }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Project Start Date') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{$projectOrder->start_date}}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('Project End Date') }}</p>
                                                        <h6 class="report-text mb-0">{{$projectOrder->end_date}}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Project Estimated Hrs ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $projectOrder->estimated_hrs . ' H' }}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('Project Description') }}</p>
                                                        <h6 class="report-text mb-0">{{  $projectOrder->description ? $projectOrder->description : '-' }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Project Label ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $projectOrder->label ? $projectOrder->label : '-' }}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('Project Status') }}</p>
                                                        <h6 class="report-text mb-3">{{ $projectOrder->status ? $projectOrder->status : '-' }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Public Accountant ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $projectOrder->accountant->name ? $projectOrder->accountant->name : '-' }}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('Leader Project') }}</p>
                                                        <h6 class="report-text mb-3">{{ $projectOrder->user->name ? $projectOrder->user->name : '-' }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Budget ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $projectOrder->budget ? \Auth::user()->priceFormat($projectOrder->budget) : '-' }}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('Tags') }}</p>
                                                        <h6 class="report-text mb-3">{{ $projectOrder->tags ? $projectOrder->tags : '-' }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="time-budget" role="tabpanel" aria-labelledby="pills-user-tab-1">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card pb-0">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ __('Project Time Budget') }}</h5>
                                            <br>
                                            <div class="row">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('Position') }}</th>
                                                            <th>{{ __('Project Hours') }}</th>
                                                            <th>{{ __('Rate') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>{{ __('Partners') }}</td>
                                                            <td>{{ $projectOrder->ph_partners . ' H' }}</td>
                                                            <td>{{ \Auth::user()->priceFormat($projectOrder->rate_partners) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>{{ __('Manager') }}</td>
                                                            <td>{{ $projectOrder->ph_manager . ' H' }}</td>
                                                            <td>{{ \Auth::user()->priceFormat($projectOrder->rate_manager) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>{{ __('Senior Associate') }}</td>
                                                            <td>{{ $projectOrder->ph_senior . ' H' }}</td>
                                                            <td>{{ \Auth::user()->priceFormat($projectOrder->rate_senior) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>{{ __('Associate') }}</td>
                                                            <td>{{ $projectOrder->ph_associate . ' H' }}</td>
                                                            <td>{{ \Auth::user()->priceFormat($projectOrder->rate_associate) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>{{ __('Assistant') }}</td>
                                                            <td>{{ $projectOrder->ph_assistant . ' H' }}</td>
                                                            <td>{{ \Auth::user()->priceFormat($projectOrder->rate_assistant) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>{{ __('Total') }}</strong></td>
                                                            <td>
                                                                <strong>
                                                                    {{ $projectOrder->ph_partners + $projectOrder->ph_manager + $projectOrder->ph_senior + $projectOrder->ph_associate + $projectOrder->ph_assistant . ' H' }}
                                                                </strong>
                                                            </td>
                                                            <td>
                                                                <strong>
                                                                    {{ \Auth::user()->priceFormat($projectOrder->rate_partners + $projectOrder->rate_manager + $projectOrder->rate_senior + $projectOrder->rate_associate + $projectOrder->rate_assistant) }}
                                                                </strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
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
    </div>

@endsection
