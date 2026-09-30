<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // list

    public function index()
    {
        $users = User::role('user')
            ->with('wallet')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully.',
            'data' => $users,
        ]);
    }

    // create
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],

            'phone' => [
                'required',
                'string',
                'unique:users,phone',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make(
                $validated['password']
            ),
            'status' => 'active',
            'kyc_status' => 'pending',
        ]);

        $user->assignRole('user');

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => $user,
        ], 201);
    }

    // show

    public function show(User $user)
    {
        if (! $user->hasRole('user')) {
            return response()->json([
                'success' => false,
                'message' => 'The selected user is not a customer.',
            ], 404);
        }

        $user->load([
            'wallet',
            'kycDocuments',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully.',
            'data' => $user,
        ]);
    }

    // update

    public function update(
        Request $request,
        User $user
    ) {
        if (! $user->hasRole('user')) {
            return response()->json([
                'success' => false,
                'message' => 'The selected user is not a customer.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'phone' => [
                'sometimes',
                'string',
                'unique:users,phone,'.$user->id,
            ],

            'status' => [
                'sometimes',
                'in:active,suspended,blocked',
            ],
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => $user->fresh(),
        ]);
    }

    // Delete

    public function destroy(User $user)
    {
        if (! $user->hasRole('user')) {
            return response()->json([
                'success' => false,
                'message' => 'The selected user is not a customer.',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }

    public function assignedUsers(Request $request): JsonResponse
    {
        $users = User::query()
            ->where('assigned_agent_id', $request->user()->id)
            ->with('wallet')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'kyc_status' => $user->kyc_status,
                    'wallet_balance' => $user->wallet?->balance,
                ];
            })->values(),

            'pagination' => [
                'page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function assignAgent(
        Request $request,
        User $user
    ): JsonResponse {

        $validated = $request->validate([
            'agent_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        $agent = User::role('agent')
            ->findOrFail($validated['agent_id']);

        $user->update([
            'assigned_agent_id' => $agent->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User assigned to agent successfully.',
            'data' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'agent_id' => $agent->id,
                'agent_name' => $agent->name,
            ],
        ]);
    }
}
