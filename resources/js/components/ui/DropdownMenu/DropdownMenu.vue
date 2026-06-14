<script setup lang="ts">
import { onMounted, onUnmounted, provide, ref, computed } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        align?: 'start' | 'end' | 'center';
        class?: string;
    }>(),
    { align: 'end' },
);

const open = ref(false);
const triggerRef = ref<HTMLElement | null>(null);

function toggle(): void {
    open.value = !open.value;
}
function close(): void {
    open.value = false;
}

function onDoc(e: MouseEvent): void {
    const target = e.target as HTMLElement;
    if (triggerRef.value?.contains(target)) return;
    const menu = document.querySelector('[data-dropdown-menu="true"]');
    if (menu && menu.contains(target)) return;
    open.value = false;
}

function onKey(e: KeyboardEvent): void {
    if (e.key === 'Escape') open.value = false;
}

onMounted(() => {
    document.addEventListener('mousedown', onDoc);
    document.addEventListener('keydown', onKey);
});
onUnmounted(() => {
    document.removeEventListener('mousedown', onDoc);
    document.removeEventListener('keydown', onKey);
});

provide('dropdownClose', close);

const alignClass = computed(() =>
    props.align === 'start' ? 'left-0' : props.align === 'center' ? 'left-1/2 -translate-x-1/2' : 'right-0',
);
</script>

<template>
    <div class="relative inline-block">
        <span ref="triggerRef" @click="toggle">
            <slot name="trigger" :open="open" />
        </span>
        <Transition
            enter-active-class="transition duration-[var(--duration-fast)] ease-[var(--ease-out)]"
            enter-from-class="opacity-0 translate-y-1 scale-[0.97]"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-[var(--duration-instant)] ease-[var(--ease-in-out)]"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                data-dropdown-menu="true"
                role="menu"
                :class="
                    cn(
                        'absolute z-50 mt-1.5 min-w-44 rounded-lg border border-[var(--border-subtle)] bg-[var(--surface-overlay)] text-[var(--text-default)] shadow-[var(--shadow-overlay)] p-1',
                        alignClass,
                        $props.class,
                    )
                "
            >
                <slot />
            </div>
        </Transition>
    </div>
</template>
