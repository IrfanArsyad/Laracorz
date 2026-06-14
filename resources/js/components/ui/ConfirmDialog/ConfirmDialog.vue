<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';
import Modal from '../Modal/Modal.vue';
import Button from '../Button/Button.vue';

const { state, accept, cancel } = useConfirm();
</script>

<template>
    <Modal
        :model-value="state.open"
        size="sm"
        :title="state.title"
        @update:model-value="(v) => !v && cancel()"
    >
        <p class="text-sm text-muted-foreground">{{ state.message }}</p>

        <template #footer>
            <Button variant="outline" type="button" @click="cancel">{{ state.cancelLabel }}</Button>
            <Button
                :variant="state.variant === 'destructive' ? 'destructive' : 'default'"
                type="button"
                @click="accept"
            >
                {{ state.confirmLabel }}
            </Button>
        </template>
    </Modal>
</template>
