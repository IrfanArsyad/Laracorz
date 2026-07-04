import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]): string {
    return twMerge(clsx(inputs));
}

export function initials(name: string | null | undefined, max = 2): string {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) return '?';
    return parts
        .slice(0, max)
        .map((p) => p[0]?.toUpperCase() ?? '')
        .join('');
}

export function debounce<T extends (...args: never[]) => void>(
    fn: T,
    delay = 300,
): (...args: Parameters<T>) => void {
    let timer: ReturnType<typeof setTimeout> | null = null;
    return (...args: Parameters<T>): void => {
        if (timer) clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

/**
 * Format tanggal/jam.
 *
 *   formatDate('2026-06-14T04:24:07.000000Z')          // → "14/06/2026"
 *   formatDate('2026-06-14T04:24:07.000000Z', 'long')  // → "14 Juni 2026"
 *   formatDate('2026-06-14T04:24:07.000000Z', 'datetime') // → "14/06/2026 11:24"
 *   formatDate('2026-06-14T04:24:07.000000Z', 'time')  // → "11:24"
 *   formatDate('2026-06-14T04:24:07.000000Z', 'iso')   // → "2026-06-14 04:24:07" (UTC stripped)
 *   formatDate('2026-06-14T04:24:07.000000Z', 'iso-local') // → "2026-06-14 11:24:07" (local TZ)
 *   formatDate('2026-06-14T04:24:07.000000Z', 'human') // → "2 jam yang lalu"
 */
export function formatDate(
    value: string | Date | null | undefined,
    style: 'short' | 'long' | 'datetime' | 'time' | 'iso' | 'iso-local' | 'human' = 'short',
): string {
    if (!value) return '-';

    // Format "ISO": jangan parse — strip "T" dan microsecond + trailing Z, supaya
    // return persis seperti yang Carbon serializeUsing() kirim ("Y-m-d H:i:s").
    if (style === 'iso' && typeof value === 'string') {
        return value
            .replace('T', ' ')
            .replace(/\.\d+/, '')
            .replace(/Z$/, '')
            .trim();
    }

    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) return '-';

    if (style === 'iso-local') {
        const pad = (n: number) => String(n).padStart(2, '0');
        return (
            `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ` +
            `${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`
        );
    }

    if (style === 'human') {
        const diff = (Date.now() - date.getTime()) / 1000;
        if (diff < 60) return 'baru saja';
        if (diff < 3600) return `${Math.floor(diff / 60)} menit yang lalu`;
        if (diff < 86_400) return `${Math.floor(diff / 3600)} jam yang lalu`;
        if (diff < 604_800) return `${Math.floor(diff / 86_400)} hari yang lalu`;
        if (diff < 2_592_000) return `${Math.floor(diff / 604_800)} minggu yang lalu`;
        if (diff < 31_536_000) return `${Math.floor(diff / 2_592_000)} bulan yang lalu`;
        return `${Math.floor(diff / 31_536_000)} tahun yang lalu`;
    }

    const opts: Intl.DateTimeFormatOptions =
        style === 'long'
            ? { day: 'numeric', month: 'long', year: 'numeric' }
            : style === 'datetime'
              ? { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }
              : style === 'time'
                ? { hour: '2-digit', minute: '2-digit' }
                : { day: '2-digit', month: '2-digit', year: 'numeric' };
    return new Intl.DateTimeFormat('id-ID', opts).format(date);
}

/**
 * Shortcut khusus untuk format Y-m-d H:i:s (stamp Carbon).
 * Sama persis dengan output backend `Carbon::serializeUsing` di AppServiceProvider.
 *
 *   formatTimestamp('2026-06-14T04:24:07.000000Z') // → "2026-06-14 04:24:07"
 *   formatTimestamp('2026-06-14 04:24:07')         // → "2026-06-14 04:24:07" (passthrough)
 *
 * Pakai option `local: true` untuk konversi ke zona waktu user:
 *   formatTimestamp('2026-06-14T04:24:07.000000Z', { local: true }) // → "2026-06-14 11:24:07"
 */
export function formatTimestamp(
    value: string | Date | null | undefined,
    options: { local?: boolean } = {},
): string {
    return formatDate(value, options.local ? 'iso-local' : 'iso');
}

export function formatCurrency(
    value: number | string | null | undefined,
    currency = 'IDR',
): string {
    if (value === null || value === undefined || value === '') return '-';
    const num = typeof value === 'number' ? value : Number(value);
    if (Number.isNaN(num)) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(num);
}

export function formatNumber(value: number | string | null | undefined): string {
    if (value === null || value === undefined || value === '') return '-';
    const num = typeof value === 'number' ? value : Number(value);
    if (Number.isNaN(num)) return '-';
    return new Intl.NumberFormat('id-ID').format(num);
}

export function uniqueId(prefix = 'id'): string {
    return `${prefix}-${Math.random().toString(36).slice(2, 10)}`;
}
