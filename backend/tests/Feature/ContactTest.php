<?php

use App\Domain\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public users can submit contact form successfully', function () {
    $response = $this->postJson('/api/v1/public/contact', [
        'name' => 'Fajar Ahmad',
        'email' => 'fajar@example.com',
        'message' => 'Saya berminat mendaftar sebagai relawan kegiatan Mudes Condet.',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Pesan Anda telah berhasil dikirim. Terima kasih!',
            'data' => [
                'name' => 'Fajar Ahmad',
                'email' => 'fajar@example.com',
                'message' => 'Saya berminat mendaftar sebagai relawan kegiatan Mudes Condet.',
            ],
        ]);

    $this->assertDatabaseHas('contacts', [
        'email' => 'fajar@example.com',
        'name' => 'Fajar Ahmad',
    ]);
});

test('contact form validates required fields and email format', function () {
    $response = $this->postJson('/api/v1/public/contact', [
        'name' => '',
        'email' => 'invalid-email',
        'message' => '',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});
