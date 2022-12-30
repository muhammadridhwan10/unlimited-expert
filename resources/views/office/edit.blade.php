@extends('layouts.admin')
@section('page-title')
    {{__('Office Edit')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('office.index')}}">{{__('Office')}}</a></li>
    <li class="breadcrumb-item">{{__('Office Edit')}}</li>
@endsection
@push('script-page')
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>
@endpush
@section('content')
    {{--    @dd($invoice)--}}
    <div class="row">
        {{ Form::model($office, array('route' => array('office.update', $office->id), 'method' => 'PUT','class'=>'w-100')) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('service_type_id', __('Service Type'),['class'=>'form-label']) }}
                                        {{ Form::select('service_type_id', $servicetype,null, array('class' => 'form-control select','required'=>'required')) }}
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
            <h5 class=" d-inline-block mb-4">{{__('Office')}}</h5>
            <div class="card repeater">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                            <tr>
                                <th>{{__('Office Name')}}</th>
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
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" onclick="location.href = '{{route("office.index")}}';" class="btn btn-light">
            <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
        </div>
        {{ Form::close() }}
    </div>
@endsection

