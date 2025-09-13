<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class WorkflowStep extends Model
{
    use HasFactory, UsesTenantConnection;

    protected $fillable = [
        'tenant_id',
        'loan_id',
        'step_name',
        'step_type',
        'step_order',
        'is_completed',
        'is_required',
        'completion_criteria',
        'assigned_to',
        'due_date',
        'completed_at',
        'completed_by',
        'step_data',
        'compliance_requirements',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'is_required' => 'boolean',
        'completion_criteria' => 'array',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'step_data' => 'array',
        'compliance_requirements' => 'array',
    ];

    /**
     * Get the tenant that owns the workflow step.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the loan that owns the workflow step.
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Get the user assigned to this step.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who completed this step.
     */
    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Mark step as completed.
     */
    public function markCompleted(?int $completedBy = null): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
            'completed_by' => $completedBy ?? auth()->id(),
        ]);
    }

    /**
     * Check if step meets completion criteria.
     */
    public function meetsCompletionCriteria(): bool
    {
        $criteria = $this->completion_criteria ?? [];
        
        foreach ($criteria as $criterion => $value) {
            if (!$this->checkCriterion($criterion, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check individual completion criterion.
     */
    private function checkCriterion(string $criterion, $value): bool
    {
        switch ($criterion) {
            case 'documents_uploaded':
                return $this->loan->documents()->count() >= $value;
            case 'compliance_verified':
                return $this->loan->isCompliant();
            case 'approval_received':
                return $this->loan->status === 'approved';
            default:
                return false;
        }
    }

    /**
     * Get next workflow step.
     */
    public function getNextStep(): ?self
    {
        return self::where('loan_id', $this->loan_id)
            ->where('step_order', '>', $this->step_order)
            ->orderBy('step_order')
            ->first();
    }

    /**
     * Get previous workflow step.
     */
    public function getPreviousStep(): ?self
    {
        return self::where('loan_id', $this->loan_id)
            ->where('step_order', '<', $this->step_order)
            ->orderBy('step_order', 'desc')
            ->first();
    }

    /**
     * Check if step is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->is_completed;
    }

    /**
     * Get compliance requirements for this step.
     */
    public function getComplianceRequirements(): array
    {
        return $this->compliance_requirements ?? [];
    }

    /**
     * Check if step meets compliance requirements.
     */
    public function meetsComplianceRequirements(): bool
    {
        $requirements = $this->getComplianceRequirements();
        
        foreach ($requirements as $regulation => $requirement) {
            if (!$this->checkComplianceRequirement($regulation, $requirement)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check individual compliance requirement.
     */
    private function checkComplianceRequirement(string $regulation, array $requirement): bool
    {
        $complianceData = $this->loan->getComplianceData($regulation);
        
        foreach ($requirement as $key => $expectedValue) {
            if (($complianceData[$key] ?? null) !== $expectedValue) {
                return false;
            }
        }

        return true;
    }
}