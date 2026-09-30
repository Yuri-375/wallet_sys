<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        $recentTransactions = $wallet
            ? WalletTransaction::where('wallet_id', $wallet->id)->latest()->limit(10)->get()
            : collect();

        return view('user.dashboard', compact('user', 'wallet', 'recentTransactions'));
    }
}
