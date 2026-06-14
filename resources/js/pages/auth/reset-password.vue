<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { InputPassword } from '@/components/ui/InputPassword';
import { Button } from '@/components/ui/Button';

const props = defineProps<{ token: string; email: string }>();

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
    <Head title="Reset Kata Sandi" />
    <AuthLayout title="Reset Kata Sandi">
        <form class="space-y-4" @submit.prevent="submit">
            <FormField label="Email" :error="form.errors.email" required>
                <Input v-model="form.email" type="email" required />
            </FormField>
            <FormField label="Kata Sandi Baru" :error="form.errors.password" required>
                <InputPassword v-model="form.password" autocomplete="new-password" />
            </FormField>
            <FormField label="Konfirmasi Kata Sandi" required>
                <InputPassword v-model="form.password_confirmation" autocomplete="new-password" />
            </FormField>
            <Button type="submit" :loading="form.processing" class="w-full">Reset Kata Sandi</Button>
        </form>
    </AuthLayout>
</template>
