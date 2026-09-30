<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    // Show Own Wallet
    public function show(Request $request)
    {
        $wallet = $request->user()
            ->wallet;

        if (! $wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'wallet' => $wallet,
        ]);
    }

    public function showUserWallet(User $user)
    {
        $wallet = Wallet::where('user_id', $user->id)->first();

        if (! $wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'wallet' => $wallet,
        ]);
    }

    // Freeze Wallet
    public function freeze(
        Request $request,
        Wallet $wallet
    ) {
        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:500',
            ],
        ]);

        $wallet->update([
            'is_frozen' => true,
            'frozen_at' => now(),
            'freeze_reason' => $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wallet frozen successfully.',
            'wallet' => $wallet->fresh(),
        ]);
    }

    // Unfreeze Wallet
    public function unfreeze(
        Request $request,
        Wallet $wallet
    ) {
        $wallet->update([
            'is_frozen' => false,
            'frozen_at' => null,
            'freeze_reason' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wallet unfrozen successfully.',
            'wallet' => $wallet->fresh(),
        ]);
    }
}
