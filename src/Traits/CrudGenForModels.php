<?php

namespace Sirgrimorum\CrudGenerator\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

trait CrudGenForModels
{
    /**
     * Get the flied value using the configuration array
     * 
     * @param string $key Optional The field to return, if null will return the entire registry
     * @param boolean $justValue Optional If return just the formated value (true) or an array with 3 elements, label, value and data (detailed data for the field)
     * @param mixed $config Optional, The configuration route or array to load. empty or ''(default) to automaticaly get it form curdgen configuration file and/or __parameters
     * @return mixed
     */
    public function get($key = null, $justValue = true, $config = "")
    {
        if ($key !== null) {
            $celda = \Sirgrimorum\CrudGenerator\CrudGenerator::field_array($this, $key, $justValue ? 'simple' : 'complete', $config);
            if ($justValue) {
                return $celda['value'];
            } else {
                return $celda;
            }
        } else {
            if ($config == "") {
                $modelo = strtolower(class_basename(get_class($this)));
                $config = \Sirgrimorum\CrudGenerator\CrudGenerator::getConfigWithParametros($modelo);
            }
            return \Sirgrimorum\CrudGenerator\CrudGenerator::registry_array($config, $this, $justValue ? 'simple' : 'complete');
        }
    }

    /**
     * Query from using config array and request
     * 
     * @param mixed $config Optional, The configuration route or array to load. empty or ''(default) to automaticaly get it form curdgen configuration file and/or __parameters
     * @return Builder
     */
    public static function scopeFromConfig(Builder $query, $config = ""){
        if ($config == "") {
            $modelo = strtolower(class_basename(get_called_class()));
            $config = \Sirgrimorum\CrudGenerator\CrudGenerator::getConfigWithParametros($modelo);
        }
        return \Sirgrimorum\CrudGenerator\CrudGenerator::filterWithQuery($query, $config);
    }

    /**
     * Generate a list of objects of a model in array format
     * 
     * @param Collection|Array|Builder $registros Optional Objects to show if null it will look for the 'query' field in config, if not found, will take all the records of model. Use start and length of request to paginate
     * @param boolean $justValue Optional If return just the formated value (true) or an array with 3 elements, label, value and data (detailed data for the field)
     * @param mixed $config Optional, The configuration route or array to load. empty or ''(default) to automaticaly get it form curdgen configuration file and/or __parameters
     * @return array with the objects in the config format
     */
    public static function getAllFromConfig($registros = null, $justValue = true, $config = ""){
        if ($config == "") {
            $modelo = strtolower(class_basename(get_called_class()));
            $config = \Sirgrimorum\CrudGenerator\CrudGenerator::getConfigWithParametros($modelo);
        }
        return \Sirgrimorum\CrudGenerator\CrudGenerator::lists_array($config, $registros, $justValue ? 'simple' : 'complete');
    }

    /**
     * Get the data to show for a specific action using the "[action]_show" option of the 
     * column configuration array
     * 
     * @param string $action The name of the action (mainly show and list)
     * @param string $key Optional The field to return, if null will return the entire registry
     * @param bool $justString If only return strings (the arrays print_r between <pre></pre>)
     * @param mixed $config Optional, The configuration route or array to load. empty or ''(default) to automaticaly get it form curdgen configuration file and/or __parameters
     * @return string|array The data to show after processing or the $data array
     */
    public function getDatoToShowIn($action, $key, $justString = true, $config = ""){
        if ($config == "") {
            $modelo = strtolower(class_basename(get_class($this)));
            $config = \Sirgrimorum\CrudGenerator\CrudGenerator::getConfigWithParametros($modelo);
        }
        if (!isset($config[$key]) && isset($this->{$key})){
            $modelo = strtolower(class_basename(get_class($this)));
            $auxConfig = \Sirgrimorum\CrudGenerator\CrudGenerator::getConfig($modelo);
            if (!isset($auxConfig[$key])){
                return "-";
            }else{
                $config[$key] = $auxConfig[$key];
            }
        }elseif(!isset($config[$key])){
            return "-";
        }
        return \Sirgrimorum\CrudGenerator\CrudGenerator::getDatoToShow($this->get($key, false, $config), $action, $config[$key], $this, $justString);
    }

    /**
     * Get the src attribute from a field
     * 
     * @param string $key The field to return, if null will return the entire registry
     * @param mixed $config Optional, The configuration route or array to load. empty or ''(default) to automaticaly get it form curdgen configuration file and/or __parameters
     * @return mixed
     */
    public function getSrc($key, $config = ""){
        $datos = $this->get($key, false, $config);
        if (isset($datos['url'])) {
            return  str_replace("\\", "/", $datos['url']);
        }
        return $this->{$key};
    }

    /**
     * Check if a value or set of values match the content of a field
     * 
     * @param string $key The field to check
     * @param mixed $values The value or array of values to look for
     * @param boolean $or Optional if only one of the values should be present, if false, and $values is array, all of them should be present, if false and $values is not array, the field value should be equal to $values. Default true
     * @return boolean if the value is found in the content of the field
     */
    public function fieldHas($key, $values, $or = true)
    {
        $datos = $this->get($key, false);
        if (is_array($datos['data'])) {
            if (is_array($values)) {
                if (Arr::isAssoc($datos['data'])) {
                    foreach ($values as $value) {
                        if ($or) {
                            if (in_array($value, $datos['data'])) {
                                return true;
                            }
                        } else {
                            if (!in_array($value, $datos['data']) && !Arr::has($datos['data'], $value)) {
                                return false;
                            }
                        }
                    }
                    return Arr::has($datos['data'], $values);
                } else {
                    foreach ($values as $value) {
                        if ($or) {
                            if (in_array($value, $datos['data'])) {
                                return true;
                            }
                        } else {
                            if (!in_array($value, $datos['data'])) {
                                return false;
                            }
                        }
                    }
                }
            } else {
                if (in_array($values, $datos['data'])) {
                    return true;
                }
                if (Arr::isAssoc($datos['data'])) {
                    return Arr::has($datos['data'], $values);
                }
            }
        } else {
            if (is_array($values)) {
                foreach ($values as $value) {
                    if ($or) {
                        if (strpos($datos['data'], $value) !== false) {
                            return true;
                        }
                    } else {
                        if (strpos($datos['data'], $value) === false) {
                            return false;
                        }
                    }
                }
            } else {
                if ($or) {
                    return strpos($datos['data'], $values) !== false;
                } else {
                    return $datos['data'] == $values;
                }
            }
        }
        return !$or;
    }
}
