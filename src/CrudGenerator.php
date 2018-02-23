<?php

namespace Sirgrimorum\CrudGenerator;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Exception;
use Sirgrimorum\CrudGenerator\CrudController;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionMethod;
use Sirgrimorum\TransArticles\Models\Article;
use Illuminate\Support\Facades\File;
use Sirgrimorum\CrudGenerator\SuperClosure;

class CrudGenerator {

    /**
     * 
     * @param string $app Ipara nada
     */
    function __construct($app) {
        
    }

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
        if (method_exists($model, $key)) {
            return is_a($model->$key(), "Illuminate\Database\Eloquent\Relations\Relation");
        } else {
            return false;
        }
    }

    /**
     * Register a Model Policy in AuthServiceProvider using a config Array
     * 
     * Assumed Policy Class Name is {Model}Policy, and assumed Policy path is /app/Policies/{Model}Policy.php
     * 
     * @param array $config Array
     * @return boolean If the policy was registered or not
     */
    public static function registerPolicy($config) {
        $modeloM = ucfirst(basename($config['modelo']));
        $modelo = strtolower($modeloM);
        $policyName = $modeloM . 'Policy';
        $path = str_finish(str_replace([ "/"], [ "\\"], app_path('Providers/AuthServiceProvider.php')), '.php');
        $policyPath = app_path('Policies/' . str_finish($policyName, ".php"));
        $policyPath = str_finish(str_replace([ "/"], [ "\\"], $policyPath), '.php');
        if (file_exists($path) && file_exists($policyPath)) {
            $modeloM = basename($config['modelo']);
            $contents = file($path);
            $inicio = -1;
            $fin = -1;
            $encontrado = -1;
            foreach ($contents as $index => $line) {
                if (strpos($line, '$policies = [') > 0) {
                    $inicio = $index;
                }
                if (strpos($line, $config['modelo']) > 0 && $inicio >= 0 && $fin == -1) {
                    $encontrado = $index;
                }
                if (strpos($line, "];") > 0 && $inicio >= 0 && $fin == -1) {
                    $fin = $index;
                }
            }
            $newTexto = chr(9) . "'" . $config['modelo'] . "' => 'App\\Policies\\" . $policyName . "', " . chr(13) . chr(10);
            if ($encontrado >= 0) {
                $contents[$encontrado] = $newTexto;
            } elseif ($inicio >= 0 && $fin >= 0) {
                $newContent = array_slice($contents, 0, $fin);
                $newContent[] = $newTexto;
                foreach (array_slice($contents, $fin) as $linea) {
                    $newContent[] = $linea;
                }
                $contents = $newContent;
            }
            $contents = file_put_contents($path, $contents);
        } else {
            $contents = false;
        }
        return $contents;
    }

    /**
     * Create a new Model related file using a view as a template and a config array as directives
     * 
     * If needed, create the path directory recursively
     * 
     * @param string $view Blade view name
     * @param boolean $localized if the views assume a localized routes or not
     * @param string $path the directory path for the file
     * @param string $filename the file name
     * @param array $config The configuration array
     * @param int $pathPermissions Optional, the permissions for the new directory, default 0746
     * @param string $flags optional, the type of file save, default is '' wich would overwrite the file, options 'append'
     * @return mix Same as file_put_contents return for the file
     */
    public static function saveResource($view, $localized, $path, $filename, $config, $pathPermissions = 0764, $flags = "") {
        $view = str_start($view, "sirgrimorum::templates.");
        $modeloClass = $config['modelo'];
        $modeloM = ucfirst(basename($config['modelo']));
        $modelo = strtolower($modeloM);
        $searchArr = ["{?php}", "{php?}", "[[", "]]", "[!!", "!!]", "{modelo}", "{Modelo}", "{model}", "{Model}", "*extends", "*section", "*stop", "*stack", "*push", "*if", "*else", "*foreach", "*end", "{ " . $modelo . " }"];
        $replaceArr = ["<?php", "?>", "{{", "}}", "{!!", "!!}", $modelo, $modeloM, $modelo, $modeloM, "@extends", "@section", "@stop", "@stack", "@push", "@if", "@else", "@foreach", "@end", "{" . $modelo . "}"];
        $contenido = view($view, ["config" => $config, "localized" => $localized])->render();
        $contenido = str_replace($searchArr, $replaceArr, $contenido);

        if (substr($path, strlen($path) - 1) == "/" || substr($path, strlen($path) - 1) == "\\") {
            $path = substr($path, 0, strlen($path) - 1);
        }
        if (!file_exists($path)) {
            mkdir($path, $pathPermissions, true);
        }
        $path = str_finish(str_replace([ "/"], [ "\\"], $path . str_start($filename, "/")), '.php');
        //echo "<pre>" . print_r([$path,$contenido], true) . "</pre>";
        if ($flags == "append") {
            return file_put_contents($path, $contenido, FILE_APPEND);
        } else {
            return file_put_contents($path, $contenido);
        }
    }

    /**
     * Create all the Model related files using config array as directive and crudgenerator views as templates
     * 
     * use php artisan vendor:publish --tag=templates to control templates
     * 
     * @param array $config The configuration array
     * @param boolean $localized if want localized route groups or not
     * @param ProgressBar $bar to register advance in the process
     * @param string $type Type of Files to create, options are "controller" (Controller, Request, Policy adn Repository), "views" (CRUD views), "reoutes" (Register CRUD Routes), "all" (All the Resources)
     * @return boolean[] the results given By saveResource() for each file
     */
    public static function generateResources($config, $localized, $bar, $type = "all") {
        $modeloM = ucfirst(basename($config['modelo']));
        $modelo = strtolower($modeloM);
        if ($type == "controller" || $type == "all") {

            $path = app_path('Http/Controllers');
            $resultController = CrudGenerator::saveResource('controller', $localized, $path, $modeloM . 'Controller.php', $config);
            $bar->advance();

            $path = app_path('Http/Requests');
            $resultRequest = CrudGenerator::saveResource('request', $localized, $path, $modeloM . 'Request.php', $config);
            $bar->advance();

            $path = app_path('Policies');
            $resultPolicy = CrudGenerator::saveResource('policy', $localized, $path, $modeloM . 'Policy.php', $config);
            $bar->advance();

            $path = app_path('Repositories');
            $resultRepository = CrudGenerator::saveResource('repository', $localized, $path, $modeloM . 'Repository.php', $config);
            $bar->advance();
        }

        if ($type == "views" || $type == "all") {
            $path = resource_path('views/models/' . $modelo);
            $resultCreate = CrudGenerator::saveResource('views.create', $localized, $path, 'create.blade.php', $config);
            $bar->advance();

            $resultEdit = CrudGenerator::saveResource('views.edit', $localized, $path, 'edit.blade.php', $config);
            $bar->advance();

            $resultIndex = CrudGenerator::saveResource('views.index', $localized, $path, 'index.blade.php', $config);
            $bar->advance();

            $resultShow = CrudGenerator::saveResource('views.show', $localized, $path, 'show.blade.php', $config);
            $bar->advance();
        }
        if ($type == "routes" || $type == "all") {
            $path = base_path('routes');
            $resultRoute = CrudGenerator::saveResource('routes', $localized, $path, 'web.php', $config, 0764, "append");
        }
        if ($type == "controller") {
            return [$resultController, $resultRequest, $resultPolicy, $resultRepository];
        } elseif ($type == "views") {
            return [$resultCreate, $resultEdit, $resultIndex, $resultShow];
        } elseif ($type == "routes") {
            return $resultRoute;
        } elseif ($type == "all") {
            return [$resultController, $resultRequest, $resultPolicy, $resultRepository, $resultCreate, $resultEdit, $resultIndex, $resultShow, $resultRoute];
        } else {
            return false;
        }
    }

    /**
     * Register a configuratio array file in the CrudGenerator config file
     * @param array $config Configuration array
     * @param string $path Path to the configuration array file relative to a configuration directory
     * @param string $config_path Optional Configuration directory, if "" use config_path()
     * @return boolean
     */
    public static function registerConfig($config, $path, $config_path = "") {
        $inPath = $path;
        $path = str_finish(str_replace([".", "/"], ["\\", "\\"], $path), '.php');
        if ($config_path == "") {
            $config_path = config_path($path);
        } else {
            $config_path = base_path($config_path . str_start($path, "/"));
        }
        $path = $config_path;
        $crudgenConfig = config_path("sirgrimorum\\crudgenerator.php");
        if (file_exists($crudgenConfig) && file_exists($path)) {
            $modeloM = basename($config['modelo']);
            $contents = file($crudgenConfig);
            $inicio = -1;
            $fin = -1;
            $encontrado = -1;
            foreach ($contents as $index => $line) {
                if (strpos($line, "a dmin_routes") > 0) {
                    $inicio = $index;
                }
                if (strpos($line, $modeloM) > 0 && $inicio >= 0 && $fin == -1) {
                    $encontrado = $index;
                }
                if (strpos($line, "]") > 0 && $inicio >= 0 && $fin == -1) {
                    $fin = $index;
                }
            }
            $newTexto = chr(9) . '"' . $modeloM . '" => "' . $inPath . '", ' . chr(13) . chr(10);
            if ($encontrado >= 0) {
                $contents[$encontrado] = $newTexto;
            } elseif ($inicio >= 0 && $fin >= 0) {
                $newContent = array_slice($contents, 0, $fin);
                $newContent[] = $newTexto;
                foreach (array_slice($contents, $fin) as $linea) {
                    $newContent[] = $linea;
                }
                $contents = $newContent;
            }
            $contents = file_put_contents($crudgenConfig, $contents);
        } else {
            $contents = false;
        }
        return $contents;
    }

    /**
     * Create a configuration array file form a configuration array
     * 
     * @param array $config COnfiguration Array
     * @param string $path Path to the configuration array file relative to a configuration directory
     * @param string $config_path Optional Configuration directory, if "" use config_path()
     * @return mix Same as file_put_contents return for the file
     */
    public static function saveConfig($config, $path, $config_path = "") {
        $inPath = $path;
        $path = str_finish(str_replace([".", "/"], ["\\", "\\"], $path), '.php');
        if ($config_path == "") {
            $config_path = config_path($path);
        } else {
            $config_path = base_path($config_path . str_start($path, "/"));
        }
        $path = $config_path;
        $strConfig = CrudGenerator::arrayToFile($config);
        $contents = file_put_contents($path, $strConfig);
        return $contents;
    }

    /**
     * Transform an array into a PHP file content string
     * 
     * @param array $array Array to transform
     * @return string
     */
    private static function arrayToFile($array) {
        $strFile = "<?php" . chr(13) . chr(10) . chr(13) . chr(10) . "return [" . chr(13) . chr(10);
        $strValue = CrudGenerator::arrayToFileWrite($array, 0);
        $strFile .= $strValue . "];";
        return $strFile;
    }

    /**
     * Transform an array into a string using identation
     * 
     * @param array $array Array to transform
     * @return string
     */
    private static function arrayToFileWrite($array, $numParent) {
        $tabs = "";
        for ($index = 0; $index <= $numParent; $index++) {
            $tabs .= chr(9);
        }
        $strArr = "";
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $strValue = CrudGenerator::arrayToFileWrite($value, $numParent + 1);
                $strArr .= $tabs . '"' . $key . '" => [' . chr(13) . chr(10) . $strValue . $tabs . '], ' . chr(13) . chr(10);
            } elseif (is_bool($value)) {
                if ($value) {
                    $strValue = "true";
                } else {
                    $strValue = "false";
                }
                $strArr .= $tabs . '"' . $key . '" => ' . $strValue . ', ' . chr(13) . chr(10);
            } elseif (is_callable($value)) {
                $closure = new SuperClosure($value);
                $strArr .= $tabs . '"' . $key . '" => ' . print_r($closure->getCode(), true) . ', ' . chr(13) . chr(10);
            } elseif (is_object($value)) {
                $strArr .= $tabs . '"' . $key . '" => "' . serialize($value) . '", ' . chr(13) . chr(10);
            } elseif (is_string($value)) {
                $strArr .= $tabs . '"' . $key . '" => "' . $value . '", ' . chr(13) . chr(10);
            } elseif (is_int($value)) {
                $strArr .= $tabs . '"' . $key . '" => ' . $value . ', ' . chr(13) . chr(10);
            }
        }
        return $strArr;
    }

    /**
     * Get the configuration parameters for a model using de CrudLoader format.
     * 
     * Using the model, it would bring it from the crudgenerator.admin_routes array.
     * 
     * If crudgenerator.admin_routes is 'render' or no configuration file with its value is found
     * it will create automatically a new one based on the CrudGenerator configuration file, 
     * the Model class and the DataBase table for that Model.
     * 
     * Use $smartMerge for merging 2 configuration arrays, if no $baseConfig is set, it would smart merge the
     * $config and use automatically created one as base, otherwise it would smart merge de two configurations using
     * $baseConfig as Base and $config to overwrite it.
     * 
     * If no $config is set and $smartMerge is true, it would look for the config file using crudgenerator.admin_routes
     * and use it to overwrite the automatically created one. If no file is found, it would return the automaticaly
     * genereted one.
     * 
     * For smart merge, use the value "notThisTime" in $config to delete that key from the $baseConfig or
     * the autoconfiguration.
     * 
     * The configuration returned would be localized.
     * 
     * @param string $modelo The Model class name, used to retreave the default configuration from crudgenerator.admin_routes
     * @param boolean $smartMerge Optional, true for smart merge or false (default) to only retrive the config
     * @param mix $config Optional, The configuration route or array to load. empty or 'render'(default) to automaticaly create it, if no one is found using only $model. If $smatMerge is true, is used to overwrite $baseConfig
     * @param mix $baseConfig Optional, used for smartMerge: The configuration route or array used as base for the merge, if empty(default) or not found, it would create automatically the Base config from the Model an the DB
     * @param boolean $trans Optional, whether to translate the config or not, default is true
     * @param boolean $fail Optional, whether to fail if automatically create config fails or simply return false
     * @return array The configuration array localized. If smartMerge fail or result is empty, it would return baseConfig if $fail is true or false if $fail is false, if automatically create one fails it would return a 500 error if $fails is true otherwise it would return false.
     */
    public static function getConfig($modelo, $smartMerge = false, $config = '', $baseConfig = '', $trans = true, $fail = true) {
        /**
         * Get initial config and model name
         */
        if (!array_has(config("sirgrimorum.crudgenerator.admin_routes"), $modelo)) {
            $modelo = ucfirst($modelo);
            if (!array_has(config("sirgrimorum.crudgenerator.admin_routes"), $modelo)) {
                //$modelo = strtolower($modelo);
            }
        }

        if ($config == '') {
            $config = 'render';
            //return config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
            if (array_has(config("sirgrimorum.crudgenerator.admin_routes"), $modelo)) {
                $config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
            }
        } elseif (is_string($config)) {
            $config = config($config);
        }

        /**
         * Separate baseConfig for smart merge y principal config
         */
        if ($smartMerge == true && is_array($config)) {
            if ($trans) {
                $preConfig = CrudGenerator::translateConfig($config);
            } else {
                $preConfig = $config;
            }
            $config = "render";
            if ($baseConfig != "") {
                if (is_array($baseConfig)) {
                    $config = $baseConfig;
                } elseif ($baseConfig != "") {
                    $config = config($baseConfig);
                }
                if (is_array($config)) {
                    if ($trans) {
                        $config = CrudGenerator::translateConfig($config);
                    }
                    $auxConfig = CrudGenerator::smartMergeConfig($config, $preConfig);
                    if ($auxConfig === false && $$fail == true) {
                        $auxConfig = $config;
                    }
                    return $auxConfig;
                }
            }
        } else {
            $smartMerge = false;
        }
        if (!is_array($config) || $smartMerge == true) {
            /**
             * Auto Generate Config array
             */
            if (!$modeloClass = CrudGenerator::getModel($modelo, $config)) {
                if ($fail) {
                    abort(500, 'There is no Model class for the model name "' . $modelo . '" ind the CrudGenerator::getConfig(String $modelo)');
                } else {
                    return false;
                }
            }
            /**
             * Get the model information
             */
            $modeloM = class_basename($modeloClass);
            $modelo = strtolower($modeloM);
            $modeloE = new $modeloClass();
            $tabla = $modeloE->getTable();
            $columns = [
                "model" => get_class($modeloE),
                "tabla" => $tabla,
                "id" => $modeloE->getKeyName(),
                "name" => CrudGenerator::getNameAttribute($modeloE),
                "attributes" => $modeloE->getConnection()->getSchemaBuilder()->getColumnListing($tabla),
            ];

            if (!$columns = CrudGenerator::getModelDetailsFromDb($tabla, $columns)) {
                if ($fail) {
                    abort(500, 'There is no valid table for the model name "' . $modelo . '" ind the CrudGenerator::getConfig(String $modelo)');
                } else {
                    return false;
                }
            }
            //echo "<p><strong>deDb</strong></p><pre>" . print_r($columns, true) . "</pre>";
            $columns = CrudGenerator::getModelDetailsFromModel($modeloClass, $modeloE, $columns);
            //echo "<p><strong>deModel</strong></p><pre>" . print_r($columns, true) . "</pre>";
            /**
             * Build the config
             */
            $config = CrudGenerator::buildConfig($modeloClass, $tabla, $modelo, $columns);

            /**
             * Localize config
             */
            if ($trans) {
                $config = CrudGenerator::translateConfig($config);
            }

            /**
             * Merge config
             */
            if ($smartMerge == true) {
                $auxConfig = CrudGenerator::smartMergeConfig($config, $preConfig);
                if ($auxConfig === false) {
                    if (!$fail) {
                        $config = false;
                    }
                } else {
                    $config = $auxConfig;
                }
            }
        } else {
            if ($trans) {
                $config = CrudGenerator::translateConfig($config);
            }
        }
        //echo "<pre>" . print_r($config, true) . "</pre>";
        return $config;
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
                                //return 'There is no Model class for the model name "' . $modelo . '" ind the CrudGenerator::getConfig(String $modelo)';
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
     * Get an array with the Details of a Model extracted from its Data Base Table
     * @param string $tabla The table name
     * @param array $columns Optional The inicial details
     * @return boolean|array The array with the details or false if the table not exists in the DB
     */
    public static function getModelDetailsFromDb($tabla, $columns = []) {
        if (!Schema::hasTable($tabla)) {
            return false;
        }
        $schema = DB::getDoctrineSchemaManager();

        $table_describes = DB::table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
                ->selectRaw("CONSTRAINT_NAME as 'key', TABLE_NAME as 'cliente', COLUMN_NAME as 'cliente_col', REFERENCED_TABLE_NAME as 'patron', REFERENCED_COLUMN_NAME as 'patron_col'")
                ->where("TABLE_SCHEMA", "=", DB::getDatabaseName())
                //->whereRaw('REFERENCED_TABLE_SCHEMA IS NOT NULL')
                ->whereRaw("REFERENCED_TABLE_NAME = '$tabla'")
                ->get();
        $auxArr = json_decode(json_encode($table_describes), true);
        $columns["hasmany"] = $auxArr;
        //echo "<p>hasmany para $tabla</p><pre>" . print_r($table_describes, true) . "</pre>";

        $table_describes = DB::table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
                ->selectRaw("CONSTRAINT_NAME as 'key', TABLE_NAME as 'cliente', COLUMN_NAME as 'cliente_col', REFERENCED_TABLE_NAME as 'patron', REFERENCED_COLUMN_NAME as 'patron_col'")
                ->where("TABLE_SCHEMA", "=", DB::getDatabaseName())
                //->whereRaw('REFERENCED_TABLE_SCHEMA IS NOT NULL')
                ->whereRaw("TABLE_NAME ='$tabla' AND REFERENCED_TABLE_SCHEMA IS NOT NULL")
                ->get();

        $auxArr = json_decode(json_encode($table_describes), true);
        //echo "<pre>" . print_r($columns["hasmany"], true) . "</pre>";
        /* $columns["hasmany"]=[];
          $columns["belongsto"]=[];
          $columns["manytomany"]=[];
          $columns["campos"]=[];
          return $columns; */
        $columns["belongsto"] = $auxArr;
        foreach ($columns["belongsto"] as $indice => $relacion) {
            $singular = substr($relacion['patron'], 0, strlen($relacion['patron']) - 1);
            $columns["belongsto"][$indice]['patron_model_name_single'] = $singular;
            if (!$columns["belongsto"][$indice]['patron_model'] = CrudGenerator::getModel($singular, "App\\" . ucfirst($singular))) {
                unset($columns["belongsto"][$indice]);
            }
        }
        $manytomany = [];
        foreach ($columns["hasmany"] as $indice => $relacion) {
            $table_describes = DB::table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
                    ->selectRaw("CONSTRAINT_NAME as 'key', TABLE_NAME as 'intermedia', COLUMN_NAME as 'intermedia_col', REFERENCED_TABLE_NAME as 'otro', REFERENCED_COLUMN_NAME as 'otro_col'")
                    ->where("TABLE_SCHEMA", "=", DB::getDatabaseName())
                    ->whereRaw("TABLE_NAME ='{$relacion['cliente']}' AND REFERENCED_TABLE_SCHEMA IS NOT NULL")
                    ->get();
            //echo "<p>analizando otro para $tabla y {$relacion['cliente']}</p><pre>" . print_r(['relacion'=>$relacion,'tabledescribes'=>$table_describes], true) . "</pre>";
            if (count($table_describes) == 2) {
                if ($table_describes[0]->otro != $tabla) {
                    $otro = $table_describes[0];
                } else {
                    $otro = $table_describes[1];
                }
                $singular = substr($otro->otro, 0, strlen($otro->otro) - 1);
                if ($otroModel = CrudGenerator::getModel($singular, "App\\" . ucfirst($singular))) {
                    if ($relacion['cliente'] != $otro->otro && $relacion['key'] != $otro->key) {
                        $pivotColumns = [];
                        foreach ($schema->listTableColumns($relacion['cliente']) as $column) {
                            if ($column->getName() != $relacion['cliente_col'] && $column->getName() != $otro->intermedia_col && $column->getName() != "created_at" && $column->getName() != "updated_at") {
                                $pivotColumns[$column->getName()] = [
                                    "name" => $column->getName(),
                                    "type" => $column->getType()->getName(),
                                    "lenght" => $column->getLength(),
                                    "default" => $column->getDefault(),
                                    "autoincrement" => $column->getAutoincrement(),
                                    "type" => $column->getType()->getName(),
                                    "notNull" => $column->getNotnull(),
                                ];
                            }
                        }
                        foreach ($schema->listTableIndexes($relacion['cliente']) as $index) {
                            foreach ($index->getColumns() as $column) {
                                if (isset($pivotColumns[$column])) {
                                    if ($index->isPrimary()) {
                                        unset($pivotColumns[$column]);
                                    } else {
                                        $pivotColumns[$column]['isUnique'] = $index->isUnique();
                                    }
                                }
                            }
                        }

                        $manytomany[] = [
                            "hasmanyIndex" => $indice,
                            "keyIntermediaMia" => $relacion['key'],
                            "col_intermediaMia" => $relacion['cliente_col'],
                            "mia_col" => $relacion['patron_col'],
                            "intermedia" => $relacion['cliente'],
                            "keyIntermediaOtro" => $otro->key,
                            "col_intermediaOtro" => $otro->intermedia_col,
                            "otro_col" => $otro->otro_col,
                            "otro" => $otro->otro,
                            "otro_model" => $otroModel,
                            "otro_model_name_single" => $singular,
                            "pivotColumns" => $pivotColumns
                        ];
                    }
                }
            }
        }
        $columns['manytomany'] = [];
        if (count($manytomany) > 0) {
            $columns['manytomany'] = $manytomany;
            foreach ($manytomany as $manymany) {
                unset($columns["hasmany"][$manymany["hasmanyIndex"]]);
            }
            //echo "<p>manytomany para $tabla</p><pre>" . print_r($columns["manytomany"], true) . "</pre>";
        }

        foreach ($columns["hasmany"] as $indice => $relacion) {
            $singular = substr($relacion['cliente'], 0, strlen($relacion['cliente']) - 1);
            $columns["hasmany"][$indice]['cliente_model_name_single'] = $singular;
            if (!$columns["hasmany"][$indice]['cliente_model'] = CrudGenerator::getModel($singular, "App\\" . ucfirst($singular))) {
                unset($columns["hasmany"][$indice]);
            }
        }



        //echo "<p>belongsto para $tabla</p><pre>" . print_r($columns["belongsto"], true) . "</pre>";
        //echo "<p>hasmany para $tabla</p><pre>" . print_r($columns["hasmany"], true) . "</pre>";
        //$columns["foreign"] = $schema->listTableForeignKeys($tabla);
        //echo "<p>doctrine</p><pre>" . print_r($schema->listTableForeignKeys($tabla), true) . "</pre>";
        foreach ($schema->listTableColumns($tabla) as $column) {
            $columns['campos'][$column->getName()] = [
                "name" => $column->getName(),
                "type" => $column->getType()->getName(),
                "lenght" => $column->getLength(),
                "precision" => $column->getPrecision(),
                "default" => $column->getDefault(),
                "autoincrement" => $column->getAutoincrement(),
                "type" => $column->getType()->getName(),
                "isIndex" => false,
                "isPrimary" => false,
                "isUnique" => false,
                "isUniqueComposite" => false,
                "notNull" => $column->getNotnull(),
                "doctrineObject" => $column,
            ];
        }
        //echo "<pre>" . print_r($schema->listTableIndexes($tabla), true) . "</pre>";
        foreach ($schema->listTableIndexes($tabla) as $index) {
            if ($index->isUnique() && count($index->getColumns()) > 1) {
                $auxColumns = $index->getColumns();
                $column = array_shift($auxColumns);
                $columns['campos'][$column]['isIndex'] = true;
                $columns['campos'][$column]['isUnique'] = true;
                $columns['campos'][$column]['isUniqueComposite'] = true;
                $columns['campos'][$column]['compositeColumns'] = $auxColumns;
                $columns['campos'][$column]['isPrimary'] = $index->isPrimary();
                $columns['campos'][$column]['doctrineIndex'] = $index;
            } else {
                foreach ($index->getColumns() as $column) {
                    $columns['campos'][$column]['isIndex'] = true;
                    $columns['campos'][$column]['isUnique'] = $index->isUnique();
                    $columns['campos'][$column]['isPrimary'] = $index->isPrimary();
                    $columns['campos'][$column]['doctrineIndex'] = $index;
                }
            }
        }

        return $columns;
    }

    /**
     * Get an array with the Details of a Model extracted from its Model definition
     * @param string $modeloClass The model class name
     * @param object $modeloE An instance of the model class
     * @param array $columns Optional The inicial details
     * @return array The array with the details
     */
    private static function getModelDetailsFromModel($modeloClass, $modeloE, $columns = []) {

        $class = new ReflectionClass($modeloClass);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $relations = [];
        $columns['relaciones'] = [];

        foreach ($methods as $method) {
            $auxColumn = [];
            if ($method->getNumberOfParameters() == 0) {
                $methodReturn = CrudGenerator::checkDocBlock($method->getDocComment(), '@return');
                //echo "<pre>" . print_r([$modeloClass,$method->name=>$methodReturn], true) . "</pre>";
                if ($methodReturn == "" || $methodReturn == "Illuminate\Database\Eloquent\Relations\Relation" || $methodReturn == "Illuminate\Database\Eloquent\Relations\Model") {
                    if (is_a($modeloE->{$method->name}(), "Illuminate\Database\Eloquent\Relations\Relation")) {
                        $responseMethod = new ReflectionMethod($modeloClass, $method->name);
                        $relations[] = $method->name;

                        $related = $modeloE->{$method->name}()->getRelated();
                        $datosQueryAux = CrudGenerator::splitQueryNames($modeloE->{$method->name}()->getQuery()->toSql());
                        $tipoRelacion = class_basename(get_class($modeloE->{$method->name}()));
                        switch ($tipoRelacion) {
                            case 'BelongsToMany':
                                $deTabla = array_where($columns['manytomany'], function($value, $key) use ($datosQueryAux) {
                                    return ($value['intermedia'] == $datosQueryAux[1]);
                                });
                                if (count($deTabla) > 0) {
                                    $datosQuery = [
                                        'tablaIntermedia' => $deTabla[0]['intermedia'],
                                        'intermediaRelatedId' => $deTabla[0]['col_intermediaOtro'],
                                        'relatedId' => $deTabla[0]['otro_col'],
                                        'intermediaModelId' => $deTabla[0]['col_intermediaMia'],
                                        'modelId' => $deTabla[0]['mia_col'],
                                        'foreignId' => $deTabla[0]['keyIntermediaMia'],
                                        'otro' => $deTabla[0]['otro'],
                                        'pivotColumns' => $deTabla[0]['pivotColumns']
                                    ];
                                    unset($columns['manytomany'][array_search($deTabla[0], $columns['manytomany'], true)]);
                                } else {
                                    $datosQuery = [
                                        'tablaIntermedia' => $datosQueryAux[1],
                                        'intermediaRelatedId' => $datosQueryAux[5],
                                        'relatedId' => $datosQueryAux[3],
                                        'intermediaModelId' => $datosQueryAux[7],
                                        'modelId' => substr($modeloE->{$method->name}()->getQualifiedParentKeyName(), stripos($modeloE->{$method->name}()->getQualifiedParentKeyName(), ".") + 1),
                                        'foreignId' => $modeloE->{$method->name}()->getForeignKey(),
                                            //'ownerId' => $modeloE->{$method->name}()->getQualifiedRelatedPivotKeyName(),
                                    ];
                                }
                                break;
                            case 'BelongsTo':
                                $foreign = $modeloE->{$method->name}()->getForeignKey();
                                $deTabla = array_where($columns['belongsto'], function($value, $key) use ($foreign, $datosQueryAux) {
                                    return ($value['cliente_col'] == $foreign && $value['patron_col'] == $datosQueryAux[2]);
                                });
                                if (count($deTabla) > 0) {
                                    $datosQuery = [
                                        'relatedId' => $deTabla[0]['patron_col'],
                                        'modelRelatedId' => $foreign,
                                    ];
                                    unset($columns['belongsto'][array_search($deTabla[0], $columns['belongsto'], true)]);
                                } else {
                                    $datosQuery = [
                                        'relatedId' => $datosQueryAux[2],
                                        'modelRelatedId' => $foreign,
                                    ];
                                }
                                $auxColumn = $columns['campos'][$datosQuery['modelRelatedId']];
                                unset($columns['campos'][$datosQuery['modelRelatedId']]);
                                break;
                            case 'HasMany':
                                $deTabla = array_where($columns['hasmany'], function($value, $key) use ($related) {
                                    return $value['cliente'] == $related->getTable();
                                });
                                if (count($deTabla) > 0) {
                                    $datosQuery = [
                                        //'foreignId' => $foreign,
                                        'relatedId' => $deTabla[0]['cliente_col'],
                                        'modelId' => $deTabla[0]['patron_col'],
                                    ];
                                    unset($columns['hasmany'][array_search($deTabla[0], $columns['hasmany'], true)]);
                                } else {
                                    $datosQuery = [
                                        //'foreignId' => $modeloE->{$method->name}()->getForeignKey(),
                                        'relatedId' => $datosQueryAux[2],
                                        'modelId' => substr($modeloE->{$method->name}()->getQualifiedParentKeyName(), stripos($modeloE->{$method->name}()->getQualifiedParentKeyName(), ".") + 1),
                                    ];
                                }
                                break;
                            default:
                                $datosQuery = $datosQueryAux;
                                break;
                        }
                        $columns['relaciones'][$method->name] = [
                            "name" => $method->name,
                            "type" => $tipoRelacion,
                            "isRelation" => true,
                            "relation" => [
                                "model" => $method->class,
                                "baseQuery" => $modeloE->{$method->name}()->getBaseQuery()->toSql(),
                                "query" => $modeloE->{$method->name}()->getQuery()->toSql(),
                                "datosQuery" => $datosQuery,
                                "related" => [
                                    "model" => get_class($related),
                                    "tabla" => $related->getTable(),
                                    "id" => $related->getKeyName(),
                                    "name" => CrudGenerator::getNameAttribute($related),
                                    "attributes" => $related->getConnection()->getSchemaBuilder()->getColumnListing($related->getTable()),
                                ],
                            ],
                            "columna" => $auxColumn,
                        ];
                    }
                }
            }
        }
        return $columns;
    }

    /**
     * Build the COnfiguration Array from an array of model details
     * @param string $modeloClass The model class name
     * @param string $tabla the table name
     * @param string $modelo the model name in lower case
     * @param array $columns the Array with the details
     * @return array The configuration array
     */
    private static function buildConfig($modeloClass, $tabla, $modelo, $columns) {
        $transPrefix = config("sirgrimorum.crudgenerator.trans_prefix");
        $config = [
            "modelo" => $modeloClass,
            "tabla" => $tabla,
            "nombre" => $columns['name'],
            "id" => $columns['id'],
            "url" => "Sirgrimorum_CrudAdministrator",
            "botones" => $transPrefix . "crudgenerator::admin.layout.labels.create",
        ];
        $configCampos = [];
        $rules = [];
        if (\Lang::has('crudgenerator::' . $modelo)) {
            $transFile = 'crudgenerator::' . $modelo;
        } elseif (\Lang::has($modelo)) {
            $transFile = $modelo;
        } else {
            $transFile = false;
        }
        foreach ($columns['campos'] as $campo => $datos) {
            if (!$datos['isPrimary']) {
                $rulesStr = "";
                $prefixRules = "bail|";
                if (\Lang::has((string) $transFile . ".selects." . $campo) && is_array(trans((string) $transFile . ".selects." . $campo))) {
                    $configCampos[$campo] = [
                        'tipo' => 'select',
                        'label' => $campo,
                        'opciones' => $transPrefix . (string) $transFile . ".selects." . $campo,
                    ];
                } else {
                    switch ($datos['type']) {
                        case 'text':
                        case 'blob':
                            $configCampos[$campo] = [
                                'tipo' => 'textarea',
                                'label' => $campo,
                                'placeholder' => "",
                            ];
                            if (CrudGenerator::getTypeByName($campo, 'html')) {
                                $configCampos[$campo]['tipo'] = "html";
                            }
                            break;
                        case 'integer':
                        case 'bigint':
                        case 'smallint':
                        case 'decimal':
                        case 'float':
                            $configCampos[$campo] = [
                                'tipo' => 'number',
                                'label' => $campo,
                                'placeholder' => "",
                                "format" => [0, ".", "."],
                            ];
                            if ($datos['type'] == 'decimal' || $datos['type'] == 'float') {
                                $configCampos[$campo]['format'] = [2, ".", "."];
                            }
                            if ($datos['autoincrement']) {
                                $configCampos[$campo]['valor'] = $modeloClass::all()->count() + 1;
                                $configCampos[$campo]['nodb'] = "nodb";
                                $configCampos[$campo]['readonly'] = "readonly";
                            }
                            break;
                        case 'time':
                        case 'datetime':
                        //case 'datetimetz':
                        case 'date':
                        case 'timestamp':
                            $typeAux = $datos['type'];
                            if ($typeAux == 'timestamp') {
                                $typeAux = 'datetime';
                            }
                            $configCampos[$campo] = [
                                'tipo' => $typeAux,
                                'label' => $campo,
                                'placeholder' => "",
                                "format" => [
                                    "carbon" => $transPrefix . "crudgenerator::admin.formats.carbon." . $typeAux,
                                    "moment" => $transPrefix . "crudgenerator::admin.formats.moment." . $typeAux
                                ],
                            ];
                            if ($campo == 'created_at' || $campo == 'updated_at') {
                                $configCampos[$campo]['nodb'] = "nodb";
                                $configCampos[$campo]['readonly'] = "readonly";
                            }
                            break;
                        case 'boolean':
                            $configCampos[$campo] = [
                                'tipo' => 'checkbox',
                                'label' => $campo,
                                'value' => true,
                            ];
                            break;
                        case 'text':
                        default:
                            $configCampos[$campo] = [
                                'tipo' => 'text',
                                'label' => $campo,
                                'placeholder' => "",
                            ];
                            if (CrudGenerator::getTypeByName($campo, 'email')) {
                                $configCampos[$campo]['tipo'] = "email";
                                $rulesStr .=$prefixRules . 'email';
                                $prefixRules = "|";
                            } elseif (CrudGenerator::getTypeByName($campo, 'url')) {
                                $configCampos[$campo]['tipo'] = "url";
                                $rulesStr .=$prefixRules . 'url';
                                $prefixRules = "|";
                            } elseif (CrudGenerator::getTypeByName($campo, 'password')) {
                                $configCampos[$campo]['tipo'] = "password";
                                $rulesStr .=$prefixRules . 'alpha_num';
                                $prefixRules = "|";
                            } elseif (CrudGenerator::getTypeByName($campo, 'file') || CrudGenerator::getTypeByName($campo, 'image')) {
                                $configCampos[$campo]['tipo'] = "file";
                                $configCampos[$campo]['pathImage'] = $tabla . "_" . $campo;
                                $configCampos[$campo]['path'] = $tabla . "_" . $campo;
                                $configCampos[$campo]['saveCompletePath'] = true;
                                $rulesStr .=$prefixRules . 'file';
                                $prefixRules = "|";
                                $config['files'] = true;
                                if (CrudGenerator::getTypeByName($campo, 'image')) {
                                    $rulesStr .=$prefixRules . 'image';
                                    $prefixRules = "|";
                                }
                            }
                            if ($datos['lenght'] > 0 && !(CrudGenerator::getTypeByName($campo, 'file') || CrudGenerator::getTypeByName($campo, 'image'))) {
                                $rulesStr .=$prefixRules . 'max:' . $datos['lenght'];
                                $prefixRules = "|";
                            }
                            break;
                    }
                }
                if ($datos['notNull'] && $datos['type'] != 'boolean') {
                    $rulesStr .=$prefixRules . 'required';
                    $prefixRules = "|";
                }
                if ($datos['isUniqueComposite']) {
                    $rulesStr .=$prefixRules . 'unique_composite:' . $tabla;
                    foreach ($datos['compositeColumns']as $columnComposite) {
                        $rulesStr .= ', ' . $columnComposite;
                    }
                    $prefixRules = "|";
                } elseif ($datos['isUnique']) {
                    $rulesStr .=$prefixRules . 'unique:' . $tabla . ', ' . $campo;
                    $prefixRules = "|";
                }
                if ($datos['default']) {
                    $configCampos[$campo]['valor'] = $datos['default'];
                }
                if ($transFile !== false) {
                    if (\Lang::has($transFile . ".labels." . $campo)) {
                        $configCampos[$campo]['label'] = $transPrefix . $transFile . ".labels." . $campo;
                    }
                    if (\Lang::has($transFile . ".placeholders." . $campo)) {
                        $configCampos[$campo]['placeholder'] = $transPrefix . $transFile . ".placeholders." . $campo;
                    }
                    if (\Lang::has($transFile . ".descriptions." . $campo)) {
                        $configCampos[$campo]['description'] = $transPrefix . $transFile . ".descriptions." . $campo;
                    }
                }
                if ($rulesStr != "") {
                    $rules[$campo] = $rulesStr;
                }
            }
        }
        foreach ($columns['relaciones'] as $campo => $datos) {

            if ($datos['type'] == "BelongsTo" || $datos['type'] == "HasMany" || $datos['type'] == "BelongsToMany") {
                $rulesStr = "";
                $prefixRules = "bail|";
                switch ($datos['type']) {
                    case "BelongsTo":
                        $configCampos[$campo] = [
                            'tipo' => 'relationship',
                            'label' => $campo,
                            'modelo' => $datos['relation']['related']['model'],
                            'id' => $datos['relation']['datosQuery']['relatedId'],
                            'campo' => $datos['relation']['related']['name'],
                            "todos" => "",
                        ];
                        $rulesStr .=$prefixRules . 'required|exists:' . $datos['relation']['related']['tabla'] . ',' . $datos['relation']['datosQuery']['relatedId'];
                        $prefixRules = "|";
                        if ($datos['columna']['isUnique']) {
                            $rulesStr .=$prefixRules . 'unique:' . $tabla . ', ' . $campo;
                            $prefixRules = "|";
                        }
                        if ($datos['columna']['default']) {
                            $configCampos[$campo]['valor'] = $datos['default'];
                        }
                        break;
                    case "HasMany":
                    case "BelongsToMany":
                        $configCampos[$campo] = [
                            'tipo' => 'relationships',
                            'label' => $campo,
                            'modelo' => $datos['relation']['related']['model'],
                            'id' => $datos['relation']['datosQuery']['relatedId'],
                            'campo' => $datos['relation']['related']['name'],
                            "todos" => "",
                        ];
                        if (isset($datos['relation']['datosQuery']['pivotColumns'])) {
                            if (count($datos['relation']['datosQuery']['pivotColumns']) > 0) {
                                $campoLabel = ucfirst($campo);
                                if ($transFile !== false) {
                                    if (\Lang::has($transFile . ".labels." . $campo)) {
                                        $campoLabel = $transPrefix . $transFile . ".labels." . $campo;
                                    }
                                }
                                $pivotColumns = [[
                                'label' => $campoLabel,
                                'type' => "label",
                                ]];

                                foreach ($datos['relation']['datosQuery']['pivotColumns'] as $pivotColumn) {
                                    $pivotColumnAux = [
                                        'label' => $pivotColumn['name'],
                                        'type' => "text",
                                        'campo' => $pivotColumn['name'],
                                        'placeholder' => "",
                                        'valor' => "",
                                    ];
                                    if ($transFile !== false) {
                                        if (\Lang::has($transFile . ".labels." . $pivotColumn['name'])) {
                                            $pivotColumnAux['label'] = $transPrefix . $transFile . ".labels." . $pivotColumn['name'];
                                        }
                                        if (\Lang::has($transFile . ".placeholders." . $pivotColumn['name'])) {
                                            $pivotColumnAux['placeholder'] = $transPrefix . $transFile . ".placeholders." . $pivotColumn['name'];
                                        }
                                        if (\Lang::has($transFile . ".descriptions." . $pivotColumn['name'])) {
                                            $pivotColumnAux['description'] = $transPrefix . $transFile . ".descriptions." . $pivotColumn['name'];
                                        }
                                        if (\Lang::has($transFile . ".selects." . $pivotColumn['name']) && is_array(trans($transFile . ".selects." . $pivotColumn['name']))) {
                                            $pivotColumnAux['type'] = 'select';
                                            $pivotColumnAux['opciones'] = $transPrefix . $transFile . ".selects." . $pivotColumn['name'];
                                        }
                                    }
                                    switch ($pivotColumn['type']) {
                                        case 'text':
                                        case 'blob':
                                            $pivotColumnAux['type'] = 'textarea';
                                            break;
                                        case 'integer':
                                        case 'bigint':
                                        case 'smallint':
                                        case 'decimal':
                                        case 'float':
                                            $pivotColumnAux['type'] = 'number';
                                            if ($pivotColumn['type'] == 'decimal' || $pivotColumn['type'] == 'float') {
                                                $pivotColumnAux['format'] = [2, ".", "."];
                                            }
                                            if ($pivotColumn['autoincrement']) {
                                                //$pivotColumnAux['valor'] = $modeloClass::all()->count() + 1;
                                                //$pivotColumnAux['nodb'] = "nodb";
                                                //$pivotColumnAux['readonly'] = "readonly";
                                            }
                                            break;
                                        case 'time':
                                        case 'datetime':
                                        //case 'datetimetz':
                                        case 'date':
                                        case 'timestamp':
                                            $pivotColumnAux['type'] = 'text';
                                            $typeAux = $pivotColumn['type'];
                                            if ($typeAux == 'timestamp') {
                                                $typeAux = 'datetime';
                                            }
                                            //$pivotColumnAux['type'] = $typeAux;
                                            $pivotColumnAux['format'] = [
                                                "carbon" => $transPrefix . "crudgenerator::admin.formats.carbon." . $typeAux,
                                                "moment" => $transPrefix . "crudgenerator::admin.formats.moment." . $typeAux
                                            ];
                                            break;
                                        case 'boolean':
                                            $pivotColumnAux['type'] = "text";
                                            $pivotColumnAux['type'] = 'checkbox';
                                            break;
                                        case 'text':
                                        default:
                                            $pivotColumnAux['type'] = "text";
                                            break;
                                    }
                                    $pivotColumns[] = $pivotColumnAux;
                                }
                                if (count($pivotColumns) > 0) {
                                    $configCampos[$campo]['tipo'] = 'relationshipssel';
                                    $configCampos[$campo]['columnas'] = $pivotColumns;
                                }
                            }
                        }
                        $rulesStr .=$prefixRules . 'exists:' . $datos['relation']['related']['tabla'] . ',' . $datos['relation']['datosQuery']['relatedId'];
                        $prefixRules = "|";
                        if ($datos['type'] == 'HasMany') {
                            $configCampos[$campo]['nodb'] = "nodb";
                            $configCampos[$campo]['readonly'] = "readonly";
                        }
                        break;
                }
                if ($transFile !== false) {
                    if (\Lang::has($transFile . ".labels." . $campo)) {
                        $configCampos[$campo]['label'] = $transPrefix . $transFile . ".labels." . $campo;
                    }
                    if (\Lang::has($transFile . ".placeholders." . $campo)) {
                        $configCampos[$campo]['placeholder'] = $transPrefix . $transFile . ".placeholders." . $campo;
                    }
                    if (\Lang::has($transFile . ".descriptions." . $campo)) {
                        $configCampos[$campo]['description'] = $transPrefix . $transFile . ".descriptions." . $campo;
                    }
                }
                if ($rulesStr != "") {
                    $rules[$campo] = $rulesStr;
                }
            }
        }
        foreach ($columns['hasmany'] as $relacion) {
            
        }
        $config["campos"] = $configCampos;
        $config['rules'] = $rules;
        return $config;
    }

    /**
     * Merge 2 configuration arrays, with $config as base and using $preConfig to overwrite.
     * 
     * A value of "notThisTime" in a field would mean that the field must be deleted
     * 
     * @param array $config The base configuration array
     * @param array $preConfig The principal configuration array
     * @return boolean|array The new configuration file
     */
    private static function smartMergeConfig($config, $preConfig) {
        if (is_array($preConfig)) {
            if (is_array($config)) {
                foreach ($preConfig as $key => $value) {
                    if (!array_has($config, $key)) {
                        if (is_array($value)) {
                            if ($auxValue = CrudGenerator::smartMergeConfig("", $value)) {
                                $config[$key] = $auxValue;
                            }
                        } elseif (is_object($value)) {
                            $config[$key] = $value;
                        } elseif (strtolower($value) !== "notthistime") {
                            $config[$key] = $value;
                        }
                    } else {
                        if (is_array($value)) {
                            if ($auxValue = CrudGenerator::smartMergeConfig($config[$key], $value)) {
                                $config[$key] = $auxValue;
                            } else {
                                unset($config[$key]);
                            }
                        } elseif (is_object($value)) {
                            $config[$key] == $value;
                        } elseif (strtolower($value) === "notthistime") {
                            unset($config[$key]);
                        } else {
                            $config[$key] = $value;
                        }
                    }
                }
                if (count($config) > 0) {
                    return $config;
                } else {
                    return false;
                }
            } else {
                $config = [];
                foreach ($preConfig as $key => $value) {
                    if (is_array($value)) {
                        if ($auxValue = CrudGenerator::smartMergeConfig("", $value)) {
                            $config[$key] = $auxValue;
                        }
                    } elseif (is_object($value)) {
                        $config[$key] = $value;
                    } elseif (strtolower($value) !== "notthistime") {
                        $config[$key] = $value;
                    }
                }
                if (count($config) > 0) {
                    return $config;
                } else {
                    return false;
                }
            }
        } elseif (is_object($preConfig)) {
            return $preConfig;
        } elseif (strtolower($preConfig) === "notthistime") {
            return false;
        } elseif (!$preConfig) {
            return false;
        } else {
            return $preConfig;
        }
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
     *  Evaluate functions inside the config array, such as trans(), route(), url() etc.
     * 
     * @param array $array The config array
     * @return array The operated config array
     */
    public static function translateConfig($array) {
        $result = [];
        foreach ($array as $key => $item) {
            if (gettype($item) != "Closure Object") {
                if (is_array($item)) {
                    $result[$key] = CrudGenerator::translateConfig($item);
                } elseif (is_string($item)) {
                    $item = str_replace(config("sirgrimorum.crudgenerator.locale_key"), \App::getLocale(), $item);
                    $item = CrudGenerator::translateString($item, config("sirgrimorum.crudgenerator.route_prefix"), "route");
                    $item = CrudGenerator::translateString($item, config("sirgrimorum.crudgenerator.url_prefix"), "url");
                    $item = CrudGenerator::translateString($item, config("sirgrimorum.crudgenerator.trans_prefix"), "trans");
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
    private static function translateString($item, $prefix, $function, $close = "__") {
        $result = "";
        if (str_contains($item, $prefix)) {
            if (($left = (stripos($item, $prefix))) !== false) {
                while ($left !== false) {
                    //echo "<pre>" . print_r($item, true) . "</pre>";
                    if (($right = stripos($item, $close, $left + strlen($prefix))) === false) {
                        $right = strlen($item);
                    }
                    $textPiece = substr($item, $left + strlen($prefix), $right - ($left + strlen($prefix)));
                    $piece = $textPiece;
                    if (str_contains($textPiece, "{")) {
                        $auxLeft = (stripos($textPiece, "{"));
                        $auxRight = stripos($textPiece, "}", $left) + 1;
                        $auxJson = substr($textPiece, $auxLeft, $auxRight - $auxLeft);
                        //echo "<pre>" . print_r(['textPiece'=>$textPiece,'auxLeft'=>$auxLeft,'auxRight'=>$auxRight,'auxJson'=>$auxJson], true) . "</pre>";
                        $textPiece = str_replace($auxJson, "*****", $textPiece);
                        $auxJson = str_replace(["'", ", }"], ['"', "}"], $auxJson);
                        $auxArr = explode(",", str_replace([" ,", " ,"], [",", ","], $textPiece));
                        if ($auxIndex = array_search("*****", $auxArr)) {
                            $auxArr[$auxIndex] = json_decode($auxJson, true);
                        } else {
                            $auxArr[] = json_decode($auxJson);
                        }
                        //echo "<pre>" . print_r($auxArr, true) . "</pre>";
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
                        //echo "<pre>" . print_r(['prefix' => $prefix, 'lenprefix' => strlen($prefix), 'left' => $left, 'rigth' => $right, 'piece' => $piece, 'lenpiece' => strlen($piece), 'csss' => $item], true) . "</pre>";
                        $left = (stripos($item, $prefix));
                    } else {
                        //echo "<pre>" . print_r($piece, true) . "</pre>";
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
     * Know if a config array has any field of certain type
     * 
     * @param array $array Config array
     * @param string|array $tipo Type of field
     * @return boolean
     */
    public static function hasTipo($array, $tipo) {
        foreach ($array['campos'] as $campo => $configCampo) {
            if (is_array($tipo)) {
                foreach ($tipo as $miniTipo) {
                    if (strtolower($configCampo['tipo']) == strtolower($miniTipo)) {
                        return true;
                    }
                }
            } elseif (strtolower($configCampo['tipo']) == strtolower($tipo)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Evaluate the "permissions" callbacks in the configuration array
     * @param string $action The actual action
     * @param array $config The configuration array
     * @param int $registro Optional The id of the registry
     * @return boolean if the user has or not permission
     */
    public static function checkPermission($action, $config, $registro = 0) {
        $resultado = true;
        $general = false;
        if (isset($config['permissions'])) {
            if (isset($config['permissions'][$action])) {
                $callback = $config['permissions'][$action];
            } elseif (isset($config['permissions']['default'])) {
                $callback = $config['permissions']['default'];
            } else {
                $general = true;
            }
        } else {
            $general = true;
        }
        if ($general) {
            $callback = config('sirgrimorum.crudgenerator.permission');
        }
        if (is_callable($callback)) {
            if ($registro > 0) {
                $objModelo = $config['modelo']::find($registro);
                $resultado = (bool) $callback($objModelo);
            } else {
                $resultado = (bool) $callback();
            }
        } else {
            $resultado = (bool) $callback;
        }

        return $resultado;
    }

    /**
     * Return the value of a field from an object. 
     * 
     * If $campo is an array, returns a concatenated string with the values separated by de separator.
     * 
     * @param object $elemento The objects
     * @param string|array $campo The field or list of fields names
     * @param string $separador Optional, the separator to concatenate with
     * @return string
     */
    private static function getNombreDeLista($elemento, $campo, $separador = "-") {
        if (is_array($campo)) {
            $strNombre = "";
            $preNombre = "";
            foreach ($campo as $indiceCampo => $nombreCampo) {
                $strNombre .= $preNombre . $elemento->{$nombreCampo};
                $preNombre = "-";
            }
            return $strNombre;
        } else {
            return $elemento->{$campo};
        }
    }

    /**
     * Generate create view for a model
     * @param array $config Configuration array
     * @param boolean $simple Optional True for a simple view (just the form)
     * @return HTML Create form
     */
    public static function create($config, $simple = false) {
        //$config = CrudGenerator::translateConfig($config);
        if (!CrudGenerator::checkPermission('create', $config)) {
            return View::make('sirgrimorum::crudgen.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
        }
        $modelo = strtolower(class_basename($config["modelo"]));
        foreach ($config['campos'] as $clave => $relacion) {
            if ($relacion['tipo'] == "relationship" || $relacion['tipo'] == "relationships" || $relacion['tipo'] == "relationshipssel") {
                if (!is_array($config['campos'][$clave]['todos'])) {
                    if ($relacion['tipo'] == "relationship") {
                        //$lista = ["-" => "-"];
                    } else {
                        $lista = [];
                    }
                    if ($config['campos'][$clave]['todos'] == "") {
                        $modeloM = ucfirst($relacion["modelo"]);
                        $modelosM = $modeloM::all();
                    } else {
                        $modelosM = $config['campos'][$clave]['todos'];
                    }
                    if (isset($config['campos'][$clave]['groupby'])) {
                        $groupBy = $config['campos'][$clave]['groupby'];
                        $modelosM->sortBy(function($elemento) use($groupBy) {
                            return CrudGenerator::getNombreDeLista($elemento, $groupBy);
                        });
                    }
                    $lista = [];
                    $auxlista = [];
                    $groupId = null;
                    foreach ($modelosM as $elemento) {
                        if (isset($config['campos'][$clave]['groupby'])) {
                            $nombreGroup = CrudGenerator::getNombreDeLista($elemento, $config['campos'][$clave]['groupby']);
                            if ($groupId === null || $groupId <> $nombreGroup) {
                                if ($groupId !== null) {
                                    $lista[$groupId] = $auxlista;
                                    $auxlista = [];
                                }
                            }
                            $auxlista[$elemento->getKey()] = CrudGenerator::getNombreDeLista($elemento, $relacion['campo']);
                            $groupId = $nombreGroup;
                        } else {
                            $lista[$elemento->getKey()] = CrudGenerator::getNombreDeLista($elemento, $relacion['campo']);
                        }
                    }
                    if (count($auxlista) > 0) {
                        $lista[$groupId] = $auxlista;
                    }
                    $config['campos'][$clave]['todos'] = $lista;
                }
            }
        }

        if (!$simple) {
            $js_section = config("sirgrimorum.crudgenerator.js_section");
            $css_section = config("sirgrimorum.crudgenerator.css_section");
        } else {
            $js_section = "";
            $css_section = "";
        }
        if ($config['url'] == "Sirgrimorum_CrudAdministrator") {
            $config['url'] = route("sirgrimorum_modelo::store", ["localecode" => \App::getLocale(), "modelo" => $modelo]);
            if (\Lang::has('crudgenerator::' . $modelo . '.labels.create')) {
                $config['botones'] = trans("crudgenerator::$modelo.labels.create");
            } else {
                $config['botones'] = trans("crudgenerator::admin.layout.crear");
            }
        }
        $view = View::make('sirgrimorum::crudgen.create', [
                    'config' => $config,
                    'tieneHtml' => CrudGenerator::hasTipo($config, 'html'),
                    'tieneDate' => CrudGenerator::hasTipo($config, ['date', 'datetime', 'time']),
                    'tieneSlider' => CrudGenerator::hasTipo($config, 'slider'),
                    'tieneSelect' => CrudGenerator::hasTipo($config, ['select', 'relationship', 'relationships']),
                    'tieneSearch' => CrudGenerator::hasTipo($config, [ 'relationshipssel']),
                    'js_section' => $js_section,
                    'css_section' => $css_section
        ]);
        return $view->render();
    }

    /**
     * Generate view to show a model
     * @param array $config Configuration array
     * @param integer $id Key of the object
     * @param boolean $simple Optional True for a simple view (just the form)
     * @param Model $registro Optional The Object
     * @return HTML the Object
     */
    public static function show($config, $id = null, $simple = false, $registro = null) {
        //$config = CrudGenerator::translateConfig($config);
        if ($registro == null) {
            $modeloM = ucfirst($config['modelo']);
            if ($id == null) {
                $registro = $modeloM::first();
            } elseif (is_object($id)) {
                $registro = $id;
                $id = $registro->getKey();
            } else {
                $registro = $modeloM::find($id)->first();
            }
        }
        if (!CrudGenerator::checkPermission('show', $config, $registro->getKey())) {
            return View::make('sirgrimorum::crudgen.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
        }
        if (!$simple) {
            $js_section = config("sirgrimorum.crudgenerator.js_section");
            $css_section = config("sirgrimorum.crudgenerator.css_section");
        } else {
            $js_section = "";
            $css_section = "";
        }
        $view = View::make('sirgrimorum::crudgen.show', array(
                    'config' => $config,
                    'registro' => $registro,
                    'js_section' => $js_section,
                    'css_section' => $css_section
        ));
        return $view->render();
    }

    /**
     * Generate de edit view of a model
     * @param array $config Configuration array
     * @param integer $id Key of the object
     * @param boolean $simple Optional True for a simple view (just the form)
     * @param Model $registro Optional The object
     * @return HTML Edit form
     */
    public static function edit($config, $id = null, $simple = false, $registro = null) {
        //$config = CrudGenerator::translateConfig($config);
        $modelo = strtolower(class_basename($config["modelo"]));
        foreach ($config['campos'] as $clave => $relacion) {
            if ($relacion['tipo'] == "relationship" || $relacion['tipo'] == "relationships" || $relacion['tipo'] == "relationshipssel") {
                if (!is_array($config['campos'][$clave]['todos'])) {
                    if ($relacion['tipo'] == "relationship") {
                        //$lista = array("-" => "-");
                    }
                    if ($config['campos'][$clave]['todos'] == "") {
                        $modeloM = ucfirst($relacion["modelo"]);
                        $modelosM = $modeloM::all();
                    } else {
                        $modelosM = $config['campos'][$clave]['todos'];
                    }
                    if (isset($config['campos'][$clave]['groupby'])) {
                        $groupBy = $config['campos'][$clave]['groupby'];
                        $modelosM->sortBy(function($elemento) use($groupBy) {
                            return CrudGenerator::getNombreDeLista($elemento, $groupBy);
                        });
                    }
                    $lista = [];
                    $auxlista = [];
                    $groupId = null;
                    foreach ($modelosM as $elemento) {
                        if (isset($config['campos'][$clave]['groupby'])) {
                            $nombreGroup = CrudGenerator::getNombreDeLista($elemento, $config['campos'][$clave]['groupby']);
                            if ($groupId === null || $groupId <> $nombreGroup) {
                                if ($groupId !== null) {
                                    $lista[$groupId] = $auxlista;
                                    $auxlista = [];
                                }
                            }
                            $auxlista[$elemento->getKey()] = CrudGenerator::getNombreDeLista($elemento, $relacion['campo']);
                            $groupId = $nombreGroup;
                        } else {
                            $lista[$elemento->getKey()] = CrudGenerator::getNombreDeLista($elemento, $relacion['campo']);
                        }
                    }
                    if (count($auxlista) > 0) {
                        $lista[$groupId] = $auxlista;
                    }
                    $config['campos'][$clave]['todos'] = $lista;
                }
            }
        }

        if ($registro == null) {
            $modeloM = ucfirst($config['modelo']);
            if ($id == null) {
                $registro = $modeloM::first();
            } elseif (is_object($id)) {
                $registro = $id;
                $id = $registro->getKey();
            } else {
                $registro = $modeloM::find($id)->first();
            }
        }
        if (!CrudGenerator::checkPermission('edit', $config, $registro->getKey())) {
            return View::make('sirgrimorum::crudgen.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
        }
        if ($config['url'] == "Sirgrimorum_CrudAdministrator") {
            $config['url'] = route("sirgrimorum_modelo::update", ["localecode" => \App::getLocale(), "modelo" => $modelo, "registro" => $registro->id]);
            if (\Lang::has('crudgenerator::' . $modelo . '.labels.edit')) {
                $config['botones'] = trans("crudgenerator::$modelo.labels.edit");
            } else {
                $config['botones'] = trans("crudgenerator::admin.layout.editar");
            }
        }
        if (!$simple) {
            $js_section = config("sirgrimorum.crudgenerator.js_section");
            $css_section = config("sirgrimorum.crudgenerator.css_section");
        } else {
            $js_section = "";
            $css_section = "";
        }
        $view = View::make('sirgrimorum::crudgen.edit', [
                    'config' => $config,
                    'registro' => $registro,
                    'tieneHtml' => CrudGenerator::hasTipo($config, 'html'),
                    'tieneDate' => CrudGenerator::hasTipo($config, ['date', 'datetime', 'time']),
                    'tieneSlider' => CrudGenerator::hasTipo($config, 'slider'),
                    'tieneSelect' => CrudGenerator::hasTipo($config, ['select', 'relationship', 'relationships']),
                    'tieneSearch' => CrudGenerator::hasTipo($config, [ 'relationshipssel']),
                    'js_section' => $js_section,
                    'css_section' => $css_section
        ]);
        return $view->render();
    }

    /**
     * Generate a list of objects of a model
     * @param array $config Configuration array
     * @param boolean $modales Optional True if you want to use modals for the crud actions
     * @param boolean $simple Optional True for a simple view (just the table)
     * @param Model() $registros Optional Array of objects to show
     * @return HTML Table with the objects
     */
    public static function lists($config, $modales = false, $simple = false, $registros = null) {
        //$config = CrudGenerator::translateConfig($config);
        if (!CrudGenerator::checkPermission('index', $config)) {
            return View::make('sirgrimorum::crudgen.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
        }
        if ($registros == null) {
            $modeloM = $config['modelo'];

            $registros = $modeloM::all();
            //$registros = $modeloM::all();
        }
        if (!$simple) {
            $js_section = config("sirgrimorum.crudgenerator.js_section");
            $css_section = config("sirgrimorum.crudgenerator.css_section");
        } else {
            $js_section = "";
            $css_section = "";
        }
        if (!isset($config['botones'])) {
            $base_url = route("sirgrimorum_home", App::getLocale());
            $modelo = basename($modeloM);
            if (($textConfirm = trans('crudgenerator::' . strtolower($modelo) . '.messages.confirm_destroy')) == 'crudgenerator::' . strtolower($modelo) . '.mensajes.confirm_destroy') {
                $textConfirm = trans('crudgenerator::admin.messages.confirm_destroy');
            }
            if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.plural")) {
                $plurales = trans("crudgenerator::" . strtolower($modelo) . ".labels.plural");
            } else {
                $plurales = $plural;
            }
            if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.singular")) {
                $singulares = trans("crudgenerator::" . strtolower($modelo) . ".labels.singular");
            } else {
                $singulares = $modelo;
            }
            $config['botones'] = [
                'show' => "<a class='btn btn-info' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId") . "' title='" . trans('crudgenerator::datatables.buttons.t_show') . " " . $singulares . "'>" . trans("crudgenerator::datatables.buttons.show") . "</a>",
                'edit' => "<a class='btn btn-success' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId/edit") . "' title='" . trans('crudgenerator::datatables.buttons.t_edit') . " " . $singulares . "'>" . trans("crudgenerator::datatables.buttons.edit") . "</a>",
                'remove' => "<a class='btn btn-danger' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId/destroy") . "' data-confirm='" . $textConfirm . "' data-yes='" . trans('crudgenerator::admin.layout.labels.yes') . "' data-no='" . trans('crudgenerator::admin.layout.labels.no') . "' data-confirmtheme='" . config('sirgrimorum.crudgenerator.confirm_theme') . "' data-confirmicon='" . config('sirgrimorum.crudgenerator.confirm_icon') . "' data-confirmtitle='' data-method='delete' rel='nofollow' title='" . trans('crudgenerator::datatables.buttons.t_remove') . " " . $plurales . "'>" . trans("crudgenerator::datatables.buttons.remove") . "</a>",
                'create' => "<a class='btn btn-info' href='" . url($base_url . "/" . strtolower($modelo) . "s/create") . "' title='" . trans('crudgenerator::datatables.buttons.t_create') . " " . $singulares . "'>" . trans("crudgenerator::datatables.buttons.create") . "</a>",
            ];
        }
        $view = View::make('sirgrimorum::crudgen.list', [
                    'config' => $config,
                    'registros' => $registros,
                    'modales' => $modales,
                    'js_section' => $js_section,
                    'css_section' => $css_section
        ]);
        return $view->render();
    }

    /**
     * Generate a list of objects of a model in array format
     * @param array $config Configuration array
     * @param Model() $registros Optional Array of objects to show
     * @return array with the objects in the config format
     */
    public static function lists_array($config, $registros = null) {
        $config = CrudGenerator::translateConfig($config);
        if ($registros == null) {
            $modeloM = ucfirst($config['modelo']);
            $registros = $modeloM::all();
        }
        $return = [];
        foreach ($registros as $registro) {
            $row = CrudGenerator::registry_array($config, $registro);
            $return[] = $row;
        }
        return $return;
    }

    /**
     * Generate an object of a model in array format
     * @param array $config Configuration array
     * @param Model() $registro Optional object to show
     * @return array with the attributes in the config format
     */
    public static function registry_array($config, $registro = null) {
        $modeloM = ucfirst($config['modelo']);
        if ($registro == null) {
            $value = $modeloM::first();
        } else {
            if (!is_object($registro)) {
                $modeloM = ucfirst($config['modelo']);
                $value = $modeloM::find($registro);
            } else {
                $value = $registro;
            }
        }
        $campos = $config['campos'];
        if (isset($config['botones'])) {
            if ($config['botones'] != "") {
                $botones = $config['botones'];
            } else {
                $botones = [];
            }
        } else {
            $botones = [];
        }
        $tabla = $config['tabla'];
        $tablaid = $tabla . "_" . str_random(5);
        if (isset($config['relaciones'])) {
            $relaciones = $config['relaciones'];
        }
        $identificador = $config['id'];
        $nombre = $config['nombre'];

        $row = [];
        foreach ($campos as $columna => $datos) {
            $celda = "";
            if (isset($datos["pre"])) {
                $celda = $datos["pre"];
            }
            if ($datos['tipo'] == "relationship") {
                if (CrudGenerator::hasRelation($value, $datos['modelo'])) {
                    if (array_key_exists('enlace', $datos)) {
                        $celda .= '<a href = "' . str_replace([":modelId", ":modelName"], [$value->{$datos['modelo']

                                    }->{$datos['id']}, $value->{$datos['modelo']}->{$datos['nombre']}], str_replace([urlencode(":  modelId"), urlencode(":modelName")], [$value->{$datos['modelo']}->{$datos['id']}, $value->{
                                    $datos['modelo']}->{
                                    $datos['nombre'] }], $datos['e nlace'])) . '"

                    >';
                    }
                    if (is_array($datos['  campo'])) {
                        $prefijoCampo = "";
                        foreach ($datos['campo'] as $campo) {
                            $celda .= $prefijoCampo . $value->{$datos['modelo']}->{$campo};
                            $prefijoCampo = ", ";
                        }
                    } else {
                        $celda .=$value->{$datos['modelo']}->{$datos['campo']};
                    }
                    if (array_key_exists('enlace', $datos)) {
                        $celda .='</a>';
                    }
                } elseif (CrudGenerator::hasRelation($value, $columna)) {
                    if (array_key_exists('enlace', $datos)) {
                        $celda .= '<a href = "' . str_replace([":modelId", ":modelName"], [$value->{$columna}->{$datos['id']}, $value->{

                                    $columna}->{$datos['nombre']}], str_replace([urlencode(":modelId"), urlencode(": modelName"
                                    )], [$value->{$columna}->{$datos['id']}, $value->{$columna}->{$datos['nombre']}], $datos['enlace'])) . '">';
                    }
                    if (is_array($datos[' campo'])) {
                        $prefijoCampo = "";
                        foreach ($datos['campo'] as $campo) {
                            $celda .= $prefijoCampo . $value->{$columna}->{$campo};
                            $prefijoCampo = ", ";
                        }
                    } else {
                        $celda .=$value->{$columna}->{$datos[' campo']};
                    }
                    if (array_key_exists('enlace', $datos)) {
                        $celda .='< / a>';
                    }
                } else {
                    $celda .='- ';
                }
            } elseif ($datos['tipo'] == "relationships") {
                if (count($value->{$datos['modelo

                    ']}()->get()) > 0) {
                    $prefijoBloque = "";
                    foreach ($value->{$datos['      modelo ']}()->get() as $sub) {
                        if (array_key_exists(' enlace', $datos)) {
                            $celda.= $prefijoBloque . '<a href = "' . str_replace([":modelId", ":modelName "], [$sub->{
                                        $datos['id']}, $sub->{
                                        $datos['nombre'] }], str_replace([urlencode(":modelId"), urlencode(": modelName")], [$sub->{$datos['id']}, $sub->{$datos['nombre']}], $datos['enlace'])) . '">';
                        } else {
                            $celda.= $prefijoBloque;
                        }
                        if (is_array($datos['campo'])) {
                            $prefijoCampo = "";
                            foreach ($datos['campo'] as $campo) {
                                $celda.= $prefijoCampo . $sub->{$campo};
                                $prefijoCampo = ", ";
                            }
                        } else {
                            $celda .= $sub->{$datos['campo']};
                        }
                        if (array_key_exists('enlace', $datos)) {
                            $celda .='</a>';
                        }
                        $prefijoBloque = "; ";
                    }
                } elseif (count($value->{$columna}()->get()) > 0) {
                    $prefijoBloque = "";
                    foreach ($value->{$columna}()->get() as $sub) {
                        if (array_key_exists('enlace', $datos)) {
                            $celda.= $prefijoBloque . '<a href = "' . str_replace([":modelId", ":modelName"], [$sub->{$datos['id']}, $sub->{$datos['nombre'] }], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$sub->{$datos['id']}, $sub->{$datos['nombre']}], $datos['enlace'])) . '">';
                        } else {
                            $celda.= $prefijoBloque;
                        }
                        if (is_array($datos['campo'])) {
                            $prefijoCampo = "";
                            foreach ($datos['campo'] as $campo) {
                                $celda.= $prefijoCampo . $sub->{$campo};
                                $prefijoCampo = ", ";
                            }
                        } else {
                            $celda .= $sub->{$datos['campo']};
                        }
                        if (array_key_exists('enlace', $datos)) {
                            $celda .='</a>';
                        }
                        $prefijoBloque = "; ";
                    }
                } else {
                    $celda .='-';
                }
            } elseif ($datos['tipo'] == "select") {
                if (array_key_exists($value->{$columna}, $datos['opciones'])) {
                    $celda.=$datos['opciones'][$value->{$columna}];
                } else {
                    $celda .='-';
                }
            } elseif ($datos['tipo'] == "function") {
                if (isset($datos['format'])) {
                    if (is_array($datos['format'])) {
                        $celda.=number_format($value->{$columna}(), $datos['format'][0], $datos['format'][1], $datos['format'][2]);
                    } else {
                        $celda .=number_format($value->{$columna}());
                    }
                } else {
                    $celda.=$value->{$columna}();
                }
            } elseif ($datos['tipo'] == "url") {
                $celda .= "<a href='" . $value->{$columna} . "' target='_blank'>" . $value->{$columna} . "</a>";
            } elseif ($datos['tipo'] == "file") {
                if (isset($datos['pathImage'])) {
                    if ($value->{$columna} == "") {
                        $celda .='-';
                    } else {
                        if (isset($datos['enlace'])) {
                            $celda .= str_replace("{value}", $value->{$columna}, $datos['enlace']);
                        } else {
                            $celda .= asset('/images/' . $datos['pathImage'] . $value->{$columna});
                        }
                    }
                } else {
                    if ($value->{$columna} == "") {
                        $celda .='-';
                    } else {
                        if (isset($datos['enlace'])) {
                            $celda .= str_replace("{value}", $value->{$columna}, $datos['enlace']);
                        } else {
                            $celda .= $value->{$columna};
                        }
                    }
                }
            } else {
                if (array_key_exists('enlace', $datos)) {
                    $celda .= '<a href = "' . str_replace([":modelId", ":modelName"], [$value->{$identificador}, $value->{$nombre}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$value->{$identificador}, $value->{$nombre}], $datos['enlace'])) . '">';
                }
                if ($datos['tipo'] == "number" && isset($datos['format'])) {
                    if (is_array($datos['format'])) {
                        $celda .= number_format($value->{$columna}, $datos['format'][0], $datos['format'][1], $datos['format'][2]);
                    } else {
                        $celda .=number_format($value->{$columna});
                    }
                } else {
                    $celda .= $value->{$columna};
                }
                if (array_key_exists('enlace', $datos)) {
                    $celda .='</a>          

                                 

                               

                             

                             

                            

                                     

                             

                              

                              

                                 

                                 

                                

                             

                                          ';
                }
            }
            if (isset($datos["post"])) {
                $celda .= " " . $datos["post"];
            }
            $row[$columna] = $celda;
        }
        if (count($botones) > 0) {
            $celda = "";
            if (is_array($botones)) {
                foreach ($botones as $boton) {
                    $celda .= str_replace([":modelId", ":modelName"], [$value->{$identificador}, $value->{$nombre}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$value->{$identificador}, $value->{$nombre}], $boton));
                }
            } else {
                $celda = str_replace([":modelId", ":modelName"], [$value->{$identificador}, $value->{$nombre}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$value->{$identificador}, $value->{$nombre}], $botones));
            }
            $row["botones"] = $celda;
        }
        return $row;
    }

}
