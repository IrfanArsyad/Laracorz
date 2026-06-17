<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { FormModal } from '@/components/ui/Modal';
import UserForm from './UserForm.vue';
import type { User } from '@/types';

const props = defineProps<{
    modelValue: boolean;
    user: User | null;
    roles: Array<{ id: number; name: string; display_name: string }>;
}>();

const emit = defineEmits<{ 'update:modelValue': [v: boolean] }>();

const { t } = useI18n();

const form = useForm<{
    _method: string;
    role_id: number | null;
    name: string;
    username: string;
    email: string;
    password: string;
    password_confirmation: string;
    status: 'active' | 'inactive' | 'banned';
    avatar: File | null;
}>({
    _method: 'put',
    role_id: null,
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    status: 'active',
    avatar: null,
});

// Isi form dari user yang dipilih setiap modal dibuka.
watch(
    () => props.modelValue,
    (open) => {
        if (open && props.user) {
            form.clearErrors();
            form.defaults({
                _method: 'put',
                role_id: props.user.role_id ?? null,
                name: props.user.name,
                username: props.user.username ?? '',
                email: props.user.email,
                password: '',
                password_confirmation: '',
                status: props.user.status,
                avatar: null,
            });
            form.reset();
        }
    },
);

function submit(): void {
    if (!props.user) return;
    form.post(`/users/${props.user.id}`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => emit('update:modelValue', false),
    });
}
</script>

<template>
    <FormModal
        :model-value="modelValue"
        :title="t('users.modalEditTitle', { name: user?.name ?? '' })"
        :description="t('users.modalEditDesc')"
        size="lg"
        :processing="form.processing"
        @update:model-value="(v) => emit('update:modelValue', v)"
        @submit="submit"
        @cancel="emit('update:modelValue', false)"
    >
        <UserForm :form="form" :roles="roles" :is-edit="true" :avatar-url="user?.avatar_url" />
    </FormModal>
</template>
