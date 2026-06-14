<script setup lang="ts">
import { cn } from '@/lib/utils';
defineProps<{ class?: string }>();
</script>

<template>
    <div :class="cn('flex items-center px-5 pb-5 pt-0', $props.class)">
        <slot />
    </div>
</template>
