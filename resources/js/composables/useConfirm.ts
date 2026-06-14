import { reactive } from 'vue';

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
    confirmLabel: 'Ya',
    cancelLabel: 'Batal',
    variant: 'default',
    resolve: null,
});

function confirm(opts: ConfirmOptions): Promise<boolean> {
    return new Promise<boolean>((resolve) => {
        state.title = opts.title ?? 'Konfirmasi';
        state.message = opts.message;
        state.confirmLabel = opts.confirmLabel ?? 'Ya';
        state.cancelLabel = opts.cancelLabel ?? 'Batal';
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
