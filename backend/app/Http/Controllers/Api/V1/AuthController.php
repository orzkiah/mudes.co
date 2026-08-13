<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\AuthService;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\AuthUserResource;
use Illuminate\Http\JsonResponse;

class AuthController extends BaseController
{
    public function __construct(private readonly AuthService $service)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->service->login(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            remember: $request->boolean('remember'),
        );

        return $this->success(new AuthUserResource($user), 'Logged in successfully.');
    }

    public function logout(): JsonResponse
    {
        $this->service->logout();

        return $this->success(message: 'Logged out successfully.');
    }

    public function me(): JsonResponse
    {
        return $this->success(new AuthUserResource($this->service->currentUser()), 'Current user retrieved successfully.');
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->service->sendPasswordResetLink($request->string('email')->toString());

        // Always the same message, regardless of whether the email is registered - avoids user enumeration.
        return $this->success(message: 'If that email is registered, a password reset link has been sent.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->service->resetPassword(
            email: $request->string('email')->toString(),
            token: $request->string('token')->toString(),
            password: $request->string('password')->toString(),
        );

        return $this->success(message: 'Password reset successfully.');
    }
}
