@extends('layouts.admin')
@section('page-title')
    {{__('Home')}}
@endsection
@push('script-page')
    <script src="https://www.gstatic.com/firebasejs/7.23.0/firebase.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        function typeWriter(text, i, callback) {
            if (i < text.length) {
                document.getElementById("timer").innerHTML += text.charAt(i);
                i++;
                setTimeout(function() {
                    typeWriter(text, i, callback);
                }, 50);
            } else {
                callback();
            }
        }

        @if(!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
            var clockInTime = "{{ $employeeAttendance->clock_in }}";
            var welcomeMessage = "Hai {{ \Auth::user()->name }}, Have a nice day!!! 😊💪. You are present at " + clockInTime;

            typeWriter(welcomeMessage, 0, function() {
            });
       @else
            @if (!empty($employeeAttendance) && is_object($employeeAttendance))
                var currentDate = new Date();
                var employeeAttendanceDate = new Date("{{ $employeeAttendance->date }}");

                if (currentDate.toDateString() === employeeAttendanceDate.toDateString()) {
                    var clockOutTime = new Date("{{ date('Y-m-d', strtotime($employeeAttendance->clock_out)) }}T{{ date('H:i:s', strtotime($employeeAttendance->clock_out)) }}");
                    var clockInTime = new Date("{{ date('Y-m-d', strtotime($employeeAttendance->clock_in)) }}T{{ date('H:i:s', strtotime($employeeAttendance->clock_in)) }}");
                    var timeDifference = clockOutTime - clockInTime;
                    var hours = Math.floor(timeDifference / (1000 * 60 * 60));
                    var minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);

                    hours = hours - 1;

                    var workedDurationMessage = "You have worked today for " + hours + " hours, " + minutes + " minutes, and " + seconds + " seconds.";
                    typeWriter(workedDurationMessage, 0, function() {
                    });
                } else {
                    var notAttendedMessage = "You have not attended today, please clock in";
                    typeWriter(notAttendedMessage, 0, function() {
                    });
                }
            @else
                var notAttendedMessage = "You have not attended today, please clock in";
                typeWriter(notAttendedMessage, 0, function() {
                });
            @endif
        @endif
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
                labels: @json(array_keys($tasksPriorityPerProject)),
                datasets: [{
                    label: 'Tasks by Priority',
                    data:  @json(array_values($tasksPriorityPerProject)),
                    backgroundColor: ['#ff6384', '#36a2eb', '#4bc0c0'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
        });


        var ctx4 = document.getElementById('overdueTasksChart').getContext('2d');
        var overdueTasksChart = new Chart(ctx4, {
            type: 'pie',
            data: {
                labels: @json(array_keys($overdueTasksPerProject)),
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
                                <div class="col-md-6">
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

            <div class="col-lg-12">
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

        <div class="row">
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-primary">
                                        <i class="ti ti-cast"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{__('Total')}}</small>
                                        <h6 class="m-0">{{__('Projects')}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{ $home_data['total_project']['total'] }}</h4>
                                <small class="text-muted"><span class="text-success">{{ $home_data['total_project']['percentage'] }}%</span> {{__('completd')}}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-activity"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{__('Total')}}</small>
                                        <h6 class="m-0">{{__('Tasks')}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{ $home_data['total_task']['total'] }}</h4>
                                <small class="text-muted"><span class="text-success">{{ $home_data['total_task']['percentage'] }}%</span> {{__('completd')}}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
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
            <div class="col-lg-12">
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
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __("Overdue Tasks") }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="overdueTasksChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__('Top Due Projects')}}</h5>
                    </div>
                    <div class="card-body project_table">
                        <div class="table-responsive ">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('End Date')}}</th>
                                    <th class="text-end">{{__('Status')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($home_data['due_project']->count() > 0)
                                    @foreach($home_data['due_project'] as $due_project)

                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h5 class="mb-0"><a class="text-blue" href="{{ route('projects.show',$due_project) }}">{{ $due_project->project_name }}</a></h5>
                                                        <!-- <p class="mb-0"><span class="text-success">{{ \Auth::user()->priceFormat($due_project->budget) }}</p> -->

                                                    </div>
                                                </div>
                                            </td>
                                            <td >{{  Utility::getDateFormated($due_project->end_date) }}</td>
                                            <td class="text-end">
                                                <span class="status_badge p-2 px-3 rounded badge bg-{{\App\Models\Project::$status_color[$due_project->status]}}">{{ __(\App\Models\Project::$project_status[$due_project->status]) }}</span>

                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <div class="py-5">
                                        <h5 class="text-center mb-0">{{__('No Due Projects Found.')}}</h5>
                                    </div>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__('Projects Remaining In 2 Weeks')}}</h5>
                    </div>
                    <div class="card-body project_table">
                        <div class="table-responsive ">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('End Date')}}</th>
                                    <th class="text-end">{{__('Status')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($home_data['project']->count() > 0)
                                    @foreach($home_data['project'] as $project)
                                        <?php
                                            $harisekarang =   date('Y-m-d');
                                        
                                            $date_now = strtotime($harisekarang . "+1 days");
                                            $date_end = strtotime($project->end_date);
                        
                                            $jarak = $date_end - $date_now;
                                            $hari = $jarak / 60 / 60 / 24;
                        
                                            $jml_hari = array();
                                            $sabtuminggu = array();
                                            
                                            for ($i = $date_now; $i <= $date_end; $i += (60 * 60 * 24)) {
                                                if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                                                    $jml_hari[] = $i;
                                                } else {
                                                    $sabtuminggu[] = $i;
                                                }
                                            
                                            }

                                            $jumlah_hari = count($jml_hari);
                                        ?>
                                        @if ($jumlah_hari == 14)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                           <h5 class="mb-0"><a class="text-blue" href="{{ route('projects.show',$project) }}">{{ $project->project_name }}</a></h5>
                                                            <!-- <p class="mb-0"><span class="text-success">{{ \Auth::user()->priceFormat($due_project->budget) }}</p> -->

                                                        </div>
                                                    </div>
                                                </td>
                                                <td >{{  Utility::getDateFormated($project->end_date) }}</td>
                                                <td class="text-end">
                                                    <span class="status_badge p-2 px-3 rounded badge bg-{{\App\Models\Project::$status_color[$project->status]}}">{{ __(\App\Models\Project::$project_status[$project->status]) }}</span>

                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="py-5">
                                        <h5 class="text-center mb-0">{{__('No Project Remaining In 2 Weeks Found.')}}</h5>
                                    </div>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

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
        
        {{-- <div class="row">
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
                                                @foreach($attendance['status'] as $status)
                                                    <td>
                                                        @if($status=='P')
                                                            <i class="badge bg-success p-2 rounded">{{__('P')}}</i>
                                                        @elseif($status=='A')
                                                            <i class="badge bg-danger p-2 rounded">{{__('A')}}</i>
                                                        @elseif($status=='W')
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
        </div> --}}

        {{-- <div class="row">
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-primary">
                                        <i class="ti ti-cast"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{__('Total')}}</small>
                                        <h6 class="m-0">{{__('Projects')}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{ $home_data['total_project']['total'] }}</h4>
                                <small class="text-muted"><span class="text-success">{{ $home_data['total_project']['percentage'] }}%</span> {{__('completd')}}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-activity"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{__('Total')}}</small>
                                        <h6 class="m-0">{{__('Tasks')}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{ $home_data['total_task']['total'] }}</h4>
                                <small class="text-muted"><span class="text-success">{{ $home_data['total_task']['percentage'] }}%</span> {{__('completd')}}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__('Top Due Projects')}}</h5>
                    </div>
                    <div class="card-body project_table">
                        <div class="table-responsive ">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('End Date')}}</th>
                                    <th class="text-end">{{__('Status')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($home_data['due_project']->count() > 0)
                                    @foreach($home_data['due_project'] as $due_project)

                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h5 class="mb-0"><a class="text-blue" href="{{ route('projects.show',$due_project) }}">{{ $due_project->project_name }}</a></h5>
                                                        <!-- <p class="mb-0"><span class="text-success">{{ \Auth::user()->priceFormat($due_project->budget) }}</p> -->

                                                    </div>
                                                </div>
                                            </td>
                                            <td >{{  Utility::getDateFormated($due_project->end_date) }}</td>
                                            <td class="text-end">
                                                <span class="status_badge p-2 px-3 rounded badge bg-{{\App\Models\Project::$status_color[$due_project->status]}}">{{ __(\App\Models\Project::$project_status[$due_project->status]) }}</span>

                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <div class="py-5">
                                        <h5 class="text-center mb-0">{{__('No Due Projects Found.')}}</h5>
                                    </div>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__('Projects Remaining In 2 Weeks')}}</h5>
                    </div>
                    <div class="card-body project_table">
                        <div class="table-responsive ">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('End Date')}}</th>
                                    <th class="text-end">{{__('Status')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($home_data['project']->count() > 0)
                                    @foreach($home_data['project'] as $project)
                                        <?php
                                            $harisekarang =   date('Y-m-d');
                                        
                                            $date_now = strtotime($harisekarang . "+1 days");
                                            $date_end = strtotime($project->end_date);
                        
                                            $jarak = $date_end - $date_now;
                                            $hari = $jarak / 60 / 60 / 24;
                        
                                            $jml_hari = array();
                                            $sabtuminggu = array();
                                            
                                            for ($i = $date_now; $i <= $date_end; $i += (60 * 60 * 24)) {
                                                if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                                                    $jml_hari[] = $i;
                                                } else {
                                                    $sabtuminggu[] = $i;
                                                }
                                            
                                            }

                                            $jumlah_hari = count($jml_hari);
                                        ?>
                                        @if ($jumlah_hari == 14)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                           <h5 class="mb-0"><a class="text-blue" href="{{ route('projects.show',$project) }}">{{ $project->project_name }}</a></h5>
                                                            <!-- <p class="mb-0"><span class="text-success">{{ \Auth::user()->priceFormat($due_project->budget) }}</p> -->

                                                        </div>
                                                    </div>
                                                </td>
                                                <td >{{  Utility::getDateFormated($project->end_date) }}</td>
                                                <td class="text-end">
                                                    <span class="status_badge p-2 px-3 rounded badge bg-{{\App\Models\Project::$status_color[$project->status]}}">{{ __(\App\Models\Project::$project_status[$project->status]) }}</span>

                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="py-5">
                                        <h5 class="text-center mb-0">{{__('No Project Remaining In 2 Weeks Found.')}}</h5>
                                    </div>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
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


