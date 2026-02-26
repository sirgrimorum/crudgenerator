<?php

namespace Sirgrimorum\CrudGenerator\Tests\Unit;

use Sirgrimorum\CrudGenerator\Tests\TestCase;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Sirgrimorum\CrudGenerator\Tests\Fixtures\StubModel;
use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Support\Facades\Config;

#[CoversClass(CrudGenerator::class)]
class GetConfigTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
    }

    public function test_get_config_with_array()
    {
        $config = [
            'modelo' => StubModel::class,
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text'],
            ],
        ];

        $result = CrudGenerator::getConfig(StubModel::class, false, $config);

        $this->assertIsArray($result);
        $this->assertEquals(StubModel::class, $result['modelo']);
        $this->assertArrayHasKey('parametros', $result);
    }

    public function test_get_config_from_config_key()
    {
        $configArray = [
            'modelo' => StubModel::class,
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text'],
            ],
        ];
        Config::set('my_custom_config', $configArray);

        $result = CrudGenerator::getConfig(StubModel::class, false, 'my_custom_config');

        $this->assertIsArray($result);
        $this->assertEquals(StubModel::class, $result['modelo']);
    }

    public function test_smart_merge_config()
    {
        $baseConfig = [
            'modelo' => StubModel::class,
            'titulo' => 'Base Title',
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text', 'label' => 'Base Label'],
                'age' => ['nombre' => 'age', 'tipo' => 'number'],
            ],
        ];
        
        $overwritingConfig = [
            'titulo' => 'Overwritten Title',
            'campos' => [
                'name' => ['label' => 'Overwritten Label'],
                'age' => 'notThisTime', // Should remove age
            ],
        ];

        $result = CrudGenerator::getConfig(StubModel::class, true, $overwritingConfig, $baseConfig);

        $this->assertEquals('Overwritten Title', $result['titulo']);
        $this->assertEquals('Overwritten Label', $result['campos']['name']['label']);
        $this->assertArrayNotHasKey('age', $result['campos']);
    }

    public function test_auto_generate_config()
    {
        // This will use the DB schema from TestCase defineDatabaseMigrations
        $result = CrudGenerator::getConfig(StubModel::class, false, 'render');

        $this->assertIsArray($result);
        $this->assertEquals(StubModel::class, $result['modelo']);
        $this->assertArrayHasKey('campos', $result);
        
        // Check if some columns from StubModel are present
        $this->assertArrayHasKey('name', $result['campos']);
        $this->assertArrayHasKey('email', $result['campos']);
        $this->assertArrayHasKey('bio', $result['campos']);
    }
}
