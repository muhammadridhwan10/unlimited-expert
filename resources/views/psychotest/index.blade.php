@extends('layouts.admin')
@section('page-title')
    {{__('Manage Psychotest Schedule')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Psychotest Schedule')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
            <a href="{{ route('psychotest-schedule.create') }}" data-bs-toggle="tooltip" title="{{__('Create')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{__('Candidate')}}</th>
                                    <th>{{__('Job Position')}}</th>
                                    <th>{{__('Username')}}</th>
                                    <th>{{__('Schedule')}}</th>
                                    <th>{{__('Duration')}}</th>
                                    <th>{{__('Status')}}</th>
                                    <th>{{__('Email Sent')}}</th>
                                    <th>{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedules as $schedule)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-group">
                                                    <a href="#" class="avatar rounded-circle avatar-sm">
                                                        <img src="{{asset('/storage/uploads/avatar/avatar.png')}}" class="hweb">
                                                    </a>
                                                </div>
                                                <div class="ms-2">
                                                    <h6 class="mb-0">{{ $schedule->candidates->name }}</h6>
                                                    <small class="text-muted">{{ $schedule->candidates->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $schedule->candidates->jobs->title ?? '-' }}</td>
                                        <td>
                                            <code class="small">{{ $schedule->username }}</code>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <strong>Start:</strong> {{ $schedule->start_time->format('d M Y H:i') }}<br>
                                                <strong>End:</strong> {{ $schedule->end_time->format('d M Y H:i') }}
                                            </div>
                                        </td>
                                        <td>{{ $schedule->duration_minutes }} {{__('minutes')}}</td>
                                        <td>
                                            @if($schedule->status == 'scheduled')
                                                <span class="badge bg-info p-2 px-3 rounded">{{__('Scheduled')}}</span>
                                            @elseif($schedule->status == 'in_progress')
                                                <span class="badge bg-warning p-2 px-3 rounded">{{__('In Progress')}}</span>
                                            @elseif($schedule->status == 'completed')
                                                <span class="badge bg-success p-2 px-3 rounded">{{__('Completed')}}</span>
                                            @elseif($schedule->status == 'expired')
                                                <span class="badge bg-danger p-2 px-3 rounded">{{__('Expired')}}</span>
                                            @else
                                                <span class="badge bg-secondary p-2 px-3 rounded">{{__('Cancelled')}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->email_sent)
                                                <span class="badge bg-success"><i class="ti ti-check"></i></span>
                                            @else
                                                <span class="badge bg-danger"><i class="ti ti-x"></i></span>
                                            @endif
                                        </td>
                                        <td class="Action">
                                            <span>
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="{{ route('psychotest-schedule.show', $schedule->id) }}" 
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('View')}}">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    </div>

                                                @if($schedule->status == 'scheduled')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="{{ route('psychotest-schedule.edit', $schedule->id) }}" 
                                                            class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>

                                                        <div class="action-btn bg-warning ms-2">
                                                            <a href="{{ route('psychotest-schedule.resend-email', $schedule->id) }}" 
                                                            class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Resend Email')}}">
                                                                <i class="ti ti-mail text-white"></i>
                                                            </a>
                                                        </div>

                                                        <div class="action-btn bg-secondary ms-2">
                                                            <a href="{{ route('psychotest-schedule.cancel', $schedule->id) }}" 
                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                                                            data-confirm="{{__('Are You Sure?').'|'.__('This will cancel the psychotest schedule.')}}" 
                                                            data-bs-toggle="tooltip" title="{{__('Cancel')}}">
                                                                <i class="ti ti-ban text-white"></i>
                                                            </a>
                                                        </div>
                                                @endif

                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['psychotest-schedule.destroy', $schedule->id],'id'=>'delete-form-'.$schedule->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                                                        data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" 
                                                        data-bs-toggle="tooltip" title="{{__('Delete')}}" 
                                                        data-confirm-yes="document.getElementById('delete-form-{{$schedule->id}}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection