@extends('layouts.admin')
@section('page-title')
    {{__('Create Test Category')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('psychotest-category.index')}}">{{__('Test Categories')}}</a></li>
    <li class="breadcrumb-item">{{__('Create')}}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{__('Create Test Category')}}</h5>
                <small class="text-muted">{{__('Create a new category for psychotest questions')}}</small>
            </div>
            <div class="card-body">
                {!! Form::open(['route' => 'psychotest-category.store', 'method' => 'post', 'id' => 'create-category-form']) !!}
                
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
                        {!! Form::select('type', $types, null, ['class' => 'form-control select2', 'id' => 'type', 'required' => true, 'placeholder' => __('Select Type')]) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('order', __('Display Order'), ['class' => 'form-label']) !!}
                        {!! Form::number('order', 0, ['class' => 'form-control', 'min' => 0, 'required' => true]) !!}
                        <small class="text-muted">{{__('Lower numbers appear first')}}</small>
                    </div>

                    <!-- Test Configuration -->
                    <div class="form-group col-md-6">
                        {!! Form::label('duration_minutes', __('Duration (Minutes)'), ['class' => 'form-label']) !!}
                        {!! Form::number('duration_minutes', 15, ['class' => 'form-control', 'min' => 1, 'max' => 120, 'required' => true]) !!}
                        <small class="text-muted">{{__('Time allocated for this category')}}</small>
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('total_questions', __('Total Questions'), ['class' => 'form-label']) !!}
                        {!! Form::number('total_questions', 20, ['class' => 'form-control', 'min' => 1, 'max' => 200, 'required' => true]) !!}
                        <small class="text-muted">{{__('Expected number of questions in this category')}}</small>
                    </div>

                    <!-- Job Specific Settings -->
                    <div class="form-group col-md-12">
                        <div class="form-check form-switch">
                            {!! Form::checkbox('is_job_specific', 1, false, ['class' => 'form-check-input', 'id' => 'is_job_specific']) !!}
                            <label class="form-check-label" for="is_job_specific">
                                {{__('Job Specific Category')}}
                            </label>
                        </div>
                        <small class="text-muted">{{__('Check if this category is specific to certain job positions')}}</small>
                    </div>

                    <!-- Target Job Keywords -->
                    <div class="form-group col-md-12 job-keywords-section" style="display: none;">
                        <label class="form-label">{{__('Target Job Keywords')}}</label>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="target_job_keywords[]" value="auditor" id="keyword_auditor">
                                                <label class="form-check-label" for="keyword_auditor">Auditor</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="target_job_keywords[]" value="audit" id="keyword_audit">
                                                <label class="form-check-label" for="keyword_audit">Audit</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="target_job_keywords[]" value="tax" id="keyword_tax">
                                                <label class="form-check-label" for="keyword_tax">Tax</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="target_job_keywords[]" value="taxation" id="keyword_taxation">
                                                <label class="form-check-label" for="keyword_taxation">Taxation</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="target_job_keywords[]" value="accounting" id="keyword_accounting">
                                                <label class="form-check-label" for="keyword_accounting">Accounting</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="target_job_keywords[]" value="akuntan" id="keyword_akuntan">
                                                <label class="form-check-label" for="keyword_akuntan">Akuntan</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="target_job_keywords[]" value="perpajakan" id="keyword_perpajakan">
                                                <label class="form-check-label" for="keyword_perpajakan">Perpajakan</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted">{{__('Select job keywords that this category applies to')}}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Type-specific Settings -->
                
                <!-- Kraeplin Settings -->
                <div class="type-settings kraeplin-settings" style="display: none;">
                    <hr>
                    <h6>{{__('Kraeplin Test Settings')}}</h6>
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('kraeplin_columns', __('Number of Columns'), ['class' => 'form-label']) !!}
                            {!! Form::number('kraeplin_columns', 10, ['class' => 'form-control', 'min' => 5, 'max' => 20]) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('time_per_column', __('Time per Column (seconds)'), ['class' => 'form-label']) !!}
                            {!! Form::number('time_per_column', 30, ['class' => 'form-control', 'min' => 10, 'max' => 60]) !!}
                        </div>
                        <div class="form-group col-md-12">
                            <div class="form-check">
                                {!! Form::checkbox('show_instructions', 1, true, ['class' => 'form-check-input', 'id' => 'show_instructions']) !!}
                                <label class="form-check-label" for="show_instructions">
                                    {{__('Show Instructions Before Test')}}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Field Specific Settings -->
                <div class="type-settings field-specific-settings" style="display: none;">
                    <hr>
                    <h6>{{__('Field Specific Test Settings')}}</h6>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">{{__('Field Topics')}}</label>
                            <div class="form-check-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="field_topics[]" value="audit_procedures" id="topic_audit_procedures" checked>
                                    <label class="form-check-label" for="topic_audit_procedures">{{__('Audit Procedures')}}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="field_topics[]" value="tax_calculation" id="topic_tax_calculation" checked>
                                    <label class="form-check-label" for="topic_tax_calculation">{{__('Tax Calculation')}}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="field_topics[]" value="financial_accounting" id="topic_financial_accounting" checked>
                                    <label class="form-check-label" for="topic_financial_accounting">{{__('Financial Accounting')}}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="field_topics[]" value="internal_control" id="topic_internal_control" checked>
                                    <label class="form-check-label" for="topic_internal_control">{{__('Internal Control')}}</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('passing_score', __('Passing Score (%)'), ['class' => 'form-label']) !!}
                            {!! Form::number('passing_score', 70, ['class' => 'form-control', 'min' => 1, 'max' => 100]) !!}
                        </div>
                    </div>
                </div>

                <!-- Personality Test Settings -->
                <div class="type-settings personality-settings" style="display: none;">
                    <hr>
                    <h6>{{__('Personality Test Settings')}}</h6>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">{{__('Scoring Method')}}</label>
                            <select name="scoring_method" class="form-control">
                                <option value="forced_choice">{{__('Forced Choice')}}</option>
                                <option value="likert_scale">{{__('Likert Scale')}}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <div class="form-check form-switch mt-4">
                                {!! Form::checkbox('show_progress', 1, true, ['class' => 'form-check-input', 'id' => 'show_progress']) !!}
                                <label class="form-check-label" for="show_progress">
                                    {{__('Show Progress Bar')}}
                                </label>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <label class="form-label">{{__('Personality Dimensions')}}</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="achievement" id="dim_achievement" checked>
                                        <label class="form-check-label" for="dim_achievement">{{__('Achievement')}}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="deference" id="dim_deference" checked>
                                        <label class="form-check-label" for="dim_deference">{{__('Deference')}}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="order" id="dim_order" checked>
                                        <label class="form-check-label" for="dim_order">{{__('Order')}}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="exhibition" id="dim_exhibition" checked>
                                        <label class="form-check-label" for="dim_exhibition">{{__('Exhibition')}}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="autonomy" id="dim_autonomy" checked>
                                        <label class="form-check-label" for="dim_autonomy">{{__('Autonomy')}}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="affiliation" id="dim_affiliation" checked>
                                        <label class="form-check-label" for="dim_affiliation">{{__('Affiliation')}}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="intraception" id="dim_intraception" checked>
                                        <label class="form-check-label" for="dim_intraception">{{__('Intraception')}}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="succorance" id="dim_succorance" checked>
                                        <label class="form-check-label" for="dim_succorance">{{__('Succorance')}}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="dominance" id="dim_dominance" checked>
                                        <label class="form-check-label" for="dim_dominance">{{__('Dominance')}}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="abasement" id="dim_abasement" checked>
                                        <label class="form-check-label" for="dim_abasement">{{__('Abasement')}}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="nurturance" id="dim_nurturance" checked>
                                        <label class="form-check-label" for="dim_nurturance">{{__('Nurturance')}}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="change" id="dim_change" checked>
                                        <label class="form-check-label" for="dim_change">{{__('Change')}}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="endurance" id="dim_endurance" checked>
                                        <label class="form-check-label" for="dim_endurance">{{__('Endurance')}}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="heterosexuality" id="dim_heterosexuality" checked>
                                        <label class="form-check-label" for="dim_heterosexuality">{{__('Heterosexuality')}}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="personality_dimensions[]" value="aggression" id="dim_aggression" checked>
                                        <label class="form-check-label" for="dim_aggression">{{__('Aggression')}}</label>
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
                    <input type="submit" value="{{__('Create Category')}}" class="btn btn-primary">
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
    // Auto-generate code from name
    $('#name').on('input', function() {
        let name = $(this).val();
        let code = name.toLowerCase()
                      .replace(/[^a-z0-9\s]/g, '')
                      .replace(/\s+/g, '_')
                      .substring(0, 50);
        $('#code').val(code);
    });

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
            $('#is_job_specific').prop('checked', true).trigger('change');
        } else if (type === 'personality') {
            $('.personality-settings').show();
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

    // Form validation
    $('#create-category-form').on('submit', function(e) {
        let type = $('#type').val();
        
        if (type === 'kraeplin') {
            let columns = parseInt($('#kraeplin_columns').val());
            let timePerColumn = parseInt($('#time_per_column').val());
            
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
        
        if ($('#is_job_specific').is(':checked')) {
            let checkedKeywords = $('input[name="target_job_keywords[]"]:checked').length;
            if (checkedKeywords === 0) {
                e.preventDefault();
                alert('{{__("Please select at least one job keyword for job-specific categories")}}');
                return false;
            }
        }
    });
});
</script>
@endpush