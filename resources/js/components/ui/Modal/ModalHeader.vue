<script setup lang="ts">
import { inject, type Ref } from 'vue';
import { X } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        closeable?: boolean;
        class?: string;
    }>(),
    { closeable: true },
);

// Modal primitive me-provide closer untuk tombol "X"
const close = inject<() => void>('modal:close', () => {});
const open = inject<Ref<boolean>>('modal:open');
</script>

<template>
    <header
        :class="
            cn(
                'flex items-start justify-between gap-4 px-5 pt-5 pb-4 border-b border-[var(--border-subtle)]',
                $props.class,
            )
        "
    >
        <div class="min-w-0 flex-1 space-y-1">
            <h2 v-if="title" class="text-base font-semibold leading-tight tracking-tight text-[var(--text-strong)]">
                {{ title }}
            </h2>
            <p v-if="description" class="text-sm leading-relaxed text-[var(--text-muted)]">
                {{ description }}
            </p>
            <slot />
        </div>

        <button
            v-if="closeable"
            type="button"
            class="-mr-1.5 -mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-[var(--text-muted)] hover:bg-[var(--state-hover)] hover:text-[var(--text-strong)] transition-colors"
            aria-label="Close"
            @click="close"
        >
            <X class="h-4 w-4" />
        </button>
    </header>
</template>
