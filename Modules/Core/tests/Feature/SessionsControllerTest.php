<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Core\Controllers\SessionsController;
use Modules\Core\Libraries\Crypt;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * SessionsController Feature Tests.
 *
 * Tests user authentication including login, logout, and password reset.
 */
#[CoversClass(SessionsController::class)]
class SessionsControllerTest extends FeatureTestCase
{
    /**
     * Generate a throttle key matching the format used by SessionsController.
     *
     * @param string $email
     * @param string $ip
     *
     * @return string
     */
    protected function getThrottleKey(string $email, string $ip = '127.0.0.1'): string
    {
        return Str::transliterate(Str::lower($email).'|'.$ip);
    }
    /**
     * Test index redirects to login page.
     */
    #[Group('smoke')]
    #[Test]
    public function it_redirects_to_login_page_from_index(): void
    {
        /** Arrange */
        // No user needed for redirect

        /** Act */
        $response = $this->get(route('sessions.index'));

        /* Assert */
        $response->assertRedirect(route('sessions.login'));
    }

    /**
     * Test login displays login form.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_login_form(): void
    {
        /** Arrange */
        // No authentication needed for login page

        /** Act */
        $response = $this->get(route('sessions.login'));

        /* Assert */
        $response->assertOk();
        $response->assertViewIs('core::users.session_login');
        $response->assertViewHas('login_logo');
    }

    /**
     * Test logout clears session and redirects to login.
     */
    #[Test]
    public function it_clears_session_and_redirects_to_login_on_logout(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $this->actingAs($user);
        session(['user_id' => $user->user_id]);

        /** Act */
        $response = $this->get(route('sessions.logout'));

        /* Assert */
        $response->assertRedirect(route('sessions.login'));
        $this->assertNull(session('user_id'));
    }

    /**
     * Test password reset displays form.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_password_reset_form(): void
    {
        /** Arrange */
        // No authentication needed

        /** Act */
        $response = $this->get(route('sessions.passwordreset'));

        /* Assert */
        $response->assertOk();
        $response->assertViewIs('session_passwordreset');
    }

    /**
     * Test password reset with token displays form.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_password_reset_form_with_token(): void
    {
        /** Arrange */
        $token = 'test-reset-token-123';

        /** Act */
        $response = $this->get(route('sessions.passwordreset', ['token' => $token]));

        /* Assert */
        $response->assertOk();
        $response->assertViewIs('session_new_password');
    }

    /**
     * Test successful authentication with valid credentials.
     */
    #[Test]
    public function it_authenticates_user_with_valid_credentials(): void
    {
        /** Arrange */
        $crypt = new Crypt();
        $salt = $crypt->salt();
        $password = 'test-password-123';
        $hashedPassword = $crypt->generate_password($password, $salt);

        $user = User::factory()->create([
            'user_email' => 'test@example.com',
            'user_password' => $hashedPassword,
            'user_active' => 1,
            'user_type' => 1,
        ]);

        /** Act */
        $response = $this->post(route('sessions.authenticate'), [
            'email' => 'test@example.com',
            'password' => $password,
        ]);

        /* Assert */
        $response->assertRedirect(route('dashboard.index'));
        $this->assertEquals($user->user_id, session('user_id'));
        $this->assertEquals($user->user_email, session('user_email'));
        $this->assertEquals($user->user_type, session('user_type'));
    }

    /**
     * Test authentication fails with invalid password.
     */
    #[Test]
    public function it_rejects_authentication_with_invalid_password(): void
    {
        /** Arrange */
        $crypt = new Crypt();
        $salt = $crypt->salt();
        $hashedPassword = $crypt->generate_password('correct-password', $salt);

        User::factory()->create([
            'user_email' => 'test@example.com',
            'user_password' => $hashedPassword,
            'user_active' => 1,
        ]);

        /** Act & Assert */
        $this->expectException(ValidationException::class);

        $this->post(route('sessions.authenticate'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertNull(session('user_id'));
    }

    /**
     * Test authentication fails for non-existent user.
     */
    #[Test]
    public function it_rejects_authentication_for_nonexistent_user(): void
    {
        /** Arrange */
        // No user created

        /** Act & Assert */
        $this->expectException(ValidationException::class);

        $this->post(route('sessions.authenticate'), [
            'email' => 'nonexistent@example.com',
            'password' => 'any-password',
        ]);

        $this->assertNull(session('user_id'));
    }

    /**
     * Test authentication fails for inactive user.
     */
    #[Test]
    public function it_rejects_authentication_for_inactive_user(): void
    {
        /** Arrange */
        $crypt = new Crypt();
        $salt = $crypt->salt();
        $password = 'test-password-123';
        $hashedPassword = $crypt->generate_password($password, $salt);

        User::factory()->create([
            'user_email' => 'inactive@example.com',
            'user_password' => $hashedPassword,
            'user_active' => 0, // Inactive user
        ]);

        /** Act & Assert */
        $this->expectException(ValidationException::class);

        $this->post(route('sessions.authenticate'), [
            'email' => 'inactive@example.com',
            'password' => $password,
        ]);

        $this->assertNull(session('user_id'));
    }

    /**
     * Test rate limiting blocks too many failed attempts.
     */
    #[Test]
    public function it_rate_limits_after_multiple_failed_attempts(): void
    {
        /** Arrange */
        $email = 'ratelimit@example.com';

        User::factory()->create([
            'user_email' => $email,
            'user_password' => 'hashed-password',
            'user_active' => 1,
        ]);

        // Clear any existing rate limits using the properly formatted throttle key
        $throttleKey = $this->getThrottleKey($email);
        RateLimiter::clear($throttleKey);

        /** Act */
        // Make 5 failed attempts (the rate limit threshold)
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->post(route('sessions.authenticate'), [
                    'email' => $email,
                    'password' => 'wrong-password',
                ]);
            } catch (ValidationException $e) {
                // Expected to fail
            }
        }

        /** Assert */
        // The 6th attempt should be rate limited
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('auth.throttle');

        $this->post(route('sessions.authenticate'), [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    /**
     * Test session regeneration on successful login.
     */
    #[Test]
    public function it_regenerates_session_on_successful_login(): void
    {
        /** Arrange */
        $crypt = new Crypt();
        $salt = $crypt->salt();
        $password = 'test-password-123';
        $hashedPassword = $crypt->generate_password($password, $salt);

        User::factory()->create([
            'user_email' => 'test@example.com',
            'user_password' => $hashedPassword,
            'user_active' => 1,
        ]);

        $oldSessionId = session()->getId();

        /** Act */
        $this->post(route('sessions.authenticate'), [
            'email' => 'test@example.com',
            'password' => $password,
        ]);

        /* Assert */
        $this->assertNotEquals($oldSessionId, session()->getId());
    }

    /**
     * Test rate limiter clears on successful authentication.
     */
    #[Test]
    public function it_clears_rate_limiter_on_successful_authentication(): void
    {
        /** Arrange */
        $crypt = new Crypt();
        $salt = $crypt->salt();
        $password = 'test-password-123';
        $hashedPassword = $crypt->generate_password($password, $salt);

        $user = User::factory()->create([
            'user_email' => 'test@example.com',
            'user_password' => $hashedPassword,
            'user_active' => 1,
        ]);

        // Generate throttle key using the same format as the controller
        $throttleKey = $this->getThrottleKey('test@example.com');

        // Simulate some failed attempts
        RateLimiter::hit($throttleKey);
        RateLimiter::hit($throttleKey);

        $this->assertEquals(2, RateLimiter::attempts($throttleKey));

        /** Act */
        $this->post(route('sessions.authenticate'), [
            'email' => 'test@example.com',
            'password' => $password,
        ]);

        /* Assert */
        $this->assertEquals(0, RateLimiter::attempts($throttleKey));
    }

    /**
     * Test validation requires email and password.
     */
    #[Test]
    public function it_validates_email_and_password_are_required(): void
    {
        /** Act */
        $response = $this->post(route('sessions.authenticate'), []);

        /* Assert */
        $response->assertSessionHasErrors(['email', 'password']);
    }

    /**
     * Test validation requires valid email format.
     */
    #[Test]
    public function it_validates_email_format(): void
    {
        /** Act */
        $response = $this->post(route('sessions.authenticate'), [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        /* Assert */
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test redirect to guest dashboard for type 2 users.
     */
    #[Test]
    public function it_redirects_guest_users_to_guest_dashboard(): void
    {
        /** Arrange */
        $crypt = new Crypt();
        $salt = $crypt->salt();
        $password = 'test-password-123';
        $hashedPassword = $crypt->generate_password($password, $salt);

        User::factory()->create([
            'user_email' => 'guest@example.com',
            'user_password' => $hashedPassword,
            'user_active' => 1,
            'user_type' => 2, // Guest user type
        ]);

        /** Act */
        $response = $this->post(route('sessions.authenticate'), [
            'email' => 'guest@example.com',
            'password' => $password,
        ]);

        /* Assert */
        $response->assertRedirect(route('guest.index'));
    }

    /**
     * Test logout invalidates session and regenerates token.
     */
    #[Test]
    public function it_invalidates_session_and_regenerates_token_on_logout(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        $this->actingAs($user);
        session(['user_id' => $user->user_id, 'user_email' => $user->user_email]);

        $oldToken = csrf_token();

        /** Act */
        $response = $this->post(route('sessions.logout'));

        /* Assert */
        $response->assertRedirect(route('sessions.login'));
        $this->assertNull(session('user_id'));
        $this->assertNull(session('user_email'));
        $this->assertNotEquals($oldToken, csrf_token());
    }
}
