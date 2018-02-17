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

class CrudGenerator {

    /**
     * 
     * @param string $app Ipara nada
     */
    function __construct($app) {
        
    }

    public static function checkDocBlock($str, $tag = '') {
        if (empty($tag)) {
            return $str;
        }
        $matches = array();
        preg_match("/" . $tag . "(.*)(\\r\\n|\\r|\\n)/U", $str, $matches);
        if (isset($matches[1])) {
            return trim($matches[1]);
        }
    }

    public static function getConfig($modelo) {
        if (!array_has(config("sirgrimorum.crudgenerator.admin_routes"), $modelo)) {
            $modelo = ucfirst($modelo);
            if (!array_has(config("sirgrimorum.crudgenerator.admin_routes"), $modelo)) {
                $modelo = strtolower($modelo);
            }
        }

        $config = 'render';
        //return config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
        if (array_has(config("sirgrimorum.crudgenerator.admin_routes"), $modelo)) {
            $config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
        }
        return $config;
        if (!is_array($config)) {
            $modeloClass = $config;
            if (!class_exists($modeloClass)) {
                $modeloClass = "App\\" . $modelo;
                if (!class_exists($modeloClass)) {
                    $modelo = strtolower($modelo);
                    $modeloM = ucfirst($modelo);
                    $modeloClass = "App\\" . $modeloM;
                    if (!class_exists($modeloClass)) {
                        $modeloClass = "App\\" . $modelo;
                        if (!class_exists($modeloClass)) {
                            $modeloClass = "Sirgrimorum\\CrudGenerator\\Models\\" . $modeloM;
                            if (!class_exists($modeloClass)) {
                                $modeloClass = "Sirgrimorum\\CrudGenerator\\Models\\" . $modelo;
                                if (!class_exists($modeloClass)) {
                                    abort(500, 'There is no Model class for the model name "' . $modelo . '" ind the CrudGenerator::getConfig(String $modelo)');
                                }
                            }
                        }
                    }
                }
            }
            $modeloM = class_basename($modeloClass);
            $modelo = strtolower($modeloM);
            $modeloE = new $modeloClass();
            $tabla = $modeloE->getTable();
            if (!Schema::hasTable($tabla)) {
                abort(500, 'There is no valid table for the model name "' . $modelo . '" ind the CrudGenerator::getConfig(String $modelo)');
            }
            /* $selects = array('column_name as field', 'column_type as type', 'is_nullable as null', 'column_key as key', 'column_default as default', 'extra as extra');
              $table_describes = DB::table('information_schema.columns')
              ->where('table_name', '=', $tabla)
              ->get($selects);
              foreach ($table_describes as $k => $v) {
              if (($kt = array_search($v, $table_describes)) !== false and $k != $kt) {
              unset($table_describes[$kt]);
              }
              } */

            /**
             * Get the model information
             */
            $columns = [
                "model" => get_class($modeloE),
                "tabla" => $tabla,
                "id" => $modeloE->getKeyName(),
                "name" => CrudGenerator::getNameAttribute($modeloE),
                "attributes" => $modeloE->getConnection()->getSchemaBuilder()->getColumnListing($tabla),
            ];
            $schema = \DB::getDoctrineSchemaManager();
            $columns["foreign"] = $schema->listTableForeignKeys($tabla);
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
                    "notNull" => $column->getNotnull(),
                    "doctrineObject" => $column,
                ];
            }
            foreach ($schema->listTableIndexes($tabla) as $index) {
                foreach ($index->getColumns() as $column) {
                    $columns['campos'][$column]['isIndex'] = true;
                    $columns['campos'][$column]['isUnique'] = $index->isUnique();
                    $columns['campos'][$column]['isPrimary'] = $index->isPrimary();
                    $columns['campos'][$column]['doctrineIndex'] = $index;
                }
            }

