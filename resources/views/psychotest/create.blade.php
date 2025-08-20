{{-- resources/views/psychotest/create.blade.php - Updated with Category Selection --}}
@extends('layouts.admin')

@section('page-title')
{{__('Create Psychotest Schedule')}}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item"><a href="{{route('psychotest-schedule.index')}}">{{__('Psychotest Schedule')}}</a></li>
<li class="breadcrumb-item">{{__('Create')}}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                {!! Form::open(['route' => 'psychotest-schedule.store', 'method' => 'post', 'id' => 'schedule-form']) !!}
                <div class="row">
                    <div class="form-group col-md-12">
                        {!! Form::label('candidate', __('Candidate'), ['class' => 'form-label']) !!}
                        {!! Form::select('candidate', $candidates->pluck('name', 'id')->prepend('-- Select Candidate --', ''), $candidateId, [
                            'class' => 'form-control select2', 
                            'required' => true,
                            'id' => 'candidate-select'
                        ]) !!}
                    </div>

                    <!-- Job Info Display -->
                    <div class="col-md-12" id="job-info" style="display: none;">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-briefcase"></i> Job Information</h6>
                            <p class="mb-1"><strong>Candidate:</strong> <span id="candidate-name">-</span></p>
                            <p class="mb-0"><strong>Position:</strong> <span id="job-title">-</span></p>
                        </div>
                    </div>

                    <!-- Category Selection Mode -->
                    <div class="form-group col-md-12" id="category-mode" style="display: none;">
                        <label class="form-label">{{__('Test Category Selection')}}</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="selection_mode" id="auto_mode" value="auto" checked>
                            <label class="form-check-label" for="auto_mode">
                                <strong>{{__('Auto Select by Job Position')}}</strong>
                                <small class="text-muted d-block">Automatically select appropriate tests based on candidate's job position</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="selection_mode" id="manual_mode" value="manual">
                            <label class="form-check-label" for="manual_mode">
                                <strong>{{__('Manual Selection')}}</strong>
                                <small class="text-muted d-block">Choose specific test categories manually</small>
                            </label>
                        </div>
                        <input type="hidden" name="auto_select_by_job" id="auto_select_by_job" value="1">
                    </div>

                    <!-- Auto Selected Categories Preview -->
                    <div class="col-md-12" id="auto-categories-preview" style="display: none;">
                        <div class="alert alert-success">
                            <h6><i class="fas fa-magic"></i> Auto Selected Test Categories</h6>
                            <div id="auto-categories-list"></div>
                            <small class="text-muted">These categories will be automatically assigned based on the job position.</small>
                        </div>
                    </div>

                    <!-- Manual Category Selection -->
                    <div class="col-md-12" id="manual-categories" style="display: none;">
                        <label class="form-label">{{__('Select Test Categories')}}</label>
                        <div class="row" id="categories-grid">
                            <!-- Categories will be loaded here via AJAX -->
                        </div>
                    </div>

                    <div class="form-group col-md-4">
                        {!! Form::label('duration_minutes', __('Duration (Minutes)'), ['class' => 'form-label']) !!}
                        {!! Form::number('duration_minutes', 60, ['class' => 'form-control', 'min' => 15, 'max' => 300, 'required' => true]) !!}
                        <small class="text-muted">{{__('Total time allowed for all tests')}}</small>
                    </div>

                    <div class="form-group col-md-4">
                        {!! Form::label('start_time', __('Start Time'), ['class' => 'form-label']) !!}
                        {!! Form::datetimeLocal('start_time', null, ['class' => 'form-control', 'required' => true]) !!}
                    </div>

                    <div class="form-group col-md-4">
                        {!! Form::label('end_time', __('End Time'), ['class' => 'form-label']) !!}
                        {!! Form::datetimeLocal('end_time', null, ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                </div>

                <div class="modal-footer">
                    <a href="{{ route('psychotest-schedule.index') }}" class="btn btn-light">{{__('Cancel')}}</a>
                    <input type="submit" value="{{__('Create Schedule')}}" class="btn btn-primary">
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
    let allCategories = [];
    let applicableCategories = [];

    // Handle candidate selection
    $('#candidate-select').change(function() {
        const candidateId = $(this).val();
        
        if (candidateId) {
            loadCategoriesForCandidate(candidateId);
        } else {
            hideAllCategorySelections();
        }
    });

    // Handle selection mode change
    $('input[name="selection_mode"]').change(function() {
        const mode = $(this).val();
        
        if (mode === 'auto') {
            $('#auto_select_by_job').val('1');
            $('#auto-categories-preview').show();
            $('#manual-categories').hide();
            clearManualSelections();
        } else {
            $('#auto_select_by_job').val('0');
            $('#auto-categories-preview').hide();
            $('#manual-categories').show();
            renderManualCategories();
        }
    });

    function loadCategoriesForCandidate(candidateId) {
        $.ajax({
            url: '{{ url("psychotest-categories/by-candidate") }}/' + candidateId,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    allCategories = response.all_categories;
                    applicableCategories = response.applicable_categories;
                    
                    // Show job info
                    $('#candidate-name').text(response.candidate.name);
                    $('#job-title').text(response.candidate.job_title || 'No specific position');
                    $('#job-info').show();
                    
                    // Show category selection mode
                    $('#category-mode').show();
                    
                    // Show auto preview
                    renderAutoCategories();
                    $('#auto-categories-preview').show();
                    
                    // Update duration suggestion based on selected categories
                    updateDurationSuggestion();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Failed to load categories for candidate');
            }
        });
    }

    function renderAutoCategories() {
        const autoList = $('#auto-categories-list');
        autoList.empty();
        
        const selectedCategories = allCategories.filter(cat => 
            applicableCategories.includes(cat.id)
        );
        
        if (selectedCategories.length === 0) {
            autoList.append('<p class="mb-0 text-muted">No applicable categories found for this position.</p>');
            return;
        }
        
        selectedCategories.forEach(category => {
            const badge = category.is_job_specific ? 
                '<span class="badge bg-warning ms-2">Job Specific</span>' : 
                '<span class="badge bg-info ms-2">General</span>';
                
            autoList.append(`
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded">
                    <div>
                        <strong>${category.name}</strong> ${badge}
                        <br><small class="text-muted">${category.description}</small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">${category.duration_minutes} min | ${category.total_questions} soal</small>
                    </div>
                </div>
            `);
        });
    }

    function renderManualCategories() {
        const grid = $('#categories-grid');
        grid.empty();
        
        allCategories.forEach(category => {
            const isRecommended = applicableCategories.includes(category.id);
            const recommendedBadge = isRecommended ? 
                '<span class="badge bg-success">Recommended</span>' : '';
            const jobSpecificBadge = category.is_job_specific ? 
                '<span class="badge bg-warning">Job Specific</span>' : 
                '<span class="badge bg-info">General</span>';
            
            grid.append(`
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card category-card ${isRecommended ? 'border-success' : ''}" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input category-checkbox" type="checkbox" 
                                       name="selected_categories[]" value="${category.id}" 
                                       id="cat_${category.id}" ${isRecommended ? 'checked' : ''}>
                                <label class="form-check-label w-100" for="cat_${category.id}">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <strong>${category.name}</strong>
                                        <div>
                                            ${recommendedBadge}
                                            ${jobSpecificBadge}
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-2">${category.description}</p>
                                    <div class="d-flex justify-content-between text-sm text-muted">
                                        <span><i class="fas fa-clock"></i> ${category.duration_minutes} min</span>
                                        <span><i class="fas fa-question-circle"></i> ${category.total_questions} soal</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
        
        // Add click handler for cards
        $('.category-card').click(function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = $(this).find('.category-checkbox');
                checkbox.prop('checked', !checkbox.prop('checked'));
                updateDurationSuggestion();
            }
        });
        
        // Add change handler for checkboxes
        $('.category-checkbox').change(function() {
            updateDurationSuggestion();
        });
    }

    function updateDurationSuggestion() {
        let totalDuration = 0;
        
        if ($('#auto_select_by_job').val() === '1') {
            // Auto mode - calculate from applicable categories
            const selectedCategories = allCategories.filter(cat => 
                applicableCategories.includes(cat.id)
            );
            totalDuration = selectedCategories.reduce((sum, cat) => sum + cat.duration_minutes, 0);
        } else {
            // Manual mode - calculate from checked categories
            $('.category-checkbox:checked').each(function() {
                const categoryId = parseInt($(this).val());
                const category = allCategories.find(cat => cat.id === categoryId);
                if (category) {
                    totalDuration += category.duration_minutes;
                }
            });
        }
        
        if (totalDuration > 0) {
            $('#duration_minutes').val(totalDuration + 10); // Add 10 minutes buffer
        }
    }

    function clearManualSelections() {
        $('.category-checkbox').prop('checked', false);
    }

    function hideAllCategorySelections() {
        $('#job-info').hide();
        $('#category-mode').hide();
        $('#auto-categories-preview').hide();
        $('#manual-categories').hide();
    }

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

    // Trigger candidate selection if candidateId is pre-selected
    @if($candidateId)
    loadCategoriesForCandidate({{ $candidateId }});
    @endif
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
</style>
@endpush