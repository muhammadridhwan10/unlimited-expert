@extends('layouts.admin')
@section('page-title')
    {{__('Manage Mapping Account Data')}}
@endsection
@push('script-page')
    <script>
        function copyToClipboard(element) {

            var copyText = element.id;
            document.addEventListener('copy', function (e) {
                e.clipboardData.setData('text/plain', copyText);
                e.preventDefault();
            }, true);

            document.execCommand('copy');
            show_toastr('success', 'Url copied to clipboard', 'success');
        }
    </script>
@endpush


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Mapping Account Data')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        {{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
        {{--            <i class="ti ti-filter"></i>--}}
        {{--        </a>--}}

        {{-- <a href="{{ route('invoice.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Export')}}">
            <i class="ti ti-file-export"></i>
        </a> --}}

        @can('create mapping account data')
            <a href="{{ route('mappingaccountdata.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Create')}}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection



@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['mappingaccountdata.index'], 'method' => 'GET', 'id' => 'frm_submit']) }}
                        <div class="row d-flex align-items-center justify-content-end">
                            @if (!\Auth::guard('customer')->check())
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('materialitas', __('Account Group'),['class'=>'form-label'])}}
                                        {{ Form::select('materialitas', $materialitas, isset($_GET['materialitas']) ? $_GET['materialitas'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                            @endif
                            <div class="col-auto float-end ms-2 mt-4">

                                <a href="#" class="btn btn-sm btn-primary"
                                   onclick="document.getElementById('frm_submit').submit(); return false;"
                                   data-toggle="tooltip" data-original-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="{{ route('mappingaccountdata.index') }}" class="btn btn-sm btn-danger" data-toggle="tooltip"
                                    data-original-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                </a>
                            </div>

                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{ __('Group Name') }}</th>
                                <th> {{ __('Mapping Code') }}</th>
                                <th>{{ __('Account Classification') }}</th>
                                @if (Gate::check('edit mapping account data') || Gate::check('delete mapping account data'))
                                    <th>{{ __('Action') }}</th>
                                @endif
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($mapping_account as $mapping_accounts)
                                <tr>
                                    <td> {{ !empty($mapping_accounts->materialitas) ? $mapping_accounts->materialitas->name : '' }} </td>
                                    <td>{{($mapping_accounts['code'])}}</td>
                                    <td>{{($mapping_accounts['name'])}}</td>
                                    @if (Gate::check('edit mapping account data') || Gate::check('delete mapping account data'))
                                        <td class="Action">
                                                <span>
                                                    @can('edit project task template')
                                                        <div class="action-btn bg-primary ms-2">
                                                                <a href="{{ route('mappingaccountdata.edit', \Crypt::encrypt($mapping_accounts->id)) }}"
                                                                   class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Edit "
                                                                   data-original-title="{{ __('Edit') }}">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                    @endcan
                                                    @can('delete project task template')
                                                        <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['mappingaccountdata.destroy', $mapping_accounts->id], 'id' => 'delete-form-' . $mapping_accounts->id]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para " data-bs-toggle="tooltip" title="{{__('Delete')}}"
                                                                       data-original-title="{{ __('Delete') }}"
                                                                       data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                       data-confirm-yes="document.getElementById('delete-form-{{ $mapping_accounts->id }}').submit();">
                                                                        <i class="ti ti-trash text-white"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                    @endcan
                                                </span>
                                        </td>
                                    @endif
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
