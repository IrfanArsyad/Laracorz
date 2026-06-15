<script setup lang="ts">
import { computed } from 'vue';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import Checkbox from '../Checkbox/Checkbox.vue';
import { Inbox, SearchX } from 'lucide-vue-next';
import EmptyState from '../EmptyState/EmptyState.vue';
import Skeleton from '../Skeleton/Skeleton.vue';
import TableShell from '../TableShell/TableShell.vue';
import { cn } from '@/lib/utils';
import type { Paginated, PaginationMeta } from '@/types';

const { t } = useI18n();

export interface Column<T = unknown> {
    key: string;
    label: string;
    sortable?: boolean;
    align?: 'left' | 'center' | 'right';
    class?: string;
    width?: string;
    accessor?: (row: T) => unknown;
}

const props = withDefaults(
    defineProps<{
        data: Paginated<Record<string, unknown>>;
        columns: Column[];
        rowKey?: string;
        loading?: boolean;
        sort?: string | null;
        direction?: 'asc' | 'desc';
        selectable?: boolean;
        selected?: Array<string | number>;
        actionStickyRight?: boolean;
        only?: string[];
        emptyTitle?: string;
        emptyDescription?: string;
        /** Set true bila ada filter aktif (search/select) — empty state akan
         *  pakai copy "Tidak ada hasil" instead of "Belum ada data". */
        hasActiveFilter?: boolean;
        class?: string;
    }>(),
    {
        rowKey: 'id',
        loading: false,
        selectable: false,
        actionStickyRight: false,
        selected: () => [],
        hasActiveFilter: false,
    },
);

const emit = defineEmits<{
    sort: [field: string];
    'update:selected': [v: Array<string | number>];
    'row-click': [row: Record<string, unknown>];
}>();

const rows = computed(() => props.data?.data ?? []);

// Laravel ->paginate() langsung mengembalikan flat `{ data, current_page,
// last_page, total, per_page, from, to, ... }` — TANPA wrapper meta. Resource
// collection bungkus dalam { data, meta, links }. Dukung dua-duanya.
const paginationMeta = computed<PaginationMeta | null>(() => {
    if (!props.data) return null;
    const d = props.data as unknown as Record<string, unknown>;
    if (d.meta) return d.meta as PaginationMeta;
    if (typeof d.current_page === 'number') {
        return {
            current_page: d.current_page as number,
            last_page: (d.last_page as number) ?? 1,
            from: (d.from as number | null) ?? 0,
            to: (d.to as number | null) ?? 0,
            total: (d.total as number) ?? 0,
            per_page: (d.per_page as number) ?? 10,
            path: (d.path as string) ?? '',
            links: (d.links as never) ?? [],
        };
    }
    return null;
});
const safeSelected = computed<Array<string | number>>(() =>
    Array.isArray(props.selected) ? props.selected : [],
);

const allSelected = computed(() => {
    if (rows.value.length === 0) return false;
    return rows.value.every((r) => safeSelected.value.includes(r[props.rowKey] as string | number));
});

const someSelected = computed(() => !allSelected.value && safeSelected.value.length > 0);

function toggleAll(): void {
    if (allSelected.value) {
        emit('update:selected', []);
    } else {
        emit('update:selected', rows.value.map((r) => r[props.rowKey] as string | number));
    }
}

function toggleRow(row: Record<string, unknown>): void {
    const key = row[props.rowKey] as string | number;
    const set = new Set(safeSelected.value);
    if (set.has(key)) set.delete(key);
    else set.add(key);
    emit('update:selected', Array.from(set));
}

function sortBy(col: Column): void {
    if (!col.sortable) return;
    emit('sort', col.key);
}

</script>

