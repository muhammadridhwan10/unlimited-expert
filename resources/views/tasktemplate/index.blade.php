@extends('layouts.admin')
@section('page-title')
    {{__('Manage Project Task Template')}}
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
    <li class="breadcrumb-item">{{__('Project Task Template')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        {{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
        {{--            <i class="ti ti-filter"></i>--}}
        {{--        </a>--}}

        <a href="{{ route('invoice.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Export')}}">
            <i class="ti ti-file-export"></i>
        </a>

        @can('create project task template')
            <a href="{{ route('tasktemplate.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Create')}}">
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
                        @if (!\Auth::guard('customer')->check())
                            {{ Form::open(['route' => ['tasktemplate.index'], 'method' => 'GET', 'id' => 'customer_submit']) }}
                        @endif
                        <div class="row d-flex align-items-center justify-content-end">
                            @if (!\Auth::guard('customer')->check())
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('category', __('Category'),['class'=>'form-label'])}}
                                        {{ Form::select('category', $category, isset($_GET['category']) ? $_GET['category'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                            @endif
                            <div class="col-auto float-end ms-2 mt-4">

                                <a href="#" class="btn btn-sm btn-primary"
                                   onclick="document.getElementById('customer_submit').submit(); return false;"
                                   data-toggle="tooltip" data-original-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>

                                @if (!\Auth::guard('customer')->check())
                                    <a href="{{ route('tasktemplate.index') }}" class="btn btn-sm btn-danger" data-toggle="tooltip"
                                       data-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                    </a>
                                @else
                                    <a href="{{ route('customer.index') }}" class="btn btn-sm btn-primary" data-toggle="tooltip"
                                       data-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-trash text-white-off"></i></span>
                                    </a>
                                @endif
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
                                <th> {{ __('Category') }}</th>
                                @if (!\Auth::guard('customer')->check())
                                    <th>{{ __('Name') }}</th>
                                @endif
                                <th>{{ __('Estimated Hours') }}</th>
                                <!-- <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th> -->
                                <th>{{ __('Status') }}</th>
                                @if (Gate::check('edit project task template') || Gate::check('delete project task template'))
                                    <th>{{ __('Action') }}</th>
                                @endif
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($templates as $template)
                            <!-- <?php
                                // dd($template);
                            ?> -->
                                <tr>
                                    @if (!\Auth::guard('customer')->check())
                                        <td> {{ !empty($template->category) ? $template->category->name : '' }} </td>
                                    @endif
                                    <td>{{($template['name'])}}</td>
                                    <td>{{($template->estimated_hrs)}}</td>
                                    <!-- <td>{{ Auth::user()->dateFormat($template->start_date) }}</td>
                                    <td>{{ Auth::user()->dateFormat($template->end_date) }}</td> -->
                                    <td>
                                    @if($template->stage_id == 1)
                                            <span
                                                class="status_badge badge bg-primary p-2 px-3 rounded">To Do</span>
                                    @endif
                                    </td>
                                    @if (Gate::check('edit project task template') || Gate::check('delete project task template'))
                                        <td class="Action">
                                                <span>
                                                    @can('edit project task template')
                                                        <div class="action-btn bg-primary ms-2">
                                                                <a href="{{ route('tasktemplate.edit', \Crypt::encrypt($template->id)) }}"
                                                                   class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Edit "
                                                                   data-original-title="{{ __('Edit') }}">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                    @endcan
                                                    @can('delete project task template')
                                                        <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['tasktemplate.destroy', $template->id], 'id' => 'delete-form-' . $template->id]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para " data-bs-toggle="tooltip" title="{{__('Delete')}}"
                                                                       data-original-title="{{ __('Delete') }}"
                                                                       data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                       data-confirm-yes="document.getElementById('delete-form-{{ $template->id }}').submit();">
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
