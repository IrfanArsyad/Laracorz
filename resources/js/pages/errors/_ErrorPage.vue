<script setup lang="ts">
import { computed, type Component } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Home, RefreshCcw } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/Button/Button.vue';

/**
 * Shared layout untuk semua halaman error (403/404/419/500/503).
 *
 *   <ErrorPage code="404" :icon="SearchX" variant="info" />
 *
 * variant menentukan warna icon circle: info | warning | danger.
 */
const props = withDefaults(
    defineProps<{
        code: string;
        icon: Component;
        title: string;
        description: string;
        message?: string | null;
        variant?: 'info' | 'warning' | 'danger';
        showReload?: boolean;
    }>(),
    { variant: 'info', showReload: false },
);

const { t } = useI18n();

const variantClass = computed(() => {
    return {
        info: 'bg-[var(--status-info-bg)] text-[var(--status-info-fg)] ring-[var(--status-info-border)]',
        warning: 'bg-[var(--status-warning-bg)] text-[var(--status-warning-fg)] ring-[var(--status-warning-border)]',
        danger: 'bg-[var(--status-danger-bg)] text-[var(--status-danger-fg)] ring-[var(--status-danger-border)]',
    }[props.variant];
});

function reload(): void {
    window.location.reload();
}
</script>

<template>
    <Head :title="title" />
    <AppLayout>
        <div class="flex min-h-[70vh] flex-col items-center justify-center text-center px-4">
            <div
                :class="
                    [
                        'mb-5 flex h-16 w-16 items-center justify-center rounded-full ring-1',
                        variantClass,
                    ]
                "
            >
                <component :is="icon" class="h-8 w-8" />
            </div>

            <p class="text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-2">
                {{ t('common.error') }} · {{ code }}
            </p>
            <h1 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                {{ title }}
            </h1>
            <p class="mt-2 max-w-md text-sm leading-relaxed text-[var(--text-muted)]">
                {{ message ?? description }}
            </p>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                <Button as="link" :href="'/'" variant="default">
                    <Home class="h-4 w-4" />
                    {{ t('errors.backDashboard') }}
                </Button>
                <Button v-if="showReload" variant="outline" @click="reload">
                    <RefreshCcw class="h-4 w-4" />
                    {{ t('errors.reload') }}
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
