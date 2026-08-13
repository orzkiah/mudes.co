<?php

use App\Shared\Exceptions\AttendanceWindowClosedException;
use App\Shared\Exceptions\DependencyConflictException;
use App\Shared\Exceptions\DuplicateCheckInException;
use App\Shared\Exceptions\OrganizationPositionCycleException;
use App\Shared\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Sanctum SPA stateful-cookie authentication (BACKEND_ARCHITECTURE.md §10).
        $middleware->statefulApi();

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Centralized exception handling (IMPLEMENTATION_RULES.md §16,
        // API_SPECIFICATION.md §2/§7). Every response, success or failure,
        // uses the same envelope - a Controller never builds its own.
        $exceptions->render(function (ValidationException $e, Request $request) {
            return ApiResponse::error(
                message: 'The given data was invalid.',
                type: 'urn:mudes:error:validation-failed',
                title: 'Validation Failed',
                status: 422,
                detail: 'One or more fields failed validation.',
                fields: $e->errors(),
            );
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return ApiResponse::error(
                message: 'Authentication required.',
                type: 'urn:mudes:error:unauthenticated',
                title: 'Unauthenticated',
                status: 401,
                detail: 'No valid session was found.',
            );
        });

        // Laravel converts AuthorizationException to AccessDeniedHttpException
        // internally before custom renderers see it - matching both here.
        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $e, Request $request) {
            return ApiResponse::error(
                message: 'You do not have permission to perform this action.',
                type: 'urn:mudes:error:forbidden',
                title: 'Forbidden',
                status: 403,
            );
        });

        $exceptions->render(function (NotFoundHttpException|ModelNotFoundException $e, Request $request) {
            return ApiResponse::error(
                message: 'Resource not found.',
                type: 'urn:mudes:error:not-found',
                title: 'Not Found',
                status: 404,
            );
        });

        $exceptions->render(function (QueryException $e, Request $request) {
            return ApiResponse::error(
                message: 'This action conflicts with existing data.',
                type: 'urn:mudes:error:conflict',
                title: 'Conflict',
                status: 409,
            );
        });

        $exceptions->render(function (DependencyConflictException $e, Request $request) {
            return ApiResponse::error(
                message: $e->getMessage(),
                type: 'urn:mudes:error:dependency-conflict',
                title: 'Conflict',
                status: 409,
            );
        });

        $exceptions->render(function (OrganizationPositionCycleException $e, Request $request) {
            return ApiResponse::error(
                message: $e->getMessage(),
                type: 'urn:mudes:error:cycle-detected',
                title: 'Conflict',
                status: 409,
            );
        });

        $exceptions->render(function (AttendanceWindowClosedException $e, Request $request) {
            return ApiResponse::error(
                message: $e->getMessage(),
                type: 'urn:mudes:error:attendance-window-closed',
                title: 'Conflict',
                status: 409,
            );
        });

        $exceptions->render(function (DuplicateCheckInException $e, Request $request) {
            return ApiResponse::error(
                message: $e->getMessage(),
                type: 'urn:mudes:error:duplicate',
                title: 'Conflict',
                status: 409,
            );
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            report($e);

            return ApiResponse::error(
                message: 'Something went wrong. Please try again later.',
                type: 'urn:mudes:error:internal',
                title: 'Internal Server Error',
                status: 500,
                // Never leaked in production - config('app.debug') is false there.
                detail: config('app.debug') ? $e->getMessage().' @ '.$e->getFile().':'.$e->getLine() : null,
            );
        });
    })->create();
