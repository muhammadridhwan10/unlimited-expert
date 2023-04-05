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
                    <div class="card-header"><h6 class="mb-0">{{__('Financial Data')}}</h6></div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th scope="col">{{'M'}}</th>
                                    <th scope="col">{{'LK'}}</th>
                                    <th scope="col">{{'C/N'}}</th>
                                    <th scope="col">{{'RP'}}</th>
                                    <th scope="col">{{'Add.1'}}</th>
                                    <th scope="col">{{'Add.2'}}</th>
                                    <th scope="col">{{'Add.3'}}</th>
                                    <th scope="col">{{'CoA'}}</th>
                                    <th scope="col">{{'Account'}}</th>
                                    <th scope="col">{{'UNAUDITED 2021'}}</th>
                                    <th scope="col">{{'AUDITED 2021'}}</th>
                                    <th scope="col">{{'INHOUSE 2022'}}</th>
                                    <th scope="col">{{'Dr.'}}</th>
                                    <th scope="col">{{'Cr.'}}</th>
                                    <th scope="col">{{'Audited 2022'}}</th>
                                    <th scope="col">{{'Jan'}}</th>
                                    <th scope="col">{{'Feb'}}</th>
                                    <th scope="col">{{'Mar'}}</th>
                                    <th scope="col">{{'Apr'}}</th>
                                    <th scope="col">{{'May'}}</th>
                                    <th scope="col">{{'Jun'}}</th>
                                    <th scope="col">{{'Jul'}}</th>
                                    <th scope="col">{{'Aug'}}</th>
                                    <th scope="col">{{'Sep'}}</th>
                                    <th scope="col">{{'Oct'}}</th>
                                    <th scope="col">{{'Nov'}}</th>
                                    <th scope="col">{{'Dec'}}</th>
                                    <th scope="col">{{'Triwulan 1'}}</th>
                                    <th scope="col">{{'Triwulan 2'}}</th>
                                    <th scope="col">{{'Triwulan 3'}}</th>
                                    <th scope="col">{{'Triwulan 4'}}</th>
                                </tr>
                                </thead>
                                <tbody class="list">
                                @if(count(array($financial_statement)) > 0)
                                    @foreach($financial_statement as $financial_statements)
                                        <tr>
                                            <td>{{ $financial_statements->m }}</td>
                                            <td>{{ $financial_statements->lk }}</td>
                                            <td>{{ $financial_statements->cn }}</td>
                                            <td>{{ $financial_statements->rp }}</td>
                                            <td>{{ $financial_statements->add1 }}</td>
                                            <td>{{ $financial_statements->add2 }}</td>
                                            <td>{{ $financial_statements->add3 }}</td>
                                            <td>{{ $financial_statements->coa }}</td>
                                            <td>{{ $financial_statements->account }}</td>
                                            <td>{{ number_format($financial_statements->unaudited2020) }}</td>
                                            <td>{{ number_format($financial_statements->audited2021) }}</td>
                                            <td>{{ number_format($financial_statements->inhouse2022) }}</td>
                                            <td>{{ number_format($financial_statements->dr) }}</td>
                                            <td>{{ number_format($financial_statements->cr) }}</td>
                                            <td>{{ number_format($financial_statements->audited2022) }}</td>
                                            <td>{{ number_format($financial_statements->jan) }}</td>
                                            <td>{{ number_format($financial_statements->feb) }}</td>
                                            <td>{{ number_format($financial_statements->mar) }}</td>
                                            <td>{{ number_format($financial_statements->apr) }}</td>
                                            <td>{{ number_format($financial_statements->may) }}</td>
                                            <td>{{ number_format($financial_statements->jun) }}</td>
                                            <td>{{ number_format($financial_statements->jul) }}</td>
                                            <td>{{ number_format($financial_statements->aug) }}</td>
                                            <td>{{ number_format($financial_statements->sep) }}</td>
                                            <td>{{ number_format($financial_statements->oct) }}</td>
                                            <td>{{ number_format($financial_statements->nov) }}</td>
                                            <td>{{ number_format($financial_statements->dec) }}</td>
                                            <td>{{ number_format($financial_statements->triwulan1) }}</td>
                                            <td>{{ number_format($financial_statements->triwulan2) }}</td>
                                            <td>{{ number_format($financial_statements->triwulan3) }}</td>
                                            <td>{{ number_format($financial_statements->triwulan4) }}</td>
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
