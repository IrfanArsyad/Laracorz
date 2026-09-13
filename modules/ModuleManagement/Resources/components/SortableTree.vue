<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import {
    Pencil,
    Trash2,
    Eye,
    GripVertical,
    Plus,
    MoreVertical,
    FolderTree,
    FolderOpen,
    Hash,
    Power,
    PowerOff,
    Users,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/Button';
import { Badge } from '@/components/ui/Badge';
import { Switch } from '@/components/ui/Switch';
import { TableShell } from '@/components/ui/TableShell';
import {
    DropdownMenu,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/DropdownMenu';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { resolveIcon } from '@/lib/icon';

/**
 * Tree + drag&drop table satu kolom untuk Module Management.
 *
 * Cara pakai:
 * - Drag row pakai handle (≡) di kiri
 * - Lepas di atas/bawah row lain → reorder
 * - Lepas DI ATAS row (zone tengah) → jadikan sub-modul dari row tersebut
 * - Lepas di header grup → pindah ke grup tersebut sebagai root
 *
 * Backend menerima bulk update via POST /modules/reorder.
 */

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
    roles_count?: number;
    children: Node[];
    parent_id?: number | null;
    module_group_id?: number | null;
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

const props = defineProps<{ tree: Group[] }>();

const totals = computed(() => {
    let modules = 0;
    function walk(nodes: Node[]): void {
        for (const n of nodes) {
            modules++;
            if (n.children?.length) walk(n.children);
        }
    }
    for (const g of props.tree) walk(g.modules);
    return { groups: props.tree.length, modules };
});

const summary = computed(
    () =>
        `${t('common.showing')} ${totals.value.groups} ${t('modules.statsGroups').toLowerCase()} · ${totals.value.modules} ${t('modules.statsModules').toLowerCase()}`,
);
const emit = defineEmits<{
    edit: [node: Node];
    detail: [node: Node];
    'add-sub': [parentId: number];
    'add-to-group': [groupId: number];
    'edit-group': [group: Group];
    'delete-group': [group: Group];
}>();

const { confirm } = useConfirm();
const toast = useToast();
const { t } = useI18n();

/* ──────────────────────────────────────────────────────────────
 * Flatten tree → flat list dengan info depth + groupId, dipakai
 * untuk render table baris. Tetap interaktif dengan tree ops.
 * ────────────────────────────────────────────────────────────── */

interface FlatRow {
    type: 'group' | 'module';
    id: number;
    depth: number;
    groupId: number; // group container
    parentId: number | null; // parent module (null untuk root)
    node?: Node;
    group?: Group;
}

const flatRows = computed<FlatRow[]>(() => {
    const out: FlatRow[] = [];
    function walk(nodes: Node[], depth: number, groupId: number, parentId: number | null): void {
        for (const n of nodes) {
            out.push({
                type: 'module',
                id: n.id,
                depth,
                groupId,
                parentId,
                node: n,
            });
            if (n.children?.length && expanded.value[n.id] !== false) {
                walk(n.children, depth + 1, groupId, n.id);
            }
        }
    }
    for (const g of props.tree) {
        out.push({
            type: 'group',
            id: g.id,
            depth: 0,
            groupId: g.id,
            parentId: null,
            group: g,
        });
        walk(g.modules, 0, g.id, null);
    }
    return out;
});

// Expanded state — by default semua expand
const expanded = ref<Record<number, boolean>>({});
function toggleExpand(id: number): void {
    expanded.value[id] = expanded.value[id] === undefined ? false : !expanded.value[id];
}
function isExpanded(id: number): boolean {
    return expanded.value[id] !== false;
}

/* ──────────────────────────────────────────────────────────────
 * Drag & drop (native HTML5)
 * ────────────────────────────────────────────────────────────── */

interface DragState {
    sourceId: number | null;
    overId: number | null;
    overZone: 'above' | 'on' | 'below' | null;
    overGroupId: number | null;
}

const drag = ref<DragState>({
    sourceId: null,
    overId: null,
    overZone: null,
    overGroupId: null,
});

function isDragging(id: number): boolean {
    return drag.value.sourceId === id;
}
function dragZone(id: number, type: 'group' | 'module'): string {
    if (drag.value.overGroupId === id && type === 'group') return 'on';
    if (drag.value.overId !== id) return '';
    return drag.value.overZone ?? '';
}

function onDragStart(e: DragEvent, id: number): void {
    drag.value.sourceId = id;
    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(id));
    }
}

