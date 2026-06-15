import * as Icons from 'lucide-vue-next';
import { Folder } from 'lucide-vue-next';
import type { Component } from 'vue';

/**
 * Resolve string icon name (kebab-case dari DB) ke komponen Lucide.
 *
 *   resolveIcon('layout-dashboard')  // → LayoutDashboard component
 *   resolveIcon('users')             // → Users component
 *   resolveIcon('shield-check')      // → ShieldCheck component
 *   resolveIcon(null)                // → Folder (fallback)
 *   resolveIcon('not-exists', Hash)  // → Hash (custom fallback)
 *
 * Catatan: di lucide-vue-next v1.0.0 setiap icon = `(props, ctx) => h(...)`
 * (functional component). Validasi musti `typeof === 'function'`, BUKAN object.
 * Object check akan blokir SEMUA icon.
 */

const NON_ICON_EXPORTS = new Set([
    'createLucideIcon',
    'icons',
    'default',
    'Icon',
    'LucideIcon',
    'aliases',
]);

function toPascalCase(name: string): string {
    return name
        .split(/[-_\s]/)
        .filter(Boolean)
        .map((p) => p.charAt(0).toUpperCase() + p.slice(1))
        .join('');
}

export function resolveIcon(
    name: string | null | undefined,
    fallback: Component = Folder,
): Component {
    if (!name) return fallback;

    const key = toPascalCase(name);
    if (NON_ICON_EXPORTS.has(key)) return fallback;

    // Lucide v1.x: icons = functional component. Coba juga variant `{Name}Icon`.
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const candidate =
        (Icons as any)[key] ??
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        (Icons as any)[`${key}Icon`];

    if (!candidate) return fallback;

    // Icon valid = function (functional component) atau object (defineComponent options)
    if (typeof candidate === 'function' || typeof candidate === 'object') {
        return candidate;
    }

    return fallback;
}
