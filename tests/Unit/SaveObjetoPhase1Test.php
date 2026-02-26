<?php

namespace Sirgrimorum\CrudGenerator\Tests\Unit;

use Sirgrimorum\CrudGenerator\Tests\TestCase;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Sirgrimorum\CrudGenerator\Tests\Fixtures\StubModel;
use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

#[CoversClass(CrudGenerator::class)]
class SaveObjetoPhase1Test extends TestCase
{
    public function test_save_objeto_basic_types()
    {
        $config = [
            'modelo' => StubModel::class,
            'id' => 'id',
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text'],
                'email' => ['nombre' => 'email', 'tipo' => 'email'],
                'bio' => ['nombre' => 'bio', 'tipo' => 'textarea'],
                'age' => ['nombre' => 'age', 'tipo' => 'number'],
                'active' => ['nombre' => 'active', 'tipo' => 'checkbox'],
                'tags' => ['nombre' => 'tags', 'tipo' => 'json'],
            ],
        ];

        $data = [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'bio' => 'Loves coding',
            'age' => 28,
            'active' => true,
            'tags' => ['php', 'laravel'],
        ];

        $request = Request::create('/', 'POST', $data);
        
        $result = CrudGenerator::saveObjeto($config, $request);

        $this->assertInstanceOf(StubModel::class, $result);
        $this->assertEquals('Alice', $result->name);
        $this->assertEquals('alice@example.com', $result->email);
        $this->assertEquals('Loves coding', $result->bio);
        $this->assertEquals(28, $result->age);
        $this->assertTrue($result->active);
        $this->assertEquals(['php', 'laravel'], $result->tags);
    }

    public function test_save_objeto_date_types()
    {
        $config = [
            'modelo' => StubModel::class,
            'id' => 'id',
            'campos' => [
                'birth_date' => ['nombre' => 'birth_date', 'tipo' => 'date'],
                'scheduled_at' => ['nombre' => 'scheduled_at', 'tipo' => 'datetime'],
                'opens_at' => ['nombre' => 'opens_at', 'tipo' => 'time'],
            ],
        ];

        $data = [
            'birth_date' => '1995-05-15',
            'scheduled_at' => '2026-03-01 10:00:00',
            'opens_at' => '08:30:00',
        ];

        $request = Request::create('/', 'POST', $data);
        
        $result = CrudGenerator::saveObjeto($config, $request);

        $this->assertInstanceOf(StubModel::class, $result);
        // CrudGenerator formats dates as "Y-m-d H:i:s" internally
        $this->assertEquals('1995-05-15 00:00:00', $result->birth_date);
        $this->assertEquals('2026-03-01 10:00:00', $result->scheduled_at);
        // Time might be prepended with current date if using Carbon without explicit format,
        // but CrudGenerator does: $date->format("Y-m-d H:i:s");
        $this->assertStringContainsString('08:30:00', $result->opens_at);
    }

    public function test_save_objeto_with_defaults()
    {
        $config = [
            'modelo' => StubModel::class,
            'id' => 'id',
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text', 'valor' => 'Default Name'],
                'status' => ['nombre' => 'status', 'tipo' => 'select', 'valor' => 'active'],
            ],
        ];

        // Empty request
        $request = Request::create('/', 'POST', []);
        
        $result = CrudGenerator::saveObjeto($config, $request);

        $this->assertEquals('Default Name', $result->name);
        $this->assertEquals('active', $result->status);
    }

    public function test_save_objeto_nodb_flag()
    {
        $config = [
            'modelo' => StubModel::class,
            'id' => 'id',
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text'],
                'virtual_field' => ['nombre' => 'virtual_field', 'tipo' => 'text', 'nodb' => true],
            ],
        ];

        $request = Request::create('/', 'POST', [
            'name' => 'Bob',
            'virtual_field' => 'Secret',
        ]);
        
        $result = CrudGenerator::saveObjeto($config, $request);

        $this->assertEquals('Bob', $result->name);
        // Should not have virtual_field set on model (or at least it shouldn't be in DB)
        $this->assertFalse(isset($result->virtual_field));
    }
}
