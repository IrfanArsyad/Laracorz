<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Search, X, Check } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { resolveIcon, listIcons } from '@/lib/icon';
import { cn } from '@/lib/utils';

const { t } = useI18n();

/**
 * IconPicker — popover dengan grid icon Lucide + search.
 *
 *   <IconPicker v-model="form.icon" />
 *
 * modelValue = kebab-case icon name (mis. "layout-dashboard", "users",
 * "shield-check"). Sinkron dengan resolveIcon() di lib/icon.ts.
 */

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        placeholder?: string;
        disabled?: boolean;
        error?: boolean;
    }>(),
    { disabled: false },
);

const emit = defineEmits<{
    'update:modelValue': [v: string | null];
}>();

const open = ref(false);
const search = ref('');
const containerRef = ref<HTMLElement | null>(null);

// Pakai registry curated dari lib/icon.ts — sudah tree-shaken named imports.
const ALL_ICONS = listIcons().sort((a, b) => a.name.localeCompare(b.name));

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return ALL_ICONS;
    return ALL_ICONS.filter((i) => i.name.includes(q));
});

const selectedIcon = computed(() => resolveIcon(props.modelValue));
const selectedLabel = computed(() => props.modelValue || props.placeholder || t('iconPicker.selectIcon'));

function pick(name: string): void {
    emit('update:modelValue', name);
    open.value = false;
}

function clear(e: Event): void {
    e.stopPropagation();
    emit('update:modelValue', null);
}

function onClickOutside(e: MouseEvent): void {
    if (!containerRef.value?.contains(e.target as Node)) open.value = false;
}

onMounted(() => document.addEventListener('mousedown', onClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside));
</script>

<template>
    <div ref="containerRef" class="relative">
        <!-- Trigger button -->
        <button
            type="button"
            :disabled="disabled"
            :class="
                cn(
                    'flex h-10 w-full items-center justify-between rounded-md border bg-[var(--surface-raised)] px-3 text-sm transition-colors',
                    'hover:border-[var(--border-strong)] focus-visible:outline-none focus-visible:border-[var(--border-focus)] focus-visible:ring-4 focus-visible:ring-[color-mix(in_oklab,var(--focus-ring),transparent_82%)]',
                    'disabled:cursor-not-allowed disabled:opacity-60',
                    error ? 'border-[var(--status-danger-border)]' : 'border-[var(--border-default)]',
                )
            "
            @click="open = !open"
        >
            <span class="flex items-center gap-2 min-w-0">
                <component
                    :is="selectedIcon"
                    class="h-4 w-4 shrink-0"
                    :class="modelValue ? 'text-[var(--text-default)]' : 'text-[var(--text-muted)]'"
                />
                <span
                    class="truncate font-mono text-xs"
                    :class="modelValue ? 'text-[var(--text-default)]' : 'text-[var(--text-muted)]'"
                >
                    {{ selectedLabel }}
                </span>
            </span>

            <span class="flex items-center gap-1">
                <button
                    v-if="modelValue"
                    type="button"
                    class="rounded-full p-0.5 text-[var(--text-muted)] hover:bg-[var(--state-hover)] hover:text-[var(--text-default)]"
                    :aria-label="t('common.reset')"
                    @click.stop="clear"
                >
                    <X class="h-3 w-3" />
                </button>
            </span>
        </button>

        <!-- Popover -->
        <Transition
            enter-active-class="transition duration-[var(--duration-fast)] ease-[var(--ease-out)]"
            enter-from-class="opacity-0 translate-y-1 scale-[0.98]"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-[var(--duration-instant)] ease-[var(--ease-in-out)]"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="absolute z-50 mt-1.5 w-full min-w-[320px] rounded-lg border border-[var(--border-subtle)] bg-[var(--surface-overlay)] shadow-[var(--shadow-overlay)]"
            >
                <!-- Search bar -->
                <div class="relative border-b border-[var(--border-subtle)] p-2">
                    <Search class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-[var(--text-muted)]" />
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="t('iconPicker.searchIcon')"
                        class="h-9 w-full rounded-md border border-[var(--border-default)] bg-[var(--surface-raised)] pl-8 pr-3 text-sm placeholder:text-[var(--text-muted)] outline-none focus-visible:border-[var(--border-focus)]"
                        autofocus
                    />
                </div>

                <!-- Result count -->
                <div class="px-3 py-1.5 text-xs text-[var(--text-muted)] border-b border-[var(--border-subtle)]">
                    <span class="tabular-nums">{{ filtered.length }}</span> {{ t('iconPicker.iconsCount') }}
                    <span v-if="search.length">{{ t('iconPicker.matching', { q: search }) }}</span>
                </div>

                <!-- Grid icons -->
                <div class="grid grid-cols-7 gap-1 p-2 max-h-72 overflow-y-auto">
                    <button
                        v-for="icon in filtered"
                        :key="icon.name"
                        type="button"
                        :title="icon.name"
                        :class="
                            cn(
                                'group flex h-9 w-9 items-center justify-center rounded-md transition-colors',
                                modelValue === icon.name
                                    ? 'bg-[var(--brand-soft-bg)] text-[var(--brand-soft-fg)] ring-1 ring-[var(--brand-bg)]'
                                    : 'text-[var(--text-default)] hover:bg-[var(--state-hover)] hover:text-[var(--text-strong)]',
                            )
                        "
                        @click="pick(icon.name)"
                    >
                        <Check
                            v-if="modelValue === icon.name"
                            class="absolute h-2.5 w-2.5 -translate-x-2 translate-y-2 text-[var(--brand-bg)]"
                        />
                        <component :is="icon.component" class="h-4 w-4" />
                    </button>

                    <p
                        v-if="filtered.length === 0"
                        class="col-span-7 text-center text-sm text-[var(--text-muted)] py-6"
                    >
                        {{ t('iconPicker.empty') }}
                    </p>
                </div>
            </div>
        </Transition>
    </div>
</template>
