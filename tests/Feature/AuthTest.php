<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Artisan::call('migrate');
        \Artisan::call('passport:install');
    }

    public function test_that_registration_attempts_are_validated()
    {
        $this
            ->postJson('/api/auth/register')
            ->assertJsonValidationErrors(['name', 'email', 'password']);

        $this
            ->postJson('/api/auth/register', [
                'email' => 'test'
            ])
            ->assertJsonFragment(['The email must be a valid email address.']);

        $this
            ->postJson('/api/auth/register', [
                'password' => 'hunter2'
            ])
            ->assertJsonFragment(['The password confirmation does not match.']);
    }

    public function test_that_successful_registration_attempts_returns_valid_tokens()
    {
        $this->withoutExceptionHandling();

        $response = $this
            ->postJson('/api/auth/register', [
                'name' => 'Test',
                'email' => 'test@test.com',
                'password' => 'hunter2',
                'password_confirmation' => 'hunter2'
            ])
            ->assertJsonFragment(['type' => 'register_success'])
            ->assertJsonStructure([
                'access_token',
                'expires_at',
                'token_type',

                'type',
                'message'
            ]);

        $this
            ->assertNotNull($response->json()['access_token']);

        $this
            ->assertDatabaseHas('users', ['name' => 'Test'])
            ->assertDatabaseHas('oauth_access_tokens', ['user_id' => 1]);
    }
}
