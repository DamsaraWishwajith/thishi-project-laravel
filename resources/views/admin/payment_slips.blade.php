@extends('admin.layout')

@section('title', 'Payment Slips Review - Water Systems')
@section('header_title', 'Payment Slips Verification')

@section('styles')
<style>
    /* Status Badge styling */
    .badge-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        letter-spacing: 0.5px;
    }
    .badge-status.pending {
        background-color: rgba(255, 193, 7, 0.15);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.2);
    }
    .badge-status.approved {
        background-color: rgba(25, 135, 84, 0.15);
        color: var(--accent-green);
        border: 1px solid rgba(25, 135, 84, 0.2);
    }
    .badge-status.rejected {
        background-color: rgba(220, 53, 69, 0.15);
        color: var(--accent-red);
        border: 1px solid rgba(220, 53, 69, 0.2);
    }

    /* Filters section */
    .filter-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .filter-tabs {
        display: flex;
        gap: 8px;
        background-color: var(--bg-primary);
        padding: 4px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
    }

    .filter-tab {
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .filter-tab:hover {
        color: var(--text-main);
    }

    .filter-tab.active {
        background-color: var(--bg-secondary);
        color: var(--accent-cyan);
        box-shadow: var(--card-shadow);
    }

    .search-form {
        display: flex;
        gap: 12px;
        max-width: 350px;
        width: 100%;
    }

    .search-input {
        background-color: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 8px 14px;
        color: var(--text-main);
        font-size: 14px;
        width: 100%;
        outline: none;
    }

    .search-input:focus {
        border-color: var(--accent-cyan);
    }

    .btn-search {
        background-color: var(--accent-blue);
        color: var(--text-main);
        border: none;
        border-radius: 10px;
        padding: 8px 16px;
        font-weight: 600;
        cursor: pointer;
    }

    /* Actions styling */
    .action-btn-group {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .btn-action {
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
    }

    .btn-action.approve {
        background-color: rgba(25, 135, 84, 0.15);
        color: var(--accent-green);
        border: 1px solid rgba(25, 135, 84, 0.2);
    }
    .btn-action.approve:hover {
        background-color: var(--accent-green);
        color: #000;
    }

    .btn-action.reject {
        background-color: rgba(220, 53, 69, 0.15);
        color: var(--accent-red);
        border: 1px solid rgba(220, 53, 69, 0.2);
    }
    .btn-action.reject:hover {
        background-color: var(--accent-red);
        color: var(--text-main);
    }

    .btn-view-slip {
        background-color: rgba(13, 202, 240, 0.15);
        color: var(--accent-cyan);
        border: 1px solid rgba(13, 202, 240, 0.2);
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-view-slip:hover {
        background-color: var(--accent-cyan);
        color: #000;
    }

    /* Modal viewer */
    .slip-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.85);
        z-index: 1000;
        justify-content: center;
        align-items: center;
        padding: 40px;
    }

    .modal-content-wrapper {
        position: relative;
        max-width: 90%;
        max-height: 90%;
    }

    .modal-image {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 12px;
        border: 2px solid var(--border-color);
        box-shadow: 0 0 24px rgba(0,0,0,0.5);
    }

    .btn-close-modal {
        position: absolute;
        top: -40px;
        right: 0;
        background: none;
        border: none;
        color: var(--text-main);
        font-size: 28px;
        cursor: pointer;
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

    .table-custom tr:hover td {
        background-color: rgba(255, 255, 255, 0.02);
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
            <span>Payment Slips verification Queue</span>
            <span class="text-cyan" style="font-size: 13px;">Verify bank transfers & slips</span>
        </div>

        <!-- Filters & Search -->
        <div class="filter-section">
            <div class="filter-tabs">
                <a href="{{ route('admin.payment-slips') }}" class="filter-tab {{ !$status ? 'active' : '' }}">All Slips</a>
                <a href="{{ route('admin.payment-slips', ['status' => 'pending', 'search' => $search]) }}" class="filter-tab {{ $status === 'pending' ? 'active' : '' }}">Pending</a>
                <a href="{{ route('admin.payment-slips', ['status' => 'approved', 'search' => $search]) }}" class="filter-tab {{ $status === 'approved' ? 'active' : '' }}">Approved</a>
                <a href="{{ route('admin.payment-slips', ['status' => 'rejected', 'search' => $search]) }}" class="filter-tab {{ $status === 'rejected' ? 'active' : '' }}">Rejected</a>
            </div>

            <form action="{{ route('admin.payment-slips') }}" method="GET" class="search-form">
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <input type="text" name="search" class="search-input" placeholder="Search NIC or User..." value="{{ $search }}">
                <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i></button>
                @if($search)
                    <a href="{{ route('admin.payment-slips', $status ? ['status' => $status] : []) }}" class="btn-reset" style="display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color); border-radius: 10px; padding: 0 10px; color: var(--text-muted); text-decoration: none; font-size: 12px;">Clear</a>
                @endif
            </form>
        </div>

        <!-- Slips Table -->
        <div class="table-container" style="overflow-x: auto;">
            @if($slips->isEmpty())
                <div style="text-align: center; padding: 60px; color: var(--text-muted);">
                    <i class="fa-solid fa-file-invoice" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                    <p>No payment slips submitted found.</p>
                </div>
            @else
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>NIC</th>
                            <th>User Name</th>
                            <th>Billing Month</th>
                            <th>Bill No</th>
                            <th>Amount Paid</th>
                            <th>Uploaded At</th>
                            <th>Verification Status</th>
                            <th>File Reference</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($slips as $slip)
                            <tr>
                                <td style="font-weight: 700; color: var(--accent-cyan);">{{ $slip->nic }}</td>
                                <td style="font-weight: 600;">{{ $slip->user->name ?? 'Unknown User' }}</td>
                                <td style="font-weight: 600;">{{ ucfirst(Carbon\Carbon::create()->month($slip->month)->format('F')) }} {{ $slip->year }}</td>
                                <td style="font-weight: 600; color: var(--text-muted);">{{ $slip->bill_no ?? 'N/A' }}</td>
                                <td style="font-weight: 700; color: var(--accent-green);">Rs. {{ number_format($slip->amount, 2) }}</td>
                                <td style="font-size: 13px; color: var(--text-muted);">{{ $slip->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <span class="badge-status {{ $slip->status }}">
                                        <i class="fa-solid {{ $slip->status === 'approved' ? 'fa-circle-check' : ($slip->status === 'rejected' ? 'fa-circle-xmark' : 'fa-circle-notch fa-spin') }}"></i>
                                        {{ $slip->status }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $extension = strtolower(pathinfo($slip->slip_path, PATHINFO_EXTENSION));
                                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png']);
                                    @endphp

                                    @if($isImage)
                                        <button type="button" class="btn-view-slip" onclick="openSlipModal('{{ asset($slip->slip_path) }}')" title="View Slip Image">
                                            <i class="fa-solid fa-image"></i> View Slip
                                        </button>
                                    @else
                                        <a href="{{ asset($slip->slip_path) }}" target="_blank" class="btn-view-slip" title="Open PDF Slip">
                                            <i class="fa-solid fa-file-pdf"></i> Open PDF
                                        </a>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    @if($slip->status === 'pending')
                                        <div class="action-btn-group">
                                            <form action="{{ route('admin.payment-slips.approve', $slip->id) }}" method="POST" onsubmit="return confirm('Approve this payment? This will mark their bill as Paid.')">
                                                @csrf
                                                <button type="submit" class="btn-action approve">
                                                    <i class="fa-solid fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.payment-slips.reject', $slip->id) }}" method="POST" onsubmit="return confirm('Reject this payment slip?')">
                                                @csrf
                                                <button type="submit" class="btn-action reject">
                                                    <i class="fa-solid fa-xmark"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span style="font-size: 12px; color: var(--text-muted);">Verified</span>
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
            {{ $slips->links() }}
        </div>
    </div>

    <!-- Image Modal Viewer -->
    <div id="slipModal" class="slip-modal" onclick="closeSlipModal()">
        <div class="modal-content-wrapper" onclick="event.stopPropagation()">
            <button type="button" class="btn-close-modal" onclick="closeSlipModal()">&times;</button>
            <img id="modalImg" class="modal-image" src="" alt="Payment Slip Reference">
        </div>
    </div>

@endsection

@section('scripts')
<script>
    function openSlipModal(imageSrc) {
        document.getElementById('modalImg').src = imageSrc;
        document.getElementById('slipModal').style.display = 'flex';
    }

    function closeSlipModal() {
        document.getElementById('slipModal').style.display = 'none';
        document.getElementById('modalImg').src = '';
    }

    // Escape key closes modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeSlipModal();
        }
    });
</script>
@endsection
