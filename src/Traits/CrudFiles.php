<?php

namespace Sirgrimorum\CrudGenerator\Traits;

use Illuminate\Support\Facades\Storage;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Sirgrimorum\CrudGenerator\SuperClosure;

trait CrudFiles
{

    /**
     * Register a Model Policy in AuthServiceProvider using a config Array
     *
     * Assumed Policy Class Name is {Model}Policy, and assumed Policy path is /app/Policies/{Model}Policy.php
     *
     * @param array $config Array
     * @return boolean If the policy was registered or not
     */
    public static function registerPolicy($config)
    {
        $modeloM = ucfirst(basename($config['modelo']));
        $modelo = strtolower($modeloM);
        $policyName = $modeloM . 'Policy';
        $path = \Illuminate\Support\Str::finish(str_replace(["/"], ["\\"], app_path('Providers/AuthServiceProvider.php')), '.php');
        $policyPath = app_path('Policies/' . \Illuminate\Support\Str::finish($policyName, ".php"));
        $policyPath = \Illuminate\Support\Str::finish(str_replace(["/"], ["\\"], $policyPath), '.php');
        if (file_exists($path) && file_exists($policyPath)) {
            $modeloM = basename($config['modelo']);
            $contents = file($path);
            $inicio = -1;
            $fin = -1;
            $encontrado = -1;
            foreach ($contents as $index => $line) {
                if (strpos($line, '$policies = [') !== false) {
                    $inicio = $index;
                }
                if (strpos($line, $config['modelo']) !== false && $inicio >= 0 && $fin == -1) {
                    $encontrado = $index;
                }
                if (strpos($line, "];") !== false && $inicio >= 0 && $fin == -1) {
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
     * Add get function in a model file
     * 
     * @param string $model El nombre del modelo
     * @param string $path the directory path for the file
     * @param string $filename the file name
     * @return boolean If the model file was successfully modified or not
     */
    public static function addGetToModel($path, $filename)
    {
        if (substr($path, strlen($path) - 1) == "/" || substr($path, strlen($path) - 1) == "\\") {
            $path = substr($path, 0, strlen($path) - 1);
        }
        $path = \Illuminate\Support\Str::finish(str_replace(["/"], ["\\"], $path . \Illuminate\Support\Str::start($filename, "/")), '.php');
        if (file_exists($path)) {
            $contents = file($path);
            $ultima = 0;
            for ($i = count($contents) - 1; $i >= 0; $i--) {
                if (strpos($contents[$i], '}') !== false) {
                    $ultima = $i;
                    $i = -1;
                }
            }
            if ($ultima > 0) {
                $newContent = array_slice($contents, 0, $ultima);
                $contenido = view("sirgrimorum::templates.getfunction")->render();
                $newArray = explode(chr(13), $contenido);
                foreach ($newArray as $newTexto) {
                    $newContent[] = $newTexto;
                }
                foreach (array_slice($contents, $ultima) as $linea) {
                    $newContent[] = $linea;
                }
                $contents = $newContent;
                $contents = file_put_contents($path, $contents);
                return $contents;
            }
        }
        return false;
    }

    /**
     * Register a the routes to a model in the lang/routes.php file
     *
     * @param array $config Array
     * @return boolean If the policy was registered or not
     */
    public static function registerTransRoutes($config)
    {
        $modeloM = ucfirst(basename($config['modelo']));
        $modelo = strtolower($modeloM);
        $policyName = $modeloM . 'Policy';
        foreach (config("sirgrimorum.crudgenerator.list_locales") as $locale) {
            echo "<p>copiando a-" . $locale . "-</p>";
            $path = \Illuminate\Support\Str::finish(str_replace(["/"], ["\\"], resource_path("lang/$locale/routes.php")), '.php');
            if (file_exists($path)) {
                $modeloM = basename($config['modelo']);
                $contents = file($path);
                $inicio = -1;
                $fin = -1;
                $encontrado = -1;
                foreach ($contents as $index => $line) {
                    if (strpos($line, '"routes" => [') !== false || strpos($line, '"routes"=>[') !== false) {
                        $inicio = $index;
                    }
                    if (strpos($line, "'{$modelo}s'") !== false && $inicio >= 0 && $fin == -1) {
                        $encontrado = $index;
                    }
                    if (strpos($line, "],") !== false && $inicio >= 0 && $fin == -1) {
                        $fin = $index;
                    }
                }
                $newTexto = chr(9) . "'{$modelo}s' => '{$modelo}s', " . chr(13) . chr(10);
                if ($encontrado >= 0) {
                    //$contents[$encontrado] = $newTexto;
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
        }
        return $contents;
    }

    /**
     * Register the routes to a model resources in web.php routes file using a config Array
     *
     * @param array $config Array
     * @param bolean $localized If the routes should be localized or not
     * @return boolean If the policy was registered or not
     */
    public static function registerRoutes($config, $localized)
    {
        $path = base_path('routes');
        if (!$localized) {
            return CrudGenerator::saveResource('routes', $localized, $path, 'web.php', $config, 0764, "append");
        }

        $modeloM = ucfirst(basename($config['modelo']));
        $modelo = strtolower($modeloM);
        $path = \Illuminate\Support\Str::finish(str_replace(["/"], ["\\"], base_path('routes/web.php')), '.php');
        if (file_exists($path)) {
            $modeloM = basename($config['modelo']);
            $contents = file($path);
            $inicio = -1;
            $fin = -1;
            $encontrado = -1;
            foreach ($contents as $index => $line) {
                if (strpos($line, "group(['prefix' => CrudGenerator::setLocale(), 'middleware' => ['web','crudgenlocalization']") !== false) {
                    $inicio = $index;
                }
                if (strpos($line, "Route::group(['prefix' => CrudGenerator::transRouteModel(\"{$modelo}s\")") !== false && $inicio >= 0) {
                    $encontrado = $index;
                }
                if (strpos($line, "})->name('locale_home');") !== false && $inicio >= 0 && $fin == -1) {
                    $fin = $index + 1;
                }
            }
            if ($inicio == -1) {
                $path = base_path('routes');
                return CrudGenerator::saveResource('routes', $localized, $path, 'web.php', $config, 0764, "append");
            }
            if ($encontrado >= 0) {
                return false;
            } elseif ($inicio >= 0 && $fin >= 0) {
                $newContent = array_slice($contents, 0, $fin);
                $searchArr = ["{?php}", "{php?}", "[[", "]]", "[!!", "!!]", "{modelo}", "{Modelo}", "{model}", "{Model}", "*extends", "*section", "*stop", "*stack", "*push", "*if", "*else", "*foreach", "*end", "{ " . $modelo . " }"];
                $replaceArr = ["<?php", "?>", "{{", "}}", "{!!", "!!}", $modelo, $modeloM, $modelo, $modeloM, "@extends", "@section", "@stop", "@stack", "@push", "@if", "@else", "@foreach", "@end", "{" . $modelo . "}"];
                $contenido = view("sirgrimorum::templates.routes", ["config" => $config, "localized" => false, "modelo" => $modelo])->render();
                $contenido = str_replace($searchArr, $replaceArr, $contenido);
                $data = explode(chr(13) . chr(10), $contenido);
                $newContent[] = chr(13) . chr(10);
                foreach ($data as $linea) {
                    $newContent[] = $linea . chr(13) . chr(10);
                }
                $newContent[] = chr(13) . chr(10);
                foreach (array_slice($contents, $fin) as $linea) {
                    $newContent[] = $linea;
                }
                $contents = $newContent;
            }
            //$path = \Illuminate\Support\Str::finish(str_replace([ "/"], [ "\\"], $path . \Illuminate\Support\Str::start("web.php", "/")), '.php');
            $contents = file_put_contents($path, $contents);
        } else {
            $contents = false;
        }
        return $contents;
    }

    /**
     * Register the Localization Middleware in Hrrp/Kernel.php
     *
     *
     * @return boolean If the middleware was registered or not
     */
    public static function registerMiddleware()
    {
        $path = \Illuminate\Support\Str::finish(str_replace(["/"], ["\\"], app_path('Http/Kernel.php')), '.php');
        $middlewareClass = "\\Sirgrimorum\\CrudGenerator\\Middleware\\CrudGeneratorLocaleRedirect";
        if (file_exists($path)) {
            $contents = file($path);
            $inicio = -1;
            $fin = -1;
            $encontrado = -1;
            foreach ($contents as $index => $line) {
                if (strpos($line, '$routeMiddleware = [') !== false) {
                    $inicio = $index;
                }
                if (strpos($line, $middlewareClass) !== false && $inicio >= 0 && $fin == -1) {
                    $encontrado = $index;
                }
                if (strpos($line, "];") !== false && $inicio >= 0 && $fin == -1) {
                    $fin = $index;
                }
            }
            $newTexto = chr(9) . "'crudgenlocalization' => " . $middlewareClass . "::class, " . chr(13) . chr(10);
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
    public static function saveResource($view, $localized, $path, $filename, $config, $pathPermissions = 0764, $flags = "")
    {
        $view = \Illuminate\Support\Str::start($view, "sirgrimorum::templates.");
        $modeloClass = $config['modelo'];
        $modeloM = ucfirst(basename($config['modelo']));
        $modelo = strtolower($modeloM);
        $searchArr = ["{?php}", "{php?}", "[[", "]]", "[!!", "!!]", "{modelo}", "{Modelo}", "{model}", "{Model}", "*extends", "*section", "*stop", "*stack", "*push", "*if", "*else", "*foreach", "*end", "{ " . $modelo . " }"];
        $replaceArr = ["<?php", "?>", "{{", "}}", "{!!", "!!}", $modelo, $modeloM, $modelo, $modeloM, "@extends", "@section", "@stop", "@stack", "@push", "@if", "@else", "@foreach", "@end", "{" . $modelo . "}"];
        $contenido = view($view, ["config" => $config, "localized" => $localized, "modelo" => $modelo])->render();
        if ($modelo == "user" && $view == "sirgrimorum::templates.policy") {
            $contenido = str_replace(['$user, {Model} ${model}', '${model}->getKey()'], ['$user, {Model} ${model}2', '${model}2->getKey()'], $contenido);
        }
        $contenido = str_replace($searchArr, $replaceArr, $contenido);

        if (substr($path, strlen($path) - 1) == "/" || substr($path, strlen($path) - 1) == "\\") {
            $path = substr($path, 0, strlen($path) - 1);
        }
        if (!file_exists($path)) {
            mkdir($path, $pathPermissions, true);
        }
        $path = \Illuminate\Support\Str::finish(str_replace(["/"], ["\\"], $path . \Illuminate\Support\Str::start($filename, "/")), '.php');
        //echo "<pre>" . print_r([$path,$contenido], true) . "</pre>";
        if ($flags == "append") {
            return file_put_contents($path, $contenido, FILE_APPEND);
        } else {
            return file_put_contents($path, $contenido);
        }
    }

    /**
     * Remove a file if exists
     * @param string $filename The file name relative to the base_path()
     * @param string $disk Optional 'public', the disk to use
     * @return boolean
     */
    public static function removeFile($filename, $disk = "local")
    {
        if (Storage::disk($disk)->exists($filename)) {
            Storage::disk($disk)->delete($filename);
        } else {
            return false;
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
    public static function generateResources($config, $localized, $bar, $type = "all")
    {
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
    public static function registerConfig($config, $path, $config_path = "")
    {
        $inPath = $path;
        $path = \Illuminate\Support\Str::finish(str_replace([".", "/"], ["\\", "\\"], $path), '.php');
        if ($config_path == "") {
            $config_path = config_path($path);
        } else {
            $config_path = base_path($config_path . \Illuminate\Support\Str::start($path, "/"));
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
                if (strpos($line, "admin_routes") > 0) {
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
    public static function saveConfig($config, $path, $config_path = "")
    {
        $inPath = $path;
        if (isset($config['parametros'])) {
            $parametros = $config['parametros'];
            unset($config['parametros']);
        } else {
            $parametros = "";
        }
        $path = \Illuminate\Support\Str::finish(str_replace([".", "/"], ["\\", "\\"], $path), '.php');
        if ($config_path == "") {
            $config_path = config_path($path);
        } else {
            $config_path = base_path($config_path . \Illuminate\Support\Str::start($path, "/"));
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
    public static function arrayToFile($array)
    {
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
    public static function arrayToFileWrite($array, $numParent)
    {
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
            } elseif (is_callable($value) && !is_string($value) && $value !== "file" && $value !== "url" && $value !== "config") {
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
     * Know if a filename in public is an image with configuration array
     * @param string $filename The file name
     * @param array $detalles Optiona, The configuration array for the field
     * @return string The type of file, options are: image, video, audio, pdf, text,office, compressed, other
     */
    public static function filenameIs(string $filename, array $detalles = [])
    {
        $allowedMimeTypes = [
            'image' => ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/x-windows-bmp', 'image/svg+xml', 'image/x-icon', 'image/tiff'],
            //'video' =>['video/avi', 'video/msvideo', 'video/x-msvideo', 'video/mpeg', 'video/x-motion-jpeg','video/quicktime','video/vivo','video/webm','video/mp4','video/ogg', 'video/3gpp', 'video/x-ms-asf' 'application/octet-stream'],
            'video' => ['video/avi', 'video/mpeg', 'video/webm', 'video/mp4', 'video/ogg', 'video/3gpp', 'application/octet-stream', 'video/quicktime'],
            //'audio' =>['audio/x-gsm', 'audio/mpeg', 'audio/midi', 'audio/x-midi', 'audio/mod','audio/mpeg3','audio/s3m','audio/wav'],
            'audio' => ['audio/mpeg', 'audio/midi', 'audio/mod', 'audio/mpeg3', 'audio/wav'],
            'pdf' => ['application/pdf'],
            'text' => ['text/html', 'text/plain', 'text/richtext'],
            'office' => ['application/mspowerpoint', 'application/msword', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint'],
            'compressed' => ['application/x-compressed', 'application/zip', 'multipart/x-zip', 'application/x-zip-compressed'],
        ];
        $mimeType = CrudGenerator::fileMime(strtolower($filename), $detalles);
        foreach ($allowedMimeTypes as $type => $allowedMimeType) {
            if (in_array($mimeType, $allowedMimeType)) {
                return $type;
            }
        }
        return 'other';
    }

    /**
     * Get the file url using its configuration array
     * @param object $registro The model
     * @param string $filename The file name from the bd
     * @param string $modelo The model name
     * @param string $columna The camp name
     * @param array $detalles Optional, The configuration array for the field
     * @param array $config Optional, The full configuration array for the model
     * @return array [TheFileName, The url to show or retrive the file]
     */
    public static function getFileUrl(string $filename, $registro, string $modelo, string $columna, array $detalles = [], array $config = [])
    {
        $modelClassName = CrudGenerator::getModel($modelo, ucfirst($modelo));
        if (isset($detalles['showPath']) && is_callable($detalles['showPath'])) {
            $urlFile = route('sirgrimorum_modelo::modelfile', ['registro' => $registro->{(new $modelClassName)->getKeyName()}, 'modelo' => $modelo, 'campo' => $columna]) . "?_f=" . $filename;
        } elseif (isset($detalles['showPath']) && is_string($detalles['showPath']) && \Illuminate\Support\Str::startsWith(strtolower($detalles['showPath']), ["http:", "https:"])) {
            if (stripos($detalles['showPath'], ":") !== false) {
                if (count($config) == 0) {
                    $config = CrudGenerator::getConfigWithParametros($modelo);
                }
                $urlFile = CrudGenerator::translateDato($detalles['showPath'], $registro, $config);
            } else {
                $urlFile = $detalles['showPath'];
            }
            $urlFile = str_replace([":modelCampo"], [$registro->{$columna}], $urlFile);
        } else {
            if (isset($datos['path'])) {
                $filename = \Illuminate\Support\Str::start($registro->{$columna}, \Illuminate\Support\Str::finish($detalles['path'], '\\'));
            }
            $urlFile = route('sirgrimorum_modelo::modelfile', ['registro' => $registro->{(new $modelClassName)->getKeyName()}, 'modelo' => $modelo, 'campo' => $columna]) . "?_f=" . $filename;
        }
        return [$filename, $urlFile];
    }

    /**
     * Get Mime of a filename in public with configuration array
     * @param string $filename The file name
     * @param array $detalles Optiona, The configuration array for the field
     * @return string The mime type
     */
    public static function fileMime(string $filename, array $detalles = [])
    {
        if (isset($detalles['path'])) {
            $path = Storage::disk(\Illuminate\Support\Arr::get($detalles, "disk", "local"))->url(\Illuminate\Support\Str::start(str_replace("\\", "/", $filename), \Illuminate\Support\Str::finish(str_replace("\\", "/", $detalles['path']), '/')));
        } else {
            $path = Storage::disk(\Illuminate\Support\Arr::get($detalles, "disk", "local"))->url($filename);
        }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $mimeType = "";
        $typesArray = config("sirgrimorum.mimebyext", []);
        if (!count($typesArray) == 0) {
            if (isset($typesArray[$ext])) {
                $mimeType = $typesArray[$ext];
            }
        }
        if ($mimeType == "" && file_exists($path)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $path);
        } elseif ($mimeType == "") {
            return false;
        }
        return $mimeType;
    }
}
