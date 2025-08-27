@extends('layouts.admin')
@section('page-title')
    {{__('Edit Question')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('psychotest-question.index')}}">{{__('Questions')}}</a></li>
    <li class="breadcrumb-item">{{__('Edit')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Edit Question')}}</h5>
                    <small class="text-muted">{{__('Modify the question details and settings')}}</small>
                </div>
                <div class="card-body">
                    {!! Form::model($question, ['route' => ['psychotest-question.update', $question->id], 'method' => 'put', 'enctype' => 'multipart/form-data', 'id' => 'edit-question-form']) !!}
                    
                    <!-- Question Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="ti ti-info-circle me-2"></i>{{__('Current Question Information')}}</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>{{__('Category')}}:</strong> {{ $question->category->name ?? 'N/A' }}<br>
                                        <strong>{{__('Type')}}:</strong> 
                                        <span class="badge bg-info">{{ \App\Models\PsychotestQuestion::$types[$question->type] ?? $question->type }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{__('Points')}}:</strong> {{ $question->points }}<br>
                                        <strong>{{__('Status')}}:</strong>
                                        <span class="badge bg-{{ $question->is_active ? 'success' : 'danger' }}">
                                            {{ $question->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('category_id', __('Test Category'), ['class' => 'form-label']) !!}
                            {!! Form::select('category_id', $categories->pluck('name', 'id'), null, ['class' => 'form-control select2', 'required' => true]) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('type', __('Question Type'), ['class' => 'form-label']) !!}
                            {!! Form::select('type', $types, null, ['class' => 'form-control select2', 'id' => 'type', 'required' => true]) !!}
                            <small class="text-muted">{{__('Changing type will reset options and correct answers')}}</small>
                        </div>
                        <div class="form-group col-md-12">
                            {!! Form::label('title', __('Question Title'), ['class' => 'form-label']) !!}
                            {!! Form::text('title', null, ['class' => 'form-control', 'required' => true, 'placeholder' => __('Enter question title')]) !!}
                        </div>
                        <div class="form-group col-md-12">
                            {!! Form::label('question', __('Question'), ['class' => 'form-label']) !!}
                            {!! Form::textarea('question', null, ['class' => 'form-control', 'rows' => 3, 'required' => true, 'placeholder' => __('Enter the question...')]) !!}
                        </div>

                        <!-- Current Image Display -->
                        @if($question->image)
                            <div class="form-group col-md-12 current-image-section">
                                <label class="form-label">{{__('Current Image')}}</label>
                                <div class="d-flex align-items-start gap-3">
                                    <img src="{{ $question->getImageUrl() }}" alt="Current Image" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    <div>
                                        <div class="form-check">
                                            <input type="checkbox" name="remove_image" value="1" class="form-check-input" id="remove_image">
                                            <label class="form-check-label text-danger" for="remove_image">
                                                <i class="ti ti-trash me-1"></i>{{__('Remove current image')}}
                                            </label>
                                        </div>
                                        <small class="text-muted">{{__('Check this box to remove the current image')}}</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Image Upload Section -->
                        <div class="form-group col-md-12 image-section" style="display: none;">
                            {!! Form::label('image', __('Question Image'), ['class' => 'form-label']) !!}
                            {!! Form::file('image', ['class' => 'form-control', 'id' => 'image', 'accept' => 'image/*']) !!}
                            <small class="text-muted">{{__('Upload new image (JPG, PNG, GIF - Max 2MB). This will replace the current image.')}}</small>
                            <div id="image-preview" class="mt-2"></div>
                        </div>

                        <div class="form-group col-md-4">
                            {!! Form::label('points', __('Points'), ['class' => 'form-label']) !!}
                            {!! Form::number('points', null, ['class' => 'form-control', 'min' => 1, 'required' => true]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('order', __('Order'), ['class' => 'form-label']) !!}
                            {!! Form::number('order', null, ['class' => 'form-control', 'min' => 0, 'required' => true]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('time_limit_seconds', __('Time Limit (Seconds)'), ['class' => 'form-label']) !!}
                            {!! Form::number('time_limit_seconds', null, ['class' => 'form-control', 'min' => 1]) !!}
                            <small class="text-muted">{{__('Optional individual time limit')}}</small>
                        </div>

                        <!-- Rating Scale -->
                        <div class="form-group col-md-6 rating-section" style="display: none;">
                            {!! Form::label('rating_scale', __('Rating Scale (1 to ?)'), ['class' => 'form-label']) !!}
                            {!! Form::number('rating_scale', $question->options ? max($question->options) : 5, ['class' => 'form-control', 'min' => 2, 'max' => 10, 'id' => 'rating_scale']) !!}
                        </div>

                        <!-- Kraeplin Settings -->
                        <div class="form-group col-md-6 kraeplin-section" style="display: none;">
                            {!! Form::label('kraeplin_columns', __('Number of Columns'), ['class' => 'form-label']) !!}
                            @php
                                $kraeplinColumns = 10;
                                if ($question->kraeplin_data && is_array($question->kraeplin_data) && isset($question->kraeplin_data['columns'])) {
                                    $kraeplinColumns = $question->kraeplin_data['columns'];
                                }
                            @endphp
                            {!! Form::number('kraeplin_columns', $kraeplinColumns, ['class' => 'form-control', 'min' => 5, 'max' => 20, 'id' => 'kraeplin_columns']) !!}
                        </div>

                        <!-- Multiple Choice Options -->
                        <div class="form-group col-md-12 options-section" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">{{__('Options')}}</label>
                                <small class="text-muted">{{__('Fill options then click outside input to update answers')}}</small>
                            </div>
                            <div id="options-container">
                                @if($question->options && is_array($question->options))
                                    @foreach($question->options as $index => $option)
                                        <div class="input-group mb-2 option-group">
                                            <input type="text" name="options[]" class="form-control option-input" value="{{ $option }}" placeholder="Option {{ $index + 1 }}" required>
                                            <button type="button" class="btn btn-outline-danger remove-option">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Default empty options -->
                                    <div class="input-group mb-2 option-group">
                                        <input type="text" name="options[]" class="form-control option-input" placeholder="Option 1" required>
                                        <button type="button" class="btn btn-outline-danger remove-option">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                    <div class="input-group mb-2 option-group">
                                        <input type="text" name="options[]" class="form-control option-input" placeholder="Option 2" required>
                                        <button type="button" class="btn btn-outline-danger remove-option">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" id="add-option" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-plus"></i> {{__('Add Option')}}
                                </button>
                                <button type="button" id="manual-refresh" class="btn btn-sm btn-warning">
                                    <i class="ti ti-refresh"></i> {{__('Manual Refresh')}}
                                </button>
                            </div>
                        </div>

                        <!-- Correct Answer -->
                        <div class="form-group col-md-6 correct-answer-section" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">{{__('Correct Answer')}}</label>
                                <span class="badge bg-info" id="answer-count">0 options</span>
                            </div>
                            {!! Form::select('correct_answer', [], $question->correct_answer, ['class' => 'form-control', 'id' => 'correct_answer', 'placeholder' => __('Select Correct Answer')]) !!}
                            <small class="text-muted">{{__('Select the correct answer from options above')}}</small>
                        </div>

                        <!-- Current Question Status -->
                        <div class="form-group col-md-6">
                            <label class="form-label">{{__('Question Status')}}</label>
                            <div class="form-check form-switch">
                                {!! Form::checkbox('is_active', 1, $question->is_active, ['class' => 'form-check-input', 'id' => 'is_active']) !!}
                                <label class="form-check-label" for="is_active">
                                    {{__('Active')}}
                                </label>
                            </div>
                            <small class="text-muted">{{__('Inactive questions will not appear in tests')}}</small>
                        </div>
                    </div>

                    <!-- Change Summary -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">{{__('Question Summary')}}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>{{__('Created')}}:</strong><br>
                                            <small class="text-muted">{{ $question->created_at ? $question->created_at->format('d M Y H:i') : 'N/A' }}</small>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>{{__('Last Updated')}}:</strong><br>
                                            <small class="text-muted">{{ $question->updated_at ? $question->updated_at->format('d M Y H:i') : 'N/A' }}</small>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>{{__('Created By')}}:</strong><br>
                                            <small class="text-muted">{{ $question->creator->name ?? 'N/A' }}</small>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>{{__('Usage')}}:</strong><br>
                                            <small class="text-muted">
                                                @if($question->answers()->exists())
                                                    <span class="badge bg-warning">{{__('Used in tests')}}</span>
                                                @else
                                                    <span class="badge bg-light text-dark">{{__('Not used yet')}}</span>
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warning for used questions -->
                    @if($question->answers()->exists())
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="ti ti-alert-triangle me-2"></i>
                                    {{__('This question has been used in tests. Major changes may affect existing test results and statistics.')}}
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="modal-footer border-top mt-4 pt-3">
                        <a href="{{ route('psychotest-question.index') }}" class="btn btn-light me-2">
                            <i class="ti ti-arrow-left me-1"></i>{{__('Cancel')}}
                        </a>
                        <a href="{{ route('psychotest-question.show', $question->id) }}" class="btn btn-info me-2">
                            <i class="ti ti-eye me-1"></i>{{__('View Details')}}
                        </a>
                        <input type="submit" value="{{__('Update Question')}}" class="btn btn-primary">
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
    
    // Initialize Select2
    setTimeout(function() {
        initializeSelect2();
        showSectionsBasedOnType(initialType);
    }, 100);

    function initializeSelect2() {
        if ($('#correct_answer').hasClass('select2-hidden-accessible')) {
            $('#correct_answer').select2('destroy');
        }
        $('#correct_answer').select2({
            placeholder: 'Select Correct Answer',
            allowClear: true
        });
    }
    
    $('#type').change(function() {
        var type = $(this).val();
        showSectionsBasedOnType(type);
    });

    function showSectionsBasedOnType(type) {
        // Hide all type-specific fields
        $('.options-section, .rating-section, .correct-answer-section, .kraeplin-section, .image-section').hide();
        
        if (type === 'multiple_choice' || type === 'image_choice') {
            $('.options-section, .correct-answer-section').show();
            if (type === 'image_choice') {
                $('.image-section').show();
            }
            setTimeout(function() {
                updateCorrectAnswerOptions();
            }, 100);
        } else if (type === 'true_false') {
            $('.correct-answer-section').show();
            setTimeout(function() {
                updateTrueFalseOptions();
            }, 100);
        } else if (type === 'rating_scale') {
            $('.rating-section, .correct-answer-section').show();
            setTimeout(function() {
                updateRatingOptions();
            }, 100);
        } else if (type === 'kraeplin') {
            $('.kraeplin-section').show();
        }
        
        if (type === 'image_choice') {
            $('.image-section').show();
        }
    }

    // Add new option
    $('#add-option').click(function() {
        var optionCount = $('.option-input').length;
        var newOption = `
            <div class="input-group mb-2 option-group">
                <input type="text" name="options[]" class="form-control option-input" placeholder="Option ${optionCount + 1}" required>
                <button type="button" class="btn btn-outline-danger remove-option">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        `;
        $('#options-container').append(newOption);
        setTimeout(updateCorrectAnswerOptions, 200);
    });

    // Remove option
    $(document).on('click', '.remove-option', function() {
        if ($('.option-group').length > 2) {
            $(this).closest('.option-group').remove();
            setTimeout(updateCorrectAnswerOptions, 200);
        } else {
            alert('{{__("At least 2 options are required")}}');
        }
    });

    // Update correct answer options when ANY option input changes
    $(document).on('input keyup blur', '.option-input', function() {
        clearTimeout(window.updateTimer);
        window.updateTimer = setTimeout(updateCorrectAnswerOptions, 300);
    });

    function updateCorrectAnswerOptions() {
        var options = [];
        var currentValue = $('#correct_answer').val();
        
        $('.option-input').each(function() {
            var value = $(this).val().trim();
            if (value !== '') {
                options.push(value);
            }
        });
        
        // Destroy existing Select2
        if ($('#correct_answer').hasClass('select2-hidden-accessible')) {
            $('#correct_answer').select2('destroy');
        }
        
        // Clear and rebuild options
        var correctAnswerSelect = $('#correct_answer');
        correctAnswerSelect.empty();
        correctAnswerSelect.append('<option value="">Select Correct Answer</option>');
        
        options.forEach(function(option) {
            var selected = (option === currentValue) ? 'selected' : '';
            correctAnswerSelect.append(`<option value="${option}" ${selected}>${option}</option>`);
        });
        
        // Reinitialize Select2
        correctAnswerSelect.select2({
            placeholder: 'Select Correct Answer',
            allowClear: true
        });
        
        if (currentValue && options.includes(currentValue)) {
            correctAnswerSelect.val(currentValue).trigger('change');
        }
        
        $('#answer-count').text(`${options.length} options`);
    }

    function updateTrueFalseOptions() {
        var currentValue = $('#correct_answer').val();
        
        if ($('#correct_answer').hasClass('select2-hidden-accessible')) {
            $('#correct_answer').select2('destroy');
        }
        
        $('#correct_answer').empty();
        $('#correct_answer').append('<option value="">Select Correct Answer</option>');
        $('#correct_answer').append('<option value="True">True</option>');
        $('#correct_answer').append('<option value="False">False</option>');
        
        $('#correct_answer').select2({
            placeholder: 'Select Correct Answer',
            allowClear: true
        });
        
        if (currentValue === 'True' || currentValue === 'False') {
            $('#correct_answer').val(currentValue).trigger('change');
        }
        
        $('#answer-count').text('2 options');
    }

    function updateRatingOptions() {
        var scale = $('#rating_scale').val() || 5;
        var currentValue = $('#correct_answer').val();
        
        if ($('#correct_answer').hasClass('select2-hidden-accessible')) {
            $('#correct_answer').select2('destroy');
        }
        
        $('#correct_answer').empty();
        $('#correct_answer').append('<option value="">Select Correct Answer</option>');
        
        for (var i = 1; i <= scale; i++) {
            var selected = (i == currentValue) ? 'selected' : '';
            $('#correct_answer').append(`<option value="${i}" ${selected}>${i}</option>`);
        }
        
        $('#correct_answer').select2({
            placeholder: 'Select Correct Answer',
            allowClear: true
        });
        
        if (currentValue && currentValue >= 1 && currentValue <= scale) {
            $('#correct_answer').val(currentValue).trigger('change');
        }
        
        $('#answer-count').text(`${scale} options`);
    }

    $('#rating_scale').change(function() {
        updateRatingOptions();
    });

    // Image preview
    $('#image').change(function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').html(`<img src="${e.target.result}" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">`);
            }
            reader.readAsDataURL(file);
        }
    });

    // Handle remove image checkbox
    $('#remove_image').change(function() {
        if ($(this).is(':checked')) {
            $('.current-image-section img').addClass('opacity-50');
            $('.image-section').show();
        } else {
            $('.current-image-section img').removeClass('opacity-50');
            $('.image-section').hide();
        }
    });

    // Manual refresh button
    $(document).on('click', '#manual-refresh', function() {
        updateCorrectAnswerOptions();
        $(this).text('Refreshed!').addClass('btn-success').removeClass('btn-warning');
        setTimeout(() => {
            $(this).text('Manual Refresh').removeClass('btn-success').addClass('btn-warning');
        }, 1500);
    });

    // Form validation
    $('#edit-question-form').on('submit', function(e) {
        var type = $('#type').val();
        
        if ((type === 'multiple_choice' || type === 'image_choice') && $('.option-input').length < 2) {
            e.preventDefault();
            alert('{{__("At least 2 options are required for multiple choice questions")}}');
            return false;
        }
        
        if ((type === 'multiple_choice' || type === 'image_choice' || type === 'true_false' || type === 'rating_scale') && !$('#correct_answer').val()) {
            e.preventDefault();
            alert('{{__("Please select a correct answer")}}');
            return false;
        }
    });
});
</script>
@endpush