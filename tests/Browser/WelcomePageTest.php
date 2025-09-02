<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Services\QuoteService;

class WelcomeQuoteTest extends DuskTestCase
{
    public function test_quote_text_is_visible(): void
    {
        $fake = new QuoteService(quotes: [
            ['text' => 'The test appears', 'author' => 'Ada'],
        ]);
        $this->app->instance(QuoteService::class, $fake);

        $this->browse(function (Browser $browser) {
            $browser->visit(route('home'))
                ->waitForText('The test appears', 5)
                ->assertSee('The test appears')
                ->assertSee('Ada');
        });
    }
}
