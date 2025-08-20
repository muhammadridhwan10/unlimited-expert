@extends('layouts.admin')
@section('page-title')
    {{__('Create Question')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('psychotest-question.index')}}">{{__('Questions')}}</a></li>
    <li class="breadcrumb-item">{{__('Create')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {!! Form::open(['route' => 'psychotest-question.store', 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('category_id', __('Test Category'), ['class' => 'form-label']) !!}
                            {!! Form::select('category_id', $categories->pluck('name', 'id'), request('category'), ['class' => 'form-control select2', 'required' => true, 'placeholder' => __('Select Category')]) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('type', __('Question Type'), ['class' => 'form-label']) !!}
                            {!! Form::select('type', $types, null, ['class' => 'form-control select2', 'id' => 'type', 'required' => true, 'placeholder' => __('Select Type')]) !!}
                        </div>
                        <div class="form-group col-md-12">
                            {!! Form::label('title', __('Question Title'), ['class' => 'form-label']) !!}
                            {!! Form::text('title', null, ['class' => 'form-control', 'required' => true, 'placeholder' => __('Enter question title')]) !!}
                        </div>
                        <div class="form-group col-md-12">
                            {!! Form::label('question', __('Question'), ['class' => 'form-label']) !!}
                            {!! Form::textarea('question', null, ['class' => 'form-control', 'rows' => 3, 'required' => true, 'placeholder' => __('Enter the question...')]) !!}
                        </div>

                        <!-- Image Upload Section -->
                        <div class="form-group col-md-12 image-section" style="display: none;">
                            {!! Form::label('image', __('Question Image'), ['class' => 'form-label']) !!}
                            {!! Form::file('image', ['class' => 'form-control', 'id' => 'image', 'accept' => 'image/*']) !!}
                            <small class="text-muted">{{__('Upload image for visual questions (JPG, PNG, GIF - Max 2MB)')}}</small>
                            <div id="image-preview" class="mt-2"></div>
                        </div>

                        <div class="form-group col-md-4">
                            {!! Form::label('points', __('Points'), ['class' => 'form-label']) !!}
                            {!! Form::number('points', 1, ['class' => 'form-control', 'min' => 1, 'required' => true]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('order', __('Order'), ['class' => 'form-label']) !!}
                            {!! Form::number('order', 0, ['class' => 'form-control', 'min' => 0, 'required' => true]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('time_limit_seconds', __('Time Limit (Seconds)'), ['class' => 'form-label']) !!}
                            {!! Form::number('time_limit_seconds', null, ['class' => 'form-control', 'min' => 1]) !!}
                            <small class="text-muted">{{__('Optional individual time limit')}}</small>
                        </div>

                        <!-- Rating Scale -->
                        <div class="form-group col-md-6 rating-section" style="display: none;">
                            {!! Form::label('rating_scale', __('Rating Scale (1 to ?)'), ['class' => 'form-label']) !!}
                            {!! Form::number('rating_scale', 5, ['class' => 'form-control', 'min' => 2, 'max' => 10, 'id' => 'rating_scale']) !!}
                        </div>

                        <!-- Kraeplin Settings -->
                        <div class="form-group col-md-6 kraeplin-section" style="display: none;">
                            {!! Form::label('kraeplin_columns', __('Number of Columns'), ['class' => 'form-label']) !!}
                            {!! Form::number('kraeplin_columns', 10, ['class' => 'form-control', 'min' => 5, 'max' => 20, 'id' => 'kraeplin_columns']) !!}
                        </div>

                        <!-- Multiple Choice Options -->
                        <div class="form-group col-md-12 options-section" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">{{__('Options')}}</label>
                                <small class="text-muted">{{__('Fill options then click outside input to update answers')}}</small>
                            </div>
                            <div id="options-container">
                                <div class="input-group mb-2 option-group">
                                    <input type="text" name="options[]" class="form-control option-input" placeholder="Option 1">
                                    <button type="button" class="btn btn-outline-danger remove-option">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                                <div class="input-group mb-2 option-group">
                                    <input type="text" name="options[]" class="form-control option-input" placeholder="Option 2">
                                    <button type="button" class="btn btn-outline-danger remove-option">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
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
                            {!! Form::select('correct_answer', [], null, ['class' => 'form-control', 'id' => 'correct_answer', 'placeholder' => __('Select Correct Answer')]) !!}
                            <small class="text-muted">{{__('Select the correct answer from options above')}}</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('psychotest-question.index') }}" class="btn btn-light">{{__('Cancel')}}</a>
                        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
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
        // Initialize Select2 after DOM is ready
        setTimeout(function() {
            initializeSelect2();
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
            
            // Hide all type-specific fields
            $('.options-section, .rating-section, .correct-answer-section, .kraeplin-section, .image-section').hide();
            
            if (type === 'multiple_choice' || type === 'image_choice') {
                $('.options-section, .correct-answer-section').show();
                if (type === 'image_choice') {
                    $('.image-section').show();
                }
                // Small delay to ensure sections are visible before updating
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
            } else if (type === 'essay') {
                // No additional fields needed
            }
            
            // Show image section for visual questions
            if (type === 'image_choice' || $('#category_id option:selected').data('type') === 'visual') {
                $('.image-section').show();
            }
        });

        $('#category_id').change(function() {
            var categoryType = $(this).find('option:selected').data('type');
            if (categoryType === 'visual') {
                $('.image-section').show();
            }
        });

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
            $(this).closest('.option-group').remove();
            setTimeout(updateCorrectAnswerOptions, 200);
        });

        // Update correct answer options when ANY option input changes
        $(document).on('input keyup blur', '.option-input', function() {
            clearTimeout(window.updateTimer);
            window.updateTimer = setTimeout(updateCorrectAnswerOptions, 300);
        });

        function updateCorrectAnswerOptions() {
            var options = [];
            var currentValue = $('#correct_answer').val();
            
            // Get all option values that are not empty
            $('.option-input').each(function() {
                var value = $(this).val().trim();
                if (value !== '') {
                    options.push(value);
                }
            });
            
            console.log('Updating correct answer options:', options);
            
            // Destroy existing Select2 instance
            if ($('#correct_answer').hasClass('select2-hidden-accessible')) {
                $('#correct_answer').select2('destroy');
            }
            
            // Clear and rebuild options
            var correctAnswerSelect = $('#correct_answer');
            correctAnswerSelect.empty();
            correctAnswerSelect.append('<option value="">Select Correct Answer</option>');
            
            // Add each option to the dropdown
            options.forEach(function(option) {
                var selected = (option === currentValue) ? 'selected' : '';
                correctAnswerSelect.append(`<option value="${option}" ${selected}>${option}</option>`);
            });
            
            // Reinitialize Select2
            correctAnswerSelect.select({
                placeholder: 'Select Correct Answer',
                allowClear: true
            });
            
            // Set the previously selected value
            if (currentValue && options.includes(currentValue)) {
                correctAnswerSelect.val(currentValue).trigger('change');
            }
            
            console.log('Select2 reinitialized with options:', options);
        }

        function updateTrueFalseOptions() {
            var currentValue = $('#correct_answer').val();
            
            // Destroy existing Select2
            if ($('#correct_answer').hasClass('select2-hidden-accessible')) {
                $('#correct_answer').select2('destroy');
            }
            
            $('#correct_answer').empty();
            $('#correct_answer').append('<option value="">Select Correct Answer</option>');
            $('#correct_answer').append('<option value="True">True</option>');
            $('#correct_answer').append('<option value="False">False</option>');
            
            // Reinitialize Select2
            $('#correct_answer').select2({
                placeholder: 'Select Correct Answer',
                allowClear: true
            });
            
            if (currentValue === 'True' || currentValue === 'False') {
                $('#correct_answer').val(currentValue).trigger('change');
            }
        }

        function updateRatingOptions() {
            var scale = $('#rating_scale').val() || 5;
            var currentValue = $('#correct_answer').val();
            
            // Destroy existing Select2
            if ($('#correct_answer').hasClass('select2-hidden-accessible')) {
                $('#correct_answer').select2('destroy');
            }
            
            $('#correct_answer').empty();
            $('#correct_answer').append('<option value="">Select Correct Answer</option>');
            
            for (var i = 1; i <= scale; i++) {
                var selected = (i == currentValue) ? 'selected' : '';
                $('#correct_answer').append(`<option value="${i}" ${selected}>${i}</option>`);
            }
            
            // Reinitialize Select2
            $('#correct_answer').select2({
                placeholder: 'Select Correct Answer',
                allowClear: true
            });
            
            if (currentValue && currentValue >= 1 && currentValue <= scale) {
                $('#correct_answer').val(currentValue).trigger('change');
            }
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

        // Manual refresh button (add this to your HTML if needed)
        $(document).on('click', '#manual-refresh', function() {
            updateCorrectAnswerOptions();
            $(this).text('Refreshed!').addClass('btn-success').removeClass('btn-warning');
            setTimeout(() => {
                $(this).text('Manual Refresh').removeClass('btn-success').addClass('btn-warning');
            }, 1500);
        });
    });
</script>
@endpush