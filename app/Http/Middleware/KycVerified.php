<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KycVerified
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Check KYC status

        if ($user->kyc_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Your KYC verification is required.',
                'kyc_status' => $user->kyc_status,
            ], 403);
        }

        return $next($request);
    }
}
