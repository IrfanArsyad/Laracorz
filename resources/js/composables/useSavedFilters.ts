import { computed, ref, watch } from 'vue';

/**
 * useSavedFilters — simpan/load named filter view di localStorage,
 * scoped per route (mis. `users`, `roles`, `admin-log`).
 *
 *   const { views, activeView, save, remove, apply } = useSavedFilters('users', {
 *     state: tableState,  // reactive object: { search, filters: { ... } }
 *   });
 *
 * View = { id: string; name: string; payload: { search: string; filters: object } }
 * Snapshot key: `laracorz.savedFilters.{scope}`.
 */

export interface SavedView {
    id: string;
    name: string;
    payload: {
        search: string;
        filters: Record<string, unknown>;
    };
}

export interface UseSavedFiltersOptions {
    state: {
        search: string;
        filters: Record<string, unknown>;
    };
}

const STORAGE_PREFIX = 'laracorz.savedFilters.';

function load(scope: string): SavedView[] {
    try {
        const raw = localStorage.getItem(STORAGE_PREFIX + scope);
        if (!raw) return [];
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function persist(scope: string, views: SavedView[]): void {
    try {
        localStorage.setItem(STORAGE_PREFIX + scope, JSON.stringify(views));
    } catch {
        // quota / private mode — abaikan
    }
}

function randomId(): string {
    return Math.random().toString(36).slice(2, 9);
}

export function useSavedFilters(scope: string, opts: UseSavedFiltersOptions) {
    const views = ref<SavedView[]>(load(scope));
    const activeViewId = ref<string | null>(null);

    watch(views, (v) => persist(scope, v), { deep: true });

    const activeView = computed<SavedView | null>(() =>
        views.value.find((v) => v.id === activeViewId.value) ?? null,
    );

    function save(name: string): SavedView {
        const view: SavedView = {
            id: randomId(),
            name: name.trim() || 'Untitled',
            payload: {
                search: opts.state.search ?? '',
                filters: JSON.parse(JSON.stringify(opts.state.filters ?? {})),
            },
        };
        views.value = [...views.value, view];
        activeViewId.value = view.id;
        return view;
    }

    function remove(id: string): void {
        views.value = views.value.filter((v) => v.id !== id);
        if (activeViewId.value === id) activeViewId.value = null;
    }

    function rename(id: string, name: string): void {
        const trimmed = name.trim();
        if (!trimmed) return;
        views.value = views.value.map((v) => (v.id === id ? { ...v, name: trimmed } : v));
    }

    function apply(view: SavedView): void {
        opts.state.search = view.payload.search ?? '';
        opts.state.filters = { ...view.payload.filters };
        activeViewId.value = view.id;
    }

    function clearActive(): void {
        activeViewId.value = null;
    }

    return {
        views,
        activeView,
        activeViewId,
        save,
        remove,
        rename,
        apply,
        clearActive,
    };
}
