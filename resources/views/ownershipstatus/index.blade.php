@extends('layouts.admin')
@section('page-title')
    {{__('Manage Ownership Status')}}
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
    <li class="breadcrumb-item">{{__('Ownership Status')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
    @can('create user')
            <a href="{{ route('ownershipstatus.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Create')}}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
</div>

@endsection



@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{ __('Ownership Status Name') }}</th>
                                @if (Gate::check('edit user') || Gate::check('delete user'))
                                    <th>{{ __('Action') }}</th>
                                @endif
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($ownershipstatus as $ownershipstatuss)
                            <!-- <?php
                                // dd($template);
                            ?> -->
                                <tr>
                                    <td>{{($ownershipstatuss->name)}}</td>
                                    @if (Gate::check('edit user') || Gate::check('delete user'))
                                        <td class="Action">
                                                <span>
                                                    @can('edit user')
                                                        <div class="action-btn bg-primary ms-2">
                                                                <a href="{{ route('ownershipstatus.edit', \Crypt::encrypt($ownershipstatuss->id)) }}"
                                                                   class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Edit "
                                                                   data-original-title="{{ __('Edit') }}">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                    @endcan
                                                    @can('delete user')
                                                        <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['ownershipstatus.destroy', $ownershipstatuss->id], 'id' => 'delete-form-' . $ownershipstatuss->id]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para " data-bs-toggle="tooltip" title="{{__('Delete')}}"
                                                                       data-original-title="{{ __('Delete') }}"
                                                                       data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                       data-confirm-yes="document.getElementById('delete-form-{{ $ownershipstatuss->id }}').submit();">
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
