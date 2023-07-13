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
    <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>


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
    <div class="float-end">
            @can('create project task')
                <a href="{{ route('projects.tasks.create.summary.identified.misstatements',[$project->id, $task->id]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Add Journal')}}">
                    <i class="ti ti-plus"></i>
                </a>
            @endcan
    </div>

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
                                            <th style="text-align: center;" scope="col">{{'Description of misstatement'}}</th>
                                            <th style="text-align: center;" scope="col">{{'Period'}}</th>
                                            <th style="text-align: center;  white-space: normal;" scope="col">{{'Type of misstatement'}}</th>
                                            <th style="text-align: center;  white-space: normal;" scope="col">{{'Corrected / Uncorrected'}}</th>
                                            <th style="text-align: center;" scope="col">{{'Assets'}}</th>
                                            <th style="text-align: center;" scope="col">{{'Liability'}}</th>
                                            <th style="text-align: center;" scope="col">{{'Equity'}}</th>
                                            <th style="text-align: center;" scope="col">{{'Income'}}</th>
                                            <th style="text-align: center;" scope="col">{{'RE'}}</th>
                                            <th style="text-align: center;" scope="col">{{'Cause of misstatement and related control deficiency'}}</th>
                                            <th style="text-align: center;" scope="col">{{'Managements reasons for not correcting'}}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @if(count(array($identifiedmisstatements)) > 0)
                                            @foreach($identifiedmisstatements as $identifiedmisstatements)
                                                <tr>
                                                    {{-- <td style="border: 1px solid black; white-space: pre-wrap;">{{ $identifiedmisstatements->description }}</td>
                                                    <td style="border: 1px solid black; text-align: center;">{{ $identifiedmisstatements->period }}</td>
                                                    <td style="border: 1px solid black; text-align: center;">{{ $identifiedmisstatements->type_misstatement }}</td>
                                                    <td style="border: 1px solid black; text-align: center;">{{ $identifiedmisstatements->corrected }}</td>
                                                    <td style="border: 1px solid black; text-align: right;">{{ !empty(number_format($identifiedmisstatements->assets))? number_format($identifiedmisstatements->assets):'-' }}</td>
                                                    <td style="border: 1px solid black; text-align: right;">{{ !empty(number_format($identifiedmisstatements->liability))? number_format($identifiedmisstatements->liability):'-' }}</td>
                                                    <td style="border: 1px solid black; text-align: right;">{{ !empty(number_format($identifiedmisstatements->equity))? number_format($identifiedmisstatements->equity):'-' }}</td>
                                                    <td style="border: 1px solid black; text-align: right;">{{ !empty(number_format($identifiedmisstatements->income))? number_format($identifiedmisstatements->income):'-' }}</td>
                                                    <td style="border: 1px solid black; text-align: right;">{{ !empty(number_format($identifiedmisstatements->re))? number_format($identifiedmisstatements->re):'-' }}</td>
                                                    <td style="border: 1px solid black; white-space: pre-wrap;">{{ $identifiedmisstatements->cause_of_misstatement }}</td>
                                                    <td style="border: 1px solid black; white-space: pre-wrap;">{{ $identifiedmisstatements->managements_reason }}</td> --}}
                                                    <td class="form-group">
                                                            {{ Form::textarea('description',$identifiedmisstatements->description, array('class' => 'form-control description','readonly'=>'true','style' => 'width: 500px; height: 100px;','placeholder'=>__('Description'))) }}
                                                    </td>
                                                    <td class="form-group">
                                                        <select class="form-control select" name="period" id="period" style = "width: 250px;" onchange="updatePeriod(this.value, {{ $identifiedmisstatements->id }})">
                                                            @foreach(\App\Models\ProjectTask::$period as $key => $val)
                                                                <option value="{{ $key }}" {{ ($key == $identifiedmisstatements->period) ? 'selected' : '' }} >{{ __($val) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="form-group">
                                                        <select class="form-control select" name="type_misstatement" id="type_misstatement" style = "width: 250px;" onchange="updateType(this.value, {{ $identifiedmisstatements->id }})">
                                                            @foreach(\App\Models\ProjectTask::$type as $key => $val)
                                                                <option value="{{ $key }}" {{ ($key == $identifiedmisstatements->type_misstatement) ? 'selected' : '' }} >{{ __($val) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                     <td class="form-group">
                                                        <select class="form-control select" name="corrected" id="corrected" style = "width: 250px;" onchange="updateCorrected(this.value, {{ $identifiedmisstatements->id }})">
                                                            @foreach(\App\Models\ProjectTask::$corrected as $key => $val)
                                                                <option value="{{ $key }}" {{ ($key == $identifiedmisstatements->corrected) ? 'selected' : '' }} >{{ __($val) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="form-group">
                                                        {{ Form::text('assets',(!empty(number_format($identifiedmisstatements->assets))) ? (number_format($identifiedmisstatements->assets) < 0 ? '('.number_format(abs($identifiedmisstatements->assets)).')' : number_format($identifiedmisstatements->assets)) : '-',
                                                            array('class' => 'form-control assets','readonly'=>'true', 'style' => 'width: 250px;  text-align: right;', 'placeholder' => __('Assets'))
                                                        )}}
                                                    </td>
                                                    <td class="form-group">
                                                        {{ Form::text('liability',(!empty(number_format($identifiedmisstatements->liability))) ? (number_format($identifiedmisstatements->liability) < 0 ? '('.number_format(abs($identifiedmisstatements->liability)).')' : number_format($identifiedmisstatements->liability)) : '-',
                                                            array('class' => 'form-control liability','readonly'=>'true', 'style' => 'width: 250px;  text-align: right;', 'placeholder' => __('Liability'))
                                                        )}}
                                                    </td>
                                                    <td class="form-group">
                                                        {{ Form::text('equity',(!empty(number_format($identifiedmisstatements->equity))) ? (number_format($identifiedmisstatements->equity) < 0 ? '('.number_format(abs($identifiedmisstatements->equity)).')' : number_format($identifiedmisstatements->equity)) : '-',
                                                            array('class' => 'form-control equity', 'readonly'=>'true', 'style' => 'width: 250px;  text-align: right;', 'placeholder' => __('Equity'))
                                                        )}}
                                                    </td>
                                                    <td class="form-group">
                                                        {{ Form::text('income',(!empty(number_format($identifiedmisstatements->income))) ? (number_format($identifiedmisstatements->income) < 0 ? '('.number_format(abs($identifiedmisstatements->income)).')' : number_format($identifiedmisstatements->income)) : '-',
                                                            array('class' => 'form-control income','readonly'=>'true', 'style' => 'width: 250px;  text-align: right;', 'placeholder' => __('Income'))
                                                        )}}
                                                    </td>
                                                    <td class="form-group">
                                                        {{ Form::text('re',(!empty(number_format($identifiedmisstatements->re))) ? (number_format($identifiedmisstatements->re) < 0 ? '('.number_format(abs($identifiedmisstatements->re)).')' : number_format($identifiedmisstatements->re)) : '-',
                                                            array('class' => 'form-control re','readonly'=>'true', 'style' => 'width: 250px;  text-align: right;', 'placeholder' => __('RE'))
                                                        )}}
                                                    </td>
                                                    <td class="form-group">
                                                            {{ Form::textarea('cause_of_misstatement',$identifiedmisstatements->cause_of_misstatement, array('class' => 'form-control cause_of_misstatement','readonly'=>'true','style' => 'width: 500px;height: 100px;','placeholder'=>__('Cause Of Misstatement and Related Control Deficiency'))) }}
                                                    </td>
                                                    <td class="form-group">
                                                            {{ Form::textarea('managements_reason',$identifiedmisstatements->managements_reason, array('class' => 'form-control managements_reason','readonly'=>'true','style' => 'width: 500px; height: 100px;','placeholder'=>__('Management Reason'))) }}
                                                    </td>
                                                    <td>
                                                        @can('edit project task')
                                                        <div class="action-btn bg-primary ms-2">
                                                                <a href="{{ route('summary.identified.misstatements.edit', [$project->id, \Crypt::encrypt($task->id), $identifiedmisstatements->id]) }}"
                                                                   class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Edit "
                                                                   data-original-title="{{ __('Edit') }}">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('edit project task')
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => array('summary.identified.misstatements.delete',[$project->id, \Crypt::encrypt($task->id), $identifiedmisstatements->id]),'class'=>'delete-form-btn','id'=>'delete-form-'.$identifiedmisstatements->id]) !!}
                                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$identifiedmisstatements->id}}').submit();">
                                                                            <i class="ti ti-trash text-white"></i>
                                                                        </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <th scope="col" colspan="7">
                                                    <h6 class="text-center">{{__('No Summary Identified Misstatements Data Found')}}</h6>
                                                </th>
                                            </tr>
                                        @endif
                                        <tr style="background-color: #008b8b;">
                                            <th style="border: 1px solid black; color:white; font-weight: bold;" scope="col" colspan="4">Total of identified misstatements during the audit</th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">{{ (!empty(number_format($identifiedmisstatements->sum('assets')))) ? (number_format($identifiedmisstatements->sum('assets')) < 0 ? '('.number_format(abs($identifiedmisstatements->sum('assets'))).')' : number_format($identifiedmisstatements->sum('assets'))) : '-' }}</th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">{{ (!empty(number_format($identifiedmisstatements->sum('liability')))) ? (number_format($identifiedmisstatements->sum('liability')) < 0 ? '('.number_format(abs($identifiedmisstatements->sum('liability'))).')' : number_format($identifiedmisstatements->sum('liability'))) : '-' }}</th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">{{ (!empty(number_format($identifiedmisstatements->sum('equity')))) ? (number_format($identifiedmisstatements->sum('equity')) < 0 ? '('.number_format(abs($identifiedmisstatements->sum('equity'))).')' : number_format($identifiedmisstatements->sum('equity'))) : '-' }}</th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">{{ (!empty(number_format($identifiedmisstatements->sum('income')))) ? (number_format($identifiedmisstatements->sum('income')) < 0 ? '('.number_format(abs($identifiedmisstatements->sum('income'))).')' : number_format($identifiedmisstatements->sum('income'))) : '-' }}</th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">{{ (!empty(number_format($identifiedmisstatements->sum('re')))) ? (number_format($identifiedmisstatements->sum('re')) < 0 ? '('.number_format(abs($identifiedmisstatements->sum('re'))).')' : number_format($identifiedmisstatements->sum('re'))) : '-' }}</th>
                                        </tr>
                                        <tr style="background-color: #008b8b;">
                                            <th style="border: 1px solid black; color:white; font-weight: bold;" scope="col" colspan="4">Misstatements corrected by management</th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">
                                                {{
                                                    (!empty(number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('assets')))) ? (number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('assets')) < 0 ? '('.number_format(abs($identifiedmisstatements->where('corrected', 'corrected')->sum('assets'))).')' : number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('assets'))) : '-'
                                                }}
                                            </th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">
                                                {{
                                                    (!empty(number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('liability')))) ? (number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('liability')) < 0 ? '('.number_format(abs($identifiedmisstatements->where('corrected', 'corrected')->sum('liability'))).')' : number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('liability'))) : '-'
                                                }}
                                            </th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">
                                                {{
                                                    (!empty(number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('equity')))) ? (number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('equity')) < 0 ? '('.number_format(abs($identifiedmisstatements->where('corrected', 'corrected')->sum('equity'))).')' : number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('equity'))) : '-'
                                                }}
                                            </th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">
                                                {{
                                                    (!empty(number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('income')))) ? (number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('income')) < 0 ? '('.number_format(abs($identifiedmisstatements->where('corrected', 'corrected')->sum('income'))).')' : number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('income'))) : '-'
                                                }}
                                            </th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">
                                                {{
                                                    (!empty(number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('re')))) ? (number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('re')) < 0 ? '('.number_format(abs($identifiedmisstatements->where('corrected', 'corrected')->sum('re'))).')' : number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('re'))) : '-'
                                                }}
                                            </th>
                                        </tr>
                                        <tr style="background-color: #008b8b;">
                                            <th style="border: 1px solid black; color:white; font-weight: bold;" scope="col" colspan="4">Total uncorrected misstatements</th>
                                             <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">
                                                {{
                                                    (!empty(number_format($identifiedmisstatements->where('corrected', 'uncorrected')->sum('assets')))) ? (number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('assets')) < 0 ? '('.number_format(abs($identifiedmisstatements->where('corrected', 'corrected')->sum('assets'))).')' : number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('assets'))) : '-'
                                                }}
                                            </th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">
                                                {{
                                                    (!empty(number_format($identifiedmisstatements->where('corrected', 'uncorrected')->sum('liability')))) ? (number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('liability')) < 0 ? '('.number_format(abs($identifiedmisstatements->where('corrected', 'corrected')->sum('liability'))).')' : number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('liability'))) : '-'
                                                }}
                                            </th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">
                                                {{
                                                    (!empty(number_format($identifiedmisstatements->where('corrected', 'uncorrected')->sum('equity')))) ? (number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('equity')) < 0 ? '('.number_format(abs($identifiedmisstatements->where('corrected', 'corrected')->sum('equity'))).')' : number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('equity'))) : '-'
                                                }}
                                            </th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">
                                                {{
                                                    (!empty(number_format($identifiedmisstatements->where('corrected', 'uncorrected')->sum('income')))) ? (number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('income')) < 0 ? '('.number_format(abs($identifiedmisstatements->where('corrected', 'corrected')->sum('income'))).')' : number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('income'))) : '-'
                                                }}
                                            </th>
                                            <th style="border: 1px solid black;text-align: right;  color:white; font-weight: bold;">
                                                {{
                                                    (!empty(number_format($identifiedmisstatements->where('corrected', 'uncorrected')->sum('re')))) ? (number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('re')) < 0 ? '('.number_format(abs($identifiedmisstatements->where('corrected', 'corrected')->sum('re'))).')' : number_format($identifiedmisstatements->where('corrected', 'corrected')->sum('re'))) : '-'
                                                }}
                                            </th>
                                        </tr>
                                    </tbody>

                            </table>

                            <script>
                                function updatePeriod(period, id) {
                                    // Kirim request POST ke server dengan nilai status dan id data yang dipilih
                                    $.ajax({
                                        url: "{{route("update-period")}}",
                                        type: "POST",
                                        data: { 
                                            id: id,
                                            period: period,
                                            // Add the CSRF token to the request data
                                            _token: "{{ csrf_token() }}"
                                        },
                                        success: function (data) {
                                            //console.log(data);
                                        },
                                    });
                                }

                                function updateType(type_misstatement, id) {
                                    // Kirim request POST ke server dengan nilai status dan id data yang dipilih
                                    $.ajax({
                                        url: "{{route("update-type_misstatement")}}",
                                        type: "POST",
                                        data: { 
                                            id: id,
                                            type_misstatement: type_misstatement,
                                            // Add the CSRF token to the request data
                                            _token: "{{ csrf_token() }}"
                                        },
                                        success: function (data) {
                                            //console.log(data);
                                        },
                                    });
                                }

                                function updateCorrected(corrected, id) {
                                    // Kirim request POST ke server dengan nilai status dan id data yang dipilih
                                    $.ajax({
                                        url: "{{route("update-corrected")}}",
                                        type: "POST",
                                        data: { 
                                            id: id,
                                            corrected: corrected,
                                            // Add the CSRF token to the request data
                                            _token: "{{ csrf_token() }}"
                                        },
                                        success: function (data) {
                                            //console.log(data);
                                        },
                                    });
                                }
                            </script>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{ Form::open(['route' => ['notes.analysis', [$project->id, $task->id]], 'method' => 'post']) }}
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="col-12">
                        <div class="card-header"><h6 class="mb-0">{{__('Auditor Notes')}}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                    <div class="col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <div class="col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                    @if(isset($notesanalysis->notes))
                                                        {{ Form::textarea('notes', $notesanalysis->notes, ['class' => 'form-control notes']) }}
                                                    @else
                                                        {{ Form::textarea('notes', null, ['class' => 'form-control notes']) }}
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
