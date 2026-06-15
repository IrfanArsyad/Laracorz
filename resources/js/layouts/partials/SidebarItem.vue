<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight, Folder } from 'lucide-vue-next';
import * as Icons from 'lucide-vue-next';
import type { ModuleNode } from '@/types';
import { cn } from '@/lib/utils';

const props = defineProps<{ node: ModuleNode; collapsed: boolean; depth?: number }>();
const open = ref(false);
const page = usePage();

const currentPath = computed(() => (page.url ?? ''));

const isActive = computed(() => {
    if (!props.node.url) return false;
    return currentPath.value === props.node.url || currentPath.value.startsWith(props.node.url + '/');
});

/**
 * Resolve string icon (mis. "users", "shield-check") ke komponen Lucide.
 * Lucide icon adalah hasil defineComponent() — bentuknya object dengan property
 * 'name' yang sama dengan PascalCase key. Pakai check ini supaya tidak salah
 * resolve ke helper/utility export dari namespace (mis. `createLucideIcon`,
 * `icons`, `default`) yang bukan komponen.
 */
const Icon = computed(() => {
    if (!props.node.icon) return Folder;
    const key = props.node.icon
        .split('-')
        .map((p) => p.charAt(0).toUpperCase() + p.slice(1))
        .join('');
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const candidate = (Icons as any)[key];
    // Lucide icons return dari defineComponent yang punya field `name`
    // matching nama icon — pakai itu sebagai signature pengecekan
    return candidate && typeof candidate === 'object' && 'name' in candidate
        ? candidate
        : Folder;
});

const isLeaf = computed(() => !!props.node.url);
const padLeft = computed(() => (props.depth ?? 0) * 16);
</script>

<template>
    <li>
        <Link
            v-if="isLeaf"
            :href="node.url!"
            :prefetch="'hover'"
            :class="
                cn(
                    'group relative flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium',
                    'transition-[background-color,color] duration-[var(--duration-fast)] ease-[var(--ease-out)]',
                    isActive
                        ? 'bg-[var(--sidebar-item-active-bg)] text-[var(--sidebar-item-active-fg)] before:absolute before:left-0 before:top-2 before:bottom-2 before:w-[3px] before:rounded-r-full before:bg-[var(--brand-bg)]'
                        : 'text-[var(--sidebar-fg)] hover:bg-[var(--sidebar-item-hover)] hover:text-[var(--text-strong)]',
                )
            "
            :style="{ paddingLeft: padLeft + 12 + 'px' }"
        >
            <component :is="Icon" class="h-[18px] w-[18px] shrink-0" />
            <span v-if="!collapsed" class="truncate">{{ node.label }}</span>
        </Link>
        <button
            v-else
            type="button"
            :class="
                cn(
                    'group flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-[var(--sidebar-fg)]',
                    'transition-[background-color,color] duration-[var(--duration-fast)] ease-[var(--ease-out)]',
                    'hover:bg-[var(--sidebar-item-hover)] hover:text-[var(--text-strong)]',
                )
            "
            :style="{ paddingLeft: padLeft + 12 + 'px' }"
            @click="open = !open"
        >
            <component :is="Icon" class="h-[18px] w-[18px] shrink-0" />
            <span v-if="!collapsed" class="truncate flex-1 text-left">{{ node.label }}</span>
            <ChevronRight
                v-if="!collapsed"
                :class="cn('h-3.5 w-3.5 transition-transform', open ? 'rotate-90' : '')"
            />
        </button>
        <ul v-if="!isLeaf && open && node.children?.length" class="mt-0.5 space-y-0.5">
            <SidebarItem
                v-for="child in node.children"
                :key="child.id"
                :node="child"
                :collapsed="collapsed"
                :depth="(depth ?? 0) + 1"
            />
        </ul>
    </li>
</template>
