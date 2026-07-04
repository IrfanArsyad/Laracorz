<script setup lang="ts">
import type { Component } from 'vue';
import { Inbox } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { cn } from '@/lib/utils';

const { t } = useI18n();

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        icon?: Component;
        /** Padding compact untuk pakai dalam tabel/card kecil */
        compact?: boolean;
        class?: string;
    }>(),
    { icon: Inbox, compact: false },
);
</script>

<template>
    <div
        :class="
            cn(
                'flex flex-col items-center justify-center text-center',
                compact ? 'py-8 px-4' : 'py-14 px-6',
                $props.class,
            )
        "
    >
        <div
            :class="
                cn(
                    'rounded-full bg-[var(--surface-sunken)] flex items-center justify-center text-[var(--text-muted)] mb-3 ring-1 ring-[var(--border-subtle)]',
                    compact ? 'h-11 w-11' : 'h-14 w-14',
                )
            "
        >
            <component :is="icon" :class="compact ? 'h-5 w-5' : 'h-6 w-6'" />
        </div>
        <h3 class="text-sm font-semibold text-[var(--text-strong)]">
            {{ title ?? t('table.empty') }}
        </h3>
        <p v-if="description" class="mt-1 text-sm text-[var(--text-muted)] max-w-sm leading-relaxed">
            {{ description }}
        </p>
        <div v-if="$slots.default" class="mt-4">
            <slot />
        </div>
    </div>
</template>
