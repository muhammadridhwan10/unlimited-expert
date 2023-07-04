@extends('layouts.admin')
@section('page-title')
    {{ucwords($task->name)}}
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
    <style>
        .formatted-text {
            white-space: pre-wrap;
            font-family: 'Roboto', sans-serif;
        }

        .typing-animation .text:after {
            content: '|';
            animation: blink-caret 0.75s infinite;
        }

        @keyframes typing {
            from { width: 0; }
            to { width: 100%; }
        }

        @keyframes blink-caret {
            from, to { opacity: 0; }
            50% { opacity: 1; }
        }

        @keyframes typing-text {
            0% { width: 0; }
            100% { width: 100%; }
        }
    </style>
@endpush
@push('script-page')
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script>

        $(document).on('change', '.item', function () {

            var iteams_id = $(this).val();
            var url = $(this).data('url');
            var el = $(this);
            console.log(url);
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'materialitas_id': iteams_id
                },
                cache: false,
                success: function (data) {
                    var item = JSON.parse(data);
                    //console.log(item)
                    $('.rate').val(0);
                    $('.pmrate').val(0);
                    $('.terate').val(0);
                    $('.materialitas_id').val(item.materialitas.materialitas_id);
                    $('.inhouse').val(parseInt(item.materialitas.inhouse).toLocaleString('en-EN'));
                    $('.audited').val(parseInt(item.materialitas.audited).toLocaleString('en-EN'));
                },
            });
        });

        $(document).on('keyup', '.rate', function () 
        {
            
            var rate = $(this).val();

            var inhouse2022 = parseInt($('.inhouse').val().replace(/,/g, ''));
            var audited2022 = parseInt($('.audited').val().replace(/,/g, ''));

            var totalMaterialitasAwal = (rate * inhouse2022)/100;
            var totalMaterialitasAkhir = (rate * audited2022)/100;

            // Hasil materialitas awal overall materiality
            var materialitasAwal = (totalMaterialitasAwal !== 0 ? totalMaterialitasAwal.toLocaleString('en-EN', { maximumFractionDigits: 0 }) : '-');
            $('.initialmaterialityom').val(materialitasAwal);

            // Hasil materialitas akhir overall materiality
            var materialitasAkhir = (totalMaterialitasAkhir !== 0 ? totalMaterialitasAkhir.toLocaleString('en-EN', { maximumFractionDigits: 0 }) : '-');
            $('.finalmaterialityom').val(materialitasAkhir);
        });

        $(document).on('keyup', '.pmrate', function () 
        {
            var pmrate = $(this).val();

            var initialmaterialityom = parseInt($('.initialmaterialityom').val().replace(/,/g, ''));
            var finalmaterialityom = parseInt($('.finalmaterialityom').val().replace(/,/g, ''));

            var totalinitialmaterialitypm = (pmrate * initialmaterialityom)/100;
            var totalfinalmaterialitypm = (pmrate * finalmaterialityom)/100;

            //hasil materialitas awal performance materiality
            var initialmaterialitypm = (totalinitialmaterialitypm !== 0 ? totalinitialmaterialitypm.toLocaleString('en-EN', { maximumFractionDigits: 0 }) : '-');
            $('.initialmaterialitypm').val(initialmaterialitypm);

            //hasil materialitas akhir performance materiality
            var finalmaterialitypm = (totalfinalmaterialitypm !== 0 ? totalfinalmaterialitypm.toLocaleString('en-EN', { maximumFractionDigits: 0 }) : '-');
            $('.finalmaterialitypm').val(finalmaterialitypm);
        });

        $(document).on('keyup', '.terate', function () 
        {
            var terate = $(this).val();

            var initialmaterialityte = parseInt($('.initialmaterialitypm').val().replace(/,/g, ''));
            var finalmaterialityte = parseInt($('.finalmaterialitypm').val().replace(/,/g, ''));

            var totalinitialmaterialityte = (terate * initialmaterialityte)/100;
            var totalfinalmaterialityte = (terate * finalmaterialityte)/100;

            //hasil materialitas awal tolerable error
            var initialmaterialityte = (totalinitialmaterialityte !== 0 ? totalinitialmaterialityte.toLocaleString('en-EN', { maximumFractionDigits: 0 }) : '-');
            $('.initialmaterialityte').val(initialmaterialityte);

            //hasil materialitas akhir tolerable error
            var finalmaterialityte = (totalfinalmaterialityte !== 0 ? totalfinalmaterialityte.toLocaleString('en-EN', { maximumFractionDigits: 0 }) : '-');
            $('.finalmaterialityte').val(finalmaterialityte);
        });

        function simpanData(materialitas_id, rate, pmrate, terate, initialom, finalom, initialpm, finalpm, initialte, finalte, description) 
        {
            // Mengirim data ke server menggunakan Ajax
            $.ajax({
                type: 'POST',
                url: '/save-summary-materiality/{pid}',
                data: {
                materialitas_id: materialitas_id,
                rate: rate,
                pmrate: pmrate,
                terate: terate,
                initialom: initialom,
                finalom: finalom,
                initialpm: initialpm,
                finalpm: finalpm,
                initialte: initialte,
                finalte: finalte,
                description: description,
                }
            });
        }

        $('.btn-simpan').click(function() 
        {
            // Mengambil nilai dari input initialmaterialityom dan finalmaterialityom
            var materialitas_id = $('.item').val();
            var rate = $('.rate').val();
            var pmrate = $('.pmrate').val();
            var terate = $('.terate').val();
            var initialom = $('.initialmaterialityom').val();
            var finalom = $('.finalmaterialityom').val();
            var initialpm = $('.initialmaterialitypm').val();
            var finalpm = $('.finalmaterialitypm').val();
            var initialte = $('.initialmaterialityte').val();
            var finalte = $('.finalmaterialityte').val();
            var description = $('.description').val();

            // Memanggil fungsi simpanData untuk menyimpan data ke database
            simpanData(materialitas_id, rate, pmrate, terate, initialom, finalom, initialpm, finalpm, initialte, finalte, description);
        });


        


    </script>
    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            let answerText = document.querySelector('.formatted-text');
            let answerTextContent = answerText.textContent.trim();
            answerText.textContent = '';

            let span = document.createElement('span');
            span.classList.add('text');
            answerText.appendChild(span);

            let currentCharIndex = 0;
            let typingTimer = setInterval(function() {
                if (currentCharIndex < answerTextContent.length) {
                    span.textContent += answerTextContent.charAt(currentCharIndex);
                    currentCharIndex++;
                } else {
                    clearInterval(typingTimer);
                }
            }, 20);
        });
    </script>


