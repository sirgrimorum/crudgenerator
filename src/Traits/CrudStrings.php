<?php

namespace Sirgrimorum\CrudGenerator\Traits;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Sirgrimorum\CrudGenerator\CrudGenerator;

trait CrudStrings
{

    /**
     * Get a block of comments from the coments string of a method, using a tag
     * @param string $str The comments string
     * @param string $tag The tag to search for, ej: @return, @param
     * @return string
     */
    public static function checkDocBlock($str, $tag = '')
    {
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
    public static function extract_tags($html, $tag, $selfclosing = null, $return_the_entire_tag = false, $charset = 'ISO-8859-1')
    {

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
    public static function inside_array($array, $key, $needle)
    {
        if (\Illuminate\Support\Arr::has($array, $key)) {
            foreach (\Illuminate\Support\Arr::get($array, $key) as $index => $value) {
                if ($value == $needle) {
                    return $index;
                }
            }
        }
        return false;
    }

    /**
     *  Evaluate a custom function inside the config arrayetc.
     *
     * @param array $array The config array to translate
     * @param string $prefix The prefix that shows where the function must be replaced
     * @param closure $function The function to evaluate
     * @param string $close The close string that shows where the function must be stopped
     * @return array The operated config array
     */
    public static function translateArray($array, $prefix, $function, $close = "__")
    {
        $result = [];
        foreach ($array as $key => $item) {
            if (gettype($item) != "Closure Object") {
                if (is_array($item)) {
                    $result[$key] = \Sirgrimorum\CrudGenerator\CrudGenerator::translateArray($array, $prefix, $function, $close);
                } elseif (is_string($item)) {
                    $item = \Sirgrimorum\CrudGenerator\CrudGenerator::translateString($item, $prefix, $function, $close);
                    $result[$key] = $item;
                } else {
                    $result[$key] = $item;
                }
            } else {
                $result[$key] = $item;
            }
        }
        return $result;
    }

    /**
     * Use the crudgenerator prefixes to change strings in config array to evaluate
     * functions such as route(), trans(), url(), etc.
     *
     * For parameters, use ', ' to separate them inside the prefix and the close.
     *
     * For array, use json notation inside comas
     *
     * @param string $item The string to operate
     * @param string $prefix The prefix for the function
     * @param string $function The name of the function to evaluate
     * @param string $close Optional, the closing string for the prefix, default is '__'
     * @return string The string with the results of the evaluations
     */
    public static function translateString($item, $prefix, $function, $close = "__")
    {
        $result = "";
        if (\Illuminate\Support\Str::contains($item, $prefix)) {
            if (($left = (stripos($item, $prefix))) !== false) {
                while ($left !== false) {
                    if (($right = stripos($item, $close, $left + strlen($prefix))) === false) {
                        $right = strlen($item);
                    }
                    $textPiece = substr($item, $left + strlen($prefix), $right - ($left + strlen($prefix)));
                    $piece = $textPiece;
                    if (\Illuminate\Support\Str::contains($textPiece, "{")) {
                        $auxLeft = (stripos($textPiece, "{"));
                        $auxRight = stripos($textPiece, "}", $left) + 1;
                        $auxJson = substr($textPiece, $auxLeft, $auxRight - $auxLeft);
                        $textPiece = str_replace($auxJson, "*****", $textPiece);
                        $auxJson = str_replace(["'", ", }"], ['"', "}"], $auxJson);
                        $auxArr = explode(",", str_replace([" ,", " ,"], [",", ","], $textPiece));
                        if ($auxIndex = array_search("*****", $auxArr)) {
                            $auxArr[$auxIndex] = json_decode($auxJson, true);
                        } else {
                            $auxArr[] = json_decode($auxJson);
                        }
                        $piece = call_user_func_array($function, $auxArr);
                    } else {
                        $piece = call_user_func($function, $textPiece);
                    }
                    if (is_string($piece)) {
                        if ($right <= strlen($item)) {
                            $item = substr($item, 0, $left) . $piece . substr($item, $right + 2);
                        } else {
                            $item = substr($item, 0, $left) . $piece;
                        }
                        $left = (stripos($item, $prefix));
                    } else {
                        $item = $piece;
                        $left = false;
                    }
                }
            }
            $result = $item;
        } else {
            $result = $item;
        }
        return $result;
    }

    /**
     * Get de prefixes and funcitons array to use when translating configs
     *
     * @return array
     */
    public static function getPrefixesTranslateConfig()
    {
        $prefixes = config("sirgrimorum.crudgenerator.data_prefixes", []);
        if (count($prefixes) <= 0) {
            $prefixes = [
                config("sirgrimorum.crudgenerator.locale_key", "__getLocale__") => 'Illuminate\Support\Facades\App::getLocale',
                config("sirgrimorum.crudgenerator.asset_prefix", '__asset__') => 'asset',
                config("sirgrimorum.crudgenerator.route_prefix", '__route__') => 'route',
                config("sirgrimorum.crudgenerator.url_prefix", '__url__') => 'url',
                config("sirgrimorum.crudgenerator.trans_prefix", '__trans__') => '__',
                config("sirgrimorum.crudgenerator.transarticle_prefix", '__transarticle__') => 'trans_article',
            ];
        }
        return $prefixes;
    }

    /**
     * Get the prefix for a specific function to use when translating configs
     *
     * @param mixed $function The name of the function or callable to look for
     * @param string $default The default value if no prefix is found
     * @return string the prefix for that function
     */
    public static function getPrefixFromFunction($function, $default = "")
    {
        $prefixes = \Sirgrimorum\CrudGenerator\CrudGenerator::getPrefixesTranslateConfig();
        $encontrado = array_search($function, $prefixes);
        if ($encontrado === false && $function == "__") {
            $encontrado = array_search("trans", $prefixes);
        } elseif ($encontrado === false && $function == "trans") {
            $encontrado = array_search("__", $prefixes);
        }
        if ($encontrado === false) {
            return $default;
        }
        return $encontrado;
    }

    /**
     * Use crudgenerator config's data_prefixes to change data from models an evaluate functions in then
     * funtions such as asset(), trans(), url(), etc.
     *
     * For parameters, use ', ' to separate them inside the prefix and the close.
     *
     * For array, use json notation inside comas
     *
     * @param string $item The string to operate
     * @param string $close The close string that shows where the function must be stopped
     * @return string The string with the results of the evaluations
     */
    public static function translateDato($item, $close = "__")
    {
        $prefixes = \Sirgrimorum\CrudGenerator\CrudGenerator::getPrefixesTranslateConfig();
        foreach ($prefixes as $prefix => $function) {
            if (is_string($item)) {
                if ($function instanceof Closure) {
                    $item = \Sirgrimorum\CrudGenerator\CrudGenerator::translateString($item, $prefix, $function);
                } elseif (is_string($function)) {
                    if (function_exists($function)) {
                        $item = \Sirgrimorum\CrudGenerator\CrudGenerator::translateString($item, $prefix, $function);
                    }
                }
            } else {
                $item = \Sirgrimorum\CrudGenerator\CrudGenerator::translateArray($item, $prefix, $function, $close);
            }
        }
        return $item;
    }

    /**
     * Get if a Model has a relation
     * @param string $model
     * @param string $key the attribute name for the relation
     * @return boolean Wheter the key attribute is a relation or not
     */
    public static function hasRelation($model, $key)
    {
        return \Sirgrimorum\CrudGenerator\CrudGenerator::isFunctionOfType($model, $key, "Illuminate\Database\Eloquent\Relations\Relation");
    }

    /**
     * Get if a Model method returns certain type of value
     * @param Object $model
     * @param string $key the attribute name for the relation
     * @param string $tipo Optional the type of object returned to compare
     * @return boolean Wheter the key attribute is a relation or not
     */
    public static function isFunctionOfType($model, $key, $tipo = "Illuminate\Database\Eloquent\Collection")
    {
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
    public static function isFunction($model, $key)
    {
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
    public static function callFunction($model, $key, $args, $numArgs = false, $valorRelleno = false)
    {
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
    public static function getModel($modelo, $probable = '')
    {
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
                                if (strtolower($modelo) == "paymentpass") {
                                    $modeloClass = "Sirgrimorum\\PaymentPass\\Models\\PaymentPass";
                                    if (!class_exists($modeloClass)) {
                                        //return 'There is no Model class for the model name "' . $modelo . '" ind the \Sirgrimorum\CrudGenerator\CrudGenerator::getConfig(String $modelo)';
                                        return false;
                                    }
                                } else {
                                    return false;
                                }
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
    public static function splitQueryNames($query)
    {
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
     * @param array|string $options The probable type name or array of options
     * @return boolean True if the $name is found in the probable names list for $option type
     */
    public static function getTypeByName($name, $options)
    {
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
    public static function getNameAttribute($model)
    {
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
    public static function getNombreDeLista($elemento, $campo, $separador = "-")
    {
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
    public static function getOpcionesDeCampo($modelo, $campo, $trans = true)
    {
        if ($modeloClass = \Sirgrimorum\CrudGenerator\CrudGenerator::getModel($modelo, $modelo)) {
            $modelo = strtolower(basename($modeloClass));
            if (is_array($campo)) {
                $tiene = false;
                if ($trans) {
                    foreach ($campo as $opcCampo) {
                        if (Lang::has('crudgenerator::' . $modelo . ".selects." . $opcCampo)) {
                            $tiene = true;
                        }
                    }
                }
                if ($tiene) {
                    $resultado = [];
                    foreach ($modeloClass::select($campo)->groupBy($campo)->get()->toArray() as $opcion) {
                        $auxResultado = [];
                        foreach ($campo as $opcCampo) {
                            if (Lang::has('crudgenerator::' . $modelo . ".selects." . $opcCampo)) {
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
                if (Lang::has('crudgenerator::' . $modelo . ".selects." . $campo) && $trans) {
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
    public static function isJsonString($json_string)
    {
        if (strpos($json_string, "{") === false) {
            return false;
        }
        return !preg_match('/[^,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t]/', preg_replace('/"(\\.|[^"\\\\])*"/', '', $json_string));
    }

    /**
     * Get the youtube Id form a url
     * @param string $url
     * @return string
     */
    public static function getYoutubeId($url)
    {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        $youtube_id = $match[1];
        return $youtube_id;
    }

    /**
     * get the type of a url by its host
     * @param string $url
     * @return string
     */
    public static function urlType($url)
    {
        $parsed = parse_url($url);
        if (isset($parsed['host'])) {
            $hostname = $parsed['host'];
        } else {
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

    /**
     * Get and set the current locale (for use in route definition).
     * @param string $locale Optional current locale, if empty, will try to get it form the url
     * @return string
     */
    public static function setLocale($locale = null)
    {
        $app = app();
        $currentLocale = $app->config->get('app.locale');
        if (empty($locale) || !is_string($locale)) {
            // If the locale has not been passed through the function
            // it tries to get it from the first segment of the url
            $locale = $app->request->segment(1);
        }
        if (!in_array($locale, $app->config->get('sirgrimorum.crudgenerator.list_locales'))) {
            $locale = $currentLocale;
        }

        //if (!empty($this->supportedLocales[$locale])) {
        $currentLocale = $locale;
        //}

        $app->setLocale($currentLocale);

        return $currentLocale;
    }

    /**
     * Translate a route to a model (for use in route definition)
     * Will search it in resources/lang/vendor/crudgenerator/{locale}/model.labels.{$modelo} or resources/lang/{locale}/model.{$modelo}
     * @param string $modelo The model name (plural or singular)
     * @return string
     */
    public static function transRouteModel($modelo)
    {
        $app = app();
        //$modeloClass = CrudGenerator::getModel($modelo, substr($modelo,0, strlen($modelo)-1));
        $base = "";
        $currentLocale = $app->getLocale();
        $defaultLocale = $app->config->get('app.locale');
        $modeloClass = $modelo;
        $crud = true;
        $transroute = $app->translator->get('routes.routes.' . $modelo, [], $currentLocale);
        if (stripos($transroute, ".") !== false) {
            if (!file_exists(resource_path("lang/vendor/crudgenerator/" . $currentLocale . "/" . $modeloClass . ".php"))) {
                if (!file_exists(resource_path("lang/vendor/crudgenerator/" . $defaultLocale . "/" . $modeloClass . ".php"))) {
                    //$modeloClass = substr($modelo, 0, strlen($modelo) - 1);
                    $modeloClass = \Illuminate\Support\Str::singular($modelo);
                    if (!file_exists(resource_path("lang/vendor/crudgenerator/" . $currentLocale . "/" . $modeloClass . ".php"))) {
                        if (!file_exists(resource_path("lang/vendor/crudgenerator/" . $defaultLocale . "/" . $modeloClass . ".php"))) {
                            $modeloClass = $modelo;
                            $crud = false;
                            if (!file_exists(resource_path("lang/" . $currentLocale . "/" . $modeloClass . ".php"))) {
                                if (!file_exists(resource_path("lang/" . $defaultLocale . "/" . $modeloClass . ".php"))) {
                                    //$modeloClass = substr($modelo, 0, strlen($modelo) - 1);
                                    $modeloClass = \Illuminate\Support\Str::singular($modelo);
                                    if (!file_exists(resource_path("lang/" . $currentLocale . "/" . $modeloClass . ".php"))) {
                                        if (!file_exists(resource_path("lang/" . $defaultLocale . "/" . $modeloClass . ".php"))) {
                                            $modeloClass = false;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($modeloClass !== false) {
                //$modeloClass = strtolower(basename($modeloClass));
                if ($crud) {
                    $modeloClass = 'crudgenerator::' . $modeloClass . '.labels';
                }
                $base = $app->translator->get($modeloClass . '.' . $modelo, [], $currentLocale);
                if (stripos($base, ".") > 0) {
                    $base = $modelo; //. $base;
                } else {
                    $base = strtolower($base);
                }
            }
        } else {
            $base = $transroute;
        }
        if ($base == "") {
            $base = $modelo;
        }
        return $base;
    }

    /**
     * Translate a route (for use in routes definition)
     * Will look for the translation in resources/lang/vendor/crudgenerator/{locale}/admin.routes.{$route}
     * @param string $route The route, it could be (modelo.action) or only (action)
     * @return string
     */
    public static function transRoute($route)
    {
        $app = app();
        $locale = \Sirgrimorum\CrudGenerator\CrudGenerator::setLocale();
        //$locale = $app->getLocale();
        $routes = explode(".", $route);
        $base = "";
        if (count($routes) > 1) {
            $base = \Sirgrimorum\CrudGenerator\CrudGenerator::transRouteModel($routes[0]);
            if ($base != "") {
                $base .= ".";
            }
            $route = $routes[1];
        }
        $transroute = $app->translator->get('routes.actions.' . $route, [], $locale);
        if (stripos($transroute, ".") !== false) {
            $transroute = $app->translator->get('crudgenerator::admin.routes.' . $route, [], $locale);
            if (stripos($transroute, ".") !== false) {
                $transroute = $route;
            }
        }
        //$route = $app->translator->get('crudgenerator::admin.routes.' . $route);
        return $base . $transroute;
    }

    /**
     * Translate a route (for use in routes definition)
     * @param string $route The route
     * @return string
     */
    public static function transRouteExternal($route)
    {
        $app = app();
        $locale = \Sirgrimorum\CrudGenerator\CrudGenerator::setLocale();
        //$locale = $app->getLocale();
        $transroute = $app->translator->get($route, [], $locale);
        return $transroute;
    }

    /**
     *  Change the locale of a url
     * @param string $locale New locale
     * @param string $url Optional the url to change, if empty, will change the current url
     * @return string
     */
    public static function changeLocale($locale, $url = null)
    {
        $app = app();
        //$modeloClass = CrudGenerator::getModel($modelo, substr($modelo,0, strlen($modelo)-1));
        $base = "";
        $currentLocale = $app->getLocale();
        $defaultLocale = $app->config->get('app.locale');
        if ($locale === null) {
            $locale = $currentLocale;
        }
        if (!in_array($locale, $app->config->get('sirgrimorum.crudgenerator.list_locales'))) {
            $locale = $currentLocale;
        }
        if (empty($url)) {
            $url = $app->request->fullUrl();
            $urlLocale = $app->request->segment(1);
            if (in_array($urlLocale, $app->config->get('sirgrimorum.crudgenerator.list_locales'))) {
                $url = str_replace("/" . $urlLocale . "/", "/" . $locale . "/", $url);
            }
        } else {
            if (stripos($url, "/" . $currentLocale . "/") > 0) {
                $url = str_replace("/" . $currentLocale . "/", "/" . $locale . "/", $url);
            } elseif (substr($url, strlen($url) - (strlen($currentLocale) + 1)) == "/" . $currentLocale) {
                $url = substr($url, 0, strlen($url) - (strlen($currentLocale))) . $currentLocale;
            } elseif (stripos($url, "http://") === false && stripos($url, "https://") === false) {
                $url = str_replace("//", "/", $locale . "/" . $url);
            }
        }


        return $url;
    }

    /**
     * Da el rgb de un color RGB a partir de un factor y un color por wavelength
     * @param real $color
     * @param real $factor
     * @return int
     */
    public static function adjustColor($color, $factor)
    {
        if ($color == 0.0) {
            return 0;
        } else {
            return round(255.0 * pow(($color * $factor), 0.80));
        }
    }

    /**
     * Define el rgb de un wavelength
     * @param int $wavelength
     * @param array valores para r, g y b
     * @return array RGB
     */
    public static function setColors($wavelength, $rgb)
    {
        $red = $rgb["r"];
        $green = $rgb["g"];
        $blue = $rgb["b"];
        if ($wavelength >= 380 && $wavelength <= 439) {
            $red = - ($wavelength - 440.0) / (440.0 - 380.0);
            $green = 0.0;
            $blue = 1.0;
        } elseif ($wavelength >= 440 && $wavelength <= 489) {
            $red = 0.0;
            $green = ($wavelength - 440.0) / (490.0 - 440.0);
            $blue = 1.0;
        } elseif ($wavelength >= 490 && $wavelength <= 509) {
            $red = 0.0;
            $green = 1.0;
            $blue = - ($wavelength - 510.0) / (510.0 - 490.0);
        } elseif ($wavelength >= 510 && $wavelength <= 579) {
            $red = ($wavelength - 510.0) / (580.0 - 510.0);
            $green = 1.0;
            $blue = 0.0;
        } elseif ($wavelength >= 580 && $wavelength <= 644) {
            $red = 1.0;
            $green = - ($wavelength - 645.0) / (645.0 - 580.0);
            $blue = 0.0;
        } elseif ($wavelength >= 645 && $wavelength <= 780) {
            $red = 1.0;
            $green = 0.0;
            $blue = 0.0;
        } else {
            $red = 0.0;
            $green = 0.0;
            $blue = 0.0;
        }
        $rgb["r"] = $red;
        $rgb["g"] = $green;
        $rgb["b"] = $blue;
        return $rgb;
    }

    /**
     * Define el factor de multiplicación de un color por wavelength
     * @param int $wavelength
     * @param array $rgb arreglo con el factor f
     * @return array con el factor f
     */
    public static function setFactor($wavelength, $rgb)
    {
        $factor = $rgb["f"];
        if ($wavelength >= 380 && $wavelength <= 419) {
            $factor = 0.3 + 0.7 * ($wavelength - 380.0) / (420.0 - 380.0);
        } elseif ($wavelength >= 420 && $wavelength <= 700) {
            $factor = 1.0;
        } elseif ($wavelength >= 701 && $wavelength <= 780) {
            $factor = 0.3 + 0.7 * (780.0 - $wavelength) / (780.0 - 700.0);
        } else {
            $factor = 0.0;
        }
        $rgb["f"] = $factor;
        return $rgb;
    }

    /**
     * Genera un array con un número específico de colores repartido uniformemente entre toda la gama
     * @param int $numSteps Número de colores a devolver
     * @return array Array de colores en rgb hexa string ej: #ff33b3
     */
    public static function arrColors($numSteps)
    {
        $colors = [];
        $rgb = [
            "r" => 0,
            "g" => 0,
            "b" => 0,
            "f" => 0
        ];

        for ($i = 0; $i < $numSteps; $i++) {
            $lambda = round(380 + 400 * ($i / ($numSteps - 1)));
            $rgb = \Sirgrimorum\CrudGenerator\CrudGenerator::setColors($lambda, $rgb);
            $rgb = \Sirgrimorum\CrudGenerator\CrudGenerator::setFactor($lambda, $rgb);
            $rgb["r"] = \Sirgrimorum\CrudGenerator\CrudGenerator::adjustColor($rgb["r"], $rgb["f"]);
            $rgb["g"] = \Sirgrimorum\CrudGenerator\CrudGenerator::adjustColor($rgb["g"], $rgb["f"]);
            $rgb["b"] = \Sirgrimorum\CrudGenerator\CrudGenerator::adjustColor($rgb["b"], $rgb["f"]);
            $redHex = dechex($rgb["r"]);
            $redHex = (strlen($redHex) < 2) ? "0" . $redHex : $redHex;
            $greenHex = dechex($rgb["g"]);
            $greenHex = (strlen($greenHex) < 2) ? "0" . $greenHex : $greenHex;
            $blueHex = dechex($rgb["b"]);
            $blueHex = (strlen($blueHex) < 2) ? "0" . $blueHex : $blueHex;
            $bgcolor = "#" . $redHex . $greenHex . $blueHex;
            array_push($colors, $bgcolor);
        }
        return $colors;
    }

    /**
     * Get the dimension of an array
     * @param array $array
     * @return int
     */
    public static function countdim($array)
    {
        if (is_array(reset($array))) {
            $return = \Sirgrimorum\CrudGenerator\CrudGenerator::countdim(reset($array)) + 1;
        } else {
            $return = 1;
        }

        return $return;
    }

    /**
     * Builds de standard html tag for a file of certain type
     *
     * @param string $tipoFile The file tipe string ej: 'image'
     * @param string $urlFile The url source for the file
     * @param string $nameFile The name of the file
     * @param string $tipoMime The mime type string of the file
     * @return string The html tag builded
     */
    public static function getHtmlParaFile($tipoFile, $urlFile, $nameFile, $tipoMime)
    {
        switch ($tipoFile) {
            case 'image':
                $fileHtml = "<img class='img-fluid' src='{$urlFile}' alt='{$nameFile}' />";
                break;
            case 'video':
                $fileHtml = "<video class='img-fluid' controls><source src='{$urlFile}' type='video/mp4'>Your browser does not support the video tag.</video>";
                break;
            case 'audio':
                $fileHtml = "<audio class='mw-100' controls preload='auto'><source src='{$urlFile}' type='$tipoMime'></audio>";
                break;
            case 'pdf':
                $fileHtml = "<iframe class='img-fluid mh-100' src='{$urlFile}' ></iframe>";
                break;
            default:
                switch ($tipoFile) {
                    case 'text':
                    case 'office':
                    case 'compressed':
                        $faTipo = CrudGenerator::getIcon("{$tipoFile}_file");
                        break;
                    default:
                        $faTipo = CrudGenerator::getIcon("file");
                        break;
                }
                $fileHtml = "<a class='text-secondary' href='{$urlFile}' target='_blank'><i class='$faTipo' aria-hidden='true'></i></a>";
                break;
        }
        return $fileHtml;
    }

    /**
     * Get the class for an icon i tag of a given type
     * Icons are defined in crudgenerators config file under icons
     * Defaults uses font-awesome 4.7
     * 
     * @param string $tipo The type of the icon
     * @param bool Optional $conTag If should return the i tag or only the icon class, default false
     * @param string Optional $classAdicional the aditional classes to includ in the i tag
     * @return string The class for that type
     */
    public static function getIcon($tipo, $conTag = false, $classAdicional = "")
    {
        $tipoConfig = $tipo;
        switch ($tipo) {
            case 'empty':
                $default = 'fa fa-lg';
                break;
            case 'minus':
                $default = 'fa fa-minus';
                break;
            case 'plus':
                $default = 'fa fa-plus';
                break;
            case 'info':
                $default = 'fa fa-info-circle fa-lg';
                break;
            case 'confirm':
                $default = 'fa fa-question-circle fa-lg';
                break;
            case 'success':
                $default = 'fa fa-check fa-lg';
                break;
            case 'error':
                $default = 'fa fa-exclamation-triangle fa-lg';
                break;
            case 'text':
            case 'text_file':
                $tipoConfig = 'text_file';
                $default = 'fa fa-file-text-o fa-lg';
                break;
            case 'office':
            case 'office_file':
                $tipoConfig = 'office_file';
                $default = 'fa fa-file-word-o fa-lg';
                break;
            case 'compressed':
            case 'compressed_file':
                $tipoConfig = 'compressed_file';
                $default = 'fa fa-file-archive-o fa-lg';
                break;
            case 'other':
            case 'file':
                $tipoConfig = 'file';
                $default = 'fa fa-file-o fa-lg';
                break;
            case 'url':
                $default = 'fa fa-link fa-lg';
                break;
            case 'video':
                $default = 'fa fa-film fa-lg';
                break;
            case 'audio':
                $default = 'fa fa-file-audio-o fa-lg';
                break;
            case 'pdf':
                $default = 'fa fa-file-pdf-o fa-lg';
                break;
            default:
                $default = "fa fa-$tipo fa-lg";
                break;
        }
        $icono =  Arr::get(config('sirgrimorum.crudgenerator.icons'), $tipoConfig, $default);
        if ($conTag){
            return "<i class='$icono $classAdicional' aria-hidden='true'></i>";
        }
        return $icono;
    }

    /**
     * Truncate a long text without truncating words.
     *
     * @param string $text The string to truncate
     * @param int $chars The aproximate max number of characters, default 200
     * @return string The truncated text
     */
    public static function truncateText($text, $chars = 200)
    {
        if (strlen($text) <= $chars) {
            return $text;
        }
        $text = $text . " ";
        $text = substr($text, 0, $chars);
        $text = substr($text, 0, strrpos($text, ' '));
        $text = $text . "...";
        return $text;
    }

    /**
     * Add a script loader to a blade
     * @param string $src The path to the script, leave empty to inline script
     * @param bool $defer Optional if the script should be defered, default false
     * @param string $inner Optional the innerHtml script, could be the id of a text/html script block or a large text
     * @param bool $innerIsBlock Optional if the $inner parameter is an id of a block or not. Default false
     * @return string The script call for the scriptLoader function
     */
    public static function addScriptLoaderHtml($src, $defer = false, $inner = "", $innerIsBlock = false)
    {
        $name = config("sirgrimorum.crudgenerator.scriptLoader_name", "scriptLoader");
        $deferStr = ($defer) ? "true" : "false";
        $html = "";
        if ($inner != "" && !$innerIsBlock) {
            $id = \Illuminate\Support\Str::random(8) . "_typeahead_block";
            $html = "<script id=\"$id\" type=\"text/html\">$inner</script>";
            $inner = $id;
        }
        $html .= "<script>$name('$src',$deferStr,\"$inner\");</script>";
        return $html;
    }

    /**
     * Add a link tag loader to a blade
     * @param string $href The path to the file
     * @param string $tyrelpe Optional the rel attribute of the link tag, default is "stylesheet"
     * @param string $type Optional the type attribute of the link tag, default is "text/css"
     * @return string The script call for the scriptLoader function
     */
    public static function addLinkTagLoaderHtml($href, $rel = "stylesheet", $type = "text/css")
    {
        $name = config("sirgrimorum.crudgenerator.linkTagLoader_name", "linkTagLoader");
        $html = "";
        $html .= "<script>$name('$href','$rel','$type');</script>";
        return $html;
    }
}
