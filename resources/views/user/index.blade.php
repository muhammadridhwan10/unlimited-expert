@extends('layouts.admin')
@php
    $profile=asset(Storage::url('uploads/avatar/'));
@endphp
@section('page-title')
    {{__('Manage User')}}
@endsection
@push('script-page')
    <script>
        // Menggunakan jQuery untuk menangani perubahan toggle switch
        $(document).ready(function() {

            $('.form-check-input').change(function() {
                var userId = $(this).data('user-id');
                var isActive = $(this).is(':checked') ? 1 : 0;

                // Mengirim permintaan Ajax ke server untuk memperbarui status pengguna
                $.ajax({
                    url: "{{route("update-active")}}",
                    method: 'POST',
                    data: {
                        user_id: userId,
                        is_active: isActive,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Tindakan sukses, misalnya memperbarui tampilan
                        console.log('User successfully updated.');
                    },
                    error: function(xhr, status, error) {
                        // Penanganan kesalahan, misalnya menampilkan pesan kesalahan
                        console.error('Something Error: ' + error);
                    }
                });
            });
        });
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('User')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
            <a href="{{ route('user.userlog') }}" class="btn btn-primary btn-sm {{ Request::segment(1) == 'user' }}"
                   data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('User Logs History') }}"><i class="ti ti-user-check"></i>
            </a>
        @endif
        <a href="#" data-size="lg" data-url="{{ route('users.create') }}" data-ajax-popup="true"  data-bs-toggle="tooltip" title="{{__('Create New User')}}"  class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-xxl-12">
            <div class="row">
                @foreach($users as $user)
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-header border-0 pb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <div class=" badge bg-primary p-2 px-3 rounded">
                                            {{ ucfirst($user->type) }}
                                        </div>
                                    </h6>

                                </div>

                                <div class="card-header-right">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle"
                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-end">
                                            @can('edit user')
                                                <a href="#!" data-size="lg" data-url="{{ route('users.edit',$user->id) }}" data-ajax-popup="true" class="dropdown-item" data-bs-original-title="{{__('Edit User')}}">
                                                    <i class="ti ti-pencil"></i>
                                                    <span>{{__('Edit')}}</span>
                                                </a>
                                            @endcan

                                            @can('delete user')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user['id']],'id'=>'delete-form-'.$user['id']]) !!}
                                                <a href="#!"  class="dropdown-item bs-pass-para">
                                                    <i class="ti ti-archive"></i>
                                                    <span> @if($user->delete_status!=0){{__('Delete')}} @else {{__('Restore')}}@endif</span>
                                                </a>

                                                {!! Form::close() !!}
                                            @endcan
                                            <a href="#!" data-url="{{route('users.reset',\Crypt::encrypt($user->id))}}" data-ajax-popup="true" class="dropdown-item" data-bs-original-title="{{__('Reset Password')}}">
                                                <i class="ti ti-adjustments"></i>
                                                <span>  {{__('Reset Password')}}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body full-card">
                                <div class="card-avatar">
                                    <img src="{{ (!empty($user->avatar)) ? asset(Storage::url('uploads/avatar/'.$user->avatar)) : asset(Storage::url('uploads/avatar/avatar.png')) }}" class="wid-80" style="width: 72px; height: 72px; object-fit: cover; object-position: center; border-radius: 50%;">
                                </div>
                                <h4 class=" mt-2 text-primary">{{ $user->name }}</h4>
                                <small class="text-primary">{{ $user->email }}</small>
                                <br>
                                <br>
                                <div class="col form-switch form-switch-left h6 mb-0">
                                    <input type="checkbox" class="form-check-input" data-user-id="{{ $user->id }}" {{ $user->is_active ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