@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show',$project->id)}}">    {{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.index',$project->id)}}">{{__('Task')}}</a></li>
    <li class="breadcrumb-item">{{__($task->name)}}</li>
@endsection
@push('script-page')
@endpush
@section('action-btn')
@endsection

@section('content')
{{ Form::open(['route' => ['send.respon.materialitas', [$project->id, $task->id]], 'method' => 'post']) }}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-header">
                        <div class="float-end">
                            <button type="submit" class="btn btn-sm btn-primary"> <i class="fas fa-robot"></i>{{__(' Generate Answers With AI')}}</>
                        </div>
                        <br>
                    </div>
                    <div class="card-body">
                        <div class="row">
                                <div class="col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <div class="col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::textarea('message', null, ['class' => 'form-control message']) }}
                                                </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
{{ Form::close() }}
{{ Form::open(['route' => ['summary.materialitas', $project->id], 'method' => 'post']) }}
    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    <input type="hidden" name="materialitas_id" class = "form-control materialitas_id">
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                <div class="card-header"><h6 class="mb-0">{{__('Financial Statement Components')}}</h6></div>
                    <div class="card-body">
                        <div class="row">
                                <div class="col-sm-3 col-md-4">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>Critical Component</b></p>
                                        @foreach($materialitas as $materiality)
                                        @if($materiality->name == 'LABA BRUTO' || $materiality->name == 'LABA OPERASIONAL' || $materiality->name == 'LABA SEBELUM PAJAK' || $materiality->name == 'LABA SETELAH PAJAK' || $materiality->name == 'LABA RUGI KOMPREHENSIF SETELAH PAJAK')
                                            <div class="col-sm-3 col-md-12">    
                                                <div class="form-group">
                                                    {{ Form::text('component', $materiality->name, ['class' => 'form-control','readonly'=>'true','style' => 'background-color:#008b8b; color:white; font-weight: bold;']) }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::text('component', $materiality->name, ['class' => 'form-control','readonly'=>'true']) }}
                                                </div>
                                            </div>
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                    <?php
                                    $ekuitas_2020 = $data_array_2020['3'];
                                    $liabilitas_2020 = $data_array_2020['2'];
                                    $aset_2020 = $liabilitas_2020 + $ekuitas_2020;
                                    ?>
                                    @if(ceil($aset_2020) == ceil($data_array_2020['1']))
                                        <p style='text-align:center; color:green;'> 
                                        <i class="fas fa-check"></i>
                                            <b>{{ date('Y', strtotime('-3 year', strtotime($project->book_year))) }}</b>
                                        </p>
                                    @else
                                        <p style='text-align:center; color:red;'>
                                        <i class="fas fa-times"></i>
                                            <b>{{ date('Y', strtotime('-3 year', strtotime($project->book_year))) }}</b>
                                        </p>
                                    @endif
                                        @foreach($data_array_2020 as $key => $data_2020)
                                        @if($key == '11' || $key == '12' || $key == '13' || $key == '14' || $key == '15')
                                            <div class="col-sm-3 col-md-12">    
                                                <div class="form-group">
                                                    {{ Form::text('2020', ($data_2020 != 0) ? (($data_2020 < 0) ? '('.number_format(abs($data_2020)).')' : number_format($data_2020)) : '-',  ['class' => 'form-control','readonly'=>'true','style' => 'background-color:#008b8b; color:white; font-weight: bold; text-align: right;']) }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::text('2020', ($data_2020 != 0) ? (($data_2020 < 0) ? '('.number_format(abs($data_2020)).')' : number_format($data_2020)) : '-', ['class' => 'form-control','readonly'=>'true', 'style' => 'text-align: right;']) }}
                                                </div>
                                            </div>
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                    <?php
                                    $ekuitas_2021 = $data_array_2021['3'];
                                    $liabilitas_2021 = $data_array_2021['2'];
                                    $aset_2021 = $liabilitas_2021 + $ekuitas_2021;
                                    ?>
                                    @if(ceil($aset_2021) == ceil($data_array_2021['1']))
                                        <p style='text-align:center; color:green;'>
                                        <i class="fas fa-check"></i>
                                            <b>{{ date('Y', strtotime('-2 year', strtotime($project->book_year))) }}</b>
                                        </p>
                                    @else
                                        <p style='text-align:center; color:red;'>
                                            <i class="fas fa-times"></i>
                                            <b>{{ date('Y', strtotime('-2 year', strtotime($project->book_year))) }}</b>
                                        </p>
                                    @endif
                                        @foreach($data_array_2021 as $key => $data_2021)
                                             @if($key == '11' || $key == '12' || $key == '13' || $key == '14' || $key == '15')
                                            <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::text('2021', ($data_2021 != 0) ? (($data_2021 < 0) ? '('.number_format(abs($data_2021)).')' : number_format($data_2021)) : '-',  ['class' => 'form-control','readonly'=>'true','style' => 'background-color:#008b8b; color:white; font-weight: bold; text-align: right;']) }}
                                                </div>
                                            </div>
                                            @else
                                                <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        {{ Form::text('2021', ($data_2021 != 0) ? (($data_2021 < 0) ? '('.number_format(abs($data_2021)).')' : number_format($data_2021)) : '-', ['class' => 'form-control','readonly'=>'true', 'style' => 'text-align: right;']) }}
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <?php
                                        $ekuitas_in_2022 = $data_array_in_2022['3'];
                                        $liabilitas_in_2022 = $data_array_in_2022['2'];
                                        $aset_in_2022 = $liabilitas_in_2022 + $ekuitas_in_2022;
                                        ?>
                                        @if(ceil($aset_in_2022) == ceil($data_array_in_2022['1']))
                                            <p style='text-align:center; color:green;'>
                                            <i class="fas fa-check"></i>
                                                <b>{{'Inhouse ' . $project->book_year}}</b>
                                            </p>
                                        @else
                                            <p style='text-align:center; color:red;'>
                                                <i class="fas fa-times"></i>
                                                <b>{{'Inhouse ' . $project->book_year}}</b>
                                            </p>
                                        @endif
                                        @foreach($data_array_in_2022 as $key => $data_in_2022)
                                            @if($key == '11' || $key == '12' || $key == '13' || $key == '14' || $key == '15')
                                            <div class="col-sm-3 col-md-12">    
                                                <div class="form-group">
                                                    {{ Form::text('inhouse', ($data_in_2022 != 0) ? (($data_in_2022 < 0) ? '('.number_format(abs($data_in_2022)).')' : number_format($data_in_2022)) : '-',  ['class' => 'form-control','readonly'=>'true','style' => 'background-color:#008b8b; color:white; font-weight: bold; text-align: right;']) }}
                                                </div>
                                            </div>
                                            @else
                                                <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        {{ Form::text('inhouse', ($data_in_2022 != 0) ? (($data_in_2022 < 0) ? '('.number_format(abs($data_in_2022)).')' : number_format($data_in_2022)) : '-', ['class' => 'form-control','readonly'=>'true', 'style' => 'text-align: right;']) }}
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <?php
                                        $ekuitas_au_2022 = $data_array_au_2022['3'];
                                        $liabilitas_au_2022 = $data_array_au_2022['2'];
                                        $aset_au_2022 = $liabilitas_au_2022 + $ekuitas_au_2022;
                                        ?>
                                        @if(ceil($aset_au_2022) == ceil($data_array_au_2022['1']))
                                            <p style='text-align:center; color:green;'>
                                            <i class="fas fa-check"></i>
                                                <b>{{'Audited ' . $project->book_year}}</b>
                                            </p>
                                        @else
                                            <p style='text-align:center; color:red;'>
                                                <i class="fas fa-times"></i>
                                                <b>{{'Audited ' . $project->book_year}}</b>
                                            </p>
                                        @endif
                                        @foreach($data_array_au_2022 as $key => $data_au_2022)
                                            @if($key == '11' || $key == '12' || $key == '13' || $key == '14' || $key == '15')
                                            <div class="col-sm-3 col-md-12">    
                                                <div class="form-group">
                                                    {{ Form::text('audited', ($data_au_2022 != 0) ? (($data_au_2022 < 0) ? '('.number_format(abs($data_au_2022)).')' : number_format($data_au_2022)) : '-',  ['class' => 'form-control','readonly'=>'true','style' => 'background-color:#008b8b; color:white; font-weight: bold; text-align: right;']) }}
                                                </div>
                                            </div>
                                            @else
                                                <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                       {{ Form::text('audited', ($data_au_2022 != 0) ? (($data_au_2022 < 0) ? '('.number_format(abs($data_au_2022)).')' : number_format($data_au_2022)) : '-', ['class' => 'form-control','readonly'=>'true', 'style' => 'text-align: right;']) }}
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-header"><h6 class="mb-0">{{__('Overall Materiality ')}}</h6>
                    <br>
                    <p>
                        <strong>
                                Reference : https://
                        </strong>
                    </p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                                <div class="col-sm-3 col-md-4">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>Component</b></p>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>Rate %</b></p>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>Inhouse</b></p>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>Audited</b></p>
                                    </div>
                                </div>
                        </div>
                        <div class="row">
                                <div class="col-sm-3 col-md-4">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    <select name="item" class="form-control select item" data-url="{{ route('tasks.materialitas') }}">
                                                            <option value="0">{{'--'}}</option>
                                                        @foreach($get_data_materialitas as $k)
                                                            <option value="{{$k->materiality->id}}">{{__($k->materiality->name)}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    @if(isset($valuemateriality->rate))
                                                        {{ Form::text('rate', $valuemateriality->rate, ['class' => 'form-control rate', 'style' => 'text-align: right;']) }}
                                                    @else
                                                        {{ Form::text('rate', null, ['class' => 'form-control rate', 'style' => 'text-align: right;']) }}
                                                    @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group inhouse">
                                                    {{ Form::text('inhouse', '', array('class' => 'form-control inhouse', 'readonly' => true, 'style' => 'text-align: right;')) }}
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group audited">
                                                    {{ Form::text('audited', '', array('class' => 'form-control audited', 'readonly' => true, 'style' => 'text-align: right;')) }}
                                                </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-header"><h6 class="mb-0">{{__('Summary')}}</h6></div>
                    <div class="card-body">
                        <div class="row">
                                <div class="col-sm-3 col-md-4">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>Item</b></p>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>Materialitas Awal</b></p>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>Materialitas Akhir</b></p>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>Rate %</b></p>
                                    </div>
                                </div>
                        </div>
                        <div class="row">
                                <div class="col-sm-3 col-md-4">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::text('overall', 'Overall Materiality', ['class' => 'form-control','readonly' => 'true']) }}
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                @if(isset($valuemateriality->initialmaterialityom))
                                                    {{ Form::text('initialmaterialityom', number_format($valuemateriality->initialmaterialityom), ['class' => 'form-control initialmaterialityom','readonly'=>'true', 'style' => 'text-align: right;']) }}
                                                @else
                                                    {{ Form::text('initialmaterialityom', null, ['class' => 'form-control initialmaterialityom','readonly'=>'true','style' => 'text-align: right;']) }}
                                                @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                @if(isset($valuemateriality->finalmaterialityom))
                                                    {{ Form::text('finalmaterialityom',number_format($valuemateriality->finalmaterialityom),array('class' => 'form-control finalmaterialityom','readonly'=>'true', 'style' => 'text-align: right;')) }}
                                                @else
                                                    {{ Form::text('finalmaterialityom',null,array('class' => 'form-control finalmaterialityom','readonly'=>'true', 'style' => 'text-align: right;')) }}
                                                @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="row">
                                <div class="col-sm-3 col-md-4">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::text('pm', 'Performance Materiality', ['class' => 'form-control', 'readonly' => 'true']) }}
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                @if(isset($valuemateriality->initialmaterialitypm))
                                                    {{ Form::text('initialmaterialitypm', number_format($valuemateriality->initialmaterialitypm), ['class' => 'form-control initialmaterialitypm','readonly' => 'true', 'style' => 'text-align: right;']) }}
                                                @else
                                                    {{ Form::text('initialmaterialitypm', null, ['class' => 'form-control initialmaterialitypm','readonly' => 'true', 'style' => 'text-align: right;']) }}
                                                @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                @if(isset($valuemateriality->finalmaterialitypm))
                                                    {{ Form::text('finalmaterialitypm', number_format($valuemateriality->finalmaterialitypm), ['class' => 'form-control finalmaterialitypm','readonly' => 'true', 'style' => 'text-align: right;']) }}
                                                @else
                                                    {{ Form::text('finalmaterialitypm', null, ['class' => 'form-control finalmaterialitypm','readonly' => 'true', 'style' => 'text-align: right;']) }}
                                                @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                @if(isset($valuemateriality->pmrate))
                                                    {{ Form::text('pmrate', $valuemateriality->pmrate, ['class' => 'form-control pmrate', 'style' => 'text-align: right;']) }}
                                                @else
                                                    {{ Form::text('pmrate', null, ['class' => 'form-control pmrate', 'style' => 'text-align: right;']) }}
                                                @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="row">
                                <div class="col-sm-3 col-md-4">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::text('te', 'Tolerable Error', ['class' => 'form-control', 'readonly' => 'true']) }}
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                @if(isset($valuemateriality->initialmaterialityte))
                                                    {{ Form::text('initialmaterialityte', number_format($valuemateriality->initialmaterialityte), ['class' => 'form-control initialmaterialityte','readonly' => 'true', 'style' => 'text-align: right;']) }}
                                                @else
                                                    {{ Form::text('initialmaterialityte', null, ['class' => 'form-control initialmaterialityte','readonly' => 'true', 'style' => 'text-align: right;']) }}
                                                @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                @if(isset($valuemateriality->finalmaterialityte))
                                                    {{ Form::text('finalmaterialityte', number_format($valuemateriality->finalmaterialityte), ['class' => 'form-control finalmaterialityte','readonly' => 'true', 'style' => 'text-align: right;']) }}
                                                @else
                                                    {{ Form::text('finalmaterialityte', null, ['class' => 'form-control finalmaterialityte','readonly' => 'true', 'style' => 'text-align: right;']) }}
                                                @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                @if(isset($valuemateriality->terate))
                                                    {{ Form::text('terate', $valuemateriality->terate, ['class' => 'form-control terate', 'style' => 'text-align: right;']) }}
                                                @else
                                                    {{ Form::text('terate', null, ['class' => 'form-control terate', 'style' => 'text-align: right;']) }}
                                                @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-header">
                        <h6 class="mb-0">{{__('Response From AI')}}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                                <div class="col-sm-12 col-md-12">
                                    @if(isset($respons->response ))
                                        <div class="form-group">
                                            <p class="formatted-text">
                                                {{ $respons->response }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-header"><h6 class="mb-0">{{__('Auditor Notes')}}</h6>
                    <br>
                    <p>
                        <strong>
                                Reference : https://
                        </strong>
                    </p>
                    <p>
                        Berdasarkan hasil diskusi, materialitas pada tingkat laporan keuangan ditentukan sebesar
                        <strong>
                            @if(isset($valuemateriality) && is_object($valuemateriality))
                                {{$valuemateriality->rate ?? ''}}
                            @else
                                N/A
                            @endif
                        %</strong>
                        dari
                        <strong>
                            @if(isset($valuemateriality) && is_object($valuemateriality) && isset($valuemateriality->materiality))
                                {{$valuemateriality->materiality->name ?? ''}}
                            @else
                                N/A
                            @endif
                        .</strong>
                    </p>
                    
                    </div>
                    <div class="card-body">
                        <div class="row">
                                <div class="col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <div class="col-sm-12 col-md-12">
                                                <div class="form-group">
                                                @if(isset($valuemateriality->description))
                                                    {{ Form::textarea('description', $valuemateriality->description, ['class' => 'form-control description']) }}
                                                @else
                                                    {{ Form::textarea('description', null, ['class' => 'form-control description']) }}
                                                @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="submit" value="{{__('Save')}}" class="btn btn-simpan  btn-primary">
    </div>
{{ Form::close() }}
@endsection
