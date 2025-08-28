<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['auth']->extend('jwt', function ($app, $name, array $config) {
            return $app->make(\PHPOpenSourceSaver\JWTAuth\JWTGuard::class);
        });
    }
}
