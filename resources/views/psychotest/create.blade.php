{{-- resources/views/psychotest/create.blade.php - Updated with Multiple Candidate Selection --}}
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
                    <!-- Candidate Selection Mode -->
                    <div class="form-group col-md-12">
                        <label class="form-label">{{__('Candidate Selection')}}</label>
                        <div class="btn-group w-100" role="group" aria-label="Candidate Selection Mode">
                            <input type="radio" class="btn-check" name="selection_mode" id="single_mode" value="single" checked>
                            <label class="btn btn-outline-primary" for="single_mode">
                                <i class="ti ti-user me-1"></i>{{__('Single Candidate')}}
                            </label>

                            <input type="radio" class="btn-check" name="selection_mode" id="multiple_mode" value="multiple">
                            <label class="btn btn-outline-primary" for="multiple_mode">
                                <i class="ti ti-users me-1"></i>{{__('Multiple Candidates')}}
                            </label>

                            <input type="radio" class="btn-check" name="selection_mode" id="all_mode" value="all">
                            <label class="btn btn-outline-primary" for="all_mode">
                                <i class="ti ti-user-check me-1"></i>{{__('All Candidates')}}
                            </label>
                        </div>
                        <small class="text-muted">{{__('Choose how many candidates to schedule for psychotest')}}</small>
                    </div>

                    <!-- Single Candidate Selection -->
                    <div class="form-group col-md-12" id="single-candidate-select">
                        {!! Form::label('candidate_single', __('Select Candidate'), ['class' => 'form-label']) !!}
                        {!! Form::select('candidate_single', $candidates->pluck('name', 'id')->prepend('-- Select Candidate --', ''), $candidateId, [
                            'class' => 'form-control select2', 
                            'id' => 'candidate-single'
                        ]) !!}
                    </div>

                    <!-- Multiple Candidate Selection -->
                    <div class="form-group col-md-12" id="multiple-candidate-select" style="display: none;">
                        {!! Form::label('candidates_multiple', __('Select Candidates'), ['class' => 'form-label']) !!}
                        <div class="candidate-selection-container">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">{{__('Select multiple candidates for psychotest')}}</small>
                                <div>
                                    <button type="button" class="btn btn-sm btn-info" id="select-all-btn">
                                        <i class="ti ti-check-all me-1"></i>{{__('Select All')}}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" id="clear-all-btn">
                                        <i class="ti ti-x me-1"></i>{{__('Clear All')}}
                                    </button>
                                </div>
                            </div>
                            <div class="candidate-grid" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px; padding: 10px;">
                                @foreach($candidates as $candidate)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input candidate-checkbox" type="checkbox" 
                                               name="candidates[]" value="{{ $candidate->id }}" 
                                               id="candidate_{{ $candidate->id }}">
                                        <label class="form-check-label w-100" for="candidate_{{ $candidate->id }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $candidate->name }}</strong>
                                                    <br><small class="text-muted">{{ $candidate->email }}</small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-info">{{ $candidate->jobs->title ?? 'No Position' }}</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-2">
                                <small class="text-info">
                                    <span id="selected-count">0</span> {{__('candidates selected')}}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- All Candidates Confirmation -->
                    <div class="col-md-12" id="all-candidates-info" style="display: none;">
                        <div class="alert alert-info">
                            <h6><i class="ti ti-info-circle me-2"></i>{{__('All Candidates Selection')}}</h6>
                            <p class="mb-2">{{__('This will create psychotest schedules for all available candidates (:count total).', ['count' => $candidates->count()])}}</p>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirm-all-candidates">
                                <label class="form-check-label" for="confirm-all-candidates">
                                    {{__('I confirm to create schedules for all candidates')}}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Candidates Summary -->
                    <div class="col-md-12" id="candidates-summary" style="display: none;">
                        <div class="alert alert-light">
                            <h6><i class="ti ti-list-check me-2"></i>{{__('Selected Candidates Summary')}}</h6>
                            <div id="summary-content"></div>
                        </div>
                    </div>

                    <!-- Job Info Display -->
                    <div class="col-md-12" id="job-info" style="display: none;">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-briefcase"></i> {{__('Job Information')}}</h6>
                            <div id="job-info-content"></div>
                        </div>
                    </div>

                    <!-- Category Selection Mode -->
                    <div class="form-group col-md-12" id="category-mode" style="display: none;">
                        <label class="form-label">{{__('Test Category Selection')}}</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category_selection_mode" id="auto_mode" value="auto" checked>
                            <label class="form-check-label" for="auto_mode">
                                <strong>{{__('Auto Select by Job Position')}}</strong>
                                <small class="text-muted d-block">{{__('Automatically select appropriate tests based on candidate job positions')}}</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category_selection_mode" id="manual_mode" value="manual">
                            <label class="form-check-label" for="manual_mode">
                                <strong>{{__('Manual Selection')}}</strong>
                                <small class="text-muted d-block">{{__('Choose specific test categories manually (same for all candidates)')}}</small>
                            </label>
                        </div>
                        <input type="hidden" name="auto_select_by_job" id="auto_select_by_job" value="1">
                    </div>

                    <!-- Auto Selected Categories Preview -->
                    <div class="col-md-12" id="auto-categories-preview" style="display: none;">
                        <div class="alert alert-success">
                            <h6><i class="fas fa-magic"></i> {{__('Auto Selected Test Categories')}}</h6>
                            <div id="auto-categories-list"></div>
                            <small class="text-muted">{{__('These categories will be automatically assigned based on job positions.')}}</small>
                        </div>
                    </div>

                    <!-- Manual Category Selection -->
                    <div class="col-md-12" id="manual-categories" style="display: none;">
                        <label class="form-label">{{__('Select Test Categories')}}</label>
                        <small class="text-muted d-block mb-3">{{__('These categories will be applied to all selected candidates')}}</small>
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
                    <input type="submit" value="{{__('Create Schedule')}}" class="btn btn-primary" id="submit-btn">
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
    let selectedCandidates = [];

    // Handle selection mode change
    $('input[name="selection_mode"]').change(function() {
        const mode = $(this).val();
        
        hideAllSelections();
        
        if (mode === 'single') {
            $('#single-candidate-select').show();
            setupSingleCandidateMode();
        } else if (mode === 'multiple') {
            $('#multiple-candidate-select').show();
            setupMultipleCandidateMode();
        } else if (mode === 'all') {
            $('#all-candidates-info').show();
            setupAllCandidatesMode();
        }
    });

    // Handle single candidate selection
    $('#candidate-single').change(function() {
        const candidateId = $(this).val();
        
        if (candidateId) {
            selectedCandidates = [candidateId];
            loadCategoriesForCandidates([candidateId]);
        } else {
            hideAllCategorySelections();
        }
    });

    // Handle multiple candidate selection
    $('.candidate-checkbox').change(function() {
        updateSelectedCandidates();
        updateSelectedCount();
        
        if (selectedCandidates.length > 0) {
            loadCategoriesForCandidates(selectedCandidates);
            showCandidatesSummary();
        } else {
            hideAllCategorySelections();
            $('#candidates-summary').hide();
        }
    });

    // Select all candidates
    $('#select-all-btn').click(function() {
        $('.candidate-checkbox').prop('checked', true);
        updateSelectedCandidates();
        updateSelectedCount();
        
        if (selectedCandidates.length > 0) {
            loadCategoriesForCandidates(selectedCandidates);
            showCandidatesSummary();
        }
    });

    // Clear all candidates
    $('#clear-all-btn').click(function() {
        $('.candidate-checkbox').prop('checked', false);
        updateSelectedCandidates();
        updateSelectedCount();
        hideAllCategorySelections();
        $('#candidates-summary').hide();
    });

    // Handle all candidates confirmation
    $('#confirm-all-candidates').change(function() {
        if ($(this).is(':checked')) {
            selectedCandidates = @json($candidates->pluck('id')->toArray());
            loadCategoriesForCandidates(selectedCandidates);
            showAllCandidatesSummary();
        } else {
            hideAllCategorySelections();
            $('#candidates-summary').hide();
        }
    });

    // Handle category selection mode change
    $('input[name="category_selection_mode"]').change(function() {
        const mode = $(this).val();
        
        if (mode === 'auto') {
            $('#auto_select_by_job').val('1');
            $('#auto-categories-preview').show();
            $('#manual-categories').hide();
            clearManualSelections();
            renderAutoCategories();
        } else {
            $('#auto_select_by_job').val('0');
            $('#auto-categories-preview').hide();
            $('#manual-categories').show();
            renderManualCategories();
        }
    });

    function setupSingleCandidateMode() {
        // Reset form for single candidate
        selectedCandidates = [];
        const preselectedId = $('#candidate-single').val();
        if (preselectedId) {
            selectedCandidates = [preselectedId];
            loadCategoriesForCandidates([preselectedId]);
        }
    }

    function setupMultipleCandidateMode() {
        $('.candidate-checkbox').prop('checked', false);
        updateSelectedCandidates();
        updateSelectedCount();
    }

    function setupAllCandidatesMode() {
        $('#confirm-all-candidates').prop('checked', false);
        selectedCandidates = [];
    }

    function updateSelectedCandidates() {
        selectedCandidates = [];
        $('.candidate-checkbox:checked').each(function() {
            selectedCandidates.push($(this).val());
        });
    }

    function updateSelectedCount() {
        $('#selected-count').text(selectedCandidates.length);
    }

    function showCandidatesSummary() {
        const candidateNames = [];
        $('.candidate-checkbox:checked').each(function() {
            const label = $(this).next('label');
            const name = label.find('strong').text();
            candidateNames.push(name);
        });
        
        let summaryHtml = '<p><strong>Selected Candidates (' + selectedCandidates.length + '):</strong></p>';
        summaryHtml += '<div class="row">';
        candidateNames.forEach(function(name, index) {
            if (index > 0 && index % 3 === 0) {
                summaryHtml += '</div><div class="row">';
            }
            summaryHtml += '<div class="col-md-4"><span class="badge bg-primary me-1">' + name + '</span></div>';
        });
        summaryHtml += '</div>';
        
        $('#summary-content').html(summaryHtml);
        $('#candidates-summary').show();
    }

    function showAllCandidatesSummary() {
        const totalCandidates = @json($candidates->count());
        let summaryHtml = '<p><strong>All Candidates Selected (' + totalCandidates + '):</strong></p>';
        summaryHtml += '<p class="text-muted">Psychotest schedules will be created for all available candidates.</p>';
        
        // Show job positions summary
        const jobPositions = @json($candidates->pluck('jobs.title')->filter()->unique()->values());
        if (jobPositions.length > 0) {
            summaryHtml += '<p><strong>Job Positions:</strong></p>';
            summaryHtml += '<div>';
            jobPositions.forEach(function(position) {
                summaryHtml += '<span class="badge bg-info me-1">' + (position || 'No Position') + '</span>';
            });
            summaryHtml += '</div>';
        }
        
        $('#summary-content').html(summaryHtml);
        $('#candidates-summary').show();
    }

    function loadCategoriesForCandidates(candidateIds) {
        if (candidateIds.length === 1) {
            // Single candidate - use existing API
            loadCategoriesForCandidate(candidateIds[0]);
        } else {
            // Multiple candidates - use new API
            loadCategoriesForMultipleCandidates(candidateIds);
        }
    }

    function loadCategoriesForCandidate(candidateId) {
        $.ajax({
            url: '{{ url("psychotest-categories/by-candidate") }}/' + candidateId,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    handleCategoriesResponse(response);
                    showJobInfo([response.candidate]);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Failed to load categories for candidate');
            }
        });
    }

    function loadCategoriesForMultipleCandidates(candidateIds) {
        $.ajax({
            url: '{{ route("psychotest-categories.multiple") }}',
            method: 'POST',
            data: {
                candidate_ids: candidateIds,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    handleCategoriesResponse(response);
                    showJobInfo(response.candidates);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Failed to load categories for candidates');
            }
        });
    }

    function handleCategoriesResponse(response) {
        allCategories = response.all_categories;
        applicableCategories = response.applicable_categories;
        
        // Show category selection mode
        $('#category-mode').show();
        
        // Show auto preview
        renderAutoCategories();
        $('#auto-categories-preview').show();
        
        // Update duration suggestion
        updateDurationSuggestion();
    }

    function showJobInfo(candidates) {
        let jobInfoHtml = '';
        
        if (candidates.length === 1) {
            const candidate = candidates[0];
            jobInfoHtml = '<p class="mb-1"><strong>Candidate:</strong> ' + candidate.name + '</p>';
            jobInfoHtml += '<p class="mb-0"><strong>Position:</strong> ' + (candidate.job_title || 'No specific position') + '</p>';
        } else {
            jobInfoHtml = '<p class="mb-1"><strong>Selected Candidates:</strong> ' + candidates.length + '</p>';
            
            // Group by job positions
            const jobGroups = {};
            candidates.forEach(function(candidate) {
                const jobTitle = candidate.job_title || 'No Position';
                if (!jobGroups[jobTitle]) {
                    jobGroups[jobTitle] = [];
                }
                jobGroups[jobTitle].push(candidate.name);
            });
            
            jobInfoHtml += '<div class="mt-2">';
            Object.keys(jobGroups).forEach(function(jobTitle) {
                jobInfoHtml += '<div class="mb-1">';
                jobInfoHtml += '<strong>' + jobTitle + ':</strong> ';
                jobInfoHtml += '<small>(' + jobGroups[jobTitle].length + ' candidates)</small>';
                jobInfoHtml += '</div>';
            });
            jobInfoHtml += '</div>';
        }
        
        $('#job-info-content').html(jobInfoHtml);
        $('#job-info').show();
    }

    function renderAutoCategories() {
        const autoList = $('#auto-categories-list');
        autoList.empty();
        
        const selectedCategories = allCategories.filter(cat => 
            applicableCategories.includes(cat.id)
        );
        
        if (selectedCategories.length === 0) {
            autoList.append('<p class="mb-0 text-muted">No applicable categories found for selected positions.</p>');
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

    function hideAllSelections() {
        $('#single-candidate-select').hide();
        $('#multiple-candidate-select').hide();
        $('#all-candidates-info').hide();
        hideAllCategorySelections();
    }

    function hideAllCategorySelections() {
        $('#job-info').hide();
        $('#category-mode').hide();
        $('#auto-categories-preview').hide();
        $('#manual-categories').hide();
        $('#candidates-summary').hide();
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

    // Form submission handling
    $('#schedule-form').submit(function(e) {
        const selectionMode = $('input[name="selection_mode"]:checked').val();
        
        // Prepare candidates array based on selection mode
        if (selectionMode === 'single') {
            const candidateId = $('#candidate-single').val();
            if (!candidateId) {
                e.preventDefault();
                alert('Please select a candidate');
                return false;
            }
            $('<input>').attr({
                type: 'hidden',
                name: 'candidates[]',
                value: candidateId
            }).appendTo(this);
            
        } else if (selectionMode === 'multiple') {
            if (selectedCandidates.length === 0) {
                e.preventDefault();
                alert('Please select at least one candidate');
                return false;
            }
            // Checkboxes already have the right name
            
        } else if (selectionMode === 'all') {
            if (!$('#confirm-all-candidates').is(':checked')) {
                e.preventDefault();
                alert('Please confirm to create schedules for all candidates');
                return false;
            }
            // Add all candidate IDs
            @foreach($candidates as $candidate)
                $('<input>').attr({
                    type: 'hidden',
                    name: 'candidates[]',
                    value: '{{ $candidate->id }}'
                }).appendTo(this);
            @endforeach
        }
        
        // Validate time
        const startTime = new Date($('#start_time').val());
        const endTime = new Date($('#end_time').val());
        
        if (startTime >= endTime) {
            e.preventDefault();
            alert('End time must be after start time');
            return false;
        }
    });

    // Trigger candidate selection if candidateId is pre-selected
    @if($candidateId)
    $('#candidate-single').val({{ $candidateId }}).trigger('change');
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

.candidate-grid {
    background-color: #f8f9fa;
}

.candidate-selection-container {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: #f8f9fa;
}

.btn-group .btn-check:checked + .btn {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}
</style>
@endpush