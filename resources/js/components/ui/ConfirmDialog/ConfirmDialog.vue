<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { AlertTriangle, Trash2 } from 'lucide-vue-next';
import { useConfirm } from '@/composables/useConfirm';
import Modal from '../Modal/Modal.vue';
import Button from '../Button/Button.vue';

/**
 * ConfirmDialog v2 — icon header + clear hierarchy, Linear/Vercel-style.
 *
 *   ┌────────────────────────────────────────┐
 *   │  [🗑]  Delete user?                   │
 *   │       Are you sure you want to delete  │
 *   │       "John"? This cannot be undone.   │
 *   │                                        │
 *   │                    [Cancel]  [Delete] │
 *   └────────────────────────────────────────┘
 *
 * Destructive: red circle + Trash icon + destructive CTA.
 * Default:     amber circle + Alert icon + default CTA.
 * Auto-focus tombol confirm. Enter = accept (selama input bukan fokus).
 */

const { state, accept, cancel } = useConfirm();

const confirmRef = ref<InstanceType<typeof Button> | null>(null);

const isDestructive = computed(() => state.variant === 'destructive');

const iconWrapClass = computed(() =>
    isDestructive.value
        ? 'bg-[var(--status-danger-bg)] text-[var(--status-danger-fg)]'
        : 'bg-[var(--status-warning-bg)] text-[var(--status-warning-fg)]',
);

const Icon = computed(() => (isDestructive.value ? Trash2 : AlertTriangle));

// Auto-focus tombol konfirmasi saat modal terbuka — UX: aksi paling
// "dimaksudkan" oleh user (mereka memang mau hapus). Tetap ada cancel
// untuk yang batal.
watch(
    () => state.open,
    async (open) => {
        if (open) {
            await nextTick();
            // Button component punya focus method, atau cari <button> di dalamnya.
            const el = (confirmRef.value as unknown as { $el?: HTMLElement })?.$el ?? null;
            (el?.querySelector('button') as HTMLButtonElement | null)?.focus();
        }
    },
);

function onKey(e: KeyboardEvent): void {
    if (!state.open) return;
    if (e.key === 'Enter') {
        const target = e.target as HTMLElement;
        // Skip kalau lagi di input/textarea (mis. future password confirm)
        if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') return;
        e.preventDefault();
        accept();
    }
}
</script>

<template>
    <Modal
        :model-value="state.open"
        size="sm"
        :body-padding="false"
        @update:model-value="(v) => !v && cancel()"
        @keydown="onKey"
    >
        <div class="p-5">
            <div class="flex items-start gap-3.5">
                <div
                    :class="
                        ['flex h-10 w-10 shrink-0 items-center justify-center rounded-full', iconWrapClass]
                    "
                >
                    <component :is="Icon" class="h-5 w-5" />
                </div>
                <div class="min-w-0 flex-1 space-y-1 pt-0.5">
                    <h2 class="text-base font-semibold leading-tight tracking-tight text-[var(--text-strong)]">
                        {{ state.title }}
                    </h2>
                    <p class="text-sm leading-relaxed text-[var(--text-muted)]">
                        {{ state.message }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-[var(--border-subtle)] bg-[var(--surface-sunken)]/40 px-5 py-3">
            <Button variant="ghost" type="button" @click="cancel">{{ state.cancelLabel }}</Button>
            <Button
                ref="confirmRef"
                :variant="isDestructive ? 'destructive' : 'default'"
                type="button"
                @click="accept"
            >
                {{ state.confirmLabel }}
            </Button>
        </div>
    </Modal>
</template>
