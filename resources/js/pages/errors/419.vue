<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { RefreshCcw } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/Button/Button.vue';

defineProps<{ status?: number; message?: string | null }>();

const { t } = useI18n();
</script>

<template>
    <Head :title="t('errors.419.title')" />
    <AppLayout>
        <div class="flex min-h-[60vh] flex-col items-center justify-center text-center">
            <RefreshCcw class="h-12 w-12 text-warning mb-4" />
            <h1 class="text-3xl font-bold">419</h1>
            <p class="text-muted-foreground mt-2 max-w-md">
                {{ t('errors.419.description') }}
            </p>
            <Button class="mt-6" @click="$inertia.reload()">{{ t('errors.reload') }}</Button>
        </div>
    </AppLayout>
</template>
