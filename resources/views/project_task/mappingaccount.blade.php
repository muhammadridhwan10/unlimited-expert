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
                <a href="{{ route('projects.tasks.create.mappingaccount',[$project->id, $task->id]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Add Mapping Account')}}">
                    <i class="ti ti-plus"></i>
                </a>
            @endcan
    </div>

@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-header"><h6 class="mb-0">{{__('Mapping Account')}}</h6></div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatable">
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
                                {{-- @if(count(array($financial_statement)) > 0)
                                    @foreach($financial_statement as $financial_statements)
                                            <tr>
                                                <td>{{ $financial_statements->coa }}</td>
                                                <td>{{ $financial_statements->account }}</td>
                                                <td>{{ !empty(number_format($financial_statements->dr))? number_format($financial_statements->dr):'-' }}</td>
                                                <td>{{ !empty(number_format($financial_statements->cr))? number_format($financial_statements->cr):'-' }}</td>
                                                <td width="50%">{{ $financial_statements->notes }}</td>
                                                
                                            </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th scope="col" colspan="7"><h6 class="text-center">{{__('No Financial Data Found')}}</h6></th>
                                    </tr>
                                @endif --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
