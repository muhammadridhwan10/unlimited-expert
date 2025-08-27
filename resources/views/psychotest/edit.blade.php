@extends('layouts.admin')

@section('page-title')
    {{__('Edit Psychotest Schedule')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('psychotest-schedule.index')}}">{{__('Psychotest Schedule')}}</a></li>
    <li class="breadcrumb-item">{{__('Edit')}}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{__('Edit Psychotest Schedule')}}</h5>
                <small class="text-muted">{{__('Modify the psychotest schedule details')}}</small>
            </div>
            <div class="card-body">
                {!! Form::model($schedule, ['route' => ['psychotest-schedule.update', $schedule->id], 'method' => 'put', 'id' => 'edit-schedule-form']) !!}
                
                <!-- Candidate Information (Read-only) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6><i class="ti ti-info-circle me-2"></i>{{__('Candidate Information')}}</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-group me-2">
                                            <a href="#" class="avatar rounded-circle avatar-sm">
                                                <img src="{{asset('/storage/uploads/avatar/avatar.png')}}" class="hweb">
                                            </a>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $schedule->candidates->name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $schedule->candidates->email ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <strong>{{__('Job Position')}}:</strong>
                                    @if($schedule->candidates && $schedule->candidates->jobs)
                                        <span class="badge bg-primary ms-2">{{ $schedule->candidates->jobs->title }}</span>
                                    @else
                                        <span class="text-muted ms-2">-</span>
                                    @endif
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="ti ti-lock me-1"></i>{{__('Candidate cannot be changed after creation')}}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Current Category Assignment -->
                @if($schedule->selected_categories || ($categories && $categories->count() > 0))
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-light border">
                                <h6><i class="ti ti-list-check me-2"></i>{{__('Assigned Test Categories')}}</h6>
                                @if($categories && $categories->count() > 0)
                                    <div class="row">
                                        @foreach($categories as $category)
                                            <div class="col-md-4 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-info me-2">{{ $category->name }}</span>
                                                    <small class="text-muted">{{ $category->duration_minutes ?? 0 }}min</small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mb-0 text-muted">{{__('No specific categories assigned - will use default categories')}}</p>
                                @endif
                                <small class="text-muted mt-2 d-block">
                                    <i class="ti ti-info-circle me-1"></i>{{__('Test categories cannot be modified after creation. Create a new schedule to change categories.')}}
                                </small>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Editable Fields -->
                <div class="row">
                    <!-- Current Status -->
                    <div class="form-group col-md-12 mb-3">
                        <label class="form-label">{{__('Current Status')}}</label>
                        <div>
                            @if($schedule->status == 'scheduled')
                                <span class="badge bg-info p-2">{{__('Scheduled')}}</span>
                                <small class="text-muted ms-2">{{__('This schedule can be edited')}}</small>
                            @elseif($schedule->status == 'in_progress')
                                <span class="badge bg-warning p-2">{{__('In Progress')}}</span>
                                <small class="text-muted ms-2">{{__('Limited editing available')}}</small>
                            @else
                                <span class="badge bg-secondary p-2">{{ ucfirst($schedule->status) }}</span>
                                <small class="text-muted ms-2">{{__('Cannot edit - schedule is not active')}}</small>
                            @endif
                        </div>
                    </div>

                    <!-- Duration -->
                    <div class="form-group col-md-4">
                        {!! Form::label('duration_minutes', __('Duration (Minutes)'), ['class' => 'form-label']) !!}
                        {!! Form::number('duration_minutes', null, [
                            'class' => 'form-control', 
                            'min' => 15, 
                            'max' => 300, 
                            'required' => true,
                            'id' => 'duration_minutes'
                        ]) !!}
                        <small class="text-muted">{{__('Total time allowed for all tests (15-300 minutes)')}}</small>
                        @if($categories && $categories->count() > 0)
                            @php
                                $suggestedDuration = $categories->sum('duration_minutes') + 10;
                            @endphp
                            <br><small class="text-info">{{__('Suggested based on categories')}}: {{ $suggestedDuration }} {{__('minutes')}}</small>
                        @endif
                    </div>

                    <!-- Start Time -->
                    <div class="form-group col-md-4">
                        {!! Form::label('start_time', __('Start Time'), ['class' => 'form-label']) !!}
                        {!! Form::datetimeLocal('start_time', $schedule->start_time ? $schedule->start_time->format('Y-m-d\TH:i') : null, [
                            'class' => 'form-control', 
                            'required' => true,
                            'id' => 'start_time'
                        ]) !!}
                        <small class="text-muted">{{__('When the test becomes available')}}</small>
                    </div>

                    <!-- End Time -->
                    <div class="form-group col-md-4">
                        {!! Form::label('end_time', __('End Time'), ['class' => 'form-label']) !!}
                        {!! Form::datetimeLocal('end_time', $schedule->end_time ? $schedule->end_time->format('Y-m-d\TH:i') : null, [
                            'class' => 'form-control', 
                            'required' => true,
                            'id' => 'end_time'
                        ]) !!}
                        <small class="text-muted">{{__('When the test expires')}}</small>
                    </div>

                    <!-- Category Selection Mode (for editing) -->
                    @if($schedule->status == 'scheduled')
                        <div class="form-group col-md-12 mt-3">
                            <label class="form-label">{{__('Update Category Selection')}}</label>
                            <div class="alert alert-warning">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="selection_mode" id="keep_current" value="keep" checked>
                                    <label class="form-check-label" for="keep_current">
                                        <strong>{{__('Keep Current Categories')}}</strong>
                                        <small class="text-muted d-block">Maintain the existing category selection</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="selection_mode" id="auto_mode_edit" value="auto">
                                    <label class="form-check-label" for="auto_mode_edit">
                                        <strong>{{__('Auto Select by Job Position')}}</strong>
                                        <small class="text-muted d-block">Re-select categories based on candidate's job position</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="selection_mode" id="manual_mode_edit" value="manual">
                                    <label class="form-check-label" for="manual_mode_edit">
                                        <strong>{{__('Manual Selection')}}</strong>
                                        <small class="text-muted d-block">Choose specific test categories manually</small>
                                    </label>
                                </div>
                                <input type="hidden" name="auto_select_by_job" id="auto_select_by_job" value="0">
                            </div>
                        </div>

                        <!-- Manual Category Selection for Edit -->
                        <div class="col-md-12" id="manual-categories-edit" style="display: none;">
                            <label class="form-label">{{__('Select Test Categories')}}</label>
                            <div class="row" id="categories-grid-edit">
                                @if($categories && $categories->count() > 0)
                                    @foreach($categories as $category)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card category-card border-success" style="cursor: pointer;">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input category-checkbox" type="checkbox" 
                                                               name="selected_categories[]" value="{{ $category->id }}" 
                                                               id="cat_edit_{{ $category->id }}" checked>
                                                        <label class="form-check-label w-100" for="cat_edit_{{ $category->id }}">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <strong>{{ $category->name }}</strong>
                                                                <div>
                                                                    <span class="badge bg-success">Current</span>
                                                                    @if($category->is_job_specific ?? false)
                                                                        <span class="badge bg-warning">Job Specific</span>
                                                                    @else
                                                                        <span class="badge bg-info">General</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <p class="small text-muted mb-2">{{ $category->description }}</p>
                                                            <div class="d-flex justify-content-between text-sm text-muted">
                                                                <span><i class="ti ti-clock"></i> {{ $category->duration_minutes ?? 0 }} min</span>
                                                                <span><i class="ti ti-help-circle"></i> {{ $category->total_questions ?? 0 }} soal</span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Current Schedule Summary -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0">{{__('Schedule Summary')}}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>{{__('Current Status')}}:</strong><br>
                                        <span class="badge bg-{{ $schedule->status == 'scheduled' ? 'info' : ($schedule->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($schedule->status) }}
                                        </span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{__('Email Status')}}:</strong><br>
                                        @if($schedule->email_sent)
                                            <span class="badge bg-success"><i class="ti ti-check"></i> {{__('Sent')}}</span>
                                        @else
                                            <span class="badge bg-danger"><i class="ti ti-x"></i> {{__('Not Sent')}}</span>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{__('Test Progress')}}:</strong><br>
                                        @php
                                            $progressPercentage = method_exists($schedule, 'getProgressPercentage') ? $schedule->getProgressPercentage() : 0;
                                        @endphp
                                        <span class="badge bg-primary">{{ $progressPercentage }}% Complete</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{__('Last Updated')}}:</strong><br>
                                        <small class="text-muted">{{ $schedule->updated_at ? $schedule->updated_at->format('d M Y H:i') : 'Never' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning Messages -->
                @if($schedule->status != 'scheduled')
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="ti ti-alert-triangle me-2"></i>
                                @if($schedule->status == 'in_progress')
                                    {{__('This test is currently in progress. Some changes may not take effect until the next session.')}}
                                @elseif($schedule->status == 'completed')
                                    {{__('This test has been completed. Only basic information can be modified.')}}
                                @else
                                    {{__('This schedule is not active. Changes will be saved but may not be effective.')}}
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <div class="modal-footer border-top mt-4 pt-3">
                    <a href="{{ route('psychotest-schedule.index') }}" class="btn btn-light me-2">
                        <i class="ti ti-arrow-left me-1"></i>{{__('Cancel')}}
                    </a>
                    <a href="{{ route('psychotest-schedule.show', $schedule->id) }}" class="btn btn-info me-2">
                        <i class="ti ti-eye me-1"></i>{{__('View Details')}}
                    </a>
                    <input type="submit" value="{{__('Update Schedule')}}" class="btn btn-primary">
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
$(document).ready(function() {
    // Auto-calculate end time based on start time and duration
    $('#start_time, #duration_minutes').change(function() {
        const startTime = $('#start_time').val();
        const duration = parseInt($('#duration_minutes').val());
        
        if (startTime && duration) {
            const start = new Date(startTime);
            const end = new Date(start.getTime() + (duration * 60 * 1000));
            
            // Format for datetime-local input
            const endTimeString = end.toISOString().slice(0, 16);
            $('#end_time').val(endTimeString);
        }
    });

    // Handle selection mode change for editing
    $('input[name="selection_mode"]').change(function() {
        const mode = $(this).val();
        
        if (mode === 'auto') {
            $('#auto_select_by_job').val('1');
            $('#manual-categories-edit').hide();
            showAutoModeWarning();
        } else if (mode === 'manual') {
            $('#auto_select_by_job').val('0');
            $('#manual-categories-edit').show();
            loadAllCategoriesForManualSelection();
        } else {
            // keep current
            $('#auto_select_by_job').val('0');
            $('#manual-categories-edit').hide();
        }
    });

    function showAutoModeWarning() {
        if (!$('#auto-mode-warning').length) {
            const warning = `
                <div id="auto-mode-warning" class="alert alert-info mt-2">
                    <i class="ti ti-info-circle me-2"></i>
                    Categories will be automatically selected based on the candidate's job position.
                    Current manual selection will be overridden.
                </div>
            `;
            $('input[name="selection_mode"]:checked').closest('.alert').after(warning);
        }
    }

    function loadAllCategoriesForManualSelection() {
        // This would typically load all available categories via AJAX
        // For now, we'll just show the current categories
        console.log('Loading all categories for manual selection...');
    }

    // Handle category card clicks
    $(document).on('click', '.category-card', function(e) {
        if (e.target.type !== 'checkbox') {
            const checkbox = $(this).find('.category-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked'));
            updateDurationSuggestion();
        }
    });

    // Handle checkbox changes
    $(document).on('change', '.category-checkbox', function() {
        updateDurationSuggestion();
    });

    function updateDurationSuggestion() {
        let totalDuration = 0;
        $('.category-checkbox:checked').each(function() {
            // Extract duration from the card text (this is a simplified approach)
            const durationText = $(this).closest('.card-body').find('.text-sm').first().text();
            const durationMatch = durationText.match(/(\d+)\s*min/);
            if (durationMatch) {
                totalDuration += parseInt(durationMatch[1]);
            }
        });
        
        if (totalDuration > 0) {
            $('#duration_minutes').val(totalDuration + 10); // Add 10 minutes buffer
            
            // Show suggestion
            if (!$('#duration-suggestion').length) {
                $('#duration_minutes').after('<small id="duration-suggestion" class="text-success d-block">Duration updated based on selected categories</small>');
            }
            setTimeout(function() {
                $('#duration-suggestion').fadeOut();
            }, 3000);
        }
    }

    // Form validation
    $('#edit-schedule-form').on('submit', function(e) {
        const startTime = new Date($('#start_time').val());
        const endTime = new Date($('#end_time').val());
        
        if (startTime >= endTime) {
            e.preventDefault();
            alert('{{__("End time must be after start time")}}');
            return false;
        }
        
        // Additional validation can be added here
    });
});
</script>

<style>
.category-card {
    transition: all 0.3s ease;
}

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.category-card.border-success {
    border-width: 2px !important;
}

.category-checkbox {
    cursor: pointer;
}

.form-check-label {
    cursor: pointer;
}

.alert .form-check {
    margin-bottom: 10px;
}

.alert .form-check:last-child {
    margin-bottom: 0;
}

.card-body .text-sm {
    font-size: 0.875rem;
}

.hweb {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar {
    width: 35px;
    height: 35px;
}
</style>
@endpush