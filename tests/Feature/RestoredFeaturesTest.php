<?php

namespace Sirgrimorum\CrudGenerator\Tests\Feature;

use PHPUnit\Framework\Attributes\CoversClass;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Sirgrimorum\CrudGenerator\ErrorCatcher;
use Sirgrimorum\CrudGenerator\Tests\TestCase;

/**
 * Behaviour that commit 3deaf98 rolled back and 3.8.44 restores.
 */
#[CoversClass(CrudGenerator::class)]
class RestoredFeaturesTest extends TestCase
{
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../src/Migrations');
    }

    public function test_catchederror_list_renders_with_array_buttons()
    {
        ErrorCatcher::catch(new \RuntimeException('boom from restore test'));
        $config = CrudGenerator::getConfig('catchederror', false, config('sirgrimorum.models.catchederror'));

        $html = CrudGenerator::lists($config);

        $this->assertIsString($html);
        $this->assertStringContainsString('boom from restore test', $html);
    }

    public function test_is_json_string_accepts_arrays_and_rejects_scalars()
    {
        $this->assertTrue(CrudGenerator::isJsonString('[]'));
        $this->assertTrue(CrudGenerator::isJsonString('["a","b"]'));
        $this->assertFalse(CrudGenerator::isJsonString('"texto"'));
        $this->assertFalse(CrudGenerator::isJsonString('true'));
    }

    public function test_string_helpers()
    {
        $this->assertSame('uno dos...', CrudGenerator::truncateText('uno dos tres cuatro', 9));
        $this->assertSame('corto', CrudGenerator::truncateText('corto', 9));
        $this->assertSame(['name', 'id'], CrudGenerator::getCamposDeReplacementString('<-name-> (<-id->)'));
        $this->assertSame('Ana (7)', CrudGenerator::replaceCamposEnString(['name' => 'Ana', 'id' => 7], '<-name-> (<-id->)'));
    }
}
