<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TopUp;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopUpController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    // User Top Up History
    public function index(Request $request): JsonResponse
    {
        $topUps = TopUp::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $topUps->map(function ($topUp) {
                return [
                    'id' => $topUp->id,
                    'amount' => $topUp->amount,
                    'method' => $topUp->method,
                    'reference' => $topUp->reference,
                    'status' => $topUp->status,
                    'processed_by' => $topUp->processed_by,
                    'completed_at' => $topUp->completed_at,
                ];
            })->values(),

            'pagination' => [
                'page' => $topUps->currentPage(),
                'total' => $topUps->total(),
            ],
        ]);
    }

    public function agentHistory(Request $request)
    {
        $topUps = TopUp::where(
            'processed_by',
            $request->user()->id
        )
            ->latest()
            ->get([
                'id',
                'user_id',
                'amount',
                'status',
                'reference',
                'completed_at',
            ]);

        return response()->json([
            'success' => true,
            'data' => $topUps,
        ]);
    }

    //Agent Cash Top Up
    public function agentTopUp(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id',],
            'amount' => ['required', 'integer', 'min:1',],
            'idempotency_key' => ['required', 'string', 'max:255',],
        ]);

        $user = User::findOrFail(
            $validated['user_id']
        );

        $topUp = $this->walletService->agentTopUp(
            agent: $request->user(),
            user: $user,
            amount: $validated['amount'],
            idempotencyKey: $validated['idempotency_key'],
        );

        return response()->json([
            'success' => true,
            'message' => 'Agent cash top up successful.',
            'top_up' => $topUp,
        ], 201);
    }
}
