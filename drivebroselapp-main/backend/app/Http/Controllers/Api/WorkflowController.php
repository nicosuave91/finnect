<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkflowController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'List workflows']);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Create workflow']);
    }

    public function show(string $workflow): JsonResponse
    {
        return response()->json(['message' => "Show workflow {$workflow}"]);
    }

    public function update(Request $request, string $workflow): JsonResponse
    {
        return response()->json(['message' => "Update workflow {$workflow}"]);
    }

    public function destroy(string $workflow): JsonResponse
    {
        return response()->json(['message' => "Delete workflow {$workflow}"]);
    }

    public function addStep(Request $request, string $workflow): JsonResponse
    {
        return response()->json(['message' => "Add step to workflow {$workflow}"]);
    }

    public function updateStep(Request $request, string $workflow, string $step): JsonResponse
    {
        return response()->json(['message' => "Update step {$step} in workflow {$workflow}"]);
    }

    public function removeStep(string $workflow, string $step): JsonResponse
    {
        return response()->json(['message' => "Remove step {$step} from workflow {$workflow}"]);
    }
}
