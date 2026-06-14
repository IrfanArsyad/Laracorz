<script setup lang="ts">
import { provide, toRef } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{ modelValue: string; class?: string }>();
const emit = defineEmits<{ 'update:modelValue': [v: string] }>();

provide('tabsValue', toRef(props, 'modelValue'));
provide('tabsChange', (v: string) => emit('update:modelValue', v));
</script>

<template>
    <div :class="cn('w-full', $props.class)">
        <slot />
    </div>
</template>
