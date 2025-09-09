@extends('layouts.admin')

@section('page-title')
    {{ __('Notifications') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Notifications') }}</li>
@endsection

@section('action-button')
    <div class="float-end">
        <button type="button" class="btn btn-sm btn-primary" onclick="markAllAsRead()">
            <i class="fa fa-check-double"></i> {{ __('Mark All as Read') }}
        </button>
        <button type="button" class="btn btn-sm btn-danger" onclick="deleteSelected()">
            <i class="fa fa-trash"></i> {{ __('Delete Selected') }}
        </button>
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Statistics Cards -->
        <div class="col-xl-3 col-6">
            <div class="card comp-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-20">{{ __('Total Notifications') }}</h6>
                            <h3 class="text-primary">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell bg-primary-light"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-6">
            <div class="card comp-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-20">{{ __('Unread') }}</h6>
                            <h3 class="text-warning">{{ $stats['unread'] }}</h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle bg-warning-light"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-6">
            <div class="card comp-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-20">{{ __('Today') }}</h6>
                            <h3 class="text-info">{{ $stats['today'] }}</h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day bg-info-light"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-6">
            <div class="card comp-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-20">{{ __('This Week') }}</h6>
                            <h3 class="text-success">{{ $stats['this_week'] }}</h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week bg-success-light"></i>
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
                    <h5>{{ __('My Notifications') }}</h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('notifications.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Type') }}</label>
                                <select name="type_filter" class="form-control">
                                    <option value="">{{ __('All Types') }}</option>
                                    @foreach($notificationTypes as $type)
                                        <option value="{{ $type['value'] }}" {{ request('type_filter') == $type['value'] ? 'selected' : '' }}>
                                            {{ $type['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status_filter" class="form-control">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="unread" {{ request('status_filter') == 'unread' ? 'selected' : '' }}>{{ __('Unread') }}</option>
                                    <option value="read" {{ request('status_filter') == 'read' ? 'selected' : '' }}>{{ __('Read') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('From Date') }}</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('To Date') }}</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input type="text" name="search" class="form-control" placeholder="{{ __('Search notifications...') }}" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                            </div>
                        </div>
                    </form>

                    <!-- Bulk Actions -->
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <input type="checkbox" id="selectAll" class="form-check-input">
                            <label for="selectAll" class="form-check-label">{{ __('Select All') }}</label>
                        </div>
                        <div>
                            <span class="text-muted">{{ __('Showing') }} {{ $notifications->firstItem() ?? 0 }} {{ __('to') }} {{ $notifications->lastItem() ?? 0 }} {{ __('of') }} {{ $notifications->total() }} {{ __('notifications') }}</span>
                        </div>
                    </div>

                    <!-- Notifications List -->
                    @if($notifications->count() > 0)
                        <div class="list-group" id="notificationsList">
                            @foreach($notifications as $notification)
                                <div class="notification-item {{ !$notification->is_read ? 'notification-unread' : '' }}" data-notification-id="{{ $notification->id }}">
                                    <div class="d-flex align-items-start">
                                        <input type="checkbox" class="form-check-input notification-checkbox me-2" value="{{ $notification->id }}">
                                        <div class="flex-grow-1">
                                            {!! $notification->toHtml() !!}
                                        </div>
                                        <div class="dropdown ms-2">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if(!$notification->is_read)
                                                    <li><a class="dropdown-item" href="#" onclick="markAsRead([{{ $notification->id }}])">
                                                        <i class="fa fa-check"></i> {{ __('Mark as Read') }}
                                                    </a></li>
                                                @endif
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteNotification([{{ $notification->id }}])">
                                                    <i class="fa fa-trash"></i> {{ __('Delete') }}
                                                </a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No notifications found') }}</h5>
                            <p class="text-muted">{{ __('You have no notifications matching the current filters.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    // Select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.notification-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Mark all as read
    function markAllAsRead() {
        if (confirm('{{ __("Are you sure you want to mark all notifications as read?") }}')) {
            fetch('{{ route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('{{ __("Error marking notifications as read") }}');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("Error marking notifications as read") }}');
            });
        }
    }

    // Mark selected as read
    function markAsRead(notificationIds = null) {
        let ids = notificationIds;
        
        if (!ids) {
            ids = [];
            const checkboxes = document.querySelectorAll('.notification-checkbox:checked');
            checkboxes.forEach(checkbox => {
                ids.push(parseInt(checkbox.value));
            });
        }

        if (ids.length === 0) {
            alert('{{ __("Please select notifications to mark as read") }}');
            return;
        }

        fetch('{{ route("notifications.mark-read") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                notification_ids: ids
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove unread styling
                ids.forEach(id => {
                    const item = document.querySelector(`[data-notification-id="${id}"]`);
                    if (item) {
                        item.classList.remove('notification-unread');
                    }
                });
                
                // Update counter in header if exists
                updateNotificationCounter();
            } else {
                alert('{{ __("Error marking notifications as read") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("Error marking notifications as read") }}');
        });
    }

    // Delete selected notifications
    function deleteSelected() {
        const ids = [];
        const checkboxes = document.querySelectorAll('.notification-checkbox:checked');
        checkboxes.forEach(checkbox => {
            ids.push(parseInt(checkbox.value));
        });

        if (ids.length === 0) {
            alert('{{ __("Please select notifications to delete") }}');
            return;
        }

        deleteNotification(ids);
    }

    // Delete notifications
    function deleteNotification(notificationIds) {
        if (confirm('{{ __("Are you sure you want to delete the selected notifications?") }}')) {
            fetch('{{ route("notifications.delete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    notification_ids: notificationIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove deleted notifications from DOM
                    notificationIds.forEach(id => {
                        const item = document.querySelector(`[data-notification-id="${id}"]`);
                        if (item) {
                            item.remove();
                        }
                    });
                    
                    // Update counter
                    updateNotificationCounter();
                    
                    // Reload if no notifications left
                    if (document.querySelectorAll('.notification-item').length === 0) {
                        location.reload();
                    }
                } else {
                    alert('{{ __("Error deleting notifications") }}');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("Error deleting notifications") }}');
            });
        }
    }

    // Update notification counter in header
    function updateNotificationCounter() {
        fetch('{{ route("notifications.unread-count") }}')
        .then(response => response.json())
        .then(data => {
            const counter = document.querySelector('.custom_messanger_counter');
            if (counter) {
                if (data.count > 0) {
                    counter.textContent = data.count;
                    counter.style.display = 'inline';
                } else {
                    counter.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error updating counter:', error);
        });
    }

    // Auto-refresh notification counter every 30 seconds
    setInterval(updateNotificationCounter, 30000);
</script>
@endpush
@push('css-page')
<style>
.notification-unread {
    background-color: #f8f9fa;
    border-left: 4px solid #007bff;
}

.notification-item {
    padding: 10px;
    border-bottom: 1px solid #dee2e6;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.comp-card {
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.bg-primary-light {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
    padding: 15px;
    border-radius: 50%;
    font-size: 20px;
}

.bg-warning-light {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
    padding: 15px;
    border-radius: 50%;
    font-size: 20px;
}

.bg-info-light {
    background-color: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
    padding: 15px;
    border-radius: 50%;
    font-size: 20px;
}

.bg-success-light {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
    padding: 15px;
    border-radius: 50%;
    font-size: 20px;
}
</style>
@endpush