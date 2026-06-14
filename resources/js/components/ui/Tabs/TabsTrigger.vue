<script setup lang="ts">
import { inject, type Ref } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{ value: string; class?: string }>();
const active = inject<Ref<string>>('tabsValue');
const change = inject<(v: string) => void>('tabsChange');
</script>

<template>
    <button
        type="button"
        role="tab"
        :aria-selected="active?.value === value"
        :class="
            cn(
                'inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                active?.value === value ? 'bg-background text-foreground shadow-sm' : '',
                $props.class,
            )
        "
        @click="change?.(value)"
    >
        <slot />
    </button>
</template>
