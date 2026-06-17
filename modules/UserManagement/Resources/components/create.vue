<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { FormModal } from '@/components/ui/Modal';
import UserForm from './UserForm.vue';

const props = defineProps<{
    modelValue: boolean;
    roles: Array<{ id: number; name: string; display_name: string }>;
}>();

const emit = defineEmits<{ 'update:modelValue': [v: boolean] }>();

const { t } = useI18n();

const blankForm = {
    role_id: null as number | null,
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    status: 'active' as 'active' | 'inactive' | 'banned',
    avatar: null as File | null,
};

const form = useForm({ ...blankForm });

// Reset tiap kali modal dibuka agar tidak membawa sisa input/error sebelumnya.
watch(
    () => props.modelValue,
    (open) => {
        if (open) {
            form.reset();
            form.clearErrors();
        }
    },
);

function submit(): void {
    form.post('/users', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => emit('update:modelValue', false),
    });
}
</script>

<template>
    <FormModal
        :model-value="modelValue"
        :title="t('users.modalCreateTitle')"
        :description="t('users.modalCreateDesc')"
        size="lg"
        :processing="form.processing"
        @update:model-value="(v) => emit('update:modelValue', v)"
        @submit="submit"
        @cancel="emit('update:modelValue', false)"
    >
        <UserForm :form="form" :roles="roles" :is-edit="false" />
    </FormModal>
</template>
