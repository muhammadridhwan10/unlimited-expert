@php
    $users=\Auth::user();
    $profile=asset(Storage::url('uploads/avatar/'));
    $languages=\App\Models\Utility::languages();
    $lang = isset($users->lang)?$users->lang:'en';
    $setting = \App\Models\Utility::colorset();
    $mode_setting = \App\Models\Utility::mode_layout();
    $notifications = \App\Models\Notification::where('user_id', auth()->user()->id)->orderBy('created_at', 'desc')->limit(10)->get();
    $unreadNotifications = \App\Models\Notification::where('user_id', auth()->user()->id)->where('is_read', false)->count();

    $unseenCounter=App\Models\ChMessage::where('to_id', Auth::user()->id)->where('seen', 0)->count();
@endphp

@if (isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on')
    <header class="dash-header transprent-bg">
        @else
            <header class="dash-header">
                @endif

    <div class="header-wrapper">
        <div class="me-auto dash-mob-drp">
            <ul class="list-unstyled">
                <li class="dash-h-item mob-hamburger">
                    <a href="#!" class="dash-head-link" id="mobile-collapse">
                        <div class="hamburger hamburger--arrowturn">
                            <div class="hamburger-box">
                                <div class="hamburger-inner"></div>
                            </div>
                        </div>
                    </a>
                </li>

                <li class="dropdown dash-h-item drp-company">
                    <a
                        class="dash-head-link dropdown-toggle arrow-none me-0"
                        data-bs-toggle="dropdown"
                        href="#"
                        role="button"
                        aria-haspopup="false"
                        aria-expanded="false"
                    >
                        <span class="card-avtar">
                             <img src="{{(!empty(\Auth::user()->avatar))? asset(Storage::url("uploads/avatar/".\Auth::user()->avatar)): asset(Storage::url(\Auth::user()->name . ".jpg"))}}" class="wid-80" style="width: 40px; height: 40px; object-fit: cover; object-position: center; border-radius: 50%;">
                           </span>
                        <span class="hide-mob ms-2">{{__('Hi, ')}}{{\Auth::user()->name }}!</span>
                        <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        <a href="{{route('profile')}}" class="dropdown-item">
                            <i class="ti ti-user"></i>
                            <span>{{__('Profile')}}</span>
                        </a>

                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();" class="dropdown-item">
                            <i class="ti ti-power"></i>
                            <span>{{__('Logout')}}</span>
                        </a>
                        <form id="frm-logout" action="{{ route('logout') }}" method="POST" class="d-none">
                            {{ csrf_field() }}
                        </form>
                    </div>
                </li>
            </ul>
        </div>
        <div class="ms-auto">
            <ul class="list-unstyled">
                @if((\Auth::user()->type == 'company'))
                    <li class="dropdown dash-h-item drp-language">
                        <a class="dash-head-link dropdown-toggle arrow-none me-0"
                        data-bs-toggle="dropdown"
                        href="#"
                        role="button"
                        aria-haspopup="false"
                        aria-expanded="false"
                        >
                            <i class="ti ti-settings"></i>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                            @if(Gate::check('manage company settings'))
                                <a href="{{ route('company.setting') }}" class="dropdown-item">{{__('System Settings')}}</a>
                            @endif
                        </div>
                    </li>
                @endif

                <!-- Enhanced Notifications Dropdown -->
                <li class="dropdown dash-h-item drp-notifications">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="notificationDropdown">
                        <i class="fa fa-bell"></i>
                        @if ($unreadNotifications > 0)
                            <span class="bg-danger dash-h-badge message-toggle-msg message-counter custom_messanger_counter beep">{{ $unreadNotifications }}</span>
                        @endif
                    </a>

                    <div class="dropdown-menu dash-h-dropdown dropdown-menu-end notification-dropdown" style="width: 450px; max-height: 500px; overflow-y: auto;">
                        <div class="dropdown-header d-flex justify-content-between align-items-center p-3 border-bottom">
                            <h6 class="mb-0">{{ __('Notifications') }}</h6>
                            <div>
                                @if($unreadNotifications > 0)
                                    <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="markAllNotificationsAsRead()">
                                        {{ __('Mark All Read') }}
                                    </button>
                                @endif
                                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-primary">
                                    {{ __('View All') }}
                                </a>
                            </div>
                        </div>

                        <div id="notificationsList">
                            @forelse ($notifications as $notification)
                                <div class="notification-item {{ !$notification->is_read ? 'notification-unread' : '' }}" data-notification-id="{{ $notification->id }}">
                                    <a href="{{ route('notifications.show', $notification->id) }}" class="dropdown-item p-3 border-bottom notification-link">
                                        <div class="d-flex align-items-center">
                                            <div class="notification-icon me-3">
                                                @php
                                                    $iconClass = 'fa fa-bell';
                                                    $iconColor = 'bg-primary';
                                                    
                                                    switch($notification->type) {
                                                        case 'create_project':
                                                            $iconClass = 'fa fa-plus';
                                                            $iconColor = 'bg-primary';
                                                            break;
                                                        case 'create_overtime':
                                                            $iconClass = 'fa fa-clock';
                                                            $iconColor = 'bg-warning';
                                                            break;
                                                        case 'create_medical_allowance':
                                                            $iconClass = 'fa fa-medical-bag';
                                                            $iconColor = 'bg-success';
                                                            break;
                                                        case 'comment_ticketing':
                                                            $iconClass = 'fa fa-comments';
                                                            $iconColor = 'bg-info';
                                                            break;
                                                        case 'new_announcement':
                                                            $iconClass = 'fa fa-bullhorn';
                                                            $iconColor = 'bg-warning';
                                                            break;
                                                        case 'reimbursement_submitted':
                                                            $iconClass = 'fa fa-money-bill';
                                                            $iconColor = 'bg-info';
                                                            break;
                                                        case 'reimbursement_approved':
                                                            $iconClass = 'fa fa-check-circle';
                                                            $iconColor = 'bg-success';
                                                            break;
                                                        case 'reimbursement_rejected':
                                                            $iconClass = 'fa fa-times-circle';
                                                            $iconColor = 'bg-danger';
                                                            break;
                                                        case 'document_submitted':
                                                            $iconClass = 'fa fa-file-alt';
                                                            $iconColor = 'bg-primary';
                                                            break;
                                                        case 'document_approved':
                                                            $iconClass = 'fa fa-check-circle';
                                                            $iconColor = 'bg-success';
                                                            break;
                                                        case 'document_rejected':
                                                            $iconClass = 'fa fa-times-circle';
                                                            $iconColor = 'bg-danger';
                                                            break;
                                                        case 'document_revision_required':
                                                            $iconClass = 'fa fa-edit';
                                                            $iconColor = 'bg-warning';
                                                            break;
                                                        case 'document_under_review':
                                                            $iconClass = 'fa fa-search';
                                                            $iconColor = 'bg-info';
                                                            break;
                                                        case 'document_comment':
                                                            $iconClass = 'fa fa-comments';
                                                            $iconColor = 'bg-info';
                                                            break;
                                                    }
                                                @endphp
                                                <span class="avatar {{ $iconColor }} text-white rounded-circle">
                                                    <i class="{{ $iconClass }} mx-auto"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="notification-content">
                                                    @php
                                                        $data = $notification->data;
                                                        $text = '';
                                                        $usr = null;
                                                        
                                                        if (isset($data['updated_by']) && !empty($data['updated_by'])) {
                                                            $usr = \App\Models\User::find($data['updated_by']);
                                                        }
                                                        
                                                        switch($notification->type) {
                                                            case 'create_project':
                                                                $text = ($usr ? $usr->name . " " : "") . __('created a new project') . " " . ($data['name'] ?? '');
                                                                break;
                                                            case 'create_overtime':
                                                                $text = ($usr ? $usr->name . " " : "") . __('created overtime');
                                                                break;
                                                            case 'create_medical_allowance':
                                                                $text = ($usr ? $usr->name . " " : "") . __('created medical allowance');
                                                                break;
                                                            case 'comment_ticketing':
                                                                $text = ($usr ? $usr->name . " " : "") . __('commented on ticket');
                                                                break;
                                                            case 'new_announcement':
                                                                $text = $data['name'] ?? __('New Announcement');
                                                                break;
                                                            case 'reimbursement_submitted':
                                                                $text = ($usr ? $usr->name . " " : "") . __('submitted reimbursement');
                                                                break;
                                                            case 'reimbursement_approved':
                                                                $text = __('Your reimbursement has been approved');
                                                                break;
                                                            case 'reimbursement_rejected':
                                                                $text = __('Your reimbursement has been rejected');
                                                                break;
                                                            case 'document_submitted':
                                                                $text = ($usr ? $usr->name . " " : "") . __('submitted document for review');
                                                                break;
                                                            case 'document_approved':
                                                                $text = __('Your document has been approved');
                                                                break;
                                                            case 'document_rejected':
                                                                $text = __('Your document has been rejected');
                                                                break;
                                                            case 'document_revision_required':
                                                                $text = __('Revision required for your document');
                                                                break;
                                                            case 'document_under_review':
                                                                $text = __('Your document is now under review');
                                                                break;
                                                            case 'document_comment':
                                                                $text = ($usr ? $usr->name . " " : "") . __('commented on document');
                                                                break;
                                                            default:
                                                                $text = __('New notification');
                                                                break;
                                                        }
                                                    @endphp
                                                    
                                                    <p class="mb-1 notification-text {{ !$notification->is_read ? 'fw-bold' : '' }}">
                                                        {{ Str::limit($text, 60) }}
                                                    </p>
                                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                                    
                                                    @if(!$notification->is_read)
                                                        <span class="badge badge-primary badge-sm ms-2">{{ __('New') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="notification-actions">
                                                @if(!$notification->is_read)
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="markNotificationAsRead({{ $notification->id }}, event)">
                                                        <i class="fa fa-check"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @empty
                                <div class="dropdown-item text-center py-4">
                                    <i class="fa fa-bell-slash fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No notifications') }}</p>
                                </div>
                            @endforelse
                        </div>

                        @if($notifications->count() > 0)
                            <div class="dropdown-footer text-center p-3 border-top">
                                <a href="{{ route('notifications.index') }}" class="text-primary">
                                    {{ __('View all notifications') }} ({{ $unreadNotifications }} {{ __('unread') }})
                                </a>
                            </div>
                        @endif
                    </div>
                </li>

                <li class="dropdown dash-h-item drp-language">
                    <a
                        class="dash-head-link dropdown-toggle arrow-none me-0"
                        data-bs-toggle="dropdown"
                        href="#"
                        role="button"
                        aria-haspopup="false"
                        aria-expanded="false"
                    >
                        <i class="ti ti-world nocolor"></i>
                        <span class="drp-text hide-mob">{{Str::upper(isset($lang)?$lang:'en')}}</span>
                        <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                        @foreach($languages as $language)
                            <a href="{{route('change.language',$language)}}" class="dropdown-item @if($language == $lang) text-danger @endif">
                                <span>{{Str::upper($language)}}</span>
                            </a>
                        @endforeach
                        <h></h>
                        <a class="dropdown-item text-primary" href="{{route('manage.language',[isset($lang)?$lang:'en'])}}">{{ __('Manage Language ') }}</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

@push('script-page')
<script>
    // Mark specific notification as read
    function markNotificationAsRead(notificationId, event) {
        event.preventDefault();
        event.stopPropagation();
        
        fetch('{{ route("notifications.mark-read") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                notification_ids: [notificationId]
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove unread styling
                const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationItem) {
                    notificationItem.classList.remove('notification-unread');
                    const badge = notificationItem.querySelector('.badge');
                    if (badge) badge.remove();
                    const boldText = notificationItem.querySelector('.fw-bold');
                    if (boldText) boldText.classList.remove('fw-bold');
                    const markButton = notificationItem.querySelector('.btn-outline-primary');
                    if (markButton) markButton.remove();
                }
                
                // Update counter
                updateNotificationCounter();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    // Mark all notifications as read
    function markAllNotificationsAsRead() {
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
                // Remove all unread styling
                document.querySelectorAll('.notification-unread').forEach(item => {
                    item.classList.remove('notification-unread');
                });
                document.querySelectorAll('.notification-text.fw-bold').forEach(text => {
                    text.classList.remove('fw-bold');
                });
                document.querySelectorAll('.badge').forEach(badge => {
                    if (badge.textContent.includes('New')) badge.remove();
                });
                document.querySelectorAll('.notification-actions .btn-outline-primary').forEach(btn => {
                    btn.remove();
                });
                
                // Update counter
                updateNotificationCounter();
                
                // Hide mark all button
                const markAllBtn = document.querySelector('button[onclick="markAllNotificationsAsRead()"]');
                if (markAllBtn) markAllBtn.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
        });
    }

    // Update notification counter
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
            console.error('Error updating notification counter:', error);
        });
    }

    // Auto-refresh notifications every 60 seconds
    setInterval(function() {
        updateNotificationCounter();
        // You can also refresh the dropdown content here if needed
    }, 60000);

    // Refresh notifications dropdown when opened
    document.getElementById('notificationDropdown').addEventListener('click', function() {
        // Optional: refresh notification list when dropdown is opened
        setTimeout(updateNotificationCounter, 100);
    });
</script>
@endpush


@push('css-page')
<style>
    .notification-dropdown {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 1px solid #e3e6f0;
    }

    .notification-item {
        transition: background-color 0.2s ease;
    }

    .notification-item:hover {
        background-color: #f8f9fc;
    }

    .notification-item.notification-unread {
        background-color: #e7f3ff;
        border-left: 3px solid #007bff;
    }

    .notification-link {
        text-decoration: none;
        color: inherit;
    }

    .notification-link:hover {
        color: inherit;
        text-decoration: none;
    }

    .notification-text {
        font-size: 13px;
        line-height: 1.4;
    }

    .notification-actions {
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .notification-item:hover .notification-actions {
        opacity: 1;
    }

    .custom_messanger_counter {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    .beep {
        background-color: #dc3545 !important;
        color: white;
    }
</style>
@endpush