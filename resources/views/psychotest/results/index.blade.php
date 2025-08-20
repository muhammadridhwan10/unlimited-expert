@extends('layouts.admin')

@section('page-title')
    {{ __('Psychotest Results') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Psychotest Results') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('psychotest-schedule.index') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-arrow-left"></i> {{ __('Back to Schedule') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Statistics Cards -->
        <div class="col-xl-3 col-6">
            <div class="card comp-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-20">{{ __('Total Tests') }}</h6>
                            <h3 class="text-primary">{{ $stats['total_tests'] }}</h3>
                        </div>
                        <div class="col-auto">
                            <i class="bg-primary-light ti ti-clipboard-list"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-6">
            <div class="card comp-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-20">{{ __('Completed') }}</h6>
                            <h3 class="text-success">{{ $stats['completed_tests'] }}</h3>
                        </div>
                        <div class="col-auto">
                            <i class="bg-success-light ti ti-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-6">
            <div class="card comp-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-20">{{ __('Completion Rate') }}</h6>
                            <h3 class="text-info">{{ $stats['completion_rate'] }}%</h3>
                        </div>
                        <div class="col-auto">
                            <i class="bg-info-light ti ti-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-6">
            <div class="card comp-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-20">{{ __('Average Score') }}</h6>
                            <h3 class="text-warning">{{ $stats['average_score'] }}%</h3>
                        </div>
                        <div class="col-auto">
                            <i class="bg-warning-light ti ti-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <h5>{{ __('Psychotest Results') }}</h5>
                        </div>
                        <div class="col-lg-6">
                            <!-- Filter Form -->
                            <form method="GET" action="{{ route('psychotest-result.index') }}" class="d-flex gap-2">
                                <input type="text" name="candidate_search" class="form-control form-control-sm" 
                                       placeholder="{{ __('Search candidate...') }}" value="{{ request('candidate_search') }}">
                                
                                <select name="job_filter" class="form-select form-select-sm">
                                    <option value="">{{ __('All Jobs') }}</option>
                                    @foreach($jobs as $job)
                                        <option value="{{ $job->id }}" {{ request('job_filter') == $job->id ? 'selected' : '' }}>
                                            {{ $job->title }}
                                        </option>
                                    @endforeach
                                </select>
                                
                                <select name="status_filter" class="form-select form-select-sm">
                                    <option value="">{{ __('All Status') }}</option>
                                    <option value="completed" {{ request('status_filter') == 'completed' ? 'selected' : '' }}>
                                        {{ __('Completed') }}
                                    </option>
                                    <option value="in_progress" {{ request('status_filter') == 'in_progress' ? 'selected' : '' }}>
                                        {{ __('In Progress') }}
                                    </option>
                                </select>
                                
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="ti ti-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Candidate') }}</th>
                                    <th>{{ __('Job Position') }}</th>
                                    <th>{{ __('Test Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Overall Score') }}</th>
                                    <th>{{ __('Grade') }}</th>
                                    <th>{{ __('Categories Completed') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-text">{{ substr($schedule->candidates->name, 0, 2) }}</span>
                                                </div>
                                                <div>
                                                    <h6 class="m-0">{{ $schedule->candidates->name }}</h6>
                                                    <small class="text-muted">{{ $schedule->candidates->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $schedule->candidates->jobs->title ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <small class="text-muted">{{ $schedule->start_time->format('d M Y') }}</small><br>
                                                <small>{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($schedule->status == 'completed')
                                                <span class="badge bg-success">{{ __('Completed') }}</span>
                                            @elseif($schedule->status == 'in_progress')
                                                <span class="badge bg-warning">{{ __('In Progress') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __(ucfirst($schedule->status)) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->result)
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 60px; height: 6px;">
                                                        <div class="progress-bar 
                                                            @if($schedule->result->percentage >= 80) bg-success
                                                            @elseif($schedule->result->percentage >= 60) bg-warning
                                                            @else bg-danger
                                                            @endif" 
                                                            style="width: {{ $schedule->result->percentage }}%">
                                                        </div>
                                                    </div>
                                                    <span class="text-sm">{{ $schedule->result->percentage }}%</span>
                                                </div>
                                            @else
                                                <span class="text-muted">{{ __('Not completed') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->result)
                                                <span class="badge 
                                                    @if($schedule->result->grade == 'A') bg-success
                                                    @elseif($schedule->result->grade == 'B') bg-info
                                                    @elseif($schedule->result->grade == 'C') bg-warning
                                                    @else bg-danger
                                                    @endif">
                                                    {{ $schedule->result->grade }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $completedSessions = $schedule->sessions()->where('status', 'completed')->count();
                                                $totalSessions = $schedule->sessions()->count();
                                            @endphp
                                            <span class="text-sm">{{ $completedSessions }}/{{ $totalSessions }}</span>
                                            @if($totalSessions > 0)
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <div class="progress-bar bg-primary" style="width: {{ ($completedSessions / $totalSessions) * 100 }}%"></div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="{{ route('psychotest-result.show', $schedule->id) }}" 
                                                   class="mx-3 btn btn-sm align-items-center" 
                                                   data-bs-toggle="tooltip" title="{{ __('View Details') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            
                                            @if($schedule->status == 'completed' && $schedule->result)
                                                <div class="action-btn bg-success ms-2">
                                                    <a href="{{ route('psychotest-result.export', [$schedule->id, 'pdf']) }}" 
                                                       class="mx-3 btn btn-sm align-items-center"
                                                       data-bs-toggle="tooltip" title="{{ __('Export PDF') }}">
                                                        <i class="ti ti-download text-white"></i>
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="py-4">
                                                <i class="ti ti-clipboard-off fs-1 text-muted"></i>
                                                <h5 class="mt-3">{{ __('No Results Found') }}</h5>
                                                <p class="text-muted">{{ __('No psychotest results available with the current filters.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($schedules->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $schedules->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Compare Modal -->
    <div class="modal fade" id="compareModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Compare Results') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('psychotest-result.compare') }}">
                    @csrf
                    <div class="modal-body">
                        <p>{{ __('Select candidates to compare their psychotest results:') }}</p>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>{{ __('Candidate') }}</th>
                                        <th>{{ __('Job') }}</th>
                                        <th>{{ __('Score') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules as $schedule)
                                        @if($schedule->status == 'completed' && $schedule->result)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="schedule_ids[]" 
                                                           value="{{ $schedule->id }}" class="form-check-input candidate-checkbox">
                                                </td>
                                                <td>{{ $schedule->candidates->name }}</td>
                                                <td>{{ $schedule->candidates->jobs->title ?? 'N/A' }}</td>
                                                <td>{{ $schedule->result->percentage }}%</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" id="compareBtn" disabled>{{ __('Compare Selected') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    // Compare functionality
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const candidateCheckboxes = document.querySelectorAll('.candidate-checkbox');
        const compareBtn = document.getElementById('compareBtn');

        // Select all functionality
        selectAll.addEventListener('change', function() {
            candidateCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateCompareButton();
        });

        // Individual checkbox change
        candidateCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateCompareButton();
                
                // Update select all state
                const checkedCount = document.querySelectorAll('.candidate-checkbox:checked').length;
                selectAll.checked = checkedCount === candidateCheckboxes.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < candidateCheckboxes.length;
            });
        });

        function updateCompareButton() {
            const checkedCount = document.querySelectorAll('.candidate-checkbox:checked').length;
            compareBtn.disabled = checkedCount < 2;
            compareBtn.textContent = checkedCount >= 2 ? 
                `Compare ${checkedCount} Candidates` : 
                'Select at least 2 candidates';
        }
    });

    // Tooltip initialization
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endpush