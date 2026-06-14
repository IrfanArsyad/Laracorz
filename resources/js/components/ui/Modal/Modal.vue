<script setup lang="ts">
import { computed, onUnmounted, provide, ref, toRef, watch } from 'vue';
import { X } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import ModalHeader from './ModalHeader.vue';
import ModalBody from './ModalBody.vue';
import ModalFooter from './ModalFooter.vue';

const props = withDefaults(
    defineProps<{
        modelValue: boolean;
        size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full';
        closeOnEsc?: boolean;
        closeOnOverlay?: boolean;
        /**
         * Title shortcut. Untuk konten lebih kompleks pakai <ModalHeader> langsung.
         */
        title?: string;
        description?: string;
        /**
         * Padding default di body. Kalau kontennya custom (mis. DataTable di dalam modal),
         * set false dan atur sendiri.
         */
        bodyPadding?: boolean;
        class?: string;
    }>(),
    {
        size: 'md',
        closeOnEsc: true,
        closeOnOverlay: true,
        bodyPadding: true,
    },
);

const emit = defineEmits<{ 'update:modelValue': [v: boolean]; close: [] }>();
const dialogRef = ref<HTMLDivElement | null>(null);

const sizes = {
    xs: 'max-w-xs',
    sm: 'max-w-sm',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
    '2xl': 'max-w-6xl',
    full: 'max-w-[95vw] h-[95vh]',
};

const panelClass = computed(() =>
    cn(
        'relative w-full bg-[var(--surface-overlay)] text-[var(--text-default)] rounded-xl border border-[var(--border-subtle)]',
        'shadow-[var(--shadow-overlay)]',
        sizes[props.size],
        props.class,
    ),
);

function close(): void {
    emit('update:modelValue', false);
    emit('close');
}

function onEsc(e: KeyboardEvent): void {
    if (e.key === 'Escape' && props.closeOnEsc) close();
}

watch(
    () => props.modelValue,
    (open) => {
        if (typeof document === 'undefined') return;
        document.body.style.overflow = open ? 'hidden' : '';
        if (open) {
            window.addEventListener('keydown', onEsc);
            setTimeout(() => dialogRef.value?.focus(), 0);
        } else {
            window.removeEventListener('keydown', onEsc);
        }
    },
);

onUnmounted(() => {
    if (typeof document !== 'undefined') document.body.style.overflow = '';
    window.removeEventListener('keydown', onEsc);
});

// Provide closer ke Header/Body/Footer children
provide('modal:close', close);
provide('modal:open', toRef(props, 'modelValue'));
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-[var(--duration-base)] ease-[var(--ease-out)]"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-[var(--duration-fast)] ease-[var(--ease-in-out)]"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="modelValue"
                class="fixed inset-0 z-50 flex items-start sm:items-center justify-center p-4 overflow-y-auto bg-black/40 backdrop-blur-[2px]"
                @click.self="closeOnOverlay && close()"
            >
                <Transition
                    appear
                    enter-active-class="transition duration-[var(--duration-base)] ease-[var(--ease-out)]"
                    enter-from-class="opacity-0 translate-y-1 scale-[0.98]"
                    enter-to-class="opacity-100 translate-y-0 scale-100"
                >
                    <div
                        ref="dialogRef"
                        role="dialog"
                        aria-modal="true"
                        tabindex="-1"
                        :class="panelClass"
                        class="my-8 max-h-[90vh] flex flex-col"
                    >
                        <!-- Header: pakai shortcut props ATAU slot ModalHeader manual -->
                        <ModalHeader
                            v-if="title || description"
                            :title="title"
                            :description="description"
                        />
                        <slot name="header" />

                        <!-- Body: default wrap konten di ModalBody bila slot bukan jelas-jelas custom. -->
                        <template v-if="$slots.default">
                            <ModalBody v-if="bodyPadding">
                                <slot />
                            </ModalBody>
                            <slot v-else />
                        </template>

                        <!-- Footer slot -->
                        <slot name="footer" />
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
