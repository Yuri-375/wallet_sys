<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    // Own Transactions

    public function index(Request $request)
    {
        $wallet = $request->user()->wallet;

        if (! $wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found.',
            ], 404);
        }

        $transactions = WalletTransaction::where(
            'wallet_id',
            $wallet->id
        )
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    // All Transactions - Admin

    public function all()
    {
        $transactions = WalletTransaction::with([
            'wallet.user',
        ])
            ->latest()
            ->paginate(20);

        return TransactionResource::collection($transactions);
    }
}
