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
        return Inertia::render('settings::index', [
            'groups' => Setting::query()->orderBy('group')->orderBy('order')->get()->groupBy('group')->toArray(),
            'values' => $this->settings->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'values' => ['required', 'array'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $path = $this->files->upload($request->file('logo'), 'settings', (string) $this->settings->get('app.logo'), 512);
            $this->settings->set('app.logo', $path, 'general', 'image');
        }

        foreach ($data['values'] as $key => $value) {
            $row = Setting::query()->where('key', $key)->first();
            $this->settings->set($key, $value, $row->group ?? 'general', $row->type ?? 'text');
        }

        $this->logger->log('updated', 'Memperbarui pengaturan', null, [], $data['values'], 'settings');

        return back()->with('success', 'Pengaturan tersimpan.');
    }
}
