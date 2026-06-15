<script setup lang="ts">
import { Lock } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import ErrorPage from './_ErrorPage.vue';

defineProps<{ status?: number; message?: string | null }>();
const { t } = useI18n();
</script>

<template>
    <ErrorPage
        code="403"
        :icon="Lock"
        variant="danger"
        :title="t('errors.403.title')"
        :description="t('errors.403.description')"
        :message="message"
    />
</template>
