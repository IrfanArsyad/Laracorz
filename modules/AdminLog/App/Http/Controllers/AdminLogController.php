<?php

declare(strict_types=1);

namespace Modules\AdminLog\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Support\SearchFilterDto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminLogController extends Controller
{
    public function index(Request $request): Response
    {
        $dto = SearchFilterDto::fromRequest($request);
        $filters = $dto->filters;

        $query = AdminLog::query()
            ->with('user:id,name,email')
            ->search($dto->search)
            ->sort($dto->sort ?? 'created_at', $dto->direction ?? 'desc');

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        if (! empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }
        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return Inertia::render('admin-log::index', [
            'data' => $query->paginate($dto->perPage)->withQueryString(),
            'filters' => array_merge(['search' => $dto->search], (array) $filters),
        ]);
    }
}
