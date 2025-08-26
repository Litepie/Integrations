<?php

namespace Litepie\Integration\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Litepie\Integration\Models\Integration;

class IntegrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Integration::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Integration',
            'description' => $this->faker->sentence(),
            'redirect_uris' => [
                $this->faker->url . '/callback',
                $this->faker->url . '/auth',
            ],
            'status' => 'active',
            'metadata' => [
                'webhook_url' => $this->faker->url . '/webhook',
                'scopes' => ['read', 'write'],
            ],
        ];
    }

    /**
     * Indicate that the integration is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the integration has a single redirect URI.
     */
    public function singleRedirectUri(): static
    {
        return $this->state(fn (array $attributes) => [
            'redirect_uris' => [$this->faker->url . '/callback'],
        ]);
    }

    /**
     * Indicate that the integration has no metadata.
     */
    public function withoutMetadata(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => null,
        ]);
    }
}
