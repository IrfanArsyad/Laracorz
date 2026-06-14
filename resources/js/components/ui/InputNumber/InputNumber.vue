<script setup lang="ts">
import { computed } from 'vue';
import Input from '../Input/Input.vue';

const props = withDefaults(
    defineProps<{
        modelValue?: number | null;
        min?: number;
        max?: number;
        step?: number;
        placeholder?: string;
        disabled?: boolean;
        error?: boolean | string;
        format?: boolean;
        id?: string;
    }>(),
    { step: 1, format: true },
);

const emit = defineEmits<{ 'update:modelValue': [value: number | null] }>();

const display = computed(() => {
    if (props.modelValue === null || props.modelValue === undefined) return '';
    return props.format ? new Intl.NumberFormat('id-ID').format(props.modelValue) : String(props.modelValue);
});

function onChange(raw: string): void {
    const cleaned = raw.replace(/[^\d.-]/g, '');
    if (cleaned === '' || cleaned === '-') {
        emit('update:modelValue', null);
        return;
    }
    let v = Number(cleaned);
    if (Number.isNaN(v)) return;
    if (props.min !== undefined && v < props.min) v = props.min;
    if (props.max !== undefined && v > props.max) v = props.max;
    emit('update:modelValue', v);
}
</script>

<template>
    <Input
        :id="id"
        type="text"
        inputmode="numeric"
        :model-value="display"
        :placeholder="placeholder"
        :disabled="disabled"
        :error="error"
        @update:model-value="onChange"
    />
</template>
