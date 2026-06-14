# UI Components

Semua komponen di `resources/js/components/ui/`. Pola: TypeScript + `<script setup>` + CVA variant + `cn()` merge.

## Form & Input
- **Button** — `variant` (default/destructive/outline/secondary/ghost/link/success/warning), `size`, `loading`, `as` (button/a/link)
- **Input**, **InputPassword**, **InputNumber** (format id-ID), **Textarea**
- **Checkbox** (boolean atau array), **Switch**, **RadioGroup**
- **Select** (searchable, clearable, keyboard nav, teleport), **MultiSelect** (chip + select all), **Combobox** (async fetcher)
- **DatePicker** (kalender buatan sendiri, format id)
- **FileUpload** (drag&drop, preview gambar, validasi ukuran)
- **FormField** (label/hint/error/required, inject id), **FormSection**, **FormActions** (sticky)

## Overlay
- **Modal** (size sm/md/lg/xl/full, focus, ESC, overlay close, scroll lock)
- **ConfirmDialog** + `useConfirm()` (promise-based)
- **Drawer** (kanan/kiri/bawah)
- **DropdownMenu** + Item/Label/Separator (floating, click-outside, keyboard)
- **Tooltip** (4 posisi, delay)
- **Popover**
- **Toast** + `useToast()` (variant success/error/warning/info, stack max 5, pause hover)

## Data Display
- **DataTable** (kolom config + slot per-cell, sort header, selection + bulk, skeleton, empty, sticky action)
- **Pagination** (terima meta Laravel)
- **Badge** (default/secondary/destructive/outline/success/warning/info/muted)
- **Card** + Header/Title/Description/Content/Footer
- **Avatar** (fallback inisial), **Tabs**, **Accordion**, **Alert** (dismissible)
- **Skeleton**, **Spinner**, **EmptyState**, **Separator**, **Kbd**
- **DescriptionList**, **StatCard** (trend ±%), **Breadcrumb**
- **CommandPalette** (Ctrl+K, sumber shared menu), **ThemeToggle**

## Pattern variant (Button)
```ts
const buttonVariants = cva(
  'inline-flex ... rounded-md text-sm font-medium ...',
  {
    variants: {
      variant: { default: 'bg-primary ...', destructive: 'bg-destructive ...', ... },
      size: { default: 'h-10 px-4', sm: 'h-9 px-3', lg: 'h-11 px-8', icon: 'h-10 w-10' },
    },
    defaultVariants: { variant: 'default', size: 'default' },
  },
)
```

## Convention
- Semua input dukung `v-model`.
- Semua komponen terima prop `class` yang di-merge `cn()`.
- Overlay (Modal, Drawer, DropdownMenu) pakai `<Teleport to="body">` + `<Transition>`.
- Dark mode konsisten via CSS variables di `_tokens.scss`.
- A11y dasar: ARIA role/state, focus ring, keyboard handler.
