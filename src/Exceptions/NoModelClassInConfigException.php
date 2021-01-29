<?php

namespace Sirgrimorum\CrudGenerator\Exceptions;

use Exception;
use Sirgrimorum\CrudGenerator\Traits\ExceptionMensajes;

class NoModelClassInConfigException extends Exception
{
    use ExceptionMensajes;
    /**
     * Create a new exception instance.
     *
     * @param  array $errores
     * @return void
     */
    public function __construct($model)
    {
        parent::__construct($this->getMensaje($model, $this));
    }
}
