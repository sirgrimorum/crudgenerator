<?php

namespace Sirgrimorum\CrudGenerator\Tests\Unit;

use Sirgrimorum\CrudGenerator\Tests\TestCase;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CrudGenerator::class)]
class IsJsonStringTest extends TestCase
{
    public function test_accepts_json_with_unicode_escapes_and_escaped_quotes()
    {
        $this->assertTrue(CrudGenerator::isJsonString(json_encode(['pregunta' => '¿Cuál es tu cédula?'])));
        $this->assertTrue(CrudGenerator::isJsonString(json_encode(['texto' => 'dice "hola"'])));
        $this->assertTrue(CrudGenerator::isJsonString('{"a":"b"}'));
    }

    public function test_rejects_non_json()
    {
        $this->assertFalse(CrudGenerator::isJsonString('no es json'));
        $this->assertFalse(CrudGenerator::isJsonString('{"a":'));
        $this->assertFalse(CrudGenerator::isJsonString('123'));
        $this->assertFalse(CrudGenerator::isJsonString(null));
    }
}
