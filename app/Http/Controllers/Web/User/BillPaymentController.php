<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RespondsWithFlash;
use App\Models\Biller;
use App\Models\BillPayment;
use App\Services\BillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class BillPaymentController extends Controller
{
    use RespondsWithFlash;

    public function __construct(
        protected BillService $bills
    ) {}

    // Bill payment history
    public function index()
    {
        $payments = BillPayment::with('biller')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view(
            'user.bills.index',
            compact('payments')
        );
    }

    // Show payment form
    public function create()
    {
        $billers = Biller::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Generate idempotency key
        $idempotencyKey = (string) Str::uuid();

        session([
            'bill_key' => $idempotencyKey,
        ]);

        return view(
            'user.bills.create',
            compact(
                'billers',
                'idempotencyKey'
            )
        );
    }

    // Submit bill payment
    public function store(Request $request)
    {
        $data = $request->validate([
            'biller_id' => ['required', 'integer', 'exists:billers,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'account_number' => ['required', 'string', 'max:255'],
            'idempotency_key' => ['required', 'string', 'max:100'],
        ]);

        // Validate session idempotency key

        if (
            ! hash_equals(
                (string) session('bill_key'),
                (string) $data['idempotency_key']
            )
        ) {
            return $this->error(
                'Session expired. Please reload the payment form.'
            );
        }

        try {

            $payment = $this->bills->pay(
                user: Auth::user(),
                billerId: (int) $data['biller_id'],
                amount: (int) $data['amount'],
                accountNumber: $data['account_number'],
                idempotencyKey: $data['idempotency_key'],
            );
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (RuntimeException $e) {

            // Provider timeout / uncertain result
            return $this->error(
                $e->getMessage()
            );
        } catch (Throwable $e) {

            report($e);

            return $this->error(
                'Unable to process the bill payment. Please try again.'
            );
        }

        // Payment was successfully processed or created
        session()->forget('bill_key');

        //Give appropriate message based on status

        if ($payment->status === 'completed') {
            return $this->success(
                'Bill payment completed successfully.',
                'user.bills.index'
            );
        }

        if ($payment->status === 'failed') {
            return $this->error(
                'Bill payment failed. Your wallet has been refunded.'
            );
        }

        // Pending
        return $this->success(
            'Bill payment is pending verification. Please check your payment history.',
            'user.bills.index'
        );
    }
}
