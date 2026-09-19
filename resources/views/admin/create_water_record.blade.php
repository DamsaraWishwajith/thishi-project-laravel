@extends('admin.layout')

@section('title')
Log Reading - {{ $user->name }}
@endsection

@section('header_title')
<a href="{{ route('admin.dashboard') }}" style="color: var(--text-muted); text-decoration: none; margin-right: 8px;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a> / 
<a href="{{ route('admin.users.detail', $user->nic) }}" style="color: var(--text-muted); text-decoration: none; margin-right: 8px;">Profile</a> / Log Consumption
@endsection

@section('styles')
<style>
    .form-container {
        max-width: 500px;
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

    .btn-submit-form {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 12px;
        background-color: var(--accent-blue);
        color: var(--text-main);
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 16px rgba(13, 110, 253, 0.2);
    }

    .btn-submit-form:hover {
        background-color: #1d4ed8;
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.35);
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
                <span>Log Daily Water Reading</span>
                <span class="text-cyan"><i class="fa-solid fa-droplet"></i></span>
            </div>

            <form action="{{ route('admin.water-records.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="nic" value="{{ $user->nic }}">

                <div class="form-group">
                    <label class="form-label">Reading Date</label>
                    <input type="date" name="date" class="form-control" required value="{{ date('Y-m-d') }}" autofocus>
                </div>

                <input type="hidden" name="water_rate" value="50.00">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Consumption (Units / Points)</label>
                        <input type="number" id="units_input" name="points" step="0.01" min="0" class="form-control" placeholder="e.g. 1.50" required oninput="if(this.value!=='') document.getElementById('liters_input').value=(parseFloat(this.value)*1000).toFixed(2)">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Consumption (Liters)</label>
                        <input type="number" id="liters_input" name="liters" step="0.01" min="0" class="form-control" placeholder="e.g. 1500.00" oninput="if(this.value!=='') document.getElementById('units_input').value=(parseFloat(this.value)/1000).toFixed(2)">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label">Override Bill Rs. (Optional)</label>
                    <input type="number" name="bill" step="0.01" min="0" class="form-control" placeholder="Leave empty for auto-calc (Units * Rate)">
                </div>

                <button type="submit" class="btn-submit-form">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Save Daily Reading
                </button>
            </form>

            <div class="card-footer">
                <a href="{{ route('admin.users.detail', $user->nic) }}" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Cancel and Return
                </a>
            </div>
        </div>
    </div>

@endsection
