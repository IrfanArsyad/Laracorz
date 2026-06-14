# Arsitektur LaraCorz

## Prinsip Inti
1. **Modular** — setiap fitur bisnis berdiri sendiri di `modules/{Name}/` dengan struktur lengkap (App, Routes, Resources, Database, Config, Tests). Core tidak men-seed modul bisnis.
2. **Permission by Module ID (jsonb)** — `roles` punya 4 kolom jsonb (`read`, `create`, `update`, `delete`) berisi array ID modul, plus `extra` jsonb keyed per module name. `["*"]` = super-admin.
3. **Tree modul = menu + permission** — `module_groups → modules` (parent/child, leaf vs container) adalah satu-satunya sumber. Container otomatis muncul jika ≥1 leaf descendant lolos `read`.
4. **UI library sendiri** — tidak ada library UI pihak ketiga. Semua komponen di `resources/js/components/ui/`, ala shadcn dengan CVA.

## Keputusan Terkunci
- **Satu user satu role** (`users.role_id` FK restrictOnDelete). Tidak ada pivot multi-role.
- **jsonb bukan pivot** — performa baca cepat, mutasi atomik (`whereJsonContains`), GIN index di Postgres.
- **Spatie ditolak** — sistem permission sengaja custom karena unit-nya adalah Module ID (bukan permission name) dan dinamis (modul baru = self-register lewat `module:sync`).
- **Tanpa agency / multi-tenant** — disimpan sebagai lapisan masa depan: bila perlu, tambahkan `agency_id` di `users` + global scope, tanpa mengubah permission.
- **Container vs Leaf** — permission hanya berlaku pada leaf; container hanya wadah menu. Validasi tree di Form Request.
- **Observer Module** — saat module dihapus, ID-nya dibersihkan dari semua jsonb role (jsonb tidak punya FK). Cache menu/permission di-flush via Observer Module/ModuleGroup/Role.

## Lapisan Backend
```
Request → FormRequest (validasi + authorize) → Controller (tipis) → Service (business logic, DTO masuk) → Repository (Eloquent) → DB
                                                                                                       ↘ Observer → AdminLog
```

## Cache Strategy
| Key | Isi | Flush |
|---|---|---|
| `modules.map` | name → ID | Observer Module |
| `modules.summary` | id+name array ringan untuk shared props | Observer Module |
| `menu.role.{id}` | tree menu terfilter per role | Observer Module/ModuleGroup/Role |
| `settings.all` | semua setting | SettingService::set |

## Logging
- **admin_logs** — jejak aksi manusia (immutable, queued via trait `LogsAdminActivity`).
- **system_logs** — kejadian teknis (job_failed, exception handler, scheduler).
- Retensi: command `logs:prune` (default admin 180 hari, system 90 hari) dijadwalkan harian.

## Frontend Architecture
- **Resolver** di `app.ts` map `module::path` → `modules/{Studly}/Resources/{path}.vue`, tanpa `::` → core `resources/js/pages/`.
- **Shared props** via `HandleInertiaRequests`: `auth.user`, `auth.permissions`, `menu`, `modules`, `flash`, `app`, `ziggy`.
- **Composables**: `usePermission`, `useToast`, `useConfirm`, `useTheme`, `useDataTable`, `useAppForm`, `useShortcut`.

## Lapisan Masa Depan
- **Multi-tenant**: tambah `agency_id` + global scope di model bisnis.
- **API**: routes/api.php sudah ada placeholder; Sanctum sudah terinstal.
- **SSR**: `ssr.ts` disiapkan namun belum diaktifkan.
