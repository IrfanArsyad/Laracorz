<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Eye, Copy, Check } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import FilterBar from '@/components/shared/FilterBar.vue';
import { Select } from '@/components/ui/Select';
import { DataTable, type Column } from '@/components/ui/DataTable';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { Modal, ModalHeader, ModalBody, ModalFooter } from '@/components/ui/Modal';
import { useDataTable } from '@/composables/useDataTable';
import { useModal } from '@/composables/useModal';
import { useToast } from '@/composables/useToast';
import { LOG_LEVELS } from '@/types/enums';
import type { Paginated } from '@/types';

interface LogRow {
    id: number;
    created_at: string;
    level: string;
    channel: string;
    event: string | null;
    message: string;
    context: Record<string, unknown> | null;
    exception: string | null;
}

const props = defineProps<{
    data: Paginated<LogRow>;
    filters: Record<string, unknown>;
    availableDates: string[];
}>();

const { t } = useI18n();

const { state, sortBy } = useDataTable({
    initial: {
        search: (props.filters.search as string) ?? '',
        filters: {
            level: props.filters.level,
            date: props.filters.date,
        },
    },
});

const dateOptions = computed(() => {
    const today = new Date().toISOString().slice(0, 10);
    return props.availableDates.map((d) => ({
        value: d,
        label: d === today ? `${d} (today)` : d,
    }));
});

const columns = computed<Column[]>(() => [
    { key: 'created_at', label: t('logs.system.columnTime'), width: '150px' },
    { key: 'level', label: t('logs.system.columnLevel'), width: '90px' },
    { key: 'channel', label: t('logs.system.columnChannel'), width: '100px' },
    { key: 'message', label: t('logs.system.columnMessage') },
]);

const detail = useModal<LogRow | null>();
const levelOptions = Object.entries(LOG_LEVELS).map(([k, v]) => ({ label: v.label, value: k }));
const toast = useToast();

const copied = ref(false);

