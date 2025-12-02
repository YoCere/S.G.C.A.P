<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
{
    parent::setUp();

    // Ejecuta migraciones (RefreshDatabase suele hacerlo, pero por seguridad):
    $this->artisan('migrate:fresh');

    // Fakes globales
    \Illuminate\Support\Facades\Notification::fake();
    \Illuminate\Support\Facades\Mail::fake();

    // Si usas cache/config que interfieren:
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');

    // Opcional: ver excepciones de forma clara
    $this->withoutExceptionHandling();
}
}
