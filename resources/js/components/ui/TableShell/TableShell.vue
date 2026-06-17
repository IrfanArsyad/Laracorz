<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Pagination from '../Pagination/Pagination.vue';
import { cn } from '@/lib/utils';
import type { PaginationMeta } from '@/types';

/**
 * TableShell — chrome reusable untuk semua tabel (DataTable, SortableTree,
 * atau tabel custom apa pun). Punya:
 *
 *   <slot name="toolbar" />     ← opsional, di atas info bar
 *   ┌─────────────────────────────────────────────────────────┐
 *   │ {info bar: count + per-page + nav}                      │
 *   ├─────────────────────────────────────────────────────────┤
 *   │ <slot />   ← konten tabel apa saja                     │
 *   └─────────────────────────────────────────────────────────┘
 *
 * Mode info bar:
 * - paginationMeta diset → tampilkan count + per-page + nav otomatis
 *   (nav buttons hanya muncul kalau multi-page)
 * - paginationMeta null, summary diset → tampilkan summary statis
 *   (mis. "Showing 3 groups · 12 modules")
 * - tidak ada keduanya → info bar di-hide
 */
const { t } = useI18n();

const props = withDefaults(
    defineProps<{
        /** Pagination meta dari Laravel paginator (flat atau wrapped) */
        paginationMeta?: PaginationMeta | null;
        /** Override teks count untuk konten non-paginated (mis. tree) */
        summary?: string;
        /** Inertia `only` partial reload keys */
        only?: string[];
        class?: string;
    }>(),
    {},
);

const isMultiPage = computed(() => (props.paginationMeta?.last_page ?? 1) > 1);
const hasInfoBar = computed(() => Boolean(props.paginationMeta) || Boolean(props.summary));
</script>

<template>
    <div
        :class="
            cn(
                'overflow-hidden rounded-xl border border-[var(--border-subtle)] bg-[var(--surface-raised)] shadow-[var(--shadow-xs)]',
                $props.class,
            )
        "
    >
        <div v-if="$slots.toolbar" class="border-b border-[var(--border-subtle)] px-4 py-2.5">
            <slot name="toolbar" />
        </div>

        <!-- Info bar (count + per-page + nav) — ghost style, satu baris -->
        <div
            v-if="hasInfoBar"
            class="flex items-center justify-between gap-3 border-b border-[var(--border-subtle)] px-4 py-2"
        >
            <!-- Left: count -->
            <p v-if="summary" class="text-xs text-[var(--text-muted)] tabular-nums">
                {{ summary }}
            </p>
            <Pagination
                v-else-if="paginationMeta"
                :meta="paginationMeta"
                :only="only"
                :show-nav="false"
                :show-per-page="false"
                class="!gap-0"
            />

            <!-- Right: per-page + nav (only when paginated) -->
            <Pagination
                v-if="paginationMeta"
                :meta="paginationMeta"
                :only="only"
                :show-count="false"
                :show-nav="isMultiPage"
            />
        </div>

        <!-- Bulk action bar (slot) -->
        <slot name="bulk" />

        <!-- Body -->
        <slot />

        <!-- Footer slot (e.g. extra actions) -->
        <slot name="footer" />
    </div>
</template>
