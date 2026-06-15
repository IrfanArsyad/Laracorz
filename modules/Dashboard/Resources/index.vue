<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    Users,
    Shield,
    Layers,
    Activity,
    ArrowUpRight,
    UserPlus,
    SquareStack,
    Settings as SettingsIcon,
    Sparkles,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import StatCard from '@/components/ui/StatCard/StatCard.vue';
import { Card } from '@/components/ui/Card';
import { Badge } from '@/components/ui/Badge';

defineProps<{
    stats?: { users: number; roles: number; modules: number; logsToday: number };
}>();

const { t } = useI18n();
const page = usePage();
const userName = (page.props.auth as { user?: { name: string } })?.user?.name ?? '';

const quickActions = computed(() => [
    { label: t('dashboard.actionAddUser'), href: '/users/create', icon: UserPlus, hint: t('dashboard.actionAddUserHint') },
    { label: t('dashboard.actionManageModules'), href: '/modules', icon: SquareStack, hint: t('dashboard.actionManageModulesHint') },
    { label: t('dashboard.actionSettings'), href: '/settings', icon: SettingsIcon, hint: t('dashboard.actionSettingsHint') },
]);
</script>

<template>
    <Head :title="t('dashboard.title')" />
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                :title="userName ? t('dashboard.helloUser', { name: userName.split(' ')[0] }) : t('dashboard.title')"
                :description="t('dashboard.description')"
            >
                <template #actions>
                    <Badge variant="outline" class="gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--status-success-solid)] animate-pulse" />
                        {{ t('dashboard.systemNormal') }}
                    </Badge>
                </template>
            </PageHeader>

            <!-- Stats grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <StatCard :label="t('dashboard.statsTotalUsers')" :value="stats?.users ?? 0" :icon="Users" />
                <StatCard :label="t('dashboard.statsTotalRoles')" :value="stats?.roles ?? 0" :icon="Shield" />
                <StatCard :label="t('dashboard.statsTotalModules')" :value="stats?.modules ?? 0" :icon="Layers" />
                <StatCard
                    :label="t('dashboard.statsTodayActivity')"
                    :value="stats?.logsToday ?? 0"
                    :icon="Activity"
                    :hint="t('dashboard.statsTodayHint')"
                />
            </div>

            <!-- Welcome banner + Quick actions -->
            <div class="grid gap-3 lg:grid-cols-3">
                <Card
                    class="lg:col-span-2 overflow-hidden relative"
                    style="background: linear-gradient(135deg, var(--brand-soft-bg), transparent 60%), var(--surface-raised);"
                >
                    <div
                        class="absolute inset-0 pointer-events-none opacity-[0.05]"
                        style="background-image: linear-gradient(to right, currentColor 1px, transparent 1px), linear-gradient(to bottom, currentColor 1px, transparent 1px); background-size: 24px 24px; color: var(--brand-soft-fg);"
                    />
                    <div class="relative p-5">
                        <div class="inline-flex items-center gap-1.5 rounded-full border border-[var(--brand-200)] bg-[var(--surface-raised)]/70 px-2 py-0.5 text-xs font-medium text-[var(--brand-soft-fg)]">
                            <Sparkles class="h-3 w-3" /> {{ t('dashboard.coreBadge') }}
                        </div>
                        <h2 class="mt-2.5 text-lg font-semibold tracking-tight text-[var(--text-strong)]">
                            {{ t('dashboard.welcomeBack') }}
                        </h2>
                        <p class="mt-1 text-sm text-[var(--text-muted)] max-w-md text-pretty">
                            {{ t('dashboard.searchHint') }}
                            <kbd class="rounded border border-[var(--border-default)] bg-[var(--surface-raised)] px-1 py-px text-xs font-mono text-[var(--text-default)]">Ctrl+K</kbd>
                            {{ t('dashboard.searchHintSuffix') }}
                        </p>
                    </div>
                </Card>

                <Card class="p-2">
                    <div class="px-3 pt-2 pb-1">
                        <p class="text-xs font-semibold text-[var(--text-muted)]">{{ t('dashboard.quickActions') }}</p>
                    </div>
                    <ul class="space-y-0.5">
                        <li v-for="a in quickActions" :key="a.href">
                            <Link
                                :href="a.href"
                                class="group flex items-center gap-3 rounded-md px-3 py-2 transition-colors hover:bg-[var(--state-hover)]"
                            >
                                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[var(--surface-sunken)] text-[var(--text-default)] group-hover:bg-[var(--brand-soft-bg)] group-hover:text-[var(--brand-soft-fg)] transition-colors">
                                    <component :is="a.icon" class="h-3.5 w-3.5" />
                                </span>
                                <span class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-[var(--text-default)]">{{ a.label }}</p>
                                    <p class="text-xs text-[var(--text-muted)] truncate">{{ a.hint }}</p>
                                </span>
                                <ArrowUpRight class="h-3.5 w-3.5 text-[var(--text-muted)] group-hover:text-[var(--text-default)] transition-colors" />
                            </Link>
                        </li>
                    </ul>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
