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
 * Validasi cukup permissive: cek `typeof === 'object'` plus exclude known
 * non-icon exports (mis. `createLucideIcon`, `icons`, `default`) supaya tidak
 * crash di runtime.
 */

/** Export dari lucide-vue-next yang BUKAN komponen icon */
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

    // Coba beberapa variant: Folder, FolderIcon, dst (lucide kadang ada suffix Icon)
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const candidate =
        (Icons as any)[key] ??
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        (Icons as any)[`${key}Icon`];

    if (!candidate || typeof candidate !== 'object') return fallback;

    return candidate;
}
