<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\LinkClick;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-6 months');

        return [
            'user_id' => User::factory(),
            'code' => fake()->unique()->regexify('[A-Za-z0-9]{8}'),
            'url' => fake()->url()
                .'/'.fake()->slug(fake()->numberBetween(3, 10))
                .'?ref='.fake()->uuid(),
            'clicks_count' => 0,
            'created_at' => $createdAt,
            'updated_at' => fake()->dateTimeBetween($createdAt),
        ];
    }

    public function withClicks(int $min = 10, int $max = 30): static
    {
        if (($min < 0) || ($max < $min)) {
            throw new InvalidArgumentException(
                'The click range must satisfy 0 <= min <= max.',
            );
        }

        return $this->afterCreating(function (Link $link) use ($min, $max): void {
            $clicksCount = fake()->numberBetween($min, $max);

            LinkClick::factory()
                ->count($clicksCount)
                ->for($link)
                ->state(fn (): array => [
                    'clicked_at' => fake()->dateTimeBetween($link->created_at),
                ])
                ->create();

            $link->updateQuietly([
                'clicks_count' => $link->clicks()->count(),
            ]);
        });
    }
}
