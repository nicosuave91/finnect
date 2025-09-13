<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function getUsers(): JsonResponse
    {
        return response()->json(['message' => 'List users']);
    }

    public function createUser(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Create user']);
    }

    public function updateUser(Request $request, string $user): JsonResponse
    {
        return response()->json(['message' => "Update user {$user}"]);
    }

    public function deleteUser(string $user): JsonResponse
    {
        return response()->json(['message' => "Delete user {$user}"]);
    }

    public function getTenants(): JsonResponse
    {
        return response()->json(['message' => 'List tenants']);
    }

    public function createTenant(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Create tenant']);
    }

    public function updateTenant(Request $request, string $tenant): JsonResponse
    {
        return response()->json(['message' => "Update tenant {$tenant}"]);
    }

    public function deleteTenant(string $tenant): JsonResponse
    {
        return response()->json(['message' => "Delete tenant {$tenant}"]);
    }
}
