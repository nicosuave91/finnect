<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

abstract class BaseIntegrationClient
{
    /**
     * API base URL for the vendor.
     */
    protected string $baseUrl;

    /**
     * Cached credentials key.
     */
    protected string $credentialsKey;

    /**
     * Cached index for credential rotation.
     */
    protected string $credentialIndexKey;

    /**
     * Loaded credentials.
     */
    protected array $credentials = [];

    public function __construct()
    {
        $class = static::class;
        $this->credentialsKey = $class . '_credentials';
        $this->credentialIndexKey = $class . '_credential_index';
        $this->credentials = Cache::get($this->credentialsKey, []);
    }

    /**
     * Store credentials for the client.
     */
    public function storeCredentials(array $credentials): void
    {
        Cache::put($this->credentialsKey, $credentials);
        $this->credentials = $credentials;
    }

    /**
     * Get the current credential.
     */
    protected function getCredential(): array
    {
        $index = Cache::get($this->credentialIndexKey, 0);
        return $this->credentials[$index] ?? [];
    }

    /**
     * Rotate to the next credential.
     */
    protected function rotateCredential(): void
    {
        if (count($this->credentials) <= 1) {
            return;
        }
        $index = (Cache::get($this->credentialIndexKey, 0) + 1) % count($this->credentials);
        Cache::put($this->credentialIndexKey, $index);
    }

    /**
     * Perform an HTTP request with retries and exponential backoff.
     *
     * @throws IntegrationException
     */
    public function request(string $method, string $endpoint, array $payload = [])
    {
        $attempts = 0;
        $maxAttempts = 3;
        $delay = 1;

        while ($attempts < $maxAttempts) {
            $credential = $this->getCredential();
            try {
                $response = Http::withHeaders($this->buildHeaders($credential))
                    ->send($method, $this->baseUrl . $endpoint, ['json' => $payload]);

                if ($response->status() === 401) {
                    $this->rotateCredential();
                    throw new IntegrationException('Unauthorized', ['status' => 401]);
                }

                if ($response->successful()) {
                    return $response->json();
                }

                throw new IntegrationException('HTTP Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (IntegrationException $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    throw $e;
                }
                sleep($delay);
                $delay *= 2;
            } catch (Exception $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    throw new IntegrationException($e->getMessage(), [], $e->getCode(), $e);
                }
                sleep($delay);
                $delay *= 2;
            }
        }
    }

    /**
     * Build HTTP headers for the request.
     */
    protected function buildHeaders(array $credential): array
    {
        return [
            'Authorization' => 'Bearer ' . ($credential['token'] ?? ''),
            'Accept' => 'application/json',
        ];
    }
}
