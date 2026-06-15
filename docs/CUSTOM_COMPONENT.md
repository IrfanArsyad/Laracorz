# Membuat / Custom Component

Panduan untuk:
1. Menambah komponen UI baru ke library
2. Custom (override) komponen yang sudah ada
3. Membuat composable baru
4. Bikin preset modal baru

> Sebelum bikin baru: **cek dulu di `resources/js/components/ui/` & `resources/js/composables/`** apakah sudah ada yang fit. Konsistensi > convenience.

---

## 1. File structure

Tiap komponen punya folder sendiri di `resources/js/components/ui/`:

```
resources/js/components/ui/
└── MyComponent/
    ├── MyComponent.vue       # implementation
    └── index.ts              # named export
```

Untuk komponen multi-part (Card, DropdownMenu, Modal, Tabs, Accordion):

```
resources/js/components/ui/
└── Modal/
    ├── Modal.vue
    ├── ModalHeader.vue
    ├── ModalBody.vue
    ├── ModalFooter.vue
    ├── FormModal.vue           # preset
    ├── DetailModal.vue         # preset
    └── index.ts                # export semua
```

`index.ts` minimal:
```ts
export { default as MyComponent } from './MyComponent.vue';
```

---

## 2. Template `.vue` standar

```vue
<script setup lang="ts">
import { computed } from 'vue';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

// 1️⃣ DEFINISI VARIANT (kalau perlu)
const componentVariants = cva(
  // base classes
  [
    'inline-flex items-center gap-2 rounded-md text-sm font-medium',
    'transition-colors duration-[var(--duration-fast)] ease-[var(--ease-out)]',
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--focus-ring)]',
    'disabled:pointer-events-none disabled:opacity-50',
  ].join(' '),
  {
    variants: {
      variant: {
        default: 'bg-[var(--brand-bg)] text-[var(--brand-fg)] hover:bg-[var(--brand-bg-hover)]',
        outline: 'border border-[var(--border-default)] hover:bg-[var(--state-hover)]',
        ghost: 'hover:bg-[var(--state-hover)]',
      },
      size: {
        default: 'h-10 px-4',
        sm: 'h-8 px-3 text-[13px]',
        xs: 'h-7 px-2 text-xs',
      },
    },
    defaultVariants: { variant: 'default', size: 'default' },
  },
);

type ComponentVariants = VariantProps<typeof componentVariants>;

// 2️⃣ PROPS — selalu pakai withDefaults + interface typed
const props = withDefaults(
  defineProps<{
    modelValue?: string | null;
    variant?: ComponentVariants['variant'];
    size?: ComponentVariants['size'];
    disabled?: boolean;
    class?: string;
  }>(),
  {
    variant: 'default',
    size: 'default',
    disabled: false,
  },
);

// 3️⃣ EMITS — typed
const emit = defineEmits<{
  'update:modelValue': [v: string];
  click: [e: MouseEvent];
}>();

// 4️⃣ COMPUTED CLASSES
const classes = computed(() =>
  cn(componentVariants({ variant: props.variant, size: props.size }), props.class),
);
</script>

<template>
  <button
    :class="classes"
    :disabled="disabled"
    @click="emit('click', $event)"
  >
    <slot />
  </button>
</template>
```

### Hal yang **wajib**:

- ✅ `<script setup lang="ts">`
- ✅ `withDefaults(defineProps<...>(), {...})` — typed, ada default
- ✅ `defineEmits<{...}>()` — typed
- ✅ Prop `class` diterima dan di-merge via `cn()`
- ✅ Pakai design token (`var(--brand-bg)`, `var(--text-default)`), **bukan hardcode warna**
- ✅ Hover/active/focus state pakai semantic state token (`var(--state-hover)`, `var(--state-pressed)`)
- ✅ `focus-visible` ring untuk a11y keyboard
- ✅ Disabled state: `disabled:pointer-events-none disabled:opacity-50`
- ✅ Motion: pakai `--duration-fast` / `--ease-out` token, **bukan duration-150**
- ✅ v-model support kalau ada input state

