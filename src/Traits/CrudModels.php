<?php

namespace Sirgrimorum\CrudGenerator\Traits;

use Carbon\Carbon;
use Error;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Illuminate\Support\Str;
use Sirgrimorum\CrudGenerator\DynamicCompare;

trait CrudModels
{

    /**
     * Evaluate the "permissions" callbacks in the configuration array
     * @param array $config The configuration array
     * @param int $registro Optional The id of the registry
     * @param string $action Optional The actual action
     * @return boolean if the user has or not permission
     */
    public static function checkPermission(array $config, $registro = 0, $action = "")
    {
        if ($action == "") {
            if (stripos(request()->route()->getName(), "::") !== false) {
                $action = substr(request()->route()->getName(), stripos(request()->route()->getName(), "::") + 2);
            } else {
                $action = substr(request()->route()->getName(), strripos(request()->route()->getName(), ".") + 1);
            }
        }
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
     * Get the list of models to use from a config array
     * 
     * @param array $config Configuration array
     * @param Collection|Array|Builder $registros Optional Objects to show if null it will look for the 'query' field in config, if not found, will take all the records of model.
     * 
     * @return Collection|Builder The list of registros
     */
    public static function getListFromConfig($config, $registros = null)
    {
        if ($registros == null) {
            if (isset($config['query'])) {
                if (is_callable($config['query'])) {
                    $auxQuery = $config['query']();
                } else {
                    $auxQuery = $config['query'];
                }
                if ($auxQuery instanceof Builder || $auxQuery instanceof Collection) {
                    $registros = $auxQuery;
                } elseif (is_array($auxQuery)) {
                    $registros = collect($auxQuery);
                } else {
                    $modeloM = ucfirst($config['modelo']);
                    $registros = $modeloM::whereRaw("(1=1)");
                }
            } else {
                $modeloM = ucfirst($config['modelo']);
                $registros = $modeloM::whereRaw("(1=1)");
            }
        } elseif (is_array($registros)) {
            $registros = collect($registros);
        }
        return $registros;
    }

    /**
     * Generate a list of objects of a model in array format
     * Could returns an array with 2 arrays in it:
     * Complete, with value, label and data for each field in position 0
     * and Simple, only with value per field at position 1
     *
     * @param array $config Configuration array
     * @param Collection|Array|Builder $registros Optional Objects to show if null it will look for the 'query' field in config, if not found, will take all the records of model. Use start and length of request to paginate
     * @param boolean|string $solo Optional if false or 'todo' will return the complete and simple array, if 'todo' will also return numTotal y numFiltrados and a special camp '_id' with '$request->tableid__id|nombre', if 'simple' only the simple one, if 'complete' only the complete one
     * @param Request $request Optional The current request
     * @return array with the objects in the config format
     */
    public static function lists_array($config, $registros = null, $solo = 'complete', Request $request = null)
    {
        //$config = CrudGenerator::translateConfig($config);
        if ($request == null) {
            $request = request();
        }
        $registros = CrudGenerator::getListFromConfig($config, $registros);
        if ($registros instanceof Builder) {
            $numTotal = DB::query()->fromSub($registros, "conteo")->count();
            $registros = CrudGenerator::filterWithQuery($registros, $config);
            $numFiltrados = DB::query()->fromSub($registros, "conteo")->count();
            if (isset($request)) {
                if ($request->has('start')) {
                    $start = $request->input('start');
                    $registros = $registros->offset($start);
                }
                if ($request->has('length')) {
                    $limit = $request->input('length');
                    $registros = $registros->limit($limit);
                }
            }
            $registros = $registros->get();
        } elseif ($registros instanceof Collection) {
            $numTotal = count($registros);
            $registros = CrudGenerator::filterWithQuery($registros, $config);
            $numFiltrados = count($registros);
            if (isset($request)) {
                if ($request->has('start')) {
                    $start = $request->input('start');
                    if ($request->has('length')) {
                        $limit = $request->input('length');
                        $registros = $registros->slice($start, $limit);
                    } else {
                        $registros = $registros->slice($start);
                    }
                } elseif ($request->has('length')) {
                    $limit = $request->input('length');
                    $registros = $registros->slice(0, $limit);
                }
            }
        } else {
            return false;
        }
        $return = [];
        $returnSimple = [];
        $index = 0;
        $_tablaId = $request->get('_tablaId', $config['tabla']);
        foreach ($registros as $registro) {
            if ($solo == 'simple') {
                $returnSimple[] = CrudGenerator::registry_array($config, $registro, $solo);
            } elseif ($solo == 'complete') {
                $return[] = CrudGenerator::registry_array($config, $registro, $solo);
            } else {
                $index++;
                list($row, $rowSimple) = CrudGenerator::registry_array($config, $registro, $solo);
                $return[] = $row;
                $_id = e(CrudGenerator::getJustValue('id', $row, $config, CrudGenerator::getJustValue('id', $rowSimple, $config, $index)));
                $_nombre = e(CrudGenerator::getJustValue('nombre', $row, $config, CrudGenerator::getJustValue('nombre', $rowSimple, $config, $index)));
                $rowSimple['_id'] = "{$_tablaId}__{$_id}|{$_nombre}";
                if (strtolower($request->get('_return', '')) == 'datatablesjson') {
                    $rowSimple['_id'] = $rowSimple['id'];
                    $rowSimple['id'] = "{$_tablaId}__{$_id}|{$_nombre}";
                } else {
                    $rowSimple['_id'] = "{$_tablaId}__{$_id}|{$_nombre}";
                }
                $returnSimple[] = $rowSimple;
            }
        }
        if ($solo == 'simple') {
            return $returnSimple;
        } elseif ($solo == 'complete') {
            return $return;
        } elseif ($solo == 'todo') {
            return [$return, $returnSimple, $numTotal, $numFiltrados];
        } else {
            return [$return, $returnSimple];
        }
    }

    /**
     * Generate an object of a model in array format.
     * Returns an array with 2 arrays in it:
     * Complete, with value, label and data for each field in position 0
     * and Simple, only with value per field at position 1
     *
     * @param array $config Configuration array
     * @param Model|int $registro Optional object or id of object (will look for it in config['query'] or model) to show if null or 0, it will look for config['query] or the model and get the first record
     * @param boolean|string $solo Optional if false, will return the complete an simple array, if 'simple' only the simple one, if 'complete' only the complete one
     * @return array with the attributes in the config format
     */
    public static function registry_array($config, $registro = null, $solo = false)
    {
        $modeloM = ucfirst($config['modelo']);
        if ($registro != null && is_object($registro)) {
            $value = $registro;
            $registro = $value->{$config['id']};
        } else {
            $query = CrudGenerator::getListFromConfig($config);
            if ($registro == null || $registro == 0) {
                $value = $query->first();
                $registro = $value->{$config['id']};
            } else {
                if ($query instanceof Collection) {
                    $value = $query->firstWhere($config['id'], $registro);
                } else {
                    $value = $query->where($config['tabla'] . "." . $config['id'], "=", $registro)->first();
                }
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
        $tablaid = $tabla . "_" . Str::random(5);
        if (isset($config['relaciones'])) {
            $relaciones = $config['relaciones'];
        }
        $identificador = $config['id'];
        $nombre = $config['nombre'];

        $row = [
            $value->getKeyName() => $value->getKey()
        ];
        $rowSimple = [
            $value->getKeyName() => $value->getKey()
        ];
        foreach ($campos as $columna => $datos) {
            $celda = CrudGenerator::field_array($value, $columna, $config, $datos);
            $row[$columna] = $celda;
            $rowSimple[$columna] = $celda['value'];
        }
        if (is_array($botones)) {
            $celda = [];
            foreach ($botones as $boton) {
                if (is_array($boton)) {
                    $subBoton = [];
                    foreach ($boton as $keySubBoton => $valueSubBoton) {
                        if (is_string($valueSubBoton)) {
                            $subBoton[$keySubBoton] = str_replace([":modelId", ":modelName"], [$value->{$identificador}, $value->{$nombre}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$value->{$identificador}, $value->{$nombre}], $valueSubBoton));
                        } else {
                            $subBoton[$keySubBoton] = $valueSubBoton;
                        }
                    }
                } elseif (is_string($boton)) {
                    $celda[] = str_replace([":modelId", ":modelName"], [$value->{$identificador}, $value->{$nombre}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$value->{$identificador}, $value->{$nombre}], $boton));
                } else {
                    $celda[] = $boton;
                }
            }
            $row["botones"] = $celda;
        } elseif (is_string($botones)) {
            $celda = str_replace([":modelId", ":modelName"], [$value->{$identificador}, $value->{$nombre}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$value->{$identificador}, $value->{$nombre}], $botones));
            $row["botones"] = $celda;
        } else {
            $row["botones"] = $botones;
        }
        if ($solo == 'simple') {
            return $rowSimple;
        } elseif ($solo == 'complete') {
            return $row;
        } else {
            return [$row, $rowSimple];
        }
    }

