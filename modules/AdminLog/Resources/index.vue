<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';
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
import { ADMIN_LOG_ACTIONS } from '@/types/enums';
import type { Paginated } from '@/types';

interface LogRow {
    id: number;
    created_at: string;
    user_name: string | null;
    module: string | null;
    action: string;
    description: string;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    ip_address: string | null;
    user_agent: string | null;
    url: string | null;
}

const props = defineProps<{ data: Paginated<LogRow>; filters: Record<string, unknown> }>();

const { state, sortBy } = useDataTable({
    initial: {
        search: (props.filters.search as string) ?? '',
        filters: {
            action: props.filters.action,
            module: props.filters.module,
            from: props.filters.from,
            to: props.filters.to,
        },
    },
});

const columns: Column[] = [
    { key: 'created_at', label: 'Waktu', sortable: true, width: '180px' },
    { key: 'user_name', label: 'User' },
    { key: 'module', label: 'Modul' },
    { key: 'action', label: 'Aksi' },
    { key: 'description', label: 'Deskripsi' },
];

const detail = useModal<LogRow | null>();
const actionOptions = Object.entries(ADMIN_LOG_ACTIONS).map(([k, v]) => ({ label: v.label, value: k }));
</script>

<template>
    <Head title="Admin Log" />
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Admin Log"
                description="Jejak aktivitas administrator."
                :breadcrumbs="[{ label: 'System' }, { label: 'Admin Log' }]"
            />

            <FilterBar v-model:search="state.search" placeholder="Cari deskripsi/user/module...">
                <Select v-model="state.filters.action" :options="actionOptions" placeholder="Aksi" clearable class="w-40" />
            </FilterBar>

            <DataTable :data="data" :columns="columns" @sort="sortBy">
                <template #cell-action="{ value }">
                    <Badge :variant="((ADMIN_LOG_ACTIONS as any)[value as string]?.color ?? 'muted')">
                        {{ (ADMIN_LOG_ACTIONS as any)[value as string]?.label ?? value }}
                    </Badge>
                </template>
                <template #cell-module="{ value }">
                    <Badge variant="muted" class="font-mono">{{ value ?? '-' }}</Badge>
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
                :description="detail.data.value?.description"
            />
            <ModalBody>
                <div v-if="detail.data.value" class="space-y-4 text-sm">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-[var(--text-muted)]">Waktu</p>
                            <p class="mt-0.5">{{ detail.data.value.created_at }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-[var(--text-muted)]">User</p>
                            <p class="mt-0.5">{{ detail.data.value.user_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-[var(--text-muted)]">IP</p>
                            <p class="mt-0.5 font-mono text-xs">{{ detail.data.value.ip_address ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-[var(--text-muted)]">Aksi</p>
                            <Badge :variant="((ADMIN_LOG_ACTIONS as any)[detail.data.value.action]?.color ?? 'muted')">
                                {{ (ADMIN_LOG_ACTIONS as any)[detail.data.value.action]?.label ?? detail.data.value.action }}
                            </Badge>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-[var(--text-muted)] mb-1">Sebelum</p>
                            <pre class="bg-[var(--surface-sunken)] border border-[var(--border-subtle)] rounded-md p-3 text-xs font-mono whitespace-pre-wrap overflow-auto max-h-64">{{ JSON.stringify(detail.data.value.old_values ?? {}, null, 2) }}</pre>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-[var(--text-muted)] mb-1">Sesudah</p>
                            <pre class="bg-[var(--surface-sunken)] border border-[var(--border-subtle)] rounded-md p-3 text-xs font-mono whitespace-pre-wrap overflow-auto max-h-64">{{ JSON.stringify(detail.data.value.new_values ?? {}, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </ModalBody>
            <ModalFooter>
                <Button variant="ghost" @click="detail.close()">Tutup</Button>
            </ModalFooter>
        </Modal>
    </AppLayout>
</template>
