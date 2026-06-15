import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h, type DefineComponent } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { i18n } from './i18n';

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

/**
 * Fallback ke halaman 404 supaya tidak crash kalau modul/page tidak ada.
 * Console warn agar developer tahu, tapi UI tetap mulus.
 */
async function fallback404(reason: string): Promise<DefineComponent> {
    console.warn('[Inertia resolver]', reason);
    const loader = corePages['./pages/errors/404.vue'];
    if (!loader) {
        throw new Error(`Halaman fallback errors/404 tidak ada. ${reason}`);
    }
    return (await loader()).default as DefineComponent;
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
            return fallback404(`Halaman modul tidak ditemukan: ${name} (expected ${key})`);
        }
        return (await loader()).default as DefineComponent;
    }

    const key = `./pages/${name}.vue`;
    const loader = corePages[key];
    if (!loader) {
        return fallback404(`Halaman core tidak ditemukan: ${name} (expected ${key})`);
    }
    return (await loader()).default as DefineComponent;
}

createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),
    resolve: resolvePage,
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            .use(ZiggyVue as any)
            .mount(el);
    },
    progress: {
        color: 'hsl(var(--primary))',
        showSpinner: false,
    },
});
