<script setup lang="ts">
import { cn } from '@/lib/utils';

withDefaults(
    defineProps<{
        align?: 'right' | 'left' | 'between' | 'center';
        class?: string;
    }>(),
    { align: 'right' },
);

const alignClass = {
    right: 'justify-end',
    left: 'justify-start',
    between: 'justify-between',
    center: 'justify-center',
};
</script>

<template>
    <footer
        :class="
            cn(
                'flex items-center gap-2 px-5 py-3 border-t border-[var(--border-subtle)] bg-[var(--surface-sunken)]/40 rounded-b-xl',
                alignClass[align],
                $props.class,
            )
        "
    >
        <slot />
    </footer>
</template>
