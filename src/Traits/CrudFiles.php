<?php

namespace Sirgrimorum\CrudGenerator\Traits;

use Sirgrimorum\CrudGenerator\SuperClosure;

trait CrudFiles {

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
     * Remove a file if exists
     * @param string $filename The file name relative to the base_path()
     * @param boolean $public Optional True, indicates the file name is relative to public_path() else is relative to base_path()
     * @return boolean
     */
    public static function removeFile($filename, $public = true) {
        if ($public) {
            $path = public_path($filename);
        } else {
            $path = base_path($filename);
        }
        if (file_exists($path)) {
            unlink($path);
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
    public static function generateResources($config, $localized, $bar, $type = "all") {
        $modeloM = ucfirst(basename($config['modelo']));
        $modelo = strtolower($modeloM);
        if ($type == "controller" || $type == "all") {

            $path = app_path('Http/Controllers');
            $resultController = \Sirgrimorum\CrudGenerator\CrudGenerator::saveResource('controller', $localized, $path, $modeloM . 'Controller.php', $config);
            $bar->advance();

            $path = app_path('Http/Requests');
            $resultRequest = \Sirgrimorum\CrudGenerator\CrudGenerator::saveResource('request', $localized, $path, $modeloM . 'Request.php', $config);
            $bar->advance();

            $path = app_path('Policies');
            $resultPolicy = \Sirgrimorum\CrudGenerator\CrudGenerator::saveResource('policy', $localized, $path, $modeloM . 'Policy.php', $config);
            $bar->advance();

            $path = app_path('Repositories');
            $resultRepository = \Sirgrimorum\CrudGenerator\CrudGenerator::saveResource('repository', $localized, $path, $modeloM . 'Repository.php', $config);
            $bar->advance();
        }

        if ($type == "views" || $type == "all") {
            $path = resource_path('views/models/' . $modelo);
            $resultCreate = \Sirgrimorum\CrudGenerator\CrudGenerator::saveResource('views.create', $localized, $path, 'create.blade.php', $config);
            $bar->advance();

            $resultEdit = \Sirgrimorum\CrudGenerator\CrudGenerator::saveResource('views.edit', $localized, $path, 'edit.blade.php', $config);
            $bar->advance();

            $resultIndex = \Sirgrimorum\CrudGenerator\CrudGenerator::saveResource('views.index', $localized, $path, 'index.blade.php', $config);
            $bar->advance();

            $resultShow = \Sirgrimorum\CrudGenerator\CrudGenerator::saveResource('views.show', $localized, $path, 'show.blade.php', $config);
            $bar->advance();
        }
        if ($type == "routes" || $type == "all") {
            $path = base_path('routes');
            $resultRoute = \Sirgrimorum\CrudGenerator\CrudGenerator::saveResource('routes', $localized, $path, 'web.php', $config, 0764, "append");
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
     * Register a configuratio array file in the \Sirgrimorum\CrudGenerator\CrudGenerator config file
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
    public static function saveConfig($config, $path, $config_path = "") {
        $inPath = $path;
        if (isset($config['parametros'])) {
            $parametros = $config['parametros'];
            unset($config['parametros']);
        } else {
            $parametros = "";
        }
        $path = str_finish(str_replace([".", "/"], ["\\", "\\"], $path), '.php');
        if ($config_path == "") {
            $config_path = config_path($path);
        } else {
            $config_path = base_path($config_path . str_start($path, "/"));
        }
        $path = $config_path;
        $strConfig = \Sirgrimorum\CrudGenerator\CrudGenerator::arrayToFile($config);
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
        $strValue = \Sirgrimorum\CrudGenerator\CrudGenerator::arrayToFileWrite($array, 0);
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
                $strValue = \Sirgrimorum\CrudGenerator\CrudGenerator::arrayToFileWrite($value, $numParent + 1);
                $strArr .= $tabs . '"' . $key . '" => [' . chr(13) . chr(10) . $strValue . $tabs . '], ' . chr(13) . chr(10);
            } elseif (is_bool($value)) {
                if ($value) {
                    $strValue = "true";
                } else {
                    $strValue = "false";
                }
                $strArr .= $tabs . '"' . $key . '" => ' . $strValue . ', ' . chr(13) . chr(10);
            } elseif (is_callable($value) && $value !== "file"  && $value !== "url"  && $value !== "config") {
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
    public static function filenameIs(string $filename, array $detalles = []) {
        $allowedMimeTypes = [
            'image' => ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/x-windows-bmp', 'image/svg+xml', 'image/x-icon', 'image/tiff'],
            //'video' =>['video/avi', 'video/msvideo', 'video/x-msvideo', 'video/mpeg', 'video/x-motion-jpeg','video/quicktime','video/vivo','video/webm','video/mp4','video/ogg', 'video/3gpp', 'video/x-ms-asf' 'application/octet-stream'],
            'video' => ['video/avi', 'video/mpeg', 'video/webm', 'video/mp4', 'video/ogg', 'video/3gpp', 'application/octet-stream'],
            //'audio' =>['audio/x-gsm', 'audio/mpeg', 'audio/midi', 'audio/x-midi', 'audio/mod','audio/mpeg3','audio/s3m','audio/wav'],
            'audio' => ['audio/mpeg', 'audio/midi', 'audio/mod', 'audio/mpeg3', 'audio/wav'],
            'pdf' => ['application/pdf'],
            'text' => ['text/html', 'text/plain', 'text/richtext'],
            'office' => ['application/mspowerpoint', 'application/msword', 'application/excel','application/vnd.ms-excel','application/vnd.ms-powerpoint'],
            'compressed' => ['application/x-compressed', 'application/zip', 'multipart/x-zip', 'application/x-zip-compressed'],
        ];
        $mimeType = \Sirgrimorum\CrudGenerator\CrudGenerator::fileMime($filename, $detalles);
        foreach ($allowedMimeTypes as $type => $allowedMimeType) {
            if (in_array($mimeType, $allowedMimeType)) {
                return $type;
            }
        }
        return 'other';
    }

    /**
     * Get Mime of a filename in public with configuration array
     * @param string $filename The file name
     * @param array $detalles Optiona, The configuration array for the field
     * @return string The mime type
     */
    public static function fileMime(string $filename, array $detalles = []) {
        if (isset($detalles['path'])) {
            $path = str_start($filename, str_finish($detalles['path'], '\\'));
        } else {
            $path = $filename;
        }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (file_exists($path)) {
            $typesArray = config("sirgrimorum.mimebyext", []);
            $mimeType = "";
            if (!count($typesArray) == 0) {
                if (isset($typesArray[$ext])) {
                    $mimeType = $typesArray[$ext];
                }
            }
            $otro = $mimeType;
            if ($mimeType == "") {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, public_path($path));
            }
            return $mimeType;
        } else {
            return false;
        }
    }

}
