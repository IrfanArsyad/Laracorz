<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Pencil,
    Plus,
    Trash2,
    ChevronRight,
    ChevronDown,
    Eye,
    FolderTree,
    Info,
    MoreVertical,
    Layers,
    FolderOpen,
    Hash,
    Activity,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Button } from '@/components/ui/Button';
import { Card, CardContent } from '@/components/ui/Card';
import { Badge } from '@/components/ui/Badge';
import { FormModal, DetailModal } from '@/components/ui/Modal';
import {
    DropdownMenu,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/DropdownMenu';
import StatCard from '@/components/ui/StatCard/StatCard.vue';
import ModuleForm from './components/ModuleForm.vue';
import GroupForm from './components/GroupForm.vue';
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

const expanded = ref<Record<number, boolean>>({});
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

function toggleExpand(id: number): void {
    expanded.value[id] = !expanded.value[id];
}

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

async function deleteModule(id: number, label: string): Promise<void> {
    const ok = await confirm({
        title: 'Hapus modul?',
        message: `Yakin hapus "${label}"? Ini akan menghapus izin terkait di semua role.`,
        variant: 'destructive',
        confirmLabel: 'Hapus',
    });
    if (!ok) return;
    router.delete(`/modules/${id}`, { preserveScroll: true });
}
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

            <!-- Unified view: per-Grup card -->
            <div class="space-y-4">
                <Card v-for="group in tree" :key="group.id" class="overflow-hidden">
                    <!-- Group header -->
                    <div class="flex items-center gap-3 px-5 py-3.5 bg-[var(--surface-sunken)]/40 border-b border-[var(--border-subtle)]">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--brand-soft-bg)] text-[var(--brand-soft-fg)]">
                            <FolderTree class="h-4 w-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-semibold text-[var(--text-strong)]">{{ group.label }}</h3>
                                <Badge variant="muted" class="font-mono text-xs">{{ group.name }}</Badge>
                                <Badge v-if="!group.active" variant="warning">Nonaktif</Badge>
                            </div>
                            <p class="text-sm text-[var(--text-muted)] mt-0.5">
                                {{ group.modules.length }} modul · urutan ke-{{ group.order }}
                            </p>
                        </div>

                        <Button size="sm" variant="outline" @click="openCreateModule(group.id)">
                            <Plus class="h-3.5 w-3.5" /> Modul
                        </Button>

                        <DropdownMenu align="end">
                            <template #trigger>
                                <Button size="icon-sm" variant="ghost" aria-label="Opsi grup">
                                    <MoreVertical class="h-4 w-4" />
                                </Button>
                            </template>
                            <DropdownMenuLabel>Grup: {{ group.label }}</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @click="openEditGroup(group)">
                                <Pencil class="h-4 w-4" /> Ubah grup
                            </DropdownMenuItem>
                            <DropdownMenuItem variant="destructive" @click="deleteGroup(group)">
                                <Trash2 class="h-4 w-4" /> Hapus grup
                            </DropdownMenuItem>
                        </DropdownMenu>
                    </div>

                    <!-- Modul list di dalam grup -->
                    <CardContent class="pt-3 pb-3">
                        <p v-if="group.modules.length === 0" class="text-sm text-[var(--text-muted)] py-4 text-center">
                            Belum ada modul di grup ini.
                            <button
                                type="button"
                                class="text-[var(--text-link)] hover:underline ml-1"
                                @click="openCreateModule(group.id)"
                            >
                                Tambah modul pertama
                            </button>
                        </p>

                        <ul v-else class="space-y-0.5">
                            <li v-for="node in group.modules" :key="node.id">
                                <div class="group flex items-center gap-2 rounded-md px-2 py-2 hover:bg-[var(--state-hover)] transition-colors">
                                    <button
                                        v-if="!node.is_leaf && node.children.length"
                                        type="button"
                                        class="flex h-5 w-5 shrink-0 items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-default)]"
                                        :aria-label="expanded[node.id] ? 'Tutup' : 'Buka'"
                                        @click="toggleExpand(node.id)"
                                    >
                                        <ChevronDown v-if="expanded[node.id]" class="h-4 w-4" />
                                        <ChevronRight v-else class="h-4 w-4" />
                                    </button>
                                    <span v-else class="w-5 shrink-0" />

                                    <FolderOpen v-if="!node.is_leaf" class="h-4 w-4 text-[var(--text-muted)] shrink-0" />
                                    <Hash v-else class="h-4 w-4 text-[var(--brand-soft-fg)] shrink-0" />

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-medium text-[var(--text-strong)]">{{ node.label }}</span>
                                            <Badge variant="muted" class="font-mono text-xs">{{ node.name }}</Badge>
                                            <Badge :variant="node.is_leaf ? 'info' : 'secondary'" class="text-xs">
                                                {{ node.is_leaf ? 'Leaf' : 'Container' }}
                                            </Badge>
                                            <Badge v-if="!node.active" variant="warning" class="text-xs">Nonaktif</Badge>
                                        </div>
                                        <p v-if="node.url" class="text-xs text-[var(--text-muted)] truncate mt-0.5 font-mono">
                                            {{ node.url }}
                                        </p>
                                    </div>

                                    <DropdownMenu align="end">
                                        <template #trigger>
                                            <Button size="icon-xs" variant="ghost" aria-label="Opsi">
                                                <MoreVertical class="h-3.5 w-3.5" />
                                            </Button>
                                        </template>
                                        <DropdownMenuItem @click="detailModal.open(node)">
                                            <Eye class="h-3.5 w-3.5" /> Detail
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="openEditModule(node)">
                                            <Pencil class="h-3.5 w-3.5" /> Ubah
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="!node.is_leaf"
                                            @click="openCreateModule(undefined, node.id)"
                                        >
                                            <Plus class="h-3.5 w-3.5" /> Tambah sub-modul
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            variant="destructive"
                                            @click="deleteModule(node.id, node.label)"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" /> Hapus
                                        </DropdownMenuItem>
                                    </DropdownMenu>
                                </div>

                                <!-- Children inline expand -->
                                <ul
                                    v-if="!node.is_leaf && expanded[node.id] && node.children?.length"
                                    class="ml-7 mt-0.5 space-y-0.5 border-l border-[var(--border-subtle)] pl-3"
                                >
                                    <li
                                        v-for="child in node.children"
                                        :key="child.id"
                                        class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-[var(--state-hover)] transition-colors"
                                    >
                                        <Hash class="h-3.5 w-3.5 text-[var(--brand-soft-fg)] shrink-0" />
                                        <span class="text-sm font-medium text-[var(--text-strong)]">{{ child.label }}</span>
                                        <Badge variant="muted" class="font-mono text-xs">{{ child.name }}</Badge>
                                        <span v-if="child.url" class="text-xs text-[var(--text-muted)] truncate ml-auto font-mono">
                                            {{ child.url }}
                                        </span>
                                        <DropdownMenu align="end">
                                            <template #trigger>
                                                <Button size="icon-xs" variant="ghost">
                                                    <MoreVertical class="h-3.5 w-3.5" />
                                                </Button>
                                            </template>
                                            <DropdownMenuItem @click="detailModal.open(child)">
                                                <Eye class="h-3.5 w-3.5" /> Detail
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="openEditModule(child)">
                                                <Pencil class="h-3.5 w-3.5" /> Ubah
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem variant="destructive" @click="deleteModule(child.id, child.label)">
                                                <Trash2 class="h-3.5 w-3.5" /> Hapus
                                            </DropdownMenuItem>
                                        </DropdownMenu>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <!-- Empty state -->
                <div
                    v-if="tree.length === 0"
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
