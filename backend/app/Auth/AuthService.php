<?php

namespace App\Auth;

class AuthService
{
    private array $users = [];

    public function register(string $email, string $password): void
    {
        $this->users[$email] = password_hash($password, PASSWORD_BCRYPT);
    }

    public function attempt(string $email, string $password): bool
    {
        if (!isset($this->users[$email])) {
            return false;
        }

        return password_verify($password, $this->users[$email]);
    }
}
