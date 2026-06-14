<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Button } from '@/components/ui/Button';

defineProps<{ status?: string }>();

const form = useForm({});

function submit(): void {
    form.post('/email/verification-notification');
}

function logout(): void {
    form.post('/logout');
}
</script>

<template>
    <Head title="Verifikasi Email" />
    <AuthLayout title="Verifikasi Email">
        <p class="text-sm text-muted-foreground">
            Sebelum melanjutkan, silakan periksa email Anda untuk tautan verifikasi.
        </p>
        <p v-if="status === 'verification-link-sent'" class="mt-3 text-sm font-medium text-success">
            Tautan verifikasi baru telah dikirim ke email Anda.
        </p>
        <div class="mt-6 flex items-center justify-between gap-2">
            <Button :loading="form.processing" @click="submit">Kirim Ulang</Button>
            <Button variant="ghost" @click="logout">Keluar</Button>
        </div>
    </AuthLayout>
</template>
