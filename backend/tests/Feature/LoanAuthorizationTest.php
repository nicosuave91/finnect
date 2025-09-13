<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoanAuthorizationTest extends TestCase
{
    public function test_guest_cannot_access_loans(): void
    {
        $response = $this->getJson('/api/loans');
        $response->assertStatus(401);
    }
}

