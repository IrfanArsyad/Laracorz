<?php

declare(strict_types=1);

namespace Modules\SystemLog\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\LaravelLogReader;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemLogController extends Controller
{
    public function index(Request $request): Response
    {
        $reader = new LaravelLogReader(storage_path('logs/laravel.log'));

        $filters = [
            'level' => $request->input('filters.level'),
            'search' => $request->input('search'),
        ];

        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 25);

        return Inertia::render('system-log::index', [
            'data' => $reader->paginate($filters, $page, $perPage),
            'filters' => [
                'search' => $filters['search'],
                'level' => $filters['level'],
            ],
            'distinctLevels' => $reader->distinctLevels(),
        ]);
    }
}
