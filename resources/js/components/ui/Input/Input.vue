<script setup lang="ts">
import { computed, inject, type Ref } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: string | number | null;
        type?: string;
        placeholder?: string;
        disabled?: boolean;
        readonly?: boolean;
        error?: boolean | string;
        class?: string;
        id?: string;
        autocomplete?: string;
    }>(),
    { type: 'text', disabled: false, readonly: false, error: false },
);

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

// Auto-inject error + id dari FormField parent — otomatis konsisten
// tanpa harus eksplisit wire setiap form.
const injectedError = inject<Ref<string | undefined>>('formFieldError', undefined);
const injectedId = inject<Ref<string>>('formFieldId', undefined);

const isError = computed(() => Boolean(props.error || injectedError?.value));

const classes = computed(() =>
    cn(
        'flex h-9 w-full rounded-md border bg-[var(--surface-raised)] px-3 py-1.5 text-sm text-[var(--text-default)]',
        'transition-[border-color,box-shadow,background-color] duration-[var(--duration-fast)] ease-[var(--ease-out)]',
        'placeholder:text-[var(--text-muted)]',
        'hover:border-[var(--border-strong)]',
        'focus-visible:outline-none focus-visible:border-[var(--border-focus)] focus-visible:ring-4 focus-visible:ring-[color-mix(in_oklab,var(--focus-ring),transparent_82%)]',
        'disabled:cursor-not-allowed disabled:opacity-60 disabled:bg-[var(--surface-sunken)]',
        'read-only:bg-[var(--surface-sunken)] read-only:text-[var(--text-muted)]',
        isError.value
            ? 'border-[var(--status-danger-border)] focus-visible:border-[var(--focus-ring-error)] focus-visible:ring-[color-mix(in_oklab,var(--focus-ring-error),transparent_82%)]'
            : 'border-[var(--border-default)]',
        props.class,
    ),
);

function onInput(e: Event): void {
    emit('update:modelValue', (e.target as HTMLInputElement).value);
}
</script>

<template>
    <div class="relative w-full">
        <span v-if="$slots.prefix" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]">
            <slot name="prefix" />
        </span>
        <input
            :id="id ?? injectedId?.value"
            :type="type"
            :value="modelValue ?? ''"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :autocomplete="autocomplete"
            :aria-invalid="isError ? 'true' : undefined"
            :class="[
                classes,
                $slots.prefix ? 'pl-9' : '',
                $slots.suffix ? 'pr-9' : '',
            ]"
            @input="onInput"
        />
        <span v-if="$slots.suffix" class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]">
            <slot name="suffix" />
        </span>
    </div>
</template>
