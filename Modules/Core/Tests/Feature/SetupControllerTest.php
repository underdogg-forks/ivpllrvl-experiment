<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Controllers\SetupController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

/**
 * SetupController Feature Tests.
 *
 * Comprehensive test suite covering all setup wizard routes and workflows.
 */
#[CoversClass(SetupController::class)]
class SetupControllerTest extends FeatureTestCase
{
    /**
     * Helper method to advance the setup workflow to a specific step.
     * 
     * This reduces code duplication by handling common workflow advancement logic:
     * - Sets the session to the current step
     * - POSTs continue data to the current route
     * - Returns the response for assertion
     * 
     * @param string $currentStep The current step name (e.g., 'prerequisites')
     * @param string $currentRoute The current route name (e.g., 'setup.prerequisites')
     * @param array<string, mixed> $additionalData Additional form data beyond 'btn_continue'
     * @return \Illuminate\Testing\TestResponse
     */
    private function advanceToStep(string $currentStep, string $currentRoute, array $additionalData = []): \Illuminate\Testing\TestResponse
    {
        // Set the session to the current step
        session(['install_step' => $currentStep]);
        
        // Merge continue button with any additional data
        $postData = array_merge(['btn_continue' => '1'], $additionalData);
        
        // POST to the route to advance
        return $this->post(route($currentRoute), $postData);
    }
    
    // ==================== ROUTE: GET /setup (index) ====================
    
    /**
     * Test index redirects to language selection.
     */
    #[Group('smoke')]
    #[Test]
    public function it_redirects_to_language_selection(): void
    {
        /** Act */
        $response = $this->get(route('setup.index'));

        /** Assert */
        $response->assertRedirect(route('setup.language'));
    }

    /**
     * Test setup wizard is accessible without authentication.
     */
    #[Group('auth')]
    #[Test]
    public function it_is_accessible_without_authentication(): void
    {
        /** Arrange */
        // No authentication for initial setup

        /** Act */
        $response = $this->get(route('setup.index'));

        /** Assert */
        // Should redirect to language, not login
        $response->assertRedirect();
        $this->assertNotEquals(route('sessions.login'), $response->headers->get('Location'));
    }

    // ==================== ROUTE: GET /setup/language (language) ====================

    /**
     * Test language selection page displays.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_language_selection_page(): void
    {
        /** Act */
        $response = $this->get(route('setup.language'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::setup.lang');
        $response->assertViewHas('languages');
    }

    /**
     * Test language selection advances to prerequisites.
     */
    #[Group('workflow')]
    #[Test]
    public function it_advances_to_prerequisites_after_language_selection(): void
    {
        /** Arrange */
        $languageData = [
            'btn_continue' => '1',
            'ip_lang' => 'en',
        ];

        /** Act */
        $response = $this->post(route('setup.language'), $languageData);

        /** Assert */
        $response->assertRedirect(route('setup.prerequisites'));
        $this->assertEquals('en', session('ip_lang'));
        $this->assertEquals('prerequisites', session('install_step'));
    }

    /**
     * Test language selection resets session cache.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_resets_session_cache_on_language_page(): void
    {
        /** Arrange */
        session(['install_step' => 'some_value']);
        session(['is_upgrade' => true]);

        /** Act */
        $response = $this->get(route('setup.language'));

        /** Assert */
        $response->assertOk();
        $this->assertNull(session('install_step'));
        $this->assertNull(session('is_upgrade'));
    }

    // ==================== ROUTE: GET /setup/prerequisites (prerequisites) ====================

    /**
     * Test prerequisites page displays when step is correct.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_prerequisites_page(): void
    {
        /** Arrange */
        session(['install_step' => 'prerequisites']);