    /**
     * Generate an array of a field using a configuration array.
     * Returns an array with at least 3 elements:
     *      data: with the detailed value
     *      label: with the translated label of the field
     *      value: with the formated value of the field
     *
     * @param Model $value The object
     * @param string $columna The field to show
     * @param array $config Optional the configuration array for the model
     * @param array $datos Optional The configuration array for the field
     * @return array with the values in the config format
     */
    public static function field_array($value, $columna, $config = "", $datos = "")
    {
        $modelo = strtolower(class_basename(get_class($value)));
        if ($config == "") {
            $config = CrudGenerator::getConfigWithParametros($modelo);
        }
        if ($datos == "" && $config != "") {
            if (isset($config['campos'][$columna])) {
                if (is_array($config['campos'][$columna])) {
                    $datos = $config['campos'][$columna];
                } else {
                    $celda = [
                        "data" => $value->{$columna},
                        "label" => $columna,
                        "value" => CrudGenerator::translateDato($value->{$columna})
                    ];
                    return $celda;
                }
            } else {
                $celda = [
                    "data" => $value->{$columna},
                    "label" => $columna,
                    "value" => CrudGenerator::translateDato($value->{$columna})
                ];
                return $celda;
            }
        }
        $celda = [];
        $celdaData = [];
        $auxcelda = "";
        if (isset($datos["pre"])) {
            $celdaData["pre"] = $datos["pre"];
        }
        if ($datos['tipo'] == "relationship") {
            if (CrudGenerator::hasRelation($value, $columna)) {
                if (isset($value->{$columna})) {
                    if (array_key_exists('enlace', $datos)) {
                        $auxcelda = '<a href = "' . CrudGenerator::getNombreDeLista($value->{$columna}, $datos['enlace']) . '">';
                    } else {
                        $auxcelda = '';
                    }
                    $celda['data'] = CrudGenerator::getNombreDeLista($value->{$columna}, $datos['campo']);
                    $celda['label'] = $datos['label'];
                    $auxcelda .= $celda['data'];
                    if (array_key_exists('enlace', $datos)) {
                        $auxcelda .= '</a>';
                    }
                    $celda['value'] = $auxcelda;
                } else {
                    $celda = '-';
                }
            } else {
                $celda = '-';
            }
        } elseif ($datos['tipo'] == "relationships") {
            if (CrudGenerator::hasRelation($value, $columna)) {
                $celda = [];
                $auxcelda2 = "";
                $prefijo = "<ul><li>";
                foreach ($value->{$columna}()->get() as $sub) {
                    $auxcelda = "";
                    if (array_key_exists('enlace', $datos)) {
                        $auxcelda2 .= $prefijo . '<a href = "' .  CrudGenerator::getNombreDeLista($sub, $datos['enlace']) . '">';
                    } else {
                        $auxcelda2 .= $prefijo;
                    }
                    $auxcelda = CrudGenerator::getNombreDeLista($sub, $datos['campo']);
                    $auxcelda2 .= $auxcelda;
                    if (array_key_exists('enlace', $datos)) {
                        $auxcelda2 .= '</a>';
                    }
                    $auxcelda2 .= "</li>";
                    $prefijo = "<li>";
                    $celda[$sub->getKey()] = $auxcelda;
                }
                if ($auxcelda2 != "") {
                    $auxcelda2 .= "</ul>";
                }
                $celda = [
                    "data" => $celda,
                    "label" => $datos['label'],
                    "value" => $auxcelda2
                ];
            } else {
                $celda = '-';
            }
        } elseif ($datos['tipo'] == "relationshipssel") {
            if (CrudGenerator::hasRelation($value, $columna)) {
                $celda = [];
                $auxcelda3 = "";
                $prefijo = "<ul><li>";
                $htmlShow = "-";
                if ($value->{$columna}()->count() > 0) {
                    $htmlShow = '<dl class="row border-top border-secondary">';
                    foreach ($value->{$columna}()->get() as $sub) {
                        $celda[$sub->getKey()] = [];
                        $auxcelda = "";
                        $htmlShow .= '<dt class="col-sm-3 border-bottom border-secondary pt-2">';
                        if (array_key_exists('enlace', $datos)) {
                            $auxcelda = '<a href = "' . CrudGenerator::getNombreDeLista($sub, $datos['enlace']) . '">';
                            $htmlShow .= $auxcelda;
                        }
                        $auxcelda2 = CrudGenerator::getNombreDeLista($sub, $datos['campo']);
                        $htmlShow .= $auxcelda2;
                        $auxcelda .= $auxcelda2;
                        if (array_key_exists('enlace', $datos)) {
                            $auxcelda .= '</a>';
                            $htmlShow .= '</a>';
                        }
                        $auxcelda3 .= $prefijo . $auxcelda;
                        $auxcelda4 = "";
                        $auxcelda5 = "";
                        $prefijo2 = "<ul><li>";
                        $htmlShow .= '</dt>';
                        if (array_key_exists('columnas', $datos)) {
                            if (is_array($datos['columnas'])) {
                                if (is_object($sub->pivot)) {
                                    $celda[$sub->getKey()]['data'] = [];
                                    $htmlShow .= '<dd class="col-sm-9 border-bottom border-secondary mb-0 pb-2">' .
                                        '<ul class="mb-0">';
                                    foreach ($datos['columnas'] as $infoPivote) {
                                        if ($infoPivote['tipo'] != "hidden" && $infoPivote['tipo'] != "label") {
                                            $celda[$sub->getKey()]['data'][$infoPivote['campo']] = ['label' => $infoPivote['label']];
                                            if ($infoPivote['tipo'] == "number" && isset($infoPivote['format'])) {
                                                $celda[$sub->getKey()]['data'][$infoPivote['campo']]['value'] = number_format($sub->pivot->{$infoPivote['campo']}, $infoPivote['format'][0], $infoPivote['format'][1], $infoPivote['format'][2]);
                                            } elseif ($infoPivote['tipo'] == "select" && isset($infoPivote['opciones'])) {
                                                $celda[$sub->getKey()]['data'][$infoPivote['campo']]['value'] = $infoPivote['opciones'][$sub->pivot->{$infoPivote['campo']}];
                                            } else {
                                                $celda[$sub->getKey()]['data'][$infoPivote['campo']]['value'] = $sub->pivot->{$infoPivote['campo']} . ', ';
                                            }
                                            $auxcelda4 .= $prefijo2 . $celda[$sub->getKey()]['data'][$infoPivote['campo']]['value'] . "</li>";
                                            $prefijo2 = "<li>";
                                            $htmlShow .= '<li>' .
                                                $celda[$sub->getKey()]['data'][$infoPivote['campo']]['value'] .
                                                '</li>';
                                        } elseif ($infoPivote['tipo'] == "label") {
                                            if (isset($infoPivote['campo'])) {
                                                $auxcelda5 = CrudGenerator::getNombreDeLista($sub, $infoPivote['campo']);
                                            } else {
                                                $auxcelda5 = $infoPivote['label'];
                                            }
                                        }
                                    }
                                    $htmlShow .= '</ul>' .
                                        '</dd>';
                                }
                            }
                        }
                        if ($auxcelda4 != "") {
                            $auxcelda4 .= "</ul>";
                        }
                        $auxcelda3 .= $auxcelda4 . "</li>";
                        $prefijo = "<li>";
                        $celda[$sub->getKey()]['name'] = $auxcelda2;
                        $celda[$sub->getKey()]['value'] = $auxcelda;
                        $celda[$sub->getKey()]['label'] = $auxcelda5;
                    }
                    if ($auxcelda3 != "") {
                        $auxcelda3 .= "</ul>";
                    }
                    $htmlShow .= '</dl>';
                }
                $celda = [
                    "data" => $celda,
                    "label" => $datos['label'],
                    "value" => $auxcelda3,
                    "html_show" => $htmlShow,
                ];
            } else {
                $celda = '-';
            }
        } elseif ($datos['tipo'] == "select") {
            if (array_key_exists($value->{$columna}, $datos['opciones'])) {
                $celda['data'] = $datos['opciones'][$value->{$columna}];
                $celda['label'] = $datos['label'];
                $celda['value'] = $datos['opciones'][$value->{$columna}];
            } else {
                $celda = '-';
            }
        } elseif ($datos['tipo'] == "checkbox" || $datos['tipo'] == "radio") {
            $auxdata = $value->{$columna};
            if (is_array($datos['value'])) {
                if (array_key_exists($value->{$columna}, $datos['value'])) {
                    $auxcelda = $datos['value'][$value->{$columna}];
                    $auxcelda = $datos['value'][$value->{$columna}];
                    if (is_array($auxcelda)) {
                        $auxcelda = Arr::get($auxcelda, 'label', Arr::get($auxcelda, 'description', Arr::get($auxcelda, 'help', $value->{$columna})));
                    }
                    $celda['data_labels'][$value->{$columna}] = $datos['value'][$value->{$columna}];
                } elseif (strpos($value->{$columna}, Arr::get($datos, 'glue', '_')) !== false) {
                    $auxdata = explode(Arr::get($datos, 'glue', '_'), $value->{$columna});
                    $auxdataLabels = [];
                    $auxcelda = "";
                    $precelda = "";
                    $auxhtml = "";
                    foreach ($auxdata as $datico) {
                        if (array_key_exists($datico, $datos['value'])) {
                            $auxDatico = $datos['value'][$datico];
                            if (is_array($auxDatico)) {
                                $auxDatico = Arr::get($auxDatico, 'label', Arr::get($auxDatico, 'description', Arr::get($auxDatico, 'help', $datico)));
                            }
                            $auxdataLabels[$datico] = $auxDatico;
                            $auxcelda .= $precelda . $auxDatico;
                            $precelda = ", ";
                            $auxhtml .= "<li>{$auxDatico}</li>";
                        }
                    }
                    if ($auxhtml != "") {
                        $celda['html'] = "<ul>{$auxhtml}</ul>";
                        $celda['html_cell'] = '<div style="max-height:10em;min-width:22em;white-space:normal;overflow-y:scroll;">' . $celda['html'] . '</div>';
                    }
                    $celda['data_labels'] = $auxdataLabels;
                } else {
                    if ($value->{$columna} === true) {
                        $auxcelda = trans('crudgenerator::admin.layout.labels.yes');
                    } else {
                        $auxcelda = trans('crudgenerator::admin.layout.labels.no');
                    }
                }
            } else {
                if ($datos['value'] == $value->{$columna} && $value->{$columna} == true) {
                    $auxcelda = trans('crudgenerator::admin.layout.labels.yes');
                } elseif ($value->{$columna} == $datos['value']) {
                    $auxcelda = $datos['value'];
                } elseif ($value->{$columna} == true) {
                    $auxcelda = $datos['value'];
                } else {
                    $auxcelda = trans('crudgenerator::admin.layout.labels.no');
                }
            }
            $celda['data'] = $auxdata;
            $celda['label'] = $datos['label'];
            $celda['value'] = $auxcelda;
        } elseif ($datos['tipo'] == "function") {
            if (isset($datos['format'])) {
                if (is_array($datos['format'])) {
                    $auxcelda = number_format($value->{$columna}(), $datos['format'][0], $datos['format'][1], $datos['format'][2]);
                } else {
                    $auxcelda = number_format($value->{$columna}());
                }
            } else {
                $auxcelda = $value->{$columna}();
            }
            $celda['data'] = $auxcelda;
            $celda['label'] = $datos['label'];
            $celda['value'] = $auxcelda;
        } elseif ($datos['tipo'] == "date" || $datos['tipo'] == "datetime" || $datos['tipo'] == "time") {
            $format = "Y-m-d H:i:s";
            if ($datos['tipo'] == "date") {
                $format = "Y-m-d";
            } elseif ($datos['tipo'] == "time") {
                $format = "H:i:s";
            }
            if (isset($datos["format"]["carbon"])) {
                $format = $datos["format"]["carbon"];
            } elseif (isset(trans("crudgenerator::admin.formats.carbon")[$datos['tipo']])) {
                $format = trans("crudgenerator::admin.formats.carbon." . $datos['tipo']);
            }
            $dato = $value->{$columna};

            if ($dato != "") {
                if (isset($datos["timezone"])) {
                    $timezone = $datos["timezone"];
                } else {
                    $timezone = config("app.timezone");
                }
                $date = new \Carbon\Carbon($dato, $timezone);
                if (stripos($format, "%") !== false) {
                    setlocale(LC_TIME, App::getLocale(), strtoupper(App::getLocale()), App::getLocale() . "_" . strtoupper(App::getLocale()));
                    $dato = $date->formatLocalized($format);
                } else {
                    $dato = $date->format($format);
                }
            } else {
                $date = $value->{$columna};
            }
            $celda['data'] = $date;
            $celda['label'] = $datos['label'];
            $celda['value'] = $dato;
        } elseif ($datos['tipo'] == "url" || ($datos['tipo'] == "file" && Str::startsWith(strtolower($value->{$columna}), ["http:", "https:"]))) {
            if ($datos['tipo'] == "url" && !Str::startsWith(strtolower($value->{$columna}), ["http:", "https:"])) {
                $url = Str::start($value->{$columna}, "http://");
            } else {
                $url = $value->{$columna};
            }
            $celda = [
                'value' => $url,
                'data' => $url,
            ];
            if (CrudGenerator::urlType($url) == "youtube") {
                $youtubeId = CrudGenerator::getYoutubeId($url);
                $celda['embed'] = "https://www.youtube.com/embed/" . $youtubeId;
                $celda['html_show'] = '<div class="card text-center" >' .
                    '<iframe class="card-img-top" height="400" src="https://www.youtube.com/embed/' . $youtubeId . '" style="border: none;"></iframe>' .
                    '<div clas="card-body" >' .
                    '<h5 class="card-title">' . $url . '</h5>' .
                    '</div>' .
                    '</div>';
            } else {
                $celda['embed'] = $url;
                $celda['html_show'] = "<a class='btn' href='{$url}' target='_blank'><i class='mt-2 " . CrudGenerator::getIcon('url') . "' aria-hidden='true'></i></a> {$url}";
            }
            $celda['label'] = $datos['label'];
            $celda['html'] = "<a href='{$url}' target='_blank'><i class='mt-2 " . CrudGenerator::getIcon('url') . "' aria-hidden='true'></i></a>";
            $celda['html_cell'] = $celda['html'];
        } elseif ($datos['tipo'] == "article" && class_exists(config('sirgrimorum.transarticles.default_articles_model'))) {
            $modelClass = config('sirgrimorum.transarticles.default_articles_model');
            $langColumn = config('sirgrimorum.transarticles.default_lang_column');
            $findArticle = config('sirgrimorum.transarticles.default_findarticle_function_name');
            $article = $modelClass::{$findArticle}($value->{$columna})->where($langColumn, "=", App::getLocale())->first();
            if (isset($article)) {
                $strArticle = $article->content;
            } else {
                $article = $modelClass::{$findArticle}($value->{$columna})->first();
                if (isset($article)) {
                    $strArticle = $article->content;
                } else {
                    if (isset($datos['valor'])) {
                        $strArticle = $datos['valor'];
                    } else {
                        $strArticle = $value->{$columna};
                    }
                }
            }
            $celda = [
                'value' => $strArticle,
                'label' => $datos['label'],
                'html_cell' => '<div style="max-height:10em;max-width:40em;overflow-y:scroll;">' . $strArticle . '</div>',
            ];
            $celda['data'] = [];
            foreach (config("sirgrimorum.crudgenerator.list_locales") as $localeCode) {
                $articles = $modelClass::{$findArticle}($value->{$columna})->where($langColumn, "=", $localeCode)->first();
                if (isset($articles)) {
                    $celda['data'][$localeCode] = $articles->content;
                } else {
                    if (isset($datos['valor'])) {
                        $celda['data'][$localeCode] = $datos['valor'];
                    } else {
                        $celda['data'][$localeCode] = $value->{$columna};
                    }
                }
            }
        } elseif ($datos['tipo'] == "json") {
            $celda['data'] = json_decode($value->{$columna}, true);
            $celda['label'] = $datos['label'];
            $celda['value'] = $value->{$columna};
            $celda['html'] = "<pre><code>" . json_encode($celda['data'], JSON_PRETTY_PRINT) . "</code></pre>";
            $fileHtml = '<div class="card text-left">' .
                '<div class="card-header">' .
                'JSON' .
                '</div>' .
                '<div class="card-body w-100" style="max-height:20em;overflow-y:scroll;">' .
                $celda['html'] .
                '</div>' .
                '</div>';
            $celda['html_show'] = $fileHtml;
            $celda['html_cell'] = '<div style="max-height:10em;max-width:30em;overflow-y:scroll;">' . $celda['html'] . '</div>';
        } elseif ($datos['tipo'] == "file") {
            if ($value->{$columna} == "") {
                $celda = '';
            } else {
                [$filename, $urlFile] = CrudGenerator::getFileUrl($value->{$columna}, $value, $modelo, $columna, $datos, $config);
                $tipoFile = CrudGenerator::filenameIs($value->{$columna}, $datos);
                if (stripos($value->{$columna}, '__') !== false) {
                    $auxprevioName = substr($value->{$columna}, stripos($value->{$columna}, '__') + 2, stripos($value->{$columna}, '.', stripos($value->{$columna}, '__')) - (stripos($value->{$columna}, '__') + 2));
                } else {
                    $auxprevioName = substr($value->{$columna}, 0, stripos($value->{$columna}, '.', 0));
                }
                $tipoMime = CrudGenerator::fileMime(strtolower($filename), $datos);
                $fileHtml = '<div class="card text-center">';
                $titleFileHtml = $auxprevioName;
                if ($value->{$columna} == "") {
                    $fileHtmlCell = '-';
                    $fileHtml = '-';
                } elseif ($tipoFile == 'image') {
                    $fileHtmlCell = '<figure class="figure">' .
                        '<a class="text-secondary" href="' . $urlFile . '" target="_blank" >' .
                        '<img src="' . $urlFile . '" class="figure-img img-fluid rounded" alt="' . $auxprevioName . '">' .
                        '<figcaption class="figure-caption">' . $auxprevioName . '</figcaption>' .
                        '</a>' .
                        '</figure>';
                    $fileHtml .= '<a class="text-secondary" href="' . $urlFile . '" target="_blank" >' .
                        '<img class="card-img-top" style="width:auto;max-width:100%;" src="' . $urlFile . '" alt="' . $auxprevioName . '">' .
                        '</a>';
                } else {
                    $fileHtmlCell = '<ul class="fa-ul">' .
                        '<li class="pl-2">' .
                        CrudGenerator::getIcon($tipoFile, true, 'fa-li') .
                        '<a class="text-secondary" href="' . $urlFile . '" target="_blank" >' .
                        $auxprevioName .
                        '</a>' .
                        '</li>' .
                        '</ul>';
                    if ($tipoFile == 'video') {
                        $fileHtml .= '<video class="card-img-top" controls preload="auto" height="300" >' .
                            '<source src="' . $urlFile . '" type="video/mp4" />' .
                            '</video>';
                    } elseif ($tipoFile == 'audio') {
                        $fileHtml .= '<audio class="card-img-top" controls preload="auto" >' .
                            '<source src="' . $urlFile . '" type="audio/mpeg" />' .
                            '</audio>';
                    } elseif ($tipoFile == 'pdf') {
                        $fileHtml .= '<iframe class="card-img-top" height="300" src="' . $urlFile . '" style="border: none;"></iframe>';
                    } else {
                        $fileHtml .= '<div class="card-header">' .
                            CrudGenerator::getIcon($tipoFile, true, 'fa-3x') .
                            '</div>';
                        $titleFileHtml = '<a class="text-secondary" href="' . $urlFile . '" target="_blank" >' .
                            "{$titleFileHtml}" .
                            '</a>';
                    }
                }
                $fileHtml .= '<div class="card-body" >' .
                    '<h5 class="card-title">' . $titleFileHtml . '</h5>' .
                    '</div>' .
                    '</div>';
                $celda = [
                    "name" => $auxprevioName,
                    "value" => $filename,
                    "url_public" => CrudGenerator::getDisk($datos)->url($filename),
                    "url" => $urlFile,
                    "label" => $datos['label'],
                    "type" => $tipoFile,
                    "html" => CrudGenerator::getHtmlParaFile($tipoFile, $urlFile, $auxprevioName, $tipoMime),
                    "html_cell" => $fileHtmlCell,
                    "html_show" => $fileHtml,
                ];
            }
        } elseif ($datos['tipo'] == "files") {
            if ($value->{$columna} == "") {
                $celda = '';
            } else {
                try {
                    $auxprevios = json_decode($value->{$columna});
                    if (!is_array($auxprevios)) {
                        $auxprevios = [];
                    }
                } catch (Exception $ex) {
                    $auxprevios = [];
                }
                $celda['data'] = [];
                $celda['label'] = $datos['label'];
                $celda['value'] = $value->{$columna};
                $fileHtml = '<div class="row">';
                $fileHtmlCell = '<ul class="fa-ul">';
                foreach ($auxprevios as $datoReg) {
                    if (is_object($datoReg)) {
                        [$filename, $urlFile] = CrudGenerator::getFileUrl($datoReg->file, $value, $modelo, $columna, $datos, $config);
                        $tipoFile = CrudGenerator::filenameIs($datoReg->file, $datos);
                        $auxprevioName = substr($value->{$columna}, stripos($value->{$columna}, '__') + 2, stripos($value->{$columna}, '.', stripos($value->{$columna}, '__')) - (stripos($value->{$columna}, '__') + 2));
                        $tipoMime = CrudGenerator::fileMime(strtolower($filename), $datos);
                        $fileHtml = CrudGenerator::getHtmlParaFile($tipoFile, $urlFile, $auxprevioName, $tipoMime);
                        $celda['data'][] = [
                            "name" => $datoReg->name,
                            "value" => $filename,
                            "url_public" => CrudGenerator::getDisk($datos)->url($filename),
                            "url" => $urlFile,
                            "type" => $tipoFile,
                            "html" => $fileHtml,
                        ];
                        $fileHtmlCell .= '<li class="pl-2">';
                        if ($tipoFile == 'image') {
                            $fileHtmlCell .= '<i class="' . CrudGenerator::getIcon('empty') . ' fa-li" aria-hidden="true"><img class="w-75 rounded" style="cursor: pointer;" src="' . $urlFile . '"></i>';
                        } else {
                            $fileHtmlCell .= CrudGenerator::getIcon($tipoFile, true, 'fa-li');
                        }
                        $fileHtmlCell .= '<a class="text-secondary" href="' . $urlFile . '" target="_blank" >' .
                            $datoReg->name .
                            '</a>' .
                            '</li>';
                        $fileHtml .= '<div class="col-md-6 col-sm-12 col-xs-12">' .
                            '<div class="card text-center">';
                        $titleFileHtml = $datoReg->name;
                        if ($tipoFile == 'image') {
                            $fileHtml .= '<a class="text-secondary" href="' . $urlFile . '" target="_blank" >' .
                                '<img class="card-img-top" src="' . $urlFile . '">' .
                                '</a>';
                        } elseif ($tipoFile == 'video') {
                            $fileHtml .= '<video class="card-img-top" controls preload="auto" height="300" >' .
                                '<source src="' . $urlFile . '" type="video/mp4" />' .
                                '</video>';
                        } elseif ($tipoFile == 'audio') {
                            $fileHtml .= '<audio class="card-img-top" controls preload="auto" >' .
                                '<source src="' . $urlFile . '" type="audio/mpeg" />' .
                                '</audio>';
                        } elseif ($tipoFile == 'pdf') {
                            $fileHtml .= '<iframe class="card-img-top" height="300" src="' . $urlFile . '" style="border: none;"></iframe>';
                        } else {
                            $fileHtml .= '<div class="card-header">' .
                                CrudGenerator::getIcon($tipoFile, true, 'fa-3x') .
                                '</div>';
                            $titleFileHtml = '<a class="text-secondary" href="' . $urlFile . '" target="_blank" >' .
                                "{$titleFileHtml}" .
                                '</a>';
                        }
                        $fileHtml .= '<div class="card-body" >' .
                            '<h5 class="card-title">' . $titleFileHtml . '</h5>' .
                            '</div>' .
                            '</div>' .
                            '</div>';
                    }
                }
                if (count($auxprevios) == 0) {
                    $fileHtmlCell = "-";
                    $fileHtml = "-";
                } else {
                    $fileHtmlCell .= '</ul>';
                    $fileHtml .= '</div>';
                }
                $celda['html'] = $fileHtml;
                $celda['html_cell'] = $fileHtmlCell;
            }
        } elseif ($datos['tipo'] == "color") {
            $celda = [
                'value' => $value->{$columna},
                'data' => $value->{$columna},
            ];
            $celda['label'] = $datos['label'];
            $color = false;
            if (!isset($value->{$columna}) && isset($datos['valor'])) {
                $color = $datos['valor'];
            } elseif (isset($value->{$columna})) {
                $color = $value->{$columna};
            }
            if ($color) {
                $celda['html'] = '<span title="' . $color . '" style="display:inline-block;width:1.5em;height:1.5em;border:1px solid #000;background-color:' . $color . ';"></span>';
                $celda['html_cell'] = $celda['html'];
                $fileHtml = '<div class="card text-center">' .
                    '<div class="card-header">' .
                    '<span style="display:inline-block;width:90%;height:5em;border:1px solid #000;background-color:' . $color . ';"></span>' .
                    '</div>' .
                    '<div class="card-body" >' .
                    '<h5 class="card-title">' . $color . '</h5>' .
                    '</div>' .
                    '</div>';
                $celda['html_show'] = $fileHtml;
            }
        } else {
            if (array_key_exists('enlace', $datos)) {
                if (count($config) <= 0) {
                    $modelo = strtolower(class_basename(get_class($value)));
                    $config = CrudGenerator::getConfigWithParametros($modelo);
                }
                $auxcelda = '<a href = "' . $datos['enlace'] . '">';
            }
            $htmlCell = $auxcelda;
            if ($datos['tipo'] == "number" && isset($datos['format'])) {
                if (is_array($datos['format'])) {
                    $auxcelda .= number_format($value->{$columna}, $datos['format'][0], $datos['format'][1], $datos['format'][2]);
                } else {
                    $auxcelda .= number_format($value->{$columna});
                }
                $htmlCell = $auxcelda;
            } else {
                $auxcelda .= $value->{$columna};
                if ($datos['tipo'] == "html") {
                    $htmlCell = '<div style="max-height:10em;max-width:40em;overflow-y:scroll;">' . $value->{$columna} . '</div>';
                } else {
                    $htmlCell .= CrudGenerator::truncateText($value->{$columna});
                }
            }
            $celda['data'] = $value->{$columna};
            $celda['label'] = Arr::get($datos, 'label', $columna);

            if (array_key_exists('enlace', $datos)) {
                $auxcelda .= '</a>';
                if ($datos['tipo'] != "html") {
                    $htmlCell .= '</a>';
                }
            }
            $celda['value'] = $auxcelda;
            $celda['html_cell'] = $htmlCell;
        }
        if (isset($datos["post"])) {
            $celdaData['post'] = $datos["post"];
        }
        if (is_string($celda)) {
            $celdaData['data'] = $celda;
            $celdaData['value'] = $celda;
            $celdaData['label'] = $datos['label'];
            $celda = $celdaData;
        } else {
            $celda = array_merge($celda, $celdaData);
        }
        if (isset($celda['pre']) && is_string($celda['value'])) {
            $celda['value'] = $celda['pre'] . $celda['value'];
            if (isset($celda['html_show'])) {
                $celda['html_show'] = $celda['pre'] . $celda['html_show'];
            } elseif (isset($celda['html'])) {
                $celda['html_show'] = $celda['pre'] . $celda['html'];
            }
            if (isset($celda['html_cell'])) {
                $celda['html_cell'] = $celda['pre'] . $celda['html_cell'];
            }
        }
        if (isset($celda['post']) && is_string($celda['value'])) {
            $celda['value'] = $celda['value'] . Str::start($celda['post'], " ");
            if (isset($celda['html_show'])) {
                $celda['html_show'] = $celda['html_show'] . Str::start($celda['post'], " ");
            } elseif (isset($celda['html'])) {
                $celda['html_show'] = $celda['html'] . Str::start($celda['post'], " ");
            }
            if (isset($celda['html_cell'])) {
                $celda['html_cell'] = $celda['html_cell'] . Str::start($celda['post'], " ");
            }
        }
        $celda['value'] = CrudGenerator::translateDato($celda['value'], $value, $config);
        if (isset($celda['html'])) {
            $celda['html'] = CrudGenerator::translateDato($celda['html'], $value, $config);
        }
        if (isset($celda['html_show'])) {
            $celda['html_show'] = CrudGenerator::translateDato($celda['html_show'], $value, $config);
        }
        if (isset($celda['html_cell'])) {
            $celda['html_cell'] = CrudGenerator::translateDato($celda['html_cell'], $value, $config);
        }
        return $celda;
    }

