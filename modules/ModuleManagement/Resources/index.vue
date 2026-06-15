<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Plus,
    FolderTree,
    Info,
    Layers,
    Hash,
    Activity,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Button } from '@/components/ui/Button';
import { FormModal, DetailModal } from '@/components/ui/Modal';
import StatCard from '@/components/ui/StatCard/StatCard.vue';
import ModuleForm from './components/ModuleForm.vue';
import GroupForm from './components/GroupForm.vue';
import SortableTree from './components/SortableTree.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useModal } from '@/composables/useModal';

interface Node {
    id: number;
    name: string;
    label: string;
    icon: string | null;
    url: string | null;
    route_name: string | null;
    order: number;
    active: boolean;
    is_leaf: boolean;
    children: Node[];
    parent_id?: number | null;
    module_group_id?: number | null;
    badge_source?: string | null;
    extra_actions?: string[] | null;
    external?: boolean;
}

interface Group {
    id: number;
    name: string;
    label: string;
    icon: string | null;
    order: number;
    active: boolean;
    modules: Node[];
}

const props = defineProps<{
    tree: Group[];
    groups: Array<{ id: number; name: string; label: string }>;
    modules: Array<{ id: number; name: string; label: string }>;
}>();

const { confirm } = useConfirm();

const stats = computed(() => {
    let totalModules = 0;
    let leafCount = 0;
    let containerCount = 0;
    let inactiveCount = 0;
    function walk(nodes: Node[]): void {
        for (const n of nodes) {
            totalModules++;
            if (n.is_leaf) leafCount++;
            else containerCount++;
            if (!n.active) inactiveCount++;
            if (n.children?.length) walk(n.children);
        }
    }
    for (const g of props.tree) walk(g.modules);
    return {
        groups: props.tree.length,
        totalModules,
        leafCount,
        containerCount,
        inactiveCount,
    };
});

// Modal: Modul
const moduleModal = useModal<Node | null>();

const blankModule = {
    name: '',
    label: '',
    icon: '',
    module_group_id: null as number | null,
    parent_id: null as number | null,
    url: '',
    route_name: '',
    badge_source: '',
    extra_actions: [] as string[],
    order: 0,
    active: true,
    external: false,
};

const moduleForm = useForm({ ...blankModule });

function openCreateModule(presetGroupId?: number, presetParentId?: number): void {
    moduleForm.reset();
    Object.assign(moduleForm, blankModule, {
        module_group_id: presetGroupId ?? null,
        parent_id: presetParentId ?? null,
    });
    moduleModal.open(null);
}

function openEditModule(node: Node): void {
    moduleForm.reset();
    Object.assign(moduleForm, {
        ...blankModule,
        name: node.name,
        label: node.label,
        icon: node.icon ?? '',
        module_group_id: node.module_group_id ?? null,
        parent_id: node.parent_id ?? null,
        url: node.url ?? '',
        route_name: node.route_name ?? '',
        badge_source: node.badge_source ?? '',
        extra_actions: node.extra_actions ?? [],
        order: node.order,
        active: node.active,
        external: node.external ?? false,
    });
    moduleModal.open(node);
}

function submitModule(): void {
    const editing = moduleModal.data.value;
    const opts = { preserveScroll: true, onSuccess: () => moduleModal.close() };
    if (editing) {
        moduleForm.put(`/modules/${editing.id}`, opts);
    } else {
        moduleForm.post('/modules', opts);
    }
}

// Modal: Grup
const groupModal = useModal<Group | null>();

const blankGroup = {
    name: '',
    label: '',
    icon: '',
    order: 0,
    active: true,
};

const groupForm = useForm({ ...blankGroup });

function openCreateGroup(): void {
    groupForm.reset();
    Object.assign(groupForm, blankGroup);
    groupModal.open(null);
}

function openEditGroup(group: Group): void {
    groupForm.reset();
    Object.assign(groupForm, {
        ...blankGroup,
        name: group.name,
        label: group.label,
        icon: group.icon ?? '',
        order: group.order,
        active: group.active,
    });
    groupModal.open(group);
}

function submitGroup(): void {
    const editing = groupModal.data.value;
    const opts = { preserveScroll: true, onSuccess: () => groupModal.close() };
    if (editing) {
        groupForm.put(`/modules/groups/${editing.id}`, opts);
    } else {
        groupForm.post('/modules/groups', opts);
    }
}

async function deleteGroup(group: Group): Promise<void> {
    if (group.modules?.length) {
        await confirm({
            title: 'Grup masih punya modul',
            message: `Grup "${group.label}" masih punya ${group.modules.length} modul. Hapus semuanya dulu sebelum menghapus grup.`,
            confirmLabel: 'OK',
            cancelLabel: '',
        });
        return;
    }
    const ok = await confirm({
        title: 'Hapus grup?',
        message: `Yakin hapus grup "${group.label}"?`,
        variant: 'destructive',
        confirmLabel: 'Hapus',
    });
    if (!ok) return;
    router.delete(`/modules/groups/${group.id}`, { preserveScroll: true });
}

// Detail modal — read-only
const detailModal = useModal<Node | null>();
</script>

