@extends('admin.layout')

@section('title', 'Admin Dashboard - Water Systems')
@section('header_title', 'Dashboard Overview')

@section('styles')
<style>
    /* Stats Grid Layout */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background-color: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--card-shadow);
        display: flex;
        align-items: center;
        gap: 20px;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
    }

    .stat-card::after {
        content: '';
        position: absolute;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.03), transparent 70%);
        right: -30px;
        bottom: -30px;
    }

    .stat-icon {
        width: 54px;
        height: 54px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .stat-icon.users { background-color: rgba(13, 110, 253, 0.1); color: var(--accent-blue); }
    .stat-icon.liters { background-color: rgba(13, 202, 240, 0.1); color: var(--accent-cyan); }
    .stat-icon.rate { background-color: rgba(25, 135, 84, 0.1); color: var(--accent-green); }
    .stat-icon.billing { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }

    .stat-details {
        display: flex;
        flex-direction: column;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Dashboard Activity Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    @media (max-width: 992px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    .recent-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 10px;
    }

    .recent-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: var(--bg-primary);
        border: 1px solid var(--border-color);
        padding: 14px 18px;
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    .recent-item:hover {
        transform: translateX(4px);
        border-color: rgba(255, 255, 255, 0.12);
    }

    .item-meta {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .item-title {
        font-weight: 600;
        font-size: 14px;
        color: var(--text-main);
    }

    .item-subtitle {
        font-size: 12px;
        color: var(--text-muted);
    }

    .item-val {
        text-align: right;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .val-primary {
        font-weight: 700;
        font-size: 14px;
    }

    .val-secondary {
        font-size: 12px;
        color: var(--text-muted);
    }

    .btn-card-action {
        color: var(--accent-cyan);
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        transition: opacity 0.2s ease;
    }

    .btn-card-action:hover {
        opacity: 0.8;
    }
</style>
@endsection

@section('content')

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon users">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value">{{ $totalUsers }}</div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon liters">
                <i class="fa-solid fa-droplet"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value">{{ number_format($totalLiters, 1) }} L</div>
                <div class="stat-label">Total Liters</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon rate">
                <i class="fa-solid fa-tag"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value">Rs. {{ number_format($avgRate, 2) }}</div>
                <div class="stat-label">Avg Water Rate</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon billing">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value">Rs. {{ number_format($totalBilling, 2) }}</div>
                <div class="stat-label">Total Bills</div>
            </div>
        </div>
    </div>

    <!-- Activity Sections -->
    <div class="dashboard-grid">
        <!-- Recent Daily Readings -->
        <div class="card">
            <div class="card-title">
                <span>Recent Daily Readings</span>
                <a href="{{ route('admin.records') }}" class="btn-card-action">View All Logs</a>
            </div>

            <div class="recent-list">
                @if($recentReadings->isEmpty())
                    <p style="text-align: center; color: var(--text-muted); padding: 40px 0;">No readings logged yet.</p>
                @else
                    @foreach($recentReadings as $reading)
                        <div class="recent-item">
                            <div class="item-meta">
                                <span class="item-title">{{ $reading->user->name ?? 'Deleted User' }}</span>
                                <span class="item-subtitle">NIC: {{ $reading->nic }} | {{ $reading->date->format('Y-m-d') }}</span>
                            </div>
                            <div class="item-val">
                                <span class="val-primary text-cyan">{{ number_format($reading->points, 1) }} Liters</span>
                                <span class="val-secondary text-green">Rs. {{ number_format($reading->bill, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Recently Registered Users -->
        <div class="card">
            <div class="card-title">
                <span>Recent User Registrations</span>
                <a href="{{ route('admin.users') }}" class="btn-card-action">View All Users</a>
            </div>

            <div class="recent-list">
                @if($recentUsers->isEmpty())
                    <p style="text-align: center; color: var(--text-muted); padding: 40px 0;">No users registered yet.</p>
                @else
                    @foreach($recentUsers as $user)
                        <div class="recent-item">
                            <div class="item-meta">
                                <span class="item-title">{{ $user->name }}</span>
                                <span class="item-subtitle">NIC: {{ $user->nic }}</span>
                            </div>
                            <div class="item-val" style="justify-content: center;">
                                <a href="{{ route('admin.users.detail', $user->nic) }}" class="btn-card-action" style="font-size: 12px;">
                                    Manage <i class="fa-solid fa-arrow-right" style="margin-left: 4px;"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

@endsection
