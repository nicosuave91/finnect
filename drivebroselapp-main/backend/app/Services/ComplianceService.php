<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\ComplianceAudit;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class ComplianceService
{

    protected array $regulations = [];

    public function __construct()
    {
        $this->loadRegulations();
    }

    protected function loadRegulations(): void
    {
        $path = app_path('Data/regulations.json');
        if (file_exists($path)) {
            $this->regulations = json_decode(file_get_contents($path), true) ?? [];
        } else {
            Log::warning('Regulations dataset not found', ['path' => $path]);
            $this->regulations = [];
        }
    }

    public function getRegulations(): array
    {
        return $this->regulations;
    }

    protected function recordViolation(Loan $loan, array $violation): void
    {
        Log::warning('Compliance violation detected', ['loan_id' => $loan->id] + $violation);
        ComplianceAudit::createAudit(
            'compliance_violation',
            'loan',
            $loan->id,
            'violation_detected',
            null,
            null,
            $violation
        );
        $this->triggerRemediationHandler($loan, $violation);
    }

    protected function triggerRemediationHandler(Loan $loan, array $violation): void
    {
        $remediation = $this->regulations[$violation['regulation']]['rules'][$violation['rule_id'] ?? '']['remediation'] ?? null;
        Log::info('Triggering remediation handler', [
            'loan_id' => $loan->id,
            'violation' => $violation,
            'remediation' => $remediation,
        ]);
        ComplianceAudit::createAudit(
            'compliance_remediation',
            'loan',
            $loan->id,
            'remediation_triggered',
            null,
            null,
            array_merge($violation, ['remediation' => $remediation])
        );
    }

    public function triggerRemediation(string $entityType, int $entityId, string $regulation, string $ruleId): array
    {
        $remediation = $this->regulations[$regulation]['rules'][$ruleId]['remediation'] ?? null;
        ComplianceAudit::createAudit(
            'compliance_remediation',
            $entityType,
            $entityId,
            'remediation_triggered',
            null,
            null,
            [
                'regulation' => $regulation,
                'rule_id' => $ruleId,
                'remediation' => $remediation,
            ]
        );
        Log::info('Manual remediation triggered', [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'regulation' => $regulation,
            'rule_id' => $ruleId,
            'remediation' => $remediation,
        ]);
        return ['remediation' => $remediation];
    }


    /**
     * Validate TRID compliance for a loan.
     */
    public function validateTRIDCompliance(Loan $loan): array
    {
        $violations = [];
        $complianceData = $loan->getComplianceData('TRID');

        // Check for required TRID disclosures
        $requiredDisclosures = [
            'loan_estimate' => 'Loan Estimate must be provided within 3 business days',
            'closing_disclosure' => 'Closing Disclosure must be provided 3 business days before closing',
            'intent_to_proceed' => 'Intent to Proceed must be obtained before proceeding',
        ];

        foreach ($requiredDisclosures as $disclosure => $message) {
            if (!($complianceData[$disclosure] ?? false)) {
                $violation = [
                    'regulation' => 'TRID',
                    'type' => 'missing_disclosure',
                    'disclosure' => $disclosure,
                    'rule_id' => $disclosure,
                    'message' => $message,
                    'severity' => 'high',
                ];
                $violations[] = $violation;
                $this->recordViolation($loan, $violation);

                $violations[] = [
                    'regulation' => 'TRID',
                    'type' => 'missing_disclosure',
                    'disclosure' => $disclosure,
                    'message' => $message,
                    'severity' => 'high',
                ];
            }
        }

        // Check timing requirements
        if ($complianceData['loan_estimate_date'] ?? null) {
            $leDate = \Carbon\Carbon::parse($complianceData['loan_estimate_date']);
            $appDate = $loan->application_date;
            
            if ($leDate->diffInBusinessDays($appDate) > 3) {
                $violation = [
                    'regulation' => 'TRID',
                    'type' => 'timing_violation',
                    'rule_id' => 'loan_estimate',
                    'message' => 'Loan Estimate provided more than 3 business days after application',
                    'severity' => 'high',
                ];
                $violations[] = $violation;
                $this->recordViolation($loan, $violation);

                $violations[] = [
                    'regulation' => 'TRID',
                    'type' => 'timing_violation',
                    'message' => 'Loan Estimate provided more than 3 business days after application',
                    'severity' => 'high',
                ]
            }
        }

        return $violations;
    }

    /**
     * Validate ECOA compliance for a loan.
     */
    public function validateECOACompliance(Loan $loan): array
    {
        $violations = [];
        $complianceData = $loan->getComplianceData('ECOA');

        // Check for required ECOA notices
        $requiredNotices = [
            'adverse_action_notice' => 'Adverse Action Notice required for denials',
            'equal_credit_opportunity_notice' => 'Equal Credit Opportunity Notice required',
        ];

        foreach ($requiredNotices as $notice => $message) {
            if (!($complianceData[$notice] ?? false)) {

                $violation = [
                    'regulation' => 'ECOA',
                    'type' => 'missing_notice',
                    'notice' => $notice,
                    'rule_id' => $notice,
                    'message' => $message,
                    'severity' => 'high',
                ];
                $violations[] = $violation;
                $this->recordViolation($loan, $violation);
                $violations[] = [
                    'regulation' => 'ECOA',
                    'type' => 'missing_notice',
                    'notice' => $notice,
                    'message' => $message,
                    'severity' => 'high',
                ];

            }
        }

        // Check for prohibited information collection
        $prohibitedFields = [
            'marital_status' => 'Marital status cannot be used for credit decisions',
            'race' => 'Race cannot be used for credit decisions',
            'religion' => 'Religion cannot be used for credit decisions',
        ];

        foreach ($prohibitedFields as $field => $message) {
            if (isset($complianceData[$field])) {

                $violation = [
                    'regulation' => 'ECOA',
                    'type' => 'prohibited_field',
                    'field' => $field,
                    'rule_id' => $field,
                    'message' => $message,
                    'severity' => 'critical',
                ];
                $violations[] = $violation;
                $this->recordViolation($loan, $violation);

                $violations[] = [
                    'regulation' => 'ECOA',
                    'type' => 'prohibited_field',
                    'field' => $field,
                    'message' => $message,
                    'severity' => 'critical',
                ];

            }
        }

        return $violations;
    }

    /**
     * Validate RESPA compliance for a loan.
     */
    public function validateRESPACompliance(Loan $loan): array
    {
        $violations = [];
        $complianceData = $loan->getComplianceData('RESPA');

        // Check for required RESPA disclosures
        $requiredDisclosures = [
            'good_faith_estimate' => 'Good Faith Estimate required',
            'hud1_settlement_statement' => 'HUD-1 Settlement Statement required',
            'servicing_disclosure' => 'Servicing Disclosure required',
        ];

        foreach ($requiredDisclosures as $disclosure => $message) {
            if (!($complianceData[$disclosure] ?? false)) {

                $violation = [
                    'regulation' => 'RESPA',
                    'type' => 'missing_disclosure',
                    'disclosure' => $disclosure,
                    'rule_id' => $disclosure,
                    'message' => $message,
                    'severity' => 'high',
                ];
                $violations[] = $violation;
                $this->recordViolation($loan, $violation);

                $violations[] = [
                    'regulation' => 'RESPA',
                    'type' => 'missing_disclosure',
                    'disclosure' => $disclosure,
                    'message' => $message,
                    'severity' => 'high',
                ];

            }
        }

        // Check for kickback prohibitions
        if ($complianceData['referral_fees'] ?? false) {

            $violation = [
                'regulation' => 'RESPA',
                'type' => 'kickback_violation',
                'rule_id' => 'referral_fees',
                'message' => 'Referral fees may violate RESPA kickback prohibitions',
                'severity' => 'critical',
            ];
            $violations[] = $violation;
            $this->recordViolation($loan, $violation);

            $violations[] = [
                'regulation' => 'RESPA',
                'type' => 'kickback_violation',
                'message' => 'Referral fees may violate RESPA kickback prohibitions',
                'severity' => 'critical',
            ];

        }

        return $violations;
    }

    /**
     * Validate GLBA compliance for a loan.
     */
    public function validateGLBACompliance(Loan $loan): array
    {
        $violations = [];
        $complianceData = $loan->getComplianceData('GLBA');

        // Check for privacy notice
        if (!($complianceData['privacy_notice_provided'] ?? false)) {

            $violation = [
                'regulation' => 'GLBA',
                'type' => 'missing_privacy_notice',
                'rule_id' => 'privacy_notice_provided',
                'message' => 'Privacy notice must be provided to customers',
                'severity' => 'high',
            ];
            $violations[] = $violation;
            $this->recordViolation($loan, $violation);

            $violations[] = [
                'regulation' => 'GLBA',
                'type' => 'missing_privacy_notice',
                'message' => 'Privacy notice must be provided to customers',
                'severity' => 'high',
            ];

        }

        // Check for opt-out mechanism
        if (!($complianceData['opt_out_mechanism'] ?? false)) {

            $violation = [
                'regulation' => 'GLBA',
                'type' => 'missing_opt_out',
                'rule_id' => 'opt_out_mechanism',
                'message' => 'Opt-out mechanism must be provided for information sharing',
                'severity' => 'high',
            ];
            $violations[] = $violation;
            $this->recordViolation($loan, $violation);
            $violations[] = [
                'regulation' => 'GLBA',
                'type' => 'missing_opt_out',
                'message' => 'Opt-out mechanism must be provided for information sharing',
                'severity' => 'high',
            ];

        }

        return $violations;
    }

    /**
     * Validate FCRA compliance for a loan.
     */
    public function validateFCRACompliance(Loan $loan): array
    {
        $violations = [];
        $complianceData = $loan->getComplianceData('FCRA');

        // Check for required FCRA notices
        $requiredNotices = [
            'adverse_action_notice' => 'Adverse Action Notice required for credit denials',
            'risk_based_pricing_notice' => 'Risk-Based Pricing Notice required',
        ];

        foreach ($requiredNotices as $notice => $message) {
            if (!($complianceData[$notice] ?? false)) {

                $violation = [
                    'regulation' => 'FCRA',
                    'type' => 'missing_notice',
                    'notice' => $notice,
                    'rule_id' => $notice,
                    'message' => $message,
                    'severity' => 'high',
                ];
                $violations[] = $violation;
                $this->recordViolation($loan, $violation);

                $violations[] = [
                    'regulation' => 'FCRA',
                    'type' => 'missing_notice',
                    'notice' => $notice,
                    'message' => $message,
                    'severity' => 'high',
                ];
            }
        }

        return $violations;
    }

    /**
     * Validate AML/BSA compliance for a loan.
     */
    public function validateAMLBSACompliance(Loan $loan): array
    {
        $violations = [];
        $complianceData = $loan->getComplianceData('AML_BSA');

        // Check for suspicious activity monitoring
        if (!($complianceData['suspicious_activity_reviewed'] ?? false)) {

            $violation = [
                'regulation' => 'AML_BSA',
                'type' => 'missing_sar_review',
                'rule_id' => 'suspicious_activity_reviewed',
                'message' => 'Suspicious activity must be reviewed for AML compliance',
                'severity' => 'high',
            ];
            $violations[] = $violation;
            $this->recordViolation($loan, $violation);

            $violations[] = [
                'regulation' => 'AML_BSA',
                'type' => 'missing_sar_review',
                'message' => 'Suspicious activity must be reviewed for AML compliance',
                'severity' => 'high',
            ];

        }

        // Check for customer due diligence
        if (!($complianceData['customer_due_diligence'] ?? false)) {

            $violation = [
                'regulation' => 'AML_BSA',
                'type' => 'missing_cdd',
                'rule_id' => 'customer_due_diligence',
                'message' => 'Customer Due Diligence must be performed',
                'severity' => 'high',
            ];
            $violations[] = $violation;
            $this->recordViolation($loan, $violation);

            $violations[] = [
                'regulation' => 'AML_BSA',
                'type' => 'missing_cdd',
                'message' => 'Customer Due Diligence must be performed',
                'severity' => 'high',
            ];

        }

        return $violations;
    }

    /**
     * Validate SAFE Act compliance for a loan.
     */
    public function validateSAFEActCompliance(Loan $loan): array
    {
        $violations = [];
        $complianceData = $loan->getComplianceData('SAFE_ACT');

        // Check for licensed loan originator
        $loanOfficer = $loan->loanOfficer;
        if (!$loanOfficer || !$loanOfficer->isSAFEActCompliant()) {

            $violation = [
                'regulation' => 'SAFE_ACT',
                'type' => 'unlicensed_originator',
                'rule_id' => 'unlicensed_originator',
                'message' => 'Loan originator must be licensed under SAFE Act',
                'severity' => 'critical',
            ];
            $violations[] = $violation;
            $this->recordViolation($loan, $violation);

            $violations[] = [
                'regulation' => 'SAFE_ACT',
                'type' => 'unlicensed_originator',
                'message' => 'Loan originator must be licensed under SAFE Act',
                'severity' => 'critical',
            ];

        }

        return $violations;
    }

    /**
     * Run comprehensive compliance check for a loan.
     */
    public function runComplianceCheck(Loan $loan): array
    {
        $allViolations = [];
        $regulations = ['TRID', 'ECOA', 'RESPA', 'GLBA', 'FCRA', 'AML_BSA', 'SAFE_ACT'];

        foreach ($regulations as $regulation) {
            $method = 'validate' . $regulation . 'Compliance';
            if (method_exists($this, $method)) {
                $violations = $this->$method($loan);
                $allViolations = array_merge($allViolations, $violations);
            }
        }

        // Log compliance check
        ComplianceAudit::createAudit(
            'compliance_check',
            'loan',
            $loan->id,
            'compliance_check_completed',
            null,
            ['violations' => $allViolations],
            ['total_violations' => count($allViolations)]
        );

        return $allViolations;
    }

    /**
     * Get compliance summary for a loan.
     */
    public function getComplianceSummary(Loan $loan): array
    {
        $violations = $this->runComplianceCheck($loan);
        
        $summary = [
            'is_compliant' => empty($violations),
            'total_violations' => count($violations),
            'critical_violations' => count(array_filter($violations, fn($v) => $v['severity'] === 'critical')),
            'high_violations' => count(array_filter($violations, fn($v) => $v['severity'] === 'high')),
            'violations_by_regulation' => [],
        ];

        foreach ($violations as $violation) {
            $regulation = $violation['regulation'];
            if (!isset($summary['violations_by_regulation'][$regulation])) {
                $summary['violations_by_regulation'][$regulation] = 0;
            }
            $summary['violations_by_regulation'][$regulation]++;
        }

        return $summary;
    }
}