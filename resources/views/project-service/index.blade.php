@extends('layouts.admin')

@section('page-title')
    {{ __('Project Service') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('List Project Service') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
            <a href="#" data-url="{{ route('project-service.create') }}" data-ajax-popup="true" 
               data-title="{{ __('Create New Project Service') }}" data-bs-toggle="tooltip" 
               title="{{ __('Create') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($serviceTypes->count() > 0)
                                    @foreach ($serviceTypes as $serviceType)
                                        <tr>
                                            <td>{{ $serviceType->name }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $serviceType->code }}</span>
                                            </td>
                                            <td>{{ !empty(Str::limit($serviceType->description, 50)) ? $serviceType->description : '-' }}</td>
                                            <td>
                                                @if($serviceType->is_active)
                                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td class="Action">
                                                <span>

                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" 
                                                        data-url="{{ route('project-service.edit', \Crypt::encrypt($serviceType->id)) }}" 
                                                        data-ajax-popup="true" 
                                                        data-title="{{ __('Edit Project Service') }}" 
                                                        data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>

                                                    <div class="action-btn bg-{{ $serviceType->is_active ? 'secondary' : 'success' }} ms-2">
                                                        <a href="{{ route('project-service.toggle-status', $serviceType->id) }}" 
                                                        class="mx-3 btn btn-sm align-items-center" 
                                                        data-bs-toggle="tooltip" 
                                                        title="{{ $serviceType->is_active ? __('Deactivate') : __('Activate') }}">
                                                            <i class="ti ti-{{ $serviceType->is_active ? 'eye-off' : 'eye' }} text-white"></i>
                                                        </a>
                                                    </div>

                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['project-service.destroy', $serviceType->id], 'id' => 'delete-form-' . $serviceType->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                                                            data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                <i class="ti ti-trash text-white text-white"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th scope="col" colspan="7"><h6 class="text-center">{{__('No Project Service Data Found')}}</h6></th>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection