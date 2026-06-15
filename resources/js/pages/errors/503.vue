<script setup lang="ts">
import { Wrench } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import ErrorPage from './_ErrorPage.vue';

defineProps<{ status?: number; message?: string | null }>();
const { t } = useI18n();
</script>

<template>
    <ErrorPage
        code="503"
        :icon="Wrench"
        variant="warning"
        :title="t('errors.503.title')"
        :description="t('errors.503.description')"
        :message="message"
        show-reload
    />
</template>
