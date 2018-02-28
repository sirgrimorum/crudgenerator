<?php

namespace Sirgrimorum\CrudGenerator\Traits;

trait CrudStrings {

    /**
     * Get a block of comments from the coments string of a method, using a tag
     * @param string $str The comments string
     * @param string $tag The tag to search for, ej: @return, @param
     * @return string
     */
    private static function checkDocBlock($str, $tag = '') {
        if (empty($tag)) {
            return $str;
        }
        $matches = array();
        preg_match("/" . $tag . "(.*)(\\r\\n|\\r|\\n)/U", $str, $matches);
        if (isset($matches[1])) {
            return trim($matches[1]);
        }
    }

    /**
     * extract_tags()
     * Extract specific HTML tags and their attributes from a string.
     *
     * You can either specify one tag, an array of tag names, or a regular expression that matches the tag name(s). 
     * If multiple tags are specified you must also set the $selfclosing parameter and it must be the same for 
     * all specified tags (so you can't extract both normal and self-closing tags in one go).
     * 
     * The function returns a numerically indexed array of extracted tags. Each entry is an associative array
     * with these keys :
     *  tag_name    - the name of the extracted tag, e.g. "a" or "img".
     *  offset      - the numberic offset of the first character of the tag within the HTML source.
     *  contents    - the inner HTML of the tag. This is always empty for self-closing tags.
     *  attributes  - a name -> value array of the tag's attributes, or an empty array if the tag has none.
     *  full_tag    - the entire matched tag, e.g. '<a href="http://example.com">example.com</a>'. This key 
     *                will only be present if you set $return_the_entire_tag to true.      
     *
     * @param string $html The HTML code to search for tags.
     * @param string|array $tag The tag(s) to extract.                           
     * @param bool $selfclosing Whether the tag is self-closing or not. Setting it to null will force the script to try and make an educated guess. 
     * @param bool $return_the_entire_tag Return the entire matched tag in 'full_tag' key of the results array.  
     * @param string $charset The character set of the HTML code. Defaults to ISO-8859-1.
     *
     * @return array An array of extracted tags, or an empty array if no matching tags were found. 
     */
    public static function extract_tags($html, $tag, $selfclosing = null, $return_the_entire_tag = false, $charset = 'ISO-8859-1') {

        if (is_array($tag)) {
            $tag = implode('|', $tag);
        }

        //If the user didn't specify if $tag is a self-closing tag we try to auto-detect it
        //by checking against a list of known self-closing tags.
        $selfclosing_tags = array('area', 'base', 'basefont', 'br', 'hr', 'input', 'img', 'link', 'meta', 'col', 'param');
        if (is_null($selfclosing)) {
            $selfclosing = in_array($tag, $selfclosing_tags);
        }

        //The regexp is different for normal and self-closing tags because I can't figure out 
        //how to make a sufficiently robust unified one.
        if ($selfclosing) {
            $tag_pattern = '@<(?P<tag>' . $tag . ')           # <tag
            (?P<attributes>\s[^>]+)?       # attributes, if any
            \s*/?>                   # /> or just >, being lenient here 
            @xsi';
        } else {
            $tag_pattern = '@<(?P<tag>' . $tag . ')           # <tag
            (?P<attributes>\s[^>]+)?       # attributes, if any
            \s*>                 # >
            (?P<contents>.*?)         # tag contents
            </(?P=tag)>               # the closing </tag>
            @xsi';
        }

        $attribute_pattern = '@
        (?P<name>\w+)                         # attribute name
        \s*=\s*
        (
            (?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)    # a quoted value
            |                           # or
            (?P<value_unquoted>[^\s"\']+?)(?:\s+|$)           # an unquoted value (terminated by whitespace or EOF) 
        )
        @xsi';

        //Find all tags 
        if (!preg_match_all($tag_pattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            //Return an empty array if we didn't find anything
            return array();
        }

        $tags = array();
        foreach ($matches as $match) {

            //Parse tag attributes, if any
            $attributes = array();
            if (!empty($match['attributes'][0])) {

                if (preg_match_all($attribute_pattern, $match['attributes'][0], $attribute_data, PREG_SET_ORDER)) {
                    //Turn the attribute data into a name->value array
                    foreach ($attribute_data as $attr) {
                        if (!empty($attr['value_quoted'])) {
                            $value = $attr['value_quoted'];
                        } else if (!empty($attr['value_unquoted'])) {
                            $value = $attr['value_unquoted'];
                        } else {
                            $value = '';
                        }

                        //Passing the value through html_entity_decode is handy when you want
                        //to extract link URLs or something like that. You might want to remove
                        //or modify this call if it doesn't fit your situation.
                        $value = html_entity_decode($value, ENT_QUOTES, $charset);

                        $attributes[$attr['name']] = $value;
                    }
                }
            }

            $tag = array(
                'tag_name' => $match['tag'][0],
                'offset' => $match[0][1],
                'contents' => !empty($match['contents']) ? $match['contents'][0] : '', //empty for self-closing tags
                'attributes' => $attributes,
            );
            if ($return_the_entire_tag) {
                $tag['full_tag'] = $match[0][0];
            }

            $tags[] = $tag;
        }

        return $tags;
    }

    /**
     * Returns if a value is present in an array using dot notation for key
     * 
     * @param array $array The array
     * @param string $key The haystack using dot notation
     * @param mix $needle The needle
     * 
     * return mix the index in the key if present, false if not present or key not available
     */
    public static function inside_array($array, $key, $needle) {
        if (array_has($array, $key)) {
            foreach (array_get($array, $key) as $index => $value) {
                if ($value == $needle) {
                    return $index;
                }
            }
        }
        return false;
    }

    /**
     * Get if a Model has a relation
     * @param string $model
     * @param string $key the attribute name for the relation
     * @return boolean Wheter the key attribute is a relation or not
     */
    public static function hasRelation($model, $key) {
        return \Sirgrimorum\CrudGenerator\CrudGenerator::isFunctionOfType($model, $key, "Illuminate\Database\Eloquent\Relations\Relation");
    }

    /**
     * Get if a Model method returns certain type of value
     * @param string $model
     * @param string $key the attribute name for the relation
     * @param string $tipo Optional the type of object returned to compare
     * @return boolean Wheter the key attribute is a relation or not
     */
    public static function isFunctionOfType($model, $key, $tipo = "Illuminate\Database\Eloquent\Collection") {
        if (method_exists($model, $key)) {
            return is_a($model->$key(), $tipo);
        } else {
            return false;
        }
    }

    /**
     * Get if a Model has a function
     * @param string $model
     * @param string $key the attribute name for the function
     * @return boolean|int if key is a callable function, return the number of arguments, if not, return false (use strict comparision)
     */
    public static function isFunction($model, $key) {
        $nombreLlamar = "";
        if (is_callable([$model, $key], true, $nombreLlamar) && method_exists($model, $key)) {
            $refClass = new \ReflectionClass($model);
            $refFunc = $refClass->getMethod($key);
            //$refFunc = new \ReflectionFunction($nombreLlamar);
            return $refFunc->getNumberOfParameters();
        } else {
            return false;
        }
    }

    /**
     * Call a function from a model by name, fixin the number of parameters
     * 
     * @param object $model The model
     * @param string $key The name of the function
     * @param array $args The parameters to pass
     * @param int $numArgs Optional the number of parameters that the function recibes
     * @param mix $valorRelleno Optional the value to fill with in the case $numArgs > count($args)
     * @return mix The return value of the function or null if something is wrong
     */
    private static function callFunction($model, $key, $args, $numArgs = false, $valorRelleno = false) {
        if ($numArgs === false) {
            $numArgs = \Sirgrimorum\CrudGenerator\CrudGenerator::isFunction($model, $key);
        }
        if ($numArgs !== false) {
            if ($numArgs > count($args)) {
                for ($i = 0; $i <= ($numArgs - count($args)); $i++) {
                    $args[] = $valorRelleno;
                }
            } elseif ($numArgs < count($args)) {
                for ($i = 0; $i <= (count($args) - $numArgs); $i++) {
                    array_pop($args);
                }
            }
            return call_user_func_array([$model, $key], $args);
        } else {
            return null;
        }
    }

    /**
     * Return the verified Class Name of a model
     * 
     * @param string $modelo The model name
     * @param string $probable Optional The probable initial Model Class Name
     * @return boolean|string The Class Name or false if not found
     */
    private static function getModel($modelo, $probable = '') {
        $modeloClass = $probable;
        if (!class_exists($modeloClass)) {
            $modeloClass = "App\\" . $modelo;
            if (!class_exists($modeloClass)) {
                $modelo = strtolower($modelo);
                $modeloM = ucfirst($modelo);
                $modeloClass = "App\\" . $modeloM;
                if (!class_exists($modeloClass)) {
                    $modeloClass = "App\\" . $modelo;
                    if (!class_exists($modeloClass)) {
                        $modeloClass = "Sirgrimorum\\TransArticles\\Models\\" . $modeloM;
                        if (!class_exists($modeloClass)) {
                            $modeloClass = "Sirgrimorum\\TransArticles\\Models\\" . $modelo;
                            if (!class_exists($modeloClass)) {
                                //return 'There is no Model class for the model name "' . $modelo . '" ind the \Sirgrimorum\CrudGenerator\CrudGenerator::getConfig(String $modelo)';
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return $modeloClass;
    }

    /**
     * Split a query string into names in an array
     * @param string $query The query string
     * @return array Array of names
     */
    private static function splitQueryNames($query) {
        $resultado = [];
        if (($left = (stripos($query, "`"))) !== false) {
            while ($left !== false) {
                //echo "<pre>" . print_r($query, true) . "</pre>";
                if (($right = stripos($query, "`", $left + 1)) === false) {
                    $right = strlen($query);
                }
                $piece = substr($query, $left + 1, $right - ($left + 1));
                $resultado[] = $piece;
                $query = substr($query, $right + 1);
                //echo "<pre>" . print_r(['prefix' => config("sirgrimorum.crudgenerator.trans_prefix"), 'lenprefix' => strlen(config("sirgrimorum.crudgenerator.trans_prefix")), 'left' => $left, 'rigth' => $right, 'piece' => $piece, 'lenpiece' => strlen($piece), 'csss' => $item], true) . "</pre>";
                $left = (stripos($query, "`"));
            }
        }
        return $resultado;
    }

    /**
     * Know if a field name is of certain type by comparing it with a list of comonly used field names 
     * of the same type.
     * 
     * @param string $name The field name
     * @param dtryn $options The probable type name
     * @return boolean True if the $name is found in the probable names list for $option type
     */
    private static function getTypeByName($name, $options) {
        if (!is_array($options)) {
            $options = config("sirgrimorum.crudgenerator.probable_" . $options);
        }

        return (in_array($name, $options));
    }

    /**
     * Get the name attribute of a model by comparing its field names to a list of comonly used field names
     * @param object $model The model object
     * @return string The attribute name or the id field name
     */
    private static function getNameAttribute($model) {
        $attributes = $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());
        $compares = config("sirgrimorum.crudgenerator.probable_name");
        foreach ($compares as $compare) {
            if (in_array($compare, $attributes)) {
                return $compare;
            }
        }
        return $model->getKeyName();
    }

    /**
     * Return the value of a field from an object. 
     * 
     * If $campo is an array, returns a concatenated string with the values separated by de separator.
     * 
     * @param object|array|string $elemento The objects
     * @param string|array $campo The field or list of fields names
     * @param string $separador Optional, the separator to concatenate with
     * @return string
     */
    public static function getNombreDeLista($elemento, $campo, $separador = "-") {
        if (is_object($elemento)) {
            if (is_array($campo)) {
                $strNombre = "";
                $preNombre = "";
                foreach ($campo as $indiceCampo => $nombreCampo) {
                    $strNombre .= $preNombre . $elemento->{$nombreCampo};
                    $preNombre = $separador;
                }
                return $strNombre;
            } else {
                return $elemento->{$campo};
            }
        } elseif (is_array($elemento)) {
            if (is_array($campo)) {
                $strNombre = "";
                $preNombre = "";
                foreach ($campo as $indiceCampo => $nombreCampo) {
                    $strNombre .= $preNombre . $elemento[$nombreCampo];
                    $preNombre = $separador;
                }
                return $strNombre;
            } else {
                return $elemento[$campo];
            }
        } elseif (is_string($elemento)) {
            if (is_array($campo)) {
                $strNombre = "";
                $preNombre = "";
                foreach ($campo as $indiceCampo => $nombreCampo) {
                    $strNombre .= $preNombre . $nombreCampo;
                    $preNombre = $separador;
                }
                return $strNombre;
            } else {
                return $campo;
            }
        }
        return "";
    }

    /**
     * Get the listo of distinct values for a field in a model
     * 
     * @param string $modelo Model name or class
     * @param string|array $campo Attribute or list of attributes
     * @param boolean $trans If try to translate the options or not
     * @return array|boolean The array with the distinct values or false if no model class could be found
     */
    public static function getOpcionesDeCampo($modelo, $campo, $trans = true) {
        if ($modeloClass = \Sirgrimorum\CrudGenerator\CrudGenerator::getModel($modelo, $modelo)) {
            $modelo = strtolower(basename($modeloClass));
            if (is_array($campo)) {
                $tiene = false;
                if ($trans) {
                    foreach ($campo as $opcCampo) {
                        if (\Lang::has('crudgenerator::' . $modelo . ".selects." . $opcCampo)) {
                            $tiene = true;
                        }
                    }
                }
                if ($tiene) {
                    $resultado = [];
                    foreach ($modeloClass::select($campo)->groupBy($campo)->get()->toArray() as $opcion) {
                        $auxResultado = [];
                        foreach ($campo as $opcCampo) {
                            if (\Lang::has('crudgenerator::' . $modelo . ".selects." . $opcCampo)) {
                                $auxResultado[] = trans('crudgenerator::' . $modelo . ".selects." . $opcCampo . "." . $opcion[$opcCampo]);
                            } else {
                                $auxResultado[] = $opcion[$opcCampo];
                            }
                        }
                        $resultado[] = $auxResultado;
                    }
                    return $resultado;
                } else {
                    return $modeloClass::select($campo)->groupBy($campo)->get()->toArray();
                }
            } else {
                if (\Lang::has('crudgenerator::' . $modelo . ".selects." . $campo) && $trans) {
                    $resultado = [];
                    foreach ($modeloClass::select($campo)->distinct()->get()->toArray() as $opcion) {
                        $resultado[] = trans('crudgenerator::' . $modelo . ".selects." . $campo . "." . $opcion);
                    }
                    return $resultado;
                } else {
                    return $modeloClass::select($campo)->distinct()->get()->toArray();
                }
            }
        } else {
            return false;
        }
    }

    /**
     * Evaluate if a string is a json
     * @param string $json_string
     * @return boolean
     */
    private static function isJsonString($json_string) {
        return !preg_match('/[^,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t]/', preg_replace('/"(\\.|[^"\\\\])*"/', '', $json_string));
    }

    /**
     * Get the youtube Id form a url
     * @param string $url
     * @return string
     */
    public static function getYoutubeId($url) {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        $youtube_id = $match[1];
        return $youtube_id;
    }

    /**
     * get the type of a url by its host
     * @param string $url
     * @return string
     */
    public static function urlType($url) {
        $parsed = parse_url($url);
        if (isset($parsed['host'])){
            $hostname = $parsed['host'];
        }else{
            $hostname = $url;
        }
        if (strpos($hostname, 'youtube') > 0) {
            return 'youtube';
        } elseif (strpos($hostname, 'vimeo') > 0) {
            return 'vimeo';
        } else {
            return 'unknown';
        }
    }

}
