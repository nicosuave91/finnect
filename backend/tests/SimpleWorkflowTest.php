<?php

namespace Tests;

use App\Workflow\SimpleWorkflow;
use PHPUnit\Framework\TestCase;

class SimpleWorkflowTest extends TestCase
{
    public function test_workflow_advances_through_steps(): void
    {
        $workflow = new SimpleWorkflow(['start', 'middle', 'end']);

        $this->assertSame('start', $workflow->current());

        $workflow->advance();
        $this->assertSame('middle', $workflow->current());

        $workflow->advance();
        $this->assertSame('end', $workflow->current());

        $workflow->advance();
        $this->assertTrue($workflow->isFinished());
        $this->assertSame('completed', $workflow->current());
    }
}
