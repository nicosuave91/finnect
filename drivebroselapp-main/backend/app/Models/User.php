<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, UsesTenantConnection;

    protected $fillable = [
        'tenant_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'nmls_id',
        'license_number',
        'profile_data',
        'compliance_data',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'profile_data' => 'array',
        'compliance_data' => 'array',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the user.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the loans for the user as loan officer.
     */
    public function loansAsOfficer()
    {
        return $this->hasMany(Loan::class, 'loan_officer_id');
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if user has NMLS license.
     */
    public function hasNMLSLicense(): bool
    {
        return !empty($this->nmls_id);
    }

    /**
     * Check if user is compliant with SAFE Act.
     */
    public function isSAFEActCompliant(): bool
    {
        $complianceData = $this->compliance_data ?? [];
        return $complianceData['safe_act_compliant'] ?? false;
    }

    /**
     * Update compliance data.
     */
    public function updateComplianceData(array $data): void
    {
        $complianceData = $this->compliance_data ?? [];
        $this->update(['compliance_data' => array_merge($complianceData, $data)]);
    }

    /**
     * Get user's role permissions for compliance.
     */
    public function getCompliancePermissions(): array
    {
        $permissions = [];
        
        foreach ($this->getAllPermissions() as $permission) {
            if (str_starts_with($permission->name, 'compliance.')) {
                $permissions[] = $permission->name;
            }
        }

        return $permissions;
    }
}