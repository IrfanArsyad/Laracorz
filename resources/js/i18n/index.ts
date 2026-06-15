import { createI18n } from 'vue-i18n';
import en from './en';
import id from './id';

export type Locale = 'en' | 'id';

const LOCALE_KEY = 'laracorz.locale';
export const SUPPORTED_LOCALES: Locale[] = ['en', 'id'];
export const DEFAULT_LOCALE: Locale = 'en';

/**
 * Detect initial locale: priority = localStorage → <html lang> → DEFAULT_LOCALE.
 * Backend juga inject locale via Inertia props (auth.locale) — di-handle di
 * app.ts setelah hydrate. Saat boot pertama kita pilih client-side dulu.
 */
function detectInitialLocale(): Locale {
    if (typeof window === 'undefined') return DEFAULT_LOCALE;
    const stored = window.localStorage?.getItem(LOCALE_KEY) as Locale | null;
    if (stored && SUPPORTED_LOCALES.includes(stored)) return stored;
    const htmlLang = document.documentElement.lang as Locale;
    if (htmlLang && SUPPORTED_LOCALES.includes(htmlLang)) return htmlLang;
    return DEFAULT_LOCALE;
}

export const i18n = createI18n({
    legacy: false,
    locale: detectInitialLocale(),
    fallbackLocale: DEFAULT_LOCALE,
    messages: { en, id },
    missingWarn: false,
    fallbackWarn: false,
});

export function setLocale(locale: Locale): void {
    if (!SUPPORTED_LOCALES.includes(locale)) return;
    i18n.global.locale.value = locale;
    if (typeof window !== 'undefined') {
        window.localStorage?.setItem(LOCALE_KEY, locale);
        document.documentElement.lang = locale;
    }
}

export function getStoredLocale(): Locale {
    return i18n.global.locale.value as Locale;
}
