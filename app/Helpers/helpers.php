<?php
namespace App\Helpers;

/**
* Função auxiliar para colocar novalidate nos formulários nos testes do dusk
* Necessario para escapar a validação do javascript
*
* @return void
*/
function disableValidationIfTesting(): void
{
    if (env('APP_ENV') == 'dusk.local') {
        echo 'novalidate';
    } else {
        return;
    }
}