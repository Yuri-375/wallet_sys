<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\KycDocument;
use App\Models\User;
use App\Services\KycService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KycController extends Controller
{
    public function __construct(
        protected KycService $kycService
    ) {}

    // Submit a KYC document.
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document_type' => ['required', 'string', 'in:national_id,passport,driving_license'],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $document = $this->kycService->submit(
            user: $request->user(),
            documentType: $validated['document_type'],
            file: $request->file('document'),
        );

        return response()->json([
            'success' => true,
            'message' => 'KYC document submitted successfully.',
            'data' => $document,
        ], 201);
    }

    // Show user's KYC documents.
    public function index(Request $request): JsonResponse
    {
        $documents = KycDocument::where(
            'user_id',
            $request->user()->id
        )
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents,
        ]);
    }

    // Show user's KYC status.
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'data' => [
                'kyc_status' => $user->kyc_status,
            ],
        ]);
    }

    // Show users with pending KYC.
    public function pending(): JsonResponse
    {
        $users = User::where('kyc_status', 'pending')->get();
        if (! $users) {
            return response()->json([
                'success' => false,
                'message' => 'No pending KYC user found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'users' => UserResource::collection($users),
        ]);
    }

    // Show a specific user's KYC documents.
    public function show(User $user): JsonResponse
    {
        $documents = KycDocument::where(
            'user_id',
            $user->id
        )
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'documents' => $documents,
            ],
        ]);
    }

    // Approve user's KYC.
    public function approve(
        Request $request,
        User $user
    ): JsonResponse {

        $user = $this->kycService->approve(
            user: $user,
            verifier: $request->user(),
        );
        return response()->json([
            'success' => true,
            'message' => 'KYC approved successfully.',
            'data' => $user,
        ]);
    }

    // Reject user's KYC.
    public function reject(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $user = $this->kycService->reject(
            user: $user,
            verifier: $request->user(),
            reason: $validated['rejection_reason'],
        );

        return response()->json([
            'success' => true,
            'message' => 'KYC rejected successfully.',
            'data' => $user,
        ]);
    }
}
