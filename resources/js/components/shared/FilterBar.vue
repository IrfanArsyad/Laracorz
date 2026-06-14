<script setup lang="ts">
import { Search, X } from 'lucide-vue-next';
import Input from '../ui/Input/Input.vue';
import Button from '../ui/Button/Button.vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    search: string;
    placeholder?: string;
    class?: string;
}>();

const emit = defineEmits<{
    'update:search': [v: string];
    reset: [];
}>();
</script>

<template>
    <div :class="cn('flex flex-col gap-2 md:flex-row md:items-center md:justify-between py-3', $props.class)">
        <div class="flex-1 max-w-md">
            <Input
                :model-value="search"
                :placeholder="placeholder ?? 'Cari...'"
                @update:model-value="(v) => emit('update:search', v as string)"
            >
                <template #prefix>
                    <Search class="h-4 w-4" />
                </template>
            </Input>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <slot />
            <Button v-if="$slots.default" variant="ghost" size="sm" @click="emit('reset')">
                <X class="h-3.5 w-3.5" /> Reset
            </Button>
        </div>
    </div>
</template>
