<?php

namespace App\Http\Controllers\Web\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::role('user')
            ->where(fn($q) => $q->where('assigned_agent_id', Auth::id())
                ->orWhereNull('assigned_agent_id'))
            ->with('wallet')
            ->latest()
            ->paginate(20);

        return view('agent.users.index', compact('users'));
    }

    public function show(User $user)
    {
        abort_unless($user->hasRole('user'), 404);
        $user->load(['wallet', 'kycDocuments', 'topUps' => fn($q) => $q->latest()->limit(5)]);
        return view('agent.users.show', compact('user'));
    }
}
