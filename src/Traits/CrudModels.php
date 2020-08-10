<?php

namespace Sirgrimorum\CrudGenerator\Traits;

use Illuminate\Support\Facades\Storage;

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
            $action = substr(request()->route()->getName(), stripos(request()->route()->getName(), "::") + 2);
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
     * Generate a list of objects of a model in array format
     * Could returns an array with 2 arrays in it:
     * Complete, with value, label and data for each field in position 0
     * and Simple, only with value per field at position 1
     * 
     * @param array $config Configuration array
     * @param Model() $registros Optional Array of objects to show
     * @param boolean|string $solo Optional if false, will return the complete an simple array, if 'simple' only the simple one, if 'complete' only the complete one
     * @return array with the objects in the config format
     */
    public static function lists_array($config, $registros = null, $solo = 'complete')
    {
        //$config = \Sirgrimorum\CrudGenerator\CrudGenerator::translateConfig($config);
        if ($registros == null) {
            $modeloM = ucfirst($config['modelo']);
            $registros = $modeloM::all();
        }
        //if (request()->)
        $registros = \Sirgrimorum\CrudGenerator\CrudGenerator::filterWithQuery($registros, $config);
        $return = [];
        $returnSimple = [];
        foreach ($registros as $registro) {
            list($row, $rowSimple) = \Sirgrimorum\CrudGenerator\CrudGenerator::registry_array($config, $registro);
            $return[] = $row;
            $returnSimple[] = $rowSimple;
        }
        if ($solo == 'simple') {
            return $returnSimple;
        } elseif ($solo == 'complete') {
            return $return;
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
     * @param Model() $registro Optional object to show
     * @param boolean|string $solo Optional if false, will return the complete an simple array, if 'simple' only the simple one, if 'complete' only the complete one
     * @return array with the attributes in the config format
     */
    public static function registry_array($config, $registro = null, $solo = false)
    {
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
        $tablaid = $tabla . "_" . \Illuminate\Support\Str::random(5);
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
            $celda = \Sirgrimorum\CrudGenerator\CrudGenerator::field_array($value, $columna, $datos);
            $row[$columna] = $celda;
            $rowSimple[$columna] = $celda['value'];
        }
        if (is_array($botones)) {
            $celda = "";
            foreach ($botones as $boton) {
                $celda .= str_replace([":modelId", ":modelName"], [$value->{$identificador}, $value->{$nombre}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$value->{$identificador}, $value->{$nombre}], $boton));
            }
            $row["botones"] = $celda;
        } else {
            $celda = str_replace([":modelId", ":modelName"], [$value->{$identificador}, $value->{$nombre}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$value->{$identificador}, $value->{$nombre}], $botones));
            $row["botones"] = $celda;
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
     * Returns an array with 3 elements:
     *      data: with the detailed value
     *      label: with the translated label of the field
     *      value: with the formated value of the field
     * 
     * @param array $config Configuration array
     * @param Model() $value The object
     * @param string $columna The field to show
     * @param array $config Optional The configuration array for the field
     * @return array with the values in the config format
     */
    public static function field_array($value, $columna, $datos = "")
    {
        if ($datos == "") {
            $modelo = strtolower(class_basename(get_class($value)));
            $config = \Sirgrimorum\CrudGenerator\CrudGenerator::getConfigWithParametros($modelo);
            if (isset($config['campos'][$columna])) {
                if (is_array($config['campos'][$columna])) {
                    $datos = $config['campos'][$columna];
                } else {
                    $celda = [
                        "data" => $value->{$columna},
                        "label" => $columna,
                        "value" => $value->{$columna}
                    ];
                    return $celda;
                }
            } else {
                $celda = [
                    "data" => $value->{$columna},
                    "label" => $columna,
                    "value" => $value->{$columna}
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
            if (\Sirgrimorum\CrudGenerator\CrudGenerator::hasRelation($value, $columna)) {
                if (array_key_exists('enlace', $datos)) {
                    $auxcelda = '<a href = "' . str_replace([":modelId", ":modelName"], [$value->{$columna}->{$datos['id']}, $value->{$columna}->{$datos['nombre']}], str_replace([urlencode(":modelId"), urlencode(": modelName")], [$value->{$columna}->{$datos['id']}, $value->{$columna}->{$datos['nombre']}], $datos['enlace'])) . '">';
                }
                $celda['data'] = \Sirgrimorum\CrudGenerator\CrudGenerator::getNombreDeLista($value->{$columna}, $datos['campo']);
                $celda['label'] = $datos['label'];
                $auxcelda = \Sirgrimorum\CrudGenerator\CrudGenerator::getNombreDeLista($value->{$columna}, $datos['campo']);
                if (array_key_exists('enlace', $datos)) {
                    $auxcelda = '< / a>';
                }
                $celda['value'] = $auxcelda;
            } else {
                $celda = '-';
            }
        } elseif ($datos['tipo'] == "relationships") {
            if (\Sirgrimorum\CrudGenerator\CrudGenerator::hasRelation($value, $columna)) {
                $celda = [];
                $auxcelda2 = "";
                $prefijo = "<ul><li>";
                foreach ($value->{$columna}()->get() as $sub) {
                    $auxcelda = "";
                    if (array_key_exists('enlace', $datos)) {
                        $auxcelda2 .= $prefijo . '<a href = "' . str_replace([":modelId", ":modelName"], [$sub->{$datos['id']}, $sub->{$datos['nombre']}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$sub->{$datos['id']}, $sub->{$datos['nombre']}], $datos['enlace'])) . '">';
                    } else {
                        $auxcelda2 .= $prefijo;
                    }
                    $auxcelda = \Sirgrimorum\CrudGenerator\CrudGenerator::getNombreDeLista($sub, $datos['campo']);
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
            }
        } elseif ($datos['tipo'] == "relationshipssel") {
            if (\Sirgrimorum\CrudGenerator\CrudGenerator::hasRelation($value, $columna)) {
                $celda = [];
                $auxcelda3 = "";
                $prefijo = "<ul><li>";
                foreach ($value->{$columna}()->get() as $sub) {
                    $celda[$sub->getKey()] = [];
                    $auxcelda = "";
                    if (array_key_exists('enlace', $datos)) {
                        $auxcelda = '<a href = "' . str_replace([":modelId", ":modelName"], [$sub->{$datos['id']}, $sub->{$datos['nombre']}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$sub->{$datos['id']}, $sub->{$datos['nombre']}], $datos['enlace'])) . '">';
                    }
                    $auxcelda2 = \Sirgrimorum\CrudGenerator\CrudGenerator::getNombreDeLista($sub, $datos['campo']);
                    $auxcelda .= $auxcelda2;
                    if (array_key_exists('enlace', $datos)) {
                        $auxcelda .= '</a>';
                    }
                    $auxcelda3 .= $prefijo . $auxcelda;
                    $auxcelda4 = "";
                    $auxcelda5 = "";
                    $prefijo2 = "<ul><li>";
                    if (array_key_exists('columnas', $datos)) {
                        if (is_array($datos['columnas'])) {
                            if (is_object($sub->pivot)) {
                                $celda[$sub->getKey()]['data'] = [];
                                foreach ($datos['columnas'] as $infoPivote) {
                                    if ($infoPivote['type'] != "hidden" && $infoPivote['type'] != "label") {
                                        $celda[$sub->getKey()]['data'][$infoPivote['campo']] = ['label' => $infoPivote['label']];
                                        if ($infoPivote['type'] == "number" && isset($infoPivote['format'])) {
                                            $celda[$sub->getKey()]['data'][$infoPivote['campo']]['value'] = number_format($sub->pivot->{$infoPivote['campo']}, $infoPivote['format'][0], $infoPivote['format'][1], $infoPivote['format'][2]);
                                        } elseif ($infoPivote['type'] == "select" && isset($infoPivote['opciones'])) {
                                            $celda[$sub->getKey()]['data'][$infoPivote['campo']]['value'] = $infoPivote['opciones'][$sub->pivot->{$infoPivote['campo']}];
                                        } else {
                                            $celda[$sub->getKey()]['data'][$infoPivote['campo']]['value'] = $sub->pivot->{$infoPivote['campo']} . ', ';
                                        }
                                        $auxcelda4 .= $prefijo2 . $celda[$sub->getKey()]['data'][$infoPivote['campo']]['value'] . "</li>";
                                        $prefijo2 = "<li>";
                                    } elseif ($infoPivote['type'] == "label") {
                                        if (isset($infoPivote['campo'])) {
                                            $auxcelda5 = \Sirgrimorum\CrudGenerator\CrudGenerator::getNombreDeLista($sub, $infoPivote['campo']);
                                        } else {
                                            $auxcelda5 = $infoPivote['label'];
                                        }
                                    }
                                }
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
                $celda = [
                    "data" => $celda,
                    "label" => $datos['label'],
                    "value" => $auxcelda3
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
            if (is_array($datos['value'])) {
                if (array_key_exists($value->{$columna}, $datos['value'])) {
                    $auxcelda = $datos['value'][$value->{$columna}];
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
            $celda['data'] = $value->{$columna};
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
                    setlocale(LC_TIME, \App::getLocale(), strtoupper(\App::getLocale()), \App::getLocale() . "_" . strtoupper(\App::getLocale()));
                    \Carbon\Carbon::setUtf8(true);
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
        } elseif ($datos['tipo'] == "url" || ($datos['tipo'] == "file" && \Illuminate\Support\Str::startsWith(strtolower($value->{$columna}), ["http:", "https:"]))) {
            $celda = [
                'value' => $value->{$columna},
                'data' => $value->{$columna},
            ];
            if (\Sirgrimorum\CrudGenerator\CrudGenerator::urlType($value->{$columna}) == "youtube") {
                $youtubeId = \Sirgrimorum\CrudGenerator\CrudGenerator::getYoutubeId($value->{$columna});
                $celda['embed'] = "https://www.youtube.com/embed/" . $youtubeId;
            } else {
                $celda['embed'] = $value->{$columna};
            }
            $celda['label'] = $datos['label'];
        } elseif ($datos['tipo'] == "article" && class_exists(config('sirgrimorum.transarticles.default_articles_model'))) {
            $modelClass = config('sirgrimorum.transarticles.default_articles_model');
            $langColumn = config('sirgrimorum.transarticles.default_lang_column');
            $findArticle = config('sirgrimorum.transarticles.default_findarticle_function_name');
            $article = $modelClass::{$findArticle}($value->{$columna})->where($langColumn, "=", \App::getLocale())->first();
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
            $celda['data'] = json_decode($value->{$columna});
            $celda['label'] = $datos['label'];
            $celda['value'] = $value->{$columna};
        } elseif ($datos['tipo'] == "file") {
            if ($value->{$columna} == "") {
                $celda = '';
            } else {
                $filename = Storage::disk(\Illuminate\Support\Arr::get($datos,"disk","local"))->url(\Illuminate\Support\Str::start($value->{$columna}, \Illuminate\Support\Str::finish($datos['path'], '\\'))) ;
                $tipoFile = \Sirgrimorum\CrudGenerator\CrudGenerator::filenameIs($value->{$columna}, $datos);
                $auxprevioName = substr($value->{$columna}, stripos($value->{$columna}, '__') + 2, stripos($value->{$columna}, '.', stripos($value->{$columna}, '__')) - (stripos($value->{$columna}, '__') + 2));
                $celda = [
                    "name" => $auxprevioName,
                    "value" => $filename,
                    "label" => $datos['label'],
                    "type" => $tipoFile
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
                foreach ($auxprevios as $datoReg) {
                    $filename =  Storage::disk(\Illuminate\Support\Arr::get($datos,"disk","local"))->url(\Illuminate\Support\Str::start($datoReg->file, \Illuminate\Support\Str::finish($datos['path'], '\\')));
                    $tipoFile = \Sirgrimorum\CrudGenerator\CrudGenerator::filenameIs($datoReg->file, $datos);
                    $celda['data'][] = [
                        "name" => $datoReg->name,
                        "value" => $filename,
                        "type" => $tipoFile
                    ];
                }
            }
        } else {
            if (array_key_exists('enlace', $datos)) {
                $auxcelda = '<a href = "' . str_replace([":modelId", ":modelName"], [$value->{$identificador}, $value->{$nombre}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$value->{$identificador}, $value->{$nombre}], $datos['enlace'])) . '">';
            }
            if ($datos['tipo'] == "number" && isset($datos['format'])) {
                if (is_array($datos['format'])) {
                    $auxcelda .= number_format($value->{$columna}, $datos['format'][0], $datos['format'][1], $datos['format'][2]);
                } else {
                    $auxcelda .= number_format($value->{$columna});
                }
            } else {
                $auxcelda .= $value->{$columna};
            }
            $celda['data'] = $value->{$columna};
            $celda['label'] = $datos['label'];

            if (array_key_exists('enlace', $datos)) {
                $auxcelda = '</a>';
            }
            $celda['value'] = $auxcelda;
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
        }
        if (isset($celda['post']) && is_string($celda['value'])) {
            $celda['value'] = $celda['value'] . \Illuminate\Support\Str::start($celda['post'], " ");
        }
        return $celda;
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
     * @param string $query The query to evaluate
     * @param string $attri The attribute to compare
     * @return boolean
     */
    private static function evaluateFilterWithSingleQuery($registro, $query, $attri)
    {
        //echo "<p>evaluando {$registro->name}</p><pre>" . print_r([$query, $attri], true) . "</pre>";
        $contiene = false;
        if (stripos($query, "*%") !== false) {
            $contiene = true;
            $query = str_replace("*%", "", $query);
        }

        if (($numArgs = \Sirgrimorum\CrudGenerator\CrudGenerator::isFunction($registro, $attri)) !== false) {
            //echo "<p>NumArgs $numArgs</p>";
            if (\Sirgrimorum\CrudGenerator\CrudGenerator::isJsonString($query)) {
                $queryArr = json_decode($query, true);
            } else {
                $queryArr = [$query];
            }
            $result = \Sirgrimorum\CrudGenerator\CrudGenerator::callFunction($registro, $attri, $queryArr, $numArgs);
            if ($result === false || $result === null) {
                return false;
            } else {
                $queryObj = array_pop($queryArr);
                if ($result != $queryObj) {
                    return false;
                }
            }
        } elseif (is_string($registro->{$attri})) {
            //echo "<p> stripos es " . stripos($registro->{$attri}, $query) . " jajaj</p>";
            if ($contiene) {
                if (stripos($registro->{$attri}, $query) === false) {
                    return false;
                }
            } else {
                if ($registro->{$attri} != $query) {
                    return false;
                }
            }
        } else {
            if ((string) $registro->{$attri} != $query) {
                return false;
            }
        }
        return true;
    }

    /**
     * Filter an object of a model with a query comparing against an attribute value.
     * 
     * If $query and/or $attri ar arrays. It will use AND operation.
     * 
     * If $query contains "*%" it will erase them and evaluate if $query is contained in the attribute value. 
     * Not aplicable for function or methods returns
     * 
     * @param object $registro The model object
     * @param string|array $query The query or querys to compare
     * @param string|array $attri The attribute or attributes to compare with. Could evaluate methods and functions.
     * @param boolean $orOperation Optional, if use or operation (just one query must be true), false will use and operation (all the querys must be true).
     * @param boolean $fbf Optional, default false. If the query and attributes arrays must be evaluated one by one (ej: $query[0] vs $attribute[0] AND $query[1] vs $attribute[1], ...) The size of $attri and $query must be the same
     * @return boolean
     */
    private static function evaluateFilter($registro, $query, $attri, $orOperation = true, $fbf = false)
    {
        if ($fbf && isset($attri) == isset($query)) {
            if (!count($attri) == count($query)) {
                $fbf = false;
            }
        } else {
            $fbf = false;
        }

        if ($fbf) {
            for ($index = 0; $index < count($query); $index++) {
                if (is_array($attri[$index]) || is_object($attri[$index])) {
                    $attriStr = json_encode($attri[$index]);
                } else {
                    $attriStr = $attri[$index];
                }
                if (is_array($query[$index]) || is_object($query[$index])) {
                    $queryStr = json_encode($query[$index]);
                } else {
                    $queryStr = $query[$index];
                }
                if (!\Sirgrimorum\CrudGenerator\CrudGenerator::evaluateFilterWithSingleQuery($registro, $queryStr, $attriStr)) {
                    //echo "<p><strong>No</strong></p>";
                    if (!$orOperation) {
                        return false;
                    }
                } else {
                    //echo "<p><strong>Si</strong></p>";
                    if ($orOperation) {
                        return true;
                    }
                }
            }
        } elseif (is_array($attri)) {
            foreach ($attri as $attribute) {
                if (is_array($attribute) || is_object($attribute)) {
                    $attriStr = json_encode($attribute);
                } else {
                    $attriStr = $attribute;
                }
                if (is_array($query)) {
                    foreach ($query as $singleQuery) {
                        if (is_array($singleQuery) || is_object($singleQuery)) {
                            $queryStr = json_encode($singleQuery);
                        } else {
                            $queryStr = $singleQuery;
                        }
                        if (!\Sirgrimorum\CrudGenerator\CrudGenerator::evaluateFilterWithSingleQuery($registro, $queryStr, $attriStr)) {
                            //echo "<p><strong>No</strong></p>";
                            if (!$orOperation) {
                                return false;
                            }
                        } else {
                            //echo "<p><strong>Si</strong></p>";
                            if ($orOperation) {
                                return true;
                            }
                        }
                    }
                } else {
                    if (is_array($query) || is_object($query)) {
                        $queryStr = json_encode($query);
                    } else {
                        $queryStr = $query;
                    }
                    if (!\Sirgrimorum\CrudGenerator\CrudGenerator::evaluateFilterWithSingleQuery($registro, $queryStr, $attriStr)) {
                        //echo "<p><strong>No</strong></p>";
                        if (!$orOperation) {
                            return false;
                        }
                    } else {
                        //echo "<p><strong>Si</strong></p>";
                        if ($orOperation) {
                            return true;
                        }
                    }
                }
            }
        } else {
            if (is_array($attri) || is_object($attri)) {
                $attriStr = json_encode($attri);
            } else {
                $attriStr = $attri;
            }
            if (is_array($query)) {
                foreach ($query as $singleQuery) {
                    if (is_array($singleQuery) || is_object($singleQuery)) {
                        $queryStr = json_encode($singleQuery);
                    } else {
                        $queryStr = $singleQuery;
                    }
                    if (!\Sirgrimorum\CrudGenerator\CrudGenerator::evaluateFilterWithSingleQuery($registro, $queryStr, $attriStr)) {
                        //echo "<p><strong>No</strong></p>";
                        if (!$orOperation) {
                            return false;
                        }
                    } else {
                        //echo "<p><strong>Si</strong></p>";
                        if ($orOperation) {
                            return true;
                        }
                    }
                }
            } else {
                if (is_array($query) || is_object($query)) {
                    $queryStr = json_encode($query);
                } else {
                    $queryStr = $query;
                }
                if (!\Sirgrimorum\CrudGenerator\CrudGenerator::evaluateFilterWithSingleQuery($registro, $queryStr, $attriStr)) {
                    //echo "<p><strong>No</strong></p>";
                    if (!$orOperation) {
                        return false;
                    }
                } else {
                    //echo "<p><strong>Si</strong></p>";
                    if ($orOperation) {
                        return true;
                    }
                }
            }
        }
        if ($orOperation) {
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
     * If not attribute is given, it will compare against the $config('nombre'] attribute
     * 
     * If $query contains "*%" it will erase them and evaluate if $query is contained in the attribute value. 
     * Not aplicable for function or methods returns
     * 
     * @param object $registros Collection of elocuent model objects
     * @param array $config Configuration array for the Model
     * @param boolean|string $orOperation Optional boolean or the key of the or value in $datos. if True: use or operation (just one query must be true), false will use and operation (all the querys must be true).
     * @param array $datos Optional the data. if empty, it will get the current request data.
     * @param string $queryStr Optional the key of the query in $datos
     * @param string $attriStr Optional the key of the attributes in $datos
     * @param string $aByAStr Optional the key of the value indicating if the $query and $attribute must be evaluated one by one (ej: $query[0] vs $attribute[0] AND $query[1] vs $attribute[1], ...)
     * @return array Collection filtered
     */
    private static function filterWithQuery($registros, $config, $datos = [], $orOperation = "_or", $queryStr = "_q", $attriStr = "_a", $aByAStr = "_aByA")
    {
        if (count($datos) == 0) {
            $datos = request()->all();
        }
        //echo "<pre>" . print_r($datos, true) . "</pre>";
        if (!is_bool($orOperation)) {
            if (isset($datos[$orOperation])) {
                $orOperation = !($datos[$orOperation] === 'false');
            } else {
                $orOperation = true;
            }
        }
        if (isset($datos[$queryStr])) {
            $query = $datos[$queryStr];
            if (\Sirgrimorum\CrudGenerator\CrudGenerator::isJsonString($query)) {
                $query = json_decode($query, true);
            } elseif (stripos($query, "|")) {
                $query = explode("|", $query);
            }
            if (isset($datos[$attriStr])) {
                $attri = $datos[$attriStr];
                if (\Sirgrimorum\CrudGenerator\CrudGenerator::isJsonString($attri)) {
                    $attri = json_decode($attri, true);
                } elseif (stripos($attri, "|")) {
                    $attri = explode("|", $attri);
                }
            } else {
                $attri = $config['nombre'];
            }
            $fbf = isset($datos[$aByAStr]);
            $registros = $registros->filter(function ($registro) use ($query, $attri, $fbf, $orOperation) {
                return \Sirgrimorum\CrudGenerator\CrudGenerator::evaluateFilter($registro, $query, $attri, $orOperation, $fbf);
            });
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
        $children = $model->{$campo};
        $children_items = collect($children_items);
        $deleted_ids = $children->filter(
            function ($child) use ($children_items) {
                return empty($children_items->where($config[$campo]['id'], $child->$config[$campo]['id'])->first());
            }
        )->map(
            function ($child) {
                $id = $child->id;
                $child->delete();
                return $id;
            }
        );
        $attachments = $children_items->filter(
            function ($children_item) {
                return empty($children_item->$config[$campo]['id']);
            }
        )->map(function ($children_item) use ($deleted_ids) {
            $children_item->$config[$campo]['id'] = $deleted_ids->pop();
            return new $config[$campo]['modelo']($children_item);
        });
        $model->{$campo}()->saveMany($attachments);
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

        $rules = \Sirgrimorum\CrudGenerator\CrudGenerator::translateArray($rules, ":model", function ($string) use ($auxIdCambio) {
            return $auxIdCambio;
        }, "Id");
        if (count($rules) > 0) {
            $customAttributes = [];
            foreach ($rules as $field => $datos) {
                if (\Illuminate\Support\Arr::has($config, "campos." . $field . ".label")) {
                    $customAttributes[$field] = \Illuminate\Support\Arr::get($config, "campos." . $field . ".label");
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
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $error_messages, $customAttributes);
            return $validator;
        }
        return false;
    }

    /**
     * Save a new object or edit an existing one base on its configuration array
     * @param array $config The configuration array
     * @param Request $input Optional the request. If null, it will use request() function
     * @param type $obj Optional, the object to save or edit. If null, it would look for one using its $config['id'] value in the $input, or create a new one if not found
     * @return Object|boolean The saved object or false in case of error with uploaded files
     */
    public static function saveObjeto(array $config, \Illuminate\Http\Request $input = null, $obj = null)
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
                        case 'radio':
                        case 'slider':
                        case 'text':
                        case 'url':
                        case 'textarea':
                        case 'json':
                            if ($input->has($campo)) {
                                $objModelo->{$campo} = $input->input($campo);
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
                                if (\Sirgrimorum\CrudGenerator\CrudGenerator::hasRelation($objModelo, $campo) && \Sirgrimorum\CrudGenerator\CrudGenerator::isJsonString($input->input($campo))) {
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
                                if (\Sirgrimorum\CrudGenerator\CrudGenerator::hasRelation($objModelo, $campo)) {
                                    $objModelo->{$campo}()->associate($input->input($campo));
                                } else {
                                    $objModelo->{$campo} = $input->input($campo);
                                }
                            } elseif (isset($detalles['valor'])) {
                                if (\Sirgrimorum\CrudGenerator\CrudGenerator::hasRelation($objModelo, $campo)) {
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
                        case 'file':
                            $existFile = $objModelo->{$campo};
                            $filename = "";
                            if ($input->has($campo)) {
                                $filename = \Sirgrimorum\CrudGenerator\CrudGenerator::saveFileFromRequest($input, $campo, $detalles);
                                if ($filename !== false) {
                                    $objModelo->{$campo} = $filename;
                                    if ($existFile != "") {
                                        \Sirgrimorum\CrudGenerator\CrudGenerator::removeFile(\Illuminate\Support\Str::start($existFile, \Illuminate\Support\Str::finish($detalles['path'], '\\')), \Illuminate\Support\Arr::get($detalles, "disk", "local"));
                                    }
                                } else {
                                    $filename = "";
                                    // Return with input????
                                }
                            } else {
                                if (!$input->has($campo . "_filereg") && $existFile != "") {
                                    \Sirgrimorum\CrudGenerator\CrudGenerator::removeFile(\Illuminate\Support\Str::start($existFile, \Illuminate\Support\Str::finish($detalles['path'], '\\')), \Illuminate\Support\Arr::get($detalles, "disk", "local"));
                                } elseif ($input->has($campo . "_filereg") && $existFile != "") {
                                    $filename = $existFile;
                                }
                            }
                            if ($filename == "" && $existFile != "" && isset($detalles['valor'])) {
                                $objModelo->{$campo} = $detalles['valor'];
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
                                    $filename = \Sirgrimorum\CrudGenerator\CrudGenerator::saveFileFromRequest($input, $campo . "." . $index, $detalles);
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
                                        \Sirgrimorum\CrudGenerator\CrudGenerator::removeFile(\Illuminate\Support\Str::start($existFile['file'], \Illuminate\Support\Str::finish($detalles['path'], '\\')), \Illuminate\Support\Arr::get($detalles, "disk", "local"));
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

                            break;
                        default:
                            break;
                    }
                }
            }
            $objModelo->save();

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
                                                $article->user_id = \Illuminate\Support\Facades\Auth::user()->id;
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
                                break;
                            case 'relationships':
                                if ($input->has($campo)) {
                                    //Cuidado porque elimina y crea objetos de tipo $campo
                                    \Sirgrimorum\CrudGenerator\CrudGenerator::syncHasMany($objModelo, $campo, $input->input($campo), $config);
                                    //$objModelo->{$campo}()->sync($input->input($campo));
                                } elseif (isset($detalles['valor'])) {
                                    //Cuidado porque elimina y crea objetos de tipo $campo
                                    \Sirgrimorum\CrudGenerator\CrudGenerator::syncHasMany($objModelo, $campo, $detalles['valor'], $config);
                                    //$objModelo->{$campo}()->sync($detalles['valor']);
                                }
                                break;
                            case 'relationshipssel':
                                if ($input->has($campo)) {
                                    $datos = [];
                                    foreach ($input->input($campo) as $id => $pivot) {
                                        $datos[$id] = [];
                                        foreach ($detalles['columnas'] as $subdetalles) {
                                            if ($subdetalles['type'] != "label" && $subdetalles['type'] != "labelpivot") {
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
                            default:
                                break;
                        }
                    }
                }
            }
            if ($config['tabla'] == "articles") {
                \Illuminate\Support\Facades\Artisan::call('view:clear');
            }
            return $objModelo;
        } else {
            return false;
        }
    }

    /**
     * Save an uploaded file from a configuration array
     * @param Request $input The request
     * @param strinf $campo The file field name
     * @param array $detalles the Field configuration array
     * @param boolean $addNewName Optional, if true, will add the new field name to the filename (assumed in $campo . "_name" in input)
     * @return boolean|string The name of the faile to save in the bd or false if something went wrong
     */
    private static function saveFileFromRequest(\Illuminate\Http\Request $input, $campo, array $detalles, $addNewName = true)
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
                $destinationPath = \Illuminate\Support\Str::finish(public_path($detalles['path']), '/');
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

                $filename .= \Illuminate\Support\Str::random($numRand) . $new_name;
                $disk = \Illuminate\Support\Arr::get($detalles, "disk", "local");
                $path = $file->store($destinationPath . $filename, $disk);
                $filename .= "." . $file->getClientOriginalExtension();
                $upload_success = $path !== false;
                if ($upload_success) {
                    if ($esImagen && isset($detalles['resize'])) {
                        foreach ($detalles['resize'] as $resize) {
                            $image_resize = Image::make($destinationPath . $filename);
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
                            $destinationPath = \Illuminate\Support\Str::finish(public_path($resize['path']), '/');
                            $quality = 100;
                            if (isset($resize['quality'])) {
                                $quality = $resize['quality'];
                            }
                            $image_resize->save($destinationPath . $filename, $quality);
                        }
                        // resizing an uploaded file
                        //return Response::json('success', 200);
                    }
                    $newFilename = $filename;
                    if (isset($detalles['saveCompletePath'])) {
                        if ($detalles['saveCompletePath']) {
                            $newFilename = \Illuminate\Support\Str::finish(str_replace("/", "\\", $detalles['path']), "\\") . $filename;
                            //$newFilename = str_replace("\\","/",$detalles['path']) . $filename;
                        }
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
     * @param array $config The ocnfiguration array
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
            if (\Sirgrimorum\CrudGenerator\CrudGenerator::inside_array($datos, "hide", $action) === false) {
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
