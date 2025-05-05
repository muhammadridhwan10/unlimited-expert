@extends('layouts.admin')
@section('page-title')
    {{__('Home')}}
@endsection

@push('script-page')
    <script src="https://www.gstatic.com/firebasejs/7.23.0/firebase.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    
    <script>
        $(document).ready(function () {
            // Pagination handler
            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();

                let url = $(this).attr('href');

                if (url.includes('untouched-projects')) {
                    loadTable(url, '#untouched-table');
                } else if (url.includes('in-progress-projects')) {
                    loadTable(url, '#inprogress-table');
                }
            });

            function loadTable(url, tableSelector) {
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function (response) {
                        $(tableSelector).html(response.html);
                    },
                    error: function () {
                        alert('Gagal memuat data.');
                    }
                });
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('reminder-filter-form');
            const select = document.getElementById('reminder-filter-select');
            const listContainer = document.getElementById('reminder-list');

            // Fungsi fetch data dari server
            function fetchReminders(filterValue = '') {
                const url = "{{ route('dashboard.reminders.data') }}?reminder_filter=" + filterValue;

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        listContainer.innerHTML = data.html;
                    })
                    .catch(err => {
                        console.error("Error fetching reminders:", err);
                        listContainer.innerHTML = "<li>Error memuat reminder.</li>";
                    });
            }

            // Event submit form filter
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const filterValue = select.value;
                fetchReminders(filterValue);
            });

            // Load data default saat halaman pertama kali dibuka
            window.addEventListener('load', () => {
                fetchReminders('3days'); // <- 🔥 Ini bagian penting: load default 3 hari
            });
        });
    </script>

    <script>

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
    </script>

    <script>
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
            })
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                events: {!! json_encode($events) !!},
                editable: false,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true
            });
            calendar.render();
        });
    </script>

    <script>
        let distributionChart;

        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('projectDistributionChart').getContext('2d');
            const chartTypeSelect = document.getElementById('chart-type');
            const summaryElement = document.getElementById('distribution-summary');

            const startDateInput = document.getElementById('start-date');
            const endDateInput = document.getElementById('end-date');

            const today = new Date();
            const lastWeek = new Date();
            lastWeek.setDate(today.getDate() - 7);

            startDateInput.value = lastWeek.toISOString().split('T')[0];
            endDateInput.value = today.toISOString().split('T')[0];
            

            async function initChart(type = 'bar') {
                const data = await fetchData(); // fungsi fetch data dari server

                if (distributionChart) {
                    distributionChart.destroy();
                }

                distributionChart = new Chart(ctx, {
                    type: type,
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Work Time (Hour)',
                            data: data.data,
                            backgroundColor: generateColors(data.labels.length),
                            hoverOffset: 30
                        }]
                    },
                    options: getChartOptions(type)
                });

                updateSummary(data, startDateInput.value, endDateInput.value);
            }

            function updateSummary(data, startDate, endDate) {
                const totalHours = data.total_hours || 0;
                const projectCount = data.labels.length;

                const start = new Date(startDate);
                const end = new Date(endDate);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                let summaryHTML = `
                    Total Work Time from <strong>${startDate}</strong> - <strong>${endDate}</strong>: 
                    <strong>${totalHours.toFixed(2)} Hour</strong>
                `;

                if (diffDays > 1) {
                    const avgPerDay = totalHours / diffDays;
                    summaryHTML += `<br>Average Time Work per Day: <strong>${avgPerDay.toFixed(2)} hour/day</strong>`;
                }

                if (!projectCount) {
                    summaryHTML = 'Data Not Found.';
                }

                summaryElement.innerHTML = summaryHTML;
            }

            function generateColors(count) {
                const colors = [];
                for (let i = 0; i < count; i++) {
                    const hue = i * 137.508; // golden angle approximation
                    colors.push(`hsl(${hue}, 70%, 60%)`);
                }
                return colors;
            }

            async function fetchData() {
                const start = document.getElementById('start-date').value;
                const end = document.getElementById('end-date').value;

                const res = await fetch(`{{ route('dashboard.project.distribution') }}?start_date=${start}&end_date=${end}`);
                return await res.json();
            }

            function getChartOptions(type) {
                const base = {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: type === 'pie' || type === 'doughnut' ? 'right' : 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let value;

                                    // Baca nilai sesuai tipe chart
                                    if (type === 'pie' || type === 'doughnut') {
                                        value = context.parsed;
                                    } else if (type === 'radar') {
                                        value = context.parsed.r; // untuk radar
                                    } else {
                                        value = context.raw; // untuk bar, line, dll
                                    }

                                    const dataset = context.chart.data.datasets[0];
                                    const total = dataset.data.reduce((a, b) => a + b, 0);

                                    const percentage = ((value / total) * 100).toFixed(1);

                                    return `${context.label}: ${value.toFixed(2)} hour (${percentage}%)`;
                                }
                            }
                        }
                    }
                };

                if (type === 'radar') {
                    base.scales = {
                        r: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 5
                            }
                        }
                    };
                }

                return base;
            }

            chartTypeSelect.addEventListener('change', (e) => {
                initChart(e.target.value);
            });

            document.getElementById('filter-project-distribution').addEventListener('click', () => {
                initChart(chartTypeSelect.value);
            });

            initChart();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const projectFilter = document.getElementById('filter-project');
            const timeframeFilter = document.getElementById('filter-timeframe');
            const chartTypeSelect = document.getElementById('chart-type');
            const summaryText = document.getElementById('summary-text');

            let chart;

            // Inisialisasi chart
            const ctx = document.getElementById('performanceChart').getContext('2d');

            async function initChart(type = 'bar') {
                const projectId = projectFilter.value;
                const timeframe = timeframeFilter.value;

                const url = `{{ route('dashboard.report.data') }}?project_id=${projectId}&timeframe=${timeframe}`;
                const res = await fetch(url);
                const data = await res.json();

                if (chart) {
                    chart.destroy(); // Hapus chart lama jika ada
                }

                chart = new Chart(ctx, {
                    type: type,
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Work Time (Hour)',
                            data: data.data,
                            backgroundColor: generateColors(data.labels.length),
                            hoverOffset: 30
                        }]
                    },
                    options: getChartOptions(type)
                });

                updateSummary(data);
            }

            function updateSummary(data) {
                if (data.labels.length > 0) {
                    const total = data.data.reduce((a, b) => a + b, 0);
                    const avg = (total / data.labels.length).toFixed(2);
                    const timeframe = timeframeFilter.value;

                    summaryText.innerHTML = `
                        Total: <strong>${total.toFixed(2)} Hour</strong> |
                        Average: <strong>${avg} Hour/${timeframe}</strong>
                    `;
                } else {
                    summaryText.innerText = 'Data Not Found.';
                }
            }

            function generateColors(count) {
                const colors = [];
                for (let i = 0; i < count; i++) {
                    const hue = i * 137.508; // golden angle approximation
                    colors.push(`hsl(${hue}, 70%, 60%)`);
                }
                return colors;
            }

            function getChartOptions(type) {
                const base = {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: type === 'pie' || type === 'doughnut' ? 'right' : 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let value;

                                    if (type === 'pie' || type === 'doughnut') {
                                        value = context.parsed;
                                    } else if (type === 'radar') {
                                        value = context.parsed.r;
                                    } else {
                                        value = context.raw;
                                    }

                                    const dataset = context.chart.data.datasets[0];
                                    const total = dataset.data.reduce((a, b) => a + b, 0);

                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0';

                                    return `${context.label}: ${value.toFixed(2)} hour (${percentage}%)`;
                                }
                            }
                        }
                    }
                };

                if (type === 'radar') {
                    base.scales = {
                        r: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 5
                            }
                        }
                    };
                }

                if (type === 'bar') {
                    base.scales = {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + ' hour';
                                }
                            }
                        }
                    };
                }

                return base;
            }

            // Event listener
            projectFilter.addEventListener('change', () => initChart(chartTypeSelect.value));
            timeframeFilter.addEventListener('change', () => initChart(chartTypeSelect.value));
            chartTypeSelect.addEventListener('change', (e) => initChart(e.target.value));

            // Load awal
            initChart();
        });
    </script>

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
                                // Determine the badge class based on the status
                                let badgeClass = project.status.includes('Overdue') ? 'bg-danger' : 'bg-success';
                                
                                // Create the table row with the appropriate badge
                                let row = `
                                    <tr>
                                        <td>${project.project_name}</td>
                                        <td>${project.end_date}</td>
                                        <td><span class="badge ${badgeClass}">${project.status}</span></td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                        } else {
                            tbody.append(`
                                <tr>
                                    <td colspan="3" class="text-center">No projects approaching the deadline.</td>
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
                                        <td>${project.project_name}</td>
                                        <td>${project.status}</td>
                                        <td>${project.start_date}</td>
                                        <td>${new Date(project.total_time_in_seconds * 1000).toISOString().substr(11, 8)}</td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                        } else {
                            tbody.append(`
                                <tr>
                                    <td colspan="3" class="text-center">No untouched projects found.</td>
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
                                        <td>${project.project_name}</td>
                                        <td>${project.status}</td>
                                        <td>${project.start_date}</td>
                                        <td>${new Date(project.total_time_in_seconds * 1000).toISOString().substr(11, 8)}</td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                        } else {
                            tbody.append(`
                                <tr>
                                    <td colspan="4" class="text-center">No in-progress projects found.</td>
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
        <!--------------------- Dashboard Project ----------------------------------->
        {{-- <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Project Reminder') }}</h5>
                        <div class="float-end">
                            <select id="filter_range" class="form-select">
                                <option value="7_days">7 Days</option>
                                <option value="1_week">1 Week</option>
                                <option value="1_month">1 Month</option>
                                <option value="2_months">2 Months</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="project-reminder-table">
                                <thead>
                                    <tr>
                                        <th>Project Name</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($projectReminder as $index => $project)
                                    <tr>
                                        <td>{{ $project->project_name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') }}</td>
                                        <td>
                                            @if (str_contains($project->status, 'Overdue'))
                                                <span class="badge bg-danger">{{ $project->status }}</span>
                                            @else
                                                <span class="badge bg-success">{{ $project->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No projects approaching the deadline.</td>
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
                                        <th>Project Name</th>
                                        <th>Status</th>
                                        <th>Start Date</th>
                                        <th>Total Timesheet</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($untouchedProjects as $index => $project)
                                    <tr>
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
                                        <td colspan="3" class="text-center">No untouched projects found.</td>
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
                                        <th>Project Name</th>
                                        <th>Status</th>
                                        <th>Start Date</th>
                                        <th>Total Timesheet</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($inProgressProjects as $index => $project)
                                    <tr>
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
                                        <td colspan="4" class="text-center">No projects in progress found.</td>
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
        </div> --}}

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>📅 Planning Calendar</h5>
                    </div>
                    <div class="card-body">
                        <div id='calendar'></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>🔔 Project Reminders</h5>
                    </div>
                    <div class="card-body">
                        <form id="reminder-filter-form">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <select name="reminder_filter" class="form-select" id="reminder-filter-select">
                                        <option value="3days">In 3 Day</option>
                                        <option value="7days">In 7 Day</option>
                                        <option value="14days">In 14 Day</option>
                                        <option value="1month">In 1 Day</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                                </div>
                            </div>
                        </form>

                        <ul id="reminder-list" class="mt-3">
                            <li>Loading...</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Untouched Projects -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>🔴 Untouched Projects</h5>
                        <span data-bs-toggle="tooltip" title="Projek yang belum tersentuh dalam sebulan terakhir, tidak memiliki timesheet aktif.">
                        ⚠️
                        </span>
                    </div>
                    <div class="card-body">
                        <div id="untouched-table">
                            @include('dashboard.partials.untouched-projects-table')
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>🟡 In Progress Projects</h5>
                        <span data-bs-toggle="tooltip" title="Projek yang aktif">
                        ⚠️
                        </span>
                    </div>
                    <div class="card-body">
                        <div id="inprogress-table">
                            @include('dashboard.partials.inprogress-projects-table')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>📊 Analytical Performance Report</h5>
                        <span data-bs-toggle="tooltip" title="Menampilkan total jam kerja berdasarkan proyek dan rentang waktu yang dipilih">
                            ⚠️
                        </span>
                    </div>
                    <div class="card-body">
                        <!-- Filter -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select id="filter-project" class="form-select">
                                    <option value="">All Project</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="filter-timeframe" class="form-select">
                                    <option value="day">Per Day</option>
                                    <option value="week">Per Week</option>
                                    <option value="month" selected>Per Month</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="chart-type" class="form-select">
                                    <option value="bar" selected>Bar</option>
                                    <option value="pie">Pie</option>
                                    <option value="doughnut">Doughnut</option>
                                    <option value="radar">Radar</option>
                                </select>
                            </div>
                        </div>

                        <!-- Chart -->
                        <canvas id="performanceChart" height="70"></canvas>

                        <!-- Ringkasan -->
                        <div id="summary-text" class="mt-3 text-center">
                            Data Not Found.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>📦 Project Time Distribution</h5>
                        <span data-bs-toggle="tooltip" title="Menampilkan distribusi waktu kerja per proyek dalam periode tertentu">
                            ⚠️
                        </span>
                    </div>
                    <div class="card-body">
                        <!-- Filter Rentang Waktu -->
                        <div class="col-md-2">
                            <label for="chart-type" class="form-label">Chart Type</label>
                            <select id="chart-type" class="form-select">
                                <option value="bar" selected>Bar</option>
                                <option value="pie">Pie</option>
                                <option value="doughnut">Doughnut</option>
                                <option value="radar">Radar</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label for="start-date" class="form-label">Start Date</label>
                                <input type="date" id="start-date" class="form-control">
                            </div>
                            <div class="col-md-5">
                                <label for="end-date" class="form-label">End Date</label>
                                <input type="date" id="end-date" class="form-control">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button id="filter-project-distribution" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>

                        <!-- Chart -->
                        <canvas id="projectDistributionChart" height="70"></canvas>

                        <!-- Ringkasan Total -->
                        <div id="distribution-summary" class="mt-3 text-center">
                            Data Not Found.
                        </div>
                    </div>
                </div>
            </div>
        </div>

         <!--------------------- End Dashboard Project ----------------------------------->
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


