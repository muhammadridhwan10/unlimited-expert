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
        const amountss = invoices.map(invoice => invoice.total_amount);

        var ctx = document.getElementById('lineChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labelss,
                datasets: [{
                    label: 'Total Amount',
                    data: amountss,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
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
       // Fetch the data from your controller and pass it to JavaScript
        const monthlyData = {!! json_encode($monthlyData) !!};
        const yearlyData = {!! json_encode($yearlyData) !!};

        // Monthly pie chart
        const monthlyLabels = Object.keys(monthlyData);
        const monthlyAmounts = Object.values(monthlyData);
        var ctxMonthlyPie = document.getElementById('monthlyPieChart').getContext('2d');
        var monthlyPieChart = new Chart(ctxMonthlyPie, {
            type: 'pie',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    data: monthlyAmounts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    labels: {
                        render: function (args) {
                            return args.value + ': ' + monthlyAmounts[args.index];
                        },
                        fontSize: 12,
                        fontStyle: 'bold',
                        fontColor: '#000',
                        fontFamily: 'Arial',
                    }
                }
            }
        });

        // Yearly pie chart
        const yearlyLabels = Object.keys(yearlyData);
        const yearlyAmounts = Object.values(yearlyData);
        var ctxYearlyPie = document.getElementById('yearlyPieChart').getContext('2d');
        var yearlyPieChart = new Chart(ctxYearlyPie, {
            type: 'pie',
            data: {
                labels: yearlyLabels,
                datasets: [{
                    data: yearlyAmounts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    labels: {
                        render: function (args) {
                            return args.value + ': ' + yearlyAmounts[args.index];
                        },
                        fontSize: 12,
                        fontStyle: 'bold',
                        fontColor: '#000',
                        fontFamily: 'Arial',
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
            const pieChartContainer = document.getElementById('pieChartContainer');

            filterOptions.forEach(function (option) {
                option.addEventListener('click', function (event) {
                    event.preventDefault();
                    const selectedChartType = event.target.getAttribute('data-chart-type');

                    // Toggle visibility based on the selected chart type
                    lineChartContainer.style.display = selectedChartType === 'line' ? 'block' : 'none';
                    pieChartContainer.style.display = selectedChartType === 'pie' ? 'block' : 'none';
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
            <span class="btn-inner--icon">{{__('Filter')}}</span>
        </a>
        <div class="dropdown-menu">
                <a class="dropdown-item filter-option" data-chart-type="line" href="#">Revenue Detail</a>
                <a class="dropdown-item filter-option" data-chart-type="pie" href="#">Report</a>
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
                                        {{ Form::label('customer', __('Customer'),['class'=>'form-label'])}}
                                        {{ Form::select('customer', $customer, isset($_GET['customer']) ? $_GET['customer'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
{{--                            @endif--}}
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

    <div class="row" id="pieChartContainer" style="display: none;">
        <div class="col-sm-12">
            <div class="row" id="printableArea">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header" style="padding: 25px 35px !important;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="row">
                                        <h5 class="mb-0">{{ __('Monthly Report') }}</h5>
                                    </div>
                                </div>
                                 
                            </div>
                            <div class="card-body text-center">
                            <div class="d-flex justify-content-center align-items-center">
                               <canvas id="monthlyPieChart" width="370" height="300"></canvas>
                            </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header" style="padding: 25px 35px !important;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="row">
                                        <h5 class="mb-0">{{ __('Yearly Report') }}</h5>
                                    </div>
                                </div>
                                 
                            </div>
                            <div class="card-body text-center">
                            <div class="d-flex justify-content-center align-items-center">
                               <canvas id="yearlyPieChart" width="300" height="300"></canvas>
                            </div>
                            </div>
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

    {{-- <div class="row" id="pieChartContainer" style="display: none;">
        <div class="row">
            <div class="col-sm-12">
                <!-- Monthly pie chart column -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="d-flex justify-content-center align-items-center">
                                <canvas id="monthlyPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Yearly pie chart column -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="d-flex justify-content-center align-items-center">
                                <canvas id="yearlyPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div> --}}

    {{-- <div class="row" id="pieChartContainer" style="display: none;">
        <!-- Add a new column for the pie chart -->
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-body text-center">
                <div class="d-flex justify-content-center align-items-center">
                    <canvas id="pieChart" width="500" height="300"></canvas>
                </div>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5></h5>
                    @if (Auth::user()->type == 'admin' || Auth::user()->type == 'company')
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{ __('Invoice') }}</th>
                                @if (!\Auth::guard('customer')->check())
                                    <th>{{ __('Customer') }}</th>
                                @endif
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
                                $totalAmount = 0;
                            @endphp
                            @foreach ($invoices as $invoice)
                                @php
                                    $totalAmount += $invoice->getDue();
                                @endphp
                                <tr>
                                    <td class="Id">
                                        @if (\Auth::guard('customer')->check())
                                            <a href="{{ route('customer.invoice.show', \Crypt::encrypt($invoice->id)) }}" class="btn btn-outline-primary">{{ $invoice->invoice_id }}</a>
                                        @else
                                            <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}" class="btn btn-outline-primary">{{ $invoice->invoice_id }}</a>
                                        @endif
                                    </td>
                                    @if (!\Auth::guard('customer')->check())
                                        <td> {{ !empty($invoice->customer) ? $invoice->customer->billing_name : '' }} </td>
                                    @endif
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
                                    <td>{{ \Auth::user()->priceFormat($invoice->getTotalTax()) }}</td>
                                    <td>{{ \Auth::user()->priceFormat($invoice->getDue()) }}</td>
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
                                                        @if (\Auth::guard('customer')->check())
                                                            <div class="action-btn bg-info ms-2">
                                                                    <a href="{{ route('customer.invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                       class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Show "
                                                                       data-original-title="{{ __('Detail') }}">
                                                                        <i class="ti ti-eye text-white"></i>
                                                                    </a>
                                                                </div>
                                                        @else
                                                            <div class="action-btn bg-info ms-2">
                                                                    <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                       class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Show "
                                                                       data-original-title="{{ __('Detail') }}">
                                                                        <i class="ti ti-eye text-white"></i>
                                                                    </a>
                                                                </div>
                                                        @endif
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
                                <td colspan="10" style="border: 1px solid black; text-align: center; background-color:#008b8b; color:white; font-weight: bold;"><strong>Total Amount : {{ \Auth::user()->priceFormat($totalAmount) }} </strong></td>
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
                                @if (!\Auth::guard('customer')->check())
                                    <th>{{ __('Customer') }}</th>
                                @endif
                                <th>{{ __('Issue Date') }}</th>
                                <th>{{ __('Due Date') }}</th>
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
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td class="Id">
                                        @if (\Auth::guard('customer')->check())
                                            <a href="{{ route('customer.invoice.show', \Crypt::encrypt($invoice->id)) }}" class="btn btn-outline-primary">{{ $invoice->invoice_id }}</a>
                                        @else
                                            <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}" class="btn btn-outline-primary">{{ $invoice->invoice_id }}</a>
                                        @endif
                                    </td>
                                    @if (!\Auth::guard('customer')->check())
                                        <td> {{ !empty($invoice->customer) ? $invoice->customer->billing_name : '' }} </td>
                                    @endif
                                    <td>{{ Auth::user()->dateFormat($invoice->issue_date) }}</td>
                                    <td>
                                        @if ($invoice->due_date < date('Y-m-d'))
                                            <div class="text-danger">{{ \Auth::user()->dateFormat($invoice->due_date) }}</div>
                                        @else
                                            {{ \Auth::user()->dateFormat($invoice->due_date) }}
                                        @endif
                                    </td>
                                    <td>{{ \Auth::user()->priceFormat($invoice->getDue()) }}</td>
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
                                                        @if (\Auth::guard('customer')->check())
                                                            <div class="action-btn bg-info ms-2">
                                                                    <a href="{{ route('customer.invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                       class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Show "
                                                                       data-original-title="{{ __('Detail') }}">
                                                                        <i class="ti ti-eye text-white"></i>
                                                                    </a>
                                                                </div>
                                                        @else
                                                            <div class="action-btn bg-info ms-2">
                                                                    <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                       class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Show "
                                                                       data-original-title="{{ __('Detail') }}">
                                                                        <i class="ti ti-eye text-white"></i>
                                                                    </a>
                                                                </div>
                                                        @endif
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
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
