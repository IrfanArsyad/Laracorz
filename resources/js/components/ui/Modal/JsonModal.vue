<script setup lang="ts">
import { computed, ref } from 'vue';
import { Check, Copy } from 'lucide-vue-next';
import Modal from './Modal.vue';
import ModalHeader from './ModalHeader.vue';
import ModalBody from './ModalBody.vue';
import ModalFooter from './ModalFooter.vue';
import Button from '../Button/Button.vue';

/**
 * Preset modal untuk menampilkan data JSON / context / payload (debug, log, webhook payload).
 *
 *   <JsonModal v-model="open" title="Konteks Log" :data="row.context" />
 */

const props = withDefaults(
    defineProps<{
        modelValue: boolean;
        title?: string;
        description?: string;
        data?: unknown;
        size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
    }>(),
    { size: 'lg' },
);

const emit = defineEmits<{ 'update:modelValue': [v: boolean] }>();

const pretty = computed(() => {
    try {
        return JSON.stringify(props.data ?? {}, null, 2);
    } catch {
        return String(props.data);
    }
});

const copied = ref(false);
async function copy(): Promise<void> {
    try {
        await navigator.clipboard.writeText(pretty.value);
        copied.value = true;
        setTimeout(() => (copied.value = false), 1500);
    } catch {
        /* noop */
    }
}
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
            <pre class="rounded-md bg-[var(--surface-sunken)] border border-[var(--border-subtle)] p-3 text-xs font-mono whitespace-pre-wrap break-all overflow-x-auto">{{ pretty }}</pre>
        </ModalBody>

        <ModalFooter align="between">
            <Button variant="outline" size="sm" @click="copy">
                <Check v-if="copied" class="h-3.5 w-3.5" />
                <Copy v-else class="h-3.5 w-3.5" />
                {{ copied ? 'Tersalin' : 'Salin' }}
            </Button>
            <Button variant="ghost" @click="emit('update:modelValue', false)">Tutup</Button>
        </ModalFooter>
    </Modal>
</template>