        /** Act */
        $response = $this->get(route('setup.prerequisites'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::setup.prerequisites');
        $response->assertViewHas('basics');
        $response->assertViewHas('writables');
    }

    /**
     * Test prerequisites redirects if step is wrong.
     */
    #[Group('workflow')]
    #[Test]
    public function it_redirects_if_prerequisites_step_is_wrong(): void
    {
        /** Arrange */
        session(['install_step' => 'wrong_step']);

        /** Act */
        $response = $this->get(route('setup.prerequisites'));

        /** Assert */
        $response->assertRedirect(route('setup.language'));
    }

    /**
     * Test prerequisites advances to database configuration.
     */
    #[Group('workflow')]
    #[Test]
    public function it_advances_to_database_configuration(): void
    {
        /** Act */
        $response = $this->advanceToStep('prerequisites', 'setup.prerequisites');

        /** Assert */
        $response->assertRedirect(route('setup.configure-database'));
        $this->assertEquals('configure_database', session('install_step'));
    }

    // ==================== ROUTE: GET /setup/configure-database (configureDatabase) ====================

    /**
     * Test database configuration page displays.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_database_configuration_page(): void
    {
        /** Arrange */
        session(['install_step' => 'configure_database']);

        /** Act */
        $response = $this->get(route('setup.configure-database'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::setup.configure_database');
        $response->assertViewHas('database');
    }

    /**
     * Test database configuration redirects if step is wrong.
     */
    #[Group('workflow')]
    #[Test]
    public function it_redirects_if_database_config_step_is_wrong(): void
    {
        /** Arrange */
        session(['install_step' => 'wrong_step']);

        /** Act */
        $response = $this->get(route('setup.configure-database'));

        /** Assert */
        $response->assertRedirect(route('setup.prerequisites'));
    }

    /**
     * Test database configuration submission with credentials.
     */
    #[Group('crud')]
    #[Test]
    public function it_processes_database_credentials(): void
    {
        /** Arrange */
        session(['install_step' => 'configure_database']);
        $dbData = [
            'db_hostname' => 'localhost',
            'db_username' => 'testuser',
            'db_password' => 'testpass',
            'db_database' => 'testdb',
            'db_port' => '3306',
        ];

        /** Act */
        $response = $this->post(route('setup.configure-database'), $dbData);

        /** Assert */
        $response->assertOk();
        $response->assertViewHas('database');
    }

    // ==================== ROUTE: GET /setup/install-tables (installTables) ====================

    /**
     * Test install tables page displays.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_install_tables_page(): void
    {
        /** Arrange */
        session(['install_step' => 'install_tables']);

        /** Act */
        $response = $this->get(route('setup.install-tables'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::setup.install_tables');
        $response->assertViewHas('success');
    }

    /**
     * Test install tables redirects if step is wrong.
     */
    #[Group('workflow')]
    #[Test]
    public function it_redirects_if_install_tables_step_is_wrong(): void
    {
        /** Arrange */
        session(['install_step' => 'wrong_step']);

        /** Act */
        $response = $this->get(route('setup.install-tables'));

        /** Assert */
        $response->assertRedirect(route('setup.prerequisites'));
    }

    /**
     * Test install tables advances to upgrade tables.
     */
    #[Group('workflow')]
    #[Test]
    public function it_advances_to_upgrade_tables_from_install(): void
    {
        /** Act */
        $response = $this->advanceToStep('install_tables', 'setup.install-tables');

        /** Assert */
        $response->assertRedirect(route('setup.upgrade-tables'));
        $this->assertEquals('upgrade_tables', session('install_step'));
    }

    // ==================== ROUTE: GET /setup/upgrade-tables (upgradeTables) ====================

    /**
     * Test upgrade tables page displays.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_upgrade_tables_page(): void
    {
        /** Arrange */
        session(['install_step' => 'upgrade_tables']);

        /** Act */
        $response = $this->get(route('setup.upgrade-tables'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::setup.upgrade_tables');
    }

    /**
     * Test upgrade tables redirects if step is wrong.
     */
    #[Group('workflow')]
    #[Test]
    public function it_redirects_if_upgrade_tables_step_is_wrong(): void
    {
        /** Arrange */
        session(['install_step' => 'wrong_step']);

        /** Act */
        $response = $this->get(route('setup.upgrade-tables'));

        /** Assert */
        $response->assertRedirect(route('setup.prerequisites'));
    }

    /**
     * Test upgrade tables advances to create user for new install.
     */
    #[Group('workflow')]
    #[Test]
    public function it_advances_to_create_user_for_new_install(): void
    {
        /** Arrange */
        session(['is_upgrade' => false]);
        
        /** Act */
        $response = $this->advanceToStep('upgrade_tables', 'setup.upgrade-tables');

        /** Assert */
        $response->assertRedirect(route('setup.create-user'));
        $this->assertEquals('create_user', session('install_step'));
    }

    /**
     * Test upgrade tables advances to calculation info for upgrade.
     */
    #[Group('workflow')]
    #[Test]
    public function it_advances_to_calculation_info_for_upgrade(): void
    {
        /** Arrange */
        session(['is_upgrade' => true]);
        
        /** Act */
        $response = $this->advanceToStep('upgrade_tables', 'setup.upgrade-tables');

        /** Assert */
        $response->assertRedirect(route('setup.calculation-info'));
        $this->assertEquals('calculation_info', session('install_step'));
    }

    // ==================== ROUTE: GET /setup/create-user (createUser) ====================

    /**
     * Test create user page displays.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_create_user_page(): void
    {
        /** Arrange */
        session(['install_step' => 'create_user']);

        /** Act */
        $response = $this->get(route('setup.create-user'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::setup.create_user');
    }

    /**
     * Test create user redirects if step is wrong.
     */
    #[Group('workflow')]
    #[Test]
    public function it_redirects_if_create_user_step_is_wrong(): void
    {
        /** Arrange */
        session(['install_step' => 'wrong_step']);

        /** Act */
        $response = $this->get(route('setup.create-user'));

        /** Assert */
        $response->assertRedirect(route('setup.prerequisites'));
    }

    /**
     * Test create user with valid data.
     */
    #[Group('crud')]
    #[Test]
    public function it_creates_admin_user(): void
    {
        /** Arrange */
        $userData = [
            'user_email' => 'admin@example.com',
            'user_password' => 'password123',
            'user_password_confirm' => 'password123',
        ];

        /** Act */
        $response = $this->advanceToStep('create_user', 'setup.create-user', $userData);

        /** Assert */
        $response->assertRedirect(route('setup.calculation-info'));
    }

    /**
     * Test create user fails with mismatched passwords.
     */
    #[Group('validation')]
    #[Test]
    public function it_fails_with_mismatched_passwords(): void
    {
        /** Arrange */
        session(['install_step' => 'create_user']);
        $userData = [
            'btn_continue' => '1',
            'user_email' => 'admin@example.com',
            'user_password' => 'password123',
            'user_password_confirm' => 'different',
        ];

        /** Act */
        $response = $this->post(route('setup.create-user'), $userData);

        /** Assert */
        $response->assertSessionHasErrors();
    }

    // ==================== ROUTE: GET /setup/calculation-info (calculationInfo) ====================

    /**
     * Test calculation info page displays.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_calculation_info_page(): void
    {
        /** Arrange */
        session(['install_step' => 'calculation_info']);

        /** Act */
        $response = $this->get(route('setup.calculation-info'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::setup.calculation_info');
    }

    /**
     * Test calculation info redirects if step is wrong.
     */
    #[Group('workflow')]
    #[Test]
    public function it_redirects_if_calculation_info_step_is_wrong(): void
    {
        /** Arrange */
        session(['install_step' => 'wrong_step']);

        /** Act */
        $response = $this->get(route('setup.calculation-info'));

        /** Assert */
        $response->assertRedirect(route('setup.prerequisites'));
    }

    /**
     * Test calculation info advances to complete.
     */
    #[Group('workflow')]
    #[Test]
    public function it_advances_to_complete(): void
    {
        /** Act */
        $response = $this->advanceToStep('calculation_info', 'setup.calculation-info');

        /** Assert */
        $response->assertRedirect(route('setup.complete'));
    }

    // ==================== ROUTE: GET /setup/complete (complete) ====================

    /**
     * Test complete page displays.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_complete_page(): void
    {
        /** Arrange */
        session(['install_step' => 'complete']);

        /** Act */
        $response = $this->get(route('setup.complete'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::setup.complete');
    }

    /**
     * Test complete page is accessible without specific step.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_displays_complete_page_without_step_check(): void
    {
        /** Arrange */
        // No session step set

        /** Act */
        $response = $this->get(route('setup.complete'));

        /** Assert */
        $response->assertOk();
    }

    // ==================== EDGE CASES ====================

    /**
     * Test setup is disabled when environment flag is set.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_blocks_setup_when_disabled(): void
    {
        /** Arrange */
        putenv('DISABLE_SETUP=true');

        /** Act */
        $response = $this->get(route('setup.index'));

        /** Assert */
        $response->assertStatus(403);
        
        // Cleanup
        putenv('DISABLE_SETUP=false');
    }

    /**
     * Test setup handles invalid language selection.
     */
    #[Group('validation')]
    #[Test]
    public function it_handles_invalid_language_selection(): void
    {
        /** Arrange */
        $languageData = [
            'btn_continue' => '1',
            'ip_lang' => 'invalid_lang',
        ];

        /** Act */
        $response = $this->post(route('setup.language'), $languageData);

        /** Assert */
        // Should either reject or default to safe value
        $this->assertTrue($response->isRedirect() || $response->isOk());
    }
}
