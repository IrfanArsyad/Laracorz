<script setup lang="ts">
import { cn } from '@/lib/utils';
defineProps<{ class?: string }>();
</script>

<template>
    <p :class="cn('text-sm text-[var(--text-muted)]', $props.class)">
        <slot />
    </p>
</template>
