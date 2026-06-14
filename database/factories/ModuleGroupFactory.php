<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ModuleGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ModuleGroup>
 */
class ModuleGroupFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => Str::slug($name),
            'label' => ucfirst($name),
            'icon' => 'folder',
            'order' => 0,
            'active' => true,
        ];
    }
}
