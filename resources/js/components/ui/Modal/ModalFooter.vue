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
                'flex items-center gap-2.5 px-5 py-3.5 border-t border-[var(--border-subtle)] rounded-b-xl',
                alignClass[align],
                $props.class,
            )
        "
    >
        <slot />
    </footer>
</template>
