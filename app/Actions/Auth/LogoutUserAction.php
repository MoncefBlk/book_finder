<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\AuthService;

class LogoutUserAction
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Logout user.
     *
     * @param User $user
     * @return void
     */
    public function execute(User $user): void
    {
        $this->authService->logout($user);
    }
}
