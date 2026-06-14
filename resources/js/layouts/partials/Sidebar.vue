<script setup lang="ts">
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';
import SidebarItem from './SidebarItem.vue';
import { cn } from '@/lib/utils';
import type { MenuGroup } from '@/types';

defineProps<{ collapsed: boolean; mobileOpen: boolean }>();
const emit = defineEmits<{ close: [] }>();

const page = usePage();
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
                collapsed ? 'md:w-16' : 'md:w-60',
                'w-60',
                mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
            )
        "
    >
        <div class="flex h-14 items-center justify-between px-3">
            <Link href="/" class="flex items-center gap-2 truncate font-semibold">
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[var(--brand-bg)] text-[var(--brand-fg)] text-xs font-bold shrink-0">
                    {{ initial }}
                </span>
                <span v-if="!collapsed" class="text-sm tracking-tight text-[var(--text-strong)] truncate">
                    {{ appName }}
                </span>
            </Link>
            <button
                type="button"
                class="md:hidden rounded-md p-1.5 text-[var(--text-muted)] hover:bg-[var(--state-hover)]"
                aria-label="Tutup"
                @click="emit('close')"
            >
                <X class="h-4 w-4" />
            </button>
        </div>

        <nav class="overflow-y-auto h-[calc(100vh-3.5rem)] py-3">
            <div v-for="group in menu" :key="group.id" class="mb-3">
                <div
                    v-if="!collapsed"
                    class="px-3 mb-1 text-[10px] font-semibold uppercase tracking-[0.06em] text-[var(--text-muted)]"
                >
                    {{ group.label }}
                </div>
                <ul class="space-y-px px-2">
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
