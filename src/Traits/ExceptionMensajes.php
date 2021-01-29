<?php

namespace Sirgrimorum\CrudGenerator\Traits;

use Exception;
use Illuminate\Support\Arr;

trait ExceptionMensajes
{

    /**
     * Get the message to show in the exception
     * 
     * @param string $model The model name
     * @param Exception $exception The exception
     * @return string The message
     */
    private function getMensaje($model, Exception $exception, $extra = "")
    {
        $baseName = class_basename(get_class($exception));
        switch ($baseName) {
            case 'NoModelClassInConfigException':
                $default = 'There is no Model class for the model named ":Modelo" in CrudGenerator::getConfig(":modelo")';
                $tipo = 'no_model_class';
                break;
            case 'SmartMergeConfigException':
                $default = 'The SmartMerge of the config for the model ":Modelo" went wrong';
                $tipo = 'smart_config_error';
                break;
            case 'NoTableForModelException':
                $default = 'There is no valid table for the model named ":Modelo" in CrudGenerator::getConfig(":modelo")';
                $tipo = 'no_table_for_model';
                break;
            case 'PreparingFileForModelException':
                $default = 'Error preparing the file for the model ":Modelo"';
                $tipo = 'error_preparing_file_for_model';
                break;
            case 'PreparingFileException':
                $default = 'Error preparing the file';
                $tipo = 'error_preparing_file';
                break;
            case 'NoStreamException':
                $default = 'Could not open stream for reading ":modelo"';
                $tipo = 'no_stream_for_reading';
                break;
            default:
                $default = 'An error occur with the model ":Modelo"';
                $tipo = 'no_error_know_class';
                break;
        }
        $mensaje = Arr::get(__('crudgenerator::admin.messages'), $tipo, $default);
        $mensaje = str_replace([":modelo", ":Modelo"], [strtolower($model), ucfirst($model)], $mensaje);
        return $mensaje;
    }
}
