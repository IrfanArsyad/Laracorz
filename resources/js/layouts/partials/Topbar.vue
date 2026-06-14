<script setup lang="ts">
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Menu, PanelLeftClose, PanelLeftOpen, LogOut, User as UserIcon, Settings as SettingsIcon, Command } from 'lucide-vue-next';
import Avatar from '@/components/ui/Avatar/Avatar.vue';
import Kbd from '@/components/ui/Kbd/Kbd.vue';
import ThemeToggle from '@/components/ui/ThemeToggle/ThemeToggle.vue';
import {
    DropdownMenu,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/DropdownMenu';
import NotificationBell from '@/components/shared/NotificationBell.vue';
import type { User } from '@/types';

const emit = defineEmits<{ toggleMobile: []; toggleCollapsed: [] }>();

const page = usePage();
const user = computed<User | null>(() => (page.props.auth as { user: User | null })?.user ?? null);

function logout(): void {
    router.post('/logout');
}
</script>

<template>
    <header class="sticky top-0 z-30 flex h-12 items-center gap-1.5 border-b border-[var(--border-subtle)] bg-[var(--surface-base)]/85 backdrop-blur-md px-3 md:px-4">
        <button
            type="button"
            class="md:hidden inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-[var(--state-hover)] text-[var(--text-default)]"
            aria-label="Buka menu"
            @click="emit('toggleMobile')"
        >
            <Menu class="h-4 w-4" />
        </button>
        <button
            type="button"
            class="hidden md:inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-[var(--state-hover)] text-[var(--text-muted)] hover:text-[var(--text-default)] transition-colors"
            aria-label="Toggle sidebar"
            @click="emit('toggleCollapsed')"
        >
            <PanelLeftClose class="h-4 w-4" />
        </button>

        <div class="ml-auto flex items-center gap-1">
            <button
                type="button"
                class="hidden md:inline-flex h-8 items-center gap-2 rounded-md border border-[var(--border-subtle)] bg-[var(--surface-raised)] pl-2.5 pr-1.5 text-xs text-[var(--text-muted)] hover:border-[var(--border-default)] transition-colors"
                aria-label="Cari"
                @click="$el?.dispatchEvent(new KeyboardEvent('keydown', { key: 'k', ctrlKey: true }))"
            >
                <Command class="h-3.5 w-3.5" />
                <span>Cari...</span>
                <Kbd class="ml-3">⌘K</Kbd>
            </button>

            <NotificationBell />
            <ThemeToggle />

            <DropdownMenu align="end">
                <template #trigger>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full hover:opacity-80 p-1"
                        aria-label="Akun"
                    >
                        <Avatar :src="user?.avatar_url" :name="user?.name" size="sm" />
                    </button>
                </template>

                <DropdownMenuLabel>
                    <div class="space-y-0.5">
                        <p class="text-sm font-medium text-[var(--text-strong)]">{{ user?.name }}</p>
                        <p class="text-xs text-[var(--text-muted)]">
                            <span v-if="user?.username">@{{ user.username }} · </span>{{ user?.email }}
                        </p>
                    </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem as="link" href="/profile">
                    <UserIcon class="h-4 w-4 mr-2" /> Profil
                </DropdownMenuItem>
                <DropdownMenuItem as="link" href="/settings">
                    <SettingsIcon class="h-4 w-4 mr-2" /> Pengaturan
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem variant="destructive" @click="logout">
                    <LogOut class="h-4 w-4 mr-2" /> Keluar
                </DropdownMenuItem>
            </DropdownMenu>
        </div>
    </header>
</template>
