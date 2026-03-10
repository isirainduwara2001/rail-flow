<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RailFlow - Train Booking System')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <!-- Material Dashboard CSS -->
    <link href="https://cdn.jsdelivr.net/npm/material-dashboard@3.0.0/assets/css/material-dashboard.min.css"
        rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f4f6fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            padding-top: 2rem;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link {
            color: #a8b1d4;
            padding: 0.75rem 1.5rem;
            border-left: 3px solid transparent;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: #e74c3c;
        }

        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(231, 76, 60, 0.2);
            border-left-color: #e74c3c;
            font-weight: 600;
        }

        .sidebar .nav-link i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .navbar {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid #e8ecef;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #1a1a2e !important;
        }

        .navbar-brand i {
            color: #e74c3c;
            margin-right: 0.5rem;
        }

        .main-content {
            padding: 2rem;
            min-height: calc(100vh - 60px);
        }

        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e8ecef;
            font-weight: 600;
            padding: 1.25rem;
        }

        .btn-primary {
            background-color: #e74c3c;
            border-color: #e74c3c;
        }

        .btn-primary:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }

        .table-hover tbody tr:hover {
            background-color: #f0f2f5;
        }

        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 0.25rem;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .badge-success {
            background-color: #27ae60;
        }

        .badge-danger {
            background-color: #e74c3c;
        }

        .badge-warning {
            background-color: #f39c12;
        }

        .badge-info {
            background-color: #3498db;
        }

        /* KPI Cards */
        .kpi-card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            background: #fff;
            transition: all 0.3s ease;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .kpi-card .card-body {
            padding: 1.5rem;
        }

        .kpi-icon {
            width: 60px;
            height: 60px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .kpi-icon.bg-success {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        .kpi-icon.bg-info {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .kpi-icon.bg-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .kpi-icon.bg-primary {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        }

        .kpi-icon.bg-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        /* Card Headers */
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e8ecef;
            font-weight: 600;
            padding: 1.25rem;
        }

        .card-header h5 {
            font-size: 1.1rem;
        }

        .card-header+.table-responsive {
            border-radius: 0 0 0.75rem 0.75rem;
        }

        /* Table Styles */
        .table thead {
            background-color: #f8f9fa;
        }

        .table thead th {
            border: none;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #8f92a6;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            border: none;
            vertical-align: middle;
        }

        .table tbody tr {
            border-bottom: 1px solid #e8ecef;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Badges */
        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 0.25rem;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge.bg-success {
            background-color: #dffce7 !important;
            color: #16a34a !important;
        }

        .badge.bg-warning {
            background-color: #fff3cd !important;
            color: #d97706 !important;
        }

        .badge.bg-secondary {
            background-color: #e8ecef !important;
            color: #6f7c9a !important;
        }

        .badge.bg-info {
            background-color: #d1e7f7 !important;
            color: #1d4ed8 !important;
        }

        /* Timeline */
        .timeline {
            position: relative;
        }

        .timeline-block {
            display: flex;
            position: relative;
        }

        .timeline-step {
            width: 40px;
            height: 40px;
            background-color: #f0f2f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
            color: #8f92a6;
        }

        .timeline-content h6 {
            font-size: 0.9rem;
            color: #1a1a2e;
        }

        .text-xs {
            font-size: 0.75rem !important;
        }

        .fw-5 {
            font-weight: 500 !important;
        }

        .opacity-7 {
            opacity: 0.7;
        }

        /* Main Layout */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .layout-wrapper {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 250px;
            flex-shrink: 0;
        }

        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Responsive Sidebar */
        @media (max-width: 991px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                z-index: 1050;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        @media (min-width: 992px) {
            .sidebar-toggle {
                display: none !important;
            }
        }

        .navbar {
            flex-shrink: 0;
        }

        .main-content {
            flex: 1;
            overflow-y: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .kpi-card .card-body {
                padding: 1rem;
            }

            .kpi-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }

            .main-content {
                padding: 1rem;
            }

            .table {
                font-size: 0.85rem;
            }

            .table th,
            .table td {
                padding: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: 100%;
            }

            .kpi-card {
                margin-bottom: 1rem;
            }

            .col-md-3 {
                min-width: 100%;
            }
        }
    </style>

    @yield('styles')
</head>

<body>
    <div class="layout-wrapper">
        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar Navigation -->
        <nav class="sidebar" id="sidebar">
            <div class="px-3 mb-4">
                <div class="navbar-brand text-center">
                    <i class="fas fa-train"></i>
                </div>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i class="material-icons">dashboard</i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @if (
                        auth()->user()
                                ?->hasAnyRole(['admin', 'staff'])
                    )
                    <li class="nav-item mt-3">
                        <span class="text-white px-3 small">MANAGEMENT</span>
                    </li>

                    @if (auth()->user()?->hasRole('admin'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                                href="{{ route('users.index') }}">
                                <i class="material-icons">people</i>
                                <span>User Management</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                                href="{{ route('roles.index') }}">
                                <i class="material-icons">security</i>
                                <span>Role Management</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('trains.*') ? 'active' : '' }}"
                                href="{{ route('trains.index') }}">
                                <i class="material-icons">train</i>
                                <span>Trains</span>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('schedules.*') ? 'active' : '' }}"
                            href="{{ route('schedules.index') }}">
                            <i class="material-icons">schedule</i>
                            <span>Schedules</span>
                        </a>
                    </li>

                    @can('notifications.send')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('passenger-informs.*') ? 'active' : '' }}"
                                href="{{ route('passenger-informs.index') }}">
                                <i class="material-icons">notifications_active</i>
                                <span>Passenger Informs</span>
                            </a>
                        </li>
                    @endcan

                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('admin/iot-dashboard*') ? 'active' : '' }}"
                            href="{{ route('admin.iot-dashboard') }}">
                            <i class="material-icons">query_stats</i>
                            <span class="nav-link-text ms-1">IoT Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('admin/history*') ? 'active' : '' }}"
                            href="{{ route('admin.history') }}">
                            <i class="material-icons">history</i>
                            <span class="nav-link-text ms-1">IoT History</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('risk-areas.*') ? 'active' : '' }}"
                            href="{{ route('risk-areas.index') }}">
                            <i class="material-icons">report_problem</i>
                            <span>Risk Areas</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.bookings') ? 'active' : '' }}"
                            href="{{ route('admin.bookings') }}">
                            <i class="material-icons">receipt</i>
                            <span>All Bookings</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('disaster-history.*') ? 'active' : '' }}"
                            href="{{ route('disaster-history.index') }}">
                            <i class="material-icons">timeline</i>
                            <span>Disaster History</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('detection-history.*') ? 'active' : '' }}"
                            href="{{ route('detection-history.index') }}">
                            <i class="material-icons">visibility</i>
                            <span>Detection History</span>
                        </a>
                    </li>
                @endif

                <li class="nav-item mt-3">
                    <span class="text-white px-3 small">BOOKING</span>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('booking.search') ? 'active' : '' }}"
                        href="{{ route('booking.search') }}">
                        <i class="material-icons">search</i>
                        <span>Search Trains</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('booking.my-bookings') ? 'active' : '' }}"
                        href="{{ route('booking.my-bookings') }}">
                        <i class="material-icons">history</i>
                        <span>My Bookings</span>
                    </a>
                </li>
            </ul>

            <hr class="my-4">

            <div class="px-3">
                <a class="nav-link text-danger" href="#" onclick="document.getElementById('logout-form').submit();">
                    <i class="material-icons">logout</i>
                    <span>Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                    @csrf
                </form>
            </div>
        </nav>

        <!-- Main Content Wrapper -->
        <div class="main-wrapper">
            <!-- Top Navigation Bar -->
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <button class="btn btn-outline-secondary sidebar-toggle d-lg-none" id="sidebar-toggle">
                        <i class="material-icons">menu</i>
                    </button>


                    <div class="ms-auto d-flex align-items-center">
                        <span class="me-3">Welcome, <strong>{{ auth()->user()->name ?? 'Guest' }}</strong></span>
                        <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'User' }}&background=e74c3c&color=fff"
                            alt="Avatar" class="rounded-circle me-3" width="40" height="40">
                        @if (Auth::check() && Auth::user()->can('notifications.view'))
                            <div class="dropdown">
                                <button class="btn btn-link text-dark p-0 position-relative" id="notificationDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="material-icons">notifications</i>
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"
                                        id="notificationCount">
                                        0
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="notificationDropdown"
                                    id="notificationList" style="min-width: 300px; max-height: 400px; overflow-y: auto;">
                                    <li>
                                        <h6 class="dropdown-header">Notifications</h6>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li class="text-center p-3 text-muted" id="noNotifications">No new notifications</li>
                                </ul>
                            </div>
                        @endif

                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="main-content">
                <!-- Notification Modal -->
                <div class="modal fade" id="notificationDetailModal" tabindex="-1"
                    aria-labelledby="notificationDetailModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="notificationDetailModalLabel">Notification Details</h5>
                                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="material-icons me-2" id="modalNotificationIcon">info</i>
                                    <span class="badge" id="modalNotificationBadge">Info</span>
                                </div>
                                <p id="modalNotificationMessage" class="lead"></p>
                                <small class="text-muted" id="modalNotificationTime"></small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Please correct the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Material Dashboard JS -->
    <script src="https://cdn.jsdelivr.net/npm/material-dashboard@3.0.0/assets/js/material-dashboard.min.js"></script>

    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function (e) {
                e.preventDefault();
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            });
        }

        // Close sidebar when clicking overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function () {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            });
        }

        // Close sidebar when clicking on a link
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 991) {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function () {
            if (window.innerWidth > 991) {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            }
        });

        // Notification Logic
        $(document).ready(function () {
            if ("Notification" in window) {
                if (Notification.permission === "default") {
                    Notification.requestPermission();
                }
            }

            function fetchNotifications() {
                $.get('/api/notifications', function (response) {
                    if (response.success && response.data.length > 0) {
                        $('#notificationCount').text(response.data.length).removeClass('d-none');
                        $('#noNotifications').addClass('d-none');

                        let items = '';
                        response.data.forEach(notification => {
                            let message = notification.message.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                            if (notification.message.length > 50) {
                                message = notification.message.substring(0, 50) + "...";
                            }

                            items += `
                                <li class="notification-item p-2 border-bottom" 
                                    data-id="${notification.id}" 
                                    data-message="${message}"
                                    data-type="${notification.type || 'info'}"
                                    data-time="${new Date(notification.created_at).toLocaleString()}"
                                    style="cursor:pointer;">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="material-icons text-${notification.type || 'info'}">info</i>
                                        </div>
                                        <div>
                                            <p class="mb-0 text-sm text-xs">${message}</p>
                                            <small class="text-muted">${new Date(notification.created_at).toLocaleTimeString()}</small>
                                        </div>
                                    </div>
                                </li>
                            `;

                            // Show browser notification
                            if (Notification.permission === "granted") {
                                // Simple check to avoid duplicate toasts if already shown
                                if (!window.shownNotifications) window.shownNotifications = new Set();
                                if (!window.shownNotifications.has(notification.id)) {
                                    new Notification("RailFlow Alert", {
                                        body: notification.message,
                                        icon: '/favicon.ico'
                                    });
                                    window.shownNotifications.add(notification.id);
                                }
                            }
                        });

                        $('#notificationList').find('.notification-item').remove();
                        $('#notificationList').append(items);
                    } else {
                        $('#notificationCount').addClass('d-none');
                        $('#noNotifications').removeClass('d-none');
                        $('#notificationList').find('.notification-item').remove();
                    }
                });
            }

            // Mark as read and show modal on click
            $(document).on('click', '.notification-item', function () {
                const id = $(this).data('id');
                const message = $(this).data('message');
                const type = $(this).data('type');
                const time = $(this).data('time');
                const item = $(this);

                // Populate and show modal
                $('#modalNotificationMessage').text(message);
                $('#modalNotificationTime').text(time);
                $('#modalNotificationIcon').text('info').attr('class', `material-icons me-2 text-${type}`);
                $('#modalNotificationBadge').text(type.toUpperCase()).attr('class', `badge bg-gradient-${type === 'info' ? 'info' : (type === 'warning' ? 'warning' : (type === 'danger' ? 'danger' : 'success'))}`);

                const modal = new bootstrap.Modal(document.getElementById('notificationDetailModal'));
                modal.show();

                // Mark as read
                $.post(`/api/notifications/${id}/read`, { _token: "{{ csrf_token() }}" }, function () {
                    item.fadeOut(function () {
                        item.remove();
                        const currentCount = parseInt($('#notificationCount').text());
                        const count = currentCount - 1;
                        if (count > 0) {
                            $('#notificationCount').text(count);
                        } else {
                            $('#notificationCount').addClass('d-none');
                            $('#noNotifications').removeClass('d-none');
                        }
                    });
                });
            });

            // Initial fetch and poll every 30 seconds
            if ({{ auth()->check() ? 'true' : 'false' }}) {
                fetchNotifications();
                setInterval(fetchNotifications, 30000);
            }
        });
    </script>

    <!-- Common Notifications -->
    <script src="{{ asset('js/notifications.js') }}"></script>

    @yield('scripts')
</body>

</html>