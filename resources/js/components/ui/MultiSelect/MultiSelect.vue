<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Check, ChevronDown, X } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { cn } from '@/lib/utils';

const { t } = useI18n();

interface Option {
    label: string;
    value: string | number;
    disabled?: boolean;
}

const props = withDefaults(
    defineProps<{
        modelValue?: Array<string | number>;
        options: Option[];
        placeholder?: string;
        searchable?: boolean;
        disabled?: boolean;
        error?: boolean;
        class?: string;
        id?: string;
    }>(),
    { modelValue: () => [], searchable: true, disabled: false },
);

const emit = defineEmits<{ 'update:modelValue': [value: Array<string | number>] }>();

const open = ref(false);
const search = ref('');
const containerRef = ref<HTMLElement | null>(null);

const filtered = computed(() =>
    props.options.filter((o) =>
        !search.value ? true : o.label.toLowerCase().includes(search.value.toLowerCase()),
    ),
);

const selected = computed(() =>
    props.options.filter((o) => props.modelValue.includes(o.value)),
);

function toggle(opt: Option): void {
    if (opt.disabled) return;
    const set = new Set(props.modelValue);
    if (set.has(opt.value)) set.delete(opt.value);
    else set.add(opt.value);
    emit('update:modelValue', Array.from(set));
}

function clear(): void {
    emit('update:modelValue', []);
}

function selectAll(): void {
    emit('update:modelValue', props.options.filter((o) => !o.disabled).map((o) => o.value));
}

function onClickOutside(e: MouseEvent): void {
    if (!containerRef.value?.contains(e.target as Node)) open.value = false;
}

onMounted(() => document.addEventListener('mousedown', onClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside));
</script>

<template>
    <div ref="containerRef" class="relative">
        <button
            :id="id"
            type="button"
            :disabled="disabled"
            :class="
                cn(
                    'flex min-h-9 w-full items-center justify-between rounded-md border bg-[var(--surface-raised)] px-2 py-1 text-sm flex-wrap gap-1',
                    'focus-visible:outline-none focus-visible:border-[var(--border-focus)] focus-visible:ring-4 focus-visible:ring-[color-mix(in_oklab,var(--focus-ring),transparent_82%)]',
                    error ? 'border-[var(--status-danger-border)]' : 'border-[var(--border-default)]',
                    $props.class,
                )
            "
            @click="open = !open"
        >
            <div v-if="selected.length === 0" class="text-muted-foreground px-1">{{ placeholder ?? t('common.select') }}</div>
            <div v-else class="flex flex-wrap gap-1">
                <span
                    v-for="opt in selected"
                    :key="String(opt.value)"
                    class="inline-flex items-center gap-1 rounded bg-secondary text-secondary-foreground px-1.5 py-0.5 text-xs"
                >
                    {{ opt.label }}
                    <X class="h-3 w-3 cursor-pointer" @click.stop="toggle(opt)" />
                </span>
            </div>
            <ChevronDown class="h-4 w-4 opacity-50 ml-auto" />
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
                <div v-if="searchable" class="border-b border-border p-1 flex gap-1">
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="t('common.search')"
                        class="flex-1 px-2 py-1.5 text-sm bg-transparent outline-none placeholder:text-muted-foreground"
                    />
                </div>
                <div class="flex justify-between gap-1 p-1 border-b border-border">
                    <button type="button" class="text-xs px-2 py-1 hover:bg-accent rounded" @click="selectAll">
                        {{ t('common.all') }}
                    </button>
                    <button type="button" class="text-xs px-2 py-1 hover:bg-accent rounded" @click="clear">
                        {{ t('common.reset') }}
                    </button>
                </div>
                <ul role="listbox" class="max-h-60 overflow-y-auto py-1">
                    <li
                        v-for="opt in filtered"
                        :key="String(opt.value)"
                        :class="
                            cn(
                                'flex items-center justify-between gap-2 px-3 py-1.5 text-sm cursor-pointer hover:bg-accent',
                                opt.disabled ? 'opacity-50 cursor-not-allowed' : '',
                            )
                        "
                        @click="toggle(opt)"
                    >
                        <span>{{ opt.label }}</span>
                        <Check v-if="modelValue.includes(opt.value)" class="h-4 w-4" />
                    </li>
                </ul>
            </div>
        </Transition>
    </div>
</template>
