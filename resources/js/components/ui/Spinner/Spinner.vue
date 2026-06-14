<script setup lang="ts">
import { Loader2 } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

withDefaults(defineProps<{ size?: 'sm' | 'md' | 'lg'; class?: string }>(), { size: 'md' });
const sizes = { sm: 'h-4 w-4', md: 'h-6 w-6', lg: 'h-10 w-10' };
</script>

<template>
    <Loader2 :class="cn('animate-spin text-muted-foreground', sizes[size], $props.class)" />
</template>
