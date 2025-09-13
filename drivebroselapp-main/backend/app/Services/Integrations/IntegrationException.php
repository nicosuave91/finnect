<?php

namespace App\Services\Integrations;

use Exception;

class IntegrationException extends Exception
{
    /**
     * Additional context about the error.
     */
    protected array $context;

    public function __construct(string $message, array $context = [], int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
