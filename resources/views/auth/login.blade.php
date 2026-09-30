<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh;">
<form method="POST" action="{{ route('login.attempt') }}" class="bg-white p-4 rounded shadow" style="width:360px;">
    @csrf
    <h1 class="h4 mb-3">Login</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" value="{{ old('email') }}" class="form-control" required autofocus>
    </div>

    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <div class="form-check mb-3">
        <input type="checkbox" name="remember" id="remember" class="form-check-input">
        <label for="remember" class="form-check-label">Remember me</label>
    </div>

    <button class="btn btn-primary w-100">Login</button>

    <div class="register-link">
            Don't have an account?
            <a href="{{ route('register') }}">Register</a>
    </div>
</form>
    
</body>
</html>