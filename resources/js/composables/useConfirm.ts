import { reactive } from 'vue';
import { i18n } from '@/i18n';

export interface ConfirmOptions {
    title?: string;
    message: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'default' | 'destructive';
}

interface ConfirmState extends ConfirmOptions {
    open: boolean;
    resolve: ((v: boolean) => void) | null;
}

const state = reactive<ConfirmState>({
    open: false,
    title: '',
    message: '',
    confirmLabel: '',
    cancelLabel: '',
    variant: 'default',
    resolve: null,
});

function t(key: string): string {
    return i18n.global.t(key);
}

function confirm(opts: ConfirmOptions): Promise<boolean> {
    return new Promise<boolean>((resolve) => {
        state.title = opts.title ?? t('common.confirm');
        state.message = opts.message;
        state.confirmLabel = opts.confirmLabel ?? t('common.yes');
        state.cancelLabel = opts.cancelLabel ?? t('common.cancel');
        state.variant = opts.variant ?? 'default';
        state.resolve = resolve;
        state.open = true;
    });
}

function resolve(value: boolean): void {
    state.open = false;
    state.resolve?.(value);
    state.resolve = null;
}

export function useConfirm() {
    return {
        state,
        confirm,
        accept: () => resolve(true),
        cancel: () => resolve(false),
    };
}
