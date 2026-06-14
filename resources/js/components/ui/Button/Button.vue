<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { cva, type VariantProps } from 'class-variance-authority';
import { Loader2 } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const buttonVariants = cva(
    [
        'relative inline-flex items-center justify-center gap-2 whitespace-nowrap select-none',
        'rounded-md text-sm font-medium',
        'transition-[background-color,color,border-color,box-shadow,transform]',
        'duration-[var(--duration-fast)] ease-[var(--ease-out)]',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--focus-ring)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--surface-base)]',
        'disabled:pointer-events-none disabled:opacity-50',
        'active:translate-y-px',
    ].join(' '),
    {
        variants: {
            variant: {
                default: [
                    'bg-[var(--brand-bg)] text-[var(--brand-fg)] shadow-xs',
                    'hover:bg-[var(--brand-bg-hover)]',
                    'active:bg-[var(--brand-bg-pressed)]',
                ].join(' '),
                destructive: [
                    'bg-[var(--status-danger-solid)] text-[var(--status-danger-solid-fg)] shadow-xs',
                    'hover:bg-[var(--danger-700)] active:bg-[var(--danger-800)]',
                ].join(' '),
                success: [
                    'bg-[var(--status-success-solid)] text-[var(--status-success-solid-fg)] shadow-xs',
                    'hover:bg-[var(--success-700)] active:bg-[var(--success-800)]',
                ].join(' '),
                warning: [
                    'bg-[var(--status-warning-solid)] text-[var(--status-warning-solid-fg)] shadow-xs',
                    'hover:bg-[var(--warning-600)] active:bg-[var(--warning-700)]',
                ].join(' '),
                outline: [
                    'border border-[var(--border-default)] bg-[var(--surface-raised)] text-[var(--text-default)]',
                    'hover:bg-[var(--surface-sunken)] hover:border-[var(--border-strong)]',
                    'active:bg-[var(--state-pressed)]',
                ].join(' '),
                secondary: [
                    'bg-[var(--brand-soft-bg)] text-[var(--brand-soft-fg)]',
                    'hover:bg-[color-mix(in_oklab,var(--brand-soft-bg),var(--brand-600)_8%)]',
                ].join(' '),
                ghost: [
                    'text-[var(--text-default)]',
                    'hover:bg-[var(--state-hover)] active:bg-[var(--state-pressed)]',
                ].join(' '),
                link: [
                    'text-[var(--text-link)] underline-offset-4',
                    'hover:underline hover:text-[var(--text-link-hover)]',
                ].join(' '),
            },
            size: {
                default: 'h-10 px-4 text-sm',            // CTA standar
                sm: 'h-9 px-3.5 text-sm gap-1.5',    // secondary action
                xs: 'h-8 px-2.5 text-xs gap-1',          // inline action di tabel
                lg: 'h-11 px-5 text-sm',                 // primary banner/hero
                icon: 'h-10 w-10',
                'icon-sm': 'h-9 w-9',
                'icon-xs': 'h-8 w-8',
            },
        },
        defaultVariants: { variant: 'default', size: 'default' },
    },
);

type ButtonVariants = VariantProps<typeof buttonVariants>;

const props = withDefaults(
    defineProps<{
        variant?: ButtonVariants['variant'];
        size?: ButtonVariants['size'];
        type?: 'button' | 'submit' | 'reset';
        loading?: boolean;
        disabled?: boolean;
        as?: 'button' | 'a' | 'link';
        href?: string;
        class?: string;
    }>(),
    {
        variant: 'default',
        size: 'default',
        type: 'button',
        loading: false,
        disabled: false,
        as: 'button',
    },
);

const classes = computed(() =>
    cn(buttonVariants({ variant: props.variant, size: props.size }), props.class),
);
</script>

<template>
    <Link v-if="as === 'link' && href" :href="href" :class="classes" :tabindex="disabled ? -1 : 0">
        <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
        <slot />
    </Link>
    <a
        v-else-if="as === 'a' && href"
        :href="href"
        :class="classes"
        :aria-disabled="disabled || loading"
    >
        <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
        <slot />
    </a>
    <button
        v-else
        :type="type"
        :class="classes"
        :disabled="disabled || loading"
        :aria-busy="loading"
    >
        <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
        <slot />
    </button>
</template>
