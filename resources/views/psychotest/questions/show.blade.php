@extends('layouts.admin')
@section('page-title')
    {{__('Question Details')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('psychotest-question.index')}}">{{__('Questions')}}</a></li>
    <li class="breadcrumb-item">{{__('Details')}}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Question Details -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Question Information')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>{{__('Title')}}:</strong></td>
                                    <td>{{ $question->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Category')}}:</strong></td>
                                    <td>
                                        @if($question->category)
                                            <span class="badge bg-primary">{{ $question->category->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Type')}}:</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ \App\Models\PsychotestQuestion::$types[$question->type] ?? $question->type }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Points')}}:</strong></td>
                                    <td>
                                        <span class="badge bg-success">{{ $question->points }} points</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>{{__('Status')}}:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $question->is_active ? 'success' : 'danger' }} p-2">
                                            {{ $question->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Order')}}:</strong></td>
                                    <td>{{ $question->order }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Time Limit')}}:</strong></td>
                                    <td>
                                        @if($question->time_limit_seconds)
                                            <span class="badge bg-warning">{{ $question->time_limit_seconds }}s</span>
                                        @else
                                            <span class="text-muted">{{__('No limit')}}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Usage Count')}}:</strong></td>
                                    <td>
                                        @php
                                            $answerCount = $question->answers()->count();
                                        @endphp
                                        <span class="badge bg-{{ $answerCount > 0 ? 'info' : 'light' }} text-{{ $answerCount > 0 ? 'white' : 'dark' }}">
                                            {{ $answerCount }} {{ $answerCount == 1 ? 'answer' : 'answers' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Question Content -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>{{__('Question Content')}}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>{{__('Question')}}</h6>
                        <div class="p-3 bg-light rounded">
                            <p class="mb-0">{!! nl2br(e($question->question)) !!}</p>
                        </div>
                    </div>

                    <!-- Question Image -->
                    @if($question->image && $question->hasImage())
                        <div class="mb-4">
                            <h6>{{__('Question Image')}}</h6>
                            <div class="text-center">
                                <img src="{{ $question->getImageUrl() }}" alt="Question Image" class="img-fluid rounded border" style="max-width: 100%; max-height: 400px;">
                            </div>
                        </div>
                    @endif

                    <!-- Options and Answers -->
                    @if($question->type === 'multiple_choice' || $question->type === 'image_choice')
                        <div class="mb-4">
                            <h6>{{__('Options')}}</h6>
                            @if($question->options && is_array($question->options))
                                <div class="row">
                                    @foreach($question->options as $index => $option)
                                        <div class="col-md-6 mb-2">
                                            <div class="d-flex align-items-center p-2 border rounded {{ $option === $question->correct_answer ? 'bg-success text-white' : 'bg-light' }}">
                                                <span class="badge bg-{{ $option === $question->correct_answer ? 'warning' : 'secondary' }} me-2">
                                                    {{ chr(65 + $index) }}
                                                </span>
                                                <span>{{ $option }}</span>
                                                @if($option === $question->correct_answer)
                                                    <i class="ti ti-check ms-auto"></i>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">{{__('No options available')}}</p>
                            @endif
                        </div>
                    @elseif($question->type === 'true_false')
                        <div class="mb-4">
                            <h6>{{__('True/False Question')}}</h6>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center p-2 border rounded {{ 'True' === $question->correct_answer ? 'bg-success text-white' : 'bg-light' }}">
                                        <span class="badge bg-{{ 'True' === $question->correct_answer ? 'warning' : 'secondary' }} me-2">A</span>
                                        <span>True</span>
                                        @if('True' === $question->correct_answer)
                                            <i class="ti ti-check ms-auto"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center p-2 border rounded {{ 'False' === $question->correct_answer ? 'bg-success text-white' : 'bg-light' }}">
                                        <span class="badge bg-{{ 'False' === $question->correct_answer ? 'warning' : 'secondary' }} me-2">B</span>
                                        <span>False</span>
                                        @if('False' === $question->correct_answer)
                                            <i class="ti ti-check ms-auto"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($question->type === 'rating_scale')
                        <div class="mb-4">
                            <h6>{{__('Rating Scale')}}</h6>
                            @if($question->options && is_array($question->options))
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($question->options as $rating)
                                        <div class="p-2 border rounded {{ $rating == $question->correct_answer ? 'bg-success text-white' : 'bg-light' }}">
                                            <span class="fw-bold">{{ $rating }}</span>
                                            @if($rating == $question->correct_answer)
                                                <i class="ti ti-check ms-1"></i>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    {{__('Correct/Expected Rating')}}: <strong>{{ $question->correct_answer ?? 'N/A' }}</strong>
                                </small>
                            @endif
                        </div>
                    @elseif($question->type === 'kraeplin')
                        <div class="mb-4">
                            <h6>{{__('Kraeplin Test Configuration')}}</h6>
                            @if($question->kraeplin_data && is_array($question->kraeplin_data))
                                <div class="alert alert-info">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>{{__('Columns')}}:</strong> {{ $question->kraeplin_data['columns'] ?? 10 }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>{{__('Rows per Column')}}:</strong> {{ $question->kraeplin_data['rows_per_column'] ?? 50 }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>{{__('Time per Column')}}:</strong> {{ $question->kraeplin_data['time_per_column'] ?? 30 }}s
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Sample Kraeplin Data Preview -->
                                @if(isset($question->kraeplin_data['sample_data']) && is_array($question->kraeplin_data['sample_data']))
                                    <div class="mt-3">
                                        <h6>{{__('Sample Data Preview')}}</h6>
                                        <div class="row">
                                            @foreach($question->kraeplin_data['sample_data'] as $colIndex => $columnData)
                                                <div class="col-md-4">
                                                    <div class="card bg-light">
                                                        <div class="card-header text-center">
                                                            <small>Column {{ $colIndex + 1 }}</small>
                                                        </div>
                                                        <div class="card-body">
                                                            @foreach(array_slice($columnData, 0, 5) as $rowData)
                                                                <div class="d-flex justify-content-center mb-1">
                                                                    <span class="me-2">{{ $rowData['num1'] }}</span>
                                                                    <span class="me-2">+</span>
                                                                    <span class="me-2">{{ $rowData['num2'] }}</span>
                                                                    <span class="me-2">=</span>
                                                                    <span class="fw-bold text-success">{{ $rowData['sum'] }}</span>
                                                                </div>
                                                            @endforeach
                                                            @if(count($columnData) > 5)
                                                                <div class="text-center">
                                                                    <small class="text-muted">... and {{ count($columnData) - 5 }} more</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @elseif($question->type === 'essay')
                        <div class="mb-4">
                            <div class="alert alert-light">
                                <i class="ti ti-pencil me-2"></i>
                                {{__('This is an essay question. Candidates will provide written answers that need manual evaluation.')}}
                            </div>
                        </div>
                    @endif

                    <!-- Correct Answer Summary -->
                    @if(in_array($question->type, ['multiple_choice', 'image_choice', 'true_false', 'rating_scale']) && $question->correct_answer)
                        <div class="alert alert-success">
                            <strong><i class="ti ti-check-circle me-2"></i>{{__('Correct Answer')}}:</strong> {{ $question->correct_answer }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Question Statistics -->
            @if($question->answers()->exists())
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>{{__('Question Statistics')}}</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $totalAnswers = $question->answers()->count();
                            $correctAnswers = 0;
                            $averageTime = 0;
                            
                            if ($question->type !== 'essay' && $question->correct_answer) {
                                $correctAnswers = $question->answers()->where('answer', $question->correct_answer)->count();
                            }
                            
                            $avgTimeQuery = $question->answers()->whereNotNull('time_taken_seconds');
                            if ($avgTimeQuery->exists()) {
                                $averageTime = round($avgTimeQuery->avg('time_taken_seconds'), 1);
                            }
                        @endphp
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h3>{{ $totalAnswers }}</h3>
                                        <small>{{__('Total Answers')}}</small>
                                    </div>
                                </div>
                            </div>
                            @if($question->type !== 'essay' && $question->correct_answer)
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $correctAnswers }}</h3>
                                            <small>{{__('Correct Answers')}}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0 }}%</h3>
                                            <small>{{__('Success Rate')}}</small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($averageTime > 0)
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $averageTime }}s</h3>
                                            <small>{{__('Avg. Time')}}</small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Answer Distribution (for multiple choice) -->
                        @if(in_array($question->type, ['multiple_choice', 'image_choice', 'true_false']) && $question->options)
                            <div class="mt-4">
                                <h6>{{__('Answer Distribution')}}</h6>
                                @foreach($question->options as $option)
                                    @php
                                        $optionCount = $question->answers()->where('answer', $option)->count();
                                        $percentage = $totalAnswers > 0 ? round(($optionCount / $totalAnswers) * 100, 1) : 0;
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>{{ $option }} @if($option === $question->correct_answer)<i class="ti ti-check text-success"></i>@endif</span>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 100px; height: 20px;">
                                                <div class="progress-bar bg-{{ $option === $question->correct_answer ? 'success' : 'info' }}" 
                                                     style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <span class="badge bg-light text-dark">{{ $optionCount }} ({{ $percentage }}%)</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Actions Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Actions')}}</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('psychotest-question.edit', $question->id) }}" class="btn btn-info btn-sm w-100 mb-2">
                        <i class="ti ti-pencil"></i> {{__('Edit Question')}}
                    </a>

                    <a href="{{ route('psychotest-question.toggle-status', $question->id) }}" 
                       class="btn btn-{{ $question->is_active ? 'warning' : 'success' }} btn-sm w-100 mb-2">
                        <i class="ti ti-toggle-{{ $question->is_active ? 'right' : 'left' }}"></i> 
                        {{ $question->is_active ? __('Deactivate') : __('Activate') }}
                    </a>

                    @if(!$question->answers()->exists())
                        {!! Form::open(['method' => 'DELETE', 'route' => ['psychotest-question.destroy', $question->id],'id'=>'delete-form-'.$question->id]) !!}
                        <a href="#" class="btn btn-danger btn-sm w-100 bs-pass-para" 
                           data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" 
                           data-confirm-yes="document.getElementById('delete-form-{{$question->id}}').submit();">
                            <i class="ti ti-trash"></i> {{__('Delete')}}
                        </a>
                        {!! Form::close() !!}
                    @else
                        <button class="btn btn-danger btn-sm w-100" disabled>
                            <i class="ti ti-lock"></i> {{__('Cannot Delete')}}
                        </button>
                        <small class="text-muted">{{__('Question is used in tests')}}</small>
                    @endif
                </div>
            </div>

            <!-- Question Metadata -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>{{__('Question Metadata')}}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>{{__('Created By')}}:</strong><br>
                        <small class="text-muted">{{ $question->creator->name ?? 'N/A' }}</small>
                    </div>

                    <div class="mb-3">
                        <strong>{{__('Created At')}}:</strong><br>
                        <small class="text-muted">{{ $question->created_at ? $question->created_at->format('d M Y H:i:s') : 'N/A' }}</small>
                    </div>

                    @if($question->updated_at && $question->updated_at != $question->created_at)
                        <div class="mb-3">
                            <strong>{{__('Last Updated')}}:</strong><br>
                            <small class="text-muted">{{ $question->updated_at->format('d M Y H:i:s') }}</small>
                        </div>
                    @endif

                    <div class="mb-3">
                        <strong>{{__('Category Details')}}:</strong><br>
                        @if($question->category)
                            <small class="text-muted">
                                {{ $question->category->name }}<br>
                                <span class="badge bg-light text-dark">{{ $question->category->type ?? 'standard' }}</span>
                            </small>
                        @else
                            <small class="text-muted">No category assigned</small>
                        @endif
                    </div>

                    @if($question->image)
                        <div class="mb-3">
                            <strong>{{__('Has Image')}}:</strong><br>
                            <span class="badge bg-success">{{__('Yes')}}</span>
                        </div>
                    @endif

                    <div class="mb-3">
                        <strong>{{__('Question ID')}}:</strong><br>
                        <small class="text-muted">#{{ $question->id }}</small>
                    </div>
                </div>
            </div>

            <!-- Related Questions -->
            @if($question->category)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>{{__('Related Questions')}}</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $relatedQuestions = $question->category->questions()
                                ->where('id', '!=', $question->id)
                                ->where('is_active', true)
                                ->orderBy('order')
                                ->limit(5)
                                ->get();
                        @endphp

                        @if($relatedQuestions->count() > 0)
                            @foreach($relatedQuestions as $related)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                    <div>
                                        <small class="fw-bold">{{ Str::limit($related->title, 30) }}</small><br>
                                        <small class="text-muted">{{ $related->points }} pts</small>
                                    </div>
                                    <a href="{{ route('psychotest-question.show', $related->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </div>
                            @endforeach
                            
                            <div class="text-center mt-3">
                                <a href="{{ route('psychotest-question.index', ['category_id' => $question->category->id]) }}" class="btn btn-sm btn-primary">
                                    {{__('View All in Category')}}
                                </a>
                            </div>
                        @else
                            <p class="text-muted small">{{__('No other questions in this category')}}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('script-page')
<script>
$(document).ready(function() {
    // Add some interactive features if needed
    $('.badge').hover(function() {
        $(this).addClass('shadow-sm');
    }, function() {
        $(this).removeClass('shadow-sm');
    });
});
</script>
@endpush