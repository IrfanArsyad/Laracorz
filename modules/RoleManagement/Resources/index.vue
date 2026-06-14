<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, ShieldCheck, Lock, AlertCircle } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import FilterBar from '@/components/shared/FilterBar.vue';
import { Button } from '@/components/ui/Button';
import { Badge } from '@/components/ui/Badge';
import { DataTable, type Column } from '@/components/ui/DataTable';
import { FormModal } from '@/components/ui/Modal';
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { Textarea } from '@/components/ui/Textarea';
import { Switch } from '@/components/ui/Switch';
import PermissionMatrix from './components/PermissionMatrix.vue';
import { useDataTable } from '@/composables/useDataTable';
import { useConfirm } from '@/composables/useConfirm';
import { useModal } from '@/composables/useModal';
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

// ─── Modal Create/Edit ─────────────────────────────────────────────────
const formModal = useModal<RoleRow | null>();

const blankForm = {
    name: '',
    display_name: '',
    description: '',
    is_active: true,
    read: [] as Array<number | string>,
    create: [] as Array<number | string>,
    update: [] as Array<number | string>,
    delete: [] as Array<number | string>,
    extra: {} as Record<string, string[]>,
};

const form = useForm<typeof blankForm & { _method?: string }>({ ...blankForm });

const isEditing = computed(() => !!formModal.data.value);
const isSuperAdmin = computed(() => formModal.data.value?.name === 'super-admin');

function openCreate(): void {
    form.reset();
    Object.assign(form, blankForm);
    delete (form as { _method?: string })._method;
    formModal.open(null);
}

function openEdit(row: RoleRow): void {
    form.reset();
    Object.assign(form, {
        ...blankForm,
        _method: 'put',
        name: row.name,
        display_name: row.display_name,
        description: row.description ?? '',
        is_active: row.is_active,
        read: Array.isArray(row.read) ? row.read : [],
        create: Array.isArray(row.create) ? row.create : [],
        update: Array.isArray(row.update) ? row.update : [],
        delete: Array.isArray(row.delete) ? row.delete : [],
        extra: row.extra ?? {},
    });
    formModal.open(row);
}

function submit(): void {
    const editing = formModal.data.value;
    const opts = { preserveScroll: true, onSuccess: () => formModal.close() };
    if (editing) {
        form.post(`/roles/${editing.id}`, opts);
    } else {
        form.post('/roles', opts);
    }
}

async function hapus(row: RoleRow): Promise<void> {
    const ok = await confirm({
        title: 'Hapus role?',
        message: `Yakin hapus role "${row.display_name}"?`,
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
        <div class="space-y-5">
            <PageHeader
                title="Manajemen Role"
                description="Atur peran pengguna dan matriks izin per modul."
                :breadcrumbs="[{ label: 'Role & Permission' }, { label: 'Role' }]"
            >
                <template #actions>
                    <Button v-if="can('create', 'role-management')" @click="openCreate">
                        <Plus class="h-4 w-4" /> Tambah Role
                    </Button>
                </template>
            </PageHeader>

            <FilterBar
                v-model:search="state.search"
                placeholder="Cari nama role..."
                @reset="state.search = ''"
            />

            <DataTable
                :data="data"
                :columns="columns"
                :sort="state.sort"
                :direction="state.direction"
                :only="['data']"
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
                    <Badge :variant="value ? 'success' : 'muted'">{{ value ? 'Aktif' : 'Nonaktif' }}</Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex justify-end gap-1">
                        <Button
                            v-if="can('update', 'role-management') && row.name !== 'super-admin'"
                            variant="ghost"
                            size="icon-xs"
                            aria-label="Ubah"
                            @click="openEdit(row)"
                        >
                            <Pencil class="h-3.5 w-3.5" />
                        </Button>
                        <Button
                            v-if="can('delete', 'role-management') && row.name !== 'super-admin'"
                            variant="ghost"
                            size="icon-xs"
                            class="text-[var(--status-danger-fg)] hover:bg-[var(--status-danger-bg)]"
                            aria-label="Hapus"
                            @click="hapus(row)"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Modal Tambah/Ubah Role — pakai FormModal preset -->
        <FormModal
            v-model="formModal.isOpen.value"
            :title="isEditing ? `Ubah Role: ${formModal.data.value?.display_name}` : 'Tambah Role Baru'"
            :description="isEditing ? 'Perbarui detail role dan matriks izinnya.' : 'Buat peran baru dengan matriks izin per modul.'"
            size="2xl"
            :processing="form.processing"
            @submit="submit"
            @cancel="formModal.close()"
        >
            <!-- Banner protection super-admin -->
            <div
                v-if="isSuperAdmin"
                class="mb-4 flex items-start gap-2 rounded-md border border-[var(--status-warning-border)] bg-[var(--status-warning-bg)] p-3 text-sm text-[var(--status-warning-fg)]"
            >
                <Lock class="h-4 w-4 shrink-0 mt-px" />
                <p>Role <strong>super-admin</strong> bersifat protected dan tidak dapat diubah.</p>
            </div>

            <!-- Identitas role -->
            <div class="grid gap-3.5 sm:grid-cols-2">
                <FormField label="Slug" hint="huruf kecil, tanpa spasi" :error="form.errors.name" required>
                    <Input v-model="form.name" placeholder="contoh: editor" :disabled="isSuperAdmin" />
                </FormField>
                <FormField label="Nama Tampilan" :error="form.errors.display_name" required>
                    <Input v-model="form.display_name" placeholder="contoh: Editor Konten" :disabled="isSuperAdmin" />
                </FormField>
                <FormField label="Deskripsi" class="sm:col-span-2">
                    <Textarea v-model="form.description" :rows="2" :disabled="isSuperAdmin" />
                </FormField>
                <FormField label="Status">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <Switch v-model="form.is_active" :disabled="isSuperAdmin" />
                        <span class="text-sm">{{ form.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </label>
                </FormField>
            </div>

            <!-- Matrix permission -->
            <div class="mt-6 pt-5 border-t border-[var(--border-subtle)]">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-[var(--text-strong)]">Matriks Izin</h3>
                        <p class="text-sm text-[var(--text-muted)]">Centang akses per modul. Container hanya tampil kalau ada leaf yang dipilih.</p>
                    </div>
                </div>

                <div v-if="!matrix" class="flex items-center gap-2 text-sm text-[var(--text-muted)] py-8 justify-center">
                    <AlertCircle class="h-4 w-4" />
                    Memuat struktur modul...
                </div>
                <PermissionMatrix
                    v-else
                    :matrix="matrix as never"
                    v-model:model-read="form.read"
                    v-model:model-create="form.create"
                    v-model:model-update="form.update"
                    v-model:model-delete="form.delete"
                    v-model:model-extra="form.extra"
                />
            </div>
        </FormModal>
    </AppLayout>
</template>
