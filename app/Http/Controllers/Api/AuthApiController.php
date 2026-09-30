<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {}

    // Register
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Registration + audit should be one transaction.
        $user = DB::transaction(function () use ($validated) {

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],

                'password' => Hash::make(
                    $validated['password']
                ),

                'status' => 'active',

                'kyc_status' => 'not_submitted',
            ]);

            /*
             * Assign default role.
             */
            $user->assignRole('user');

            /*
             * Audit registration.
             *
             * The new user is both:
             * - actor
             * - target
             */
            $this->auditLogService->log(
                actor: $user,
                action: 'user.registered',
                description: 'User registered a new account',
                model: $user,
                newValues: [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'kyc_status' => $user->kyc_status,
                    'role' => 'user',
                ]
            );

            return $user;
        });

        // Create Passport token after successful registration.
        $token = $user
            ->createToken('wallet-api')
            ->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'user' => new UserResource(
                $user->fresh()
            ),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    // Login

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],

            'password' => ['required', 'string'],
        ]);

        $user = User::where(
            'email',
            $validated['email']
        )->first();

        // Invalid credentials.
        if (
            ! $user ||
            ! Hash::check(
                $validated['password'],
                $user->password
            )
        ) {
            $this->auditLogService->log(
                actor: $user,
                action: 'login.failed',
                description: 'Failed login attempt due to invalid credentials',
                model: $user,
                newValues: [
                    'email' => $validated['email'],
                    'reason' => 'invalid_credentials',
                ]
            );

            throw ValidationException::withMessages([
                'email' => [
                    'The provided credentials are incorrect.',
                ],
            ]);
        }

        // Account must be active.
        if ($user->status !== 'active') {

            $this->auditLogService->log(
                actor: $user,
                action: 'login.failed',
                description: 'Login rejected because account is not active',
                model: $user,
                newValues: [
                    'status' => $user->status,
                    'reason' => 'account_not_active',
                ]
            );

            return response()->json([
                'success' => false,

                'message' =>
                'Your account is not active.',
            ], 403);
        }

        // Create Passport token.
        $token = $user
            ->createToken('wallet-api')
            ->accessToken;

        // Successful login audit.
        $this->auditLogService->log(
            actor: $user,
            action: 'login.success',
            description: 'User logged in successfully',
            model: $user,
            newValues: [
                'status' => $user->status,
            ]
        );

        return response()->json([
            'success' => true,

            'message' => 'Login successful.',

            'user' => new UserResource(
                $user->fresh()
            ),

            'token' => $token,

            'token_type' => 'Bearer',
        ]);
    }

    // Current User
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,

            'user' => new UserResource(
                $user
            ),
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $user = $request->user();

        $token = $user->token();

        // Revoke current Passport token.
        if ($token) {
            $token->revoke();
        }

        // Audit logout.
        $this->auditLogService->log(
            actor: $user,
            action: 'logout',
            description: 'User logged out',
            model: $user
        );

        return response()->json([
            'success' => true,

            'message' =>
            'Logged out successfully.',
        ]);
    }
}
