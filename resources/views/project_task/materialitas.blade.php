@extends('layouts.admin')
@section('page-title')
    {{ucwords($task->name)}}
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
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

            //hasil materialitas awal overall materiality
            var materialitasAwal = (totalMaterialitasAwal !== 0 ? totalMaterialitasAwal.toLocaleString('en-EN') : '-');
            $('.initialmaterialityom').val(materialitasAwal);

            //hasil materialitas akhir overall materiality
            var materialitasAkhir = (totalMaterialitasAkhir !== 0 ? totalMaterialitasAkhir.toLocaleString('en-EN') : '-');
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
            var initialmaterialitypm = (totalinitialmaterialitypm !== 0 ? totalinitialmaterialitypm.toLocaleString('en-EN') : '-');
            $('.initialmaterialitypm').val(initialmaterialitypm);

            //hasil materialitas akhir performance materiality
            var finalmaterialitypm = (totalfinalmaterialitypm !== 0 ? totalfinalmaterialitypm.toLocaleString('en-EN') : '-');
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
            var initialmaterialityte = (totalinitialmaterialityte !== 0 ? totalinitialmaterialityte.toLocaleString('en-EN') : '-');
            $('.initialmaterialityte').val(initialmaterialityte);

            //hasil materialitas akhir tolerable error
            var finalmaterialityte = (totalfinalmaterialityte !== 0 ? totalfinalmaterialityte.toLocaleString('en-EN') : '-');
            $('.finalmaterialityte').val(finalmaterialityte);
        });

        function simpanData(materialitas_id, rate, pmrate, terate, initialom, finalom, initialpm, finalpm, initialte, finalte, description) 
        {
            // Mengirim data ke server menggunakan Ajax
            $.ajax({
                type: 'POST',
                url: '/save-summary-materiality',
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
{{ Form::open(['route' => ['summary.materialitas'], 'method' => 'post']) }}
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
                                        @if($materiality->name == 'LABA BRUTO' || $materiality->name == 'LABA OPERASI' || $materiality->name == 'LABA SEBELUM PAJAK')
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
                                        <p style= 'text-align:center'><b>{{date(' Y', strtotime('-3 year', strtotime($project->book_year)))}}</b></p>
                                        @foreach($data_array_2020 as $key => $data_2020)
                                        @if($key == '8' || $key == '9' || $key == '10')
                                            <div class="col-sm-3 col-md-12">    
                                                <div class="form-group">
                                                    {{ Form::text('2020', number_format($data_2020), ['class' => 'form-control','readonly'=>'true','style' => 'background-color:#008b8b; color:white; font-weight: bold;']) }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::text('2020', number_format($data_2020), ['class' => 'form-control','readonly'=>'true']) }}
                                                </div>
                                            </div>
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>{{date(' Y', strtotime('-2 year', strtotime($project->book_year)))}}</b></p>
                                        @foreach($data_array_2021 as $key => $data_2021)
                                            @if($key == '8' || $key == '9' || $key == '10')
                                            <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::text('2021', number_format($data_2021), ['class' => 'form-control','readonly'=>'true','style' => 'background-color:#008b8b; color:white; font-weight: bold;']) }}
                                                </div>
                                            </div>
                                            @else
                                                <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        {{ Form::text('2021', number_format($data_2021), ['class' => 'form-control','readonly'=>'true']) }}
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>{{'Inhouse ' . $project->book_year}}</b></p>
                                        @foreach($data_array_in_2022 as $key => $data_in_2022)
                                            @if($key == '8' || $key == '9' || $key == '10')
                                            <div class="col-sm-3 col-md-12">    
                                                <div class="form-group">
                                                    {{ Form::text('inhouse', number_format($data_in_2022), ['class' => 'form-control','readonly'=>'true','style' => 'background-color:#008b8b; color:white; font-weight: bold;']) }}
                                                </div>
                                            </div>
                                            @else
                                                <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        {{ Form::text('inhouse', number_format($data_in_2022), ['class' => 'form-control','readonly'=>'true']) }}
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>{{'Audited ' . $project->book_year}}</b></p>
                                        @foreach($data_array_au_2022 as $key => $data_au_2022)
                                            @if($key == '8' || $key == '9' || $key == '10')
                                            <div class="col-sm-3 col-md-12">    
                                                <div class="form-group">
                                                    {{ Form::text('audited', number_format($data_au_2022), ['class' => 'form-control','readonly'=>'true','style' => 'background-color:#008b8b; color:white; font-weight: bold;']) }}
                                                </div>
                                            </div>
                                            @else
                                                <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        {{ Form::text('audited', number_format($data_au_2022), ['class' => 'form-control','readonly'=>'true']) }}
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
                    <div class="card-header"><h6 class="mb-0">{{__('Rule of thumb penentuan margin (ISA Guidance 3th Edition) :')}}</h6></div>
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
                                                        {{ Form::text('rate', $valuemateriality->rate, ['class' => 'form-control rate']) }}
                                                    @else
                                                        {{ Form::text('rate', null, ['class' => 'form-control rate']) }}
                                                    @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group inhouse">
                                                    {{ Form::text('inhouse', '', array('class' => 'form-control inhouse', 'readonly' => true)) }}
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group audited">
                                                    {{ Form::text('audited', '', array('class' => 'form-control audited', 'readonly' => true)) }}
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
                    <div class="card-header"><h6 class="mb-0">{{__('Ringkasan Materialitas')}}</h6></div>
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
                                        <p style= 'text-align:center'><b>PM Rate % and TE Rate %</b></p>
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
                                                    {{ Form::text('initialmaterialityom', number_format($valuemateriality->initialmaterialityom), ['class' => 'form-control initialmaterialityom','readonly'=>'true']) }}
                                                @else
                                                    {{ Form::text('initialmaterialityom', null, ['class' => 'form-control initialmaterialityom','readonly'=>'true']) }}
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
                                                    {{ Form::text('finalmaterialityom',number_format($valuemateriality->finalmaterialityom),array('class' => 'form-control finalmaterialityom','readonly'=>'true')) }}
                                                @else
                                                    {{ Form::text('finalmaterialityom',null,array('class' => 'form-control finalmaterialityom','readonly'=>'true')) }}
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
                                                    {{ Form::text('initialmaterialitypm', number_format($valuemateriality->initialmaterialitypm), ['class' => 'form-control initialmaterialitypm','readonly' => 'true']) }}
                                                @else
                                                    {{ Form::text('initialmaterialitypm', null, ['class' => 'form-control initialmaterialitypm','readonly' => 'true']) }}
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
                                                    {{ Form::text('finalmaterialitypm', number_format($valuemateriality->finalmaterialitypm), ['class' => 'form-control finalmaterialitypm','readonly' => 'true']) }}
                                                @else
                                                    {{ Form::text('finalmaterialitypm', null, ['class' => 'form-control finalmaterialitypm','readonly' => 'true']) }}
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
                                                    {{ Form::text('pmrate', $valuemateriality->pmrate, ['class' => 'form-control pmrate']) }}
                                                @else
                                                    {{ Form::text('pmrate', null, ['class' => 'form-control pmrate']) }}
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
                                                    {{ Form::text('initialmaterialityte', number_format($valuemateriality->initialmaterialityte), ['class' => 'form-control initialmaterialityte','readonly' => 'true']) }}
                                                @else
                                                    {{ Form::text('initialmaterialityte', null, ['class' => 'form-control initialmaterialityte','readonly' => 'true']) }}
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
                                                    {{ Form::text('finalmaterialityte', number_format($valuemateriality->finalmaterialityte), ['class' => 'form-control finalmaterialityte','readonly' => 'true']) }}
                                                @else
                                                    {{ Form::text('finalmaterialityte', null, ['class' => 'form-control finalmaterialityte','readonly' => 'true']) }}
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
                                                    {{ Form::text('terate', $valuemateriality->terate, ['class' => 'form-control terate']) }}
                                                @else
                                                    {{ Form::text('terate', null, ['class' => 'form-control terate']) }}
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
                    <div class="card-header"><h6 class="mb-0">{{__('Auditor Notes')}}</h6></div>
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
    @if(empty($valuemateriality))
        <div class="modal-footer">
            <input type="submit" value="{{__('Save')}}" class="btn btn-simpan  btn-primary">
        </div>
    @endif
{{ Form::close() }}
@endsection
