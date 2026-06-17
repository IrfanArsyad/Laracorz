<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { DetailModal } from '@/components/ui/Modal';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/Tabs';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { Badge } from '@/components/ui/Badge';
import { Avatar } from '@/components/ui/Avatar';
import { USER_STATUS, ADMIN_LOG_ACTIONS } from '@/types/enums';
import type { User } from '@/types';

const props = defineProps<{
    modelValue: boolean;
    detail?: User | null;
    logs?: Array<{ id: number; action: string; description: string; created_at: string }>;
}>();

const emit = defineEmits<{ 'update:modelValue': [v: boolean] }>();

const { t } = useI18n();

const tab = ref('detail');

// Selalu mulai dari tab "detail" setiap modal dibuka.
watch(
    () => props.modelValue,
    (open) => {
        if (open) tab.value = 'detail';
    },
);

const items = computed(() => {
    const u = props.detail;
    if (!u) return [];
    return [
        { label: t('users.detailName'), value: u.name },
        { label: t('users.detailEmail'), value: u.email },
        { label: t('users.detailRole'), value: u.role?.display_name ?? '-' },
        { label: t('users.detailStatus'), value: (USER_STATUS as any)[u.status]?.label ?? u.status },
        { label: t('users.detailLastLogin'), value: u.last_login_at ?? '-' },
        { label: t('users.detailCreated'), value: u.created_at },
    ];
});
</script>

<template>
    <DetailModal
        :model-value="modelValue"
        :title="detail?.name"
        :description="detail?.email"
        size="lg"
        @update:model-value="(v) => emit('update:modelValue', v)"
    >
        <div class="flex items-center gap-4 mb-4">
            <Avatar :src="detail?.avatar_url" :name="detail?.name ?? ''" size="xl" />
            <div class="min-w-0">
                <p class="text-lg font-semibold text-[var(--text-strong)] truncate">{{ detail?.name }}</p>
                <p class="text-sm text-[var(--text-muted)] truncate">{{ detail?.email }}</p>
            </div>
        </div>

        <Tabs v-model="tab">
            <TabsList>
                <TabsTrigger value="detail">{{ t('users.tabDetail') }}</TabsTrigger>
                <TabsTrigger value="activity">{{ t('users.tabActivity') }}</TabsTrigger>
            </TabsList>
            <TabsContent value="detail" class="pt-3">
                <DescriptionList :items="items" />
            </TabsContent>
            <TabsContent value="activity" class="pt-3">
                <ul class="divide-y divide-[var(--border-subtle)]">
                    <li
                        v-for="log in logs ?? []"
                        :key="log.id"
                        class="py-2 text-sm flex items-center gap-2"
                    >
                        <Badge :variant="(ADMIN_LOG_ACTIONS as any)[log.action]?.color ?? 'muted'">
                            {{ (ADMIN_LOG_ACTIONS as any)[log.action]?.label ?? log.action }}
                        </Badge>
                        <span>{{ log.description }}</span>
                        <span class="ml-auto text-xs text-[var(--text-muted)]">{{ log.created_at }}</span>
                    </li>
                    <li v-if="!(logs?.length)" class="py-4 text-center text-sm text-[var(--text-muted)]">
                        {{ t('users.emptyActivity') }}
                    </li>
                </ul>
            </TabsContent>
        </Tabs>
    </DetailModal>
</template>
