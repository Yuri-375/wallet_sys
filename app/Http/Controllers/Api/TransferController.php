<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransferResource;
use App\Models\Transfer;
use App\Models\User;
use App\Services\TransferService;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function __construct(
        protected TransferService $transferService
    ) {}

    // Create transfer
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'idempotency_key' => ['required', 'string', 'max:255'],
        ]);

        try {
            $receiver = User::findOrFail($validated['receiver_id']);

            $transfer = $this->transferService->createTransfer(
                sender: $request->user(),
                receiver: $receiver,
                amount: (int) $validated['amount'],
                idempotencyKey: $validated['idempotency_key']
            );

            return response()->json([
                'success' => true,
                'message' => $transfer->requiresOtp()
                    ? 'Transfer requires OTP verification.'
                    : 'Transfer completed successfully.',
                'data' => [
                    'transfer' => new TransferResource($transfer),
                    'requires_otp' => $transfer->requiresOtp(),
                    'mock_otp' => $transfer->development_otp ?? null,
                    'flags' => [],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // Verify transfer OTP
    public function verifyOtp(
        Request $request,
        Transfer $transfer
    ) {
        $validated = $request->validate([
            'otp' => [
                'required',
                'digits:6',
            ],
        ]);

        $completedTransfer = $this->transferService
            ->verifyTransferOtp(
                transfer: $transfer,
                sender: $request->user(),
                otp: $validated['otp']
            );

        return response()->json([
            'success' => true,
            'message' => 'OTP verified. Transfer completed successfully.',
            'transfer' => $completedTransfer,
        ]);
    }
}
