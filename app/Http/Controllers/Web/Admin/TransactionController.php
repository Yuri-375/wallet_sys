<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = WalletTransaction::with('wallet.user')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }
        if ($request->filled('direction')) {
            $query->where('direction', $request->string('direction'));
        }
        if ($request->filled('reference')) {
            $query->where('reference', 'like', '%'.$request->string('reference').'%');
        }

        $transactions = $query->paginate(10)->withQueryString();

        return view('admin.transactions.index', compact('transactions'));
    }
}
