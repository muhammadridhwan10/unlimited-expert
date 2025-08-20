<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Psychotest - Assessment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .test-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .question-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .question-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .timer {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 0.5rem 1rem;
        }
        .option-item {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .option-item:hover {
            border-color: #667eea;
            background-color: #f8f9ff;
        }
        .option-item.selected {
            border-color: #667eea;
            background-color: #e7f3ff;
        }
        .progress-container {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="test-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h5 class="mb-0">{{ $schedule->candidate->name }}</h5>
                    <small>Psychotest Assessment</small>
                </div>
                <div class="col-md-4 text-center">
                    <div class="progress-container">
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" style="width: 0%" id="progress-bar"></div>
                        </div>
                        <small class="text-white" id="progress-text">0 of {{ $questions->count() }} questions</small>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="timer">
                        <i class="fas fa-clock"></i>
                        <span id="timer">{{ $remainingMinutes }}:00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <form id="test-form">
            @csrf
            @foreach($questions as $index => $question)
            <div class="question-card card" data-question="{{ $index + 1 }}" style="{{ $index > 0 ? 'display: none;' : '' }}">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="question-number me-3">{{ $index + 1 }}</div>
                        <div class="flex-grow-1">
                            <h5 class="card-title">{{ $question->title }}</h5>
                            <p class="card-text">{{ $question->question }}</p>
                            
                            @if($question->type == 'multiple_choice')
                                @foreach($question->options as $optionIndex => $option)
                                <div class="option-item" data-value="{{ $option }}">
                                    <input type="radio" name="question_{{ $question->id }}" value="{{ $option }}" 
                                           id="q{{ $question->id }}_{{ $optionIndex }}" class="me-2"
                                           {{ ($answers[$question->id]->answer ?? '') == $option ? 'checked' : '' }}>
                                    <label for="q{{ $question->id }}_{{ $optionIndex }}" class="mb-0 w-100">{{ $option }}</label>
                                </div>
                                @endforeach
                            
                            @elseif($question->type == 'true_false')
                                <div class="option-item" data-value="True">
                                    <input type="radio" name="question_{{ $question->id }}" value="True" 
                                           id="q{{ $question->id }}_true" class="me-2"
                                           {{ ($answers[$question->id]->answer ?? '') == 'True' ? 'checked' : '' }}>
                                    <label for="q{{ $question->id }}_true" class="mb-0 w-100">True</label>
                                </div>
                                <div class="option-item" data-value="False">
                                    <input type="radio" name="question_{{ $question->id }}" value="False" 
                                           id="q{{ $question->id }}_false" class="me-2"
                                           {{ ($answers[$question->id]->answer ?? '') == 'False' ? 'checked' : '' }}>
                                    <label for="q{{ $question->id }}_false" class="mb-0 w-100">False</label>
                                </div>
                            
                            @elseif($question->type == 'rating_scale')
                                <div class="row">
                                    @foreach($question->options as $rating)
                                    <div class="col-auto">
                                        <div class="option-item text-center" data-value="{{ $rating }}" style="width: 60px;">
                                            <input type="radio" name="question_{{ $question->id }}" value="{{ $rating }}" 
                                                   id="q{{ $question->id }}_{{ $rating }}" class="mb-2"
                                                   {{ ($answers[$question->id]->answer ?? '') == $rating ? 'checked' : '' }}>
                                            <label for="q{{ $question->id }}_{{ $rating }}" class="mb-0 w-100">{{ $rating }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            
                            @elseif($question->type == 'essay')
                                <textarea name="question_{{ $question->id }}" class="form-control" rows="4" 
                                          placeholder="Type your answer here...">{{ $answers[$question->id]->answer ?? '' }}</textarea>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <div class="card">
                <div class="card-body text-center">
                    <div class="row">
                        <div class="col-6">
                            <button type="button" id="prev-btn" class="btn btn-outline-primary" disabled>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                        </div>
                        <div class="col-6 text-end">
                            <button type="button" id="next-btn" class="btn btn-primary">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                            <button type="button" id="submit-btn" class="btn btn-success" style="display: none;">
                                <i class="fas fa-check"></i> Submit Test
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Submit Confirmation Modal -->
    <div class="modal fade" id="submitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to submit your test?</p>
                    <p class="text-warning"><small>You cannot change your answers after submission.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirm-submit">Submit Test</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentQuestion = 1;
        const totalQuestions = {{ $questions->count() }};
        let remainingSeconds = {{ $remainingMinutes * 60 }};

        // Timer
        function updateTimer() {
            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;
            document.getElementById('timer').textContent = 
                minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
            
            if (remainingSeconds <= 0) {
                alert('Time is up! Submitting your test automatically.');
                submitTest();
                return;
            }
            
            remainingSeconds--;
        }

        setInterval(updateTimer, 1000);

        // Navigation
        function showQuestion(questionNum) {
            document.querySelectorAll('.question-card').forEach(card => {
                card.style.display = 'none';
            });
            
            document.querySelector(`[data-question="${questionNum}"]`).style.display = 'block';
            
            // Update navigation buttons
            document.getElementById('prev-btn').disabled = questionNum === 1;
            
            if (questionNum === totalQuestions) {
                document.getElementById('next-btn').style.display = 'none';
                document.getElementById('submit-btn').style.display = 'inline-block';
            } else {
                document.getElementById('next-btn').style.display = 'inline-block';
                document.getElementById('submit-btn').style.display = 'none';
            }
            
            // Update progress
            const progress = (questionNum / totalQuestions) * 100;
            document.getElementById('progress-bar').style.width = progress + '%';
            document.getElementById('progress-text').textContent = `${questionNum} of ${totalQuestions} questions`;
        }

        document.getElementById('next-btn').addEventListener('click', () => {
            if (currentQuestion < totalQuestions) {
                currentQuestion++;
                showQuestion(currentQuestion);
            }
        });

        document.getElementById('prev-btn').addEventListener('click', () => {
            if (currentQuestion > 1) {
                currentQuestion--;
                showQuestion(currentQuestion);
            }
        });

        // Auto-save answers
        function saveAnswer(questionId, answer) {
            fetch('{{ route("psychotest.test.save-answer") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    question_id: questionId,
                    answer: answer
                })
            }).then(response => {
                if (!response.ok) {
                    console.error('Failed to save answer');
                }
            });
        }

        // Handle answer selection
        document.addEventListener('change', (e) => {
            if (e.target.type === 'radio' || e.target.tagName === 'TEXTAREA') {
                const questionId = e.target.name.replace('question_', '');
                saveAnswer(questionId, e.target.value);
            }
        });

        // Handle option clicks
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('option-item') || e.target.closest('.option-item')) {
                const optionItem = e.target.classList.contains('option-item') ? e.target : e.target.closest('.option-item');
                const radio = optionItem.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                    
                    // Update visual selection
                    optionItem.parentElement.querySelectorAll('.option-item').forEach(item => {
                        item.classList.remove('selected');
                    });
                    optionItem.classList.add('selected');
                }
            }
        });

        // Submit test
        document.getElementById('submit-btn').addEventListener('click', () => {
            new bootstrap.Modal(document.getElementById('submitModal')).show();
        });

        document.getElementById('confirm-submit').addEventListener('click', () => {
            submitTest();
        });

        function submitTest() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("psychotest.test.submit") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        }

        // Prevent accidental page refresh
        window.addEventListener('beforeunload', (e) => {
            e.preventDefault();
            e.returnValue = '';
        });
    </script>
</body>
</html>