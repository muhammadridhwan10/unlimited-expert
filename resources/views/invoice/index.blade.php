@extends('layouts.admin')
@section('page-title')
    {{__('Manage Invoices')}}
@endsection
@push('script-page')
    <script>
        function copyToClipboard(element) {

            var copyText = element.id;
            document.addEventListener('copy', function (e) {
                e.clipboardData.setData('text/plain', copyText);
                e.preventDefault();
            }, true);

            document.execCommand('copy');
            show_toastr('success', 'Url copied to clipboard', 'success');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const invoices = {!! json_encode($invoices) !!};
        const labelss = invoices.map(invoice => invoice.invoice_id);
        const amountRp = invoices.filter(invoice => invoice.currency === 'Rp').map(invoice => invoice.total_amount);
        const amountUsd = invoices.filter(invoice => invoice.currency === '$').map(invoice => invoice.total_amount);

        var ctx = document.getElementById('lineChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labelss,
                datasets: [
                    {
                        label: 'Total Amount (Rp)',
                        data: amountRp,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Total Amount ($)',
                        data: amountUsd,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    <script>
        // Add an event listener to the filter dropdown items
        document.addEventListener('DOMContentLoaded', function () {
            const filterOptions = document.querySelectorAll('.filter-option');
            const lineChartContainer = document.getElementById('lineChartContainer');

            filterOptions.forEach(function (option) {
                option.addEventListener('click', function (event) {
                    event.preventDefault();
                    const selectedChartType = event.target.getAttribute('data-chart-type');

                    // Toggle visibility based on the selected chart type
                    lineChartContainer.style.display = selectedChartType === 'line' ? 'block' : 'none';
                });
            });
        });
    </script>
@endpush


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Invoice')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        {{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
        {{--            <i class="ti ti-filter"></i>--}}
        {{--        </a>--}}

        <a href="{{ route('invoice.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Export')}}">
            <i class="ti ti-file-export"></i>
        </a>

        @can('create invoice')
            <a href="{{ route('invoice.create', 0) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Create')}}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan

        <a class="btn btn-sm btn-primary action-item"  role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="btn-inner--icon" style="color:white;">{{__('Filter')}}</span>
        </a>
        <div class="dropdown-menu">
                <a class="dropdown-item filter-option" data-chart-type="line" href="#">Revenue Detail</a>
            </div>
    </div>
@endsection



@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 ">
                <div class="card">
                    <div class="card-body">
{{--                         @if (!\Auth::guard('customer')->check())--}}
                            {{ Form::open(['route' => ['invoice.index'], 'method' => 'GET', 'id' => 'customer_submit']) }}
{{--                        @else--}}
{{--                            {{ Form::open(['route' => ['customer.invoice'], 'method' => 'GET', 'id' => 'customer_submit']) }}--}}
{{--                        @endif--}}
                        <div class="row d-flex align-items-center justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('issue_date', __('Issue Date'),['class'=>'form-label'])}}
                                    {{ Form::date('issue_date', isset($_GET['issue_date'])?$_GET['issue_date']:'', array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1')) }}


                                </div>
                            </div>
{{--                            @if (!\Auth::guard('customer')->check())--}}
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('client', __('Client'),['class'=>'form-label'])}}
                                        {{ Form::select('client', $client, isset($_GET['client']) ? $_GET['client'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
{{--                            @endif--}}
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('partner', __('Partner'),['class'=>'form-label'])}}
                                    {{ Form::select('user_id', $partner, isset($_GET['user_id']) ? $_GET['user_id'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::label('status', __('Status'),['class'=>'form-label'])}}
                                    {{ Form::select('status', [''=>'Select Status'] + $status,isset($_GET['status'])?$_GET['status']:'', array('class' => 'form-control select')) }}

                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('company', __('Company'), ['class' => 'form-label']) }}
                                    {{ Form::select('company', [''=>'Select Company'] + $companies, isset($_GET['company']) ? $_GET['company'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">

                                <a href="#" class="btn btn-sm btn-primary"
                                   onclick="document.getElementById('customer_submit').submit(); return false;"
                                   data-toggle="tooltip" data-original-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>

                                <a href="{{ route('invoice.index') }}" class="btn btn-sm btn-danger" data-toggle="tooltip"
                                   data-original-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                </a>
                            </div>

                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row" id="lineChartContainer" style="display: none;">
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-body">
                    <canvas id="lineChart" width="500" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5></h5>
                    @if (Auth::user()->type == 'company')
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{ __('Invoice') }}</th>
                                <th>{{ __('Client') }}</th>
                                <th>{{ __('Account') }}</th>
                                <th>{{ __('Partner') }}</th>
                                <th>{{ __('Company') }}</th>
                                <th>{{ __('Issue Date') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Tax') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                    <th>{{ __('Action') }}</th>
                                @endif
                                {{-- <th>
                                <td class="barcode">
                                    {!! DNS1D::getBarcodeHTML($invoice->sku, "C128",1.4,22) !!}
                                    <p class="pid">{{$invoice->sku}}</p>
                                </td>
                            </th> --}}
                            </tr>
                            </thead>

                            <tbody>
                            @php
                               $totalAmountRp = 0;
                               $totalAmountDollar = 0;
                            @endphp
                            @foreach ($invoices as $invoice)
                                @php
                                    if ($invoice->currency == 'Rp') {
                                        $totalAmountRp += $invoice->getDue();
                                    } elseif ($invoice->currency == '$') {
                                        $totalAmountDollar += $invoice->getDue();
                                    }
                                @endphp
                                <tr>
                                    <td class="Id">
                                        <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}" class="btn btn-outline-primary">{{ $invoice->invoice_id }}</a>
                                    </td>
                                    <td> {{ !empty($invoice->client) ? $invoice->client->name : '' }} </td>
                                    <td>{{!empty($invoice->account->name) ? $invoice->account->name:'-'}}</td>
                                    <td>{{!empty($invoice->user->name) ? $invoice->user->name:'-'}}</td>
                                    <td>{{!empty($invoice->company) ? $invoice->company:'-'}}</td>
                                    <td>{{ Auth::user()->dateFormat($invoice->issue_date) }}</td>
                                    <td>
                                        @if ($invoice->due_date < date('Y-m-d'))
                                            <div class="text-danger">{{ \Auth::user()->dateFormat($invoice->due_date) }}</div>
                                        @else
                                            {{ \Auth::user()->dateFormat($invoice->due_date) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($invoice->currency == '$')
                                            {{ \Auth::user()->priceFormat2($invoice->getTotalTax()) }}
                                        @else
                                            {{ \Auth::user()->priceFormat($invoice->getTotalTax()) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($invoice->currency == '$')
                                            {{ \Auth::user()->priceFormat2($invoice->getDue()) }}
                                        @else
                                            {{ \Auth::user()->priceFormat($invoice->getDue()) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($invoice->status == 0)
                                            <span
                                                class="status_badge badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 1)
                                            <span
                                                class="status_badge badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 2)
                                            <span
                                                class="status_badge badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 3)
                                            <span
                                                class="status_badge badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 4)
                                            <span
                                                class="status_badge badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @endif
                                    </td>
                                    @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                        <td class="Action">
                                                <span>
                                                @php $invoiceID= Crypt::encrypt($invoice->id); @endphp

                                                    @can('copy invoice')
                                                        <div class="action-btn bg-warning ms-2">
                                                       <a href="#" id="{{ route('invoice.link.copy',[$invoiceID]) }}" class="mx-3 btn btn-sm align-items-center"   onclick="copyToClipboard(this)" data-bs-toggle="tooltip" data-original-title="{{__('Click to copy')}}"><i class="ti ti-link text-white"></i></a>
                                                </div>
                                                    @endcan
                                                    @can('duplicate invoice')
                                                        <div class="action-btn bg-success ms-2">
                                                           {!! Form::open(['method' => 'get', 'route' => ['invoice.duplicate', $invoice->id], 'id' => 'duplicate-form-' . $invoice->id]) !!}

                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-toggle="tooltip"
                                                               data-original-title="{{ __('Duplicate') }}" data-bs-toggle="tooltip" title="Duplicate Invoice"
                                                               data-original-title="{{ __('Delete') }}"
                                                               data-confirm="You want to confirm this action. Press Yes to continue or Cancel to go back"
                                                               data-confirm-yes="document.getElementById('duplicate-form-{{ $invoice->id }}').submit();">
                                                                <i class="ti ti-copy text-white"></i>
                                                                {!! Form::open(['method' => 'get', 'route' => ['invoice.duplicate', $invoice->id], 'id' => 'duplicate-form-' . $invoice->id]) !!}
                                                                {!! Form::close() !!}
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('show invoice')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Show "
                                                                data-original-title="{{ __('Detail') }}">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('edit invoice')
                                                        <div class="action-btn bg-primary ms-2">
                                                                <a href="{{ route('invoice.edit', \Crypt::encrypt($invoice->id)) }}"
                                                                   class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Edit "
                                                                   data-original-title="{{ __('Edit') }}">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                    @endcan
                                                    @can('delete invoice')
                                                        <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id], 'id' => 'delete-form-' . $invoice->id]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para " data-bs-toggle="tooltip" title="{{__('Delete')}}"
                                                                       data-original-title="{{ __('Delete') }}"
                                                                       data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                       data-confirm-yes="document.getElementById('delete-form-{{ $invoice->id }}').submit();">
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
                            <tr>
                                <td colspan="11" style="border: 1px solid black; text-align: center; background-color:#008b8b; color:white; font-weight: bold;"><strong>Total Amount (Rp) : {{ \Auth::user()->priceFormat($totalAmountRp) }} </strong><strong>|</strong> <strong>Total Amount ($) : {{ \Auth::user()->priceFormat2($totalAmountDollar) }} </strong></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    @elseif (Auth::user()->type == 'partners')
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{ __('Invoice') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Account') }}</th>
                                <th>{{ __('Partner') }}</th>
                                <th>{{ __('Company') }}</th>
                                <th>{{ __('Issue Date') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Tax') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                    <th>{{ __('Action') }}</th>
                                @endif
                                {{-- <th>
                                <td class="barcode">
                                    {!! DNS1D::getBarcodeHTML($invoice->sku, "C128",1.4,22) !!}
                                    <p class="pid">{{$invoice->sku}}</p>
                                </td>
                            </th> --}}
                            </tr>
                            </thead>

                            <tbody>
                               @php
                                $totalAmountRp = 0;
                                $totalAmountDollar = 0;
                                @endphp
                                @foreach ($invoices as $invoice)
                                    @php
                                        if ($invoice->currency == 'Rp') {
                                            $totalAmountRp += $invoice->getDue();
                                        } elseif ($invoice->currency == '$') {
                                            $totalAmountDollar += $invoice->getDue();
                                        }
                                    @endphp
                                    <tr>
                                        <td class="Id">
                                            <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}" class="btn btn-outline-primary">{{ $invoice->invoice_id }}</a>
                                        </td>
                                        <td> {{ !empty($invoice->customer) ? $invoice->customer->billing_name : '' }} </td>
                                        <td>{{!empty($invoice->account->name) ? $invoice->account->name:'-'}}</td>
                                        <td>{{!empty($invoice->user->name) ? $invoice->user->name:'-'}}</td>
                                        <td>{{!empty($invoice->company) ? $invoice->company:'-'}}</td>
                                        <td>{{ Auth::user()->dateFormat($invoice->issue_date) }}</td>
                                        <td>
                                            @if ($invoice->due_date < date('Y-m-d'))
                                                <div class="text-danger">{{ \Auth::user()->dateFormat($invoice->due_date) }}</div>
                                            @else
                                                {{ \Auth::user()->dateFormat($invoice->due_date) }}
                                            @endif
                                        </td>
                                       <td>
                                        @if ($invoice->currency == '$')
                                            {{ \Auth::user()->priceFormat2($invoice->getTotalTax()) }}
                                        @else
                                            {{ \Auth::user()->priceFormat($invoice->getTotalTax()) }}
                                        @endif
                                        </td>
                                        <td>
                                            @if ($invoice->currency == '$')
                                                {{ \Auth::user()->priceFormat2($invoice->getDue()) }}
                                            @else
                                                {{ \Auth::user()->priceFormat($invoice->getDue()) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($invoice->status == 0)
                                                <span
                                                    class="status_badge badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                            @elseif($invoice->status == 1)
                                                <span
                                                    class="status_badge badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                            @elseif($invoice->status == 2)
                                                <span
                                                    class="status_badge badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                            @elseif($invoice->status == 3)
                                                <span
                                                    class="status_badge badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                            @elseif($invoice->status == 4)
                                                <span
                                                    class="status_badge badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                            @endif
                                        </td>
                                        @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                            <td class="Action">
                                                    <span>
                                                    @php $invoiceID= Crypt::encrypt($invoice->id); @endphp

                                                        @can('copy invoice')
                                                            <div class="action-btn bg-warning ms-2">
                                                        <a href="#" id="{{ route('invoice.link.copy',[$invoiceID]) }}" class="mx-3 btn btn-sm align-items-center"   onclick="copyToClipboard(this)" data-bs-toggle="tooltip" data-original-title="{{__('Click to copy')}}"><i class="ti ti-link text-white"></i></a>
                                                    </div>
                                                        @endcan
                                                        @can('duplicate invoice')
                                                            <div class="action-btn bg-success ms-2">
                                                            {!! Form::open(['method' => 'get', 'route' => ['invoice.duplicate', $invoice->id], 'id' => 'duplicate-form-' . $invoice->id]) !!}

                                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-toggle="tooltip"
                                                                data-original-title="{{ __('Duplicate') }}" data-bs-toggle="tooltip" title="Duplicate Invoice"
                                                                data-original-title="{{ __('Delete') }}"
                                                                data-confirm="You want to confirm this action. Press Yes to continue or Cancel to go back"
                                                                data-confirm-yes="document.getElementById('duplicate-form-{{ $invoice->id }}').submit();">
                                                                    <i class="ti ti-copy text-white"></i>
                                                                    {!! Form::open(['method' => 'get', 'route' => ['invoice.duplicate', $invoice->id], 'id' => 'duplicate-form-' . $invoice->id]) !!}
                                                                    {!! Form::close() !!}
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('show invoice')
                                                            <div class="action-btn bg-info ms-2">
                                                                <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Show "
                                                                    data-original-title="{{ __('Detail') }}">
                                                                    <i class="ti ti-eye text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('edit invoice')
                                                            <div class="action-btn bg-primary ms-2">
                                                                    <a href="{{ route('invoice.edit', \Crypt::encrypt($invoice->id)) }}"
                                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Edit "
                                                                    data-original-title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                </div>
                                                        @endcan
                                                        @can('delete invoice')
                                                            <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id], 'id' => 'delete-form-' . $invoice->id]) !!}
                                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para " data-bs-toggle="tooltip" title="{{__('Delete')}}"
                                                                        data-original-title="{{ __('Delete') }}"
                                                                        data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="document.getElementById('delete-form-{{ $invoice->id }}').submit();">
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
                                <tr>
                                    <td colspan="11" style="border: 1px solid black; text-align: center; background-color:#008b8b; color:white; font-weight: bold;"><strong>Total Amount (Rp) : {{ \Auth::user()->priceFormat($totalAmountRp) }} </strong><strong>|</strong> <strong>Total Amount ($) : {{ \Auth::user()->priceFormat2($totalAmountDollar) }} </strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
