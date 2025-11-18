<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login — Split</title>
    <link rel="stylesheet" href="{{ asset('resources/assets/css/login-variants.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="lv-split">
    <div class="lv-split-wrap">
        <aside class="lv-split-left">
            <div class="lv-split-hero">
                <h2>Welcome to SmartHr</h2>
                <p>Manage payroll, tickets, and more — from one place.</p>
            </div>
        </aside>
        <main class="lv-split-right">
            <div class="lv-split-card">
                <h3>Sign in</h3>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="lv-field"><input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required></div>
                    <div class="lv-field"><input type="password" name="password" placeholder="Password" required></div>
                    <div class="lv-actions"><button class="lv-btn" type="submit">Sign In</button></div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
