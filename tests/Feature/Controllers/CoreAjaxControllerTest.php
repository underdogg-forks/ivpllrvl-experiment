<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Controllers\AjaxController as CoreAjaxController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * Core AjaxController Feature Tests.
 *
 * Tests AJAX requests for settings operations.
 */
#[CoversClass(CoreAjaxController::class)]
class CoreAjaxControllerTest extends FeatureTestCase
{
    /**
     * Test getCronKey returns JSON with random key.
     */
    #[Test]
    public function it_returns_json_with_random_cron_key(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('core.ajax.get_cron_key'));

        /** Assert */
        $response->assertOk();
        $response->assertJsonStructure(['key']);
        
        $data = $response->json();
        $this->assertIsString($data['key']);
        $this->assertEquals(16, strlen($data['key']));
    }

    /**
     * Test getCronKey generates different keys on each request.
     */
    #[Test]
    public function it_generates_different_keys_on_each_request(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response1 = $this->actingAs($user)->get(route('core.ajax.get_cron_key'));
        $response2 = $this->actingAs($user)->get(route('core.ajax.get_cron_key'));

        /** Assert */
        $key1 = $response1->json('key');
        $key2 = $response2->json('key');
        
        $this->assertNotEquals($key1, $key2);
    }

    /**
     * Test getCronKey generates alphanumeric keys only.
     */
    #[Test]
    public function it_generates_alphanumeric_keys_only(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('core.ajax.get_cron_key'));

        /** Assert */
        $key = $response->json('key');
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{16}$/', $key);
    }
}
