<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\LinkClick;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LinkClick>
 */
class LinkClickFactory extends Factory
{
    public function definition(): array
    {
        return [
            'link_id' => Link::factory(),
            'ip_address' => fake()->boolean(25)
                ? fake()->ipv6()
                : fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'clicked_at' => fake()->dateTimeBetween('-6 months'),
        ];
    }
}
