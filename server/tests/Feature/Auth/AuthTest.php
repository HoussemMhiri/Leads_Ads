<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;



beforeEach(function () {
    $this->startSession();
    $this->csrfToken = session()->token();
});

function withCsrfHeaders()
{
    return test()
        ->withHeader('Accept', 'application/json')
        ->withHeader('Referer', 'localhost:5173')
        ->withHeader('X-XSRF-TOKEN', test()->csrfToken)
        ->withSession(['_token' => test()->csrfToken]);
}

it('registers a user and logs them in', function () {
    $payload = [
        'name' => 'Houssem',
        'email' => 'houssem@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    withCsrfHeaders()
        ->postJson('/api/auth/register', $payload)
        ->assertCreated()
        ->assertJsonStructure(['user' => ['id', 'name', 'email']])
        ->assertJsonMissing(['token']);

    $this->assertDatabaseHas('users', ['email' => 'houssem@test.com'])
        ->assertAuthenticated();
});

it('logs in a user and creates a session', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    withCsrfHeaders()
        ->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])
        ->assertOk()
        ->assertJsonStructure(['user' => ['id', 'name', 'email']])
        ->assertJsonMissing(['token']);

    $this->assertAuthenticatedAs($user);
});

it('fails login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'test@test.com',
        'password' => Hash::make('correct-password'),
    ]);

    withCsrfHeaders()
        ->postJson('/api/auth/login', [
            'email' => 'test@test.com',
            'password' => 'wrong-password',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');

    $this->assertGuest();
});

it('logs out the authenticated user', function () {
    $user = User::factory()->create();

    withCsrfHeaders()
        ->actingAs($user)

        ->postJson('/api/auth/logout')
        ->assertNoContent();
});

it('regenerates session on login', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $oldSessionId = session()->getId();

    withCsrfHeaders()
        ->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])
        ->assertOk();

    expect(session()->getId())
        ->not->toBe($oldSessionId);

    $this->assertAuthenticatedAs($user);
});
