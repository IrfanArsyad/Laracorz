<script setup lang="ts">
import { Bell } from 'lucide-vue-next';
import { DropdownMenu, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuItem } from '../ui/DropdownMenu';
import Badge from '../ui/Badge/Badge.vue';
import { ref } from 'vue';

const unreadCount = ref(0);
const items = ref<Array<{ id: string; title: string; message: string; url?: string }>>([]);
</script>

<template>
    <DropdownMenu align="end">
        <template #trigger>
            <button
                type="button"
                class="relative inline-flex h-9 w-9 items-center justify-center rounded-md hover:bg-accent"
                aria-label="Notifikasi"
            >
                <Bell class="h-4 w-4" />
                <Badge
                    v-if="unreadCount > 0"
                    variant="destructive"
                    class="absolute -top-1 -right-1 px-1.5 min-w-[1.25rem] h-5 text-xs"
                >
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </Badge>
            </button>
        </template>

        <DropdownMenuLabel>Notifikasi</DropdownMenuLabel>
        <DropdownMenuSeparator />
        <div class="max-h-80 overflow-y-auto">
            <DropdownMenuItem v-for="n in items" :key="n.id" :as="n.url ? 'link' : 'button'" :href="n.url">
                <div class="flex-1 text-left">
                    <p class="text-sm font-medium">{{ n.title }}</p>
                    <p class="text-xs text-muted-foreground">{{ n.message }}</p>
                </div>
            </DropdownMenuItem>
            <p v-if="items.length === 0" class="px-3 py-6 text-center text-xs text-muted-foreground">
                Tidak ada notifikasi
            </p>
        </div>
        <DropdownMenuSeparator />
        <DropdownMenuItem as="link" href="/notifications">
            <span class="text-xs text-center w-full">Lihat semua</span>
        </DropdownMenuItem>
    </DropdownMenu>
</template>
