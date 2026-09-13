<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeCrudCommand extends Command
{
    protected $signature = 'module:make-crud {module} {model} {--no-sync : Jangan jalankan module:sync setelah generate}';

    protected $description = 'Generate scaffold modul CRUD (Model, Controller, Form Request, migration, Config/menu, routes web+api, Vue index/create/edit, css) dari stubs/laracorz.';

    public function handle(): int
    {
        $module = Str::studly($this->argument('module'));
        $model = Str::studly($this->argument('model'));
        $slug = Str::kebab($module);
        // Nama tabel/migration diturunkan dari MODEL agar cocok dengan tebakan Eloquent
        // walau nama module != model (mis. module:make-crud Blog Post -> tabel posts).
        $plural = Str::plural(Str::snake($model));
        // URL/route/prefix pakai kebab-case dari MODULE agar konsisten dengan slug modul.
        $kebabPlural = Str::kebab(Str::pluralStudly($module));

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
            '{{kebabPlural}}' => $kebabPlural,
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

        // module:sync menulis baris ke tabel modules. Kalau modul batal dipakai
        // dan foldernya dihapus, barisnya tertinggal jadi entri yatim di menu —
        // --no-sync memungkinkan scaffold dulu, daftarkan ke DB belakangan.
        if ($this->option('no-sync')) {
            $this->comment('Lewati module:sync. Jalankan `php artisan module:sync` saat modul siap didaftarkan.');

            return self::SUCCESS;
        }

        $this->call('module:sync');

        return self::SUCCESS;
    }
}
