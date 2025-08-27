{{-- resources/views/projects/activity_filter.blade.php - Updated with Branch Permissions --}}

@php
// Helper function untuk memastikan data aman
function getSafeValue($array, $key, $default = 0) {
    return isset($array[$key]) ? $array[$key] : $default;
}

function getSafeSummary($summary) {
    $defaultSummary = [
        'total_users' => 0,
        'total_projects' => 0,
        'active_today' => 0,
        'no_tracker_today' => 0,
        'absent_today' => 0,
        'no_data_today' => 0
    ];
    
    if (!isset($summary) || !is_array($summary)) {
        return $defaultSummary;
    }
    
    return array_merge($defaultSummary, $summary);
}

function getSafeActivity($project, $date) {
    if (!isset($project['daily_activity']) || !is_array($project['daily_activity'])) {
        return null;
    }
    
    return isset($project['daily_activity'][$date]) ? $project['daily_activity'][$date] : null;
}

// Get current user info for branch display
$currentUser = Auth::user();
$isAdminUser = ($currentUser->type === 'admin' || $currentUser->type === 'company');

// Set safe summary
$safeSummary = getSafeSummary($summary ?? []);
@endphp

{{-- Show info about filtering --}}
@if(isset($groupedActivities) && count($groupedActivities) > 0)
    {{-- Info banner about what's being shown --}}
    <div class="alert alert-info mb-3">
        <div class="d-flex align-items-center">
            <i class="ti ti-info-circle me-2"></i>
            <small>
                <strong>{{__('Filter Applied')}}:</strong>
                {{__('Showing only active users who worked on projects during')}}
                @if($timeFilter == 'today')
                    {{__('today')}} ({{ date('d M Y') }})
                @elseif($timeFilter == '7days')
                    {{__('the last 7 days')}} ({{ date('d M Y', strtotime('-7 days')) }} - {{ date('d M Y') }})
                @elseif($timeFilter == '1month')
                    {{__('the last month')}} ({{ date('d M Y', strtotime('-1 month')) }} - {{ date('d M Y') }})
                @endif
                • {{__('Admin and Company users are excluded')}} • {{__('Weekends are not shown')}} • {{__('Summary per user for entire period')}}
                @if(!$isAdminUser)
                    • <span class="text-primary fw-medium">{{__('Restricted to your branch only')}}</span>
                @endif
            </small>
        </div>
    </div>

    @foreach($groupedActivities as $branchName => $branchData)
        @if(isset($branchData['users']) && count($branchData['users']) > 0)
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">
                        <i class="ti ti-building me-2"></i>
                        {{__('Branch')}}: {{ $branchName }}
                        <span class="badge bg-primary ms-2">{{ count($branchData['users']) }} {{__('Users')}}</span>
                        @if(!$isAdminUser)
                            <small class="text-muted ms-2">{{__('(Your Branch)')}}</small>
                        @endif
                    </h6>
                    <div class="d-flex gap-2">
                        @php
                            $branchSummary = ['active' => 0, 'no_tracker' => 0, 'absent' => 0];
                            foreach($branchData['users'] as $userData) {
                                $todayDate = date('Y-m-d');
                                $combinedActivity = $userData['combined_activity'] ?? [];
                                if(isset($combinedActivity[$todayDate])) {
                                    $status = $combinedActivity[$todayDate]['status'];
                                    if(isset($branchSummary[$status])) {
                                        $branchSummary[$status]++;
                                    }
                                } else {
                                    // No data for today = absent
                                    $branchSummary['absent']++;
                                }
                            }
                        @endphp
                        <small class="badge bg-success">{{$branchSummary['active']}} Active</small>
                        <small class="badge bg-warning">{{$branchSummary['no_tracker']}} No Tracker</small>
                        <small class="badge bg-danger">{{$branchSummary['absent']}} Absent</small>
                    </div>
                </div>

                {{-- FIXED: Horizontal scroll table with frozen columns --}}
                <div class="table-container">
                    <table class="table table-bordered table-hover table-sm activity-table">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="frozen-user" width="200px">{{__('User')}}</th>
                                @if($timeFilter == 'today')
                                    @php
                                        $today = date('Y-m-d');
                                        $todayDayOfWeek = \Carbon\Carbon::parse($today)->format('w');
                                        $isWeekend = ($todayDayOfWeek == 0 || $todayDayOfWeek == 6);
                                    @endphp
                                    @if(!$isWeekend)
                                        <th class="text-center date-column" width="120px">
                                            <div class="d-flex flex-column">
                                                <span>{{__('Today')}}</span>
                                                <small class="text-muted">{{ date('d M Y') }}</small>
                                            </div>
                                        </th>
                                    @else
                                        <th class="text-center date-column" width="120px">
                                            <div class="d-flex flex-column">
                                                <span class="text-muted">{{__('Weekend')}}</span>
                                                <small class="text-muted">{{ date('d M Y') }}</small>
                                            </div>
                                        </th>
                                    @endif
                                @elseif($timeFilter == '7days')
                                    @php
                                        $weekdayHeaders = [];
                                        for($i = 6; $i >= 0; $i--) {
                                            $date = date('Y-m-d', strtotime('-'.$i.' days'));
                                            $dayOfWeek = \Carbon\Carbon::parse($date)->format('w');
                                            if($dayOfWeek != 0 && $dayOfWeek != 6) { // Skip weekends
                                                $weekdayHeaders[] = [
                                                    'date' => $date,
                                                    'day' => date('D', strtotime('-'.$i.' days')),
                                                    'formatted' => date('d/m', strtotime('-'.$i.' days'))
                                                ];
                                            }
                                        }
                                    @endphp
                                    @foreach($weekdayHeaders as $header)
                                        <th class="text-center date-column" width="80px">
                                            <div class="d-flex flex-column">
                                                <span>{{ $header['day'] }}</span>
                                                <small class="text-muted">{{ $header['formatted'] }}</small>
                                            </div>
                                        </th>
                                    @endforeach
                                @elseif($timeFilter == '1month')
                                    @php
                                        // FIXED: Show ALL weekdays in month, not just every 5th
                                        $monthHeaders = [];
                                        for($i = 30; $i >= 0; $i--) {
                                            $date = date('Y-m-d', strtotime('-'.$i.' days'));
                                            $dayOfWeek = \Carbon\Carbon::parse($date)->format('w');
                                            if($dayOfWeek != 0 && $dayOfWeek != 6) { // Skip weekends
                                                $monthHeaders[] = [
                                                    'date' => $date,
                                                    'day' => date('d', strtotime('-'.$i.' days')),
                                                    'month' => date('M', strtotime('-'.$i.' days')),
                                                    'full_date' => date('d/m', strtotime('-'.$i.' days'))
                                                ];
                                            }
                                        }
                                    @endphp
                                    @foreach($monthHeaders as $header)
                                        <th class="text-center date-column" width="70px">
                                            <div class="d-flex flex-column">
                                                <span>{{ $header['day'] }}</span>
                                                <small class="text-muted">{{ $header['month'] }}</small>
                                            </div>
                                        </th>
                                    @endforeach
                                @endif
                                <th class="frozen-summary text-center" width="120px">{{__('Period Summary')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($branchData['users'] as $userIndex => $userData)
                                @php
                                    $user = $userData['user'];
                                    $combinedActivity = $userData['combined_activity'] ?? [];
                                    $projects = $userData['projects'] ?? [];
                                @endphp
                                <tr class="align-middle">
                                    <td class="frozen-user border-end">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                @if(!empty($user->avatar))
                                                    <img src="{{ asset('/storage/uploads/avatar/'.$user->avatar) }}" 
                                                         class="rounded-circle" width="32" height="32" alt="Avatar">
                                                @else
                                                    <span class="avatar-initial rounded-circle bg-primary text-white small">
                                                        {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 min-width-0">
                                                <div class="fw-medium text-truncate" title="{{ $user->name ?? 'Unknown User' }}">
                                                    {{ Str::limit($user->name ?? 'Unknown User', 20) }}
                                                </div>
                                                <small class="text-muted">{{ ucfirst($user->type ?? 'unknown') }}</small>
                                                {{-- CLICKABLE PROJECT COUNT --}}
                                                <div class="small text-info">
                                                    <a href="#" class="text-decoration-none" 
                                                       data-bs-toggle="collapse" 
                                                       data-bs-target="#projects-{{ $branchName }}-{{ $userIndex }}" 
                                                       aria-expanded="false">
                                                        <i class="ti ti-chevron-right me-1 collapse-icon"></i>
                                                        {{ count($projects) }} {{__('project(s)')}}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- COLLAPSIBLE PROJECT LIST --}}
                                        <div class="collapse mt-2" id="projects-{{ $branchName }}-{{ $userIndex }}">
                                            <div class="card card-body p-2">
                                                <small class="fw-medium mb-1">{{__('Projects')}}:</small>
                                                @foreach($projects as $project)
                                                    <small class="text-muted d-block">
                                                        • {{ $project['project_name'] ?? 'Unknown Project' }}
                                                    </small>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Activity columns - scrollable --}}
                                    @if($timeFilter == 'today')
                                        @php
                                            $todayDate = date('Y-m-d');
                                            $todayDayOfWeek = \Carbon\Carbon::parse($todayDate)->format('w');
                                            $isWeekend = ($todayDayOfWeek == 0 || $todayDayOfWeek == 6);
                                            $todayActivity = isset($combinedActivity[$todayDate]) ? $combinedActivity[$todayDate] : null;
                                        @endphp
                                        <td class="text-center border-end date-column">
                                            @if($isWeekend)
                                                <span class="text-muted">
                                                    <i class="ti ti-calendar-off"></i>
                                                    <small class="d-block">{{__('Weekend')}}</small>
                                                </span>
                                            @elseif($todayActivity)
                                                @switch($todayActivity['status'])
                                                    @case('active')
                                                        @php $hours = $todayActivity['work_hours'] ?? '00:00:00'; @endphp
                                                        <div class="d-flex flex-column align-items-center">
                                                            <span class="badge bg-success mb-1" title="Work Hours: {{$hours}}">
                                                                <i class="ti ti-check"></i> {{__('Active')}}
                                                            </span>
                                                            <small class="text-success fw-medium">{{$hours}}</small>
                                                        </div>
                                                        @break
                                                    @case('no_tracker')
                                                        <span class="badge bg-warning" title="Present but no tracker">
                                                            <i class="ti ti-clock-off"></i> {{__('No Tracker')}}
                                                        </span>
                                                        @break
                                                    @case('absent')
                                                        <span class="badge bg-danger" title="Absent">
                                                            <i class="ti ti-x"></i> {{__('Absent')}}
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="text-muted">
                                                            <i class="ti ti-minus"></i>
                                                        </span>
                                                @endswitch
                                            @else
                                                <span class="badge bg-danger" title="No attendance record">
                                                    <i class="ti ti-x"></i> {{__('Absent')}}
                                                </span>
                                            @endif
                                        </td>
                                    @elseif($timeFilter == '7days')
                                        @foreach($weekdayHeaders as $header)
                                            @php
                                                $dayActivity = isset($combinedActivity[$header['date']]) ? $combinedActivity[$header['date']] : null;
                                            @endphp
                                            <td class="text-center border-end date-column">
                                                @if($dayActivity)
                                                    @switch($dayActivity['status'])
                                                        @case('active')
                                                            <i class="ti ti-circle-check text-success fs-5" 
                                                               title="Active - {{$dayActivity['work_hours'] ?? '00:00:00'}}" 
                                                               data-bs-toggle="tooltip"></i>
                                                            @break
                                                        @case('no_tracker')
                                                            <i class="ti ti-clock-off text-warning fs-5" 
                                                               title="Present but no tracker" 
                                                               data-bs-toggle="tooltip"></i>
                                                            @break
                                                        @case('absent')
                                                            <i class="ti ti-circle-x text-danger fs-5" 
                                                               title="Absent" 
                                                               data-bs-toggle="tooltip"></i>
                                                            @break
                                                        @default
                                                            <i class="ti ti-minus text-muted" 
                                                               title="No data" 
                                                               data-bs-toggle="tooltip"></i>
                                                    @endswitch
                                                @else
                                                    <i class="ti ti-circle-x text-danger fs-5" 
                                                       title="Absent - No attendance record" 
                                                       data-bs-toggle="tooltip"></i>
                                                @endif
                                            </td>
                                        @endforeach
                                    @elseif($timeFilter == '1month')
                                        @foreach($monthHeaders as $header)
                                            @php
                                                $dayActivity = isset($combinedActivity[$header['date']]) ? $combinedActivity[$header['date']] : null;
                                            @endphp
                                            <td class="text-center border-end date-column">
                                                @if($dayActivity)
                                                    @switch($dayActivity['status'])
                                                        @case('active')
                                                            <i class="ti ti-circle-check text-success" 
                                                               title="{{$header['date']}} - Active ({{$dayActivity['work_hours'] ?? '00:00:00'}})" 
                                                               data-bs-toggle="tooltip"></i>
                                                            @break
                                                        @case('no_tracker')
                                                            <i class="ti ti-clock-off text-warning" 
                                                               title="{{$header['date']}} - Present but no tracker" 
                                                               data-bs-toggle="tooltip"></i>
                                                            @break
                                                        @case('absent')
                                                            <i class="ti ti-circle-x text-danger" 
                                                               title="{{$header['date']}} - Absent" 
                                                               data-bs-toggle="tooltip"></i>
                                                            @break
                                                        @default
                                                            <i class="ti ti-minus text-muted" 
                                                               title="{{$header['date']}} - No data" 
                                                               data-bs-toggle="tooltip"></i>
                                                    @endswitch
                                                @else
                                                    <i class="ti ti-circle-x text-danger" 
                                                       title="{{$header['date']}} - Absent" 
                                                       data-bs-toggle="tooltip"></i>
                                                @endif
                                            </td>
                                        @endforeach
                                    @endif

                                    {{-- FROZEN: Period Summary --}}
                                    <td class="text-center frozen-summary">
                                        @php
                                            $periodSummary = ['active' => 0, 'no_tracker' => 0, 'absent' => 0, 'no_data' => 0];
                                            
                                            if(isset($combinedActivity) && is_array($combinedActivity)) {
                                                foreach($combinedActivity as $day) {
                                                    if(isset($day['status']) && isset($periodSummary[$day['status']])) {
                                                        $periodSummary[$day['status']]++;
                                                    }
                                                }
                                            }
                                            
                                            $total = array_sum($periodSummary);
                                            $activePercentage = $total > 0 ? round(($periodSummary['active'] / $total) * 100) : 0;
                                        @endphp
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="d-flex gap-1 mb-1">
                                                <span class="badge bg-success-subtle text-success small" title="Active days in period">
                                                    {{ $periodSummary['active'] }}
                                                </span>
                                                <span class="badge bg-warning-subtle text-warning small" title="No tracker days in period">
                                                    {{ $periodSummary['no_tracker'] }}
                                                </span>
                                                <span class="badge bg-danger-subtle text-danger small" title="Absent days in period">
                                                    {{ $periodSummary['absent'] }}
                                                </span>
                                            </div>
                                            @if($total > 0)
                                                <div class="progress" style="width: 60px; height: 4px;">
                                                    <div class="progress-bar bg-success" 
                                                         style="width: {{ $activePercentage }}%" 
                                                         title="{{$activePercentage}}% active in period"></div>
                                                </div>
                                                <small class="text-muted mt-1">{{$activePercentage}}%</small>
                                            @endif
                                            <small class="text-muted">{{__('of')}} {{ $total }} {{__('days')}}</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endforeach

    {{-- Compact Legend --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-light border-0 mb-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="d-flex flex-wrap align-items-center">
                        <strong class="me-3 text-dark">{{__('Legend')}}:</strong>
                        <div class="d-flex flex-wrap gap-3">
                            <span class="d-flex align-items-center">
                                <i class="ti ti-circle-check text-success me-1"></i>
                                <small>{{__('Active (Has timesheet/tracker)')}}</small>
                            </span>
                            <span class="d-flex align-items-center">
                                <i class="ti ti-clock-off text-warning me-1"></i>
                                <small>{{__('No Tracker (Present but no activity)')}}</small>
                            </span>
                            <span class="d-flex align-items-center">
                                <i class="ti ti-circle-x text-danger me-1"></i>
                                <small>{{__('Absent (No attendance record)')}}</small>
                            </span>
                            <span class="d-flex align-items-center">
                                <i class="ti ti-minus text-muted me-1"></i>
                                <small>{{__('No Data')}}</small>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <small class="text-muted">
                            @if(isset($safeSummary['is_weekend']) && $safeSummary['is_weekend'])
                                <span class="text-info fw-medium">{{__('Today is weekend - No activity tracking')}}</span>
                            @else
                                {{__('Today Summary')}}: 
                                <span class="text-success fw-medium">{{ getSafeValue($safeSummary, 'active_today') }} {{__('Active')}}</span>,
                                <span class="text-warning fw-medium">{{ getSafeValue($safeSummary, 'no_tracker_today') }} {{__('No Tracker')}}</span>,
                                <span class="text-danger fw-medium">{{ getSafeValue($safeSummary, 'absent_today') }} {{__('Absent')}}</span>
                                @if(!$isAdminUser)
                                    <br><small class="text-primary">{{__('(From your branch only)')}}</small>
                                @endif
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="text-center p-5">
        <div class="mb-4">
            <i class="ti ti-folder-off text-muted" style="font-size: 4rem;"></i>
        </div>
        @if(!$isAdminUser)
            <h5 class="text-muted mb-3">{{__('No Active Projects in Your Branch')}}</h5>
            <div class="text-muted mb-4">
                <p>{{__('No active users found working on projects in your branch during the selected period.')}}</p>
                <small class="d-block">
                    <strong>{{__('Note')}}:</strong> {{__('You can only view activity data from your assigned branch.')}}
                    <br>{{__('Only showing active users who have timesheet or tracker activity on weekdays.')}}
                    <br>{{__('Weekend activities are not tracked.')}}
                </small>
            </div>
        @else
            <h5 class="text-muted mb-3">{{__('No Active Projects Found')}}</h5>
            <div class="text-muted mb-4">
                <p>{{__('No active users found working on projects during the selected period.')}}</p>
                <small class="d-block">
                    <strong>{{__('Note')}}:</strong> {{__('Only showing active users who have timesheet or tracker activity on weekdays.')}}
                    <br>{{__('Admin and Company users are excluded from this report.')}}
                    <br>{{__('Weekend activities are not tracked.')}}
                </small>
                @if($timeFilter != 'today')
                    <small class="d-block mt-2">{{__('Try selecting a different time period or branch.')}}</small>
                @endif
            </div>
        @endif
        
        <div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                <i class="ti ti-refresh"></i> {{__('Refresh Data')}}
            </button>
            @if($timeFilter != 'today')
                <button type="button" class="btn btn-primary" onclick="filterToday()">
                    <i class="ti ti-calendar"></i> {{__('View Today')}}
                </button>
            @endif
            @if($isAdminUser)
                <button type="button" class="btn btn-outline-secondary" onclick="showAllBranches()">
                    <i class="ti ti-building"></i> {{__('All Branches')}}
                </button>
            @endif
        </div>
    </div>
@endif

{{-- Enhanced CSS for horizontal scroll and frozen columns (same as before) --}}
@push('css-page')
<style>
.table-container {
    position: relative;
    overflow-x: auto;
    overflow-y: visible;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.activity-table {
    margin-bottom: 0;
    min-width: 100%;
}

/* Frozen User Column */
.frozen-user {
    position: sticky;
    left: 0;
    z-index: 10;
    background: white !important;
    border-right: 2px solid #dee2e6 !important;
    box-shadow: 2px 0 4px rgba(0,0,0,0.1);
}

/* Frozen Summary Column */
.frozen-summary {
    position: sticky;
    right: 0;
    z-index: 10;
    background: white !important;
    border-left: 2px solid #dee2e6 !important;
    box-shadow: -2px 0 4px rgba(0,0,0,0.1);
}

/* Scrollable Date Columns */
.date-column {
    min-width: 80px;
    white-space: nowrap;
}

/* Header styling */
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 15;
    background-color: #f8f9fa;
}

.frozen-user.sticky-top,
.frozen-summary.sticky-top {
    z-index: 20;
}

/* Table styling */
.table-sm td, .table-sm th {
    padding: 0.375rem 0.5rem;
    vertical-align: middle;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    font-size: 12px;
    font-weight: 600;
}

.progress {
    border-radius: 2px;
}

.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-danger-subtle {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

/* Collapse icon rotation */
.collapse-icon {
    transition: transform 0.2s ease;
}

[aria-expanded="true"] .collapse-icon {
    transform: rotate(90deg);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .frozen-user {
        min-width: 180px;
    }
    
    .frozen-summary {
        min-width: 120px;
    }
    
    .date-column {
        min-width: 70px;
    }
}

/* Scrollbar styling */
.table-container::-webkit-scrollbar {
    height: 8px;
}

.table-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}
</style>
@endpush

{{-- JavaScript for enhanced functionality --}}
@push('script-page')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Handle collapse icon rotation
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function(element) {
            element.addEventListener('click', function() {
                const icon = this.querySelector('.collapse-icon');
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
            });
        });
        
        // Filter to today function (only for admin users)
        @if(Auth::user()->type === 'admin' || Auth::user()->type === 'company')
        window.filterToday = function() {
            $('#time_filter a[data-value="today"]').click();
        };
        
        // Show all branches function (only for admin users)
        window.showAllBranches = function() {
            $('#branch_filter a[data-value="all"]').click();
        };
        @else
        window.filterToday = function() {
            $('#time_filter a[data-value="today"]').click();
        };
        @endif
    });
</script>
@endpush