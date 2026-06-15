<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $defaults = [
            // ───── GENERAL ─────────────────────────────────────────────────
            ['key' => 'app.name', 'group' => 'general', 'type' => 'text', 'label' => 'Application name', 'description' => 'Nama aplikasi yang muncul di sidebar & email.', 'value' => 'LaraCorz', 'order' => 1],
            ['key' => 'app.tagline', 'group' => 'general', 'type' => 'text', 'label' => 'Tagline', 'description' => 'Slogan singkat (opsional).', 'value' => 'Core Laravel + Vue Modular', 'order' => 2],
            ['key' => 'app.description', 'group' => 'general', 'type' => 'textarea', 'label' => 'Description', 'description' => 'Deskripsi panjang untuk SEO & onboarding.', 'value' => '', 'order' => 3],
            ['key' => 'app.timezone', 'group' => 'general', 'type' => 'select', 'label' => 'Default timezone', 'description' => 'Timezone untuk tampilan tanggal/waktu.', 'value' => 'Asia/Jakarta', 'order' => 4],
            ['key' => 'app.locale', 'group' => 'general', 'type' => 'select', 'label' => 'Default language', 'description' => 'Bahasa default untuk user baru.', 'value' => 'en', 'order' => 5],
            ['key' => 'app.contact_email', 'group' => 'general', 'type' => 'email', 'label' => 'Contact email', 'description' => 'Email untuk laporan bug / kontak admin.', 'value' => 'admin@example.com', 'order' => 6],

            // ───── BRANDING ────────────────────────────────────────────────
            ['key' => 'branding.logo', 'group' => 'branding', 'type' => 'image', 'label' => 'Logo', 'description' => 'Logo utama, idealnya SVG/PNG transparan.', 'value' => null, 'order' => 1],
            ['key' => 'branding.favicon', 'group' => 'branding', 'type' => 'image', 'label' => 'Favicon', 'description' => 'Icon kecil untuk tab browser (32×32 PNG/ICO).', 'value' => null, 'order' => 2],
            ['key' => 'branding.brand_color', 'group' => 'branding', 'type' => 'color', 'label' => 'Brand color', 'description' => 'Warna primer untuk button & accent (HEX).', 'value' => '#6366f1', 'order' => 3],

            // ───── EMAIL / SMTP ────────────────────────────────────────────
            ['key' => 'mail.from_name', 'group' => 'email', 'type' => 'text', 'label' => 'From name', 'description' => 'Nama pengirim email default.', 'value' => 'LaraCorz', 'order' => 1],
            ['key' => 'mail.from_address', 'group' => 'email', 'type' => 'email', 'label' => 'From address', 'description' => 'Alamat email pengirim default.', 'value' => 'noreply@example.com', 'order' => 2],
            ['key' => 'mail.host', 'group' => 'email', 'type' => 'text', 'label' => 'SMTP host', 'description' => 'Server SMTP (mis. smtp.gmail.com).', 'value' => 'smtp.mailtrap.io', 'order' => 3],
            ['key' => 'mail.port', 'group' => 'email', 'type' => 'number', 'label' => 'SMTP port', 'description' => 'Port SMTP (587 untuk TLS, 465 untuk SSL).', 'value' => 587, 'order' => 4],
            ['key' => 'mail.username', 'group' => 'email', 'type' => 'text', 'label' => 'SMTP username', 'description' => 'Username login SMTP.', 'value' => '', 'order' => 5],
            ['key' => 'mail.encryption', 'group' => 'email', 'type' => 'select', 'label' => 'Encryption', 'description' => 'Metode enkripsi koneksi SMTP.', 'value' => 'tls', 'order' => 6],

            // ───── SECURITY ────────────────────────────────────────────────
            ['key' => 'security.session_lifetime', 'group' => 'security', 'type' => 'number', 'label' => 'Session lifetime (minutes)', 'description' => 'Berapa lama session valid setelah aktivitas terakhir.', 'value' => 120, 'order' => 1],
            ['key' => 'security.max_login_attempts', 'group' => 'security', 'type' => 'number', 'label' => 'Max login attempts', 'description' => 'Jumlah percobaan gagal sebelum akun dikunci sementara.', 'value' => 5, 'order' => 2],
            ['key' => 'security.lock_duration', 'group' => 'security', 'type' => 'number', 'label' => 'Lock duration (minutes)', 'description' => 'Durasi kunci akun setelah max attempts tercapai.', 'value' => 15, 'order' => 3],
            ['key' => 'security.password_min_length', 'group' => 'security', 'type' => 'number', 'label' => 'Password min length', 'description' => 'Minimum karakter untuk password user baru.', 'value' => 8, 'order' => 4],
            ['key' => 'security.force_2fa', 'group' => 'security', 'type' => 'boolean', 'label' => 'Force 2FA for admins', 'description' => 'Wajibkan 2FA untuk user dengan role super-admin.', 'value' => false, 'order' => 5],

            // ───── MAINTENANCE ─────────────────────────────────────────────
            ['key' => 'maintenance.enabled', 'group' => 'maintenance', 'type' => 'boolean', 'label' => 'Maintenance mode', 'description' => 'Aktifkan untuk tampilkan halaman 503 ke semua pengunjung.', 'value' => false, 'order' => 1],
            ['key' => 'maintenance.message', 'group' => 'maintenance', 'type' => 'textarea', 'label' => 'Maintenance message', 'description' => 'Pesan yang tampil saat maintenance mode aktif.', 'value' => 'Sistem sedang dalam pemeliharaan. Mohon kembali sebentar lagi.', 'order' => 2],
            ['key' => 'maintenance.allowed_ips', 'group' => 'maintenance', 'type' => 'textarea', 'label' => 'Allowed IPs', 'description' => 'Satu IP per baris. IP ini tetap bisa akses saat maintenance.', 'value' => "127.0.0.1\n::1", 'order' => 3],

            // ───── APPEARANCE ──────────────────────────────────────────────
            ['key' => 'theme.default', 'group' => 'appearance', 'type' => 'select', 'label' => 'Default theme', 'description' => 'Tema untuk user baru. "system" mengikuti preferensi OS.', 'value' => 'system', 'order' => 1],
        ];

        foreach ($defaults as $row) {
            Setting::query()->updateOrCreate(['key' => $row['key']], $row);
        }
    }
}
