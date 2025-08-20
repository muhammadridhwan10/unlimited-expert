@extends('layouts.admin')

@section('page-title')
    {{ __('Compare Psychotest Results') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('psychotest-result.index') }}">{{ __('Psychotest Results') }}</a></li>
    <li class="breadcrumb-item">{{ __('Compare') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('psychotest-result.index') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-arrow-left"></i> {{ __('Back to Results') }}
        </a>
        <button class="btn btn-sm btn-success" onclick="exportComparison()">
            <i class="ti ti-download"></i> {{ __('Export Comparison') }}
        </button>
    </div>
@endsection

@section('content')
    <!-- Comparison Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">{{ __('Comparison Overview') }}</h5>
                            <small class="text-muted">{{ __('Comparing') }} {{ count($schedules) }} {{ __('candidates') }}</small>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary active" onclick="switchView('overview')">
                                    <i class="ti ti-chart-bar me-1"></i>{{ __('Overview') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="switchView('detailed')">
                                    <i class="ti ti-list-details me-1"></i>{{ __('Detailed') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="switchView('radar')">
                                    <i class="ti ti-radar me-1"></i>{{ __('Radar Chart') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Candidates Summary -->
                    <div class="candidates-summary mb-4">
                        <div class="row">
                            @foreach($comparison as $index => $candidate)
                                <div class="col-lg-{{ 12 / count($comparison) }} col-md-6 mb-3">
                                    <div class="candidate-card p-3 border rounded-3 h-100 
                                        {{ $index == 0 ? 'border-success bg-success-subtle' : 
                                           ($index == 1 ? 'border-primary bg-primary-subtle' : 
                                           ($index == 2 ? 'border-warning bg-warning-subtle' : 'border-info bg-info-subtle')) }}">
                                        <div class="text-center">
                                            <div class="avatar avatar-lg mb-3">
                                                <span class="avatar-text fs-3">{{ substr($candidate['candidate']->name, 0, 2) }}</span>
                                            </div>
                                            <h6 class="mb-1">{{ $candidate['candidate']->name }}</h6>
                                            <small class="text-muted">{{ $candidate['candidate']->jobs->title ?? 'N/A' }}</small>
                                            
                                            <div class="mt-3">
                                                <div class="score-circle mx-auto mb-2" style="width: 80px; height: 80px; position: relative;">
                                                    <svg width="80" height="80" class="circular-progress">
                                                        <circle cx="40" cy="40" r="35" stroke="#e9ecef" stroke-width="6" fill="none"></circle>
                                                        <circle cx="40" cy="40" r="35" 
                                                                stroke="{{ $index == 0 ? '#28a745' : ($index == 1 ? '#007bff' : ($index == 2 ? '#ffc107' : '#17a2b8')) }}" 
                                                                stroke-width="6" fill="none"
                                                                stroke-dasharray="{{ 2 * 3.14159 * 35 }}"
                                                                stroke-dashoffset="{{ 2 * 3.14159 * 35 * (1 - $candidate['overall_score'] / 100) }}"
                                                                style="transition: stroke-dashoffset 1.5s ease-in-out; transform: rotate(-90deg); transform-origin: 50% 50%;"></circle>
                                                    </svg>
                                                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                                                        <div class="fs-6 fw-bold">{{ $candidate['overall_score'] }}%</div>
                                                    </div>
                                                </div>
                                                <span class="badge badge-lg
                                                    {{ $candidate['grade'] == 'A' ? 'bg-success' : 
                                                       ($candidate['grade'] == 'B' ? 'bg-info' : 
                                                       ($candidate['grade'] == 'C' ? 'bg-warning' : 'bg-danger')) }}">
                                                    Grade {{ $candidate['grade'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview View -->
    <div id="overview-view">
        <!-- Overall Ranking -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Overall Ranking') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="ranking-table">
                            @php
                                $sortedCandidates = collect($comparison)->sortByDesc('overall_score')->values();
                            @endphp
                            @foreach($sortedCandidates as $index => $candidate)
                                <div class="ranking-item d-flex align-items-center p-3 mb-2 rounded-2
                                    {{ $index == 0 ? 'bg-success-subtle border-success' : 
                                       ($index == 1 ? 'bg-info-subtle border-info' : 
                                       ($index == 2 ? 'bg-warning-subtle border-warning' : 'bg-light border')) }} border">
                                    
                                    <div class="rank-number me-3">
                                        <span class="badge 
                                            {{ $index == 0 ? 'bg-success' : 
                                               ($index == 1 ? 'bg-info' : 
                                               ($index == 2 ? 'bg-warning' : 'bg-secondary')) }}"
                                              style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                            @if($index == 0)
                                                <i class="ti ti-trophy"></i>
                                            @elseif($index == 1)
                                                <i class="ti ti-medal"></i>
                                            @elseif($index == 2)
                                                <i class="ti ti-award"></i>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <div class="candidate-info flex-grow-1">
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <h6 class="mb-1">{{ $candidate['candidate']->name }}</h6>
                                                <small class="text-muted">{{ $candidate['candidate']->jobs->title ?? 'N/A' }}</small>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="progress mb-1" style="height: 8px;">
                                                    <div class="progress-bar 
                                                        {{ $index == 0 ? 'bg-success' : 
                                                           ($index == 1 ? 'bg-info' : 
                                                           ($index == 2 ? 'bg-warning' : 'bg-secondary')) }}" 
                                                         style="width: {{ $candidate['overall_score'] }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $candidate['overall_score'] }}% Overall Score</small>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <span class="badge 
                                                    {{ $candidate['grade'] == 'A' ? 'bg-success' : 
                                                       ($candidate['grade'] == 'B' ? 'bg-info' : 
                                                       ($candidate['grade'] == 'C' ? 'bg-warning' : 'bg-danger')) }} me-2">
                                                    Grade {{ $candidate['grade'] }}
                                                </span>
                                                <small class="text-muted">
                                                    {{ count($candidate['sessions']) }} {{ __('categories completed') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Comparison -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Category Comparison') }}</h5>
                        <small class="text-muted">{{ __('Performance comparison across different test categories') }}</small>
                    </div>
                    <div class="card-body">
                        @php
                            $allCategories = collect($comparison)->flatMap(function($candidate) {
                                return collect($candidate['sessions'])->pluck('category.name');
                            })->unique()->values();
                        @endphp

                        @foreach($allCategories as $categoryName)
                            <div class="category-comparison mb-4">
                                <h6 class="mb-3">{{ $categoryName }}</h6>
                                <div class="comparison-bars">
                                    @foreach($comparison as $index => $candidate)
                                        @php
                                            $categorySession = collect($candidate['sessions'])->firstWhere('category.name', $categoryName);
                                            $score = $categorySession ? $categorySession['percentage'] : 0;
                                            $status = $categorySession ? $categorySession['status'] : 'not_taken';
                                        @endphp
                                        <div class="comparison-bar-item d-flex align-items-center mb-2">
                                            <div class="candidate-name" style="width: 150px;">
                                                <small class="fw-bold">{{ $candidate['candidate']->name }}</small>
                                            </div>
                                            <div class="progress flex-grow-1 mx-3" style="height: 10px;">
                                                @if($status === 'completed')
                                                    <div class="progress-bar 
                                                        {{ $score >= 80 ? 'bg-success' : 
                                                           ($score >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                                         style="width: {{ $score }}%"></div>
                                                @else
                                                    <div class="progress-bar bg-secondary" style="width: 100%; opacity: 0.3;"></div>
                                                @endif
                                            </div>
                                            <div class="score-display text-end" style="width: 80px;">
                                                @if($status === 'completed')
                                                    <span class="fw-bold">{{ $score }}%</span>
                                                @else
                                                    <span class="text-muted">{{ __('Not taken') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed View -->
    <div id="detailed-view" style="display: none;">
        <div class="row">
            @foreach($comparison as $index => $candidate)
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-text">{{ substr($candidate['candidate']->name, 0, 2) }}</span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $candidate['candidate']->name }}</h6>
                                    <small class="text-muted">{{ $candidate['candidate']->jobs->title ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Overall Score -->
                            <div class="overall-score mb-4 text-center">
                                <div class="score-display mb-2">
                                    <span class="fs-2 fw-bold text-primary">{{ $candidate['overall_score'] }}%</span>
                                </div>
                                <span class="badge 
                                    {{ $candidate['grade'] == 'A' ? 'bg-success' : 
                                       ($candidate['grade'] == 'B' ? 'bg-info' : 
                                       ($candidate['grade'] == 'C' ? 'bg-warning' : 'bg-danger')) }}">
                                    Grade {{ $candidate['grade'] }}
                                </span>
                            </div>

                            <!-- Category Breakdown -->
                            <div class="category-breakdown">
                                <h6 class="mb-3">{{ __('Category Performance') }}</h6>
                                @foreach($candidate['sessions'] as $session)
                                    <div class="category-item mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-sm fw-bold">{{ $session['category']->name }}</span>
                                            <span class="badge 
                                                @if($session['status'] == 'completed')
                                                    {{ $session['percentage'] >= 80 ? 'bg-success' : 
                                                       ($session['percentage'] >= 60 ? 'bg-warning' : 'bg-danger') }}
                                                @else
                                                    bg-secondary
                                                @endif">
                                                @if($session['status'] == 'completed')
                                                    {{ $session['percentage'] }}%
                                                @else
                                                    {{ ucfirst($session['status']) }}
                                                @endif
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            @if($session['status'] == 'completed')
                                                <div class="progress-bar 
                                                    {{ $session['percentage'] >= 80 ? 'bg-success' : 
                                                       ($session['percentage'] >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                                     style="width: {{ $session['percentage'] }}%"></div>
                                            @else
                                                <div class="progress-bar bg-secondary" style="width: 100%; opacity: 0.3;"></div>
                                            @endif
                                        </div>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                {{ $session['answered_questions'] }}/{{ $session['total_questions'] }} questions
                                                @if($session['time_spent_formatted'])
                                                    • {{ $session['time_spent_formatted'] }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Action Buttons -->
                            <div class="text-center mt-4">
                                <a href="{{ route('psychotest-result.show', $candidate['candidate']->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye me-1"></i>{{ __('View Details') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Radar Chart View -->
    <div id="radar-view" style="display: none;">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Radar Chart Comparison') }}</h5>
                        <small class="text-muted">{{ __('Visual comparison of performance across categories') }}</small>
                    </div>
                    <div class="card-body">
                        <!-- Radar Chart Canvas -->
                        <div class="radar-chart-container text-center">
                            <canvas id="radarChart" width="800" height="400"></canvas>
                        </div>

                        <!-- Legend -->
                        <div class="chart-legend mt-4">
                            <div class="row justify-content-center">
                                @foreach($comparison as $index => $candidate)
                                    <div class="col-auto">
                                        <div class="legend-item d-flex align-items-center">
                                            <div class="legend-color me-2" 
                                                 style="width: 20px; height: 20px; border-radius: 3px; 
                                                        background: {{ $index == 0 ? '#28a745' : ($index == 1 ? '#007bff' : ($index == 2 ? '#ffc107' : '#17a2b8')) }};"></div>
                                            <span class="text-sm">{{ $candidate['candidate']->name }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Strengths & Weaknesses Comparison -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Strengths & Weaknesses Analysis') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($comparison as $index => $candidate)
                            <div class="col-lg-{{ 12 / count($comparison) }} mb-4">
                                <div class="analysis-card">
                                    <h6 class="mb-3">{{ $candidate['candidate']->name }}</h6>
                                    
                                    @php
                                        $strengths = collect($candidate['sessions'])->where('percentage', '>=', 80);
                                        $weaknesses = collect($candidate['sessions'])->where('percentage', '<', 60);
                                    @endphp

                                    <!-- Strengths -->
                                    @if($strengths->count() > 0)
                                        <div class="strengths mb-3">
                                            <h6 class="text-success">
                                                <i class="ti ti-check-circle me-2"></i>{{ __('Strengths') }}
                                            </h6>
                                            @foreach($strengths as $strength)
                                                <div class="strength-item d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-sm">{{ $strength['category']->name }}</span>
                                                    <span class="badge bg-success">{{ $strength['percentage'] }}%</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Weaknesses -->
                                    @if($weaknesses->count() > 0)
                                        <div class="weaknesses">
                                            <h6 class="text-warning">
                                                <i class="ti ti-alert-circle me-2"></i>{{ __('Areas for Improvement') }}
                                            </h6>
                                            @foreach($weaknesses as $weakness)
                                                <div class="weakness-item d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-sm">{{ $weakness['category']->name }}</span>
                                                    <span class="badge bg-warning">{{ $weakness['percentage'] }}%</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($strengths->count() == 0 && $weaknesses->count() == 0)
                                        <div class="text-center text-muted">
                                            <i class="ti ti-minus-circle"></i>
                                            <small>{{ __('Balanced performance across categories') }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style-page')
<style>
.candidate-card {
    transition: all 0.3s ease;
}

.candidate-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.circular-progress {
    transform: rotate(-90deg);
}

.ranking-item {
    transition: all 0.3s ease;
}

.ranking-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.rank-number .badge {
    font-size: 1rem;
}

.comparison-bar-item {
    padding: 5px 0;
}

.progress {
    border-radius: 10px;
}

.badge-lg {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.score-circle {
    position: relative;
}

.chart-legend {
    padding: 20px 0;
}

.legend-item {
    margin: 0 15px;
}

.analysis-card {
    padding: 20px;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    height: 100%;
}

.strength-item, .weakness-item {
    padding: 5px 0;
    border-bottom: 1px solid #f8f9fa;
}

.strength-item:last-child, .weakness-item:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .candidate-name {
        width: 100px !important;
        font-size: 12px;
    }
    
    .score-display {
        width: 60px !important;
    }
    
    .btn-group .btn {
        font-size: 12px;
        padding: 5px 10px;
    }
}
</style>
@endpush

@push('script-page')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let radarChart;
const candidateData = @json($comparison);

document.addEventListener('DOMContentLoaded', function() {
    // Initialize radar chart
    initializeRadarChart();
    
    // Animate progress bars and circles
    setTimeout(() => {
        animateProgressElements();
    }, 500);
});

function switchView(viewType) {
    // Hide all views
    document.getElementById('overview-view').style.display = 'none';
    document.getElementById('detailed-view').style.display = 'none';
    document.getElementById('radar-view').style.display = 'none';
    
    // Show selected view
    document.getElementById(viewType + '-view').style.display = 'block';
    
    // Update button states
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active', 'btn-primary', 'btn-info', 'btn-success');
        if (btn.onclick.toString().includes("'overview'")) {
            btn.classList.add('btn-outline-primary');
        } else if (btn.onclick.toString().includes("'detailed'")) {
            btn.classList.add('btn-outline-info');
        } else if (btn.onclick.toString().includes("'radar'")) {
            btn.classList.add('btn-outline-success');
        }
    });
    
    // Set active button
    const activeButton = document.querySelector(`button[onclick="switchView('${viewType}')"]`);
    if (activeButton) {
        activeButton.classList.add('active');
        if (viewType === 'overview') {
            activeButton.classList.remove('btn-outline-primary');
            activeButton.classList.add('btn-primary');
        } else if (viewType === 'detailed') {
            activeButton.classList.remove('btn-outline-info');
            activeButton.classList.add('btn-info');
        } else if (viewType === 'radar') {
            activeButton.classList.remove('btn-outline-success');
            activeButton.classList.add('btn-success');
        }
    }
    
    // Update radar chart if switching to radar view
    if (viewType === 'radar') {
        setTimeout(() => {
            if (radarChart) {
                radarChart.update();
            }
        }, 100);
    }
}

function initializeRadarChart() {
    const ctx = document.getElementById('radarChart').getContext('2d');
    
    // Get all unique categories
    const allCategories = [];
    candidateData.forEach(candidate => {
        candidate.sessions.forEach(session => {
            if (!allCategories.includes(session.category.name)) {
                allCategories.push(session.category.name);
            }
        });
    });
    
    // Prepare datasets
    const datasets = candidateData.map((candidate, index) => {
        const colors = ['rgba(40, 167, 69, 0.8)', 'rgba(0, 123, 255, 0.8)', 'rgba(255, 193, 7, 0.8)', 'rgba(23, 162, 184, 0.8)'];
        const borderColors = ['#28a745', '#007bff', '#ffc107', '#17a2b8'];
        
        const data = allCategories.map(categoryName => {
            const session = candidate.sessions.find(s => s.category.name === categoryName);
            return session ? session.percentage : 0;
        });
        
        return {
            label: candidate.candidate.name,
            data: data,
            backgroundColor: colors[index] || 'rgba(108, 117, 125, 0.8)',
            borderColor: borderColors[index] || '#6c757d',
            borderWidth: 2,
            pointBackgroundColor: borderColors[index] || '#6c757d',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: borderColors[index] || '#6c757d'
        };
    });
    
    radarChart = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: allCategories,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        stepSize: 20,
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    },
                    angleLines: {
                        color: 'rgba(0,0,0,0.1)'
                    },
                    pointLabels: {
                        font: {
                            size: 12
                        },
                        callback: function(label) {
                            // Truncate long labels
                            return label.length > 15 ? label.substring(0, 15) + '...' : label;
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false // We have custom legend
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.r + '%';
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });
}

function animateProgressElements() {
    // Animate progress bars
    document.querySelectorAll('.progress-bar').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        bar.style.transition = 'width 1s ease-in-out';
        setTimeout(() => {
            bar.style.width = width;
        }, Math.random() * 500 + 100);
    });
    
    // Animate circular progress
    document.querySelectorAll('.circular-progress circle').forEach((circle, index) => {
        if (index % 2 === 1) { // Only progress circles, not background
            const dashOffset = circle.style.strokeDashoffset;
            circle.style.strokeDashoffset = circle.getAttribute('stroke-dasharray');
            setTimeout(() => {
                circle.style.strokeDashoffset = dashOffset;
            }, Math.random() * 500 + 200);
        }
    });
}

function exportComparison() {
    // Prepare export data
    const exportData = {
        candidates: candidateData.map(candidate => ({
            name: candidate.candidate.name,
            email: candidate.candidate.email,
            position: candidate.candidate.jobs?.title || 'N/A',
            overall_score: candidate.overall_score,
            grade: candidate.grade,
            categories: candidate.sessions.map(session => ({
                category: session.category.name,
                score: session.percentage,
                status: session.status,
                questions_answered: session.answered_questions,
                total_questions: session.total_questions,
                time_spent: session.time_spent_formatted
            }))
        })),
        comparison_date: new Date().toISOString().split('T')[0],
        total_candidates: candidateData.length
    };
    
    // For now, show alert with export info
    // In production, you would implement actual export functionality
    alert(`Comparison export prepared for ${candidateData.length} candidates.\n\nData includes:\n- Overall scores\n- Category breakdowns\n- Performance analysis\n\nImplement actual export logic here.`);
    
    // Example: Create downloadable JSON
    const dataStr = JSON.stringify(exportData, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `psychotest_comparison_${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.altKey) {
        switch(e.key) {
            case '1':
                e.preventDefault();
                switchView('overview');
                break;
            case '2':
                e.preventDefault();
                switchView('detailed');
                break;
            case '3':
                e.preventDefault();
                switchView('radar');
                break;
        }
    }
});

// Add tooltips for keyboard shortcuts
document.querySelectorAll('.btn-group .btn').forEach((btn, index) => {
    const shortcuts = ['Alt+1', 'Alt+2', 'Alt+3'];
    btn.title = btn.textContent.trim() + ' (' + shortcuts[index] + ')';
});

// Performance insights
function generateInsights() {
    const insights = [];
    
    // Find top performer
    const topPerformer = candidateData.reduce((prev, current) => 
        (prev.overall_score > current.overall_score) ? prev : current
    );
    insights.push(`${topPerformer.candidate.name} achieved the highest overall score of ${topPerformer.overall_score}%`);
    
    // Find category leaders
    const categoryLeaders = {};
    candidateData.forEach(candidate => {
        candidate.sessions.forEach(session => {
            const categoryName = session.category.name;
            if (!categoryLeaders[categoryName] || categoryLeaders[categoryName].score < session.percentage) {
                categoryLeaders[categoryName] = {
                    name: candidate.candidate.name,
                    score: session.percentage
                };
            }
        });
    });
    
    Object.entries(categoryLeaders).forEach(([category, leader]) => {
        insights.push(`${leader.name} leads in ${category} with ${leader.score}%`);
    });
    
    return insights;
}

// Display insights on page load
document.addEventListener('DOMContentLoaded', function() {
    const insights = generateInsights();
    console.log('Comparison Insights:', insights);
});

// Print functionality
function printComparison() {
    window.print();
}

// Add print styles
const printStyles = `
@media print {
    .btn, .card-header .col-auto, .float-end {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        margin-bottom: 20px !important;
    }
    
    .candidate-card {
        background: #f8f9fa !important;
        border: 1px solid #000 !important;
    }
    
    .progress-bar {
        background: #000 !important;
    }
    
    .badge {
        border: 1px solid #000 !important;
    }
    
    #radar-view {
        page-break-before: always;
    }
}
`;

const styleSheet = document.createElement('style');
styleSheet.textContent = printStyles;
document.head.appendChild(styleSheet);
</script>
@endpush

@endsection