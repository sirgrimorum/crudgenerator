<?php

namespace Sirgrimorum\CrudGenerator;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App;

class CrudController extends BaseController {

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    public function index($localecode, $modelo, Request $request) {
        App::setLocale($localecode);
        $modeloM = ucfirst($modelo);
        $config = CrudGenerator::getConfig($modelo);
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        return $this->devolver($request, $config, $this->checkPermission($request, $config));
    }

    public function create($localecode, $modelo, Request $request) {
        App::setLocale($localecode);
        $modeloM = ucfirst($modelo);
        $config = CrudGenerator::getConfig($modelo);
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        return $this->devolver($request, $config, $this->checkPermission($request, $config));
    }

    public function store($localecode, $modelo, Request $request) {
        App::setLocale($localecode);

        $modeloM = ucfirst($modelo);
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        //$config = CrudGenerator::translateConfig($config);
        $config = CrudGenerator::getConfig($modelo);
        if (!$permiso = $this->checkPermission($request, $config)) {
            return $this->devolver($request, $config, $permiso);
        }
        if (($validator = $this->validateModel($config, $request)) !== false) {
            if ($validator->fails()) {
                return $this->devolverValidation($validator, $modelo, $request);
            }
        }
        $objeto = $this->saveObjeto($config, $request);
        return $this->devolver($request, $config, $permiso);
    }

    public function show($localecode, $modelo, $registro, Request $request) {
        App::setLocale($localecode);

        $modeloM = ucfirst($modelo);
        //$registro = $modeloM::find($id);
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        $config = CrudGenerator::getConfig($modelo);
        return $this->devolver($request, $config, $this->checkPermission($request, $config, $registro), $registro);
    }

    public function edit($localecode, $modelo, $registro, Request $request) {
        App::setLocale($localecode);

        $modeloM = ucfirst($modelo);
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        $config = CrudGenerator::getConfig($modelo);
        return $this->devolver($request, $config, $this->checkPermission($request, $config, $registro), $registro);
    }

    public function update($localecode, $modelo, $registro, Request $request) {
        App::setLocale($localecode);

        $modeloM = ucfirst($modelo);
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        //$config = CrudGenerator::translateConfig($config);
        $config = CrudGenerator::getConfig($modelo);
        if (!$permiso = $this->checkPermission($request, $config, $registro)) {
            return $this->devolver($request, $config, $permiso, $registro);
        }
        if (($validator = $this->validateModel($config, $request)) !== false) {
            if ($validator->fails()) {
                return $this->devolverValidation($validator, $modelo, $request, $registro);
            }
        }
        $objeto = $this->saveObjeto($config, $request, $registro);
        return $this->devolver($request, $config, $permiso, $objeto);
    }

    public function destroy($localecode, $modelo, $registro, Request $request) {
        App::setLocale($localecode);

        $modeloM = ucfirst($this->modelo);
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        //$config = CrudGenerator::translateConfig($config);
        $config = CrudGenerator::getConfig($modelo);
        if (!$permiso = $this->checkPermission($request, $config, $registro)) {
            return $this->devolver($request, $config, $permiso, $registro);
        }
        $muerto = $config['modelo']::find($registro);
        $muerto->delete();
        return $this->devolver($request, $config, $permiso);
    }

    private function devolverValidation($validator, $modelo, Request $request, $registro = 0) {
        $action = substr($request->route()->getName(), stripos($request->route()->getName(), "::") + 2);
        $localecode = App::getLocale();

        $tipoReturn = "content";
        if ($request->has('_return')) {
            if ($request->_return == 'pureJson') {
                $tipoReturn = "json";
            } elseif ($request->_return == 'modal') {
                $tipoReturn = "modal";
            } elseif ($request->_return == 'simple') {
                $tipoReturn = "simple";
            }
        }
        if ($request->ajax() || $tipoReturn == 'json') {
            $result = [
                'status' => 422,
                'statusText' => trans("crudgenerator::admin.messages.422"),
                'errors' => $validator->errors(),
            ];
            if ($request->has('callback')) {
                return response()->json($result, 422)->setCallback($request->callback);
            } else {
                return response()->json($result, 422);
            }
        } else {
            $extra = ['modelo' => $modelo, 'localecode' => $localecode];
            if ($action == 'update') {
                $extra['registro'] = $registro;
                $newaction = 'sirgrimorum_modelo::edit';
            } else {
                $newaction = 'sirgrimorum_modelos::create';
            }
            if ($tipoReturn == "content") {
                $getReturn = "";
            } else {
                $getReturn = "?_return=" . $tipoReturn;
            }
            //return route('sirgrimorum_modelo::' . $newaction, $extra) . $getReturn;
            return redirect(route($newaction, $extra) . $getReturn)
                            ->withInput()
                            ->withErrors($validator);
        }
    }

