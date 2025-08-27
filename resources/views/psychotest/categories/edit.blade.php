@extends('layouts.admin')
@section('page-title')
    {{__('Edit Test Category')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('psychotest-category.index')}}">{{__('Test Categories')}}</a></li>
    <li class="breadcrumb-item">{{__('Edit')}}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{__('Edit Test Category')}}</h5>
                <small class="text-muted">{{__('Modify the category settings and configuration')}}</small>
            </div>
            <div class="card-body">
                {!! Form::model($category, ['route' => ['psychotest-category.update', $category->id], 'method' => 'put', 'id' => 'edit-category-form']) !!}
                
                <!-- Current Category Info -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6><i class="ti ti-info-circle me-2"></i>{{__('Current Category Information')}}</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>{{__('Name')}}:</strong> {{ $category->name }}<br>
                                    <strong>{{__('Type')}}:</strong> 
                                    <span class="badge bg-info">{{ \App\Models\PsychotestCategory::$types[$category->type] ?? $category->type }}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>{{__('Questions')}}:</strong> {{ $category->questions->count() }}/{{ $category->total_questions }}<br>
                                    <strong>{{__('Status')}}:</strong>
                                    <span class="badge bg-{{ $category->is_active ? 'success' : 'danger' }}">
                                        {{ $category->is_active ? __('Active') : __('Inactive') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Basic Information -->
                    <div class="form-group col-md-6">
                        {!! Form::label('name', __('Category Name'), ['class' => 'form-label']) !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required' => true, 'placeholder' => __('Enter category name')]) !!}
                    </div>
                    
                    <div class="form-group col-md-6">
                        {!! Form::label('code', __('Category Code'), ['class' => 'form-label']) !!}
                        {!! Form::text('code', null, ['class' => 'form-control', 'required' => true, 'placeholder' => __('Enter unique code'), 'id' => 'code']) !!}
                        <small class="text-muted">{{__('Unique identifier for this category (will be auto-slugified)')}}</small>
                    </div>

                    <div class="form-group col-md-12">
                        {!! Form::label('description', __('Description'), ['class' => 'form-label']) !!}
                        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Describe this test category...')]) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('type', __('Category Type'), ['class' => 'form-label']) !!}
                        {!! Form::select('type', $types, null, ['class' => 'form-control select2', 'id' => 'type', 'required' => true]) !!}
                        <small class="text-muted">{{__('Changing type may affect existing questions and settings')}}</small>
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('order', __('Display Order'), ['class' => 'form-label']) !!}
                        {!! Form::number('order', null, ['class' => 'form-control', 'min' => 0, 'required' => true]) !!}
                        <small class="text-muted">{{__('Lower numbers appear first')}}</small>
                    </div>

                    <!-- Test Configuration -->
                    <div class="form-group col-md-6">
                        {!! Form::label('duration_minutes', __('Duration (Minutes)'), ['class' => 'form-label']) !!}
                        {!! Form::number('duration_minutes', null, ['class' => 'form-control', 'min' => 1, 'max' => 120, 'required' => true]) !!}
                        <small class="text-muted">{{__('Time allocated for this category')}}</small>
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('total_questions', __('Total Questions'), ['class' => 'form-label']) !!}
                        {!! Form::number('total_questions', null, ['class' => 'form-control', 'min' => 1, 'max' => 200, 'required' => true]) !!}
                        <small class="text-muted">
                            {{__('Expected number of questions in this category')}}
                            @if($category->questions->count() > 0)
                                <br><span class="text-warning">{{__('Currently has')}} {{ $category->questions->count() }} {{__('questions')}}</span>
                            @endif
                        </small>
                    </div>

                    <!-- Job Specific Settings -->
                    <div class="form-group col-md-12">
                        <div class="form-check form-switch">
                            {!! Form::checkbox('is_job_specific', 1, $category->is_job_specific, ['class' => 'form-check-input', 'id' => 'is_job_specific']) !!}
                            <label class="form-check-label" for="is_job_specific">
                                {{__('Job Specific Category')}}
                            </label>
                        </div>
                        <small class="text-muted">{{__('Check if this category is specific to certain job positions')}}</small>
                    </div>

                    <!-- Target Job Keywords -->
                    <div class="form-group col-md-12 job-keywords-section" style="{{ $category->is_job_specific ? '' : 'display: none;' }}">
                        <label class="form-label">{{__('Target Job Keywords')}}</label>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check-group">
                                    <div class="row">
                                        @php
                                            $selectedKeywords = $category->target_job_keywords ?? [];
                                            $availableKeywords = ['auditor', 'audit', 'tax', 'taxation', 'accounting', 'akuntan', 'perpajakan'];
                                        @endphp
                                        @foreach($availableKeywords as $keyword)
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="target_job_keywords[]" value="{{ $keyword }}" 
                                                           id="keyword_{{ $keyword }}" {{ in_array($keyword, $selectedKeywords) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="keyword_{{ $keyword }}">{{ ucfirst($keyword) }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <small class="text-muted">{{__('Select job keywords that this category applies to')}}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Category Status -->
                    <div class="form-group col-md-6">
                        <label class="form-label">{{__('Category Status')}}</label>
                        <div class="form-check form-switch">
                            {!! Form::checkbox('is_active', 1, $category->is_active, ['class' => 'form-check-input', 'id' => 'is_active']) !!}
                            <label class="form-check-label" for="is_active">
                                {{__('Active')}}
                            </label>
                        </div>
                        <small class="text-muted">{{__('Inactive categories will not appear in test selections')}}</small>
                    </div>
                </div>

                <!-- Type-specific Settings -->
                
                <!-- Kraeplin Settings -->
                <div class="type-settings kraeplin-settings" style="{{ $category->type === 'kraeplin' ? '' : 'display: none;' }}">
                    <hr>
                    <h6>{{__('Kraeplin Test Settings')}}</h6>
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('kraeplin_columns', __('Number of Columns'), ['class' => 'form-label']) !!}
                            {!! Form::number('kraeplin_columns', $category->getKraeplinColumns(), ['class' => 'form-control', 'min' => 5, 'max' => 20]) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('time_per_column', __('Time per Column (seconds)'), ['class' => 'form-label']) !!}
                            {!! Form::number('time_per_column', $category->getKraeplinTimePerColumn(), ['class' => 'form-control', 'min' => 10, 'max' => 60]) !!}
                        </div>
                        <div class="form-group col-md-12">
                            <div class="form-check">
                                {!! Form::checkbox('show_instructions', 1, $category->getSetting('show_instructions', true), ['class' => 'form-check-input', 'id' => 'show_instructions']) !!}
                                <label class="form-check-label" for="show_instructions">
                                    {{__('Show Instructions Before Test')}}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Field Specific Settings -->
                <div class="type-settings field-specific-settings" style="{{ $category->type === 'field_specific' ? '' : 'display: none;' }}">
                    <hr>
                    <h6>{{__('Field Specific Test Settings')}}</h6>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">{{__('Field Topics')}}</label>
                            @php
                                $selectedTopics = $category->getSetting('topics', ['audit_procedures', 'tax_calculation', 'financial_accounting', 'internal_control']);
                                $availableTopics = [
                                    'audit_procedures' => __('Audit Procedures'),
                                    'tax_calculation' => __('Tax Calculation'),
                                    'financial_accounting' => __('Financial Accounting'),
                                    'internal_control' => __('Internal Control'),
                                    'financial_analysis' => __('Financial Analysis'),
                                    'cost_accounting' => __('Cost Accounting')
                                ];
                            @endphp
                            <div class="form-check-group">
                                @foreach($availableTopics as $value => $label)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="field_topics[]" value="{{ $value }}" 
                                               id="topic_{{ $value }}" {{ in_array($value, $selectedTopics) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="topic_{{ $value }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('passing_score', __('Passing Score (%)'), ['class' => 'form-label']) !!}
                            {!! Form::number('passing_score', $category->getSetting('passing_score', 70), ['class' => 'form-control', 'min' => 1, 'max' => 100]) !!}
                        </div>
                    </div>
                </div>

                <!-- Personality Test Settings -->
                <div class="type-settings personality-settings" style="{{ $category->type === 'personality' ? '' : 'display: none;' }}">
                    <hr>
                    <h6>{{__('Personality Test Settings')}}</h6>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">{{__('Scoring Method')}}</label>
                            <select name="scoring_method" class="form-control">
                                <option value="forced_choice" {{ $category->getEPPSScoringMethod() === 'forced_choice' ? 'selected' : '' }}>{{__('Forced Choice')}}</option>
                                <option value="likert_scale" {{ $category->getEPPSScoringMethod() === 'likert_scale' ? 'selected' : '' }}>{{__('Likert Scale')}}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <div class="form-check form-switch mt-4">
                                {!! Form::checkbox('show_progress', 1, $category->shouldShowProgress(), ['class' => 'form-check-input', 'id' => 'show_progress']) !!}
                                <label class="form-check-label" for="show_progress">
                                    {{__('Show Progress Bar')}}
                                </label>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <label class="form-label">{{__('Personality Dimensions')}}</label>
                            @php
                                $selectedDimensions = $category->getEPPSDimensions();
                                $availableDimensions = [
                                    'achievement' => __('Achievement'),
                                    'deference' => __('Deference'),
                                    'order' => __('Order'),
                                    'exhibition' => __('Exhibition'),
                                    'autonomy' => __('Autonomy'),
                                    'affiliation' => __('Affiliation'),
                                    'intraception' => __('Intraception'),
                                    'succorance' => __('Succorance'),
                                    'dominance' => __('Dominance'),
                                    'abasement' => __('Abasement'),
                                    'nurturance' => __('Nurturance'),
                                    'change' => __('Change'),
                                    'endurance' => __('Endurance'),
                                    'heterosexuality' => __('Heterosexuality'),
                                    'aggression' => __('Aggression')
                                ];
                            @endphp
                            <div class="row">
                                <div class="col-md-4">
                                    @foreach(array_slice($availableDimensions, 0, 5, true) as $value => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="{{ $value }}" 
                                                   id="dim_{{ $value }}" {{ in_array($value, $selectedDimensions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="dim_{{ $value }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="col-md-4">
                                    @foreach(array_slice($availableDimensions, 5, 5, true) as $value => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="{{ $value }}" 
                                                   id="dim_{{ $value }}" {{ in_array($value, $selectedDimensions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="dim_{{ $value }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="col-md-4">
                                    @foreach(array_slice($availableDimensions, 10, 5, true) as $value => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="{{ $value }}" 
                                                   id="dim_{{ $value }}" {{ in_array($value, $selectedDimensions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="dim_{{ $value }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Impact Warning -->
                @if($category->questions->count() > 0 || $category->sessions()->exists())
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <h6><i class="ti ti-alert-triangle me-2"></i>{{__('Impact Warning')}}</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        @if($category->questions->count() > 0)
                                            <p class="mb-1"><strong>{{__('Questions Impact')}}:</strong></p>
                                            <ul class="mb-0">
                                                <li>{{__('This category has')}} {{ $category->questions->count() }} {{__('existing questions')}}</li>
                                                <li>{{__('Type changes may affect question compatibility')}}</li>
                                                <li>{{__('Settings changes may affect test behavior')}}</li>
                                            </ul>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        @if($category->sessions()->exists())
                                            <p class="mb-1"><strong>{{__('Usage Impact')}}:</strong></p>
                                            <ul class="mb-0">
                                                <li>{{__('This category is used in')}} {{ $category->sessions()->count() }} {{__('test sessions')}}</li>
                                                <li>{{__('Changes may affect ongoing/future tests')}}</li>
                                                <li>{{__('Historical data may be affected')}}</li>
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Current Settings Summary -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0">{{__('Category Summary')}}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>{{__('Current Type')}}:</strong><br>
                                        <span class="badge bg-info">{{ \App\Models\PsychotestCategory::$types[$category->type] ?? $category->type }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{__('Question Progress')}}:</strong><br>
                                        <span class="badge bg-{{ $category->questions->count() >= $category->total_questions ? 'success' : 'warning' }}">
                                            {{ $category->questions->count() }}/{{ $category->total_questions }}
                                        </span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{__('Usage Count')}}:</strong><br>
                                        <span class="badge bg-primary">{{ $category->sessions()->count() }} sessions</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{__('Last Modified')}}:</strong><br>
                                        <small class="text-muted">{{ $category->updated_at ? $category->updated_at->format('d M Y H:i') : 'Never' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top mt-4 pt-3">
                    <a href="{{ route('psychotest-category.index') }}" class="btn btn-light me-2">
                        <i class="ti ti-arrow-left me-1"></i>{{__('Cancel')}}
                    </a>
                    <a href="{{ route('psychotest-category.show', $category->id) }}" class="btn btn-info me-2">
                        <i class="ti ti-eye me-1"></i>{{__('View Details')}}
                    </a>
                    <input type="submit" value="{{__('Update Category')}}" class="btn btn-primary">
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
    let initialType = $('#type').val();
    
    // Handle type change
    $('#type').change(function() {
        let type = $(this).val();
        
        // Hide all type-specific settings
        $('.type-settings').hide();
        
        // Show relevant settings
        if (type === 'kraeplin') {
            $('.kraeplin-settings').show();
        } else if (type === 'field_specific') {
            $('.field-specific-settings').show();
            if (!$('#is_job_specific').is(':checked')) {
                $('#is_job_specific').prop('checked', true).trigger('change');
            }
        } else if (type === 'personality') {
            $('.personality-settings').show();
        }
        
        // Warn about type change if category has questions
        if (type !== initialType) {
            let questionCount = {{ $category->questions->count() }};
            if (questionCount > 0) {
                if (!confirm('{{__("Changing category type may affect existing questions. Are you sure?")}}')) {
                    $(this).val(initialType);
                    return false;
                }
            }
        }
    });

    // Handle job specific toggle
    $('#is_job_specific').change(function() {
        if ($(this).is(':checked')) {
            $('.job-keywords-section').show();
        } else {
            $('.job-keywords-section').hide();
        }
    });

    // Handle total questions change warning
    $('#total_questions').change(function() {
        let newTotal = parseInt($(this).val());
        let currentQuestions = {{ $category->questions->count() }};
        
        if (newTotal < currentQuestions) {
            alert('{{__("Warning: New total is less than current questions count. Some questions may become inactive.")}}');
        }
    });

    // Form validation
    $('#edit-category-form').on('submit', function(e) {
        let type = $('#type').val();
        
        // Validate Kraeplin settings
        if (type === 'kraeplin') {
            let columns = parseInt($('input[name="kraeplin_columns"]').val());
            let timePerColumn = parseInt($('input[name="time_per_column"]').val());
            
            if (columns < 5 || columns > 20) {
                e.preventDefault();
                alert('{{__("Kraeplin columns must be between 5 and 20")}}');
                return false;
            }
            
            if (timePerColumn < 10 || timePerColumn > 60) {
                e.preventDefault();
                alert('{{__("Time per column must be between 10 and 60 seconds")}}');
                return false;
            }
        }
        
        // Validate job-specific settings
        if ($('#is_job_specific').is(':checked')) {
            let checkedKeywords = $('input[name="target_job_keywords[]"]:checked').length;
            if (checkedKeywords === 0) {
                e.preventDefault();
                alert('{{__("Please select at least one job keyword for job-specific categories")}}');
                return false;
            }
        }
        
        // Validate field-specific settings
        if (type === 'field_specific') {
            let checkedTopics = $('input[name="field_topics[]"]:checked').length;
            if (checkedTopics === 0) {
                e.preventDefault();
                alert('{{__("Please select at least one topic for field-specific categories")}}');
                return false;
            }
        }
        
        // Validate personality settings
        if (type === 'personality') {
            let checkedDimensions = $('input[name="personality_dimensions[]"]:checked').length;
            if (checkedDimensions === 0) {
                e.preventDefault();
                alert('{{__("Please select at least one personality dimension")}}');
                return false;
            }
        }
    });

    // Initialize type-specific sections on load
    $('#type').trigger('change');
});
</script>

<style>
.form-check-group .form-check {
    margin-bottom: 8px;
}

.type-settings {
    margin-top: 1rem;
}

.alert .form-check {
    margin-bottom: 5px;
}

.card-body .text-sm {
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75em;
}
</style>
@endpush