<?php

namespace Sirgrimorum\CrudGenerator\Tests\Unit;

use Sirgrimorum\CrudGenerator\Tests\TestCase;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Sirgrimorum\CrudGenerator\Tests\Fixtures\StubModel;
use Sirgrimorum\CrudGenerator\Tests\Fixtures\Category;
use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Http\Request;

#[CoversClass(CrudGenerator::class)]
class SaveObjetoPhase2Test extends TestCase
{
    public function test_save_objeto_relationship_belongsto()
    {
        $category = Category::create(['name' => 'Tech']);
        
        $config = [
            'modelo' => StubModel::class,
            'id' => 'id',
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text'],
                'category' => ['nombre' => 'category_id', 'tipo' => 'relationship'], // In Phase 1 it is treated like simple field or associate
            ],
        ];

        $request = Request::create('/', 'POST', [
            'name' => 'Alice',
            'category' => $category->id,
        ]);
        
        $result = CrudGenerator::saveObjeto($config, $request);

        $this->assertEquals($category->id, $result->category_id);
        $this->assertEquals('Tech', $result->category->name);
    }

    public function test_save_objeto_relationshipssel_many_to_many()
    {
        $cat1 = Category::create(['name' => 'PHP']);
        $cat2 = Category::create(['name' => 'Laravel']);
        
        $config = [
            'modelo' => StubModel::class,
            'id' => 'id',
            'campos' => [
                'name' => ['nombre' => 'name', 'tipo' => 'text'],
                'relatedCategories' => [
                    'tipo' => 'relationshipssel',
                    'columnas' => [
                        ['campo' => 'priority', 'type' => 'number', 'valor' => 0]
                    ]
                ],
            ],
        ];

        // relationshipssel expects data in a specific format in the request
        $request = Request::create('/', 'POST', [
            'name' => 'Alice',
            'relatedCategories' => [
                $cat1->id => [],
                $cat2->id => [],
            ],
            // For pivot data
            'relatedCategories_priority_' . $cat1->id => 10,
            'relatedCategories_priority_' . $cat2->id => 20,
        ]);
        
        $result = CrudGenerator::saveObjeto($config, $request);

        $this->assertCount(2, $result->relatedCategories);
        $this->assertEquals(10, $result->relatedCategories()->find($cat1->id)->pivot->priority);
        $this->assertEquals(20, $result->relatedCategories()->find($cat2->id)->pivot->priority);
    }
}
