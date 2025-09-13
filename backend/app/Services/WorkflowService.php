<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\WorkflowStep;
use App\Models\ComplianceAudit;
use Illuminate\Support\Facades\DB;

class WorkflowService
{
    /**
     * Initialize workflow for a loan.
     */
    public function initializeWorkflow(Loan $loan): void
    {
        $workflowSteps = $this->getDefaultWorkflowSteps($loan);
        
        foreach ($workflowSteps as $index => $stepData) {
            WorkflowStep::create([
                'tenant_id' => $loan->tenant_id,
                'loan_id' => $loan->id,
                'step_name' => $stepData['name'],
                'step_type' => $stepData['type'],
                'step_order' => $index + 1,
                'is_completed' => false,
                'is_required' => $stepData['required'],
                'completion_criteria' => $stepData['criteria'],
                'assigned_to' => $stepData['assigned_to'],
                'due_date' => $stepData['due_date'],
                'compliance_requirements' => $stepData['compliance_requirements']
            ]);
        }

        // Log workflow initialization
        ComplianceAudit::createAudit(
            'workflow_initialized',
            'loan',
            $loan->id,
            'workflow_initialized',
            null,
            ['workflow_steps' => count($workflowSteps)],
            ['loan_id' => $loan->id]
        );
    }

    /**
     * Update workflow based on loan status change.
     */
    public function updateWorkflowForStatus(Loan $loan, string $status): void
    {
        $workflowSteps = $loan->workflowSteps;
        
        foreach ($workflowSteps as $step) {
            $shouldComplete = $this->shouldCompleteStepForStatus($step, $status);
            
            if ($shouldComplete && !$step->is_completed) {
                $step->markCompleted();
                
                // Log step completion
                ComplianceAudit::createAudit(
                    'workflow_step_completed',
                    'workflow_step',
                    $step->id,
                    'step_completed',
                    null,
                    ['step_name' => $step->step_name, 'loan_status' => $status],
                    ['loan_id' => $loan->id]
                );
            }
        }
    }

    /**
     * Get default workflow steps for a loan.
     */
    private function getDefaultWorkflowSteps(Loan $loan): array
    {
        $applicationDate = $loan->application_date;
        
        return [
            [
                'name' => 'Application Received',
                'type' => 'system',
                'required' => true,
                'criteria' => ['application_submitted' => true],
                'assigned_to' => null,
                'due_date' => $applicationDate->addHours(1),
                'compliance_requirements' => []
            ],
            [
                'name' => 'Initial Document Collection',
                'type' => 'manual',
                'required' => true,
                'criteria' => ['documents_uploaded' => 5],
                'assigned_to' => $loan->loan_officer_id,
                'due_date' => $applicationDate->addDays(3),
                'compliance_requirements' => ['TRID' => ['loan_estimate' => true]]
            ],
            [
                'name' => 'Credit Check',
                'type' => 'integration',
                'required' => true,
                'criteria' => ['credit_report_obtained' => true],
                'assigned_to' => $loan->loan_officer_id,
                'due_date' => $applicationDate->addDays(5),
                'compliance_requirements' => ['FCRA' => ['credit_report_obtained' => true]]
            ],
            [
                'name' => 'Income Verification',
                'type' => 'manual',
                'required' => true,
                'criteria' => ['income_verified' => true],
                'assigned_to' => $loan->loan_officer_id,
                'due_date' => $applicationDate->addDays(7),
                'compliance_requirements' => ['ECOA' => ['income_verification' => true]]
            ],
            [
                'name' => 'Property Appraisal',
                'type' => 'integration',
                'required' => true,
                'criteria' => ['appraisal_completed' => true],
                'assigned_to' => null,
                'due_date' => $applicationDate->addDays(10),
                'compliance_requirements' => ['RESPA' => ['appraisal_ordered' => true]]
            ],
            [
                'name' => 'Underwriting Review',
                'type' => 'manual',
                'required' => true,
                'criteria' => ['underwriting_approved' => true],
                'assigned_to' => null, // Will be assigned to underwriter
                'due_date' => $applicationDate->addDays(14),
                'compliance_requirements' => [
                    'TRID' => ['closing_disclosure' => true],
                    'ECOA' => ['adverse_action_notice' => false],
                    'RESPA' => ['hud1_settlement_statement' => true]
                ]
            ],
            [
                'name' => 'Final Approval',
                'type' => 'manual',
                'required' => true,
                'criteria' => ['final_approval' => true],
                'assigned_to' => null, // Will be assigned to underwriter
                'due_date' => $applicationDate->addDays(18),
                'compliance_requirements' => [
                    'TRID' => ['intent_to_proceed' => true],
                    'GLBA' => ['privacy_notice' => true],
                    'FCRA' => ['risk_based_pricing_notice' => true]
                ]
            ],
            [
                'name' => 'Closing Preparation',
                'type' => 'manual',
                'required' => true,
                'criteria' => ['closing_documents_prepared' => true],
                'assigned_to' => $loan->loan_officer_id,
                'due_date' => $applicationDate->addDays(21),
                'compliance_requirements' => [
                    'TRID' => ['closing_disclosure' => true],
                    'RESPA' => ['hud1_settlement_statement' => true]
                ]
            ],
            [
                'name' => 'Closing',
                'type' => 'manual',
                'required' => true,
                'criteria' => ['closing_completed' => true],
                'assigned_to' => $loan->loan_officer_id,
                'due_date' => $applicationDate->addDays(25),
                'compliance_requirements' => [
                    'TRID' => ['closing_disclosure' => true],
                    'RESPA' => ['hud1_settlement_statement' => true],
                    'GLBA' => ['privacy_notice' => true]
                ]
            ],
            [
                'name' => 'Funding',
                'type' => 'system',
                'required' => true,
                'criteria' => ['funding_completed' => true],
                'assigned_to' => null,
                'due_date' => $applicationDate->addDays(26),
                'compliance_requirements' => [
                    'AML_BSA' => ['suspicious_activity_reviewed' => true],
                    'SAFE_ACT' => ['originator_licensed' => true]
                ]
            ]
        ];
    }

