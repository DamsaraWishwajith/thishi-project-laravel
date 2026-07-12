@extends('admin.layout')

@section('title', 'User Water History - Water Systems')
@section('header_title', 'User Water History')

@section('styles')
<style>
    /* Search & Filter Styling */
    .filter-bar {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex-grow: 1;
        min-width: 200px;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-control {
        background-color: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 10px 14px;
        color: var(--text-main);
        font-size: 14px;
        outline: none;
        width: 100%;
    }

    .filter-control:focus {
        border-color: var(--accent-cyan);
    }

    .btn-filter-actions {
        display: flex;
        gap: 12px;
    }

    .btn-action {
        padding: 11px 20px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-primary {
        background-color: var(--accent-blue);
        color: var(--text-main);
    }

    .btn-primary:hover {
        background-color: #1d4ed8;
    }

    .btn-secondary {
        background-color: var(--bg-tertiary);
        color: var(--text-muted);
        border: 1px solid var(--border-color);
    }

    .btn-secondary:hover {
        background-color: rgba(255, 255, 255, 0.05);
        color: var(--text-main);
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

    .btn-delete {
        background: none;
        border: none;
        color: var(--accent-red);
        cursor: pointer;
        padding: 6px 10px;
        border-radius: 6px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        font-size: 13px;
        font-weight: 600;
    }

    .btn-delete:hover {
        background-color: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.2);
    }

    /* Pagination CSS */
    .pagination-wrapper {
        margin-top: 24px;
        display: flex;
        justify-content: center;
    }

    /* Target standard Laravel Tailwind pagination structure but make it compatible */
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
            <span>Water Readings Directory</span>
            <a href="{{ route('admin.records.create') }}" class="btn-action btn-primary" style="padding: 10px 18px; border-radius: 10px;">
                <i class="fa-solid fa-pen-to-square"></i> Log Daily Reading
            </a>
        </div>

        <!-- Filter Bar -->
        <form action="{{ route('admin.records') }}" method="GET" class="filter-bar">
            <div class="filter-group">
                <label class="filter-label">Filter by NIC</label>
                <input type="text" name="nic" class="filter-control" placeholder="e.g. 199912345" value="{{ $searchNic }}">
            </div>

            <div class="filter-group">
                <label class="filter-label">Filter by Date</label>
                <input type="date" name="date" class="filter-control" value="{{ $searchDate }}">
            </div>

            <div class="btn-filter-actions">
                @if($searchNic || $searchDate)
                    <a href="{{ route('admin.records') }}" class="btn-action btn-secondary">Reset</a>
                @endif
                <button type="submit" class="btn-action btn-primary">
                    <i class="fa-solid fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>

        <!-- Table -->
        <div class="table-container">
            @if($records->isEmpty())
                <div style="text-align: center; padding: 60px; color: var(--text-muted);">
                    <i class="fa-solid fa-droplet-slash" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                    <p>No water readings found matching the filters.</p>
                </div>
            @else
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>User (NIC)</th>
                            <th>Date</th>
                            <th>Water Rate</th>
                            <th>Consumption</th>
                            <th>Total Bill</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-main);">
                                        {{ $record->user->name ?? 'Deleted User' }}
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted);">
                                        NIC: {{ $record->nic }}
                                    </div>
                                </td>
                                <td style="font-weight: 500;">
                                    {{ $record->date->format('Y-m-d') }}
                                </td>
                                <td>Rs. {{ number_format($record->water_rate, 2) }} / L</td>
                                <td class="text-cyan" style="font-weight: 500;">
                                    {{ number_format($record->points, 2) }} L
                                </td>
                                <td class="text-green" style="font-weight: 600;">
                                    Rs. {{ number_format($record->bill, 2) }}
                                </td>
                                <td style="text-align: right;">
                                    <form action="{{ route('admin.water-records.delete', ['id' => $record->id, 'from' => 'all']) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this water record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete" title="Delete record">
                                            <i class="fa-solid fa-trash-can"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            {{ $records->links() }}
        </div>
    </div>

@endsection
