<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'List documents']);
    }

    public function upload(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Upload document']);
    }

    public function show(string $document): JsonResponse
    {
        return response()->json(['message' => "Show document {$document}"]);
    }

    public function update(Request $request, string $document): JsonResponse
    {
        return response()->json(['message' => "Update document {$document}"]);
    }

    public function destroy(string $document): JsonResponse
    {
        return response()->json(['message' => "Delete document {$document}"]);
    }

    public function download(string $document): JsonResponse
    {
        return response()->json(['message' => "Download document {$document}"]);
    }

    public function verify(string $document): JsonResponse
    {
        return response()->json(['message' => "Verify document {$document}"]);
    }
}
