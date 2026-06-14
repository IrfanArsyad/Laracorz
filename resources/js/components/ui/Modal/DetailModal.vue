<script setup lang="ts">
import Modal from './Modal.vue';
import ModalHeader from './ModalHeader.vue';
import ModalBody from './ModalBody.vue';
import ModalFooter from './ModalFooter.vue';
import Button from '../Button/Button.vue';
import DescriptionList from '../DescriptionList/DescriptionList.vue';

/**
 * Preset modal untuk menampilkan data read-only (detail row, log entry, dsb).
 *
 * Pemakaian sederhana (auto-render DescriptionList):
 *
 *   <DetailModal v-model="detail.isOpen.value" title="Detail Pengguna" :items="[
 *       { label: 'Nama', value: row.name },
 *       { label: 'Email', value: row.email },
 *   ]" />
 *
 * Atau pakai default slot untuk konten custom:
 *
 *   <DetailModal v-model="open" title="Konteks Log">
 *       <pre>{{ ... }}</pre>
 *   </DetailModal>
 */

interface Item {
    label: string;
    value?: string | number | null;
}

withDefaults(
    defineProps<{
        modelValue: boolean;
        title?: string;
        description?: string;
        items?: Item[];
        size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl';
        closeLabel?: string;
    }>(),
    { size: 'md', closeLabel: 'Tutup' },
);

const emit = defineEmits<{ 'update:modelValue': [v: boolean] }>();
</script>

<template>
    <Modal
        :model-value="modelValue"
        :size="size"
        :body-padding="false"
        @update:model-value="(v) => emit('update:modelValue', v)"
    >
        <ModalHeader :title="title" :description="description" />

        <ModalBody>
            <DescriptionList v-if="items?.length" :items="items" />
            <slot />
        </ModalBody>

        <ModalFooter v-if="$slots.actions">
            <slot name="actions" />
        </ModalFooter>
        <ModalFooter v-else>
            <Button variant="ghost" @click="emit('update:modelValue', false)">{{ closeLabel }}</Button>
        </ModalFooter>
    </Modal>
</template>
