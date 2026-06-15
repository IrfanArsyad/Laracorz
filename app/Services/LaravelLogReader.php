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
    private const LINE_PATTERN = '/^\[(?<time>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?<env>\w+)\.(?<level>\w+): (?<message>.*?)(?:\{(?<context>".*"[\s\S]*?)\})?$/m';

    public function __construct(private readonly string $logPath)
    {
    }

    /**
     * @param  array{level?: string|null, search?: string|null}  $filters
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
     * @param  array{level?: string|null, search?: string|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function all(array $filters = []): Collection
    {
        if (! is_file($this->logPath)) {
            return collect();
        }

        $raw = @file_get_contents($this->logPath);
        if ($raw === false || $raw === '') {
            return collect();
        }

        $entries = $this->parse($raw);

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

        // Tampil terbaru di atas
        return $entries->reverse()->values();
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
    private function parse(string $raw): Collection
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
                'id' => $idx + 1,
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
