<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RespondsWithFlash;
use App\Jobs\GenerateStatement;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StatementController extends Controller
{
    use RespondsWithFlash;

    public function index()
    {
        $statements = Statement::with(['user', 'wallet'])->latest()->paginate(20);

        return view('admin.statements.index', compact('statements'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        $user = User::findOrFail($data['user_id']);
        if (! $user->wallet) {
            throw ValidationException::withMessages(['user_id' => 'User has no wallet.']);
        }

        GenerateStatement::dispatch(
            $user->id,
            $data['period_start'],
            $data['period_end'],
        )->onQueue('pdf');

        return $this->success('Statement generation queued.', 'admin.statements.index');
    }
}
