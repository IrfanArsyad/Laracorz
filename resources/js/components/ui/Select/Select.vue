<script setup lang="ts">
import { computed, inject, onMounted, onUnmounted, ref, type Ref } from 'vue';
import { Check, ChevronDown, X } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { cn } from '@/lib/utils';

const { t } = useI18n();

const injectedError = inject<Ref<string | undefined>>('formFieldError');
const injectedId = inject<Ref<string>>('formFieldId');

interface Option {
    label: string;
    value: string | number | null;
    disabled?: boolean;
}

const props = withDefaults(
    defineProps<{
        modelValue?: string | number | null;
        options: Option[];
        placeholder?: string;
        searchable?: boolean;
        clearable?: boolean;
        disabled?: boolean;
        error?: boolean | string;
        class?: string;
        id?: string;
    }>(),
    { searchable: false, clearable: false, disabled: false },
);

const emit = defineEmits<{ 'update:modelValue': [value: string | number | null] }>();

const isError = computed(() => Boolean(props.error || injectedError?.value));

const open = ref(false);
const search = ref('');
const containerRef = ref<HTMLElement | null>(null);
const activeIndex = ref(-1);

const selectedLabel = computed(() => {
    const f = props.options.find((o) => o.value === props.modelValue);
    return f ? f.label : '';
});

const filtered = computed(() => {
    if (!props.searchable || !search.value) return props.options;
    const s = search.value.toLowerCase();
    return props.options.filter((o) => o.label.toLowerCase().includes(s));
});

function toggle(): void {
    if (props.disabled) return;
    open.value = !open.value;
    if (open.value) search.value = '';
}

function pick(opt: Option): void {
    if (opt.disabled) return;
    emit('update:modelValue', opt.value);
    open.value = false;
}

function clear(e: Event): void {
    e.stopPropagation();
    emit('update:modelValue', null);
}

function onClickOutside(e: MouseEvent): void {
    if (!containerRef.value?.contains(e.target as Node)) open.value = false;
}

function onKey(e: KeyboardEvent): void {
    if (!open.value) {
        if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
            e.preventDefault();
            open.value = true;
        }
        return;
    }
    if (e.key === 'Escape') {
        open.value = false;
    } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex.value = Math.min(filtered.value.length - 1, activeIndex.value + 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex.value = Math.max(0, activeIndex.value - 1);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const opt = filtered.value[activeIndex.value];
        if (opt) pick(opt);
    }
}

onMounted(() => document.addEventListener('mousedown', onClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside));
</script>

<template>
    <div ref="containerRef" class="relative" @keydown="onKey">
        <button
            :id="id ?? injectedId"
            type="button"
            :disabled="disabled"
            :aria-invalid="isError ? 'true' : undefined"
            :class="
                cn(
                    'flex h-9 w-full items-center justify-between rounded-md border bg-[var(--surface-raised)] px-3 py-1.5 text-sm text-[var(--text-default)]',
                    'transition-[border-color,box-shadow] duration-[var(--duration-fast)] ease-[var(--ease-out)]',
                    'hover:border-[var(--border-strong)]',
                    'focus-visible:outline-none focus-visible:border-[var(--border-focus)] focus-visible:ring-4 focus-visible:ring-[color-mix(in_oklab,var(--focus-ring),transparent_82%)]',
                    'disabled:cursor-not-allowed disabled:opacity-60',
                    isError ? 'border-[var(--status-danger-border)]' : 'border-[var(--border-default)]',
                    $props.class,
                )
            "
            @click="toggle"
        >
            <span :class="modelValue == null ? 'text-muted-foreground' : ''">
                {{ selectedLabel || placeholder || t('common.select') }}
            </span>
            <span class="flex items-center gap-1">
                <button
                    v-if="clearable && modelValue !== null && modelValue !== undefined"
                    type="button"
                    class="text-muted-foreground hover:text-foreground"
                    @click="clear"
                    @keydown.enter="clear"
                    :aria-label="t('common.reset')"
                >
                    <X class="h-3.5 w-3.5" />
                </button>
                <ChevronDown class="h-4 w-4 opacity-50" />
            </span>
        </button>

        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="absolute z-50 mt-1 w-full rounded-md border border-border bg-popover shadow-md"
            >
                <div v-if="searchable" class="border-b border-border p-1">
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="t('common.search')"
                        class="w-full px-2 py-1.5 text-sm bg-transparent outline-none placeholder:text-muted-foreground"
                    />
                </div>
                <ul role="listbox" class="max-h-60 overflow-y-auto py-1">
                    <li
                        v-for="(opt, idx) in filtered"
                        :key="String(opt.value)"
                        role="option"
                        :aria-selected="opt.value === modelValue"
                        :class="
                            cn(
                                'flex items-center justify-between gap-2 px-3 py-1.5 text-sm cursor-pointer select-none',
                                opt.value === modelValue ? 'bg-accent text-accent-foreground' : '',
                                idx === activeIndex ? 'bg-accent/60' : 'hover:bg-accent',
                                opt.disabled ? 'opacity-50 cursor-not-allowed' : '',
                            )
                        "
                        @click="pick(opt)"
                        @mousemove="activeIndex = idx"
                    >
                        <span>{{ opt.label }}</span>
                        <Check v-if="opt.value === modelValue" class="h-4 w-4" />
                    </li>
                    <li v-if="filtered.length === 0" class="px-3 py-2 text-sm text-muted-foreground">
                        Tidak ada hasil
                    </li>
                </ul>
            </div>
        </Transition>
    </div>
</template>
