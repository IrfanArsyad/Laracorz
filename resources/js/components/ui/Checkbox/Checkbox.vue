<script setup lang="ts">
import { computed } from 'vue';
import { Check, Minus } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: boolean | unknown[];
        value?: unknown;
        indeterminate?: boolean;
        disabled?: boolean;
        class?: string;
        id?: string;
    }>(),
    { disabled: false, indeterminate: false },
);

const emit = defineEmits<{ 'update:modelValue': [value: boolean | unknown[]] }>();

const isChecked = computed(() => {
    if (Array.isArray(props.modelValue)) {
        return props.modelValue.includes(props.value);
    }
    return !!props.modelValue;
});

function toggle(): void {
    if (props.disabled) return;
    if (Array.isArray(props.modelValue)) {
        const set = new Set(props.modelValue);
        if (set.has(props.value)) set.delete(props.value);
        else set.add(props.value);
        emit('update:modelValue', Array.from(set));
    } else {
        emit('update:modelValue', !props.modelValue);
    }
}
</script>

<template>
    <button
        :id="id"
        type="button"
        role="checkbox"
        :aria-checked="indeterminate ? 'mixed' : isChecked"
        :disabled="disabled"
        :class="
            cn(
                'inline-flex h-4 w-4 shrink-0 items-center justify-center rounded border border-input bg-background transition-colors',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                isChecked || indeterminate ? 'bg-primary border-primary text-primary-foreground' : '',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
                props.class,
            )
        "
        @click="toggle"
        @keydown.space.prevent="toggle"
    >
        <Minus v-if="indeterminate" class="h-3 w-3" />
        <Check v-else-if="isChecked" class="h-3 w-3" />
    </button>
</template>
