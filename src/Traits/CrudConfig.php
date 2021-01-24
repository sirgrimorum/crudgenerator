<?php

namespace Sirgrimorum\CrudGenerator\Traits;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use ReflectionClass;
use ReflectionMethod;
use Sirgrimorum\CrudGenerator\CrudGenerator;

trait CrudConfig
{

    /**
     * Get the configuration array for a model using de CrudGenerator format.
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
     * @param boolean $override Optional, If true, override searching for existing config, and go to automaticaly create one
     * @return array The configuration array localized. If smartMerge fail or result is empty, it would return baseConfig if $fail is true or false if $fail is false, if automatically create one fails it would return a 500 error if $fails is true otherwise it would return false.
     */
    public static function getConfig($modelo, $smartMerge = false, $config = '', $baseConfig = '', $trans = true, $fail = true, $override = false)
    {
        /**
         * Save parameters passed
         */
        $parametros = json_encode([
            "modelo" => $modelo,
            "smartMerge" => $smartMerge,
            "config" => $config,
            "baseConfig" => $baseConfig,
            "trans" => $trans,
            "fail" => $fail,
            "override" => $override
        ]);
        /**
         * Get initial config and model name
         */
        if (!Arr::has(config("sirgrimorum.crudgenerator.admin_routes"), $modelo)) {
            $modelo = ucfirst($modelo);
            if (!Arr::has(config("sirgrimorum.crudgenerator.admin_routes"), $modelo)) {
                //$modelo = strtolower($modelo);
            }
        }
        if (!$override) {
            if ($config == '') {
                $config = 'render';
                //return config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
                if (Arr::has(config("sirgrimorum.crudgenerator.admin_routes"), $modelo)) {
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
                    } elseif (is_string($baseConfig)) {
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
                        $auxConfig['parametros'] = $parametros;
                        return $auxConfig;
                    }
                }
            } else {
                $smartMerge = false;
            }
        } else {
            if (is_array($config)) {
                $preConfig = $config;
            }
            $config = 'render';
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
        $config['parametros'] = $parametros;
        return $config;
    }

    /**
     * Get the configuration array for a model using de CrudGenerator format, just like getConfig,
     * but if in the request a "__parametros" field with a json is found, the configuratio will be created
     * using its values insted of the ones passed to this function.
     *
     * @param string $modelo The Model class name, used to retreave the default configuration from crudgenerator.admin_routes
     * @param boolean $smartMerge Optional, true for smart merge or false (default) to only retrive the config
     * @param mix $config Optional, The configuration route or array to load. empty or 'render'(default) to automaticaly create it, if no one is found using only $model. If $smatMerge is true, is used to overwrite $baseConfig
     * @param mix $baseConfig Optional, used for smartMerge: The configuration route or array used as base for the merge, if empty(default) or not found, it would create automatically the Base config from the Model an the DB
     * @param boolean $trans Optional, whether to translate the config or not, default is true
     * @param boolean $fail Optional, whether to fail if automatically create config fails or simply return false
     * @param boolean $override Optional, If true, override searching for existing config, and go to automaticaly create one
     * @return array The configuration array localized. If smartMerge fail or result is empty, it would return baseConfig if $fail is true or false if $fail is false, if automatically create one fails it would return a 500 error if $fails is true otherwise it would return false.
     */
    public static function getConfigWithParametros($modelo, $smartMerge = false, $config = '', $baseConfig = '', $trans = true, $fail = true, $override = false)
    {
        $request = request();
        $newConfig = "";
        if ($request->has("__parametros")) {
            $parametros = json_decode($request->__parametros, true);
            if (is_array($parametros)) {
                if ($parametros["modelo"] == $modelo) {
                    $newConfig = CrudGenerator::getConfig($parametros["modelo"], Arr::get($parametros,"smartMerge", $smartMerge), Arr::get($parametros,"config", $config), Arr::get($parametros,"baseConfig", $baseConfig), Arr::get($parametros,"trans", $trans), Arr::get($parametros,"fail", $fail), Arr::get($parametros,"override", $override));
                }
            }
        }
        if ($newConfig == "") {
            $newConfig = CrudGenerator::getConfig($modelo, $smartMerge, $config, $baseConfig, $trans, $fail, $override);
        }
        return $newConfig;
    }

