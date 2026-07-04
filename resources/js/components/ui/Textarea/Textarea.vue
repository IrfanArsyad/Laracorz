<script setup lang="ts">
import { computed, inject, type Ref } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        placeholder?: string;
        rows?: number;
        disabled?: boolean;
        error?: boolean | string;
        autoResize?: boolean;
        class?: string;
        id?: string;
    }>(),
    { rows: 3, disabled: false },
);

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const injectedError = inject<Ref<string | undefined>>('formFieldError');
const injectedId = inject<Ref<string>>('formFieldId');
const isError = computed(() => Boolean(props.error || injectedError?.value));

const classes = computed(() =>
    cn(
        'flex min-h-[80px] w-full rounded-md border bg-[var(--surface-raised)] px-3 py-2 text-sm text-[var(--text-default)]',
        'transition-[border-color,box-shadow,background-color] duration-[var(--duration-fast)] ease-[var(--ease-out)]',
        'placeholder:text-[var(--text-muted)]',
        'hover:border-[var(--border-strong)]',
        'focus-visible:outline-none focus-visible:border-[var(--border-focus)] focus-visible:ring-4 focus-visible:ring-[color-mix(in_oklab,var(--focus-ring),transparent_82%)]',
        'disabled:cursor-not-allowed disabled:opacity-60 disabled:bg-[var(--surface-sunken)]',
        isError.value
            ? 'border-[var(--status-danger-border)] focus-visible:border-[var(--focus-ring-error)] focus-visible:ring-[color-mix(in_oklab,var(--focus-ring-error),transparent_82%)]'
            : 'border-[var(--border-default)]',
        props.class,
    ),
);

function onInput(e: Event): void {
    const el = e.target as HTMLTextAreaElement;
    if (props.autoResize) {
        el.style.height = 'auto';
        el.style.height = `${el.scrollHeight}px`;
    }
    emit('update:modelValue', el.value);
}
</script>

<template>
    <textarea
        :id="id ?? injectedId"
        :value="modelValue ?? ''"
        :placeholder="placeholder"
        :rows="rows"
        :disabled="disabled"
        :aria-invalid="isError ? 'true' : undefined"
        :class="classes"
        @input="onInput"
    />
</template>
