<script setup lang="ts">
import { computed, ref } from 'vue';
import { X, CheckCircle2, AlertTriangle, Info, AlertCircle } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';
import { cn } from '@/lib/utils';

const { toasts, dismiss } = useToast();

const variants = {
    success: {
        icon: CheckCircle2,
        bar: 'bg-[var(--status-success-solid)]',
        text: 'text-[var(--status-success-fg)]',
    },
    error: {
        icon: AlertCircle,
        bar: 'bg-[var(--status-danger-solid)]',
        text: 'text-[var(--status-danger-fg)]',
    },
    warning: {
        icon: AlertTriangle,
        bar: 'bg-[var(--status-warning-solid)]',
        text: 'text-[var(--status-warning-fg)]',
    },
    info: {
        icon: Info,
        bar: 'bg-[var(--status-info-solid)]',
        text: 'text-[var(--status-info-fg)]',
    },
};

const paused = ref<Record<number, boolean>>({});
</script>

<template>
    <Teleport to="body">
        <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 w-80 max-w-[calc(100vw-2rem)]">
            <TransitionGroup
                enter-active-class="transition duration-[var(--duration-base)] ease-[var(--ease-out)]"
                enter-from-class="translate-x-2 opacity-0 scale-95"
                enter-to-class="translate-x-0 opacity-100 scale-100"
                leave-active-class="transition duration-[var(--duration-fast)] ease-[var(--ease-in-out)]"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <div
                    v-for="t in toasts"
                    :key="t.id"
                    role="status"
                    :class="
                        cn(
                            'pointer-events-auto relative flex items-start gap-3 rounded-lg border border-[var(--border-subtle)] bg-[var(--surface-overlay)] p-3.5 pl-4 shadow-[var(--shadow-overlay)]',
                            'overflow-hidden',
                        )
                    "
                    @mouseenter="paused[t.id] = true"
                    @mouseleave="paused[t.id] = false"
                >
                    <div :class="['absolute left-0 top-0 h-full w-1', variants[t.variant].bar]" />
                    <component
                        :is="variants[t.variant].icon"
                        :class="['h-5 w-5 shrink-0 mt-0.5', variants[t.variant].text]"
                    />
                    <div class="flex-1 text-sm">
                        <p v-if="t.title" class="font-medium leading-none mb-1 text-[var(--text-strong)]">{{ t.title }}</p>
                        <p class="text-[var(--text-default)]">{{ t.message }}</p>
                    </div>
                    <button
                        type="button"
                        class="text-[var(--text-muted)] hover:text-[var(--text-strong)] transition-colors"
                        aria-label="Tutup"
                        @click="dismiss(t.id)"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
