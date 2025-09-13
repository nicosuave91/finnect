<?php

namespace App\Services;

use App\Models\Integration;
use App\Models\ComplianceAudit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class IntegrationService
{
    /**
     * Available integrations.
     */
    private $integrations = [
        'credit_bureaus' => [
            'experian' => [
                'name' => 'Experian',
                'type' => 'credit_bureau',
                'endpoint' => 'https://api.experian.com',
                'required_fields' => ['ssn', 'name', 'address'],
                'compliance' => ['FCRA']
            ],
            'equifax' => [
                'name' => 'Equifax',
                'type' => 'credit_bureau',
                'endpoint' => 'https://api.equifax.com',
                'required_fields' => ['ssn', 'name', 'address'],
                'compliance' => ['FCRA']
            ],
            'transunion' => [
                'name' => 'TransUnion',
                'type' => 'credit_bureau',
                'endpoint' => 'https://api.transunion.com',
                'required_fields' => ['ssn', 'name', 'address'],
                'compliance' => ['FCRA']
            ]
        ],
        'appraisal' => [
            'clear_capital' => [
                'name' => 'Clear Capital',
                'type' => 'appraisal',
                'endpoint' => 'https://api.clearcapital.com',
                'required_fields' => ['property_address', 'loan_amount'],
                'compliance' => ['RESPA']
            ],
            'appraisal_port' => [
                'name' => 'AppraisalPort',
                'type' => 'appraisal',
                'endpoint' => 'https://api.appraisalport.com',
                'required_fields' => ['property_address', 'loan_amount'],
                'compliance' => ['RESPA']
            ]
        ],
        'title_insurance' => [
            'first_american' => [
                'name' => 'First American',
                'type' => 'title_insurance',
                'endpoint' => 'https://api.firstam.com',
                'required_fields' => ['property_address', 'borrower_name'],
                'compliance' => ['RESPA']
            ],
            'fidelity' => [
                'name' => 'Fidelity National Title',
                'type' => 'title_insurance',
                'endpoint' => 'https://api.fnti.com',
                'required_fields' => ['property_address', 'borrower_name'],
                'compliance' => ['RESPA']
            ]
        ],
        'flood_insurance' => [
            'fema' => [
                'name' => 'FEMA Flood Zone',
                'type' => 'flood_insurance',
                'endpoint' => 'https://api.fema.gov',
                'required_fields' => ['property_address'],
                'compliance' => ['RESPA']
            ]
        ],
        'mortgage_insurance' => [
            'genworth' => [
                'name' => 'Genworth Mortgage Insurance',
                'type' => 'mortgage_insurance',
                'endpoint' => 'https://api.genworth.com',
                'required_fields' => ['loan_amount', 'ltv_ratio'],
                'compliance' => ['RESPA']
            ],
            'mgic' => [
                'name' => 'MGIC',
                'type' => 'mortgage_insurance',
                'endpoint' => 'https://api.mgic.com',
                'required_fields' => ['loan_amount', 'ltv_ratio'],
                'compliance' => ['RESPA']
            ]
        ],
        'verification_services' => [
            'the_work_number' => [
                'name' => 'The Work Number',
                'type' => 'employment_verification',
                'endpoint' => 'https://api.theworknumber.com',
                'required_fields' => ['employer_name', 'employee_id'],
                'compliance' => ['ECOA']
            ],
            'equifax_workforce' => [
                'name' => 'Equifax Workforce Solutions',
                'type' => 'employment_verification',
                'endpoint' => 'https://api.equifaxworkforce.com',
                'required_fields' => ['employer_name', 'employee_id'],
                'compliance' => ['ECOA']
            ]
        ],
        'document_management' => [
            'docu_sign' => [
                'name' => 'DocuSign',
                'type' => 'document_management',
                'endpoint' => 'https://api.docusign.net',
                'required_fields' => ['document_id', 'recipient_email'],
                'compliance' => ['TRID', 'ECOA']
            ],
            'adobe_sign' => [
                'name' => 'Adobe Sign',
                'type' => 'document_management',
                'endpoint' => 'https://api.adobesign.com',
                'required_fields' => ['document_id', 'recipient_email'],
                'compliance' => ['TRID', 'ECOA']
            ]
        ],
        'loan_origination' => [
            'encompass' => [
                'name' => 'Encompass',
                'type' => 'loan_origination',
                'endpoint' => 'https://api.encompass.com',
                'required_fields' => ['loan_data'],
                'compliance' => ['TRID', 'ECOA', 'RESPA']
            ],
            'calyx_point' => [
                'name' => 'Calyx Point',
                'type' => 'loan_origination',
                'endpoint' => 'https://api.calyxpoint.com',
                'required_fields' => ['loan_data'],
                'compliance' => ['TRID', 'ECOA', 'RESPA']
            ]
        ],
        'compliance_services' => [
            'compliance_alpha' => [
                'name' => 'ComplianceAlpha',
                'type' => 'compliance',
                'endpoint' => 'https://api.compliancealpha.com',
                'required_fields' => ['loan_data'],
                'compliance' => ['TRID', 'ECOA', 'RESPA', 'GLBA', 'FCRA']
            ],
            'mortgage_compliance' => [
                'name' => 'Mortgage Compliance Advisors',
                'type' => 'compliance',
                'endpoint' => 'https://api.mortgagecompliance.com',
                'required_fields' => ['loan_data'],
                'compliance' => ['TRID', 'ECOA', 'RESPA', 'GLBA', 'FCRA']
            ]
        ]
    ];

    /**
     * Get all available integrations.
     */
    public function getAvailableIntegrations(): array
    {
        return $this->integrations;
    }

    /**
     * Get integrations by type.
     */
    public function getIntegrationsByType(string $type): array
    {
        $integrations = [];
        foreach ($this->integrations as $category => $providers) {
            foreach ($providers as $key => $integration) {
                if ($integration['type'] === $type) {
                    $integrations[$key] = $integration;
                }
            }
        }
        return $integrations;
    }

    /**
     * Get integration configuration.
     */
    public function getIntegrationConfig(string $provider): ?array
    {
        foreach ($this->integrations as $category => $providers) {
            if (isset($providers[$provider])) {
                return $providers[$provider];
            }
        }
        return null;
    }

    /**
     * Create integration for tenant.
     */
    public function createIntegration(int $tenantId, string $provider, array $configuration): Integration
    {
        $config = $this->getIntegrationConfig($provider);
        if (!$config) {
            throw new \Exception("Unknown integration provider: $provider");
        }

        $integration = Integration::create([
            'tenant_id' => $tenantId,
            'name' => $config['name'],
            'type' => $config['type'],
            'status' => 'inactive',
            'configuration' => $configuration
        ]);

        // Test the integration
        $testResult = $this->testIntegration($integration);
        if ($testResult['success']) {
            $integration->update(['status' => 'active']);
        } else {
            $integration->update([
                'status' => 'error',
                'error_message' => $testResult['error']
            ]);
        }

        return $integration;
    }

    /**
     * Test integration connection.
     */
    public function testIntegration(Integration $integration): array
    {
        try {
            $config = $this->getIntegrationConfig($integration->name);
            if (!$config) {
                return ['success' => false, 'error' => 'Unknown integration provider'];
            }

            $endpoint = $config['endpoint'] . '/test';
            $response = Http::timeout(30)
                ->withHeaders($this->getAuthHeaders($integration))
                ->get($endpoint);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            } else {
                return [
                    'success' => false,
                    'error' => 'Connection failed: ' . $response->body()
                ];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Sync data with integration.
     */
    public function syncWithIntegration(Integration $integration, string $action, array $data): array
    {
        try {
            $config = $this->getIntegrationConfig($integration->name);
            if (!$config) {
                return ['success' => false, 'error' => 'Unknown integration provider'];
            }

            $endpoint = $config['endpoint'] . '/' . $action;
            $response = Http::timeout(60)
                ->withHeaders($this->getAuthHeaders($integration))
                ->post($endpoint, $data);

            if ($response->successful()) {
                // Update last sync time
                $integration->update(['last_sync_at' => now()]);

                // Log sync activity
                ComplianceAudit::createAudit(
                    'integration_sync',
                    'integration',
                    $integration->id,
                    'sync_completed',
                    null,
                    ['action' => $action, 'data' => $data],
                    ['response' => $response->json()]
                );

                return ['success' => true, 'data' => $response->json()];
            } else {
                $integration->update([
                    'status' => 'error',
                    'error_message' => 'Sync failed: ' . $response->body()
                ]);

                return [
                    'success' => false,
                    'error' => 'Sync failed: ' . $response->body()
                ];
            }
        } catch (\Exception $e) {
            $integration->update([
                'status' => 'error',
                'error_message' => 'Sync error: ' . $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get authentication headers for integration.
     */
    private function getAuthHeaders(Integration $integration): array
    {
        $config = $integration->configuration;
        $headers = ['Content-Type' => 'application/json'];

        switch ($integration->name) {
            case 'Experian':
            case 'Equifax':
            case 'TransUnion':
                $headers['Authorization'] = 'Bearer ' . ($config['api_key'] ?? '');
                break;
            case 'DocuSign':
                $headers['Authorization'] = 'Bearer ' . ($config['access_token'] ?? '');
                break;
            case 'Encompass':
                $headers['X-Encompass-API-Key'] = $config['api_key'] ?? '';
                break;
            default:
                $headers['X-API-Key'] = $config['api_key'] ?? '';
                break;
        }

        return $headers;
    }

    /**
     * Run credit check.
     */
    public function runCreditCheck(int $loanId, string $provider = 'experian'): array
    {
        $loan = \App\Models\Loan::find($loanId);
        if (!$loan) {
            return ['success' => false, 'error' => 'Loan not found'];
        }

        $integration = Integration::where('tenant_id', $loan->tenant_id)
            ->where('name', ucfirst($provider))
            ->where('status', 'active')
            ->first();

        if (!$integration) {
            return ['success' => false, 'error' => 'Credit check integration not configured'];
        }

        $borrower = $loan->borrower;
        $data = [
            'ssn' => $borrower->ssn,
            'name' => [
                'first' => $borrower->first_name,
                'last' => $borrower->last_name
            ],
            'address' => $borrower->address_data,
            'loan_amount' => $loan->loan_amount
        ];

        return $this->syncWithIntegration($integration, 'credit-check', $data);
    }

    /**
     * Order appraisal.
     */
    public function orderAppraisal(int $loanId, string $provider = 'clear_capital'): array
    {
        $loan = \App\Models\Loan::find($loanId);
        if (!$loan) {
            return ['success' => false, 'error' => 'Loan not found'];
        }

        $integration = Integration::where('tenant_id', $loan->tenant_id)
            ->where('name', 'Clear Capital')
            ->where('status', 'active')
            ->first();

        if (!$integration) {
            return ['success' => false, 'error' => 'Appraisal integration not configured'];
        }

        $data = [
            'property_address' => $loan->loan_data['property_address'] ?? '',
            'loan_amount' => $loan->loan_amount,
            'loan_type' => $loan->loan_type,
            'property_type' => $loan->property_type
        ];

        return $this->syncWithIntegration($integration, 'order-appraisal', $data);
    }

    /**
     * Send document for signature.
     */
    public function sendForSignature(int $loanId, string $documentType, string $provider = 'docu_sign'): array
    {
        $loan = \App\Models\Loan::find($loanId);
        if (!$loan) {
            return ['success' => false, 'error' => 'Loan not found'];
        }

        $integration = Integration::where('tenant_id', $loan->tenant_id)
            ->where('name', 'DocuSign')
            ->where('status', 'active')
            ->first();

        if (!$integration) {
            return ['success' => false, 'error' => 'Document signing integration not configured'];
        }

        $borrower = $loan->borrower;
        $data = [
            'document_type' => $documentType,
            'recipient_email' => $borrower->email,
            'recipient_name' => $borrower->first_name . ' ' . $borrower->last_name,
            'loan_id' => $loan->id,
            'loan_number' => $loan->loan_number
        ];

        return $this->syncWithIntegration($integration, 'send-for-signature', $data);
    }

    /**
     * Get integration logs.
     */
    public function getIntegrationLogs(Integration $integration, int $limit = 50): array
    {
        return ComplianceAudit::where('entity_type', 'integration')
            ->where('entity_id', $integration->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get integration status summary.
     */
    public function getIntegrationStatusSummary(int $tenantId): array
    {
        $integrations = Integration::where('tenant_id', $tenantId)->get();
        
        $summary = [
            'total' => $integrations->count(),
            'active' => $integrations->where('status', 'active')->count(),
            'inactive' => $integrations->where('status', 'inactive')->count(),
            'error' => $integrations->where('status', 'error')->count(),
            'by_type' => []
        ];

        foreach ($integrations as $integration) {
            $type = $integration->type;
            if (!isset($summary['by_type'][$type])) {
                $summary['by_type'][$type] = [
                    'total' => 0,
                    'active' => 0,
                    'inactive' => 0,
                    'error' => 0
                ];
            }
            
            $summary['by_type'][$type]['total']++;
            $summary['by_type'][$type][$integration->status]++;
        }

        return $summary;
    }
}