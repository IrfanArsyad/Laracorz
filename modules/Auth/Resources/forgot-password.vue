<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { Button } from '@/components/ui/Button';

defineProps<{ status?: string }>();

const form = useForm({ email: '' });

function submit(): void {
    form.post('/forgot-password');
}
</script>

<template>
    <Head title="Lupa Kata Sandi" />
    <AuthLayout title="Lupa Kata Sandi">
        <p class="mb-4 text-sm text-muted-foreground">
            Masukkan email Anda untuk menerima tautan reset kata sandi.
        </p>
        <p v-if="status" class="mb-4 text-sm font-medium text-success">{{ status }}</p>

        <form class="space-y-4" @submit.prevent="submit">
            <FormField label="Email" :error="form.errors.email" required>
                <Input v-model="form.email" type="email" required autocomplete="username" />
            </FormField>
            <Button type="submit" :loading="form.processing" class="w-full">Kirim Tautan Reset</Button>
            <p class="text-center text-sm">
                <a href="/login" class="text-primary hover:underline">Kembali ke login</a>
            </p>
        </form>
    </AuthLayout>
</template>
