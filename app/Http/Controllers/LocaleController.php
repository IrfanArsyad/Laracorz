<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'string', Rule::in(['en', 'id'])],
        ]);

        $request->session()->put('locale', $data['locale']);
        cookie()->queue('locale', $data['locale'], 60 * 24 * 365);

        return back();
    }
}