function onDragOverModule(e: DragEvent, target: FlatRow): void {
    if (!drag.value.sourceId) return;
    if (drag.value.sourceId === target.id) return;

    e.preventDefault();
    if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';

    const rect = (e.currentTarget as HTMLElement).getBoundingClientRect();
    const offsetY = e.clientY - rect.top;
    const h = rect.height;

    // Zona: 0-30% above | 30-70% on | 70-100% below
    let zone: 'above' | 'on' | 'below';
    if (offsetY < h * 0.3) zone = 'above';
    else if (offsetY > h * 0.7) zone = 'below';
    else zone = 'on';

    // Tidak boleh nest ke leaf yang berisi child... well, leaf node
    // bisa dijadikan container kalau kita drop ke atasnya — itu tidak masuk
    // akal karena leaf punya url. Jadi disabled 'on' untuk leaf.
    if (target.node?.is_leaf && zone === 'on') zone = 'below';

    drag.value.overId = target.id;
    drag.value.overZone = zone;
    drag.value.overGroupId = null;
}

function onDragOverGroup(e: DragEvent, groupId: number): void {
    if (!drag.value.sourceId) return;
    e.preventDefault();
    if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';
    drag.value.overGroupId = groupId;
    drag.value.overId = null;
    drag.value.overZone = null;
}

function clearDrag(): void {
    drag.value = { sourceId: null, overId: null, overZone: null, overGroupId: null };
}

function isAncestor(srcId: number, targetId: number): boolean {
    // src adalah ancestor target?
    function walk(node: Node): boolean {
        if (node.id === targetId) return true;
        for (const c of node.children ?? []) {
            if (walk(c)) return true;
        }
        return false;
    }
    const src = findNode(srcId);
    if (!src) return false;
    return walk(src);
}

function findNode(id: number): Node | null {
    function walk(nodes: Node[]): Node | null {
        for (const n of nodes) {
            if (n.id === id) return n;
            if (n.children?.length) {
                const found = walk(n.children);
                if (found) return found;
            }
        }
        return null;
    }
    for (const g of props.tree) {
        const found = walk(g.modules);
        if (found) return found;
    }
    return null;
}

function findTarget(id: number): FlatRow | null {
    return flatRows.value.find((r) => r.id === id && r.type === 'module') ?? null;
}

async function onDrop(): Promise<void> {
    const src = drag.value.sourceId;
    if (!src) return clearDrag();

    // Drop ke grup header
    if (drag.value.overGroupId) {
        await commitMove(src, {
            parentId: null,
            groupId: drag.value.overGroupId,
            position: 'last',
        });
        clearDrag();
        return;
    }

    if (!drag.value.overId || !drag.value.overZone) {
        clearDrag();
        return;
    }
    const target = findTarget(drag.value.overId);
    if (!target) return clearDrag();

    // Tidak boleh drop ke descendant sendiri
    if (isAncestor(src, target.id) && src !== target.id) {
        toast.warning(t('modules.toast.cantNestInDescendant'));
        clearDrag();
        return;
    }
    if (src === target.id) return clearDrag();

    const zone = drag.value.overZone;
    if (zone === 'on') {
        // Jadikan child target
        await commitMove(src, {
            parentId: target.id,
            groupId: target.groupId,
            position: 'last',
        });
    } else {
        // Above/below target — sibling
        await commitMove(src, {
            parentId: target.parentId,
            groupId: target.groupId,
            position: zone,
            relativeId: target.id,
        });
    }
    clearDrag();
}

/**
 * Build payload baru lalu kirim ke backend.
 */
