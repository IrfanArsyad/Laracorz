<script setup lang="ts">
import { computed, provide } from 'vue';
import { AlertCircle } from 'lucide-vue-next';
import { uniqueId } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        label?: string;
        hint?: string;
        error?: string | string[];
        required?: boolean;
        id?: string;
    }>(),
    { required: false },
);

const fieldId = computed(() => props.id || uniqueId('field'));
const errorText = computed(() => (Array.isArray(props.error) ? props.error[0] : props.error));

provide('formFieldId', fieldId);
provide('formFieldError', errorText);
</script>

<template>
    <div class="space-y-1.5">
        <label
            v-if="label"
            :for="fieldId"
            class="inline-flex items-center gap-1 text-sm font-medium leading-none text-[var(--text-strong)]"
        >
            {{ label }}
            <span v-if="required" class="text-[var(--status-danger-fg)]" aria-hidden="true">*</span>
        </label>
        <slot :id="fieldId" :error="!!errorText" />
        <p v-if="hint && !errorText" class="text-xs text-[var(--text-muted)]">{{ hint }}</p>
        <p
            v-if="errorText"
            class="inline-flex items-start gap-1 text-xs text-[var(--status-danger-fg)]"
            role="alert"
        >
            <AlertCircle class="h-3.5 w-3.5 shrink-0 mt-px" />
            <span>{{ errorText }}</span>
        </p>
    </div>
</template>
