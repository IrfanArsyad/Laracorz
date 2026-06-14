<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Pencil,
    Plus,
    Trash2,
    ChevronRight,
    ChevronDown,
    Eye,
    FolderOpen,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Button } from '@/components/ui/Button';
import { Card, CardContent } from '@/components/ui/Card';
import { Badge } from '@/components/ui/Badge';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/Tabs';
import { FormModal, DetailModal } from '@/components/ui/Modal';
import ModuleForm from './components/ModuleForm.vue';
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

defineProps<{
    tree: Group[];
    groups: Array<{ id: number; name: string; label: string }>;
    modules: Array<{ id: number; name: string; label: string }>;
}>();

const tab = ref('modules');
const expanded = ref<Record<number, boolean>>({});
const { confirm } = useConfirm();

function toggleExpand(id: number): void {
    expanded.value[id] = !expanded.value[id];
}

/**
 * Modal "tambah/ubah modul" — pakai useModal() controller.
 * data context = node yang diedit (atau null untuk create).
 */
const formModal = useModal<Node | null>();

const blankForm = {
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

const form = useForm({ ...blankForm });

function openCreate(): void {
    form.reset();
    Object.assign(form, blankForm);
    formModal.open(null);
}

function openEdit(node: Node): void {
    form.reset();
    Object.assign(form, {
        ...blankForm,
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
    formModal.open(node);
}

function submit(): void {
    const editing = formModal.data.value;
    if (editing) {
        form.put(`/modules/${editing.id}`, {
            preserveScroll: true,
            onSuccess: () => formModal.close(),
        });
    } else {
        form.post('/modules', {
            preserveScroll: true,
            onSuccess: () => formModal.close(),
        });
    }
}

/**
 * Modal "detail" — read-only view.
 */
const detailModal = useModal<Node | null>();
function openDetail(node: Node): void {
    detailModal.open(node);
}

async function hapus(id: number, label: string): Promise<void> {
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
        <div class="space-y-6">
            <PageHeader
                title="Manajemen Modul"
                description="Kelola pohon modul dan grupnya."
                :breadcrumbs="[{ label: 'Role & Permission' }, { label: 'Module' }]"
            >
                <template #actions>
                    <Button @click="openCreate">
                        <Plus class="h-4 w-4" /> Tambah Modul
                    </Button>
                </template>
            </PageHeader>

            <Tabs v-model="tab">
                <TabsList>
                    <TabsTrigger value="modules">Modul</TabsTrigger>
                    <TabsTrigger value="groups">Grup</TabsTrigger>
                </TabsList>

                <TabsContent value="modules">
                    <Card>
                        <CardContent class="space-y-5">
                            <div v-for="group in tree" :key="group.id">
                                <h3 class="text-xs uppercase tracking-[0.06em] text-[var(--text-muted)] mb-2 font-semibold">
                                    {{ group.label }}
                                </h3>
                                <ul class="space-y-0.5">
                                    <li v-for="node in group.modules" :key="node.id">
                                        <div class="flex items-center justify-between rounded-md px-2 py-1.5 hover:bg-[var(--state-hover)] transition-colors">
                                            <span class="flex items-center gap-2 min-w-0">
                                                <button
                                                    v-if="!node.is_leaf"
                                                    type="button"
                                                    class="flex h-5 w-5 items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-default)]"
                                                    @click="toggleExpand(node.id)"
                                                >
                                                    <ChevronDown v-if="expanded[node.id]" class="h-4 w-4" />
                                                    <ChevronRight v-else class="h-4 w-4" />
                                                </button>
                                                <FolderOpen v-else class="h-4 w-4 text-[var(--text-muted)] shrink-0" />
                                                <span class="font-medium text-sm truncate">{{ node.label }}</span>
                                                <Badge variant="muted" class="font-mono text-xs">{{ node.name }}</Badge>
                                                <span v-if="node.url" class="text-xs text-[var(--text-muted)] truncate hidden sm:inline">{{ node.url }}</span>
                                            </span>
                                            <div class="flex items-center gap-1 shrink-0">
                                                <Badge :variant="node.active ? 'success' : 'muted'" class="text-xs">
                                                    {{ node.active ? 'Aktif' : 'Nonaktif' }}
                                                </Badge>
                                                <Button size="icon-xs" variant="ghost" aria-label="Detail" @click="openDetail(node)">
                                                    <Eye class="h-3.5 w-3.5" />
                                                </Button>
                                                <Button size="icon-xs" variant="ghost" aria-label="Ubah" @click="openEdit(node)">
                                                    <Pencil class="h-3.5 w-3.5" />
                                                </Button>
                                                <Button
                                                    size="icon-xs"
                                                    variant="ghost"
                                                    class="text-[var(--status-danger-fg)] hover:bg-[var(--status-danger-bg)]"
                                                    aria-label="Hapus"
                                                    @click="hapus(node.id, node.label)"
                                                >
                                                    <Trash2 class="h-3.5 w-3.5" />
                                                </Button>
                                            </div>
                                        </div>
                                        <ul
                                            v-if="expanded[node.id] && node.children?.length"
                                            class="ml-7 mt-0.5 space-y-0.5 border-l border-[var(--border-subtle)] pl-3"
                                        >
                                            <li
                                                v-for="child in node.children"
                                                :key="child.id"
                                                class="flex items-center justify-between rounded-md px-2 py-1.5 text-sm hover:bg-[var(--state-hover)] transition-colors"
                                            >
                                                <span class="flex items-center gap-2 min-w-0">
                                                    <span class="text-[var(--text-default)] truncate">{{ child.label }}</span>
                                                    <Badge variant="muted" class="font-mono text-xs">{{ child.name }}</Badge>
                                                    <span class="text-xs text-[var(--text-muted)] truncate hidden sm:inline">{{ child.url }}</span>
                                                </span>
                                                <div class="flex items-center gap-1 shrink-0">
                                                    <Button size="icon-xs" variant="ghost" @click="openDetail(child)">
                                                        <Eye class="h-3.5 w-3.5" />
                                                    </Button>
                                                    <Button size="icon-xs" variant="ghost" @click="openEdit(child)">
                                                        <Pencil class="h-3.5 w-3.5" />
                                                    </Button>
                                                    <Button
                                                        size="icon-xs"
                                                        variant="ghost"
                                                        class="text-[var(--status-danger-fg)] hover:bg-[var(--status-danger-bg)]"
                                                        @click="hapus(child.id, child.label)"
                                                    >
                                                        <Trash2 class="h-3.5 w-3.5" />
                                                    </Button>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="groups">
                    <Card>
                        <CardContent>
                            <ul class="divide-y divide-[var(--border-subtle)]">
                                <li v-for="g in tree" :key="g.id" class="flex items-center justify-between py-2.5">
                                    <span>
                                        <span class="font-medium text-sm">{{ g.label }}</span>
                                        <span class="ml-2 text-xs text-[var(--text-muted)] font-mono">{{ g.name }}</span>
                                    </span>
                                    <Badge :variant="g.active ? 'success' : 'muted'">{{ g.active ? 'Aktif' : 'Nonaktif' }}</Badge>
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>

        <!-- Form modal — single source, dipakai untuk create & edit -->
        <FormModal
            v-model="formModal.isOpen.value"
            :title="formModal.data.value ? `Ubah Modul: ${formModal.data.value.label}` : 'Tambah Modul'"
            :description="formModal.data.value ? 'Perbarui detail modul.' : 'Buat modul baru di pohon menu.'"
            size="xl"
            :processing="form.processing"
            @submit="submit"
            @cancel="formModal.close()"
        >
            <ModuleForm :form="form" :groups="groups" :modules="modules" />
        </FormModal>

        <!-- Detail modal -->
        <DetailModal
            v-model="detailModal.isOpen.value"
            :title="detailModal.data.value?.label ?? 'Detail'"
            :description="detailModal.data.value?.name"
            :items="detailModal.data.value ? [
                { label: 'Slug', value: detailModal.data.value.name },
                { label: 'URL', value: detailModal.data.value.url ?? '— container —' },
                { label: 'Route', value: detailModal.data.value.route_name ?? '—' },
                { label: 'Tipe', value: detailModal.data.value.is_leaf ? 'Leaf' : 'Container' },
                { label: 'Urutan', value: detailModal.data.value.order },
                { label: 'Status', value: detailModal.data.value.active ? 'Aktif' : 'Nonaktif' },
            ] : []"
        />
    </AppLayout>
</template>
