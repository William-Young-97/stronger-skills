<?php

use App\Services\QuoteService;
use Carbon\Carbon;
use Tests\TestCase;

final class QuoteServiceTest extends TestCase
{
    private function makeService(): QuoteService
    {
    
        return new QuoteService(
            quotes: [
                ['text' => 'Q1', 'author' => 'A1'],
                ['text' => 'Q2', 'author' => 'A2'],
                ['text' => 'Q3', 'author' => 'A3'],
            ],
            timezone: 'Europe/London',
            anchor: '2025-01-01'
        );
    }

    public function test_it_maps_dates_to_rotating_indices(): void
    {
        $svc = $this->makeService();

        Carbon::setTestNow('2025-01-01 12:00 Europe/London');
        $this->assertSame('Q1', $svc->forDate(now())['text']);

        Carbon::setTestNow('2025-01-02 00:00 Europe/London');
        $this->assertSame('Q2', $svc->forDate(now())['text']);

        // Wrap after the end: Jan 4 -> index 0 (since we have 3 quotes)
        Carbon::setTestNow('2025-01-04 09:00 Europe/London');
        $this->assertSame('Q1', $svc->forDate(now())['text']);
    }
}
