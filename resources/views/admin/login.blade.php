<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Water Systems</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-primary: #0a1128;
            --bg-secondary: rgba(16, 31, 66, 0.4);
            --border-color: rgba(255, 255, 255, 0.08);
            --accent-cyan: #0dcaf0;
            --accent-blue: #0d6efd;
            --text-main: #f8f9fa;
            --text-muted: #a0aec0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Ambient Glow Backgrounds */
        body::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(var(--accent-blue), transparent 70%);
            top: -100px;
            left: -100px;
            opacity: 0.3;
            z-index: 1;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(var(--accent-cyan), transparent 70%);
            bottom: -150px;
            right: -150px;
            opacity: 0.2;
            z-index: 1;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            z-index: 10;
        }

        .login-card {
            background: var(--bg-secondary);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px 32px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            text-align: center;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background-color: rgba(13, 202, 240, 0.1);
            border: 1px solid rgba(13, 202, 240, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 28px;
            color: var(--accent-cyan);
            box-shadow: 0 0 20px rgba(13, 202, 240, 0.2);
        }

        .title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 24px;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 46px;
            background-color: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-main);
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-cyan);
            background-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 12px rgba(13, 202, 240, 0.2);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--accent-blue), #1d4ed8);
            color: var(--text-main);
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(13, 110, 253, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .error-message {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            <div class="logo-icon">
                <i class="fa-solid fa-water"></i>
            </div>
            <h1 class="title">Water Admin Panel</h1>
            <p class="subtitle">Please enter administrative passcode to continue.</p>

            @if($errors->has('passcode'))
                <div class="error-message">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first('passcode') }}</span>
                </div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Passcode</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="passcode" class="form-control" placeholder="••••••••" required autocomplete="current-password" autofocus>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    Unlock Dashboard
                </button>
            </form>
        </div>
    </div>

</body>
</html>
