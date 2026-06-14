import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ModuleMap, Permissions } from '@/types';

type Action = 'read' | 'create' | 'update' | 'delete';

export function usePermission() {
    const page = usePage();

    const permissions = computed<Permissions | null>(() => page.props.auth?.permissions ?? null);
    const modules = computed<ModuleMap[]>(() => page.props.modules ?? []);

    function resolveId(module: number | string): number | null {
        if (typeof module === 'number') return module;
        const found = modules.value.find((m) => m.name === module);
        return found ? found.id : null;
    }

    function can(action: Action, module: number | string): boolean {
        const perms = permissions.value;
        if (!perms) return false;
        const list = perms[action] ?? [];
        if (list.includes('*')) return true;

        const id = resolveId(module);
        if (id === null) return false;

        return list.map((v) => (typeof v === 'string' ? Number(v) : v)).includes(id);
    }

    function canExtra(moduleName: string, action: string): boolean {
        if (isSuperAdmin.value) return true;
        // extra not yet wired in props; placeholder false
        const _ = `${moduleName}:${action}`;
        return false;
    }

    const isSuperAdmin = computed(() => {
        const list = permissions.value?.read ?? [];
        return list.includes('*');
    });

    function canAny(action: Action, modules: Array<number | string>): boolean {
        return modules.some((m) => can(action, m));
    }

    return { can, canAny, canExtra, isSuperAdmin };
}
