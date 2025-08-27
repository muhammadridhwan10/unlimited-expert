{{-- resources/views/projects/activity_index.blade.php - Updated with Branch Permissions --}}
@extends('layouts.admin')
@section('page-title')
    {{__('Project Activity Report')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Projects')}}</a></li>
    <li class="breadcrumb-item">{{__('Activity Report')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        {{-- Time Filter --}}
        <div class="btn-group me-2" role="group">
            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="ti ti-calendar"></i>
                @if($timeFilter == 'today') {{__('Today')}}
                @elseif($timeFilter == '7days') {{__('Last 7 Days')}}
                @elseif($timeFilter == '1month') {{__('Last Month')}}
                @endif
            </button>
            <ul class="dropdown-menu" id="time_filter">
                <li><a class="dropdown-item {{ $timeFilter == 'today' ? 'active' : '' }}" href="#" data-value="today">{{__('Today')}}</a></li>
                <li><a class="dropdown-item {{ $timeFilter == '7days' ? 'active' : '' }}" href="#" data-value="7days">{{__('Last 7 Days')}}</a></li>
                <li><a class="dropdown-item {{ $timeFilter == '1month' ? 'active' : '' }}" href="#" data-value="1month">{{__('Last Month')}}</a></li>
            </ul>
        </div>

        {{-- Branch Filter - Only for Admin/Company users --}}
        @if(Auth::user()->type === 'admin' || Auth::user()->type === 'company')
        <div class="btn-group me-2" role="group">
            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="ti ti-building"></i>
                @if($branchFilter == 'all') {{__('All Branches')}}
                @else {{ $branchFilter }}
                @endif
            </button>
            <ul class="dropdown-menu" id="branch_filter">
                <li><a class="dropdown-item {{ $branchFilter == 'all' ? 'active' : '' }}" href="#" data-value="all">{{__('All Branches')}}</a></li>
                <li><a class="dropdown-item {{ $branchFilter == 'PUSAT' ? 'active' : '' }}" href="#" data-value="PUSAT">PUSAT</a></li>
                <li><a class="dropdown-item {{ $branchFilter == 'BEKASI' ? 'active' : '' }}" href="#" data-value="BEKASI">BEKASI</a></li>
                <li><a class="dropdown-item {{ $branchFilter == 'MALANG' ? 'active' : '' }}" href="#" data-value="MALANG">MALANG</a></li>
            </ul>
        </div>
        @else
        {{-- Show current user's branch info for non-admin users --}}
        @php
            $currentUserBranch = \DB::table('users')
                ->join('employees', 'users.id', '=', 'employees.user_id')
                ->join('branches', 'employees.branch_id', '=', 'branches.id')
                ->where('users.id', Auth::id())
                ->select('branches.name')
                ->first();
        @endphp
        <div class="btn btn-sm btn-outline-secondary me-2" disabled>
            <i class="ti ti-building"></i>
            {{__('Your Branch')}}: {{ $currentUserBranch->name ?? 'Unknown' }}
        </div>
        @endif

        {{-- Refresh Button --}}
        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="refreshData()" title="{{__('Refresh Data')}}">
            <i class="ti ti-refresh"></i>
        </button>

        {{-- Export Button --}}
        <button type="button" class="btn btn-sm btn-primary" onclick="exportActivityReport()">
            <i class="ti ti-download"></i> {{__('Export')}}
        </button>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5>{{__('Project Activity Report')}}</h5>
                        <small class="text-muted">
                            {{__('Period')}}: 
                            @if($timeFilter == 'today') {{__('Today')}} ({{ date('d M Y') }})
                            @elseif($timeFilter == '7days') {{__('Last 7 Days')}} ({{ date('d M Y', strtotime('-7 days')) }} - {{ date('d M Y') }})
                            @elseif($timeFilter == '1month') {{__('Last Month')}} ({{ date('d M Y', strtotime('-1 month')) }} - {{ date('d M Y') }})
                            @endif
                            
                            {{-- Show branch restriction info for non-admin users --}}
                            @if(Auth::user()->type !== 'admin' && Auth::user()->type !== 'company')
                                <br><small class="text-info">
                                    <i class="ti ti-info-circle"></i>
                                    {{__('Showing data from your branch only')}}
                                </small>
                            @endif
                        </small>
                    </div>
                    
                    @if(isset($pagination))
                    <div class="d-flex align-items-center">
                        <small class="text-muted me-3">
                            {{__('Showing')}} {{ (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 }} - 
                            {{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }} 
                            {{__('of')}} {{ $pagination['total'] }} {{__('entries')}}
                        </small>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    {{-- Branch Access Warning for Users without Branch --}}
                    @php
                        $currentUserBranch = \DB::table('users')
                            ->join('employees', 'users.id', '=', 'employees.user_id')
                            ->join('branches', 'employees.branch_id', '=', 'branches.id')
                            ->where('users.id', Auth::id())
                            ->first();
                    @endphp
                    
                    @if(Auth::user()->type !== 'admin' && Auth::user()->type !== 'company' && !$currentUserBranch)
                        <div class="alert alert-warning">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-alert-triangle me-2"></i>
                                <div>
                                    <strong>{{__('Branch Access Required')}}</strong>
                                    <p class="mb-0">{{__('Your account is not assigned to any branch. Please contact the administrator to assign you to a branch to view activity reports.')}}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Loading Indicator --}}
                        <div id="loading_indicator" class="text-center p-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">{{__('Loading...')}}</span>
                            </div>
                            <p class="mt-2">{{__('Loading activity data...')}}</p>
                        </div>

                        {{-- Content Area --}}
                        <div id="activity_content">
                            @include('projects.activity_filter')
                        </div>

                        {{-- Pagination --}}
                        @if(isset($pagination) && $pagination['last_page'] > 1)
                        <div class="d-flex justify-content-center mt-4">
                            <nav>
                                <ul class="pagination pagination-sm">
                                    {{-- Previous Page --}}
                                    @if($pagination['current_page'] > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="#" onclick="loadPage({{ $pagination['current_page'] - 1 }})">
                                            <i class="ti ti-chevron-left"></i>
                                        </a>
                                    </li>
                                    @endif

                                    {{-- Page Numbers --}}
                                    @for($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++)
                                    <li class="page-item {{ $i == $pagination['current_page'] ? 'active' : '' }}">
                                        <a class="page-link" href="#" onclick="loadPage({{ $i }})">{{ $i }}</a>
                                    </li>
                                    @endfor

                                    {{-- Next Page --}}
                                    @if($pagination['current_page'] < $pagination['last_page'])
                                    <li class="page-item">
                                        <a class="page-link" href="#" onclick="loadPage({{ $pagination['current_page'] + 1 }})">
                                            <i class="ti ti-chevron-right"></i>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
$(document).ready(function() {
    var currentTimeFilter = '{{ $timeFilter }}';
    var currentBranchFilter = '{{ $branchFilter }}'; 
    var currentPage = {{ $pagination['current_page'] ?? 1 }};
    var userType = '{{ Auth::user()->type }}';
    var isAdminUser = (userType === 'admin' || userType === 'company');

    // Time filter change
    $('#time_filter').on('click', 'a', function(e) {
        e.preventDefault();
        currentTimeFilter = $(this).data('value');
        currentPage = 1; // Reset to first page
        $('#time_filter a').removeClass('active');
        $(this).addClass('active');
        loadActivityData();
    });

    // Branch filter change - only for admin/company users
    if(isAdminUser) {
        $('#branch_filter').on('click', 'a', function(e) {
            e.preventDefault();
            currentBranchFilter = $(this).data('value');
            currentPage = 1; // Reset to first page
            $('#branch_filter a').removeClass('active');
            $(this).addClass('active');
            loadActivityData();
        });
    } else {
        // For non-admin users, force branch filter to 'all' (will be restricted by backend)
        currentBranchFilter = 'all';
    }

    function loadActivityData() {
        showLoading();
        
        var requestData = {
            time_filter: currentTimeFilter,
            page: currentPage
        };

        // Only send branch filter for admin/company users
        if(isAdminUser) {
            requestData.branch_filter = currentBranchFilter;
        }
        
        $.ajax({
            url: '{{ route("projects.activity.filter") }}',
            method: 'GET',
            data: requestData,
            timeout: 30000, // 30 second timeout
            success: function(response) {
                hideLoading();
                if(response.success) {
                    $('#activity_content').html(response.html);
                    updatePagination(response.pagination);
                    
                    // Update URL without reload
                    var newUrl = new URL(window.location);
                    newUrl.searchParams.set('time_filter', currentTimeFilter);
                    if(isAdminUser) {
                        newUrl.searchParams.set('branch_filter', currentBranchFilter);
                    }
                    newUrl.searchParams.set('page', currentPage);
                    window.history.pushState({}, '', newUrl);
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                if(status === 'timeout') {
                    $('#activity_content').html('<div class="alert alert-warning">{{__("Request timed out. Please try again with a shorter time period.")}}</div>');
                } else {
                    $('#activity_content').html('<div class="alert alert-danger">{{__("Error loading data. Please try again.")}}</div>');
                }
                console.error('AJAX Error:', error);
            }
        });
    }

    // Fixed pagination function
    window.loadPage = function(page) {
        currentPage = parseInt(page);
        loadActivityData();
    };

    // Refresh data function
    window.refreshData = function() {
        loadActivityData();
    };

    function showLoading() {
        $('#loading_indicator').show();
        $('#activity_content').hide();
    }

    function hideLoading() {
        $('#loading_indicator').hide();
        $('#activity_content').show();
    }

    function updatePagination(paginationData) {
        if(paginationData && paginationData.last_page > 1) {
            // Generate new pagination HTML
            var paginationHtml = '<nav><ul class="pagination pagination-sm">';
            
            // Previous button
            if(paginationData.current_page > 1) {
                paginationHtml += '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(' + (paginationData.current_page - 1) + ')"><i class="ti ti-chevron-left"></i></a></li>';
            }
            
            // Page numbers
            var start = Math.max(1, paginationData.current_page - 2);
            var end = Math.min(paginationData.last_page, paginationData.current_page + 2);
            
            for(var i = start; i <= end; i++) {
                var activeClass = i == paginationData.current_page ? 'active' : '';
                paginationHtml += '<li class="page-item ' + activeClass + '"><a class="page-link" href="#" onclick="loadPage(' + i + ')">' + i + '</a></li>';
            }
            
            // Next button
            if(paginationData.current_page < paginationData.last_page) {
                paginationHtml += '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(' + (paginationData.current_page + 1) + ')"><i class="ti ti-chevron-right"></i></a></li>';
            }
            
            paginationHtml += '</ul></nav>';
            
            // Update pagination if exists
            $('.pagination').parent().html(paginationHtml);
            
            // Update info text
            var infoText = 'Showing ' + paginationData.from + ' - ' + paginationData.to + ' of ' + paginationData.total + ' entries';
            $('.card-header small').text(infoText);
        }
    }
});

function exportActivityReport() {
    var params = new URLSearchParams({
        time_filter: '{{ $timeFilter }}',
        export: 'excel'
    });
    
    // Only add branch filter for admin/company users
    @if(Auth::user()->type === 'admin' || Auth::user()->type === 'company')
    params.set('branch_filter', '{{ $branchFilter }}');
    @endif
    
    window.location.href = '{{ route("projects.activity.export") }}?' + params.toString();
}

// Helper functions for non-admin users
window.filterToday = function() {
    $('#time_filter a[data-value="today"]').click();
};
</script>
@endpush