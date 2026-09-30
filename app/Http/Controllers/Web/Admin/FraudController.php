<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RespondsWithFlash;
use App\Models\FraudReport;
use App\Services\FraudService;
use Illuminate\Http\Request;

class FraudController extends Controller
{
    use RespondsWithFlash;

    public function __construct(protected FraudService $fraud) {}

    public function index(Request $request)
    {
        $query = FraudReport::with(['user', 'fraudRule'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->string('severity'));
        }

        $reports = $query->paginate(20)->withQueryString();
        return view('admin.fraud.index', compact('reports'));
    }

    public function show(FraudReport $report)
    {
        $report->load(['user', 'wallet', 'fraudRule', 'assignedUser']);
        return view('admin.fraud.show', compact('report'));
    }

    public function investigate(Request $request, FraudReport $report)
    {
        $this->fraud->investigate($report, $request->user());
        return $this->success('Report under investigation.', 'admin.fraud.show', ['report' => $report]);
    }

    public function resolve(Request $request, FraudReport $report)
    {
        $data = $request->validate([
            'resolution_notes' => ['required', 'string', 'max:2000'],
        ]);

        $this->fraud->resolve($report, $request->user(), $data['resolution_notes']);
        return $this->success('Report resolved.', 'admin.fraud.show', ['report' => $report]);
    }
}
