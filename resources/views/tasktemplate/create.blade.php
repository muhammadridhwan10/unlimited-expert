@extends('layouts.admin')

@section('page-title')
    {{ __('Project Task Template Create') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tasktemplate.index') }}">{{ __('Project Task Template') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Project Task Template Create') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('Create Task Template') }}
                    </h5>
                    <a href="{{ route('tasktemplate.index') }}" class="btn btn-sm btn-primary">
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body">
                    {{ Form::open(['url' => 'tasktemplate', 'class' => 'w-100']) }}

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
                                    <th>{{ __('Task Name') }}</th>
                                    <th>{{ __('Estimated Hours') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        {{ Form::text('tasks[0][name]', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Task Name'), 'style' => 'width: 300px;']) }}
                                    </td>
                                    <td>
                                        {{ Form::number('tasks[0][estimated_hrs]', '', ['class' => 'form-control', 'required' => 'required', 'min' => '0', 'step' => '0.01','style' => 'width: 90px;']) }}
                                    </td>
                                    <td>
                                        {{ Form::textarea('tasks[0][description]', null, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Description')]) }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger remove-task">{{ __('Remove') }}</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary mt-3" id="addTask">{{ __('Add Task') }}</button>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-3 modal-footer">
                        {{ Form::submit(__('Save'), ['class' => 'btn btn-primary']) }}
                    </div>

                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        // Add Task Dynamically
        let taskIndex = 1;
        $('#addTask').on('click', function () {
            const newRow = `
                <tr>
                    <td>
                        <input type="text" name="tasks[${taskIndex}][name]" class="form-control" required placeholder="{{ __('Task Name') }}" style="width: 300px;">
                    </td>
                    <td>
                        <input type="number" name="tasks[${taskIndex}][estimated_hrs]" class="form-control" required min="0" step="0.01" style="width: 90px;"">
                    </td>
                    <td>
                        <textarea name="tasks[${taskIndex}][description]" class="form-control" rows="2" placeholder="{{ __('Description') }}"></textarea>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger remove-task">{{ __('Remove') }}</button>
                    </td>
                </tr>
            `;
            $('#taskTable tbody').append(newRow);
            taskIndex++;
        });

        // Remove Task Dynamically
        $(document).on('click', '.remove-task', function () {
            $(this).closest('tr').remove();
        });
    </script>
@endpush