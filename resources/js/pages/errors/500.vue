<script setup lang="ts">
import { ServerCrash } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import ErrorPage from './_ErrorPage.vue';

defineProps<{ status?: number; message?: string | null }>();
const { t } = useI18n();
</script>

<template>
    <ErrorPage
        code="500"
        :icon="ServerCrash"
        variant="danger"
        :title="t('errors.500.title')"
        :description="t('errors.500.description')"
        :message="message"
        show-reload
    />
</template>
