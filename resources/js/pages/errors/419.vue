<script setup lang="ts">
import { Clock } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import ErrorPage from './_ErrorPage.vue';

defineProps<{ status?: number; message?: string | null }>();
const { t } = useI18n();
</script>

<template>
    <ErrorPage
        code="419"
        :icon="Clock"
        variant="warning"
        :title="t('errors.419.title')"
        :description="t('errors.419.description')"
        :message="message"
        show-reload
    />
</template>