<template>
    <Head title="Manajemen Modul" />
    <AppLayout>
        <div class="space-y-5">
            <PageHeader
                title="Manajemen Modul"
                description="Atur struktur menu sidebar dan unit izin sistem."
                :breadcrumbs="[{ label: 'Role & Permission' }, { label: 'Module' }]"
            >
                <template #actions>
                    <Button variant="outline" @click="openCreateGroup">
                        <FolderTree class="h-4 w-4" /> Tambah Grup
                    </Button>
                    <Button @click="() => openCreateModule()">
                        <Plus class="h-4 w-4" /> Tambah Modul
                    </Button>
                </template>
            </PageHeader>

            <!-- KPI ringkas -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <StatCard label="Total Grup" :value="stats.groups" :icon="FolderTree" />
                <StatCard label="Total Modul" :value="stats.totalModules" :icon="Layers" />
                <StatCard label="Leaf (Halaman)" :value="stats.leafCount" :icon="Hash" />
                <StatCard label="Nonaktif" :value="stats.inactiveCount" :icon="Activity" />
            </div>

            <!-- Info concept banner -->
            <div
                class="flex items-start gap-3 rounded-xl border border-[var(--status-info-border)] bg-[var(--status-info-bg)] p-4 text-sm text-[var(--status-info-fg)]"
            >
                <Info class="h-5 w-5 shrink-0 mt-0.5" />
                <div class="space-y-1.5 leading-relaxed">
                    <p class="font-semibold">Konsep Grup &amp; Modul</p>
                    <p>
                        <strong>Grup</strong> = section pemisah di sidebar (mis. "User &amp; Access").
                        <strong>Modul</strong> = entri yang bisa diklik. Modul bisa berupa
                        <em>leaf</em> (punya url, contoh: "Pengguna" → /users) atau
                        <em>container</em> (cuma folder yang isinya modul lain).
                        Permission disimpan per modul leaf — container otomatis tampil kalau ≥ 1 child-nya boleh diakses.
                    </p>
                </div>
            </div>

            <!-- Sortable tree table — drag to reorder / nest -->
            <SortableTree
                v-if="tree.length > 0"
                :tree="tree"
                @edit="openEditModule"
                @detail="(n) => detailModal.open(n)"
                @add-sub="(parentId) => openCreateModule(undefined, parentId)"
                @add-to-group="(groupId) => openCreateModule(groupId)"
                @edit-group="openEditGroup"
                @delete-group="deleteGroup"
            />

            <!-- Empty state -->
            <div
                v-else
                class="rounded-xl border border-dashed border-[var(--border-default)] bg-[var(--surface-raised)] p-8 text-center"
            >
                <FolderTree class="h-10 w-10 mx-auto text-[var(--text-muted)] mb-3" />
                <h3 class="text-base font-semibold text-[var(--text-strong)]">Belum ada grup</h3>
                <p class="text-sm text-[var(--text-muted)] mt-1">
                    Mulai dengan membuat grup pertama (contoh: "Main", "System").
                </p>
                <Button class="mt-4" @click="openCreateGroup">
                    <Plus class="h-4 w-4" /> Tambah Grup Pertama
                </Button>
            </div>
        </div>

        <!-- Modal: Modul (create/edit) -->
        <FormModal
            v-model="moduleModal.isOpen.value"
            :title="moduleModal.data.value ? `Ubah Modul: ${moduleModal.data.value.label}` : 'Tambah Modul'"
            :description="moduleModal.data.value ? 'Perbarui detail modul.' : 'Buat modul baru. Modul leaf butuh URL + route name; container biarkan kosong.'"
            size="xl"
            :processing="moduleForm.processing"
            @submit="submitModule"
            @cancel="moduleModal.close()"
        >
            <ModuleForm :form="moduleForm" :groups="groups" :modules="modules" />
        </FormModal>

        <!-- Modal: Grup (create/edit) -->
        <FormModal
            v-model="groupModal.isOpen.value"
            :title="groupModal.data.value ? `Ubah Grup: ${groupModal.data.value.label}` : 'Tambah Grup'"
            :description="groupModal.data.value ? 'Perbarui detail grup.' : 'Grup adalah section header di sidebar yang menampung modul-modul terkait.'"
            size="md"
            :processing="groupForm.processing"
            @submit="submitGroup"
            @cancel="groupModal.close()"
        >
            <GroupForm :form="groupForm" />
        </FormModal>

        <!-- Modal: Detail -->
        <DetailModal
            v-model="detailModal.isOpen.value"
            :title="detailModal.data.value?.label ?? 'Detail'"
            :description="detailModal.data.value?.name"
            :items="detailModal.data.value ? [
                { label: 'Slug', value: detailModal.data.value.name },
                { label: 'URL', value: detailModal.data.value.url ?? '— container —' },
                { label: 'Route', value: detailModal.data.value.route_name ?? '—' },
                { label: 'Tipe', value: detailModal.data.value.is_leaf ? 'Leaf (halaman)' : 'Container (folder)' },
                { label: 'Icon', value: detailModal.data.value.icon ?? '—' },
                { label: 'Urutan', value: detailModal.data.value.order },
                { label: 'Status', value: detailModal.data.value.active ? 'Aktif' : 'Nonaktif' },
            ] : []"
        />
    </AppLayout>
</template>
