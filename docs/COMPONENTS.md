# UI Components

Referensi lengkap pemakaian library UI di `resources/js/components/ui/` + composable di `resources/js/composables/`.

Semua komponen sudah:
- TypeScript + `<script setup>`
- Pakai design token (OKLCH 3-tier) — otomatis support dark mode
- Honor `prefers-reduced-motion`
- A11y dasar (ARIA, focus-visible, keyboard nav)
- Bisa terima prop `class` yang di-merge `cn()` (clsx + twMerge)

---

## Daftar Isi

- [Form & Input](#-form--input)
- [Display](#-display)
- [Overlay & Modal](#-overlay--modal)
- [Data](#-data)
- [Layout](#-layout)
- [Composables](#-composables)
- [Pattern halaman](#-pattern-halaman)

---

## 🎛 Form & Input

### `Button`

```vue
<script setup>
import { Button } from '@/components/ui/Button';
import { Plus } from 'lucide-vue-next';
</script>

<template>
  <Button @click="save">Simpan</Button>
  <Button variant="outline" size="sm"><Plus class="h-4 w-4" /> Tambah</Button>
  <Button as="link" href="/users">Lihat semua</Button>
  <Button variant="destructive" :loading="form.processing">Hapus</Button>
</template>
```

| Prop | Type | Default | Catatan |
|---|---|---|---|
| `variant` | `'default'\|'destructive'\|'success'\|'warning'\|'outline'\|'secondary'\|'ghost'\|'link'` | `default` | |
| `size` | `'default'\|'sm'\|'xs'\|'lg'\|'icon'\|'icon-sm'\|'icon-xs'` | `default` (h-10) | |
| `loading` | `boolean` | `false` | tampil spinner + disable |
| `disabled` | `boolean` | `false` | |
| `as` | `'button'\|'a'\|'link'` | `button` | `link` = Inertia `<Link>` |
| `href` | `string` | — | wajib jika `as='link'\|'a'` |
| `type` | `'button'\|'submit'\|'reset'` | `button` | |

### `Input`

```vue
<Input v-model="form.name" placeholder="Nama..." :error="form.errors.name" />
<Input v-model="search">
  <template #prefix><Search class="h-4 w-4" /></template>
  <template #suffix><button @click="search=''"><X class="h-3 w-3" /></button></template>
</Input>
```

| Prop | Type | Catatan |
|---|---|---|
| `modelValue` | `string\|number\|null` | v-model |
| `type` | `string` | default `text` |
| `error` | `boolean\|string` | border merah + ring danger |
| `disabled` / `readonly` | `boolean` | |

Slot: `prefix`, `suffix` — diisi icon/button.

### `InputPassword`

Sama seperti Input + toggle show/hide built-in.

```vue
<InputPassword v-model="form.password" autocomplete="new-password" />
```

### `InputNumber`

Format ribuan Indonesia otomatis (`id-ID`).

```vue
<InputNumber v-model="form.harga" :min="0" :max="1000000" :step="1000" />
<InputNumber v-model="form.qty" :format="false" />  <!-- tanpa format -->
```

### `Textarea`

```vue
<Textarea v-model="form.description" :rows="3" auto-resize />
```

### `Checkbox`

Single boolean atau array (multi-check):

```vue
<!-- boolean -->
<Checkbox v-model="agreed" />

<!-- array (multi) -->
<Checkbox v-model="selected" :value="row.id" />

<!-- indeterminate -->
<Checkbox :model-value="all" :indeterminate="some" @update:model-value="toggleAll" />
```

### `Switch`

```vue
<Switch v-model="form.is_active" />
```

### `RadioGroup`

```vue
<RadioGroup
  v-model="form.tier"
  :options="[
    { label: 'Free', value: 'free' },
    { label: 'Pro', value: 'pro', disabled: true },
  ]"
  orientation="horizontal"
/>
```

### `Select`

Dropdown custom (bukan native), searchable, keyboard nav, clearable.

```vue
<Select
  v-model="form.role_id"
  :options="[
    { label: 'Admin', value: 1 },
    { label: 'Editor', value: 2 },
  ]"
  placeholder="Pilih role..."
  searchable
  clearable
/>
```

| Prop | Type | Catatan |
|---|---|---|
| `options` | `{label, value, disabled?}[]` | |
| `searchable` | `boolean` | tambah search box di dropdown |
| `clearable` | `boolean` | tampil tombol X untuk clear |
| `placeholder` | `string` | |

### `MultiSelect`

```vue
<MultiSelect
  v-model="form.tags"
  :options="tagOptions"
  placeholder="Pilih tag..."
/>
```

Chips di-render untuk setiap nilai terpilih, ada "Pilih semua" / "Bersihkan".

### `Combobox` (async search)

```vue
<Combobox
  v-model="form.user_id"
  :fetcher="async (q) => (await axios.get('/api/users/search', { params: { q } })).data"
  :initial-label="user?.name"
  placeholder="Cari pengguna..."
/>
```

### `DatePicker`

Kalender custom (zero dependency), format Indonesia.

```vue
<DatePicker v-model="form.tanggal" placeholder="Pilih tanggal" />
```

modelValue = ISO string `YYYY-MM-DD`.

### `FileUpload`

Drag & drop + preview image + validasi ukuran.

```vue
<FileUpload
  v-model="form.avatar"
  accept="image/*"
  :max-size="2 * 1024 * 1024"
  @error="(msg) => toast.error(msg)"
/>
```

### `FormField`

Wrapper label + hint + error (untuk error Inertia).

```vue
<FormField label="Email" :error="form.errors.email" hint="Pakai email kantor" required>
  <Input v-model="form.email" type="email" />
</FormField>
```

| Prop | Type | Catatan |
|---|---|---|
| `label` | `string` | |
| `hint` | `string` | text muted di bawah (auto-hide kalau ada error) |
| `error` | `string\|string[]` | text danger di bawah |
| `required` | `boolean` | tampil `*` merah |

### `FormSection` & `FormActions`

```vue
<FormSection title="Informasi Akun" description="Identitas dan akses.">
  <FormField label="Nama"><Input v-model="form.name" /></FormField>
  <FormField label="Email"><Input v-model="form.email" /></FormField>
</FormSection>

<FormSection title="Foto" layout="split">
  <!-- layout split: header kiri 1/3, fields kanan 2/3 (formal) -->
</FormSection>

<FormActions>
  <Button as="link" href="/users" variant="ghost">Batal</Button>
  <Button type="submit" :loading="form.processing">Simpan</Button>
</FormActions>
```

`FormSection.layout`: `'stacked'` (default, header di atas) atau `'split'` (header kiri 1/3).

---

## 🖼 Display

### `Badge`

Soft-tone style (bukan solid mencolok).

```vue
<Badge>Default</Badge>
<Badge variant="success">Aktif</Badge>
<Badge variant="warning">Pending</Badge>
<Badge variant="destructive">Diblokir</Badge>
<Badge variant="muted">Draft</Badge>
<Badge variant="outline">Outline</Badge>
<Badge variant="solid">99+</Badge>  <!-- emphasis tinggi -->
```

Variants: `default | secondary | muted | outline | success | warning | destructive | info | solid`.

### `Avatar`

```vue
<Avatar :src="user.avatar_url" :name="user.name" size="md" />
<!-- fallback initials kalau tidak ada src -->
```

Sizes: `xs | sm | md | lg | xl`.

### `Card`

```vue
<Card>
  <CardHeader>
    <CardTitle>Judul</CardTitle>
    <CardDescription>Deskripsi singkat.</CardDescription>
  </CardHeader>
  <CardContent>
    Isi card.
  </CardContent>
  <CardFooter>
    <Button>Aksi</Button>
  </CardFooter>
</Card>
```

### `Alert`

```vue
<Alert variant="warning" title="Perhatian" dismissible @dismiss="hide">
  Cek konfigurasi sebelum melanjutkan.
</Alert>
```

Variants: `default | info | success | warning | destructive`.

### `Skeleton` / `Spinner` / `EmptyState`

```vue
<Skeleton class="h-6 w-24" />
<Spinner size="md" />
<EmptyState
  title="Belum ada data"
  description="Klik 'Tambah' untuk membuat entri pertama."
  :icon="Inbox"
>
  <Button>Tambah</Button>
</EmptyState>
```

### `Separator`, `Kbd`

```vue
<Separator />
<Separator orientation="vertical" />
<Kbd>Ctrl+K</Kbd>
```

### `DescriptionList`

```vue
<DescriptionList :items="[
  { label: 'Nama', value: user.name },
  { label: 'Email', value: user.email },
  { label: 'Role', value: user.role?.display_name },
]" />
```

### `StatCard`

```vue
<StatCard label="Total Pengguna" :value="stats.total" :icon="Users" />
<StatCard label="Aktif" :value="stats.active" :icon="UserCheck" :trend="12" />
<StatCard label="Loading..." :loading="!stats" :icon="Activity" hint="dari minggu lalu" />
```

### `Breadcrumb`

```vue
<Breadcrumb :items="[
  { label: 'User & Access' },
  { label: 'Pengguna', href: '/users' },
  { label: 'Edit' },
]" />
```

---

## 🪟 Overlay & Modal

### `Modal` primitive + parts

```vue
<script setup>
import { Modal, ModalHeader, ModalBody, ModalFooter } from '@/components/ui/Modal';
import { useModal } from '@/composables/useModal';

const modal = useModal();
</script>

<template>
  <Button @click="modal.open()">Buka</Button>

  <Modal v-model="modal.isOpen.value" size="lg" :body-padding="false">
    <ModalHeader title="Judul" description="Subtitle" />
    <ModalBody>
      <p>Konten modal...</p>
    </ModalBody>
    <ModalFooter align="between">
      <Button variant="ghost" @click="modal.close()">Batal</Button>
      <Button>Lanjut</Button>
    </ModalFooter>
  </Modal>
</template>
```

Sizes: `xs | sm | md | lg | xl | 2xl | full`. Props: `closeOnEsc`, `closeOnOverlay`, `bodyPadding`.

### `FormModal` (preset form simpel)

Auto-render header + body + footer (Batal/Simpan).

```vue
<script setup>
import { FormModal } from '@/components/ui/Modal';
import { useForm } from '@inertiajs/vue3';
import { useModal } from '@/composables/useModal';

const formModal = useModal<User | null>();
const form = useForm({ name: '', email: '' });

function openCreate() {
  form.reset();
  formModal.open(null);
}
function openEdit(user) {
  form.reset();
  Object.assign(form, { name: user.name, email: user.email });
  formModal.open(user);
}
function submit() {
  const editing = formModal.data.value;
  const opts = { preserveScroll: true, onSuccess: () => formModal.close() };
  if (editing) form.put(`/users/${editing.id}`, opts);
  else form.post('/users', opts);
}
</script>

<template>
  <FormModal
    v-model="formModal.isOpen.value"
    :title="formModal.data.value ? 'Ubah Pengguna' : 'Tambah Pengguna'"
    size="lg"
    :processing="form.processing"
    @submit="submit"
    @cancel="formModal.close()"
  >
    <FormField label="Nama" :error="form.errors.name" required>
      <Input v-model="form.name" />
    </FormField>
    <FormField label="Email" :error="form.errors.email" required>
      <Input v-model="form.email" type="email" />
    </FormField>
  </FormModal>
</template>
```

| Prop | Type | Default |
|---|---|---|
| `title`, `description` | string | |
| `size` | `xs..2xl` | `md` |
| `processing` | boolean | `false` — disable + spinner |
| `submitLabel`, `cancelLabel` | string | `Simpan`, `Batal` |
| `submitVariant` | `default\|destructive\|success\|warning` | `default` |

### `DetailModal` (preset detail data)

Auto-render `DescriptionList`.

```vue
<DetailModal
  v-model="detail.isOpen.value"
  title="Detail Pengguna"
  :items="[
    { label: 'Nama', value: detail.data.value?.name },
    { label: 'Email', value: detail.data.value?.email },
  ]"
/>

<!-- atau slot custom body -->
<DetailModal v-model="detail.isOpen.value" title="Detail">
  <div>Konten custom...</div>
</DetailModal>
```

### `JsonModal` (preset debug payload)

Pretty-print JSON + copy button.

```vue
<JsonModal
  v-model="json.isOpen.value"
  title="Konteks Log"
  :data="json.data.value?.context"
/>
```

### `ConfirmDialog` + `useConfirm`

Sudah otomatis mounted di `AppLayout`. Cara pakai:

```vue
<script setup>
import { useConfirm } from '@/composables/useConfirm';

const { confirm } = useConfirm();

async function hapus(row) {
  const ok = await confirm({
    title: 'Hapus pengguna?',
    message: `Yakin hapus "${row.name}"?`,
    variant: 'destructive',
    confirmLabel: 'Hapus',
    cancelLabel: 'Batal',
  });
  if (!ok) return;
  router.delete(`/users/${row.id}`);
}
</script>
```

### `DropdownMenu`

```vue
<DropdownMenu align="end">
  <template #trigger>
    <Button variant="outline"><MoreVertical class="h-4 w-4" /></Button>
  </template>

  <DropdownMenuLabel>Akun</DropdownMenuLabel>
  <DropdownMenuSeparator />
  <DropdownMenuItem as="link" href="/profile"><User /> Profil</DropdownMenuItem>
  <DropdownMenuItem variant="destructive" @click="logout"><LogOut /> Keluar</DropdownMenuItem>
</DropdownMenu>
```

`align`: `start | center | end`.

### `Tooltip`, `Popover`, `Drawer`

```vue
<Tooltip text="Salin link" position="top">
  <Button size="icon" variant="ghost"><Link /></Button>
</Tooltip>

<Popover align="end">
  <template #trigger><Button>Opsi</Button></template>
  <div>Konten popover...</div>
</Popover>

<Drawer v-model="drawer.isOpen.value" side="right" size="md" title="Detail">
  Isi drawer...
</Drawer>
```

### `Toast` (via `useToast`)

`ToastContainer` sudah mounted di `AppLayout`.

```vue
<script setup>
import { useToast } from '@/composables/useToast';

const toast = useToast();

toast.success('Tersimpan');
toast.error('Gagal: koneksi terputus', 'Error');  // judul opsional
toast.warning('Periksa kembali');
toast.info('Info baru');

// custom
toast.push({ message: '...', variant: 'success', duration: 5000 });
```

Flash session dari Laravel `back()->with('success', '...')` otomatis tampil sebagai toast.

---

## 📊 Data

### `DataTable`

```vue
<script setup>
import { DataTable, type Column } from '@/components/ui/DataTable';
import { useDataTable } from '@/composables/useDataTable';

const props = defineProps<{ data: Paginated<User>; filters: ... }>();

const { state, sortBy } = useDataTable({
  initial: { search: props.filters.search, sort: props.filters.sort },
  only: ['data'],
});

const columns: Column[] = [
  { key: 'name', label: 'Nama', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'status', label: 'Status', align: 'center' },
];

const selected = ref<Array<number>>([]);
</script>

<template>
  <DataTable
    :data="data"
    :columns="columns"
    :sort="state.sort"
    :direction="state.direction"
    :only="['data']"
    selectable
    v-model:selected="selected"
    @sort="sortBy"
  >
    <!-- cell slot per kolom -->
    <template #cell-name="{ row }">
      <div class="flex items-center gap-2">
        <Avatar :src="row.avatar_url" :name="row.name" size="sm" />
        <span class="font-medium">{{ row.name }}</span>
      </div>
    </template>

    <template #cell-status="{ value }">
      <Badge :variant="value === 'active' ? 'success' : 'muted'">{{ value }}</Badge>
    </template>

    <!-- action column (kanan) -->
    <template #actions="{ row }">
      <Button size="icon-xs" variant="ghost" @click="edit(row)"><Pencil /></Button>
    </template>

    <!-- bulk action bar (atas, muncul saat ada selected) -->
    <template #bulk-actions>
      <Button variant="destructive" size="sm" @click="bulkDelete">Hapus terpilih</Button>
    </template>
  </DataTable>
</template>
```

Column type:
```ts
interface Column<T = unknown> {
  key: string;
  label: string;
  sortable?: boolean;
  align?: 'left' | 'center' | 'right';
  width?: string;          // contoh '180px'
  accessor?: (row: T) => unknown;  // override cell value
  class?: string;
}
```

### `Pagination`

Otomatis di-render oleh `DataTable` di bawah. Tapi bisa standalone:

```vue
<Pagination :meta="data.meta" :only="['data']" />
```

Layout: total info | nav `« ‹ [input N] dari M › »` | per-page selector.

### `FilterBar` (di `components/shared/`)

Toolbar collapsible — search inline, filter selects tersembunyi di panel.

```vue
<FilterBar
  v-model:search="state.search"
  placeholder="Cari..."
  :filters-count="filtersCount"
  @reset="resetFilters"
>
  <FormField label="Role">
    <Select v-model="state.filters.role_id" :options="roleOptions" placeholder="Semua" clearable />
  </FormField>
  <FormField label="Status">
    <Select v-model="state.filters.status" :options="statusOptions" placeholder="Semua" clearable />
  </FormField>
</FilterBar>
```

`filtersCount`: jumlah filter aktif (untuk badge angka di tombol Filter).

### `Tabs`

```vue
<Tabs v-model="activeTab">
  <TabsList>
    <TabsTrigger value="profile">Profil</TabsTrigger>
    <TabsTrigger value="activity">Aktivitas</TabsTrigger>
  </TabsList>
  <TabsContent value="profile">
    <DescriptionList :items="..." />
  </TabsContent>
  <TabsContent value="activity">
    <ul>...</ul>
  </TabsContent>
</Tabs>
```

### `Accordion`

```vue
<Accordion type="single" default-value="general">
  <AccordionItem value="general" title="Umum">
    <p>Konten...</p>
  </AccordionItem>
  <AccordionItem value="security" title="Keamanan">
    <p>Konten...</p>
  </AccordionItem>
</Accordion>
```

### `CommandPalette`

Sudah otomatis di-mount di `AppLayout`, trigger via `Ctrl+K`. Sumber: shared `menu` prop (sudah terfilter permission).

---

## 🎨 Layout

### `PageHeader` (di `components/shared/`)

```vue
<PageHeader
  title="Manajemen Pengguna"
  description="Kelola pengguna sistem."
  :breadcrumbs="[{ label: 'User & Access' }, { label: 'Pengguna' }]"
>
  <template #actions>
    <Button variant="outline" size="sm"><Download /> Ekspor</Button>
    <Button><Plus /> Tambah</Button>
  </template>
</PageHeader>
```

### `ThemeToggle`

Dropdown light/dark/system. Sudah otomatis di Topbar.

```vue
<ThemeToggle />
```

---

## 🧠 Composables

### `useModal<T>()`

```ts
const m = useModal<User | null>();

m.open();            // open kosong (mode create)
m.open(user);        // open dengan context data (mode edit)
m.close();           // close + retain data 220ms (anim out)
m.toggle();

m.isOpen.value;      // boolean
m.data.value;        // T | null
```

### `useConfirm()`

```ts
const { confirm } = useConfirm();
const ok: boolean = await confirm({ title, message, variant, confirmLabel, cancelLabel });
```

### `useToast()`

```ts
const t = useToast();
t.success(msg, title?);
t.error(msg, title?);
t.warning(msg, title?);
t.info(msg, title?);
```

### `useDataTable({ initial, only, preserveScroll })`

```ts
const { state, sortBy, reset, reload } = useDataTable({
  initial: { search, sort, filters: { role_id, status } },
  only: ['data'],
});

// state.search, state.sort, state.direction, state.perPage, state.page, state.filters
sortBy('name');  // klik header column
reset();
reload();
```

### `useAppForm(data)`

Wrapper `useForm` Inertia + auto-toast error pertama.

```ts
const { form, submit } = useAppForm({ name: '', email: '' });
submit('post', '/users', { onSuccess: () => ... });
```

### `usePermission()`

```ts
const { can, canAny, canExtra, isSuperAdmin } = usePermission();

can('create', 'user-management');         // by slug
can('delete', 5);                          // by ID
canAny('read', ['users', 'roles']);        // any of
canExtra('reporting', 'export');           // extra actions
isSuperAdmin.value;                        // boolean
```

### `useTheme()`

```ts
const { mode, setMode, toggle } = useTheme();
// mode.value: 'light' | 'dark' | 'system'
setMode('dark');
toggle();
```

### `useShortcut(combo, handler)`

```ts
useShortcut('ctrl+k', (e) => {
  e.preventDefault();
  paletteOpen.value = true;
});
```

---

## 📋 Pattern halaman

### Index page lengkap (User Management style)

```vue
<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import FilterBar from '@/components/shared/FilterBar.vue';
import { Button } from '@/components/ui/Button';
import { Select } from '@/components/ui/Select';
import { FormField } from '@/components/ui/FormField';
import { StatCard } from '@/components/ui/StatCard';
import { DataTable } from '@/components/ui/DataTable';
import { FormModal } from '@/components/ui/Modal';
import { useDataTable } from '@/composables/useDataTable';
import { useModal } from '@/composables/useModal';
import { useConfirm } from '@/composables/useConfirm';
import { Users } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{ data, filters, stats }>();
const { state, sortBy } = useDataTable({ initial: { ... }, only: ['data'] });
const formModal = useModal<Row | null>();
const form = useForm({ ... });
const { confirm } = useConfirm();
</script>

<template>
  <AppLayout>
    <div class="space-y-5">
      <PageHeader title="..." description="...">
        <template #actions>
          <Button @click="formModal.open(null)">Tambah</Button>
        </template>
      </PageHeader>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <StatCard label="Total" :value="stats.total" :icon="Users" />
        <!-- ... -->
      </div>

      <FilterBar v-model:search="state.search" :filters-count="...">
        <FormField label="Role">
          <Select v-model="state.filters.role_id" :options="..." clearable />
        </FormField>
      </FilterBar>

      <DataTable :data="data" :columns="columns" @sort="sortBy">
        <!-- cell slots, action slot -->
      </DataTable>
    </div>

    <FormModal v-model="formModal.isOpen.value" :processing="form.processing" @submit="submit">
      <!-- FormField fields -->
    </FormModal>
  </AppLayout>
</template>
```

### Detail page

```vue
<template>
  <AppLayout>
    <div class="space-y-5">
      <PageHeader title="..." :breadcrumbs="..." />
      <Card>
        <CardContent>
          <Tabs v-model="tab">
            <TabsList>
              <TabsTrigger value="info">Info</TabsTrigger>
              <TabsTrigger value="logs">Aktivitas</TabsTrigger>
            </TabsList>
            <TabsContent value="info">
              <DescriptionList :items="items" />
            </TabsContent>
            <TabsContent value="logs">
              <!-- ... -->
            </TabsContent>
          </Tabs>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
```

### Pattern memilih: modal vs page

| Kasus | Pilih |
|---|---|
| Form simpel (< 10 field, tanpa matrix) | **FormModal** |
| Form kompleks (matrix permission, multi-section panjang) | **Page** OR **FormModal `size="2xl"`** dengan scrollable body |
| Detail / read-only data | **DetailModal** kalau singkat, **Page show.vue** kalau ada banyak tab/activity |
| Log payload / JSON | **JsonModal** (copy button built-in) |
| Konfirmasi destructive | **useConfirm()** |
