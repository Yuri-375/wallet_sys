<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use App\Models\FraudReport;
use App\Models\KycDocument;
use App\Models\TopUp;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::role('user')->count(),
            'agents' => User::role('agent')->count(),
            'wallets' => Wallet::count(),
            'total_balance' => (int) Wallet::sum('balance'),
            'frozen_wallets' => Wallet::where('is_frozen', true)->count(),
            'pending_kyc' => KycDocument::where('status', 'pending')->count(),
            'open_fraud' => FraudReport::whereIn('status', ['open', 'investigating'])->count(),
            'transfers_today' => Transfer::whereDate('created_at', today())->count(),
            'topups_today' => TopUp::whereDate('created_at', today())->count(),
            'billpay_today' => BillPayment::whereDate('created_at', today())->count(),
        ];

        $recentTransfers = Transfer::with(['sender', 'receiver'])->latest()->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'recentTransfers'));
    }
}