<template>
    <TableShell :pagination-meta="paginationMeta" :only="only" :class="$props.class">
        <template v-if="$slots.toolbar" #toolbar><slot name="toolbar" /></template>

        <template #bulk>
            <div v-if="selected.length > 0" class="flex items-center gap-2 border-b border-[var(--border-subtle)] bg-[var(--brand-soft-bg)] text-[var(--brand-soft-fg)] px-4 py-2 text-sm">
                <span class="font-medium">{{ t('table.selected', { count: selected.length }) }}</span>
                <div class="ml-auto flex items-center gap-2">
                    <slot name="bulk-actions" :selected="selected" />
                </div>
            </div>
        </template>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[var(--surface-sunken)] text-[var(--text-muted)] border-b border-[var(--border-subtle)]">
                    <tr>
                        <th v-if="selectable" class="w-9 px-3 py-2">
                            <Checkbox
                                :model-value="allSelected"
                                :indeterminate="someSelected"
                                @update:model-value="toggleAll"
                            />
                        </th>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="
                                cn(
                                    'px-3 py-2.5 text-xs font-semibold uppercase tracking-wider',
                                    col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                                    col.sortable ? 'cursor-pointer select-none hover:text-[var(--text-default)] transition-colors' : '',
                                    col.class,
                                )
                            "
                            :style="col.width ? `width: ${col.width}` : ''"
                            @click="sortBy(col)"
                        >
                            <span class="inline-flex items-center gap-1">
                                {{ col.label }}
                                <span v-if="col.sortable" class="opacity-50">
                                    <ArrowUp v-if="sort === col.key && direction === 'asc'" class="h-3 w-3" />
                                    <ArrowDown v-else-if="sort === col.key && direction === 'desc'" class="h-3 w-3" />
                                    <ArrowUpDown v-else class="h-3 w-3" />
                                </span>
                            </span>
                        </th>
                        <th
                            v-if="$slots.actions"
                            :class="cn('px-3 py-2 text-right', actionStickyRight ? 'sticky right-0 bg-[var(--surface-sunken)] z-10' : '')"
                        >
                            <span class="sr-only">{{ t('common.actions') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-subtle)]">
                    <tr v-if="loading">
                        <td :colspan="columns.length + (selectable ? 1 : 0) + ($slots.actions ? 1 : 0)" class="px-3 py-3">
                            <Skeleton class="h-5 w-full" />
                        </td>
                    </tr>
                    <tr v-else-if="rows.length === 0">
                        <td :colspan="columns.length + (selectable ? 1 : 0) + ($slots.actions ? 1 : 0)" class="px-3 py-4">
                            <EmptyState
                                v-if="hasActiveFilter"
                                :icon="SearchX"
                                :title="t('table.noResults')"
                                :description="t('table.noResultsHint')"
                                compact
                            />
                            <EmptyState
                                v-else
                                :icon="Inbox"
                                :title="emptyTitle ?? t('table.empty')"
                                :description="emptyDescription"
                                compact
                            />
                        </td>
                    </tr>
                    <tr
                        v-for="row in rows"
                        v-else
                        :key="row[rowKey] as string | number"
                        :class="
                            cn(
                                'group transition-colors hover:bg-[var(--state-hover)]',
                                $attrs.onRowClick !== undefined ? 'cursor-pointer' : '',
                            )
                        "
                        @click="emit('row-click', row)"
                    >
                        <td v-if="selectable" class="w-9 px-3 py-2.5" @click.stop>
                            <Checkbox
                                :model-value="safeSelected.includes(row[rowKey] as string | number)"
                                @update:model-value="toggleRow(row)"
                            />
                        </td>
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            :class="
                                cn(
                                    'px-3 py-2.5 text-sm text-[var(--text-default)]',
                                    col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                                    col.class,
                                )
                            "
                        >
                            <slot :name="`cell-${col.key}`" :row="row" :value="col.accessor ? col.accessor(row) : row[col.key]">
                                {{ col.accessor ? col.accessor(row) : row[col.key] }}
                            </slot>
                        </td>
                        <td
                            v-if="$slots.actions"
                            :class="
                                cn(
                                    'px-3 py-2 text-right',
                                    actionStickyRight
                                        ? 'sticky right-0 bg-[var(--surface-raised)] before:absolute before:inset-0 before:pointer-events-none before:bg-[var(--state-hover)] before:opacity-0 group-hover:before:opacity-100 before:transition-opacity'
                                        : '',
                                )
                            "
                            @click.stop
                        >
                            <slot name="actions" :row="row" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </TableShell>
</template>
