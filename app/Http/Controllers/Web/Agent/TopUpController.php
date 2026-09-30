<?php

namespace App\Http\Controllers\Web\Agent;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RespondsWithFlash;
use App\Models\TopUp;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TopUpController extends Controller
{
    use RespondsWithFlash;

    public function __construct(protected WalletService $wallets) {}

    public function index()
    {
        $topUps = TopUp::with(['user', 'processor'])
            ->where('processed_by', Auth::id())
            ->latest()->paginate(20);

        return view('agent.topups.index', compact('topUps'));
    }

    public function create(Request $request)
    {
        $users = User::role('user')->where('kyc_status', 'approved')->orderBy('name')->get();

        $idempotencyKey = (string) Str::uuid();
        session(['topup_key' => $idempotencyKey]);

        $preselectedId = $request->integer('user_id') ?: null;

        return view('agent.topups.create', compact('users', 'idempotencyKey', 'preselectedId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'idempotency_key' => ['required', 'string'],
        ]);

        if ($data['idempotency_key'] !== session('topup_key')) {
            return $this->error('Session expired. Please reload the form.');
        }

        try {
            $this->wallets->agentTopUp(
                agent: Auth::user(),
                user: User::findOrFail($data['user_id']),
                amount: (int) $data['amount'],
                idempotencyKey: $data['idempotency_key'],
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $e) {
            return $this->error('Top-up failed: '.$e->getMessage());
        }

        session()->forget('topup_key');

        return $this->success('Top-up successful.', 'agent.topups.index');
    }
}
