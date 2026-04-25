<?php

namespace Sanvex\Core\Tests;

use Orchestra\Testbench\TestCase;
use Sanvex\Core\SanvexServiceProvider;

abstract class CoreTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SanvexServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('sanvex.kek', str_repeat('k', 32));
        $app['config']->set('cache.default', 'array');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh', ['--database' => 'testing'])->run();
    }
}
