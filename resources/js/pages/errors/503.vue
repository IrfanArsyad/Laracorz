<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Wrench } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import AuthLayout from '@/layouts/AuthLayout.vue';

defineProps<{ status?: number; message?: string | null }>();

const { t } = useI18n();
</script>

<template>
    <Head :title="t('errors.503.title')" />
    <AuthLayout>
        <div class="text-center">
            <Wrench class="h-12 w-12 text-warning mx-auto mb-4" />
            <h1 class="text-3xl font-bold">503</h1>
            <p class="text-muted-foreground mt-2">
                {{ message ?? t('errors.503.description') }}
            </p>
        </div>
    </AuthLayout>
</template>
