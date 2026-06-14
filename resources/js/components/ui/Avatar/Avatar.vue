<script setup lang="ts">
import { computed, ref } from 'vue';
import { cn, initials } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        src?: string | null;
        alt?: string | null;
        name?: string | null;
        size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
        class?: string;
    }>(),
    { size: 'md' },
);

const sizes = { xs: 'h-6 w-6 text-xs', sm: 'h-8 w-8 text-xs', md: 'h-10 w-10 text-sm', lg: 'h-14 w-14 text-base', xl: 'h-20 w-20 text-lg' };
const failed = ref(false);

const initial = computed(() => initials(props.name ?? props.alt ?? '?'));
const classes = computed(() =>
    cn(
        'inline-flex shrink-0 items-center justify-center rounded-full bg-muted text-muted-foreground font-medium overflow-hidden',
        sizes[props.size],
        props.class,
    ),
);
</script>

<template>
    <span :class="classes" :aria-label="alt ?? name ?? ''">
        <img
            v-if="src && !failed"
            :src="src"
            :alt="alt ?? ''"
            class="h-full w-full object-cover"
            loading="lazy"
            @error="failed = true"
        />
        <span v-else>{{ initial }}</span>
    </span>
</template>
