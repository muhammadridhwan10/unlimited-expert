@extends('layouts.admin')
@section('page-title')
    {{ucwords($task->name)}}
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
@endpush
@push('script-page')
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>


@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show',$project->id)}}">    {{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.index',$project->id)}}">{{__('Task')}}</a></li>
    <li class="breadcrumb-item">{{__($task->name)}}</li>
@endsection
@push('script-page')
@endpush
@section('action-btn')
    <div class="float-end">
            @can('create project task')
                <a href="#" data-size="lg" data-url="{{ route('projects.tasks.create.financial.statement',[$project->id, $task->id]) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Financial Statement')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-plus"></i>
                </a>
            @endcan
    </div>

@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-end">
                                <div class="px-1 py-0 row align-items-center">
                                <form action="{{ route('import', $project->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <input type="file" name="file" id="file" class="form-control">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Import</button>
                                </form>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-header"><h6 class="mb-0">{{__('Financial Data Tahunan')}}</h6></div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">{{'CoA'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Account'}}</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-3 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-2 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Inhouse ' . $project->book_year}}</th>
                                    <th style="text-align: center;" scope="col">{{'Dr.'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Cr.'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited ' . $project->book_year}}</th>
                                </tr>
                                </thead>
                                <tbody class="list">
                                @if(count(array($financial_statement)) > 0)
                                    @foreach($financial_statement as $financial_statements)
                                        <tr>
                                            <td style="border: 1px solid black; width:100px;">{{ $financial_statements->coa }}</td>
                                            <td style="border: 1px solid black;">{{ $financial_statements->account }}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->prior_period2))? (number_format($financial_statements->prior_period2) < 0 ? '('.number_format(abs($financial_statements->prior_period2)).')' : number_format($financial_statements->prior_period2)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->prior_period))? (number_format($financial_statements->prior_period) < 0 ? '('.number_format(abs($financial_statements->prior_period)).')' : number_format($financial_statements->prior_period)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->inhouse))? (number_format($financial_statements->inhouse) < 0 ? '('.number_format(abs($financial_statements->inhouse)).')' : number_format($financial_statements->inhouse)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->dr))? (number_format($financial_statements->dr) < 0 ? '('.number_format(abs($financial_statements->dr)).')' : number_format($financial_statements->dr)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->cr))? (number_format($financial_statements->cr) < 0 ? '('.number_format(abs($financial_statements->cr)).')' : number_format($financial_statements->cr)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->audited))? (number_format($financial_statements->audited) < 0 ? '('.number_format(abs($financial_statements->audited)).')' : number_format($financial_statements->audited)): number_format($financial_statements->inhouse)}}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th scope="col" colspan="7"><h6 class="text-center">{{__('No Financial Data Found')}}</h6></th>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-header"><h6 class="mb-0">{{__('Financial Data Bulanan')}}</h6></div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatables">
                                <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">{{'CoA'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Account'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Jan'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Feb'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Mar'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Apr'}}</th>
                                    <th style="text-align: center;" scope="col">{{'May'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Jun'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Jul'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Aug'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Sep'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Oct'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Nov'}}</th>
                                    <th style="text-align: center;" scope="col">{{'Dec'}}</th>

                                </tr>
                                </thead>
                                <tbody class="list">
                                @if(count(array($financial_statement)) > 0)
                                    @foreach($financial_statement as $financial_statements)
                                        <tr>
                                            <td style="border: 1px solid black;">{{ $financial_statements->coa }}</td>
                                            <td style="border: 1px solid black;">{{ $financial_statements->account }}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->jan))? (number_format($financial_statements->jan) < 0 ? '('.number_format(abs($financial_statements->jan)).')' : number_format($financial_statements->jan)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->feb))? (number_format($financial_statements->feb) < 0 ? '('.number_format(abs($financial_statements->feb)).')' : number_format($financial_statements->feb)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->mar))? (number_format($financial_statements->mar) < 0 ? '('.number_format(abs($financial_statements->mar)).')' : number_format($financial_statements->mar)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->apr))? (number_format($financial_statements->apr) < 0 ? '('.number_format(abs($financial_statements->apr)).')' : number_format($financial_statements->apr)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->may))? (number_format($financial_statements->may) < 0 ? '('.number_format(abs($financial_statements->may)).')' : number_format($financial_statements->may)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->jun))? (number_format($financial_statements->jun) < 0 ? '('.number_format(abs($financial_statements->jun)).')' : number_format($financial_statements->jun)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->jul))? (number_format($financial_statements->jul) < 0 ? '('.number_format(abs($financial_statements->jul)).')' : number_format($financial_statements->jul)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->aug))? (number_format($financial_statements->aug) < 0 ? '('.number_format(abs($financial_statements->aug)).')' : number_format($financial_statements->aug)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->sep))? (number_format($financial_statements->sep) < 0 ? '('.number_format(abs($financial_statements->sep)).')' : number_format($financial_statements->sep)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->oct))? (number_format($financial_statements->oct) < 0 ? '('.number_format(abs($financial_statements->oct)).')' : number_format($financial_statements->oct)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->nov))? (number_format($financial_statements->nov) < 0 ? '('.number_format(abs($financial_statements->nov)).')' : number_format($financial_statements->nov)): '-'}}</td>
                                            <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($financial_statements->dec))? (number_format($financial_statements->dec) < 0 ? '('.number_format(abs($financial_statements->dec)).')' : number_format($financial_statements->dec)): '-'}}</td>
                                            
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th scope="col" colspan="7"><h6 class="text-center">{{__('No Financial Data Found')}}</h6></th>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-header">
                        <div class="float-end">
                                @can('create project task')
                                    <a href="{{ route('projects.tasks.create.mappingaccount',[$project->id, $task->id]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Add Mapping Account')}}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                @endcan
                        </div>
                    <h6 class="mb-0">{{__('Mapping Account')}}</h6>
                    
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatabless">
                                <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">{{'Account Name'}}</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-3 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-2 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Inhouse ' . $project->book_year}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited ' . $project->book_year}}</th>
                                </tr>
                                </thead>
                                <tbody class="list">
                                @if(count(array($result)) > 0)
                                    @foreach ($result as $results)
                                        <tr>
                                            <td  style="border: 1px solid black; width:100px;">{{ $results['name'] }}</td>
                                            <td  style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($results['prior_period2'])) ? number_format($results['prior_period2']) : '-' }}</td>
                                            <td  style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($results['prior_period'])) ? number_format($results['prior_period']) : '-' }}</td>
                                            <td  style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($results['inhouse'])) ? number_format($results['inhouse']) : '-' }}</td>
                                            <td  style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($results['audited'])) ? number_format($results['audited']) : '-' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th scope="col" colspan="7"><h6 class="text-center">{{__('No Financial Data Found')}}</h6></th>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
