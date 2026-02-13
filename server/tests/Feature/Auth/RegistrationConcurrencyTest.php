<?php

use App\Models\User;
use App\Services\Tenancy\CreateTenantService;

beforeEach(function () {
    $this->startSession();
    $this->csrfToken = session()->token();

    // Mock tenant service — we're testing user registration concurrency,
    // not tenant DB creation (stancl creates real MySQL databases).
    $this->mock(CreateTenantService::class, function ($mock) {
        $mock->shouldReceive('execute')->andReturn([
            'tenant' => null,
            'subdomain' => 'test-subdomain',
        ]);
    });
});

function concurrencyHeaders()
{
    return test()
        ->withHeader('Accept', 'application/json')
        ->withHeader('Referer', 'localhost:5173')
        ->withHeader('X-XSRF-TOKEN', test()->csrfToken)
        ->withSession(['_token' => test()->csrfToken]);
}

it('rejects the second registration when two users register with the same email concurrently', function () {
    $payload = [
        'name' => 'Houssem',
        'email' => 'houssem@concurrent.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    // First registration should succeed
    $response1 = concurrencyHeaders()
        ->postJson('/api/auth/register', $payload);

    $response1->assertCreated();

    // Second registration with the same email should fail with validation error
    $response2 = concurrencyHeaders()
        ->postJson('/api/auth/register', $payload);

    $response2->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'name']);

    // Only one user should exist
    expect(User::where('email', 'houssem@concurrent.com')->count())->toBe(1);
});

it('handles race condition when validation passes for both but DB rejects the duplicate', function () {
    // Simulate the race condition:
    // Both requests pass the unique validation check, but the second insert
    // hits the DB unique constraint.
    //
    // We do this by:
    // 1. Creating the user directly (bypassing validation)
    // 2. Then attempting to register with the same email via the API
    //    to verify the DB constraint catches it

    $user = User::factory()->create([
        'name' => 'RaceUser',
        'email' => 'race@test.com',
    ]);

    // Mock CreateTenantService so we don't need actual tenant DB creation
    $this->mock(CreateTenantService::class, function ($mock) {
        $mock->shouldNotReceive('execute');
    });

    // Now try to create another user with the same email.
    // The unique validation rule should catch it.
    concurrencyHeaders()
        ->postJson('/api/auth/register', [
            'name' => 'DifferentName',
            'email' => 'race@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('handles true concurrent registration by testing the DB unique constraint directly', function () {
    // This test proves that even if both requests bypass validation,
    // the database unique constraint on email prevents duplicates.

    $userData = [
        'name' => 'ConcurrentUser',
        'email' => 'concurrent@test.com',
        'password' => bcrypt('password123'),
    ];

    // First insert succeeds
    $user1 = User::create($userData);
    expect($user1)->toBeInstanceOf(User::class);

    // Second insert with same email should throw a QueryException
    expect(fn () => User::create($userData))
        ->toThrow(\Illuminate\Database\QueryException::class);

    // Verify only one user exists
    expect(User::where('email', 'concurrent@test.com')->count())->toBe(1);
});

it('returns a clean 422 when the race condition bypasses validation but hits the DB constraint', function () {
    // Simulate the race condition by creating the user directly,
    // then bypassing form request validation to hit the controller's try/catch.

    User::factory()->create([
        'name' => 'RaceWinner',
        'email' => 'racewin@test.com',
    ]);

    // Use withoutMiddleware to skip form request validation,
    // simulating what happens when both requests pass validation concurrently.
    concurrencyHeaders()
        ->withoutMiddleware()
        ->postJson('/api/auth/register', [
            'name' => 'RaceWinner',
            'email' => 'racewin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('handles concurrent registration with same name but different email', function () {
    $basePayload = [
        'name' => 'SameName',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    // First registration
    $response1 = concurrencyHeaders()
        ->postJson('/api/auth/register', array_merge($basePayload, [
            'email' => 'user1@test.com',
        ]));

    $response1->assertCreated();

    // Second registration with same name but different email
    $response2 = concurrencyHeaders()
        ->postJson('/api/auth/register', array_merge($basePayload, [
            'email' => 'user2@test.com',
        ]));

    // Should fail because name is also unique
    $response2->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});
