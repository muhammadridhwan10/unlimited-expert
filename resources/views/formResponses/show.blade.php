@php
$loggedInUserId = auth()->user()->employee->id;
$userLogin = auth()->user()->id;
foreach($formResponses as $response)
{
    $isUser = $userLogin == $response->user_id;
    $isAppraisal = $loggedInUserId == $response->appraisal_id;
}

foreach($formResponses->where('form_type', 'A2') as $responses)
{
    $isSupervisor = $loggedInUserId == $responses->supervisor_id;
}
@endphp

@extends('layouts.admin')
@section('page-title')
    {{__('Assessment')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('form-response.index')}}">{{__('Personel Assessment')}}</a></li>
    <li class="breadcrumb-item">{{__('Assessment')}}</li>
@endsection
@push('script-page')
    <script>
        // Function to determine if the full season option should be unlocked
        function unlockFullSeason() {
            const today = new Date();
            const month = today.getMonth() + 1; // getMonth() returns 0-based month, so +1 to make it 1-based

            if (month === 9) {
                document.getElementById('full-season').disabled = false;
                document.getElementById('full-season-label').classList.remove('btn-locked');
            }
        }

        function showMidSeason() {
            document.getElementById('formC-tab').style.display = 'none';
            document.getElementById('formD-tab').style.display = 'none';
            document.getElementById('formE-tab').style.display = 'block';
            document.getElementById('formE_comment').style.display = 'none';
            document.getElementById('formE_evaluasi').style.display = 'block';
            document.getElementById('formE_pengembangan').style.display = 'none';
        }

        function showFullSeason() {
            document.getElementById('formE_comment').style.display = 'block';
            document.getElementById('formE_evaluasi').style.display = 'none';
            document.getElementById('formC-tab').style.display = 'block';
            document.getElementById('formD-tab').style.display = 'block';
            document.getElementById('formE-tab').style.display = 'block';
            document.getElementById('formE_pengembangan').style.display = 'block';
        }

        // Call unlockFullSeason on page load
        unlockFullSeason();
        // Initialize with Mid-Season selection
        showMidSeason();
    </script>
@endpush
@push('css-page')
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        td input[type="radio"] {
            margin: 0;
        }
        .criteria {
            text-align: left;
        }
        .comment-box {
            width: 100%;
            height: 50px;
        }
        .section-header {
            font-weight: bold;
            text-align: left;
            background-color: #e0e0e0;
        }
    </style>
