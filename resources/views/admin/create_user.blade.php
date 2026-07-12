@extends('admin.layout')

@section('title', 'Register New User - Water Systems')
@section('header_title')
<a href="{{ route('admin.dashboard') }}" style="color: var(--text-muted); text-decoration: none; margin-right: 8px;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a> / Register User
@endsection

@section('styles')
<style>
    .form-container {
        max-width: 600px;
        margin: 0 auto;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control {
        width: 100%;
        background-color: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 12px 16px;
        color: var(--text-main);
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--accent-cyan);
        box-shadow: 0 0 12px rgba(13, 202, 240, 0.15);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media (max-width: 576px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .btn-submit-form {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 12px;
        background-color: var(--accent-green);
        color: var(--text-main);
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 16px rgba(25, 135, 84, 0.2);
    }

    .btn-submit-form:hover {
        background-color: #146c43;
        box-shadow: 0 6px 20px rgba(25, 135, 84, 0.35);
        transform: translateY(-2px);
    }

    .btn-submit-form:active {
        transform: translateY(0);
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 32px;
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

    <div class="form-container">
        <div class="card">
            <div class="card-title">
                <span>Create User Account</span>
                <span class="text-cyan"><i class="fa-solid fa-user-plus"></i></span>
            </div>

            <form action="{{ route('admin.users.submit') }}" method="POST">
                @csrf
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">NIC (National Identity Card)</label>
                        <input type="number" name="nic" class="form-control" placeholder="e.g. 199912345" required value="{{ old('nic') }}" autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Damsara Wishwajith" required value="{{ old('name') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. damsara@gmail.com" required value="{{ old('email') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="pno" class="form-control" placeholder="e.g. +94771234567" required value="{{ old('pno') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Physical Address</label>
                    <input type="text" name="address" class="form-control" placeholder="e.g. Colombo, Sri Lanka" required value="{{ old('address') }}">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Bill Number (Reference)</label>
                        <input type="text" name="bill_no" class="form-control" placeholder="e.g. B-1002" required value="{{ old('bill_no') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit-form" style="margin-top: 16px;">
                    <i class="fa-solid fa-circle-check"></i> Register User Account
                </button>
            </form>

            <div class="card-footer">
                <a href="{{ route('admin.dashboard') }}" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Cancel and Go Back
                </a>
            </div>
        </div>
    </div>

@endsection
