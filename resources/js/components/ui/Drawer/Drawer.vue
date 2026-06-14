<script setup lang="ts">
import { computed, watch, onUnmounted } from 'vue';
import { X } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue: boolean;
        side?: 'right' | 'left' | 'bottom';
        title?: string;
        size?: 'sm' | 'md' | 'lg';
        class?: string;
    }>(),
    { side: 'right', size: 'md' },
);

const emit = defineEmits<{ 'update:modelValue': [v: boolean] }>();
function close(): void {
    emit('update:modelValue', false);
}

const sizeClass = computed(() => {
    if (props.side === 'bottom') {
        return props.size === 'sm' ? 'h-1/3' : props.size === 'lg' ? 'h-3/4' : 'h-1/2';
    }
    return props.size === 'sm' ? 'w-80' : props.size === 'lg' ? 'w-[36rem]' : 'w-96';
});

const positionClass = computed(() => {
    if (props.side === 'right') return 'right-0 top-0 h-full';
    if (props.side === 'left') return 'left-0 top-0 h-full';
    return 'bottom-0 left-0 w-full';
});

const enterFrom = computed(() => {
    if (props.side === 'right') return 'translate-x-full';
    if (props.side === 'left') return '-translate-x-full';
    return 'translate-y-full';
});

watch(
    () => props.modelValue,
    (v) => {
        if (typeof document === 'undefined') return;
        document.body.style.overflow = v ? 'hidden' : '';
    },
);

onUnmounted(() => {
    if (typeof document !== 'undefined') document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="modelValue" class="fixed inset-0 z-50 bg-black/50" @click.self="close" />
        </Transition>
        <Transition
            :enter-active-class="'transition ease-out duration-200'"
            :enter-from-class="enterFrom + ' opacity-0'"
            :enter-to-class="'translate-x-0 translate-y-0 opacity-100'"
            :leave-active-class="'transition ease-in duration-150'"
            :leave-from-class="'translate-x-0 translate-y-0 opacity-100'"
            :leave-to-class="enterFrom + ' opacity-0'"
        >
            <aside
                v-if="modelValue"
                :class="
                    cn(
                        'fixed z-50 bg-card border-border shadow-lg flex flex-col',
                        positionClass,
                        sizeClass,
                        side !== 'bottom' ? 'border-l border-r' : 'border-t',
                        $props.class,
                    )
                "
            >
                <header v-if="title || $slots.header" class="flex items-center justify-between border-b border-border px-4 py-3">
                    <h2 class="text-base font-semibold">{{ title }}</h2>
                    <button
                        type="button"
                        class="rounded-md p-1 text-muted-foreground hover:bg-muted"
                        aria-label="Tutup"
                        @click="close"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </header>
                <div class="flex-1 overflow-y-auto p-4">
                    <slot />
                </div>
                <div v-if="$slots.footer" class="border-t border-border p-4">
                    <slot name="footer" />
                </div>
            </aside>
        </Transition>
    </Teleport>
</template>
