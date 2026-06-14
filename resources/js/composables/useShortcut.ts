import { onMounted, onUnmounted } from 'vue';

type Modifier = 'ctrl' | 'meta' | 'shift' | 'alt';

interface ShortcutDef {
    key: string;
    modifiers?: Modifier[];
}

function parse(combo: string): ShortcutDef {
    const parts = combo.toLowerCase().split('+').map((p) => p.trim());
    const key = parts.pop() ?? '';
    return { key, modifiers: parts as Modifier[] };
}

function matches(e: KeyboardEvent, def: ShortcutDef): boolean {
    if (e.key.toLowerCase() !== def.key) return false;
    const mods = def.modifiers ?? [];
    if (mods.includes('ctrl') !== (e.ctrlKey || e.metaKey)) return false;
    if (mods.includes('shift') !== e.shiftKey) return false;
    if (mods.includes('alt') !== e.altKey) return false;
    return true;
}

export function useShortcut(combo: string, handler: (e: KeyboardEvent) => void): void {
    const def = parse(combo);

    function onKey(e: KeyboardEvent): void {
        if (matches(e, def)) {
            handler(e);
        }
    }

    onMounted(() => window.addEventListener('keydown', onKey));
    onUnmounted(() => window.removeEventListener('keydown', onKey));
}
