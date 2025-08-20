@extends('layouts.admin')

@section('page-title')
    {{ __('Category Results') }} - {{ $category->name }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('psychotest-result.index') }}">{{ __('Psychotest Results') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('psychotest-result.show', $schedule->id) }}">{{ __('Detail') }}</a></li>
    <li class="breadcrumb-item">{{ $category->name }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('psychotest-result.show', $schedule->id) }}" class="btn btn-sm btn-primary">
            <i class="ti ti-arrow-left"></i> {{ __('Back to Results') }}
        </a>
        <button class="btn btn-sm btn-success" onclick="exportCategoryResults()">
            <i class="ti ti-download"></i> {{ __('Export Category') }}
        </button>
    </div>
@endsection

@push('css-page')
<style>
    .category-icon {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(13, 110, 253, 0.1);
        border-radius: 50%;
        margin: 0 auto;
    }

    .info-list .info-item {
        border-bottom: 1px solid #f1f3f4;
    }

    .info-list .info-item:last-child {
        border-bottom: none;
    }

    .answer-item {
        transition: all 0.3s ease;
    }

    .answer-item:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .question-number .badge {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 18px;
    }

    .badge-lg {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .option-item {
        transition: all 0.2s ease;
        border: 1px solid #dee2e6;
    }

    .option-letter {
        width: 30px;
        height: 30px;
        background: #6c757d;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
    }

    .stat-card {
        border: 1px solid rgba(0,0,0,0.1);
    }

    .stat-item {
        padding: 8px 0;
    }

    .progress {
        border-radius: 10px;
    }

    .rating-item {
        transition: all 0.2s ease;
    }

    .summary-stat {
        padding: 10px;
    }

    .btn-group .btn {
        transition: all 0.2s ease;
    }

    .empty-state {
        padding: 60px 20px;
    }

    .insights-list .insight-item {
        border-left: 3px solid #28a745;
    }

    .answer-summary {
        border: 1px solid #dee2e6;
    }

    @media (max-width: 768px) {
        .question-content {
            padding: 20px;
        }
        
        .true-false-options .col-6 {
            margin-bottom: 10px;
        }
        
        .summary-stat {
            margin-bottom: 15px;
        }
    }
    </style>
@endpush

@section('content')
    <div class="row">
        <!-- Candidate & Category Info -->
        <div class="col-lg-4">
            <!-- Candidate Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>{{ __('Candidate Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar avatar-lg mb-3">
                            <span class="avatar-text fs-3">{{ substr($schedule->candidates->name, 0, 2) }}</span>
                        </div>
                        <h6 class="mb-1">{{ $schedule->candidates->name }}</h6>
                        <small class="text-muted">{{ $schedule->candidates->email }}</small>
                    </div>
                    
                    <div class="info-list">
                        <div class="info-item d-flex justify-content-between py-2">
                            <span class="text-muted">{{ __('Position') }}:</span>
                            <span class="fw-bold">{{ $schedule->candidates->jobs->title ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2">
                            <span class="text-muted">{{ __('Test Date') }}:</span>
                            <span>{{ $schedule->start_time->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>{{ __('Category Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="category-icon mb-3">
                            @if($category->isKraeplin())
                                <i class="ti ti-calculator fs-1 text-warning"></i>
                                <span class="badge bg-warning mt-2">Kraeplin Test</span>
                            @elseif($category->isEPPS())
                                <i class="ti ti-user-heart fs-1 text-info"></i>
                                <span class="badge bg-info mt-2">EPPS Personality</span>
                            @elseif($category->isFieldSpecific())
                                <i class="ti ti-briefcase fs-1 text-success"></i>
                                <span class="badge bg-success mt-2">Field Specific</span>
                            @elseif($category->type == 'visual')
                                <i class="ti ti-eye fs-1 text-primary"></i>
                                <span class="badge bg-primary mt-2">Visual Test</span>
                            @elseif($category->type == 'verbal')
                                <i class="ti ti-message fs-1 text-purple"></i>
                                <span class="badge bg-purple mt-2">Verbal Test</span>
                            @elseif($category->type == 'numeric')
                                <i class="ti ti-math fs-1 text-danger"></i>
                                <span class="badge bg-danger mt-2">Numeric Test</span>
                            @else
                                <i class="ti ti-clipboard-list fs-1 text-secondary"></i>
                                <span class="badge bg-secondary mt-2">Standard Test</span>
                            @endif
                        </div>
                        <h6 class="mb-1">{{ $category->name }}</h6>
                        <p class="text-muted text-sm">{{ $category->description }}</p>
                    </div>

                    <div class="info-list">
                        <div class="info-item d-flex justify-content-between py-2">
                            <span class="text-muted">{{ __('Duration') }}:</span>
                            <span>{{ $category->duration_minutes }} {{ __('minutes') }}</span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2">
                            <span class="text-muted">{{ __('Total Questions') }}:</span>
                            <span>{{ $category->total_questions }}</span>
                        </div>
                        @if($session && $session->started_at)
                            <div class="info-item d-flex justify-content-between py-2">
                                <span class="text-muted">{{ __('Started At') }}:</span>
                                <span>{{ $session->started_at->format('H:i:s') }}</span>
                            </div>
                        @endif
                        @if($session && $session->completed_at)
                            <div class="info-item d-flex justify-content-between py-2">
                                <span class="text-muted">{{ __('Completed At') }}:</span>
                                <span>{{ $session->completed_at->format('H:i:s') }}</span>
                            </div>
                        @endif
                        @if($session && $session->time_spent_seconds)
                            <div class="info-item d-flex justify-content-between py-2">
                                <span class="text-muted">{{ __('Time Spent') }}:</span>
                                <span>{{ gmdate('i:s', $session->time_spent_seconds) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($categoryAnalysis)
                <!-- Performance Metrics -->
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Performance Metrics') }}</h5>
                    </div>
                    <div class="card-body">
                        <!-- Completion Rate -->
                        <div class="metric-item mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">{{ __('Completion Rate') }}</span>
                                <span class="fw-bold fs-6">{{ $categoryAnalysis['completion_rate'] }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: {{ $categoryAnalysis['completion_rate'] }}%"></div>
                            </div>
                        </div>

                        <!-- Accuracy Rate -->
                        <div class="metric-item mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">{{ __('Accuracy Rate') }}</span>
                                <span class="fw-bold fs-6">{{ $categoryAnalysis['accuracy_rate'] }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar 
                                    @if($categoryAnalysis['accuracy_rate'] >= 80) bg-success
                                    @elseif($categoryAnalysis['accuracy_rate'] >= 60) bg-warning
                                    @else bg-danger
                                    @endif" style="width: {{ $categoryAnalysis['accuracy_rate'] }}%"></div>
                            </div>
                        </div>

                        <!-- Average Time -->
                        <div class="metric-item mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">{{ __('Avg Time/Question') }}</span>
                                <span class="fw-bold fs-6">{{ $categoryAnalysis['average_time_per_question'] }}s</span>
                            </div>
                        </div>

                        @if(count($categoryAnalysis['category_insights']) > 0)
                            <!-- Insights -->
                            <div class="insights-section mt-4">
                                <h6 class="text-primary mb-3">
                                    <i class="ti ti-bulb me-2"></i>{{ __('Key Insights') }}
                                </h6>
                                <div class="insights-list">
                                    @foreach($categoryAnalysis['category_insights'] as $insight)
                                        <div class="insight-item p-2 mb-2 bg-light rounded">
                                            <i class="ti ti-check text-success me-2"></i>
                                            <span class="text-sm">{{ $insight }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Detailed Answers -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">{{ __('Detailed Answers') }}</h5>
                            <small class="text-muted">{{ __('Question by question analysis') }}</small>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="filterAnswers('correct')">
                                    <i class="ti ti-check me-1"></i>{{ __('Correct') }}
                                    <span class="badge bg-success ms-1" id="correct-count">0</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="filterAnswers('incorrect')">
                                    <i class="ti ti-x me-1"></i>{{ __('Incorrect') }}
                                    <span class="badge bg-danger ms-1" id="incorrect-count">0</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary active" onclick="filterAnswers('all')">
                                    <i class="ti ti-list me-1"></i>{{ __('All') }}
                                    <span class="badge bg-primary ms-1" id="total-count">0</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($answers && count($answers) > 0)
                        <div class="answers-container">
                            @foreach($answers as $index => $answer)
                                <div class="answer-item mb-4 p-4 border rounded-3 
                                    {{ $answer['is_correct'] ? 'border-success-subtle bg-success-subtle' : 'border-danger-subtle bg-danger-subtle' }}"
                                    data-answer-type="{{ $answer['is_correct'] ? 'correct' : 'incorrect' }}"
                                    data-points="{{ $answer['points_earned'] }}"
                                    data-time="{{ $answer['time_taken'] ?? 0 }}">
                                    
                                    <div class="row">
                                        <!-- Question Content -->
                                        <div class="col-lg-8">
                                            <div class="question-header d-flex align-items-start mb-3">
                                                <div class="question-number me-3">
                                                    <span class="badge badge-lg
                                                        {{ $answer['is_correct'] ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $index + 1 }}
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-2 fw-bold">{{ $answer['question']->title }}</h6>
                                                    <p class="text-muted mb-3">{{ $answer['question']->question }}</p>

                                                    @if($answer['question']->image)
                                                        <div class="question-image mb-3 text-center">
                                                            <img src="{{ asset('storage/public/uploads/psychotest/images/' . $answer['question']->image) }}" 
                                                                 alt="Question Image" 
                                                                 class="img-fluid rounded shadow-sm" 
                                                                 style="max-height: 200px; cursor: pointer;"
                                                                 onclick="showImageModal(this.src)">
                                                        </div>
                                                    @endif

                                                    <!-- Answer Options Display -->
                                                    @if($answer['question']->type == 'multiple_choice' && $answer['question']->options)
                                                        <div class="options-grid">
                                                            @foreach($answer['question']->options as $optionIndex => $option)
                                                                <div class="option-item p-3 mb-2 rounded-2 d-flex align-items-center
                                                                    @if($option == $answer['user_answer']) 
                                                                        {{ $answer['is_correct'] ? 'bg-success text-white' : 'bg-danger text-white' }}
                                                                    @elseif($option == $answer['correct_answer'] && $option != $answer['user_answer'])
                                                                        bg-success-subtle text-success border border-success
                                                                    @else
                                                                        bg-light border
                                                                    @endif">
                                                                    
                                                                    <div class="option-indicator me-3">
                                                                        @if($option == $answer['user_answer'])
                                                                            @if($answer['is_correct'])
                                                                                <i class="ti ti-check-circle fs-5"></i>
                                                                            @else
                                                                                <i class="ti ti-x-circle fs-5"></i>
                                                                            @endif
                                                                        @elseif($option == $answer['correct_answer'])
                                                                            <i class="ti ti-arrow-right text-success fs-5"></i>
                                                                        @else
                                                                            <div class="option-letter">{{ chr(65 + $optionIndex) }}</div>
                                                                        @endif
                                                                    </div>
                                                                    
                                                                    <div class="option-text flex-grow-1">
                                                                        {{ $option }}
                                                                    </div>
                                                                    
                                                                    <div class="option-badges">
                                                                        @if($option == $answer['user_answer'])
                                                                            <span class="badge bg-primary">{{ __('User Choice') }}</span>
                                                                        @endif
                                                                        @if($option == $answer['correct_answer'] && $option != $answer['user_answer'])
                                                                            <span class="badge bg-success">{{ __('Correct Answer') }}</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                    @elseif($answer['question']->type == 'true_false')
                                                        <div class="true-false-options row g-3">
                                                            <div class="col-6">
                                                                <div class="option-item p-3 rounded-2 text-center border
                                                                    @if($answer['user_answer'] == 'True')
                                                                        {{ $answer['is_correct'] ? 'bg-success text-white' : 'bg-danger text-white' }}
                                                                    @elseif($answer['correct_answer'] == 'True')
                                                                        bg-success-subtle text-success border-success
                                                                    @else
                                                                        bg-light
                                                                    @endif">
                                                                    <i class="ti ti-check fs-3 mb-2 d-block"></i>
                                                                    <div class="fw-bold">TRUE</div>
                                                                    @if($answer['user_answer'] == 'True')
                                                                        <span class="badge bg-primary mt-2">{{ __('Selected') }}</span>
                                                                    @endif
                                                                    @if($answer['correct_answer'] == 'True' && $answer['user_answer'] != 'True')
                                                                        <span class="badge bg-success mt-2">{{ __('Correct') }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="option-item p-3 rounded-2 text-center border
                                                                    @if($answer['user_answer'] == 'False')
                                                                        {{ $answer['is_correct'] ? 'bg-success text-white' : 'bg-danger text-white' }}
                                                                    @elseif($answer['correct_answer'] == 'False')
                                                                        bg-success-subtle text-success border-success
                                                                    @else
                                                                        bg-light
                                                                    @endif">
                                                                    <i class="ti ti-x fs-3 mb-2 d-block"></i>
                                                                    <div class="fw-bold">FALSE</div>
                                                                    @if($answer['user_answer'] == 'False')
                                                                        <span class="badge bg-primary mt-2">{{ __('Selected') }}</span>
                                                                    @endif
                                                                    @if($answer['correct_answer'] == 'False' && $answer['user_answer'] != 'False')
                                                                        <span class="badge bg-success mt-2">{{ __('Correct') }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                    @elseif($answer['question']->type == 'essay')
                                                        <div class="essay-answer">
                                                            <h6 class="text-primary mb-2">
                                                                <i class="ti ti-edit me-2"></i>{{ __('User Answer') }}:
                                                            </h6>
                                                            <div class="p-3 bg-light rounded-2 border">
                                                                <p class="mb-0">{{ $answer['user_answer'] ?: __('No answer provided') }}</p>
                                                            </div>
                                                        </div>

                                                    @elseif($answer['question']->type == 'rating_scale')
                                                        <div class="rating-answer">
                                                            <div class="d-flex justify-content-center align-items-center gap-2 py-3">
                                                                @for($i = 1; $i <= (max($answer['question']->options) ?? 5); $i++)
                                                                    <div class="rating-item text-center
                                                                        @if($i == $answer['user_answer']) bg-primary text-white
                                                                        @elseif($i == $answer['correct_answer']) bg-success text-white
                                                                        @else bg-light
                                                                        @endif
                                                                        rounded-circle d-flex align-items-center justify-content-center" 
                                                                        style="width: 40px; height: 40px;">
                                                                        <span class="fw-bold">{{ $i }}</span>
                                                                    </div>
                                                                @endfor
                                                            </div>
                                                            <div class="text-center mt-2">
                                                                <small class="text-muted">
                                                                    {{ __('User selected') }}: <strong>{{ $answer['user_answer'] }}</strong>
                                                                    @if($answer['correct_answer'])
                                                                        | {{ __('Expected') }}: <strong>{{ $answer['correct_answer'] }}</strong>
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Answer Metadata -->
                                        <div class="col-lg-4">
                                            <div class="answer-stats">
                                                <!-- Result Status -->
                                                <div class="stat-card mb-3 p-3 rounded-2 text-center
                                                    {{ $answer['is_correct'] ? 'bg-success-subtle' : 'bg-danger-subtle' }}">
                                                    <div class="stat-icon mb-2">
                                                        <i class="ti {{ $answer['is_correct'] ? 'ti-check-circle text-success' : 'ti-x-circle text-danger' }} fs-1"></i>
                                                    </div>
                                                    <div class="stat-label fw-bold {{ $answer['is_correct'] ? 'text-success' : 'text-danger' }}">
                                                        {{ $answer['is_correct'] ? __('CORRECT') : __('INCORRECT') }}
                                                    </div>
                                                </div>

                                                <!-- Points -->
                                                <div class="stat-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                                    <span class="text-muted">{{ __('Points') }}:</span>
                                                    <div class="d-flex align-items-center">
                                                        <span class="fw-bold me-2">{{ $answer['points_earned'] }}/{{ $answer['question']->points }}</span>
                                                        <div class="progress" style="width: 60px; height: 6px;">
                                                            <div class="progress-bar bg-primary" 
                                                                 style="width: {{ $answer['question']->points > 0 ? ($answer['points_earned'] / $answer['question']->points) * 100 : 0 }}%"></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if($answer['time_taken'])
                                                    <!-- Time Taken -->
                                                    <div class="stat-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                                        <span class="text-muted">{{ __('Time') }}:</span>
                                                        <span class="fw-bold">{{ $answer['time_taken'] }}s</span>
                                                    </div>
                                                @endif

                                                @if($answer['answered_at'])
                                                    <!-- Timestamp -->
                                                    <div class="stat-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                                        <span class="text-muted">{{ __('Answered') }}:</span>
                                                        <span class="text-sm">{{ $answer['answered_at']->format('H:i:s') }}</span>
                                                    </div>
                                                @endif

                                                @if($answer['question']->difficulty_level)
                                                    <!-- Difficulty -->
                                                    <div class="stat-item d-flex justify-content-between align-items-center py-2">
                                                        <span class="text-muted">{{ __('Difficulty') }}:</span>
                                                        <span class="badge 
                                                            @if($answer['question']->difficulty_level == 'easy') bg-success
                                                            @elseif($answer['question']->difficulty_level == 'medium') bg-warning
                                                            @else bg-danger
                                                            @endif">
                                                            {{ ucfirst($answer['question']->difficulty_level) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Summary Statistics -->
                        <div class="answer-summary mt-4 p-3 bg-light rounded-2">
                            <div class="row text-center">
                                <div class="col-3">
                                    <div class="summary-stat">
                                        <div class="stat-number fs-4 fw-bold text-primary" id="summary-total">{{ count($answers) }}</div>
                                        <div class="stat-label text-muted">{{ __('Total Questions') }}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="summary-stat">
                                        <div class="stat-number fs-4 fw-bold text-success" id="summary-correct">0</div>
                                        <div class="stat-label text-muted">{{ __('Correct') }}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="summary-stat">
                                        <div class="stat-number fs-4 fw-bold text-danger" id="summary-incorrect">0</div>
                                        <div class="stat-label text-muted">{{ __('Incorrect') }}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="summary-stat">
                                        <div class="stat-number fs-4 fw-bold text-warning" id="summary-percentage">0%</div>
                                        <div class="stat-label text-muted">{{ __('Accuracy') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @else
                        <!-- Empty State -->
                        <div class="empty-state text-center py-5">
                            <div class="empty-icon mb-4">
                                <i class="ti ti-clipboard-off fs-1 text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-2">{{ __('No Answers Found') }}</h5>
                            <p class="text-muted">{{ __('No answers were recorded for this category.') }}</p>
                            <a href="{{ route('psychotest-result.show', $schedule->id) }}" class="btn btn-primary">
                                <i class="ti ti-arrow-left me-2"></i>{{ __('Back to Results') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Question Image') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Question Image" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script-page')
<script>
   // ==============================================
// COMPLETE JAVASCRIPT FOR CATEGORY RESULTS VIEW
// ==============================================

// Global Variables
let currentFilter = 'all';
let totalAnswers = {{ count($answers ?? []) }};
let correctAnswers = 0;
let incorrectAnswers = 0;
let chartInstance = null;
let animationPlayed = false;

// DOM Elements Cache
const elements = {
    filterButtons: null,
    answerItems: null,
    summaryElements: {},
    progressBars: null,
    modals: {}
};

// ==============================================
// INITIALIZATION
// ==============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing Category Results View');
    
    // Cache DOM elements
    cacheElements();
    
    // Initialize data
    initializeData();
    
    // Calculate statistics
    calculateStatistics();
    
    // Initialize filter system
    initializeFilters();
    
    // Initialize interactions
    initializeInteractions();
    
    // Initialize animations
    initializeAnimations();
    
    // Initialize keyboard shortcuts
    initializeKeyboardShortcuts();
    
    // Initialize tooltips
    initializeTooltips();
    
    console.log('✅ Category Results View initialized successfully');
});

// ==============================================
// DOM ELEMENT CACHING
// ==============================================

function cacheElements() {
    elements.filterButtons = document.querySelectorAll('.btn-group .btn');
    elements.answerItems = document.querySelectorAll('.answer-item');
    elements.progressBars = document.querySelectorAll('.progress-bar');
    
    // Summary elements
    elements.summaryElements = {
        total: document.getElementById('summary-total'),
        correct: document.getElementById('summary-correct'),
        incorrect: document.getElementById('summary-incorrect'),
        percentage: document.getElementById('summary-percentage')
    };
    
    // Badge counters
    elements.badgeCounters = {
        correct: document.getElementById('correct-count'),
        incorrect: document.getElementById('incorrect-count'),
        total: document.getElementById('total-count')
    };
    
    // Modals
    elements.modals = {
        image: document.getElementById('imageModal'),
        imageElement: document.getElementById('modalImage')
    };
}

// ==============================================
// DATA INITIALIZATION
// ==============================================

function initializeData() {
    // Get answer data from server
    const serverData = @json($answers ?? []);
    
    // Process server data
    totalAnswers = serverData.length;
    correctAnswers = serverData.filter(answer => answer.is_correct).length;
    incorrectAnswers = totalAnswers - correctAnswers;
    
    console.log('📊 Data initialized:', {
        total: totalAnswers,
        correct: correctAnswers,
        incorrect: incorrectAnswers,
        percentage: totalAnswers > 0 ? Math.round((correctAnswers / totalAnswers) * 100) : 0
    });
}

// ==============================================
// STATISTICS CALCULATION
// ==============================================

function calculateStatistics() {
    // Update badge counters
    if (elements.badgeCounters.correct) {
        elements.badgeCounters.correct.textContent = correctAnswers;
    }
    if (elements.badgeCounters.incorrect) {
        elements.badgeCounters.incorrect.textContent = incorrectAnswers;
    }
    if (elements.badgeCounters.total) {
        elements.badgeCounters.total.textContent = totalAnswers;
    }
    
    // Update summary statistics
    if (elements.summaryElements.total) {
        elements.summaryElements.total.textContent = totalAnswers;
    }
    if (elements.summaryElements.correct) {
        elements.summaryElements.correct.textContent = correctAnswers;
    }
    if (elements.summaryElements.incorrect) {
        elements.summaryElements.incorrect.textContent = incorrectAnswers;
    }
    
    const percentage = totalAnswers > 0 ? Math.round((correctAnswers / totalAnswers) * 100) : 0;
    if (elements.summaryElements.percentage) {
        elements.summaryElements.percentage.textContent = percentage + '%';
    }
    
    console.log('📈 Statistics updated');
}

// ==============================================
// FILTER SYSTEM
// ==============================================

function initializeFilters() {
    // Set default filter
    currentFilter = 'all';
    updateFilterButtons('all');
    filterAnswers('all');
    
    console.log('🔍 Filter system initialized');
}

function filterAnswers(type) {
    console.log(`🔍 Filtering answers by: ${type}`);
    
    const answerItems = document.querySelectorAll('.answer-item');
    let visibleCount = 0;
    
    // Remove any existing empty state
    removeEmptyFilterState();
    
    answerItems.forEach((item, index) => {
        const answerType = item.getAttribute('data-answer-type');
        
        if (type === 'all' || answerType === type) {
            // Show item with animation
            item.style.display = 'block';
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 50);
            
            visibleCount++;
        } else {
            // Hide item with animation
            item.style.transition = 'all 0.2s ease';
            item.style.opacity = '0';
            item.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                item.style.display = 'none';
            }, 200);
        }
    });
    
    currentFilter = type;
    updateFilterButtons(type);
    
    // Show empty state if no items visible
    if (visibleCount === 0 && type !== 'all') {
        showEmptyFilterState(type);
    }
    
    // Update visible count in console
    console.log(`📊 Showing ${visibleCount} out of ${totalAnswers} answers`);
    
    // Trigger custom event
    document.dispatchEvent(new CustomEvent('filterChanged', {
        detail: { type, visibleCount, totalAnswers }
    }));
}

function updateFilterButtons(activeType) {
    elements.filterButtons.forEach(btn => {
        // Reset all button styles
        btn.classList.remove('active', 'btn-success', 'btn-danger', 'btn-primary');
        
        // Set default outline styles
        if (btn.onclick.toString().includes("'correct'")) {
            btn.classList.add('btn-outline-success');
        } else if (btn.onclick.toString().includes("'incorrect'")) {
            btn.classList.add('btn-outline-danger');
        } else if (btn.onclick.toString().includes("'all'")) {
            btn.classList.add('btn-outline-primary');
        }
    });
    
    // Set active button style
    const activeButton = document.querySelector(`button[onclick*="filterAnswers('${activeType}')"]`);
    if (activeButton) {
        activeButton.classList.add('active');
        
        if (activeType === 'correct') {
            activeButton.classList.remove('btn-outline-success');
            activeButton.classList.add('btn-success');
        } else if (activeType === 'incorrect') {
            activeButton.classList.remove('btn-outline-danger');
            activeButton.classList.add('btn-danger');
        } else {
            activeButton.classList.remove('btn-outline-primary');
            activeButton.classList.add('btn-primary');
        }
        
        // Add pulse effect
        activeButton.style.transform = 'scale(1.05)';
        setTimeout(() => {
            activeButton.style.transform = 'scale(1)';
        }, 150);
    }
}

function showEmptyFilterState(type) {
    const container = document.querySelector('.answers-container');
    if (!container) return;
    
    const emptyState = document.createElement('div');
    emptyState.className = 'empty-filter-state text-center py-5';
    emptyState.innerHTML = `
        <div class="empty-icon mb-4">
            <i class="ti ti-filter-off fs-1 text-muted"></i>
        </div>
        <h6 class="text-muted mb-2">No ${type} answers found</h6>
        <p class="text-muted mb-4">There are no ${type} answers to display for this category.</p>
        <button class="btn btn-outline-primary" onclick="filterAnswers('all')">
            <i class="ti ti-list me-2"></i>Show All Answers
        </button>
    `;
    
    // Add animation
    emptyState.style.opacity = '0';
    emptyState.style.transform = 'translateY(20px)';
    container.appendChild(emptyState);
    
    setTimeout(() => {
        emptyState.style.transition = 'all 0.4s ease';
        emptyState.style.opacity = '1';
        emptyState.style.transform = 'translateY(0)';
    }, 100);
}

function removeEmptyFilterState() {
    const emptyState = document.querySelector('.empty-filter-state');
    if (emptyState) {
        emptyState.style.transition = 'all 0.2s ease';
        emptyState.style.opacity = '0';
        emptyState.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            emptyState.remove();
        }, 200);
    }
}

// ==============================================
// INTERACTIONS
// ==============================================

function initializeInteractions() {
    // Image modal interactions
    initializeImageModal();
    
    // Answer item hover effects
    initializeHoverEffects();
    
    // Progress bar click interactions
    initializeProgressInteractions();
    
    // Scroll animations
    initializeScrollAnimations();
    
    console.log('🎯 Interactions initialized');
}

function initializeImageModal() {
    // Image click handlers
    document.querySelectorAll('.question-image img').forEach(img => {
        img.addEventListener('click', function() {
            showImageModal(this.src);
        });
        
        // Add cursor pointer
        img.style.cursor = 'pointer';
        
        // Add hover effect
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
}

function showImageModal(imageSrc) {
    if (elements.modals.imageElement) {
        elements.modals.imageElement.src = imageSrc;
    }
    
    if (elements.modals.image) {
        const modal = new bootstrap.Modal(elements.modals.image);
        modal.show();
        
        // Add keyboard close
        elements.modals.image.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                modal.hide();
            }
        });
    }
}

function initializeHoverEffects() {
    elements.answerItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            if (this.style.display !== 'none') {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
                this.style.transition = 'all 0.3s ease';
            }
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
}

function initializeProgressInteractions() {
    elements.progressBars.forEach(bar => {
        bar.addEventListener('click', function() {
            // Add click animation
            this.style.transform = 'scaleY(1.2)';
            setTimeout(() => {
                this.style.transform = 'scaleY(1)';
            }, 150);
        });
    });
}

function initializeScrollAnimations() {
    // Intersection Observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    // Observe answer items
    elements.answerItems.forEach(item => {
        observer.observe(item);
    });
}

// ==============================================
// ANIMATIONS
// ==============================================

function initializeAnimations() {
    // Animate on page load
    setTimeout(() => {
        animateProgressBars();
        animateCounters();
        animateCards();
    }, 300);
    
    console.log('🎨 Animations initialized');
}

function animateProgressBars() {
    elements.progressBars.forEach((bar, index) => {
        const width = bar.style.width || bar.getAttribute('data-width') || '0%';
        
        // Reset width
        bar.style.width = '0%';
        bar.style.transition = 'none';
        
        setTimeout(() => {
            bar.style.transition = 'width 1s ease-in-out';
            bar.style.width = width;
        }, index * 100 + 200);
    });
}

function animateCounters() {
    // Animate summary counters
    Object.entries(elements.summaryElements).forEach(([key, element], index) => {
        if (!element) return;
        
        const targetValue = element.textContent;
        const isPercentage = targetValue.includes('%');
        const numericValue = parseInt(targetValue);
        
        if (isNaN(numericValue)) return;
        
        element.textContent = isPercentage ? '0%' : '0';
        
        setTimeout(() => {
            animateNumber(element, 0, numericValue, 1000, isPercentage);
        }, index * 200);
    });
}

function animateNumber(element, start, end, duration, isPercentage = false) {
    const startTime = performance.now();
    
    function updateNumber(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function (ease-out)
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(start + (end - start) * easeOut);
        
        element.textContent = isPercentage ? current + '%' : current;
        
        if (progress < 1) {
            requestAnimationFrame(updateNumber);
        }
    }
    
    requestAnimationFrame(updateNumber);
}

function animateCards() {
    elements.answerItems.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// ==============================================
// KEYBOARD SHORTCUTS
// ==============================================

function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Only trigger if not typing in input fields
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            return;
        }
        
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case '1':
                    e.preventDefault();
                    filterAnswers('all');
                    break;
                case '2':
                    e.preventDefault();
                    filterAnswers('correct');
                    break;
                case '3':
                    e.preventDefault();
                    filterAnswers('incorrect');
                    break;
            }
        }
        
        // ESC to close modals
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                const modal = bootstrap.Modal.getInstance(openModal);
                if (modal) modal.hide();
            }
        }
        
        // Arrow keys for navigation
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            e.preventDefault();
            navigateAnswers(e.key === 'ArrowUp' ? 'up' : 'down');
        }
    });
    
    console.log('⌨️ Keyboard shortcuts initialized');
}

function navigateAnswers(direction) {
    const visibleAnswers = Array.from(elements.answerItems).filter(item => 
        item.style.display !== 'none' && item.offsetParent !== null
    );
    
    if (visibleAnswers.length === 0) return;
    
    const currentFocused = document.querySelector('.answer-item.focused');
    let currentIndex = currentFocused ? visibleAnswers.indexOf(currentFocused) : -1;
    
    // Remove current focus
    if (currentFocused) {
        currentFocused.classList.remove('focused');
    }
    
    // Calculate new index
    if (direction === 'up') {
        currentIndex = currentIndex <= 0 ? visibleAnswers.length - 1 : currentIndex - 1;
    } else {
        currentIndex = currentIndex >= visibleAnswers.length - 1 ? 0 : currentIndex + 1;
    }
    
    // Focus new item
    const newFocused = visibleAnswers[currentIndex];
    if (newFocused) {
        newFocused.classList.add('focused');
        newFocused.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// ==============================================
// TOOLTIPS
// ==============================================

function initializeTooltips() {
    // Add tooltips to filter buttons
    elements.filterButtons.forEach((btn, index) => {
        const shortcuts = ['Ctrl+1', 'Ctrl+2', 'Ctrl+3'];
        const originalTitle = btn.textContent.trim();
        btn.title = `${originalTitle} (${shortcuts[index]})`;
        
        // Initialize Bootstrap tooltip
        if (typeof bootstrap !== 'undefined') {
            new bootstrap.Tooltip(btn);
        }
    });
    
    // Add tooltips to answer items
    elements.answerItems.forEach(item => {
        const points = item.getAttribute('data-points');
        const time = item.getAttribute('data-time');
        
        if (points || time) {
            let tooltipText = '';
            if (points) tooltipText += `Points: ${points}`;
            if (time) tooltipText += (tooltipText ? ' | ' : '') + `Time: ${time}s`;
            
            item.title = tooltipText;
            
            if (typeof bootstrap !== 'undefined') {
                new bootstrap.Tooltip(item);
            }
        }
    });
    
    console.log('💡 Tooltips initialized');
}

// ==============================================
// EXPORT FUNCTIONALITY
// ==============================================

function exportCategoryResults() {
    const categoryName = '{{ $category->name ?? "Unknown" }}';
    const candidateName = '{{ $schedule->candidates->name ?? "Unknown" }}';
    
    // Collect data
    const exportData = {
        candidate: {
            name: candidateName,
            email: '{{ $schedule->candidates->email ?? "" }}',
            position: '{{ $schedule->candidates->jobs->title ?? "N/A" }}'
        },
        category: {
            name: categoryName,
            type: '{{ $category->type ?? "standard" }}',
            description: '{{ $category->description ?? "" }}'
        },
        performance: {
            total_questions: totalAnswers,
            correct_answers: correctAnswers,
            incorrect_answers: incorrectAnswers,
            accuracy_percentage: totalAnswers > 0 ? Math.round((correctAnswers / totalAnswers) * 100) : 0
        },
        export_date: new Date().toISOString(),
        filter_applied: currentFilter
    };
    
    // Create and download JSON file
    const dataStr = JSON.stringify(exportData, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(dataBlob);
    
    const link = document.createElement('a');
    link.href = url;
    link.download = `${categoryName.replace(/\s+/g, '_')}_${candidateName.replace(/\s+/g, '_')}_results.json`;
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    // Show success message
    showNotification('Export completed successfully!', 'success');
    
    console.log('📤 Export completed:', exportData);
}

// ==============================================
// UTILITY FUNCTIONS
// ==============================================

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    notification.innerHTML = `
        <i class="ti ti-check-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// ==============================================
// EVENT LISTENERS
// ==============================================

// Custom event listeners
document.addEventListener('filterChanged', function(e) {
    console.log('🔍 Filter changed:', e.detail);
});

// Window resize handler
window.addEventListener('resize', debounce(function() {
    // Recalculate layouts if needed
    console.log('📱 Window resized');
}, 250));

// Page visibility change
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        console.log('👁️ Page hidden');
    } else {
        console.log('👁️ Page visible');
        // Restart animations if needed
        if (!animationPlayed) {
            initializeAnimations();
            animationPlayed = true;
        }
    }
});

// Before unload warning
window.addEventListener('beforeunload', function(e) {
    // Only show warning if user made changes or spent significant time
    const timeSpent = Date.now() - window.performance.timing.navigationStart;
    if (timeSpent > 30000) { // 30 seconds
        e.preventDefault();
        e.returnValue = '';
    }
});

// ==============================================
// DEBUG FUNCTIONS (Development Only)
// ==============================================

// Debug function untuk development
window.debugCategoryResults = function() {
    console.log('=== 🔍 DEBUG CATEGORY RESULTS ===');
    console.log('Current Filter:', currentFilter);
    console.log('Statistics:', {
        total: totalAnswers,
        correct: correctAnswers,
        incorrect: incorrectAnswers,
        percentage: totalAnswers > 0 ? Math.round((correctAnswers / totalAnswers) * 100) : 0
    });
    console.log('Cached Elements:', elements);
    console.log('Visible Answers:', 
        Array.from(elements.answerItems).filter(item => 
            item.style.display !== 'none' && item.offsetParent !== null
        ).length
    );
    console.log('================================');
};

// Performance monitoring
window.categoryResultsPerformance = {
    start: performance.now(),
    markMilestone: function(name) {
        console.log(`⚡ ${name}: ${(performance.now() - this.start).toFixed(2)}ms`);
    }
};

// Console welcome message
console.log(`
🎯 Category Results View Loaded Successfully!

Available Debug Commands:
- debugCategoryResults() - Show current state
- filterAnswers('all'|'correct'|'incorrect') - Change filter
- exportCategoryResults() - Export data

Keyboard Shortcuts:
- Ctrl+1: Show all answers
- Ctrl+2: Show correct answers only  
- Ctrl+3: Show incorrect answers only
- ↑/↓: Navigate through answers
- ESC: Close modals
`);

// Mark initialization complete
window.categoryResultsPerformance.markMilestone('Initialization Complete');
</script>
@endpush