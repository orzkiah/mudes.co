<?php

declare(strict_types=1);

use App\Domain\Models\Member;
use App\Domain\Models\User;
use Database\Seeders\MemberSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(MemberSeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('rejects unauthenticated requests', function (): void {
    $this->getJson('/api/v1/admin/members')->assertStatus(401);
});

it('rejects users without the members permission', function (): void {
    $this->actingAs($this->editor)
        ->getJson('/api/v1/admin/members')
        ->assertStatus(403);
});

it('lists members for a super admin with the pagination envelope', function (): void {
    Member::factory()->count(3)->create();

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/members?perPage=5')
        ->assertOk();

    $response->assertJsonPath('success', true)
        ->assertJsonPath('meta.pagination.strategy', 'offset')
        ->assertJsonPath('meta.pagination.perPage', 5);
});

it('filters members by status', function (): void {
    Member::factory()->count(2)->create(['status' => 'alumni']);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/members?filter[status]=alumni')
        ->assertOk();

    expect(collect($response->json('data'))->every(fn ($m) => $m['status'] === 'alumni'))->toBeTrue();
});

it('filters members by gender', function (): void {
    Member::factory()->count(2)->create(['gender' => 'female']);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/members?filter[gender]=female')
        ->assertOk();

    expect(collect($response->json('data'))->every(fn ($m) => $m['gender'] === 'female'))->toBeTrue();
});

it('searches members by full name', function (): void {
    Member::factory()->create(['full_name' => 'Unique Search Member']);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/members?search=Unique Search Member')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('sorts members by full name ascending', function (): void {
    Member::factory()->create(['full_name' => 'Aaa Sort Test']);
    Member::factory()->create(['full_name' => 'Zzz Sort Test']);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/members?sort=full_name&perPage=100')
        ->assertOk();

    $names = collect($response->json('data'))->pluck('fullName');

    expect($names->search('Aaa Sort Test'))->toBeLessThan($names->search('Zzz Sort Test'));
});

it('creates a member', function (): void {
    $payload = [
        'fullName' => 'Budi Santoso',
        'gender' => 'male',
        'birthDate' => '2005-06-01',
        'phone' => '+6281234567890',
        'joinDate' => '2024-01-01',
    ];

    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/members', $payload)
        ->assertCreated();

    $response->assertJsonPath('data.fullName', 'Budi Santoso')
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.userId', null);
    $this->assertDatabaseHas('members', ['full_name' => 'Budi Santoso']);
});

it('validates required fields and gender enum on create', function (): void {
    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/members', ['gender' => 'not-a-gender'])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['fields' => ['fullName', 'gender']]]);
});

it('rejects a photoMediaId that does not reference an existing member-photo media item', function (): void {
    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/members', [
            'fullName' => 'Photo Test Member',
            'photoMediaId' => (string) Str::uuid7(),
        ])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['fields' => ['photoMediaId']]]);
});

it('ignores a userId passed in the request body since it is read-only', function (): void {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/members', [
            'fullName' => 'Readonly UserId Test',
            'userId' => $otherUser->id,
        ])
        ->assertCreated();

    $response->assertJsonPath('data.userId', null);
});

it('updates a member', function (): void {
    $member = Member::factory()->create(['full_name' => 'Update Me', 'status' => 'active']);

    $response = $this->actingAs($this->superAdmin)
        ->putJson("/api/v1/admin/members/{$member->id}", [
            'fullName' => 'Update Me',
            'status' => 'alumni',
        ])
        ->assertOk();

    $response->assertJsonPath('data.status', 'alumni');
});

it('soft deletes and restores a member', function (): void {
    $member = Member::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/members/{$member->id}")
        ->assertOk();

    $this->assertSoftDeleted('members', ['id' => $member->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/members/{$member->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('members', ['id' => $member->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent member', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/members/'.Str::uuid7())
        ->assertStatus(404);
});

it('records audit columns on create', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/members', ['fullName' => 'Audit Test Member'])
        ->assertCreated();

    $member = Member::findOrFail($response->json('data.id'));

    expect($member->created_by)->toBe($this->superAdmin->id);
    expect($member->updated_by)->toBe($this->superAdmin->id);
});
