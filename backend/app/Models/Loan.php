<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Loan extends Model
{
    use HasFactory, UsesTenantConnection, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'loan_number',
        'status',
        'loan_officer_id',
        'borrower_id',
        'co_borrower_id',
        'loan_data',
        'compliance_data',
        'workflow_data',
        'loan_amount',
        'interest_rate',
        'loan_type',
        'property_type',
        'occupancy_type',
        'purpose',
        'application_date',
        'closing_date',
        'funding_date',
        'vendor_integrations',
        'audit_trail',
    ];

    protected $casts = [
        'loan_data' => 'array',
        'compliance_data' => 'array',
        'workflow_data' => 'array',
        'loan_amount' => 'decimal:2',
        'interest_rate' => 'decimal:4',
        'application_date' => 'date',
        'closing_date' => 'date',
        'funding_date' => 'date',
        'vendor_integrations' => 'array',
        'audit_trail' => 'array',
    ];

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'loan_amount', 'interest_rate', 'compliance_data'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the tenant that owns the loan.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the loan officer for the loan.
     */
    public function loanOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loan_officer_id');
    }

    /**
     * Get the primary borrower for the loan.
     */
    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    /**
     * Get the co-borrower for the loan.
     */
    public function coBorrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class, 'co_borrower_id');
    }

    /**
     * Get the compliance audits for the loan.
     */
    public function complianceAudits(): HasMany
    {
        return $this->hasMany(ComplianceAudit::class, 'entity_id')
            ->where('entity_type', 'loan');
    }

    /**
     * Get the workflow steps for the loan.
     */
    public function workflowSteps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class);
    }

    /**
     * Check if loan is in a specific status.
     */
    public function isInStatus(string $status): bool
    {
        return $this->status === $status;
    }

    /**
     * Get the current workflow step.
     */
    public function getCurrentWorkflowStep(): ?WorkflowStep
    {
        return $this->workflowSteps()
            ->where('is_completed', false)
            ->orderBy('step_order')
            ->first();
    }

    /**
     * Update loan status and log the change.
     */
    public function updateStatus(string $newStatus, ?string $reason = null): void
    {
        $oldStatus = $this->status;
        $this->update(['status' => $newStatus]);
        
        $this->logStatusChange($oldStatus, $newStatus, $reason);
    }

    /**
     * Log status change for compliance.
     */
    private function logStatusChange(string $oldStatus, string $newStatus, ?string $reason = null): void
    {
        $auditTrail = $this->audit_trail ?? [];
        $auditTrail[] = [
            'action' => 'status_change',
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
        ];

        $this->update(['audit_trail' => $auditTrail]);
    }

    /**
     * Get compliance data for a specific regulation.
     */
    public function getComplianceData(string $regulation): array
    {
        return $this->compliance_data[$regulation] ?? [];
    }

    /**
     * Update compliance data for a specific regulation.
     */
    public function updateComplianceData(string $regulation, array $data): void
    {
        $complianceData = $this->compliance_data ?? [];
        $complianceData[$regulation] = $data;
        $this->update(['compliance_data' => $complianceData]);
    }

    /**
     * Check if loan is compliant with all regulations.
     */
    public function isCompliant(): bool
    {
        $requiredRegulations = ['TRID', 'ECOA', 'RESPA', 'GLBA', 'FCRA', 'AML_BSA', 'SAFE_ACT'];
        
        foreach ($requiredRegulations as $regulation) {
            $complianceData = $this->getComplianceData($regulation);
            if (empty($complianceData) || !($complianceData['compliant'] ?? false)) {
                return false;
            }
        }

        return true;
    }
}