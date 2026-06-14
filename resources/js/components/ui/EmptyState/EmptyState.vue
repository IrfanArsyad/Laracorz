<script setup lang="ts">
import { Inbox } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        icon?: unknown;
        class?: string;
    }>(),
    { title: 'Tidak ada data', icon: Inbox },
);
</script>

<template>
    <div :class="cn('flex flex-col items-center justify-center py-12 px-4 text-center', $props.class)">
        <div class="h-12 w-12 rounded-full bg-muted flex items-center justify-center text-muted-foreground mb-3">
            <component :is="icon" class="h-6 w-6" />
        </div>
        <h3 class="text-sm font-medium">{{ title }}</h3>
        <p v-if="description" class="mt-1 text-sm text-muted-foreground max-w-sm">
            {{ description }}
        </p>
        <div v-if="$slots.default" class="mt-4">
            <slot />
        </div>
    </div>
</template>
