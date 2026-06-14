<script setup lang="ts">
import { computed } from 'vue';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import Checkbox from '../Checkbox/Checkbox.vue';
import EmptyState from '../EmptyState/EmptyState.vue';
import Pagination from '../Pagination/Pagination.vue';
import Skeleton from '../Skeleton/Skeleton.vue';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

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
        class?: string;
    }>(),
    {
        rowKey: 'id',
        loading: false,
        selectable: false,
        actionStickyRight: true,
        emptyTitle: 'Belum ada data',
        selected: () => [],
    },
);

const emit = defineEmits<{
    sort: [field: string];
    'update:selected': [v: Array<string | number>];
    'row-click': [row: Record<string, unknown>];
}>();

const rows = computed(() => props.data?.data ?? []);
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
    <div :class="cn('rounded-xl border border-[var(--border-subtle)] bg-[var(--surface-raised)] shadow-[var(--shadow-xs)] overflow-hidden', $props.class)">
        <div v-if="$slots.toolbar" class="border-b border-[var(--border-subtle)] px-4 py-2.5">
            <slot name="toolbar" />
        </div>

        <div v-if="selected.length > 0" class="flex items-center gap-2 border-b border-[var(--border-subtle)] bg-[var(--brand-soft-bg)] text-[var(--brand-soft-fg)] px-4 py-2 text-sm">
            <span class="font-medium">{{ selected.length }} dipilih</span>
            <div class="ml-auto flex items-center gap-2">
                <slot name="bulk-actions" :selected="selected" />
            </div>
        </div>

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
                                    'px-3 py-2 text-xs font-medium tracking-tight',
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
                            :class="cn('px-3 py-2 text-right', actionStickyRight ? 'sticky right-0 bg-[var(--surface-sunken)]' : '')"
                        >
                            <span class="sr-only">Aksi</span>
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
                        <td :colspan="columns.length + (selectable ? 1 : 0) + ($slots.actions ? 1 : 0)" class="px-3 py-10">
                            <EmptyState :title="emptyTitle" :description="emptyDescription" />
                        </td>
                    </tr>
                    <tr
                        v-for="row in rows"
                        v-else
                        :key="row[rowKey] as string | number"
                        class="transition-colors hover:bg-[var(--state-hover)]"
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
                            :class="cn('px-3 py-2 text-right', actionStickyRight ? 'sticky right-0 bg-[var(--surface-raised)]' : '')"
                            @click.stop
                        >
                            <slot name="actions" :row="row" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="data?.meta" class="border-t border-[var(--border-subtle)] px-4 py-2 bg-[var(--surface-sunken)]/40 rounded-b-xl">
            <Pagination :meta="data.meta" :only="only" />
        </div>
    </div>
</template>
