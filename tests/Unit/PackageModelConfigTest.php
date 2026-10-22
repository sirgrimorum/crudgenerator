<?php

namespace Sirgrimorum\CrudGenerator\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Sirgrimorum\CrudGenerator\ErrorCatcherController;
use Sirgrimorum\CrudGenerator\Tests\TestCase;

#[CoversClass(CrudGenerator::class)]
#[CoversClass(ErrorCatcherController::class)]
class PackageModelConfigTest extends TestCase
{
    public function test_error_catcher_controller_builds_without_its_table()
    {
        // route:list builds every controller; apps without catched_errors must not abort here
        $this->assertInstanceOf(ErrorCatcherController::class, $this->app->make(ErrorCatcherController::class));
    }

    public function test_get_model_finds_the_package_own_models()
    {
        $getModel = new \ReflectionMethod(CrudGenerator::class, 'getModel');

        $this->assertSame('Sirgrimorum\CrudGenerator\Models\Catchederror', $getModel->invoke(null, 'catchederror'));
    }
}
