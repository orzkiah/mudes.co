<?php

declare(strict_types=1);

use App\Domain\Models\AttendanceSession;
use App\Domain\Models\Member;
use App\Domain\Models\User;
use Database\Seeders\AttendanceSessionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(AttendanceSessionSeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('rejects unauthenticated admin requests', function (): void {
    $this->getJson('/api/v1/admin/attendance/sessions')->assertStatus(401);
});

it('rejects editor without attendance-sessions permission', function (): void {
    $this->actingAs($this->editor)
        ->getJson('/api/v1/admin/attendance/sessions')
        ->assertStatus(403);
});

it('creates an attendance session with an auto-generated qr token', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/attendance/sessions', [
            'sourceType' => 'activity',
            'sourceId' => (string) Str::uuid7(),
            'opensAt' => now()->toIso8601String(),
            'closesAt' => now()->addHours(2)->toIso8601String(),
        ])
        ->assertCreated();

    expect($response->json('data.qrToken'))->not->toBeEmpty();
});

it('allows a member to self check-in via the public qr endpoint within the window', function (): void {
    $member = Member::factory()->create();
    $session = AttendanceSession::factory()->create(['opens_at' => now()->subMinute(), 'closes_at' => now()->addHour()]);

    $this->postJson('/api/v1/public/attendance/check-in', [
        'qrToken' => $session->qr_token,
        'memberId' => $member->id,
    ])->assertCreated()
        ->assertJsonPath('data.method', 'qr');

    $this->assertDatabaseHas('attendances', ['attendance_session_id' => $session->id, 'member_id' => $member->id]);
});

it('rejects a public check-in outside the session window', function (): void {
    $member = Member::factory()->create();
    $session = AttendanceSession::factory()->create(['opens_at' => now()->subHours(3), 'closes_at' => now()->subHours(2)]);

    $this->postJson('/api/v1/public/attendance/check-in', [
        'qrToken' => $session->qr_token,
        'memberId' => $member->id,
    ])->assertStatus(409)
        ->assertJsonPath('errors.type', 'urn:mudes:error:attendance-window-closed');
});

it('rejects a duplicate check-in for the same member and session', function (): void {
    $member = Member::factory()->create();
    $session = AttendanceSession::factory()->create(['opens_at' => now()->subMinute(), 'closes_at' => now()->addHour()]);

    $this->postJson('/api/v1/public/attendance/check-in', ['qrToken' => $session->qr_token, 'memberId' => $member->id])
        ->assertCreated();

    $this->postJson('/api/v1/public/attendance/check-in', ['qrToken' => $session->qr_token, 'memberId' => $member->id])
        ->assertStatus(409)
        ->assertJsonPath('errors.type', 'urn:mudes:error:duplicate');
});

it('allows manual check-in by name for members without a device', function (): void {
    $session = AttendanceSession::factory()->create();

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/attendance/sessions/{$session->id}/check-in", ['memberName' => 'Walk-in Guest'])
        ->assertCreated()
        ->assertJsonPath('data.memberName', 'Walk-in Guest')
        ->assertJsonPath('data.method', 'manual');
});

it('lists the session roster', function (): void {
    $session = AttendanceSession::factory()->create();
    $member = Member::factory()->create();

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/attendance/sessions/{$session->id}/check-in", ['memberId' => $member->id])
        ->assertCreated();

    $this->actingAs($this->superAdmin)
        ->getJson("/api/v1/admin/attendance/sessions/{$session->id}/roster")
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('returns 404 for a nonexistent attendance session', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/attendance/sessions/'.Str::uuid7())
        ->assertStatus(404);
});
