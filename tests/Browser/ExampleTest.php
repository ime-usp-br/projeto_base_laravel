<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    /**
     * Um exemplo básico de teste de navegador.
     *
     * @param \Laravel\Dusk\Browser $browser Instância do navegador Dusk.
     * @return void
     */
    public function testBasicExample(Browser $browser): void
    {
        $browser->visit('/')
                ->assertSee('Laravel');
    }
}