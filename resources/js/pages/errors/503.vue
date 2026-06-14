<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Wrench } from 'lucide-vue-next';
import AuthLayout from '@/layouts/AuthLayout.vue';

defineProps<{ status?: number; message?: string | null }>();
</script>

<template>
    <Head title="Sedang Pemeliharaan" />
    <AuthLayout>
        <div class="text-center">
            <Wrench class="h-12 w-12 text-warning mx-auto mb-4" />
            <h1 class="text-3xl font-bold">503</h1>
            <p class="text-muted-foreground mt-2">
                {{ message ?? 'Sistem sedang dalam pemeliharaan. Silakan kembali sebentar lagi.' }}
            </p>
        </div>
    </AuthLayout>
</template>
