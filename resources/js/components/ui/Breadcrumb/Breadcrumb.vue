<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight, Home } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

interface Crumb {
    label: string;
    href?: string;
    icon?: unknown;
}

defineProps<{ items: Crumb[]; class?: string }>();
</script>

<template>
    <nav :class="cn('flex items-center gap-1 text-sm', $props.class)" aria-label="Breadcrumb">
        <ol class="flex items-center flex-wrap gap-1">
            <li class="flex items-center text-muted-foreground">
                <Link href="/" class="hover:text-foreground inline-flex items-center gap-1">
                    <Home class="h-3.5 w-3.5" />
                </Link>
            </li>
            <li v-for="(c, i) in items" :key="i" class="flex items-center gap-1">
                <ChevronRight class="h-3.5 w-3.5 text-muted-foreground" />
                <Link
                    v-if="c.href && i < items.length - 1"
                    :href="c.href"
                    class="text-muted-foreground hover:text-foreground"
                >
                    {{ c.label }}
                </Link>
                <span v-else class="text-foreground font-medium">{{ c.label }}</span>
            </li>
        </ol>
    </nav>
</template>
