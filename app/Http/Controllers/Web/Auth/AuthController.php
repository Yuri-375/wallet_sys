<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Show Register Form
    public function showRegister()
    {
        return view('auth.register');
    }

    // Register
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),

            'status' => 'active',
            'kyc_status' => 'not_submitted',
        ]);

        $user->assignRole('user');

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('user.dashboard')
            ->with('success', 'Registration successful. Please complete your KYC.');
    }

    // Login Form
    public function showLogin()
    {
        return view('auth.login');
    }

    // Login

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {

            throw ValidationException::withMessages([
                'email' => 'The email or password is incorrect.',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->hasRole('admin')) {

            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Welcome back, Admin!');
        }

        if ($user->hasRole('agent')) {

            return redirect()
                ->route('agent.dashboard')
                ->with('success', 'Welcome back, Agent!');
        }

        return redirect()
            ->route('user.dashboard')
            ->with('success', 'Welcome back!');
    }

    // LogOut
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}