@endpush
@section('content')
    <div class="container mt-4">
        <div class="btn-group btn-group-toggle mb-4" data-bs-toggle="buttons">
            <input type="radio" class="btn-check" name="options" id="mid-season" autocomplete="off" onclick="showMidSeason()" checked>
            <label class="btn btn-outline-primary" for="mid-season">Mid-Season</label>

            <input type="radio" class="btn-check" name="options" id="full-season" autocomplete="off" onclick="showFullSeason()" disabled>
            <label class="btn btn-outline-primary btn-locked" for="full-season" id="full-season-label">Full-Season</label>
        </div>

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="ms-0">
                                        <!-- Total Project -->
                                        <small class="text-muted">{{__('Total Project : ')}}</small>
                                        <span>{{ $totalProject }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center justify-content-between mt-2">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="ms-0">
                                        <!-- Total Training -->
                                        <small class="text-muted">{{__('Total Training : ')}}</small>
                                        <span>{{ $totalTraining }}</span>
                                        <a href="#" class="text-blue text-sm ms-3" data-size="lg" data-url="{{ route('employee.training', $id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('View Training')}}">{{ __('View') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center justify-content-between mt-2">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="ms-0">
                                        <!-- Total Sertifikasi -->
                                        <small class="text-muted">{{__('Total Sertifikasi : ')}}</small>
                                        <span>{{ $totalSertifikasi }}</span>
                                        <a href="#" class="text-blue text-sm ms-3" data-size="lg" data-url="{{ route('employee.training', $id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('View Sertifikasi')}}">{{ __('View') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <form action="{{ route('update.forms',$id) }}" method="POST">
            @csrf
            @method('PUT')
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                @if ($isAppraisal || $isUser || \Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="formA1-tab" data-bs-toggle="tab" data-bs-target="#formA1" type="button" role="tab" aria-controls="formA1" aria-selected="true">Form A1</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="formA2-tab" data-bs-toggle="tab" data-bs-target="#formA2" type="button" role="tab" aria-controls="formA2" aria-selected="{{ $isSupervisor ? 'true' : 'false' }}">Form A2</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="formC-tab" data-bs-toggle="tab" data-bs-target="#formC" type="button" role="tab" aria-controls="formC" aria-selected="false">Form C</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="formD-tab" data-bs-toggle="tab" data-bs-target="#formD" type="button" role="tab" aria-controls="formD" aria-selected="false">Form D</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="formE-tab" data-bs-toggle="tab" data-bs-target="#formE" type="button" role="tab" aria-controls="formE" aria-selected="false">Form E and Form B</button>
                    </li>
                @elseif ($isSupervisor)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="formA2-tab" data-bs-toggle="tab" data-bs-target="#formA2" type="button" role="tab" aria-controls="formA2" aria-selected="{{ $isSupervisor ? 'true' : 'false' }}">Form A2</button>
                    </li>
                @endif
            </ul>

            <div class="tab-content" id="myTabContent">

                @if ($isAppraisal || $isUser || \Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
                <!-- Form A1 -->
                <div class="tab-pane fade show active" id="formA1" role="tabpanel" aria-labelledby="formA1-tab">
                    <table class="mt-4">
                        <thead>
                            <tr>
                                <th>{{__('WORK TARGETS')}}</th>
                                <th>{{__('BENCHMARK / JOB DESC')}}</th>
                                <th>{{__('PERFORMANCE ACHIEVEMENT')}}</th>
                                <th>{{__('SELF-ASSESSMENT')}}</th>
                                <th>{{__('SUPERVISOR ASSESSMENT')}}</th>
                                <th>{{__('FINAL ASSESSMENT')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($formResponses->where('form_type', 'A1') as $response)
                            <tr>
                                <td>{{ $response->work_targets }}</td>
                                <td>{{ $response->criteria }}</td>
                                <td>{{ $response->performance_achievements }}</td>
                                <td>{{ $response->self_assessment }}</td>
                                @if($response->supervisor_assessment == NULL)
                                <td>
                                    <input type="radio" name="formA1_supervisor[{{ $response->id }}]" value="1" {{ $response->supervisor_assessment == 1 ? 'checked' : '' }}> 1
                                    <input type="radio" name="formA1_supervisor[{{ $response->id }}]" value="2" {{ $response->supervisor_assessment == 2 ? 'checked' : '' }}> 2
                                    <input type="radio" name="formA1_supervisor[{{ $response->id }}]" value="3" {{ $response->supervisor_assessment == 3 ? 'checked' : '' }}> 3
                                    <input type="radio" name="formA1_supervisor[{{ $response->id }}]" value="4" {{ $response->supervisor_assessment == 4 ? 'checked' : '' }}> 4
                                </td>
                                @else
                                <td>{{ $response->supervisor_assessment }}</td>
                                @endif
                                @if($response->final_assessment == NULL)
                                <td>
                                    <input type="radio" name="formA1_final[{{ $response->id }}]" value="1" {{ $response->final_assessment == 1 ? 'checked' : '' }}> 1
                                    <input type="radio" name="formA1_final[{{ $response->id }}]" value="2" {{ $response->final_assessment == 2 ? 'checked' : '' }}> 2
                                    <input type="radio" name="formA1_final[{{ $response->id }}]" value="3" {{ $response->final_assessment == 3 ? 'checked' : '' }}> 3
                                    <input type="radio" name="formA1_final[{{ $response->id }}]" value="4" {{ $response->final_assessment == 4 ? 'checked' : '' }}> 4
                                </td>
                                @else
                                <td>{{ $response->final_assessment }}</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Form A2 -->
                <div class="tab-pane fade" id="formA2" role="tabpanel" aria-labelledby="formA2-tab">
                    <table class="mt-4">
                        <thead>
                            <tr>
                                <th>{{__('PROJECT NAME')}}</th>
                                <th>{{__('SUPERVISOR NAME')}}</th>
                                <th>{{__('WORK TARGETS')}}</th>
                                <th>{{__('BENCHMARK / JOB DESC')}}</th>
                                <th>{{__('PERFORMANCE ACHIEVEMENT')}}</th>
                                <th>{{__('SELF-ASSESSMENT')}}</th>
                                <th>{{__('SUPERVISOR ASSESSMENT')}}</th>
                                <th>{{__('FINAL ASSESSMENT')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($formResponses->where('form_type', 'A2') as $response)
                            <tr>
                                <td>{{ $response->project_name }}</td>
                                <td>{{ $response->employee->name }}</td>
                                <td>{{ $response->work_targets }}</td>
                                <td>{{ $response->criteria }}</td>
                                <td>{{ $response->performance_achievements }}</td>
                                <td>{{ $response->self_assessment }}</td>
                                @if($response->supervisor_assessment == NULL)
                                <td>
                                    <input type="radio" name="formA2_supervisor[{{ $response->id }}]" value="1" {{ $response->supervisor_assessment == 1 ? 'checked' : '' }}> 1
                                    <input type="radio" name="formA2_supervisor[{{ $response->id }}]" value="2" {{ $response->supervisor_assessment == 2 ? 'checked' : '' }}> 2
                                    <input type="radio" name="formA2_supervisor[{{ $response->id }}]" value="3" {{ $response->supervisor_assessment == 3 ? 'checked' : '' }}> 3
                                    <input type="radio" name="formA2_supervisor[{{ $response->id }}]" value="4" {{ $response->supervisor_assessment == 4 ? 'checked' : '' }}> 4
                                </td>
                                @else
                                <td>{{ $response->supervisor_assessment }}</td>
                                @endif
                                @if($response->final_assessment == NULL)
                                <td>
                                    <input type="radio" name="formA2_final[{{ $response->id }}]" value="1" {{ $response->final_assessment == 1 ? 'checked' : '' }}> 1
                                    <input type="radio" name="formA2_final[{{ $response->id }}]" value="2" {{ $response->final_assessment == 2 ? 'checked' : '' }}> 2
                                    <input type="radio" name="formA2_final[{{ $response->id }}]" value="3" {{ $response->final_assessment == 3 ? 'checked' : '' }}> 3
                                    <input type="radio" name="formA2_final[{{ $response->id }}]" value="4" {{ $response->final_assessment == 4 ? 'checked' : '' }}> 4
                                </td>
                                @else
                                <td>{{ $response->final_assessment }}</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="formC" role="tabpanel" aria-labelledby="formC-tab">
                    <table class="mt-4">
                        <thead>
                            <tr>
                                <th>{{__('CRITERIA')}}</th>
                                <th>{{__('SELF-ASSESSMENT')}}</th>
                                <th>{{__('SUPERVISOR ASSESSMENT')}}</th>
                                <th>{{__('FINAL ASSESSMENT')}}</th>
                                <th>{{__('COMMENTS')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($formResponses->where('form_type', 'C')->groupBy('criteria_id') as $criteriaId => $responses)
                                @php
                                    $criteria = \App\Models\Criteria::find($criteriaId);
                                @endphp
                                @if($criteria)
                                    @if($loop->first || $criteria->section !== \App\Models\Criteria::find($formResponses->where('form_type', 'C')->pluck('criteria_id')[$loop->index - 1])->section)
                                        <tr>
                                            <td class="section-header" colspan="14">{{ $criteria->section }}</td>
                                        </tr>
                                    @endif
                                    @foreach($responses as $response)
                                    <tr>
                                        <td>{{ $criteria->criteria }}</td>
                                        <td>{{ $response->self_assessment }}</td>
                                        @if($response->supervisor_assessment == NULL)
                                        <td>
                                            <input type="radio" name="formC_supervisor[{{ $response->id }}]" value="1" {{ $response->supervisor_assessment == 1 ? 'checked' : '' }}> 1
                                            <input type="radio" name="formC_supervisor[{{ $response->id }}]" value="2" {{ $response->supervisor_assessment == 2 ? 'checked' : '' }}> 2
                                            <input type="radio" name="formC_supervisor[{{ $response->id }}]" value="3" {{ $response->supervisor_assessment == 3 ? 'checked' : '' }}> 3
                                            <input type="radio" name="formC_supervisor[{{ $response->id }}]" value="4" {{ $response->supervisor_assessment == 4 ? 'checked' : '' }}> 4
                                        </td>
                                        @else
                                        <td>{{ $response->supervisor_assessment }}</td>
                                        @endif
                                        @if($response->final_assessment == NULL)
                                        <td>
                                            <input type="radio" name="formC_final[{{ $response->id }}]" value="1" {{ $response->final_assessment == 1 ? 'checked' : '' }}> 1
                                            <input type="radio" name="formC_final[{{ $response->id }}]" value="2" {{ $response->final_assessment == 2 ? 'checked' : '' }}> 2
                                            <input type="radio" name="formC_final[{{ $response->id }}]" value="3" {{ $response->final_assessment == 3 ? 'checked' : '' }}> 3
                                            <input type="radio" name="formC_final[{{ $response->id }}]" value="4" {{ $response->final_assessment == 4 ? 'checked' : '' }}> 4
                                        </td>
                                        @else
                                        <td>{{ $response->final_assessment }}</td>
                                        @endif
                                        @if($response->comments == NULL)
                                        <!-- Komentar for Form C -->
                                        <td>
                                            <textarea name="formC_comment[{{ $response->id }}]">{{ $response->comment }}</textarea>
                                        </td>
                                        <!-- Comments -->
                                        @else
                                        <td>{{ $response->comments }}</td>
                                        @endif
                                    </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="formD" role="tabpanel" aria-labelledby="formD-tab">
                    <table class="mt-4">
                        <thead>
                            <tr>
                                <th>{{__('CRITERIA')}}</th>
                                <th>{{__('SELF-ASSESSMENT')}}</th>
                                <th>{{__('SUPERVISOR ASSESSMENT')}}</th>
                                <th>{{__('FINAL ASSESSMENT')}}</th>
                                <th>{{__('COMMENTS')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($formResponses->where('form_type', 'D')->groupBy('criteria_id') as $criteriaId => $responses)
                                @php
                                    $criteria = \App\Models\Criteria::find($criteriaId);
                                @endphp
                                @if($criteria)
                                    @if($loop->first || $criteria->section !== \App\Models\Criteria::find($formResponses->where('form_type', 'D')->pluck('criteria_id')[$loop->index - 1])->section)
                                        <tr>
                                            <td class="section-header" colspan="14">{{ $criteria->section }}</td>
                                        </tr>
                                    @endif
                                    @foreach($responses as $response)
                                    <tr>
                                        <td>{{ $criteria->criteria }}</td>
                                        <td>{{ $response->self_assessment }}</td>
                                        @if($response->supervisor_assessment == NULL)
                                        <td>
                                            <input type="radio" name="formD_supervisor[{{ $response->id }}]" value="1" {{ $response->supervisor_assessment == 1 ? 'checked' : '' }}> 1
                                            <input type="radio" name="formD_supervisor[{{ $response->id }}]" value="2" {{ $response->supervisor_assessment == 2 ? 'checked' : '' }}> 2
                                            <input type="radio" name="formD_supervisor[{{ $response->id }}]" value="3" {{ $response->supervisor_assessment == 3 ? 'checked' : '' }}> 3
                                            <input type="radio" name="formD_supervisor[{{ $response->id }}]" value="4" {{ $response->supervisor_assessment == 4 ? 'checked' : '' }}> 4
                                        </td>
                                        @else
                                        <td>{{ $response->supervisor_assessment }}</td>
                                        @endif
                                        @if($response->final_assessment == NULL)
                                        <td>
                                            <input type="radio" name="formD_final[{{ $response->id }}]" value="1" {{ $response->final_assessment == 1 ? 'checked' : '' }}> 1
                                            <input type="radio" name="formD_final[{{ $response->id }}]" value="2" {{ $response->final_assessment == 2 ? 'checked' : '' }}> 2
                                            <input type="radio" name="formD_final[{{ $response->id }}]" value="3" {{ $response->final_assessment == 3 ? 'checked' : '' }}> 3
                                            <input type="radio" name="formD_final[{{ $response->id }}]" value="4" {{ $response->final_assessment == 4 ? 'checked' : '' }}> 4
                                        </td>
                                        @else
                                        <td>{{ $response->final_assessment }}</td>
                                        @endif
                                        @if($response->comments == NULL)
                                        <td>
                                            <textarea name="formD_comment[{{ $response->id }}]">{{ $response->comment }}</textarea>
                                        </td>
                                        @else
                                        <td>{{ $response->comments }}</td>
                                        @endif
                                    </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="tab-pane fade" id="formE" role="tabpanel" aria-labelledby="formE-tab">
                    <table class="mt-4">
                        <thead>
                            <tr>
                                <th>{{__('FORM')}}</th>
                                <th>{{__('AVERAGE SCORE')}}</th>
                                <th>{{__('WEIGHT')}}</th>
                                <th>{{__('FINAL SCORE')}}</th>
                            </tr>
                        </thead>
                    <tbody>
                            <tr>
                                <td>{{__('Work Target Achievement (Form A1 & A2)')}}</td>
                                <td>{{ number_format($combinedAverageA, 2) }}</td>
                                <td>{{__('80%')}}</td>
                                <td>{{ number_format($finalA, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{__('Core Competencies (Form C)')}}</td>
                                <td>{{ number_format($averageC, 2) }}</td>
                                <td>{{__('10%')}}</td>
                                <td>{{ number_format($finalC, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{__('Managerial Competence (Form D)')}}</td>
                                <td>{{ number_format($averageD, 2) }}</td>
                                <td>{{__('10%')}}</td>
                                <td>{{ number_format($finalD, 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">{{__('TOTAL FINAL SCORE')}}</th>
                                <th>{{ number_format($totalFinal, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="mt-4" id="formE_comment">
                        @if($formResponses->where('form_type', 'E')->where('comment', '!=', '')->count() == 0)
                        <h5>{{__('COMMENTS')}}</h5>
                        <textarea name="formE_comment" class="form-control" rows="4">{{ old('formE_comment') }}</textarea>
                        @else
                        <h5>{{__('COMMENTS')}}</h5>
                        <p>{{ $commentE->comment ?? 'Tidak ada komentar' }}</p>
                        @endif
                    </div>
                    <br>

                    <div id="formE_pengembangan">
                        @if($formResponses->where('form_type', 'E')->where('advantages', '!=', '')->count() == 0)
                        <div class="mt-4">
                            <h5>{{__('STRENGTHS / ADVANTAGES')}}</h5>
                            <textarea name="formE_kelebihan" class="form-control" rows="4">{{ old('formE_kelebihan') }}</textarea>
                        </div>
                        <div class="mt-4">
                            <h5>{{__('AREAS FOR IMPROVEMENT')}}</h5>
                            <textarea name="formE_tingkatan" class="form-control" rows="4">{{ old('formE_tingkatan') }}</textarea>
                        </div>
                        <div class="mt-4">
                            <h5>{{__('DEVELOPMENT / TRAINING PLAN')}}</h5>
                            <textarea name="formE_pelatihan" class="form-control" rows="4">{{ old('formE_pelatihan') }}</textarea>
                        </div>
                        @else
                        <h4>{{__('Development Plan')}}</h4>
                        <table class="mt-4">
                            <thead>
                                <tr>
                                    <th>{{__('STRENGTHS / ADVANTAGES')}}</th>
                                    <th>{{__('AREAS FOR IMPROVEMENT')}}</th>
                                    <th>{{__('DEVELOPMENT / TRAINING PLAN')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formResponses->where('form_type', 'E') as $response)
                                <tr>
                                    <td>{!! nl2br(e($response->advantages)) !!}</td>
                                    <td>{!! nl2br(e($response->tiers)) !!}</td>
                                    <td>{!! nl2br(e($response->training_plan)) !!}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                        <button type="button" class="btn btn-success mt-2" onclick="window.location.href='{{ route('download.pdf', $id) }}'">Cetak PDF</button>
                    </div>

                    <div class="mt-4" id="formE_evaluasi">
                        @if($formResponses->where('form_type', 'E')->where('performance_progress', '!=', '')->count() == 0)
                        <div class="mt-4">
                            <h5>{{__('PERFORMANCE PROGRESS')}}</h5>
                            <textarea name="formE_kemajuan_kinerja" class="form-control" rows="4">{{ old('formE_kemajuan_kinerja') }}</textarea>
                        </div>
                        <div class="mt-4">
                            <h5>{{__('BARRIERS TO ACHIEVING TARGETS')}}</h5>
                            <textarea name="formE_hambatan" class="form-control" rows="4">{{ old('formE_hambatan') }}</textarea>
                        </div>
                        <div class="mt-4">
                            <h5>{{__('FOLLOW-UP ACTIONS')}}</h5>
                            <textarea name="formE_tindak_lanjut" class="form-control" rows="4">{{ old('formE_tindak_lanjut') }}</textarea>
                        </div>
                        @else
                        <h4>{{__('EVALUATION')}}</h4>
                        <table class="mt-4">
                            <thead>
                                <tr>
                                    <th>{{__('PERFORMANCE PROGRESS')}}</th>
                                    <th>{{__('BARRIERS TO ACHIEVING TARGETS')}}</th>
                                    <th>{{__('FOLLOW-UP ACTIONS')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formResponses->where('form_type', 'E') as $response)
                                <tr>
                                    <td>{!! nl2br(e($response->performance_progress)) !!}</td>
                                    <td>{!! nl2br(e($response->barriers)) !!}</td>
                                    <td>{!! nl2br(e($response->follow_up)) !!}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Submit</button>

                </div>
                @elseif ($isSupervisor)
                <!-- Form A2 -->
                <div class="tab-pane fade show active" id="formA2" role="tabpanel" aria-labelledby="formA2-tab">
                    <table class="mt-4">
                        <thead>
                            <tr>
                                <th>{{__('PROJECT NAME')}}</th>
                                <th>{{__('SUPERVISOR NAME')}}</th>
                                <th>{{__('WORK TARGETS')}}</th>
                                <th>{{__('BENCHMARK / JOB DESC')}}</th>
                                <th>{{__('PERFORMANCE ACHIEVEMENT')}}</th>
                                <th>{{__('SELF-ASSESSMENT')}}</th>
                                <th>{{__('SUPERVISOR ASSESSMENT')}}</th>
                                <th>{{__('FINAL ASSESSMENT')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($formResponses->where('form_type', 'A2') as $response)
                            <tr>
                                <td>{{ $response->project_name }}</td>
                                <td>{{ $response->employee->name }}</td>
                                <td>{{ $response->work_targets }}</td>
                                <td>{{ $response->criteria }}</td>
                                <td>{{ $response->performance_achievements }}</td>
                                <td>{{ $response->self_assessment }}</td>
                                @if($response->supervisor_assessment == NULL)
                                <td>
                                    <input type="radio" name="formA2_supervisor[{{ $response->id }}]" value="1" {{ $response->supervisor_assessment == 1 ? 'checked' : '' }}> 1
                                    <input type="radio" name="formA2_supervisor[{{ $response->id }}]" value="2" {{ $response->supervisor_assessment == 2 ? 'checked' : '' }}> 2
                                    <input type="radio" name="formA2_supervisor[{{ $response->id }}]" value="3" {{ $response->supervisor_assessment == 3 ? 'checked' : '' }}> 3
                                    <input type="radio" name="formA2_supervisor[{{ $response->id }}]" value="4" {{ $response->supervisor_assessment == 4 ? 'checked' : '' }}> 4
                                </td>
                                @else
                                <td>{{ $response->supervisor_assessment }}</td>
                                @endif
                                @if($response->final_assessment == NULL)
                                <td>
                                    <input type="radio" name="formA2_final[{{ $response->id }}]" value="1" {{ $response->final_assessment == 1 ? 'checked' : '' }}> 1
                                    <input type="radio" name="formA2_final[{{ $response->id }}]" value="2" {{ $response->final_assessment == 2 ? 'checked' : '' }}> 2
                                    <input type="radio" name="formA2_final[{{ $response->id }}]" value="3" {{ $response->final_assessment == 3 ? 'checked' : '' }}> 3
                                    <input type="radio" name="formA2_final[{{ $response->id }}]" value="4" {{ $response->final_assessment == 4 ? 'checked' : '' }}> 4
                                </td>
                                @else
                                <td>{{ $response->final_assessment }}</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Submit</button>
                @endif

                <!-- Tambahkan tab lain sesuai form yang ada -->

            </div>
            {{-- <button type="submit" class="btn btn-primary mt-2">Submit</button> --}}
        </form>
    </div>
@endsection