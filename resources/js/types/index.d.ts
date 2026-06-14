export interface Role {
    id: number;
    name: string;
    display_name: string;
    description: string | null;
    is_active: boolean;
}

export interface User {
    id: number;
    name: string;
    username: string | null;
    email: string;
    avatar: string | null;
    avatar_url: string | null;
    role_id: number | null;
    role: Role | null;
    status: 'active' | 'inactive' | 'banned';
    email_verified_at: string | null;
    last_login_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface ModuleNode {
    id: number;
    name: string;
    label: string;
    icon: string | null;
    url: string | null;
    route_name: string | null;
    badge_source: string | null;
    external: boolean;
    children: ModuleNode[];
}

export interface MenuGroup {
    id: number;
    name: string;
    label: string;
    icon: string | null;
    modules: ModuleNode[];
}

export interface Permissions {
    read: Array<number | string>;
    create: Array<number | string>;
    update: Array<number | string>;
    delete: Array<number | string>;
}

export interface ModuleMap {
    id: number;
    name: string;
}

export interface FlashMessages {
    success: string | null;
    error: string | null;
    warning: string | null;
    info: string | null;
}

export interface AppConfig {
    name: string;
    locale: string;
    fallback_locale: string;
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export interface PageProps<T extends Record<string, unknown> = Record<string, unknown>> {
    auth: {
        user: User | null;
        permissions: Permissions | null;
    };
    menu: MenuGroup[];
    modules: ModuleMap[];
    flash: FlashMessages;
    app: AppConfig;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    ziggy: any;
    errors: Record<string, string>;
    props: T;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginationMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    links: PaginationLink[];
    path: string;
    per_page: number;
    to: number | null;
    total: number;
}

export interface Paginated<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: PaginationMeta;
}

declare global {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const route: any;
    interface Window {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        route: any;
    }
}
