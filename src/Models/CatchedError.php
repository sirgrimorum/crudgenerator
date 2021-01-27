<?php

namespace Sirgrimorum\CrudGenerator\Models;

use Illuminate\Database\Eloquent\Model;
use Sirgrimorum\CrudGenerator\Traits\CrudGenForModels;

class Catchederror extends Model {

    use CrudGenForModels;

    protected $table = 'catched_errors';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var  array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    //The validation rules
    public $rules = [];

    //The validation error messages
    public $error_messages = [];

    //For serialization
    protected $with = [];

    public function _construct() {
        $this->error_messages = [];
    }

}
