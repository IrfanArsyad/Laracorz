<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Pencil,
    Plus,
    Trash2,
    Eye,
    Download,
    Trash,
    RefreshCcw,
    Users,
    UserCheck,
    UserMinus,
    UserX,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import FilterBar from '@/components/shared/FilterBar.vue';
import { Button } from '@/components/ui/Button';
import { Avatar } from '@/components/ui/Avatar';
import { Badge } from '@/components/ui/Badge';
import { Select } from '@/components/ui/Select';
import { FormField } from '@/components/ui/FormField';
import { DataTable, type Column } from '@/components/ui/DataTable';
import { DropdownMenu, DropdownMenuItem } from '@/components/ui/DropdownMenu';
import StatCard from '@/components/ui/StatCard/StatCard.vue';
import CreateUserModal from './components/create.vue';
import EditUserModal from './components/edit.vue';
import ShowUserModal from './components/show.vue';
import { useDataTable } from '@/composables/useDataTable';
import { useConfirm } from '@/composables/useConfirm';
import { usePermission } from '@/composables/usePermission';
import { USER_STATUS } from '@/types/enums';
import type { Paginated, User } from '@/types';
import { computed, ref, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    data: Paginated<User>;
    roles: Array<{ id: number; name: string; display_name: string }>;
    filters: Record<string, unknown>;
    trashed: boolean;
    stats?: { total: number; active: number; inactive: number; banned: number };
    detail?: User | null;
    detailLogs?: Array<{ id: number; action: string; description: string; created_at: string }>;
}>();

const { t } = useI18n();

const { state, sortBy } = useDataTable({
    initial: {
        search: (props.filters.search as string) ?? '',
        sort: (props.filters.sort as string) ?? null,
        direction: (props.filters.direction as 'asc' | 'desc') ?? 'desc',
        filters: { role_id: props.filters.role_id, status: props.filters.status },
    },
    only: ['data'],
});

const selected = ref<Array<number | string>>([]);
const { confirm } = useConfirm();
const { can } = usePermission();

const filtersCount = computed(() => {
    let n = 0;
    if (state.filters.role_id) n++;
    if (state.filters.status) n++;
    return n;
});

const columns = computed<Column[]>(() => [
    { key: 'name', label: t('users.columnName'), sortable: true },
    { key: 'email', label: t('users.columnEmail'), sortable: true },
    { key: 'role', label: t('users.columnRole') },
    { key: 'status', label: t('users.columnStatus'), align: 'center' },
    { key: 'last_login_at', label: t('users.columnLastLogin'), sortable: true },
]);

const roleOptions = computed(() => props.roles.map((r) => ({ label: r.display_name, value: r.id })));
const statusOptions = Object.entries(USER_STATUS).map(([k, v]) => ({ label: v.label, value: k }));

// ─── Modal Create / Edit ────────────────────────────────────────────────
// State buka-tutup di sini; form & submit dienkapsulasi di komponen modal
// masing-masing (components/CreateUserModal, EditUserModal).
const createOpen = ref(false);
const editOpen = ref(false);
const editUser = ref<User | null>(null);

function openCreate(): void {
    createOpen.value = true;
}

function openEdit(row: User): void {
    editUser.value = row;
    editOpen.value = true;
}

// ─── Modal Detail (URL /users/{id}, deep-link friendly) ─────────────────
const detailOpen = ref(false);

// Detail datang dari server (partial visit / deep-link) → buka modal.
watch(
    () => props.detail,
    (u) => {
        if (u) detailOpen.value = true;
    },
);
onMounted(() => {
    if (props.detail) detailOpen.value = true;
});

function openDetail(row: User): void {
    router.visit(`/users/${row.id}`, {
        only: ['detail', 'detailLogs'],
        preserveState: true,
        preserveScroll: true,
    });
}

