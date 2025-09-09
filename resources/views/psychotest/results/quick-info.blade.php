{{-- resources/views/psychotest/results/quick-info.blade.php --}}
<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3">{{ __('Candidate Information') }}</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>{{ __('Name') }}:</strong></td>
                <td>{{ $schedule->candidates->name }}</td>
            </tr>
            <tr>
                <td><strong>{{ __('Email') }}:</strong></td>
                <td>{{ $schedule->candidates->email }}</td>
            </tr>
            <tr>
                <td><strong>{{ __('Position') }}:</strong></td>
                <td>{{ $schedule->candidates->jobs->title ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>{{ __('Test Date') }}:</strong></td>
                <td>{{ $schedule->start_time->format('d M Y H:i') }}</td>
            </tr>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary mb-3">{{ __('Test Results') }}</h6>
        @if($schedule->result)
            <div class="text-center mb-3">
                <div class="display-6 fw-bold text-success">{{ $schedule->result->percentage }}%</div>
                <div class="badge bg-primary fs-6">{{ __('Grade') }} {{ $schedule->result->grade }}</div>
            </div>
            
            @if($performanceMetrics)
                <div class="mb-2">
                    <strong>{{ __('Decision') }}:</strong>
                    <span class="badge bg-info">{{ $performanceMetrics['decision_status'] }}</span>
                </div>
                <div class="mb-2">
                    <strong>{{ __('Risk Level') }}:</strong>
                    <span class="badge bg-warning">{{ $performanceMetrics['risk_level'] }}</span>
                </div>
            @endif
        @else
            <div class="alert alert-warning">{{ __('Test not completed yet') }}</div>
        @endif
    </div>
</div>

@if(!empty($fieldBreakdown))
<hr>
<div class="row">
    <div class="col-12">
        <h6 class="text-primary mb-3">{{ __('Field Test Breakdown') }}</h6>
        <div class="row text-center">
            <div class="col-4">
                <div class="border rounded p-2">
                    <div class="fw-bold text-primary">{{ $fieldBreakdown['audit'] }}%</div>
                    <small class="text-muted">{{ __('Audit') }}</small>
                </div>
            </div>
            <div class="col-4">
                <div class="border rounded p-2">
                    <div class="fw-bold text-success">{{ $fieldBreakdown['accounting'] }}%</div>
                    <small class="text-muted">{{ __('Accounting') }}</small>
                </div>
            </div>
            <div class="col-4">
                <div class="border rounded p-2">
                    <div class="fw-bold text-warning">{{ $fieldBreakdown['tax'] }}%</div>
                    <small class="text-muted">{{ __('Tax') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<hr>
<div class="text-center">
    <a href="{{ route('psychotest-result.show', $schedule->id) }}" class="btn btn-primary">
        <i class="ti ti-eye me-2"></i>{{ __('View Full Details') }}
    </a>
    @if($schedule->status == 'completed' && $schedule->result)
        <a href="{{ route('psychotest-result.export', [$schedule->id, 'pdf']) }}" class="btn btn-success">
            <i class="ti ti-download me-2"></i>{{ __('Export PDF') }}
        </a>
    @endif
</div>