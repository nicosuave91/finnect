<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function getStats(): JsonResponse
    {
        return response()->json(['message' => 'Dashboard stats']);
    }

    public function getLoansSummary(): JsonResponse
    {
        return response()->json(['message' => 'Loans summary']);
    }

    public function getComplianceSummary(): JsonResponse
    {
        return response()->json(['message' => 'Compliance summary']);
    }

    public function getWorkflowSummary(): JsonResponse
    {
        return response()->json(['message' => 'Workflow summary']);
    }
}
