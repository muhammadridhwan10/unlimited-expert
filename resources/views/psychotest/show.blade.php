@extends('layouts.admin')
@section('page-title')
    {{__('Psychotest Schedule Details')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('psychotest-schedule.index')}}">{{__('Psychotest Schedule')}}</a></li>
    <li class="breadcrumb-item">{{__('Details')}}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Schedule Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Schedule Information')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>{{__('Candidate')}}:</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-group me-2">
                                                <a href="#" class="avatar rounded-circle avatar-sm">
                                                    <img src="{{asset('/storage/uploads/avatar/avatar.png')}}" class="hweb">
                                                </a>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $schedule->candidates->name ?? 'N/A' }}</h6>
                                                <small class="text-muted">{{ $schedule->candidates->email ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Job Position')}}:</strong></td>
                                    <td>
                                        @if($schedule->candidates && $schedule->candidates->jobs)
                                            <span class="badge bg-primary">{{ $schedule->candidates->jobs->title }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Username')}}:</strong></td>
                                    <td><code class="bg-light p-1 rounded">{{ $schedule->username }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Work Period')}}:</strong></td>
                                    <td>
                                        @if($schedule->started_at && $schedule->completed_at)
                                            <div class="small">
                                                <i class="ti ti-play text-success me-1"></i><strong>Started:</strong> {{ $schedule->started_at->format('d M Y H:i:s') }}<br>
                                                <i class="ti ti-flag text-danger me-1"></i><strong>Completed:</strong> {{ $schedule->completed_at->format('d M Y H:i:s') }}<br>
                                                <i class="ti ti-clock text-info me-1"></i><strong>Duration:</strong> 
                                                @php
                                                    $duration = $schedule->started_at->diffInMinutes($schedule->completed_at);
                                                    $hours = floor($duration / 60);
                                                    $minutes = $duration % 60;
                                                @endphp
                                                @if($hours > 0)
                                                    {{ $hours }}h {{ $minutes }}m
                                                @else
                                                    {{ $minutes }}m
                                                @endif
                                                <span class="badge bg-light text-dark ms-1">{{ $duration }} minutes</span>
                                            </div>
                                        @elseif($schedule->started_at)
                                            <div class="small">
                                                <i class="ti ti-play text-success me-1"></i><strong>Started:</strong> {{ $schedule->started_at->format('d M Y H:i:s') }}<br>
                                                <span class="badge bg-warning">{{__('In Progress')}}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">{{__('Not started yet')}}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Test Categories')}}:</strong></td>
                                    <td>
                                        @if($categories && $categories->count() > 0)
                                            @foreach($categories as $category)
                                                <span class="badge bg-info me-1 mb-1">{{ $category->name }}</span>
                                            @endforeach
                                            <br><small class="text-muted">{{ $categories->count() }} category(ies) assigned</small>
                                        @else
                                            <span class="text-muted">No categories assigned</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>{{__('Status')}}:</strong></td>
                                    <td>
                                        @if($schedule->status == 'scheduled')
                                            <span class="badge bg-info p-2">{{__('Scheduled')}}</span>
                                        @elseif($schedule->status == 'in_progress')
                                            <span class="badge bg-warning p-2">{{__('In Progress')}}</span>
                                        @elseif($schedule->status == 'completed')
                                            <span class="badge bg-success p-2">{{__('Completed')}}</span>
                                        @elseif($schedule->status == 'expired')
                                            <span class="badge bg-danger p-2">{{__('Expired')}}</span>
                                        @else
                                            <span class="badge bg-secondary p-2">{{__('Cancelled')}}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Schedule Period')}}:</strong></td>
                                    <td>
                                        <div class="small">
                                            <i class="ti ti-calendar me-1"></i><strong>Start:</strong> {{ $schedule->start_time ? $schedule->start_time->format('d M Y H:i') : 'N/A' }}<br>
                                            <i class="ti ti-calendar me-1"></i><strong>End:</strong> {{ $schedule->end_time ? $schedule->end_time->format('d M Y H:i') : 'N/A' }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Duration')}}:</strong></td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $schedule->duration_minutes }} {{__('minutes')}}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Progress')}}:</strong></td>
                                    <td>
                                        @php
                                            $progressPercentage = method_exists($schedule, 'getProgressPercentage') ? $schedule->getProgressPercentage() : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: {{ $progressPercentage }}%">
                                                {{ $progressPercentage }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Results -->
            @if($schedule->result)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>{{__('Test Results')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h3>{{ $schedule->result->percentage ?? '0' }}%</h3>
                                        <small>{{__('Overall Score')}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h3>{{ $schedule->result->grade ?? 'N/A' }}</h3>
                                        <small>{{__('Grade')}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h3>{{ $schedule->result->answered_questions ?? 0 }}/{{ $schedule->result->total_questions ?? 0 }}</h3>
                                        <small>{{__('Questions Answered')}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h3>{{ $schedule->result->earned_points ?? 0 }}/{{ $schedule->result->total_points ?? 0 }}</h3>
                                        <small>{{__('Points Earned')}}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($schedule->result->notes)
                            <div class="mt-4">
                                <h6>{{__('Assessment Report')}}</h6>
                                @php
                                    // Parse JSON notes if it's a string
                                    $notes = $schedule->result->notes;
                                    if (is_string($notes)) {
                                        $decodedNotes = json_decode($notes, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedNotes)) {
                                            $notes = $decodedNotes;
                                        }
                                    }
                                @endphp
                                
                                @if(is_array($notes))
                                    <!-- Recommendations Section -->
                                    @if(isset($notes['recommendations']) && is_array($notes['recommendations']))
                                        <div class="alert alert-success">
                                            <h6><i class="ti ti-bulb me-2"></i>{{__('Recommendations')}}</h6>
                                            <ul class="mb-0">
                                                @foreach($notes['recommendations'] as $recommendation)
                                                    <li>{{ $recommendation }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Final Assessment -->
                                    @if(isset($notes['final_assessment']))
                                        <div class="row">
                                            @if(isset($notes['final_assessment']['overall_rating']))
                                                <div class="col-md-6 mb-3">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h6><i class="ti ti-star me-2"></i>{{__('Overall Rating')}}</h6>
                                                            <span class="badge bg-primary fs-6">{{ $notes['final_assessment']['overall_rating'] }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            @if(isset($notes['final_assessment']['strengths']) && is_array($notes['final_assessment']['strengths']))
                                                <div class="col-md-6 mb-3">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h6><i class="ti ti-thumb-up me-2 text-success"></i>{{__('Strengths')}}</h6>
                                                            @foreach($notes['final_assessment']['strengths'] as $strength)
                                                                <span class="badge bg-success me-1 mb-1">{{ $strength }}</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        @if(isset($notes['final_assessment']['areas_for_improvement']) && is_array($notes['final_assessment']['areas_for_improvement']))
                                            <div class="alert alert-warning">
                                                <h6><i class="ti ti-alert-triangle me-2"></i>{{__('Areas for Improvement')}}</h6>
                                                <ul class="mb-0">
                                                    @foreach($notes['final_assessment']['areas_for_improvement'] as $area)
                                                        <li>{{ $area }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        @if(isset($notes['final_assessment']['position_fit']))
                                            <div class="card mt-3">
                                                <div class="card-body">
                                                    <h6><i class="ti ti-briefcase me-2"></i>{{__('Position Fit Analysis')}}</h6>
                                                    @if(isset($notes['final_assessment']['position_fit']['division']))
                                                        <p><strong>Division:</strong> {{ $notes['final_assessment']['position_fit']['division'] }}</p>
                                                    @endif
                                                    @if(isset($notes['final_assessment']['position_fit']['position_type']) && is_array($notes['final_assessment']['position_fit']['position_type']))
                                                        <p><strong>Recommended Position Types:</strong></p>
                                                        @foreach($notes['final_assessment']['position_fit']['position_type'] as $type)
                                                            <span class="badge bg-info me-1 mb-1">{{ $type }}</span>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endif

                                    <!-- Field Recommendations -->
                                    @if(isset($notes['field_recommendations']))
                                        <div class="card mt-3">
                                            <div class="card-body">
                                                <h6><i class="ti ti-target me-2"></i>{{__('Field Analysis')}}</h6>
                                                @if(isset($notes['field_recommendations']['recommended_field']))
                                                    <p><strong>{{__('Recommended Field')}}:</strong> 
                                                        <span class="badge bg-primary">{{ ucfirst($notes['field_recommendations']['recommended_field']) }}</span>
                                                    </p>
                                                @endif
                                                
                                                @if(isset($notes['field_recommendations']['field_percentages']) && is_array($notes['field_recommendations']['field_percentages']))
                                                    <div class="mt-3">
                                                        <h6>{{__('Field Scores')}}</h6>
                                                        @foreach($notes['field_recommendations']['field_percentages'] as $field => $percentage)
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span>{{ ucfirst($field) }}</span>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="progress me-2" style="width: 100px; height: 20px;">
                                                                        <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                                                                    </div>
                                                                    <span class="badge bg-light text-dark">{{ $percentage }}%</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Kraeplin Analysis -->
                                    @if(isset($notes['kraeplin_analysis']))
                                        <div class="card mt-3">
                                            <div class="card-body">
                                                <h6><i class="ti ti-activity me-2"></i>{{__('Kraeplin Test Analysis')}}</h6>
                                                @if(isset($notes['kraeplin_analysis']['grade']))
                                                    <p><strong>{{__('Grade')}}:</strong> 
                                                        <span class="badge bg-secondary fs-6">{{ $notes['kraeplin_analysis']['grade'] }}</span>
                                                    </p>
                                                @endif
                                                
                                                @if(isset($notes['kraeplin_analysis']['scores']) && is_array($notes['kraeplin_analysis']['scores']))
                                                    <div class="row">
                                                        @foreach($notes['kraeplin_analysis']['scores'] as $scoreType => $score)
                                                            <div class="col-md-3 mb-2">
                                                                <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $scoreType)) }}</small>
                                                                <div class="progress" style="height: 15px;">
                                                                    <div class="progress-bar" style="width: {{ $score }}%">{{ number_format($score, 1) }}%</div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                
                                                @if(isset($notes['kraeplin_analysis']['interpretation']) && is_array($notes['kraeplin_analysis']['interpretation']))
                                                    <div class="mt-3">
                                                        <strong>{{__('Interpretation')}}:</strong>
                                                        <ul class="mb-0 mt-2">
                                                            @foreach($notes['kraeplin_analysis']['interpretation'] as $interpretation)
                                                                <li><small>{{ $interpretation }}</small></li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Personality Insights -->
                                    @if(isset($notes['personality_insights']))
                                        <div class="card mt-3">
                                            <div class="card-body">
                                                <h6><i class="ti ti-user me-2"></i>{{__('Personality Insights')}}</h6>
                                                @if(isset($notes['personality_insights']['personality_profile']))
                                                    <p>{{ $notes['personality_insights']['personality_profile'] }}</p>
                                                @endif
                                                
                                                @if(isset($notes['personality_insights']['position_recommendation']) && is_array($notes['personality_insights']['position_recommendation']))
                                                    <p><strong>{{__('Position Recommendations')}}:</strong></p>
                                                    @foreach($notes['personality_insights']['position_recommendation'] as $recommendation)
                                                        <span class="badge bg-light text-dark me-1 mb-1">{{ $recommendation }}</span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                @else
                                    <!-- Fallback for simple text notes -->
                                    <div class="alert alert-info">
                                        <pre style="white-space: pre-wrap; margin: 0;">{{ $schedule->result->notes }}</pre>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($schedule->status == 'completed')
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle me-2"></i>
                            {{__('Test completed but results are not available yet. Results may still be processing.')}}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Test Sessions Progress -->
            @if($schedule->sessions && $schedule->sessions->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>{{__('Test Sessions Progress')}}</h5>
                    </div>
                    <div class="card-body">
                        @foreach($schedule->sessions as $session)
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                <div>
                                    <h6 class="mb-1">{{ $session->category->name ?? 'Unknown Category' }}</h6>
                                    <small class="text-muted">{{ $session->category->description ?? '' }}</small>
                                </div>
                                <div class="text-end">
                                    @if($session->status == 'completed')
                                        <span class="badge bg-success">{{__('Completed')}}</span>
                                        @if($session->completed_at)
                                            <br><small class="text-muted">{{ $session->completed_at->format('d M Y H:i') }}</small>
                                        @endif
                                    @elseif($session->status == 'in_progress')
                                        <span class="badge bg-warning">{{__('In Progress')}}</span>
                                        @if($session->started_at)
                                            <br><small class="text-muted">Started: {{ $session->started_at->format('d M Y H:i') }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">{{__('Pending')}}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Detailed Answers -->
            @if($schedule->answers && $schedule->answers->count() > 0)
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>{{__('Detailed Answers')}}</h5>
                        <small class="text-muted">{{ $schedule->answers->count() }} answer(s)</small>
                    </div>
                    <div class="card-body">
                        @foreach($schedule->answers as $index => $answer)
                            <div class="mb-4 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-1">
                                        <span class="badge bg-light text-dark me-2">#{{ $index + 1 }}</span>
                                        {{ $answer->question->title ?? 'Question ' . ($index + 1) }}
                                    </h6>
                                    <span class="badge bg-{{ ($answer->points_earned ?? 0) > 0 ? 'success' : 'danger' }}">
                                        {{ $answer->points_earned ?? 0 }}/{{ $answer->question->points ?? 0 }} pts
                                    </span>
                                </div>
                                
                                @if($answer->question)
                                    <p class="text-muted mb-2">{{ $answer->question->question ?? 'No question text available' }}</p>
                                    
                                    @if($answer->question->type == 'multiple_choice' || $answer->question->type == 'true_false')
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>{{__('Answer')}}:</strong> 
                                                <span class="badge bg-light text-dark">{{ $answer->answer ?? 'No answer' }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>{{__('Correct Answer')}}:</strong> 
                                                <span class="badge bg-success">{{ $answer->question->correct_answer ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    @elseif($answer->question->type == 'rating_scale')
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>{{__('Rating Given')}}:</strong> {{ $answer->answer ?? 'N/A' }}
                                                @if($answer->question->options && is_array($answer->question->options))
                                                    /{{ max($answer->question->options) }}
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <strong>{{__('Expected Rating')}}:</strong> {{ $answer->question->correct_answer ?? 'N/A' }}
                                            </div>
                                        </div>
                                    @elseif($answer->question->type == 'kraeplin')
                                        <div class="mt-2">
                                            <strong>{{__('Kraeplin Answers')}}:</strong>
                                            @if($answer->kraeplin_answers && is_array($answer->kraeplin_answers))
                                                <div class="bg-light p-2 rounded mt-1">
                                                    <small>{{ count($answer->kraeplin_answers) }} answers submitted</small>
                                                </div>
                                            @else
                                                <div class="bg-light p-2 rounded mt-1">
                                                    <small class="text-muted">No kraeplin answers data</small>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="mt-2">
                                            <strong>{{__('Answer')}}:</strong>
                                            <div class="bg-light p-2 rounded mt-1">
                                                {{ $answer->answer ?? 'No answer provided' }}
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-warning">
                                        <small>Question data not available</small>
                                    </div>
                                @endif
                                
                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="ti ti-clock me-1"></i>
                                        {{__('Answered at')}}: {{ $answer->answered_at ? $answer->answered_at->format('d M Y H:i:s') : 'N/A' }}
                                    </small>
                                    @if($answer->time_taken_seconds)
                                        <small class="text-muted">
                                            <i class="ti ti-timer me-1"></i>
                                            Time taken: {{ $answer->time_taken_seconds }} seconds
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
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
                    @if($schedule->status == 'scheduled')
                        <a href="{{ route('psychotest-schedule.edit', $schedule->id) }}" class="btn btn-info btn-sm w-100 mb-2">
                            <i class="ti ti-pencil"></i> {{__('Edit Schedule')}}
                        </a>

                        <a href="{{ route('psychotest-schedule.resend-email', $schedule->id) }}" class="btn btn-warning btn-sm w-100 mb-2">
                            <i class="ti ti-mail"></i> {{__('Resend Email')}}
                        </a>

                        <a href="{{ route('psychotest-schedule.cancel', $schedule->id) }}" 
                           class="btn btn-secondary btn-sm w-100 mb-2 bs-pass-para"
                           data-confirm="{{__('Are You Sure?').'|'.__('This will cancel the psychotest schedule.')}}">
                            <i class="ti ti-ban"></i> {{__('Cancel Test')}}
                        </a>
                    @endif

                    {!! Form::open(['method' => 'DELETE', 'route' => ['psychotest-schedule.destroy', $schedule->id],'id'=>'delete-form-'.$schedule->id]) !!}
                    <a href="#" class="btn btn-danger btn-sm w-100 bs-pass-para" 
                       data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" 
                       data-confirm-yes="document.getElementById('delete-form-{{$schedule->id}}').submit();">
                        <i class="ti ti-trash"></i> {{__('Delete')}}
                    </a>
                    {!! Form::close() !!}
                </div>
            </div>

            <!-- Test Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>{{__('Test Information')}}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>{{__('Email Sent')}}:</strong>
                        @if($schedule->email_sent)
                            <span class="badge bg-success"><i class="ti ti-check"></i> {{__('Yes')}}</span>
                        @else
                            <span class="badge bg-danger"><i class="ti ti-x"></i> {{__('No')}}</span>
                        @endif
                    </div>

                    @if($schedule->started_at)
                        <div class="mb-3">
                            <strong>{{__('Started At')}}:</strong><br>
                            <small class="text-muted">{{ $schedule->started_at->format('d M Y H:i:s') }}</small>
                        </div>
                    @endif

                    @if($schedule->completed_at)
                        <div class="mb-3">
                            <strong>{{__('Completed At')}}:</strong><br>
                            <small class="text-muted">{{ $schedule->completed_at->format('d M Y H:i:s') }}</small>
                        </div>

                        @if($schedule->started_at)
                            <div class="mb-3">
                                <strong>{{__('Duration Taken')}}:</strong><br>
                                <small class="text-muted">{{ $schedule->started_at->diffInMinutes($schedule->completed_at) }} {{__('minutes')}}</small>
                            </div>
                        @endif
                    @endif

                    @if($schedule->creator)
                        <div class="mb-3">
                            <strong>{{__('Created By')}}:</strong><br>
                            <small class="text-muted">{{ $schedule->creator->name ?? 'N/A' }}</small>
                        </div>
                    @endif

                    <div class="mb-3">
                        <strong>{{__('Created At')}}:</strong><br>
                        <small class="text-muted">{{ $schedule->created_at ? $schedule->created_at->format('d M Y H:i:s') : 'N/A' }}</small>
                    </div>

                    @if($schedule->updated_at && $schedule->updated_at != $schedule->created_at)
                        <div class="mb-3">
                            <strong>{{__('Last Updated')}}:</strong><br>
                            <small class="text-muted">{{ $schedule->updated_at->format('d M Y H:i:s') }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Test Statistics -->
            @if($schedule->result || $schedule->answers->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>{{__('Test Statistics')}}</h5>
                    </div>
                    <div class="card-body">
                        @if($schedule->result && isset($schedule->result->total_time_spent_seconds))
                            <div class="mb-3">
                                <strong>{{__('Total Time Spent')}}:</strong><br>
                                <small class="text-muted">{{ floor($schedule->result->total_time_spent_seconds / 60) }} minutes {{ $schedule->result->total_time_spent_seconds % 60 }} seconds</small>
                            </div>
                        @endif

                        <div class="mb-3">
                            <strong>{{__('Answers Submitted')}}:</strong><br>
                            <small class="text-muted">{{ $schedule->answers->count() }} answer(s)</small>
                        </div>

                        @if($categories && $categories->count() > 0)
                            <div class="mb-3">
                                <strong>{{__('Categories Assigned')}}:</strong><br>
                                <small class="text-muted">{{ $categories->count() }} category(ies)</small>
                            </div>
                        @endif

                        @if($schedule->sessions && $schedule->sessions->count() > 0)
                            <div class="mb-3">
                                <strong>{{__('Sessions Progress')}}:</strong><br>
                                @php
                                    $completedSessions = $schedule->sessions->where('status', 'completed')->count();
                                    $totalSessions = $schedule->sessions->count();
                                @endphp
                                <small class="text-muted">{{ $completedSessions }}/{{ $totalSessions }} completed</small>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection