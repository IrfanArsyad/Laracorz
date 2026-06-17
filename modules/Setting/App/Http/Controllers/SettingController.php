<?php

declare(strict_types=1);

namespace Modules\Setting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AdminLogService;
use App\Services\FileService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly AdminLogService $logger,
        private readonly FileService $files,
    ) {}

    public function index(): Response
    {
        return Inertia::render('setting::index', [
            'groups' => Setting::query()
                ->orderBy('group')
                ->orderBy('order')
                ->get()
                ->groupBy('group')
                ->toArray(),
            'values' => $this->settings->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $values = (array) $request->input('values', []);
        $uploads = (array) $request->file('files', []);

        // Handle file uploads dinamis: form-data `files[{setting.key}]`
        foreach ($uploads as $key => $file) {
            if ($file === null) {
                continue;
            }
            $row = Setting::query()->where('key', $key)->first();
            if (! $row) {
                continue;
            }
            $oldPath = (string) $this->settings->get($key);
            $path = $this->files->upload($file, 'settings', $oldPath, 512);
            $values[$key] = $path;
        }

        // Tipe-cast nilai sesuai setting type sebelum simpan.
        foreach ($values as $key => $value) {
            $row = Setting::query()->where('key', $key)->first();
            if (! $row) {
                continue;
            }
            $casted = $this->castValue($value, $row->type);
            $this->settings->set($key, $casted, $row->group ?? 'general', $row->type ?? 'text');
        }

        // Log tanpa nilai sensitif (mail.* password kalau ada di masa depan).
        $this->logger->log(
            action: 'updated',
            description: 'Memperbarui pengaturan',
            module: 'settings',
            new: collect($values)
                ->reject(fn ($_v, string $k): bool => str_starts_with($k, 'mail.password'))
                ->all(),
        );

        return back()->with('success', 'Pengaturan tersimpan.');
    }

    private function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'number' => $value === '' || $value === null ? null : (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            default => $value,
        };
    }
}
