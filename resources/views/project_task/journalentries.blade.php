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
                <a href="{{ route('projects.tasks.create.journal.data',[$project->id, $task->id]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Add Journal')}}">
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
                    @php
                        $prevAdjCode = null;
                        $totalDr = 0;
                        $totalCr = 0;
                    @endphp
                    @foreach($journaldata as $journaldatas)
                        @php
                            $totalDr += $journaldatas->dr;
                            $totalCr += $journaldatas->cr;
                        @endphp

                    @endforeach
                    @if ($totalDr == 0 && $totalCr == 0)
                        <tr>
                            <div class="card-header"><h6 class="mb-0">{{__('Proposed Adjustment / Reclassification Journal Entries')}}</h6></div>
                        </tr>
                    @elseif ($totalDr == $totalCr)
                        <tr>
                            <div class="card-header"><h6 class="mb-0" style='color:green;'>{{__('Balance')}} <i class="fas fa-check"></i></h6></div>
                        </tr>
                    @else
                        <tr>
                            <div class="card-header"><h6 class="mb-0" style='color:red;'>{{__('Not Balance')}} <i class="fas fa-times"></i></h6></div>
                        </tr>
                    @endif
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatable">
                                    <thead>
                                        <tr>
                                            <th style="text-align: center; width:80px;" scope="col">{{'Adj Code'}}</th>
                                            <th style="text-align: center; width:500px;" scope="col">{{'Account Name'}}</th>
                                            <th style="text-align: center; width:150px;" scope="col">{{'Adj Dr.'}}</th>
                                            <th style="text-align: center; width:150px;" scope="col">{{'Adj Cr.'}}</th>
                                            <th style="text-align: center;" scope="col">{{'Notes'}}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @if(count(array($journaldata)) > 0)
                                            @foreach($journaldata as $journaldatas)
                                                <tr>
                                                    <td style="width:80px;">
                                                        @if ($prevAdjCode !== $journaldatas->adj_code)
                                                            {{ substr_replace($journaldatas->adj_code, ' ', 4, 0) }}
                                                        @endif
                                                    </td>
                                                    <td style="border: 1px solid black; width:500px;">{{ $journaldatas->lk->account }}</td>
                                                    <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($journaldatas->dr))? number_format($journaldatas->dr):'-' }}</td>
                                                    <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($journaldatas->cr))? number_format($journaldatas->cr):'-' }}</td>
                                                    <td style="border: 1px solid black; text-align: center;" width="50%">{{ $journaldatas->notes }}</td>
                                                    <td>
                                                        @can('edit project task')
                                                        <div class="action-btn bg-primary ms-2">
                                                                <a href="{{ route('journal.data.edit', [$project->id, \Crypt::encrypt($task->id), $journaldatas->id]) }}"
                                                                   class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Edit "
                                                                   data-original-title="{{ __('Edit') }}">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('edit project task')
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => array('journal.data.delete',[$project->id, \Crypt::encrypt($task->id), $journaldatas->id]),'class'=>'delete-form-btn','id'=>'delete-form-'.$journaldatas->id]) !!}
                                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$journaldatas->id}}').submit();">
                                                                            <i class="ti ti-trash text-white"></i>
                                                                        </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                        @endcan
                                                    </td>
                                                </tr>
                                                @php
                                                    $prevAdjCode = $journaldatas->adj_code;
                                                @endphp
                                            @endforeach
                                        @else
                                            <tr>
                                                <th scope="col" colspan="7">
                                                    <h6 class="text-center">{{__('No Financial Data Found')}}</h6>
                                                </th>
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