    private function devolver(Request $request, $config, $permiso, $objeto = 0) {
        $action = substr($request->route()->getName(), stripos($request->route()->getName(), "::") + 2);
        $localecode = App::getLocale();
        $modelo = strtolower(class_basename($config["modelo"]));
        $plural = $modelo . 's';
        $modeloM = ucfirst($modelo);
        $mensajes=[];
        if (is_array(trans("crudgenerator::admin.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::admin.messages"));
        }
        if (is_array(trans("crudgenerator::' . strtolower($modelo) . '.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::' . strtolower($modelo) . '.messages"));
        }
        if (!is_object($objeto)) {
            $registro = $objeto;
            if ($registro > 0) {
                $objeto = $config["modelo"]::find($registro);
            }
        } else {
            $registro = $objeto->{$config['id']};
        }
        $tipoReturn = "content";
        if ($request->has('_return')) {
            if ($request->_return == 'pureJson') {
                $tipoReturn = "json";
            } elseif ($request->_return == 'modal') {
                $tipoReturn = "modal";
            } elseif ($request->_return == 'simple') {
                $tipoReturn = "simple";
            }
        }
        if ($permiso) {
            $result = "";
            if ($tipoReturn == 'json') {
                switch ($action) {
                    case "create":
                        $result = [
                            config("sirgrimorum.crudgenerator.status_messages_key") => $mensajes['na'],
                        ];
                        break;
                    case "index":
                        $crud = new CrudGenerator($request->app);
                        $result = $crud->lists_array($config);
                        break;
                    case "show":
                    case "edit":
                        $crud = new CrudGenerator($request->app);
                        $config = $crud->translateConfig($config);
                        $result = $crud->registry_array($config, $registro);
                        break;
                    case "store":
                    case "update":
                    case "destroy":
                        if ($registro == 0) {
                            $nombre = "";
                        } else {
                            $nombre = $objeto->{$config['nombre']};
                        }
                        $result = [
                            config("sirgrimorum.crudgenerator.status_messages_key") => str_replace(":modelName", $nombre, $mensajes[$action . "_success"])
                        ];
                        break;
                }
                //return "<pre>" . print_r($result, true) . "</pre>";
            } else {
                switch ($action) {
                    case "index":
                    case "create":
                        $result = view('sirgrimorum::admin.' . $action . "." . $tipoReturn, [
                            "modelo" => $modeloM,
                            "base_url" => route('sirgrimorum_home', $localecode),
                            "plural" => $plural,
                            "config" => $config,
                        ]);
                        break;
                    case "show":
                    case "edit":
                        $result = view('sirgrimorum::admin/' . $action . "." . $tipoReturn, [
                            "modelo" => $modeloM,
                            "base_url" => route('sirgrimorum_home', $localecode),
                            "plural" => $plural,
                            "registro" => $registro,
                            "config" => $config,
                        ]);
                        break;
                    case "store":
                    case "update":
                    case "destroy":
                        if ($registro == 0) {
                            $nombre = "";
                        } else {
                            $nombre = $objeto->{$config['nombre']};
                        }
                        if ($tipoReturn == "content") {
                            $getReturn = "";
                        } else {
                            $getReturn = "?_return=" . $tipoReturn;
                        }
                        $result = redirect(route('sirgrimorum_modelos::index', [
                                    'modelo' => $modelo,
                                    'localecode' => $localecode,
                                ]) . $getReturn)->with(config("sirgrimorum.crudgenerator.status_messages_key"), str_replace(":modelName", $nombre, $mensajes[$action . "_success"]))
                        ;
                        break;
                }
            }
            if ($request->ajax() || $tipoReturn == 'json') {
                $result = [
                    'status' => 200,
                    'statusText' => trans("crudgenerator::admin.messages.200"),
                    'result' => $result,
                ];
                if ($request->has('callback')) {
                    return response()->json($result, 200)->setCallback($request->callback);
                } else {
                    return response()->json($result, 200);
                }
            } else {
                return $result;
            }
        } else {
            switch ($action) {
                case "index":
                case "create":
                    $result = [
                        config("sirgrimorum.crudgenerator.error_messages_key") => $mensajes['permission'],
                        config("sirgrimorum.crudgenerator.login_redirect_key") => route("sirgrimorum_modelos::" . $action, ['localecode' => $localecode, 'modelo' => $modelo])
                    ];
                    break;
                case "show":
                case "edit":
                    $result = [
                        config("sirgrimorum.crudgenerator.error_messages_key") => $mensajes['permission'],
                        config("sirgrimorum.crudgenerator.login_redirect_key") => route("sirgrimorum_modelo::" . $action, ['localecode' => $localecode, 'modelo' => $modelo, 'registro' => $registro])
                    ];
                    break;
                case "store":
                case "destroy":
                    $result = [
                        config("sirgrimorum.crudgenerator.error_messages_key") => $mensajes['permission'],
                        config("sirgrimorum.crudgenerator.login_redirect_key") => route("sirgrimorum_modelos::index", ['localecode' => $localecode, 'modelo' => $modelo])
                    ];
                    break;
                case "update":
                    $result = [
                        config("sirgrimorum.crudgenerator.error_messages_key") => $mensajes['permission'],
                        config("sirgrimorum.crudgenerator.login_redirect_key") => route("sirgrimorum_modelo::edit", ['localecode' => $localecode, 'modelo' => $modelo, 'registro' => $registro])
                    ];
                    break;
            }
            if ($request->ajax() || $tipoReturn == 'json') {
                $result = [
                    'status' => 403,
                    'statusText' => $mensajes['permission'],
                    'result' => $result,
                ];
                if ($request->has('callback')) {
                    return response()->json($result, 403)->setCallback($request->callback);
                } else {
                    return response()->json($result, 403);
                }
            } else {
                return redirect(config("sirgrimorum.crudgenerator.login_path"))->with($result);
            }
        }
    }

