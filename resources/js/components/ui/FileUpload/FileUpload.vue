<script setup lang="ts">
import { ref } from 'vue';
import { Upload, X } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: File | File[] | null;
        accept?: string;
        multiple?: boolean;
        maxSize?: number; // bytes
        disabled?: boolean;
        previewUrl?: string | null;
        class?: string;
    }>(),
    { multiple: false, disabled: false, maxSize: 5 * 1024 * 1024 },
);

const emit = defineEmits<{
    'update:modelValue': [v: File | File[] | null];
    error: [msg: string];
}>();

const inputRef = ref<HTMLInputElement | null>(null);
const dragOver = ref(false);
const preview = ref<string | null>(props.previewUrl ?? null);

function pickFiles(files: FileList | null): void {
    if (!files) return;
    const list = Array.from(files);
    const invalid = list.find((f) => f.size > props.maxSize);
    if (invalid) {
        emit('error', `Ukuran file ${invalid.name} melebihi batas`);
        return;
    }
    if (props.multiple) {
        emit('update:modelValue', list);
    } else {
        const f = list[0];
        emit('update:modelValue', f);
        if (f && f.type.startsWith('image/')) {
            preview.value = URL.createObjectURL(f);
        }
    }
}

function clear(): void {
    emit('update:modelValue', null);
    preview.value = null;
    if (inputRef.value) inputRef.value.value = '';
}

function onDrop(e: DragEvent): void {
    e.preventDefault();
    dragOver.value = false;
    pickFiles(e.dataTransfer?.files ?? null);
}
</script>

<template>
    <div :class="cn('relative', $props.class)">
        <div
            :class="
                cn(
                    'flex flex-col items-center justify-center gap-2 rounded-md border-2 border-dashed border-input bg-background p-6 text-center transition-colors',
                    dragOver ? 'border-primary bg-accent/30' : '',
                    disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-primary',
                )
            "
            @dragover.prevent="dragOver = true"
            @dragleave="dragOver = false"
            @drop="onDrop"
            @click="!disabled && inputRef?.click()"
        >
            <Upload class="h-6 w-6 text-muted-foreground" />
            <p class="text-sm text-muted-foreground">
                Tarik file di sini atau klik untuk memilih
                <span v-if="accept">({{ accept }})</span>
            </p>
            <p v-if="preview" class="mt-2">
                <img :src="preview" class="h-24 w-24 rounded object-cover mx-auto" alt="" />
            </p>
            <input
                ref="inputRef"
                type="file"
                class="hidden"
                :accept="accept"
                :multiple="multiple"
                :disabled="disabled"
                @change="(e) => pickFiles((e.target as HTMLInputElement).files)"
            />
        </div>
        <button
            v-if="modelValue || preview"
            type="button"
            class="absolute top-2 right-2 rounded-full bg-card border border-border p-1 hover:bg-muted"
            aria-label="Hapus"
            @click.stop="clear"
        >
            <X class="h-3 w-3" />
        </button>
    </div>
</template>
