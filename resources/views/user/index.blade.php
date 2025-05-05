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
    <div class="row row-gap-2 mb-4">
            @foreach($users as $user)
                {{-- <div class="col-md-3">
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
                </div> --}}
                <div class="col-xxl-3 col-xl-4 col-md-6">
                    <div class="card user-card">
                        <div class="card-header p-3 border border-bottom h-100">
                            <div class="user-img-wrp d-flex align-items-center justify-content-between">
                                <!-- User Image -->
                                <div class="d-flex align-items-center">
                                    <div class="user-image rounded-circle border-2 border border-primary me-3">
                                        <img src="{{ (!empty($user->avatar)) ? asset(Storage::url('uploads/avatar/'.$user->avatar)) : asset('assets/images/user/avatar-4.jpg') }}"
                                            alt="user-image" class="h-100 w-100 rounded-circle">
                                    </div>
                                    <div class="user-content">
                                        <h6 class="mb-0">{{ $user->name }}</h6>
                                        <span class="text-dark text-md">{{ $user->email }}</span>
                                    </div>
                                </div>

                                <!-- Edit Button (Pencil Icon) -->
                                @can('edit user')
                                    <a href="#!" 
                                        data-url="{{ route('users.edit', $user->id) }}" 
                                        data-ajax-popup="true" 
                                        data-size="md" 
                                        data-title="{{ __('Update ' . $user->name) }}" 
                                        data-bs-original-title="{{ __('Edit') }}" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        class="btn btn-sm border d-flex align-items-center edit-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16" fill="none" class="me-1">
                                            <path d="M1.56382 11.5713C1.40611 11.5713 1.24871 11.5112 1.12827 11.3908C0.887704 11.1502 0.887704 10.7603 1.12827 10.5197L10.7553 0.892668C10.9956 0.6521 11.3858 0.6521 11.6264 0.892668C11.867 1.13324 11.867 1.5232 11.6264 1.76376L1.99937 11.3908C1.87924 11.5109 1.72153 11.5713 1.56382 11.5713Z" fill="#060606" />
                                            <path d="M0.61263 16.0078C0.569815 16.0078 0.526383 16.0031 0.482952 15.9939C0.150284 15.9224 -0.0616371 15.595 0.00982476 15.2623L0.961623 10.8258C1.03308 10.4932 1.36206 10.2819 1.69318 10.3527C2.02585 10.4242 2.23777 10.7516 2.16631 11.0843L1.21451 15.5208C1.1526 15.81 0.896938 16.0078 0.61263 16.0078Z" fill="#060606" />
                                            <path d="M5.04863 15.056C4.89092 15.056 4.73352 14.9959 4.61308 14.8755C4.37251 14.6349 4.37251 14.245 4.61308 14.0044L14.2401 4.37767C14.4804 4.1371 14.8706 4.1371 15.1112 4.37767C15.3518 4.61824 15.3518 5.0082 15.1112 5.24877L5.48448 14.8755C5.36404 14.9959 5.20633 15.056 5.04863 15.056Z" fill="#060606" />
                                            <path d="M0.611348 16.0078C0.32704 16.0078 0.0716875 15.81 0.0094664 15.5208C-0.0616875 15.1881 0.149926 14.8607 0.482593 14.7892L4.91908 13.8374C5.25206 13.7669 5.57949 13.9782 5.65064 14.3105C5.7218 14.6432 5.51018 14.9706 5.17752 15.0421L0.741027 15.9939C0.697595 16.0034 0.654163 16.0078 0.611348 16.0078Z" fill="#060606" />
                                            <path d="M12.9331 7.17142C12.7754 7.17142 12.6177 7.11136 12.4976 6.99092L9.01287 3.50623C8.7723 3.26566 8.7723 2.8757 9.01287 2.63514C9.25313 2.39457 9.6437 2.39457 9.88396 2.63514L13.3687 6.11983C13.6092 6.36039 13.6092 6.75035 13.3687 6.99092C13.2485 7.11136 13.0908 7.17142 12.9331 7.17142Z" fill="#060606" />
                                            <path d="M14.6757 5.42925C14.518 5.42925 14.3603 5.36919 14.2399 5.24875C13.9993 5.00818 13.9993 4.61822 14.2399 4.37735C14.5827 4.03452 14.7715 3.57032 14.7715 3.0707C14.7715 2.57109 14.5827 2.10689 14.2399 1.76406C13.8967 1.42092 13.4325 1.2321 12.9329 1.2321C12.4333 1.2321 11.9691 1.42092 11.6263 1.76406C11.386 2.00463 10.996 2.00494 10.7549 1.76406C10.5143 1.52349 10.5143 1.13353 10.7549 0.892657C11.3303 0.316958 12.1037 0 12.9329 0C13.7618 0 14.5356 0.316958 15.111 0.892657C15.6867 1.46805 16.0036 2.2415 16.0036 3.0707C16.0036 3.89991 15.6867 4.67336 15.111 5.24875C14.9911 5.36888 14.8334 5.42925 14.6757 5.42925Z" fill="#060606" />
                                        </svg>
                                    </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body p-3  text-center">
                                <div class="bottom-icons d-flex flex-wrap align-items-center justify-content-between">
                                    <div class="edit-btn-wrp d-flex flex-wrap align-items-center">
                                                @can('delete user')
                                                    {{ Form::open(['route' => ['users.destroy', $user->id], 'class' => 'm-0']) }}
                                                    @method('DELETE')
                                                    <a href="#!" aria-label="Delete" data-confirm="{{ __('Are You Sure?') }}"
                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="delete-form-{{ $user->id }}" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" data-bs-original-title="{{ __('Delete') }}"
                                                        class="btn btn-sm border bs-pass-para show_confirm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            viewBox="0 0 16 16" fill="none">
                                                            <g clip-path="url(#clip0_11_8426)">
                                                                <path
                                                                    d="M13.625 1.875H11.2812V1.40625C11.2812 0.630844 10.6504 0 9.875 0H6.125C5.34959 0 4.71875 0.630844 4.71875 1.40625V1.875H2.375C1.59959 1.875 0.96875 2.50584 0.96875 3.28125C0.96875 3.904 1.37578 4.43316 1.93766 4.61753L2.77375 14.7105C2.83397 15.4336 3.44953 16 4.17513 16H11.8249C12.5505 16 13.1661 15.4336 13.2263 14.7103L14.0623 4.6175C14.6242 4.43316 15.0312 3.904 15.0312 3.28125C15.0312 2.50584 14.4004 1.875 13.625 1.875ZM5.65625 1.40625C5.65625 1.14778 5.86653 0.9375 6.125 0.9375H9.875C10.1335 0.9375 10.3438 1.14778 10.3438 1.40625V1.875H5.65625V1.40625ZM12.292 14.6327C12.2719 14.8737 12.0667 15.0625 11.8249 15.0625H4.17513C3.93328 15.0625 3.72809 14.8737 3.70806 14.6329L2.88419 4.6875H13.1158L12.292 14.6327ZM13.625 3.75H2.375C2.11653 3.75 1.90625 3.53972 1.90625 3.28125C1.90625 3.02278 2.11653 2.8125 2.375 2.8125H13.625C13.8835 2.8125 14.0938 3.02278 14.0938 3.28125C14.0938 3.53972 13.8835 3.75 13.625 3.75Z"
                                                                    fill="#060606" />
                                                                <path
                                                                    d="M6.12409 13.6272L5.65534 6.06472C5.63931 5.80631 5.41566 5.60978 5.1585 5.62588C4.90009 5.64191 4.70363 5.86435 4.71963 6.12272L5.18838 13.6853C5.20378 13.9338 5.41016 14.125 5.65578 14.125C5.92725 14.125 6.14075 13.8964 6.12409 13.6272Z"
                                                                    fill="#060606" />
                                                                <path
                                                                    d="M8 5.625C7.74112 5.625 7.53125 5.83487 7.53125 6.09375V13.6562C7.53125 13.9151 7.74112 14.125 8 14.125C8.25888 14.125 8.46875 13.9151 8.46875 13.6562V6.09375C8.46875 5.83487 8.25888 5.625 8 5.625Z"
                                                                    fill="#060606" />
                                                                <path
                                                                    d="M10.8415 5.62591C10.5837 5.60987 10.3606 5.80634 10.3446 6.06475L9.87587 13.6272C9.85991 13.8856 10.0564 14.1081 10.3147 14.1241C10.5733 14.1401 10.7956 13.9435 10.8116 13.6852L11.2803 6.12275C11.2963 5.86434 11.0999 5.64191 10.8415 5.62591Z"
                                                                    fill="#060606" />
                                                            </g>
                                                            <defs>
                                                                <clipPath id="clip0_11_8426">
                                                                    <rect width="16" height="16" fill="white" />
                                                                </clipPath>
                                                            </defs>
                                                        </svg>
                                                    </a>
                                                    {{ Form::close() }}
                                                @endcan
                                                <a href="#!"
                                                    data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}"
                                                    data-ajax-popup="true" data-size="md" class="btn btn-sm border"
                                                    data-title="{{ __('Reset Password') }}"
                                                    data-bs-original-title="{{ __('Reset Password') }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        viewBox="0 0 16 16" fill="none">
                                                        <g clip-path="url(#clip0_11_8441)">
                                                            <path
                                                                d="M14.492 1.50803C12.4812 -0.502691 9.20956 -0.50266 7.19884 1.50803C5.82553 2.88134 5.34866 4.90568 5.94306 6.74187L0.137344 12.5476C0.0494062 12.6355 0 12.7548 0 12.8791V15.5312C0 15.7901 0.209906 16 0.468812 16H3.12087C3.24525 16 3.36444 15.9506 3.45241 15.8627L4.11553 15.1995C4.21681 15.0982 4.26625 14.9562 4.24969 14.8139L4.16694 14.1019L5.15394 14.0089C5.37806 13.9877 5.55556 13.8103 5.57669 13.5862L5.66978 12.599L6.38181 12.6818C6.51475 12.6973 6.64781 12.6552 6.74775 12.5662C6.84762 12.4773 6.90478 12.3499 6.90478 12.2161V11.343H7.76197C7.88537 11.343 8.00378 11.2944 8.09156 11.2076L9.25631 10.0563C11.0929 10.6517 13.1183 10.1749 14.492 8.80112C16.5027 6.79043 16.5027 3.51871 14.492 1.50803ZM13.829 8.13815C12.6457 9.32143 10.8707 9.69078 9.307 9.07922C9.13444 9.01175 8.93837 9.05218 8.80665 9.1824L7.56937 10.4054H6.43594C6.17703 10.4054 5.96712 10.6153 5.96712 10.8742V11.6896L5.30212 11.6123C5.17684 11.5978 5.05103 11.6343 4.953 11.7136C4.855 11.793 4.79306 11.9084 4.78122 12.034L4.67956 13.1118L3.60187 13.2133C3.47631 13.2252 3.36084 13.2871 3.28147 13.3851C3.20212 13.4831 3.16559 13.609 3.18016 13.7342L3.29209 14.6969L2.92666 15.0624H0.937594V13.0734L6.81562 7.19534C6.94731 7.06365 6.98859 6.8665 6.92075 6.69306C6.30912 5.12937 6.67853 3.35443 7.86181 2.17109C9.507 0.525965 12.1838 0.525965 13.8289 2.17109C15.4741 3.81618 15.4741 6.49303 13.829 8.13815Z"
                                                                fill="#060606" />
                                                            <path
                                                                d="M13.1659 2.83406C12.6175 2.28566 11.7252 2.28569 11.1769 2.83406C10.6285 3.38244 10.6285 4.27472 11.1769 4.82309C11.7252 5.37147 12.6175 5.37153 13.1659 4.82309C13.7143 4.27472 13.7143 3.38244 13.1659 2.83406ZM12.5029 4.16009C12.3201 4.34287 12.0227 4.34291 11.8399 4.16009C11.6571 3.97731 11.6571 3.67991 11.8399 3.49709C12.0232 3.31384 12.3197 3.31384 12.5029 3.49709C12.6862 3.68034 12.6861 3.97684 12.5029 4.16009Z"
                                                                fill="#060606" />
                                                        </g>
                                                        <defs>
                                                            <clipPath id="clip0_11_8441">
                                                                <rect width="16" height="16" fill="white" />
                                                            </clipPath>
                                                        </defs>
                                                    </svg>
                                                </a>
                                                <div class="col form-switch form-switch-left h6 mb-0">
                                                    <input type="checkbox" class="form-check-input" data-user-id="{{ $user->id }}" {{ $user->is_active ? 'checked' : '' }}>
                                                </div>
                                        </div>
                                    <span class="badge bg-primary p-2 px-3">{{ ucfirst($user->type) }}</span>
                                </div>
                        </div>
                    </div>
                </div>
            @endforeach
    </div>
@endsection
