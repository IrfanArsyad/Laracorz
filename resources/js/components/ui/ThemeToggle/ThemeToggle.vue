<script setup lang="ts">
import { Sun, Moon, Monitor } from 'lucide-vue-next';
import { useTheme } from '@/composables/useTheme';
import { DropdownMenu, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '../DropdownMenu';

const { mode, setMode } = useTheme();
</script>

<template>
    <DropdownMenu align="end">
        <template #trigger>
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md hover:bg-accent hover:text-accent-foreground"
                aria-label="Ganti tema"
            >
                <Sun v-if="mode === 'light'" class="h-4 w-4" />
                <Moon v-else-if="mode === 'dark'" class="h-4 w-4" />
                <Monitor v-else class="h-4 w-4" />
            </button>
        </template>
        <DropdownMenuLabel>Tema</DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem @click="setMode('light')">
            <Sun class="h-4 w-4 mr-2" /> Terang
        </DropdownMenuItem>
        <DropdownMenuItem @click="setMode('dark')">
            <Moon class="h-4 w-4 mr-2" /> Gelap
        </DropdownMenuItem>
        <DropdownMenuItem @click="setMode('system')">
            <Monitor class="h-4 w-4 mr-2" /> Sistem
        </DropdownMenuItem>
    </DropdownMenu>
</template>
