import { router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import { debounce } from '@/lib/utils';
import { SEARCH_DEBOUNCE_MS, DEFAULT_PER_PAGE } from '@/lib/constants';

export interface DataTableState<F extends Record<string, unknown> = Record<string, unknown>> {
    search: string;
    sort: string | null;
    direction: 'asc' | 'desc';
    perPage: number;
    page: number;
    filters: F;
}

interface UseDataTableOptions<F extends Record<string, unknown>> {
    initial?: Partial<Omit<DataTableState<F>, 'filters'>> & { filters?: F };
    only?: string[];
    preserveScroll?: boolean;
}

export function useDataTable<F extends Record<string, unknown> = Record<string, unknown>>(
    opts: UseDataTableOptions<F> = {},
) {
    const url = typeof window !== 'undefined' ? new URL(window.location.href) : null;
    const initialFilters: Record<string, unknown> = {};
    if (url) {
        url.searchParams.forEach((v, k) => {
            if (k.startsWith('filters[')) {
                const match = k.match(/filters\[(.+)]/);
                if (match) initialFilters[match[1]] = v;
            }
        });
    }

    const state = reactive<DataTableState<F>>({
        search: url?.searchParams.get('search') ?? opts.initial?.search ?? '',
        sort: url?.searchParams.get('sort') ?? opts.initial?.sort ?? null,
        direction:
            (url?.searchParams.get('direction') as 'asc' | 'desc') ?? opts.initial?.direction ?? 'asc',
        perPage:
            Number(url?.searchParams.get('per_page')) ||
            opts.initial?.perPage ||
            DEFAULT_PER_PAGE,
        page: Number(url?.searchParams.get('page')) || opts.initial?.page || 1,
        filters: { ...initialFilters, ...(opts.initial?.filters ?? {}) } as F,
    });

    function buildQuery(): Record<string, unknown> {
        const query: Record<string, unknown> = {
            search: state.search || undefined,
            sort: state.sort || undefined,
            direction: state.sort ? state.direction : undefined,
            per_page: state.perPage !== DEFAULT_PER_PAGE ? state.perPage : undefined,
            page: state.page > 1 ? state.page : undefined,
        };
        const filters: Record<string, unknown> = {};
        for (const [k, v] of Object.entries(state.filters)) {
            if (v !== '' && v !== null && v !== undefined) filters[k] = v;
        }
        if (Object.keys(filters).length) {
            query.filters = filters;
        }
        return query;
    }

    function reload(): void {
        router.get(window.location.pathname, buildQuery() as Record<string, string | number>, {
            preserveState: true,
            preserveScroll: opts.preserveScroll ?? true,
            replace: true,
            only: opts.only,
        });
    }

    const debouncedReload = debounce(() => reload(), SEARCH_DEBOUNCE_MS);

    watch(() => state.search, () => {
        state.page = 1;
        debouncedReload();
    });
    watch(() => state.perPage, () => {
        state.page = 1;
        reload();
    });
    watch(() => state.page, () => reload());
    watch(
        () => state.filters,
        () => {
            state.page = 1;
            reload();
        },
        { deep: true },
    );

    function sortBy(field: string): void {
        if (state.sort === field) {
            state.direction = state.direction === 'asc' ? 'desc' : 'asc';
        } else {
            state.sort = field;
            state.direction = 'asc';
        }
        state.page = 1;
        reload();
    }

    function reset(): void {
        state.search = '';
        state.sort = null;
        state.direction = 'asc';
        state.perPage = DEFAULT_PER_PAGE;
        state.page = 1;
        state.filters = {} as typeof state.filters;
    }

    return { state, sortBy, reset, reload };
}
