@extends('layouts.admin')
@section('page-title')
   <span style="font-size: 25px;">{{ucwords('Audit Memorandum')}}</span>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
@endpush
@push('script-page')
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
                                            $('#summernote').summernote({
                                                height: 200,
                                                toolbar: [
                                                    ['style', ['style']],
                                                    ['font', ['bold', 'underline', 'clear']],
                                                    ['fontname', ['fontname']],
                                                    ['fontsize', ['fontsize']],
                                                    ['color', ['color']],
                                                    ['para', ['ul', 'ol', 'paragraph']],
                                                    ['table', ['table']],
                                                    ['view', ['fullscreen', 'codeview', 'help']]
                                                ]
                                            });
                                        });

    </script>


@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show',$project->id)}}">    {{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.index',$project->id)}}">{{__('Task')}}</a></li>
    <li class="breadcrumb-item">{{__('Audit Memorandum')}}</li>
@endsection
@push('script-page')
@endpush
@section('action-btn')
@endsection

@section('content')
{{ Form::open(['route' => ['store.audit.memorandum', $project->id], 'method' => 'post']) }}
    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    <input type="hidden" name="materialitas_id" class = "form-control materialitas_id">
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                <div class="card-header"><h6 class="mb-0" style="font-size: 15px;">{{__('Audit Memorandum Project ' . $project->project_name . ' Tahun ' . date('Y', strtotime($project->start_date)))}}</h6></div>
                    <div class="card-body">
                        <div class="row">
                                <div class="form-group col-12">
                                @if(isset($auditmemorandum->content))
                                    {{ Form::textarea('content', $auditmemorandum->content, ['class' => 'form-control summernote', 'id' => 'summernote', 'required' => 'required']) }}
                                @else
                                    {{ Form::textarea('content', null, ['class' => 'form-control summernote', 'id' => 'summernote', 'required' => 'required']) }}
                                @endif
                                </div>      
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <div class="modal-footer">
            <input type="submit" value="{{__('Save')}}" class="btn btn-simpan  btn-primary">
        </div>
{{ Form::close() }}
@endsection