    /**
     * Executes a filter query over a data using diferent operators
     * 
     * @param mixed $dato The data to compare
     * @param string $query The query to execute, includes >, <, =, !=, null, not null, set:, notset:, contiene:, nocontiene:
     * @param bool $negado Optional If true, will negate all the query operators, default false
     * @return bool If the data complies with the query or not
     */
    public static function execFilterQuery($dato, $query, $negado = false)
    {
        $contiene = false;
        if (stripos($query, "*%") !== false) {
            $contiene = true;
            $buscar = str_replace("*%", "", $query);
        } else {
            $buscar = $query;
        }
        $buscarExtra = null;
        $operador = "";
        if ($buscar == "else") {
            $operador = "else";
        } elseif (str_contains($buscar, "|")) {
            $auxDatos = explode("|", $buscar);
            if ($auxDatos[0] == "<" && $auxDatos[1] != ">") {
                if ($negado) {
                    $operador = ">";
                } else {
                    $operador = "<=";
                }
                $buscar = $auxDatos[1];
            } elseif ($auxDatos[0] != "<" && $auxDatos[1] == ">") {
                if ($negado) {
                    $operador = "<";
                } else {
                    $operador = ">=";
                }
                $buscar = $auxDatos[0];
            } elseif ($auxDatos[0] != "<" && $auxDatos[1] != ">") {
                $buscar = $auxDatos[0];
                if ($negado) {
                    $operador = "noentre";
                } else {
                    $operador = "entre";
                }
                $buscarExtra = $auxDatos[1];
            }
        } elseif ((($buscar == "null" || Str::contains($buscar, "notset:")) && !$negado) || (($buscar == "not null" || Str::contains($buscar, "set:")) && $negado)) {
            $buscar = str_replace(["notset:", "set:"], "", $buscar);
            $operador = "notset";
        } elseif ((($buscar == "null" || Str::contains($buscar, "notset:")) && $negado) || (($buscar == "not null" || Str::contains($buscar, "set:")) && !$negado)) {
            $buscar = str_replace(["notset:", "set:"], "", $buscar);
            $operador = "set";
        } elseif (Str::contains($buscar, ">=")) {
            $buscar = str_replace(">=", "", $buscar);
            if ($negado) {
                $operador = "<";
            } else {
                $operador = ">=";
            }
        } elseif (Str::contains($buscar, "<=")) {
            $buscar = str_replace("<=", "", $buscar);
            if ($negado) {
                $operador = ">";
            } else {
                $operador = "<=";
            }
        } elseif (Str::contains($buscar, ">")) {
            $buscar = str_replace(">", "", $buscar);
            if ($negado) {
                $operador = "<=";
            } else {
                $operador = ">";
            }
        } elseif (Str::contains($buscar, "<")) {
            $buscar = str_replace("<", "", $buscar);
            if ($negado) {
                $operador = ">=";
            } else {
                $operador = "<";
            }
        } elseif ((Str::contains($buscar, "!=") && !$negado) || (Str::contains($buscar, "=") && $negado)) {
            $buscar = str_replace(["!=", "="], "", $buscar);
            $operador = "!=";
        } elseif ((Str::contains($buscar, "!=") && $negado) || (Str::contains($buscar, "=") && !$negado)) {
            $buscar = str_replace(["!=", "="], "", $buscar);
            $operador = "==";
        } elseif ((Str::contains($buscar, "contiene:") && !$negado) || (Str::contains($buscar, "nocontiene:") && $negado)) {
            $buscar = str_replace(["nocontiene:", "contiene:"], "", $buscar);
            $operador = "contiene";
        } elseif ((Str::contains($buscar, "contiene:") && $negado) || (Str::contains($buscar, "nocontiene:") && !$negado)) {
            $buscar = str_replace(["nocontiene:", "contiene:"], "", $buscar);
            $operador = "nocontiene";
        } else {
            if (!$contiene) {
                if ($negado) {
                    $operador = "!=";
                } else {
                    $operador = "==";
                }
            } else {
                if ($negado) {
                    $operador = "nocontiene";
                } else {
                    $operador = "contiene";
                }
            }
        }
        //echo "<p>ejecutando comparación</p><pre>" . print_r([$dato, $buscar, $operador, $buscarExtra], true) . "</pre>";
        $comparador = new DynamicCompare($dato, $buscar, $operador, $buscarExtra);
        return $comparador->es();
    }

