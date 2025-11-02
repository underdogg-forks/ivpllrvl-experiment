<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Additional feature test setup can go here
        // For example: seed common data, set up authentication, etc.
    }

    /**
     * Helper method to authenticate as a user for tests.
     *
     * @param  \Modules\Core\Models\User|null  $user
     * @return \Modules\Core\Models\User
     */
    protected function actingAsUser($user = null)
    {
        $user = $user ?? \Modules\Core\Models\User::factory()->create();

        return $this->actingAs($user);
    }

    /**
     * Make a POST request as an authenticated user.
     *
     * @param  \Modules\Core\Models\User|null  $user
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Testing\TestResponse
     */
    protected function postAs($user, string $uri, array $data = [], array $headers = [])
    {
        return $this->actingAs($user)->post($uri, $data, $headers);
    }

    /**
     * Make a POST request as a newly created user.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Testing\TestResponse
     */
    protected function postAsUser(string $uri, array $data = [], array $headers = [])
    {
        $user = \Modules\Core\Models\User::factory()->create();
        return $this->actingAs($user)->post($uri, $data, $headers);
    }

    /**
     * Make a GET request as an authenticated user.
     *
     * @param  \Modules\Core\Models\User|null  $user
     * @param  string  $uri
     * @param  array  $headers
     * @return \Illuminate\Testing\TestResponse
     */
    protected function getAs($user, string $uri, array $headers = [])
    {
        return $this->actingAs($user)->get($uri, $headers);
    }

    /**
     * Make a GET request as a newly created user.
     *
     * @param  string  $uri
     * @param  array  $headers
     * @return \Illuminate\Testing\TestResponse
     */
    protected function getAsUser(string $uri, array $headers = [])
    {
        $user = \Modules\Core\Models\User::factory()->create();
        return $this->actingAs($user)->get($uri, $headers);
    }
}
