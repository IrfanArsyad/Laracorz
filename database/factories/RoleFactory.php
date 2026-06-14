<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => Str::slug($name),
            'display_name' => ucfirst($name),
            'description' => fake()->sentence(),
            'is_active' => true,
            'read' => [],
            'create' => [],
            'update' => [],
            'delete' => [],
            'extra' => [],
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'name' => Role::SUPER_ADMIN,
            'display_name' => 'Super Administrator',
            'read' => [Role::WILDCARD],
            'create' => [Role::WILDCARD],
            'update' => [Role::WILDCARD],
            'delete' => [Role::WILDCARD],
        ]);
    }
}
