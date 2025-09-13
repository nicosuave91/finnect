<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class ComplianceAudit extends Model
{
    use HasFactory, UsesTenantConnection;

    protected $fillable = [
        'tenant_id',
        'audit_type',
        'entity_type',
        'entity_id',
        'action',
        'old_values',
        'new_values',
        'metadata',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the tenant that owns the audit.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable entity.
     */
    public function auditable()
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }

    /**
     * Create a compliance audit entry.
     */
    public static function createAudit(
        string $auditType,
        string $entityType,
        int $entityId,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'tenant_id' => tenant()->id,
            'audit_type' => $auditType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get audit trail for an entity.
     */
    public static function getAuditTrail(string $entityType, int $entityId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get compliance violations.
     */
    public static function getViolations(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('audit_type', 'compliance_violation')
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();
    }
}