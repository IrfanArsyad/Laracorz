<script setup lang="ts">
import { SearchX } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import ErrorPage from './_ErrorPage.vue';

defineProps<{ status?: number; message?: string | null }>();
const { t } = useI18n();
</script>

<template>
    <ErrorPage
        code="404"
        :icon="SearchX"
        variant="info"
        :title="t('errors.404.title')"
        :description="t('errors.404.description')"
        :message="message"
    />
</template>
