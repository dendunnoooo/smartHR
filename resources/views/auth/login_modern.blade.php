<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login — Modern</title>
    <link rel="stylesheet" href="{{ asset('resources/assets/css/login-variants.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="lv-modern">
    <div class="lv-modern-bg">
        <div class="lv-modern-panel">
            <div class="lv-modern-brand">
                <img src="{{ asset('images/logo.png') }}" alt="Logo">
                <h1>Welcome back</h1>
                <p>Sign in to continue to SmartHr</p>
            </div>
            <form method="POST" action="{{ route('login') }}" class="lv-modern-form">
                @csrf
                <div class="floating">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                    <label for="email">Email</label>
                </div>
                <div class="floating">
                    <input id="password" type="password" name="password" required>
                    <label for="password">Password</label>
                </div>
                <button class="lv-btn lv-btn-primary" type="submit">Sign in</button>
                <div class="lv-socials">
                    <button type="button" class="social google"><i class="fa-brands fa-google"></i> Google</button>
                    <button type="button" class="social facebook"><i class="fa-brands fa-facebook"></i> Facebook</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
