import { ref, watch, onMounted } from 'vue';

export type ThemeMode = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'theme';

const mode = ref<ThemeMode>(
    (typeof localStorage !== 'undefined' && (localStorage.getItem(STORAGE_KEY) as ThemeMode)) || 'system',
);

function applyTheme(value: ThemeMode): void {
    const isDark =
        value === 'dark' ||
        (value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDark);
}

function setMode(value: ThemeMode): void {
    mode.value = value;
    try {
        localStorage.setItem(STORAGE_KEY, value);
    } catch {
        /* ignore */
    }
    applyTheme(value);
}

export function useTheme() {
    onMounted(() => applyTheme(mode.value));

    watch(mode, (v) => applyTheme(v));

    if (typeof window !== 'undefined') {
        const media = window.matchMedia('(prefers-color-scheme: dark)');
        media.addEventListener('change', () => {
            if (mode.value === 'system') applyTheme('system');
        });
    }

    return {
        mode,
        setMode,
        toggle: () => setMode(mode.value === 'dark' ? 'light' : 'dark'),
    };
}
