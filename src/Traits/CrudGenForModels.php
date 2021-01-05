<?php

namespace Sirgrimorum\CrudGenerator\Traits;

use Illuminate\Support\Arr;

trait CrudGenForModels
{
    /**
     * Get the flied value using the configuration array
     * 
     * @param string $key The field to return
     * @param boolean $justValue Optional If return just the formated value (true) or an array with 3 elements, label, value and data (detailed data for the field)
     * @return mixed
     */
    public function get($key, $justValue = true)
    {
        $celda = \Sirgrimorum\CrudGenerator\CrudGenerator::field_array($this, $key);
        if ($justValue) {
            return $celda['value'];
        } else {
            return $celda;
        }
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
