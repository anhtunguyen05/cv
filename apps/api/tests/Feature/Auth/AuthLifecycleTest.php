<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class AuthLifecycleTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, mixed> */
    private array $fixture;

    protected function setUp(): void
    {
        parent::setUp();

        ThrottleRequests::shouldHashKeys(false);
        $fixturePath = (string) ($_ENV['AUTH_CONTRACT_FIXTURE_PATH'] ?? dirname(base_path(), 2).'/docs/contracts/auth/fixtures/registration-v1.json');
        if (! is_file($fixturePath) || ! is_readable($fixturePath)) {
            self::fail('Authentication fixture is unavailable or unreadable: '.$fixturePath);
        }

        $this->fixture = json_decode(
            (string) file_get_contents($fixturePath),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $this->withSession(['_token' => 'test-csrf-token']);
        $clientIp = '2001:db8::'.dechex(crc32($this->name()));
        $this->withServerVariables(['REMOTE_ADDR' => $clientIp]);

        foreach (['login-ip', 'login-email'] as $limiter) {
            $key = $limiter === 'login-ip' ? $clientIp : '';
            RateLimiter::clear($limiter.':'.($limiter === 'login-ip' ? 'ip' : 'email').':'
                .hash_hmac('sha256', $key, (string) config('app.key')));
        }

        foreach (['', 'not-an-email', 'existing@example.test', 'unknown@example.test', 'wrong@example.test', 'protected@example.test', 'target@example.test', 'login-protected@example.test'] as $email) {
            RateLimiter::clear('login-email:email:'.hash_hmac('sha256', $email, (string) config('app.key')));
        }

        for ($index = 1; $index <= 6; $index++) {
            $email = "throttle-{$index}@example.test";
            RateLimiter::clear('login-email:email:'.hash_hmac('sha256', $email, (string) config('app.key')));
        }
    }

    protected function tearDown(): void
    {
        ThrottleRequests::shouldHashKeys(true);
        parent::tearDown();
    }

    public function test_valid_fixture_login_regenerates_the_session_and_authenticates_me(): void
    {
        $user = User::factory()->create([
            'name' => 'Existing User',
            'email' => 'existing@example.test',
            'password' => 'secure-password-123',
        ]);
        $oldSessionId = session()->getId();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => ' EXISTING@example.test ',
            'password' => 'secure-password-123',
        ], $this->spaHeaders());

        $response->assertStatus($this->fixtureExpect('login-valid-credentials', 'status'))
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.user.id', (string) $user->getKey());
        $this->assertNotSame($oldSessionId, session()->getId());
        $this->assertAuthenticatedAs($user);
        $this->assertSame(['id', 'name', 'email'], array_keys($response->json('data.user')));

        $this->getJson('/api/v1/auth/me', $this->spaHeaders())->assertOk();
    }

    public function test_unknown_email_and_wrong_password_have_the_same_generic_failure(): void
    {
        User::factory()->create(['email' => 'wrong@example.test', 'password' => 'secure-password-123']);

        $unknown = $this->postJson('/api/v1/auth/login', [
            'email' => 'unknown@example.test',
            'password' => 'secure-password-123',
        ], $this->spaHeaders());
        $wrongPassword = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@example.test',
            'password' => 'wrong-password-123',
        ], $this->spaHeaders());

        $this->assertSame($unknown->json(), $wrongPassword->json());
        $unknown->assertStatus($this->fixtureExpect('login-unknown-email', 'status'))
            ->assertJsonPath('code', $this->fixtureExpect('login-unknown-email', 'error.code'));
        $unknown->assertJsonPath('message', 'The email or password is incorrect.')
            ->assertJsonMissingPath('details');
        $this->assertGuest();
    }

    public function test_invalid_login_input_and_malformed_json_are_rejected_without_authentication(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'not-an-email',
            'password' => '',
        ], $this->spaHeaders())
            ->assertStatus($this->fixtureExpect('login-invalid-fields', 'status'))
            ->assertJsonPath('code', 'VALIDATION_FAILED')
            ->assertJsonStructure(['details' => ['email', 'password']]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'existing@example.test',
            'password' => 'secure-password-123',
            'remember' => true,
        ], $this->spaHeaders())
            ->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_FAILED');

        $this->call(
            'POST',
            '/api/v1/auth/login',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_CSRF_TOKEN' => session()->token()],
            '{"email":',
        )->assertStatus($this->fixtureExpect('login-malformed-body', 'status'))
            ->assertJsonPath('code', 'INVALID_REQUEST_BODY');

        $this->assertGuest();
    }

    public function test_login_throttle_returns_fixture_error_and_retry_after(): void
    {
        for ($index = 1; $index <= 5; $index++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => "throttle-{$index}@example.test",
                'password' => 'secure-password-123',
            ], $this->spaHeaders());
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'throttle-6@example.test',
            'password' => 'secure-password-123',
        ], $this->spaHeaders());

        $response->assertStatus($this->fixtureExpect('login-throttled', 'status'))
            ->assertJsonPath('code', $this->fixtureExpect('login-throttled', 'error.code'))
            ->assertHeader('Retry-After');
        $this->assertGuest();
    }

    public function test_login_email_throttle_is_independent_of_ip_throttle_and_uses_canonical_email(): void
    {
        for ($index = 1; $index <= 3; $index++) {
            $this->withServerVariables(['REMOTE_ADDR' => '2001:db8::'.(100 + $index)]);
            $this->postJson('/api/v1/auth/login', [
                'email' => $index === 1 ? ' TARGET@example.test ' : 'target@example.test',
                'password' => 'secure-password-123',
            ], $this->spaHeaders());
        }

        $this->withServerVariables(['REMOTE_ADDR' => '2001:db8::104']);
        $this->postJson('/api/v1/auth/login', [
            'email' => 'target@example.test',
            'password' => 'secure-password-123',
        ], $this->spaHeaders())
            ->assertStatus($this->fixtureExpect('login-throttled', 'status'))
            ->assertJsonPath('code', $this->fixtureExpect('login-throttled', 'error.code'))
            ->assertHeader('Retry-After');
    }

    public function test_login_rejects_invalid_csrf_and_bearer_authentication(): void
    {
        $user = User::factory()->create(['email' => 'login-protected@example.test']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'login-protected@example.test',
            'password' => 'wrong-password-123',
        ], $this->spaHeaders(false))
            ->assertStatus($this->fixtureExpect('logout-invalid-csrf', 'status'))
            ->assertJsonPath('code', $this->fixtureExpect('logout-invalid-csrf', 'error.code'));
        $this->assertGuest();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secure-password-123',
        ], [
            ...$this->spaHeaders(),
            'Authorization' => 'Bearer intentionally-not-supported',
        ])->assertUnauthorized()->assertJsonPath('code', 'UNAUTHENTICATED');
        $this->assertGuest();
    }

    public function test_logout_is_idempotent_invalidates_session_and_preserves_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Logout User',
            'email' => 'logout@example.test',
            'password' => 'secure-password-123',
        ]);
        $this->actingAs($user, 'web');
        $before = $user->fresh()->toArray();
        $oldSessionId = session()->getId();
        $oldCsrfToken = session()->token();

        $this->postJson('/api/v1/auth/logout', [], $this->spaHeaders())
            ->assertNoContent();
        $this->assertNotSame($oldSessionId, session()->getId());
        $this->assertNotSame($oldCsrfToken, session()->token());
        $this->assertGuest('web');
        $this->getJson('/api/v1/auth/me', $this->spaHeaders())
            ->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');
        $this->assertSame($before, $user->fresh()->toArray());

        $this->postJson('/api/v1/auth/logout', [], $this->spaHeaders())->assertNoContent();
    }

    public function test_logout_rejects_invalid_csrf_and_bearer_authentication(): void
    {
        $user = User::factory()->create(['email' => 'protected@example.test']);
        $this->actingAs($user, 'web');

        $this->postJson('/api/v1/auth/logout', [], $this->spaHeaders(false))
            ->assertStatus($this->fixtureExpect('logout-invalid-csrf', 'status'))
            ->assertJsonPath('code', $this->fixtureExpect('logout-invalid-csrf', 'error.code'));
        $this->assertAuthenticatedAs($user, 'web');

        $this->postJson('/api/v1/auth/logout', [], [
            ...$this->spaHeaders(),
            'Authorization' => 'Bearer intentionally-not-supported',
        ])->assertUnauthorized()->assertJsonPath('code', 'UNAUTHENTICATED');
        $this->assertAuthenticatedAs($user, 'web');
    }

    /** @return array<string, string> */
    private function spaHeaders(bool $csrf = true): array
    {
        $headers = [
            'Origin' => 'http://localhost:5173',
            'Referer' => 'http://localhost:5173/login',
        ];

        if ($csrf) {
            $headers['X-CSRF-TOKEN'] = session()->token();
        }

        return $headers;
    }

    private function fixtureExpect(string $id, string $path): mixed
    {
        $fixture = collect($this->fixture['fixtures'])->firstWhere('id', $id);
        $value = $fixture['expect'];

        foreach (explode('.', $path) as $segment) {
            $value = $value[$segment];
        }

        return $value;
    }
}
