import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h, type DefineComponent } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'LaraCorz';

const corePages = import.meta.glob<DefineComponent>('./pages/**/*.vue');
const modulePages = import.meta.glob<DefineComponent>(
    '../../modules/*/Resources/**/*.vue',
);
// Auto-load per-module CSS (lazy, loaded only when module pages are visited).
const moduleStyles = import.meta.glob('../../modules/*/assets/css/*.css');

const loadedModuleStyles = new Set<string>();

async function loadModuleStyles(moduleStudly: string): Promise<void> {
    if (loadedModuleStyles.has(moduleStudly)) return;
    const prefix = `../../modules/${moduleStudly}/assets/css/`;
    const entries = Object.entries(moduleStyles).filter(([k]) => k.startsWith(prefix));
    if (entries.length === 0) {
        loadedModuleStyles.add(moduleStudly);
        return;
    }
    await Promise.all(entries.map(([, loader]) => loader()));
    loadedModuleStyles.add(moduleStudly);
}

async function resolvePage(name: string): Promise<DefineComponent> {
    if (name.includes('::')) {
        const [moduleName, ...rest] = name.split('::');
        const pagePath = rest.join('::');
        const moduleStudly = moduleName
            .split(/[-_]/)
            .map((p) => p.charAt(0).toUpperCase() + p.slice(1))
            .join('');
        await loadModuleStyles(moduleStudly);
        const key = `../../modules/${moduleStudly}/Resources/${pagePath}.vue`;
        const loader = modulePages[key];
        if (!loader) {
            throw new Error(`Halaman modul tidak ditemukan: ${name} (${key})`);
        }
        return (await loader()).default as DefineComponent;
    }

    const key = `./pages/${name}.vue`;
    const loader = corePages[key];
    if (!loader) {
        throw new Error(`Halaman core tidak ditemukan: ${name} (${key})`);
    }
    return (await loader()).default as DefineComponent;
}

createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),
    resolve: resolvePage,
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            .use(ZiggyVue as any)
            .mount(el);
    },
    progress: {
        color: 'hsl(var(--primary))',
        showSpinner: false,
    },
});
