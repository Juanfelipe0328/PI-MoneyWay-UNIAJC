<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    // PF-01: sin sesion iniciada, las paginas privadas mandan al login
    public function test_un_invitado_es_redirigido_al_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/registrar')->assertRedirect('/login');
    }

    // La pagina de inicio de sesion carga para cualquier visitante
    public function test_la_pagina_de_login_carga(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    // La raiz nunca muestra la pagina de bienvenida de Laravel: redirige
    public function test_la_raiz_redirige(): void
    {
        $this->get('/')->assertStatus(302);
    }
}
