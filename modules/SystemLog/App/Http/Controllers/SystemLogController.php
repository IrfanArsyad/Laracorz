<?php

declare(strict_types=1);

namespace Modules\SystemLog\App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\SystemLog;
use App\Support\SearchFilterDto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemLogController extends Controller
{
    public function index(Request $request): Response
    {
        $dto = SearchFilterDto::fromRequest($request);
        $filters = $dto->filters;

        $query = SystemLog::query()
            ->search($dto->search)
            ->sort($dto->sort ?? 'created_at', $dto->direction ?? 'desc');

        if (! empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }
        if (! empty($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }

        return Inertia::render('system-log::index', [
            'data' => $query->paginate($dto->perPage)->withQueryString(),
            'filters' => array_merge(['search' => $dto->search], (array) $filters),
        ]);
    }
}
