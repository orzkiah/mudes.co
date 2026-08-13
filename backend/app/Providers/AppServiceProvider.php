<?php

namespace App\Providers;

use App\Domain\Models\Activity;
use App\Domain\Models\ActivityCategory;
use App\Domain\Models\Announcement;
use App\Domain\Models\AttendanceSession;
use App\Domain\Models\Article;
use App\Domain\Models\ArticleCategory;
use App\Domain\Models\Department;
use App\Domain\Models\Gallery;
use App\Domain\Models\GalleryCategory;
use App\Domain\Models\LibraryCategory;
use App\Domain\Models\LibraryDocument;
use App\Domain\Models\Member;
use App\Domain\Models\OrganizationPosition;
use App\Domain\Models\OrganizationPeriod;
use App\Domain\Models\Setting;
use App\Domain\Models\StudyCategory;
use App\Domain\Models\StudySchedule;
use App\Domain\Policies\ActivityCategoryPolicy;
use App\Domain\Policies\ActivityPolicy;
use App\Domain\Policies\AnnouncementPolicy;
use App\Domain\Policies\AttendanceSessionPolicy;
use App\Domain\Policies\ArticleCategoryPolicy;
use App\Domain\Policies\ArticlePolicy;
use App\Domain\Policies\DepartmentPolicy;
use App\Domain\Policies\GalleryCategoryPolicy;
use App\Domain\Policies\GalleryPolicy;
use App\Domain\Policies\LibraryCategoryPolicy;
use App\Domain\Policies\LibraryDocumentPolicy;
use App\Domain\Policies\MemberPolicy;
use App\Domain\Policies\OrganizationPositionPolicy;
use App\Domain\Policies\OrganizationPeriodPolicy;
use App\Domain\Policies\SettingPolicy;
use App\Domain\Policies\StudyCategoryPolicy;
use App\Domain\Policies\StudySchedulePolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ApiResponse already adds its own "data" key around each Resource's
        // array - disable JsonResource's default wrapping so it isn't nested twice.
        JsonResource::withoutWrapping();

        // Explicit, not relying on Laravel's namespace-guessing auto-discovery,
        // since Models/Policies live under App\Domain rather than the default paths.
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Member::class, MemberPolicy::class);
        Gate::policy(OrganizationPosition::class, OrganizationPositionPolicy::class);
        Gate::policy(OrganizationPeriod::class, OrganizationPeriodPolicy::class);
        Gate::policy(StudyCategory::class, StudyCategoryPolicy::class);
        Gate::policy(ActivityCategory::class, ActivityCategoryPolicy::class);
        Gate::policy(GalleryCategory::class, GalleryCategoryPolicy::class);
        Gate::policy(ArticleCategory::class, ArticleCategoryPolicy::class);
        Gate::policy(LibraryCategory::class, LibraryCategoryPolicy::class);
        Gate::policy(StudySchedule::class, StudySchedulePolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Gallery::class, GalleryPolicy::class);
        Gate::policy(LibraryDocument::class, LibraryDocumentPolicy::class);
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        Gate::policy(AttendanceSession::class, AttendanceSessionPolicy::class);
    }
}
