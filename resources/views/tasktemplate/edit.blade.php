@extends('layouts.admin')

@section('page-title')
    {{ __('Project Task Template Edit') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tasktemplate.index') }}">{{ __('Project Task Template') }}</a></li>
    <li class="breadcrumb-item">{{ __('Project Task Template Edit') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('Edit Task Template') }}
                    </h5>
                    <a href="{{ route('tasktemplate.index') }}" class="btn btn-sm btn-primary">
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body">
                    {{ Form::model($tasktemplate, ['route' => ['tasktemplate.update', $tasktemplate->id], 'method' => 'PUT', 'class' => 'w-100']) }}

                    <!-- Category Selection -->
                    <div class="form-group">
                        {{ Form::label('category_id', __('Category'), ['class' => 'form-label']) }}
                        {{ Form::select('category_id', $category, null, ['class' => 'form-control select', 'required' => 'required']) }}
                    </div>

                    <!-- Custom Fields -->
                    @if (!$customFields->isEmpty())
                        <div class="form-group">
                            @include('customFields.formBuilder')
                        </div>
                    @endif

                    <!-- Task Details -->
                    <div class="mt-4">
                        <h6>{{ __('Task Details') }}</h6>
                        <table class="table table-bordered" id="taskTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Estimated Hours') }}</th>
                                    <th>{{ __('Description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        {{ Form::text('name', $tasktemplate->name, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Task Name')]) }}
                                    </td>
                                    <td>
                                        {{ Form::number('estimated_hrs', $tasktemplate->estimated_hrs, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Estimated Hours'), 'min' => '0', 'step' => '0.01']) }}
                                    </td>
                                    <td>
                                        {{ Form::textarea('description', $tasktemplate->description, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Description')]) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-3">
                        {{ Form::submit(__('Update'), ['class' => 'btn btn-primary']) }}
                    </div>

                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection