<?php

namespace Sirgrimorum\CrudGenerator\Exceptions;

use Exception;
use Sirgrimorum\CrudGenerator\Traits\ExceptionMensajes;

class NoValueInCeldaException extends Exception
{
    use ExceptionMensajes;
    /**
     * Create a new exception instance.
     *
     * @param  array $errores
     * @return void
     */
    public function __construct($model, $campo)
    {
        parent::__construct(str_replace(":campo", $campo, $this->getMensaje($model, $this)));
    }
}
