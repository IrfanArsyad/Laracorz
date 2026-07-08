# CLAUDE.md — LaraCorz

Core modular **Laravel 13 + Vue 3 + Inertia 2**. Permission 100% dari DB (module tree), UI library sendiri (ala shadcn), tanpa library UI pihak ketiga.

> Baca file ini dulu sebelum eksplorasi. Untuk detail, buka `docs/` (jangan grep buta) — pointer di bawah.

## Stack
PHP 8.3+ · Laravel 13 · Inertia 2 · Vue 3 (Composition API + TS) · Tailwind 4 + Sass · nwidart/laravel-modules · Ziggy · PostgreSQL (jsonb + GIN) · Pest · Vitest · Pint · Larastan (lvl 6) · ESLint · Prettier · Telescope (dev).

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

**Modul** (`modules/`, nwidart) — HANYA 4 modul (README lama menyebut nama lain, abaikan):
- `AccessControl` — User, Role, Module management.
- `Activity` — AdminLog, SystemLog (SystemLog dibaca dari file log via `LaravelLogReader`).
- `Auth` — login, register-less, password reset, verify email.
- `General` — Dashboard, Notification, Profile, Setting.

Struktur tiap modul: `App/Http/{Controllers,Requests}`, `Resources/{Page}/*.vue`, `Routes/{web,api}.php`, `assets/css/{module}.css` (auto-load saat halaman modul dibuka).

**Inti `app/`:**
- Models: `User, Role, Module, ModuleGroup, AdminLog, Setting` (+ `Concerns/`).
- Services: `MenuService, ModuleRegistry, AdminLogService, SettingService, FileService, LaravelLogReader, UserSessionService`.
- Support: `BaseRepository, SearchFilterDto, Traits/{Searchable,Sortable,HasActiveScope,SerializesDates,LogsAdminActivity}`.
- Middleware: `EnsureModulePermission, HandleInertiaRequests, SecurityHeaders, SetLocale, TrackLastActivity`.
- Policy: `ModulePolicy`. Command: `ModuleSync, ModuleMakeCrud, LogsPrune`.

**Permission:** `module_groups` → `modules` (tree). Role menyimpan `read/create/update/delete` sebagai jsonb array of module ID. Enforce via `EnsureModulePermission` + `ModulePolicy`. Tidak ada Spatie/permission library.

**Frontend:** resolver modular di `resources/js/app.ts` (`module::path`). Komponen UI hanya di `resources/js/components/ui/` (CVA + clsx + tailwind-merge, lucide-vue-next). Tokens style di `resources/sass/_tokens.scss`.

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
