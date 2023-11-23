@extends('layouts.admin')
@php
    $profile=asset(Storage::url('uploads/avatar/'));
@endphp
@section('page-title')
    {{__('Manage User Assigment')}}
@endsection
@push('script-page')

@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('User Assgined')}}</li>
@endsection
@section('action-btn')
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th scope="col">{{__('Name')}}</th>
                                    <th scope="col">{{__('Email')}}</th>
                                    <th scope="col">{{__('Role')}}</th>
                                    <th scope="col">{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody class="list">
                                @if(count($users) > 0)
                                    @foreach($users as $user)
                                        <tr>
                                            <td>
                                                <div class="user-group">
                                                    <a href="#" class="img-fluid rounded-circle">
                                                        <img data-original-title="{{(!empty($user)?$user->name:'')}}" @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset(Storage::url($user->name . ".png"))}}" @endif title="{{ $user->name }}" class="hweb">
                                                    </a>
                                                        {{ $user->name }}
                                                </div>
                                            </td>
                                            <td>
                                                {{$user->email}}
                                            </td>
                                            <td>
                                                {{$user->type}}
                                            </td>
                                            <td class="Action">
                                                <span>
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#!" data-size="lg" data-url="{{ route('list.users.edit',$user->id) }}" data-ajax-popup="true" data-bs-original-title="{{__('Assigned User To Project and Task')}}"
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Assigned User" data-original-title="{{ __('Assigned User') }}">
                                                            <i class="ti ti-send text-white"></i>
                                                        </a>  
                                                    </div>
                                                </span>
                                        </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th scope="col" colspan="7"><h6 class="text-center">{{__('No Users found')}}</h6></th>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
