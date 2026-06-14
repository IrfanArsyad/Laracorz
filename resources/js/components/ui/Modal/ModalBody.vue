<script setup lang="ts">
import { cn } from '@/lib/utils';

withDefaults(
    defineProps<{
        padded?: boolean;
        scroll?: boolean;
        class?: string;
    }>(),
    { padded: true, scroll: true },
);
</script>

<template>
    <div
        :class="
            cn(
                'flex-1',
                padded ? 'px-5 py-4' : '',
                scroll ? 'overflow-y-auto' : '',
                $props.class,
            )
        "
    >
        <slot />
    </div>
</template>
