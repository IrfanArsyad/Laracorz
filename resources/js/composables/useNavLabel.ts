import { useI18n } from 'vue-i18n';

/**
 * Translate label module/grup berdasar slug (name) — kalau key i18n
 * tersedia (mis. `nav.modules.user-management`), pakai itu. Kalau tidak,
 * fallback ke label asli dari DB (label user-data).
 *
 *   const { moduleLabel, groupLabel } = useNavLabel();
 *   moduleLabel('settings', 'Pengaturan')  // → 'Settings' / 'Pengaturan'
 */
export function useNavLabel() {
    const { t, te } = useI18n();

    function moduleLabel(name: string | null | undefined, fallback?: string | null): string {
        if (!name) return fallback ?? '';
        const key = `nav.modules.${name}`;
        return te(key) ? t(key) : (fallback ?? name);
    }

    function groupLabel(name: string | null | undefined, fallback?: string | null): string {
        if (!name) return fallback ?? '';
        const key = `nav.groups.${name}`;
        return te(key) ? t(key) : (fallback ?? name);
    }

    function settingsGroup(name: string | null | undefined): string {
        if (!name) return '';
        const key = `settingsGroup.${name}`;
        return te(key) ? t(key) : name.charAt(0).toUpperCase() + name.slice(1);
    }

    function settingsField(name: string | null | undefined, fallback?: string | null): string {
        if (!name) return fallback ?? '';
        const key = `settingsField.${name}`;
        return te(key) ? t(key) : (fallback ?? name);
    }

    return { moduleLabel, groupLabel, settingsGroup, settingsField };
}
