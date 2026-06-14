<script setup lang="ts">
import { computed } from 'vue';
import { cva, type VariantProps } from 'class-variance-authority';
import { X, CheckCircle2, AlertTriangle, Info, AlertCircle } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const alertVariants = cva(
    'relative w-full rounded-lg border p-4 flex gap-3 items-start',
    {
        variants: {
            variant: {
                default: 'bg-background text-foreground border-border',
                info: 'bg-info/10 text-info border-info/30',
                success: 'bg-success/10 text-success border-success/30',
                warning: 'bg-warning/10 text-warning border-warning/30',
                destructive: 'bg-destructive/10 text-destructive border-destructive/30',
            },
        },
        defaultVariants: { variant: 'default' },
    },
);

type Variants = VariantProps<typeof alertVariants>;

const props = withDefaults(
    defineProps<{
        variant?: Variants['variant'];
        title?: string;
        dismissible?: boolean;
        class?: string;
    }>(),
    { variant: 'default', dismissible: false },
);

const emit = defineEmits<{ dismiss: [] }>();

const icon = computed(() => {
    switch (props.variant) {
        case 'success':
            return CheckCircle2;
        case 'warning':
            return AlertTriangle;
        case 'destructive':
            return AlertCircle;
        case 'info':
            return Info;
        default:
            return Info;
    }
});

const classes = computed(() => cn(alertVariants({ variant: props.variant }), props.class));
</script>

<template>
    <div :class="classes" role="alert">
        <component :is="icon" class="h-5 w-5 shrink-0 mt-0.5" />
        <div class="flex-1 text-sm">
            <p v-if="title" class="font-medium leading-none mb-1">{{ title }}</p>
            <slot />
        </div>
        <button
            v-if="dismissible"
            type="button"
            class="ml-2 shrink-0 opacity-70 hover:opacity-100"
            @click="emit('dismiss')"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
