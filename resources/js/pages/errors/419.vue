<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { RefreshCcw } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/Button/Button.vue';

defineProps<{ status?: number; message?: string | null }>();
</script>

<template>
    <Head title="Sesi Kedaluwarsa" />
    <AppLayout>
        <div class="flex min-h-[60vh] flex-col items-center justify-center text-center">
            <RefreshCcw class="h-12 w-12 text-warning mb-4" />
            <h1 class="text-3xl font-bold">419</h1>
            <p class="text-muted-foreground mt-2 max-w-md">
                Sesi Anda telah kedaluwarsa. Silakan muat ulang halaman.
            </p>
            <Button class="mt-6" @click="$inertia.reload()">Muat Ulang</Button>
        </div>
    </AppLayout>
</template>
