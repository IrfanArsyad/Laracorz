# CLAUDE.md — LaraCorz

Core modular **Laravel 13 + Vue 3 + Inertia 2**. Permission 100% dari DB (module tree), UI library sendiri (ala shadcn), tanpa library UI pihak ketiga.

> Baca file ini dulu sebelum eksplorasi. Untuk detail, buka `docs/` (jangan grep buta) — pointer di bawah.

## Stack
PHP 8.3+ · Laravel 13 · Inertia 2 · Vue 3 (Composition API + TS) · Tailwind 4 (token via CSS custom properties, bukan Sass) · Ziggy · PostgreSQL (jsonb + GIN) · Redis (cache menu) · Pest · Vitest · Pint · Larastan (lvl 6) · ESLint · Prettier · Telescope (dev).

Sistem modul dibuat sendiri — **tidak pakai nwidart/laravel-modules**. Paket itu sudah dicopot karena tidak dipakai runtime dan command `module:make`-nya menghasilkan Blade, yang tidak sesuai core ini.

## Perintah
| Aksi | Perintah |
|---|---|
| Dev (serve+queue+pail+vite) | `composer dev` |
| Test PHP | `composer test` (Pest) |
| Test JS | `npm run test` (Vitest) |
| Lint | `composer lint` (Pint + ESLint) · `./vendor/bin/pint` saja untuk PHP |
| Static analysis | `composer analyse` (Larastan lvl 6) |
| Type-check TS | `npm run type-check` |
| Sync modul → DB | `php artisan module:sync` |
| Generate CRUD modul | `php artisan module:make-crud {Module} {Model}` |
| Prune log | `php artisan logs:prune` |

## Arsitektur
**Backend:** Controller tipis → Service → Repository (`app/Support/BaseRepository`). Validasi via Form Request. Response API `{ success, data, message }`.

**Modul** (`modules/`) — 8 modul, satu folder per fitur. Nama folder sengaja dibuat sama dengan `modules.name` di DB supaya tidak ambigu:

| Folder | `modules.name` di DB | Isi |
|---|---|---|
| `UserManagement` | `user-management` | CRUD user |
| `RoleManagement` | `role-management` | Role + permission matrix |
| `ModuleManagement` | `module-management` | Module tree + group |
| `AdminLog` | `admin-log` | Jejak aksi admin |
| `SystemLog` | `system-log` | Baca file log via `LaravelLogReader` |
| `Settings` | `settings` | Setting aplikasi |
| `General` | `dashboard` (hanya Dashboard) | Dashboard, Profile, Notification — Profile & Notification tanpa permission |
| `Auth` | — | Login, password reset, verify email (tanpa permission) |

Struktur tiap modul: `App/Http/{Controllers,Requests}`, `Resources/*.vue` (+ `Resources/components/`), `Routes/{web,api}.php`, `assets/css/{slug}.css` (auto-load saat halaman modul dibuka).

Modul tidak punya `module.json`, ServiceProvider, atau `composer.json`. Registrasinya cuma empat glob:

| Apa | Di mana |
|---|---|
| Route web | `routes/web.php` → `modules/*/Routes/web.php` |
| Route api | `routes/api.php` → `modules/*/Routes/api.php` |
| Migration | `AppServiceProvider::loadModuleMigrations()` → `modules/*/Database/Migrations` |
| Halaman Vue + CSS | `resources/js/app.ts` → `modules/*/Resources/**/*.vue`, `modules/*/assets/css/*.css` |

Plus PSR-4 `Modules\` → `modules/` di `composer.json`. Tambah modul = tambah folder, lalu `composer dump-autoload`.

**Generator: `php artisan module:make-crud {Module} {Model}`** — satu-satunya cara bikin modul. Baca `stubs/laracorz/`, hasilnya langsung lolos Pint + Larastan. Tambah `--no-sync` kalau belum mau mendaftarkan modul ke tabel `modules` (tanpa flag itu `module:sync` langsung jalan; kalau modul batal dipakai dan foldernya dihapus, barisnya tertinggal jadi entri yatim di menu).

Isi `stubs/laracorz/` = persis struktur satu modul. Tambah/hapus file di sana otomatis ikut ter-generate — command-nya menyalin seisi folder, tidak ada daftar file yang di-hardcode.

**Inti `app/`:**
- Models: `User, Role, Module, ModuleGroup, AdminLog, Setting` (+ `Concerns/`).
- Services: `MenuService, ModuleRegistry, AdminLogService, SettingService, FileService, LaravelLogReader, UserSessionService`.
- Support: `BaseRepository, SearchFilterDto, MenuCache, Traits/{Searchable,Sortable,HasActiveScope,SerializesDates,LogsAdminActivity}`.
- Middleware: `EnsureModulePermission, HandleInertiaRequests, SecurityHeaders, SetLocale, TrackLastActivity`.
- Policy: `ModulePolicy`. Command: `ModuleSync, ModuleMakeCrud, LogsPrune`.

**Permission:** `module_groups` → `modules` (tree). Role menyimpan `read/create/update/delete` sebagai jsonb array of module ID. Enforce via `EnsureModulePermission` + `ModulePolicy`. Tidak ada Spatie/permission library.

**Cache menu:** menu per-role dan module map disimpan di Redis pada store terpisah (`config/cache.php` → `menu_store`, koneksi `menu` dengan database index sendiri). Semua akses lewat `App\Support\MenuCache` — jangan pakai facade `Cache` langsung untuk data menu. Store terpisah supaya observer bisa flush menu tanpa membuang cache aplikasi lain. Saat testing di-override ke driver `array` (`MENU_CACHE_STORE=array` di `phpunit.xml`).

**Frontend:** resolver modular di `resources/js/app.ts` (`module::path`, mis. `user-management::index` → `modules/UserManagement/Resources/index.vue`). Komponen UI hanya di `resources/js/components/ui/` (CVA + clsx + tailwind-merge, lucide-vue-next).

**Style** (`resources/css/`, tanpa Sass — token pakai CSS custom properties supaya bisa diganti saat runtime):

```
app.css                 entry, hanya @import + @source + @custom-variant
tokens/palette.css      tier 1 — skala warna mentah (OKLCH)
tokens/semantic.css     tier 2 — token yang dipakai komponen + alias legacy HSL
tokens/dark.css         override token untuk .dark
theme.css               expose token ke utility Tailwind (@theme)
base.css                reset + elemen dasar
utilities.css           utility custom, keyframes, reduced-motion
brand.css               layer override aplikasi — di-import PALING AKHIR
```

Untuk mengubah tampilan, tulis di `brand.css` saja; karena di-import terakhir ia menang tanpa `!important`. Override token tier 2 (`--brand-bg`), bukan tier 1 (`--brand-500`), kecuali memang mau menggeser seluruh skala. Urutan `@import` di `app.css` = urutan cascade, jangan diacak.

## Konvensi
- `declare(strict_types=1);` + PSR-12. Prefer Eloquent, Form Request, Service/Action, Resource.
- Vue: `<script setup>` + TS, hindari `any`, named exports.
- Jangan tambah library UI pihak ketiga — extend `components/ui/`.
- Jangan ubah DB/migrasi/seeder tanpa konfirmasi.
- Commit: imperatif, atomik, TANPA co-author/embel Claude. Branch: `feature/`, `fix/`, `hotfix/`.

## Docs (baca sesuai kebutuhan, jangan semua)
- `docs/ARCHITECTURE.md` — keputusan arsitektur (single role, jsonb vs pivot).
- `docs/COMPONENTS.md` — referensi UI library + composable (props/emits/slot).
- `docs/CUSTOM_COMPONENT.md` — panduan bikin komponen (CVA, tokens, a11y, checklist).
- `docs/MODULES.md` — cara bikin modul baru.
- `docs/STATIC_ANALYSIS.md` — setup Larastan/PHPStan.
