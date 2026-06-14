<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { FormField } from '@/components/ui/FormField';
import { InputPassword } from '@/components/ui/InputPassword';
import { Button } from '@/components/ui/Button';

const form = useForm({ password: '' });

function submit(): void {
    form.post('/confirm-password', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head title="Konfirmasi Kata Sandi" />
    <AuthLayout title="Konfirmasi Kata Sandi">
        <p class="mb-4 text-sm text-muted-foreground">
            Konfirmasi kata sandi Anda sebelum melanjutkan.
        </p>
        <form class="space-y-4" @submit.prevent="submit">
            <FormField label="Kata Sandi" :error="form.errors.password" required>
                <InputPassword v-model="form.password" autocomplete="current-password" />
            </FormField>
            <Button type="submit" :loading="form.processing" class="w-full">Konfirmasi</Button>
        </form>
    </AuthLayout>
</template>
