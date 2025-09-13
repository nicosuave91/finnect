<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Tenant extends Model
{
    use HasFactory, UsesTenantConnection;

    protected $fillable = [
        'name',
        'domain',
        'database',
        'configuration',
        'compliance_settings',
        'is_active',
        'trial_ends_at',
        'subscription_ends_at',
    ];

    protected $casts = [
        'configuration' => 'array',
        'compliance_settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Get the users for the tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the loans for the tenant.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get the borrowers for the tenant.
     */
    public function borrowers(): HasMany
    {
        return $this->hasMany(Borrower::class);
    }

    /**
     * Check if tenant is in trial period.
     */
    public function isInTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if tenant has active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isFuture();
    }

    /**
     * Get compliance settings for a specific regulation.
     */
    public function getComplianceSetting(string $regulation): array
    {
        return $this->compliance_settings[$regulation] ?? [];
    }

    /**
     * Update compliance settings for a specific regulation.
     */
    public function updateComplianceSetting(string $regulation, array $settings): void
    {
        $complianceSettings = $this->compliance_settings ?? [];
        $complianceSettings[$regulation] = $settings;
        $this->update(['compliance_settings' => $complianceSettings]);
    }
}