---

## 3. Design tokens (rujuk, jangan hardcode)

Semua warna **harus** rujuk semantic token di `resources/css/app.css`. Daftar lengkap:

### Surfaces
- `--surface-base` — page bg
- `--surface-raised` — card, modal
- `--surface-sunken` — input bg, alternate row, header tabel
- `--surface-overlay` — modal/dropdown panel
- `--surface-inverse` — tooltip dark on light

### Text
- `--text-strong` — heading
- `--text-default` — body
- `--text-muted` — secondary text, hint, metadata
- `--text-disabled`
- `--text-inverse` — text on inverse surface
- `--text-link` / `--text-link-hover`

### Borders
- `--border-subtle` — divider tipis, card border default
- `--border-default` — input border
- `--border-strong` — input hover border
- `--border-focus` — input focus border (brand)

### States (overlay color-mix)
- `--state-hover` — hover bg overlay
- `--state-pressed` — pressed bg overlay
- `--state-selected` — selected item bg
- `--focus-ring` — focus outline color

### Brand interaction
- `--brand-bg` / `--brand-bg-hover` / `--brand-bg-pressed`
- `--brand-fg` — text di atas brand bg
- `--brand-soft-bg` / `--brand-soft-fg` — soft tone (badge default, secondary button)

### Status (success/warning/danger/info)
- `--status-{kind}-bg` — soft bg
- `--status-{kind}-border` — soft border
- `--status-{kind}-fg` — text
- `--status-{kind}-solid` — solid bg (button destructive, badge solid)
- `--status-{kind}-solid-fg` — text di atas solid

### Radius
- `--radius-xs/sm/md/lg/xl` — pakai utility Tailwind `rounded-{size}`

### Shadow
- `--shadow-xs/sm/md/lg/xl/overlay` — pakai bracket `shadow-[var(--shadow-sm)]`

### Motion
- `--duration-instant` (80ms) / `--duration-fast` (140ms) / `--duration-base` (200ms) / `--duration-slow` (320ms)
- `--ease-out` / `--ease-in-out` / `--ease-spring`

### Contoh pemakaian
```html
<!-- ❌ HARDCODE -->
<div class="bg-white border border-gray-200 text-gray-900 hover:bg-gray-50">

<!-- ✅ TOKEN -->
<div class="bg-[var(--surface-raised)] border border-[var(--border-subtle)] text-[var(--text-default)] hover:bg-[var(--state-hover)]">
```

Sekali pakai semantic token, dark mode auto, kontras AA auto, brand-color global change tinggal edit 1 file.

---

## 4. Aturan a11y

- **ARIA role / state**: `role="dialog"`, `role="menu"`, `role="checkbox"`, `aria-checked`, `aria-expanded`, `aria-haspopup`, `aria-label`
- **Focus management**: 
  - Modal: focus trap + return focus saat tutup
  - Dropdown: keyboard nav ↑↓ Enter Esc
- **Focus ring** via `focus-visible:` (bukan `focus:`) supaya gak nongol saat mouse click
- **Disabled** state: tampak (opacity), tapi `aria-disabled` atau native `disabled`
- **Reduced motion** auto-honored via CSS global di `app.css` (`@media (prefers-reduced-motion: reduce)`)

---

## 5. Slot pattern

### Default slot saja
```vue
<template>
  <button :class="classes"><slot /></button>
</template>
```

### Named slots
```vue
<template>
  <div :class="classes">
    <slot name="prefix" />
    <slot />
    <slot name="suffix" />
  </div>
</template>
```

### Scoped slot (lempar data ke parent)
```vue
<template>
  <ul>
    <li v-for="item in items" :key="item.id">
      <slot :item="item" :index="index">
        <!-- fallback default -->
        {{ item.label }}
      </slot>
    </li>
  </ul>
</template>

<!-- pakainya -->
<MyList :items="users">
  <template #default="{ item }">
    <strong>{{ item.name }}</strong>
  </template>
</MyList>
```

