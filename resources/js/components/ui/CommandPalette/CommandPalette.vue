<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Search, ArrowRight, Clock, Zap, FolderTree, Folder } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import Modal from '../Modal/Modal.vue';
import { Kbd } from '../Kbd';
import { resolveIcon } from '@/lib/icon';
import { useNavLabel } from '@/composables/useNavLabel';
import type { MenuGroup, ModuleNode } from '@/types';

const props = defineProps<{ modelValue: boolean }>();
const emit = defineEmits<{ 'update:modelValue': [v: boolean] }>();

const page = usePage();
const { t } = useI18n();
const { moduleLabel, groupLabel } = useNavLabel();

const query = ref('');
const activeIndex = ref(0);
const inputRef = ref<HTMLInputElement | null>(null);

/* ───── Item types: page (sidebar leaf), action (quick command), recent ───── */

interface PaletteItem {
    id: string;
    kind: 'page' | 'action' | 'recent';
    label: string;
    description?: string;
    group?: string;
    icon?: unknown;
    href?: string;
    onSelect?: () => void;
    keywords?: string;
}

/* ───── 1. Sidebar leaves (pages user bisa akses) ───── */

const pageItems = computed<PaletteItem[]>(() => {
    const out: PaletteItem[] = [];
    function walk(nodes: ModuleNode[], groupName: string, groupRawName?: string): void {
        for (const n of nodes) {
            if (n.url || n.route_name) {
                const label = moduleLabel(n.name, n.label);
                out.push({
                    id: `page-${n.id}`,
                    kind: 'page',
                    label,
                    group: groupName,
                    icon: resolveIcon(n.icon, Folder),
                    href: n.url ?? undefined,
                    keywords: `${label} ${n.name} ${groupName} ${groupRawName ?? ''}`.toLowerCase(),
                });
            }
            if (n.children?.length) walk(n.children, groupName, groupRawName);
        }
    }
    const menu: MenuGroup[] = (page.props.menu as MenuGroup[]) ?? [];
    for (const g of menu) {
        const label = groupLabel(g.name, g.label);
        walk(g.modules, label, g.name);
    }
    return out;
});

/* ───── 2. Quick actions ───── */

const actionItems = computed<PaletteItem[]>(() => [
    {
        id: 'act-add-user',
        kind: 'action',
        label: t('users.create'),
        description: t('users.title'),
        icon: resolveIcon('user', Folder),
        href: '/users?action=create',
        keywords: `add user create user ${t('users.create').toLowerCase()}`,
    },
    {
        id: 'act-add-role',
        kind: 'action',
        label: t('roles.create'),
        description: t('roles.title'),
        icon: resolveIcon('shield-check', Folder),
        href: '/roles?action=create',
        keywords: `add role create role ${t('roles.create').toLowerCase()}`,
    },
    {
        id: 'act-add-module',
        kind: 'action',
        label: t('modules.addModule'),
        description: t('modules.title'),
        icon: resolveIcon('puzzle', Folder),
        href: '/modules?action=create',
        keywords: `add module ${t('modules.addModule').toLowerCase()}`,
    },
]);

/* ───── 3. Recent visits — disimpan di localStorage ───── */

const RECENT_KEY = 'laracorz.recent-paths';
const recentItems = ref<PaletteItem[]>([]);

function loadRecent(): void {
    try {
        const raw = localStorage.getItem(RECENT_KEY);
        const arr = raw ? (JSON.parse(raw) as Array<{ path: string; label: string }>) : [];
        recentItems.value = arr.slice(0, 5).map((r, i) => ({
            id: `recent-${i}`,
            kind: 'recent',
            label: r.label,
            description: r.path,
            icon: Clock,
            href: r.path,
            keywords: `${r.label} ${r.path}`.toLowerCase(),
        }));
    } catch {
        recentItems.value = [];
    }
}

function pushRecent(label: string, path: string): void {
    if (!path || path === '/') return;
    try {
        const raw = localStorage.getItem(RECENT_KEY);
        const arr = raw ? (JSON.parse(raw) as Array<{ path: string; label: string }>) : [];
        const next = [{ path, label }, ...arr.filter((r) => r.path !== path)].slice(0, 10);
        localStorage.setItem(RECENT_KEY, JSON.stringify(next));
    } catch {
        /* noop */
    }
}

/* ───── Filtering: ranked by where the match appears ───── */

function score(item: PaletteItem, q: string): number {
    if (!q) return 0;
    const label = item.label.toLowerCase();
    const kw = item.keywords ?? label;
    if (label === q) return 100;
    if (label.startsWith(q)) return 80;
    if (label.includes(q)) return 60;
    if (kw.includes(q)) return 40;
    return 0;
}

