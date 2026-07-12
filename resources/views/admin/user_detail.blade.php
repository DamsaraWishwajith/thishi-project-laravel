@extends('admin.layout')

@section('title')
User Profile - {{ $user->name }}
@endsection

@section('header_title')
<a href="{{ route('admin.users') }}" style="color: var(--text-muted); text-decoration: none; margin-right: 8px;"><i class="fa-solid fa-arrow-left"></i> User List</a> / User Profile
@endsection

@section('styles')
<style>
    .profile-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 32px;
        align-items: start;
    }

    @media (max-width: 992px) {
        .profile-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-muted);
        font-weight: 500;
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        background-color: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 12px 14px;
        color: var(--text-main);
        font-size: 14px;
        outline: none;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--accent-cyan);
        box-shadow: 0 0 8px rgba(13, 202, 240, 0.15);
    }

    .form-control[readonly] {
        background-color: rgba(255, 255, 255, 0.02);
        color: var(--text-muted);
        cursor: not-allowed;
    }

    .text-danger {
        color: var(--accent-red);
        font-size: 12px;
        margin-top: 4px;
        display: block;
    }

    .btn-save {
        width: 100%;
        background-color: var(--accent-cyan);
        color: #000;
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-save:hover {
        background-color: #0dcaf0;
        box-shadow: 0 0 16px rgba(13, 202, 240, 0.4);
    }

    /* Actions Navigation Panel */
    .actions-panel {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .nav-card {
        background-color: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        text-decoration: none;
        color: var(--text-main);
        transition: all 0.3s ease;
        box-shadow: var(--card-shadow);
        cursor: pointer;
    }

    .nav-card:hover {
        transform: translateX(6px);
        border-color: var(--accent-cyan);
        background-color: rgba(255, 255, 255, 0.02);
    }

    .nav-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        transition: transform 0.3s ease;
    }

    .nav-card:hover .nav-icon {
        transform: scale(1.1);
    }

    .nav-icon.log { background-color: rgba(13, 202, 240, 0.1); color: var(--accent-cyan); }
    .nav-icon.summary { background-color: rgba(25, 135, 84, 0.1); color: var(--accent-green); }
    .nav-icon.history { background-color: rgba(13, 110, 253, 0.1); color: var(--accent-blue); }

    .nav-details {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .nav-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .nav-desc {
        font-size: 13px;
        color: var(--text-muted);
    }

    .nav-arrow {
        font-size: 16px;
        color: var(--text-muted);
        transition: transform 0.3s ease;
    }

    .nav-card:hover .nav-arrow {
        transform: translateX(4px);
        color: var(--text-main);
    }
</style>
@endsection

@section('content')

    <!-- User Profile Information Edit Form (Full Width) -->
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-title">
            <span>Edit Profile Information</span>
            <span class="text-cyan"><i class="fa-solid fa-user-pen"></i></span>
        </div>
        
        <form action="{{ route('admin.users.update', $user->nic) }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="nic">NIC (Primary ID - Read-Only)</label>
                <input type="text" id="nic" class="form-control" value="{{ $user->nic }}" readonly>
            </div>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="pno">Phone Number</label>
                <input type="text" name="pno" id="pno" class="form-control" value="{{ old('pno', $user->pno) }}" required>
                @error('pno')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="address">Physical Address</label>
                <input type="text" name="address" id="address" class="form-control" value="{{ old('address', $user->address) }}" required>
                @error('address')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="bill_no">Bill Reference Code</label>
                <input type="text" name="bill_no" id="bill_no" class="form-control" value="{{ old('bill_no', $user->bill_no) }}" required>
                @error('bill_no')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Change Password (Optional)</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Leave blank to keep current password">
                @error('password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-save">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </form>
    </div>

@endsection
