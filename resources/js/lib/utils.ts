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

export function debounce<T extends (...args: unknown[]) => void>(
    fn: T,
    delay = 300,
): (...args: Parameters<T>) => void {
    let timer: ReturnType<typeof setTimeout> | null = null;
    return (...args: Parameters<T>): void => {
        if (timer) clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

export function formatDate(
    value: string | Date | null | undefined,
    style: 'short' | 'long' | 'datetime' = 'short',
): string {
    if (!value) return '-';
    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) return '-';
    const opts: Intl.DateTimeFormatOptions =
        style === 'long'
            ? { day: 'numeric', month: 'long', year: 'numeric' }
            : style === 'datetime'
              ? { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }
              : { day: '2-digit', month: '2-digit', year: 'numeric' };
    return new Intl.DateTimeFormat('id-ID', opts).format(date);
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
