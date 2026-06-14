<script setup lang="ts">
import { cn } from '@/lib/utils';
defineProps<{ class?: string }>();
</script>

<template>
    <kbd
        :class="
            cn(
                'inline-flex items-center gap-1 rounded border border-border bg-muted px-1.5 py-0.5 text-[10px] font-mono text-muted-foreground',
                $props.class,
            )
        "
    >
        <slot />
    </kbd>
</template>
