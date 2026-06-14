<script setup lang="ts">
import { inject, computed, type Ref } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps<{ value: string; title: string; class?: string }>();
const active = inject<Ref<string[]>>('accordionValue');
const toggle = inject<(v: string) => void>('accordionToggle');
const open = computed(() => active?.value.includes(props.value) ?? false);
</script>

<template>
    <div :class="cn('py-1', $props.class)">
        <button
            type="button"
            class="flex w-full items-center justify-between gap-2 py-3 text-left text-sm font-medium hover:underline"
            :aria-expanded="open"
            @click="toggle?.(value)"
        >
            <span>{{ title }}</span>
            <ChevronDown :class="cn('h-4 w-4 transition-transform', open ? 'rotate-180' : '')" />
        </button>
        <div v-if="open" class="pb-3 text-sm text-muted-foreground">
            <slot />
        </div>
    </div>
</template>
