<script setup lang="ts">
import { computed, ref } from 'vue';
import { Search, X, Star, Plus, Check, Bookmark } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { cn } from '@/lib/utils';
import { useSavedFilters, type SavedView } from '@/composables/useSavedFilters';

const { t } = useI18n();

/**
 * FilterBar v3 — flat, always-visible.
 *
 *  ┌──────────────────────────────────────────────────────────────┐
 *  │  🔍 Search...      │  [Filter A] [Filter B]   │ [⭐] [⟲]    │
 *  │  ─────────────── saved views ─────────────────               │
 *  │  • All   ⭐ Active only   ⭐ Banned             + Save view  │
 *  └──────────────────────────────────────────────────────────────┘
 *
 * Filter slot SELALU tampak (tidak collapsible). Saved views opsional —
 * aktif kalau `scope` di-set, simpan view di localStorage per scope.
 *
 * Pakai prop `state` (reactive) untuk dukung save view. state.search & state.filters
 * akan disinkronkan saat apply view.
 */
const props = withDefaults(
    defineProps<{
        search: string;
        placeholder?: string;
        /** jumlah filter aktif untuk visual cue di reset button */
        filtersCount?: number;
        /** scope key untuk saved views (mis. 'users', 'roles', 'admin-log').
         *  Kalau diisi, baris saved views akan muncul. */
        scope?: string;
        /** reactive state object untuk save view fitur. Wajib jika `scope` di-set. */
        state?: { search: string; filters: Record<string, unknown> };
        class?: string;
    }>(),
    { filtersCount: 0 },
);

const emit = defineEmits<{
    'update:search': [v: string];
    reset: [];
}>();

const hasAnyFilter = computed(() => props.search.length > 0 || props.filtersCount > 0);

// ─── Saved views ────────────────────────────────────────────────────────
const enabled = computed(() => Boolean(props.scope && props.state));

// Hanya init useSavedFilters jika scope+state ada. Pakai object kosong
// sebagai fallback supaya types-nya tidak runtime error — tapi UI tetap
// di-guard via `enabled`.
const saved = useSavedFilters(
    props.scope ?? '__noop__',
    { state: props.state ?? { search: '', filters: {} } },
);

const namePromptOpen = ref(false);
const newViewName = ref('');

function openSaveDialog(): void {
    newViewName.value = '';
    namePromptOpen.value = true;
}

function confirmSave(): void {
    if (!enabled.value) return;
    saved.save(newViewName.value);
    namePromptOpen.value = false;
    newViewName.value = '';
}

function applyView(v: SavedView): void {
    if (!enabled.value) return;
    saved.apply(v);
}

function removeView(id: string, e: Event): void {
    e.stopPropagation();
    saved.remove(id);
}

function clearAll(): void {
    saved.clearActive();
    emit('reset');
}
</script>

