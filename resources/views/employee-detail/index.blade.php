@extends('layouts.admin')
@php
    $profile=asset(Storage::url('uploads/avatar/'));
@endphp
@section('page-title')
    {{__('Employee List')}}
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
    <li class="breadcrumb-item">{{__('Employee List')}}</li>
@endsection
@section('action-btn')

@endsection
@section('content')
    <div class="row">
        <div class="col-xxl-12">
            <div class="row">
                @foreach($users as $user)
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body full-card">
                                <div class="card-avatar">
                                    <img src="{{ (!empty($user->avatar)) ? asset(Storage::url('uploads/avatar/'.$user->avatar)) : asset(Storage::url('uploads/avatar/avatar.png')) }}" class="wid-80" style="width: 72px; height: 72px; object-fit: cover; object-position: center; border-radius: 50%;">
                                </div>
                                <h4 class=" mt-2 text-primary">{{ $user->name }}</h4>
                                <small class="text-primary">{{ $user->email }}</small>
                                <div class="align-items-center h6 mt-2" data-bs-toggle="tooltip" title="{{__('Detail User')}}">
                                <a href="{{ route('employee-details.show',$user->id) }}"  class="btn btn-primary" data-bs-original-title="{{__('View')}}">
                                    <span>{{__('Detail')}}</span>
                                </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
