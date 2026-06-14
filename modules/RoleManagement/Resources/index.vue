<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import FilterBar from '@/components/shared/FilterBar.vue';
import { Button } from '@/components/ui/Button';
import { Badge } from '@/components/ui/Badge';
import { DataTable, type Column } from '@/components/ui/DataTable';
import { useDataTable } from '@/composables/useDataTable';
import { useConfirm } from '@/composables/useConfirm';
import { usePermission } from '@/composables/usePermission';
import type { Paginated } from '@/types';

interface RoleRow {
    id: number;
    name: string;
    display_name: string;
    is_active: boolean;
    users_count: number;
}

const props = defineProps<{
    data: Paginated<RoleRow>;
    filters: { search: string | null; sort: string | null; direction: 'asc' | 'desc' };
}>();

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

const columns: Column[] = [
    { key: 'name', label: 'Nama', sortable: true },
    { key: 'display_name', label: 'Display Name', sortable: true },
    { key: 'users_count', label: 'Jumlah User', align: 'right' },
    { key: 'is_active', label: 'Status', align: 'center' },
];

async function hapus(row: RoleRow): Promise<void> {
    const ok = await confirm({
        title: 'Hapus role?',
        message: `Yakin hapus role "${row.name}"?`,
        variant: 'destructive',
        confirmLabel: 'Hapus',
    });
    if (!ok) return;
    router.delete(`/roles/${row.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Manajemen Role" />
    <AppLayout>
        <PageHeader
            title="Manajemen Role"
            description="Atur peran pengguna dan matriks izinnya."
            :breadcrumbs="[{ label: 'Role & Permission' }, { label: 'Role' }]"
        >
            <template #actions>
                <Button v-if="can('create', 'role-management')" as="link" href="/roles/create">
                    <Plus class="h-4 w-4" /> Tambah Role
                </Button>
            </template>
        </PageHeader>

        <FilterBar v-model:search="state.search" placeholder="Cari nama role..." />

        <DataTable
            :data="data"
            :columns="columns"
            :sort="state.sort"
            :direction="state.direction"
            :only="['data']"
            @sort="sortBy"
        >
            <template #cell-is_active="{ value }">
                <Badge :variant="value ? 'success' : 'muted'">{{ value ? 'Aktif' : 'Nonaktif' }}</Badge>
            </template>
            <template #actions="{ row }">
                <div class="flex justify-end gap-1">
                    <Link
                        v-if="can('update', 'role-management') && row.name !== 'super-admin'"
                        :href="`/roles/${row.id}/edit`"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent"
                    >
                        <Pencil class="h-3.5 w-3.5" />
                    </Link>
                    <button
                        v-if="can('delete', 'role-management') && row.name !== 'super-admin'"
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-destructive hover:bg-destructive/10"
                        @click="hapus(row)"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                    </button>
                </div>
            </template>
        </DataTable>
    </AppLayout>
</template>
