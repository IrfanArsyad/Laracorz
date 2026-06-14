<script setup lang="ts">
import { computed } from 'vue';
import { Search, X, SlidersHorizontal } from 'lucide-vue-next';
import Input from '../ui/Input/Input.vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        search: string;
        placeholder?: string;
        hasFilters?: boolean;
        class?: string;
    }>(),
    { placeholder: 'Cari...', hasFilters: false },
);

const emit = defineEmits<{
    'update:search': [v: string];
    reset: [];
}>();

const hasAnyFilter = computed(() => props.search.length > 0 || props.hasFilters);
</script>

<template>
    <div
        :class="
            cn(
                'rounded-xl border border-[var(--border-subtle)] bg-[var(--surface-raised)] shadow-[var(--shadow-xs)] p-3',
                'flex flex-col gap-2.5 lg:flex-row lg:items-center',
                $props.class,
            )
        "
    >
        <!-- Search — kiri -->
        <div class="lg:flex-1 lg:max-w-md">
            <Input
                :model-value="search"
                :placeholder="placeholder"
                @update:model-value="(v) => emit('update:search', v as string)"
            >
                <template #prefix>
                    <Search class="h-4 w-4" />
                </template>
                <template v-if="search" #suffix>
                    <button
                        type="button"
                        class="text-[var(--text-muted)] hover:text-[var(--text-default)] transition-colors"
                        aria-label="Bersihkan"
                        @click="emit('update:search', '')"
                    >
                        <X class="h-3.5 w-3.5" />
                    </button>
                </template>
            </Input>
        </div>

        <!-- Filters — kanan -->
        <div v-if="$slots.default" class="flex flex-wrap items-center gap-2 lg:ml-auto">
            <SlidersHorizontal class="h-3.5 w-3.5 text-[var(--text-muted)] hidden lg:block" />
            <slot />
        </div>

        <button
            v-if="hasAnyFilter"
            type="button"
            class="inline-flex h-8 items-center gap-1 rounded-md px-2 text-xs font-medium text-[var(--text-muted)] hover:bg-[var(--state-hover)] hover:text-[var(--text-default)] transition-colors"
            @click="emit('reset')"
        >
            <X class="h-3 w-3" />
            Reset
        </button>
    </div>
</template>
