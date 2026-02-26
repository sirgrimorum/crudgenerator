<?php

namespace Sirgrimorum\CrudGenerator\Tests\Unit;

use Sirgrimorum\CrudGenerator\Tests\TestCase;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Sirgrimorum\CrudGenerator\Tests\Fixtures\StubModel;
use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

#[CoversClass(CrudGenerator::class)]
class ValidateModelTest extends TestCase
{
    public function test_validate_model_no_rules_returns_false()
    {
        $config = [
            'modelo' => StubModel::class,
            'id' => 'id',
            'campos' => [],
        ];
        $request = Request::create('/', 'POST', []);

        $result = CrudGenerator::validateModel($config, $request);

        $this->assertFalse($result);
    }

    public function test_validate_model_with_rules_and_valid_request()
    {
        $config = [
            'modelo' => StubModel::class,
            'id' => 'id',
            'rules' => [
                'name' => 'required|min:3',
            ],
            'campos' => [
                'name' => ['label' => 'Name Label'],
            ],
        ];
        $request = Request::create('/', 'POST', ['name' => 'John Doe']);

        $result = CrudGenerator::validateModel($config, $request);

        $this->assertInstanceOf(\Illuminate\Contracts\Validation\Validator::class, $result);
        $this->assertFalse($result->fails());
    }

    public function test_validate_model_with_rules_and_invalid_request()
    {
        $config = [
            'modelo' => StubModel::class,
            'id' => 'id',
            'rules' => [
                'name' => 'required|min:3',
            ],
            'campos' => [
                'name' => ['label' => 'Name Label'],
            ],
        ];
        $request = Request::create('/', 'POST', ['name' => 'Jo']);

        $result = CrudGenerator::validateModel($config, $request);

        $this->assertInstanceOf(\Illuminate\Contracts\Validation\Validator::class, $result);
        $this->assertTrue($result->fails());
        $this->assertArrayHasKey('name', $result->errors()->toArray());
    }
}
