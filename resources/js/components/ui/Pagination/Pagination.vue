<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, ChevronDown } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import type { PaginationMeta } from '@/types';

const { t } = useI18n();

/**
 * Pagination v2 — custom input page number.
 *
 *   Menampilkan 11–20 dari 250    [‹‹] [‹] [ 2 ] dari 25 [›] [››]    [10 ▾] / hal
 *
 * - Klik prev/next/first/last untuk navigasi
 * - Input page langsung, dibatasi [1, lastPage]
 * - Auto-clamp kalau user input di luar range
 * - Per-page selector di kanan (opsional)
 */
const props = withDefaults(
    defineProps<{
        meta?: PaginationMeta | null;
        only?: string[];
        /** Tampilkan kolom "Showing X–Y of Z" */
        showCount?: boolean;
        /** Tampilkan selector rows-per-page */
        showPerPage?: boolean;
        /** Tampilkan tombol navigasi halaman */
        showNav?: boolean;
        class?: string;
    }>(),
    { showCount: true, showPerPage: true, showNav: true },
);

const safeMeta = computed(() => ({
    current_page: props.meta?.current_page ?? 1,
    last_page: props.meta?.last_page ?? 1,
    from: props.meta?.from ?? 0,
    to: props.meta?.to ?? 0,
    total: props.meta?.total ?? 0,
    per_page: props.meta?.per_page ?? 10,
    path: props.meta?.path ?? '',
}));

const pageInput = ref<number>(safeMeta.value.current_page);

watch(
    () => safeMeta.value.current_page,
    (v) => (pageInput.value = v),
);

function goTo(page: number): void {
    const clamped = Math.max(1, Math.min(safeMeta.value.last_page, Math.floor(page) || 1));
    if (clamped === safeMeta.value.current_page) return;
    router.get(
        window.location.pathname,
        { ...routeQuery(), page: clamped },
        { preserveState: true, preserveScroll: true, replace: true, only: props.only },
    );
}

function onInputBlur(): void {
    const v = Number(pageInput.value);
    const clamped = Math.max(1, Math.min(safeMeta.value.last_page, Math.floor(v) || 1));
    pageInput.value = clamped;
    if (clamped !== safeMeta.value.current_page) goTo(clamped);
}

function onInputEnter(e: KeyboardEvent): void {
    if (e.key === 'Enter') {
        (e.target as HTMLInputElement).blur();
    }
}

function changePerPage(v: string): void {
    const perPage = Number(v) || 10;
    router.get(
        window.location.pathname,
        { ...routeQuery(), per_page: perPage, page: 1 },
        { preserveState: true, preserveScroll: true, replace: true, only: props.only },
    );
}

function routeQuery(): Record<string, string> {
    const url = new URL(window.location.href);
    const q: Record<string, string> = {};
    url.searchParams.forEach((v, k) => {
        q[k] = v;
    });
    delete q.page;
    return q;
}

const isFirst = computed(() => safeMeta.value.current_page <= 1);
const isLast = computed(() => safeMeta.value.current_page >= safeMeta.value.last_page);

const navBtn = 'inline-flex h-7 w-7 items-center justify-center rounded text-[var(--text-muted)] transition-colors hover:bg-[var(--state-hover)] hover:text-[var(--text-default)] disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent';
</script>

<template>
    <div :class="cn('flex items-center gap-3 flex-wrap', $props.class)">
        <!-- Total info -->
        <p v-if="showCount" class="text-xs text-[var(--text-muted)] tabular-nums order-1">
            {{ t('common.showing') }}
            <span class="font-semibold text-[var(--text-default)]">{{ safeMeta.from }}</span>
            <span>–</span>
            <span class="font-semibold text-[var(--text-default)]">{{ safeMeta.to }}</span>
            {{ t('common.of') }}
            <span class="font-semibold text-[var(--text-default)]">{{ safeMeta.total }}</span>
            {{ t('common.items') }}
        </p>

        <!-- Page navigation: ‹‹ ‹ [input] of M › ›› -->
        <div v-if="showNav" class="flex items-center gap-0.5 order-3 sm:order-2">
            <button
                type="button"
                :class="navBtn"
                :disabled="isFirst"
                :aria-label="t('common.page') + ' 1'"
                @click="goTo(1)"
            >
                <ChevronsLeft class="h-3.5 w-3.5" />
            </button>
            <button
                type="button"
                :class="navBtn"
                :disabled="isFirst"
                :aria-label="t('common.previous')"
                @click="goTo(safeMeta.current_page - 1)"
            >
                <ChevronLeft class="h-3.5 w-3.5" />
            </button>

            <div class="flex items-center gap-1 px-1 text-xs">
                <input
                    v-model.number="pageInput"
                    type="number"
                    :min="1"
                    :max="safeMeta.last_page"
                    class="h-7 w-10 rounded bg-transparent px-1 text-center text-xs font-semibold tabular-nums text-[var(--text-default)] transition-colors hover:bg-[var(--state-hover)] focus-visible:outline-none focus-visible:bg-[var(--state-hover)] focus-visible:ring-1 focus-visible:ring-[var(--border-focus)] [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                    :aria-label="t('common.page')"
                    @blur="onInputBlur"
                    @keydown="onInputEnter"
                />
                <span class="text-[var(--text-muted)] whitespace-nowrap">
                    / <span class="font-semibold text-[var(--text-default)] tabular-nums">{{ safeMeta.last_page }}</span>
                </span>
            </div>

            <button
                type="button"
                :class="navBtn"
                :disabled="isLast"
                :aria-label="t('common.next')"
                @click="goTo(safeMeta.current_page + 1)"
            >
                <ChevronRight class="h-3.5 w-3.5" />
            </button>
            <button
                type="button"
                :class="navBtn"
                :disabled="isLast"
                :aria-label="t('common.page') + ' ' + safeMeta.last_page"
                @click="goTo(safeMeta.last_page)"
            >
                <ChevronsRight class="h-3.5 w-3.5" />
            </button>
        </div>

        <!-- Per-page selector (borderless, ghost) -->
        <div v-if="showPerPage" class="relative order-2 sm:order-3">
            <select
                :value="safeMeta.per_page"
                class="h-7 appearance-none rounded bg-transparent pl-2 pr-6 text-xs font-semibold tabular-nums text-[var(--text-default)] cursor-pointer transition-colors hover:bg-[var(--state-hover)] focus-visible:outline-none focus-visible:bg-[var(--state-hover)] focus-visible:ring-1 focus-visible:ring-[var(--border-focus)]"
                @change="changePerPage(($event.target as HTMLSelectElement).value)"
            >
                <option v-for="opt in [10, 25, 50, 100]" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <ChevronDown class="pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2 h-3 w-3 text-[var(--text-muted)]" />
        </div>
    </div>
</template>