function closeDetail(): void {
    detailOpen.value = false;
    // Kembalikan URL ke /users tanpa kehilangan daftar (refetch ringan `data` saja).
    router.visit('/users', {
        only: ['data'],
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

// ─── Aksi row ───────────────────────────────────────────────────────────
async function hapus(row: User): Promise<void> {
    const ok = await confirm({
        title: t('users.deleteTitle'),
        message: t('users.deleteShort', { name: row.name }),
        variant: 'destructive',
        confirmLabel: t('common.delete'),
    });
    if (!ok) return;
    router.delete(`/users/${row.id}`, { preserveScroll: true });
}

function pulihkan(row: User): void {
    router.post(`/users/${row.id}/restore`, {}, { preserveScroll: true });
}

async function hapusBulk(): Promise<void> {
    const ok = await confirm({
        title: t('users.deleteBulkTitle'),
        message: t('users.deleteBulkConfirm', { count: selected.value.length }),
        variant: 'destructive',
    });
    if (!ok) return;
    for (const id of selected.value) router.delete(`/users/${id}`, { preserveScroll: true });
    selected.value = [];
}

function toggleTrashed(): void {
    router.get('/users', { trashed: !props.trashed ? 1 : undefined }, { preserveState: true, preserveScroll: true });
}

function resetFilters(): void {
    state.search = '';
    state.filters.role_id = undefined;
    state.filters.status = undefined;
}
</script>

<template>
    <Head :title="t('users.title')" />
    <AppLayout>
        <div class="space-y-5">
            <PageHeader
                :title="t('users.title')"
                :description="t('users.description')"
                :breadcrumbs="[{ label: t('users.breadcrumbRoot') }, { label: t('users.breadcrumb') }]"
            >
                <template #actions>
                    <DropdownMenu align="end">
                        <template #trigger>
                            <Button variant="outline" size="sm">
                                <Download class="h-3.5 w-3.5" /> {{ t('common.export') }}
                            </Button>
                        </template>
                        <DropdownMenuItem as="a" :href="`/users/export/csv?search=${state.search ?? ''}`">CSV</DropdownMenuItem>
                    </DropdownMenu>
                    <Button variant="outline" size="sm" @click="toggleTrashed">
                        <Trash class="h-3.5 w-3.5" /> {{ trashed ? t('users.showActive') : t('users.showTrashed') }}
                    </Button>
                    <Button v-if="can('create', 'user-management') && !trashed" @click="openCreate">
                        <Plus class="h-4 w-4" /> {{ t('users.create') }}
                    </Button>
                </template>
            </PageHeader>

            <!-- Stat cards — KPI ringkas -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <StatCard :label="t('users.statsTotal')" :value="stats?.total ?? '—'" :icon="Users" :loading="!stats" />
                <StatCard :label="t('users.statsActive')" :value="stats?.active ?? '—'" :icon="UserCheck" :loading="!stats" />
                <StatCard :label="t('users.statsInactive')" :value="stats?.inactive ?? '—'" :icon="UserMinus" :loading="!stats" />
                <StatCard :label="t('users.statsBanned')" :value="stats?.banned ?? '—'" :icon="UserX" :loading="!stats" />
            </div>

            <FilterBar
                v-model:search="state.search"
                :placeholder="t('users.searchPlaceholder')"
                :filters-count="filtersCount"
                scope="users"
                :state="state"
                @reset="resetFilters"
            >
                <div class="min-w-[160px]">
                    <Select
                        v-model="state.filters.role_id"
                        :options="roleOptions"
                        :placeholder="t('users.filterAllRoles')"
                        clearable
                        searchable
                    />
                </div>
                <div class="min-w-[160px]">
                    <Select
                        v-model="state.filters.status"
                        :options="statusOptions"
                        :placeholder="t('users.filterAllStatuses')"
                        clearable
                    />
                </div>
            </FilterBar>

            <DataTable
                :data="data"
                :columns="columns"
                :sort="state.sort"
                :direction="state.direction"
                :only="['data']"
                :selectable="true"
                :has-active-filter="!!(state.search || state.filters.role_id || state.filters.status)"
                :empty-title="t('users.empty')"
                v-model:selected="selected"
                @sort="sortBy"
            >
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-2.5">
                        <Avatar :src="row.avatar_url" :name="row.name" size="sm" />
                        <div class="min-w-0">
                            <p class="font-medium text-[var(--text-strong)] truncate">{{ row.name }}</p>
                            <p v-if="row.username" class="text-xs text-[var(--text-muted)] font-mono">@{{ row.username }}</p>
                        </div>
                    </div>
                </template>
                <template #cell-role="{ row }">
                    <Badge variant="muted">{{ row.role?.display_name ?? '—' }}</Badge>
                </template>
                <template #cell-status="{ value }">
                    <Badge :variant="((USER_STATUS as any)[value]?.color ?? 'muted')">
                        {{ (USER_STATUS as any)[value]?.label ?? value }}
                    </Badge>
                </template>
                <template #cell-last_login_at="{ value }">
                    <span class="text-[var(--text-muted)] text-xs">{{ value ?? '—' }}</span>
                </template>
                <template #actions="{ row }">
                    <div class="flex justify-end gap-1">
                        <Button variant="ghost" size="icon-xs" :aria-label="t('users.actionDetail')" @click="openDetail(row)">
                            <Eye class="h-3.5 w-3.5" />
                        </Button>
                        <Button
                            v-if="can('update', 'user-management') && !trashed"
                            variant="ghost"
                            size="icon-xs"
                            :aria-label="t('users.actionEdit')"
                            @click="openEdit(row)"
                        >
                            <Pencil class="h-3.5 w-3.5" />
                        </Button>
                        <Button
                            v-if="trashed"
                            variant="ghost"
                            size="icon-xs"
                            class="text-[var(--status-success-fg)] hover:bg-[var(--status-success-bg)]"
                            :aria-label="t('users.actionRestore')"
                            @click="pulihkan(row)"
                        >
                            <RefreshCcw class="h-3.5 w-3.5" />
                        </Button>
                        <Button
                            v-if="can('delete', 'user-management') && !trashed"
                            variant="ghost"
                            size="icon-xs"
                            class="text-[var(--status-danger-fg)] hover:bg-[var(--status-danger-bg)]"
                            :aria-label="t('users.actionDelete')"
                            @click="hapus(row)"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                </template>
                <template #bulk-actions>
                    <Button variant="destructive" size="sm" @click="hapusBulk">
                        <Trash2 class="h-3.5 w-3.5" /> {{ t('users.deleteBulkAction') }}
                    </Button>
                </template>
            </DataTable>
        </div>

        <!-- Modal: Tambah / Ubah / Detail (tiap modal = 1 komponen di components/) -->
        <CreateUserModal v-model="createOpen" :roles="roles" />
        <EditUserModal v-model="editOpen" :user="editUser" :roles="roles" />
        <ShowUserModal
            :model-value="detailOpen"
            :detail="detail"
            :logs="detailLogs"
            @update:model-value="(v: boolean) => { if (!v) closeDetail(); }"
        />
    </AppLayout>
</template>
