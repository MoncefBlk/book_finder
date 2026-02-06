<?php

namespace App\Actions\Auth;

use App\Services\AuthService;

class RegisterUserAction
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @return array
     */
    public function execute(array $data): array
    {
        return $this->authService->register($data);
    }
}
