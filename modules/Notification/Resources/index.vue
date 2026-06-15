<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { EmptyState } from '@/components/ui/EmptyState';
import { Pagination } from '@/components/ui/Pagination';
import type { Paginated } from '@/types';

interface NotifRow {
    id: string;
    data: { title: string; message: string; icon?: string; url?: string };
    read_at: string | null;
    created_at: string;
}

defineProps<{ data: Paginated<NotifRow> }>();

const { t } = useI18n();

function markRead(id: string): void {
    router.patch(`/notifications/${id}/read`, {}, { preserveScroll: true });
}

function markAllRead(): void {
    router.post('/notifications/mark-all-read', {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('notifications.title')" />
    <AppLayout>
        <PageHeader :title="t('notifications.title')" :description="t('notifications.description')">
            <template #actions>
                <Button variant="outline" size="sm" @click="markAllRead">
                    <Check class="h-3.5 w-3.5" /> {{ t('notifications.markAllRead') }}
                </Button>
            </template>
        </PageHeader>

        <Card class="mt-6">
            <CardContent>
                <ul v-if="data.data.length > 0" class="divide-y divide-border">
                    <li
                        v-for="n in data.data"
                        :key="n.id"
                        :class="['flex items-start gap-3 py-3', !n.read_at ? 'bg-accent/20' : '']"
                    >
                        <div class="flex-1">
                            <p class="text-sm font-medium">{{ n.data.title }}</p>
                            <p class="text-xs text-muted-foreground">{{ n.data.message }}</p>
                            <p class="text-xs text-muted-foreground mt-1">{{ n.created_at }}</p>
                        </div>
                        <Button v-if="!n.read_at" variant="ghost" size="sm" @click="markRead(n.id)">
                            <Check class="h-3.5 w-3.5" />
                        </Button>
                    </li>
                </ul>
                <EmptyState v-else :title="t('notifications.emptyTitle')" />
                <Pagination v-if="data.data.length > 0" :meta="data.meta" class="mt-4" />
            </CardContent>
        </Card>
    </AppLayout>
</template>