function shortTime(s: string): string {
    // "2026-06-14 18:26:11" → "Jun 14, 18:26"
    if (!s || s.length < 16) return s;
    const [date, time] = s.split(' ');
    const [, m, d] = date.split('-');
    const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${MONTHS[Number(m) - 1] ?? m} ${Number(d)}, ${time.slice(0, 5)}`;
}

function clipMessage(s: string, limit = 140): string {
    const first = (s ?? '').split('\n')[0];
    return first.length > limit ? first.slice(0, limit) + '…' : first;
}

async function copyDetail(row: LogRow): Promise<void> {
    const text = [
        `[${row.created_at}] ${row.channel}.${row.level.toUpperCase()}`,
        row.message,
        row.exception ? '\n' + row.exception : '',
    ].join('\n');
    try {
        await navigator.clipboard.writeText(text);
        copied.value = true;
        toast.success(t('common.copied'));
        setTimeout(() => (copied.value = false), 1500);
    } catch {
        toast.error('Copy failed');
    }
}
</script>

<template>
    <Head :title="t('logs.system.title')" />
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                :title="t('logs.system.title')"
                :description="t('logs.system.description')"
                :breadcrumbs="[{ label: t('logs.system.breadcrumbRoot') }, { label: t('logs.system.breadcrumb') }]"
            />

            <FilterBar
                v-model:search="state.search"
                :placeholder="t('logs.system.searchPlaceholder')"
                :filters-count="(state.filters.level ? 1 : 0) + (state.filters.date ? 1 : 0)"
                scope="system-log"
                :state="state"
                @reset="state.filters.level = undefined; state.filters.date = undefined; state.search = ''"
            >
                <div class="min-w-[180px]">
                    <Select
                        v-model="state.filters.date"
                        :options="dateOptions"
                        :placeholder="t('logs.system.filterAllDates')"
                        clearable
                    />
                </div>
                <div class="min-w-[160px]">
                    <Select v-model="state.filters.level" :options="levelOptions" :placeholder="t('logs.system.filterAllLevels')" clearable />
                </div>
            </FilterBar>

            <DataTable :data="data" :columns="columns" @sort="sortBy" @row-click="(r) => detail.open(r as LogRow)">
                <template #cell-created_at="{ value }">
                    <span class="text-xs text-[var(--text-muted)] tabular-nums whitespace-nowrap">{{ shortTime(value as string) }}</span>
                </template>
                <template #cell-level="{ value }">
                    <Badge :variant="((LOG_LEVELS as any)[value as string]?.color ?? 'muted')" class="text-xs uppercase">
                        {{ (LOG_LEVELS as any)[value as string]?.label ?? value }}
                    </Badge>
                </template>
                <template #cell-channel="{ value }">
                    <span class="font-mono text-xs text-[var(--text-muted)]">{{ value }}</span>
                </template>
                <template #cell-message="{ row }">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-[var(--text-default)]" :title="row.message">{{ clipMessage(row.message) }}</span>
                        <Badge v-if="row.exception" variant="warning" class="text-[10px] shrink-0">trace</Badge>
                    </div>
                </template>
                <template #actions="{ row }">
                    <Button size="icon-xs" variant="ghost" :aria-label="t('common.detail')" @click.stop="detail.open(row as LogRow)">
                        <Eye class="h-3.5 w-3.5" />
                    </Button>
                </template>
            </DataTable>
        </div>

        <Modal v-model="detail.isOpen.value" size="xl" :body-padding="false">
            <ModalHeader>
                <div v-if="detail.data.value" class="flex items-center gap-2.5 flex-wrap">
                    <Badge :variant="((LOG_LEVELS as any)[detail.data.value.level]?.color ?? 'muted')" class="uppercase">
                        {{ (LOG_LEVELS as any)[detail.data.value.level]?.label ?? detail.data.value.level }}
                    </Badge>
                    <span class="font-mono text-xs text-[var(--text-muted)]">{{ detail.data.value.channel }}</span>
                    <span class="text-xs text-[var(--text-muted)] tabular-nums">·</span>
                    <span class="text-xs text-[var(--text-muted)] tabular-nums">{{ detail.data.value.created_at }}</span>
                </div>
            </ModalHeader>
            <ModalBody>
                <div v-if="detail.data.value" class="space-y-4">
                    <!-- Message: monospace, wrap, selectable -->
                    <div>
                        <p class="text-[11px] uppercase tracking-wider text-[var(--text-muted)] mb-1.5">{{ t('logs.system.fieldMessage') }}</p>
                        <p class="text-sm leading-relaxed text-[var(--text-default)] whitespace-pre-wrap break-words font-mono bg-[var(--surface-sunken)] border border-[var(--border-subtle)] rounded-md p-3">{{ detail.data.value.message }}</p>
                    </div>

                    <!-- Stack trace collapsible -->
                    <details v-if="detail.data.value.exception" class="group" open>
                        <summary class="cursor-pointer text-[11px] uppercase tracking-wider text-[var(--text-muted)] mb-1.5 hover:text-[var(--text-default)] select-none">
                            <span class="group-open:hidden">▸</span><span class="hidden group-open:inline">▾</span>
                            {{ t('logs.system.fieldTrace') }}
                        </summary>
                        <pre class="mt-1.5 bg-[var(--surface-sunken)] border border-[var(--border-subtle)] rounded-md p-3 text-[11px] font-mono whitespace-pre overflow-auto max-h-80 text-[var(--text-muted)]">{{ detail.data.value.exception }}</pre>
                    </details>
                </div>
            </ModalBody>
            <ModalFooter>
                <Button variant="ghost" @click="copyDetail(detail.data.value!)" :disabled="!detail.data.value">
                    <Check v-if="copied" class="h-3.5 w-3.5" />
                    <Copy v-else class="h-3.5 w-3.5" />
                    {{ copied ? t('common.copied') : t('common.copy') }}
                </Button>
                <Button @click="detail.close()">{{ t('common.close') }}</Button>
            </ModalFooter>
        </Modal>
    </AppLayout>
</template>
