@extends('layouts.admin')
@section('page-title')
    {{__('Edit Psychotest Schedule')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('psychotest-schedule.index')}}">{{__('Psychotest Schedule')}}</a></li>
    <li class="breadcrumb-item">{{__('Edit')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {!! Form::model($schedule, ['route' => ['psychotest-schedule.update', $schedule->id], 'method' => 'put']) !!}
                    <div class="row">
                        <div class="form-group col-md-12">
                            {!! Form::label('candidate', __('Candidate'), ['class' => 'form-label']) !!}
                            {!! Form::select('candidate', $candidates, null, ['class' => 'form-control select2', 'required' => true, 'disabled' => true]) !!}
                            <small class="text-muted">{{__('Candidate cannot be changed after creation')}}</small>
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('duration_minutes', __('Duration (Minutes)'), ['class' => 'form-label']) !!}
                            {!! Form::number('duration_minutes', null, ['class' => 'form-control', 'min' => 15, 'max' => 300, 'required' => true]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('start_time', __('Start Time'), ['class' => 'form-label']) !!}
                            {!! Form::datetimeLocal('start_time', $schedule->start_time ? $schedule->start_time->format('Y-m-d\TH:i') : null, ['class' => 'form-control', 'required' => true]) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('end_time', __('End Time'), ['class' => 'form-label']) !!}
                            {!! Form::datetimeLocal('end_time', $schedule->end_time ? $schedule->end_time->format('Y-m-d\TH:i') : null, ['class' => 'form-control', 'required' => true]) !!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('psychotest-schedule.index') }}" class="btn btn-light">{{__('Cancel')}}</a>
                        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection