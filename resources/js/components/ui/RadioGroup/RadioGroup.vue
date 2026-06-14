<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

interface Option {
    label: string;
    value: string | number;
    disabled?: boolean;
}

const props = withDefaults(
    defineProps<{
        modelValue?: string | number | null;
        options: Option[];
        name?: string;
        orientation?: 'horizontal' | 'vertical';
        disabled?: boolean;
        class?: string;
    }>(),
    { orientation: 'vertical', disabled: false },
);

const emit = defineEmits<{ 'update:modelValue': [value: string | number] }>();
const containerClass = computed(() => cn(props.orientation === 'horizontal' ? 'flex gap-4' : 'space-y-2', props.class));
</script>

<template>
    <div :class="containerClass" role="radiogroup">
        <label
            v-for="opt in options"
            :key="opt.value"
            class="inline-flex items-center gap-2 cursor-pointer"
            :class="opt.disabled || disabled ? 'opacity-50 cursor-not-allowed' : ''"
        >
            <input
                type="radio"
                class="h-4 w-4 border-input text-primary focus:ring-ring"
                :name="name"
                :value="opt.value"
                :checked="modelValue === opt.value"
                :disabled="disabled || opt.disabled"
                @change="emit('update:modelValue', opt.value)"
            />
            <span class="text-sm">{{ opt.label }}</span>
        </label>
    </div>
</template>