---

## 6. Composition (komponen multi-part)

Pakai `provide`/`inject` untuk komunikasi parent → child tanpa prop drilling.

### Parent
```vue
<script setup>
import { provide, ref, toRef } from 'vue';

const open = ref(false);
provide('myParent:open', toRef(props, 'modelValue'));
provide('myParent:close', () => emit('update:modelValue', false));
</script>
```

### Child
```vue
<script setup>
import { inject, type Ref } from 'vue';

const open = inject<Ref<boolean>>('myParent:open');
const close = inject<() => void>('myParent:close', () => {});
</script>
```

Contoh lihat: `Modal/Modal.vue` + `ModalHeader.vue` (header consume `modal:close`).

---

## 7. Animasi & transition

Pakai `<Transition>` Vue dengan motion token:

```vue
<Transition
  enter-active-class="transition duration-[var(--duration-base)] ease-[var(--ease-out)]"
  enter-from-class="opacity-0 translate-y-1 scale-[0.98]"
  enter-to-class="opacity-100 translate-y-0 scale-100"
  leave-active-class="transition duration-[var(--duration-fast)] ease-[var(--ease-in-out)]"
  leave-from-class="opacity-100"
  leave-to-class="opacity-0"
>
  <div v-if="open">...</div>
</Transition>
```

Untuk overlay (modal, drawer, dropdown): dual transition — outer fade backdrop + inner scale/translate panel.

---

## 8. Mendaftarkan komponen baru

1. Buat folder + `.vue` + `index.ts`
2. Pakai langsung tanpa register global:
   ```vue
   import { MyComponent } from '@/components/ui/MyComponent';
   ```
3. (Opsional) tambah ke `docs/COMPONENTS.md`

Tidak perlu register di `app.ts` — auto-discover via import.

---

## 9. Custom (override) komponen existing

### Pilihan A: forward dengan default override

Buat wrapper di `modules/{Module}/Resources/components/`:

```vue
<!-- modules/UserManagement/Resources/components/UserBadge.vue -->
<script setup>
import { Badge } from '@/components/ui/Badge';
import { USER_STATUS } from '@/types/enums';

defineProps<{ status: 'active' | 'inactive' | 'banned' }>();
</script>

<template>
  <Badge :variant="USER_STATUS[status]?.color ?? 'muted'">
    {{ USER_STATUS[status]?.label ?? status }}
  </Badge>
</template>
```

### Pilihan B: pass class custom (paling sering)

```vue
<Button variant="ghost" class="text-[var(--status-danger-fg)] hover:bg-[var(--status-danger-bg)]">
  Hapus
</Button>
```

cn() + twMerge handle override otomatis.

### Pilihan C: extend variant CVA

Kalau perlu varian baru yang dipakai banyak tempat, edit CVA di komponen langsung (mis. Button tambah `variant: success`). Jangan bikin komponen mirip-mirip baru.

---

## 10. Bikin composable baru

Pattern di `resources/js/composables/use{Name}.ts`:

```ts
import { ref, computed } from 'vue';

export function useCounter(initial = 0) {
  const count = ref(initial);

  const isZero = computed(() => count.value === 0);

  function inc(by = 1): void {
    count.value += by;
  }
  function dec(by = 1): void {
    count.value -= by;
  }
  function reset(): void {
    count.value = initial;
  }

  return { count, isZero, inc, dec, reset };
}
```

### Aturan composable
- Nama: `use{Noun}` atau `use{Verb}` — camelCase
- Generic: `useModal<T>()` kalau perlu typed context data
- Return: `{ ... }` object, semua reactive sudah `ref`/`computed`
- Side effect cleanup: pakai `onUnmounted` (lihat `useShortcut`, `useTheme`)
- Single state composable: state module-level (singleton, mis. `useToast`/`useConfirm`)
- Per-instance: state di dalam `useFoo()` body (mis. `useModal<T>()`)

