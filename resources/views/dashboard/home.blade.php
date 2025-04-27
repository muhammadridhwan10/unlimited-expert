@extends('layouts.admin')
@section('page-title')
    {{__('Home')}}
@endsection
@php
if(\Auth::user()->type != 'partners')
{
$shortenedTasksPriorityLabels = array_map(fn($name) => substr($name, 0,10) . '...', array_keys($tasksPriorityPerProject));
$shortenedOverdueTasksLabels = array_map(fn($name) => substr($name, 0,10) . '...', array_keys($overdueTasksPerProject));
}
@endphp
@push('script-page')
    <script src="https://www.gstatic.com/firebasejs/7.23.0/firebase.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function () {

            $('#filter_range').on('change', function () {
                const filterRange = $(this).val();

                $.ajax({
                    url: "{{ route('home') }}",
                    method: "GET",
                    data: { filter_range: filterRange },
                    success: function (response) {

                        let projects = response.projects;
                        let tbody = $('#project-reminder-table tbody');
                        tbody.empty();

                        if (projects.length > 0) {
                            projects.forEach((project, index) => {
                                let row = `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${project.project_name}</td>
                                        <td>${project.end_date}</td>
                                        <td>${project.status}</td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                        } else {
                            tbody.append(`
                                <tr>
                                    <td colspan="4" class="text-center">No projects approaching the deadline.</td>
                                </tr>
                            `);
                        }
                    },
                    error: function () {
                        alert('An error occurred while fetching data.');
                    }
                });
            });

            $('#top_n_filter').on('change', function () {
                const topNunproject = $(this).val();

                $.ajax({
                    url: "{{ route('home') }}",
                    method: "GET",
                    data: { top_nunproject: topNunproject },
                    success: function (response) {
                        let projects = response.untouchedProjects;
                        let tbody = $('#untouched-projects-table tbody');
                        tbody.empty();

                        if (projects.length > 0) {
                            projects.forEach((project, index) => {
                                let row = `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${project.project_name}</td>
                                        <td>${new Date(project.total_time_in_seconds * 1000).toISOString().substr(11, 8)}</td>
                                        <td>${project.status}</td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                        } else {
                            tbody.append(`
                                <tr>
                                    <td colspan="4" class="text-center">No untouched projects found.</td>
                                </tr>
                            `);
                        }
                    },
                    error: function () {
                        alert('An error occurred while fetching data.');
                    }
                });
            });

            $('#top_n_filter_in_progress').on('change', function () {
                const topNinproject = $(this).val();

                $.ajax({
                    url: "{{ route('home') }}",
                    method: "GET",
                    data: { top_ninproject: topNinproject },
                    success: function (response) {
                        let projects = response.inprogressProjects;
                        let tbody = $('#in-progress-projects-table tbody');
                        tbody.empty();

                        if (projects.length > 0) {
                            projects.forEach((project, index) => {
                                let row = `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${project.project_name}</td>
                                        <td>${new Date(project.total_time_in_seconds * 1000).toISOString().substr(11, 8)}</td>
                                        <td>${project.status}</td>
                                        <td>${project.updated_at}</td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                        } else {
                            tbody.append(`
                                <tr>
                                    <td colspan="5" class="text-center">No in-progress projects found.</td>
                                </tr>
                            `);
                        }
                    },
                    error: function () {
                        alert('An error occurred while fetching data.');
                    }
                });
            });
        });
    </script>
    <script>


        var firebaseConfig = {
            apiKey: "AIzaSyDD7yWFgBi1xlc7y7FBHSNalQjnDAHq__g",
            databaseURL: "https://aup-apps-581d7-default-rtdb.firebaseio.com",
            authDomain: "aup-apps-581d7.firebaseapp.com",
            projectId: "aup-apps-581d7",
            storageBucket: "aup-apps-581d7.appspot.com",
            messagingSenderId: "1058833635712",
            appId: "1:1058833635712:web:3c4c99990b390eb6258965"
        };
        // measurementId: G-R1KQTR3JBN
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();

        function IntitalizeFireBaseMessaging() {
                messaging
                .requestPermission()
                .then(function () {
                    console.log("Notification Permission");
                    return messaging.getToken()
                })
                .then(function(token) {
                    console.log(token);

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        url: '{{ route("save-token") }}',
                        type: 'POST',
                        data: {
                            token: token
                        },
                        dataType: 'JSON',
                    });

                }).catch(function (err) {
                    toastr.error('User Chat Token Error'+ err, null, {timeOut: 3000, positionClass: "toast-bottom-right"});
                });
        }  
        

        messaging.onMessage(function(payload) {
            const noteTitle = payload.notification.title;
            const noteOptions = {
                body: payload.notification.body,
                icon: payload.notification.icon,
            };
            new Notification(noteTitle, noteOptions);

            if(Notification.permission==="granted")
            {
                    var notification=new Notification(payload.notification.title,notificationOption);

                    notification.onclick=function (ev) {
                        ev.preventDefault();
                        window.open(payload.notification.click_action,'_blank');
                        notification.close();
                    }
            }
        });
        IntitalizeFireBaseMessaging();
    </script>
    <script>
    @if(\Auth::user()->type == 'partners')
        (function () {
            

            var options = {
                chart: {
                    height: 200,
                    type: 'bar',
                    toolbar: {
                        show: false,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [{
                    name: "{{__('Income')}}",
                    data: {!! json_encode($incPartExpBarChartData['income']) !!}
                }, {
                    name: "{{__('Expense')}}",
                    data: {!! json_encode($incPartExpBarChartData['expense']) !!}
                }],
                xaxis: {
                    categories: {!! json_encode($incPartExpBarChartData['month']) !!},
                },
                 colors: ['#3ec9d6', '#FF3A6E'],
                fill: {
                    type: 'solid',
                },
                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                },
                // markers: {
                //     size: 4,
                //     colors: ['#3ec9d6', '#FF3A6E',],
                //     opacity: 0.9,
                //     strokeWidth: 2,
                //     hover: {
                //         size: 7,
                //     }
                // }
            };
            var chart = new ApexCharts(document.querySelector("#incExpBarChart"), options);
            chart.render();
        })();

        (function () {
            var options = {
                chart: {
                    height: 140,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                legend: {
                    show: true
                }
            };
            var chart = new ApexCharts(document.querySelector("#expenseByCategory"), options);
            chart.render();
        })();

        (function () {
            var options = {
                chart: {
                    height: 140,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                legend: {
                    show: true
                }
            };
            var chart = new ApexCharts(document.querySelector("#incomeByCategory"), options);
            chart.render();
        })();
    @endif
    </script>
    <script>
    document.getElementById('income-card').addEventListener('click', function() {
        var incomeListCard = document.getElementById('income-list-card');
        if (incomeListCard.style.display === 'none') {
            incomeListCard.style.display = 'block';
        } else {
            incomeListCard.style.display = 'none';
        }
    });

    document.getElementById('expense-card').addEventListener('click', function() {
        var incomeListCard = document.getElementById('expense-list-card');
        if (incomeListCard.style.display === 'none') {
            incomeListCard.style.display = 'block';
        } else {
            incomeListCard.style.display = 'none';
        }
    });
    </script>
    <script>
    @if(\Auth::user()->type == 'partners')
        var ctx = document.getElementById('attendanceChart').getContext('2d');
        var attendanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array.from({length: 31}, (v, k) => k + 1),
                datasets: [{
                    label: 'Present',
                    data: @json($absentData),
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                }, {
                    label: 'Late',
                    data: @json($lateData),
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    @endif
    </script>
    <script>
    @if(\Auth::user()->type !== 'partners')
        var ctx = document.getElementById('attendanceChart').getContext('2d');
        var attendanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array.from({length: 31}, (v, k) => k + 1),
                datasets: [{
                    label: 'Present',
                    data: @json($data_absen),
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                }, {
                    label: 'Late',
                    data: @json($data_late),
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        var ctx = document.getElementById('overtimeChart').getContext('2d');
        var overtimeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(@json($overtimePerDay)),
                datasets: [{
                    label: 'Overtime Hours',
                    data: Object.values(@json($overtimePerDay)),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        var ctx = document.getElementById('timesheetChart').getContext('2d');
        var timesheetChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dates) !!},
                datasets: [{
                    label: 'Hours Worked',
                    data: {!! json_encode($timesheetData) !!},
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Hours'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                }
            }
        });

        var ctx = document.getElementById('projectStatusChart').getContext('2d');
        var projectStatusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_keys($projectStatusCounts)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($projectStatusCounts)) !!},
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.raw || 0;
                                return label + ': ' + value + ' projects';
                            }
                        }
                    }
                }
            }
        });

        var ctx2 = document.getElementById('tasksStatusChart').getContext('2d');
        var tasksStatusChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Complete', 'Incomplete'],
                datasets: [{
                    label: 'Tasks Status',
                    data: [
                        {{ $completedTasks }},
                        {{ $incompleteTasks }}
                    ],
                    backgroundColor: ['#36a2eb', '#ff6384'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
        });

    var ctx3 = document.getElementById('tasksPriorityChart').getContext('2d');
    var tasksPriorityChart = new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: @json($shortenedTasksPriorityLabels),
            datasets: [{
                label: 'Tasks by Priority',
                data: @json(array_values($tasksPriorityPerProject)),
                backgroundColor: ['#ff6384', '#36a2eb', '#4bc0c0'],
                borderColor: '#fff',
                borderWidth: 1
            }]
        }
    });

    var ctx4 = document.getElementById('overdueTasksChart').getContext('2d');
    var overdueTasksChart = new Chart(ctx4, {
        type: 'bar',
        data: {
            labels: @json($shortenedOverdueTasksLabels),
            datasets: [{
                label: 'Overdue Tasks',
                data: @json(array_values($overdueTasksPerProject)),
                backgroundColor: [
                    '#ff6384', '#36a2eb', '#4bc0c0', '#ffce56', '#9966ff', 
                    '#ff9f40', '#c9cbcf', '#2b908f', '#f45b5b', '#91e8e1', 
                    '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd'
                ],
                borderColor: '#fff',
                fill: false,
                tension: 0.1,
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Project Name'
                    },
                    ticks: {
                        maxRotation: 0,
                        minRotation: 0,
                        callback: function(value) {
                            return this.getLabelForValue(value).substring(0, 10) + '...';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                }
            }
        }
    });

    @endif
    </script>
    <script>
       document.getElementById('clock_in').addEventListener('click', function(event) {

        const selectedLocation = document.querySelector('input[name="work_location"]:checked');
        
        if (!selectedLocation) {
            event.preventDefault();
            alert('Please select a work location before clocking in.');
            return;
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                    document.getElementById('clock_in_form').submit();
                },
                function(error) {
                    if (error.code === error.PERMISSION_DENIED) {
                        alert('Location permission is required to clock in. Please enable location services and try again.');
                    } else {
                        alert('Unable to retrieve your location. Please ensure location services are enabled.');
                    }
                }
            );
        } else {
            alert('Geolocation is not supported by your browser.');
        }
    });

    function updatePerPage(paramName, value) {
        const url = new URL(window.location.href);
        url.searchParams.set(paramName, value);
        window.location.href = url.toString();
    }
    </script>
@endpush
@push('css-page')
    <style>
        #timer 
        {
            font-family: "Comic Sans MS";
            font-size: 30px;
            font-weight: bold;
            color: #333;
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        #message 
        {
            font-size: 24px;
            color: #666;
            margin-top: 10px;
        }

        .container 
        {
            text-align: center;
        }
        .small-font {
            font-size: 1.5em;
        }
    </style>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Home')}}</a></li>
    <li class="breadcrumb-item">{{__('Home')}}</li>
@endsection
@section('content')
    @if(\Auth::user()->type != 'client' && \Auth::user()->type != 'company' && \Auth::user()->type != 'admin' && \Auth::user()->type != 'partners' && \Auth::user()->type != 'support')
        <div class="row">
            <div class="col-sm-6">
                <div class="card">
                    {{-- <div class="card-header">
                        <h4>{{__('Mark Attendance')}}</h4>
                    </div> --}}
                    <div class="card-body dash-card-body">
                        <p class="text-muted pb-0-5">{{__('My Office Time: '.$officeTime['startTime'].' to '.$officeTime['endTime'])}}</p>
                        <center>
                            {{Form::open(array('url'=>'attendanceemployee/attendance','method'=>'post', 'id' => 'clock_in_form'))}}
                            <div class="row">
                                <label class="font-weight-bold">Work Location:</label>
                                <div class="d-flex align-items-center mt-3">
                                    <div class="col-3 d-flex justify-content-center">
                                        <div class="form-check form-check-inline">
                                            <input type="radio" id="wfh" name="work_location" value="WFH" class="form-check-input"
                                                {{ !empty($employeeAttendance) && $employeeAttendance->work_location == 'WFH' ? 'checked' : '' }}
                                                {{ !empty($employeeAttendance) && $employeeAttendance->work_location != 'WFH' ? 'disabled' : '' }}>
                                            <label for="wfh" class="form-check-label">
                                                🏠 WFH
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-3 d-flex justify-content-center">
                                        <div class="form-check form-check-inline">
                                            <input type="radio" id="wfa" name="work_location" value="WFA" class="form-check-input"
                                                {{ !empty($employeeAttendance) && $employeeAttendance->work_location == 'WFA' ? 'checked' : '' }}
                                                {{ !empty($employeeAttendance) && $employeeAttendance->work_location != 'WFA' ? 'disabled' : '' }}>
                                            <label for="wfa" class="form-check-label">
                                                🌍 WFA
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-3 d-flex justify-content-center">
                                        <div class="form-check form-check-inline">
                                            <input type="radio" id="wfo" name="work_location" value="WFO" class="form-check-input"
                                                {{ !empty($employeeAttendance) && $employeeAttendance->work_location == 'WFO' ? 'checked' : '' }}
                                                {{ !empty($employeeAttendance) && $employeeAttendance->work_location != 'WFO' ? 'disabled' : '' }}>
                                            <label for="wfo" class="form-check-label">
                                                🏢 WFO
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-3 d-flex justify-content-center">
                                        <div class="form-check form-check-inline">
                                            <input type="radio" id="client" name="work_location" value="CLIENT" class="form-check-input"
                                                {{ !empty($employeeAttendance) && $employeeAttendance->work_location == 'CLIENT' ? 'checked' : '' }}
                                                {{ !empty($employeeAttendance) && $employeeAttendance->work_location != 'CLIENT' ? 'disabled' : '' }}>
                                            <label for="wfo" class="form-check-label">
                                                🏢 Client
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6 d-flex justify-content-center">
                                        <input type="hidden" name="latitude" id="latitude">
                                        <input type="hidden" name="longitude" id="longitude">
                                        @if(empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00')
                                            <button type="button" value="0" name="in" id="clock_in" class="btn btn-success">{{__('CLOCK IN')}}</button>
                                        @else
                                            <button type="button" value="0" name="in" id="clock_in" class="btn btn-success disabled" disabled>{{__('CLOCK IN')}}</button>
                                        @endif
                                    {{Form::close()}}
                                </div>
                                <div class="col-6 d-flex justify-content-center">
                                    @if(!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                        {{Form::model($employeeAttendance,array('route'=>array('attendanceemployee.update',$employeeAttendance->id),'method' => 'PUT')) }}
                                        <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger">{{__('CLOCK OUT')}}</button>
                                    @else
                                        <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger disabled" disabled>{{__('CLOCK OUT')}}</button>
                                    @endif
                                    {{Form::close()}}
                                </div>
                            </div>
                        </center>
                        {{-- <br>
                        <div class="container">
                            <div id="message"></div>
                            <br>
                            <div id="timer"></div> 
                        </div> --}}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h6>{{ __("Total Attendance in $curMonth") }}</h6>
                        <br>
                        <h3 class="small-font">{{ $data['totalPresent']}} Days</h3>

                        <!-- Konten tambahan -->
                        <p class="text-muted mt-3">This is the number of days you were present this month.</p>
                        <i class="fas fa-calendar-check fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-success">
                                        <i class="ti ti-checks"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted h7">{{__('All Project')}}</small>
                                        <h6 class="m-0">{{$home_data['total_project']['all_project'] }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-danger">
                                        <i class="ti ti-alert-triangle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted h7">{{__('Project In Progress')}}</small>
                                        <h6 class="m-0">{{$home_data['total_project']['in_progress_project'] }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-clipboard-check"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted h7">{{__(' Project Completed')}}</small>
                                        <h6 class="m-0">{{$home_data['total_project']['complete_project'] }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-success">
                                        <i class="ti ti-check"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted h7">{{__('Done Project')}}</small>
                                        <h6 class="m-0">{{$home_data['total_project']['percentage'] . '%'}}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__("Project Status Distribution")}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <canvas id="projectStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart for Tasks Status -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __("Tasks Status") }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="tasksStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart for Tasks by Priority -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __("Tasks by Priority") }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="tasksPriorityChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart for Overdue Tasks -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __("Overdue Tasks") }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="overdueTasksChart"></canvas>
                    </div>
                </div>
            </div>
        </div> --}}
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{('Project Reminder')}}</h5>
                        <div class="float-end">
                            <select id="filter_range" class="form-select">
                                <option value="7_days">7 Days Ahead</option>
                                <option value="1_week">1 Week Ahead</option>
                                <option value="1_month">1 Month Ahead</option>
                                <option value="2_months">2 Months Ahead</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="project-reminder-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Project Name</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($projectReminder as $index => $project)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $project->project_name }}</td>
                                        <td>{{ $project->end_date }}</td>
                                        <td>{{ $project->status }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No projects approaching the deadline.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{('Untouched Project')}}</h5>
                        <div class="float-end">
                            <select id="top_n_filter" class="form-select" style="width:100px;">
                                <option value="5">Top 5</option>
                                <option value="10">Top 10</option>
                                <option value="20">Top 20</option>
                                <option value="30">Top 30</option>
                                <option value="50">Top 50</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="untouched-projects-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Project Name</th>
                                        <th>Start Date</th>
                                        <th>Total Timesheet</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($untouchedProjects as $index => $project)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $project->project_name }}</td>
                                        <td>{{ $project->start_date }}</td>
                                        <td>
                                            @php
                                                $totalTimesheet = $project->timesheets->sum('time');
                                            @endphp
                                            {{ gmdate('H:i:s', $totalTimesheet) }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No untouched projects found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{('Project in Progress')}}</h5>
                        <div class="float-end">
                            <select id="top_n_filter_in_progress" class="form-select" style="width:100px;">
                                <option value="1">Top 1</option>
                                <option value="5">Top 5</option>
                                <option value="10">Top 10</option>
                                <option value="20">Top 20</option>
                                <option value="30">Top 30</option>
                                <option value="50">Top 50</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="in-progress-projects-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Project Name</th>
                                        <th>Status</th>
                                        <th>Start Date</th>
                                        <th>Total Timesheet</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($inProgressProjects as $index => $project)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $project->project_name }}</td>
                                        <td>{{ $project->status }}</td>
                                        <td>{{ $project->start_date }}</td>
                                        <td>
                                            @php
                                                $totalSeconds = $project->timesheets->reduce(function ($carry, $item) {
                                                    list($hours, $minutes, $seconds) = explode(':', $item->time);
                                                    return $carry + ($hours * 3600) + ($minutes * 60) + $seconds;
                                                }, 0);
                                            @endphp
                                            {{ gmdate('H:i:s', $totalSeconds) }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No projects in progress found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{('Productivity Comparison')}}</h5>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Metric</th>
                                        <th>Previous Month</th>
                                        <th>Current Month</th>
                                        <th>Trend</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Total Work Time</td>
                                        <td>{{ $previousMonthWork }}</td>
                                        <td>{{ $currentMonthWork }}</td>
                                        <td>
                                            @if ($currentMonthWorkInSeconds > $previousMonthWorkInSeconds)
                                                <span class="text-success">↑ Increased</span>
                                            @elseif ($currentMonthWorkInSeconds < $previousMonthWorkInSeconds)
                                                <span class="text-danger">↓ Decreased</span>
                                            @else
                                                <span class="text-info">↔ No Change</span>
                                            @endif

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Tasks Completed</td>
                                        <td>{{ $previousMonthTasksCompleted }}</td>
                                        <td>{{ $currentMonthTasksCompleted }}</td>
                                        <td>
                                            @if ($currentMonthTasksCompleted > $previousMonthTasksCompleted)
                                                <span class="text-success">↑ Increased</span>
                                            @elseif ($currentMonthTasksCompleted < $previousMonthTasksCompleted)
                                                <span class="text-danger">↓ Decreased</span>
                                            @else
                                                <span class="text-info">↔ No Change</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('home'),'method'=>'get','id'=>'report_monthly_attendance_user')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="btn-box">
                                            {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                                            {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'month-btn form-control'))}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_monthly_attendance_user').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('home')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>{{ __("Total Overtime Hours") }}</h6>
                            <h3 class="small-font">{{ $totalOvertimeHours }} hours</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>{{ __("Total Annual Leave Taken") }}</h6>
                            <h3 class="small-font">{{ $totalLeaveDays . ' / ' . $totalAllocatedLeaveDays }} days</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>{{ __("Remaining Annual Leave Days") }}</h6>
                            <h3 class="small-font">{{ $totalRemainingLeaveDays }} days</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>{{ __("Total Sick") }}</h6>
                            <h3 class="small-font">{{ $totalSickDays }} Days</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>{{ __("Total Reimbursement") }}</h6>
                            <h3 class="small-font">{{ 'Rp ' . number_format($totalReimbursement, 0, ',', '.') . ' / ' . number_format($totalReimbursementAmount, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__("Attendance Statistics")}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <canvas id="attendanceChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __("Timesheet Hours Per Day in $curMonth") }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="timesheetChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__("Overtime Statistics")}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <canvas id="overtimeChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                    <div class="card-header"><h6 class="mb-0">{{__('Notification Approval')}}</h6></div>
                    <div class="card-body table-border-style">
                            <div class="table-responsive">
                            <table class="table datatables">
                                    <thead>
                                    <tr>
                                        @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                            <th>{{__('Employee')}}</th>
                                        @endif
                                        <th>{{__('Project Name')}}</th>
                                        <th>{{__('Start Date')}}</th>
                                        <th>{{__('Start Time')}}</th>
                                        <th>{{__('End Time')}}</th>
                                        <th width="200px">{{__('Note')}}</th>
                                        <th width="100px">{{__('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($approval as $approvals)
                                        <tr>
                                            @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                                <td>{{!empty($approvals->employee->name)?$approvals->employee->name:'-'}}</td>
                                            @endif
                                            <td>{{!empty($approvals->project->project_name)?$approvals->project->project_name:'-'}}</td>
                                            <td>{{date("l, d-m-Y",strtotime($approvals->start_date))}}</td>
                                            <td>{{ ($approvals->start_time !='00:00:00') ?\Auth::user()->timeFormat( $approvals->start_time):'00:00' }} </td>
                                            <td>{{ ($approvals->end_time !='00:00:00') ?\Auth::user()->timeFormat( $approvals->end_time):'00:00' }}</td>
                                            <td>{{!empty($approvals->note)?$approvals->note:'-'}}</td>
                                            <td>
                                                <div class="action-btn ms-2">
                                                    <a style="color:blue;" href="{{route('overtime.index')}}" class="mx-3 btn btn-sm" title="{{__('Overtime Link')}}" data-original-title="{{__('Overtime Link')}}">
                                                        Link</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @elseif(\Auth::user()->type == 'support')
        <div class="row">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-xxl-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>{{__('Mark Attendance')}}</h4>
                            </div>
                            <div class="card-body dash-card-body">
                                <p class="text-muted pb-0-5">{{__('My Office Time: '.$officeTime['startTime'].' to '.$officeTime['endTime'])}}</p>
                                <center>
                                    <div class="row">
                                        <div class="col-md-6">
                                            {{Form::open(array('url'=>'attendanceemployee/attendance','method'=>'post'))}}
                                            @if(empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00')
                                                <button type="submit" value="0" name="in" id="clock_in" class="btn btn-success ">{{__('CLOCK IN')}}</button>
                                            @else
                                                <button type="submit" value="0" name="in" id="clock_in" class="btn btn-success disabled" disabled>{{__('CLOCK IN')}}</button>
                                            @endif
                                            {{Form::close()}}
                                        </div>
                                        <div class="col-md-6 ">
                                            @if(!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                                {{Form::model($employeeAttendance,array('route'=>array('attendanceemployee.update',$employeeAttendance->id),'method' => 'PUT')) }}
                                                <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger">{{__('CLOCK OUT')}}</button>
                                            @else
                                                <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger disabled" disabled>{{__('CLOCK OUT')}}</button>
                                            @endif
                                            {{Form::close()}}
                                        </div>
                                    </div>
                                </center>
                                <br>
                                <div class="container">
                                    <div id="message"></div>
                                    <br>
                                    <div id="timer"></div> 
                                </div>

                            </div>
                        </div>

                         <div class="card">
                            <div class="card-body">
                                {{ Form::open(array('route' => array('home'),'method'=>'get','id'=>'report_monthly_attendance_user')) }}
                                <div class="row align-items-center justify-content-end">
                                    <div class="col-auto">
                                        <div class="row">
                                            <div class="col-auto">
                                                <div class="btn-box">
                                                    {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                                                    {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'month-btn form-control'))}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="row">
                                            <div class="col-auto mt-4">
                                                <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_monthly_attendance_user').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                                </a>
                                                <a href="{{route('home')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                                    <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>

                        <div class="card">
                            <div class="card-body table-border-style">
                                <div class="table-responsive py-4 attendance-table-responsive">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th class="active">{{__('Name')}}</th>
                                            @foreach($dates as $date)
                                                <th>{{$date}}</th>
                                            @endforeach
                                        </tr>
                                        </thead>
                                        <tbody>

                                        @foreach($employeesAttendances as $attendance)
                                            <tr>
                                                <td>{{$attendance['name']}}</td>
                                                @foreach($dates as $date)
                                                    <td>
                                                        @if($attendance['status'][$date] == 'P')
                                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance['latitude'][$date] }},{{ $attendance['longitude'][$date] }}" target="_blank" title="View on map">
                                                                <i class="ti ti-map-pin" style="font-size: 24px;"></i>
                                                            </a>
                                                        @elseif($attendance['status'][$date]=='A')
                                                            <i class="badge bg-danger p-2 rounded">{{__('A')}}</i>
                                                        @elseif($attendance['status'][$date]=='W')
                                                            <i class="badge bg-danger p-2 rounded">{{__('W')}}</i>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif(\Auth::user()->type == 'partners')
        <!-- Project Performance -->
        <div class="section">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{('List of Top Ranked Untouched Project')}}</h5>
                        </div>
                        <div class="row">
                            <div class="col-auto">
                                <div class="btn-box mb-3 mt-3" style="margin-left:20px">
                                    {{ Form::open(['method' => 'GET']) }}
                                    {{ Form::label('untouched_per_page', __('Show Entries'), ['class' => 'form-label']) }}
                                    {{ Form::select('untouched_per_page', [10 => '10', 25 => '25', 50 => '50', 100 => '100'], request('untouched_per_page', 10), ['class' => 'form-select', 'onchange' => 'this.form.submit()']) }}
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Project Name</th>
                                            <th>Budget</th>
                                            <th>Start Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($untouchedProjects as $index => $project)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $project->project_name }}</td>
                                            <td>{{ $project->budget ?? 0 }}</td>
                                            <td>{{ $project->start_date }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No untouched projects found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex mt-4">
                                    {{ $untouchedProjects->appends(['untouched_per_page' => request('untouched_per_page')])->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{('List of Project in Progress')}}</h5>
                        </div>
                        <div class="row">
                            <div class="col-auto">
                                <div class="btn-box mb-3 mt-3" style="margin-left:20px">
                                    {{ Form::open(['method' => 'GET']) }}
                                    {{ Form::label('in_progress_per_page', __('Show Entries'), ['class' => 'form-label']) }}
                                    {{ Form::select('in_progress_per_page', [10 => '10', 25 => '25', 50 => '50', 100 => '100'], request('in_progress_per_page', 10), ['class' => 'form-select', 'onchange' => 'this.form.submit()']) }}
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Project Name</th>
                                            <th>Status</th>
                                            <th>Start Date</th>
                                            <th>Budget</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($inProgressProjects as $index => $project)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $project->project_name }}</td>
                                            <td>{{ $project->status }}</td>
                                            <td>{{ $project->start_date }}</td>
                                            <td>{{ $project->budget ?? 0  }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No projects in progress found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex mt-4">
                                {{ $inProgressProjects->appends(['in_progress_per_page' => request('in_progress_per_page')])->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{('Percentage of Hours Spent vs Budget')}}</h5>
                        </div>
                        <div class="row">
                            <div class="col-auto">
                                <div class="btn-box mb-3 mt-3" style="margin-left:20px">
                                    {{ Form::open(['method' => 'GET']) }}
                                    {{ Form::label('percentage_project_page', __('Show Entries'), ['class' => 'form-label']) }}
                                    {{ Form::select('percentage_project_page', [10 => '10', 25 => '25', 50 => '50', 100 => '100'], request('percentage_project_page', 10), ['class' => 'form-select', 'onchange' => 'this.form.submit()']) }}
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Project Name</th>
                                            <th>Estimated Hours</th>
                                            <th>Hours Spent</th>
                                            <th>Budget</th>
                                            <th>Percentage</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($projects as $index => $project)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $project->project_name }}</td>
                                            <td>{{ $project->estimated_hrs }} Hours</td>
                                            <td>{{ number_format($project->hours_spent, 2) }} Hours</td>
                                            <td>{{ number_format($project->budget, 2) }}</td>
                                             <td>{{ is_numeric($project->percentage_budget) ? $project->percentage_budget . '%' : $project->percentage_budget }}</td>
                                            <td>
                                                @if ($project->is_over_hours)
                                                    <span class="text-danger">Over by {{ $project->over_percentage }}%</span>
                                                @else
                                                    <span class="text-success">On Track</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No projects found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex mt-4">
                                {{ $projects->appends(['percentage_project_page' => request('percentage_project_page')])->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Performance -->
        <div class="section">
            <div class="row">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{('List of Top Ranked High Performer Personnel')}}</h5>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Total Tasks Completed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($highPerformers as $index => $user)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->completed_tasks }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No high performers found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{('List of Low-Performer Personnel')}}</h5>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Total Tasks In Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($lowPerformers as $index => $user)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->in_progress_tasks }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No low performers found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Performance -->
        {{-- <div class="section">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{('Percentage of Hours Spent vs Budget')}}</h5>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Project Name</th>
                                            <th>Running Cost</th>
                                            <th>Fee</th>
                                            <th>Profit/Loss</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($financialData as $index => $project)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $project->project->project_name }}</td>
                                            <td>{{ $project->running_cost }}</td>
                                            <td>{{ $project->fee }}</td>
                                            <td>{{ $project->fee - $project->running_cost }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No financial data found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center">
                                {!! $financialData->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <!-- Task Reminder -->
        <div class="section">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{('Task Reminder')}}</h5>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table datatablesss">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Task Name</th>
                                            <th>Project Name</th>
                                            <th>Milestone</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($outstandingTasks as $index => $task)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $task->name }}</td>
                                            <td>{{ $task->project ? $task->project->project_name : 'Project Not Found' }}</td>
                                            <td>{{ $task->milestone->title ?? 'No Milestone'  }}</td>
                                            <td><span class="badge p-2 px-3 rounded badge-sm bg-{{__(\App\Models\ProjectTask::$priority_color[$task->priority])}}">{{ __(\App\Models\ProjectTask::$priority[$task->priority]) }}</span></td>
                                            <td>
                                                @if ($task->stage_id == 4)
                                                    <span class="text-success">Completed</span>
                                                @else
                                                    <span class="text-danger">Outstanding</span>
                                                @endif
                                            </td>
                                           
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No outstanding tasks found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center">
                                {!! $outstandingTasks->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="theme-avtar bg-success">
                            <i class="ti ti-users"></i>
                        </div>
                        <p class="text-muted text-sm mt-4 mb-2">{{__('Total')}}</p>
                        <h6 class="mb-3">{{__('Employee')}}</h6>
                        <h3 class="mb-0">{{\Auth::user()->totalCompanyUserBranch(\Auth::user()->id)}}

                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="theme-avtar bg-warning">
                            <i class="ti ti-share"></i>
                        </div>
                        <p class="text-muted text-sm mt-4 mb-2">{{__('Total')}}</p>
                        <h6 class="mb-3">{{__('Project')}}</h6>
                        <h3 class="mb-0">{{\Auth::user()->user_project()}} </h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="theme-avtar bg-info">
                            <i class="ti ti-report-money"></i>
                        </div>
                        <p class="text-muted text-sm mt-4 mb-2">{{__('Total')}}</p>
                        <h6 class="mb-3">{{__('Invoices')}}</h6>
                        <h3 class="mb-0">{{\Auth::user()->countInvoicesPartners()}}
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="theme-avtar bg-danger">
                            <i class="ti ti-report-money"></i>
                        </div>
                        <p class="text-muted text-sm mt-4 mb-2">{{__('Total')}}</p>
                        <h6 class="mb-3">{{__('Bills')}}</h6>
                        <h3 class="mb-0">{{\Auth::user()->countBillsPartners()}} </h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card" id="income-card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-success">
                                        <i class="ti ti-circle-plus"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted h6">{{__('Total Income (Cash In)')}}</small>
                                        <h6 class="m-0">{{\Auth::user()->priceFormat(\Auth::user()->IncomePartners())}}</h6>
                                        <small>{{__('All Invoice Paid')}}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card" id="expense-card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-danger">
                                        <i class="ti ti-circle-minus"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted h6">{{__('Total Expense (Cash Out)')}}</small>
                                        <h6 class="m-0">{{\Auth::user()->priceFormat(\Auth::user()->ExpensePartners())}}</h6>
                                        <small>{{__('Bill + Tax Invoice (%6)')}}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-report-money"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted h6">{{__('Total Balance')}}</small>
                                        <h6 class="m-0">{{\Auth::user()->priceFormat(\Auth::user()->BalancePartners())}}</h6>
                                        <small>{{__('Total Income - Total Expense')}}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row"> 
            <div class="col-lg-6 col-md-6">
                <div class="card" id="income-list-card" style="display: none;">
                    <div class="card-header">
                        <h5>{{__('List Income Data')}}</h5>
                    </div>
                    <div class="card-body project_table">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th>{{__('Date')}}</th>
                                        <th>{{__('Invoice')}}</th>
                                        <th>{{__('Amount')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if($list_revenue->count() > 0)
                                    @foreach($list_revenue as $revenue)
                                        <tr class="font-style">
                                            <td>{{ Auth::user()->dateFormat($revenue->date) }}</td>
                                            <td class="Id">
                                                @if($revenue->invoice_id)
                                                    <a href="{{ route('invoice.show', \Crypt::encrypt($revenue->invoice->invoice_id)) }}" class="btn btn-outline-primary">{{ $revenue->invoice->invoice_id }}</a>
                                                @else
                                                    <span class="btn btn-outline-primary">-</span>
                                                @endif
                                            </td>
                                            <td>{{ Auth::user()->priceFormat($revenue->amount) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <div class="py-5">
                                        <h5 class="text-center mb-0">{{__('No Revenue Found.')}}</h5>
                                    </div>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card" id="expense-list-card" style="display: none;">
                    <div class="card-header">
                        <h5>{{__('List Expense Data')}}</h5>
                    </div>
                    <div class="card-body project_table">
                        <div class="table-responsive">
                            <table class="table datatables">
                                <thead>
                                <tr>
                                    <th>{{__('Date')}}</th>
                                    <th>{{__('Amount')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($list_expense->count() > 0)
                                    @foreach($list_expense as $expense)
                                        <tr class="font-style">
                                            <td>{{  Auth::user()->dateFormat($expense->date)}}</td>
                                            <td>{{  Auth::user()->priceFormat($expense->amount)}}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <div class="py-5">
                                        <h5 class="text-center mb-0">{{__('No Revenue Found.')}}</h5>
                                    </div>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Income and Expense')}}
                        <span class="float-end text-muted">{{__('Current Year').' - '.$currentYear}}</span>
                    </h5>

                </div>
                <div class="card-body">
                    <div id="incExpBarChart"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class=" mt-2 " id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {{ Form::open(array('route' => array('home'),'method'=>'get','id'=>'report_monthly_attendance')) }}
                            <div class="row align-items-center justify-content-end">
                                <div class="col-auto">
                                    <div class="row">
                                        <div class="col-auto">
                                            <div class="btn-box">
                                                {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                                                {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'month-btn form-control'))}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="row">
                                        <div class="col-auto mt-4">
                                            <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_monthly_attendance').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                            </a>
                                            <a href="{{route('home')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                                <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__("Attendance Statistics")}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <canvas id="attendanceChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Attendance Employee')}}</h5>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive py-4 attendance-table-responsive">
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th class="active">{{__('Name')}}</th>
                                    @foreach($dates as $date)
                                        <th>{{$date}}</th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($employeesAttendances as $attendance)
                                        <tr>
                                            <td>{{$attendance['name']}}</td>
                                            @foreach($dates as $date)
                                                <td>
                                                    @if($attendance['status'][$date] == 'P')
                                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance['latitude'][$date] }},{{ $attendance['longitude'][$date] }}" target="_blank" title="View on map">
                                                            <i class="ti ti-map-pin" style="font-size: 24px;"></i>
                                                        </a>
                                                    @elseif($attendance['status'][$date]=='A')
                                                        <i class="badge bg-danger p-2 rounded">{{__('A')}}</i>
                                                    @elseif($attendance['status'][$date]=='W')
                                                        <i class="badge bg-danger p-2 rounded">{{__('W')}}</i>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-xxl-12">
            <div class="card">
                            <div class="card-header">
                                <h4>{{__('Mark Attendance')}}</h4>
                            </div>
                            <div class="card-body dash-card-body">
                                <p class="text-muted pb-0-5">{{__('My Office Time: '.$officeTime['startTime'].' to '.$officeTime['endTime'])}}</p>
                                <center>
                                    <div class="row">
                                        <div class="col-md-6">
                                            {{Form::open(array('url'=>'attendanceemployee/attendance','method'=>'post'))}}
                                            @if(empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00')
                                                <button type="submit" value="0" name="in" id="clock_in" class="btn btn-success ">{{__('CLOCK IN')}}</button>
                                            @else
                                                <button type="submit" value="0" name="in" id="clock_in" class="btn btn-success disabled" disabled>{{__('CLOCK IN')}}</button>
                                            @endif
                                            {{Form::close()}}
                                        </div>
                                        <div class="col-md-6 ">
                                            @if(!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                                {{Form::model($employeeAttendance,array('route'=>array('attendanceemployee.update',$employeeAttendance->id),'method' => 'PUT')) }}
                                                <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger">{{__('CLOCK OUT')}}</button>
                                            @else
                                                <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger disabled" disabled>{{__('CLOCK OUT')}}</button>
                                            @endif
                                            {{Form::close()}}
                                        </div>
                                    </div>
                                </center>

                            </div>
                        </div>
                <div class="card">
                    <div class="card-header">
                        <h5>{{__("Today's Not Clock In")}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-12">
                                <div class="row g-3 flex-nowrap team-lists horizontal-scroll-cards">
                                    @foreach($notClockIns as $notClockIn)

                                        <div class="col-auto">
                                            <img src="{{(!empty($notClockIn->user))? $notClockIn->user->profile : asset(Storage::url('uploads/avatar/avatar.png'))}}" alt="">

                                            <p class="mt-2">{{ $notClockIn->name }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{__('Event')}}</h5>
                            </div>
                            <div class="card-body">
                                <div id='event_calendar' class='calendar'></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="col-xxl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5>{{__('Staff')}}</h5>
                                    <div class="row  mt-4">
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-users"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Total Staff')}}</p>
                                                    <h4 class="mb-0 text-success">{{ $countUser + $countIntern}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6 my-3 my-sm-0">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-info">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Employee')}}</p>
                                                    <h4 class="mb-0 text-primary">{{$countUser}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-danger">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Client')}}</p>
                                                    <h4 class="mb-0 text-danger">{{$countClient}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-users"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Intern')}}</p>
                                                    <h4 class="mb-0 text-success">{{ $countIntern}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5>{{__('Job')}}</h5>
                                    <div class="row  mt-4">
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-award"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Total Jobs')}}</p>
                                                    <h4 class="mb-0 text-success">{{$activeJob + $inActiveJOb}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6 my-3 my-sm-0">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-info">
                                                    <i class="ti ti-check"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Active Job')}}</p>
                                                    <h4 class="mb-0 text-primary">{{$activeJob}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-danger">
                                                    <i class="ti ti-x"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Inactive Job ')}}</p>
                                                    <h4 class="mb-0 text-danger">{{$inActiveJOb}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5>{{__('Training')}}</h5>
                                    <div class="row  mt-4">
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-users"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Total Training')}}</p>
                                                    <h4 class="mb-0 text-success">{{ $onGoingTraining +   $doneTraining}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6 my-3 my-sm-0">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-info">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Trainer')}}</p>
                                                    <h4 class="mb-0 text-primary">{{$countTrainer}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-danger">
                                                    <i class="ti ti-user-check"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Active Training')}}</p>
                                                    <h4 class="mb-0 text-danger">{{$onGoingTraining}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-secondary">
                                                    <i class="ti ti-user-minus"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Done Training')}}</p>
                                                    <h4 class="mb-0 text-secondary">{{$doneTraining}}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">

                                <h5>{{__('Announcement List')}}</h5>
                            </div>
                            <div class="card-body" style="min-height: 295px;">
                                <div class="table-responsive">
                                    @if(count($announcements) > 0)
                                        <table class="table align-items-center">
                                            <thead>
                                            <tr>
                                                <th>{{__('Title')}}</th>
                                                <th>{{__('Start Date')}}</th>
                                                <th>{{__('End Date')}}</th>

                                            </tr>
                                            </thead>
                                            <tbody class="list">
                                            @foreach($announcements as $announcement)
                                                <tr>
                                                    <td>{{ $announcement->title }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($announcement->start_date) }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($announcement->end_date) }}</td>

                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="p-2">
                                            No accouncement present yet.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{__('Meeting schedule')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    @if(count($meetings) > 0)
                                        <table class="table align-items-center">
                                            <thead>
                                            <tr>
                                                <th>{{__('Title')}}</th>
                                                <th>{{__('Date')}}</th>
                                                <th>{{__('Time')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody class="list">
                                            @foreach($meetings as $meeting)
                                                <tr>
                                                    <td>{{ $meeting->title }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($meeting->date) }}</td>
                                                    <td>{{  \Auth::user()->timeFormat($meeting->time) }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="p-2">
                                            No meeting scheduled yet.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ sample-page ] end -->
        </div>
    @endif
@endsection


