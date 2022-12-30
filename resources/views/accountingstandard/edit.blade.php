@extends('layouts.admin')
@section('page-title')
    {{__('Accounting Standard Edit')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('accountingstandard.index')}}">{{__('Accounting Standard')}}</a></li>
    <li class="breadcrumb-item">{{__('Accounting Standard Edit')}}</li>
@endsection
@push('script-page')
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>
@endpush
@section('content')
    {{--    @dd($invoice)--}}
    <div class="row">
        {{ Form::model($accountingstandard, array('route' => array('accountingstandard.update', $accountingstandard->id), 'method' => 'PUT','class'=>'w-100')) }}
        <div class="col-12">
            <h5 class=" d-inline-block mb-4">{{__('Accounting Standard')}}</h5>
            <div class="card repeater">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                            <tr>
                                <th>{{__('Accounting Standard Name')}}</th>
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
            <input type="button" value="{{__('Cancel')}}" onclick="location.href = '{{route("accountingstandard.index")}}';" class="btn btn-light">
            <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
        </div>
        {{ Form::close() }}
    </div>
@endsection

