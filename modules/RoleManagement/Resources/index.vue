<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, ShieldCheck, Lock, AlertCircle } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
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
const formModal = useModal<RoleRow | null>();

const blankForm = {
    name: '',
    display_name: '',
    description: '',
    is_active: true,
    /*
     * Field di-prefix `perm_` supaya tidak bentrok dengan Inertia useForm
     * built-in methods (`form.delete()` HTTP DELETE) — kalau dinamai polos
     * `delete`, akses `form.delete` me-return method, bukan array kita.
     * Sebelum submit, transform balik ke nama backend (read/create/update/delete)
     * via `form.transform()`.
     */
    perm_read: [] as Array<number | string>,
    perm_create: [] as Array<number | string>,
    perm_update: [] as Array<number | string>,
    perm_delete: [] as Array<number | string>,
    extra: {} as Record<string, string[]>,
};

const form = useForm<typeof blankForm & { _method?: string }>({ ...blankForm });

form.transform((data) => ({
    name: data.name,
    display_name: data.display_name,
    description: data.description,
    is_active: data.is_active,
    read: data.perm_read,
    create: data.perm_create,
    update: data.perm_update,
    delete: data.perm_delete,
    extra: data.extra,
    ...(data._method ? { _method: data._method } : {}),
}));

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
        perm_read: Array.isArray(row.read) ? row.read : [],
        perm_create: Array.isArray(row.create) ? row.create : [],
        perm_update: Array.isArray(row.update) ? row.update : [],
        perm_delete: Array.isArray(row.delete) ? row.delete : [],
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

        <!-- Modal Tambah/Ubah Role — pakai FormModal preset -->
        <FormModal
            v-model="formModal.isOpen.value"
            :title="isEditing ? t('roles.editTitle', { name: formModal.data.value?.display_name }) : t('roles.createNew')"
            :description="isEditing ? t('roles.modalEditDesc') : t('roles.modalCreateDesc')"
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
                <p>{{ t('roles.superAdminBanner', { role: 'super-admin' }) }}</p>
            </div>

            <!-- Identitas role -->
            <div class="grid gap-3.5 sm:grid-cols-2">
                <FormField :label="t('roles.name')" :hint="t('roles.nameHint')" :error="form.errors.name" required>
                    <Input v-model="form.name" :placeholder="t('roles.namePlaceholder')" :disabled="isSuperAdmin" />
                </FormField>
                <FormField :label="t('roles.displayName')" :error="form.errors.display_name" required>
                    <Input v-model="form.display_name" :placeholder="t('roles.displayNamePlaceholder')" :disabled="isSuperAdmin" />
                </FormField>
                <FormField :label="t('roles.description2')" class="sm:col-span-2">
                    <Textarea v-model="form.description" :rows="2" :disabled="isSuperAdmin" />
                </FormField>
                <FormField :label="t('common.status')">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <Switch v-model="form.is_active" :disabled="isSuperAdmin" />
                        <span class="text-sm">{{ form.is_active ? t('roles.statusActive') : t('roles.statusInactive') }}</span>
                    </label>
                </FormField>
            </div>

            <!-- Matrix permission -->
            <div class="mt-6 pt-5 border-t border-[var(--border-subtle)]">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-[var(--text-strong)]">{{ t('roles.permissionMatrixTitle') }}</h3>
                        <p class="text-sm text-[var(--text-muted)]">{{ t('roles.permissionMatrixDesc') }}</p>
                    </div>
                </div>

                <div v-if="!matrix" class="flex items-center gap-2 text-sm text-[var(--text-muted)] py-8 justify-center">
                    <AlertCircle class="h-4 w-4" />
                    {{ t('roles.loadingMatrix') }}
                </div>
                <PermissionMatrix
                    v-else
                    :matrix="matrix as never"
                    v-model:model-read="form.perm_read"
                    v-model:model-create="form.perm_create"
                    v-model:model-update="form.perm_update"
                    v-model:model-delete="form.perm_delete"
                    v-model:model-extra="form.extra"
                />
            </div>
        </FormModal>
    </AppLayout>
</template>
