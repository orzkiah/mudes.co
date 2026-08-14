<?php

use App\Http\Controllers\Api\V1\ActivityCategoryController;
use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\AttendanceSessionController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\DashboardAnalyticsController;

use App\Http\Controllers\Api\V1\ArticleCategoryController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\GalleryCategoryController;
use App\Http\Controllers\Api\V1\GalleryController;
use App\Http\Controllers\Api\V1\LibraryCategoryController;
use App\Http\Controllers\Api\V1\LibraryDocumentController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrganizationPeriodController;
use App\Http\Controllers\Api\V1\OrganizationPositionController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\StudyCategoryController;
use App\Http\Controllers\Api\V1\StudyScheduleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Versioned per API_SPECIFICATION.md §1. Module routes are registered inside
| this group as each business module is implemented.
|
*/

/**
 * Registers the standard 10-endpoint Taxonomy Resource Contract route set
 * (API_SPECIFICATION.md §8.2) for one admin resource - shared by every
 * taxonomy module instead of repeating the same 10 lines per module.
 *
 * @param class-string $controller
 */
$taxonomyRoutes = static function (string $resource, string $param, string $controller): void {
    Route::put("{$resource}/bulk-reorder", [$controller, 'reorder']);
    Route::post("{$resource}/bulk-activate", [$controller, 'bulkActivate']);
    Route::post("{$resource}/bulk-deactivate", [$controller, 'bulkDeactivate']);
    Route::post("{$resource}/bulk-delete", [$controller, 'bulkDelete']);
    Route::post("{$resource}/bulk-restore", [$controller, 'bulkRestore']);

    Route::get($resource, [$controller, 'index']);
    Route::post($resource, [$controller, 'store']);
    Route::get("{$resource}/{{$param}}", [$controller, 'show']);
    Route::patch("{$resource}/{{$param}}", [$controller, 'update']);
    Route::delete("{$resource}/{{$param}}", [$controller, 'destroy']);
    Route::post("{$resource}/{{$param}}/restore", [$controller, 'restore']);
};

/**
 * Registers the Standard CRUD Contract's admin route set
 * (API_SPECIFICATION.md §8.1) - shared by every content module (Activities,
 * Articles, Digital Library, Announcements; Galleries extends this with its
 * own photo endpoints).
 *
 * @param class-string $controller
 */
$contentRoutes = static function (string $resource, string $param, string $controller): void {
    Route::get($resource, [$controller, 'index']);
    Route::post($resource, [$controller, 'store']);
    Route::get("{$resource}/{{$param}}", [$controller, 'show']);
    Route::put("{$resource}/{{$param}}", [$controller, 'update']);
    Route::delete("{$resource}/{{$param}}", [$controller, 'destroy']);
    Route::post("{$resource}/{{$param}}/restore", [$controller, 'restore']);
};

