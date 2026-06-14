<script setup lang="ts">
import Breadcrumb from '../ui/Breadcrumb/Breadcrumb.vue';

defineProps<{
    title: string;
    description?: string;
    breadcrumbs?: { label: string; href?: string }[];
}>();
</script>

<template>
    <div class="space-y-4">
        <Breadcrumb v-if="breadcrumbs?.length" :items="breadcrumbs" />
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold tracking-tight text-[var(--text-strong)] sm:text-[22px]">
                    {{ title }}
                </h1>
                <p v-if="description" class="text-sm text-[var(--text-muted)] max-w-2xl text-pretty">
                    {{ description }}
                </p>
            </div>
            <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2 shrink-0">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>
