<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La raíz del sitio lleva al formulario de inicio de sesión.
     */
    public function test_la_raiz_redirige_al_inicio_de_sesion()
    {
        $this->get('/')->assertRedirect(route('acceso'));
    }
}
