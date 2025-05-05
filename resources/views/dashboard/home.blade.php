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
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
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
                fetchReminders('3days');
            });
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
            const projectFilter = document.getElementById('filter-project');
            const timeframeFilter = document.getElementById('filter-timeframe');
            const summaryText = document.getElementById('summary-text');

            let chart;

            // Inisialisasi chart
            const ctx = document.getElementById('performanceChart').getContext('2d');
            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Work Time (Hour)',
                        data: [],
                        backgroundColor: '#1de9b6'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Fungsi ambil data dari server
            async function fetchData() {
                const projectId = projectFilter.value;
                const timeframe = timeframeFilter.value;

                const url = `{{ route('dashboard.report.data') }}?project_id=${projectId}&timeframe=${timeframe}`;
                const res = await fetch(url);
                const data = await res.json();

                chart.data.labels = data.labels;
                chart.data.datasets[0].data = data.data;
                chart.update();

                // Ringkasan
                if (data.labels.length > 0) {
                    const total = data.data.reduce((a, b) => a + b, 0);
                    const avg = (total / data.labels.length).toFixed(2);
                    summaryText.innerHTML = `
                        Total: <strong>${total.toFixed(2)} jam</strong> |
                        Rata-rata: <strong>${avg} jam/${timeframe}</strong>
                    `;
                } else {
                    summaryText.innerText = 'Tidak ada data.';
                }
            }

            // Event listener
            projectFilter.addEventListener('change', fetchData);
            timeframeFilter.addEventListener('change', fetchData);

            fetchData(); // Load awal
        });
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
                    summaryHTML += `<br>Average Work Time per Day: <strong>${avgPerDay.toFixed(2)} Hour/Day</strong>`;
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
                const userId = document.getElementById('filter-user').value;

                const res = await fetch(`{{ route('dashboard.project.distribution.partner') }}?start_date=${start}&end_date=${end}&user_id=${userId}`);
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

                                    return `${context.label}: ${value.toFixed(2)} jam (${percentage}%)`;
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

        {{-- <div class="section">
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
                                        <option value="3days">Dalam 3 Hari</option>
                                        <option value="7days">Dalam 7 Hari</option>
                                        <option value="14days">Dalam 14 Hari</option>
                                        <option value="1month">Dalam 1 Bulan</option>
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
                        <h5>📦 Project Time Distribution</h5>
                        <span data-bs-toggle="tooltip" title="Menampilkan distribusi waktu kerja per proyek dalam periode tertentu">
                            ⚠️
                        </span>
                    </div>
                    <div class="card-body">
                        <!-- Filter Rentang Waktu -->
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label for="chart-type" class="form-label">Chart Type</label>
                                <select id="chart-type" class="form-select">
                                    <option value="bar" selected>Bar</option>
                                    <option value="pie">Pie</option>
                                    <option value="doughnut">Doughnut</option>
                                    <option value="radar">Radar</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="filter-user" class="form-label">Select Employee</label>
                                <select id="filter-user" class="form-select select2">
                                    <option value="">Pilih User</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
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
                            Tidak ada data.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- <div class="row">
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
        </div> --}}

        {{-- <div class="col-xxl-12">
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
        </div> --}}

        {{-- <div class="row">
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
        </div> --}}

        {{-- <div class="row">
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
        </div> --}}

@endsection


