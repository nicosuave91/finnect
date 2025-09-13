<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BorrowerController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'List borrowers']);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Create borrower']);
    }

    public function show(string $borrower): JsonResponse
    {
        return response()->json(['message' => "Show borrower {$borrower}"]);
    }

    public function update(Request $request, string $borrower): JsonResponse
    {
        return response()->json(['message' => "Update borrower {$borrower}"]);
    }

    public function destroy(string $borrower): JsonResponse
    {
        return response()->json(['message' => "Delete borrower {$borrower}"]);
    }

    public function verifyIdentity(string $borrower): JsonResponse
    {
        return response()->json(['message' => "Verify identity for borrower {$borrower}"]);
    }

    public function getCreditReport(string $borrower): JsonResponse
    {
        return response()->json(['message' => "Credit report for borrower {$borrower}"]);
    }
}
