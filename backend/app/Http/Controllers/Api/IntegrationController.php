<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Services\IntegrationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IntegrationController extends Controller
{
    private IntegrationService $service;

    public function __construct(IntegrationService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAvailableIntegrations());
    }

    public function getStatus(Integration $integration): JsonResponse
    {
        return response()->json([
            'status' => $integration->status,
            'last_sync_at' => $integration->last_sync_at,
            'error_message' => $integration->error_message,
        ]);
    }

    public function sync(Integration $integration, Request $request): JsonResponse
    {
        $result = $this->service->syncWithIntegration($integration, $request->get('action', 'sync'), $request->all());
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function getLogs(Integration $integration): JsonResponse
    {
        return response()->json($this->service->getIntegrationLogs($integration));
    }
}