async function commitMove(
    sourceId: number,
    opts: {
        parentId: number | null;
        groupId: number;
        position: 'above' | 'below' | 'last';
        relativeId?: number;
    },
): Promise<void> {
    // Bangun list flat target — semua modules dalam tree (tetap), lalu
    // pindah source ke posisi baru, lalu resequence.

    // Kumpulkan semua module entry [{id, parent_id, module_group_id}]
    const list: Array<{ id: number; parent_id: number | null; module_group_id: number | null }> = [];
    function collect(nodes: Node[], gId: number, pId: number | null): void {
        for (const n of nodes) {
            list.push({ id: n.id, parent_id: pId, module_group_id: pId ? null : gId });
            if (n.children?.length) collect(n.children, gId, n.id);
        }
    }
    for (const g of props.tree) collect(g.modules, g.id, null);

    // Hapus source dari list
    const sourceItem = list.find((x) => x.id === sourceId);
    if (!sourceItem) return;
    const filtered = list.filter((x) => x.id !== sourceId);

    // Tentukan posisi insert
    let insertAt = filtered.length;
    if (opts.position === 'above' && opts.relativeId !== undefined) {
        insertAt = filtered.findIndex((x) => x.id === opts.relativeId);
    } else if (opts.position === 'below' && opts.relativeId !== undefined) {
        const idx = filtered.findIndex((x) => x.id === opts.relativeId);
        insertAt = idx === -1 ? filtered.length : idx + 1;
    } else if (opts.position === 'last') {
        // sesama parent — taruh paling bawah di antara siblings
        // tetap di insertAt = filtered.length, urutan akan di-recompute via order
        insertAt = filtered.length;
    }

    sourceItem.parent_id = opts.parentId;
    sourceItem.module_group_id = opts.parentId ? null : opts.groupId;
    filtered.splice(insertAt, 0, sourceItem);

    // Re-sequence order per parent/group bucket
    const bucketOrder = new Map<string, number>();
    const items = filtered.map((x) => {
        const bucketKey = `${x.parent_id ?? 'root'}:${x.module_group_id ?? '-'}`;
        const ord = bucketOrder.get(bucketKey) ?? 0;
        bucketOrder.set(bucketKey, ord + 1);
        return { ...x, order: ord };
    });

    router.post(
        '/modules/reorder',
        { items },
        {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => toast.success(t('modules.toast.structureUpdated')),
            onError: (errors) => {
                const msg = (Object.values(errors)[0] as string) || t('modules.toast.structureUpdateFailed');
                toast.error(msg);
            },
        },
    );
}

/* ──────────────────────────────────────────────────────────────
 * Aksi inline
 * ────────────────────────────────────────────────────────────── */

function toggleActive(node: Node): void {
    router.patch(
        `/modules/${node.id}/toggle`,
        {},
        {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () =>
                toast.success(
                    t('modules.toast.toggleSuccess', {
                        label: node.label,
                        state: node.active ? t('common.inactive') : t('common.active'),
                    }),
                ),
        },
    );
}

async function deleteModule(node: Node): Promise<void> {
    const ok = await confirm({
        title: t('modules.deleteModule') + '?',
        message: t('modules.deleteModuleConfirm', { label: node.label }),
        variant: 'destructive',
        confirmLabel: t('common.delete'),
    });
    if (!ok) return;
    router.delete(`/modules/${node.id}`, { preserveScroll: true });
}
</script>

