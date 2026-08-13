<?php

declare(strict_types=1);

use App\Domain\Models\ArticleCategory;
use App\Domain\Models\User;
use Database\Seeders\ArticleCategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(ArticleCategorySeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('lists active article categories publicly without authentication', function (): void {
    $this->getJson('/api/v1/public/article-categories')->assertOk();
});

it('rejects unauthenticated admin requests', function (): void {
    $this->getJson('/api/v1/admin/article-categories')->assertStatus(401);
});

it('allows editor to view but not write (read-all-write-restricted matrix)', function (): void {
    $this->actingAs($this->editor)->getJson('/api/v1/admin/article-categories')->assertOk();

    $this->actingAs($this->editor)
        ->postJson('/api/v1/admin/article-categories', ['name' => 'Should Fail'])
        ->assertStatus(403);
});

it('creates an article category with auto-generated slug', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/article-categories', ['name' => 'Opini'])
        ->assertCreated();

    $response->assertJsonPath('data.slug', 'opini')
        ->assertJsonPath('data.articleCount', 0);
});

it('partially updates an article category via PATCH', function (): void {
    $category = ArticleCategory::factory()->create(['name' => 'Original', 'is_active' => true]);

    $this->actingAs($this->superAdmin)
        ->patchJson("/api/v1/admin/article-categories/{$category->id}", ['isActive' => false])
        ->assertOk()
        ->assertJsonPath('data.isActive', false)
        ->assertJsonPath('data.name', 'Original');
});

it('soft deletes and restores an article category', function (): void {
    $category = ArticleCategory::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/article-categories/{$category->id}")
        ->assertOk();

    $this->assertSoftDeleted('article_categories', ['id' => $category->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/article-categories/{$category->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('article_categories', ['id' => $category->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent article category', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/article-categories/'.Str::uuid7())
        ->assertStatus(404);
});
