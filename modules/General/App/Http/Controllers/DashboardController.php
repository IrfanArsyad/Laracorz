<?php

declare(strict_types=1);

namespace Modules\General\App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\AdminLog;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;


class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('general::Dashboard/index', [
            'stats' => Inertia::defer(fn (): array => [
                'users' => User::query()->count(),
                'roles' => Role::query()->count(),
                'modules' => Module::query()->count(),
                'logsToday' => AdminLog::query()->whereDate('created_at', today())->count(),
            ]),
        ]);
    }
}
