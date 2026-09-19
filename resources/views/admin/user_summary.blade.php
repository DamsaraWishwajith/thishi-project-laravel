@extends('admin.layout')

@section('title')
Monthly Summary - {{ $user->name }}
@endsection

@section('header_title')
<a href="{{ route('admin.dashboard') }}" style="color: var(--text-muted); text-decoration: none; margin-right: 8px;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a> / 
<a href="{{ route('admin.users.detail', $user->nic) }}" style="color: var(--text-muted); text-decoration: none; margin-right: 8px;">Profile</a> / Monthly Summary
@endsection

@section('styles')
<style>
    .summary-container {
        max-width: 700px;
        margin: 0 auto;
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .summary-table th, .summary-table td {
        padding: 16px;
        text-align: left;
        font-size: 14px;
    }

    .summary-table th {
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-color);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .summary-table td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    }

    .summary-table tr.total-row td {
        font-weight: 700;
        border-top: 2px solid var(--border-color);
        border-bottom: none;
        color: var(--accent-cyan);
        font-size: 16px;
    }

    .year-filter-form {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .year-select {
        background-color: var(--bg-primary);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        outline: none;
        cursor: pointer;
    }

    .year-select:focus {
        border-color: var(--accent-cyan);
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

    <div class="summary-container">
        <div class="card">
            <div class="card-title">
                <span>Monthly Aggregates Summary</span>
                <form action="{{ route('admin.users.summary', $user->nic) }}" method="GET" class="year-filter-form">
                    <select name="year" class="year-select" onchange="this.form.submit()">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>
            
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Units Consumed</th>
                        <th>Billing Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyAggregates as $month => $vals)
                        <tr>
                            <td style="font-weight: 500;">{{ $month }}</td>
                            <td>{{ number_format($vals['points'], 2) }} Units</td>
                            <td class="text-green" style="font-weight: 600;">
                                Rs. {{ number_format($vals['bill'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>Annual Total</td>
                        <td>{{ number_format($totalPoints, 2) }} Units</td>
                        <td>Rs. {{ number_format($totalBill, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="card-footer">
                <a href="{{ route('admin.users.detail', $user->nic) }}" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Return to Profile
                </a>
            </div>
        </div>
    </div>

@endsection