Route::prefix('v1')->group(function () use ($taxonomyRoutes, $contentRoutes): void {
    Route::prefix('public')->group(function (): void {
        Route::get('departments', [DepartmentController::class, 'publicIndex']);
        Route::get('organization/structure', [OrganizationPositionController::class, 'publicStructure']);
        Route::get('study-categories', [StudyCategoryController::class, 'publicIndex']);
        Route::get('activity-categories', [ActivityCategoryController::class, 'publicIndex']);
        Route::get('gallery-categories', [GalleryCategoryController::class, 'publicIndex']);
        Route::get('article-categories', [ArticleCategoryController::class, 'publicIndex']);
        Route::get('library-categories', [LibraryCategoryController::class, 'publicIndex']);
        Route::get('schedule', [StudyScheduleController::class, 'publicIndex']);
        Route::get('activities', [ActivityController::class, 'publicIndex']);
        Route::get('activities/{slug}', [ActivityController::class, 'publicShow']);
        Route::get('articles', [ArticleController::class, 'publicIndex']);
        Route::get('articles/{slug}', [ArticleController::class, 'publicShow']);
        Route::get('galleries', [GalleryController::class, 'publicIndex']);
        Route::get('galleries/{gallery}', [GalleryController::class, 'publicShow']);
        Route::get('library', [LibraryDocumentController::class, 'publicIndex']);
        Route::get('library/{document}', [LibraryDocumentController::class, 'publicShow']);
        Route::get('announcements', [AnnouncementController::class, 'publicIndex']);
        Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:5,1');


        // Token-scoped, no login required; rate-limited to prevent abuse
        // (PROJECT_SPECIFICATION.md §15).
        Route::post('attendance/check-in', [AttendanceSessionController::class, 'checkIn'])
            ->middleware('throttle:10,1');
    });

    // Rate-limited per PROJECT_SPECIFICATION.md L198 (5/minute per IP+email).
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('auth/token/login', [AuthController::class, 'loginToken'])->middleware('throttle:5,1');
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/token/logout', [AuthController::class, 'logoutToken']);
        Route::get('auth/me', [AuthController::class, 'me']);
    });

    Route::middleware('auth:sanctum')->prefix('admin')->group(function () use ($taxonomyRoutes, $contentRoutes): void {
        Route::get('settings', [SettingController::class, 'index']);
        Route::post('settings', [SettingController::class, 'store']);
        Route::get('settings/{setting}', [SettingController::class, 'show']);
        Route::put('settings/{setting}', [SettingController::class, 'update']);
        Route::delete('settings/{setting}', [SettingController::class, 'destroy']);
        Route::post('settings/{setting}/restore', [SettingController::class, 'restore']);

        Route::get('members', [MemberController::class, 'index']);
        Route::post('members', [MemberController::class, 'store']);
        Route::get('members/{member}', [MemberController::class, 'show']);
        Route::put('members/{member}', [MemberController::class, 'update']);
        Route::delete('members/{member}', [MemberController::class, 'destroy']);
        Route::post('members/{member}/restore', [MemberController::class, 'restore']);

        // 'tree' registered before the {position} wildcard for clarity.
        Route::get('organization/positions/tree', [OrganizationPositionController::class, 'tree']);
        Route::get('organization/positions', [OrganizationPositionController::class, 'index']);
        Route::post('organization/positions', [OrganizationPositionController::class, 'store']);
        Route::get('organization/positions/{position}', [OrganizationPositionController::class, 'show']);
        Route::put('organization/positions/{position}', [OrganizationPositionController::class, 'update']);
        Route::delete('organization/positions/{position}', [OrganizationPositionController::class, 'destroy']);
        Route::post('organization/positions/{position}/restore', [OrganizationPositionController::class, 'restore']);
        Route::put('organization/positions/{position}/reorder', [OrganizationPositionController::class, 'reorder']);

        // Organization Periods — GET/POST list/create, GET/PUT/DELETE single, POST activate.
        // Routes registered before the {period} wildcard to avoid conflicts.
        Route::get('organization/periods', [OrganizationPeriodController::class, 'index']);
        Route::post('organization/periods', [OrganizationPeriodController::class, 'store']);
        Route::get('organization/periods/{period}', [OrganizationPeriodController::class, 'show']);
        Route::put('organization/periods/{period}', [OrganizationPeriodController::class, 'update']);
        Route::delete('organization/periods/{period}', [OrganizationPeriodController::class, 'destroy']);
        Route::post('organization/periods/{period}/restore', [OrganizationPeriodController::class, 'restore']);
        Route::post('organization/periods/{period}/activate', [OrganizationPeriodController::class, 'activate']);

        // Bulk routes registered before the {department} wildcard for clarity.
        Route::post('departments/bulk-activate', [DepartmentController::class, 'bulkActivate']);
        Route::post('departments/bulk-deactivate', [DepartmentController::class, 'bulkDeactivate']);
        Route::post('departments/bulk-delete', [DepartmentController::class, 'bulkDelete']);
        Route::post('departments/bulk-restore', [DepartmentController::class, 'bulkRestore']);
        Route::put('departments/bulk-reorder', [DepartmentController::class, 'reorder']);

        Route::get('departments', [DepartmentController::class, 'index']);
        Route::post('departments', [DepartmentController::class, 'store']);
        Route::get('departments/{department}', [DepartmentController::class, 'show']);
        Route::patch('departments/{department}', [DepartmentController::class, 'update']);
        Route::delete('departments/{department}', [DepartmentController::class, 'destroy']);
        Route::post('departments/{department}/restore', [DepartmentController::class, 'restore']);

        $taxonomyRoutes('study-categories', 'studyCategory', StudyCategoryController::class);
        $taxonomyRoutes('activity-categories', 'activityCategory', ActivityCategoryController::class);
        $taxonomyRoutes('gallery-categories', 'galleryCategory', GalleryCategoryController::class);
        $taxonomyRoutes('article-categories', 'articleCategory', ArticleCategoryController::class);
        $taxonomyRoutes('library-categories', 'libraryCategory', LibraryCategoryController::class);

        Route::get('schedule-occurrences', [StudyScheduleController::class, 'allOccurrences']);
        Route::patch('schedule/occurrences/{occurrence}', [StudyScheduleController::class, 'updateOccurrence']);

        Route::get('schedule/{schedule}/occurrences', [StudyScheduleController::class, 'occurrences']);
        Route::post('schedule/{schedule}/occurrences/generate', [StudyScheduleController::class, 'generateOccurrences']);
        Route::get('schedule', [StudyScheduleController::class, 'index']);
        Route::post('schedule', [StudyScheduleController::class, 'store']);
        Route::get('schedule/{schedule}', [StudyScheduleController::class, 'show']);
        Route::put('schedule/{schedule}', [StudyScheduleController::class, 'update']);
        Route::delete('schedule/{schedule}', [StudyScheduleController::class, 'destroy']);
        Route::post('schedule/{schedule}/restore', [StudyScheduleController::class, 'restore']);

        $contentRoutes('activities', 'activity', ActivityController::class);
        $contentRoutes('articles', 'article', ArticleController::class);

        Route::post('galleries/{gallery}/photos', [GalleryController::class, 'attachPhotos']);
        Route::delete('galleries/{gallery}/photos/{photo}', [GalleryController::class, 'removePhoto']);
        Route::put('galleries/{gallery}/photos/reorder', [GalleryController::class, 'reorderPhotos']);
        $contentRoutes('galleries', 'gallery', GalleryController::class);
        $contentRoutes('library', 'document', LibraryDocumentController::class);
        $contentRoutes('announcements', 'announcement', AnnouncementController::class);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
        Route::post('notifications/{notification}/mark-read', [NotificationController::class, 'markRead']);
        Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);

        Route::get('attendance/sessions/{session}/roster', [AttendanceSessionController::class, 'roster']);
        Route::post('attendance/sessions/{session}/check-in', [AttendanceSessionController::class, 'manualCheckIn']);
        $contentRoutes('attendance/sessions', 'session', AttendanceSessionController::class);

        Route::get('dashboard/summary', [DashboardAnalyticsController::class, 'summary']);
        Route::get('dashboard/attendance-trend', [DashboardAnalyticsController::class, 'attendanceTrend']);
        Route::get('dashboard/content-volume', [DashboardAnalyticsController::class, 'contentVolume']);
        Route::get('dashboard/library-engagement', [DashboardAnalyticsController::class, 'libraryEngagement']);
        Route::get('dashboard/activity-participation', [DashboardAnalyticsController::class, 'activityParticipation']);

        // Media Manager — POST /admin/media uploads a file and returns a
        // Media UUID for attaching to entity FK fields
        // (API_SPECIFICATION.md §9.27, §5).
        Route::post('media', [MediaController::class, 'store']);
    });
});
