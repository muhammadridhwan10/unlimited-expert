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
                            <table class="table">
                                <tr>
                                    <td><strong>{{__('Candidate')}}:</strong></td>
                                    <td>{{ $schedule->candidates->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Email')}}:</strong></td>
                                    <td>{{ $schedule->candidates->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Job Position')}}:</strong></td>
                                    <td>{{ $schedule->candidates->jobs->title ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Username')}}:</strong></td>
                                    <td><code>{{ $schedule->username }}</code></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <td><strong>{{__('Status')}}:</strong></td>
                                    <td>
                                        @if($schedule->status == 'scheduled')
                                            <span class="badge bg-info">{{__('Scheduled')}}</span>
                                        @elseif($schedule->status == 'in_progress')
                                            <span class="badge bg-warning">{{__('In Progress')}}</span>
                                        @elseif($schedule->status == 'completed')
                                            <span class="badge bg-success">{{__('Completed')}}</span>
                                        @elseif($schedule->status == 'expired')
                                            <span class="badge bg-danger">{{__('Expired')}}</span>
                                        @else
                                            <span class="badge bg-secondary">{{__('Cancelled')}}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Start Time')}}:</strong></td>
                                    <td>{{ $schedule->start_time->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('End Time')}}:</strong></td>
                                    <td>{{ $schedule->end_time->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Duration')}}:</strong></td>
                                    <td>{{ $schedule->duration_minutes }} {{__('minutes')}}</td>
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
                                        <h3>{{ $schedule->result->percentage }}%</h3>
                                        <small>{{__('Overall Score')}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h3>{{ $schedule->result->grade }}</h3>
                                        <small>{{__('Grade')}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h3>{{ $schedule->result->answered_questions }}/{{ $schedule->result->total_questions }}</h3>
                                        <small>{{__('Questions Answered')}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h3>{{ $schedule->result->earned_points }}/{{ $schedule->result->total_points }}</h3>
                                        <small>{{__('Points Earned')}}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($schedule->result->category_scores)
                            <div class="mt-4">
                                <h6>{{__('Category Breakdown')}}</h6>
                                <div class="row">
                                    @foreach($schedule->result->category_scores as $category => $scores)
                                        <div class="col-md-4 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title">{{ ucfirst($category) }}</h6>
                                                    <div class="progress mb-2">
                                                        <div class="progress-bar" role="progressbar" 
                                                             style="width: {{ $scores['percentage'] }}%">
                                                            {{ round($scores['percentage']) }}%
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $scores['earned_points'] }}/{{ $scores['total_points'] }} points
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Detailed Answers -->
            @if($schedule->answers->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>{{__('Detailed Answers')}}</h5>
                    </div>
                    <div class="card-body">
                        @foreach($schedule->answers as $answer)
                            <div class="mb-4 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-1">{{ $answer->question->title }}</h6>
                                    <span class="badge bg-{{ $answer->points_earned > 0 ? 'success' : 'danger' }}">
                                        {{ $answer->points_earned }}/{{ $answer->question->points }} pts
                                    </span>
                                </div>
                                <p class="text-muted mb-2">{{ $answer->question->question }}</p>
                                
                                @if($answer->question->type == 'multiple_choice' || $answer->question->type == 'true_false')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>{{__('Answer')}}:</strong> {{ $answer->answer }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{__('Correct Answer')}}:</strong> {{ $answer->question->correct_answer }}
                                        </div>
                                    </div>
                                @elseif($answer->question->type == 'rating_scale')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>{{__('Rating Given')}}:</strong> {{ $answer->answer }}/{{ max($answer->question->options) }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{__('Expected Rating')}}:</strong> {{ $answer->question->correct_answer }}
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-2">
                                        <strong>{{__('Answer')}}:</strong>
                                        <div class="bg-light p-2 rounded mt-1">
                                            {{ $answer->answer }}
                                        </div>
                                    </div>
                                @endif
                                
                                <small class="text-muted">
                                    {{__('Answered at')}}: {{ $answer->answered_at->format('d M Y H:i:s') }}
                                </small>
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
                            <small>{{ $schedule->started_at->format('d M Y H:i:s') }}</small>
                        </div>
                    @endif

                    @if($schedule->completed_at)
                        <div class="mb-3">
                            <strong>{{__('Completed At')}}:</strong><br>
                            <small>{{ $schedule->completed_at->format('d M Y H:i:s') }}</small>
                        </div>

                        @if($schedule->started_at)
                            <div class="mb-3">
                                <strong>{{__('Duration Taken')}}:</strong><br>
                                <small>{{ $schedule->started_at->diffInMinutes($schedule->completed_at) }} {{__('minutes')}}</small>
                            </div>
                        @endif
                    @endif

                    <div class="mb-3">
                        <strong>{{__('Created By')}}:</strong><br>
                        <small>{{ $schedule->creator->name }}</small>
                    </div>

                    <div class="mb-3">
                        <strong>{{__('Created At')}}:</strong><br>
                        <small>{{ $schedule->created_at->format('d M Y H:i:s') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection