<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

final class QuoteService
{
    public function __construct(
        private ?array $quotes = null,
        private ?string $jsonPath = null,
        private string $timezone = 'Europe/London',
        private string $anchor = '2025-01-01',
        private int $cacheTtl = 86400,
    ) {}

    /** @return list<array{text:string,author?:string,source?:string,tags?:array}> */
    private function all(): array
    {
        if ($this->quotes !== null) {
            return $this->quotes;
        }

        $path = $this->jsonPath ?? resource_path('data/quotes.json');
        if (!File::exists($path)) return [];

        $cacheKey = 'quotes:v1:' . (string) @filemtime($path);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($path) {
            $decoded = json_decode(File::get($path), true);
            if (!is_array($decoded)) return [];
            return array_values(array_filter($decoded, fn ($q) =>
                is_array($q) && isset($q['text']) && is_string($q['text']) && $q['text'] !== ''
            ));
        });
    }

        public function forDate(Carbon $date): array
    {
        $quotes = $this->all();
        if (empty($quotes)) {
            return ['text' => 'Keep going.', 'author' => 'Unknown'];
        }

        $anchor = Carbon::parse($this->anchor, $this->timezone)->startOfDay();
        $d      = $date->clone()->timezone($this->timezone)->startOfDay();
        $days   = $anchor->diffInDays($d, false);
        $idx    = (($days % count($quotes)) + count($quotes)) % count($quotes);

        return $quotes[$idx];
    }
}