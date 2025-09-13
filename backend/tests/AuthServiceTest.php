<?php

namespace Tests;

use App\Auth\AuthService;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    public function test_successful_login(): void
    {
        $service = new AuthService();
        $service->register('user@example.com', 'secret');
        $this->assertTrue($service->attempt('user@example.com', 'secret'));
    }

    public function test_failed_login_with_wrong_password(): void
    {
        $service = new AuthService();
        $service->register('user@example.com', 'secret');
        $this->assertFalse($service->attempt('user@example.com', 'wrong'));
    }
}