<template>
    <TableShell :summary="summary">
        <!-- Table header -->
        <div class="grid grid-cols-[28px_minmax(0,1fr)_120px_90px_80px_60px_40px] gap-2 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] bg-[var(--surface-sunken)] border-b border-[var(--border-subtle)]">
            <div class="text-center"><GripVertical class="h-3 w-3 inline opacity-50" /></div>
            <div>{{ t('modules.table.module') }}</div>
            <div>{{ t('modules.table.slug') }}</div>
            <div>{{ t('modules.table.type') }}</div>
            <div class="text-center">{{ t('modules.table.role') }}</div>
            <div class="text-center">{{ t('modules.table.active') }}</div>
            <div></div>
        </div>

        <!-- Table body -->
        <ul class="divide-y divide-[var(--border-subtle)]">
            <li
                v-for="row in flatRows"
                :key="`${row.type}-${row.id}`"
                @dragend="clearDrag"
            >
                <!-- GROUP HEADER ROW -->
                <div
                    v-if="row.type === 'group' && row.group"
                    class="grid grid-cols-[28px_minmax(0,1fr)_120px_90px_80px_60px_40px] gap-2 px-3 py-3 transition-colors"
                    :class="[
                        'bg-[var(--surface-sunken)]/40',
                        dragZone(row.id, 'group') === 'on' ? 'ring-2 ring-[var(--brand-bg)] ring-inset' : '',
                    ]"
                    @dragover="onDragOverGroup($event, row.id)"
                    @drop="onDrop"
                >
                    <div></div>
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-[var(--brand-soft-bg)] text-[var(--brand-soft-fg)]">
                            <component :is="resolveIcon(row.group.icon, FolderTree)" class="h-3.5 w-3.5" />
                        </div>
                        <span class="text-sm font-semibold text-[var(--text-strong)] truncate">{{ row.group.label }}</span>
                        <Badge variant="muted" class="font-mono text-xs">{{ row.group.name }}</Badge>
                        <Badge v-if="!row.group.active" variant="warning">{{ t('common.inactive') }}</Badge>
                    </div>
                    <div class="text-xs text-[var(--text-muted)] truncate self-center">{{ t('modules.table.moduleCount', { count: row.group.modules.length }) }}</div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div class="flex items-center justify-end gap-1">
                        <Button size="icon-xs" variant="ghost" :aria-label="t('modules.addModule')" @click="emit('add-to-group', row.group.id)">
                            <Plus class="h-3.5 w-3.5" />
                        </Button>
                        <DropdownMenu align="end">
                            <template #trigger>
                                <Button size="icon-xs" variant="ghost" :aria-label="t('common.more')">
                                    <MoreVertical class="h-3.5 w-3.5" />
                                </Button>
                            </template>
                            <DropdownMenuLabel>{{ row.group.label }}</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @click="emit('edit-group', row.group)">
                                <Pencil class="h-3.5 w-3.5" /> {{ t('modules.editGroup') }}
                            </DropdownMenuItem>
                            <DropdownMenuItem variant="destructive" @click="emit('delete-group', row.group)">
                                <Trash2 class="h-3.5 w-3.5" /> {{ t('modules.deleteGroup') }}
                            </DropdownMenuItem>
                        </DropdownMenu>
                    </div>
                </div>

                <!-- MODULE ROW -->
                <div
                    v-else-if="row.type === 'module' && row.node"
                    draggable="true"
                    class="relative grid grid-cols-[28px_minmax(0,1fr)_120px_90px_80px_60px_40px] gap-2 px-3 py-2.5 transition-colors group"
                    :class="[
                        'hover:bg-[var(--state-hover)]',
                        isDragging(row.id) ? 'opacity-40' : '',
                        dragZone(row.id, 'module') === 'on' ? 'bg-[var(--brand-soft-bg)]/40' : '',
                    ]"
                    @dragstart="onDragStart($event, row.id)"
                    @dragover="onDragOverModule($event, row)"
                    @drop="onDrop"
                >
                    <!-- Drop indicator line above -->
                    <div
                        v-if="dragZone(row.id, 'module') === 'above'"
                        class="absolute -top-px left-0 right-0 h-0.5 bg-[var(--brand-bg)] z-10"
                    />
                    <!-- Drop indicator line below -->
                    <div
                        v-if="dragZone(row.id, 'module') === 'below'"
                        class="absolute -bottom-px left-0 right-0 h-0.5 bg-[var(--brand-bg)] z-10"
                    />

                    <!-- Drag handle -->
                    <div class="flex items-center justify-center cursor-grab active:cursor-grabbing text-[var(--text-muted)] group-hover:text-[var(--text-default)]">
                        <GripVertical class="h-3.5 w-3.5" />
                    </div>

                    <!-- Label + icon (indented by depth) -->
                    <div class="flex items-center gap-2 min-w-0" :style="{ paddingLeft: row.depth * 20 + 'px' }">
                        <button
                            v-if="!row.node.is_leaf && row.node.children.length"
                            type="button"
                            class="flex h-4 w-4 items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-default)] shrink-0"
                            :aria-label="isExpanded(row.id) ? t('sidebar.collapse') : t('sidebar.expand')"
                            @click="toggleExpand(row.id)"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="12"
                                height="12"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                :class="[
                                    'transition-transform',
                                    isExpanded(row.id) ? 'rotate-90' : '',
                                ]"
                            >
                                <path d="m9 18 6-6-6-6" />
                            </svg>
                        </button>
                        <span v-else class="w-4 shrink-0" />

                        <component
                            :is="resolveIcon(row.node.icon, row.node.is_leaf ? Hash : FolderOpen)"
                            :class="[
                                'h-4 w-4 shrink-0',
                                row.node.is_leaf ? 'text-[var(--brand-soft-fg)]' : 'text-[var(--text-muted)]',
                            ]"
                        />
                        <span class="text-sm font-medium text-[var(--text-strong)] truncate">{{ row.node.label }}</span>
                        <span v-if="row.node.url" class="text-xs text-[var(--text-muted)] truncate font-mono hidden md:inline">
                            {{ row.node.url }}
                        </span>
                    </div>

                    <!-- Slug -->
                    <div class="text-xs font-mono text-[var(--text-muted)] truncate self-center">{{ row.node.name }}</div>

                    <!-- Type badge -->
                    <div class="self-center">
                        <Badge :variant="row.node.is_leaf ? 'info' : 'secondary'" class="text-xs">
                            {{ row.node.is_leaf ? t('modules.typeLeaf') : t('modules.typeContainer') }}
                        </Badge>
                    </div>

                    <!-- Role count -->
                    <div class="text-center text-xs text-[var(--text-muted)] self-center">
                        <span v-if="(row.node.roles_count ?? 0) > 0" class="inline-flex items-center gap-1 tabular-nums">
                            <Users class="h-3 w-3" />
                            {{ row.node.roles_count }}
                        </span>
                        <span v-else>—</span>
                    </div>

                    <!-- Active toggle inline -->
                    <div class="flex items-center justify-center self-center" @click.stop>
                        <Switch
                            :model-value="row.node.active"
                            @update:model-value="() => toggleActive(row.node!)"
                        />
                    </div>

                    <!-- Action dropdown -->
                    <div class="flex items-center justify-end self-center">
                        <DropdownMenu align="end">
                            <template #trigger>
                                <Button size="icon-xs" variant="ghost" :aria-label="t('common.actions')">
                                    <MoreVertical class="h-3.5 w-3.5" />
                                </Button>
                            </template>
                            <DropdownMenuItem @click="emit('detail', row.node!)">
                                <Eye class="h-3.5 w-3.5" /> {{ t('common.detail') }}
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="emit('edit', row.node!)">
                                <Pencil class="h-3.5 w-3.5" /> {{ t('common.edit') }}
                            </DropdownMenuItem>
                            <DropdownMenuItem v-if="!row.node.is_leaf" @click="emit('add-sub', row.node!.id)">
                                <Plus class="h-3.5 w-3.5" /> {{ t('modules.addSubmodule') }}
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="toggleActive(row.node!)">
                                <Power v-if="!row.node.active" class="h-3.5 w-3.5" />
                                <PowerOff v-else class="h-3.5 w-3.5" />
                                {{ row.node.active ? t('common.inactive') : t('common.active') }}
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem variant="destructive" @click="deleteModule(row.node!)">
                                <Trash2 class="h-3.5 w-3.5" /> {{ t('common.delete') }}
                            </DropdownMenuItem>
                        </DropdownMenu>
                    </div>
                </div>
            </li>
        </ul>

        <!-- Empty state -->
        <div v-if="tree.length === 0" class="p-8 text-center text-sm text-[var(--text-muted)]">
            {{ t('modules.table.emptyTable') }}
        </div>
    </TableShell>
</template>
