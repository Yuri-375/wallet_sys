<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(
        private WalletService $walletService
    ) {}

    // Display the authenticated user's wallet.
    public function index()
    {
        $user = Auth::user();
        abort_unless($user, 401);
        $wallet = $this->walletService->getWallet($user->id);
        abort_unless($wallet, 404, 'Wallet not found.');
        return view('user.wallet.index', compact('wallet'));
    }

    // Display wallet details.
    public function show()
    {
        $user = Auth::user();
        abort_unless($user, 401);
        $wallet = $user->wallet;
        abort_unless($wallet, 404, 'Wallet not found.');
        return view('user.wallet.show', compact('wallet'));
    }

    /**
     * Display wallet transactions.
     */
    public function transactions()
    {
        $user = Auth::user();
        abort_unless($user, 401);
        $wallet = $user->wallet;
        abort_unless($wallet, 404, 'Wallet not found.');

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->latest()
            ->paginate(10);
        return view('user.wallet.transactions', compact('wallet', 'transactions'));
    }
}
