@extends('layouts.admin')
@section('page-title')
    {{__('Project Task Template Edit')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('tasktemplate.index')}}">{{__('Project Task Template')}}</a></li>
    <li class="breadcrumb-item">{{__('Project Task Template Edit')}}</li>
@endsection
@push('script-page')
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>
@endpush
@section('content')
    {{--    @dd($invoice)--}}
    <div class="row">
        {{ Form::model($tasktemplate, array('route' => array('tasktemplate.update', $tasktemplate->id), 'method' => 'PUT','class'=>'w-100')) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    {{ Form::label('category_id', __('Category'),['class'=>'form-label']) }}
                                    {{ Form::select('category_id', $category,null, array('class' => 'form-control select','required'=>'required')) }}
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('category_template_id', __('Category Template'),['class'=>'form-label']) }}
                                        {{ Form::select('category_template_id', $category_template,null, array('class' => 'form-control select','required'=>'required')) }}
                                    </div>
                                </div>

                                @if(!$customFields->isEmpty())
                                    <div class="col-md-6">
                                        <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                                            @include('customFields.formBuilder')
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <h5 class=" d-inline-block mb-4">{{__('Task Details')}}</h5>
            <div class="card repeater">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                            <tr>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Estimated Hours')}} </th>
                                <!-- <th>{{__('Start Date')}}</th>
                                <th>{{__('End Date')}}</th> -->
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="ui-sortable" data-repeater-item>
                            <tr>
                                {{ Form::hidden('id',null, array('class' => 'form-control id')) }}
                                <td>
                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::text('name',null, array('class' => 'form-control','required'=>'required','required'=>'required')) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::text('estimated_hrs',null, array('class' => 'form-control','required'=>'required','required'=>'required')) }}
                                    </div>
                                </td>
                                <!-- <td>
                                    <div class="form-group price-input input-group search-form">
                                    {{Form::date('start_date',null,array('class'=>'form-control','required'=>'required'))}}
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group price-input input-group search-form">
                                    {{Form::date('end_date',null,array('class'=>'form-control','required'=>'required'))}}
                                    </div>
                                </td> -->
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="form-group">
                                        {{ Form::textarea('description', null, ['class'=>'form-control pro_description','rows'=>'2','placeholder'=>__('Description')]) }}
                                    </div>
                                </td>
                                <td colspan="5"></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" onclick="location.href = '{{route("tasktemplate.index")}}';" class="btn btn-light">
            <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
        </div>
        {{ Form::close() }}
    </div>
@endsection

