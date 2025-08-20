@extends('layouts.admin')

@section('page-title')
    {{ __('Psychotest Results Detail') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('psychotest-result.index') }}">{{ __('Psychotest Results') }}</a></li>
    <li class="breadcrumb-item">{{ __('Detail') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('psychotest-result.index') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-arrow-left"></i> {{ __('Back to Results') }}
        </a>
        @if($schedule->status == 'completed' && $schedule->result)
            <a href="{{ route('psychotest-result.export', [$schedule->id, 'pdf']) }}" class="btn btn-sm btn-success">
                <i class="ti ti-download"></i> {{ __('Export PDF') }}
            </a>
        @endif
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Candidate Information -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Candidate Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar avatar-xl mb-3">
                            <span class="avatar-text fs-2">{{ substr($schedule->candidates->name, 0, 2) }}</span>
                        </div>
                        <h5 class="mb-1">{{ $schedule->candidates->name }}</h5>
                        <p class="text-muted">{{ $schedule->candidates->email }}</p>
                    </div>

                    <div class="info-list">
                        <div class="info-item d-flex justify-content-between py-2">
                            <span class="text-muted">{{ __('Position Applied') }}:</span>
                            <span class="fw-bold">{{ $schedule->candidates->jobs->title ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2">
                            <span class="text-muted">{{ __('Test Date') }}:</span>
                            <span>{{ $schedule->start_time->format('d M Y') }}</span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2">
                            <span class="text-muted">{{ __('Test Time') }}:</span>
                            <span>{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}</span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2">
                            <span class="text-muted">{{ __('Status') }}:</span>
                            <span>
                                @if($schedule->status == 'completed')
                                    <span class="badge bg-success">{{ __('Completed') }}</span>
                                @elseif($schedule->status == 'in_progress')
                                    <span class="badge bg-warning">{{ __('In Progress') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __(ucfirst($schedule->status)) }}</span>
                                @endif
                            </span>
                        </div>
                        @if($performanceMetrics)
                            <div class="info-item d-flex justify-content-between py-2">
                                <span class="text-muted">{{ __('Total Time') }}:</span>
                                <span>{{ $performanceMetrics['total_time'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($performanceMetrics)
                <!-- Overall Performance -->
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Overall Performance') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <svg width="120" height="120" class="circular-progress">
                                    <circle cx="60" cy="60" r="50" stroke="#e9ecef" stroke-width="8" fill="none"></circle>
                                    <circle cx="60" cy="60" r="50" stroke="#28a745" stroke-width="8" fill="none"
                                            stroke-dasharray="{{ 2 * 3.14159 * 50 }}"
                                            stroke-dashoffset="{{ 2 * 3.14159 * 50 * (1 - $performanceMetrics['overall_score'] / 100) }}"
                                            style="transition: stroke-dashoffset 1s ease-in-out;"></circle>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle text-center">
                                    <h3 class="mb-0">{{ $performanceMetrics['overall_score'] }}%</h3>
                                    <small class="text-muted">Overall Score</small>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mb-3">
                            <span class="badge badge-lg 
                                @if($performanceMetrics['grade'] == 'A') bg-success
                                @elseif($performanceMetrics['grade'] == 'B') bg-info
                                @elseif($performanceMetrics['grade'] == 'C') bg-warning
                                @else bg-danger
                                @endif">
                                Grade {{ $performanceMetrics['grade'] }}
                            </span>
                        </div>

                        <div class="recommendation-box p-3 bg-light rounded">
                            <h6 class="text-primary">{{ __('Recommendation') }}</h6>
                            <p class="mb-0 text-sm">{{ $performanceMetrics['recommendation'] }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Test Results by Category -->
        <div class="col-lg-8">
            @if($performanceMetrics && (count($performanceMetrics['strengths']) > 0 || count($performanceMetrics['weaknesses']) > 0))
                <!-- Strengths and Weaknesses -->
                <div class="row mb-4">
                    @if(count($performanceMetrics['strengths']) > 0)
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="ti ti-check-circle me-2"></i>{{ __('Strengths') }}</h6>
                                </div>
                                <div class="card-body">
                                    @foreach($performanceMetrics['strengths'] as $strength)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-sm">{{ $strength['category'] }}</span>
                                            <span class="badge bg-success">{{ $strength['score'] }}%</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(count($performanceMetrics['weaknesses']) > 0)
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-white">
                                    <h6 class="mb-0"><i class="ti ti-alert-circle me-2"></i>{{ __('Areas for Improvement') }}</h6>
                                </div>
                                <div class="card-body">
                                    @foreach($performanceMetrics['weaknesses'] as $weakness)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-sm">{{ $weakness['category'] }}</span>
                                            <span class="badge bg-warning">{{ $weakness['score'] }}%</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Category Results -->
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Results by Category') }}</h5>
                </div>
                <div class="card-body">
                    @if($sessionResults && count($sessionResults) > 0)
                        @foreach($sessionResults as $result)
                            <div class="category-result-item mb-4 p-3 border rounded">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-1">{{ $result['category']->name }}</h6>
                                        <small class="text-muted">{{ $result['category']->description }}</small>
                                        <div class="mt-2">
                                            @if($result['status'] == 'completed')
                                                <span class="badge bg-success">{{ __('Completed') }}</span>
                                            @elseif($result['status'] == 'in_progress')
                                                <span class="badge bg-warning">{{ __('In Progress') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __(ucfirst($result['status'])) }}</span>
                                            @endif

                                            @if($result['category']->type)
                                                <span class="badge bg-info ms-1">{{ ucfirst($result['category']->type) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="mb-1">
                                                <span class="h5 mb-0">{{ $result['percentage'] }}%</span>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar 
                                                    @if($result['percentage'] >= 80) bg-success
                                                    @elseif($result['percentage'] >= 60) bg-warning
                                                    @else bg-danger
                                                    @endif" 
                                                    style="width: {{ $result['percentage'] }}%">
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                {{ $result['answered_questions'] }}/{{ $result['total_questions'] }} {{ __('Questions') }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-end">
                                            <div class="mb-1">
                                                <small class="text-muted">{{ __('Time Spent') }}:</small><br>
                                                <span class="text-sm">{{ $result['time_spent_formatted'] }}</span>
                                            </div>
                                            <div class="mb-1">
                                                <small class="text-muted">{{ __('Points') }}:</small><br>
                                                <span class="text-sm">{{ $result['earned_points'] }}/{{ $result['total_points'] }}</span>
                                            </div>
                                            <div>
                                                <a href="{{ route('psychotest-result.category', [$schedule->id, $result['category']->id]) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-eye"></i> {{ __('View Details') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($result['category']->isKraeplin() && isset($result['kraeplin_score']))
                                    <div class="mt-3 p-2 bg-light rounded">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <small class="text-muted">{{ __('Kraeplin Score') }}:</small>
                                                <span class="fw-bold">{{ $result['kraeplin_score'] }}%</span>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted">{{ __('Completed Columns') }}:</small>
                                                <span class="fw-bold">{{ $result['completed_columns'] ?? 0 }}</span>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted">{{ __('Test Type') }}:</small>
                                                <span class="badge bg-warning">Kraeplin</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($result['category']->isEPPS() && isset($result['completion_rate']))
                                    <div class="mt-3 p-2 bg-light rounded">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">{{ __('Completion Rate') }}:</small>
                                                <span class="fw-bold">{{ $result['completion_rate'] }}%</span>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">{{ __('Test Type') }}:</small>
                                                <span class="badge bg-info">EPPS Personality</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-clipboard-off fs-1 text-muted"></i>
                            <h5 class="mt-3">{{ __('No Category Results') }}</h5>
                            <p class="text-muted">{{ __('No test results available for individual categories.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<style>
.circular-progress {
    transform: rotate(-90deg);
}

.circular-progress circle {
    transition: stroke-dashoffset 1s ease-in-out;
}

.category-result-item {
    transition: all 0.3s ease;
}

.category-result-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.info-list .info-item {
    border-bottom: 1px solid #f1f3f4;
}

.info-list .info-item:last-child {
    border-bottom: none;
}

.recommendation-box {
    border-left: 4px solid #007bff;
}

.badge-lg {
    padding: 0.5rem 1rem;
    font-size: 1rem;
}
</style>

<script>
    // Animate progress bars on page load
    document.addEventListener('DOMContentLoaded', function() {
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.transition = 'width 1s ease-in-out';
                bar.style.width = width;
            }, 100);
        });
    });
</script>
@endpush