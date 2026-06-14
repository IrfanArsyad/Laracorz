<script setup lang="ts">
import { inject } from 'vue';
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        as?: 'button' | 'a' | 'link';
        href?: string;
        disabled?: boolean;
        variant?: 'default' | 'destructive';
        class?: string;
    }>(),
    { as: 'button', variant: 'default', disabled: false },
);

const close = inject<() => void>('dropdownClose', () => {});

const classes = cn(
    'flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm cursor-pointer select-none',
    'transition-colors duration-[var(--duration-instant)] ease-[var(--ease-out)]',
    props.variant === 'destructive'
        ? 'text-[var(--status-danger-fg)] hover:bg-[var(--status-danger-bg)]'
        : 'text-[var(--text-default)] hover:bg-[var(--state-hover)] hover:text-[var(--text-strong)]',
    'focus:outline-none focus-visible:bg-[var(--state-hover)]',
    props.disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '',
    props.class,
);

function onClick(): void {
    if (!props.disabled) close();
}
</script>

<template>
    <Link v-if="as === 'link' && href" :href="href" :class="classes" role="menuitem" @click="onClick">
        <slot />
    </Link>
    <a v-else-if="as === 'a' && href" :href="href" :class="classes" role="menuitem" @click="onClick">
        <slot />
    </a>
    <button v-else type="button" :class="classes" :disabled="disabled" role="menuitem" @click="onClick">
        <slot />
    </button>
</template>
