<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeCrudCommand extends Command
{
    protected $signature = 'module:make-crud {module} {model}';

    protected $description = 'Generate scaffold modul CRUD (Model, Controller, migration, Config/menu, routes, Vue index/create/edit, css) dari stubs/laracorz.';

    public function handle(): int
    {
        $module = Str::studly($this->argument('module'));
        $model = Str::studly($this->argument('model'));
        $slug = Str::kebab($module);
        $plural = Str::plural(Str::snake($module));

        $base = base_path("modules/{$module}");
        if (File::isDirectory($base)) {
            $this->error("Modul {$module} sudah ada.");

            return self::FAILURE;
        }

        $tokens = [
            '{{module}}' => $module,
            '{{model}}' => $model,
            '{{slug}}' => $slug,
            '{{plural}}' => $plural,
            '{{snake}}' => Str::snake($module),
        ];

        $stubsDir = base_path('stubs/laracorz');
        if (! File::isDirectory($stubsDir)) {
            $this->error('Stubs tidak ditemukan di stubs/laracorz/');

            return self::FAILURE;
        }

        foreach (File::allFiles($stubsDir) as $file) {
            $relative = $file->getRelativePathname();
            $targetRel = strtr($relative, $tokens);
            $targetRel = preg_replace('/\.stub$/', '', $targetRel);
            $target = "{$base}/{$targetRel}";

            File::ensureDirectoryExists(dirname($target));
            $content = strtr($file->getContents(), $tokens);
            File::put($target, $content);
        }

        $this->info("Modul {$module} berhasil dibuat di modules/{$module}/");
        $this->call('module:sync');

        return self::SUCCESS;
    }
}
