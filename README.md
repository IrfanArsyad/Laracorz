# LaraCorz

Core production-ready **Laravel 13 + Vue 3 + Inertia 2** dengan arsitektur modular, sistem permission custom berbasis ID modul, dan UI library buatan sendiri (ala shadcn).

## Stack
- Laravel 13 (PHP 8.3+)
- Vue 3 + Composition API + TypeScript
- Inertia.js 2
- Tailwind CSS 4 + Sass (sass-embedded)
- nwidart/laravel-modules
- tightenco/ziggy
- lucide-vue-next, CVA, clsx, tailwind-merge
- PostgreSQL (jsonb + GIN index)
- Pest, Vitest, Pint, Larastan, ESLint, Prettier
- Laravel Telescope (dev)

## Setup Cepat

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# atur DB di .env (PostgreSQL: laracoz / user: laracorz / pass: f1r3c0d3)
php artisan migrate --seed
php artisan storage:link
composer dev   # serve + queue + pail + vite paralel
```

Login admin awal:
- Email: `admin@example.com`
- Password: `password`

## Perintah Penting

| Perintah | Kegunaan |
|---|---|
| `php artisan module:sync` | Sinkronisasi modul Nwidart ke tabel `module_groups` & `modules` (baca `Config/menu.php` tiap modul). |
| `php artisan module:make-crud {Module} {Model}` | Generate modul CRUD lengkap dari stubs. |
| `php artisan logs:prune` | Pangkas `admin_logs` & `system_logs` sesuai retensi. |
| `composer dev` | Jalankan server + queue + pail + vite paralel. |
| `composer test` | Jalankan test Pest. |
| `composer lint` | Pint + npm lint. |
| `composer analyse` | Larastan level 6. |

## Konvensi
- Backend: Controller tipis → Service → Repository (BaseRepository di `app/Support`).
- Frontend modular: halaman modul di `modules/{Module}/Resources/*.vue`, resolver `module::path`.
- Style modular: `modules/{Module}/assets/css/{module}.css` (auto-load saat halaman modul dibuka).
- Permission: 100% dari DB (`module_groups` → `modules` tree + role `read/create/update/delete` jsonb array module ID).
- Tidak ada library UI pihak ketiga; semua komponen di `resources/js/components/ui/`.

## Struktur Direktori (ringkas)
```
app/
├── Console/Commands/
├── Helpers/helpers.php
├── Http/Middleware/EnsureModulePermission.php
├── Models/{User,Role,Module,ModuleGroup,AdminLog,SystemLog,Setting}.php
├── Observers/
├── Policies/ModulePolicy.php
├── Providers/
├── Services/{MenuService,ModuleRegistry,AdminLogService,SystemLogService,SettingService,FileService}.php
└── Support/{BaseRepository,SearchFilterDto,Traits/...}
modules/
├── RoleManagement/Resources/
├── ModuleManagement/Resources/
├── UserManagement/Resources/
├── AdminLog/Resources/
├── SystemLog/Resources/
├── Setting/Resources/
└── Notification/Resources/
resources/
├── sass/{app.scss,_tokens.scss,_base.scss,_utilities.scss}
└── js/
    ├── app.ts (resolver modular)
    ├── layouts/{AppLayout,AuthLayout,partials}
    ├── components/{ui,shared}
    ├── composables/
    └── pages/{dashboard,auth,profile,errors}
stubs/laracorz/  ← stubs untuk module:make-crud
```

## Dokumentasi
- `docs/ARCHITECTURE.md` — keputusan arsitektur (single role, jsonb vs pivot, lapisan masa depan).
- **`docs/COMPONENTS.md`** — referensi lengkap UI library + composable (props/emits/slot per komponen, contoh kode).
- **`docs/CUSTOM_COMPONENT.md`** — panduan bikin/kustom komponen baru (file structure, CVA, tokens, a11y, motion, checklist, anti-pattern).
- `docs/MODULES.md` — cara membuat modul baru.
