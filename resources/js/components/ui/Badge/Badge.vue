<script setup lang="ts">
import { computed } from 'vue';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const badgeVariants = cva(
    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium leading-5 border ring-1 ring-inset ring-transparent transition-colors',
    {
        variants: {
            variant: {
                // "soft" tone variants — modern, low-noise default for data tables/forms
                default:     'border-transparent bg-[var(--brand-soft-bg)] text-[var(--brand-soft-fg)]',
                secondary:   'border-transparent bg-[var(--surface-sunken)] text-[var(--text-default)]',
                muted:       'border-transparent bg-[var(--surface-sunken)] text-[var(--text-muted)]',
                outline:     'border-[var(--border-default)] bg-[var(--surface-raised)] text-[var(--text-default)]',
                success:     'border-[var(--status-success-border)] bg-[var(--status-success-bg)] text-[var(--status-success-fg)]',
                warning:     'border-[var(--status-warning-border)] bg-[var(--status-warning-bg)] text-[var(--status-warning-fg)]',
                destructive: 'border-[var(--status-danger-border)] bg-[var(--status-danger-bg)] text-[var(--status-danger-fg)]',
                info:        'border-[var(--status-info-border)] bg-[var(--status-info-bg)] text-[var(--status-info-fg)]',
                // "solid" untuk emphasis tinggi (notifikasi count, dst)
                solid:       'border-transparent bg-[var(--brand-bg)] text-[var(--brand-fg)]',
            },
        },
        defaultVariants: { variant: 'default' },
    },
);

type Variants = VariantProps<typeof badgeVariants>;

const props = withDefaults(
    defineProps<{ variant?: Variants['variant']; class?: string }>(),
    { variant: 'default' },
);
const classes = computed(() => cn(badgeVariants({ variant: props.variant }), props.class));
</script>

<template>
    <span :class="classes">
        <slot />
    </span>
</template>
