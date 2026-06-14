<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { Check, ChevronDown } from 'lucide-vue-next';
import { cn, debounce } from '@/lib/utils';

interface Option {
    label: string;
    value: string | number;
}

const props = withDefaults(
    defineProps<{
        modelValue?: string | number | null;
        fetcher: (term: string) => Promise<Option[]>;
        placeholder?: string;
        initialLabel?: string;
        disabled?: boolean;
        error?: boolean;
        class?: string;
    }>(),
    { placeholder: 'Ketik untuk mencari...', disabled: false },
);

const emit = defineEmits<{ 'update:modelValue': [value: string | number | null] }>();

const open = ref(false);
const term = ref('');
const options = ref<Option[]>([]);
const loading = ref(false);
const containerRef = ref<HTMLElement | null>(null);
const selectedLabel = ref(props.initialLabel ?? '');

const search = debounce(async (q: string) => {
    loading.value = true;
    try {
        options.value = await props.fetcher(q);
    } catch {
        options.value = [];
    } finally {
        loading.value = false;
    }
}, 300);

watch(term, (v) => search(v));

function pick(opt: Option): void {
    emit('update:modelValue', opt.value);
    selectedLabel.value = opt.label;
    open.value = false;
}

function onClickOutside(e: MouseEvent): void {
    if (!containerRef.value?.contains(e.target as Node)) open.value = false;
}

onMounted(() => document.addEventListener('mousedown', onClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside));
</script>

<template>
    <div ref="containerRef" class="relative w-full">
        <button
            type="button"
            :disabled="disabled"
            :class="
                cn(
                    'flex h-9 w-full items-center justify-between rounded-md border bg-[var(--surface-raised)] px-3 py-1.5 text-sm text-[var(--text-default)] hover:border-[var(--border-strong)] transition-colors',
                    error ? 'border-[var(--status-danger-border)]' : 'border-[var(--border-default)]',
                    $props.class,
                )
            "
            @click="open = !open"
        >
            <span :class="!selectedLabel ? 'text-muted-foreground' : ''">
                {{ selectedLabel || placeholder }}
            </span>
            <ChevronDown class="h-4 w-4 opacity-50" />
        </button>

        <div
            v-if="open"
            class="absolute z-50 mt-1 w-full rounded-md border border-border bg-popover shadow-md"
        >
            <div class="border-b border-border p-1">
                <input
                    v-model="term"
                    type="text"
                    :placeholder="placeholder"
                    class="w-full px-2 py-1.5 text-sm bg-transparent outline-none placeholder:text-muted-foreground"
                />
            </div>
            <ul class="max-h-60 overflow-y-auto py-1">
                <li v-if="loading" class="px-3 py-2 text-sm text-muted-foreground">Memuat...</li>
                <li
                    v-for="opt in options"
                    :key="String(opt.value)"
                    class="flex items-center justify-between px-3 py-1.5 text-sm cursor-pointer hover:bg-accent"
                    @click="pick(opt)"
                >
                    <span>{{ opt.label }}</span>
                    <Check v-if="opt.value === modelValue" class="h-4 w-4" />
                </li>
                <li v-if="!loading && options.length === 0" class="px-3 py-2 text-sm text-muted-foreground">
                    Tidak ada hasil
                </li>
            </ul>
        </div>
    </div>
</template>
