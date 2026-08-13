<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(MemberSeeder::class);
        $this->call(OrganizationPositionSeeder::class);
        $this->call(StudyCategorySeeder::class);
        $this->call(ActivityCategorySeeder::class);
        $this->call(GalleryCategorySeeder::class);
        $this->call(ArticleCategorySeeder::class);
        $this->call(LibraryCategorySeeder::class);
        $this->call(StudyScheduleSeeder::class);
        $this->call(ActivitySeeder::class);
        $this->call(ArticleSeeder::class);
        $this->call(GallerySeeder::class);
        $this->call(LibraryDocumentSeeder::class);
        $this->call(AnnouncementSeeder::class);
        $this->call(AttendanceSessionSeeder::class);
        $this->call(DashboardAnalyticsSeeder::class);
    }
}
