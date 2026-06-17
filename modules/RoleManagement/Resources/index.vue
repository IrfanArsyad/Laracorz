<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, ShieldCheck } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import FilterBar from '@/components/shared/FilterBar.vue';
import { Button } from '@/components/ui/Button';
import { Badge } from '@/components/ui/Badge';
import { DataTable, type Column } from '@/components/ui/DataTable';
import RoleModal from './components/role-modal.vue';
import { useDataTable } from '@/composables/useDataTable';
import { useConfirm } from '@/composables/useConfirm';
import { usePermission } from '@/composables/usePermission';
import type { Paginated } from '@/types';

interface RoleRow {
    id: number;
    name: string;
    display_name: string;
    description: string | null;
    is_active: boolean;
    users_count: number;
    read: Array<number | string> | null;
    create: Array<number | string> | null;
    update: Array<number | string> | null;
    delete: Array<number | string> | null;
    extra: Record<string, string[]> | null;
}

interface MatrixGroup {
    id: number;
    name: string;
    label: string;
    modules: Array<{
        id: number;
        name: string;
        label: string;
        is_leaf: boolean;
        extra_actions: string[];
        children: unknown[];
    }>;
}

const props = defineProps<{
    data: Paginated<RoleRow>;
    matrix?: MatrixGroup[];
    filters: { search: string | null; sort: string | null; direction: 'asc' | 'desc' };
}>();

const { t } = useI18n();

const { state, sortBy } = useDataTable({
    initial: {
        search: props.filters.search ?? '',
        sort: props.filters.sort,
        direction: props.filters.direction,
    },
    only: ['data'],
});

const { can } = usePermission();
const { confirm } = useConfirm();

const columns = computed<Column[]>(() => [
    { key: 'name', label: t('common.name'), sortable: true },
    { key: 'display_name', label: t('roles.columnDisplayName'), sortable: true },
    { key: 'users_count', label: t('roles.usersCountColumn'), align: 'right' },
    { key: 'is_active', label: t('common.status'), align: 'center' },
]);

// ─── Modal Create/Edit ─────────────────────────────────────────────────
// Form & submit dienkapsulasi di components/role-modal.vue; index hanya
// mengatur buka-tutup + role yang sedang diedit (null = create).
const roleModalOpen = ref(false);
const editingRole = ref<RoleRow | null>(null);

function openCreate(): void {
    editingRole.value = null;
    roleModalOpen.value = true;
}

function openEdit(row: RoleRow): void {
    editingRole.value = row;
    roleModalOpen.value = true;
}

async function hapus(row: RoleRow): Promise<void> {
    const ok = await confirm({
        title: t('roles.deleteTitle'),
        message: t('roles.deleteConfirm', { name: row.display_name }),
        variant: 'destructive',
        confirmLabel: t('common.delete'),
    });
    if (!ok) return;
    router.delete(`/roles/${row.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('roles.title')" />
    <AppLayout>
        <div class="space-y-5">
            <PageHeader
                :title="t('roles.title')"
                :description="t('roles.description')"
                :breadcrumbs="[{ label: t('roles.breadcrumbRoot') }, { label: t('roles.breadcrumb') }]"
            >
                <template #actions>
                    <Button v-if="can('create', 'role-management')" @click="openCreate">
                        <Plus class="h-4 w-4" /> {{ t('roles.create') }}
                    </Button>
                </template>
            </PageHeader>

            <FilterBar
                v-model:search="state.search"
                :placeholder="t('roles.searchPlaceholder')"
                scope="roles"
                :state="state"
                @reset="state.search = ''"
            />

            <DataTable
                :data="data"
                :columns="columns"
                :sort="state.sort"
                :direction="state.direction"
                :only="['data']"
                :has-active-filter="!!state.search"
                :empty-title="t('roles.empty')"
                @sort="sortBy"
            >
                <template #cell-name="{ row }">
                    <div class="inline-flex items-center gap-2">
                        <ShieldCheck v-if="row.name === 'super-admin'" class="h-3.5 w-3.5 text-[var(--brand-soft-fg)]" />
                        <span class="font-medium font-mono text-sm">{{ row.name }}</span>
                    </div>
                </template>
                <template #cell-users_count="{ value }">
                    <span class="tabular-nums">{{ value }}</span>
                </template>
                <template #cell-is_active="{ value }">
                    <Badge :variant="value ? 'success' : 'muted'">{{ value ? t('roles.statusActive') : t('roles.statusInactive') }}</Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex justify-end gap-1">
                        <Button
                            v-if="can('update', 'role-management') && row.name !== 'super-admin'"
                            variant="ghost"
                            size="icon-xs"
                            :aria-label="t('roles.actionEdit')"
                            @click="openEdit(row)"
                        >
                            <Pencil class="h-3.5 w-3.5" />
                        </Button>
                        <Button
                            v-if="can('delete', 'role-management') && row.name !== 'super-admin'"
                            variant="ghost"
                            size="icon-xs"
                            class="text-[var(--status-danger-fg)] hover:bg-[var(--status-danger-bg)]"
                            :aria-label="t('roles.actionDelete')"
                            @click="hapus(row)"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Modal Tambah/Ubah Role (konten di components/role-modal.vue) -->
        <RoleModal v-model="roleModalOpen" :role="editingRole" :matrix="matrix" />
    </AppLayout>
</template>
