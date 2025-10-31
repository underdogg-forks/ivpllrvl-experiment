<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Controllers\SessionsController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
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
     * Test index redirects to login page.
     */
    #[Test]
    public function it_redirects_to_login_page_from_index(): void
    {
        /** Arrange */
        // No user needed for redirect

        /** Act */
        $response = $this->get(route('sessions.index'));

        /** Assert */
        $response->assertRedirect(route('sessions.login'));
    }

    /**
     * Test login displays login form.
     */
    #[Test]
    public function it_displays_login_form(): void
    {
        /** Arrange */
        // No authentication needed for login page

        /** Act */
        $response = $this->get(route('sessions.login'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('sessions::login');
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

        /** Assert */
        $response->assertRedirect(route('sessions.login'));
        $this->assertNull(session('user_id'));
    }

    /**
     * Test password reset displays form.
     */
    #[Test]
    public function it_displays_password_reset_form(): void
    {
        /** Arrange */
        // No authentication needed

        /** Act */
        $response = $this->get(route('sessions.passwordreset'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('sessions::passwordreset');
    }

    /**
     * Test password reset with token displays form.
     */
    #[Test]
    public function it_displays_password_reset_form_with_token(): void
    {
        /** Arrange */
        $token = 'test-reset-token-123';

        /** Act */
        $response = $this->get(route('sessions.passwordreset', ['token' => $token]));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('sessions::passwordreset');
    }
}
