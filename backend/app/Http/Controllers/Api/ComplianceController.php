<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ComplianceService;
use App\Models\ComplianceAudit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ComplianceController extends Controller
{
    protected $complianceService;

    public function __construct(ComplianceService $complianceService)
    {
        $this->middleware('auth:sanctum');
        $this->complianceService = $complianceService;
    }

    /**
     * Get all supported regulations.
     */
    public function getRegulations(): JsonResponse
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'underwriter'])) {
            abort(403, 'Unauthorized');
        }

        return response()->json(['data' => $this->complianceService->getSupportedRegulations()]);
    }

    /**
     * Get compliance violations.
     */
    public function getViolations(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['admin', 'underwriter'])) {
            abort(403, 'Unauthorized');
        }

        $query = ComplianceAudit::where('audit_type', 'compliance_violation');

        // Apply filters
        if ($request->has('regulation')) {
            $query->where('metadata->regulation', $request->regulation);
        }

        if ($request->has('severity')) {
            $query->where('metadata->severity', $request->severity);
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $violations = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($violations);
    }

    /**
     * Get compliance audit trail.
     */
    public function getAuditTrail(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['admin', 'underwriter'])) {
            abort(403, 'Unauthorized');
        }
        $query = ComplianceAudit::query();

        // Apply filters
        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->has('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        if ($request->has('audit_type')) {
            $query->where('audit_type', $request->audit_type);
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $auditTrail = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json($auditTrail);
    }

    /**
     * Generate compliance report.
     */
    public function generateReport(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['admin', 'underwriter'])) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate([
            'report_type' => 'required|string|in:summary,detailed,violations,audit_trail',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'regulations' => 'nullable|array',
            'format' => 'nullable|string|in:pdf,excel,csv'
        ]);

        $reportData = $this->complianceService->generateReport(
            $validated['report_type'],
            $validated['date_from'],
            $validated['date_to'],
            $validated['regulations'] ?? [],
            $validated['format'] ?? 'pdf'
        );

        return response()->json([
            'message' => 'Report generated successfully',
            'data' => $reportData
        ]);
    }

    /**
     * Download compliance report.
     */
    public function downloadReport(string $reportId): JsonResponse
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'underwriter'])) {
            abort(403, 'Unauthorized');
        }
        $report = $this->complianceService->getReport($reportId);

        if (!$report) {
            return response()->json(['error' => 'Report not found'], 404);
        }

        return response()->download(
            $report['file_path'],
            $report['filename'],
            $report['headers']
        );
    }

    /**
     * Get compliance dashboard data.
     */
    public function getDashboardData(): JsonResponse
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'underwriter'])) {
            abort(403, 'Unauthorized');
        }
        $data = [
            'total_violations' => ComplianceAudit::where('audit_type', 'compliance_violation')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'critical_violations' => ComplianceAudit::where('audit_type', 'compliance_violation')
                ->where('metadata->severity', 'critical')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'high_violations' => ComplianceAudit::where('audit_type', 'compliance_violation')
                ->where('metadata->severity', 'high')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'violations_by_regulation' => ComplianceAudit::where('audit_type', 'compliance_violation')
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('metadata->regulation as regulation, COUNT(*) as count')
                ->groupBy('regulation')
                ->pluck('count', 'regulation')
                ->toArray(),
            'recent_violations' => ComplianceAudit::where('audit_type', 'compliance_violation')
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return response()->json(['data' => $data]);
    }

    /**
     * Get compliance summary for a specific entity.
     */
    public function getEntityComplianceSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['admin', 'underwriter'])) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate([
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer'
        ]);

        $summary = $this->complianceService->getEntityComplianceSummary(
            $validated['entity_type'],
            $validated['entity_id']
        );

        return response()->json(['data' => $summary]);
    }

    /**
     * Update compliance settings.
     */
    public function updateComplianceSettings(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['admin'])) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate([
            'regulation' => 'required|string',
            'settings' => 'required|array'
        ]);

        $tenant = tenant();
        $result = $tenant->updateComplianceSetting(
            $validated['regulation'],
            $validated['settings']
        );

        if ($result) {
            return response()->json([
                'message' => 'Compliance settings updated successfully',
                'data' => $tenant->fresh()
            ]);
        }

        return response()->json(['error' => 'Failed to update compliance settings'], 500);
    }

    /**
     * Get compliance requirements for a regulation.
     */
    public function getComplianceRequirements(string $regulation): JsonResponse
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'underwriter'])) {
            abort(403, 'Unauthorized');
        }
        $requirements = $this->complianceService->getComplianceRequirements($regulation);

        return response()->json(['data' => $requirements]);
    }

    /**
     * Validate compliance for a specific entity.
     */
    public function validateCompliance(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['admin', 'underwriter'])) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate([
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
            'regulation' => 'nullable|string'
        ]);

        $violations = $this->complianceService->validateEntityCompliance(
            $validated['entity_type'],
            $validated['entity_id'],
            $validated['regulation'] ?? null
        );

        return response()->json([
            'data' => [
                'violations' => $violations,
                'is_compliant' => empty($violations),
                'total_violations' => count($violations)
            ]
        ]);
    }
}