<script setup lang="ts">
import { cn } from '@/lib/utils';
withDefaults(defineProps<{ sticky?: boolean; class?: string }>(), { sticky: false });
</script>

<template>
    <div
        :class="
            cn(
                'mt-5 flex items-center justify-end gap-2 pt-4 border-t border-[var(--border-subtle)]',
                sticky ? 'sticky bottom-0 z-10 bg-[var(--surface-raised)] -mx-6 px-6 pb-4' : '',
                $props.class,
            )
        "
    >
        <slot />
    </div>
</template>
