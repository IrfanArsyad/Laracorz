<script setup lang="ts">
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { X } from 'lucide-vue-next';
import SidebarItem from './SidebarItem.vue';
import { cn } from '@/lib/utils';
import { useNavLabel } from '@/composables/useNavLabel';
import type { MenuGroup } from '@/types';

defineProps<{ collapsed: boolean; mobileOpen: boolean }>();
const emit = defineEmits<{ close: [] }>();

const page = usePage();
const { t } = useI18n();
const { groupLabel } = useNavLabel();
const menu = computed<MenuGroup[]>(() => (page.props.menu as MenuGroup[]) ?? []);
const appName = computed(() => (page.props.app as { name?: string })?.name ?? 'LaraCorz');
const initial = computed(() => appName.value.charAt(0).toUpperCase());
</script>

<template>
    <aside
        :class="
            cn(
                'fixed inset-y-0 left-0 z-40 bg-[var(--sidebar-bg)] text-[var(--sidebar-fg)]',
                'transition-[width] duration-[var(--duration-base)] ease-[var(--ease-out)]',
                collapsed ? 'md:w-[72px]' : 'md:w-64',
                'w-64',
                mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
            )
        "
    >
        <div class="flex h-16 items-center justify-between px-4">
            <Link href="/" class="flex items-center gap-2.5 truncate font-semibold">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--brand-bg)] text-[var(--brand-fg)] text-base font-bold shrink-0">
                    {{ initial }}
                </span>
                <span v-if="!collapsed" class="text-base tracking-tight text-[var(--text-strong)] truncate">
                    {{ appName }}
                </span>
            </Link>
            <button
                type="button"
                class="md:hidden rounded-md p-1.5 text-[var(--text-muted)] hover:bg-[var(--state-hover)]"
                :aria-label="t('common.close')"
                @click="emit('close')"
            >
                <X class="h-5 w-5" />
            </button>
        </div>

        <nav class="overflow-y-auto h-[calc(100vh-4rem)] py-3">
            <div v-for="group in menu" :key="group.id" class="mb-4">
                <div
                    v-if="!collapsed"
                    class="px-4 mb-1.5 text-xs font-semibold uppercase tracking-[0.06em] text-[var(--text-muted)]"
                >
                    {{ groupLabel(group.name, group.label) }}
                </div>
                <ul class="space-y-0.5 px-2">
                    <SidebarItem v-for="m in group.modules" :key="m.id" :node="m" :collapsed="collapsed" />
                </ul>
            </div>
        </nav>
    </aside>

    <Transition
        enter-active-class="transition-opacity duration-[var(--duration-fast)]"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-[var(--duration-fast)]"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-30 bg-black/50 backdrop-blur-[2px] md:hidden"
            @click="emit('close')"
        />
    </Transition>
</template>
