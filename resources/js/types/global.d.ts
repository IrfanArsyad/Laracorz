import type { AxiosInstance } from 'axios';
import type { PageProps as AppPageProps } from './';

declare global {
    interface Window {
        axios: AxiosInstance;
    }

    // eslint-disable-next-line no-var, @typescript-eslint/no-explicit-any
    var route: any;
}

declare module '@inertiajs/core' {
    // eslint-disable-next-line @typescript-eslint/no-empty-object-type -- declaration merging requires an interface to augment Inertia's PageProps
    interface PageProps extends AppPageProps {}
}

export {};
