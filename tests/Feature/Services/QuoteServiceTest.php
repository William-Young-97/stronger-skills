<?php

use App\Services\QuoteService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

final class QuoteLoadingTest extends \Tests\TestCase
{
    private function writeTempJson(array $data): string
    {
        $path = tempnam(sys_get_temp_dir(), 'quotes_') . '.json';
        file_put_contents($path, json_encode($data));
        return $path;
    }

    public function test_loads_quotes_from_json_path(): void
    {
        Cache::flush();
        $path = $this->writeTempJson([
            ['text' => 'Q1', 'author' => 'A1'],
            ['text' => 'Q2', 'author' => 'A2'],
        ]);

        $svc = new QuoteService(jsonPath: $path);

        $q = $svc->forDate(Carbon::parse('2025-01-01', 'Europe/London'));
        $this->assertSame('Q1', $q['text']);
    }

    public function test_filters_malformed_entries(): void
    {
        Cache::flush();
        $path = $this->writeTempJson([
            ['text' => ''],                       // invalid
            ['author' => 'NoText'],               // invalid
            ['text' => 'Valid', 'author' => 'A'], // valid
        ]);

        $svc = new QuoteService(jsonPath: $path);

        $q = $svc->forDate(Carbon::parse('2025-01-01', 'Europe/London'));
        $this->assertSame('Valid', $q['text']);
    }

    public function test_missing_file_returns_default_quote(): void
    {
        Cache::flush();
        $svc = new QuoteService(jsonPath: '/non/existent/path.json');

        $q = $svc->forDate(Carbon::parse('2025-01-01', 'Europe/London'));
        $this->assertSame('Keep going.', $q['text']);
        $this->assertSame('Unknown', $q['author']);
    }

    public function test_cache_key_changes_when_file_mtime_changes(): void
    {
        Cache::flush();

        $path = $this->writeTempJson([['text' => 'Old', 'author' => 'A']]);
        try {
            $svc  = new QuoteService(jsonPath: $path);

            $first = $svc->forDate(Carbon::parse('2025-01-01', 'Europe/London'));
            $this->assertSame('Old', $first['text']);

            sleep(1);
            file_put_contents($path, json_encode([['text' => 'New', 'author' => 'A']]));
            clearstatcache(true, $path); // ensure new mtime is seen

            $second = $svc->forDate(Carbon::parse('2025-01-01', 'Europe/London'));
            $this->assertSame('New', $second['text']);
        } finally {
            @unlink($path);
        }
    }

}
