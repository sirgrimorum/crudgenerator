<?php
namespace Sirgrimorum\CrudGenerator\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Sirgrimorum\CrudGenerator\CrudGeneratorServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        View::share('errors', new ViewErrorBag());
        $this->app['router']->aliasMiddleware('crudgenlocalization', \Sirgrimorum\CrudGenerator\Middleware\CrudGeneratorLocaleRedirect::class);
        
        // Run stub model migrations manually since they are not in the package
        $this->app['db']->connection()->getSchemaBuilder()->dropIfExists('category_stub_model');
        $this->app['db']->connection()->getSchemaBuilder()->dropIfExists('categories');
        $this->app['db']->connection()->getSchemaBuilder()->dropIfExists('stub_models');

        $this->app['db']->connection()->getSchemaBuilder()->create('stub_models', function ($table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->text('bio')->nullable();
            $table->integer('age')->nullable();
            $table->boolean('active')->default(true);
            $table->string('status')->nullable();
            $table->string('image')->nullable();
            $table->json('tags')->nullable();
            $table->date('birth_date')->nullable();
            $table->datetime('scheduled_at')->nullable();
            $table->time('opens_at')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('categories', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('category_stub_model', function ($table) {
            $table->unsignedBigInteger('stub_model_id');
            $table->unsignedBigInteger('category_id');
            $table->integer('priority')->default(0);
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            CrudGeneratorServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:yvS68vVrTk8vS68vVrTk8vS68vVrTk8vS68vVrTk8vS=');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'mysql',
            'host'     => '127.0.0.1',
            'port'     => '3306',
            'database' => 'sirgrimorum_test',
            'username' => 'root',
            'password' => '',
            'charset'  => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'   => '',
        ]);

        // Set up the prefixes that seem to be missing from the default config but used in code
        $app['config']->set('sirgrimorum.crudgenerator.trans_prefix', '__trans__');
        $app['config']->set('sirgrimorum.crudgenerator.route_prefix', '__route__');
        $app['config']->set('sirgrimorum.crudgenerator.url_prefix', '__url__');
        $app['config']->set('sirgrimorum.crudgenerator.transarticle_prefix', '__transarticle__');
        $app['config']->set('sirgrimorum.crudgenerator.locale_key', '__locale__');
        
        $app['config']->set('sirgrimorum.crudgenerator.admin_prefix', 'admin');
        $app['config']->set('sirgrimorum.crudgenerator.login_path', 'login');
        $app['config']->set('sirgrimorum.crudgenerator.status_messages_key', 'status');
        $app['config']->set('sirgrimorum.crudgenerator.error_messages_key', 'error');
        $app['config']->set('sirgrimorum.crudgenerator.login_redirect_key', 'login_redirect');
        $app['config']->set('sirgrimorum.crudgenerator.permission', true);
    }
}
