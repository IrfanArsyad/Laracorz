<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import type { PaginationMeta } from '@/types';

const props = defineProps<{
    meta?: PaginationMeta | null;
    only?: string[];
    class?: string;
}>();

const safeMeta = computed(() => ({
    from: props.meta?.from ?? 0,
    to: props.meta?.to ?? 0,
    total: props.meta?.total ?? 0,
    links: props.meta?.links ?? [],
}));

function go(url: string | null): void {
    if (!url) return;
    router.get(url, {}, { preserveState: true, preserveScroll: true, replace: true, only: props.only });
}

function isPrev(label: string): boolean {
    return label.includes('Previous') || label.includes('Sebelumnya') || label.includes('&laquo;');
}
function isNext(label: string): boolean {
    return label.includes('Next') || label.includes('Berikutnya') || label.includes('&raquo;');
}
</script>

<template>
    <div :class="cn('flex items-center justify-between gap-4 flex-wrap py-2', $props.class)">
        <p class="text-xs text-[var(--text-muted)]">
            Menampilkan
            <span class="font-medium text-[var(--text-default)] tabular-nums">{{ safeMeta.from }}</span>
            -
            <span class="font-medium text-[var(--text-default)] tabular-nums">{{ safeMeta.to }}</span>
            dari
            <span class="font-medium text-[var(--text-default)] tabular-nums">{{ safeMeta.total }}</span>
            data
        </p>
        <nav v-if="safeMeta.links.length > 0" class="flex items-center gap-1">
            <button
                v-for="(link, idx) in safeMeta.links"
                :key="idx"
                type="button"
                :disabled="!link.url"
                :class="
                    cn(
                        'inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-xs font-medium border transition-colors',
                        link.active
                            ? 'bg-[var(--brand-bg)] text-[var(--brand-fg)] border-[var(--brand-bg)]'
                            : 'bg-[var(--surface-raised)] text-[var(--text-default)] border-[var(--border-subtle)] hover:border-[var(--border-default)] hover:bg-[var(--state-hover)]',
                        !link.url ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer',
                    )
                "
                @click="go(link.url)"
            >
                <ChevronLeft v-if="isPrev(link.label)" class="h-3.5 w-3.5" />
                <ChevronRight v-else-if="isNext(link.label)" class="h-3.5 w-3.5" />
                <span v-else v-html="link.label" />
            </button>
        </nav>
    </div>
</template>