    /**
     * Executes a filter query over a query Builder using diferent operators
     * 
     * @param array $config The configuration array
     * @param string $campo The name of the field
     * @param Builder $query The original Query Builder
     * @param boolean $orOperation Optional (only when $registro is Builder), if use or operation, false will use and operation.
     * @param bool $negado Optional If true, will negate all the query operators, default false
     * @return Builder The new query Builder
     */
    public static function execQueryFilterQuery($config, $campo, $query, $buscar, $orOperation = true, $negado = false)
    {
        $columna = $config['campos'][$campo];
        if ($columna['tipo'] == "relationship" && CrudGenerator::hasRelation($config['modelo'], $campo)) {
            $campo = (new $config['modelo']())->{$campo}()->getForeignKeyName();
        }
        $contiene = false;
        if (stripos($buscar, "*%") !== false) {
            $contiene = true;
            $buscar = str_replace("*%", "", $buscar);
        } else {
            $buscar = $buscar;
        }
        $operador = "";
        if ($buscar == "else") {
            //Pendiente
        } elseif ($buscar != null && Str::contains($buscar, "|")) {
            $auxDatos = explode("|", $buscar);
            if ($columna["tipo"] == "date") {
                $datos = [
                    Carbon::parse($auxDatos[0])->toDateString(),
                    Carbon::parse($auxDatos[1])->toDateString(),
                ];
                if ($negado) {
                    if ($orOperation) {
                        $query = $query->orWhereNotBetween($campo, $datos);
                    } else {
                        $query = $query->whereNotBetween($campo, $datos);
                    }
                } else {
                    if ($orOperation) {
                        $query = $query->orWhereBetween($campo, $datos);
                    } else {
                        $query = $query->whereBetween($campo, $datos);
                    }
                }
            } elseif ($columna["tipo"] == "datetime") {
                $datos = [
                    Carbon::parse($auxDatos[0])->toDateTimeString(),
                    Carbon::parse($auxDatos[1])->toDateTimeString(),
                ];
                if ($negado) {
                    if ($orOperation) {
                        $query = $query->orWhereNotBetween($campo, $datos);
                    } else {
                        $query = $query->whereNotBetween($campo, $datos);
                    }
                } else {
                    if ($orOperation) {
                        $query = $query->orWhereBetween($campo, $datos);
                    } else {
                        $query = $query->whereBetween($campo, $datos);
                    }
                }
            } elseif ($columna["tipo"] == "time") {
                $datos = [
                    Carbon::parse($auxDatos[0])->toTimeString(),
                    Carbon::parse($auxDatos[1])->toTimeString(),
                ];
                if ($negado) {
                    if ($orOperation) {
                        $query = $query->orWhereNotBetween($campo, $datos);
                    } else {
                        $query = $query->whereNotBetween($campo, $datos);
                    }
                } else {
                    if ($orOperation) {
                        $query = $query->orWhereBetween($campo, $datos);
                    } else {
                        $query = $query->whereBetween($campo, $datos);
                    }
                }
            } elseif ($auxDatos[0] == "<" && $auxDatos[1] != ">") {
                if ($negado) {
                    $operador = ">";
                } else {
                    $operador = "<=";
                }
                $buscar = $auxDatos[1];
                if ($orOperation) {
                    $query = $query->orWhereRaw("($campo $operador '$buscar' or $campo is null)");
                } else {
                    $query = $query->whereRaw("($campo $operador '$buscar' or $campo is null)");
                }
                $operador = "";
            } elseif ($auxDatos[0] != "<" && $auxDatos[1] == ">") {
                if ($negado) {
                    $operador = "<";
                } else {
                    $operador = ">=";
                }
                $buscar = $auxDatos[0];
            } elseif ($auxDatos[0] != "<" && $auxDatos[1] != ">") {
                $buscar = $auxDatos[0];
                if ($negado) {
                    if ($orOperation) {
                        $query = $query->orWhereNotBetween($campo, $auxDatos);
                    } else {
                        $query = $query->whereNotBetween($campo, $auxDatos);
                    }
                } else {
                    if ($orOperation) {
                        $query = $query->orWhereBetween($campo, $auxDatos);
                    } else {
                        $query = $query->whereBetween($campo, $auxDatos);
                    }
                }
            }
        } elseif ((($buscar == "null" || Str::contains($buscar, "notset:")) && !$negado) || (($buscar == "not null" || Str::contains($buscar, "set:")) && $negado)) {
            $buscar = str_replace(["notset:", "set:"], "", $buscar);
            if ($orOperation) {
                $query = $query->orWhereNull($campo);
            } else {
                $query = $query->whereNull($campo);
            }
        } elseif ((($buscar == "null" || Str::contains($buscar, "notset:")) && $negado) || (($buscar == "not null" || Str::contains($buscar, "set:")) && !$negado)) {
            $buscar = str_replace(["notset:", "set:"], "", $buscar);
            if ($orOperation) {
                $query = $query->orWhereNotNull($campo);
            } else {
                $query = $query->whereNotNull($campo);
            }
        } elseif ($buscar != null && Str::contains($buscar, ">=")) {
            $buscar = str_replace(">=", "", $buscar);
            if ($negado) {
                $operador = "<";
            } else {
                $operador = ">=";
            }
        } elseif ($buscar != null && Str::contains($buscar, "<=")) {
            $buscar = str_replace("<=", "", $buscar);
            if ($negado) {
                $operador = ">";
            } else {
                $operador = "<=";
            }
        } elseif ($buscar != null && Str::contains($buscar, ">")) {
            $buscar = str_replace(">", "", $buscar);
            if ($negado) {
                $operador = "<=";
            } else {
                $operador = ">";
            }
        } elseif ($buscar != null && Str::contains($buscar, "<")) {
            $buscar = str_replace("<", "", $buscar);
            if ($negado) {
                $operador = ">=";
            } else {
                $operador = "<";
            }
        } elseif ($buscar != null && (Str::contains($buscar, "!=") && !$negado) || (Str::contains($buscar, "=") && $negado)) {
            $buscar = str_replace(["!=", "="], "", $buscar);
            $operador = "<>";
        } elseif ($buscar != null && (Str::contains($buscar, "!=") && $negado) || (Str::contains($buscar, "=") && !$negado)) {
            $buscar = str_replace(["!=", "="], "", $buscar);
            $operador = "=";
        } elseif ($buscar != null && (Str::contains($buscar, "contiene:") && !$negado) || (Str::contains($buscar, "nocontiene:") && $negado)) {
            $buscar = str_replace(["nocontiene:", "contiene:"], "", $buscar);
            if ($orOperation) {
                $query = $query->orWhereRaw("$campo LIKE '%$buscar%'");
            } else {
                $query = $query->whereRaw("$campo LIKE '%$buscar%'");
            }
        } elseif ($buscar != null && (Str::contains($buscar, "contiene:") && $negado) || (Str::contains($buscar, "nocontiene:") && !$negado)) {
            $buscar = str_replace(["nocontiene:", "contiene:"], "", $buscar);
            if ($orOperation) {
                $query = $query->orWhereRaw("$campo NOT LIKE '%$buscar%'");
            } else {
                $query = $query->whereRaw("$campo NOT LIKE '%$buscar%'");
            }
        } else {
            if (!$contiene) {
                if ($negado) {
                    $operador = "<>";
                } else {
                    $operador = "=";
                }
            } else {
                if ($negado) {
                    if ($orOperation) {
                        $query = $query->orWhereRaw("$campo NOT LIKE '%$buscar%'");
                    } else {
                        $query = $query->whereRaw("$campo NOT LIKE '%$buscar%'");
                    }
                } else {
                    if ($orOperation) {
                        $query = $query->orWhereRaw("$campo LIKE '%$buscar%'");
                    } else {
                        $query = $query->whereRaw("$campo LIKE '%$buscar%'");
                    }
                }
            }
        }
        if ($operador != "") {
            if ($orOperation) {
                $query = $query->orWhereRaw("$campo $operador '$buscar'");
            } else {
                $query = $query->whereRaw("$campo $operador '$buscar'");
            }
        }
        return $query;
    }

