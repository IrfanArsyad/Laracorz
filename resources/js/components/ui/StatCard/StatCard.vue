<script setup lang="ts">
import { computed } from 'vue';
import { TrendingDown, TrendingUp } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        label: string;
        value?: string | number;
        icon?: unknown;
        trend?: number;
        hint?: string;
        loading?: boolean;
        class?: string;
    }>(),
    { loading: false },
);

const trendUp = computed(() => (props.trend ?? 0) > 0);
const trendDown = computed(() => (props.trend ?? 0) < 0);
</script>

<template>
    <div
        :class="
            cn(
                'group relative rounded-xl border border-[var(--border-subtle)] bg-[var(--surface-raised)] p-4',
                'transition-[border-color,box-shadow] duration-[var(--duration-fast)] ease-[var(--ease-out)]',
                'hover:border-[var(--border-strong)] hover:shadow-[var(--shadow-xs)]',
                $props.class,
            )
        "
    >
        <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-medium text-[var(--text-muted)]">{{ label }}</p>
            <div
                v-if="icon"
                class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--brand-soft-bg)] text-[var(--brand-soft-fg)]"
            >
                <component :is="icon" class="h-4 w-4" />
            </div>
        </div>
        <p v-if="loading" class="mt-3 h-8 w-24 animate-pulse rounded bg-[var(--surface-sunken)]" />
        <p v-else class="mt-2 text-3xl font-semibold leading-tight tracking-tight tabular-nums text-[var(--text-strong)]">
            {{ value }}
        </p>
        <div v-if="(trend !== undefined || hint) && !loading" class="mt-1.5 flex items-center gap-2 text-xs">
            <span
                v-if="trend !== undefined"
                :class="
                    cn(
                        'inline-flex items-center gap-0.5 rounded-md px-1.5 py-0.5 font-medium',
                        trendUp
                            ? 'bg-[var(--status-success-bg)] text-[var(--status-success-fg)]'
                            : trendDown
                                ? 'bg-[var(--status-danger-bg)] text-[var(--status-danger-fg)]'
                                : 'bg-[var(--surface-sunken)] text-[var(--text-muted)]',
                    )
                "
            >
                <TrendingUp v-if="trendUp" class="h-3 w-3" />
                <TrendingDown v-if="trendDown" class="h-3 w-3" />
                <span>{{ Math.abs(trend ?? 0) }}%</span>
            </span>
            <span v-if="hint" class="text-[var(--text-muted)]">{{ hint }}</span>
        </div>
    </div>
</template>
