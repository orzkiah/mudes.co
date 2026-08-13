<?php

declare(strict_types=1);

use App\Domain\Models\Article;
use App\Domain\Models\User;
use Database\Seeders\ArticleSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(ArticleSeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    // Editor genuinely has articles.create (PROJECT_SPECIFICATION.md §3.9 -
    // "Write/Create: Editor, Humas, Super Admin"), unlike most other
    // content modules where editor is the non-writer example. 'multimedia'
    // is not a writer here.
    $this->nonWriter = User::factory()->create();
    $this->nonWriter->assignRole('multimedia');
});

it('lists only published articles publicly', function (): void {
    Article::factory()->create(['status' => 'draft']);

    $response = $this->getJson('/api/v1/public/articles')->assertOk();

    expect(collect($response->json('data'))->every(fn ($a) => $a['status'] === 'published'))->toBeTrue();
});

it('hides a draft article from the public detail endpoint', function (): void {
    $draft = Article::factory()->create(['status' => 'draft', 'slug' => 'draft-article']);

    $this->getJson('/api/v1/public/articles/draft-article')->assertStatus(404);
});

it('increments view count when a published article is viewed publicly', function (): void {
    $article = Article::factory()->create(['status' => 'published', 'slug' => 'view-count-test', 'published_at' => now(), 'view_count' => 0]);

    $this->getJson('/api/v1/public/articles/view-count-test')->assertOk();

    expect($article->fresh()->view_count)->toBe(1);
});

it('allows a non-writer role to view but not write (read-all-write-restricted matrix)', function (): void {
    $this->actingAs($this->nonWriter)->getJson('/api/v1/admin/articles')->assertOk();

    $this->actingAs($this->nonWriter)
        ->postJson('/api/v1/admin/articles', ['title' => 'Should Fail'])
        ->assertStatus(403);
});

it('creates a draft article', function (): void {
    $article = Article::factory()->create();

    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/articles', [
            'articleCategoryId' => $article->article_category_id,
            'title' => 'New Draft Article',
            'body' => 'Article body content.',
        ])
        ->assertCreated();

    $response->assertJsonPath('data.status', 'draft');
});

it('requires publishedAt when status is scheduled', function (): void {
    $article = Article::factory()->create();

    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/articles', [
            'articleCategoryId' => $article->article_category_id,
            'title' => 'Scheduled Without Date',
            'body' => 'Body.',
            'status' => 'scheduled',
        ])
        ->assertStatus(422);
});

it('auto-publishes scheduled articles whose time has arrived via the artisan command', function (): void {
    $due = Article::factory()->create(['status' => 'scheduled', 'published_at' => now()->subMinute()]);
    $notYetDue = Article::factory()->create(['status' => 'scheduled', 'published_at' => now()->addDay()]);

    $this->artisan('articles:publish-scheduled')->assertSuccessful();

    expect($due->fresh()->status->value)->toBe('published');
    expect($notYetDue->fresh()->status->value)->toBe('scheduled');
});

it('soft deletes and restores an article', function (): void {
    $article = Article::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/articles/{$article->id}")
        ->assertOk();

    $this->assertSoftDeleted('articles', ['id' => $article->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/articles/{$article->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('articles', ['id' => $article->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent article', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/articles/'.Str::uuid7())
        ->assertStatus(404);
});
