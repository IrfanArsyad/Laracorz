<script setup lang="ts">
import { computed } from 'vue';
import { Search, X } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

/**
 * Toolbar filter — flat & integrated, single row.
 *
 *   [ 🔍 Cari... .................. ✕ ]    [ Filter ▾ ] [ Filter ▾ ]    Reset
 *
 * Tidak dibungkus card. Tidak ada icon dekorasi (sliders, dst).
 * Search dan filter berbagi 1 row, pakai divider halus di antaranya pada desktop.
 */
const props = withDefaults(
    defineProps<{
        search: string;
        placeholder?: string;
        hasFilters?: boolean;
        class?: string;
    }>(),
    { placeholder: 'Cari...', hasFilters: false },
);

const emit = defineEmits<{
    'update:search': [v: string];
    reset: [];
}>();

const hasAnyFilter = computed(() => props.search.length > 0 || props.hasFilters);
</script>

<template>
    <div
        :class="
            cn(
                'flex flex-col gap-2.5 sm:flex-row sm:items-center sm:flex-wrap',
                $props.class,
            )
        "
    >
        <!-- Search — input mandiri, prominent -->
        <div class="relative sm:flex-1 sm:min-w-[240px] sm:max-w-md">
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

        <!-- Filter group + Reset di kanan -->
        <div
            v-if="$slots.default || hasAnyFilter"
            class="flex items-center gap-2 flex-wrap sm:ml-auto sm:pl-3 sm:border-l sm:border-[var(--border-subtle)]"
        >
            <slot />

            <button
                v-if="hasAnyFilter"
                type="button"
                class="inline-flex h-9 items-center gap-1.5 rounded-md px-3 text-sm font-medium text-[var(--text-muted)] hover:bg-[var(--state-hover)] hover:text-[var(--text-default)] transition-colors"
                @click="emit('reset')"
            >
                <X class="h-3.5 w-3.5" />
                Reset
            </button>
        </div>
    </div>
</template>
