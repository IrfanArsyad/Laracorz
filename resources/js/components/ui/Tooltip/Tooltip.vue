<script setup lang="ts">
import { ref } from 'vue';
import { cn } from '@/lib/utils';

withDefaults(
    defineProps<{
        text: string;
        position?: 'top' | 'bottom' | 'left' | 'right';
        delay?: number;
        class?: string;
    }>(),
    { position: 'top', delay: 300 },
);

const open = ref(false);
let timer: ReturnType<typeof setTimeout> | null = null;

function show(): void {
    timer = setTimeout(() => (open.value = true), 200);
}
function hide(): void {
    if (timer) clearTimeout(timer);
    open.value = false;
}

const positions: Record<string, string> = {
    top: '-translate-x-1/2 left-1/2 bottom-full mb-1',
    bottom: '-translate-x-1/2 left-1/2 top-full mt-1',
    left: '-translate-y-1/2 top-1/2 right-full mr-1',
    right: '-translate-y-1/2 top-1/2 left-full ml-1',
};
</script>

<template>
    <span class="relative inline-flex" @mouseenter="show" @mouseleave="hide" @focusin="show" @focusout="hide">
        <slot />
        <span
            v-if="open"
            role="tooltip"
            :class="
                cn(
                    'absolute z-50 pointer-events-none whitespace-nowrap rounded-md bg-foreground text-background text-xs px-2 py-1 shadow',
                    positions[position],
                    $props.class,
                )
            "
        >
            {{ text }}
        </span>
    </span>
</template>
