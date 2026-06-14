<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, Eye, Download, Trash, RefreshCcw } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import FilterBar from '@/components/shared/FilterBar.vue';
import { Button } from '@/components/ui/Button';
import { Avatar } from '@/components/ui/Avatar';
import { Badge } from '@/components/ui/Badge';
import { Select } from '@/components/ui/Select';
import { DataTable, type Column } from '@/components/ui/DataTable';
import { DropdownMenu, DropdownMenuItem } from '@/components/ui/DropdownMenu';
import { useDataTable } from '@/composables/useDataTable';
import { useConfirm } from '@/composables/useConfirm';
import { usePermission } from '@/composables/usePermission';
import { USER_STATUS } from '@/types/enums';
import type { Paginated, User } from '@/types';
import { ref } from 'vue';

const props = defineProps<{
    data: Paginated<User>;
    roles: Array<{ id: number; name: string; display_name: string }>;
    filters: Record<string, unknown>;
    trashed: boolean;
}>();

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

const columns: Column[] = [
    { key: 'name', label: 'Nama', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'role', label: 'Role' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'last_login_at', label: 'Login terakhir', sortable: true },
];

const roleOptions = props.roles.map((r) => ({ label: r.display_name, value: r.id }));
const statusOptions = Object.entries(USER_STATUS).map(([k, v]) => ({ label: v.label, value: k }));

async function hapus(row: User): Promise<void> {
    const ok = await confirm({
        title: 'Hapus pengguna?',
        message: `Yakin hapus "${row.name}"?`,
        variant: 'destructive',
        confirmLabel: 'Hapus',
    });
    if (!ok) return;
    router.delete(`/users/${row.id}`, { preserveScroll: true });
}

async function pulihkan(row: User): Promise<void> {
    router.post(`/users/${row.id}/restore`, {}, { preserveScroll: true });
}

async function hapusBulk(): Promise<void> {
    const ok = await confirm({
        title: 'Hapus pengguna terpilih?',
        message: `Yakin hapus ${selected.value.length} pengguna?`,
        variant: 'destructive',
    });
    if (!ok) return;
    for (const id of selected.value) router.delete(`/users/${id}`, { preserveScroll: true });
    selected.value = [];
}

function toggleTrashed(): void {
    router.get('/users', { trashed: !props.trashed ? 1 : undefined }, { preserveState: true, preserveScroll: true });
}
</script>

<template>
    <Head title="Manajemen Pengguna" />
    <AppLayout>
        <PageHeader
            title="Manajemen Pengguna"
            description="Kelola pengguna sistem."
            :breadcrumbs="[{ label: 'User & Access' }, { label: 'Pengguna' }]"
        >
            <template #actions>
                <DropdownMenu align="end">
                    <template #trigger>
                        <Button variant="outline" size="sm">
                            <Download class="h-4 w-4" /> Ekspor
                        </Button>
                    </template>
                    <DropdownMenuItem as="a" :href="`/users/export/csv?search=${state.search ?? ''}`">CSV</DropdownMenuItem>
                </DropdownMenu>
                <Button variant="outline" size="sm" @click="toggleTrashed">
                    <Trash class="h-4 w-4" /> {{ trashed ? 'Aktif' : 'Terhapus' }}
                </Button>
                <Button v-if="can('create', 'user-management')" as="link" href="/users/create">
                    <Plus class="h-4 w-4" /> Tambah Pengguna
                </Button>
            </template>
        </PageHeader>

        <FilterBar v-model:search="state.search" placeholder="Cari nama/email...">
            <Select v-model="state.filters.role_id" :options="roleOptions" placeholder="Role" clearable class="w-44" />
            <Select v-model="state.filters.status" :options="statusOptions" placeholder="Status" clearable class="w-32" />
        </FilterBar>

        <DataTable
            :data="data"
            :columns="columns"
            :sort="state.sort"
            :direction="state.direction"
            :only="['data']"
            :selectable="true"
            v-model:selected="selected"
            @sort="sortBy"
        >
            <template #cell-name="{ row }">
                <div class="flex items-center gap-2">
                    <Avatar :src="row.avatar_url" :name="row.name" size="sm" />
                    <span class="font-medium">{{ row.name }}</span>
                </div>
            </template>
            <template #cell-role="{ row }">
                <Badge variant="secondary">{{ row.role?.display_name ?? '-' }}</Badge>
            </template>
            <template #cell-status="{ value }">
                <Badge :variant="(USER_STATUS as never)[value]?.color ?? 'muted'">
                    {{ (USER_STATUS as never)[value]?.label ?? value }}
                </Badge>
            </template>
            <template #cell-last_login_at="{ value }">
                <span class="text-muted-foreground text-xs">{{ value ?? '-' }}</span>
            </template>
            <template #actions="{ row }">
                <div class="flex justify-end gap-1">
                    <Link
                        :href="`/users/${row.id}`"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent"
                    >
                        <Eye class="h-3.5 w-3.5" />
                    </Link>
                    <Link
                        v-if="can('update', 'user-management') && !trashed"
                        :href="`/users/${row.id}/edit`"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent"
                    >
                        <Pencil class="h-3.5 w-3.5" />
                    </Link>
                    <button
                        v-if="trashed"
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-success hover:bg-success/10"
                        @click="pulihkan(row)"
                    >
                        <RefreshCcw class="h-3.5 w-3.5" />
                    </button>
                    <button
                        v-if="can('delete', 'user-management') && !trashed"
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-destructive hover:bg-destructive/10"
                        @click="hapus(row)"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                    </button>
                </div>
            </template>
            <template #bulk-actions>
                <Button variant="destructive" size="sm" @click="hapusBulk">Hapus terpilih</Button>
            </template>
        </DataTable>
    </AppLayout>
</template>
