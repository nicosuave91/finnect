<?php

namespace App\Workflow;

class SimpleWorkflow
{
    /** @var array<int,string> */
    private array $steps;
    private int $current = 0;

    /**
     * @param array<int,string> $steps
     */
    public function __construct(array $steps)
    {
        $this->steps = $steps;
    }

    public function current(): string
    {
        return $this->steps[$this->current] ?? 'completed';
    }

    public function advance(): void
    {
        if ($this->current < count($this->steps)) {
            $this->current++;
        }
    }

    public function isFinished(): bool
    {
        return $this->current >= count($this->steps);
    }
}
