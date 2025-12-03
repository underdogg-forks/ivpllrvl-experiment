<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Controllers\SettingsController;
use Modules\Core\Models\Setting;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * SettingsController Feature Tests.
 *
 * Tests application settings management.
 */
#[CoversClass(SettingsController::class)]
class SettingsControllerTest extends FeatureTestCase
{
    /**
     * Test index displays settings.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_settings_page(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        Setting::factory()->create(['setting_key' => 'company_name', 'setting_value' => 'Test Company']);
        Setting::factory()->create(['setting_key' => 'currency_code', 'setting_value' => 'USD']);

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('settings.index'));

        /* Assert */
        $response->assertOk();
        $response->assertViewIs('core::settings_index');
        $response->assertViewHas('settings');

        $settings = $response->viewData('settings');
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('company_name', $settings);
        $this->assertArrayHasKey('currency_code', $settings);
    }

    /**
     * Test settings are returned as key-value array.
     */
    #[Group('smoke')]
    #[Test]
    public function it_returns_settings_as_key_value_array(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        Setting::factory()->create(['setting_key' => 'email_from', 'setting_value' => 'noreply@example.com']);
        Setting::factory()->create(['setting_key' => 'invoice_prefix', 'setting_value' => 'INV-']);

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('settings.index'));

        /** Assert */
        $settings = $response->viewData('settings');
        $this->assertEquals('noreply@example.com', $settings['email_from']);
        $this->assertEquals('INV-', $settings['invoice_prefix']);
    }

    /**
     * Test save creates new settings.
     */
    #[Group('crud')]
    #[Test]
    public function it_creates_new_settings(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /**
         * {
         *     "company_name": "New Company",
         *     "currency_code": "EUR"
         * }.
         */
        $settingsData = [
            'company_name'  => 'New Company',
            'currency_code' => 'EUR',
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('settings.save'), $settingsData);

        /* Assert */
        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('alert_success');

        $this->assertDatabaseHas('ip_settings', [
            'setting_key'   => 'company_name',
            'setting_value' => 'New Company',
        ]);
    }

    /**
     * Test save updates existing settings.
     */
    #[Group('crud')]
    #[Test]
    public function it_updates_existing_settings(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        Setting::factory()->create(['setting_key' => 'company_name', 'setting_value' => 'Old Company']);

        /**
         * {
         *     "company_name": "Updated Company"
         * }.
         */
        $settingsData = [
            'company_name' => 'Updated Company',
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('settings.save'), $settingsData);

        /* Assert */
        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('alert_success');

        $this->assertDatabaseHas('ip_settings', [
            'setting_key'   => 'company_name',
            'setting_value' => 'Updated Company',
        ]);
    }

    /**
     * Test save handles multiple settings at once.
     */
    #[Group('crud')]
    #[Test]
    public function it_saves_multiple_settings_at_once(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /**
         * {
         *     "company_name": "Multi Test Company",
         *     "currency_code": "GBP",
         *     "invoice_prefix": "INV-"
         * }.
         */
        $settingsData = [
            'company_name'   => 'Multi Test Company',
            'currency_code'  => 'GBP',
            'invoice_prefix' => 'INV-',
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('settings.save'), $settingsData);

        /* Assert */
        $response->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('ip_settings', [
            'setting_key'   => 'company_name',
            'setting_value' => 'Multi Test Company',
        ]);
        $this->assertDatabaseHas('ip_settings', [
            'setting_key'   => 'currency_code',
            'setting_value' => 'GBP',
        ]);
        $this->assertDatabaseHas('ip_settings', [
            'setting_key'   => 'invoice_prefix',
            'setting_value' => 'INV-',
        ]);
    }

    /**
     * Test save redirects to index with GET request.
     */
    #[Group('smoke')]
    #[Test]
    public function it_redirects_to_index_on_get_request(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('settings.save'));

        /* Assert */
        $response->assertRedirect(route('settings.index'));
    }

    /**
     * Test index with no settings returns empty array.
     */
    #[Group('smoke')]
    #[Test]
    public function it_returns_empty_array_when_no_settings_exist(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('settings.index'));

        /* Assert */
        $response->assertOk();
        $settings = $response->viewData('settings');
        $this->assertIsArray($settings);
        $this->assertEmpty($settings);
    }
}
