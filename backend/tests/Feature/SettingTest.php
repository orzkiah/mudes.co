<?php

declare(strict_types=1);

use App\Domain\Models\Setting;
use App\Domain\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(SettingSeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('rejects unauthenticated requests', function (): void {
    $this->getJson('/api/v1/admin/settings')->assertStatus(401);
});

it('rejects users without the settings permission', function (): void {
    $this->actingAs($this->editor)
        ->getJson('/api/v1/admin/settings')
        ->assertStatus(403);
});

it('lists settings for a super admin with the pagination envelope', function (): void {
    Setting::factory()->count(3)->create();

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/settings?perPage=5')
        ->assertOk();

    $response->assertJsonPath('success', true)
        ->assertJsonPath('meta.pagination.strategy', 'offset')
        ->assertJsonPath('meta.pagination.perPage', 5);
});

it('filters settings by group', function (): void {
    Setting::factory()->count(2)->create(['group' => 'custom-filter-test']);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/settings?filter[group]=custom-filter-test')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('searches settings by key', function (): void {
    Setting::factory()->create(['key' => 'unique-search-target']);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/settings?search=unique-search-target')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('sorts settings by key ascending', function (): void {
    Setting::factory()->create(['key' => 'aaa-sort-test']);
    Setting::factory()->create(['key' => 'zzz-sort-test']);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/settings?sort=key&perPage=100')
        ->assertOk();

    $keys = collect($response->json('data'))->pluck('key');

    expect($keys->search('aaa-sort-test'))->toBeLessThan($keys->search('zzz-sort-test'));
});

it('creates a setting', function (): void {
    $payload = [
        'key' => 'new.test.setting',
        'value' => 'hello',
        'type' => 'string',
        'group' => 'general',
        'description' => 'A test setting.',
    ];

    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/settings', $payload)
        ->assertCreated();

    $response->assertJsonPath('data.key', 'new.test.setting');
    $this->assertDatabaseHas('settings', ['key' => 'new.test.setting']);
});

it('validates required fields on create', function (): void {
    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/settings', [])
        ->assertStatus(422)
        ->assertJsonPath('errors.type', 'urn:mudes:error:validation-failed')
        ->assertJsonStructure(['errors' => ['fields' => ['key', 'type']]]);
});

it('rejects a duplicate key on create', function (): void {
    Setting::factory()->create(['key' => 'duplicate.key']);

    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/settings', [
            'key' => 'duplicate.key',
            'type' => 'string',
        ])
        ->assertStatus(422);
});

it('masks an encrypted value in responses but stores it encrypted', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/settings', [
            'key' => 'secret.token',
            'value' => 'super-secret-value',
            'type' => 'encrypted',
            'isEncrypted' => true,
        ])
        ->assertCreated();

    $response->assertJsonPath('data.value', '••••••••');

    $stored = Setting::where('key', 'secret.token')->firstOrFail();

    expect($stored->value)->not->toBe('super-secret-value');
    expect(Crypt::decryptString($stored->value))->toBe('super-secret-value');
});

it('updates a setting', function (): void {
    $setting = Setting::factory()->create(['key' => 'update.me', 'value' => 'old']);

    $response = $this->actingAs($this->superAdmin)
        ->putJson("/api/v1/admin/settings/{$setting->id}", [
            'key' => 'update.me',
            'value' => 'new',
            'type' => 'string',
        ])
        ->assertOk();

    $response->assertJsonPath('data.value', 'new');
});

it('soft deletes and restores a setting', function (): void {
    $setting = Setting::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/settings/{$setting->id}")
        ->assertOk();

    $this->assertSoftDeleted('settings', ['id' => $setting->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/settings/{$setting->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('settings', ['id' => $setting->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent setting', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/settings/'.Str::uuid7())
        ->assertStatus(404)
        ->assertJsonPath('errors.type', 'urn:mudes:error:not-found');
});

it('records audit columns on create', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/settings', [
            'key' => 'audit.test',
            'type' => 'string',
        ])
        ->assertCreated();

    $setting = Setting::findOrFail($response->json('data.id'));

    expect($setting->created_by)->toBe($this->superAdmin->id);
    expect($setting->updated_by)->toBe($this->superAdmin->id);
});
