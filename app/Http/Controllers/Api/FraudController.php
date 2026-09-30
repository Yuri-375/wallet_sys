<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FraudReport;
use App\Services\FraudService;
use Illuminate\Http\Request;

class FraudController extends Controller
{
    public function __construct(
        private FraudService $fraudService
    ) {}

    // list
    public function index()
    {
        $reports = FraudReport::with([
            'user:id,name,email',
            'wallet:id,user_id,balance,is_frozen',
            'fraudRule:id,name,code,description',
            'transaction:id,wallet_id,reference,amount,direction,status',
            'assignedUser:id,name,email',
        ])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Fraud reports retrieved successfully.',
            'data' => $reports,
        ]);
    }

    // show
    public function show(FraudReport $fraudReport)
    {
        $fraudReport->load([
            'user:id,name,email',
            'wallet:id,user_id,balance,is_frozen',
            'fraudRule:id,name,code,description',
            'transaction',
            'assignedUser:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fraud report retrieved successfully.',
            'data' => $fraudReport,
        ]);
    }

    // Investigate

    public function investigate(
        Request $request,
        FraudReport $fraudReport
    ) {
        $admin = $request->user();

        $report = $this->fraudService->investigate(
            report: $fraudReport,
            admin: $admin
        );

        return response()->json([
            'success' => true,
            'message' => 'Fraud report is now under investigation.',
            'data' => $report,
        ]);
    }

    // resolve

    public function resolve(
        Request $request,
        FraudReport $fraudReport
    ) {
        $validated = $request->validate([
            'resolution_notes' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $admin = $request->user();

        $report = $this->fraudService->resolve(
            report: $fraudReport,
            admin: $admin,
            resolution: $validated['resolution_notes']
        );

        return response()->json([
            'success' => true,
            'message' => 'Fraud report resolved successfully.',
            'data' => $report,
        ]);
    }
}
