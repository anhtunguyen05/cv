<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ThrottleRequests::shouldHashKeys(false);
        $this->withSession(['_token' => 'test-csrf-token']);
        $clientIp = '2001:db8::'.dechex(crc32($this->name()));
        $this->withServerVariables(['REMOTE_ADDR' => $clientIp]);
        RateLimiter::clear('registration-ip:ip:'.hash_hmac('sha256', $clientIp, (string) config('app.key')));

        foreach ([
            '',
            'nguyen.anhtu@example.test',
            'not-an-email',
            'existing@example.test',
            'current@example.test',
            'existing-session@example.test',
            'second@example.test',
            'no-csrf@example.test',
            'throttle-1@example.test',
            'throttle-2@example.test',
            'throttle-3@example.test',
            'throttle-4@example.test',
            'throttle-5@example.test',
            'throttle-6@example.test',
        ] as $email) {
            RateLimiter::clear('registration-email:email:'.hash_hmac('sha256', $email, (string) config('app.key')));
        }
    }

    public function test_a_guest_can_register_with_a_canonical_public_projection(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => ' Nguyen Anh Tu ',
            'email' => ' Nguyen.Anhtu@Example.Test ',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ], $this->spaHeaders());

        $response->assertCreated()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.user.name', 'Nguyen Anh Tu')
            ->assertJsonPath('data.user.email', 'nguyen.anhtu@example.test');

        $this->assertSame(['id', 'name', 'email'], array_keys($response->json('data.user')));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'nguyen.anhtu@example.test']);
        $this->assertNotSame('secure-password-123', User::query()->firstOrFail()->password);
    }

    public function test_invalid_registration_returns_field_errors_without_creating_a_user(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ], $this->spaHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_FAILED')
            ->assertJsonStructure(['code', 'message', 'details']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_duplicate_email_is_generic_and_does_not_disclose_the_existing_user(): void
    {
        User::factory()->create(['email' => 'existing@example.test']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Another User',
            'email' => ' EXISTING@example.test ',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ], $this->spaHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_FAILED')
            ->assertJsonPath('details.email.0.code', 'INVALID');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_current_account_requires_a_session_and_returns_the_public_projection(): void
    {
        $this->getJson('/api/v1/auth/me', $this->spaHeaders())
            ->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Current User',
            'email' => 'current@example.test',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ], $this->spaHeaders())->assertCreated();

        $response = $this->getJson('/api/v1/auth/me', $this->spaHeaders());

        $response->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.user.email', 'current@example.test');
        $this->assertSame(['id', 'name', 'email'], array_keys($response->json('data.user')));
    }

    public function test_csrf_bootstrap_is_available_for_the_browser_client(): void
    {
        $this->get('/sanctum/csrf-cookie', $this->spaHeaders(false))
            ->assertNoContent();
    }

    public function test_registration_rejects_an_invalid_csrf_token(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'No CSRF User',
            'email' => 'no-csrf@example.test',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ], $this->spaHeaders(false))
            ->assertStatus(419)
            ->assertJsonPath('code', 'SESSION_EXPIRED');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_bearer_tokens_are_not_an_authentication_path(): void
    {
        $this->getJson('/api/v1/auth/me', [
            ...$this->spaHeaders(false),
            'Authorization' => 'Bearer intentionally-not-supported',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');
    }

    public function test_an_authenticated_caller_cannot_register_another_account(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Existing User',
            'email' => 'existing-session@example.test',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ], $this->spaHeaders())->assertCreated();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Second User',
            'email' => 'second@example.test',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ], $this->spaHeaders())
            ->assertConflict()
            ->assertJsonPath('code', 'AUTHENTICATED_REGISTRATION_FORBIDDEN');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_throttle_returns_retry_after_without_disclosing_identity(): void
    {
        for ($index = 1; $index <= 5; $index++) {
            $this->postJson('/api/v1/auth/register', [
                'name' => "Throttle User {$index}",
                'email' => "throttle-{$index}@example.test",
                'password' => 'secure-password-123',
                'password_confirmation' => 'secure-password-123',
            ], $this->spaHeaders())->assertCreated();

            auth()->logout();
        }

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Throttle User 6',
            'email' => 'throttle-6@example.test',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ], $this->spaHeaders());

        $response->assertTooManyRequests()
            ->assertJsonPath('code', 'THROTTLED')
            ->assertHeader('Retry-After');
    }

    public function test_malformed_json_returns_the_contract_error(): void
    {
        $response = $this->call(
            'POST',
            '/api/v1/auth/register',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name":',
        );

        $response->assertBadRequest()
            ->assertJsonPath('code', 'INVALID_REQUEST_BODY');
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * @return array<string, string>
     */
    private function spaHeaders(bool $csrf = true): array
    {
        $headers = [
            'Origin' => 'http://localhost:5173',
            'Referer' => 'http://localhost:5173/register',
        ];

        if ($csrf) {
            $headers['X-CSRF-TOKEN'] = session()->token();
        }

        return $headers;
    }
}
