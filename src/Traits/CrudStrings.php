<?php

namespace Sirgrimorum\CrudGenerator\Traits;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
        if (Arr::has($array, $key)) {
            foreach (Arr::get($array, $key) as $index => $value) {
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
                    $result[$key] = CrudGenerator::translateArray($array, $prefix, $function, $close);
                } elseif (is_string($item)) {
                    if (strpos($item, $prefix) !== false) {
                        $item = CrudGenerator::translateString($item, $prefix, $function, $close);
                    }
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
        if (isset($item)) {
            $result = "";
            if (Str::contains($item, $prefix)) {
                if (($left = (stripos($item, $prefix))) !== false) {
                    while ($left !== false) {
                        if ((CrudGenerator::isFunction($function, null)) === 0) {
                            $right = $left + strlen($prefix);
                            if (substr($item, $left, strlen($prefix) + strlen($close)) != $prefix . $close) {
                                $right -= strlen($close);
                            }
                            if ($right > strlen($item)) {
                                $right = strlen($item);
                            }
                        } else {
                            if (($right = stripos($item, $close, $left + strlen($prefix))) === false) {
                                $right = strlen($item);
                            }
                        }
                        $textPiece = substr($item, $left + strlen($prefix), $right - ($left + strlen($prefix)));
                        $piece = $textPiece;
                        if (Str::contains($textPiece, "{")) {
                            $auxLeft = (stripos($textPiece, "{"));
                            $auxRight = stripos($textPiece, "}", $auxLeft) + 1;
                            $auxJson = substr($textPiece, $auxLeft, $auxRight - $auxLeft);
                            $textPiece = str_replace($auxJson, "*****", $textPiece);
                            $auxJson = str_replace(["'", ", }"], ['"', "}"], $auxJson);
                            $auxArr = explode(",", str_replace([" ,", ", "], [",", ","], $textPiece));
                            if ($auxIndex = array_search("*****", $auxArr)) {
                                $auxArr[$auxIndex] = json_decode($auxJson, true);
                            } else {
                                $auxArr[] = json_decode($auxJson, true);
                            }
                            $piece = call_user_func_array($function, $auxArr);
                        } else {
                            $piece = call_user_func($function, $textPiece);
                        }
                        if (is_string($piece) || $piece == null) {
                            if ($right <= strlen($item)) {
                                $item = substr($item, 0, $left) . $piece . substr($item, $right + strlen($close));
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
        return $item;
    }

    /**
     * Get the data to show for a specific action using the "[action]_show" option of the 
     * column configuration array
     * 
     * @param array $datos The data values array from the $model->get("campo", false) function
     * @param string $action The name of the action (mainly show and list)
     * @param array $detalles The column configuration array
     * @param object|array $registro The registry of the data
     * @param bool $justString If only return strings (the arrays print_r between <pre></pre>)
     * @return string|array The data to show after processing or the $data array
     */
    public static function getDatoToShow($datos, $action, array $detalles, $registro, $justString = true)
    {
        if (isset($detalles["{$action}_data"])) {
            if (is_callable($detalles["{$action}_data"])) {
                $return = $detalles["{$action}_data"]($datos, $registro);
                if (is_array($return) && $justString) {
                    return "<pre>" . print_r($return, true) . "</pre>";
                }
                return CrudGenerator::getNombreDeLista($registro, $return);
            } elseif ($datos !== null && ($return = CrudGenerator::getNombreDeLista($registro, $detalles["{$action}_data"])) !== null) {
                if (is_array($return) && $justString) {
                    return "<pre>" . print_r($return, true) . "</pre>";
                }
                return $return;
            }
        }
        if ($datos !== null && is_array($datos)) {
            if ($action == "list" && isset($datos['html_cell'])) {
                return $datos['html_cell'];
            } elseif (isset($datos['html_show'])) {
                return $datos['html_show'];
            } elseif (isset($datos['html'])) {
                return $datos['html'];
            } elseif (isset($datos['value'])) {
                return (string) $datos['value'];
            } elseif ($justString) {
                return "<pre>" . print_r($datos, true) . "</pre>";
            }
        }
        return $datos;
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
        $prefixes = CrudGenerator::getPrefixesTranslateConfig();
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
     * Use crudgenerator config's data_prefixes to change data from models an evaluate functions in them
     * funtions such as asset(), trans(), url(), etc.
     *
     * For parameters, use ', ' to separate them inside the prefix and the close.
     *
     * For array, use json notation inside comas
     *
     * @param string $item The string to operate
     * @param object $registro Optional The model to be used as base to change field names using :*field_name: inside the string
     * @param array $config Optional The config array for the model, used to replace :modelId and :modelName inside the item with the $registro values
     * @param string $close The close string that shows where the function must be stopped
     * @return string The string with the results of the evaluations
     */
    public static function translateDato($item, $registro = null, $config = null, $close = "__")
    {
        if (isset($item)) {
            if ($registro != null) {
                $item = CrudGenerator::getNombreDeLista($registro, $item);
            }
            $prefixes = CrudGenerator::getPrefixesTranslateConfig();
            foreach ($prefixes as $prefix => $preFunction) {
                $sigue = true;
                if (is_array($preFunction)) {
                    $function = $preFunction[1];
                    $sigue = $preFunction[0];
                    if ($preFunction[0]) {
                        $sigue = $registro != null;
                    }
                } else {
                    $function = $preFunction;
                }
                if ($sigue) {
                    if (is_string($item)) { //&& strlen($item) <= 255) {
                        if (strpos($item, $prefix) !== false) {
                            if ($function instanceof Closure) {
                                $item = CrudGenerator::translateString($item, $prefix, $function);
                            } elseif (is_string($function)) {
                                if (function_exists($function)) {
                                    $item = CrudGenerator::translateString($item, $prefix, $function);
                                }
                            }
                        }
                    } elseif (is_array($item)) {
                        $item = CrudGenerator::translateArray($item, $prefix, $function, $close);
                    }
                }
            }
            if ($config != null && is_array($config) && $registro != null && is_object($registro) && is_string($item) && strlen($item) <= 255) {
                if (isset($config['id']) && isset($registro->{$config['id']}) && (strpos($item, ":modelId") !== false || strpos($item, urlencode(":modelId")) !== false)) {
                    $item = str_replace([":modelId", urlencode(":modelId")], $registro->{$config['id']}, $item);
                }
                if (isset($config['nombre']) && (strpos($item, ":modelName") !== false || strpos($item, urlencode(":modelName")) !== false)) {
                    $nombreValor = CrudGenerator::getNombreDeLista($registro, $config['nombre'], "-", ":modelName");
                    $item = str_replace([":modelName", urlencode(":modelName")], $nombreValor, $item);
                }
            }
        }
        return $item;
    }

    /**
     * Get if a Model has a relation
     * @param Object|string $model
     * @param string $key the attribute name for the relation
     * @return boolean Wheter the key attribute is a relation or not
     */
    public static function hasRelation($model, $key)
    {
        if (is_string($model)) {
            $model = (new $model());
        }
        if (is_object($model)) {
            return CrudGenerator::isFunctionOfType($model, $key, "Illuminate\Database\Eloquent\Relations\Relation");
        }
        return false;
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
        if (is_object($model)) {
            if (method_exists($model, $key)) {
                return is_a($model->$key(), $tipo);
            }
        }
        return false;
    }

    /**
     * Get if a Model has a function, 
     * could be use to get the number of parameters of a function if $key is null
     * @param string $model
     * @param string $key the attribute name for the function
     * @return boolean|int if key is a callable function, return the number of arguments, if not, return false (use strict comparision)
     */
    public static function isFunction($model, $key = null)
    {
        $nombreLlamar = "";
        if ($key == null) {
            if (is_callable($model, true, $nombreLlamar)) {
                $refFunc = new \ReflectionFunction($model);
                return $refFunc->getNumberOfParameters();
            }
        } elseif (is_callable([$model, $key], true, $nombreLlamar) && method_exists($model, $key)) {
            $refClass = new \ReflectionClass($model);
            $refFunc = $refClass->getMethod($key);
            //$refFunc = new \ReflectionFunction($nombreLlamar);
            return $refFunc->getNumberOfParameters();
        }
        return false;
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
            $numArgs = CrudGenerator::isFunction($model, $key);
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
        $modeloClass = $modelo;
        if (!Str::startsWith(strtolower($modeloClass), 'sirgrimorum') || (Str::startsWith(strtolower($modeloClass), 'sirgrimorum') && !class_exists($modeloClass))) {
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
                            if (strtolower($modelo) == "catchederror" || stripos($modelo, "catchederror") !== false) {
                                $modeloClass = "Sirgrimorum\\CrudGenerator\\Models\\Catchederror";
                                if (!class_exists($modeloClass)) {
                                    $modeloClass = "Sirgrimorum\\CrudGenerator\\Models\\CatchedError";
                                    if (!class_exists($modeloClass)) {
                                        return false;
                                    }
                                }
                            } elseif (strtolower($modelo) == "article" || stripos($modelo, "article") !== false) {
                                $modeloClass = "Sirgrimorum\\TransArticles\\Models\\Article";
                                if (!class_exists($modeloClass)) {
                                    return false;
                                }
                            } elseif (strtolower($modelo) == "paymentpass" || stripos($modelo, "paymentpass") !== false) {
                                $modeloClass = "Sirgrimorum\\PaymentPass\\Models\\PaymentPass";
                                if (!class_exists($modeloClass)) {
                                    return false;
                                }
                            } elseif (strtolower($modelo) == "pagina" || stripos($modelo, "pagina") !== false) {
                                $modeloClass = "Sirgrimorum\\Pages\\Models\\Pagina";
                                if (!class_exists($modeloClass)) {
                                    return false;
                                }
                            } elseif (strtolower($modelo) == "section" || stripos($modelo, "section") !== false) {
                                $modeloClass = "Sirgrimorum\\Pages\\Models\\Section";
                                if (!class_exists($modeloClass)) {
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
     * Take a data array or the request with search directives for a given model and normalizes it to work with CrudGenerator filters.
     * Accept datatables search querys
     * 
     * @param array $config Configuration array for the Model
     * @param array $datos Optional the data. if empty, it will get the current request data.
     * @param boolean|string $orOperation Optional boolean or the key of the or value in $datos. if True: use or operation (just one query must be true), false will use and operation (all the querys must be true).
     * @param string $queryStr Optional the key of the query in $datos
     * @param string $attriStr Optional the key of the attributes in $datos
     * @param string $aByAStr Optional the key of the value indicating if the $query and $attribute must be evaluated one by one (ej: $query[0] vs $attribute[0] AND $query[1] vs $attribute[1], ...)
     * @param string $orderStr Optional the key of the order field(s) in $datos
     * @return array the normalize array of search directives
     */
    public static function normalizeDataForSearch($config, $datos = [], $orOperation = "_or", $queryStr = "_q", $attriStr = "_a", $aByAStr = "_aByA", $orderStr = '_order')
    {
        if (count($datos) == 0) {
            $request = request();
            if ($request->has($queryStr)) {
                // Is a crudgenerator query
                $datos = $request->all();
            } else {
                $columnas = CrudGenerator::getCamposNames($config);
                $datos = $request->only([$orOperation]);
                $atributos = [];
                $atributosParaConfig = [];
                $querys = [];
                $ordenes = [];
                if ($request->has("columns")) {
                    //is a datatables query
                    $datos[$orOperation] = false;
                    foreach ($request->input("columns") as $key => $columna) {
                        if ($request->input("columns.$key.search.value") != "") {
                            if (in_array($request->input("columns.$key.data"), $columnas)) {
                                $attriR = $request->input("columns.$key.data");
                                $queryR = $request->input("columns.$key.search.value");
                                $attriFinal = $attriR;
                                if (isset($config['campos'][$attriR])) {
                                    if ($config['campos'][$attriR]['tipo'] == "relationship" && CrudGenerator::hasRelation($config['modelo'], $attriR)) {
                                        $attriFinal = (new $config['modelo']())->{$attriR}()->getForeignKeyName();
                                    }
                                }
                                $atributos[] = $attriFinal;
                                $atributosParaConfig[] = $attriR;
                                $querys[] = $queryR;
                            }
                        }
                    }
                    if (!empty($request->input('search.value'))) {
                        $search = $request->input('search.value');
                        foreach ($columnas as $attriR) {
                            $attriFinal = $attriR;
                            if (isset($config['campos'][$attriR])) {
                                $querys[] = "*%{$search}";
                                $atributos[] = $attriFinal;
                                $atributosParaConfig[] = $attriFinal;
                            }
                        }
                    }
                    if (!empty($request->input('order'))) {
                        foreach ($request->input('order') as $key => $columnaData) {
                            $columna = $request->input("order.$key.column");
                            $dir = $request->input("order.$key.dir", "asc");
                            if ($request->input("columns.$columna.data", "") != "" && $dir != "") {
                                $ordenes[$request->input("columns.$columna.data")] = $dir;
                            }
                        }
                    }
                    if (is_array($request->get('_preFiltros', ''))) {
                        $arrayFiltros = $request->get('_preFiltros', []);
                    } else {
                        $camposConPrefiltro = CrudGenerator::getCamposNames(CrudGenerator::justWithValor($config, 'datatables', 'prefiltro'));
                        $arrayFiltros = $request->only($camposConPrefiltro);
                    }
                    foreach ($arrayFiltros as $attriR => $queryR) {
                        if (in_array($attriR, $columnas)) {
                            $attriFinal = $attriR;
                            if (isset($config['campos'][$attriR])) {
                                if ($config['campos'][$attriR]['tipo'] == "relationship" && CrudGenerator::hasRelation($config['modelo'], $attriR)) {
                                    $attriFinal = (new $config['modelo']())->{$attriR}()->getForeignKeyName();
                                }
                            }
                            $atributos[] = $attriFinal;
                            $atributosParaConfig[] = $attriR;
                            $querys[] = $queryR;
                        }
                    }
                } else {
                    //are from a regular form
                    if (is_array($request->get('_preFiltros', ''))) {
                        $arrayFiltros = $request->get('_preFiltros', []);
                    } else {
                        $arrayFiltros = $request->except(['_token', '_return', '_tablaId', $orOperation]);
                    }
                    foreach ($arrayFiltros as $attriR => $queryR) {
                        if ($queryR != "" && ((is_array($queryR) && count($queryR) > 0) || !is_array($queryR))) {
                            if (in_array($attriR, $columnas)) {
                                $attriFinal = $attriR;
                                if (isset($config['campos'][$attriR])) {
                                    if ($config['campos'][$attriR]['tipo'] == "relationship" && CrudGenerator::hasRelation($config['modelo'], $attriR)) {
                                        $attriFinal = (new $config['modelo']())->{$attriR}()->getForeignKeyName();
                                    }
                                }
                                $atributos[] = $attriFinal;
                                $atributosParaConfig[] = $attriR;
                                $querys[] = $queryR;
                            }
                        }
                    }
                    if (!empty($request->input('order'))) {
                        foreach ($request->input('order') as $key => $columnaData) {
                            $columna = $request->input("order.$key.column");
                            $dir = $request->input("order.$key.dir", "asc");
                            if ($request->input("columns.$columna.data", "") != "" && $dir != "") {
                                $ordenes[$request->input("columns.$columna.data")] = $dir;
                            }
                        }
                    }
                }
                $datos[$attriStr] = json_encode($atributos);
                $datos["{$attriStr}__C"] = json_encode($atributosParaConfig);
                $datos[$queryStr] = json_encode($querys);
                $datos[$orderStr] = json_encode($ordenes);
                $datos[$aByAStr] = true;
            }
        }
        //echo "<p>datos</p><pre>" . print_r($datos, true) . "</pre>";
        return $datos;
    }

    /**
     * Know if a field name is of certain type by comparing it with a list of comonly used field names
     * of the same type.
     *
     * @param string $name The field name
     * @param array|string $options The probable type name
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
     * @param string $default Optional, the value to be used when nothing is found, if null will leave $campo
     * @return string
     */
    public static function getNombreDeLista($elemento, $campo, $separador = "-", $default = null)
    {
        if (isset($campo)) {
            if (is_object($elemento) || is_array($elemento)) {
                if (is_array($campo)) {
                    //no es asociativo ni multi
                    if (count(array_filter(array_keys($campo), 'is_string')) == 0 && count(array_filter($campo, 'is_array')) == 0) {
                        $strNombre = "";
                        $preNombre = "";
                        foreach ($campo as $indiceCampo => $nombreCampo) {
                            if (($dato = data_get($elemento, $nombreCampo, null)) !== null) {
                                if (is_string($dato)) {
                                    $stringDato = $dato;
                                } elseif (is_array($dato) || is_object($dato)) {
                                    $stringDato = json_encode($dato);
                                } else {
                                    $stringDato = (string) $dato;
                                }
                                $strNombre .= $preNombre . $stringDato;
                                $preNombre = $separador;
                            }
                        }
                        return $strNombre;
                    } else {
                        return $campo;
                    }
                } elseif (is_callable($campo)) {
                    return $campo($elemento);
                } elseif (is_string($campo) && strlen($campo) <= 255) {
                    return CrudGenerator::replaceCamposEnString($elemento, $campo, $default);
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
                } elseif (is_callable($campo)) {
                    return $campo($elemento);
                }
            }
            return $default ?? $campo;
        }
        return $default ?? $campo;
    }

    /**
     * Replaces field values form an element in a string (almost lake trans())
     * 
     * @param object|array $elemento The element with the fields
     * @param string $plantilla The string to be "translated"
     * @param string $default Optional The default value to be used when $plantilla is a single name field, and the field is not found in $elemento, if null leave $plantilla unchanged
     * @param string $separadorInicial Optional The string used to identify de begining of the field's name in $plantilla, default "<-"
     * @param string $separadorFinal Optional The string used to identify de end of the field's name in $plantilla, default "->"
     * @return string The plantilla "translated"
     */
    public static function replaceCamposEnString($elemento, $plantilla, $default = null, $separadorInicial = "<-", $separadorFinal = "->")
    {
        $pedazos = "";
        if (($left = strpos($plantilla, $separadorInicial)) !== false) {
            $conUrl = false;
        } elseif (($left = strpos($plantilla, urlencode($separadorInicial))) !== false) {
            $separadorInicial = urlencode($separadorInicial);
            $separadorFinal = urlencode($separadorFinal);
            $conUrl = true;
        } else {
            return $pedazos = data_get($elemento, $plantilla, $default ?? $plantilla);
        }
        $right = 0;
        while ($left !== false) {
            $pedazos .= substr($plantilla, $right, $left - $right);
            if (($right = stripos($plantilla, $separadorFinal, $right)) === false) {
                $right = strlen($plantilla);
            }
            $textPiece = substr($plantilla, $left + strlen($separadorInicial), $right - ($left + strlen($separadorInicial)));
            $piece = $textPiece;
            if (($dato = data_get($elemento, $piece, $piece)) === $piece && strpos($piece, ".") !== false) {
                $subCampo = $piece;
                $listoSubCampo = false;
                while (strpos($subCampo, ".") !== false && $listoSubCampo == false) {
                    $finalSubCampo = Str::afterLast($subCampo, '.');
                    $subCampo = Str::beforeLast($subCampo, '.');
                    if (Str::endsWith($subCampo, '.*')) {
                        $subCampo = Str::beforeLast($subCampo, '.');
                        $finalSubCampo = "*.$finalSubCampo";
                    }
                    if (($subDato = data_get($elemento, $subCampo, null)) !== null) {
                        if (is_array($subDato) || is_object($subDato)) {
                            $dato = data_get($subDato, $finalSubCampo, $finalSubCampo);
                        } elseif (is_string($subDato)) {
                            if (CrudGenerator::isJsonString($subDato)) {
                                $dato = data_get(json_decode($subDato, true), $finalSubCampo, $finalSubCampo);
                            } else {
                                $dato = $finalSubCampo;
                            }
                        } else {
                            $dato = $finalSubCampo;
                        }
                        $listoSubCampo = true;
                    }
                }
            } elseif ($dato === $piece) {
                $campo = Str::slug($piece);
                data_get($elemento, $campo, $piece);
            }

            if (is_string($dato)) {
                $stringDato = $dato;
            } elseif (is_array($dato) || is_object($dato)) {
                $stringDato = json_encode($dato);
            } else {
                $stringDato = (string) $dato;
            }
            if ($conUrl) {
                $pedazos .= urlencode($stringDato);
            } else {
                $pedazos .= $stringDato;
            }

            $right += strlen($separadorFinal);
            if ($right < strlen($plantilla)) {
                $left = (stripos($plantilla, $separadorInicial, $right));
            } else {
                $left = false;
            }
        }
        if ($right < strlen($plantilla)) {
            $pedazos .= substr($plantilla, $right);
        }
        return $pedazos;
    }

    /**
     * Extract the fields names from a string used to be replaced
     * 
     * @param string $plantilla The string to be "translated"
     * @param string $separadorInicial Optional The string used to identify de begining of the field's name in $plantilla, default "<-"
     * @param string $separadorFinal Optional The string used to identify de end of the field's name in $plantilla, default "->"
     * @return array The list of fields in $plantilla
     */
    public static function getCamposDeReplacementString($plantilla, $separadorInicial = "<-", $separadorFinal = "->")
    {
        $listOfFields = [];
        if (($left = strpos($plantilla, $separadorInicial)) !== false) {
            $right = 0;
            while ($left !== false) {
                if (($right = stripos($plantilla, $separadorFinal, $right)) === false) {
                    $right = strlen($plantilla);
                }
                $textPiece = substr($plantilla, $left + strlen($separadorInicial), $right - ($left + strlen($separadorInicial)));
                $piece = $textPiece;
                $campo = Str::slug($piece);
                $listOfFields[] = $campo;
                $right += strlen($separadorFinal);
                if ($right < strlen($plantilla)) {
                    $left = (stripos($plantilla, $separadorInicial, $right));
                } else {
                    $left = false;
                }
            }
        } elseif (($left = strpos($plantilla, urlencode($separadorInicial))) !== false) {
            $separadorInicial = urlencode($separadorInicial);
            $separadorFinal = urlencode($separadorFinal);
            $right = 0;
            while ($left !== false) {
                if (($right = stripos($plantilla, $separadorFinal, $right)) === false) {
                    $right = strlen($plantilla);
                }
                $textPiece = substr($plantilla, $left + strlen($separadorInicial), $right - ($left + strlen($separadorInicial)));
                $piece = $textPiece;
                $campo = Str::slug($piece);
                $right += strlen($separadorFinal);
                if ($right < strlen($plantilla)) {
                    $left = (stripos($plantilla, $separadorInicial, $right));
                } else {
                    $left = false;
                }
            }
        } else {
            $listOfFields[] = $plantilla;
        }
        return $listOfFields;
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
        if ($modeloClass = CrudGenerator::getModel($modelo)) {
            $modelo = strtolower(class_basename($modeloClass));
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
        if (is_string($json_string)) {
            json_decode($json_string);
            return (json_last_error() == JSON_ERROR_NONE);

            if (strpos($json_string, "{") === false) {
                return false;
            }
            return !preg_match('/[^,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t]/', preg_replace('/"(\\.|[^"\\\\])*"/', '', $json_string));
        }
        return false;
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
                    $modeloClass = Str::singular($modelo);
                    if (!file_exists(resource_path("lang/vendor/crudgenerator/" . $currentLocale . "/" . $modeloClass . ".php"))) {
                        if (!file_exists(resource_path("lang/vendor/crudgenerator/" . $defaultLocale . "/" . $modeloClass . ".php"))) {
                            $modeloClass = $modelo;
                            $crud = false;
                            if (!file_exists(resource_path("lang/" . $currentLocale . "/" . $modeloClass . ".php"))) {
                                if (!file_exists(resource_path("lang/" . $defaultLocale . "/" . $modeloClass . ".php"))) {
                                    //$modeloClass = substr($modelo, 0, strlen($modelo) - 1);
                                    $modeloClass = Str::singular($modelo);
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
                //$modeloClass = strtolower(class_basename($modeloClass));
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
        $locale = CrudGenerator::setLocale();
        //$locale = $app->getLocale();
        $routes = explode(".", $route);
        $base = "";
        if (count($routes) > 1) {
            $base = CrudGenerator::transRouteModel($routes[0]);
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
        $locale = CrudGenerator::setLocale();
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
     * @param float $color
     * @param float $factor
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
            $rgb = CrudGenerator::setColors($lambda, $rgb);
            $rgb = CrudGenerator::setFactor($lambda, $rgb);
            $rgb["r"] = CrudGenerator::adjustColor($rgb["r"], $rgb["f"]);
            $rgb["g"] = CrudGenerator::adjustColor($rgb["g"], $rgb["f"]);
            $rgb["b"] = CrudGenerator::adjustColor($rgb["b"], $rgb["f"]);
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
            $return = CrudGenerator::countdim(reset($array)) + 1;
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
            case 'expandir':
                $default = 'fa fa-caret-down';
                break;
            case 'contraer':
                $default = 'fa fa-caret-up';
                break;
            default:
                $default = "fa fa-$tipo fa-lg";
                break;
        }
        $icono =  Arr::get(config('sirgrimorum.crudgenerator.icons'), $tipoConfig, $default);
        if ($conTag) {
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
        if (strpos($text, " ") === false) {
            return substr($text, 0, $chars) . "...";
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
     * @param bool $waitForJquery Optional if should wait for Jquery to be loaded before load de script
     * @return string The script call for the scriptLoader function
     */
    public static function addScriptLoaderHtml($src, $defer = false, $inner = "", $innerIsBlock = false, $waitForJquery = true)
    {
        $name = config("sirgrimorum.crudgenerator.scriptLoader_name", "scriptLoader");
        $deferStr = ($defer) ? "true" : "false";
        $waitForJqueryStr = ($waitForJquery) ? "true" : "false";
        $html = "";
        if ($inner != "" && !$innerIsBlock) {
            $id = Str::random(8) . "_typeahead_block";
            $html = "<script id=\"$id\" type=\"text/html\">$inner</script>";
            $inner = $id;
        }
        $html .= "<script>$name('$src',$deferStr,\"$inner\",$waitForJqueryStr);</script>";
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

    /**
     * Convert array to utf8 array recursively
     * @param array $dat The array to convert
     * @return array The converted array
     */
    public static function convert_from_latin1_to_utf8_recursively($dat)
    {
        if (is_string($dat)) {
            return mb_convert_encoding($dat, 'UTF-8', 'UTF-8');
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) $ret[$i] = self::convert_from_latin1_to_utf8_recursively($d);

            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

            return $dat;
        } else {
            return $dat;
        }
    }
}
