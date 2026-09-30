<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RespondsWithFlash;
use App\Models\Transfer;
use App\Models\User;
use App\Services\TransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TransferController extends Controller
{
    use RespondsWithFlash;

    public function __construct(protected TransferService $transfers) {}

    public function index()
    {
        $transfers = Transfer::where('sender_id', Auth::id())
            ->orWhere('receiver_id', Auth::id())
            ->latest()->paginate(10);

        return view('user.transfers.index', compact('transfers'));
    }

    public function create()
    {
        $idempotencyKey = (string) Str::uuid();
        session(['transfer_key' => $idempotencyKey]);

        $recipients = User::role('user')
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();

        return view('user.transfers.create', compact('idempotencyKey', 'recipients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'idempotency_key' => ['required', 'string'],
        ]);

        if ($data['idempotency_key'] !== session('transfer_key')) {
            return $this->error('Session expired. Please reload the form.');
        }

        try {
            $result = $this->transfers->createTransfer(
                sender: Auth::user(),
                receiver: User::findOrFail($data['receiver_id']),
                amount: (int) $data['amount'],
                idempotencyKey: $data['idempotency_key'],
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $e) {
            return $this->error('Transfer failed: ' . $e->getMessage());
        }

        if ($result->status === 'otp_required') {
            return redirect()
                ->route('user.transfers.otp', $result)
                ->with('success', 'OTP required to complete this transfer.')
                ->with('mock_otp', $result->development_otp);
        }

        session()->forget('transfer_key');

        return $this->success(
            'Transfer completed.',
            'user.transfers.index'
        );
    }

    public function showOtp(Transfer $transfer)
    {
        abort_unless($transfer->sender_id === Auth::id(), 403);
        abort_unless($transfer->status === 'otp_required', 404);

        return view('user.transfers.otp', compact('transfer'));
    }

    public function verifyOtp(Request $request, Transfer $transfer)
    {
        abort_unless($transfer->sender_id === Auth::id(), 403);

        $data = $request->validate(['otp' => ['required', 'digits:6']]);

        try {
            $this->transfers->verifyTransferOtp(
                transfer: $transfer,
                sender: Auth::user(),
                otp: $data['otp'],
            );

            session()->forget('transfer_key');

            return $this->success('Transfer completed.', 'user.transfers.index');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
