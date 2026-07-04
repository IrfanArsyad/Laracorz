<script setup lang="ts">
import { inject, type Ref } from 'vue';
import { cn } from '@/lib/utils';

defineProps<{ value: string; class?: string }>();
const active = inject<Ref<string>>('tabsValue');
</script>

<template>
    <div
        v-if="active === value"
        :class="cn('mt-2 focus-visible:outline-none', $props.class)"
        role="tabpanel"
    >
        <slot />
    </div>
</template>
