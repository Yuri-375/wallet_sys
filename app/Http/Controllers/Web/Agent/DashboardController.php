<?php

namespace App\Http\Controllers\Web\Agent;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Models\TopUp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $agentId = Auth::id();

        $pendingKyc = KycDocument::where('status', 'pending')->count();
        $assigned = User::where('assigned_agent_id', $agentId)->count();
        $todayTopUps = TopUp::where('processed_by', $agentId)
            ->whereDate('created_at', today())
            ->count();

        return view('agent.dashboard', compact('pendingKyc', 'assigned', 'todayTopUps'));
    }
}
