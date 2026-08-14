<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

/**
 * Thin auth-session orchestration, not a CRUD business module - no
 * Repository/DTO/Policy/Observer (mirrors the Notifications module's
 * reasoning: self-contained infra, no backing resource to authorize
 * against beyond "is this session valid").
 */
class AuthService
{
    /**
     * @throws AuthenticationException when credentials are invalid or the account is inactive.
     */
    public function login(string $email, string $password, bool $remember = false): User
    {
        if (! Auth::guard('web')->attempt(['email' => $email, 'password' => $password, 'is_active' => true], $remember)) {
            throw new AuthenticationException('These credentials do not match our records.');
        }

        request()->session()->regenerate();

        /** @var User */
        return Auth::guard('web')->user();
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    /**
     * @throws AuthenticationException when the token/email pair is invalid or expired.
     */
    public function resetPassword(string $email, string $token, string $password): void
    {
        $status = Password::reset(
            ['email' => $email, 'token' => $token, 'password' => $password],
            function (User $user) use ($password): void {
                $user->forceFill(['password' => $password])->save();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new AuthenticationException('This password reset token is invalid or has expired.');
        }
    }

    /**
     * @return array{user: User, token: string}
     *
     * @throws AuthenticationException when credentials are invalid or the account is inactive.
     */
    public function loginWithToken(string $email, string $password): array
    {
        if (! Auth::guard('web')->attempt(['email' => $email, 'password' => $password, 'is_active' => true])) {
            throw new AuthenticationException('These credentials do not match our records.');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        $user->tokens()->where('name', 'api-token')->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logoutToken(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        $user?->currentAccessToken()?->delete();
    }

    public function currentUser(): User
    {
        /** @var User */
        return Auth::user() ?? Auth::guard('web')->user();
    }
}
