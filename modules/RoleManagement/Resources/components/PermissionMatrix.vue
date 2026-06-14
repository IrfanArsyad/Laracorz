<script setup lang="ts">
import { computed } from 'vue';
import { Checkbox } from '@/components/ui/Checkbox';
import { Button } from '@/components/ui/Button';

type Action = 'read' | 'create' | 'update' | 'delete';

interface Node {
    id: number;
    name: string;
    label: string;
    is_leaf: boolean;
    extra_actions: string[];
    children: Node[];
}

interface Group {
    id: number;
    name: string;
    label: string;
    modules: Node[];
}

const props = defineProps<{
    matrix: Group[];
    modelRead: Array<number | string>;
    modelCreate: Array<number | string>;
    modelUpdate: Array<number | string>;
    modelDelete: Array<number | string>;
    modelExtra: Record<string, string[]>;
}>();

const emit = defineEmits<{
    'update:modelRead': [v: Array<number | string>];
    'update:modelCreate': [v: Array<number | string>];
    'update:modelUpdate': [v: Array<number | string>];
    'update:modelDelete': [v: Array<number | string>];
    'update:modelExtra': [v: Record<string, string[]>];
}>();

const ACTIONS: Action[] = ['read', 'create', 'update', 'delete'];

const leaves = computed<Node[]>(() => {
    const out: Node[] = [];
    function walk(nodes: Node[]): void {
        for (const n of nodes) {
            if (n.is_leaf) out.push(n);
            if (n.children?.length) walk(n.children);
        }
    }
    for (const g of props.matrix) walk(g.modules);
    return out;
});

function getList(action: Action): Array<number | string> {
    const v = action === 'read'
        ? props.modelRead
        : action === 'create'
          ? props.modelCreate
          : action === 'update'
            ? props.modelUpdate
            : props.modelDelete;
    return Array.isArray(v) ? v : [];
}

function setList(action: Action, v: Array<number | string>): void {
    const e =
        action === 'read'
            ? 'update:modelRead'
            : action === 'create'
              ? 'update:modelCreate'
              : action === 'update'
                ? 'update:modelUpdate'
                : 'update:modelDelete';
    emit(e as 'update:modelRead', v);
}

function isWildcard(action: Action): boolean {
    return getList(action).includes('*');
}

function isChecked(action: Action, id: number): boolean {
    const list = getList(action);
    return list.includes('*') || list.includes(id) || list.map(Number).includes(id);
}

function toggle(action: Action, id: number): void {
    const list = getList(action).filter((v) => v !== '*').map(Number);
    if (list.includes(id)) {
        setList(action, list.filter((v) => v !== id));
    } else {
        setList(action, [...list, id]);
    }
}

function toggleRow(node: Node): void {
    const allChecked = ACTIONS.every((a) => isChecked(a, node.id));
    for (const a of ACTIONS) {
        if (allChecked) {
            setList(a, getList(a).filter((v) => v !== '*' && Number(v) !== node.id));
        } else if (!isChecked(a, node.id)) {
            const list = getList(a).filter((v) => v !== '*').map(Number);
            setList(a, [...list, node.id]);
        }
    }
}

function toggleColumn(action: Action): void {
    const allIds = leaves.value.map((l) => l.id);
    const allChecked = allIds.every((id) => isChecked(action, id));
    setList(action, allChecked ? [] : allIds);
}

function setSuperAdmin(): void {
    for (const a of ACTIONS) setList(a, ['*']);
}

function clearAll(): void {
    for (const a of ACTIONS) setList(a, []);
}

function toggleExtra(moduleName: string, ext: string): void {
    const extra = { ...props.modelExtra };
    const list = extra[moduleName] ?? [];
    extra[moduleName] = list.includes(ext) ? list.filter((v) => v !== ext) : [...list, ext];
    if (extra[moduleName].length === 0) delete extra[moduleName];
    emit('update:modelExtra', extra);
}

function hasExtra(moduleName: string, ext: string): boolean {
    return (props.modelExtra[moduleName] ?? []).includes(ext);
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-end gap-2">
            <Button type="button" size="sm" variant="outline" @click="clearAll">Bersihkan</Button>
            <Button type="button" size="sm" variant="secondary" @click="setSuperAdmin">Super Admin (*)</Button>
        </div>

        <div class="rounded-lg border border-border overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-muted/40">
                    <tr>
                        <th class="px-3 py-2 text-left">Modul</th>
                        <th v-for="a in ACTIONS" :key="a" class="px-3 py-2 text-center capitalize">
                            <button type="button" class="hover:underline" @click="toggleColumn(a)">{{ a }}</button>
                        </th>
                        <th class="px-3 py-2 text-right">Extra</th>
                        <th class="px-3 py-2 text-center">Semua</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <template v-for="group in matrix" :key="group.id">
                        <tr class="bg-accent/40">
                            <td colspan="7" class="px-3 py-2 text-xs uppercase tracking-wider text-muted-foreground">
                                {{ group.label }}
                            </td>
                        </tr>
                        <template v-for="node in group.modules" :key="node.id">
                            <template v-if="node.is_leaf">
                                <tr>
                                    <td class="px-3 py-2">{{ node.label }}</td>
                                    <td v-for="a in ACTIONS" :key="a" class="px-3 py-2 text-center">
                                        <Checkbox :model-value="isChecked(a, node.id)" @update:model-value="toggle(a, node.id)" />
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex flex-wrap gap-1 justify-end">
                                            <button
                                                v-for="ext in node.extra_actions"
                                                :key="ext"
                                                type="button"
                                                :class="[
                                                    'rounded border px-2 py-0.5 text-xs',
                                                    hasExtra(node.name, ext)
                                                        ? 'bg-primary text-primary-foreground border-primary'
                                                        : 'border-border',
                                                ]"
                                                @click="toggleExtra(node.name, ext)"
                                            >
                                                {{ ext }}
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <Checkbox
                                            :model-value="ACTIONS.every((a) => isChecked(a, node.id))"
                                            @update:model-value="toggleRow(node)"
                                        />
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr class="bg-muted/20">
                                    <td colspan="7" class="px-3 py-1.5 text-xs font-medium pl-6">
                                        {{ node.label }}
                                    </td>
                                </tr>
                                <tr v-for="child in node.children.filter((c) => c.is_leaf)" :key="child.id">
                                    <td class="px-3 py-2 pl-10">{{ child.label }}</td>
                                    <td v-for="a in ACTIONS" :key="a" class="px-3 py-2 text-center">
                                        <Checkbox :model-value="isChecked(a, child.id)" @update:model-value="toggle(a, child.id)" />
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex flex-wrap gap-1 justify-end">
                                            <button
                                                v-for="ext in child.extra_actions"
                                                :key="ext"
                                                type="button"
                                                :class="[
                                                    'rounded border px-2 py-0.5 text-xs',
                                                    hasExtra(child.name, ext)
                                                        ? 'bg-primary text-primary-foreground border-primary'
                                                        : 'border-border',
                                                ]"
                                                @click="toggleExtra(child.name, ext)"
                                            >
                                                {{ ext }}
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <Checkbox
                                            :model-value="ACTIONS.every((a) => isChecked(a, child.id))"
                                            @update:model-value="toggleRow(child)"
                                        />
                                    </td>
                                </tr>
                            </template>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
