@extends('layouts.admin')
@section('page-title')
    {{__('Accountant Edit')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('accountant.index')}}">{{__('Accountant')}}</a></li>
    <li class="breadcrumb-item">{{__('Accountant Edit')}}</li>
@endsection
@push('script-page')
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>
@endpush
@section('content')
    {{--    @dd($invoice)--}}
    <div class="row">
        {{ Form::model($accountant, array('route' => array('accountant.update', $accountant->id), 'method' => 'PUT','class'=>'w-100')) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('office_id', __('Office (KAP)'),['class'=>'form-label']) }}
                                        {{ Form::select('office_id', $office,null, array('class' => 'form-control select','required'=>'required')) }}
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
            <h5 class=" d-inline-block mb-4">{{__('Accountant')}}</h5>
            <div class="card repeater">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                            <tr>
                                <th>{{__('Accountant Name')}}</th>
                                <th>{{__('Public Accountant Code')}}</th>
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
                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::text('client_public_accountant_code',null, array('class' => 'form-control','required'=>'required','required'=>'required')) }}
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
            <input type="button" value="{{__('Cancel')}}" onclick="location.href = '{{route("accountant.index")}}';" class="btn btn-light">
            <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
        </div>
        {{ Form::close() }}
    </div>
@endsection

