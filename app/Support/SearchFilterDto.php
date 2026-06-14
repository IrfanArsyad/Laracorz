<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

final readonly class SearchFilterDto
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public ?string $search = null,
        public ?string $sort = null,
        public ?string $direction = 'asc',
        public int $perPage = 10,
        public int $page = 1,
        public array $filters = [],
    ) {}

    public static function fromRequest(Request $request): self
    {
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        return new self(
            search: $request->input('search'),
            sort: $request->input('sort'),
            direction: $request->input('direction', 'asc'),
            perPage: $perPage,
            page: max(1, (int) $request->input('page', 1)),
            filters: (array) $request->input('filters', []),
        );
    }
}
