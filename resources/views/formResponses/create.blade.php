@extends('layouts.admin')
@section('page-title')
    {{__('Personel Assessment Create')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('form-response.index')}}">{{__('Personel Assessment')}}</a></li>
    <li class="breadcrumb-item">{{__('Personel Assessment Create')}}</li>
@endsection
@push('script-page')
    <script>
        function addRowA1() {
            var table = document.getElementById("formA1Table").getElementsByTagName('tbody')[0];
            var newRow = table.insertRow();
            var rowCount = table.rows.length;

            var cell1 = newRow.insertCell(0);
            var cell2 = newRow.insertCell(1);
            var cell3 = newRow.insertCell(2);
            var cell4 = newRow.insertCell(3);
            var cell5 = newRow.insertCell(4);
            var cell6 = newRow.insertCell(5);
            var cell7 = newRow.insertCell(6);
            var cell8 = newRow.insertCell(7);

            cell1.innerHTML = '<textarea class="form-control" name="work_targetsA1[]' + rowCount + '"></textarea>';
            cell2.innerHTML = '<textarea type="text" class="form-control" name="customCriteriaA1[]"></textarea>';
            cell3.innerHTML = '<textarea class="form-control" name="performance_achievementsA1[]' + rowCount + '"></textarea>';
            cell4.innerHTML = '<input type="radio" name="form_selfA1[]' + rowCount + '" value="1">';
            cell5.innerHTML = '<input type="radio" name="form_selfA1[]' + rowCount + '" value="2">';
            cell6.innerHTML = '<input type="radio" name="form_selfA1[]' + rowCount + '" value="3">';
            cell7.innerHTML = '<input type="radio" name="form_selfA1[]' + rowCount + '" value="4">';
            cell8.innerHTML = '<button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button>';
        }

        function addRowA2() {
            var table = document.getElementById("formA2Table").getElementsByTagName('tbody')[0];
            var newRow = table.insertRow();
            var rowCount = table.rows.length;

            var cell1 = newRow.insertCell(0);
            var cell2 = newRow.insertCell(1);
            var cell3 = newRow.insertCell(2);
            var cell4 = newRow.insertCell(3);
            var cell5 = newRow.insertCell(4);
            var cell6 = newRow.insertCell(5);
            var cell7 = newRow.insertCell(6);
            var cell8 = newRow.insertCell(7);
            var cell9 = newRow.insertCell(8);
            var cell10 = newRow.insertCell(9);

            cell1.innerHTML = '<textarea class="form-control" name="project_nameA[]' + rowCount + '"></textarea>';
            cell2.innerHTML = `<select class="form-control" name="supervisor_idA[]">
                @foreach($supervisor as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>`;
            cell3.innerHTML = '<textarea class="form-control" name="work_targetsA2[]' + rowCount + '"></textarea>';
            cell4.innerHTML = '<textarea type="text" class="form-control" name="customCriteriaA2[]"></textarea>';
            cell5.innerHTML = '<textarea class="form-control" name="performance_achievementsA2[]' + rowCount + '"></textarea>';
            cell6.innerHTML = '<input type="radio" name="form_selfA2[]' + rowCount + '" value="1">';
            cell7.innerHTML = '<input type="radio" name="form_selfA2[]' + rowCount + '" value="2">';
            cell8.innerHTML = '<input type="radio" name="form_selfA2[]' + rowCount + '" value="3">';
            cell9.innerHTML = '<input type="radio" name="form_selfA2[]' + rowCount + '" value="4">';
            cell10.innerHTML = '<button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button>';
        }


        function removeRow(button) {
            var row = button.parentNode.parentNode;
            row.parentNode.removeChild(row);
        }
    </script>
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
            document.getElementById('submitA2').style.display = 'block';  // Show submit on A2
            document.getElementById('submitD').style.display = 'none';    // Hide submit on D
        }

        function showFullSeason() {
            document.getElementById('formC-tab').style.display = 'block';
            document.getElementById('formD-tab').style.display = 'block';
            document.getElementById('submitA2').style.display = 'none';   // Hide submit on A2
            document.getElementById('submitD').style.display = 'block';   // Show submit on D
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
                                        <a href="#" class="text-blue text-sm ms-3" data-size="lg" data-url="{{ route('training.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Training')}}">{{ __('Add') }}</a>
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
                                        <a href="#" class="text-blue text-sm ms-3" data-size="lg" data-url="{{ route('training.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Sertifikasi')}}">{{ __('Add') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        

        <form action="{{ route('submit.forms') }}" method="POST">
            @csrf
            <div class="col-lg-3 col-md-6">
                <select class="form-control" name="appraisal_id">
                    <option value="0">{{ 'Select Appraisal' }}</option>
                    @foreach($supervisor as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <br>
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="formA1-tab" data-bs-toggle="tab" data-bs-target="#formA1" type="button" role="tab" aria-controls="formA1" aria-selected="true">Form A1</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="formA2-tab" data-bs-toggle="tab" data-bs-target="#formA2" type="button" role="tab" aria-controls="formA2" aria-selected="true">Form A2</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="formC-tab" data-bs-toggle="tab" data-bs-target="#formC" type="button" role="tab" aria-controls="formC" aria-selected="false">Form C</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="formD-tab" data-bs-toggle="tab" data-bs-target="#formD" type="button" role="tab" aria-controls="formD" aria-selected="false">Form D</button>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">

                <!-- Form A1 -->
                <div class="tab-pane fade show active" id="formA1" role="tabpanel" aria-labelledby="formA1-tab">
                    <table class="mt-4" id="formA1Table">
                        <thead>
                            <tr>
                                <th rowspan="2">{{__('WORK TARGETS')}}</th>
                                <th rowspan="2">{{__('BENCHMARK / JOB DESC')}}</th>
                                <th rowspan="2">{{__('PERFORMANCE ACHIEVEMENT')}}</th>
                                <th colspan="4">{{__('SELF-ASSESSMENT')}}</th>
                                <th rowspan="2">{{__('ACTION')}}</th>
                            </tr>
                            <tr>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><textarea class="form-control" name="work_targetsA1[]"></textarea></td>
                                <td><textarea type="text" class="form-control" name="customCriteriaA1[]"></textarea></td>
                                <td><textarea class="form-control" name="performance_achievementsA1[]"></textarea></td>
                                <td><input type="radio" name="form_selfA1[]" value="1"></td>
                                <td><input type="radio" name="form_selfA1[]" value="2"></td>
                                <td><input type="radio" name="form_selfA1[]" value="3"></td>
                                <td><input type="radio" name="form_selfA1[]" value="4"></td>
                                <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-primary mt-2" onclick="addRowA1()">Add Row</button>
                </div>

                <!-- Form A2 -->
                <div class="tab-pane fade" id="formA2" role="tabpanel" aria-labelledby="formA2-tab">
                    <table class="mt-4" id="formA2Table">
                        <thead>
                            <tr>
                                <th rowspan="2">{{__('PROJECT NAME')}}</th>
                                <th rowspan="2">{{__('SUPERVISOR NAME')}}</th>
                                <th rowspan="2">{{__('WORK TARGETS')}}</th>
                                <th rowspan="2">{{__('BENCHMARK / JOB DESC')}}</th>
                                <th rowspan="2">{{__('PERFORMANCE ACHIEVEMENT')}}</th>
                                <th colspan="4">{{__('SELF-ASSESSMENT')}}</th>
                                <th rowspan="2">{{__('ACTION')}}</th>
                            </tr>
                            <tr>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><textarea type="text" class="form-control" name="project_nameA[]"></textarea></td>
                                <td>
                                    <select class="form-control" name="supervisor_idA[]">
                                        <option value="0">{{ 'Select Supervisor' }}</option>
                                        @foreach($supervisor as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><textarea class="form-control" name="work_targetsA2[]"></textarea></td>
                                <td><textarea type="text" class="form-control" name="customCriteriaA2[]"></textarea></td>
                                <td><textarea class="form-control" name="performance_achievementsA2[]"></textarea></td>
                                <td><input type="radio" name="form_selfA2[]" value="1"></td>
                                <td><input type="radio" name="form_selfA2[]" value="2"></td>
                                <td><input type="radio" name="form_selfA2[]" value="3"></td>
                                <td><input type="radio" name="form_selfA2[]" value="4"></td>
                                <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-primary mt-2" onclick="addRowA2()">Add Row</button>
                    <button type="submit" class="btn btn-success mt-2" id="submitA2" style="display: none;">Submit</button>
                </div>


                <!-- Form C -->
                <div class="tab-pane fade" id="formC" role="tabpanel" aria-labelledby="formC-tab">
                    <table class="mt-4">
                        <thead>
                            <tr>
                                <th rowspan="2">{{__('CRITERIA')}}</th>
                                <th colspan="4">{{__('SELF-ASSESSMENT')}}</th>
                                <th rowspan="2">{{__('COMMENT')}}</th>
                            </tr>
                            <tr>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($formC_criteria as $criteria)
                                <tr>
                                    @if($loop->first || $criteria->section !== $formC_criteria[$loop->index - 1]->section)
                                        <td class="section-header" colspan="14">{{ $criteria->section }}</td>
                                    @endif
                                </tr>
                                <tr>
                                    <td class="criteria">{{ $criteria->criteria }}</td>
                                    <td><input type="radio" name="formC[{{ $criteria->id }}][_self]" value="1"></td>
                                    <td><input type="radio" name="formC[{{ $criteria->id }}][_self]" value="2"></td>
                                    <td><input type="radio" name="formC[{{ $criteria->id }}][_self]" value="3"></td>
                                    <td><input type="radio" name="formC[{{ $criteria->id }}][_self]" value="4"></td>
                                    <td><textarea class="comment-box" name="comment_formC[{{ $criteria->id }}]"></textarea></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Form D -->
                <div class="tab-pane fade" id="formD" role="tabpanel" aria-labelledby="formD-tab">
                    <table class="mt-4">
                        <thead>
                            <tr>
                                <th rowspan="2">{{__('CRITERIA')}}</th>
                                <th colspan="4">{{__('SELF-ASSESSMENT')}}</th>
                                <th rowspan="2">{{__('COMMENT')}}</th>
                            </tr>
                            <tr>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($formD_criteria as $criteria)
                                <tr>
                                    @if($loop->first || $criteria->section !== $formD_criteria[$loop->index - 1]->section)
                                        <td class="section-header" colspan="14">{{ $criteria->section }}</td>
                                    @endif
                                </tr>
                                <tr>
                                    <td class="criteria">{{ $criteria->criteria }}</td>
                                    <td><input type="radio" name="formD[{{ $criteria->id }}][_self]" value="1"></td>
                                    <td><input type="radio" name="formD[{{ $criteria->id }}][_self]" value="2"></td>
                                    <td><input type="radio" name="formD[{{ $criteria->id }}][_self]" value="3"></td>
                                    <td><input type="radio" name="formD[{{ $criteria->id }}][_self]" value="4"></td>
                                    <td><textarea class="comment-box" name="comment_formD[{{ $criteria->id }}]"></textarea></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-success mt-2" id="submitD" style="display: none;">Submit</button>
                </div>

            </div>
        </form>
    </div>
@endsection