    /**
     * Check if a step should be completed for a given status.
     */
    private function shouldCompleteStepForStatus(WorkflowStep $step, string $status): bool
    {
        $statusStepMapping = [
            'application' => ['Application Received'],
            'processing' => ['Initial Document Collection', 'Credit Check', 'Income Verification', 'Property Appraisal'],
            'underwriting' => ['Underwriting Review'],
            'approved' => ['Final Approval'],
            'closed' => ['Closing Preparation', 'Closing'],
            'funded' => ['Funding']
        ];

        $stepsForStatus = $statusStepMapping[$status] ?? [];
        return in_array($step->step_name, $stepsForStatus);
    }

    /**
     * Get workflow summary for a loan.
     */
    public function getWorkflowSummary(Loan $loan): array
    {
        $workflowSteps = $loan->workflowSteps;
        
        return [
            'total_steps' => $workflowSteps->count(),
            'completed_steps' => $workflowSteps->where('is_completed', true)->count(),
            'overdue_steps' => $workflowSteps->filter(function ($step) {
                return $step->isOverdue();
            })->count(),
            'pending_steps' => $workflowSteps->where('is_completed', false)->count(),
            'current_step' => $loan->getCurrentWorkflowStep(),
            'next_due_date' => $workflowSteps->where('is_completed', false)
                ->sortBy('due_date')
                ->first()?->due_date
        ];
    }

    /**
     * Assign workflow step to user.
     */
    public function assignStep(WorkflowStep $step, int $userId): void
    {
        $step->update(['assigned_to' => $userId]);
        
        ComplianceAudit::createAudit(
            'workflow_step_assigned',
            'workflow_step',
            $step->id,
            'step_assigned',
            null,
            ['assigned_to' => $userId],
            ['loan_id' => $step->loan_id]
        );
    }

    /**
     * Complete workflow step.
     */
    public function completeStep(WorkflowStep $step, ?int $completedBy = null): void
    {
        if (!$step->meetsCompletionCriteria()) {
            throw new \Exception('Step does not meet completion criteria');
        }

        $step->markCompleted($completedBy);
        
        ComplianceAudit::createAudit(
            'workflow_step_completed',
            'workflow_step',
            $step->id,
            'step_completed',
            null,
            ['completed_by' => $completedBy],
            ['loan_id' => $step->loan_id]
        );
    }

    /**
     * Get overdue workflow steps.
     */
    public function getOverdueSteps(int $tenantId, ?int $assignedTo = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = WorkflowStep::where('tenant_id', $tenantId)
            ->where('is_completed', false)
            ->where('due_date', '<', now());

        if ($assignedTo) {
            $query->where('assigned_to', $assignedTo);
        }

        return $query->with(['loan', 'assignedUser'])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get workflow statistics.
     */
    public function getWorkflowStatistics(int $tenantId): array
    {
        $totalSteps = WorkflowStep::where('tenant_id', $tenantId)->count();
        $completedSteps = WorkflowStep::where('tenant_id', $tenantId)
            ->where('is_completed', true)
            ->count();
        $overdueSteps = WorkflowStep::where('tenant_id', $tenantId)
            ->where('is_completed', false)
            ->where('due_date', '<', now())
            ->count();

        return [
            'total_steps' => $totalSteps,
            'completed_steps' => $completedSteps,
            'overdue_steps' => $overdueSteps,
            'completion_rate' => $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100, 2) : 0
        ];
    }
}