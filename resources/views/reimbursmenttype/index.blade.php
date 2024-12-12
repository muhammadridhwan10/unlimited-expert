@extends('layouts.admin')

@section('page-title')
    {{__('Manage Reimbursement Type')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Reimbursement Type')}}</li>
@endsection


@section('action-btn')
    <div class="float-end">
        @can('create leave type')
            <a href="#" data-url="{{ route('reimbursmenttype.create') }}" data-ajax-popup="true" data-title="{{__('Create New Reimbursement Type')}}" data-bs-toggle="tooltip" title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Reimbursement Type')}}</th>
                                <th>{{__('Amount / Year')}}</th>
                                <th width="200px">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($reimbursmenttypes as $reimbursmenttype)
                                <tr>
                                    <td>{{ $reimbursmenttype->title }}</td>
                                    <td>{{ number_format($reimbursmenttype->amount)}}</td>

                                    <td>


                                        @can('edit leave type')
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('reimbursmenttype/'.$reimbursmenttype->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Reimbursement Type')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                        @endcan

                                        @can('delete leave type')
                                            <div class="action-btn bg-danger ms-2">

                                                {!! Form::open(['method' => 'DELETE', 'route' => ['reimbursmenttype.destroy', $reimbursmenttype->id],'id'=>'delete-form-'.$reimbursmenttype->id]) !!}
                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$reimbursmenttype->id}}').submit();">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                        @endcan

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

@endsection
