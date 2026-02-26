<?php

namespace Sirgrimorum\CrudGenerator\Tests\Feature;

use Sirgrimorum\CrudGenerator\Tests\TestCase;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Sirgrimorum\CrudGenerator\Tests\Fixtures\StubModel;
use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Support\Facades\DB;

#[CoversClass(CrudGenerator::class)]
class DataHelpersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed some data
        StubModel::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'bio' => 'Some bio',
            'age' => 30,
            'active' => true,
        ]);
        
        StubModel::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'bio' => 'Another bio',
            'age' => 25,
            'active' => false,
        ]);
    }

    public function test_registry_array_single_record()
    {
        $model = StubModel::first();
        $config = [
            'modelo' => StubModel::class,
            'tabla' => 'stub_models',
            'id' => 'id',
            'nombre' => 'name',
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text', 'label' => 'Name Label'],
                'active' => ['nombre' => 'active', 'tipo' => 'checkbox', 'label' => 'Active Label', 'value' => [true => 'Yes', false => 'No']],
            ],
            'botones' => ['<a href="/edit/:modelId">Edit</a>'],
        ];

        $result = CrudGenerator::registry_array($config, $model, 'complete');

        $this->assertIsArray($result);
        $this->assertEquals($model->id, $result['id']);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('John Doe', $result['name']['value']);
        $this->assertEquals('Name Label', $result['name']['label']);
        
        $this->assertArrayHasKey('active', $result);
        $this->assertEquals('Yes', $result['active']['value']);
        
        $this->assertArrayHasKey('botones', $result);
        $this->assertStringContainsString('/edit/' . $model->id, $result['botones']);
    }

    public function test_registry_array_simple_format()
    {
        $model = StubModel::first();
        $config = [
            'modelo' => StubModel::class,
            'tabla' => 'stub_models',
            'id' => 'id',
            'nombre' => 'name',
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text', 'label' => 'Name Label'],
            ],
        ];

        $result = CrudGenerator::registry_array($config, $model, 'simple');

        $this->assertIsArray($result);
        $this->assertEquals($model->id, $result['id']);
        $this->assertEquals('John Doe', $result['name']);
        $this->assertArrayNotHasKey('botones', $result);
    }

    public function test_lists_array_multiple_records()
    {
        $config = [
            'modelo' => StubModel::class,
            'tabla' => 'stub_models',
            'id' => 'id',
            'nombre' => 'name',
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text', 'label' => 'Name Label'],
            ],
        ];

        $result = CrudGenerator::lists_array($config, null, 'complete');

        $this->assertCount(2, $result);
        $this->assertEquals('John Doe', $result[0]['name']['value']);
        $this->assertEquals('Jane Smith', $result[1]['name']['value']);
    }

    public function test_lists_array_with_provided_collection()
    {
        $collection = StubModel::where('age', '>', 28)->get();
        $config = [
            'modelo' => StubModel::class,
            'tabla' => 'stub_models',
            'id' => 'id',
            'nombre' => 'name',
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text', 'label' => 'Name Label'],
            ],
        ];

        $result = CrudGenerator::lists_array($config, $collection, 'simple');

        $this->assertCount(1, $result);
        $this->assertEquals('John Doe', $result[0]['name']);
    }
}