---

## 11. Membuat preset Modal baru

Contoh: `WizardModal` — modal multi-step.

```vue
<!-- resources/js/components/ui/Modal/WizardModal.vue -->
<script setup lang="ts">
import { computed, ref } from 'vue';
import Modal from './Modal.vue';
import ModalHeader from './ModalHeader.vue';
import ModalBody from './ModalBody.vue';
import ModalFooter from './ModalFooter.vue';
import Button from '../Button/Button.vue';

const props = withDefaults(
  defineProps<{
    modelValue: boolean;
    title?: string;
    steps: Array<{ key: string; label: string }>;
    processing?: boolean;
    size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
  }>(),
  { size: 'lg', processing: false },
);

const emit = defineEmits<{
  'update:modelValue': [v: boolean];
  finish: [];
}>();

const currentIndex = ref(0);
const currentStep = computed(() => props.steps[currentIndex.value]);
const isFirst = computed(() => currentIndex.value === 0);
const isLast = computed(() => currentIndex.value === props.steps.length - 1);

function next() { if (!isLast.value) currentIndex.value++; }
function prev() { if (!isFirst.value) currentIndex.value--; }
function finish() { emit('finish'); }
</script>

<template>
  <Modal
    :model-value="modelValue"
    :size="size"
    :body-padding="false"
    @update:model-value="(v) => emit('update:modelValue', v)"
  >
    <ModalHeader :title="title" :description="`Langkah ${currentIndex + 1} dari ${steps.length}: ${currentStep.label}`" />
    <ModalBody>
      <slot :step="currentStep.key" :step-key="currentStep.key" />
    </ModalBody>
    <ModalFooter align="between">
      <Button variant="ghost" :disabled="isFirst" @click="prev">Sebelumnya</Button>
      <Button v-if="!isLast" @click="next">Lanjut</Button>
      <Button v-else :loading="processing" @click="finish">Selesai</Button>
    </ModalFooter>
  </Modal>
</template>
```

Lalu export di `Modal/index.ts`:
```ts
export { default as WizardModal } from './WizardModal.vue';
```

---

## 12. Checklist sebelum merge

- [ ] File di lokasi yang benar (`resources/js/components/ui/` atau `modules/{X}/Resources/components/`)
- [ ] `index.ts` export
- [ ] TypeScript types lengkap (no `any` kecuali memang harus)
- [ ] Props pakai `withDefaults` + default value yang aman
- [ ] Emits typed
- [ ] Terima prop `class` di-merge `cn()`
- [ ] **Semua warna** pakai semantic token, bukan hardcode
- [ ] Hover/active/focus/disabled state ada
- [ ] `focus-visible` ring untuk a11y
- [ ] Dark mode otomatis works (via token)
- [ ] Reduced motion otomatis works (global CSS)
- [ ] Motion durasi pakai token, bukan magic ms
- [ ] A11y: ARIA role/state/label, keyboard nav untuk overlay
- [ ] Update `docs/COMPONENTS.md` (kalau komponen UI library)
- [ ] Tidak duplikasi komponen existing (sudah cek `ui/` dulu)

---

## 13. Anti-pattern (jangan lakukan)

❌ Bikin button dengan `<button class="...">` manual — pakai `<Button>`
❌ Bikin overlay dengan `<div fixed inset-0>` — pakai `<Modal>` atau preset
❌ Hardcode warna `bg-white`, `text-gray-900`, `border-gray-200` — pakai token
❌ `transition duration-150` — pakai `--duration-fast` token
❌ Akses Inertia useForm di composable global — pass form sebagai param atau pakai per-instance
❌ Duplikasi state composable — pakai existing (`useModal`, `useConfirm`, `useToast`)
❌ Komponen baru tanpa prop `class` — selalu allow override
❌ Skip `focus-visible` ring — keyboard user gak bisa lihat focus
❌ Mainkan z-index manual — pakai tier z-30 (topbar), z-40 (sidebar), z-50 (modal/dropdown)
