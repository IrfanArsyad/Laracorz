<script setup lang="ts">
import { computed } from 'vue';
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

const classes = computed(() =>
    cn(
        'flex min-h-[80px] w-full rounded-md border bg-background px-3 py-2 text-sm',
        'placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
        'disabled:cursor-not-allowed disabled:opacity-50',
        props.error ? 'border-destructive' : 'border-input',
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
        :id="id"
        :value="modelValue ?? ''"
        :placeholder="placeholder"
        :rows="rows"
        :disabled="disabled"
        :class="classes"
        @input="onInput"
    />
</template>
