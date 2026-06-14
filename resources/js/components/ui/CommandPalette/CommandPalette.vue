<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import Modal from '../Modal/Modal.vue';
import type { MenuGroup, ModuleNode } from '@/types';

const props = defineProps<{ modelValue: boolean }>();
const emit = defineEmits<{ 'update:modelValue': [v: boolean] }>();

const page = usePage();
const query = ref('');
const activeIndex = ref(0);

const allLeaves = computed<Array<ModuleNode & { group: string }>>(() => {
    const out: Array<ModuleNode & { group: string }> = [];
    function walk(nodes: ModuleNode[], group: string): void {
        for (const n of nodes) {
            if (n.url || n.route_name) out.push({ ...n, group });
            if (n.children?.length) walk(n.children, group);
        }
    }
    const menu: MenuGroup[] = (page.props.menu as MenuGroup[]) ?? [];
    for (const g of menu) walk(g.modules, g.label);
    return out;
});

const filtered = computed(() => {
    const q = query.value.toLowerCase().trim();
    if (!q) return allLeaves.value;
    return allLeaves.value.filter((n) => n.label.toLowerCase().includes(q) || n.name.toLowerCase().includes(q));
});

watch(query, () => (activeIndex.value = 0));
watch(
    () => props.modelValue,
    (v) => {
        if (v) {
            query.value = '';
            activeIndex.value = 0;
        }
    },
);

function go(node: ModuleNode & { group: string }): void {
    if (node.url) router.visit(node.url);
    emit('update:modelValue', false);
}

function onKey(e: KeyboardEvent): void {
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex.value = Math.min(filtered.value.length - 1, activeIndex.value + 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex.value = Math.max(0, activeIndex.value - 1);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const item = filtered.value[activeIndex.value];
        if (item) go(item);
    }
}
</script>

<template>
    <Modal :model-value="modelValue" size="lg" @update:model-value="(v) => emit('update:modelValue', v)">
        <template #header>
            <div class="flex items-center gap-2">
                <Search class="h-4 w-4 text-muted-foreground" />
                <input
                    v-model="query"
                    autofocus
                    placeholder="Ketik untuk mencari menu..."
                    class="flex-1 bg-transparent outline-none text-sm placeholder:text-muted-foreground"
                    @keydown="onKey"
                />
            </div>
        </template>
        <ul class="max-h-80 overflow-y-auto">
            <li
                v-for="(item, idx) in filtered"
                :key="item.id"
                :class="[
                    'flex items-center justify-between px-3 py-2 text-sm cursor-pointer rounded',
                    idx === activeIndex ? 'bg-accent text-accent-foreground' : 'hover:bg-accent/60',
                ]"
                @click="go(item)"
                @mousemove="activeIndex = idx"
            >
                <span class="flex items-center gap-2">
                    <span class="font-medium">{{ item.label }}</span>
                    <span class="text-xs text-muted-foreground">{{ item.group }}</span>
                </span>
                <span class="text-xs text-muted-foreground">{{ item.url }}</span>
            </li>
            <li v-if="filtered.length === 0" class="px-3 py-6 text-center text-sm text-muted-foreground">
                Tidak ada hasil
            </li>
        </ul>
    </Modal>
</template>
