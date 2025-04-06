<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class HomePage extends Page
{
    /**
     * Obtém a URL para a página.
     *
     * @return string
     */
    public function url(): string
    {
        return '/';
    }

    /**
     * Afirma que o navegador está na página.
     *
     * @return void
     */
    public function assert(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->assertPathIs($this->url());
        });
    }

    /**
     * Obtém os atalhos de elemento para a página.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@element' => '#selector',
        ];
    }
}