@extends('layouts.auth')
@php
    $logo=asset(Storage::url('uploads/logo/'));
    $company_logo=Utility::getValByName('company_logo');
    $settings = Utility::settings();

@endphp
@push('custom-scripts')
    @if(env('RECAPTCHA_MODULE') == 'yes')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush
@section('page-title')
    {{__('Login')}}
@endsection

@section('auth-topbar')
    {{-- <li class="nav-item">
        <a class="nav-link" target="_blank" href="https://aup-docs.au-partners.com/">Documentation</a>
    </li> --}}
    <li class="nav-item ">
        <select class="btn btn-primary my-1 me-2 " onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);" id="language">
            @foreach(Utility::languages() as $language)
                <option class="" @if($lang == $language) selected @endif value="{{ route('login',$language) }}">{{Str::upper($language)}}</option>
            @endforeach
        </select>
    </li>
@endsection
@section('content')
    {{-- <div class="">
        <h2 class="mb-3 f-w-600">{{__('Login')}}</h2>
        <p class="text-black">
                        Teamwork is so important that it is virtually impossible for you to reach the heights 
                        of your capabilities or make the money 
                        that you want without becoming very good at it
                        </p>
    </div> --}}
    {{Form::open(array('route'=>'login','method'=>'post','id'=>'loginForm' ))}}
    @csrf
    <div class="card-body">
        <h2 class="mb-3 f-w-600">{{__('Login')}}</h2>

        <div class="form-group mb-3">
            <label for="email" class="form-label">{{__('Email')}}</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            @error('email')
            <div class="invalid-feedback" role="alert">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="password" class="form-label">{{__('Password')}}</label>
            <div class="input-group">
                <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required autocomplete="current-password">
                <span class="input-group-text" onclick="togglePasswordVisibility()">
                    <i class="fa fa-eye" id="password-icon"></i>
                </span>
            </div>
            @error('password')
            <div class="invalid-feedback" role="alert">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">
                    {{ __('Remember Me') }}
                </label>
            </div>
        </div>

        @if(env('RECAPTCHA_MODULE') == 'yes')
        <div class="form-group mb-3">
            {!! NoCaptcha::display() !!}
            @error('g-recaptcha-response')
            <span class="small text-danger" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        @endif

        <div class="d-grid">
            <button type="submit" class="btn-login btn btn-primary btn-block mt-2" id="login_button">{{__('Login')}}</button>
        </div>
    </div>
    {{Form::close()}}
@endsection

<script src="{{asset('js/jquery.min.js')}}"></script>
<script>
    $(document).ready(function () {
        $("#form_data").submit(function (e) {
            $("#login_button").attr("disabled", true);
            return true;
        });
    });
</script>
<script>
    function togglePasswordVisibility() {
        var passwordField = document.getElementById('password');
        var passwordIcon = document.getElementById('password-icon');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            passwordIcon.classList.remove('fa-eye');
            passwordIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            passwordIcon.classList.remove('fa-eye-slash');
            passwordIcon.classList.add('fa-eye');
        }
    }
</script>

