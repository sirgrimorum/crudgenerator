<?php

namespace Sirgrimorum\CrudGenerator\Tests\Unit;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Sirgrimorum\CrudGenerator\ErrorCatcher;
use Sirgrimorum\CrudGenerator\Tests\TestCase;

#[CoversClass(ErrorCatcher::class)]
class ErrorCatcherDataTest extends TestCase
{
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../src/Migrations');
    }

    public function test_secrets_are_not_kept()
    {
        $request = Request::create('/boom', 'POST', ['password' => 'hunter2', '_token' => 'csrf', 'nombre' => 'ok'], ['laravel_session' => 'abc']);
        $request->setLaravelSession($session = $this->app['session']->driver());
        $session->put(['_token' => 'csrf', 'locale' => 'es']);
        $this->app->instance('request', $request);

        $exception = (function ($dsn, $user, $password) {
            return new \RuntimeException('boom');
        })('mysql:host=db', 'ekko_app', 'db-secret');

        $method = new \ReflectionMethod(ErrorCatcher::class, 'processCurrentError');
        $method->setAccessible(true);
        $data = $method->invoke(new ErrorCatcher($exception));

        $this->assertSame(['nombre' => 'ok'], $data['request']['data']);
        $this->assertSame(['locale' => 'es'], $data['request']['session']);
        $this->assertArrayNotHasKey('cookie', $data['request']);
        $this->assertStringNotContainsString('db-secret', json_encode($data['trace']));
    }
}
