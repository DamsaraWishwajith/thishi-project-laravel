@extends('admin.layout')

@section('title', 'Meter Disconnect - Water Systems')
@section('header_title', 'Meter Disconnect Panel')

@section('styles')
<style>
    /* Search Bar */
    .search-bar-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .search-form {
        display: flex;
        gap: 12px;
        flex-grow: 1;
        max-width: 450px;
    }

    .search-input {
        background-color: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 12px 16px;
        color: var(--text-main);
        font-size: 15px;
        width: 100%;
        outline: none;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        border-color: var(--accent-cyan);
        box-shadow: 0 0 12px rgba(13, 202, 240, 0.15);
    }

    .btn-search {
        background-color: var(--accent-blue);
        color: var(--text-main);
        border: none;
        border-radius: 12px;
        padding: 12px 20px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-search:hover {
        background-color: #1d4ed8;
    }

    .btn-reset {
        background-color: var(--bg-tertiary);
        color: var(--text-muted);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 12px 16px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .btn-reset:hover {
        color: var(--text-main);
        background-color: rgba(255, 255, 255, 0.05);
    }

    /* Table styling */
    .table-container {
        overflow-x: auto;
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .table-custom th {
        padding: 16px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-muted);
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table-custom td {
        padding: 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        font-size: 14px;
    }

    .table-custom tr:last-child td {
        border-bottom: none;
    }

    .table-custom tr:hover td {
        background-color: rgba(255, 255, 255, 0.02);
    }

    /* Status Action Button */
    .btn-toggle-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        letter-spacing: 0.5px;
    }

    .btn-toggle-status.connected {
        background-color: rgba(25, 135, 84, 0.15);
        color: var(--accent-green);
        border: 1px solid rgba(25, 135, 84, 0.2);
    }

    .btn-toggle-status.connected:hover {
        background-color: var(--accent-green);
        color: #000;
        box-shadow: 0 0 10px rgba(25, 135, 84, 0.35);
    }

    .btn-toggle-status.disconnected {
        background-color: rgba(220, 53, 69, 0.15);
        color: var(--accent-red);
        border: 1px solid rgba(220, 53, 69, 0.2);
    }

    .btn-toggle-status.disconnected:hover {
        background-color: var(--accent-red);
        color: var(--text-main);
        box-shadow: 0 0 10px rgba(220, 53, 69, 0.35);
    }

    /* Pagination CSS */
    .pagination-wrapper {
        margin-top: 24px;
        display: flex;
        justify-content: center;
    }

    .pagination-wrapper nav {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pagination-wrapper nav a, .pagination-wrapper nav span {
        padding: 8px 14px;
        background-color: var(--bg-secondary);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
    }

    .pagination-wrapper nav a:hover {
        background-color: var(--accent-blue);
        border-color: var(--accent-blue);
    }

    .pagination-wrapper nav .active {
        background-color: var(--accent-blue);
        border-color: var(--accent-blue);
    }
</style>
@endsection

@section('content')

    <div class="card">
        <div class="card-title">
            <span>Water Meter status Management</span>
            <span class="text-cyan" style="font-size: 13px;">Manage service connections</span>
        </div>

        <!-- Search Bar -->
        <div class="search-bar-container">
            <form action="{{ route('admin.meter-disconnect') }}" method="GET" class="search-form">
                <input type="text" name="search" class="search-input" placeholder="Search by Name, NIC or Address..." value="{{ $search }}" autofocus>
                
                @if($search)
                    <a href="{{ route('admin.meter-disconnect') }}" class="btn-reset" title="Clear search">Clear</a>
                @endif
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="table-container">
            @if($users->isEmpty())
                <div style="text-align: center; padding: 60px; color: var(--text-muted);">
                    <i class="fa-solid fa-users-slash" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                    <p>No user accounts found matching criteria.</p>
                </div>
            @else
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>NIC</th>
                            <th>User Name</th>
                            <th>Phone No</th>
                            <th>Bill No</th>
                            <th>Address</th>
                            <th>Connection Status</th>
                            <th style="text-align: right;">Toggle Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td style="font-weight: 700; color: var(--accent-cyan);">{{ $user->nic }}</td>
                                <td style="font-weight: 600;">{{ $user->name }}</td>
                                <td>{{ $user->pno }}</td>
                                <td>{{ $user->bill_no }}</td>
                                <td style="color: var(--text-muted); font-size: 13px;">{{ $user->address }}</td>
                                <td>
                                    @if(($user->status ?? 'connected') === 'connected')
                                        <span class="text-green" style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                                            <i class="fa-solid fa-circle" style="font-size: 8px;"></i> Connected
                                        </span>
                                    @else
                                        <span class="text-red" style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                                            <i class="fa-solid fa-circle" style="font-size: 8px;"></i> Disconnected
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    @if(($user->status ?? 'connected') === 'connected')
                                        <button type="button" class="btn-toggle-status connected" onclick="submitToggle('{{ $user->nic }}', 'disconnected', '{{ $user->name }}')" title="Disconnect water service">
                                            <i class="fa-solid fa-plug-circle-xmark"></i> Disconnect
                                        </button>
                                    @else
                                        <button type="button" class="btn-toggle-status disconnected" onclick="submitToggle('{{ $user->nic }}', 'connected', '{{ $user->name }}')" title="Reconnect water service">
                                            <i class="fa-solid fa-plug-circle-check"></i> Connect
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Hidden form for status toggle -->
    <form id="statusToggleForm" action="{{ route('admin.meter-disconnect.toggle') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="nic" id="toggleNic">
        <input type="hidden" name="status" id="toggleStatus">
    </form>

@endsection

@section('scripts')
<script>
    function submitToggle(nic, targetStatus, userName) {
        const actionStr = targetStatus === 'connected' ? 'reconnect' : 'disconnect';
        if (confirm(`Are you sure you want to ${actionStr} water meter service for ${userName}?`)) {
            document.getElementById('toggleNic').value = nic;
            document.getElementById('toggleStatus').value = targetStatus;
            document.getElementById('statusToggleForm').submit();
        }
    }
</script>
@endsection
