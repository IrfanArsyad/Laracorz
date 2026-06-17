<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import {
    Plus,
    FolderTree,
    Layers,
    Hash,
    Activity,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Button } from '@/components/ui/Button';
import StatCard from '@/components/ui/StatCard/StatCard.vue';
import SortableTree from './components/SortableTree.vue';
import ModuleModal from './components/module-modal.vue';
import GroupModal from './components/group-modal.vue';
import DetailNodeModal from './components/detail-modal.vue';
import { useConfirm } from '@/composables/useConfirm';
import type { Node, Group } from './types';

const { t } = useI18n();

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

// Modal: Modul (form & submit di components/module-modal.vue)
const moduleModalOpen = ref(false);
const editingModule = ref<Node | null>(null);
const modulePresetGroup = ref<number | null>(null);
const modulePresetParent = ref<number | null>(null);

function openCreateModule(presetGroupId?: number, presetParentId?: number): void {
    editingModule.value = null;
    modulePresetGroup.value = presetGroupId ?? null;
    modulePresetParent.value = presetParentId ?? null;
    moduleModalOpen.value = true;
}

function openEditModule(node: Node): void {
    editingModule.value = node;
    modulePresetGroup.value = null;
    modulePresetParent.value = null;
    moduleModalOpen.value = true;
}

// Modal: Grup (form & submit di components/group-modal.vue)
const groupModalOpen = ref(false);
const editingGroup = ref<Group | null>(null);

function openCreateGroup(): void {
    editingGroup.value = null;
    groupModalOpen.value = true;
}

function openEditGroup(group: Group): void {
    editingGroup.value = group;
    groupModalOpen.value = true;
}

async function deleteGroup(group: Group): Promise<void> {
    if (group.modules?.length) {
        await confirm({
            title: t('modules.groupHasModules'),
            message: t('modules.groupHasModulesMessage', { label: group.label, count: group.modules.length }),
            confirmLabel: t('common.ok'),
            cancelLabel: '',
        });
        return;
    }
    const ok = await confirm({
        title: t('modules.deleteGroup') + '?',
        message: t('modules.deleteGroupConfirm', { label: group.label }),
        variant: 'destructive',
        confirmLabel: t('common.delete'),
    });
    if (!ok) return;
    router.delete(`/modules/groups/${group.id}`, { preserveScroll: true });
}

// Detail modal — read-only (konten di components/detail-modal.vue)
const detailOpen = ref(false);
const detailNode = ref<Node | null>(null);

function openDetail(node: Node): void {
    detailNode.value = node;
    detailOpen.value = true;
}
</script>

<template>
    <Head :title="t('modules.title')" />
    <AppLayout>
        <div class="space-y-5">
            <PageHeader
                :title="t('modules.title')"
                :description="t('modules.description')"
                :breadcrumbs="[{ label: t('modules.breadcrumbRoot') }, { label: t('modules.breadcrumb') }]"
            >
                <template #actions>
                    <Button variant="outline" @click="openCreateGroup">
                        <FolderTree class="h-4 w-4" /> {{ t('modules.addGroup') }}
                    </Button>
                    <Button @click="() => openCreateModule()">
                        <Plus class="h-4 w-4" /> {{ t('modules.addModule') }}
                    </Button>
                </template>
            </PageHeader>

            <!-- KPI ringkas -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <StatCard :label="t('modules.statsGroups')" :value="stats.groups" :icon="FolderTree" />
                <StatCard :label="t('modules.statsModules')" :value="stats.totalModules" :icon="Layers" />
                <StatCard :label="t('modules.statsLeaves')" :value="stats.leafCount" :icon="Hash" />
                <StatCard :label="t('modules.statsInactive')" :value="stats.inactiveCount" :icon="Activity" />
            </div>

            <!-- Sortable tree table — drag to reorder / nest -->
            <SortableTree
                v-if="tree.length > 0"
                :tree="tree"
                @edit="openEditModule"
                @detail="openDetail"
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
                <h3 class="text-base font-semibold text-[var(--text-strong)]">{{ t('modules.emptyTitle') }}</h3>
                <p class="text-sm text-[var(--text-muted)] mt-1">
                    {{ t('modules.emptyDescription') }}
                </p>
                <Button class="mt-4" @click="openCreateGroup">
                    <Plus class="h-4 w-4" /> {{ t('modules.addFirstGroup') }}
                </Button>
            </div>
        </div>

        <!-- Modal: Modul / Grup / Detail (konten di components/) -->
        <ModuleModal
            v-model="moduleModalOpen"
            :node="editingModule"
            :groups="groups"
            :modules="modules"
            :preset-group-id="modulePresetGroup"
            :preset-parent-id="modulePresetParent"
        />
        <GroupModal v-model="groupModalOpen" :group="editingGroup" />
        <DetailNodeModal v-model="detailOpen" :node="detailNode" />
    </AppLayout>
</template>
