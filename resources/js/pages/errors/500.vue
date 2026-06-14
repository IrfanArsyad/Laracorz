<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ServerCrash } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/Button/Button.vue';

defineProps<{ status?: number; message?: string | null }>();
</script>

<template>
    <Head title="Kesalahan Server" />
    <AppLayout>
        <div class="flex min-h-[60vh] flex-col items-center justify-center text-center">
            <ServerCrash class="h-12 w-12 text-destructive mb-4" />
            <h1 class="text-3xl font-bold">500</h1>
            <p class="text-muted-foreground mt-2 max-w-md">
                {{ message ?? 'Terjadi kesalahan pada server. Tim kami sudah diberi tahu.' }}
            </p>
            <Button as="link" href="/" class="mt-6">Kembali ke Dashboard</Button>
        </div>
    </AppLayout>
</template>