    /**
     * Get an array with the Details of a Model extracted from its Data Base Table
     * @param string $tabla The table name
     * @param array $columns Optional The inicial details
     * @return boolean|array The array with the details or false if the table not exists in the DB
     */
    public static function getModelDetailsFromDb($tabla, $columns = [])
    {
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
            //$singular = substr($relacion['patron'], 0, strlen($relacion['patron']) - 1);
            $singular = Str::singular($relacion['patron']);
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
                //$singular = substr($otro->otro, 0, strlen($otro->otro) - 1);
                $singular = Str::singular($otro->otro);
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
                                    "isIndex" => false,
                                    "isPrimary" => false,
                                    "isUnique" => false,
                                    "isUniqueComposite" => false,
                                    "doctrineObject" => $column,
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
                                        $pivotColumns[$column]['doctrineIndex'] = $index;
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
            //$singular = substr($relacion['cliente'], 0, strlen($relacion['cliente']) - 1);
            $singular = Str::singular($relacion['cliente']);
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
    public static function getModelDetailsFromModel($modeloClass, $modeloE, $columns = [])
    {

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
                                $deTabla = Arr::where($columns['manytomany'], function ($value, $key) use ($datosQueryAux) {
                                    return ($value['intermedia'] == $datosQueryAux[1]);
                                });
                                if (count($deTabla) > 0) {
                                    $auxDeTabla = array_values($deTabla)[0];
                                    $datosQuery = [
                                        'tablaIntermedia' => $auxDeTabla['intermedia'],
                                        'intermediaRelatedId' => $auxDeTabla['col_intermediaOtro'],
                                        'relatedId' => $auxDeTabla['otro_col'],
                                        'intermediaModelId' => $auxDeTabla['col_intermediaMia'],
                                        'modelId' => $auxDeTabla['mia_col'],
                                        'foreignId' => $auxDeTabla['keyIntermediaMia'],
                                        'otro' => $auxDeTabla['otro'],
                                        'pivotColumns' => $auxDeTabla['pivotColumns']
                                    ];
                                    unset($columns['manytomany'][array_search($auxDeTabla, $columns['manytomany'], true)]);
                                } else {
                                    $datosQuery = [
                                        'tablaIntermedia' => $datosQueryAux[1],
                                        'intermediaRelatedId' => $datosQueryAux[5],
                                        'relatedId' => $datosQueryAux[3],
                                        'intermediaModelId' => $datosQueryAux[7],
                                        'modelId' => substr($modeloE->{$method->name}()->getQualifiedParentKeyName(), stripos($modeloE->{$method->name}()->getQualifiedParentKeyName(), ".") + 1),
                                        'foreignId' => $modeloE->{$method->name}()->getForeignPivotKeyName(),
                                        //'ownerId' => $modeloE->{$method->name}()->getQualifiedRelatedPivotKeyName(),
                                    ];
                                }
                                break;
                            case 'BelongsTo':
                                $foreign = $modeloE->{$method->name}()->getForeignKeyName();
                                $deTabla = Arr::where($columns['belongsto'], function ($value, $key) use ($foreign, $datosQueryAux) {
                                    return ($value['cliente_col'] == $foreign && $value['patron_col'] == $datosQueryAux[2]);
                                });
                                if (count($deTabla) > 0) {
                                    $datosQuery = [
                                        'relatedId' => array_values($deTabla)[0]['patron_col'],
                                        'modelRelatedId' => $foreign,
                                    ];
                                    unset($columns['belongsto'][array_search(array_values($deTabla)[0], $columns['belongsto'], true)]);
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
                                $deTabla = Arr::where($columns['hasmany'], function ($value, $key) use ($related) {
                                    return $value['cliente'] == $related->getTable();
                                });
                                if (count($deTabla) > 0) {
                                    $datosQuery = [
                                        //'foreignId' => $foreign,
                                        'relatedId' => array_values($deTabla)[0]['cliente_col'],
                                        'modelId' => array_values($deTabla)[0]['patron_col'],
                                    ];
                                    unset($columns['hasmany'][array_search(array_values($deTabla)[0], $columns['hasmany'], true)]);
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
    public static function buildConfig($modeloClass, $tabla, $modelo, $columns)
    {
        $transPrefix = CrudGenerator::getPrefixFromFunction("__",  '__trans__');
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
        if (Lang::has('crudgenerator::' . $modelo)) {
            $transFile = 'crudgenerator::' . $modelo;
        } elseif (Lang::has($modelo)) {
            $transFile = $modelo;
        } else {
            $transFile = false;
        }
        foreach ($columns['campos'] as $campo => $datos) {
            if (!$datos['isPrimary']) {
                $rulesStr = "";
                $rulesExtraArrayStr = "";
                $prefixRules = "bail|";
                $prefixRulesExtraArray = "bail|";
                if (Lang::has((string) $transFile . ".selects." . $campo) && is_array(trans((string) $transFile . ".selects." . $campo))) {
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
                            } elseif (CrudGenerator::getTypeByName($campo, 'json')) {
                                $configCampos[$campo]['tipo'] = "json";
                                $configCampos[$campo]['valor'] = "{}";
                            } elseif (CrudGenerator::getTypeByName($campo, 'article')) {
                                $configCampos[$campo]['tipo'] = "article";
                                $configCampos[$campo]['scope'] = "$tabla.$campo";
                                $configCampos[$campo]['es_html'] = true;
                                $rulesStr .= $prefixRules . 'with_articles';
                                $prefixRules = "|";
                            } elseif (CrudGenerator::getTypeByName($campo, 'file') || CrudGenerator::getTypeByName($campo, 'image')) {
                                $configCampos[$campo]['tipo'] = "files";
                                $configCampos[$campo]['path'] = $tabla . "_" . $campo;
                                $configCampos[$campo]['saveCompletePath'] = true;
                                $rulesExtraArrayStr .= $prefixRulesExtraArray . 'file';
                                $prefixRulesExtraArray = "|";
                                $config['files'] = true;
                                if (CrudGenerator::getTypeByName($campo, 'image')) {
                                    $rulesExtraArrayStr .= $prefixRulesExtraArray . 'image';
                                    $prefixRulesExtraArray = "|";
                                }
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
                            }
                            break;
                        case 'boolean':
                            $configCampos[$campo] = [
                                'tipo' => 'checkbox',
                                'label' => $campo,
                                'value' => true,
                                'unchecked' => 0,
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
                                $rulesStr .= $prefixRules . 'email';
                                $prefixRules = "|";
                            } elseif (CrudGenerator::getTypeByName($campo, 'article')) {
                                $configCampos[$campo]['tipo'] = "article";
                                $configCampos[$campo]['scope'] = "$tabla.$campo";
                                $configCampos[$campo]['es_html'] = true;
                                $rulesStr .= $prefixRules . 'with_articles';
                                $prefixRules = "|";
                            } elseif (CrudGenerator::getTypeByName($campo, 'url')) {
                                $configCampos[$campo]['tipo'] = "url";
                                $rulesStr .= $prefixRules . 'url';
                                $prefixRules = "|";
                            } elseif (CrudGenerator::getTypeByName($campo, 'color')) {
                                $configCampos[$campo]['tipo'] = "color";
                            } elseif (CrudGenerator::getTypeByName($campo, 'password')) {
                                $configCampos[$campo]['tipo'] = "password";
                                $configCampos[$campo]['hide'] = ["show", "list", "edit"];
                                $rulesStr .= $prefixRules . 'alpha_num';
                                $prefixRules = "|";
                            } elseif (CrudGenerator::getTypeByName($campo, 'file') || CrudGenerator::getTypeByName($campo, 'image')) {
                                $configCampos[$campo]['tipo'] = "file";
                                $configCampos[$campo]['path'] = $tabla . "_" . $campo;
                                $configCampos[$campo]['saveCompletePath'] = true;
                                $rulesStr .= $prefixRules . 'file';
                                $prefixRules = "|";
                                $config['files'] = true;
                                if (CrudGenerator::getTypeByName($campo, 'image')) {
                                    $rulesStr .= $prefixRules . 'image';
                                    $prefixRules = "|";
                                }
                            }
                            if ($datos['lenght'] > 0  && $configCampos[$campo]['tipo'] != "article" &&  !(CrudGenerator::getTypeByName($campo, 'file') || CrudGenerator::getTypeByName($campo, 'image'))) {
                                $rulesStr .= $prefixRules . 'max:' . $datos['lenght'];
                                $prefixRules = "|";
                            }
                            break;
                    }
                }
                if ($datos['notNull'] && $datos['type'] != 'boolean' && $configCampos[$campo]['tipo'] != "article") {
                    if ($configCampos[$campo]['tipo'] == "files" || $configCampos[$campo]['tipo'] == "file") {
                        $rulesStr .= $prefixRules . 'required_without:' . $campo . "_filereg";
                    } else {
                        $rulesStr .= $prefixRules . 'required';
                    }

                    $prefixRules = "|";
                }
                if ($datos['isUniqueComposite']) {
                    $rulesStr .= $prefixRules . 'unique_composite:' . $tabla;
                    foreach ($datos['compositeColumns'] as $columnComposite) {
                        $rulesStr .= ', ' . $columnComposite;
                    }
                    $prefixRules = "|";
                } elseif ($datos['isUnique'] && $configCampos[$campo]['tipo'] != "article") {
                    $rulesStr .= $prefixRules . 'unique_except:' . $tabla . ',' . $campo;
                    $prefixRules = "|";
                }
                if (isset($datos['default'])) {
                    $configCampos[$campo]['valor'] = $datos['default'];
                }
                if ($transFile !== false) {
                    CrudGenerator::getTransNameForField($configCampos[$campo], "label", $transFile, $campo, $transPrefix);
                    CrudGenerator::getTransNameForField($configCampos[$campo], "placeholder", $transFile, $campo, $transPrefix);
                    CrudGenerator::getTransNameForField($configCampos[$campo], "description", $transFile, $campo, $transPrefix);
                }
                if ($rulesStr != "") {
                    $rules[$campo] = $rulesStr;
                }
                if ($rulesExtraArrayStr != "") {
                    $rules[$campo . ".*"] = $rulesExtraArrayStr;
                }
            }
        }
        foreach ($columns['relaciones'] as $campo => $datos) {

            if ($datos['type'] == "BelongsTo" || $datos['type'] == "HasMany" || $datos['type'] == "BelongsToMany") {
                $rulesStr = "";
                $prefixRules = "bail|";
                switch ($datos['type']) {
                    case "BelongsTo":
                        $modeloSimple = strtolower(class_basename($datos['relation']['related']['model']));
                        $configCampos[$campo] = [
                            'tipo' => 'relationship',
                            'label' => $campo,
                            'modelo' => $datos['relation']['related']['model'],
                            'id' => $datos['relation']['datosQuery']['relatedId'],
                            'campo' => "<-{$datos['relation']['related']['name']}-> (<-{$datos['relation']['related']['id']}->)",
                            'enlace' => "__route__sirgrimorum_modelo::show,{'modelo':'{$modeloSimple}','registro':'<-{$datos['relation']['related']['id']}->'}__",
                            "todos" => "",
                        ];
                        $rulesStr .= $prefixRules . 'required|exists:' . $datos['relation']['related']['tabla'] . ',' . $datos['relation']['datosQuery']['relatedId'];
                        $prefixRules = "|";
                        if ($datos['columna']['isUnique']) {
                            $rulesStr .= $prefixRules . 'unique:' . $tabla . ',' . $campo;
                            $prefixRules = "|";
                        }
                        if (isset($datos['columna']['default'])) {
                            $configCampos[$campo]['valor'] = $datos['default'];
                        }
                        break;
                    case "HasMany":
                    case "BelongsToMany":
                        $modeloSimple = strtolower(class_basename($datos['relation']['related']['model']));
                        $configCampos[$campo] = [
                            'tipo' => 'relationships',
                            'label' => $campo,
                            'modelo' => $datos['relation']['related']['model'],
                            'id' => $datos['relation']['related']['id'],
                            'campo' => "<-{$datos['relation']['related']['name']}-> (<-{$datos['relation']['related']['id']}->)",
                            'enlace' => "__route__sirgrimorum_modelo::show,{'modelo':'{$modeloSimple}','registro':'<-{$datos['relation']['related']['id']}->'}__",
                            'card_class' => 'bg-light',
                            "todos" => "",
                        ];
                        if (isset($datos['relation']['datosQuery']['pivotColumns'])) {
                            if (count($datos['relation']['datosQuery']['pivotColumns']) > 0) {
                                $campoLabel = ucfirst($campo);
                                if ($transFile !== false) {
                                    if (Lang::has($transFile . ".labels." . $campo)) {
                                        $campoLabel = $transPrefix . $transFile . ".labels." . $campo;
                                    }
                                }
                                $pivotColumns = [[
                                    'label' => $campoLabel,
                                    'tipo' => "label",
                                ]];

                                foreach ($datos['relation']['datosQuery']['pivotColumns'] as $pivotColumn) {
                                    $pivotColumnAux = [
                                        'campo' => $pivotColumn['name'],
                                        'label' => $pivotColumn['name'],
                                        'tipo' => "text",
                                        'placeholder' => "",
                                        //'valor' => "",
                                    ];
                                    $rulesPivotStr = "";
                                    $rulesPivotExtraArrayStr = "";
                                    $prefixRulesPivot = "bail|";
                                    $prefixRulesPivotExtraArray = "bail|";
                                    if ($transFile !== false) {
                                        CrudGenerator::getTransNameForField($pivotColumnAux, "label", $transFile, "{$campo}_{$pivotColumn['name']}", $transPrefix);
                                        CrudGenerator::getTransNameForField($pivotColumnAux, "placeholder", $transFile, "{$campo}_{$pivotColumn['name']}", $transPrefix);
                                        CrudGenerator::getTransNameForField($pivotColumnAux, "description", $transFile, "{$campo}_{$pivotColumn['name']}", $transPrefix);
                                    }
                                    if (Lang::has((string)  "$transFile.selects.{$campo}_{$pivotColumn['name']}") && is_array(trans((string) "$transFile.selects.{$campo}_{$pivotColumn['name']}"))) {
                                        $pivotColumnAux['tipo'] = 'select';
                                        $pivotColumnAux['opciones'] =  $transPrefix . (string) "$transFile.selects.{$campo}_{$pivotColumn['name']}";
                                    } else {
                                        switch ($pivotColumn['type']) {
                                            case 'text':
                                            case 'blob':
                                                $pivotColumnAux['tipo'] = 'textarea';
                                                if (CrudGenerator::getTypeByName($pivotColumn['name'], 'html')) {
                                                    $pivotColumnAux['tipo'] = "html";
                                                } elseif (CrudGenerator::getTypeByName($pivotColumn['name'], 'json')) {
                                                    $pivotColumnAux['tipo'] = "json";
                                                    $pivotColumnAux['valor'] = "{}";
                                                } elseif (CrudGenerator::getTypeByName($campo, 'article')) {
                                                    $pivotColumnAux['tipo'] = "article";
                                                    $pivotColumnAux['scope'] = "$tabla.$campo.{$pivotColumn['name']}";
                                                    $pivotColumnAux['es_html'] = true;
                                                    $rulesPivotStr .= $prefixRulesPivot . 'with_articles';
                                                    $prefixRulesPivot = "|";
                                                } elseif (CrudGenerator::getTypeByName($pivotColumn['name'], 'file') || CrudGenerator::getTypeByName($pivotColumn['name'], 'image')) {
                                                    $pivotColumnAux['tipo'] = "files";
                                                    $pivotColumnAux['path'] = $tabla . "_" . $campo . "_" . $pivotColumn['name'];
                                                    $pivotColumnAux['saveCompletePath'] = true;
                                                    $rulesPivotExtraArrayStr .= $prefixRulesPivotExtraArray . 'file';
                                                    $prefixRulesPivotExtraArray = "|";
                                                    $configCampos[$campo]['files'] = true;
                                                    if (CrudGenerator::getTypeByName($pivotColumn['name'], 'image')) {
                                                        $rulesPivotExtraArrayStr .= $prefixRulesPivotExtraArray . 'image';
                                                        $prefixRulesPivotExtraArray = "|";
                                                    }
                                                }
                                                break;
                                            case 'integer':
                                            case 'bigint':
                                            case 'smallint':
                                            case 'decimal':
                                            case 'float':
                                                $pivotColumnAux['tipo'] = 'number';
                                                $pivotColumnAux['format'] = [0, ".", "."];
                                                if ($pivotColumn['type'] == 'decimal' || $pivotColumn['type'] == 'float') {
                                                    $pivotColumnAux['format'] = [2, ".", "."];
                                                }
                                                if ($pivotColumn['autoincrement']) {
                                                    //$pivotColumnAux['valor'] = $modeloClass::all()->count() + 1;
                                                    //$pivotColumnAux['nodb'] = "nodb";
                                                }
                                                break;
                                            case 'time':
                                            case 'datetime':
                                                //case 'datetimetz':
                                            case 'date':
                                            case 'timestamp':
                                                //$pivotColumnAux['tipo'] = 'text';
                                                $typeAux = $pivotColumn['type'];
                                                if ($typeAux == 'timestamp') {
                                                    $typeAux = 'datetime';
                                                }
                                                $pivotColumnAux['tipo'] = $typeAux;
                                                $pivotColumnAux['format'] = [
                                                    "carbon" => $transPrefix . "crudgenerator::admin.formats.carbon." . $typeAux,
                                                    "moment" => $transPrefix . "crudgenerator::admin.formats.moment." . $typeAux
                                                ];
                                                break;
                                            case 'boolean':
                                                $pivotColumnAux['value'] = true;
                                                $pivotColumnAux['tipo'] = 'checkbox';
                                                break;
                                            case 'text':
                                            default:
                                                $pivotColumnAux['tipo'] = "text";
                                                if (CrudGenerator::getTypeByName($pivotColumn['name'], 'email')) {
                                                    $pivotColumnAux['tipo'] = "email";
                                                } elseif (CrudGenerator::getTypeByName($campo, 'article')) {
                                                    $pivotColumnAux['tipo'] = "article";
                                                    $pivotColumnAux['scope'] = "$tabla.$campo.{$pivotColumn['name']}";
                                                    $pivotColumnAux['es_html'] = true;
                                                    $rulesPivotStr .= $prefixRulesPivot . 'with_articles';
                                                    $prefixRulesPivot = "|";
                                                } elseif (CrudGenerator::getTypeByName($pivotColumn['name'], 'url')) {
                                                    $pivotColumnAux['tipo'] = "url";
                                                    $rulesPivotStr .= $prefixRulesPivot . 'url';
                                                    $prefixRulesPivot = "|";
                                                } elseif (CrudGenerator::getTypeByName($pivotColumn['name'], 'color')) {
                                                    $pivotColumnAux['tipo'] = "color";
                                                } elseif (CrudGenerator::getTypeByName($pivotColumn['name'], 'password')) {
                                                    $pivotColumnAux['tipo'] = "password";
                                                    $rulesPivotStr .= $prefixRulesPivot . 'alpha_num';
                                                    $prefixRulesPivot = "|";
                                                } elseif (CrudGenerator::getTypeByName($pivotColumn['name'], 'file') || CrudGenerator::getTypeByName($pivotColumn['name'], 'image')) {
                                                    $pivotColumnAux['tipo'] = "file";
                                                    $pivotColumnAux['path'] = $tabla . "_" . $campo . "_" . $pivotColumn['name'];
                                                    $pivotColumnAux['saveCompletePath'] = true;
                                                    $rulesPivotStr .= $prefixRulesPivot . 'file';
                                                    $prefixRulesPivot = "|";
                                                    $configCampos[$campo]['files'] = true;
                                                    if (CrudGenerator::getTypeByName($pivotColumn['name'], 'image')) {
                                                        $rulesPivotStr .= $prefixRulesPivot . 'image';
                                                        $prefixRulesPivot = "|";
                                                    }
                                                }
                                                if ($pivotColumn['lenght'] > 0  && $pivotColumnAux['tipo'] != "article" &&  !(CrudGenerator::getTypeByName($pivotColumn['name'], 'file') || CrudGenerator::getTypeByName($pivotColumn['name'], 'image'))) {
                                                    $rulesPivotStr .= $prefixRulesPivot . 'max:' . $pivotColumn['lenght'];
                                                    $prefixRulesPivot = "|";
                                                }
                                                break;
                                        }
                                    }
                                    if ($pivotColumn['notNull'] && $pivotColumn['type'] != 'boolean' && $pivotColumnAux['tipo'] != "article") {
                                        if ($pivotColumnAux['tipo'] == "files" || $pivotColumnAux['tipo'] == "file") {
                                            $rulesPivotStr .= $prefixRulesPivot . 'required_without:' . $campo . "_filereg";
                                        } else {
                                            $rulesPivotStr .= $prefixRulesPivot . 'required';
                                        }
                                        $prefixRulesPivot = "|";
                                    }
                                    if ($pivotColumn['isUnique'] && $pivotColumnAux['tipo'] != "article") {
                                        $tablaRelacionada = (new $configCampos[$campo]['modelo'])->getTable();
                                        $rulesPivotStr .= $prefixRulesPivot . 'unique_except:' . $tablaRelacionada . ',' . $pivotColumn['name'];
                                        $prefixRulesPivot = "|";
                                    }
                                    if (isset($pivotColumn['default'])) {
                                        $pivotColumnAux['valor'] = $pivotColumn['default'];
                                    }
                                    $pivotColumns[] = $pivotColumnAux;
                                    if ($rulesPivotStr != "") {
                                        $rules["{$campo}__{$pivotColumn['name']}"] = $rulesPivotStr;
                                    }
                                    if ($rulesPivotExtraArrayStr != "") {
                                        $rules["{$campo}__{$pivotColumn['name']}.*"] = $rulesPivotExtraArrayStr;
                                    }
                                }
                                if (count($pivotColumns) > 0) {
                                    $configCampos[$campo]['tipo'] = 'relationshipssel';
                                    $configCampos[$campo]['minLength'] = 1;
                                    $configCampos[$campo]['maxItem'] = 15;
                                    $configCampos[$campo]['columnas'] = $pivotColumns;
                                }
                            }
                        }
                        $rulesStr .= $prefixRules . 'exists:' . $datos['relation']['related']['tabla'] . ',' . $datos['relation']['datosQuery']['modelId'];
                        $prefixRules = "|";
                        if ($datos['type'] == 'HasMany') {
                            $configCampos[$campo]['nodb'] = "nodb";
                        }
                        break;
                }
                if ($transFile !== false) {
                    CrudGenerator::getTransNameForField($configCampos[$campo], "label", $transFile, $campo, $transPrefix);
                    CrudGenerator::getTransNameForField($configCampos[$campo], "placeholder", $transFile, $campo, $transPrefix);
                    CrudGenerator::getTransNameForField($configCampos[$campo], "description", $transFile, $campo, $transPrefix);
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
     * Sets the corret trans command for a column of a specific type
     * 
     * @param array $config The configuration to change
     * @param string $tipo The type, ej: label, placeholder, description
     * @param array $transFile The parent trans file
     * @param string $campo The field name
     * @param string $transPRefix The trans prefix defined in config
     */
    public static function getTransNameForField(&$config, $tipo, $transFile, $campo, $transPrefix)
    {
        if (Lang::has($transFile . ".{$tipo}s." . $campo)) {
            $nuevo = $transPrefix . $transFile . ".{$tipo}s." . $campo;
            if (Arr::has($config, "{$tipo}")) {
                Arr::set($config, "{$tipo}", $nuevo);
            } elseif (count($config) > 2) {
                $config = array_slice($config, 0, count($config) - 2, true) +
                    [$tipo => $nuevo] +
                    array_slice($config, count($config) - 2, count($config) - 1, true);
            } else {
                Arr::set($config, "{$tipo}", $nuevo);
            }
        }
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
    public static function smartMergeConfig($config, $preConfig)
    {
        if (is_array($preConfig)) {
            if (isset($preConfig['parametros'])) {
                $parametros = $preConfig['parametros'];
                unset($preConfig['parametros']);
            } else {
                $parametros = "";
            }
            if (is_array($config)) {
                foreach ($preConfig as $key => $value) {
                    if (!Arr::has($config, $key)) {
                        if (is_array($value)) {
                            if (($auxValue = CrudGenerator::smartMergeConfig("", $value))!== false) {
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
                    if ($parametros != "") {
                        $config["parametros"] = $parametros;
                    }
                    return $config;
                } elseif(count($preConfig) == 0) {
                    return $preConfig;
                }else{
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
     *  Evaluate functions inside the config array, such as trans(), route(), url() etc.
     *
     * @param array $array The config array
     * @return array The operated config array
     */
    public static function translateConfig($array)
    {
        $result = [];
        if (isset($array['parametros'])) {
            $parametros = $array['parametros'];
            unset($array['parametros']);
        } else {
            $parametros = "";
        }
        foreach ($array as $key => $item) {
            if (gettype($item) != "Closure Object") {
                if (is_array($item)) {
                    $result[$key] = CrudGenerator::translateConfig($item);
                } elseif (is_string($item)) {
                    $item = CrudGenerator::translateDato($item);
                    $result[$key] = $item;
                } else {
                    $result[$key] = $item;
                }
            } else {
                $result[$key] = $item;
            }
        }
        if ($parametros != "") {
            $result['parametros'] = $parametros;
        }
        return $result;
    }

    /**
     * Know if a config array has any field of certain type
     *
     * @param array $config Config array
     * @param string|array $tipo Type of field
     * @return boolean
     */
    public static function hasTipo($config, $tipo)
    {
        return CrudGenerator::hasValor($config, 'tipo', $tipo);
    }

    /**
     * Know if a config array has any field whith certain value on certain column
     *
     * @param array $config Config array
     * @param string $columna Column
     * @param string|array $valor Value for the Column
     * @return boolean
     */
    public static function hasValor($config, $columna, $valor)
    {
        foreach ($config['campos'] as $campo => $configCampo) {
            if (isset($configCampo[$columna])) {
                if (is_array($valor)) {
                    if (in_array(strtolower($configCampo[$columna]), $valor)) {
                        return true;
                    }
                } elseif (strtolower($configCampo[$columna]) == strtolower($valor)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Know if a config array has any field whith certain option
     *
     * @param array $config Config array
     * @param string $columna Column
     * @return boolean
     */
    public static function hasClave($config, $columna)
    {
        foreach ($config['campos'] as $campo => $configCampo) {
            if (isset($configCampo[$columna]) && $configCampo[$columna] != "") {
                return true;
            }
        }
        return false;
    }

    /**
     * Do something only for fields with certain value in certain column
     *
     * @param array $config Config array
     * @param string $columna Column
     * @param string|array $valor Value for the column
     * @param callback $callback the callback, is called with [field that fullfill the criteria], $config['campos'][field that fullfill the criteria] as parameter
     * @return boolean
     */
    public static function forValor($config, $columna, $valor, $callback)
    {
        if (is_callable($callback)) {
            foreach ($config['campos'] as $campo => $configCampo) {
                if (isset($configCampo[$columna])) {
                    if (is_array($valor)) {
                        if (in_array(strtolower($configCampo[$columna]), $valor)) {
                            $callback($campo, $configCampo);
                        }
                    } elseif (strtolower($configCampo[$columna]) == strtolower($valor)) {
                        $callback($campo, $configCampo);
                    }
                }
            }
        }
    }

    /**
     * Get the config array but only with fields with certain value in certain column
     *
     * @param array $config Config array
     * @param string $columna Column
     * @param string|array $valor Value for the column
     * @return array The new config array
     */
    public static function justWithValor($config, $columna, $valor)
    {
        $configValor = Arr::except($config, ['campos']);
        $configValor['campos'] = [];
        foreach ($config['campos'] as $campo => $configCampo) {
            if (isset($configCampo[$columna])) {
                if (is_array($valor)) {
                    if (in_array(strtolower($configCampo[$columna]), $valor)) {
                        $configValor['campos'][$campo] = $configCampo;
                    }
                } elseif (strtolower($configCampo[$columna]) == strtolower($valor)) {
                    $configValor['campos'][$campo] = $configCampo;
                }
            }
        }
        return $configValor;
    }

    /**
     * Get an array with only the field names from a config array.
     * If columna and valor are set, return only the ones that have certain value in certain column
     * 
     * @param array $config Config array
     * @param bool $nombreOriginal Optional If true, will use the name of the column in the table, not the one in the config array. Applies for relationship types
     * @param string $columna Optional Column to look for
     * @param string|array $valor Optional Value for the column to look for
     * @return array The new config array 
     */
    public static function getCamposNames($config, $nombreOriginal = false, $columna = null, $valor = null)
    {
        $columns = [];
        foreach ($config['campos'] as $campo => $configCampo) {
            $campoPoner = "";
            if ($columna == null || $valor == null) {
                $campoPoner = $campo;
            } elseif (isset($configCampo[$columna])) {
                if (is_array($valor)) {
                    if (in_array(strtolower($configCampo[$columna]), $valor)) {
                        $campoPoner = $campo;
                    }
                } elseif (strtolower($configCampo[$columna]) == strtolower($valor)) {
                    $campoPoner = $campo;
                }
            }
            if ($campoPoner != "") {
                if ($nombreOriginal) {
                    if ($configCampo['tipo'] == "relationship" && CrudGenerator::hasRelation($config['modelo'], $campo)) {
                        $campoPoner = (new $config['modelo']())->{$campo}()->getForeignKeyName();
                    }
                }
                $columns[] = $campoPoner;
            }
        }
        return $columns;
    }

    public static function turnNodbIntoReadonly($config)
    {
        CrudGenerator::forValor($config, "nodb", "nodb", function ($campo, $configCampo) use (&$config) {
            $config['campos'][$campo]["readonly"] = "readonly";
        });
        return $config;
    }

    /**
     * Check and load the necesary "todos" option form all fields in a configuration array
     * @param array $config The configuration array
     * @return array The configuration array with the "todos" option normalized
     */
    private static function loadTodosFromConfig($config)
    {
        foreach ($config['campos'] as $clave => $relacion) {
            if ($relacion['tipo'] == "relationship" || $relacion['tipo'] == "relationships" || $relacion['tipo'] == "relationshipssel") {
                $config['campos'][$clave] = CrudGenerator::loadTodosForField($relacion, $clave, $config);
            }
        }
        return $config;
    }

    /**
     * Check and load the necesary "todos" option for a specific field in a configuration array
     * 
     * @param array $relacion The configuration array for that field
     * @param string $clave The name of the column in the config array
     * @param array $config Optional The configuration array for the model
     * @param bool $conAdicional Optional Whether or not to add an aditional option, Default false
     * @param string $adicionalText Optional The aditional text to show, default is trans("crudgenerator::admin.layout.labels.seleccione")
     * @param string $adicionalValor Optional The aditional option value, default is "-"
     * 
     * @return array The configuration array for the field with "todos" option nomalized
     */
    public static function loadTodosForField($relacion, $clave = null, $config = null, $conAdicional = false, $adicionalText = null, $adicionalValor = "-")
    {
        if (isset($relacion['todos'])){
            $relacion['todosOriginal'] = $relacion['todos'];
        }else{
            $relacion['todosOriginal'] = $relacion['todos'] = "";
        }
        if ($relacion['tipo'] == "relationship" || $relacion['tipo'] == "relationships" || $relacion['tipo'] == "relationshipssel") {
            if (is_array($relacion['todos'])) {
                if (CrudGenerator::countdim($relacion['todos']) > 1) {
                    try {
                        $modeloM = ucfirst($relacion["modelo"]);
                        $auxTodos = $modeloM::hydrate($relacion['todos']);
                        $relacion['todos'] = $auxTodos;
                    } catch (Exception $exc) {
                    }
                }
            }
            if (!is_array($relacion['todos'])) {
                if ($relacion['tipo'] == "relationship") {
                    //$lista = array("-" => "-");
                }
                if ($relacion['todos'] == "") {
                    $modeloM = ucfirst($relacion["modelo"]);
                    if ($config == null || $clave == null || !($config != null && $clave != null && is_array($config) && Arr::has($config, 'query') && $config['query'] != null)) {
                        $modelosM = $modeloM::all();
                    } else {
                        $registros = CrudGenerator::getListFromConfig($config);
                        if ($registros instanceof Collection) {
                            $modelosM = $registros->map(function ($objeto) use ($relacion, $config) {
                                return [$objeto->getKey() => CrudGenerator::translateDato($relacion['campo'], $objeto, $config)];
                            })->values()->unique()->toArray();
                        } elseif ($registros instanceof Builder) {
                            if ($relacion['tipo'] == "relationship") {
                                $foreign = (new $config['modelo']())->{$clave}()->getForeignKeyName();
                                $foreignQ = (new $config['modelo']())->{$clave}()->getQualifiedForeignKeyName();
                                $ids = DB::query()->fromSub($registros, "ids")->selectRaw("distinct {$foreign}")->get()->map(function ($objeto) use ($foreign) {
                                    return $objeto->{$foreign};
                                })->values()->unique()->toArray();
                                $modelosM = $modeloM::whereIn($relacion['id'], $ids);
                            } elseif ($relacion['tipo'] == "relationships") {
                                $foreignQ = (new $config['modelo']())->{$clave}()->getQualifiedForeignKeyName();
                                $ids = DB::query()->fromSub($registros, "ids")->selectRaw("distinct {$config['id']}")->get()->map(function ($objeto) use ($config) {
                                    return $objeto->{$config['id']};
                                })->values()->unique()->toArray();
                                $modelosM = $modeloM::whereIn($foreignQ, $ids);
                            } elseif ($relacion['tipo'] == "relationshipssel") {
                                /**Pendiente */
                                $modelosM = $modeloM::all();
                            } else {
                                $modelosM = $modeloM::all();
                            }
                        } else {
                            $modelosM = $modeloM::all();
                        }
                    }
                } elseif (is_callable($relacion['todos'])) {
                    $modelosM = $relacion['todos']();
                } elseif (is_string($relacion['todos'])) {
                    try{
                        $modeloM = ucfirst($relacion["modelo"]);
                        $modelosM = $modeloM::whereRaw("({$relacion['todos']})");
                    }catch(Exception $e){
                        $modelosM = $modeloM::whereRaw("(1=1)");
                    }
                } else {
                    $modelosM = $relacion['todos'];
                }
                if ($modelosM instanceof Builder) {
                    $modelosM = $modelosM->get();
                }
                if ($modelosM instanceof Collection) {

                    if (isset($relacion['groupby'])) {
                        $groupBy = $relacion['groupby'];
                        $modelosM->sortBy(function ($elemento) use ($groupBy, $config) {
                            return CrudGenerator::translateDato($groupBy, $elemento, $config);
                        });
                    }
                    $lista = [];
                    $auxlista = [];
                    $groupId = null;
                    foreach ($modelosM as $elemento) {
                        if (isset($relacion['groupby'])) {
                            $nombreGroup = CrudGenerator::translateDato($relacion['groupby'], $elemento, $config);
                            if ($groupId === null || $groupId <> $nombreGroup) {
                                if ($groupId !== null) {
                                    $lista[$groupId] = $auxlista;
                                    $auxlista = [];
                                }
                            }
                            $auxlista[$elemento->getKey()] = CrudGenerator::translateDato($relacion['campo'], $elemento, $config);
                            $groupId = $nombreGroup;
                        } else {
                            $lista[$elemento->getKey()] = CrudGenerator::translateDato($relacion['campo'], $elemento, $config);
                        }
                    }
                    if (count($auxlista) > 0) {
                        $lista[$groupId] = $auxlista;
                    }
                    $relacion['todos'] = $lista;
                } elseif (is_array($modelosM)) {
                    $relacion['todos'] = $modelosM;
                }
            }
        }
        if ($conAdicional) {
            if ($adicionalText == null) {
                $adicionalText = trans("crudgenerator::admin.layout.labels.seleccione");
            }
            $relacion['todos'] = [$adicionalValor => $adicionalText] + $relacion['todos'];
        }
        return $relacion;
    }

    /**
     * Load default classes missing to a config array
     * 
     * @param array $config The current config array
     * @param bool $sub If is going to be loaded in a sub (narrower) view
     * @return array The current config array with the missing default classes loaded
     */
    public static function loadDefaultClasses($config, $sub = false)
    {
        if (!isset($config['class_form'])) {
            $config['class_form'] = '';
        }
        if (!isset($config['class_labelcont'])) {
            $config['class_labelcont'] = !$sub ? 'col-xs-12 col-sm-12 col-md-3': 'col-xs-12 col-sm-12 col-md-12 col-lg-3';
        }
        if (!isset($config['class_label'])) {
            $config['class_label'] = 'col-form-label font-weight-bold mb-0 pb-0';
        }
        if (!isset($config['class_divinput'])) {
            $config['class_divinput'] = !$sub ? 'col-xs-12 col-sm-12 col-md-9' : 'col-xs-12 col-sm-12 col-md-12 col-lg-9';
        }
        if (!isset($config['class_input'])) {
            $config['class_input'] = '';
        }
        if (!isset($config['class_offset'])) {
            $config['class_offset'] = !$sub ? 'offset-xs-0 offset-sm-0 offset-md-3' : 'offset-xs-0 offset-sm-0 offset-md-0 offset-lg-3';
        }
        if (!isset($config['class_button'])) {
            $config['class_button'] = 'btn btn-primary';
        }
        if (!isset($config['class_formgroup'])) {
            $config['class_formgroup'] = '';
        }
        if (!isset($config['pre_html'])) {
            $config['pre_html'] = "";
        }
        if (!isset($config['post_html'])) {
            $config['post_html'] = "";
        }
        if (!isset($config['pre_form_html'])) {
            $config['pre_form_html'] = "";
        }
        if (!isset($config['post_form_html'])) {
            $config['post_form_html'] = "";
        }
        return $config;
    }

    /**
     * Get just the value of a field from a complete registry_array
     * 
     * @param string $campo The field name
     * @param array $value The array obtained from callin CrunGenerator::registry_array($config, $registro, 'complete')
     * @param array $config The configuration array
     * @param string $default The default value in case the field is not found, normally is '-'
     * @param 
     */
    public static function getJustValue($campo, $value, $config, $default = "-")
    {
        $result = $default;
        if (isset($value[$config[$campo]])) {
            if (is_array($value[$config[$campo]]) && isset($value[$config[$campo]]['value'])) {
                $result = $value[$config[$campo]]['value'];
            } elseif (is_array($value[$config[$campo]])) {
                if (count($value[$config[$campo]]) > 0) {
                    $result = $value[$config[$campo]][0];
                }
            } else {
                $result = $value[$config[$campo]];
            }
        }
        return $result ?? $default;
    }

    /**
     * Generate an array of buttons mainly for list views
     * 
     * @param string $modelo The model name
     * @param array $config The config array
     * @return array The array with buttons for show, edit, remove and create
     */
    public static function generateArrBotones($modelo, $config)
    {
        //$base_url = route("sirgrimorum_home", App::getLocale());
        if (($textConfirm = trans('crudgenerator::' . strtolower($modelo) . '.messages.confirm_destroy')) == 'crudgenerator::' . strtolower($modelo) . '.mensajes.confirm_destroy') {
            $textConfirm = trans('crudgenerator::admin.messages.confirm_destroy');
        }
        if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.plural")) {
            $plurales = trans("crudgenerator::" . strtolower($modelo) . ".labels.plural");
        } else {
            $plurales = Str::plural($modelo);
        }
        if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.singular")) {
            $singulares = trans("crudgenerator::" . strtolower($modelo) . ".labels.singular");
        } else {
            $singulares = $modelo;
        }
        $urls = [];
        if ($config['url'] == "Sirgrimorum_CrudAdministrator") {
            $urls = [
                "show" => route('sirgrimorum_modelo::show', ['modelo' => $modelo, 'registro' => ':modelId']),
                "edit" => route('sirgrimorum_modelo::edit', ['modelo' => $modelo, 'registro' => ':modelId']),
                "remove" => route('sirgrimorum_modelo::destroy', ['modelo' => $modelo, 'registro' => ':modelId']),
                "create" => route('sirgrimorum_modelos::create', ['modelo' => $modelo]),
            ];
        } elseif (is_array($config['url'])) {
            $urls = [
                "show" =>  Arr::get($config['url'], 'show', route('sirgrimorum_modelo::show', ['modelo' => $modelo, 'registro' => ':modelId'])),
                "edit" => Arr::get($config['url'], 'edit', route('sirgrimorum_modelo::edit', ['modelo' => $modelo, 'registro' => ':modelId'])),
                "remove" => Arr::get($config['url'], 'remove', route('sirgrimorum_modelo::destroy', ['modelo' => $modelo, 'registro' => ':modelId'])),
                "create" => Arr::get($config['url'], 'create', route('sirgrimorum_modelos::create', ['modelo' => $modelo])),
            ];
        }
        return [
            'show' => "<a class='btn btn-info' href='{$urls['show']}' title='" . trans('crudgenerator::datatables.buttons.t_show') . " $singulares'>" . trans("crudgenerator::datatables.buttons.show") . "</a>",
            'edit' => "<a class='btn btn-success' href='{$urls['edit']}' title='" . trans('crudgenerator::datatables.buttons.t_edit') . " $singulares'>" . trans("crudgenerator::datatables.buttons.edit") . "</a>",
            'remove' => "<a class='btn btn-danger' href='{$urls['remove']}' data-confirm='$textConfirm' data-yes='" . trans('crudgenerator::admin.layout.labels.yes') . "' data-no='" . trans('crudgenerator::admin.layout.labels.no') . "' data-confirmtheme='" . config('sirgrimorum.crudgenerator.confirm_theme') . "' data-confirmicon='" . config('sirgrimorum.crudgenerator.confirm_icon') . "' data-confirmtitle='' data-method='delete' rel='nofollow' title='" . trans('crudgenerator::datatables.buttons.t_remove') . " $plurales'>" . trans("crudgenerator::datatables.buttons.remove") . "</a>",
            'create' => "<a class='btn btn-info' href='{$urls['create']}' title='" . trans('crudgenerator::datatables.buttons.t_create') . " $singulares'>" . trans("crudgenerator::datatables.buttons.create") . "</a>",
        ];
    }

    /**
     * Generate get the arrays for rules, messages and Custom Attributes for validation 
     * including the dynamic ones for relationshipssel fields
     * 
     * @param array $config The config array
     * @return array [$rules, $messages, $customAttributes]
     */
    public static function getRulesWithRelationShips($config, Request $request = null)
    {
        if (is_null($request)) {
            $request = request();
        }
        $rules = [];
        $error_messages = [];
        $customAttributes = [];
        foreach ($config["campos"] as $field => $datos) {
            $customAttributes[$field] = Arr::get($datos, "label", $field);
        }
        if (isset($config['rules'])) {
            if (is_array($config['rules'])) {
                $rules = $config['rules'];
            }
        }
        if (count($rules) == 0) {
            $objModelo = new $config['modelo'];
            if (isset($objModelo->rules)) {
                if (is_array($objModelo->rules)) {
                    $rules = $objModelo->rules;
                }
            }
        }
        $auxIdCambio = $request->get($config["id"]);

        $rules = CrudGenerator::translateArray($rules, ":model", function ($string) use ($auxIdCambio) {
            return $auxIdCambio;
        }, "Id");
        if (count($rules) > 0) {
            $error_messages = [];
            if (isset($config['error_messages'])) {
                if (is_array($config['error_messages'])) {
                    $error_messages = $config['error_messages'];
                }
            }
            if (count($error_messages) == 0) {
                $objModelo = new $config['modelo'];
                if (isset($objModelo->error_messages)) {
                    if (is_array($objModelo->error_messages)) {
                        $error_messages = $objModelo->error_messages;
                    }
                }
            }
            foreach (CrudGenerator::justWithValor($config, "tipo", "relationshipssel")['campos'] as $relacion => $datos) {
                foreach ($datos['columnas'] as $detalles) {
                    if (($campo = Arr::get($detalles, "campo", "")) != "") {
                        $tambienMensajes = [];
                        if (Arr::has($rules, "{$relacion}__{$campo}")) {
                            if ($request->has($relacion)) {
                                foreach ($request->input($relacion) as $pivot => $id) {
                                    $rules["{$relacion}_{$campo}_$id"] = $rules["{$relacion}__{$campo}"];
                                    foreach ($error_messages as $err => $mes) {
                                        if (strpos($err, "{$relacion}__{$campo}") !== false) {
                                            $tambienMensajes[$err] = $err;
                                            $error_messages[str_replace("{$relacion}__{$campo}", "{$relacion}_{$campo}_$id", $err)] = str_replace(":submodel", $pivot, $mes);
                                        }
                                    }
                                    $customAttributes["{$relacion}_{$campo}_$id"] = Arr::get($detalles, "label", $campo) . " " . trans("crudgenerator::admin.layout.labels.de") . " " . $pivot;
                                }
                            }
                            unset($rules["{$relacion}__{$campo}"]);
                        }
                        if (count($tambienMensajes) > 0) {
                            foreach ($tambienMensajes as $error)
                                unset($error_messages[$error]);
                        }
                    }
                }
            }
            $error_messages = array_merge(trans("crudgenerator::admin.error_messages"), $error_messages);
        }
        return [$rules, $error_messages, $customAttributes];
    }
}
