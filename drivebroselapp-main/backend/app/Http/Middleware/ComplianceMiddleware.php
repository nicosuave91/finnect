<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ComplianceService;
use App\Models\ComplianceAudit;

class ComplianceMiddleware
{
    protected $complianceService;

    public function __construct(ComplianceService $complianceService)
    {
        $this->complianceService = $complianceService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log the request for compliance auditing
        $this->logRequest($request);

        $response = $next($request);

        // Log the response for compliance auditing
        $this->logResponse($request, $response);

        return $response;
    }

    /**
     * Log request for compliance auditing.
     */
    private function logRequest(Request $request): void
    {
        $sensitiveEndpoints = [
            'loans',
            'borrowers',
            'documents',
            'compliance',
        ];

        $isSensitive = collect($sensitiveEndpoints)->contains(function ($endpoint) use ($request) {
            return str_contains($request->path(), $endpoint);
        });

        if ($isSensitive) {
            ComplianceAudit::createAudit(
                'api_request',
                'request',
                0,
                $request->method() . ' ' . $request->path(),
                null,
                [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'headers' => $this->sanitizeHeaders($request->headers->all()),
                    'query' => $request->query(),
                    'body' => $this->sanitizeBody($request->all()),
                ],
                [
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                ]
            );
        }
    }

    /**
     * Log response for compliance auditing.
     */
    private function logResponse(Request $request, Response $response): void
    {
        $sensitiveEndpoints = [
            'loans',
            'borrowers',
            'documents',
            'compliance',
        ];

        $isSensitive = collect($sensitiveEndpoints)->contains(function ($endpoint) use ($request) {
            return str_contains($request->path(), $endpoint);
        });

        if ($isSensitive && $response->getStatusCode() >= 400) {
            ComplianceAudit::createAudit(
                'api_error',
                'response',
                0,
                'HTTP ' . $response->getStatusCode(),
                null,
                [
                    'status_code' => $response->getStatusCode(),
                    'response_body' => $this->sanitizeResponseBody($response->getContent()),
                ],
                [
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                ]
            );
        }
    }

    /**
     * Sanitize headers for compliance logging.
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'cookie',
            'x-api-key',
            'x-auth-token',
        ];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['***REDACTED***'];
            }
        }

        return $headers;
    }

    /**
     * Sanitize request body for compliance logging.
     */
    private function sanitizeBody(array $body): array
    {
        $sensitiveFields = [
            'password',
            'ssn',
            'social_security_number',
            'credit_card',
            'bank_account',
            'routing_number',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($body[$field])) {
                $body[$field] = '***REDACTED***';
            }
        }

        return $body;
    }

    /**
     * Sanitize response body for compliance logging.
     */
    private function sanitizeResponseBody(string $body): string
    {
        $sensitiveFields = [
            'password',
            'ssn',
            'social_security_number',
            'credit_card',
            'bank_account',
            'routing_number',
        ];

        foreach ($sensitiveFields as $field) {
            $body = preg_replace('/"' . $field . '":\s*"[^"]*"/', '"' . $field . '":"***REDACTED***"', $body);
        }

        return $body;
    }
}