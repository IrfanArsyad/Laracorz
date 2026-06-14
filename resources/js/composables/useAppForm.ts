import { useForm } from '@inertiajs/vue3';
import { useToast } from './useToast';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function useAppForm<T extends Record<string, any>>(data: T) {
    const form = useForm<T>(data);
    const toast = useToast();

    function onError(): void {
        const first = Object.values(form.errors)[0];
        if (first && typeof first === 'string') {
            toast.error(first);
        }
    }

    function submit(method: 'post' | 'put' | 'patch' | 'delete', url: string, options: Record<string, unknown> = {}): void {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        form[method](url, {
            preserveScroll: true,
            onError,
            ...(options as any),
        });
    }

    return { form, submit, toast };
}
