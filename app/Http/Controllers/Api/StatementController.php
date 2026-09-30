<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Statement;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class StatementController extends Controller
{
    // Own Statements
    public function index(Request $request)
    {
        $wallet = $request->user()->wallet;

        if (! $wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found.',
            ], 404);
        }

        $statements = WalletTransaction::where(
            'wallet_id',
            $wallet->id
        )
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Statements retrieved successfully.',
            'data' => $statements->items(),
            'pagination' => [
                'current_page' => $statements->currentPage(),
                'per_page' => $statements->perPage(),
                'total' => $statements->total(),
                'last_page' => $statements->lastPage(),
            ],
        ]);
    }

    // All Statements - Admin
    public function all()
    {
        $statements = Statement::with([
            'user',
            'wallet',
        ])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $statements,
        ]);
    }

    // Generate Statement
    // public function generate(Request $request)
    // {
    //     $validated = $request->validate([
    //         'user_id' => [
    //             'required',
    //             'integer',
    //             'exists:users,id',
    //         ],

    //         'period_start' => [
    //             'required',
    //             'date',
    //         ],

    //         'period_end' => [
    //             'required',
    //             'date',
    //             'after_or_equal:period_start',
    //         ],
    //     ]);

    //     // In production:
    //     // Dispatch GenerateStatement job here.

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Statement generation has been queued.',
    //     ]);
    // }
}
