<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Button } from '@/components/ui/Button';

defineProps<{ status?: string }>();

const { t } = useI18n();

const form = useForm({});

function submit(): void {
    form.post('/email/verification-notification');
}

function logout(): void {
    form.post('/logout');
}
</script>

<template>
    <Head :title="t('auth.verify')" />
    <AuthLayout :title="t('auth.verifyTitle')">
        <p class="text-sm text-muted-foreground">
            {{ t('auth.verifyBody') }}
        </p>
        <p v-if="status === 'verification-link-sent'" class="mt-3 text-sm font-medium text-success">
            {{ t('auth.verifyLinkSent') }}
        </p>
        <div class="mt-6 flex items-center justify-between gap-2">
            <Button :loading="form.processing" @click="submit">{{ t('auth.resend') }}</Button>
            <Button variant="ghost" @click="logout">{{ t('topbar.logout') }}</Button>
        </div>
    </AuthLayout>
</template>