<template>
    <div :class="cn('space-y-2.5', $props.class)">
        <!-- Main row: search + inline filters + actions -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Search -->
            <div class="relative flex-1 min-w-[200px] sm:max-w-md">
                <Search class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--text-muted)]" />
                <input
                    type="text"
                    :value="search"
                    :placeholder="placeholder ?? t('common.search')"
                    class="h-10 w-full rounded-md border border-[var(--border-default)] bg-[var(--surface-raised)] pl-9 pr-9 text-sm text-[var(--text-default)] placeholder:text-[var(--text-muted)] transition-[border-color,box-shadow] duration-[var(--duration-fast)] ease-[var(--ease-out)] hover:border-[var(--border-strong)] focus-visible:outline-none focus-visible:border-[var(--border-focus)] focus-visible:ring-4 focus-visible:ring-[color-mix(in_oklab,var(--focus-ring),transparent_82%)]"
                    @input="emit('update:search', ($event.target as HTMLInputElement).value)"
                />
                <button
                    v-if="search"
                    type="button"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 flex h-5 w-5 items-center justify-center rounded-full text-[var(--text-muted)] hover:bg-[var(--state-hover)] hover:text-[var(--text-default)] transition-colors"
                    :aria-label="t('common.reset')"
                    @click="emit('update:search', '')"
                >
                    <X class="h-3 w-3" />
                </button>
            </div>

            <!-- Inline filter selects (slot) -->
            <div v-if="$slots.default" class="flex flex-wrap items-center gap-2">
                <slot />
            </div>

            <div class="flex items-center gap-1 ml-auto">
                <!-- Save view (only when scope provided) -->
                <button
                    v-if="enabled && hasAnyFilter"
                    type="button"
                    class="inline-flex h-9 items-center gap-1.5 rounded-md border border-[var(--border-default)] bg-[var(--surface-raised)] px-2.5 text-xs font-medium text-[var(--text-default)] hover:border-[var(--border-strong)] hover:bg-[var(--state-hover)] transition-colors"
                    :title="t('filter.saveView')"
                    @click="openSaveDialog"
                >
                    <Bookmark class="h-3.5 w-3.5" />
                    <span class="hidden sm:inline">{{ t('filter.saveView') }}</span>
                </button>

                <!-- Reset -->
                <button
                    v-if="hasAnyFilter"
                    type="button"
                    class="inline-flex h-9 items-center gap-1.5 rounded-md px-2.5 text-xs font-medium text-[var(--text-muted)] hover:bg-[var(--state-hover)] hover:text-[var(--text-default)] transition-colors"
                    @click="clearAll"
                >
                    <X class="h-3.5 w-3.5" />
                    {{ t('common.reset') }}
                </button>
            </div>
        </div>

        <!-- Saved views row -->
        <div
            v-if="enabled && saved.views.value.length > 0"
            class="flex flex-wrap items-center gap-1.5 -mx-0.5"
        >
            <span class="text-xs text-[var(--text-muted)] px-1">{{ t('filter.views') }}:</span>
            <button
                v-for="v in saved.views.value"
                :key="v.id"
                type="button"
                :class="
                    cn(
                        'group inline-flex h-7 items-center gap-1.5 rounded-full border px-2.5 text-xs font-medium transition-colors',
                        saved.activeViewId.value === v.id
                            ? 'border-[var(--brand-bg)] bg-[var(--brand-soft-bg)] text-[var(--brand-soft-fg)]'
                            : 'border-[var(--border-subtle)] bg-[var(--surface-raised)] text-[var(--text-default)] hover:border-[var(--border-default)] hover:bg-[var(--state-hover)]',
                    )
                "
                @click="applyView(v)"
            >
                <Star
                    v-if="saved.activeViewId.value === v.id"
                    class="h-3 w-3 fill-current"
                />
                <Star v-else class="h-3 w-3" />
                <span class="truncate max-w-[140px]">{{ v.name }}</span>
                <span
                    role="button"
                    tabindex="0"
                    class="-mr-1 flex h-4 w-4 items-center justify-center rounded-full opacity-0 group-hover:opacity-60 hover:!opacity-100 hover:bg-[var(--state-active)] transition-opacity"
                    :title="t('common.delete')"
                    @click="removeView(v.id, $event)"
                    @keydown.enter="removeView(v.id, $event)"
                >
                    <X class="h-2.5 w-2.5" />
                </span>
            </button>
        </div>

        <!-- Save dialog (inline minimal) -->
        <div
            v-if="namePromptOpen"
            class="flex items-center gap-2 rounded-md border border-[var(--brand-bg)] bg-[var(--brand-soft-bg)]/40 p-2"
        >
            <Bookmark class="h-4 w-4 text-[var(--brand-soft-fg)] shrink-0" />
            <input
                v-model="newViewName"
                type="text"
                :placeholder="t('filter.viewNamePlaceholder')"
                class="h-8 flex-1 min-w-[160px] rounded-md border border-[var(--border-default)] bg-[var(--surface-raised)] px-2.5 text-sm outline-none focus-visible:border-[var(--border-focus)]"
                @keydown.enter="confirmSave"
                @keydown.escape="namePromptOpen = false"
                autofocus
            />
            <button
                type="button"
                class="inline-flex h-8 items-center gap-1 rounded-md bg-[var(--brand-bg)] px-2.5 text-xs font-medium text-[var(--brand-fg)] hover:bg-[var(--brand-bg-hover)] disabled:opacity-50"
                :disabled="!newViewName.trim()"
                @click="confirmSave"
            >
                <Check class="h-3.5 w-3.5" />
                {{ t('common.save') }}
            </button>
            <button
                type="button"
                class="inline-flex h-8 items-center gap-1 rounded-md px-2.5 text-xs text-[var(--text-muted)] hover:bg-[var(--state-hover)]"
                @click="namePromptOpen = false"
            >
                {{ t('common.cancel') }}
            </button>
        </div>
    </div>
</template>
