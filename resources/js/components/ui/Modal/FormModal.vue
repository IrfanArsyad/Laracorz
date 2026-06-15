<script setup lang="ts">
import Modal from './Modal.vue';
import ModalHeader from './ModalHeader.vue';
import ModalBody from './ModalBody.vue';
import ModalFooter from './ModalFooter.vue';
import Button from '../Button/Button.vue';

/**
 * Preset modal untuk form simpel.
 *
 * Pemakaian:
 *
 *   <FormModal
 *       v-model="formModal.isOpen.value"
 *       title="Tambah Modul"
 *       :processing="form.processing"
 *       @submit="onSubmit"
 *       @cancel="formModal.close()"
 *   >
 *       <FormField label="Nama"><Input v-model="form.name" /></FormField>
 *       <FormField label="Label"><Input v-model="form.label" /></FormField>
 *   </FormModal>
 */

import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = withDefaults(
    defineProps<{
        modelValue: boolean;
        title?: string;
        description?: string;
        size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl';
        submitLabel?: string;
        cancelLabel?: string;
        submitVariant?: 'default' | 'destructive' | 'success' | 'warning';
        processing?: boolean;
        disabled?: boolean;
    }>(),
    {
        size: 'md',
        submitVariant: 'default',
        processing: false,
        disabled: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [v: boolean];
    submit: [e: Event];
    cancel: [];
}>();

function onSubmit(e: Event): void {
    e.preventDefault();
    emit('submit', e);
}

function onCancel(): void {
    emit('cancel');
    emit('update:modelValue', false);
}
</script>

<template>
    <Modal
        :model-value="modelValue"
        :size="size"
        :body-padding="false"
        @update:model-value="(v) => emit('update:modelValue', v)"
    >
        <form class="flex flex-col min-h-0" @submit="onSubmit">
            <ModalHeader :title="title" :description="description" />

            <ModalBody>
                <div class="space-y-4">
                    <slot />
                </div>
            </ModalBody>

            <ModalFooter v-if="$slots.footer" align="between">
                <slot name="footer" />
            </ModalFooter>
            <ModalFooter v-else>
                <Button type="button" variant="ghost" :disabled="processing" @click="onCancel">
                    {{ cancelLabel ?? t('common.cancel') }}
                </Button>
                <Button
                    type="submit"
                    :variant="submitVariant"
                    :loading="processing"
                    :disabled="disabled"
                >
                    {{ submitLabel ?? t('common.save') }}
                </Button>
            </ModalFooter>
        </form>
    </Modal>
</template>
