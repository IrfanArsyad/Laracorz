<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{ align?: 'start' | 'center' | 'end'; class?: string }>(), { align: 'center' });

const open = ref(false);
const ref_ = ref<HTMLElement | null>(null);

function onDoc(e: MouseEvent): void {
    if (!ref_.value?.contains(e.target as Node)) open.value = false;
}
onMounted(() => document.addEventListener('mousedown', onDoc));
onUnmounted(() => document.removeEventListener('mousedown', onDoc));

const alignClass = props.align === 'start' ? 'left-0' : props.align === 'end' ? 'right-0' : 'left-1/2 -translate-x-1/2';
</script>

<template>
    <div ref="ref_" class="relative inline-block">
        <span @click="open = !open">
            <slot name="trigger" />
        </span>
        <div
            v-if="open"
            :class="
                cn(
                    'absolute z-50 mt-2 rounded-md border border-border bg-popover p-3 shadow-md min-w-48',
                    alignClass,
                    $props.class,
                )
            "
        >
            <slot />
        </div>
    </div>
</template>
