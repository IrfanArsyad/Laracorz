<script setup lang="ts">
import { cn } from '@/lib/utils';

withDefaults(
    defineProps<{
        orientation?: 'horizontal' | 'vertical';
        class?: string;
    }>(),
    { orientation: 'horizontal' },
);
</script>

<template>
    <div
        :class="
            cn(
                'shrink-0 bg-border',
                orientation === 'horizontal' ? 'h-px w-full' : 'w-px h-full',
                $props.class,
            )
        "
        role="separator"
        :aria-orientation="orientation"
    />
</template>