            $class = new ReflectionClass($modeloClass);
            $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
            $relations = [];
            foreach ($methods as $method) {
                $auxColumn = [];
                if ($method->getNumberOfParameters() == 0) {
                    $methodReturn = CrudGenerator::checkDocBlock($method->getDocComment(), '@return');
                    if ($methodReturn == "" || $methodReturn == "Illuminate\Database\Eloquent\Relations\Relation" || $methodReturn == "Illuminate\Database\Eloquent\Relations\Model")
                        if (is_a($modeloE->{$method->name}(), "Illuminate\Database\Eloquent\Relations\Relation")) {
                            $responseMethod = new ReflectionMethod($modeloClass, $method->name);
                            $relations[] = $method->name;
                            $related = $modeloE->{$method->name}()->getRelated();
                            $datosQueryAux = CrudGenerator::splitQueryNames($modeloE->{$method->name}()->getQuery()->toSql());
                            $tipoRelacion = class_basename(get_class($modeloE->{$method->name}()));
                            switch ($tipoRelacion) {
                                case 'BelongsToMany':
                                    $datosQuery = [
                                        'tablaIntermedia' => $datosQueryAux[1],
                                        'intermediaRelatedId' => $datosQueryAux[5],
                                        'relatedId' => $datosQueryAux[3],
                                        'intermediaModelId' => $datosQueryAux[7],
                                        'modelId' => substr($modeloE->{$method->name}()->getQualifiedParentKeyName(), stripos($modeloE->{$method->name}()->getQualifiedParentKeyName(), ".") + 1),
                                        'foreignId' => $modeloE->{$method->name}()->getForeignKey(),
                                            //'ownerId' => $modeloE->{$method->name}()->getQualifiedRelatedPivotKeyName(),
                                    ];
                                    break;
                                case 'BelongsTo':
                                    $datosQuery = [
                                        'relatedId' => $datosQueryAux[2],
                                        'modelRelatedId' => $modeloE->{$method->name}()->getForeignKey(),
                                    ];
                                    $auxColumn = $columns['campos'][$datosQuery['modelRelatedId']];
                                    unset($columns['campos'][$datosQuery['modelRelatedId']]);
                                    break;
                                case 'HasMany':
                                    $datosQuery = [
                                        'foreignId' => $modeloE->{$method->name}()->getForeignKey(),
                                        'relatedId' => $datosQueryAux[2],
                                        'modelId' => substr($modeloE->{$method->name}()->getQualifiedParentKeyName(), stripos($modeloE->{$method->name}()->getQualifiedParentKeyName(), ".") + 1),
                                    ];
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

            /**
             * Build the config
             */
            $config = [
                "modelo" => $modeloClass,
                "tabla" => $tabla,
                "nombre" => $columns['name'],
                "id" => $columns['id'],
                "url" => "Sirgrimorum_CrudAdministrator",
                "botones" => trans("crudgenerator::admin.layout.labels.create"),
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
                    if (\Lang::has($transFile . ".selects." . $campo) && is_array(trans($transFile . ".selects." . $campo))) {
                        $configCampos[$campo] = [
                            'tipo' => 'select',
                            'label' => $campo,
                            'opciones' => trans($transFile . ".selects." . $campo),
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
                                $configCampos[$campo] = [
                                    'tipo' => 'datetime',
                                    'label' => $campo,
                                    'placeholder' => "",
                                    "format" => trans("crudgenerator::admin.formats." . $datos['type']),
                                ];
                                if ($datos['type'] == 'date') {
                                    $configCampos[$campo]['tipo'] = 'date';
                                }
                                if ($campo == 'created_at' || $campo == 'updated_at') {
                                    $configCampos[$campo]['nodb'] = "nodb";
                                    //$configCampos[$campo]['readonly'] = "readonly";
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
                    if ($datos['notNull']) {
                        $rulesStr .=$prefixRules . 'required';
                        $prefixRules = "|";
                    }
                    if ($datos['isUnique']) {
                        $rulesStr .=$prefixRules . 'unique:' . $tabla . ',' . $campo;
                        $prefixRules = "|";
                    }
                    if ($datos['default']) {
                        $configCampos[$campo]['valor'] = $datos['default'];
                    }
                    if ($transFile !== false) {
                        if (\Lang::has($transFile . ".labels." . $campo)) {
                            $configCampos[$campo]['label'] = trans($transFile . ".labels." . $campo);
                        }
                        if (\Lang::has($transFile . ".placeholders." . $campo)) {
                            $configCampos[$campo]['placeholder'] = trans($transFile . ".placeholders." . $campo);
                        }
                        if (\Lang::has($transFile . ".descriptions." . $campo)) {
                            $configCampos[$campo]['description'] = trans($transFile . ".descriptions." . $campo);
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
                                $rulesStr .=$prefixRules . 'unique:' . $tabla . ',' . $campo;
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
                            $rulesStr .=$prefixRules . 'exists:' . $datos['relation']['related']['tabla'] . ',' . $datos['relation']['datosQuery']['relatedId'];
                            $prefixRules = "|";
                            if ($datos['type'] == 'HasMany') {
                                //$configCampos[$campo]['nodb'] = "nodb";
                                //$configCampos[$campo]['readonly'] = "readonly";
                            }
                            break;
                    }
                    if ($transFile !== false) {
                        if (\Lang::has($transFile . ".labels." . $campo)) {
                            $configCampos[$campo]['label'] = trans($transFile . ".labels." . $campo);
                        }
                        if (\Lang::has($transFile . ".placeholders." . $campo)) {
                            $configCampos[$campo]['placeholder'] = trans($transFile . ".placeholders." . $campo);
                        }
                        if (\Lang::has($transFile . ".descriptions." . $campo)) {
                            $configCampos[$campo]['description'] = trans($transFile . ".descriptions." . $campo);
                        }
                    }
                    if ($rulesStr != "") {
                        $rules[$campo] = $rulesStr;
                    }
                }
            }
            $config["campos"] = $configCampos;
            $config['rules'] = $rules;
        } else {
            $config = $this->translateConfig($config);
        }
        return $config;
    }

    public static function splitQueryNames($query) {
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

    public static function getTypeByName($name, $options) {
        if (!is_array($options)) {
            $options = config("sirgrimorum.crudgenerator.probable_" . $options);
        }

        return (in_array($name, $options));
    }

    public static function getNameAttribute($model) {
        $attributes = $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());
        $model->getKeyName();
        $compares = config("sirgrimorum.crudgenerator.probable_name");
        foreach ($compares as $compare) {
            if (in_array($compare, $attributes)) {
                return $compare;
            }
        }
        return $model->getKeyName();
    }

    public static function translateConfig($array) {
        $result = [];
        foreach ($array as $key => $item) {
            if (gettype($item) != "Closure Object") {
                if (is_array($item)) {
                    $result[$key] = CrudGenerator::translateConfig($item);
                } elseif (is_string($item)) {
                    if (str_contains($item, config("sirgrimorum.crudgenerator.trans_prefix"))) {
                        if (($left = (stripos($item, config("sirgrimorum.crudgenerator.trans_prefix")))) !== false) {
                            while ($left !== false) {
                                //echo "<pre>" . print_r($item, true) . "</pre>";
                                if (($right = stripos($item, '__', $left + strlen(config("sirgrimorum.crudgenerator.trans_prefix")))) === false) {
                                    $right = strlen($item);
                                }
                                $piece = trans(substr($item, $left + strlen(config("sirgrimorum.crudgenerator.trans_prefix")), $right - ($left + strlen(config("sirgrimorum.crudgenerator.trans_prefix")))));
                                if (is_string($piece)) {
                                    if ($right <= strlen($item)) {
                                        $item = substr($item, 0, $left) . $piece . substr($item, $right + 2);
                                    } else {
                                        $item = substr($item, 0, $left) . $piece;
                                    }
                                    //echo "<pre>" . print_r(['prefix' => config("sirgrimorum.crudgenerator.trans_prefix"), 'lenprefix' => strlen(config("sirgrimorum.crudgenerator.trans_prefix")), 'left' => $left, 'rigth' => $right, 'piece' => $piece, 'lenpiece' => strlen($piece), 'csss' => $item], true) . "</pre>";
                                    $left = (stripos($item, config("sirgrimorum.crudgenerator.trans_prefix")));
                                } else {
                                    //echo "<pre>" . print_r($piece, true) . "</pre>";
                                    $item = $piece;
                                    $left = false;
                                }
                            }
                        }
                        $result[$key] = $item;
                    } else {
                        $result[$key] = $item;
                    }
                } else {
                    $result[$key] = $item;
                }
            } else {
                $result[$key] = $item;
            }
        }
        return $result;
    }

    public static function hasTipo($array, $tipo) {
        foreach ($array['campos'] as $campo => $configCampo) {
            if (strtolower($configCampo['tipo']) == strtolower($tipo)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generate create view for a model
     * @param array $config Configuration array
     * @return HTML Create form
     */
    public static function create($config) {
        $config = CrudGenerator::translateConfig($config);
        if (isset($config['render'])) {
            foreach ($config['relaciones'] as $clave => $relacion) {
                if (!is_array($relacion['todos'])) {
                    $lista = array("-" => "-");
                    $modeloM = ucfirst($relacion["modelo"]);
                    foreach ($modeloM::all() as $elemento) {
                        if (is_array($relacion['nombre'])) {
                            $strNombre = "";
                            $preNombre = "";
                            foreach ($relacion['nombre'] as $nombreRelacion) {
                                $strNombre .= $preNombre . $elemento->{$nombreRelacion};
                                $preNombre = ", ";
                            }
                            $lista[$elemento->{$relacion['id']}] = $strNombre;
                        } else {
                            $lista[$elemento->{$relacion['id']}] = $elemento->{$relacion['nombre']};
                        }
                    }
                    $config['relaciones'][$clave]['todos'] = $lista;
                }
            }
        } else {
            foreach ($config['campos'] as $clave => $relacion) {
                if ($relacion['tipo'] == "relationship" || $relacion['tipo'] == "relationships") {
                    if (!is_array($config['campos'][$clave]['todos'])) {
                        if ($relacion['tipo'] == "relationship") {
                            $lista = ["-" => "-"];
                        } else {
                            $lista = [];
                        }
                        if ($config['campos'][$clave]['todos'] == "") {
                            $modeloM = ucfirst($relacion["modelo"]);
                            $modelosM = $modeloM::all();
                        } else {
                            $modelosM = $config['campos'][$clave]['todos'];
                        }

                        foreach ($modelosM as $elemento) {
                            if (is_array($relacion['campo'])) {
                                $strNombre = "";
                                $preNombre = "";
                                foreach ($relacion['campo'] as $nombreRelacion) {
                                    $strNombre .= $preNombre . $elemento->{$nombreRelacion};
                                    $preNombre = ", ";
                                }
                                $lista[$elemento->{$relacion['id']}] = $strNombre;
                            } else {
                                $lista[$elemento->{$relacion['id']}] = $elemento->{$relacion['campo']};
                            }
                        }
                        $config['campos'][$clave]['todos'] = $lista;
                    }
                }
            }
        }
        if ($config['url'] == "Sirgrimorum_CrudAdministrator") {
            $config['url'] = route("sirgrimorum_modelo::store", ["localecode" => \App::getLocale(), "modelo" => strtolower(class_basename($config["modelo"]))]);
            $config['botones'] = trans("crudgenerator::article.labels.create");
        }
        $view = View::make('sirgrimorum::crudgen.create', [
                    'config' => $config,
                    'tieneHtml' => $this->hasTipo($config, 'html'),
                    'tieneDate' => ($this->hasTipo($config, 'date') || $this->hasTipo($config, 'datetime')),
                    'tieneSlider' => $this->hasTipo($config, 'slider')
        ]);
        return $view->render();
    }

    /**
     * Generate view to show a model
     * @param array $config Configuration array
     * @param integer $id Key of the object
     * @param Model $registro Optional The Object
     * @return HTML the Object
     */
    public static function show($config, $id = null, $registro = null) {
        $config = CrudGenerator::translateConfig($config);
        if ($registro == null) {
            $modeloM = ucfirst($config['modelo']);
            if ($id == null) {
                $registro = $modeloM::first();
            } else {
                $registro = $modeloM::find($id);
            }
        }

        $view = View::make('sirgrimorum::crudgen.show', array('config' => $config, 'registro' => $registro));
        return $view->render();
    }

    /**
     * Generate de edit view of a model
     * @param array $config Configuration array
     * @param integer $id Key of the object
     * @param Model $registro Optional The object
     * @return HTML Edit form
     */
    public static function edit($config, $id = null, $registro = null) {
        $config = CrudGenerator::translateConfig($config);
        if (isset($config['render'])) {
            foreach ($config['relaciones'] as $clave => $relacion) {
                $lista = array("-" => "-");
                $modeloM = ucfirst($relacion["modelo"]);
                foreach ($modeloM::all() as $elemento) {
                    $lista[$elemento->{$relacion['id']}] = $elemento->{$relacion['nombre']};
                }
                $config['relaciones'][$clave]['todos'] = $lista;
            }
        } else {
            foreach ($config['campos'] as $clave => $relacion) {
                if ($relacion['tipo'] == "relationship" || $relacion['tipo'] == "relationships") {
                    if (!is_array($config['campos'][$clave]['todos'])) {
                        if ($relacion['tipo'] == "relationship") {
                            $lista = array("-" => "-");
                        }
                        if ($config['campos'][$clave]['todos'] == "") {
                            $modeloM = ucfirst($relacion["modelo"]);
                            $modelosM = $modeloM::all();
                        } else {
                            $modelosM = $config['campos'][$clave]['todos'];
                        }
                        foreach ($modelosM as $elemento) {
                            if (is_array($relacion['campo'])) {
                                $strNombre = "";
                                $preNombre = "";
                                foreach ($relacion['campo'] as $nombreRelacion) {
                                    $strNombre .= $preNombre . $elemento->{$nombreRelacion};
                                    $preNombre = ", ";
                                }
                                $lista[$elemento->{$relacion['id']}] = $strNombre;
                            } else {
                                $lista[$elemento->{$relacion['id']}] = $elemento->{$relacion['campo']};
                            }
                        }
                        $config['campos'][$clave]['todos'] = $lista;
                    }
                }
            }
        }
        if ($registro == null) {
            $modeloM = ucfirst($config['modelo']);
            if ($id == null) {
                $registro = $modeloM::first();
            } else {
                $registro = $modeloM::find($id);
            }
        }
        if ($config['url'] == "Sirgrimorum_CrudAdministrator") {
            $config['url'] = route("sirgrimorum_modelo::update", ["localecode" => \App::getLocale(), "modelo" => strtolower(class_basename($config["modelo"])), "registro" => $registro->id]);
            $config['botones'] = trans("crudgenerator::article.labels.edit");
        }
        $view = View::make('sirgrimorum::crudgen.edit', [
                    'config' => $config,
                    'registro' => $registro,
                    'tieneHtml' => $this->hasTipo($config, 'html'),
                    'tieneDate' => ($this->hasTipo($config, 'date') || $this->hasTipo($config, 'datetime')),
                    'tieneSlider' => $this->hasTipo($config, 'slider')
        ]);
        return $view->render();
    }

    /**
     * Generate a list of objects of a model
     * @param array $config Configuration array
     * @param Model() $registros Optional Array of objects to show
     * @return HTML Table with the objects
     */
    public static function lists($config, $registros = null) {
        $config = CrudGenerator::translateConfig($config);
        if ($registros == null) {
            $modeloM = $config['modelo'];
            
            $registros = $modeloM::all();
            //$registros = $modeloM::all();
        }
        $view = View::make('sirgrimorum::crudgen.list', array('config' => $config, 'registros' => $registros));
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
            $row = $this->registry_array($config, $registro);
            $return[] = $row;
        }
        return $return;
    }

    /**
     * Generate an objects of a model in array format
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
                if (CrudController::hasRelation($value, $datos['modelo'])) {
                    if (array_key_exists('enlace', $datos)) {
                        $celda .= '<a href="' . str_replace([":modelId", ":modelName"], [$value->{$datos['modelo']}->{$datos['id']}, $value->{$datos['modelo']}->{$datos['nombre']}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$value->{$datos['modelo']}->{$datos['id']}, $value->{$datos['modelo']}->{$datos['nombre']}], $datos['enlace'])) . '">';
                    }
                    if (is_array($datos['campo'])) {
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
                } elseif (CrudController::hasRelation($value, $columna)) {
                    if (array_key_exists('enlace', $datos)) {
                        $celda .= '<a href="' . str_replace([":modelId", ":modelName"], [$value->{$columna}->{$datos['id']}, $value->{$columna}->{$datos['nombre']}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$value->{$columna}->{$datos['id']}, $value->{$columna}->{$datos['nombre']}], $datos['enlace'])) . '">';
                    }
                    if (is_array($datos['campo'])) {
                        $prefijoCampo = "";
                        foreach ($datos['campo'] as $campo) {
                            $celda .= $prefijoCampo . $value->{$columna}->{$campo};
                            $prefijoCampo = ", ";
                        }
                    } else {
                        $celda .=$value->{$columna}->{$datos['campo']};
                    }
                    if (array_key_exists('enlace', $datos)) {
                        $celda .='</a>';
                    }
                } else {
                    $celda .='-';
                }
            } elseif ($datos['tipo'] == "relationships") {
                if (count($value->{$datos['modelo']}()->get()) > 0) {
                    $prefijoBloque = "";
                    foreach ($value->{$datos['modelo']}()->get() as $sub) {
                        if (array_key_exists('enlace', $datos)) {
                            $celda.= $prefijoBloque . '<a href="' . str_replace([":modelId", ":modelName"], [$sub->{$datos['id']}, $sub->{$datos['nombre']}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$sub->{$datos['id']}, $sub->{$datos['nombre']}], $datos['enlace'])) . '">';
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
                            $celda.= $prefijoBloque . '<a href="' . str_replace([":modelId", ":modelName"], [$sub->{$datos['id']}, $sub->{$datos['nombre']}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$sub->{$datos['id']}, $sub->{$datos['nombre']}], $datos['enlace'])) . '">';
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
                    $celda .= '<a href="' . str_replace([":modelId", ":modelName"], [$value->{$identificador}, $value->{$nombre}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$value->{$identificador}, $value->{$nombre}], $datos['enlace'])) . '">';
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
                    $celda .='</a>';
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
