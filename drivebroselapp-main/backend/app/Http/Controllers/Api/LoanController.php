<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Borrower;
use App\Services\ComplianceService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Redis;


class LoanController extends Controller
{
    protected $complianceService;
    protected $workflowService;

    public function __construct(ComplianceService $complianceService, WorkflowService $workflowService)
    {
        $this->complianceService = $complianceService;
        $this->workflowService = $workflowService;
    }

    /**
     * Display a listing of loans.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Loan::with(['borrower', 'coBorrower', 'loanOfficer']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('loan_officer_id')) {
            $query->where('loan_officer_id', $request->loan_officer_id);
        }

        if ($request->has('date_from')) {
            $query->where('application_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('application_date', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                  ->orWhereHas('borrower', function ($bq) use ($search) {
                      $bq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $loans = $query->paginate($request->get('per_page', 15));

        return response()->json($loans);
    }

    /**
     * Store a newly created loan.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'borrower_id' => 'required|exists:borrowers,id',
            'co_borrower_id' => 'nullable|exists:borrowers,id',
            'loan_amount' => 'required|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'loan_type' => 'required|string|in:conventional,fha,va,usda,jumbo',
            'property_type' => 'required|string|in:single_family,condo,townhouse,multi_family',
            'occupancy_type' => 'required|string|in:primary,secondary,investment',
            'purpose' => 'required|string|in:purchase,refinance,cash_out',
            'application_date' => 'required|date',
            'loan_data' => 'nullable|array',
            'compliance_data' => 'nullable|array',
        ]);

        $validated['tenant_id'] = tenant()->id;
        $validated['loan_officer_id'] = auth()->id();
        $validated['loan_number'] = $this->generateLoanNumber();
        $validated['status'] = 'application';
        $validated['workflow_data'] = [];

        $loan = Loan::create($validated);

        // Initialize workflow
        $this->workflowService->initializeWorkflow($loan);

        // Run initial compliance check
        $complianceSummary = $this->complianceService->getComplianceSummary($loan);

        return response()->json([
            'loan' => $loan->load(['borrower', 'coBorrower', 'loanOfficer']),
            'compliance_summary' => $complianceSummary,
        ], 201);
    }

    /**
     * Display the specified loan.
     */
    public function show(Loan $loan): JsonResponse
    {
        $loan->load(['borrower', 'coBorrower', 'loanOfficer', 'workflowSteps']);
        
        $complianceSummary = $this->complianceService->getComplianceSummary($loan);
        
        return response()->json([
            'loan' => $loan,
            'compliance_summary' => $complianceSummary,
        ]);
    }

    /**
     * Update the specified loan.
     */
    public function update(Request $request, Loan $loan): JsonResponse
    {
        $validated = $request->validate([
            'loan_amount' => 'sometimes|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'loan_type' => 'sometimes|string|in:conventional,fha,va,usda,jumbo',
            'property_type' => 'sometimes|string|in:single_family,condo,townhouse,multi_family',
            'occupancy_type' => 'sometimes|string|in:primary,secondary,investment',
            'purpose' => 'sometimes|string|in:purchase,refinance,cash_out',
            'closing_date' => 'nullable|date',
            'funding_date' => 'nullable|date',
            'loan_data' => 'nullable|array',
            'compliance_data' => 'nullable|array',
        ]);

        $loan->update($validated);

        // Run compliance check after update
        $complianceSummary = $this->complianceService->getComplianceSummary($loan);

        return response()->json([
            'loan' => $loan->fresh(['borrower', 'coBorrower', 'loanOfficer']),
            'compliance_summary' => $complianceSummary,
        ]);
    }

    /**
     * Remove the specified loan.
     */
    public function destroy(Loan $loan): JsonResponse
    {
        // Only allow deletion of loans in application status
        if ($loan->status !== 'application') {
            return response()->json(['error' => 'Cannot delete loan in current status'], 400);
        }

        $loan->delete();

        return response()->json(['message' => 'Loan deleted successfully']);
    }

    /**
     * Update loan status.
     */
    public function updateStatus(Request $request, Loan $loan): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:application,processing,underwriting,approved,denied,closed,funded',
            'reason' => 'nullable|string|max:1000',
        ]);

        $loan->updateStatus($validated['status'], $validated['reason'] ?? null);

        // Update workflow if needed
        $this->workflowService->updateWorkflowForStatus($loan, $validated['status']);


        // Broadcast status change
        Redis::publish('loan-status', json_encode([
            'loan_id' => $loan->id,
            'status' => $validated['status'],
        ]));


        return response()->json([
            'loan' => $loan->fresh(),
            'message' => 'Status updated successfully',
        ]);
    }

    /**

     * Stream loan status events via Server-Sent Events.
     */
    public function stream()
    {
        return response()->stream(function () {
            Redis::subscribe(['loan-status'], function ($message) {
                echo "event: loan-status\n";
                echo "data: {$message}\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            });
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }

    /**

     * Run compliance check for loan.
     */
    public function runComplianceCheck(Loan $loan): JsonResponse
    {
        $violations = $this->complianceService->runComplianceCheck($loan);
        $summary = $this->complianceService->getComplianceSummary($loan);

        return response()->json([
            'violations' => $violations,
            'summary' => $summary,
        ]);
    }

    /**
     * Get audit trail for loan.
     */
    public function getAuditTrail(Loan $loan): JsonResponse
    {
        $auditTrail = $loan->complianceAudits()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($auditTrail);
    }

    /**
     * Get workflow for loan.
     */
    public function getWorkflow(Loan $loan): JsonResponse
    {
        $workflowSteps = $loan->workflowSteps()
            ->with(['assignedUser', 'completedByUser'])
            ->orderBy('step_order')
            ->get();

        return response()->json([
            'workflow_steps' => $workflowSteps,
            'current_step' => $loan->getCurrentWorkflowStep(),
        ]);
    }

    /**
     * Generate unique loan number.
     */
    private function generateLoanNumber(): string
    {
        $prefix = tenant()->configuration['loan_number_prefix'] ?? 'LN';
        $year = now()->year;
        $month = now()->format('m');
        
        $lastLoan = Loan::where('loan_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('loan_number', 'desc')
            ->first();

        if ($lastLoan) {
            $lastNumber = (int) substr($lastLoan->loan_number, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . $month . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}