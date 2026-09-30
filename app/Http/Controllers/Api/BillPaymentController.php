<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillPaymentResource;
use App\Models\BillPayment;
use App\Models\Biller;
use App\Services\BillService;
use Illuminate\Http\Request;

class BillPaymentController extends Controller
{
    public function __construct(
        protected BillService $billService
    ) {}

    // Pay Bill
    public function store(Request $request)
    {
        $validated = $request->validate([
            'biller_id' => ['required', 'integer', 'exists:billers,id',],
            'amount' => ['required', 'integer', 'min:1',],
            'account_number' => ['required', 'string', 'max:255',],
            'idempotency_key' => ['required', 'string', 'max:255',],
        ]);

        try {

            $payment = $this->billService->pay(
                user: $request->user(),
                billerId: $validated['biller_id'],
                amount: $validated['amount'],
                accountNumber: $validated['account_number'],
                idempotencyKey: $validated['idempotency_key'],
            );

            return response()->json([
                'success' => true,
                'message' => 'Bill payment submitted.',
                'bill_payment' => $payment,
            ], 201);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // Bill Payment History
    public function index(Request $request)
    {
        $payments = BillPayment::where(
            'user_id',
            $request->user()->id
        )
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,

            'message' => 'Bill payments retrieved successfully.',

            'data' => BillPaymentResource::collection(
                $payments->items()
            ),

            'pagination' => [
                'current_page' => $payments->currentPage(),

                'per_page' => $payments->perPage(),

                'total' => $payments->total(),

                'last_page' => $payments->lastPage(),
            ],
        ]);
    }
}
