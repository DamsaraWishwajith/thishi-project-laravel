@extends('admin.layout')

@section('title', 'User Payments - Water Systems')
@section('header_title', 'User Payments')

@section('styles')
<style>
    /* Filters Bar styling */
    .filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .search-form {
        display: flex;
        gap: 12px;
        flex-grow: 1;
        max-width: 650px;
        flex-wrap: wrap;
    }

    .filter-control {
        background-color: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 10px 14px;
        color: var(--text-main);
        font-size: 14px;
        outline: none;
        min-width: 120px;
    }

    .filter-control:focus {
        border-color: var(--accent-cyan);
    }

    .search-input {
        flex-grow: 2;
        min-width: 200px;
    }

    .btn-search-submit {
        background-color: var(--accent-blue);
        color: var(--text-main);
        border: none;
        border-radius: 10px;
        padding: 10px 18px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-search-submit:hover {
        background-color: #1d4ed8;
    }

    .btn-clear-filter {
        background-color: var(--bg-tertiary);
        color: var(--text-muted);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 10px 14px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .btn-clear-filter:hover {
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

    /* Value Displays */
    .val-points {
        color: var(--accent-cyan);
        font-weight: 600;
    }

    .val-bill {
        color: var(--accent-green);
        font-weight: 700;
    }

    /* Payment Status Buttons */
    .btn-pay-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        letter-spacing: 0.5px;
    }

    .btn-pay-status.paid {
        background-color: rgba(25, 135, 84, 0.15);
        color: var(--accent-green);
        border: 1px solid rgba(25, 135, 84, 0.2);
    }

    .btn-pay-status.paid:hover {
        background-color: var(--accent-green);
        color: #000;
        box-shadow: 0 0 10px rgba(25, 135, 84, 0.35);
    }

    .btn-pay-status.unpaid {
        background-color: rgba(220, 53, 69, 0.15);
        color: var(--accent-red);
        border: 1px solid rgba(220, 53, 69, 0.2);
    }

    .btn-pay-status.unpaid:hover {
        background-color: var(--accent-red);
        color: var(--text-main);
        box-shadow: 0 0 10px rgba(220, 53, 69, 0.35);
    }

    .btn-view-profile {
        background-color: var(--bg-tertiary);
        color: var(--text-muted);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 6px 12px;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-view-profile:hover {
        border-color: var(--accent-cyan);
        color: var(--accent-cyan);
    }
</style>
@endsection

@section('content')

    @php
        $monthsList = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
    @endphp

    <div class="card">
        <div class="card-title">
            <span>Monthly Payment Status Directory</span>
            <span class="text-cyan" style="font-size: 14px; font-weight: 600;">
                Selected: {{ $monthsList[$month] }} {{ $year }}
            </span>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <form action="{{ route('admin.summaries') }}" method="GET" class="search-form">
                <!-- Search -->
                <input type="text" name="search" class="filter-control search-input" placeholder="Search user by Name or NIC..." value="{{ $search }}">

                <!-- Month Filter -->
                <select name="month" class="filter-control" onchange="this.form.submit()">
                    @foreach($monthsList as $num => $name)
                        <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>

                <!-- Year Filter -->
                <select name="year" class="filter-control" onchange="this.form.submit()">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                <button type="submit" class="btn-search-submit">Apply Filters</button>

                @if($search || $month != date('n') || $year != date('Y'))
                    <a href="{{ route('admin.summaries') }}" class="btn-clear-filter" title="Reset to current month">Reset</a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="table-container">
            @if($users->isEmpty())
                <div style="text-align: center; padding: 60px; color: var(--text-muted);">
                    <i class="fa-solid fa-users-slash" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                    <p>No users found matching search criteria.</p>
                </div>
            @else
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>NIC</th>
                            <th>User Name</th>
                            <th>Units Consumed</th>
                            <th>Total Bill (Rs.)</th>
                            <th>Payment Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            @php
                                $cell = $grid[$user->nic] ?? ['points' => 0.0, 'bill' => 0.0, 'paid' => true, 'has_records' => false];
                            @endphp
                            <tr>
                                <td style="font-weight: 700; color: var(--accent-cyan);">{{ $user->nic }}</td>
                                <td style="font-weight: 600;">{{ $user->name }}</td>
                                
                                <td>
                                    @if($cell['has_records'])
                                        <span class="val-points">{{ number_format($cell['points'], 1) }} Units</span>
                                    @else
                                        <span style="color: var(--text-muted); opacity: 0.5;">0.0 Units</span>
                                    @endif
                                </td>

                                <td>
                                    @if($cell['has_records'])
                                        <span class="val-bill">Rs. {{ number_format($cell['bill'], 2) }}</span>
                                    @else
                                        <span style="color: var(--text-muted); opacity: 0.5;">Rs. 0.00</span>
                                    @endif
                                </td>

                                <td>
                                    @if($cell['has_records'])
                                        @if($cell['paid'])
                                            <button type="button" class="btn-pay-status paid" onclick="submitToggle('{{ $user->nic }}', {{ $month }}, 0)" title="Click to mark as Unpaid">
                                                <i class="fa-solid fa-circle-check"></i> Paid
                                            </button>
                                        @else
                                            <button type="button" class="btn-pay-status unpaid" onclick="submitToggle('{{ $user->nic }}', {{ $month }}, 1)" title="Click to mark as Paid">
                                                <i class="fa-solid fa-circle-xmark"></i> Unpaid
                                            </button>
                                        @endif
                                    @else
                                        <span style="color: var(--text-muted); font-size: 12px; font-style: italic;">No readings logged</span>
                                    @endif
                                </td>

                                <td style="text-align: right;">
                                    <a href="{{ route('admin.users.detail', $user->nic) }}" class="btn-view-profile">
                                        <i class="fa-solid fa-user-gear"></i> Manage
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- Hidden form for payment status toggle -->
    <form id="paymentToggleForm" action="{{ route('admin.payments.toggle') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="nic" id="toggleNic">
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="hidden" name="month" id="toggleMonth">
        <input type="hidden" name="status" id="toggleStatus">
    </form>

@endsection

@section('scripts')
<script>
    function submitToggle(nic, month, newStatus) {
        const actionStr = newStatus === 1 ? 'mark as Paid' : 'mark as Unpaid';
        if (confirm(`Are you sure you want to ${actionStr} this month?`)) {
            document.getElementById('toggleNic').value = nic;
            document.getElementById('toggleMonth').value = month;
            document.getElementById('toggleStatus').value = newStatus;
            document.getElementById('paymentToggleForm').submit();
        }
    }
</script>
@endsection