    /**
     * Filter an object of a model with a single query. It will use AND operation.
     *
     * If $attri is a method or function fo the object it will try to evaluate it with
     * $query as parametter. Use a Json string to pass more than one parametter.
     * If the returned value is not a boolean or null, will use the $query or the las value of
     * the $query array to comare.
     *
     * If $attri is only an attribute, it will compare against $query as it is.
     *
     * if $query contains "*%" it will erase them and evaluate if $query is contained in the attribute value.
     * Not aplicable for function or methods returns
     *
     * @param object $registro The model object
     * @param string|array $query The query to evaluate
     * @param string $attri The attribute to compare
     * @param boolean $orOperation Optional (only when $registro is Builder), if use or operation, false will use and operation.
     * @return boolean
     */
    public static function evaluateFilterWithSingleQuery($registro, $query, $attri, $orOperation = true)
    {
        if (($numArgs = CrudGenerator::isFunction($registro, $attri)) !== false) {
            //echo "<p>NumArgs $numArgs</p>";
            if (CrudGenerator::isJsonString($query)) {
                $queryArr = json_decode($query, true);
            } elseif (is_array($query)) {
                $queryArr = $query;
            } else {
                $queryArr = [$query];
            }
            $result = CrudGenerator::callFunction($registro, $attri, $queryArr, $numArgs);
            //echo "<p>evaluando function {$registro->id}</p><pre>" . print_r([$query, $attri, $result], true) . "</pre>";
            if ($result === false) {
                return false;
            } elseif ($result === true) {
                return true;
            } else {
                $query = array_pop($queryArr);
                $dato = $result;
            }
        } else {
            $dato = $registro->{$attri};
        }
        if (CrudGenerator::isJsonString($query)) {
            $query = json_decode($query, true);
        }
        //echo "<p>comparando {$registro->id}</p><pre>" . print_r([$dato, $query, $orOperation], true) . "</pre>";
        if (is_array($query)) {
            foreach ($query as $queryStr) {
                $result = CrudGenerator::execFilterQuery($dato, $queryStr);
                if ($result === true) {
                    return true;
                }
            }
            return false;
        } else {
            if (!CrudGenerator::execFilterQuery($dato, $query)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Filter a query Builder with a single query.
     *
     * @param Builder $query The original Query Builder
     * @param string|array $query The query to evaluate
     * @param string $attri The attribute to compare
     * @param array $config The configuration array
     * @param boolean $orOperation Optional (only when $registro is Builder), if use or operation, false will use and operation.
     * @return Builder
     */
    public static function evaluateQueryFilterWithSingleQuery($queryBuilder, $query, $attri, $config, $orOperation = true)
    {
        if (CrudGenerator::isJsonString($query)) {
            $query = json_decode($query, true);
        }
        if (is_array($query)) {
            if ($orOperation) {
                $queryBuilder = $queryBuilder->orWhere(function ($subQuery) use ($query, $attri, $config) {
                    foreach ($query as $queryStr) {
                        $subQuery = CrudGenerator::execQueryFilterQuery($config, $attri, $subQuery, $queryStr, true);
                    }
                });
            } else {
                $queryBuilder = $queryBuilder->where(function ($subQuery) use ($query, $attri, $config) {
                    foreach ($query as $queryStr) {
                        $subQuery = CrudGenerator::execQueryFilterQuery($config, $attri, $subQuery, $queryStr, true);
                    }
                });
            }
        } else {
            $queryBuilder = CrudGenerator::execQueryFilterQuery($config, $attri, $queryBuilder, $query, $orOperation);
        }
        return $queryBuilder;
    }

    /**
     * Filter an object of a model with a query comparing against an attribute value.
     *
     *
     * @param object|Builder $registro The model object or a Query Builder
     * @param string|array $query The query or querys to compare
     * @param string|array $attri The attribute or attributes to compare with. Could evaluate methods and functions.
     * @param array $config Optional (only when $registro is Builder) Configuration array for the Model
     * @param boolean $orOperation Optional (only when $registro is Builder), if use or operation, false will use and operation.
     * @return boolean|Builder if $registro is a model, will return true or false, else will return a Builder with the necessary operations
     */
    public static function subEvaluateFilter($registro, $query, $attri, $config = null, $orOperation = true)
    {
        if (is_array($attri) || is_object($attri)) {
            $attriStr = json_encode($attri);
        } else {
            $attriStr = $attri;
        }
        if (is_object($query)) {
            $queryStr = json_encode($query);
        } else {
            $queryStr = $query;
        }
        if ($registro instanceof Builder) {
            return CrudGenerator::evaluateQueryFilterWithSingleQuery($registro, $queryStr, $attriStr, $config, $orOperation);
        } else {
            return CrudGenerator::evaluateFilterWithSingleQuery($registro, $queryStr, $attriStr, $orOperation);
        }
    }

    /**
     * Filter an object of a model with a query comparing against an attribute value.
     *
     * If $query and/or $attri ar arrays. It will use AND operation.
     *
     * If $query contains "*%" it will erase them and evaluate if $query is contained in the attribute value.
     * Not aplicable for function or methods returns
     *
     * @param object|Builder $registro The model object or a Query Builder
     * @param string|array $query The query or querys to compare
     * @param string|array $attri The attribute or attributes to compare with. Could evaluate methods and functions.
     * @param boolean $orOperation Optional, if use or operation (just one query must be true), false will use and operation (all the querys must be true).
     * @param boolean $fbf Optional, default false. If the query and attributes arrays must be evaluated one by one (ej: $query[0] vs $attribute[0] AND $query[1] vs $attribute[1], ...) The size of $attri and $query must be the same
     * @param array $config Optional (only when $registro is Builder) Configuration array for the Model
     * @return boolean|Builder if $registro is a model, will return true or false, else will return a Builder with the necessary operations
     */
    public static function evaluateFilter($registro, $query, $attri, $orOperation = true, $fbf = false, $config = null)
    {
        if ($fbf && isset($attri) == isset($query)) {
            if (count($attri) != count($query)) {
                $fbf = false;
            }
        } else {
            $fbf = false;
        }
        //echo "<p>Evaluando filtro</p><pre>" . print_r([$fbf, $query, $attri, $orOperation, $config], true) . "</pre>";
        if ($fbf) {
            if ($registro instanceof Builder && $orOperation) {
                $registro = $registro->where(function ($subQuery) use ($query, $attri, $config, $orOperation) {
                    for ($index = 0; $index < count($query); $index++) {
                        $subQuery = CrudGenerator::subEvaluateFilter($subQuery, $query[$index], $attri[$index], $config, $orOperation);
                    }
                });
            } else {
                for ($index = 0; $index < count($query); $index++) {
                    $result = CrudGenerator::subEvaluateFilter($registro, $query[$index], $attri[$index], $config, $orOperation);
                    if ($result === false && !$orOperation) {
                        return false;
                    } elseif ($result === true && $orOperation) {
                        return true;
                    } elseif ($result instanceof Builder) {
                        $registro = $result;
                    }
                }
            }
        } elseif (is_array($attri)) {
            if ($registro instanceof Builder && $orOperation) {
                $registro = $registro->where(function ($subQuery) use ($query, $attri, $config, $orOperation) {
                    foreach ($attri as $attribute) {
                        if (is_array($query)) {
                            foreach ($query as $singleQuery) {
                                $subQuery = CrudGenerator::subEvaluateFilter($subQuery, $singleQuery, $attribute, $config, $orOperation);
                            }
                        } else {
                            $subQuery = CrudGenerator::subEvaluateFilter($subQuery, $query, $attribute, $config, $orOperation);
                        }
                    }
                });
            } else {
                foreach ($attri as $attribute) {
                    if (is_array($query)) {
                        foreach ($query as $singleQuery) {
                            $result = CrudGenerator::subEvaluateFilter($registro, $singleQuery, $attribute, $config, $orOperation);
                            if ($result === false && !$orOperation) {
                                return false;
                            } elseif ($result === true && $orOperation) {
                                return true;
                            } elseif ($result instanceof Builder) {
                                $registro = $result;
                            }
                        }
                    } else {
                        $result = CrudGenerator::subEvaluateFilter($registro, $query, $attribute, $config, $orOperation);
                        if ($result === false && !$orOperation) {
                            return false;
                        } elseif ($result === true && $orOperation) {
                            return true;
                        } elseif ($result instanceof Builder) {
                            $registro = $result;
                        }
                    }
                }
            }
        } else {
            if (is_array($query)) {
                if ($registro instanceof Builder && $orOperation) {
                    $registro = $registro->where(function ($subQuery) use ($query, $attri, $config, $orOperation) {
                        foreach ($query as $singleQuery) {
                            $subQuery = CrudGenerator::subEvaluateFilter($subQuery, $singleQuery, $attri, $config, $orOperation);
                        }
                    });
                } else {
                    foreach ($query as $singleQuery) {
                        $result = CrudGenerator::subEvaluateFilter($registro, $singleQuery, $attri, $config, $orOperation);
                        if ($result === false && !$orOperation) {
                            return false;
                        } elseif ($result === true && $orOperation) {
                            return true;
                        } elseif ($result instanceof Builder) {
                            $registro = $result;
                        }
                    }
                }
            } else {
                $result = CrudGenerator::subEvaluateFilter($registro, $query, $attri, $config, $orOperation);
                if ($result === false && !$orOperation) {
                    return false;
                } elseif ($result === true && $orOperation) {
                    return true;
                } elseif ($result instanceof Builder) {
                    $registro = $result;
                }
            }
        }
        if ($registro instanceof Builder) {
            return $registro;
        } elseif ($orOperation) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Filter a collection of object models using a query an attribute sets in an array.
     *
     * The values in the $datos object must be strings, if using arrays, use json notation or separate the values with an |.
     *
     * If not attribute is given, it will compare against the $config['nombre'] attribute
     *
     * If $query contains "*%" it will erase them and evaluate if $query is contained in the attribute value.
     * Not aplicable for function or methods returns
     *
     * @param Collection|Builder $registros Collection of elocuent model objects or a Query Builder
     * @param array $config Configuration array for the Model
     * @param array $datos Optional the data. if empty, it will get the current request data.
     * @param boolean|string $orOperation Optional boolean or the key of the or value in $datos. if True: use or operation (just one query must be true), false will use and operation (all the querys must be true).
     * @param string $queryStr Optional the key of the query in $datos
     * @param string $attriStr Optional the key of the attributes in $datos
     * @param string $aByAStr Optional the key of the value indicating if the $query and $attribute must be evaluated one by one (ej: $query[0] vs $attribute[0] AND $query[1] vs $attribute[1], ...)
     * @param string $orderStr Optional the key of the order field(s) in $datos
     * @return Collection|Builder Collection filtered or Query Builder with the necesario operations performed
     */
    public static function filterWithQuery($registros, $config, $datos = [], $orOperation = "_or", $queryStr = "_q", $attriStr = "_a", $aByAStr = "_aByA", $orderStr = '_order')
    {
        $datos = CrudGenerator::normalizeDataForSearch($config, $datos, $orOperation, $queryStr, $attriStr, $aByAStr);
        if (!is_bool($orOperation)) {
            if (isset($datos[$orOperation])) {
                $orOperation = !($datos[$orOperation] === 'false');
            } else {
                $orOperation = true;
            }
        }
        if (isset($datos[$queryStr])) {
            $query = $datos[$queryStr];
            if (CrudGenerator::isJsonString($query)) {
                $query = json_decode($query, true);
            } elseif (stripos($query, "|")) {
                $query = explode("|", $query);
            }
            $fbf = isset($datos[$aByAStr]);
            if (is_array($registros)) {
                $registros = collect($registros);
            }
            if ($registros instanceof Collection) {
                if (isset($datos[$attriStr])) {
                    $attri = $datos[$attriStr];
                    if (CrudGenerator::isJsonString($attri)) {
                        $attri = json_decode($attri, true);
                    } elseif (stripos($attri, "|")) {
                        $attri = explode("|", $attri);
                    }
                } else {
                    $attri = $config['nombre'];
                }
                $registros = $registros->filter(function ($registro) use ($query, $attri, $fbf, $orOperation) {
                    return CrudGenerator::evaluateFilter($registro, $query, $attri, $orOperation, $fbf);
                });
            } elseif ($registros instanceof Builder) {
                if (isset($datos[$attriStr])) {
                    $attri = $datos[$attriStr];
                    if (isset($datos["{$attriStr}__C"])) {
                        $attri = $datos["{$attriStr}__C"];
                    }
                    if (CrudGenerator::isJsonString($attri)) {
                        $attri = json_decode($attri, true);
                    } elseif (stripos($attri, "|")) {
                        $attri = explode("|", $attri);
                    }
                } else {
                    $attri = $config['nombre'];
                }
                $registros = CrudGenerator::evaluateFilter($registros, $query, $attri, $orOperation, $fbf, $config);
            }
        }
        if (isset($datos[$orderStr])) {
            $query = $datos[$orderStr];
            if (CrudGenerator::isJsonString($query)) {
                $query = json_decode($query, true);
            } elseif (stripos($query, "|")) {
                $arrQuery = [];
                foreach (explode("|", $query) as $campoOrden) {
                    if (stripos($campoOrden, "__")) {
                        $auxQuery = explode("__", $campoOrden);
                        $arrQuery[$auxQuery[0]] = (isset($auxQuery[1]) ? $auxQuery[1] : "asc");
                    } else {
                        $arrQuery[$campoOrden] = "asc";
                    }
                }
                $query = $arrQuery;
            }
            if ($registros instanceof Collection) {
                foreach ($query as $key => $orden) {
                    if ($registros->has($key)) {
                        if (strtolower($orden) == "desc") {
                            $registros = $registros->sortByDesc($key);
                        } else {
                            $registros = $registros->sortBy($key);
                        }
                        break;
                    }
                }
            } elseif ($registros instanceof Builder) {
                foreach ($query as $key => $orden) {
                    if (strtolower($orden) == "desc") {
                        $registros = $registros->orderBy($key, 'desc');
                    } else {
                        $registros = $registros->orderBy($key, 'asc');
                    }
                }
            }
        }
        return $registros;
    }

    /**
     * Sync a HasMany relationsihp
     * @param object $model The model to sync
     * @param string $campo The name of the method with the hasMany relationsihp
     * @param array $children_items An array with the models to sync with
     * @param array $config The configuration array of the model
     */
    public static function syncHasMany($model, $campo, $children_items, $config)
    {
        if (isset($config['campos']) && is_array($config['campos']) && isset($config['campos'][$campo])) {
            $config = $config['campos'];
        }
        $children = $model->{$campo};
        $children_itemsC = collect($children_items);
        if ($children_itemsC->has($config[$campo]['id'])) {
            $children_items = $children_itemsC;
        }
        $deleted_ids = $children->filter(
            function ($child) use ($children_items, $config, $campo) {
                if ($children_items instanceof Collection) {
                    return empty($children_items->where($config[$campo]['id'], $child->{$config[$campo]['id']})->first());
                } else {
                    return !in_array($child->{$config[$campo]['id']}, $children_items);
                }
            }
        )->map(
            function ($child) {
                return $child->id;
            }
        );
        $attachments = $children_itemsC->filter(
            function ($children_item) use ($config, $campo, $children) {
                if (is_int($children_item) || is_string($children_item)) {
                    return empty($children->whereIn($config[$campo]['id'], $children_item)->first());
                }
                return empty($children->whereIn($config[$campo]['id'], $children_item->{$config[$campo]['id']})->first());
            }
        )->map(function ($children_item) use ($config, $campo) {
            if (is_int($children_item) || is_string($children_item)) {
                return $children_item;
            }
            $id = $children_item->{$config[$campo]['id']};
            $children_item->delete();
            return $id;
        });
        $model->{$campo}()->detach($deleted_ids);
        $model->{$campo}()->attach($attachments);
    }

    /**
     * Runs a validation of a request based on a model and its configuration array
     * @param array $config The configuration array
     * @param Request $request Optional the request. If null, it will use request() function
     * @return mix Retuns the validator or false if there are no rules
     */
    public static function validateModel(array $config, \Illuminate\Http\Request $request = null)
    {
        if (is_null($request)) {
            $request = request();
        }
        $rules = [];
        $modeloM = class_basename($config["modelo"]);
        $modelo = strtolower($modeloM);
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
            $customAttributes = [];
            foreach ($rules as $field => $datos) {
                if (Arr::has($config, "campos." . $field . ".label")) {
                    $customAttributes[$field] = Arr::get($config, "campos." . $field . ".label");
                }
            }
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
            $error_messages = array_merge(trans("crudgenerator::admin.error_messages"), $error_messages);
            $validator = Validator::make($request->all(), $rules, $error_messages, $customAttributes);
            return $validator;
        }
        return false;
    }

    /**
     * Save a new object or edit an existing one base on its configuration array
     * @param array $config The configuration array
     * @param Request $input Optional the request. If null, it will use request() function
     * @param type $obj Optional, the object to save or edit. If null, it would look for one using its $config['id'] value in the $input, or create a new one if not found
     * @param bool $returnChanges Optional if return an array with all the changes made to the object after saving, default false
     * @return Object|array|boolean Object changed, array with changes if $returnChanges is true, or an error response in case of error with uploaded files
     */
    public static function saveObjeto(array $config, \Illuminate\Http\Request $input = null, $obj = null, $returnChanges = false)
    {
        if (is_null($input)) {
            $input = request();
        }
        if (!$obj) {
            $objModelo = new $config['modelo'];
        } else {
            if (!is_object($obj)) {
                $objModelo = $config['modelo']::find($obj);
            } else {
                $objModelo = $obj;
            }
        }

        if ($objModelo) {
            foreach ($config['campos'] as $campo => $detalles) {
                if (!isset($detalles["nodb"])) {
                    switch ($detalles['tipo']) {
                        case 'checkbox':
                        case 'email':
                        case 'html':
                        case 'number':
                        case 'password':
                        case 'color':
                        case 'radio':
                        case 'slider':
                        case 'text':
                        case 'url':
                        case 'textarea':
                        case 'json':
                            if ($input->has($campo)) {
                                if (is_array($input->input($campo))) {
                                    $objModelo->{$campo} = implode(Arr::get($detalles, 'glue', '_'), $input->input($campo));
                                } else {
                                    $objModelo->{$campo} = $input->input($campo);
                                }
                            } elseif (isset($detalles['valor'])) {
                                $objModelo->{$campo} = $detalles['valor'];
                            }
                            break;
                        case 'article':
                            $modelClass = config('sirgrimorum.transarticles.default_articles_model');
                            if (class_exists($modelClass)) {
                                if ($input->has($campo)) {
                                    $nickname = $detalles['scope'] . "." . $objModelo->getKey();
                                    $objModelo->{$campo} = $nickname;
                                } elseif (isset($detalles['valor'])) {
                                    $objModelo->{$campo} = $detalles['valor'] . "." . $objModelo->getKey();
                                }
                            } else {
                                if ($input->has($campo)) {
                                    $objModelo->{$campo} = $input->input($campo);
                                } elseif (isset($detalles['valor'])) {
                                    $objModelo->{$campo} = $detalles['valor'];
                                }
                            }
                            break;
                        case 'hidden':
                            if ($input->has($campo)) {
                                if (CrudGenerator::hasRelation($objModelo, $campo) && CrudGenerator::isJsonString($input->input($campo))) {
                                    if (isset($detalles['id'])) {
                                        $idKeyName = $detalles['id'];
                                    } else {
                                        $idKeyName = $objModelo->{$campo}->getKeyName();
                                    }
                                    $objModelo->{$campo} = $input->input($campo . "." . $objModelo->{$campo}->{$idKeyName});
                                } else {
                                    $objModelo->{$campo} = $input->input($campo);
                                }
                            } elseif (isset($detalles['valor'])) {
                                $objModelo->{$campo} = $detalles['valor'];
                            }
                            break;
                        case 'relationship':
                            if ($input->has($campo)) {
                                if (CrudGenerator::hasRelation($objModelo, $campo)) {
                                    $objModelo->{$campo}()->associate($input->input($campo));
                                } else {
                                    $objModelo->{$campo} = $input->input($campo);
                                }
                            } elseif (isset($detalles['valor'])) {
                                if (CrudGenerator::hasRelation($objModelo, $campo)) {
                                    $objModelo->{$campo}()->associate($detalles['valor']);
                                } else {
                                    $objModelo->{$campo} = $detalles['valor'];
                                }
                            }
                            break;
                        case 'select':
                            if ($input->has($campo)) {
                                if (!isset($detalles["multiple"])) {
                                    $objModelo->{$campo} = $input->input($campo);
                                } elseif ($detalles["multiple"] != "multiple") {
                                    $objModelo->{$campo} = $input->input($campo);
                                } else {
                                    $objModelo->{$campo} = $input->input($campo);
                                }
                            } elseif (isset($detalles['valor'])) {
                                $objModelo->{$campo} = $detalles['valor'];
                            }
                            break;
                        case 'date':
                        case 'datetime':
                        case 'time':
                            if ($input->has($campo)) {
                                if (isset($detalles["timezone"])) {
                                    $timezone = $detalles["timezone"];
                                } else {
                                    $timezone = config("app.timezone");
                                }
                                $date = new \Carbon\Carbon($input->input($campo), $timezone);
                                $objModelo->{$campo} = $date->format("Y-m-d H:i:s");
                            } elseif (isset($detalles['valor'])) {
                                if (isset($detalles["timezone"])) {
                                    $timezone = $detalles["timezone"];
                                } else {
                                    $timezone = config("app.timezone");
                                }
                                $date = new \Carbon\Carbon($detalles['valor'], $timezone);
                                $objModelo->{$campo} = $date->format("Y-m-d H:i:s");
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
            $objModelo->save();

            if ($returnChanges) {
                $cambios = $objModelo->getChanges();
            }

            if ($objModelo) {
                foreach ($config['campos'] as $campo => $detalles) {
                    if (!isset($detalles["nodb"])) {
                        switch ($detalles['tipo']) {
                            case 'article':
                                $modelClass = config('sirgrimorum.transarticles.default_articles_model');
                                if (class_exists($modelClass)) {
                                    if ($input->has($campo)) {
                                        $langColumn = config('sirgrimorum.transarticles.default_lang_column');
                                        $findArticle = config('sirgrimorum.transarticles.default_findarticle_function_name');
                                        $nickname = $detalles['scope'] . "." . $objModelo->getKey();
                                        $objModelo->{$campo} = $nickname;
                                        if (is_array($input->input($campo))) {
                                            foreach ($input->input($campo) as $localeCode => $textoArticulo) {
                                                $article = $modelClass::{$findArticle}($nickname)->where($langColumn, "=", $localeCode)->first();
                                                if (!isset($article)) {
                                                    $article = new $modelClass();
                                                    $segmentsArticle = explode(".", $nickname);
                                                    $scopeArticle = array_shift($segmentsArticle);
                                                    $nicknameArticle = implode(".", $segmentsArticle);
                                                    $article->scope = $scopeArticle;
                                                    $article->nickname = $nicknameArticle;
                                                    $article->lang = $localeCode;
                                                    $article->activated = true;
                                                }
                                                $article->user_id = Auth::user()->id;
                                                $article->content = $textoArticulo;
                                                $article->save();
                                            }
                                        }
                                    } elseif (isset($detalles['valor'])) {
                                        $objModelo->{$campo} = $detalles['valor'] . "." . $objModelo->getKey();
                                    }
                                } else {
                                    if ($input->has($campo)) {
                                        $objModelo->{$campo} = $input->input($campo);
                                    } elseif (isset($detalles['valor'])) {
                                        $objModelo->{$campo} = $detalles['valor'];
                                    }
                                }
                                $objModelo->save();
                                if ($returnChanges) {
                                    $cambios = array_merge($cambios, $objModelo->getChanges());
                                }
                                break;
                            case 'relationships':
                                if ($input->has($campo)) {
                                    //Cuidado porque elimina y crea objetos de tipo $campo
                                    CrudGenerator::syncHasMany($objModelo, $campo, $input->input($campo), $config);
                                    //$objModelo->{$campo}()->sync($input->input($campo));
                                } elseif (isset($detalles['valor'])) {
                                    //Cuidado porque elimina y crea objetos de tipo $campo
                                    CrudGenerator::syncHasMany($objModelo, $campo, $detalles['valor'], $config);
                                    //$objModelo->{$campo}()->sync($detalles['valor']);
                                }
                                break;
                            case 'relationshipssel':
                                if ($input->has($campo)) {
                                    $datos = [];
                                    foreach ($input->input($campo) as $id => $pivot) {
                                        $datos[$id] = [];
                                        foreach ($detalles['columnas'] as $subdetalles) {
                                            if ($subdetalles['tipo'] != "label" && $subdetalles['tipo'] != "labelpivot") {
                                                if ($input->has($campo . "_" . $subdetalles['campo'] . "_" . $id)) {
                                                    $datos[$id][$subdetalles['campo']] = $input->input($campo . "_" . $subdetalles['campo'] . "_" . $id);
                                                } else {
                                                    $datos[$id][$subdetalles['campo']] = $subdetalles['valor'];
                                                }
                                            }
                                        }
                                    }
                                    $objModelo->{$campo}()->sync($datos);
                                } else {
                                    $objModelo->{$campo}()->sync([]);
                                }
                                break;
                            case 'file':
                                $existFile = $objModelo->{$campo};
                                $filename = "";
                                if ($input->has($campo)) {
                                    $filename = CrudGenerator::saveFileFromRequest($objModelo, $input, $campo, $detalles);
                                    if ($filename !== false) {
                                        $objModelo->{$campo} = $filename;
                                        if ($existFile != "") {
                                            if (isset($detalles['removeFunction']) && is_callable($detalles['removeFunction'])) {
                                                $detalles['removeFunction']($objModelo, $existFile, $detalles);
                                            } else {
                                                if (isset($detalles['path'])) {
                                                    $path = Str::finish($detalles['path'], '\\');
                                                } else {
                                                    $path = "";
                                                }
                                                CrudGenerator::removeFile(Str::start($existFile, $path), Arr::get($detalles, "disk", "local"));
                                            }
                                        }
                                    } else {
                                        $filename = "";
                                        // Return with input????
                                    }
                                } else {
                                    if (!$input->has($campo . "_filereg") && $existFile != "") {
                                        if (isset($detalles['removeFunction']) && is_callable($detalles['removeFunction'])) {
                                            $detalles['removeFunction']($objModelo, $existFile, $detalles);
                                        } else {
                                            if (isset($detalles['path'])) {
                                                $path = Str::finish($detalles['path'], '\\');
                                            } else {
                                                $path = "";
                                            }
                                            CrudGenerator::removeFile(Str::start($existFile, $path), Arr::get($detalles, "disk", "local"));
                                        }
                                        $filename = "";
                                        $existFile = "";
                                    } elseif ($input->has($campo . "_filereg") && $existFile != "") {
                                        $filename = $existFile;
                                    }
                                }
                                if ($filename == "" && $existFile == "" && isset($detalles['valor'])) {
                                    $objModelo->{$campo} = $detalles['valor'];
                                } elseif ($filename == "") {
                                    $objModelo->{$campo} = null;
                                }
                                $objModelo->save();
                                if ($returnChanges) {
                                    $cambios = array_merge($cambios, $objModelo->getChanges());
                                }
                                break;
                            case 'files':
                                $existFiles = $objModelo->{$campo};
                                if (is_string($existFiles)) {
                                    $existFiles = json_decode($existFiles, true);
                                }
                                $masFiles = [];
                                if ($input->has($campo)) {
                                    $paraGuardar = [];
                                    for ($index = 0; $index < count($input->$campo); $index++) {
                                        $filename = CrudGenerator::saveFileFromRequest($objModelo, $input, $campo . "." . $index, $detalles);
                                        if ($filename !== false) {
                                            $paraGuardar[] = [
                                                "name" => $input->input($campo . "_name." . $index),
                                                "file" => $filename
                                            ];
                                        } else {
                                            // Return with input????
                                        }
                                    }
                                    if (count($paraGuardar) > 0) {
                                        $masFiles = $paraGuardar;
                                    }
                                }
                                $finalFiles = [];
                                if (is_array($existFiles)) {
                                    foreach ($existFiles as $existFile) {
                                        $esta = false;
                                        if ($input->has($campo . "_filereg")) {
                                            $preReg = $input->input($campo . "_filereg");
                                            $preRegName = $input->input($campo . "_namereg");
                                            for ($index = 0; $index < count($preReg); $index++) {
                                                if ($existFile['file'] == $preReg[$index]) {
                                                    $finalFiles[] = [
                                                        'name' => $preRegName[$index],
                                                        'file' => $preReg[$index]
                                                    ];
                                                    $esta = true;
                                                    break;
                                                }
                                            }
                                        }
                                        if (!$esta) {
                                            if (isset($detalles['removeFunction']) && is_callable($detalles['removeFunction'])) {
                                                $detalles['removeFunction']($objModelo, $existFile['file'], $detalles);
                                            } else {
                                                if (isset($detalles['path'])) {
                                                    $path = Str::finish($detalles['path'], '\\');
                                                } else {
                                                    $path = "";
                                                }
                                                CrudGenerator::removeFile(Str::start($existFile['file'], $path), Arr::get($detalles, "disk", "local"));
                                            }
                                        }
                                    }
                                    $finalFiles = array_merge($finalFiles, $masFiles);
                                } else {
                                    $finalFiles = $masFiles;
                                }

                                if (count($finalFiles) && isset($detalles['valor'])) {
                                    if (!is_array($detalles['valor'])) {
                                        $objModelo->{$campo} = json_encode(["name" => $detalles['valor'], "file" => $detalles['valor']]);
                                    } else {
                                        $objModelo->{$campo} = json_encode($detalles['valor']);
                                    }
                                } else {
                                    $objModelo->{$campo} = json_encode($finalFiles);
                                }
                                $objModelo->save();
                                if ($returnChanges) {
                                    $cambios = array_merge($cambios, $objModelo->getChanges());
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
            if ($config['tabla'] == "articles") {
                Artisan::call('view:clear');
            }
            if ($returnChanges) {
                return $cambios;
            }
            return $objModelo;
        } else {
            return false;
        }
    }

    /**
     * Save an uploaded file from a configuration array
     * @param object $objModelo The model
     * @param Request $input The request
     * @param string $campo The file field name
     * @param array $detalles the Field configuration array
     * @param boolean $addNewName Optional, if true, will add the new field name to the filename (assumed in $campo . "_name" in input)
     * @return boolean|string The name of the faile to save in the bd or false if something went wrong
     */
    public static function saveFileFromRequest($objModelo, \Illuminate\Http\Request $input, $campo, array $detalles, $addNewName = true)
    {
        if ($input->hasFile($campo)) {
            $file = $input->file($campo);
            if ($file) {
                try {
                    if (substr($file->getMimeType(), 0, 5) == 'image') {
                        $esImagen = true;
                    } else {
                        $esImagen = false;
                    }
                } catch (Error $err) {
                    $esImagen = false;
                }
                $filename = "";
                if (isset($detalles['pre'])) {
                    if ($detalles['pre'] == '_originalName_') {
                        $filename = $file->getClientOriginalName();
                    } else {
                        $filename = $detalles['pre'];
                    }
                }
                if (isset($detalles['length'])) {
                    $numRand = $detalles['length'];
                } else {
                    $numRand = 20;
                }
                if (stripos($campo, ".") > 0) {
                    $campoName = str_replace(".", "_name.", $campo);
                } else {
                    $campoName = $campo . "_name";
                }
                $new_name = "";
                if ($input->has($campoName) && $addNewName) {
                    $new_name = str_replace(" ", "_", $input->input($campoName));
                    $new_name = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $new_name);
                    $new_name = mb_ereg_replace("([\.]{2,})", '', $new_name);
                    $new_name = "__" . $new_name;
                }
                $filename .= Str::random($numRand) . $new_name;
                $filename .= "." . $file->getClientOriginalExtension();
                $destinationPath = false;
                if (isset($detalles['saveFunction']) && is_callable($detalles['saveFunction'])) {
                    $path = $detalles['saveFunction']($objModelo, $file, $filename, $detalles);
                    $esImagen = false;
                } else {
                    if (isset($detalles['path'])) {
                        $destinationPath = Str::finish($detalles['path'], '/');
                    } else {
                        $destinationPath = '';
                    }
                    $disk = Arr::get($detalles, "disk", "local");
                    $path = $file->storeAs($destinationPath, $filename, $disk);
                }
                $upload_success = $path !== false;
                if ($upload_success) {
                    $newFilename = $filename;
                    if (isset($detalles['saveCompletePath'])) {
                        if ($detalles['saveCompletePath']) {
                            $newFilename = $path;
                            //$newFilename = Str::finish(str_replace("/", "\\", $detalles['path']), "\\") . $filename;
                        }
                    }
                    if ($esImagen && isset($detalles['resize']) && class_exists('Intervention\Image\Image')) {
                        foreach ($detalles['resize'] as $resize) {
                            $imageManager = new \Intervention\Image\ImageManager(['driver' => 'imagick']);
                            $modelo = strtolower(class_basename($objModelo));
                            $image_resize = $imageManager->make(CrudGenerator::getFileUrl($newFilename, $objModelo, $modelo, $campo, $detalles));
                            $width = 0;
                            if (isset($resize['width'])) {
                                $width = $resize['width'];
                            }
                            $height = 0;
                            if (isset($resize['height'])) {
                                $height = $resize['height'];
                            }
                            if ($width > 0 || $height > 0) {
                                if ($width == 0 || $height == 0) {
                                    $image_resize->resize($width, $height, function ($constraint) {
                                        $constraint->aspectRatio();
                                    });
                                } else {
                                    $image_resize->resize($width, $height);
                                }
                            }
                            $destinationPath = Str::finish(public_path($resize['path']), '/');
                            $quality = 90;
                            if (isset($resize['quality'])) {
                                $quality = $resize['quality'];
                            }
                            $content = (string) $image_resize->encode(null, $quality);
                            CrudGenerator::getDisk($detalles)->put($destinationPath . $filename, $content);
                        }
                        // resizing an uploaded file
                        //return Response::json('success', 200);
                    }
                    return $newFilename;
                } else {
                    return false;
                    return Response::json('error', 400);
                }
            }
        }
        return false;
    }

    /**
     * Build the array for conditional fields using a configuration array
     * @param array $config The configuration array
     * @param string $action Optional the action (create, edit, etc) where the conditionals are needed
     */
    public static function buildConditionalArray(array $config, string $action = "-")
    {
        if ($action == "") {
            $action = substr(request()->route()->getName(), stripos(request()->route()->getName(), "::") + 2);
        }
        $condiciones = [];
        $validadores = [];
        $tabla = $config['tabla'];
        foreach ($config['campos'] as $campo => $datos) {
            if (CrudGenerator::inside_array($datos, "hide", $action) === false) {
                if (isset($datos['conditional'])) {
                    if (is_array($datos['conditional'])) {
                        $validadores["{$tabla}_{$campo}"] = [];
                        foreach ($datos['conditional'] as $conCampo => $conValor) {
                            if (!isset($condiciones["{$tabla}_{$conCampo}"])) {
                                $condiciones["{$tabla}_{$conCampo}"] = [];
                            }
                            $condiciones["{$tabla}_{$conCampo}"][] = "{$tabla}_{$campo}";
                            $validadores["{$tabla}_{$campo}"]["{$tabla}_{$conCampo}"] = $conValor;
                        }
                    }
                }
            }
        }
        return [$condiciones, $validadores];
    }
}
