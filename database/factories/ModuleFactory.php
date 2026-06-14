<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Module;
use App\Models\ModuleGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Module>
 */
class ModuleFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->slug(2);

        return [
            'module_group_id' => ModuleGroup::factory(),
            'parent_id' => null,
            'name' => $name,
            'label' => Str::headline($name),
            'icon' => 'square',
            'url' => '/'.$name,
            'route_name' => str_replace('-', '.', $name).'.index',
            'badge_source' => null,
            'extra_actions' => null,
            'active' => true,
            'external' => false,
            'order' => 0,
        ];
    }

    public function container(): static
    {
        return $this->state(fn () => [
            'url' => null,
            'route_name' => null,
        ]);
    }
}
