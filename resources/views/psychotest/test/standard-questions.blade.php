<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $category->name }} - Psychotest (Secure Mode)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Disable text selection globally */
        * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
            -webkit-tap-highlight-color: transparent;
        }

        /* Re-enable selection for input fields only */
        input, textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }

        /* Disable right-click context menu */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            position: relative;
        }

        /* Anti-screenshot overlay (subtle watermark) */
        body::before {
            content: "{{ $session->schedule->candidates->name }} - {{ date('Y-m-d H:i:s') }}";
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 48px;
            color: rgba(255, 255, 255, 0.05);
            pointer-events: none;
            z-index: -1;
            white-space: nowrap;
            font-weight: bold;
        }

        /* Fullscreen mode styles */
        .fullscreen-warning {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(220, 53, 69, 0.95);
            color: white;
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            flex-direction: column;
            text-align: center;
        }

        .fullscreen-warning.show {
            display: flex;
        }

        /* Focus monitoring indicator */
        .focus-indicator {
            position: fixed;
            top: 10px;
            left: 10px;
            background: #28a745;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 9999;
            transition: all 0.3s ease;
        }

        .focus-indicator.lost {
            background: #dc3545;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }

        /* Security warning banner */
        .security-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 10px;
            text-align: center;
            z-index: 9998;
            font-weight: bold;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        /* Adjust main content to accommodate security banner */
        .test-header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            color: #333;
            padding: 1rem 0;
            position: sticky;
            top: 0; /* Reset to normal position */
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .test-layout {
            min-height: calc(100vh - 80px); /* Reset to normal height */
            margin: 0;
            padding: 0;
        }

        .questions-column {
            background: white;
            min-height: calc(100vh - 80px); /* Reset to normal height */
            padding: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .navigation-column {
            background: rgba(255,255,255,0.95);
            min-height: calc(100vh - 80px); /* Reset to normal height */
            padding: 0;
            border-left: 1px solid #e0e0e0;
        }

        .question-content {
            padding: 40px;
            height: calc(100vh - 80px); /* Reset to normal height */
            overflow-y: auto;
        }

        .navigation-content {
            padding: 30px 20px;
            height: calc(100vh - 80px); /* Reset to normal height */
            overflow-y: auto;
        }

        /* Disable print styles */
        @media print {
            body {
                display: none !important;
            }
        }

        /* Warning modal styles */
        .warning-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10001;
        }

        .warning-content {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            max-width: 500px;
            margin: 20px;
        }

        .violation-counter {
            position: fixed;
            top: 60px;
            right: 10px;
            background: #dc3545;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 9999;
            font-weight: bold;
        }

        /* Existing styles... */
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
        .option-item {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.2rem;
            margin-bottom: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
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
        
        /* EPPS Specific Styles */
        .epps-container {
            background: linear-gradient(135deg, #f8fafc 0%, #e7f3ff 100%);
            border: 2px solid #0ea5e9;
            border-radius: 20px;
            padding: 30px;
            margin: 20px 0;
        }
        
        .epps-instruction {
            text-align: center;
            background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #0284c7;
        }
        
        .epps-choices {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }
        
        .epps-choice {
            flex: 1;
            padding: 25px;
            border: 3px solid #e2e8f0;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
        }
        
        .epps-choice:hover {
            border-color: #667eea;
            background: #f0f4ff;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102,126,234,0.2);
        }
        
        .epps-choice.selected {
            border-color: #22c55e;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            box-shadow: 0 5px 20px rgba(34,197,94,0.3);
        }
        
        .epps-label {
            position: absolute;
            top: -15px;
            left: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .epps-choice.selected .epps-label {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        /* Auto-Advance and other existing styles remain the same... */
        .auto-advance-indicator {
            position: fixed;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 50px;
            z-index: 9999;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: none;
            min-width: 180px;
            text-align: center;
            font-weight: 600;
        }
        
        .auto-advance-indicator.show {
            opacity: 1;
            transform: translateY(-50%) translateX(-10px);
        }
        
        .auto-advance-progress {
            width: 100%;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }
        
        .auto-advance-progress-bar {
            height: 100%;
            background: white;
            border-radius: 2px;
            transition: width linear;
        }
        
        .answer-selected-feedback {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(34, 197, 94, 0.95);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            z-index: 1000;
            opacity: 0;
            animation: answerFeedback 0.6s ease-in-out;
        }
        
        @keyframes answerFeedback {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
            50% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1); }
        }
        
        .question-transition {
            transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        
        .question-transition.exiting {
            opacity: 0;
            transform: translateX(-30px);
        }
        
        .question-transition.entering {
            opacity: 0;
            transform: translateX(30px);
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
        .progress-info {
            background: rgba(102,126,234,0.1);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .question-image {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .rating-option {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            margin: 0 5px;
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
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        @media (max-width: 768px) {
            .epps-choices {
                flex-direction: column;
                gap: 15px;
            }
            .epps-choice {
                min-height: 100px;
                padding: 20px;
            }
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
    <!-- Hidden indicators - removed from view -->
    <!-- Security monitoring active but invisible -->

    <!-- Fullscreen Warning -->
    <div class="fullscreen-warning" id="fullscreenWarning">
        <div>
            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
            <h2>Please Return to Fullscreen Mode</h2>
            <p>This test must be taken in fullscreen mode for security reasons.</p>
            <button class="btn btn-light btn-lg" onclick="enterFullscreen()">
                <i class="fas fa-expand me-2"></i>Enter Fullscreen
            </button>
        </div>
    </div>

    <!-- Warning Overlay -->
    <div class="warning-overlay" id="warningOverlay">
        <div class="warning-content">
            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
            <h3 id="warningTitle">Security Violation Detected</h3>
            <p id="warningMessage">Suspicious activity has been detected.</p>
            <div class="mt-4">
                <button class="btn btn-warning me-2" onclick="acknowledgeWarning()">
                    <i class="fas fa-check me-2"></i>I Understand
                </button>
                <button class="btn btn-danger" onclick="submitTest()">
                    <i class="fas fa-stop me-2"></i>End Test
                </button>
            </div>
        </div>
    </div>

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
                            <small class="text-muted">{{ isset($isEPPS) && $isEPPS ? 'Pairs' : 'Questions' }} Answered</small>
                        </div>
                        <div class="timer" id="main-timer">
                            <i class="fas fa-clock"></i>
                            <span id="timer">{{ floor($remainingSeconds / 60) }}:{{ sprintf('%02d', $remainingSeconds % 60) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <small class="text-muted">{{ isset($isEPPS) && $isEPPS ? 'Pair' : 'Question' }} <span id="current-q">1</span> of {{ $questions->count() }}</small>
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
                            
                            @if(isset($isEPPS) && $isEPPS)
                            <!-- EPPS Specific Layout -->
                            <div class="epps-container">
                                <div class="d-flex align-items-start mb-4">
                                    <div class="question-number-badge me-4">{{ $index + 1 }}</div>
                                    <div class="flex-grow-1">
                                        <div class="epps-instruction">
                                            <h4 class="mb-3">
                                                <i class="fas fa-balance-scale text-primary me-2"></i>
                                                Pilih pernyataan yang PALING menggambarkan diri Anda
                                            </h4>
                                            <p class="mb-0 text-muted">Dari kedua pilihan di bawah ini, pilih satu yang paling sesuai dengan kepribadian Anda</p>
                                        </div>
                                        
                                        @if($question->options && count($question->options) >= 2)
                                        <div class="epps-choices">
                                            <div class="epps-choice" data-value="{{ $question->options[0] }}">
                                                <div class="epps-label">A</div>
                                                <input type="radio" name="question_{{ $question->id }}" value="{{ $question->options[0] }}" 
                                                       id="q{{ $question->id }}_a" class="d-none"
                                                       {{ ($answers[$question->id]->answer ?? '') == $question->options[0] ? 'checked' : '' }}>
                                                <div>{{ $question->options[0] }}</div>
                                            </div>
                                            
                                            <div class="epps-choice" data-value="{{ $question->options[1] }}">
                                                <div class="epps-label">B</div>
                                                <input type="radio" name="question_{{ $question->id }}" value="{{ $question->options[1] }}" 
                                                       id="q{{ $question->id }}_b" class="d-none"
                                                       {{ ($answers[$question->id]->answer ?? '') == $question->options[1] ? 'checked' : '' }}>
                                                <div>{{ $question->options[1] }}</div>
                                            </div>
                                        </div>
                                        @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            This question pair is not properly configured. Please contact administrator.
                                        </div>
                                        @endif
                                        
                                        <div class="text-center mt-3">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Pilih salah satu pilihan yang PALING sesuai dengan kepribadian Anda
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @else
                            <!-- Standard Question Layout -->
                            <div class="d-flex align-items-start mb-4">
                                <div class="question-number-badge me-4">{{ $index + 1 }}</div>
                                <div class="flex-grow-1">
                                    <h4 class="mb-3">{{ $question->title }}</h4>
                                    <p class="mb-4 text-muted" style="font-size: 16px; line-height: 1.6;">{{ $question->question }}</p>
                                    
                                    @if($question->image)
                                        <div class="text-center mb-4">
                                            <img src="{{ asset('storage/public/uploads/psychotest/images/' . $question->image) }}" alt="Question Image" class="question-image">
                                        </div>
                                    @endif
                                    
                                    @if($question->type == 'multiple_choice' || $question->type == 'image_choice')
                                        @foreach($question->options as $optionIndex => $option)
                                        <div class="option-item" data-value="{{ $option }}">
                                            <input type="radio" name="question_{{ $question->id }}" value="{{ $option }}" 
                                                   id="q{{ $question->id }}_{{ $optionIndex }}" class="me-3"
                                                   {{ ($answers[$question->id]->answer ?? '') == $option ? 'checked' : '' }}>
                                            <label for="q{{ $question->id }}_{{ $optionIndex }}" class="mb-0 w-100" style="font-size: 16px;">{{ $option }}</label>
                                        </div>
                                        @endforeach
                                    
                                    @elseif($question->type == 'true_false')
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="option-item" data-value="True">
                                                    <input type="radio" name="question_{{ $question->id }}" value="True" 
                                                           id="q{{ $question->id }}_true" class="me-3"
                                                           {{ ($answers[$question->id]->answer ?? '') == 'True' ? 'checked' : '' }}>
                                                    <label for="q{{ $question->id }}_true" class="mb-0 w-100">
                                                        <i class="fas fa-check-circle text-success me-2"></i>True
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="option-item" data-value="False">
                                                    <input type="radio" name="question_{{ $question->id }}" value="False" 
                                                           id="q{{ $question->id }}_false" class="me-3"
                                                           {{ ($answers[$question->id]->answer ?? '') == 'False' ? 'checked' : '' }}>
                                                    <label for="q{{ $question->id }}_false" class="mb-0 w-100">
                                                        <i class="fas fa-times-circle text-danger me-2"></i>False
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    
                                    @elseif($question->type == 'rating_scale')
                                        <div class="d-flex justify-content-center align-items-center mb-3">
                                            <span class="me-3">1 (Lowest)</span>
                                            @foreach($question->options as $rating)
                                            <div class="rating-option option-item" data-value="{{ $rating }}">
                                                <input type="radio" name="question_{{ $question->id }}" value="{{ $rating }}" 
                                                       id="q{{ $question->id }}_{{ $rating }}" class="d-none"
                                                       {{ ($answers[$question->id]->answer ?? '') == $rating ? 'checked' : '' }}>
                                                <label for="q{{ $question->id }}_{{ $rating }}" class="mb-0 w-100 h-100 d-flex align-items-center justify-content-center">{{ $rating }}</label>
                                            </div>
                                            @endforeach
                                            <span class="ms-3">{{ max($question->options) }} (Highest)</span>
                                        </div>
                                    
                                    @elseif($question->type == 'essay')
                                        <div class="mb-3">
                                            <textarea name="question_{{ $question->id }}" class="form-control" rows="8" 
                                                      placeholder="Type your answer here..." 
                                                      style="border-radius: 10px; border: 2px solid #e9ecef; padding: 20px; font-size: 16px; line-height: 1.6;">{{ $answers[$question->id]->answer ?? '' }}</textarea>
                                        </div>
                                    @endif

                                    @if($question->time_limit_seconds)
                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-clock"></i> This question has a time limit of {{ $question->time_limit_seconds }} seconds
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </form>
                </div>
            </div>

            <!-- Navigation Column -->
            <div class="col-lg-4 navigation-column">
                <div class="navigation-content">
                    <h5 class="mb-3">{{ isset($isEPPS) && $isEPPS ? 'EPPS' : 'Question' }} Navigation</h5>
                    
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
                    </div>

                    <!-- Progress Info -->
                    <div class="progress-info">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Progress:</span>
                            <span><strong id="progress-percentage">0%</strong></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" id="progress-bar" style="width: 0%"></div>
                        </div>
                        @if(isset($isEPPS) && $isEPPS)
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-users me-1"></i>
                                Mengukur 15 dimensi kepribadian kerja
                            </small>
                        </div>
                        @endif
                    </div>

                    <!-- Auto-Advance Toggle (akan ditambahkan oleh JavaScript) -->

                    <!-- Question Navigation Grid -->
                    <div class="question-nav-grid">
                        @foreach($questions as $index => $question)
                        <div class="question-nav-btn {{ $index == 0 ? 'current' : '' }}" 
                             data-question="{{ $index + 1 }}" 
                             data-question-id="{{ $question->id }}"
                             onclick="goToQuestion({{ $index + 1 }})">
                            {{ $index + 1 }}
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

                    @if(isset($isEPPS) && $isEPPS)
                    <!-- EPPS Dimensions Info -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-brain"></i> 15 Dimensi EPPS</h6>
                        <small>Achievement, Deference, Order, Exhibition, Autonomy, Affiliation, Intraception, Succorance, Dominance, Abasement, Nurturance, Change, Endurance, Heterosexuality, Aggression</small>
                    </div>
                    @endif

                    <!-- Submit Section -->
                    <div class="submit-section">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Important:</strong> Make sure you have answered all {{ isset($isEPPS) && $isEPPS ? 'pairs' : 'questions' }} before submitting.
                        </div>
                        <button type="button" id="submit-btn" class="btn btn-success w-100 btn-lg">
                            <i class="fas fa-check me-2"></i>Submit {{ isset($isEPPS) && $isEPPS ? 'EPPS' : '' }} Test
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
                    <h5 class="modal-title">Submit {{ isset($isEPPS) && $isEPPS ? 'EPPS' : '' }} Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Test Summary</h6>
                        <p class="mb-1">Total {{ isset($isEPPS) && $isEPPS ? 'Pairs' : 'Questions' }}: <strong>{{ $questions->count() }}</strong></p>
                        <p class="mb-1">Answered: <strong id="modal-answered-count">0</strong></p>
                        <p class="mb-0">Unanswered: <strong id="modal-unanswered-count">{{ $questions->count() }}</strong></p>
                    </div>
                    <div class="alert alert-warning" id="securitySummary" style="display: none;">
                        <h6><i class="fas fa-shield-alt"></i> Security Report</h6>
                        <p class="mb-1">Security monitoring was active during this test.</p>
                        <p class="mb-0">Some activities may have been detected and recorded.</p>
                    </div>
                    <p>Are you sure you want to submit your {{ isset($isEPPS) && $isEPPS ? 'EPPS' : '' }} test?</p>
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
    // ==========================================
    // ENHANCED ANTI-CHEATING SECURITY SYSTEM
    // ==========================================

    let currentQuestion = 1;
    const totalQuestions = {{ $questions->count() }};
    let remainingSeconds = {{ $remainingSeconds }};
    const sessionId = {{ $session->id }};
    let answeredQuestions = new Set();
    const isEPPS = {{ isset($isEPPS) && $isEPPS ? 'true' : 'false' }};

    // Security monitoring variables
    let securityViolations = {
        tabSwitches: 0,
        focusLost: 0,
        rightClicks: 0,
        keyboardShortcuts: 0,
        copyAttempts: 0,
        printAttempts: 0,
        devToolsAttempts: 0,
        fullscreenExits: 0
    };

    let isTestActive = true;
    let lastActiveTime = Date.now();
    let securityCheckInterval;
    let isFullscreenRequired = true;
    let maxViolationsAllowed = 5;

    // Initialize security system when page loads
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔒 Initializing Enhanced Anti-Cheating Security System');
        
        initializeSecurity();
        initializeOriginalFunctionality();
        
        // Start security monitoring
        startSecurityMonitoring();
        
        console.log('✅ Security system initialized successfully');
    });

    // ==========================================
    // CORE SECURITY FUNCTIONS
    // ==========================================

    function initializeSecurity() {
        // Force fullscreen mode
        if (isFullscreenRequired) {
            enterFullscreen();
        }

        // Disable right-click context menu
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            recordViolation('rightClicks', 'Right-click context menu blocked');
            showDiscreteViolationWarning('rightClicks', 'Right-click is disabled during the test');
            return false;
        });

        // Disable text selection and drag
        document.addEventListener('selectstart', function(e) {
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                return false;
            }
        });

        // Disable drag and drop
        document.addEventListener('dragstart', function(e) {
            e.preventDefault();
            return false;
        });

        // Monitor focus changes (simplified - no UI indicators)
        window.addEventListener('focus', function() {
            // Focus regained - just track silently
        });

        window.addEventListener('blur', function() {
            if (isTestActive) {
                recordViolation('focusLost', 'Window lost focus');
                
                // Only show warning after multiple focus losses
                if (securityViolations.focusLost >= 3) {
                    showDiscreteViolationWarning('focusLost', 'Multiple focus loss detected');
                }
            }
        });

        // Monitor tab visibility changes (simplified)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden && isTestActive) {
                recordViolation('tabSwitches', 'Tab switched or window minimized');
                
                // Show warning only after multiple tab switches
                if (securityViolations.tabSwitches >= 2) {
                    showDiscreteViolationWarning('tabSwitches', 'Multiple tab switches detected');
                }
            }
        });

        // Monitor fullscreen changes (simplified)
        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement && isTestActive && isFullscreenRequired) {
                recordViolation('fullscreenExits', 'Exited fullscreen mode');
                document.getElementById('fullscreenWarning').classList.add('show');
                
                setTimeout(() => {
                    if (!document.fullscreenElement) {
                        showDiscreteViolationWarning('fullscreenExits', 'Please return to fullscreen mode');
                    }
                }, 2000);
            } else if (document.fullscreenElement) {
                document.getElementById('fullscreenWarning').classList.remove('show');
            }
        });

        // Monitor dangerous keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            const forbiddenKeys = [
                // Developer tools
                { key: 'F12' },
                { key: 'I', ctrl: true, shift: true }, // Ctrl+Shift+I
                { key: 'J', ctrl: true, shift: true }, // Ctrl+Shift+J
                { key: 'C', ctrl: true, shift: true }, // Ctrl+Shift+C
                { key: 'U', ctrl: true }, // Ctrl+U (View Source)
                
                // Copy/Paste
                { key: 'C', ctrl: true }, // Ctrl+C
                { key: 'A', ctrl: true }, // Ctrl+A
                { key: 'V', ctrl: true }, // Ctrl+V
                { key: 'X', ctrl: true }, // Ctrl+X
                
                // Print
                { key: 'P', ctrl: true }, // Ctrl+P
                
                // Refresh
                { key: 'F5' },
                { key: 'R', ctrl: true }, // Ctrl+R
                
                // New tab/window
                { key: 'T', ctrl: true }, // Ctrl+T
                { key: 'N', ctrl: true }, // Ctrl+N
                { key: 'W', ctrl: true }, // Ctrl+W
                
                // Alt+Tab
                { key: 'Tab', alt: true }
            ];

            for (let forbidden of forbiddenKeys) {
                if (e.key === forbidden.key && 
                    (!forbidden.ctrl || e.ctrlKey) && 
                    (!forbidden.shift || e.shiftKey) && 
                    (!forbidden.alt || e.altKey)) {
                    
                    e.preventDefault();
                    e.stopPropagation();
                    
                    let violationType = 'keyboardShortcuts';
                    let message = `Blocked keyboard shortcut: ${e.key}`;
                    
                    if (forbidden.key === 'C' && e.ctrlKey) {
                        violationType = 'copyAttempts';
                        message = 'Copy attempt blocked';
                    } else if (forbidden.key === 'P' && e.ctrlKey) {
                        violationType = 'printAttempts';
                        message = 'Print attempt blocked';
                    } else if (['F12', 'I', 'J', 'C'].includes(forbidden.key) && (e.ctrlKey || e.shiftKey)) {
                        violationType = 'devToolsAttempts';
                        message = 'Developer tools access blocked';
                    }
                    
                    recordViolation(violationType, message);
                    showDiscreteViolationWarning(violationType, 'This action is not allowed during the test');
                    return false;
                }
            }
        });

        // Disable print media
        const style = document.createElement('style');
        style.textContent = '@media print { body { display: none !important; } }';
        document.head.appendChild(style);

        // Monitor for developer tools
        let devtools = {
            open: false,
            orientation: null
        };

        const threshold = 160;
        
        setInterval(() => {
            if (isTestActive && (window.outerHeight - window.innerHeight > threshold || 
                window.outerWidth - window.innerWidth > threshold)) {
                if (!devtools.open) {
                    devtools.open = true;
                    recordViolation('devToolsAttempts', 'Developer tools detected');
                    showDiscreteViolationWarning('devToolsAttempts', 'Developer tools detected');
                }
            } else {
                devtools.open = false;
            }
        }, 500);

        // Disable image dragging
        document.addEventListener('dragstart', function(e) {
            if (e.target.tagName === 'IMG') {
                e.preventDefault();
                return false;
            }
        });
    }

    function enterFullscreen() {
        const element = document.documentElement;
        if (element.requestFullscreen) {
            element.requestFullscreen().catch(err => {
                console.warn('Fullscreen request failed:', err);
            });
        } else if (element.webkitRequestFullscreen) {
            element.webkitRequestFullscreen();
        } else if (element.msRequestFullscreen) {
            element.msRequestFullscreen();
        }
    }

    function recordViolation(type, message) {
        if (!isTestActive) return;
        
        securityViolations[type]++;
        const totalViolations = Object.values(securityViolations).reduce((a, b) => a + b, 0);
        
        console.warn('🚨 Security Violation:', { type, message, count: securityViolations[type], total: totalViolations });
    
        
        // Show discrete warning based on violation type
        showDiscreteViolationWarning(type, message);
        
        // Check if max violations reached
        if (totalViolations >= maxViolationsAllowed) {
            showCriticalViolationWarning();
        }
    }

    function showDiscreteViolationWarning(type, message) {
        let title, description;
        
        switch(type) {
            case 'tabSwitches':
                title = 'Focus Required';
                description = 'Please stay focused on the test. Switching tabs or windows is not recommended.';
                break;
            case 'rightClicks':
                title = 'Action Not Allowed';
                description = 'Right-click menu is disabled during the test for security purposes.';
                break;
            case 'copyAttempts':
                title = 'Copy Disabled';
                description = 'Copying content is not allowed during the test.';
                break;
            case 'devToolsAttempts':
                title = 'Developer Tools Blocked';
                description = 'Browser developer tools are not permitted during the test.';
                break;
            case 'printAttempts':
                title = 'Print Disabled';
                description = 'Printing is not allowed during the test.';
                break;
            case 'fullscreenExits':
                title = 'Return to Fullscreen';
                description = 'Please return to fullscreen mode to continue the test.';
                break;
            default:
                title = 'Please Focus';
                description = 'Please maintain focus on the test to ensure proper completion.';
        }
        
        showQuickWarning(title, description);
    }

    function showSecurityWarning(title, message) {
        document.getElementById('warningTitle').textContent = title;
        document.getElementById('warningMessage').textContent = message;
        document.getElementById('warningOverlay').style.display = 'flex';
    }

    function showQuickWarning(title, message) {
        // Create a temporary warning that disappears automatically
        const warning = document.createElement('div');
        warning.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #dc3545;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 10002;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            opacity: 0;
            transform: translateX(100px);
            transition: all 0.3s ease;
            max-width: 300px;
        `;
        warning.innerHTML = `<strong>${title}</strong><br><small>${message}</small>`;
        
        document.body.appendChild(warning);
        
        // Animate in
        setTimeout(() => {
            warning.style.opacity = '1';
            warning.style.transform = 'translateX(0)';
        }, 100);
        
        // Animate out and remove
        setTimeout(() => {
            warning.style.opacity = '0';
            warning.style.transform = 'translateX(100px)';
            setTimeout(() => {
                if (warning.parentNode) {
                    warning.parentNode.removeChild(warning);
                }
            }, 300);
        }, 3000);
    }

    function showCriticalViolationWarning() {
        showSecurityWarning(
            'Critical Security Violations', 
            `You have exceeded the maximum allowed violations (${maxViolationsAllowed}). The test may be terminated automatically. Please follow the test guidelines strictly.`
        );
        
        // Auto-submit after critical violations
        setTimeout(() => {
            if (Object.values(securityViolations).reduce((a, b) => a + b, 0) >= maxViolationsAllowed + 2) {
                alert('Test terminated due to excessive security violations.');
                submitTest();
            }
        }, 10000);
    }

    function acknowledgeWarning() {
        document.getElementById('warningOverlay').style.display = 'none';
    }

    function startSecurityMonitoring() {
        securityCheckInterval = setInterval(() => {
            if (isTestActive) {
                // Check if user is still active
                const currentTime = Date.now();
                if (currentTime - lastActiveTime > 300000) { // 5 minutes inactive
                    recordViolation('focusLost', 'Extended inactivity detected');
                }
                
                // Periodic security checks
                if (!document.hasFocus()) {
                    recordViolation('focusLost', 'Periodic check: window not focused');
                }
                
                if (document.hidden) {
                    recordViolation('tabSwitches', 'Periodic check: tab not visible');
                }
            }
        }, 30000); // Check every 30 seconds
    }

    // Track user activity
    document.addEventListener('mousemove', () => lastActiveTime = Date.now());
    document.addEventListener('keypress', () => lastActiveTime = Date.now());
    document.addEventListener('click', () => lastActiveTime = Date.now());

    // ==========================================
    // ORIGINAL AUTO-ADVANCE SYSTEM (PRESERVED)
    // ==========================================

    // Configuration for auto-advance behavior
    const AUTO_ADVANCE_CONFIG = {
        enabled: true,
        delay: 800,
        enableForEPPS: true,
        enableForStandard: true,
        enableForFieldTest: true,
        skipLastQuestion: true,
        showAdvanceIndicator: true,
        allowManualOverride: true
    };

    // State management for auto-advance
    let autoAdvanceEnabled = AUTO_ADVANCE_CONFIG.enabled;
    let advanceTimer = null;
    let isAdvancing = false;

    // Initialize original functionality
    function initializeOriginalFunctionality() {
        console.log('Initializing original functionality...');
        console.log('Test Type:', isEPPS ? 'EPPS' : 'Standard');
        
        // Clear any existing answered status first
        answeredQuestions.clear();
        document.querySelectorAll('.question-nav-btn').forEach(btn => {
            btn.classList.remove('answered');
        });
        
        // Check existing answers from server
        document.querySelectorAll('input[type="radio"]:checked, textarea').forEach(input => {
            const questionId = input.name.replace('question_', '');
            if (input.value.trim() !== '') {
                console.log('Found existing answer for question:', questionId, 'value:', input.value);
                answeredQuestions.add(questionId);
                updateQuestionNavStatus(questionId, true);
            }
        });
        
        // Initialize selected visual state for EPPS
        if (isEPPS) {
            document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
                const eppsChoice = radio.closest('.epps-choice');
                if (eppsChoice) {
                    eppsChoice.classList.add('selected');
                    console.log('EPPS choice marked as selected');
                }
            });
        } else {
            // Initialize selected visual state for standard questions
            document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
                const optionItem = radio.closest('.option-item');
                if (optionItem) {
                    optionItem.classList.add('selected');
                    console.log('Standard option marked as selected');
                }
            });
        }
        
        updateProgressInfo();
        updateCurrentQuestionDisplay();
        console.log('Initial answered questions:', Array.from(answeredQuestions));
        
        // Initialize auto-advance system
        setTimeout(() => {
            initializeAutoAdvance();
            setupEnhancedClickHandlers();
        }, 500);
    }

    // Initialize auto-advance system
    function initializeAutoAdvance() {
        console.log('🚀 Initializing Auto-Advance System');
        
        // Add auto-advance toggle button to navigation
        addAutoAdvanceToggle();
        
        // Add visual indicators
        addAdvanceIndicators();
        
        // Load user preference from localStorage (if available)
        loadAutoAdvancePreference();
        
        console.log('✅ Auto-Advance System initialized');
    }

    // Add toggle button for auto-advance
    function addAutoAdvanceToggle() {
        const navigationContent = document.querySelector('.navigation-content');
        if (!navigationContent) return;
        
        const toggleContainer = document.createElement('div');
        toggleContainer.className = 'auto-advance-toggle mb-3';
        toggleContainer.innerHTML = `
            <div class="card">
                <div class="card-body p-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="autoAdvanceToggle" ${autoAdvanceEnabled ? 'checked' : ''}>
                        <label class="form-check-label fw-bold" for="autoAdvanceToggle">
                            <i class="fas fa-forward me-2"></i>Auto Next Question
                        </label>
                    </div>
                    <small class="text-muted">Automatically move to next question after answering</small>
                </div>
            </div>
        `;
        
        // Insert after progress info
        const progressInfo = navigationContent.querySelector('.progress-info');
        if (progressInfo) {
            progressInfo.insertAdjacentElement('afterend', toggleContainer);
        } else {
            navigationContent.insertBefore(toggleContainer, navigationContent.firstChild);
        }
        
        // Add event listener for toggle
        document.getElementById('autoAdvanceToggle').addEventListener('change', (e) => {
            autoAdvanceEnabled = e.target.checked;
            saveAutoAdvancePreference();
            
            // Show feedback
            showAutoAdvanceFeedback(autoAdvanceEnabled);
            
            console.log('🔄 Auto-advance toggled:', autoAdvanceEnabled);
        });
    }

    // Add visual indicators for auto-advance
    function addAdvanceIndicators() {
        // Create advance indicator element
        const indicator = document.createElement('div');
        indicator.id = 'autoAdvanceIndicator';
        indicator.className = 'auto-advance-indicator';
        indicator.innerHTML = `
            <div><i class="fas fa-forward me-2"></i>Next Question</div>
            <div class="auto-advance-progress">
                <div class="auto-advance-progress-bar" id="autoAdvanceProgressBar"></div>
            </div>
        `;
        document.body.appendChild(indicator);
    }

    // Enhanced answer handling with auto-advance
    function handleAnswerWithAutoAdvance(questionId, answer, element) {
        console.log('📝 Answer selected with auto-advance:', { questionId, answer });
        
        // Cancel any existing advance timer
        clearAutoAdvanceTimer();
        
        // Mark as answered and update UI
        answeredQuestions.add(questionId);
        updateQuestionNavStatus(questionId, true);
        updateProgressInfo();
        saveAnswer(questionId, answer);
        
        // Show answer feedback
        showAnswerFeedback(element);
        
        // Check if auto-advance should trigger
        if (shouldAutoAdvance()) {
            startAutoAdvanceTimer();
        }
    }

    // Check if auto-advance should be triggered
    function shouldAutoAdvance() {
        // Check global setting
        if (!autoAdvanceEnabled) return false;
        
        // Check test type specific settings
        if (isEPPS && !AUTO_ADVANCE_CONFIG.enableForEPPS) return false;
        if (!isEPPS && typeof isFieldTest !== 'undefined' && isFieldTest && !AUTO_ADVANCE_CONFIG.enableForFieldTest) return false;
        if (!isEPPS && (typeof isFieldTest === 'undefined' || !isFieldTest) && !AUTO_ADVANCE_CONFIG.enableForStandard) return false;
        
        // Don't auto-advance on last question if configured
        if (AUTO_ADVANCE_CONFIG.skipLastQuestion && currentQuestion >= totalQuestions) return false;
        
        // Don't auto-advance if already advancing
        if (isAdvancing) return false;
        
        return true;
    }

    // Start auto-advance timer
    function startAutoAdvanceTimer() {
        console.log('⏰ Starting auto-advance timer');
        
        isAdvancing = true;
        
        // Show advance indicator
        if (AUTO_ADVANCE_CONFIG.showAdvanceIndicator) {
            showAdvanceIndicator();
        }
        
        // Set timer for auto-advance
        advanceTimer = setTimeout(() => {
            executeAutoAdvance();
        }, AUTO_ADVANCE_CONFIG.delay);
    }

    // Execute auto-advance
    function executeAutoAdvance() {
        console.log('🚀 Executing auto-advance');
        
        // Hide advance indicator
        hideAdvanceIndicator();
        
        // Add transition effect
        addQuestionTransition();
        
        // Move to next question
        setTimeout(() => {
            if (currentQuestion < totalQuestions) {
                nextQuestion();
            }
            isAdvancing = false;
        }, 150); // Small delay for transition effect
    }

    // Clear auto-advance timer
    function clearAutoAdvanceTimer() {
        if (advanceTimer) {
            clearTimeout(advanceTimer);
            advanceTimer = null;
            hideAdvanceIndicator();
            isAdvancing = false;
            console.log('⏹️ Auto-advance timer cleared');
        }
    }

    // Show advance indicator with progress
    function showAdvanceIndicator() {
        const indicator = document.getElementById('autoAdvanceIndicator');
        const progressBar = document.getElementById('autoAdvanceProgressBar');
        
        if (!indicator || !progressBar) return;
        
        // Reset progress
        progressBar.style.width = '0%';
        progressBar.style.transitionDuration = '0s';
        
        // Show indicator
        indicator.classList.add('show');
        
        // Animate progress bar
        setTimeout(() => {
            progressBar.style.transitionDuration = AUTO_ADVANCE_CONFIG.delay + 'ms';
            progressBar.style.width = '100%';
        }, 50);
    }

    // Hide advance indicator
    function hideAdvanceIndicator() {
        const indicator = document.getElementById('autoAdvanceIndicator');
        if (indicator) {
            indicator.classList.remove('show');
        }
    }

    // Show answer feedback
    function showAnswerFeedback(element) {
        if (!element) return;
        
        const feedback = document.createElement('div');
        feedback.className = 'answer-selected-feedback';
        feedback.innerHTML = '<i class="fas fa-check me-2"></i>Answer Selected';
        
        // Position relative to the selected element
        const rect = element.getBoundingClientRect();
        feedback.style.position = 'fixed';
        feedback.style.top = rect.top + rect.height / 2 + 'px';
        feedback.style.left = rect.left + rect.width / 2 + 'px';
        
        document.body.appendChild(feedback);
        
        // Remove after animation
        setTimeout(() => {
            if (feedback.parentNode) {
                feedback.parentNode.removeChild(feedback);
            }
        }, 600);
    }

    // Add transition effects to questions
    function addQuestionTransition() {
        const currentCard = document.querySelector(`[data-question="${currentQuestion}"]`);
        if (currentCard) {
            currentCard.classList.add('question-transition', 'exiting');
        }
    }

    // Enhanced navigation function with transition
    function goToQuestionWithTransition(questionNum) {
        if (questionNum < 1 || questionNum > totalQuestions) return;
        
        console.log('🎯 Going to question with transition:', questionNum);
        
        const currentCard = document.querySelector(`[data-question="${currentQuestion}"]`);
        const targetCard = document.querySelector(`[data-question="${questionNum}"]`);
        
        if (!targetCard) return;
        
        // Prepare target card for transition
        targetCard.classList.add('question-transition', 'entering');
        targetCard.style.display = 'block';
        
        // Animate transition
        setTimeout(() => {
            // Hide current question
            if (currentCard) {
                currentCard.style.display = 'none';
                currentCard.classList.remove('question-transition', 'exiting');
            }
            
            // Show target question
            targetCard.classList.remove('entering');
            
            // Update navigation
            document.querySelectorAll('.question-nav-btn').forEach(btn => {
                btn.classList.remove('current');
            });
            
            const targetNavBtn = document.querySelector(`.question-nav-btn[data-question="${questionNum}"]`);
            if (targetNavBtn) {
                targetNavBtn.classList.add('current');
            }
            
            currentQuestion = questionNum;
            updateCurrentQuestionDisplay();
            
        }, 300);
    }

    // Show auto-advance toggle feedback
    function showAutoAdvanceFeedback(enabled) {
        const feedback = document.createElement('div');
        feedback.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${enabled ? '#28a745' : '#6c757d'};
            color: white;
            padding: 15px 20px;
            border-radius: 25px;
            z-index: 9999;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            opacity: 0;
            transform: translateX(100px);
            transition: all 0.3s ease;
            font-weight: 600;
        `;
        feedback.innerHTML = `
            <i class="fas fa-${enabled ? 'check' : 'times'} me-2"></i>
            Auto-advance ${enabled ? 'Enabled' : 'Disabled'}
        `;
        
        document.body.appendChild(feedback);
        
        // Animate in
        setTimeout(() => {
            feedback.style.opacity = '1';
            feedback.style.transform = 'translateX(0)';
        }, 100);
        
        // Animate out and remove
        setTimeout(() => {
            feedback.style.opacity = '0';
            feedback.style.transform = 'translateX(100px)';
            setTimeout(() => {
                if (feedback.parentNode) {
                    feedback.parentNode.removeChild(feedback);
                }
            }, 300);
        }, 2000);
    }

    // Save auto-advance preference
    function saveAutoAdvancePreference() {
        if (typeof Storage !== 'undefined') {
            localStorage.setItem('psychotest_auto_advance', autoAdvanceEnabled.toString());
        }
    }

    // Load auto-advance preference
    function loadAutoAdvancePreference() {
        if (typeof Storage !== 'undefined') {
            const saved = localStorage.getItem('psychotest_auto_advance');
            if (saved !== null) {
                autoAdvanceEnabled = saved === 'true';
                
                // Update toggle if it exists
                const toggle = document.getElementById('autoAdvanceToggle');
                if (toggle) {
                    toggle.checked = autoAdvanceEnabled;
                }
            }
        }
    }

    // Enhanced click handlers for different test types
    function setupEnhancedClickHandlers() {
        document.addEventListener('click', (e) => {
            if (isEPPS) {
                // EPPS specific handling
                const eppsChoice = e.target.closest('.epps-choice');
                if (eppsChoice) {
                    const radio = eppsChoice.querySelector('input[type="radio"]');
                    if (radio) {
                        handleEPPSSelection(eppsChoice, radio);
                    }
                }
            } else {
                // Standard and Field test handling
                const optionItem = e.target.closest('.option-item');
                if (optionItem) {
                    const radio = optionItem.querySelector('input[type="radio"]');
                    if (radio && !radio.checked) {
                        handleStandardSelection(optionItem, radio);
                    }
                }
            }
        });
    }

    // Handle EPPS selection with auto-advance
    function handleEPPSSelection(eppsChoice, radio) {
        console.log('🎯 EPPS choice selected with auto-advance');
        
        // Clear other selections
        const questionCard = eppsChoice.closest('.question-card');
        questionCard.querySelectorAll('.epps-choice').forEach(choice => {
            choice.classList.remove('selected');
        });
        
        const groupName = radio.getAttribute('name');
        document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => {
            r.checked = false;
        });
        
        // Select current option
        radio.checked = true;
        eppsChoice.classList.add('selected');
        
        // Handle with auto-advance
        const questionId = radio.name.replace('question_', '');
        handleAnswerWithAutoAdvance(questionId, radio.value, eppsChoice);
    }

    // Handle standard selection with auto-advance
    function handleStandardSelection(optionItem, radio) {
        console.log('🎯 Standard option selected with auto-advance');
        
        // Clear other selections
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
        
        // Handle with auto-advance
        const questionId = radio.name.replace('question_', '');
        handleAnswerWithAutoAdvance(questionId, radio.value, optionItem);
    }

    // ==========================================
    // NAVIGATION SYSTEM (ENHANCED WITH SECURITY)
    // ==========================================

    // Timer function
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
            alert('Time is up! Submitting your test automatically.');
            submitTest();
            return;
        }
        
        remainingSeconds--;
    }

    // Start timer
    setInterval(updateTimer, 1000);

    // Navigation functions (enhanced with auto-advance)
    function goToQuestion(questionNum) {
        // Clear any pending auto-advance
        clearAutoAdvanceTimer();
        
        // Use transition if available
        if (typeof goToQuestionWithTransition === 'function') {
            goToQuestionWithTransition(questionNum);
        } else {
            // Fallback to original navigation
            if (questionNum < 1 || questionNum > totalQuestions) return;
            
            console.log('Going to question:', questionNum);
            
            // Hide all questions
            document.querySelectorAll('.question-card').forEach(card => {
                card.style.display = 'none';
            });
            
            // Show target question
            const targetQuestion = document.querySelector(`[data-question="${questionNum}"]`);
            if (targetQuestion) {
                targetQuestion.style.display = 'block';
            }
            
            // Update navigation buttons
            document.querySelectorAll('.question-nav-btn').forEach(btn => {
                btn.classList.remove('current');
            });
            
            const targetNavBtn = document.querySelector(`.question-nav-btn[data-question="${questionNum}"]`);
            if (targetNavBtn) {
                targetNavBtn.classList.add('current');
            }
            
            currentQuestion = questionNum;
            updateCurrentQuestionDisplay();
        }
    }

    function nextQuestion() {
        // Clear any pending auto-advance
        clearAutoAdvanceTimer();
        
        if (currentQuestion < totalQuestions) {
            goToQuestion(currentQuestion + 1);
        }
    }

    function previousQuestion() {
        // Clear any pending auto-advance
        clearAutoAdvanceTimer();
        
        if (currentQuestion > 1) {
            goToQuestion(currentQuestion - 1);
        }
    }

    function updateCurrentQuestionDisplay() {
        const currentDisplay = document.getElementById('current-q');
        if (currentDisplay) {
            currentDisplay.textContent = currentQuestion;
        }
    }

    // Update question navigation status
    function updateQuestionNavStatus(questionId, answered) {
        console.log('Updating nav status for question:', questionId, 'answered:', answered);
        
        const navBtn = document.querySelector(`.question-nav-btn[data-question-id="${questionId}"]`);
        if (navBtn) {
            if (answered) {
                navBtn.classList.add('answered');
                console.log('✅ Added answered class to nav button for question:', questionId);
            } else {
                navBtn.classList.remove('answered');
                console.log('❌ Removed answered class from nav button for question:', questionId);
            }
        } else {
            console.error('⚠️ Nav button not found for question ID:', questionId);
        }
    }

    // Update progress info
    function updateProgressInfo() {
        const answeredCount = answeredQuestions.size;
        const percentage = Math.round((answeredCount / totalQuestions) * 100);
        
        const answeredCountEl = document.getElementById('answered-count');
        const progressPercentageEl = document.getElementById('progress-percentage');
        const progressBarEl = document.getElementById('progress-bar');
        const modalAnsweredCountEl = document.getElementById('modal-answered-count');
        const modalUnansweredCountEl = document.getElementById('modal-unanswered-count');
        
        if (answeredCountEl) answeredCountEl.textContent = answeredCount;
        if (progressPercentageEl) progressPercentageEl.textContent = percentage + '%';
        if (progressBarEl) {
            progressBarEl.style.width = percentage + '%';
            progressBarEl.style.transition = 'width 0.5s ease-in-out';
        }
        if (modalAnsweredCountEl) modalAnsweredCountEl.textContent = answeredCount;
        if (modalUnansweredCountEl) modalUnansweredCountEl.textContent = totalQuestions - answeredCount;
        
        console.log('📊 Progress updated:', {
            answeredCount,
            totalQuestions,
            percentage,
            answeredQuestions: Array.from(answeredQuestions),
            testType: isEPPS ? 'EPPS' : 'Standard'
        });
    }

    // Auto-save answers
    function saveAnswer(questionId, answer) {
        console.log('💾 Saving answer:', { questionId, answer, sessionId, testType: isEPPS ? 'EPPS' : 'Standard' });
        
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
                console.log('✅ Answer saved successfully');
                return response.json();
            } else {
                console.error('❌ Failed to save answer - Response not OK');
                throw new Error('Failed to save answer');
            }
        }).then(data => {
            console.log('📝 Save response:', data);
        }).catch(error => {
            console.error('🚨 Error saving answer:', error);
        });
    }

    // Handle answer selection (fallback for change events)
    document.addEventListener('change', (e) => {
        if (e.target.type === 'radio' || e.target.tagName === 'TEXTAREA') {
            const questionId = e.target.name.replace('question_', '');
            const answered = e.target.value.trim() !== '';
            
            console.log('🔄 Answer changed via change event:', { 
                questionId, 
                answer: e.target.value, 
                answered,
                elementName: e.target.name,
                testType: isEPPS ? 'EPPS' : 'Standard'
            });
            
            if (answered) {
                answeredQuestions.add(questionId);
                updateQuestionNavStatus(questionId, true);
            } else {
                answeredQuestions.delete(questionId);
                updateQuestionNavStatus(questionId, false);
            }
            
            updateProgressInfo();
            saveAnswer(questionId, e.target.value);
        }
    });

    // Submit test functions
    document.getElementById('submit-btn').addEventListener('click', () => {
        console.log('🚀 Submit button clicked. Current answers:', Array.from(answeredQuestions));
        // Clear any pending auto-advance
        clearAutoAdvanceTimer();
        
        // Show security summary if there were violations
        const securitySummary = document.getElementById('securitySummary');
        const totalViolations = Object.values(securityViolations).reduce((a, b) => a + b, 0);
        
        if (totalViolations > 0) {
            securitySummary.style.display = 'block';
        }
        
        new bootstrap.Modal(document.getElementById('submitModal')).show();
    });

    document.getElementById('confirm-submit').addEventListener('click', () => {
        submitTest();
    });

    function submitTest() {
        console.log('📤 Submitting test with answered questions:', Array.from(answeredQuestions));
        console.log('🔒 Security violations:', securityViolations);
        
        // Mark test as inactive to stop security monitoring
        isTestActive = false;
        
        // Clear any pending auto-advance
        clearAutoAdvanceTimer();
        
        // Clear security monitoring
        if (securityCheckInterval) {
            clearInterval(securityCheckInterval);
        }
        
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
        
        // Add security violations data
        const violationsInput = document.createElement('input');
        violationsInput.type = 'hidden';
        violationsInput.name = 'security_violations';
        violationsInput.value = JSON.stringify(securityViolations);
        form.appendChild(violationsInput);
        
        document.body.appendChild(form);
        form.submit();
    }

    // Prevent accidental page refresh
    window.addEventListener('beforeunload', (e) => {
        if (remainingSeconds > 0 && isTestActive) {
            e.preventDefault();
            e.returnValue = 'Your test progress will be lost if you leave this page.';
        }
    });

    // Auto-save textarea content with debounce
    document.addEventListener('input', (e) => {
        if (e.target.tagName === 'TEXTAREA') {
            clearTimeout(window.textareaTimer);
            window.textareaTimer = setTimeout(() => {
                const questionId = e.target.name.replace('question_', '');
                const answered = e.target.value.trim() !== '';
                
                console.log('📝 Textarea input detected:', { questionId, answered, value: e.target.value });
                
                if (answered) {
                    answeredQuestions.add(questionId);
                    updateQuestionNavStatus(questionId, true);
                } else {
                    answeredQuestions.delete(questionId);
                    updateQuestionNavStatus(questionId, false);
                }
                
                updateProgressInfo();
                saveAnswer(questionId, e.target.value);
            }, 2000);
        }
    });

    // Enhanced keyboard shortcuts for auto-advance control and security
    document.addEventListener('keydown', (e) => {
        // Security checks are handled in initializeSecurity()
        
        // Navigation shortcuts (only if not blocked by security)
        if (!e.ctrlKey && !e.altKey && !e.shiftKey) {
            if (isEPPS) {
                if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    nextQuestion();
                } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    previousQuestion();
                } else if (e.key === 'a' || e.key === 'A') {
                    e.preventDefault();
                    const currentCard = document.querySelector(`[data-question="${currentQuestion}"]`);
                    const choiceA = currentCard.querySelector('.epps-choice[data-value]');
                    if (choiceA) choiceA.click();
                } else if (e.key === 'b' || e.key === 'B') {
                    e.preventDefault();
                    const currentCard = document.querySelector(`[data-question="${currentQuestion}"]`);
                    const choices = currentCard.querySelectorAll('.epps-choice');
                    if (choices.length > 1) {
                        choices[1].click();
                    }
                }
            }
        }
    });

    // Export functions for external use
    window.autoAdvanceSystem = {
        toggle: () => {
            autoAdvanceEnabled = !autoAdvanceEnabled;
            const toggle = document.getElementById('autoAdvanceToggle');
            if (toggle) toggle.checked = autoAdvanceEnabled;
            saveAutoAdvancePreference();
            showAutoAdvanceFeedback(autoAdvanceEnabled);
        },
        
        enable: () => {
            autoAdvanceEnabled = true;
            const toggle = document.getElementById('autoAdvanceToggle');
            if (toggle) toggle.checked = true;
            saveAutoAdvancePreference();
            showAutoAdvanceFeedback(true);
        },
        
        disable: () => {
            autoAdvanceEnabled = false;
            clearAutoAdvanceTimer();
            const toggle = document.getElementById('autoAdvanceToggle');
            if (toggle) toggle.checked = false;
            saveAutoAdvancePreference();
            showAutoAdvanceFeedback(false);
        },
        
        setDelay: (delay) => {
            AUTO_ADVANCE_CONFIG.delay = delay;
            console.log('🔧 Auto-advance delay set to:', delay + 'ms');
        },
        
        getStatus: () => ({
            enabled: autoAdvanceEnabled,
            delay: AUTO_ADVANCE_CONFIG.delay,
            isAdvancing: isAdvancing,
            currentQuestion: currentQuestion
        })
    };

    // Export security system for monitoring
    window.securitySystem = {
        getViolations: () => securityViolations,
        getTotalViolations: () => Object.values(securityViolations).reduce((a, b) => a + b, 0),
        isActive: () => isTestActive,
        forceFullscreen: () => enterFullscreen(),
        getStatus: () => ({
            violations: securityViolations,
            totalViolations: Object.values(securityViolations).reduce((a, b) => a + b, 0),
            isActive: isTestActive,
            isFullscreen: !!document.fullscreenElement,
            hasFocus: document.hasFocus(),
            isVisible: !document.hidden
        })
    };

    console.log('🚀 Enhanced Anti-Cheating Navigation System loaded');
    console.log('🔒 Security monitoring active');
    console.log('Available commands: autoAdvanceSystem.*, securitySystem.*');
    console.log('Security features: Fullscreen enforcement, Copy/paste blocking, Tab monitoring, Focus tracking, Right-click disabled');
</script>
</body>
</html>