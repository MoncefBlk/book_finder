<?php

namespace App\Actions\Auth;

use App\Services\AuthService;

class LoginUserAction
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Login user.
     *
     * @param array $credentials
     * @return array
     */
    public function execute(array $credentials): array
    {
        return $this->authService->login($credentials);
    }
}
