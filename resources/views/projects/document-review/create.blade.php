{{-- resources/views/projects/document-review/create.blade.php --}}
@extends('layouts.admin')
@section('page-title')
    {{__('Submit Work/Document for Review')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show', \Crypt::encrypt($project->id))}}">{{$project->project_name}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.document-review.index', $project->id)}}">{{__('Work/Document Review')}}</a></li>
    <li class="breadcrumb-item">{{__('Submit Work/Document')}}</li>
@endsection

@push('css-page')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.form-group {
    margin-bottom: 1.5rem;
}
.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.required {
    color: #dc3545;
}
.select2-container--default .select2-selection--multiple {
    min-height: 45px;
    border: 1px solid #d1d3e2;
    border-radius: 0.35rem;
}
.theme-avtar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    font-size: 26px;
    color: #fff;
}
.custom-category-section {
    margin-top: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px dashed #dee2e6;
    display: none;
}
.category-preview {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 13px;
    font-weight: 500;
    margin-top: 8px;
}
.category-icon {
    width: 16px;
    height: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.quick-category-btn {
    margin: 2px;
    font-size: 11px;
    padding: 4px 12px;
    border-radius: 15px;
}
.color-picker {
    width: 40px;
    height: 35px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    cursor: pointer;
}
.icon-selector {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 8px;
    max-height: 160px;
    overflow-y: auto;
    padding: 8px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background: #fff;
}
.icon-option {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
}
.icon-option:hover,
.icon-option.selected {
    background: #007bff;
    color: white;
    border-color: #007bff;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{__('Submit Work/Document for Review')}}</h5>
                        <small class="text-muted">{{__('Project')}}: <strong>{{ $project->project_name }}</strong></small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">{{__('Submission Date')}}</small>
                        <strong>{{ date('d M Y') }}</strong>
                    </div>
                </div>
            </div>
            <div class="card-body">
                {{ Form::open(['route' => ['projects.document-review.store', $project->id], 'method' => 'POST', 'id' => 'document-form']) }}
                
                <div class="row">
                    <!-- Work/Document Information -->
                    <div class="col-md-12">
                        <h6 class="text-primary mb-3"><i class="ti ti-file-text me-1"></i>{{__('Work/Document Information')}}</h6>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            {{ Form::label('document_name', __('Work/Document Title'), ['class' => 'form-label']) }}
                            <span class="required">*</span>
                            {{ Form::text('document_name', null, ['class' => 'form-control', 'required' => true, 'placeholder' => __('Enter work/document title')]) }}
                            <small class="text-muted">{{__('Brief descriptive title of your work or document')}}</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('category', __('Category'), ['class' => 'form-label']) }}
                            <span class="required">*</span>
                            <select name="category_id" id="category-select" class="form-control" required>
                                <option value="">{{__('Select category')}}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            data-color="{{ $category->color }}" 
                                            data-icon="{{ $category->icon }}"
                                            data-predefined="{{ $category->is_predefined ? 'true' : 'false' }}"
                                            data-description="{{ $category->description }}">
                                        {{ $category->name }}
                                        @if(!$category->is_predefined) ({{__('Custom')}}) @endif
                                    </option>
                                @endforeach
                                <option value="custom" data-color="#6c757d" data-icon="ti-plus">
                                    {{__('+ Create Custom Category')}}
                                </option>
                            </select>
                            
                            <!-- Category Preview -->
                            <div class="mt-2" id="category-preview" style="display: none;">
                                <small class="text-muted d-block mb-1">{{__('Selected:')}}</small>
                                <div class="category-preview" id="preview-badge">
                                    <span class="category-icon" id="preview-icon"></span>
                                    <span id="preview-name">{{__('No category selected')}}</span>
                                </div>
                            </div>

                            <!-- Custom Category Section -->
                            <div class="custom-category-section" id="custom-category-section">
                                <h6 class="mb-3">{{__('Create Custom Category')}}</h6>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>{{__('Category Name')}} <span class="text-danger">*</span></label>
                                            <input type="text" name="custom_category_name" id="custom-category-name" 
                                                   class="form-control" placeholder="{{__('Enter category name')}}" maxlength="100">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{__('Color')}}</label>
                                            <input type="color" name="custom_category_color" id="custom-category-color" 
                                                   class="color-picker" value="#6c757d">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{__('Icon')}}</label>
                                            <div class="icon-selector" id="icon-selector">
                                                @php
                                                $icons = [
                                                    'ti ti-file', 'ti ti-file-text', 'ti ti-folder', 'ti ti-chart-line', 
                                                    'ti ti-calculator', 'ti ti-search', 'ti ti-bulb', 'ti ti-mail', 
                                                    'ti ti-crown', 'ti ti-notes', 'ti ti-presentation', 'ti ti-clipboard',
                                                    'ti ti-trending-up', 'ti ti-package', 'ti ti-message-circle', 'ti ti-edit',
                                                    'ti ti-tag', 'ti ti-star', 'ti ti-award', 'ti ti-target'
                                                ];
                                                @endphp
                                                @foreach($icons as $icon)
                                                <div class="icon-option" data-icon="{{ $icon }}">
                                                    <i class="{{ $icon }}"></i>
                                                </div>
                                                @endforeach
                                            </div>
                                            <input type="hidden" name="custom_category_icon" id="custom-category-icon" value="ti-tag">
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <small>
                                        <i class="ti ti-info-circle me-1"></i>
                                        {{__('Custom categories will be available for future use in this project.')}}
                                    </small>
                                </div>
                            </div>

                            <!-- Quick category suggestions -->
                            <div class="mt-2" id="category-suggestions">
                                <small class="text-muted d-block mb-1">{{__('Quick suggestions:')}}</small>
                                <button type="button" class="btn btn-outline-primary btn-sm quick-category-btn" data-name="Meeting Summary">{{__('Meeting Summary')}}</button>
                                <button type="button" class="btn btn-outline-primary btn-sm quick-category-btn" data-name="Client Communication">{{__('Client Communication')}}</button>
                                <button type="button" class="btn btn-outline-primary btn-sm quick-category-btn" data-name="Research Analysis">{{__('Research & Analysis')}}</button>
                                <button type="button" class="btn btn-outline-primary btn-sm quick-category-btn" data-name="Draft Document">{{__('Draft Document')}}</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('submission_date', __('Submission Date'), ['class' => 'form-label']) }}
                            <span class="required">*</span>
                            {{ Form::date('submission_date', date('Y-m-d'), ['class' => 'form-control', 'required' => true]) }}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            {{ Form::label('document_link', __('Work/Document Link'), ['class' => 'form-label']) }}
                            <span class="required">*</span>
                            {{ Form::url('document_link', null, ['class' => 'form-control', 'required' => true, 'placeholder' => 'https://drive.google.com/... or https://docs.google.com/...']) }}
                            <small class="text-muted">
                                <i class="ti ti-info-circle me-1"></i>
                                {{__('Paste the link to your work/document (Google Drive, Google Docs, etc.). Make sure the link is accessible to reviewers.')}}
                            </small>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Brief description of the work/document, its purpose, and any important notes (optional)')]) }}
                        </div>
                    </div>

                    <!-- Review Process -->
                    <div class="col-md-12 mt-4">
                        <h6 class="text-primary mb-3"><i class="ti ti-users me-1"></i>{{__('Review Process')}}</h6>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('approver_id', __('Approver'), ['class' => 'form-label']) }}
                            <span class="required">*</span>
                            {{ Form::select('approver_id', $approvers, null, ['class' => 'form-control select2', 'required' => true, 'placeholder' => __('Select approver')]) }}
                            <small class="text-muted">{{__('Select the partner/manager who will review and approve this work')}}</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('contributors', __('Contributors'), ['class' => 'form-label']) }}
                            <span class="required">*</span>
                            <select name="contributors[]" class="form-control select3" multiple required>
                                @foreach($contributors as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{__('Select all people who contributed to this work/document')}}</small>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="col-md-12 mt-4">
                        <h6 class="text-primary mb-3"><i class="ti ti-message me-1"></i>{{__('Additional Information')}}</h6>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            {{ Form::label('initial_comment', __('Initial Comment'), ['class' => 'form-label']) }}
                            {{ Form::textarea('initial_comment', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Any initial comments, notes, or specific areas you\'d like the reviewer to focus on (optional)')]) }}
                        </div>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">{{__('Submission Summary')}}</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">{{__('Submitted by')}}: <strong>{{ Auth::user()->name }}</strong></small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">{{__('Project')}}: <strong>{{ $project->project_name }}</strong></small>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <small class="text-muted">{{__('Selected Category')}}: <span id="summary-category" class="badge bg-secondary">{{__('Not selected')}}</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="modal-footer border-0 p-0 mt-4">
                    <a href="{{ route('projects.document-review.index', $project->id) }}" class="btn btn-light">
                        <i class="ti ti-arrow-left me-1"></i>{{__('Cancel')}}
                    </a>
                    {{ Form::submit(__('Submit for Review'), ['class' => 'btn btn-primary', 'id' => 'submit-btn']) }}
                </div>

                {{ Form::close() }}
            </div>
        </div>

        <!-- Help Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti ti-help-circle me-1"></i>{{__('Review Process')}}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="theme-avtar bg-primary mb-2 mx-auto">
                            <i class="ti ti-upload"></i>
                        </div>
                        <h6>{{__('Submit')}}</h6>
                        <small class="text-muted">{{__('Upload your work/document link and details')}}</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="theme-avtar bg-warning mb-2 mx-auto">
                            <i class="ti ti-eye"></i>
                        </div>
                        <h6>{{__('Review')}}</h6>
                        <small class="text-muted">{{__('Approver reviews your work')}}</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="theme-avtar bg-info mb-2 mx-auto">
                            <i class="ti ti-message-circle"></i>
                        </div>
                        <h6>{{__('Feedback')}}</h6>
                        <small class="text-muted">{{__('Comments and revision requests')}}</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="theme-avtar bg-success mb-2 mx-auto">
                            <i class="ti ti-check"></i>
                        </div>
                        <h6>{{__('Approve')}}</h6>
                        <small class="text-muted">{{__('Final approval or feedback')}}</small>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">{{__('Tips for Successful Submission:')}}</h6>
                            <ul class="mb-0">
                                <li>{{__('Choose a descriptive title that clearly identifies your work')}}</li>
                                <li>{{__('Select or create an appropriate category for better organization')}}</li>
                                <li>{{__('Ensure your document link is accessible and properly shared')}}</li>
                                <li>{{__('Add a description explaining the purpose and context of your work')}}</li>
                                <li>{{__('Include any specific areas you want the reviewer to focus on')}}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select3').select2({
        allowClear: false,
        width: '100%'
    });

    // Category selection handling
    $('#category-select').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const value = $(this).val();
        const customSection = $('#custom-category-section');
        const categoryPreview = $('#category-preview');
        const summaryCategory = $('#summary-category');
        
        if (value === 'custom') {
            customSection.show();
            categoryPreview.hide();
            summaryCategory.text('{{__("Custom Category")}}').removeClass().addClass('badge bg-warning');
            $('#custom-category-name').attr('required', true);
        } else if (value) {
            customSection.hide();
            categoryPreview.show();
            $('#custom-category-name').attr('required', false);
            
            // Update preview
            const color = selectedOption.data('color') || '#6c757d';
            const icon = selectedOption.data('icon') || 'ti-tag';
            const name = selectedOption.text();
            const description = selectedOption.data('description') || '';
            
            $('#preview-badge').css('background-color', color).css('color', 'white');
            $('#preview-icon').html(`<i class="${icon}"></i>`);
            $('#preview-name').text(name);
            
            summaryCategory.text(name).removeClass().addClass('badge').css('background-color', color).css('color', 'white');
        } else {
            customSection.hide();
            categoryPreview.hide();
            $('#custom-category-name').attr('required', false);
            summaryCategory.text('{{__("Not selected")}}').removeClass().addClass('badge bg-secondary');
        }
    });

    // Custom category name input handling
    $('#custom-category-name').on('input', function() {
        const value = $(this).val().trim();
        const color = $('#custom-category-color').val();
        const summaryCategory = $('#summary-category');
        
        if (value) {
            summaryCategory.text(value).removeClass().addClass('badge').css('background-color', color).css('color', 'white');
        } else {
            summaryCategory.text('{{__("Custom Category")}}').removeClass().addClass('badge bg-warning');
        }
    });

    // Custom category color change
    $('#custom-category-color').on('change', function() {
        const color = $(this).val();
        const name = $('#custom-category-name').val().trim();
        const summaryCategory = $('#summary-category');
        
        if (name) {
            summaryCategory.css('background-color', color);
        }
    });

    // Icon selector
    $('.icon-option').on('click', function() {
        $('.icon-option').removeClass('selected');
        $(this).addClass('selected');
        const icon = $(this).data('icon');
        $('#custom-category-icon').val(icon);
    });

    // Initialize first icon as selected
    $('.icon-option[data-icon="ti-tag"]').addClass('selected');

    // Quick category suggestions
    $('.quick-category-btn').on('click', function() {
        const categoryName = $(this).data('name');
        
        // Set to custom and fill the name
        $('#category-select').val('custom').trigger('change');
        $('#custom-category-name').val(categoryName).trigger('input');
    });

    // Form validation
    $('#document-form').on('submit', function(e) {
        let isValid = true;
        let errors = [];

        // Validate required fields
        const documentName = $('input[name="document_name"]').val().trim();
        const categoryId = $('select[name="category_id"]').val();
        const customCategoryName = $('input[name="custom_category_name"]').val().trim();
        const documentLink = $('input[name="document_link"]').val().trim();
        const approverId = $('select[name="approver_id"]').val();
        const contributors = $('select[name="contributors[]"]').val();

        if (!documentName) {
            errors.push('{{__("Work/Document title is required")}}');
            isValid = false;
        }

        if (!categoryId) {
            errors.push('{{__("Category is required")}}');
            isValid = false;
        } else if (categoryId === 'custom' && !customCategoryName) {
            errors.push('{{__("Custom category name is required")}}');
            isValid = false;
        }

        if (!documentLink) {
            errors.push('{{__("Work/Document link is required")}}');
            isValid = false;
        } else if (!isValidUrl(documentLink)) {
            errors.push('{{__("Please enter a valid URL")}}');
            isValid = false;
        }

        if (!approverId) {
            errors.push('{{__("Approver is required")}}');
            isValid = false;
        }

        if (!contributors || contributors.length === 0) {
            errors.push('{{__("At least one contributor is required")}}');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            showErrors(errors);
            return false;
        }

        // Show loading state
        const submitBtn = $('#submit-btn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="ti ti-loader animate-spin me-1"></i>{{__("Submitting...")}}');

        // Restore button after 5 seconds if form doesn't submit
        setTimeout(() => {
            submitBtn.prop('disabled', false).html(originalText);
        }, 5000);
    });

    // URL validation function
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    // Show validation errors
    function showErrors(errors) {
        const errorHtml = errors.map(error => `<li>${error}</li>`).join('');
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>{{__('Please fix the following errors:')}}</strong>
                <ul class="mb-0 mt-2">${errorHtml}</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert-danger').remove();
        
        // Add new alert at the top of the form
        $('.card-body').prepend(alertHtml);
        
        // Scroll to top
        $('html, body').animate({
            scrollTop: $('.alert').offset().top - 100
        }, 500);
    }

    // Real-time link validation
    $('input[name="document_link"]').on('blur', function() {
        const link = $(this).val().trim();
        const feedback = $(this).siblings('.invalid-feedback');
        
        if (link && !isValidUrl(link)) {
            $(this).addClass('is-invalid');
            if (feedback.length === 0) {
                $(this).after('<div class="invalid-feedback">{{__("Please enter a valid URL")}}</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            feedback.remove();
        }
    });

    // Auto-suggest current user as contributor
    const currentUserId = '{{ Auth::id() }}';
    const contributorsSelect = $('select[name="contributors[]"]');
    
    // Pre-select current user if available in the list
    if (contributorsSelect.find(`option[value="${currentUserId}"]`).length > 0) {
        const currentValues = contributorsSelect.val() || [];
        if (!currentValues.includes(currentUserId)) {
            currentValues.push(currentUserId);
            contributorsSelect.val(currentValues).trigger('change');
        }
    }
});
</script>
@endpush