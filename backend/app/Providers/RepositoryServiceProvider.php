<?php

declare(strict_types=1);

namespace App\Providers;

use App\Infrastructure\Repositories\Contracts\ActivityCategoryRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\ActivityRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\AnnouncementRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\AttendanceSessionRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\ArticleCategoryRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\ArticleRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\GalleryCategoryRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\GalleryRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\LibraryCategoryRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\LibraryDocumentRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\MemberRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\NotificationRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\OrganizationPeriodRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\OrganizationPositionRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\SettingRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\StudyCategoryRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\StudyScheduleRepositoryInterface;
use App\Infrastructure\Repositories\Eloquent\ActivityCategoryRepository;
use App\Infrastructure\Repositories\Eloquent\ActivityRepository;
use App\Infrastructure\Repositories\Eloquent\AnnouncementRepository;
use App\Infrastructure\Repositories\Eloquent\AttendanceSessionRepository;
use App\Infrastructure\Repositories\Eloquent\ArticleCategoryRepository;
use App\Infrastructure\Repositories\Eloquent\ArticleRepository;
use App\Infrastructure\Repositories\Eloquent\DepartmentRepository;
use App\Infrastructure\Repositories\Eloquent\GalleryCategoryRepository;
use App\Infrastructure\Repositories\Eloquent\GalleryRepository;
use App\Infrastructure\Repositories\Eloquent\LibraryCategoryRepository;
use App\Infrastructure\Repositories\Eloquent\LibraryDocumentRepository;
use App\Infrastructure\Repositories\Eloquent\MemberRepository;
use App\Infrastructure\Repositories\Eloquent\NotificationRepository;
use App\Infrastructure\Repositories\Eloquent\OrganizationPeriodRepository;
use App\Infrastructure\Repositories\Eloquent\OrganizationPositionRepository;
use App\Infrastructure\Repositories\Eloquent\SettingRepository;
use App\Infrastructure\Repositories\Eloquent\StudyCategoryRepository;
use App\Infrastructure\Repositories\Eloquent\StudyScheduleRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds every Repository interface to its Eloquent implementation
 * (BACKEND_ARCHITECTURE.md §7.3, IMPLEMENTATION_RULES.md §6).
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->bind(MemberRepositoryInterface::class, MemberRepository::class);
        $this->app->bind(OrganizationPositionRepositoryInterface::class, OrganizationPositionRepository::class);
        $this->app->bind(OrganizationPeriodRepositoryInterface::class, OrganizationPeriodRepository::class);
        $this->app->bind(StudyCategoryRepositoryInterface::class, StudyCategoryRepository::class);
        $this->app->bind(ActivityCategoryRepositoryInterface::class, ActivityCategoryRepository::class);
        $this->app->bind(GalleryCategoryRepositoryInterface::class, GalleryCategoryRepository::class);
        $this->app->bind(ArticleCategoryRepositoryInterface::class, ArticleCategoryRepository::class);
        $this->app->bind(LibraryCategoryRepositoryInterface::class, LibraryCategoryRepository::class);
        $this->app->bind(StudyScheduleRepositoryInterface::class, StudyScheduleRepository::class);
        $this->app->bind(ActivityRepositoryInterface::class, ActivityRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
        $this->app->bind(GalleryRepositoryInterface::class, GalleryRepository::class);
        $this->app->bind(LibraryDocumentRepositoryInterface::class, LibraryDocumentRepository::class);
        $this->app->bind(AnnouncementRepositoryInterface::class, AnnouncementRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
        $this->app->bind(AttendanceSessionRepositoryInterface::class, AttendanceSessionRepository::class);
    }
}
