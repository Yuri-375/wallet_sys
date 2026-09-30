<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Register</title>
        <style>
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
            body {
                font-family: Arial, sans-serif;
                background: #f4f6f9;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .register-container {
                width: 100%;
                max-width: 450px;
                background: white;
                padding: 35px;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }
            .register-container h1 {
                text-align: center;
                margin-bottom: 10px;
                color: #333;
            }

            .register-container .subtitle {
                text-align: center;
                color: #777;
                margin-bottom: 25px;
            }

            .form-group {
                margin-bottom: 18px;
            }
            .form-group label {
                display: block;
                margin-bottom: 7px;
                font-weight: bold;
                color: #333;
            }

            .form-group input {
                width: 100%;
                padding: 12px;
                border: 1px solid #ccc;
                border-radius: 6px;
                font-size: 15px;
            }

            .form-group input:focus {
                outline: none;
                border-color: #007bff;
            }

            .error {
                color: #dc3545;
                font-size: 13px;
                margin-top: 5px;
            }

            .alert {
                padding: 12px;
                border-radius: 6px;
                margin-bottom: 20px;
                font-size: 14px;
            }

            .alert-error {
                background: #f8d7da;
                color: #842029;
                border: 1px solid #f5c2c7;
            }
            .register-btn {
                width: 100%;
                padding: 13px;
                border: none;
                border-radius: 6px;
                background: #007bff;
                color: white;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
            }
            .register-btn:hover {
                background: #0056b3;
            }
            .login-link {
                text-align: center;
                margin-top: 20px;
                color: #666;
            }
            .login-link a {
                color: #007bff;
                text-decoration: none;
                font-weight: bold;
            }
            .login-link a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="register-container">
            <h1>Create Account</h1>
            <p class="subtitle">Register for your wallet account</p>
            {{-- Display validation errors --}} @if ($errors->any())
            <div class="alert alert-error">
                <strong>Please fix the following errors:</strong>
                <ul style="margin-top: 8px; padding-left: 20px">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif {{-- Register Form --}}
            <form action="{{ route('register') }}" method="POST">
                @csrf {{-- Name --}}
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Enter your full name"
                        required
                        autofocus
                    />
                    @error('name')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                {{-- Email --}}
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter your email"
                        required
                    />
                    @error('email')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                {{-- Phone --}}
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="{{ old('phone') }}"
                        placeholder="Enter your phone number"
                        required
                    />
                    @error('phone')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                {{-- Password --}}
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    />
                    @error('password')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                {{-- Confirm Password --}}
                <div class="form-group">
                    <label for="password_confirmation">
                        Confirm Password
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Confirm your password"
                        required
                    />
                </div>
                {{-- Submit --}}
                <button type="submit" class="register-btn">Register</button>
            </form>
            {{-- Login Link --}}
            <div class="login-link">
                Already have an account?
                <a href="{{ route('login') }}"> Login </a>
            </div>
        </div>
    </body>
</html>
