<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Item {
    label: string;
    value?: string | number | null;
}

defineProps<{ items: Item[]; class?: string }>();
</script>

<template>
    <dl :class="cn('divide-y divide-border', $props.class)">
        <div v-for="(it, i) in items" :key="i" class="grid grid-cols-3 gap-2 py-3 text-sm">
            <dt class="text-muted-foreground">{{ it.label }}</dt>
            <dd class="col-span-2 text-foreground">{{ it.value ?? '-' }}</dd>
        </div>
    </dl>
</template>
