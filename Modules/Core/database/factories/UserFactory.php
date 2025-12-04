<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Libraries\Crypt;
use Modules\Core\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Core\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $crypt = new Crypt();
        $salt = $crypt->salt();
        $password = 'password'; // Default password for testing
        $hashedPassword = $crypt->generate_password($password, $salt);

        return [
            'user_type' => 1, // Admin user type
            'user_name' => fake()->name(),
            'user_email' => fake()->unique()->safeEmail(),
            'user_psalt' => $salt,
            'user_password' => $hashedPassword,
            'user_passwordreset_token' => '',
            'user_company' => fake()->company(),
            'user_address_1' => fake()->streetAddress(),
            'user_address_2' => '',
            'user_city' => fake()->city(),
            'user_state' => fake()->state(),
            'user_zip' => fake()->postcode(),
            'user_country' => fake()->country(),
            'user_phone' => fake()->phoneNumber(),
            'user_fax' => '',
            'user_mobile' => fake()->phoneNumber(),
            'user_web' => fake()->url(),
            'user_vat_id' => fake()->optional(0.7)->lexify('??########'), // e.g., "GB123456789" or empty
            'user_tax_code' => fake()->optional(0.7)->bothify('TAX-####-????'), // e.g., "TAX-1234-ABCD" or empty
            'user_language' => 'system',
            'user_all_clients' => true,
            'user_active' => 1,
        ];
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_active' => 0,
        ]);
    }

    /**
     * Indicate that the user is a guest user.
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 2,
        ]);
    }

    /**
     * Indicate that the user is an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 1,
        ]);
    }
}