    private function checkPermission(Request $request, $config, $registro = 0) {
        $action = substr($request->route()->getName(), stripos($request->route()->getName(), "::") + 2);
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
            if ($action != "store") {
                if ($registro > 0) {
                    $objModelo = $config['modelo']::find($registro);
                    $resultado = (bool) $callback($objModelo);
                } else {
                    $resultado = (bool) $callback();
                }
            } else {
                $resultado = (bool) $callback($request);
            }
        } else {
            $resultado = (bool) $callback;
        }

        return $resultado;
    }

    private function validateModel($config, Request $request) {
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
        if (count($rules) > 0) {
            $customAttributes = [];
            foreach ($rules as $field => $datos) {
                if (array_has($config, "campos." . $field . ".label")) {
                    $customAttributes[$field] = array_get($config, "campos." . $field . ".label");
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

    private function saveObjeto($config, Request $input, $obj = null) {
        if (!$obj) {
            $objModelo = new $config['modelo'];
        } else {
            if (!is_object($obj)) {
                $objModelo = $config['modelo']::find($obj);
            } else {
                $objModelo = $obj;
            }
        }
        //echo "<p>modelo</p><pre>" . print_r($objModelo, true) . "</pre>";

        if ($objModelo) {
            foreach ($config['campos'] as $campo => $detalles) {
                if (!isset($detalles["nodb"])) {
                    switch ($detalles['tipo']) {
                        case 'checkbox':
                        case 'email':
                        case 'hidden':
                        case 'html':
                        case 'number':
                        case 'password':
                        case 'radio':
                        case 'slider':
                        case 'text':
                        case 'textarea':
                            if ($input->has($campo)) {
                                $objModelo->{$campo} = $input->input($campo);
                            } elseif (isset($detalles['valor'])) {
                                $objModelo->{$campo} = $detalles['valor'];
                            }
                            break;
                        case 'relationship':
                            if ($input->has($campo)) {
                                if (CrudController::hasRelation($objModelo, $campo)) {
                                    $objModelo->{$campo}()->attach($input->input($campo));
                                } else {
                                    $objModelo->{$campo} = $input->input($campo);
                                }
                            } elseif (isset($detalles['valor'])) {
                                if (CrudController::hasRelation($objModelo, $campo)) {
                                    $objModelo->{$campo}()->attach($detalles['valor']);
                                } else {
                                    $objModelo->{$campo} = $detalles['valor'];
                                }
                            }
                            break;
                        case 'relationships':
                            if ($input->has($campo)) {
                                $objModelo->{$campo}()->attach($input->input($campo));
                            } elseif (isset($detalles['valor'])) {
                                $objModelo->{$campo}()->attach($detalles['valor']);
                            }
                            break;
                        case 'relationshipssel':
                            if ($input->has($campo)) {
                                $datos = [];
                                foreach ($input->input($campo) as $id => $pivot) {
                                    $datos[$id] = [];
                                    foreach ($detalles['columnas'] as $subdetalles) {
                                        if ($subdetalles['type'] == "text" || $subdetalles['type'] == "number" || $subdetalles['type'] == "hidden") {
                                            if ($input->has($campo . "_" . $subdetalles['campo'] . "_" . $id)) {
                                                $datos[$id][$subdetalles['campo']] = $input->input($campo . "_" . $subdetalles['campo'] . "_" . $id);
                                            } else {
                                                $datos[$id][$subdetalles['campo']] = $subdetalles['valor'];
                                            }
                                        }
                                    }
                                }
                                $objModelo->{$campo}()->attach($datos);
                            } elseif (isset($detalles['valor'])) {
                                $objModelo->{$campo}()->attach($detalles['valor']);
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
                            if ($input->has($campo)) {
                                if (isset($detalles["timezone"])) {
                                    $timezone = $detalles["timezone"];
                                } else {
                                    $timezone = config("app.timezone");
                                }
                                $date = new \Carbon\Carbon($input->input($campo), $timezone);
                                $objModelo->{$campo} = $date->getTimestamp();
                            } elseif (isset($detalles['valor'])) {
                                if (isset($detalles["timezone"])) {
                                    $timezone = $detalles["timezone"];
                                } else {
                                    $timezone = config("app.timezone");
                                }
                                $date = new \Carbon\Carbon($detalles['valor'], $timezone);
                                $objModelo->{$campo} = $date->getTimestamp();
                            }
                            break;
                        case 'file':
                            if ($input->has($campo)) {
                                $file = Input::file($campo);

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
                                    $destinationPath = public_path() . str_finish($detalles['path'], '/');
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
                                    $filename = $filename . str_random($numRand) . "." . $file->getClientOriginalExtension();
                                    $upload_success = $file->move($destinationPath, $filename);
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
                                                $destinationPath = public_path() . str_finish($resize['path'], '/');
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
                                                $newFilename = $detalles['path'] . $filename;
                                            }
                                        }
                                        $objModelo->{$campo} = $newFilename;
                                    } else {
                                        return Response::json('error', 400);
                                    }
                                }
                            } elseif (isset($detalles['valor'])) {
                                $objModelo->{$campo} = $detalles['valor'];
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
            $objModelo->save();
            return $objModelo;
        } else {
            return false;
        }
    }

    public static function hasRelation($model, $key) {
        if (method_exists($model, $key)) {
            return is_a($model->$key(), "Illuminate\Database\Eloquent\Relations\Relation");
        } else {
            return false;
        }
    }

}
