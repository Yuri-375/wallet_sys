<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    // Display a paginated list of audit logs.
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()
            ->with([
                'user:id,name,email',
            ])
            ->latest();

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where(
                'user_id',
                $request->integer('user_id')
            );
        }

        // Filter by HTTP method
        if ($request->filled('method')) {
            $query->where(
                'method',
                strtoupper($request->string('method')->toString())
            );
        }

        // Filter by HTTP status code
        if ($request->filled('status_code')) {
            $query->where(
                'status_code',
                $request->integer('status_code')
            );
        }

        // Filter by route
        if ($request->filled('route')) {
            $route = $request->string('route')->toString();

            $query->where(function ($q) use ($route) {
                $q->where('route_name', 'like', "%{$route}%")
                    ->orWhere('route_uri', 'like', "%{$route}%");
            });
        }

        // Filter by IP address
        if ($request->filled('ip_address')) {
            $query->where(
                'ip_address',
                $request->string('ip_address')->toString()
            );
        }

        // Filter by success / failure
        if ($request->has('success')) {
            $success = filter_var(
                $request->input('success'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            if ($success !== null) {
                $query->where('success', $success);
            }
        }

        // Filter by date        |
        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->input('date_to')
            );
        }

        // General search
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%")
                    ->orWhere('route_uri', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Pagination

        $perPage = $request->integer('per_page', 20);

        // Prevent extremely large API responses.
        $perPage = max(1, min($perPage, 100));

        $auditLogs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Audit logs retrieved successfully.',
            'data' => $auditLogs,
        ]);
    }

    // Display one audit log.
    public function show(AuditLog $auditLog): JsonResponse
    {
        $auditLog->load([
            'user:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Audit log retrieved successfully.',
            'data' => $auditLog,
        ]);
    }

    //Get audit log statistics.
    public function statistics(Request $request): JsonResponse
    {
        $query = AuditLog::query();

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->input('date_to')
            );
        }

        $total = (clone $query)->count();

        $successful = (clone $query)
            ->where('success', true)
            ->count();

        $failed = (clone $query)
            ->where('success', false)
            ->count();

        $clientErrors = (clone $query)
            ->whereBetween('status_code', [400, 499])
            ->count();

        $serverErrors = (clone $query)
            ->whereBetween('status_code', [500, 599])
            ->count();

        $postRequests = (clone $query)
            ->where('method', 'POST')
            ->count();

        $putRequests = (clone $query)
            ->whereIn('method', ['PUT', 'PATCH'])
            ->count();

        $deleteRequests = (clone $query)
            ->where('method', 'DELETE')
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Audit log statistics retrieved successfully.',

            'data' => [
                'total_requests' => $total,
                'successful_requests' => $successful,
                'failed_requests' => $failed,

                'client_errors' => $clientErrors,
                'server_errors' => $serverErrors,

                'post_requests' => $postRequests,
                'update_requests' => $putRequests,
                'delete_requests' => $deleteRequests,
            ],
        ]);
    }

    // Get audit logs for a specific user.
    public function userLogs(
        int $userId,
        Request $request
    ): JsonResponse {
        $perPage = $request->integer('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $auditLogs = AuditLog::query()
            ->with(['user:id,name,email',])
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'User audit logs retrieved successfully.',
            'data' => $auditLogs,
        ]);
    }

    // Get failed API requests.
    public function failed(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 20);
        $perPage = max(1, min($perPage, 100));
        $auditLogs = AuditLog::query()
            ->with(['user:id,name,email'])
            ->where('success', false)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Failed audit logs retrieved successfully.',
            'data' => $auditLogs,
        ]);
    }
}
