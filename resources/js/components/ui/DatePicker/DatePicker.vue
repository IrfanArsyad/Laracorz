<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Calendar, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        placeholder?: string;
        min?: string;
        max?: string;
        disabled?: boolean;
        class?: string;
    }>(),
    { placeholder: 'Pilih tanggal' },
);
const emit = defineEmits<{ 'update:modelValue': [value: string | null] }>();

const open = ref(false);
const containerRef = ref<HTMLElement | null>(null);
const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const DAYS = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

const current = ref<Date>(props.modelValue ? new Date(props.modelValue) : new Date());
const view = ref<{ year: number; month: number }>({
    year: current.value.getFullYear(),
    month: current.value.getMonth(),
});

const display = computed(() => {
    if (!props.modelValue) return '';
    const d = new Date(props.modelValue);
    if (Number.isNaN(d.getTime())) return '';
    return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
});

interface Cell {
    date: Date;
    inMonth: boolean;
}

const cells = computed<Cell[]>(() => {
    const first = new Date(view.value.year, view.value.month, 1);
    const startOffset = first.getDay();
    const start = new Date(view.value.year, view.value.month, 1 - startOffset);
    const arr: Cell[] = [];
    for (let i = 0; i < 42; i++) {
        const d = new Date(start);
        d.setDate(start.getDate() + i);
        arr.push({ date: d, inMonth: d.getMonth() === view.value.month });
    }
    return arr;
});

function prev(): void {
    if (view.value.month === 0) {
        view.value.month = 11;
        view.value.year--;
    } else view.value.month--;
}
function next(): void {
    if (view.value.month === 11) {
        view.value.month = 0;
        view.value.year++;
    } else view.value.month++;
}

function isSelected(d: Date): boolean {
    if (!props.modelValue) return false;
    return new Date(props.modelValue).toDateString() === d.toDateString();
}

function isToday(d: Date): boolean {
    return new Date().toDateString() === d.toDateString();
}

function pick(d: Date): void {
    const iso = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    emit('update:modelValue', iso);
    open.value = false;
}

function clear(): void {
    emit('update:modelValue', null);
    open.value = false;
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
            type="button"
            :disabled="disabled"
            :class="
                cn(
                    'flex h-9 w-full items-center justify-between rounded-md border border-[var(--border-default)] bg-[var(--surface-raised)] px-3 py-1.5 text-sm text-[var(--text-default)] hover:border-[var(--border-strong)] transition-colors',
                    $props.class,
                )
            "
            @click="open = !open"
        >
            <span :class="!display ? 'text-muted-foreground' : ''">{{ display || placeholder }}</span>
            <Calendar class="h-4 w-4 opacity-50" />
        </button>

        <div
            v-if="open"
            class="absolute z-50 mt-1 w-72 rounded-md border border-border bg-popover p-3 shadow-md"
        >
            <div class="flex items-center justify-between mb-2">
                <button type="button" class="p-1 hover:bg-accent rounded" @click="prev">
                    <ChevronLeft class="h-4 w-4" />
                </button>
                <span class="text-sm font-medium">{{ MONTHS[view.month] }} {{ view.year }}</span>
                <button type="button" class="p-1 hover:bg-accent rounded" @click="next">
                    <ChevronRight class="h-4 w-4" />
                </button>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center text-xs text-muted-foreground mb-1">
                <div v-for="d in DAYS" :key="d">{{ d }}</div>
            </div>
            <div class="grid grid-cols-7 gap-1">
                <button
                    v-for="cell in cells"
                    :key="cell.date.toISOString()"
                    type="button"
                    :class="
                        cn(
                            'h-8 w-8 text-xs rounded',
                            isSelected(cell.date) ? 'bg-primary text-primary-foreground' : 'hover:bg-accent',
                            !cell.inMonth ? 'opacity-40' : '',
                            isToday(cell.date) && !isSelected(cell.date) ? 'ring-1 ring-ring' : '',
                        )
                    "
                    @click="pick(cell.date)"
                >
                    {{ cell.date.getDate() }}
                </button>
            </div>
            <div class="mt-2 flex justify-between border-t border-border pt-2">
                <button type="button" class="text-xs text-muted-foreground hover:text-foreground" @click="clear">
                    Bersihkan
                </button>
                <button type="button" class="text-xs text-primary" @click="pick(new Date())">Hari ini</button>
            </div>
        </div>
    </div>
</template>
