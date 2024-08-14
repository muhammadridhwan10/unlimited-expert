@extends('layouts.admin')
@section('page-title')
    {{__('Dashboard')}}
@endsection
@push('script-page')
    <script>
        (function () {
            var chartBarOptions = {
                series: [
                    {
                    name: '{{ __("Income (Rp)") }}',
                    data:  {!! json_encode($chartIncomeArrRp) !!},
                    },
                ],

                chart: {
                    height: 300,
                    type: 'line',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: {!! json_encode($monthList) !!},
                    title: {
                        text: '{{ __("Months") }}'
                    }
                },
                colors: ['#6fd944'],

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                // markers: {
                //     size: 4,
                //     colors: ['#ffa21d', '#FF3A6E'],
                //     opacity: 0.9,
                //     strokeWidth: 2,
                //     hover: {
                //         size: 7,
                //     }
                // },
                yaxis: {
                    title: {
                        text: '{{ __("Income (Rp)") }}'
                    },

                }

            };
            var arChart = new ApexCharts(document.querySelector("#chart-sales-rp"), chartBarOptions);
            arChart.render();
        })();

        (function () {
            var chartBarOptions = {
                series: [
                    {
                        name: '{{ __("Income (USD)") }}',
                        data:  {!! json_encode($chartIncomeArrUsd) !!},
                    },
                ],

                chart: {
                    height: 300,
                    type: 'line',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: {!! json_encode($monthList) !!},
                    title: {
                        text: '{{ __("Months") }}'
                    }
                },
                colors: ['#FF3A6E'],

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                // markers: {
                //     size: 4,
                //     colors: ['#ffa21d', '#FF3A6E'],
                //     opacity: 0.9,
                //     strokeWidth: 2,
                //     hover: {
                //         size: 7,
                //     }
                // },
                yaxis: {
                    title: {
                        text: '{{ __("Income ($)") }}'
                    },

                }

            };
            var arChart = new ApexCharts(document.querySelector("#chart-sales-doll"), chartBarOptions);
            arChart.render();
        })();
    </script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        var year = '{{$currentYear}}';
        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {type: 'jpeg', quality: 1},
                html2canvas: {scale: 4, dpi: 72, letterRendering: true},
                jsPDF: {unit: 'in', format: 'A2'}
            };
            html2pdf().set(opt).from(element).save();
        }
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var ctx = document.getElementById('employeeChart').getContext('2d');
            var employeeChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jakarta', 'Bekasi', 'Malang'],
                    datasets: [{
                        label: 'Total Employees',
                        data: [
                            {{$totalEmployees['Jakarta']}},
                            {{$totalEmployees['Bekasi']}},
                            {{$totalEmployees['Malang']}}
                        ],
                        backgroundColor: randomColor(),
                        borderColor: randomColor(),
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
        });

        function randomColor() {
            var r = Math.floor(Math.random() * 256);
            var g = Math.floor(Math.random() * 256);
            var b = Math.floor(Math.random() * 256);
            return 'rgba(' + r + ',' + g + ',' + b + '1)';
        }
    </script>
    <script>
    var employeesByBranch = @json($employeesByBranch);
    var employeeTypes = @json($employeeTypes);

    function randomColor() {
        var r = Math.floor(Math.random() * 256);
        var g = Math.floor(Math.random() * 256);
        var b = Math.floor(Math.random() * 256);
        return 'rgba(' + r + ',' + g + ',' + b + '1)';
    }

    // Render Chart for Jakarta
    var ctxJakarta = document.getElementById('employeeChartsJakarta').getContext('2d');
    var dataJakarta = {
        labels: employeeTypes,
        datasets: [{
            label: 'Total Employees',
            data: Object.values(employeesByBranch['Jakarta']),
            backgroundColor: randomColor(),
            borderColor: randomColor(),
            borderWidth: 1
        }]
    };
    var optionsJakarta = {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    };
    new Chart(ctxJakarta, {
        type: 'bar',
        data: dataJakarta,
        options: optionsJakarta
    });

    // Render Chart for Bekasi
    var ctxBekasi = document.getElementById('employeeChartsBekasi').getContext('2d');
    var dataBekasi = {
        labels: employeeTypes,
        datasets: [{
            label: 'Total Employees',
            data: Object.values(employeesByBranch['Bekasi']),
            backgroundColor: randomColor(),
            borderColor: randomColor(),
            borderWidth: 1
        }]
    };
    var optionsBekasi = {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    };
    new Chart(ctxBekasi, {
        type: 'bar',
        data: dataBekasi,
        options: optionsBekasi
    });

    // Render Chart for Malang
    var ctxMalang = document.getElementById('employeeChartsMalang').getContext('2d');
    var dataMalang = {
        labels: employeeTypes,
        datasets: [{
            label: 'Total Employees',
            data: Object.values(employeesByBranch['Malang']),
            backgroundColor: randomColor(),
            borderColor: randomColor(),
            borderWidth: 1
        }]
    };
    var optionsMalang = {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    };
    new Chart(ctxMalang, {
        type: 'bar',
        data: dataMalang,
        options: optionsMalang
    });
    </script>
    @foreach($lineChartDataRp as $partnerName => $partnerData)
        <script>
            function randomColor() {
                var r = Math.floor(Math.random() * 256);
                var g = Math.floor(Math.random() * 256);
                var b = Math.floor(Math.random() * 256);
                return 'rgba(' + r + ',' + g + ',' + b + '1)';
            }

            var ctx = document.getElementById('lineChartRp-{{ $partnerName }}').getContext('2d');

            var lineChartDataRp = {!! json_encode($partnerData) !!};
            var monthList = {!! json_encode($monthList) !!};

            var data = {
                labels: Object.values(monthList),
                datasets: [{
                    label: 'Invoice Chart for {{ $partnerName }} (Rp)',
                    data: Object.values(lineChartDataRp),
                    fill: false,
                    backgroundColor: randomColor(),
                    borderColor: randomColor(),
                    borderWidth: 1
                }]
            };

            var chart = new Chart(ctx, {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Invoice Chart for {{ $partnerName }}'
                    },
                    scales: {
                        xAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: 'Month'
                            }
                        }],
                        yAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: 'Total Invoice'
                            }
                        }]
                    }
                }
            });
        </script>
    @endforeach
    @foreach($lineChartDataUsd as $partnerName => $partnerData)
        <script>
            function randomColor() {
                var r = Math.floor(Math.random() * 256);
                var g = Math.floor(Math.random() * 256);
                var b = Math.floor(Math.random() * 256);
                return 'rgba(' + r + ',' + g + ',' + b + '1)';
            }

            var ctx = document.getElementById('lineChartUsd-{{ $partnerName }}').getContext('2d');

            var lineChartDataUsd = {!! json_encode($partnerData) !!};
            var monthList = {!! json_encode($monthList) !!};

            var data = {
                labels: Object.values(monthList),
                datasets: [{
                    label: 'Invoice Chart for {{ $partnerName }} ($)',
                    data: Object.values(lineChartDataUsd),
                    fill: false,
                    backgroundColor: randomColor(),
                    borderColor: randomColor(),
                    borderWidth: 1
                }]
            };

            var chart = new Chart(ctx, {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Invoice Chart for {{ $partnerName }}'
                    },
                    scales: {
                        xAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: 'Month'
                            }
                        }],
                        yAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: 'Total Invoice'
                            }
                        }]
                    }
                }
            });
        </script>
        <script>
            var ptx = document.getElementById('attendanceChartPusat').getContext('2d');
            var btx = document.getElementById('attendanceChartBekasi').getContext('2d');
            var mtx = document.getElementById('attendanceChartMalang').getContext('2d');
            var attendanceChartPusat = new Chart(ptx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 31}, (v, k) => k + 1),
                    datasets: [{
                        label: 'Present',
                        data: @json($absentDataPusat),
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2
                    }, {
                        label: 'Late',
                        data: @json($lateDataPusat),
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
            var attendanceChartBekasi = new Chart(btx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 31}, (v, k) => k + 1),
                    datasets: [{
                        label: 'Present',
                        data: @json($absentDataBekasi),
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2
                    }, {
                        label: 'Late',
                        data: @json($lateDataBekasi),
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
            var attendanceChartMalang = new Chart(mtx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 31}, (v, k) => k + 1),
                    datasets: [{
                        label: 'Present',
                        data: @json($absentDataMalang),
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2
                    }, {
                        label: 'Late',
                        data: @json($lateDataMalang),
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
        </script>
    @endforeach


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
    </style>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{__('Dashboard')}}</a></li>
@endsection
@section('action-btn')

@endsection

@section('content')
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-4">
                </div>
                <div class="col-md-8 mt-4">
                    <ul class="nav nav-pills nav-fill cust-nav information-tab" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="company-hrm-tab" data-bs-toggle="pill"
                                data-bs-target="#company-hrm" type="button">{{ __('HRM') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="company-finance-tab"
                                data-bs-toggle="pill" data-bs-target="#company-finance"
                                type="button">{{ __('Finance') }}</button>
                        </li>
                        {{-- <li class="nav-item" role="presentation">
                            <button class="nav-link " id="company-project-tab"
                                data-bs-toggle="pill" data-bs-target="#company-project"
                                type="button">{{ __('Projects') }}</button>
                        </li> --}}
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 ">
            <div class="row">
                <div class="col-lg-12">
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade active show" id="company-hrm" role="tabpanel"
                            aria-labelledby="pills-user-tab-1">
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
                                            <div class="container">
                                                <div id="message"></div>
                                                <br>
                                                <div id="timer"></div> 
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xxl-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>{{__("Didn't Activate Unlimited Tracker today")}}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">

                                                <div class="col-md-12">
                                                    <div class="row g-3 flex-nowrap team-lists horizontal-scroll-cards">
                                                        @foreach($notEnableDesktops as $notEnableDesktop)

                                                            <div class="col-auto">
                                                                <img src="{{(!empty($notEnableDesktop))? $notEnableDesktop->profile : asset(Storage::url('uploads/avatar/avatar.png'))}}" alt="">

                                                                <p class="mt-2">{{ $notEnableDesktop->name }}</p>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{ Form::open(array('route' => array('admin.dashboard'),'method'=>'get','id'=>'report_monthly_medical_allowance')) }}
                            <div class="row">
                                <div class="col-xxl-12">
                                    <div class="card">
                                        <div class="card-header">
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
                                                            <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_monthly_medical_allowance').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                                            </a>
                                                            <a href="{{route('admin.dashboard')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                                                <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xxl-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>{{__("Attendance Statistics Pusat")}}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <canvas id="attendanceChartPusat" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>{{__("Attendance Statistics Bekasi")}}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <canvas id="attendanceChartBekasi" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>{{__("Attendance Statistics Malang")}}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <canvas id="attendanceChartMalang" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{ Form::close() }}
                            <div class="row">
                                <div class="col-md-6">
                                   <div class="card">
                                        <div class="card-header">
                                            <h4>Total Employees by Branch</h4>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="employeeChart" width="400" height="200"></canvas>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4>Detail Employee by Branch Jakarta</h4>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="employeeChartsJakarta" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4>Detail Employee by Branch Bekasi</h4>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="employeeChartsBekasi" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4>Detail Employee by Branch Malang</h4>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="employeeChartsMalang" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="tab-pane fade" id="company-finance" role="tabpanel"
                        aria-labelledby="pills-user-tab-3">
                            <div class="row">
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
                                                            <small class="text-muted h6">{{__('Total Income Bekasi')}}</small>
                                                            <h6 class="m-0">{{\Auth::user()->priceFormat(\Auth::user()->IncomePartnersBekasi())}}</h6>
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
                                                            <small class="text-muted h6">{{__('Total Expense Bekasi')}}</small>
                                                            <h6 class="m-0">{{\Auth::user()->priceFormat(\Auth::user()->ExpensePartnersBekasi())}}</h6>
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
                                                            <small class="text-muted h6">{{__('Total Balance Bekasi')}}</small>
                                                            <h6 class="m-0">{{\Auth::user()->priceFormat(\Auth::user()->BalancePartnersBekasi())}}</h6>
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
                                                            <small class="text-muted h6">{{__('Total Income Malang')}}</small>
                                                            <h6 class="m-0">{{\Auth::user()->priceFormat(\Auth::user()->IncomePartnersMalang())}}</h6>
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
                                                            <small class="text-muted h6">{{__('Total Expense Malang')}}</small>
                                                            <h6 class="m-0">{{\Auth::user()->priceFormat(\Auth::user()->ExpensePartnersMalang())}}</h6>
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
                                                            <small class="text-muted h6">{{__('Total Balance Malang')}}</small>
                                                            <h6 class="m-0">{{\Auth::user()->priceFormat(\Auth::user()->BalancePartnersMalang())}}</h6>
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
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">{{__('Invoice Overview')}}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="progress-wrapper">
                                                <span class="progress-label">{{$invoiceSummary['draft']['count'] . ' ' . __('Draft')}}</span>
                                                <div class="progress mt-1" style="height: 5px;">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{($invoiceSummary['draft']['count'] / $invoiceSummary['total']) * 100}}%;" aria-valuenow="{{$invoiceSummary['draft']['count']}}" aria-valuemin="0" aria-valuemax="{{$invoiceSummary['total']}}"></div>
                                                </div>
                                            </div>
                                            <div class="progress-wrapper">
                                                <span class="progress-label">{{$invoiceSummary['paid']['count'] . ' ' . __('Paid')}}</span>
                                                <div class="progress mt-1" style="height: 5px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{($invoiceSummary['paid']['count'] / $invoiceSummary['total']) * 100}}%;" aria-valuenow="{{$invoiceSummary['paid']['count']}}" aria-valuemin="0" aria-valuemax="{{$invoiceSummary['total']}}"></div>
                                                </div>
                                            </div>
                                            <div class="progress-wrapper">
                                                <span class="progress-label">{{$invoiceSummary['unpaid']['count'] . ' ' . __('Unpaid')}}</span>
                                                <div class="progress mt-1" style="height: 5px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{($invoiceSummary['unpaid']['count'] / $invoiceSummary['total']) * 100}}%;" aria-valuenow="{{$invoiceSummary['unpaid']['count']}}" aria-valuemin="0" aria-valuemax="{{$invoiceSummary['total']}}"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>                
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class=" mt-2 " id="multiCollapseExample1">
                                        <div class="card">
                                            <div class="card-body">
                                                {{ Form::open(array('route' => array('admin.dashboard'),'method' => 'GET','id'=>'report_income_summary')) }}
                                                <div class="row align-items-center justify-content-end">
                                                    <div class="col-xl-10">
                                                        <div class="row">

                                                            <div class="col-md-6 col-sm-12 col-12">
                                                                <div class="btn-box">
                                                                    {{ Form::label('year', __('Year'),['class'=>'form-label'])}}
                                                                    {{ Form::select('year',$yearList,isset($_GET['year'])?$_GET['year']:'', array('class' => 'form-control select')) }}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 col-sm-12 col-12">
                                                                <div class="btn-box">
                                                                    {{ Form::label('company', __('Company'),['class'=>'form-label'])}}
                                                                    {{ Form::select('company',$companies,isset($_GET['company'])?$_GET['company']:'', array('class' => 'form-control select')) }}
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="row">
                                                            <div class="col-auto mt-4">
                                                                <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_income_summary').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                                                </a>
                                                                <a href="{{route('admin.dashboard')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
                            {{-- <div class="row">
                                <div class="col-xl-6 col-md-6 col-lg-6">
                                    <div class="card p-4 mb-4">
                                        <h7 class="report-text gray-text mb-0">{{__('Total Paid Invoice (Rp)')}}</h7>
                                        <h6 class="report-text mb-0">{{Auth::user()->priceFormat($totalInvoiceRp)}}</h6>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-lg-6">
                                    <div class="card p-4 mb-4">
                                        <h7 class="report-text gray-text mb-0">{{__('Total Paid Invoice ($)')}}</h7>
                                        <h6 class="report-text mb-0">{{Auth::user()->priceFormat2($totalInvoiceDollar)}}</h6>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="row">
                                {{-- <div class="col-6" id="chart-container">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="scrollbar-inner">
                                                <div id="chart-sales-rp" data-color="primary" data-height="300" ></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6" id="chart-container">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="scrollbar-inner">
                                                <div id="chart-sales-doll" data-color="primary" data-height="300" ></div>
                                            </div>
                                        </div>
                                    </div>
                                </div> --}}
                                @foreach($lineChartDataRp as $partnerName => $partnerData)
                                <div class="col-6" id="chart-container">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="scrollbar-inner">
                                                <canvas id="lineChartRp-{{ $partnerName }}" data-color="primary" data-height="300" ></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @foreach($lineChartDataUsd as $partnerName => $partnerData)
                                <div class="col-6" id="chart-container">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="scrollbar-inner">
                                                <canvas id="lineChartUsd-{{ $partnerName }}" data-color="primary" data-height="300" ></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- <div class="tab-pane fade" id="company-project" role="tabpanel"
                        aria-labelledby="pills-user-tab-4">
                            <div class="row">
                                
                            </div>
                        </div> --}}

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
