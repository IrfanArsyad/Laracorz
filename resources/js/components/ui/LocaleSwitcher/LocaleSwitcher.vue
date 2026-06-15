<script setup lang="ts">
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Languages, Check } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/DropdownMenu';
import { setLocale, SUPPORTED_LOCALES, type Locale } from '@/i18n';

const { t, locale } = useI18n();
const page = usePage();

const current = computed<Locale>(() => locale.value as Locale);

const labels: Record<Locale, string> = {
    en: 'English',
    id: 'Indonesia',
};

const flags: Record<Locale, string> = {
    en: '🇺🇸',
    id: '🇮🇩',
};

function pick(l: Locale): void {
    if (l === current.value) return;
    setLocale(l);
    // Sync ke backend session — pakai router.post supaya CSRF token included.
    // Tidak perlu reload: vue-i18n sudah switch lokal, backend hanya untuk
    // server-rendered strings (validation messages, dll.) di request berikutnya.
    router.post(
        '/locale',
        { locale: l },
        { preserveScroll: true, preserveState: true, only: ['app'] },
    );
}

// Sinkron initial dari Inertia shared props (app.locale) bila berbeda.
const serverLocale = (page.props.app as { locale?: string } | undefined)?.locale as Locale | undefined;
if (serverLocale && SUPPORTED_LOCALES.includes(serverLocale) && serverLocale !== current.value) {
    setLocale(serverLocale);
}
</script>

<template>
    <DropdownMenu align="end">
        <template #trigger>
            <button
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-[var(--state-hover)] text-[var(--text-muted)] hover:text-[var(--text-default)] transition-colors"
                :aria-label="t('topbar.language')"
            >
                <Languages class="h-4 w-4" />
            </button>
        </template>

        <DropdownMenuLabel>{{ t('topbar.language') }}</DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem
            v-for="l in SUPPORTED_LOCALES"
            :key="l"
            @click="pick(l)"
        >
            <span class="mr-2">{{ flags[l] }}</span>
            <span class="flex-1">{{ labels[l] }}</span>
            <Check v-if="current === l" class="h-3.5 w-3.5 text-[var(--brand-bg)]" />
        </DropdownMenuItem>
    </DropdownMenu>
</template>
