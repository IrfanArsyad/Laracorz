<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: boolean;
        disabled?: boolean;
        id?: string;
        class?: string;
    }>(),
    { modelValue: false, disabled: false },
);

const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>();

function toggle(): void {
    if (props.disabled) return;
    emit('update:modelValue', !props.modelValue);
}
</script>

<template>
    <button
        :id="id"
        type="button"
        role="switch"
        :aria-checked="modelValue"
        :disabled="disabled"
        :class="
            cn(
                'inline-flex h-6 w-11 shrink-0 items-center rounded-full border-2 border-transparent transition-colors',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                modelValue ? 'bg-primary' : 'bg-muted',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
                props.class,
            )
        "
        @click="toggle"
    >
        <span
            :class="[
                'pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform',
                modelValue ? 'translate-x-5' : 'translate-x-0',
            ]"
        />
    </button>
</template>
