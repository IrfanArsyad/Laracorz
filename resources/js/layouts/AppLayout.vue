<script setup lang="ts">
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Sidebar from './partials/Sidebar.vue';
import Topbar from './partials/Topbar.vue';
import ToastContainer from '@/components/ui/Toast/ToastContainer.vue';
import ConfirmDialog from '@/components/ui/ConfirmDialog/ConfirmDialog.vue';
import CommandPalette from '@/components/ui/CommandPalette/CommandPalette.vue';
import ErrorBoundary from '@/components/shared/ErrorBoundary.vue';
import { useTheme } from '@/composables/useTheme';
import { useShortcut } from '@/composables/useShortcut';
import { useToast } from '@/composables/useToast';
import { watch } from 'vue';

defineProps<{ title?: string }>();

useTheme();
const toast = useToast();
const page = usePage();

const sidebarCollapsed = ref<boolean>(
    (typeof localStorage !== 'undefined' && localStorage.getItem('sidebar:collapsed') === '1') || false,
);
const mobileOpen = ref(false);
const paletteOpen = ref(false);

useShortcut('ctrl+k', (e) => {
    e.preventDefault();
    paletteOpen.value = true;
});

function toggleCollapsed(): void {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    try {
        localStorage.setItem('sidebar:collapsed', sidebarCollapsed.value ? '1' : '0');
    } catch {
        /* noop */
    }
}

watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;
        const f = flash as Record<string, string | null>;
        if (f.success) toast.success(f.success);
        if (f.error) toast.error(f.error);
        if (f.warning) toast.warning(f.warning);
        if (f.info) toast.info(f.info);
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <div class="min-h-screen bg-[var(--surface-base)] text-[var(--text-default)]">
        <Sidebar :collapsed="sidebarCollapsed" :mobile-open="mobileOpen" @close="mobileOpen = false" />
        <div :class="['transition-[padding] duration-[var(--duration-base)] ease-[var(--ease-out)]', sidebarCollapsed ? 'md:pl-[72px]' : 'md:pl-64']">
            <Topbar @toggle-mobile="mobileOpen = !mobileOpen" @toggle-collapsed="toggleCollapsed" />
            <main class="px-4 py-5 md:px-8 md:py-6 lg:px-10">
                <ErrorBoundary>
                    <slot />
                </ErrorBoundary>
            </main>
            <footer class="px-4 md:px-8 lg:px-10 py-4 text-xs text-[var(--text-muted)] flex flex-wrap items-center justify-between gap-2">
                <span>
                    &copy; {{ new Date().getFullYear() }} LaraCorz
                </span>
                <a
                    href="https://studiolab.id"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="hover:text-[var(--text-default)] transition-colors"
                >
                    Created by <span class="font-medium text-[var(--text-default)]">studiolab.id</span>
                </a>
            </footer>
        </div>

        <ToastContainer />
        <ConfirmDialog />
        <CommandPalette v-model="paletteOpen" />
    </div>
</template>
