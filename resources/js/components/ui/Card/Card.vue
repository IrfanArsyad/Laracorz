<script setup lang="ts">
import { cn } from '@/lib/utils';
defineProps<{ class?: string }>();
</script>

<template>
    <div
        :class="
            cn(
                'rounded-xl border border-[var(--border-subtle)] bg-[var(--surface-raised)] text-[var(--text-default)]',
                'shadow-[var(--shadow-xs)]',
                $props.class,
            )
        "
    >
        <slot />
    </div>
</template>
