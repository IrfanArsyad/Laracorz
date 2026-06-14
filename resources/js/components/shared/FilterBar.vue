<script setup lang="ts">
import { computed, ref } from 'vue';
import { Search, X, ListFilter, ChevronDown } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

/**
 * FilterBar v2 — collapsible.
 *
 *   [ 🔍 Cari ........... ]   [ ⛁ Filter (2) ▾ ]   Reset
 *   └─ panel slide-down (saat di-toggle) ───────────────────────┐
 *     │  Filter A  [Select ▾]    Filter B  [Select ▾]           │
 *     │  Filter C  [Select ▾]    ...                            │
 *     └──────────────────────────────────────────────────────────┘
 *
 * Filter selects disembunyikan dalam panel collapsible — meskipun ada banyak
 * filter, UI tetap bersih sampai user membuka panel.
 *
 * Pakai prop `filtersCount` untuk badge angka di tombol Filter.
 */
const props = withDefaults(
    defineProps<{
        search: string;
        placeholder?: string;
        /** jumlah filter aktif untuk badge */
        filtersCount?: number;
        /** auto-open panel ketika ada filter aktif (default: true) */
        defaultOpen?: boolean;
        class?: string;
    }>(),
    { placeholder: 'Cari...', filtersCount: 0, defaultOpen: false },
);

const emit = defineEmits<{
    'update:search': [v: string];
    reset: [];
}>();

const open = ref(props.defaultOpen || props.filtersCount > 0);

const hasAnyFilter = computed(() => props.search.length > 0 || props.filtersCount > 0);
</script>

<template>
    <div :class="cn('space-y-3', $props.class)">
        <!-- Top row: search + filter toggle + reset -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <!-- Search input -->
            <div class="relative sm:flex-1 sm:max-w-md">
                <Search class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--text-muted)]" />
                <input
                    type="text"
                    :value="search"
                    :placeholder="placeholder"
                    class="h-10 w-full rounded-md border border-[var(--border-default)] bg-[var(--surface-raised)] pl-9 pr-9 text-sm text-[var(--text-default)] placeholder:text-[var(--text-muted)] transition-[border-color,box-shadow] duration-[var(--duration-fast)] ease-[var(--ease-out)] hover:border-[var(--border-strong)] focus-visible:outline-none focus-visible:border-[var(--border-focus)] focus-visible:ring-4 focus-visible:ring-[color-mix(in_oklab,var(--focus-ring),transparent_82%)]"
                    @input="emit('update:search', ($event.target as HTMLInputElement).value)"
                />
                <button
                    v-if="search"
                    type="button"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 flex h-5 w-5 items-center justify-center rounded-full text-[var(--text-muted)] hover:bg-[var(--state-hover)] hover:text-[var(--text-default)] transition-colors"
                    aria-label="Bersihkan pencarian"
                    @click="emit('update:search', '')"
                >
                    <X class="h-3 w-3" />
                </button>
            </div>

            <div class="flex items-center gap-2 sm:ml-auto">
                <!-- Filter toggle button -->
                <button
                    v-if="$slots.default"
                    type="button"
                    :aria-expanded="open"
                    aria-controls="filter-panel"
                    :class="
                        cn(
                            'inline-flex h-10 items-center gap-2 rounded-md border bg-[var(--surface-raised)] px-3 text-sm font-medium transition-colors',
                            open || filtersCount > 0
                                ? 'border-[var(--brand-bg)] text-[var(--brand-soft-fg)] bg-[var(--brand-soft-bg)]'
                                : 'border-[var(--border-default)] text-[var(--text-default)] hover:border-[var(--border-strong)] hover:bg-[var(--state-hover)]',
                        )
                    "
                    @click="open = !open"
                >
                    <ListFilter class="h-4 w-4" />
                    <span>Filter</span>
                    <span
                        v-if="filtersCount > 0"
                        class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[var(--brand-bg)] px-1.5 text-xs font-semibold text-[var(--brand-fg)] tabular-nums"
                    >
                        {{ filtersCount }}
                    </span>
                    <ChevronDown
                        :class="
                            cn(
                                'h-4 w-4 opacity-60 transition-transform duration-[var(--duration-fast)] ease-[var(--ease-out)]',
                                open ? 'rotate-180' : '',
                            )
                        "
                    />
                </button>

                <!-- Reset semua -->
                <button
                    v-if="hasAnyFilter"
                    type="button"
                    class="inline-flex h-10 items-center gap-1.5 rounded-md px-3 text-sm font-medium text-[var(--text-muted)] hover:bg-[var(--state-hover)] hover:text-[var(--text-default)] transition-colors"
                    @click="emit('reset')"
                >
                    <X class="h-3.5 w-3.5" />
                    Reset
                </button>
            </div>
        </div>

        <!-- Collapsible panel — filter selects dalam grid -->
        <Transition
            enter-active-class="overflow-hidden transition-[max-height,opacity] duration-[var(--duration-base)] ease-[var(--ease-out)]"
            enter-from-class="max-h-0 opacity-0"
            enter-to-class="max-h-[400px] opacity-100"
            leave-active-class="overflow-hidden transition-[max-height,opacity] duration-[var(--duration-fast)] ease-[var(--ease-in-out)]"
            leave-from-class="max-h-[400px] opacity-100"
            leave-to-class="max-h-0 opacity-0"
        >
            <div
                v-if="open && $slots.default"
                id="filter-panel"
                class="rounded-lg border border-[var(--border-subtle)] bg-[var(--surface-raised)] shadow-[var(--shadow-xs)] p-4"
            >
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <slot />
                </div>
            </div>
        </Transition>
    </div>
</template>
