@extends('admin.layout')

@section('title', 'User List - Water Systems')
@section('header_title', 'Registered User Directory')

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

    .btn-register-new {
        background-color: var(--accent-cyan);
        color: #000;
        font-weight: 700;
        border-radius: 12px;
        padding: 12px 20px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        box-shadow: 0 4px 14px rgba(13, 202, 240, 0.2);
        transition: all 0.3s ease;
    }

    .btn-register-new:hover {
        background-color: #0baccc;
        box-shadow: 0 6px 18px rgba(13, 202, 240, 0.4);
        transform: translateY(-2px);
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

    .btn-view-profile {
        background-color: var(--bg-tertiary);
        color: var(--accent-cyan);
        border: 1px solid rgba(13, 202, 240, 0.2);
        border-radius: 8px;
        padding: 6px 14px;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-view-profile:hover {
        background-color: var(--accent-cyan);
        color: #000;
        box-shadow: 0 0 12px rgba(13, 202, 240, 0.3);
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
            <span>User Directory</span>
            <a href="{{ route('admin.users.create') }}" class="btn-register-new">
                <i class="fa-solid fa-user-plus"></i> Register New User
            </a>
        </div>

        <!-- Search Bar -->
        <div class="search-bar-container">
            <form action="{{ route('admin.users') }}" method="GET" class="search-form">
                <input type="text" name="search" class="search-input" placeholder="Search by Name, Email, NIC or PNo..." value="{{ $search }}" autofocus>
                
                @if($search)
                    <a href="{{ route('admin.users') }}" class="btn-reset" title="Clear search">Clear</a>
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
                    <p>No registered user accounts found.</p>
                </div>
            @else
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>NIC</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone No</th>
                            <th>Bill No</th>
                            <th>Address</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td style="font-weight: 700; color: var(--accent-cyan);">{{ $user->nic }}</td>
                                <td style="font-weight: 600;">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->pno }}</td>
                                <td>{{ $user->bill_no }}</td>
                                <td style="color: var(--text-muted); font-size: 13px;">{{ $user->address }}</td>
                                <td style="text-align: right;">
                                    <a href="{{ route('admin.users.detail', $user->nic) }}" class="btn-view-profile">
                                        <i class="fa-solid fa-user-gear"></i> Manage Profile
                                    </a>
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

@endsection
