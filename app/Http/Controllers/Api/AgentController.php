<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgentController extends Controller
{
    // List Agents

    public function index()
    {
        $agents = User::role('agent')
            ->with('wallet')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Agents retrieved successfully.',
            'data' => $agents,
        ]);
    }

    // Create Agent

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $agent = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make(
                $validated['password']
            ),
            'status' => 'active',
            'kyc_status' => 'approved',
        ]);

        $agent->assignRole('agent');

        return response()->json([
            'success' => true,
            'message' => 'Agent created successfully.',
            'data' => $agent,
        ], 201);
    }

    // Show Agent
    public function show(User $agent)
    {
        if (! $agent->hasRole('agent')) {
            return response()->json([
                'success' => false,
                'message' => 'The selected user is not an agent.',
            ], 404);
        }

        $agent->load('wallet');

        return response()->json([
            'success' => true,
            'message' => 'Agent retrieved successfully.',
            'data' => $agent,
        ]);
    }

    // Update Agent

    public function update(
        Request $request,
        User $agent
    ) {
        if (! $agent->hasRole('agent')) {
            return response()->json([
                'success' => false,
                'message' => 'The selected user is not an agent.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'unique:users,phone,' . $agent->id],
            'status' => ['sometimes', 'in:active,suspended,blocked'],
        ]);

        $agent->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Agent updated successfully.',
            'data' => $agent->fresh(),
        ]);
    }

    // Delete Agent

    public function destroy(User $agent)
    {
        if (! $agent->hasRole('agent')) {
            return response()->json([
                'success' => false,
                'message' => 'The selected user is not an agent.',
            ], 404);
        }

        $agent->delete();

        return response()->json([
            'success' => true,
            'message' => 'Agent deleted successfully.',
        ]);
    }
}
