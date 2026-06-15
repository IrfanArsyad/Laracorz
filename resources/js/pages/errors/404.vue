<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { SearchX } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/Button/Button.vue';

defineProps<{ status?: number; message?: string | null }>();

const { t } = useI18n();
</script>

<template>
    <Head :title="t('errors.404.title')" />
    <AppLayout>
        <div class="flex min-h-[60vh] flex-col items-center justify-center text-center">
            <SearchX class="h-12 w-12 text-muted-foreground mb-4" />
            <h1 class="text-3xl font-bold">404</h1>
            <p class="text-muted-foreground mt-2 max-w-md">{{ message ?? t('errors.404.description') }}</p>
            <Button as="link" href="/" class="mt-6">{{ t('errors.backDashboard') }}</Button>
        </div>
    </AppLayout>
</template>
