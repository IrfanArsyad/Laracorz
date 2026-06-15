<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Baca file laravel.log dan parse jadi struktur untuk UI.
 *
 *   $reader->all(['level' => 'error', 'search' => 'foo'], page: 1, perPage: 20);
 *
 * Pakai file scanning (bukan DB) — sumber log Laravel adalah file logs.
 * Untuk dataset besar (>50k lines), refactor pakai stream + Redis index
 * kalau perlu. Untuk dev/prod normal sufficient.
 */
class LaravelLogReader
{
    /**
     * @param  string  $logPath  Base path. Untuk daily channel pakai
     *                           `storage/logs/laravel.log` — reader akan
     *                           auto-discover laravel-YYYY-MM-DD.log files.
     */
    public function __construct(private readonly string $logPath)
    {
    }

    /**
     * Daftar file log yang akan dibaca. Daily channel pakai pola
     * `laravel-YYYY-MM-DD.log`. Single channel pakai `laravel.log`.
     * Urut paling baru di depan.
     *
     * @param  string|null  $onlyDate  Filter tanggal `YYYY-MM-DD` — kalau diset,
     *                                 hanya file dengan tanggal itu yang dibaca.
     * @return list<string>
     */
    private function logFiles(?string $onlyDate = null): array
    {
        $dir = dirname($this->logPath);
        if (! is_dir($dir)) {
            return [];
        }

        $base = basename($this->logPath, '.log');
        $files = [];

        // Daily channel files: laravel-YYYY-MM-DD.log
        foreach (glob($dir.'/'.$base.'-*.log') ?: [] as $f) {
            if ($onlyDate !== null) {
                if (! preg_match('/-(\d{4}-\d{2}-\d{2})\.log$/', $f, $m) || $m[1] !== $onlyDate) {
                    continue;
                }
            }
            $files[] = $f;
        }

        // Single-driver fallback: hanya kalau laravel.log ada DAN tidak ada
        // filter tanggal (atau filter = hari ini)
        if (is_file($this->logPath)) {
            $today = date('Y-m-d');
            if ($onlyDate === null || $onlyDate === $today) {
                $files[] = $this->logPath;
            }
        }

        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return $files;
    }

    /**
     * Daftar tanggal yang masih punya file log (dalam window retensi).
     * Format: ['2026-06-15', '2026-06-14', ...] terbaru di depan.
     *
     * @return list<string>
     */
    public function availableDates(): array
    {
        $dir = dirname($this->logPath);
        if (! is_dir($dir)) {
            return [];
        }

        $base = basename($this->logPath, '.log');
        $dates = [];

        // Tanggal dari file rotated
        foreach (glob($dir.'/'.$base.'-*.log') ?: [] as $f) {
            if (preg_match('/-(\d{4}-\d{2}-\d{2})\.log$/', $f, $m)) {
                $dates[] = $m[1];
            }
        }

        // Tanggal hari ini kalau laravel.log ada (current daily file)
        if (is_file($this->logPath)) {
            $dates[] = date('Y-m-d');
        }

        // Dedup + urut desc
        $dates = array_values(array_unique($dates));
        rsort($dates);

        return $dates;
    }

    /**
     * @param  array{level?: string|null, search?: string|null, date?: string|null}  $filters
     * @return array{data: array<int, array<string, mixed>>, total: int, from: int, to: int, current_page: int, per_page: int, last_page: int}
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $entries = $this->all($filters);
        $total = $entries->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $current = min(max(1, $page), $lastPage);
        $sliced = $entries->slice(($current - 1) * $perPage, $perPage)->values();

        return [
            'data' => $sliced->all(),
            'total' => $total,
            'from' => $total === 0 ? 0 : ($current - 1) * $perPage + 1,
            'to' => $total === 0 ? 0 : ($current - 1) * $perPage + $sliced->count(),
            'current_page' => $current,
            'per_page' => $perPage,
            'last_page' => $lastPage,
        ];
    }

    /**
     * @param  array{level?: string|null, search?: string|null, date?: string|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function all(array $filters = []): Collection
    {
        $date = ! empty($filters['date']) ? (string) $filters['date'] : null;
        $files = $this->logFiles($date);
        if (empty($files)) {
            return collect();
        }

        $entries = collect();
        $offset = 0;
        foreach ($files as $file) {
            $raw = @file_get_contents($file);
            if ($raw === false || $raw === '') {
                continue;
            }
            $entries = $entries->concat($this->parse($raw, $offset));
            $offset += 100000; // Ensure unique IDs across files
        }

        if (! empty($filters['level'])) {
            $level = strtolower((string) $filters['level']);
            $entries = $entries->filter(fn (array $e) => strtolower((string) $e['level']) === $level);
        }
        if (! empty($filters['search'])) {
            $needle = strtolower((string) $filters['search']);
            $entries = $entries->filter(
                fn (array $e) => str_contains(strtolower((string) $e['message']), $needle)
                    || str_contains(strtolower((string) ($e['channel'] ?? '')), $needle)
                    || str_contains(strtolower((string) ($e['context'] ?? '')), $needle),
            );
        }

        // Sort by waktu desc, paling baru di atas
        return $entries->sortByDesc('created_at')->values();
    }

    public function distinctLevels(): array
    {
        return $this->all()->pluck('level')->unique()->values()->all();
    }

    public function distinctChannels(): array
    {
        return $this->all()->pluck('channel')->unique()->filter()->values()->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function parse(string $raw, int $offset = 0): Collection
    {
        // Setiap entry log dimulai dengan `[YYYY-mm-dd HH:ii:ss]`. Split di
        // batas baris baru yang diawali pola itu, lalu parse satu per satu.
        $lines = preg_split('/\n(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\])/', $raw) ?: [];

        $entries = [];
        foreach ($lines as $idx => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (! preg_match('/^\[(?<time>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?<channel>\w+)\.(?<level>\w+): (?<rest>[\s\S]*)$/', $line, $m)) {
                continue;
            }

            $rest = $m['rest'];
            // Pisahkan stack trace bila ada (biasanya diawali "in /path/file.php:NN" atau "Stack trace:")
            $message = $rest;
            $exception = null;
            if (preg_match('/^(?<msg>.*?)(?:\n(?<trace>(?:\#\d+|Stack trace:|in \/)[\s\S]*))$/', $rest, $mm)) {
                $message = trim($mm['msg']);
                $exception = trim($mm['trace']);
            }

            $entries[] = [
                'id' => $offset + $idx + 1,
                'created_at' => $m['time'],
                'channel' => $m['channel'],
                'level' => strtolower($m['level']),
                'event' => null,
                'message' => $message,
                'context' => null,
                'exception' => $exception,
            ];
        }

        return collect($entries);
    }
}
