@extends('layouts.admin')
@section('page-title')
    {{__('Translator')}}
@endsection
@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
@endpush

@push('script-page')
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Translator') }}</li>
@endsection
@section('action-btn')

    <div class="row">
        <div class="col-lg-6">

        </div>
    </div>
@endsection


@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body ">
                    {{-- <div class="card"> --}}
                    <div class="row">

                        <div class="form-group">
                            <h1 style = 'text-align: center'>Translator</h1>
                        </div>
                        
                        <div class="col-sm-6 col-md-6">
                            <div class="form-group">
                                <select name="lang_one" id="lang_one" class="form-control main-element">
                                    @foreach(\App\Models\Translate::$country_language_one as $k => $v)
                                        <option value="{{$k}}">{{__($v)}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-6">
                            <div class="form-group">
                                <select name="lang_two" id="lang_two" class="form-control main-element">
                                    @foreach(\App\Models\Translate::$country_language_two as $k => $v)
                                        <option value="{{$k}}">{{__($v)}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-6">
                            <div class="form-group">
                                {{ Form::textarea('text', null, ['id' => 'text', 'class' => 'form-control', 'rows' => '4', 'placeholder' => 'Enter Text...', 'cols' => '50']) }}
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-6">
                            <div class="form-group">
                                {{ Form::textarea('output', null, ['id' => 'output', 'class' => 'form-control', 'rows' => '4', 'placeholder' => 'Translation...', 'readonly' => 'true', 'cols' => '50']) }}
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button id="convert" class='btn btn-xs btn-primary'>
                                Translate
                            </button>
                        </div>


                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

<script src="{{asset('js/jquery.min.js')}}"></script>
<script>
    $(document).ready(function () {
    $("#convert").on("click", function () {
        var lang_one = $("#lang_one").val();
        var lang_two = $("#lang_two").val();
        var text = $("#text").val();

        $.ajax({
        url: "{{route('translate.text')}}",
        type: "POST",
        data: { lang_one: lang_one, lang_two: lang_two, text: text, "_token": "{{ csrf_token() }}" },
        success: function (status) {
            text = $("#output").val(status);
        },
        });
    });
    });
</script>

