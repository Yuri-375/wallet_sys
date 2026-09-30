<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use Illuminate\Http\Request;

class BillPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = BillPayment::with(['user', 'biller'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('admin.bill-payments.index', compact('payments'));
    }
}
