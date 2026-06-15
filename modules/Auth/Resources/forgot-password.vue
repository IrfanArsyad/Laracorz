<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { Button } from '@/components/ui/Button';

defineProps<{ status?: string }>();

const { t } = useI18n();

const form = useForm({ email: '' });

function submit(): void {
    form.post('/forgot-password');
}
</script>

<template>
    <Head :title="t('auth.forgot')" />
    <AuthLayout :title="t('auth.resetTitle')">
        <p class="mb-4 text-sm text-muted-foreground">
            {{ t('auth.forgotDescription') }}
        </p>
        <p v-if="status" class="mb-4 text-sm font-medium text-success">{{ status }}</p>

        <form class="space-y-4" @submit.prevent="submit">
            <FormField :label="t('auth.email')" :error="form.errors.email" required>
                <Input v-model="form.email" type="email" required autocomplete="username" />
            </FormField>
            <Button type="submit" :loading="form.processing" class="w-full">{{ t('auth.sendResetLink') }}</Button>
            <p class="text-center text-sm">
                <a href="/login" class="text-primary hover:underline">{{ t('auth.backToLogin') }}</a>
            </p>
        </form>
    </AuthLayout>
</template>
