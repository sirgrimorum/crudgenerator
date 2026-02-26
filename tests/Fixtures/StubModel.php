<?php
namespace Sirgrimorum\CrudGenerator\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class StubModel extends Model
{
    protected $table = 'stub_models';
    protected $guarded = [];
    protected $casts = [
        'active' => 'boolean',
        'tags'   => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function relatedCategories()
    {
        return $this->belongsToMany(Category::class, 'category_stub_model')
                    ->withPivot('priority');
    }
}
