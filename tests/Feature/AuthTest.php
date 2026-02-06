<?php

namespace Tests\Feature;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $action = app(RegisterUserAction::class);
        $result = $action->execute([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertArrayHasKey('access_token', $result);
        $this->assertEquals('Bearer', $result['token_type']);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_register_fails_without_password_confirmation()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    public function test_register_fails_with_mismatch_password_confirmation()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong-password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    public function test_user_can_login()
    {
        $registerAction = app(RegisterUserAction::class);
        $registerAction->execute([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $loginAction = app(LoginUserAction::class);
        $result = $loginAction->execute([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertArrayHasKey('access_token', $result);
        $this->assertEquals('Bearer', $result['token_type']);
    }

    public function test_user_can_logout()
    {
        $registerAction = app(RegisterUserAction::class);
        $result = $registerAction->execute([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $token = $result['access_token'];
        
        $accessToken = $user->tokens()->where('id', explode('|', $token)[0])->first();
        $user->withAccessToken($accessToken);

        $logoutAction = app(LogoutUserAction::class);
        $logoutAction->execute($user);

        $this->assertCount(0, $user->tokens);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $action = app(RegisterUserAction::class);
        $action->execute([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        try {
            app(LoginUserAction::class)->execute([
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
            $this->fail('Expected HttpResponseException was not thrown');
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
        }
    }

    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ]);
    }
}
