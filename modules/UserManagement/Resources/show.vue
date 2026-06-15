<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Card, CardContent } from '@/components/ui/Card';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/Tabs';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { Badge } from '@/components/ui/Badge';
import { Avatar } from '@/components/ui/Avatar';
import { USER_STATUS, ADMIN_LOG_ACTIONS } from '@/types/enums';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        status: string;
        avatar_url: string | null;
        role: { display_name: string } | null;
        last_login_at: string | null;
        created_at: string;
    };
    logs: Array<{ id: number; action: string; description: string; created_at: string }>;
}>();

const { t } = useI18n();
const tab = ref('detail');

const items = computed(() => [
    { label: t('users.detailName'), value: props.user.name },
    { label: t('users.detailEmail'), value: props.user.email },
    { label: t('users.detailRole'), value: props.user.role?.display_name ?? '-' },
    {
        label: t('users.detailStatus'),
        value:
            (USER_STATUS as never)[props.user.status as never]?.label ?? props.user.status,
    },
    { label: t('users.detailLastLogin'), value: props.user.last_login_at ?? '-' },
    { label: t('users.detailCreated'), value: props.user.created_at },
]);
</script>

<template>
    <Head :title="t('users.pageTitle', { name: user.name })" />
    <AppLayout>
        <PageHeader
            :title="user.name"
            :breadcrumbs="[{ label: t('users.breadcrumb'), href: '/users' }, { label: user.name }]"
        />

        <Card class="mt-6">
            <CardContent>
                <div class="flex items-center gap-4 mb-4">
                    <Avatar :src="user.avatar_url" :name="user.name" size="xl" />
                    <div>
                        <h2 class="text-xl font-semibold">{{ user.name }}</h2>
                        <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                    </div>
                </div>

                <Tabs v-model="tab">
                    <TabsList>
                        <TabsTrigger value="detail">{{ t('users.tabDetail') }}</TabsTrigger>
                        <TabsTrigger value="activity">{{ t('users.tabActivity') }}</TabsTrigger>
                    </TabsList>
                    <TabsContent value="detail">
                        <DescriptionList :items="items" />
                    </TabsContent>
                    <TabsContent value="activity">
                        <ul class="divide-y divide-border">
                            <li v-for="log in logs" :key="log.id" class="py-2 text-sm flex items-center gap-2">
                                <Badge :variant="(ADMIN_LOG_ACTIONS as never)[log.action]?.color ?? 'muted'">
                                    {{ (ADMIN_LOG_ACTIONS as never)[log.action]?.label ?? log.action }}
                                </Badge>
                                <span>{{ log.description }}</span>
                                <span class="ml-auto text-xs text-muted-foreground">{{ log.created_at }}</span>
                            </li>
                            <li v-if="logs.length === 0" class="py-4 text-center text-sm text-muted-foreground">
                                {{ t('users.emptyActivity') }}
                            </li>
                        </ul>
                    </TabsContent>
                </Tabs>
            </CardContent>
        </Card>
    </AppLayout>
</template>
