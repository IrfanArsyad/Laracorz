<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import FilterBar from '@/components/shared/FilterBar.vue';
import { Select } from '@/components/ui/Select';
import { FormField } from '@/components/ui/FormField';
import { DataTable, type Column } from '@/components/ui/DataTable';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { Modal, ModalHeader, ModalBody, ModalFooter } from '@/components/ui/Modal';
import { useDataTable } from '@/composables/useDataTable';
import { useModal } from '@/composables/useModal';
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

const props = defineProps<{ data: Paginated<LogRow>; filters: Record<string, unknown> }>();

const { state, sortBy } = useDataTable({
    initial: {
        search: (props.filters.search as string) ?? '',
        filters: { level: props.filters.level, channel: props.filters.channel },
    },
});

const columns: Column[] = [
    { key: 'created_at', label: 'Waktu', sortable: true, width: '180px' },
    { key: 'level', label: 'Level' },
    { key: 'channel', label: 'Channel' },
    { key: 'event', label: 'Event' },
    { key: 'message', label: 'Pesan' },
];

const detail = useModal<LogRow | null>();
const levelOptions = Object.entries(LOG_LEVELS).map(([k, v]) => ({ label: v.label, value: k }));
</script>

<template>
    <Head title="System Log" />
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="System Log"
                description="Catatan kejadian teknis sistem."
                :breadcrumbs="[{ label: 'System' }, { label: 'System Log' }]"
            />

            <FilterBar
                v-model:search="state.search"
                placeholder="Cari pesan/event..."
                :filters-count="state.filters.level ? 1 : 0"
                @reset="state.filters.level = undefined; state.search = ''"
            >
                <FormField label="Level">
                    <Select v-model="state.filters.level" :options="levelOptions" placeholder="Semua level" clearable />
                </FormField>
            </FilterBar>

            <DataTable :data="data" :columns="columns" @sort="sortBy">
                <template #cell-level="{ value }">
                    <Badge :variant="((LOG_LEVELS as any)[value as string]?.color ?? 'muted')">
                        {{ (LOG_LEVELS as any)[value as string]?.label ?? value }}
                    </Badge>
                </template>
                <template #actions="{ row }">
                    <Button size="icon-xs" variant="ghost" aria-label="Detail" @click="detail.open(row)">
                        <Eye class="h-3.5 w-3.5" />
                    </Button>
                </template>
            </DataTable>
        </div>

        <Modal v-model="detail.isOpen.value" size="xl" :body-padding="false">
            <ModalHeader
                :title="`Log #${detail.data.value?.id ?? ''}`"
                :description="detail.data.value?.event ?? undefined"
            />
            <ModalBody>
                <div v-if="detail.data.value" class="space-y-4 text-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-[var(--text-muted)]">Waktu</p>
                            <p class="mt-0.5">{{ detail.data.value.created_at }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-[var(--text-muted)]">Channel</p>
                            <p class="mt-0.5 font-mono text-xs">{{ detail.data.value.channel }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-[var(--text-muted)] mb-1">Pesan</p>
                        <p>{{ detail.data.value.message }}</p>
                    </div>
                    <div v-if="detail.data.value.context">
                        <p class="text-xs uppercase tracking-wider text-[var(--text-muted)] mb-1">Context</p>
                        <pre class="bg-[var(--surface-sunken)] border border-[var(--border-subtle)] rounded-md p-3 text-xs font-mono whitespace-pre-wrap overflow-auto max-h-64">{{ JSON.stringify(detail.data.value.context, null, 2) }}</pre>
                    </div>
                    <div v-if="detail.data.value.exception">
                        <p class="text-xs uppercase tracking-wider text-[var(--text-muted)] mb-1">Trace</p>
                        <pre class="bg-[var(--surface-sunken)] border border-[var(--border-subtle)] rounded-md p-3 text-xs font-mono whitespace-pre-wrap overflow-auto max-h-64">{{ detail.data.value.exception }}</pre>
                    </div>
                </div>
            </ModalBody>
            <ModalFooter>
                <Button variant="ghost" @click="detail.close()">Tutup</Button>
            </ModalFooter>
        </Modal>
    </AppLayout>
</template>
