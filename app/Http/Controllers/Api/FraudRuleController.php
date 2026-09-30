<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FraudRule;
use Illuminate\Http\Request;

class FraudRuleController extends Controller
{
    // List fraud rules

    public function index()
    {
        $fraudRules = FraudRule::latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'Fraud rules retrieved successfully.',
            'data' => $fraudRules,
        ]);
    }

    // Create fraud rule
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255',],
            'description' => ['nullable', 'string',],
            'type' => ['required', 'string', 'max:100',],
            'threshold' => ['nullable', 'numeric', 'min:0',],
            'is_active' => ['sometimes', 'boolean',],
        ]);

        $rule = FraudRule::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fraud rule created successfully.',
            'data' => $rule,
        ], 201);
    }

    // Show fraud rule
    public function show(FraudRule $fraudRule)
    {
        return response()->json([
            'success' => true,
            'data' => $fraudRule,
        ]);
    }

    // Update fraud rule
    public function update(
        Request $request,
        FraudRule $fraudRule
    ) {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255',],
            'description' => ['nullable', 'string',],
            'type' => ['sometimes', 'string', 'max:100',],
            'threshold' => ['nullable', 'numeric', 'min:0',],
            'is_active' => ['sometimes', 'boolean',],

        ]);

        $fraudRule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fraud rule updated successfully.',
            'data' => $fraudRule->fresh(),
        ]);
    }

    // Delete fraud rule
    public function destroy(FraudRule $fraudRule)
    {
        $fraudRule->delete();
        return response()->json([
            'success' => true,
            'message' => 'Fraud rule deleted successfully.',
        ]);
    }
}
