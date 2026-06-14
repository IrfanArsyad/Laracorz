<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $defaults = [
            ['key' => 'app.name', 'group' => 'general', 'type' => 'text', 'label' => 'Nama Aplikasi', 'value' => 'LaraCorz', 'order' => 1],
            ['key' => 'app.tagline', 'group' => 'general', 'type' => 'text', 'label' => 'Tagline', 'value' => 'Core Laravel + Vue Modular', 'order' => 2],
            ['key' => 'app.logo', 'group' => 'general', 'type' => 'image', 'label' => 'Logo', 'value' => null, 'order' => 3],
            ['key' => 'app.description', 'group' => 'general', 'type' => 'text', 'label' => 'Deskripsi', 'value' => '', 'order' => 4],
            ['key' => 'theme.default', 'group' => 'appearance', 'type' => 'select', 'label' => 'Tema Default', 'value' => 'system', 'order' => 1],
        ];

        foreach ($defaults as $row) {
            Setting::query()->updateOrCreate(['key' => $row['key']], $row);
        }
    }
}
