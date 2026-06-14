import { reactive } from 'vue';
import { TOAST_MAX_STACK, TOAST_DEFAULT_DURATION } from '@/lib/constants';

export type ToastVariant = 'success' | 'error' | 'warning' | 'info';

export interface Toast {
    id: number;
    title?: string;
    message: string;
    variant: ToastVariant;
    duration: number;
}

interface ToastInput {
    title?: string;
    message: string;
    variant?: ToastVariant;
    duration?: number;
}

const state = reactive<{ toasts: Toast[] }>({ toasts: [] });

let counter = 0;

function push(input: ToastInput): number {
    const toast: Toast = {
        id: ++counter,
        title: input.title,
        message: input.message,
        variant: input.variant ?? 'info',
        duration: input.duration ?? TOAST_DEFAULT_DURATION,
    };

    state.toasts.push(toast);
    while (state.toasts.length > TOAST_MAX_STACK) state.toasts.shift();

    if (toast.duration > 0) {
        setTimeout(() => dismiss(toast.id), toast.duration);
    }

    return toast.id;
}

function dismiss(id: number): void {
    const idx = state.toasts.findIndex((t) => t.id === id);
    if (idx > -1) state.toasts.splice(idx, 1);
}

export function useToast() {
    return {
        toasts: state.toasts,
        push,
        dismiss,
        success: (message: string, title?: string) => push({ message, title, variant: 'success' }),
        error: (message: string, title?: string) => push({ message, title, variant: 'error' }),
        warning: (message: string, title?: string) => push({ message, title, variant: 'warning' }),
        info: (message: string, title?: string) => push({ message, title, variant: 'info' }),
    };
}