const filtered = computed<Array<{ heading?: string; items: PaletteItem[] }>>(() => {
    const q = query.value.toLowerCase().trim();

    if (!q) {
        // Default tampilan: Recent (kalau ada) → Pages → Actions
        const sections: Array<{ heading: string; items: PaletteItem[] }> = [];
        if (recentItems.value.length) sections.push({ heading: t('palette.recent'), items: recentItems.value });
        sections.push({ heading: t('palette.pages'), items: pageItems.value });
        sections.push({ heading: t('palette.actions'), items: actionItems.value });
        return sections.filter((s) => s.items.length > 0);
    }

    const all = [...pageItems.value, ...actionItems.value];
    const ranked = all
        .map((item) => ({ item, score: score(item, q) }))
        .filter((x) => x.score > 0)
        .sort((a, b) => b.score - a.score)
        .map((x) => x.item);

    return ranked.length ? [{ items: ranked }] : [];
});

const flatItems = computed<PaletteItem[]>(() => filtered.value.flatMap((s) => s.items));

watch(query, () => (activeIndex.value = 0));
watch(
    () => props.modelValue,
    async (open) => {
        if (open) {
            query.value = '';
            activeIndex.value = 0;
            loadRecent();
            await nextTick();
            inputRef.value?.focus();
        }
    },
);

function go(item: PaletteItem): void {
    if (item.onSelect) {
        item.onSelect();
    } else if (item.href) {
        if (item.kind === 'page') {
            pushRecent(item.label, item.href);
        }
        router.visit(item.href);
    }
    emit('update:modelValue', false);
}

function onKey(e: KeyboardEvent): void {
    const max = flatItems.value.length - 1;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex.value = activeIndex.value >= max ? 0 : activeIndex.value + 1;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex.value = activeIndex.value <= 0 ? max : activeIndex.value - 1;
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const item = flatItems.value[activeIndex.value];
        if (item) go(item);
    }
}

function isActive(item: PaletteItem): boolean {
    return flatItems.value[activeIndex.value]?.id === item.id;
}
</script>

<template>
    <Modal
        :model-value="modelValue"
        size="lg"
        :body-padding="false"
        @update:model-value="(v) => emit('update:modelValue', v)"
    >
        <template #header>
            <div class="flex items-center gap-2.5 px-4 py-3 border-b border-[var(--border-subtle)]">
                <Search class="h-4 w-4 text-[var(--text-muted)] shrink-0" />
                <input
                    ref="inputRef"
                    v-model="query"
                    :placeholder="t('palette.placeholder')"
                    class="flex-1 bg-transparent outline-none text-sm placeholder:text-[var(--text-muted)] text-[var(--text-default)]"
                    @keydown="onKey"
                />
                <Kbd class="hidden sm:inline-flex">ESC</Kbd>
            </div>
        </template>

        <div class="max-h-[60vh] overflow-y-auto py-1">
            <div v-for="(section, sIdx) in filtered" :key="sIdx">
                <p
                    v-if="section.heading"
                    class="px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-[var(--text-muted)]"
                >
                    {{ section.heading }}
                </p>
                <ul>
                    <li
                        v-for="item in section.items"
                        :key="item.id"
                        :class="[
                            'group flex items-center gap-3 mx-1 my-px px-2.5 py-2 text-sm rounded-md cursor-pointer transition-colors',
                            isActive(item)
                                ? 'bg-[var(--state-hover)] text-[var(--text-strong)]'
                                : 'text-[var(--text-default)] hover:bg-[var(--state-hover)]',
                        ]"
                        @click="go(item)"
                        @mousemove="activeIndex = flatItems.findIndex((x) => x.id === item.id)"
                    >
                        <component
                            :is="item.icon ?? FolderTree"
                            class="h-4 w-4 shrink-0 text-[var(--text-muted)]"
                        />
                        <div class="min-w-0 flex-1">
                            <p class="font-medium truncate">{{ item.label }}</p>
                            <p
                                v-if="item.description || item.group"
                                class="text-xs text-[var(--text-muted)] truncate"
                            >
                                {{ item.description ?? item.group }}
                            </p>
                        </div>
                        <ArrowRight
                            class="h-3.5 w-3.5 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity text-[var(--text-muted)]"
                        />
                    </li>
                </ul>
            </div>

            <div v-if="filtered.length === 0" class="flex flex-col items-center justify-center px-3 py-12">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--surface-sunken)] text-[var(--text-muted)] ring-1 ring-[var(--border-subtle)] mb-2">
                    <Search class="h-4 w-4" />
                </div>
                <p class="text-sm text-[var(--text-muted)]">{{ t('palette.empty') }}</p>
            </div>
        </div>

        <!-- Footer hint bar -->
        <div class="flex items-center gap-3 px-4 py-2 border-t border-[var(--border-subtle)] text-[10px] text-[var(--text-muted)]">
            <span class="inline-flex items-center gap-1">
                <Kbd>↑</Kbd><Kbd>↓</Kbd> {{ t('palette.hintNavigate') }}
            </span>
            <span class="inline-flex items-center gap-1">
                <Kbd>↵</Kbd> {{ t('palette.hintSelect') }}
            </span>
            <span class="ml-auto inline-flex items-center gap-1">
                <Zap class="h-3 w-3" /> {{ t('palette.poweredBy') }}
            </span>
        </div>
    </Modal>
</template>
