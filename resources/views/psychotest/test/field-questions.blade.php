<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $category->name }} - Field Specific Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        .test-header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            color: #333;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-layout {
            min-height: calc(100vh - 80px);
            margin: 0;
            padding: 0;
        }
        .questions-column {
            background: white;
            min-height: calc(100vh - 80px);
            padding: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .navigation-column {
            background: rgba(255,255,255,0.95);
            min-height: calc(100vh - 80px);
            padding: 0;
            border-left: 1px solid #e0e0e0;
        }
        .question-content {
            padding: 40px;
            height: calc(100vh - 80px);
            overflow-y: auto;
        }
        .navigation-content {
            padding: 30px 20px;
            height: calc(100vh - 80px);
            overflow-y: auto;
        }
        .question-card {
            background: transparent;
            border: none;
            padding: 0;
            margin-bottom: 0;
        }
        .question-number-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .timer {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 0.8rem 1.5rem;
            color: #333;
            font-weight: bold;
        }
        .timer.warning {
            background: rgba(255,107,107,0.2);
            color: #d63031;
            animation: pulse 1s infinite;
        }
        
        /* Field Specific Styles */
        .field-question-container {
            background: linear-gradient(135deg, #f8fafc 0%, #e7f3ff 100%);
            border: 2px solid #0ea5e9;
            border-radius: 20px;
            padding: 30px;
            margin: 20px 0;
        }
        
        .difficulty-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .difficulty-easy {
            background: linear-gradient(135deg, #d4f8d4 0%, #a8e6a8 100%);
            color: #166534;
        }
        
        .difficulty-medium {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }
        
        .difficulty-hard {
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            color: #991b1b;
        }
        
        .question-type-indicator {
            background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);
            border: 1px solid #0284c7;
            border-radius: 10px;
            padding: 8px 15px;
            margin-bottom: 20px;
            display: inline-block;
            font-size: 14px;
            font-weight: 500;
            color: #0284c7;
        }
        
        .option-item {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.2rem;
            margin-bottom: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
            position: relative;
        }
        
        .option-item:hover {
            border-color: #667eea;
            background-color: #f0f4ff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.2);
        }
        
        .option-item.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #e7f3ff 0%, #f0f4ff 100%);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
        }
        
        .option-letter {
            position: absolute;
            top: -10px;
            left: 20px;
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .option-item.selected .option-letter {
            background: #22c55e;
        }
        
        .case-study-container {
            background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%);
            border: 2px solid #f59e0b;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
        }
        
        .case-study-title {
            color: #d97706;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .points-indicator {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border: 1px solid #22c55e;
            border-radius: 20px;
            padding: 5px 15px;
            margin-bottom: 15px;
            display: inline-block;
            font-size: 13px;
            font-weight: 600;
            color: #166534;
        }
        
        .question-nav-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 30px;
        }
        .question-nav-btn {
            width: 45px;
            height: 45px;
            border: 2px solid #e9ecef;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            position: relative;
        }
        .question-nav-btn:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        .question-nav-btn.current {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
        .question-nav-btn.answered {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .question-nav-btn.answered.current {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .difficulty-indicator {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .difficulty-indicator.easy { background: #22c55e; }
        .difficulty-indicator.medium { background: #f59e0b; }
        .difficulty-indicator.hard { background: #ef4444; }
        
        .progress-info {
            background: rgba(102,126,234,0.1);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .submit-section {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 20px;
            border-top: 1px solid #e0e0e0;
            margin-top: auto;
        }
        .legend {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 12px;
            flex-wrap: wrap;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .legend-box {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        .field-progress {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #0ea5e9;
        }
        
        .specialty-indicator {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #f59e0b;
            border-radius: 10px;
            padding: 10px 15px;
            margin-bottom: 20px;
            color: #92400e;
            font-size: 14px;
            font-weight: 500;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        @media (max-width: 768px) {
            .questions-column {
                min-height: 60vh;
            }
            .navigation-column {
                min-height: 40vh;
            }
            .question-content {
                padding: 20px;
                height: 60vh;
            }
            .navigation-content {
                padding: 20px 15px;
                height: 40vh;
            }
        }
    </style>
</head>
<body>
    <div class="test-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h5 class="mb-0">{{ $category->name }}</h5>
                    <small class="text-muted">{{ $session->schedule->candidates->name }}</small>
                </div>
                <div class="col-md-6 text-center">
                    <div class="d-flex justify-content-center align-items-center gap-4">
                        <div>
                            <strong id="answered-count">0</strong> / {{ $questions->count() }} 
                            <small class="text-muted">Answered</small>
                        </div>
                        <div class="timer" id="main-timer">
                            <i class="fas fa-clock"></i>
                            <span id="timer">{{ floor($remainingSeconds / 60) }}:{{ sprintf('%02d', $remainingSeconds % 60) }}</span>
                        </div>
                        <div>
                            <strong id="score-display">0</strong> 
                            <small class="text-muted">Points</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <small class="text-muted">Question <span id="current-q">1</span> of {{ $questions->count() }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid test-layout">
        <div class="row h-100">
            <!-- Questions Column -->
            <div class="col-lg-8 questions-column">
                <div class="question-content">
                    <form id="test-form">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $session->id }}">
                        
                        @foreach($questions as $index => $question)
                        <div class="question-card" data-question="{{ $index + 1 }}" data-question-id="{{ $question->id }}" style="{{ $index > 0 ? 'display: none;' : '' }}">
                            
                            <!-- Question Container -->
                            <div class="field-question-container position-relative">
                                <!-- Difficulty Badge -->
                                @php
                                    $difficulty = $question->difficulty ?? 'medium';
                                @endphp
                                <div class="difficulty-badge difficulty-{{ $difficulty }}">
                                    {{ ucfirst($difficulty) }}
                                </div>
                                
                                <div class="d-flex align-items-start mb-4">
                                    <div class="question-number-badge me-4">{{ $index + 1 }}</div>
                                    <div class="flex-grow-1">
                                        
                                        <!-- Question Type & Points -->
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            <div class="question-type-indicator">
                                                <i class="fas fa-briefcase me-1"></i>
                                                Field Specific
                                            </div>
                                            <div class="points-indicator">
                                                <i class="fas fa-star me-1"></i>
                                                {{ $question->points }} {{ $question->points > 1 ? 'Points' : 'Point' }}
                                            </div>
                                        </div>

                                        <!-- Specialty Indicator -->
                                        @if($category->target_job_keywords)
                                        <div class="specialty-indicator">
                                            <i class="fas fa-tags me-2"></i>
                                            <strong>Bidang:</strong> {{ implode(', ', array_map('ucfirst', $category->target_job_keywords)) }}
                                        </div>
                                        @endif
                                        
                                        <!-- Question Title -->
                                        <h4 class="mb-3">{{ $question->title }}</h4>
                                        
                                        <!-- Case Study (if applicable) -->
                                        @if(stripos($question->question, 'case study') !== false || stripos($question->question, 'studi kasus') !== false)
                                        <div class="case-study-container">
                                            <div class="case-study-title">
                                                <i class="fas fa-file-alt"></i>
                                                Studi Kasus
                                            </div>
                                            <div class="case-study-content">
                                                {!! nl2br(e($question->question)) !!}
                                            </div>
                                        </div>
                                        @else
                                        <!-- Regular Question -->
                                        <div class="question-text mb-4" style="font-size: 16px; line-height: 1.6; color: #374151;">
                                            {!! nl2br(e($question->question)) !!}
                                        </div>
                                        @endif
                                        
                                        @if($question->image)
                                            <div class="text-center mb-4">
                                                <img src="{{ asset('storage/public/uploads/psychotest/images/' . $question->image) }}" alt="Question Image" class="img-fluid rounded shadow" style="max-height: 400px;">
                                            </div>
                                        @endif
                                        
                                        <!-- Answer Options -->
                                        @if($question->type == 'multiple_choice' || $question->type == 'field_specific')
                                            @foreach($question->options as $optionIndex => $option)
                                            <div class="option-item" data-value="{{ $option }}">
                                                <div class="option-letter">{{ chr(65 + $optionIndex) }}</div>
                                                <input type="radio" name="question_{{ $question->id }}" value="{{ $option }}" 
                                                       id="q{{ $question->id }}_{{ $optionIndex }}" class="me-3 d-none"
                                                       {{ ($answers[$question->id]->answer ?? '') == $option ? 'checked' : '' }}>
                                                <label for="q{{ $question->id }}_{{ $optionIndex }}" class="mb-0 w-100" style="font-size: 16px; padding-left: 10px;">
                                                    {{ $option }}
                                                </label>
                                            </div>
                                            @endforeach
                                        
                                        @elseif($question->type == 'true_false')
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="option-item" data-value="True">
                                                        <div class="option-letter">A</div>
                                                        <input type="radio" name="question_{{ $question->id }}" value="True" 
                                                               id="q{{ $question->id }}_true" class="me-3 d-none"
                                                               {{ ($answers[$question->id]->answer ?? '') == 'True' ? 'checked' : '' }}>
                                                        <label for="q{{ $question->id }}_true" class="mb-0 w-100" style="padding-left: 10px;">
                                                            <i class="fas fa-check-circle text-success me-2"></i>Benar (True)
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="option-item" data-value="False">
                                                        <div class="option-letter">B</div>
                                                        <input type="radio" name="question_{{ $question->id }}" value="False" 
                                                               id="q{{ $question->id }}_false" class="me-3 d-none"
                                                               {{ ($answers[$question->id]->answer ?? '') == 'False' ? 'checked' : '' }}>
                                                        <label for="q{{ $question->id }}_false" class="mb-0 w-100" style="padding-left: 10px;">
                                                            <i class="fas fa-times-circle text-danger me-2"></i>Salah (False)
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                        @elseif($question->type == 'essay')
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Jawaban Anda:</label>
                                                <textarea name="question_{{ $question->id }}" class="form-control" rows="8" 
                                                          placeholder="Ketik jawaban Anda di sini..." 
                                                          style="border-radius: 10px; border: 2px solid #e9ecef; padding: 20px; font-size: 16px; line-height: 1.6;">{{ $answers[$question->id]->answer ?? '' }}</textarea>
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Berikan jawaban yang lengkap dan detail sesuai dengan pertanyaan.
                                                </small>
                                            </div>
                                        @endif

                                        @if($question->time_limit_seconds)
                                            <div class="alert alert-warning mt-3">
                                                <i class="fas fa-clock"></i> 
                                                <strong>Perhatian:</strong> Soal ini memiliki batas waktu {{ $question->time_limit_seconds }} detik
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </form>
                </div>
            </div>

            <!-- Navigation Column -->
            <div class="col-lg-4 navigation-column">
                <div class="navigation-content">
                    <h5 class="mb-3">Field Test Navigation</h5>
                    
                    <!-- Legend -->
                    <div class="legend">
                        <div class="legend-item">
                            <div class="legend-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                            <span>Current</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-box" style="background: #d4edda; border: 1px solid #28a745;"></div>
                            <span>Answered</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-box" style="background: white; border: 1px solid #e9ecef;"></div>
                            <span>Not Answered</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-box" style="background: #22c55e;"></div>
                            <span>Easy</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-box" style="background: #f59e0b;"></div>
                            <span>Medium</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-box" style="background: #ef4444;"></div>
                            <span>Hard</span>
                        </div>
                    </div>

                    <!-- Progress Info -->
                    <div class="field-progress">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-chart-pie me-1"></i> Progress:</span>
                            <span><strong id="progress-percentage">0%</strong></span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 10px;">
                            <div class="progress-bar" id="progress-bar" style="width: 0%; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);"></div>
                        </div>
                        <div class="mt-3 row">
                            <div class="col-6">
                                <small class="text-muted">
                                    <i class="fas fa-star me-1"></i>
                                    Score: <strong id="current-score">0</strong>
                                </small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">
                                    <i class="fas fa-target me-1"></i>
                                    Target: {{ $category->settings['passing_score'] ?? 70 }}%
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Question Navigation Grid -->
                    <div class="question-nav-grid">
                        @foreach($questions as $index => $question)
                        @php
                            $difficulty = $question->difficulty ?? 'medium';
                        @endphp
                        <div class="question-nav-btn {{ $index == 0 ? 'current' : '' }}" 
                             data-question="{{ $index + 1 }}" 
                             data-question-id="{{ $question->id }}"
                             onclick="goToQuestion({{ $index + 1 }})">
                            {{ $index + 1 }}
                            <div class="difficulty-indicator {{ $difficulty }}"></div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Quick Navigation -->
                    <div class="d-flex gap-2 mb-4">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="goToQuestion(1)">
                            <i class="fas fa-fast-backward"></i> First
                        </button>
                        <button type="button" id="prev-btn" class="btn btn-outline-primary btn-sm" onclick="previousQuestion()">
                            <i class="fas fa-chevron-left"></i> Prev
                        </button>
                        <button type="button" id="next-btn" class="btn btn-outline-primary btn-sm" onclick="nextQuestion()">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="goToQuestion({{ $questions->count() }})">
                            Last <i class="fas fa-fast-forward"></i>
                        </button>
                    </div>

                    <!-- Field Topics Info -->
                    @if($category->settings && isset($category->settings['topics']))
                    <div class="alert alert-info">
                        <h6><i class="fas fa-list"></i> Materi Yang Diujikan</h6>
                        <small>
                            @foreach($category->settings['topics'] as $topic)
                                {{ ucwords(str_replace('_', ' ', $topic)) }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </small>
                    </div>
                    @endif

                    <!-- Submit Section -->
                    <div class="submit-section">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Important:</strong> Pastikan semua soal sudah dijawab. Target: {{ $category->settings['passing_score'] ?? 70 }}% untuk lulus.
                        </div>
                        <button type="button" id="submit-btn" class="btn btn-success w-100 btn-lg">
                            <i class="fas fa-check me-2"></i>Submit Field Test
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Confirmation Modal -->
    <div class="modal fade" id="submitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit Field Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Test Summary</h6>
                        <p class="mb-1">Total Questions: <strong>{{ $questions->count() }}</strong></p>
                        <p class="mb-1">Answered: <strong id="modal-answered-count">0</strong></p>
                        <p class="mb-1">Current Score: <strong id="modal-current-score">0</strong></p>
                        <p class="mb-0">Unanswered: <strong id="modal-unanswered-count">{{ $questions->count() }}</strong></p>
                    </div>
                    <p>Are you sure you want to submit your field test?</p>
                    <p class="text-warning"><small><i class="fas fa-exclamation-triangle"></i> You cannot change your answers after submission.</small></p>
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
        let remainingSeconds = {{ $remainingSeconds }};
        const sessionId = {{ $session->id }};
        let answeredQuestions = new Set();
        let currentScore = 0;

        // Initialize answered questions from existing data
        document.addEventListener('DOMContentLoaded', function() {
            // Cek jawaban yang sudah ada dari server
            document.querySelectorAll('input[type="radio"]:checked, textarea').forEach(input => {
                const questionId = input.name.replace('question_', '');
                if (input.value.trim() !== '') {
                    answeredQuestions.add(questionId);
                    updateQuestionNavStatus(questionId, true);
                }
            });
            
            // Initialize selected visual state
            document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
                const optionItem = radio.closest('.option-item');
                if (optionItem) {
                    optionItem.classList.add('selected');
                }
            });
            
            updateProgressInfo();
            updateCurrentQuestionDisplay();
        });

        // Timer
        function updateTimer() {
            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;
            const timerElement = document.getElementById('timer');
            const mainTimer = document.getElementById('main-timer');
            
            timerElement.textContent = 
                minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
            
            if (remainingSeconds <= 300) {
                mainTimer.classList.add('warning');
            }
            
            if (remainingSeconds <= 0) {
                alert('Waktu habis! Test akan disubmit otomatis.');
                submitTest();
                return;
            }
            
            remainingSeconds--;
        }

        setInterval(updateTimer, 1000);

        // Navigation functions
        function goToQuestion(questionNum) {
            if (questionNum < 1 || questionNum > totalQuestions) return;
            
            // Hide all questions
            document.querySelectorAll('.question-card').forEach(card => {
                card.style.display = 'none';
            });
            
            // Show target question
            document.querySelector(`[data-question="${questionNum}"]`).style.display = 'block';
            
            // Update navigation buttons
            document.querySelectorAll('.question-nav-btn').forEach(btn => {
                btn.classList.remove('current');
            });
            
            // Find the correct nav button by data-question attribute
            const targetNavBtn = document.querySelector(`.question-nav-btn[data-question="${questionNum}"]`);
            if (targetNavBtn) {
                targetNavBtn.classList.add('current');
            }
            
            currentQuestion = questionNum;
            updateCurrentQuestionDisplay();
        }

        function nextQuestion() {
            if (currentQuestion < totalQuestions) {
                goToQuestion(currentQuestion + 1);
            }
        }

        function previousQuestion() {
            if (currentQuestion > 1) {
                goToQuestion(currentQuestion - 1);
            }
        }

        function updateCurrentQuestionDisplay() {
            document.getElementById('current-q').textContent = currentQuestion;
        }

        function updateQuestionNavStatus(questionId, answered) {
            const navBtn = document.querySelector(`[data-question-id="${questionId}"]`);
            if (navBtn) {
                if (answered) {
                    navBtn.classList.add('answered');
                } else {
                    navBtn.classList.remove('answered');
                }
            }
        }

        function updateProgressInfo() {
            const answeredCount = answeredQuestions.size;
            const percentage = Math.round((answeredCount / totalQuestions) * 100);
            
            document.getElementById('answered-count').textContent = answeredCount;
            document.getElementById('progress-percentage').textContent = percentage + '%';
            document.getElementById('progress-bar').style.width = percentage + '%';
            document.getElementById('score-display').textContent = currentScore;
            document.getElementById('current-score').textContent = currentScore;
            
            // Update modal counts
            document.getElementById('modal-answered-count').textContent = answeredCount;
            document.getElementById('modal-unanswered-count').textContent = totalQuestions - answeredCount;
            document.getElementById('modal-current-score').textContent = currentScore;
            
            console.log('Field test progress updated:', {
                answeredCount,
                totalQuestions,
                percentage,
                currentScore,
                answeredQuestions: Array.from(answeredQuestions)
            });
        }

        // Auto-save answers
        function saveAnswer(questionId, answer) {
            console.log('Saving field test answer:', { questionId, answer, sessionId });
            
            fetch('{{ route("psychotest.test.save-answer") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    session_id: sessionId,
                    question_id: questionId,
                    answer: answer
                })
            }).then(response => {
                if (response.ok) {
                    console.log('Field test answer saved successfully');
                    return response.json();
                } else {
                    console.error('Failed to save field test answer');
                    throw new Error('Failed to save answer');
                }
            }).then(data => {
                console.log('Save response:', data);
                if (data.data && data.data.points_earned !== undefined) {
                    // Update score if points are returned
                    updateScore();
                }
            }).catch(error => {
                console.error('Error saving answer:', error);
            });
        }

        // Update score calculation
        function updateScore() {
            let totalScore = 0;
            document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
                const questionCard = radio.closest('.question-card');
                const pointsElement = questionCard.querySelector('.points-indicator');
                if (pointsElement) {
                    const points = parseInt(pointsElement.textContent.match(/\d+/)[0]);
                    totalScore += points;
                }
            });
            
            // Add points from essay questions (assuming full points if answered)
            document.querySelectorAll('textarea').forEach(textarea => {
                if (textarea.value.trim() !== '') {
                    const questionCard = textarea.closest('.question-card');
                    const pointsElement = questionCard.querySelector('.points-indicator');
                    if (pointsElement) {
                        const points = parseInt(pointsElement.textContent.match(/\d+/)[0]);
                        totalScore += points;
                    }
                }
            });
            
            currentScore = totalScore;
            updateProgressInfo();
        }

        // Handle answer selection - Field specific
        document.addEventListener('change', (e) => {
            if (e.target.type === 'radio' || e.target.tagName === 'TEXTAREA') {
                const questionId = e.target.name.replace('question_', '');
                const answered = e.target.value.trim() !== '';
                
                console.log('Field test answer changed:', { 
                    questionId, 
                    answer: e.target.value, 
                    answered,
                    elementName: e.target.name 
                });
                
                if (answered) {
                    answeredQuestions.add(questionId);
                    updateQuestionNavStatus(questionId, true);
                } else {
                    answeredQuestions.delete(questionId);
                    updateQuestionNavStatus(questionId, false);
                }
                
                updateScore();
                updateProgressInfo();
                saveAnswer(questionId, e.target.value);
            }
        });

        // Handle option clicks - Field specific
        document.addEventListener('click', (e) => {
            const optionItem = e.target.closest('.option-item');
            if (optionItem) {
                const radio = optionItem.querySelector('input[type="radio"]');
                if (radio && !radio.checked) {
                    // Clear other selections in same group
                    const groupName = radio.getAttribute('name');
                    document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => {
                        const parentOption = r.closest('.option-item');
                        if (parentOption) {
                            parentOption.classList.remove('selected');
                        }
                    });
                    
                    // Select current option
                    radio.checked = true;
                    optionItem.classList.add('selected');
                    
                    // Trigger change event
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    console.log('Field test option clicked and selected:', {
                        questionId: radio.name.replace('question_', ''),
                        value: radio.value
                    });
                }
            }
        });

        // Submit test
        document.getElementById('submit-btn').addEventListener('click', () => {
            console.log('Submit field test button clicked. Current answers:', Array.from(answeredQuestions));
            new bootstrap.Modal(document.getElementById('submitModal')).show();
        });

        document.getElementById('confirm-submit').addEventListener('click', () => {
            submitTest();
        });

        function submitTest() {
            console.log('Submitting field test with answered questions:', Array.from(answeredQuestions));
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("psychotest.test.submit") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfToken);
            
            const sessionInput = document.createElement('input');
            sessionInput.type = 'hidden';
            sessionInput.name = 'session_id';
            sessionInput.value = sessionId;
            form.appendChild(sessionInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        // Prevent accidental page refresh
        window.addEventListener('beforeunload', (e) => {
            if (remainingSeconds > 0) {
                e.preventDefault();
                e.returnValue = 'Your field test progress will be lost if you leave this page.';
            }
        });

        // Auto-save textarea content
        document.addEventListener('input', (e) => {
            if (e.target.tagName === 'TEXTAREA') {
                clearTimeout(window.textareaTimer);
                window.textareaTimer = setTimeout(() => {
                    e.target.dispatchEvent(new Event('change', { bubbles: true }));
                }, 2000);
            }
        });

        // Keyboard shortcuts for field test
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                e.preventDefault();
                nextQuestion();
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                e.preventDefault();
                previousQuestion();
            } else if (e.key >= '1' && e.key <= '5') {
                e.preventDefault();
                const optionIndex = parseInt(e.key) - 1;
                const currentCard = document.querySelector(`[data-question="${currentQuestion}"]`);
                const options = currentCard.querySelectorAll('.option-item');
                if (options[optionIndex]) {
                    options[optionIndex].click();
                }
            }
        });

        // Highlight difficult questions
        document.addEventListener('DOMContentLoaded', function() {
            const hardQuestions = document.querySelectorAll('.difficulty-hard');
            hardQuestions.forEach(badge => {
                badge.style.animation = 'pulse 2s infinite';
            });
        });

        // Progress animation
        function animateProgress() {
            const progressBar = document.getElementById('progress-bar');
            progressBar.style.transition = 'width 0.5s ease-in-out';
        }

        // Call animate progress on updates
        const originalUpdateProgressInfo = updateProgressInfo;
        updateProgressInfo = function() {
            originalUpdateProgressInfo();
            animateProgress();
        };

        // Debug function for field test
        window.debugFieldTest = function() {
            console.log('=== DEBUG FIELD TEST ===');
            console.log('Answered Questions Set:', Array.from(answeredQuestions));
            console.log('Current Score:', currentScore);
            console.log('All checked radios:');
            document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
                console.log('- Question ID:', radio.name.replace('question_', ''), 'Value:', radio.value);
            });
            console.log('All textareas with content:');
            document.querySelectorAll('textarea').forEach(textarea => {
                if (textarea.value.trim()) {
                    console.log('- Question ID:', textarea.name.replace('question_', ''), 'Value:', textarea.value);
                }
            });
            console.log('========================');
        };
    </script>
</body>
</html>