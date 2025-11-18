<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login — Classic</title>
    <link rel="stylesheet" href="{{ asset('resources/assets/css/login-variants.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="lv-classic">
    <div class="lv-container">
        <div class="lv-card">
            <div class="lv-brand"> <img src="{{ asset('images/logo.png') }}" alt="Logo" class="lv-logo"> <h2>SmartHr</h2></div>
            <form method="POST" action="{{ route('login') }}" class="lv-form">
                @csrf
                <div class="lv-field">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="lv-field">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="lv-actions">
                    <button class="lv-btn" type="submit">Sign In</button>
                </div>
                <div class="lv-meta">
                    <label><input type="checkbox" name="remember"> Remember me</label>
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                </div>
            </form>
        </div>
        <footer class="lv-footer">© {{ date('Y') }} SmartHr</footer>
    </div>
</body>
</html>
