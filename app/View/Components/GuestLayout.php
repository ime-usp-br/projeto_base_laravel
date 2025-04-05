<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * Obtém a visão / conteúdo que representa o componente.
     *
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}