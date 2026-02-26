<?php

namespace Sirgrimorum\CrudGenerator\Tests\Feature;

use Sirgrimorum\CrudGenerator\Tests\TestCase;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Sirgrimorum\CrudGenerator\Tests\Fixtures\StubModel;
use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Sirgrimorum\CrudGenerator\CrudController;

#[CoversClass(CrudController::class)]
class ControllerLifecycleTest extends TestCase
{
    private string $encodedParametros;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        
        // Mock some translations
        $this->app['translator']->addLines([
            'crudgenerator::admin.messages.200' => 'Success',
            'crudgenerator::admin.messages.permission' => 'Permission Denied',
            'crudgenerator::admin.layout.borrar' => 'Delete',
            'crudgenerator::admin.messages.destroy_success' => ':modelName deleted',
        ], 'en');

        // Register the model in admin_routes
        Config::set('sirgrimorum.crudgenerator.admin_routes.stubmodel', 'stubmodel_config');
        Config::set('stubmodel_config', [
            'modelo' => StubModel::class,
            'tabla' => 'stub_models',
            'id' => 'id',
            'nombre' => 'name',
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text', 'label' => 'Name'],
            ],
        ]);

        // Create a record
        $model = StubModel::create(['name' => 'To be deleted']);
        
        $config = CrudGenerator::getConfig('stubmodel');
        $this->encodedParametros = $config['parametros'];
    }

    public function test_index_returns_json_when_requested()
    {
        $response = $this->get(route('sirgrimorum_modelos::index', [
            'modelo' => 'stubmodel',
            '__parametros' => $this->encodedParametros,
            '_return' => 'purejson'
        ]));

        $response->assertStatus(200);
        $response->assertJsonFragment(['status' => 200]);
        $this->assertIsArray($response->json('result'));
    }

    public function test_permission_denied_returns_403_json()
    {
        // Set permission to false
        Config::set('sirgrimorum.crudgenerator.permission', false);

        $response = $this->get(route('sirgrimorum_modelos::index', [
            'modelo' => 'stubmodel',
            '__parametros' => $this->encodedParametros,
            '_return' => 'purejson'
        ]));

        $response->assertStatus(403);
        $response->assertJsonFragment(['status' => 403]);
    }

    public function test_destroy_deletes_record_and_returns_json()
    {
        $model = StubModel::first();
        
        $response = $this->delete(route('sirgrimorum_modelo::destroy', [
            'modelo' => 'stubmodel',
            'registro' => $model->id,
            '__parametros' => $this->encodedParametros,
            '_return' => 'purejson'
        ]));

        $response->assertStatus(200);
        $this->assertNull(StubModel::find($model->id));
    }
}
