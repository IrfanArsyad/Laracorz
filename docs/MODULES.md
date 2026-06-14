# Membuat Modul Baru (<5 menit)

## 1. Generate kerangka
```bash
php artisan module:make-crud Product Product
```

Otomatis membuat:
- `modules/Product/App/{Models,Http/Controllers}/`
- `modules/Product/Routes/web.php`
- `modules/Product/Database/Migrations/create_products_table.php`
- `modules/Product/Resources/{index,create,edit}.vue`
- `modules/Product/Resources/components/` (komponen Vue khusus modul)
- `modules/Product/assets/css/product.css` ⭐ ← style khusus modul
- `modules/Product/Config/{config.php,menu.php}`
- `modules/Product/module.json`

Setelah generate, command otomatis menjalankan `module:sync` sehingga modul muncul di tabel `modules` dan di sidebar (untuk role yang punya `read`).

## 2. Migrate
```bash
php artisan migrate
```

## 3. Atur permission
Buka halaman **Role > Edit** lalu centang Read/Create/Update/Delete untuk modul baru. Simpan; perubahan langsung berlaku.

## 4. Convention modul
```
modules/Product/
├── App/                                # Backend PHP
│   ├── Http/Controllers/ProductController.php
│   ├── Models/Product.php
│   ├── Services/, Repositories/, ...
├── Routes/web.php                      # Route dengan module.permission
├── Database/Migrations/                # Migrasi tabel modul
├── Resources/                          # Frontend Vue
│   ├── index.vue                       # Halaman Inertia langsung di sini
│   ├── create.vue
│   ├── edit.vue
│   ├── show.vue
│   └── components/                     # Komponen khusus modul
├── assets/                             # ⭐ Aset front-end khusus modul
│   ├── css/product.css                 # Style hand-written (selector kompleks, print, dst)
│   └── images/                         # Gambar/icon khusus modul (opsional)
├── Config/
│   ├── config.php
│   └── menu.php                        # Definisi grup/modul yang dibaca module:sync
├── Lang/                               # i18n khusus modul (opsional)
└── module.json
```

- **Controller** tipis → Service → Repository (BaseRepository).
- **Form Request** untuk validasi (`messages()`, `attributes()` bahasa Indonesia).
- **Resources/*.vue** = halaman Inertia (resolver `product::index` → `modules/Product/Resources/index.vue`).
- **assets/css/{slug}.css** = style hand-written; **auto di-load oleh resolver app.ts** saat halaman modul dibuka (code-split, hanya termuat saat dibutuhkan).
- Tailwind utility class di template Vue otomatis di-scan via `@source` di `resources/css/app.css` — **tidak perlu** menambahkan apa-apa di app.css untuk modul baru.

## 5. Tree modul
- Root modul wajib punya `module_group_id` (grup hanya di root).
- Container = `url` & `route_name` keduanya null.
- Leaf = wajib isi `url` & `route_name` (validasi di Form Request).
- Kedalaman maksimal: 2 di bawah root (root → child → grandchild).

## 6. Lifecycle data
- Saat modul dihapus, Observer otomatis menghapus ID modul dari semua jsonb role + flush cache menu.
- Saat nama modul di-rename, Observer rename key di kolom `extra` semua role.

## 7. Audit
Pasang trait `App\Support\Traits\LogsAdminActivity` di model bisnis untuk auto-log create/update/delete/restore ke `admin_logs` (queued).

## 8. Test
```bash
php artisan test --filter Product
```
Cek: CRUD happy path, 403 tanpa permission, validasi gagal, soft delete + restore.

## 9. Catatan style
- **CSS per-module di `assets/css/`**: hand-written CSS untuk hal yang sulit ditulis sebagai utility (keyframes, selector dalam, print). Namespace dengan prefix (`.product-…`) atau bungkus dalam class root halaman supaya tidak bocor.
- **Tailwind utilities** tetap dipakai langsung di template Vue.
- **Token warna** ambil dari CSS variable global (`hsl(var(--primary))`, `bg-primary`, dst) — dilarang hardcode warna agar dark mode konsisten.
