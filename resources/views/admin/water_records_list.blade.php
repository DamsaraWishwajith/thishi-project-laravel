@extends('admin.layout')

@section('title')
Daily Logs - {{ $user->name }}
@endsection

@section('header_title')
<a href="{{ route('admin.dashboard') }}" style="color: var(--text-muted); text-decoration: none; margin-right: 8px;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a> / 
<a href="{{ route('admin.users.detail', $user->nic) }}" style="color: var(--text-muted); text-decoration: none; margin-right: 8px;">Profile</a> / Daily Logs
@endsection

@section('styles')
<style>
    .logs-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .table-container {
        overflow-x: auto;
        margin-top: 10px;
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
        padding: 8px 12px;
        border-radius: 8px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .btn-delete:hover {
        background-color: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.2);
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 32px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
    }

    .btn-back {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: color 0.3s ease;
    }

    .btn-back:hover {
        color: var(--text-main);
    }
</style>
@endsection

@section('content')

    <div class="logs-container">
        <div class="card">
            <div class="card-title">
                <span>Daily Readings History Log</span>
                <span style="font-size: 13px; color: var(--text-muted);">{{ $dailyRecords->count() }} total entries</span>
            </div>

            <div class="table-container">
                @if($dailyRecords->isEmpty())
                    <div style="text-align: center; padding: 60px 40px; color: var(--text-muted);">
                        <i class="fa-solid fa-droplet-slash" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                        <p>No daily usage records registered for this user.</p>
                    </div>
                @else
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Water Rate</th>
                                <th>Liters Consumed</th>
                                <th>Billing Cost</th>
                                <th style="text-align: right;">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyRecords as $record)
                                <tr>
                                    <td style="font-weight: 600; color: var(--text-main);">
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
                                        <form action="{{ route('admin.water-records.delete', $record->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this water record?')">
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

            <div class="card-footer">
                <a href="{{ route('admin.users.detail', $user->nic) }}" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Return to Profile
                </a>
            </div>
        </div>
    </div>

@endsection
