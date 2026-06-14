<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import ToastContainer from '@/components/ui/Toast/ToastContainer.vue';
import { useTheme } from '@/composables/useTheme';

defineProps<{ title?: string }>();
useTheme();
const page = usePage();
</script>

<template>
    <div class="min-h-screen grid md:grid-cols-5 bg-[var(--surface-base)] text-[var(--text-default)]">
        <!-- Brand panel — gradient indigo→violet dengan grid pattern halus -->
        <div
            class="relative hidden md:col-span-2 md:flex flex-col justify-between overflow-hidden p-10 text-[var(--brand-fg)]"
            style="background: radial-gradient(at 100% 0%, var(--brand-400) 0%, transparent 50%), radial-gradient(at 0% 100%, var(--brand-700) 0%, transparent 50%), linear-gradient(135deg, var(--brand-600), var(--brand-800));"
        >
            <!-- Decorative grid overlay -->
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.08]"
                style="background-image: linear-gradient(to right, currentColor 1px, transparent 1px), linear-gradient(to bottom, currentColor 1px, transparent 1px); background-size: 48px 48px;"
                aria-hidden="true"
            />
            <div class="relative flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-white/15 backdrop-blur ring-1 ring-white/20">
                    <span class="text-sm font-bold">{{ (page.props.app?.name ?? 'L').charAt(0) }}</span>
                </div>
                <h1 class="text-lg font-semibold tracking-tight">{{ page.props.app?.name ?? 'LaraCorz' }}</h1>
            </div>
            <div class="relative space-y-3">
                <blockquote class="text-balance text-2xl font-medium leading-snug">
                    “Bangun aplikasi Laravel + Vue production-ready dari satu core yang rapi.”
                </blockquote>
                <p class="text-sm text-white/70">— LaraCorz</p>
            </div>
        </div>

        <!-- Form panel -->
        <div class="flex items-center justify-center p-6 md:col-span-3 md:p-10">
            <div class="w-full max-w-sm animate-slide-up">
                <h2 v-if="title" class="text-2xl font-semibold tracking-tight text-[var(--text-strong)] mb-1">
                    {{ title }}
                </h2>
                <p class="mb-6 text-sm text-[var(--text-muted)]">
                    Masuk ke <strong class="text-[var(--text-default)]">{{ page.props.app?.name ?? 'LaraCorz' }}</strong> untuk melanjutkan.
                </p>
                <slot />
            </div>
        </div>
        <ToastContainer />
    </div>
</template>
