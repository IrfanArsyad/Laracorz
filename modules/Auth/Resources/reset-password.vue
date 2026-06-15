<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { InputPassword } from '@/components/ui/InputPassword';
import { Button } from '@/components/ui/Button';

const props = defineProps<{ token: string; email: string }>();

const { t } = useI18n();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit(): void {
    form.post('/reset-password', { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>

<template>
    <Head :title="t('auth.reset')" />
    <AuthLayout :title="t('auth.resetTitle')">
        <form class="space-y-4" @submit.prevent="submit">
            <FormField :label="t('auth.email')" :error="form.errors.email" required>
                <Input v-model="form.email" type="email" required />
            </FormField>
            <FormField :label="t('auth.newPassword')" :error="form.errors.password" required>
                <InputPassword v-model="form.password" autocomplete="new-password" />
            </FormField>
            <FormField :label="t('auth.passwordConfirmation')" required>
                <InputPassword v-model="form.password_confirmation" autocomplete="new-password" />
            </FormField>
            <Button type="submit" :loading="form.processing" class="w-full">{{ t('auth.reset') }}</Button>
        </form>
    </AuthLayout>
</template>
