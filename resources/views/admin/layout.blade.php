<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Water Systems Admin')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Premium CSS Design System -->
    <style>
        :root {
            --bg-primary: #0a1128;
            --bg-secondary: #101f42;
            --bg-tertiary: #172c5e;
            --accent-blue: #0d6efd;
            --accent-cyan: #0dcaf0;
            --accent-green: #198754;
            --accent-red: #dc3545;
            --text-main: #f8f9fa;
            --text-muted: #a0aec0;
            --border-color: rgba(255, 255, 255, 0.08);
            --card-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background-color: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
            transition: var(--transition-speed);
        }

        .sidebar-brand {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
            text-decoration: none;
        }

        .sidebar-brand i {
            color: var(--accent-cyan);
            filter: drop-shadow(0 0 8px rgba(13, 202, 240, 0.5));
        }

        .sidebar-menu {
            list-style: none;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
        }

        .sidebar-menu-item a {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            transition: all var(--transition-speed);
        }

        .sidebar-menu-item a:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
        }

        .sidebar-menu-item.active a {
            background-color: var(--accent-blue);
            color: var(--text-main);
            box-shadow: 0 0 16px rgba(13, 110, 253, 0.4);
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid var(--border-color);
        }

        .btn-logout {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--accent-red);
            border: 1px solid rgba(220, 53, 69, 0.2);
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all var(--transition-speed);
        }

        .btn-logout:hover {
            background-color: var(--accent-red);
            color: var(--text-main);
            box-shadow: 0 0 16px rgba(220, 53, 69, 0.4);
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: var(--transition-speed);
        }

        .top-navbar {
            height: 70px;
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
        }

        .navbar-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-main);
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-avatar {
            width: 38px;
            height: 38px;
            background-color: var(--bg-tertiary);
            border: 1px solid var(--accent-cyan);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-cyan);
        }

        .admin-name {
            font-size: 14px;
            font-weight: 500;
        }

        .content-body {
            padding: 32px;
            flex-grow: 1;
        }

        /* Global UI Elements */
        .card {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--card-shadow);
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Alert styling */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            font-weight: 500;
        }

        .alert-success {
            background-color: rgba(25, 135, 84, 0.1);
            border: 1px solid rgba(25, 135, 84, 0.2);
            color: #2ec4b6;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: #ff9f1c;
        }

        /* Utility classes */
        .text-cyan { color: var(--accent-cyan); }
        .text-blue { color: var(--accent-blue); }
        .text-green { color: var(--accent-green); }
        .text-red { color: var(--accent-red); }

        .sidebar-separator {
            height: 1px;
            background-color: var(--border-color);
            margin: 16px 0;
            list-style: none;
        }

        .sidebar-section-title {
            color: var(--accent-cyan);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 16px 8px;
            list-style: none;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
            }
            .sidebar-brand span, .sidebar-menu-item span, .btn-logout span {
                display: none;
            }
            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <i class="fa-solid fa-water"></i>
            <span>Water Admin</span>
        </a>
        
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="sidebar-menu-item {{ Request::routeIs('admin.users') ? 'active' : '' }}">
                <a href="{{ route('admin.users') }}">
                    <i class="fa-solid fa-users"></i>
                    <span>User List</span>
                </a>
            </li>
            
            <li class="sidebar-menu-item {{ Request::routeIs('admin.users.create') ? 'active' : '' }}">
                <a href="{{ route('admin.users.create') }}">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Create User</span>
                </a>
            </li>

            <li class="sidebar-menu-item {{ Request::routeIs('admin.records') || Request::routeIs('admin.records.create') ? 'active' : '' }}">
                <a href="{{ route('admin.records') }}">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>User Water History</span>
                </a>
            </li>

            <li class="sidebar-menu-item {{ Request::routeIs('admin.summaries') ? 'active' : '' }}">
                <a href="{{ route('admin.summaries') }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>User Payments</span>
                </a>
            </li>

            <li class="sidebar-menu-item {{ Request::routeIs('admin.meter-disconnect') ? 'active' : '' }}">
                <a href="{{ route('admin.meter-disconnect') }}">
                    <i class="fa-solid fa-toggle-off"></i>
                    <span>Meter Disconnect</span>
                </a>
            </li>

            <li class="sidebar-menu-item {{ Request::routeIs('admin.payment-slips') ? 'active' : '' }}">
                <a href="{{ route('admin.payment-slips') }}">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Payment Slips</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="{{ route('admin.logout') }}" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="navbar-title">
                @yield('header_title', 'System Management')
            </div>
            <div class="admin-profile">
                <div class="admin-avatar">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div class="admin-name">Administrator</div>
            </div>
        </nav>

        <!-- Page Body -->
        <div class="content-body">
            <!-- Success/Error Alerts -->
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    @yield('scripts')
</body>
</html>
