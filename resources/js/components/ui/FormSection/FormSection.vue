<script setup lang="ts">
import { cn } from '@/lib/utils';

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        /**
         * Layout:
         * - stacked (default): header di atas, fields di bawah — airy & modern.
         * - split: header kiri (1/3), fields kanan (2/3) — formal/enterprise.
         */
        layout?: 'stacked' | 'split';
        class?: string;
    }>(),
    { layout: 'stacked' },
);
</script>

<template>
    <section
        v-if="layout === 'stacked'"
        :class="
            cn(
                'space-y-4 py-5 first:pt-0 last:pb-0',
                'border-t border-[var(--border-subtle)] first:border-t-0',
                $props.class,
            )
        "
    >
        <div v-if="title || description" class="space-y-1">
            <h3 v-if="title" class="text-sm font-semibold text-[var(--text-strong)]">{{ title }}</h3>
            <p v-if="description" class="text-xs text-[var(--text-muted)]">{{ description }}</p>
        </div>
        <div class="space-y-3.5">
            <slot />
        </div>
    </section>

    <section
        v-else
        :class="
            cn(
                'grid gap-6 md:grid-cols-3 py-5 first:pt-0 last:pb-0',
                'border-t border-[var(--border-subtle)] first:border-t-0',
                $props.class,
            )
        "
    >
        <div class="md:col-span-1 space-y-1">
            <h3 v-if="title" class="text-sm font-semibold text-[var(--text-strong)]">{{ title }}</h3>
            <p v-if="description" class="text-xs text-[var(--text-muted)]">{{ description }}</p>
        </div>
        <div class="md:col-span-2 space-y-3.5">
            <slot />
        </div>
    </section>
</template>
