@extends('layouts.admin')
@section('page-title')
    {{__('Manage Client')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('clients.index')}}">{{__('Client')}}</a></li>
    <li class="breadcrumb-item">  {{ ucwords($client->name).__("'s Detail") }}</li>
@endsection
@section('action-btn')

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
                            <button class="nav-link" id="client-invoice-tab"
                                data-bs-toggle="pill" data-bs-target="#client-invoice"
                                type="button">{{ __('Invoices') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link " id="client-project-tab"
                                data-bs-toggle="pill" data-bs-target="#client-project"
                                type="button">{{ __('Projects') }}</button>
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
                                            <h5 class="card-title">{{ __('Company Info') }}</h5>

                                            <div class="row">
                                                {{-- @php
                                                    $totalInvoiceSum = $customer->customerTotalInvoiceSum($customer['id']);
                                                    $totalInvoice = $customer->customerTotalInvoice($customer['id']);
                                                    $averageSale = $totalInvoiceSum != 0 ? $totalInvoiceSum / $totalInvoice : 0;
                                                @endphp --}}
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Company Name ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $client->name }}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('E-Mail') }}</p>
                                                        <h6 class="report-text mb-0">{{ $client->email }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('NPWP') }}</p>
                                                        <h6 class="report-text mb-3">{{ $clients ? $clients->npwp : '-' }}</h6>
                                                        <p class="card-text mb-0">{{ __('Telephone') }}</p>
                                                        <h6 class="report-text mb-0">{{ $clients ? $clients->telp : '-' }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('Country') }}</p>
                                                        <h6 class="report-text mb-3">{{ $clients ? $clients->country : '-' }}</h6>
                                                        <p class="card-text mb-0">{{ __('State') }}</p>
                                                        <h6 class="report-text mb-0">{{ $clients ? $clients->state : '-' }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('City') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $clients ? $clients->city : '-' }}</h6>
                                                        <p class="card-text mb-0">{{ __('Client Business Sector') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $clients && $clients->sector ? $clients->sector->name : '-' }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="p-4">
                                                        <p class="card-text mb-0">{{ __('PIC Name ') }}</p>
                                                        <h6 class="report-text mb-3">
                                                            {{ $clients ? $clients->name_invoice : '-' }}
                                                        </h6>
                                                        <p class="card-text mb-0">{{ __('Position') }}</p>
                                                        <h6 class="report-text mb-0">
                                                            {{ $clients ? $clients->position : '-' }}
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="tab-pane fade" id="client-invoice" role="tabpanel"
                        aria-labelledby="pills-user-tab-3">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body table-border-style table-border-style">
                                            <h5 class="d-inline-block mb-5">{{ __('Invoice') }}</h5>
                                            <div class="table-responsive">
                                                <table class="table datatable">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('Invoice') }}</th>
                                                            <th>{{ __('Issue Date') }}</th>
                                                            <th>{{ __('Due Date') }}</th>
                                                            <th>{{ __('Due Amount') }}</th>
                                                            <th>{{ __('Status') }}</th>
                                                            @if (Gate::check('invoice edit') || Gate::check('invoice delete') || Gate::check('invoice show'))
                                                                <th width="10%"> {{ __('Action') }}</th>
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($client->clientInvoice($client->id) as $invoice)
                                                            <tr>
                                                                <td class="Id">
                                                                @can('invoice show')
                                                                    <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                        class="btn btn-outline-primary">{{ $invoice->invoice_id }}
                                                                    </a>
                                                                @else
                                                                    <a
                                                                        class="btn btn-outline-primary">{{ $invoice->invoice_id }}
                                                                    </a>
                                                                @endcan
                                                                </td>
                                                                <td>{{ $invoice->issue_date }}</td>
                                                                <td>
                                                                    @if ($invoice->due_date < date('Y-m-d'))
                                                                        <p class="text-danger"> {{$invoice->due_date }}</p>
                                                                    @else
                                                                        {{ $invoice->due_date }}
                                                                    @endif
                                                                </td>
                                                                <td>{{ number_format($invoice->getDue()) }}</td>
                                                                <td>
                                                                    @if ($invoice->status == 0)
                                                                        <span
                                                                            class="badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                                    @elseif($invoice->status == 1)
                                                                        <span
                                                                            class="badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                                    @elseif($invoice->status == 2)
                                                                        <span
                                                                            class="badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                                    @elseif($invoice->status == 3)
                                                                        <span
                                                                            class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                                    @elseif($invoice->status == 4)
                                                                        <span
                                                                            class="badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                                    @endif
                                                                </td>
                                                                @if (Gate::check('invoice edit') || Gate::check('invoice delete') || Gate::check('invoice show'))
                                                                    <td class="Action">
                                                                        <span>
                                                                            @can('duplicate invoice')
                                                                                <div class="action-btn bg-secondary ms-2">

                                                                                    {!! Form::open([
                                                                                        'method' => 'get',
                                                                                        'route' => ['invoice.duplicate', $invoice->id],
                                                                                        'id' => 'invoice-duplicate-form-' . $invoice->id,
                                                                                    ]) !!}

                                                                                    <a
                                                                                        class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                                        data-bs-toggle="tooltip"
                                                                                        title="{{ __('Duplicate Invoice') }}"
                                                                                        data-original-title="{{ __('Duplicate') }}"
                                                                                        data-confirm="{{ __('You want to confirm this action. Press Yes to continue or Cancel to go back') }}"
                                                                                        data-confirm-yes="document.getElementById('invoice-duplicate-form-{{ $invoice->id }}').submit();">
                                                                                        <i class="ti ti-copy text-white text-white"></i>
                                                                                    </a>
                                                                                    {!! Form::close() !!}

                                                                                </div>
                                                                            @endcan
                                                                            @can('invoice show')
                                                                                @if (\Auth::user()->type == 'client')
                                                                                    <div class="action-btn bg-warning ms-2">
                                                                                        <a href="{{ route('customer.invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                                            class="mx-3 btn btn-sm align-items-center"
                                                                                            data-bs-toggle="tooltip" title="{{ __('Show') }}"
                                                                                            data-original-title="{{ __('Detail') }}">
                                                                                            <i class="ti ti-eye text-white text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @else
                                                                                    <div class="action-btn bg-warning ms-2">
                                                                                        <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                                            class="mx-3 btn btn-sm align-items-center"
                                                                                            data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                                                            <i class="ti ti-eye text-white text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endif
                                                                            @endcan
                                                                            @can('invoice edit')
                                                                                <div class="action-btn bg-info ms-2">
                                                                                    <a href="{{ route('invoice.edit', \Crypt::encrypt($invoice->id)) }}"
                                                                                        class="mx-3 btn btn-sm  align-items-center"
                                                                                        data-bs-toggle="tooltip"
                                                                                        data-bs-original-title="{{ __('Edit') }}">
                                                                                        <i class="ti ti-pencil text-white"></i>
                                                                                    </a>
                                                                                </div>
                                                                            @endcan
                                                                            @can('invoice delete')
                                                                                <div class="action-btn bg-danger ms-2">
                                                                                    {{ Form::open(['route' => ['invoice.destroy', $invoice->id], 'class' => 'm-0']) }}
                                                                                    @method('DELETE')
                                                                                    <a
                                                                                        class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                                        data-bs-toggle="tooltip" title=""
                                                                                        data-bs-original-title="Delete" aria-label="Delete"
                                                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                                        data-confirm-yes="delete-form-{{ $invoice->id }}">
                                                                                        <i class="ti ti-trash text-white text-white"></i>
                                                                                    </a>
                                                                                    {{ Form::close() }}
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
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="client-project" role="tabpanel"
                        aria-labelledby="pills-user-tab-4">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body table-border-style table-border-style">
                                            <h5 class="d-inline-block mb-5">{{ __('Project') }}</h5>
                                            <div class="table-responsive">
                                                <table class="table datatable">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('Name') }}</th>
                                                            <th>{{ __('Status') }}</th>
                                                            <th>{{ __('Fee') }}</th>
                                                            <th>{{ __('Start Date') }}</th>
                                                            <th>{{ __('End Date') }}</th>
                                                            <th>{{ __('Description') }}</th>
                                                            @if(Gate::check('project show') || Gate::check('project edit') || Gate::check('project delete'))
                                                                <th width="10%"> {{ __('Action') }}</th>
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($client->clientProject($client->id) as $project)
                                                        <tr class="font-style">
                                                            <td>{{ !empty($project) ? $project->project_name : '-' }}</td>
                                                            <td>{{ !empty($project) ? $project->status : '-' }}</td>
                                                             <td>{{ !empty($project) ? \Auth::user()->priceFormat($project->budget) : '-' }}</td>
                                                            <td>{{ !empty($project) ? $project->start_date : '-'}}</td>
                                                            <td>{{ !empty($project) ? $project->end_date : '-' }}</td>
                                                            <td>
                                                                <p style="white-space: nowrap;
                                                                    width: 200px;
                                                                    overflow: hidden;
                                                                    text-overflow: ellipsis;">{{ !empty($project) ? $project->description  : '-'}}
                                                                </p>
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
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
