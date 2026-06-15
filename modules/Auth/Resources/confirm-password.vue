<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { FormField } from '@/components/ui/FormField';
import { InputPassword } from '@/components/ui/InputPassword';
import { Button } from '@/components/ui/Button';

const { t } = useI18n();

const form = useForm({ password: '' });

function submit(): void {
    form.post('/confirm-password', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head :title="t('auth.confirmPassword')" />
    <AuthLayout :title="t('auth.confirmPasswordTitle')">
        <p class="mb-4 text-sm text-muted-foreground">
            {{ t('auth.confirmPasswordDescription') }}
        </p>
        <form class="space-y-4" @submit.prevent="submit">
            <FormField :label="t('auth.password')" :error="form.errors.password" required>
                <InputPassword v-model="form.password" autocomplete="current-password" />
            </FormField>
            <Button type="submit" :loading="form.processing" class="w-full">{{ t('common.confirm') }}</Button>
        </form>
    </AuthLayout>
</template>
