<?php namespace Sirgrimorum\CrudGenerator;

use Illuminate\Support\Facades\Facade;

class CrudGeneratorFacade extends Facade {

    /**
     * Name of the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CrudGenerator';
    }

} 