<script setup lang="ts">
import { provide, ref } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        type?: 'single' | 'multiple';
        defaultValue?: string | string[];
        class?: string;
    }>(),
    { type: 'single', defaultValue: () => [] },
);

const value = ref<string[]>(
    Array.isArray(props.defaultValue)
        ? props.defaultValue
        : props.defaultValue
          ? [props.defaultValue]
          : [],
);

function toggle(id: string): void {
    if (props.type === 'single') {
        value.value = value.value.includes(id) ? [] : [id];
    } else {
        value.value = value.value.includes(id) ? value.value.filter((v) => v !== id) : [...value.value, id];
    }
}

provide('accordionValue', value);
provide('accordionToggle', toggle);
</script>

<template>
    <div :class="cn('divide-y divide-border border-y border-border', $props.class)">
        <slot />
    </div>
</template>
