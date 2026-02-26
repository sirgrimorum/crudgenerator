<?php

namespace Sirgrimorum\CrudGenerator\Tests\Feature;

use Sirgrimorum\CrudGenerator\Tests\TestCase;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Sirgrimorum\CrudGenerator\Tests\Fixtures\StubModel;
use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Support\Facades\View;

#[CoversClass(CrudGenerator::class)]
class EntryPointsTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        parent::setUp();
        
        $configRaw = [
            'modelo' => StubModel::class,
            'tabla' => 'stub_models',
            'id' => 'id',
            'nombre' => 'name',
            'url' => 'Sirgrimorum_CrudAdministrator',
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text', 'label' => 'Name'],
            ],
            'botones' => [
                'create' => 'Create',
                'show' => 'Show',
                'edit' => 'Edit',
                'remove' => 'Remove',
            ],
        ];
        
        $this->config = CrudGenerator::getConfig(StubModel::class, false, $configRaw);
        
        StubModel::create(['name' => 'Test Record']);
    }

    public function test_create_returns_view_string()
    {
        try {
            $result = CrudGenerator::create($this->config);
            $this->assertIsString($result);
            $this->assertStringContainsString('form', $result);
            $this->assertStringContainsString('name', $result);
        } catch (\Exception $e) {
            $this->fail("Create failed: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function test_show_returns_view_string()
    {
        $model = StubModel::first();
        try {
            $result = CrudGenerator::show($this->config, $model->id);
            $this->assertIsString($result);
            $this->assertStringContainsString('Test Record', $result);
        } catch (\Exception $e) {
            $this->fail("Show failed: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function test_edit_returns_view_string()
    {
        $model = StubModel::first();
        try {
            $result = CrudGenerator::edit($this->config, $model->id);
            $this->assertIsString($result);
            $this->assertStringContainsString('form', $result);
            $this->assertStringContainsString('Test Record', $result);
        } catch (\Exception $e) {
            $this->fail("Edit failed: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function test_lists_returns_view_string()
    {
        try {
            // Need to make sure $registros are objects for the view
            $registros = StubModel::all();
            $result = CrudGenerator::lists($this->config, false, false, $registros);
            $this->assertIsString($result);
            $this->assertStringContainsString('Test Record', $result);
        } catch (\Exception $e) {
            $this->fail("Lists failed: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}
